<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Slider/slider_widget.php
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
$lang = file_exists(WIDGETS."slider/locale/".LANGUAGE.".php") ? WIDGETS."slider/locale/".LANGUAGE.".php" : WIDGETS."slider/locale/English.php";
$widget_locale = fusion_get_locale('', $lang);
/**
 * Widget SDK
 * $widget_title - The title of the widget
 * $widget_icon - You can use anything. Image, Glyphs
 * $widget_description - The description of the widget
 * $widget_admin_file - The administration class file
 * $widget_display_file - The display class file
 * $widget_admin_callback - The class name in the administration file
 * $widget_display_callback - The class name in the display file
 */
$widget_title = $widget_locale['SLDW_0100'];
$widget_icon = 'slider.svg';
$widget_description = $widget_locale['SLDW_0101'];
$widget_admin_file = "slider_admin.php";
$widget_display_file = "slider.php";
$widget_admin_callback = "carouselWidgetAdmin";
$widget_display_callback = "carouselWidget";
