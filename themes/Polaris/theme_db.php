<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Polaris/theme_db.php
| Author: Meangczac (Chan), Core Development Team
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

require_once __DIR__ . '/theme.php';

$locale = fusion_get_locale( '', POLARIS_LOCALE );

$theme_title       = 'Polaris';
$theme_description = $locale['POLARIS_100'];
$theme_screenshot  = '--';
$theme_author      = 'Meangczac';
$theme_web         = 'https://github.com/meangczac';
$theme_license     = 'AGPL3';
$theme_version     = '1.0.0';
$theme_folder      = 'Polaris';

$theme_insertdbrow[] = DB_SETTINGS_THEME . " (settings_name, settings_value, settings_theme) VALUES
    ('polaris_color_scheme', '', '" . $theme_folder . "'),
    ('polaris_github_url', '', '" . $theme_folder . "'),
    ('polaris_facebook_url', '', '" . $theme_folder . "'),
    ('polaris_twitter_url', '', '" . $theme_folder . "')
";

$theme_deldbrow[] = DB_SETTINGS_THEME . " WHERE settings_theme='" . $theme_folder . "'";
