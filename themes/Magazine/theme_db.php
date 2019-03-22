<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Magazine/theme_db.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

if (!defined('MG_LOCALE')) {
    if (file_exists(THEMES.'Magazine/locale/'.LANGUAGE.'.php')) {
        define('MG_LOCALE', THEMES.'Magazine/locale/'.LANGUAGE.'.php');
    } else {
        define('MG_LOCALE', THEMES.'Magazine/locale/English.php');
    }
}

$locale = fusion_get_locale('', MG_LOCALE);

$theme_title       = 'Magazine';
$theme_description = $locale['MG_description'];
$theme_screenshot  = 'screenshot.jpg';
$theme_author      = 'RobiNN';
$theme_web         = 'https://github.com/RobiNN1';
$theme_license     = 'AGPL3';
$theme_version     = '1.0.0';
$theme_folder      = 'Magazine';

$theme_insertdbrow[] = DB_SETTINGS_THEME." (settings_name, settings_value, settings_theme) VALUES
    ('github_url', '', '".$theme_folder."'),
    ('facebook_url', '', '".$theme_folder."'),
    ('twitter_url', '', '".$theme_folder."')
";

$theme_deldbrow[] = DB_SETTINGS_THEME." WHERE settings_theme='".$theme_folder."'";
