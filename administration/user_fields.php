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

use PHPFusion\Admins;

require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
$locale = fusion_get_locale('', [LOCALE.LOCALESET.'admin/fields.php']);

// Add tab for public fields, preference fields and security fields
// Add field type - preference, and security.
$admin = Admins::getInstance();
$admin->addAdminPage('UF', 'All Users', 'M1', ADMIN.'members.php'.$aidlink);
$admin->addAdminPage('UF', 'Add User', 'M2', ADMIN.'members.php'.$aidlink.'&amp;action=add');
$admin->addAdminPage('UF', 'Manage Signups', 'M3', ADMIN.'members.php'.$aidlink.'&amp;action=signup');
$admin->addAdminPage('UF', 'Administrators', 'M4', ADMIN.'administrators.php'.$aidlink);
$admin->addAdminPage('UF', 'User Fields', 'M5', ADMIN.'user_fields.php'.$aidlink);
$admin->addAdminPage('M5', "Public Fields", "UF-1", ADMIN.'user_fields.php'.fusion_get_aidlink());
$admin->addAdminPage( 'M5', "Preference Fields", "UF-2", ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=preferences' );
$admin->addAdminPage('M5', "Security Fields", "UF-3", ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=security');

$user_field = new PHPFusion\UserFieldsQuantum();
$user_field->setLocale($locale);
$user_field->setTitle($locale['202']);
$user_field->setAdminRights('UF');
$user_field->setMethod('input');
$user_field->displayQuantumAdmin();

require_once THEMES.'templates/footer.php';
