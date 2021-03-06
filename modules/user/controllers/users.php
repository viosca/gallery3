<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class Users_Controller extends Controller {
  public function update($id) {
    $user = user::lookup($id);

    if ($user->guest || $user->id != identity::active_user()->id) {
      access::forbidden();
    }

    $form = $this->_get_edit_form($user);
    $valid = $form->validate();
    if ($valid) {
      $user->full_name = $form->edit_user->full_name->value;
      if ($form->edit_user->password->value) {
        $user->password = $form->edit_user->password->value;
      }
      $user->email = $form->edit_user->email->value;
      $user->url = $form->edit_user->url->value;
      if ($form->edit_user->locale) {
        $desired_locale = $form->edit_user->locale->value;
        $new_locale = $desired_locale == "none" ? null : $desired_locale;
        if ($new_locale != $user->locale) {
          // Delete the session based locale preference
          setcookie("g_locale", "", time() - 24 * 3600, "/");
        }
        $user->locale = $new_locale;
      }
      $user->save();
      module::event("user_edit_form_completed", $user, $form);

      message::success(t("User information updated."));
      print json_encode(
        array("result" => "success",
              "resource" => url::site("users/{$user->id}")));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  public function form_edit($id) {
    $user = user::lookup($id);
    if ($user->guest || $user->id != identity::active_user()->id) {
      access::forbidden();
    }

    $v = new View("user_form.html");
    $v->form = $this->_get_edit_form($user);
    print $v;
  }

  private function _get_edit_form($user) {
    $form = new Forge("users/update/$user->id", "", "post", array("id" => "g-edit-user-form"));
    $group = $form->group("edit_user")->label(t("Edit User: %name", array("name" => $user->name)));
    $group->input("full_name")->label(t("Full Name"))->id("g-fullname")->value($user->full_name);
    self::_add_locale_dropdown($group, $user);
    $group->password("password")->label(t("Password"))->id("g-password");
    $group->password("password2")->label(t("Confirm Password"))->id("g-password2")
      ->matches($group->password);
    $group->input("email")->label(t("Email"))->id("g-email")->value($user->email);
    $group->input("url")->label(t("URL"))->id("g-url")->value($user->url);
    $form->add_rules_from($user);

    $minimum_length = module::get_var("user", "mininum_password_length", 5);
    $form->edit_user->password
      ->rules($minimum_length ? "length[$minimum_length, 40]" : "length[40]");

    module::event("user_edit_form", $user, $form);
    $group->submit("")->value(t("Save"));
    return $form;
  }

  /** @todo combine with Admin_Users_Controller::_add_locale_dropdown */
  private function _add_locale_dropdown(&$form, $user=null) {
    $locales = locales::installed();
    foreach ($locales as $locale => $display_name) {
      $locales[$locale] = SafeString::of_safe_html($display_name);
    }
    if (count($locales) > 1) {
      // Put "none" at the first position in the array
      $locales = array_merge(array("" => t("« none »")), $locales);
      $selected_locale = ($user && $user->locale) ? $user->locale : "";
      $form->dropdown("locale")
        ->label(t("Language Preference"))
        ->options($locales)
        ->selected($selected_locale);
    }
  }
}
