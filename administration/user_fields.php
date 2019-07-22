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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
$locale = fusion_get_locale('', [LOCALE.LOCALESET.'admin/fields.php']);

// Add tab for public fields, preference fields and security fields
// Add field type - preference, and security.
$admin = \PHPFusion\Admins::getInstance();
$admin->addAdminPage('UF', "Public Fields", "UF-1", ADMIN.'user_fields.php'.fusion_get_aidlink());
$admin->addAdminPage('UF', "Preference Fields", "UF-2", ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=preference');
$admin->addAdminPage('UF', "Security Fields", "UF-3", ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=security');

$user_field = new PHPFusion\UserFieldsQuantum();
$user_field->setLocale($locale);
$user_field->setTitle($locale['202']);
$user_field->setAdminRights('UF');
$user_field->setMethod('input');
$user_field->displayQuantumAdmin();

require_once THEMES.'templates/footer.php';
