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
class Photos_Controller extends Items_Controller {
  public function _show($photo) {
    access::required("view", $photo);

    $where = array("type != " => "album");
    $position = $photo->parent()->get_position($photo, $where);
    if ($position > 1) {
      list ($previous_item, $ignore, $next_item) =
        $photo->parent()->children(3, $position - 2, $where);
    } else {
      $previous_item = null;
      list ($next_item) = $photo->parent()->viewable()->children(1, $position, $where);
    }

    $template = new Theme_View("page.html", "item", "photo");
    $template->set_global("item", $photo);
    $template->set_global("children", array());
    $template->set_global("children_count", 0);
    $template->set_global("parents", $photo->parents());
    $template->set_global("next_item", $next_item);
    $template->set_global("previous_item", $previous_item);
    $template->set_global("sibling_count", $photo->parent()->viewable()->children_count($where));
    $template->set_global("position", $position);

    $template->content = new View("photo.html");

    $photo->view_count++;
    $photo->save();

    print $template;
  }

  public function update($photo_id) {
    access::verify_csrf();
    $photo = ORM::factory("item", $photo_id);
    access::required("view", $photo);
    access::required("edit", $photo);

    $form = photo::get_edit_form($photo);
    $valid = $form->validate();

    if ($valid) {
      $new_ext = pathinfo($form->edit_item->filename->value, PATHINFO_EXTENSION);
      $old_ext = pathinfo($photo->name, PATHINFO_EXTENSION);
      if (strcasecmp($new_ext, $old_ext)) {
        $form->edit_item->filename->add_error("illegal_extension", 1);
        $valid = false;
      }
    }

    if ($valid) {
      if ($form->edit_item->filename->value != $photo->name ||
          $form->edit_item->slug->value != $photo->slug) {
        // Make sure that there's not a name or slug conflict
        if ($row = Database::instance()
            ->select(array("name", "slug"))
            ->from("items")
            ->where("parent_id", $photo->parent_id)
            ->where("id <>", $photo->id)
            ->open_paren()
            ->where("name", $form->edit_item->filename->value)
            ->orwhere("slug", $form->edit_item->slug->value)
            ->close_paren()
            ->get()
            ->current()) {
          if ($row->name == $form->edit_item->filename->value) {
            $form->edit_item->filename->add_error("name_conflict", 1);
          }
          if ($row->slug == $form->edit_item->slug->value) {
            $form->edit_item->slug->add_error("slug_conflict", 1);
          }
          $valid = false;
        }
      }
    }

    if ($valid) {
      $watching_album = $photo->url() != ($location = parse_url(request::referrer(), PHP_URL_PATH));

      $photo->title = $form->edit_item->title->value;
      $photo->description = $form->edit_item->description->value;
      $photo->slug = $form->edit_item->slug->value;
      $photo->rename($form->edit_item->filename->value);
      $photo->save();
      module::event("item_edit_form_completed", $photo, $form);

      log::success("content", "Updated photo", "<a href=\"{$photo->url()}\">view</a>");
      message::success(
                       t("Saved photo %photo_title",
                         array("photo_title" => html::purify($photo->title))));

      print json_encode(
        array("result" => "success",
              "location" => $watching_album ? $location : $photo->url()));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  public function form_edit($photo_id) {
    $photo = ORM::factory("item", $photo_id);
    access::required("view", $photo);
    access::required("edit", $photo);

    print photo::get_edit_form($photo);
  }
}
