<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: File/file_widget.php
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
$widget_locale = fusion_get_locale('', WIDGETS."/file/locale/".LANGUAGE.".php");

$widget_title = $widget_locale['f0107'];
$widget_description = $widget_locale['f0106'];
$widget_icon = "<span class='fa-stack fa-2x'><i class='fa fa-square-o fa-stack-2x'></i><i class='fa fa-file fa-stack-1x'></i></span>";
$widget_admin_file = "file_admin.php";
$widget_display_file = "file.php";
$widget_admin_callback = "fileWidgetAdmin";
$widget_display_callback = "fileWidget";