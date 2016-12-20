<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: FusionTheme/theme.php
| Author: PHP-Fusion Inc
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once THEME."autoloader.php";
$theme_settings = get_theme_settings('FusionTheme');
$theme_package = !empty($theme_settings['theme_pack']) ? $theme_settings['theme_pack'] : 'Nebula';
ThemeFactory\Core::getInstance()->get_ThemePack($theme_package);