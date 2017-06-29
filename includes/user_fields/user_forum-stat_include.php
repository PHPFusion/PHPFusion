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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if ($profile_method == "input") {
    //Nothing here
    $user_fields = '';
    if (defined('ADMIN_PANEL')) { // To show in admin panel only.
        $user_fields = "<div class='well m-t-5 text-center'>".$locale['uf_forum-stat']."</div>";
    }
} elseif ($profile_method == "display") {
    if (infusion_exists('forum')) {
    	$user_fields = array(
    		'title' => $locale['uf_forum-stat'],
    		'value' => number_format(dbcount("(forum_id)", DB_FORUM_POSTS, "post_author='".intval($_GET['lookup'])."'"))."");
	}
}
