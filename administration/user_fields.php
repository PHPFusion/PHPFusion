<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: UserFields.php
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

require_once "../maincore.php";
include LOCALE.LOCALESET.'admin/fields.php';
if (!checkrights('UFC') || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }
require_once THEMES."templates/admin_header.php";
require_once CLASSES."quantumFields.class.php";

$user_field = new quantumFields();
$user_field->system_title = 'User Profile Configuration';
$user_field->basic_fields_title = 'Registration Fields';
$user_field->category_db = DB_USER_FIELD_CATS;
$user_field->field_db = DB_USER_FIELDS;
$user_field->plugin_folder = INCLUDES."user_fields/";
$user_field->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
//$user_field->debug = TRUE;
$user_field->boot();

require_once THEMES."templates/footer.php";
?>