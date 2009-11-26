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
class Group_Model extends ORM implements Group_Definition {
  protected $has_and_belongs_to_many = array("users");

  var $form_rules = array(
    "name" => "required|length[4,255]");

  /**
   * @see ORM::delete()
   */
  public function delete($id=null) {
    $old = clone $this;
    module::event("group_before_delete", $this);
    parent::delete($id);
    module::event("group_deleted", $old);
  }

  public function save() {
    if (!$this->loaded()) {
        $created = 1;
    }
    parent::save();
    if (isset($created)) {
      module::event("group_created", $this);
    } else {
      module::event("group_updated", $this->original(), $this);
    }
    return $this;
  }
}