<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Comments/comments_widget.php
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
$lang = file_exists(WIDGETS."comments/locale/".LANGUAGE.".php") ? WIDGETS."comments/locale/".LANGUAGE.".php" : WIDGETS."comments/locale/English.php";
$widget_locale = fusion_get_locale('', $lang);

// Path Definitions
$widget_title = $widget_locale['CMW_0100'];
$widget_icon = 'comments.svg';
$widget_description = $widget_locale['CMW_0101'];
$widget_admin_file = "comments_admin.php";
$widget_display_file = "comments.php";
$widget_admin_callback = "commentsWidgetAdmin";
$widget_display_callback = "commentsWidget";
