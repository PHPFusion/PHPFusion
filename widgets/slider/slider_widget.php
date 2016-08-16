<?php
// Path Definitions
$widget_locale = fusion_get_locale('', WIDGETS."slider/locale/".LANGUAGE.".php");
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
$widget_title = $widget_locale['0100'];
$widget_icon = "<span class='fa-stack fa-2x'><i class='fa fa-square-o fa-stack-2x'></i><i class='fa fa-refresh fa-stack-1x'></i></span>";
$widget_description = $widget_locale['0101'];
$widget_admin_file = "slider_admin.php";
$widget_display_file = "slider.php";
$widget_admin_callback = "carouselWidgetAdmin";
$widget_display_callback = "carouselWidget";