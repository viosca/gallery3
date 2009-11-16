<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="g-block ui-helper-clearfix">
  <h1> <?= t("Gallery Modules") ?> </h1>
  <p>
    <?= t("Power up your Gallery by adding more modules! Each module provides new cool features.") ?>
  </p>

  <div class="g-block-content">
    <form method="post" action="<?= url::site("admin/modules/save") ?>">
      <?= access::csrf_form_field() ?>
      <table>
        <tr>
          <th> <?= t("Installed") ?> </th>
          <th> <?= t("Name") ?> </th>
          <th> <?= t("Version") ?> </th>
          <th> <?= t("Description") ?> </th>
        </tr>
        <? foreach ($available as $module_name => $module_info):  ?>
        <tr class="<?= text::alternate("g-odd", "g-even") ?>">
          <? $data = array("name" => $module_name); ?>
          <? if ($module_info->locked) $data["disabled"] = 1; ?>
          <td> <?= form::checkbox($data, '1', module::is_active($module_name)) ?> </td>
          <td> <?= t($module_info->name) ?> </td>
          <td> <?= $module_info->version ?> </td>
          <td> <?= t($module_info->description) ?> </td>
        </tr>
        <? endforeach ?>
      </table>
      <input type="submit" value="<?= t("Update")->for_html_attr() ?>" />
    </form>
  </div>
</div>
