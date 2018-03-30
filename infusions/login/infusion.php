<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login/infusion.php
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
    die("Access Denied");
}

$locale = fusion_get_locale('', LOGIN_LOCALESET.'login.php');

// Infusion general information
$inf_title = $locale['login_000'];
$inf_description = $locale['login_001'];
$inf_version = "1.0.0";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "login";
$inf_image = "login.svg";

$inf_adminpanel[] = [
    "image"  => $inf_image,
    "page"   => 3,
    "rights" => "L1",
    "title"  => $locale['login_002'],
    "panel"  => "login_admin.php"
];

/**
 * Login ID     incremental
 * Login Type   2FA (2 factor authentication) / LGA (Login Authentication)
 * Login Name   Title of Driver
 */

$inf_newtable[] = DB_LOGIN." (
    login_name VARCHAR(100) NOT NULL DEFAULT '0',
    login_type VARCHAR(10) NOT NULL DEFAULT '0',
    login_status TINYINT(1) NOT NULL DEFAULT '0',
    login_settings TEXT NOT NULL,
    PRIMARY KEY (login_name)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_LOGIN_EMAILS." (    
    email_address VARCHAR(50) NOT NULL DEFAULT '',
    email_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    email_type VARCHAR(10) NOT NULL DEFAULT '',
    email_ref VARCHAR(100) NOT NULL DEFAULT '',
    email_verified TINYINT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (email_address),
    KEY email_user (email_user)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_droptable[] = DB_LOGIN;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='L1'";
