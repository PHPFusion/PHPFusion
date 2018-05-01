<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Ratings/ratings_widget.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
// Path Definitions
$lang = file_exists(WIDGETS."ratings/locale/".LANGUAGE.".php") ? WIDGETS."ratings/locale/".LANGUAGE.".php" : WIDGETS."ratings/locale/English.php";
$widget_locale = fusion_get_locale('', $lang);

$widget_title = $widget_locale['RTW_0100'];
$widget_icon = 'ratings.svg';
$widget_description = $widget_locale['RTW_0101'];
$widget_admin_file = 'ratings_admin.php';
$widget_display_file = 'ratings.php';
$widget_admin_callback = 'ratingsWidgetAdmin';
$widget_display_callback = 'ratingsWidget';
