<?php
$widget_locale = fusion_get_locale('', WIDGETS."comments/locale/".LANGUAGE.".php");
// Path Definitions
$widget_title = $widget_locale['0100'];
$widget_icon = "<span class='fa-stack fa-2x'><i class='fa fa-square-o fa-stack-2x'></i><i class='fa fa-comment-o fa-stack-1x'></i></span>";
$widget_description = $widget_locale['0101'];
$widget_admin_file = "comments_admin.php";
$widget_display_file = "comments.php";
$widget_admin_callback = "commentsWidgetAdmin";
$widget_display_callback = "commentsWidget";