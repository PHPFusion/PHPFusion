<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_forum-stat_include.php
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
defined('IN_FUSION') || exit;

if ($profile_method == "input") {
    //Nothing here
    $user_fields = '';
    if (defined('ADMIN_PANEL')) { // To show in admin panel only.
        $user_fields .= "<div class='row'>\n<div class='col-xs-12 col-sm-3 strong'>".$locale['uf_forum-stat']."</div>\n<div class='col-xs-12 col-sm-9'>\n";
        if (defined('FORUM_EXIST')) {
            $user_fields .= "--";
        } else {
            $user_fields .= "<div class='alert alert-warning'><i class='fa fa-exclamation-triangle m-r-10'></i> ".$locale['uf_forum-stat_na']."</div>\n";
        }
        $user_fields .= "</div>\n</div>\n<br/>";
    }
} else if ($profile_method == "display") {
    if (defined('FORUM_EXIST')) {
        $user_fields = [
            'title' => $locale['uf_forum-stat'],
            'value' => number_format(dbcount("(forum_id)", DB_FORUM_POSTS, "post_author='".intval($_GET['lookup'])."'"))
        ];
    }
}
