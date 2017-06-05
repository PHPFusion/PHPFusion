<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_fields.php
| Author: PHP-Fusion Development Team
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
require_once THEMES."templates/admin_header.php";
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/fields.php");
$user_field = new PHPFusion\QuantumFields();
$user_field->setSystemTitle($locale['202']);
$user_field->setAdminRights('UF');
$user_field->setCategoryDb(DB_USER_FIELD_CATS);
$user_field->setFieldDb(DB_USER_FIELDS);
$user_field->setMethod('input');
$user_field->setPluginFolder(INCLUDES."user_fields/");
$user_field->setPluginLocaleFolder(LOCALE.LOCALESET."user_fields/");
$user_field->displayQuantumAdmin();
require_once THEMES."templates/footer.php";