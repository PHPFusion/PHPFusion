<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login/infusion_db.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied!");
}

if (!defined('LOGIN_LOCALESET')) {
    define('LOGIN_LOCALESET', INFUSIONS.'login/locale/'.LANGUAGE.'/');
}

if (!defined('DB_LOGIN')) {
    define('DB_LOGIN', DB_PREFIX.'login');
}

if (!defined('DB_LOGIN_EMAILS')) {
    define('DB_LOGIN_EMAILS', DB_PREFIX.'login_emails');
}

\PHPFusion\Admins::getInstance()->setAdminPageIcons("L1", "<i class='admin-ico fa fa-fw fa-sign-in'></i>");
