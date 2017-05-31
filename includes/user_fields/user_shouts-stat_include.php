<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_shouts-stat_include.php
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
        if (infusion_exists('shoutbox')) {
    	    $user_fields = "<div class='well m-t-5 text-center'>".$locale['uf_shouts-stat']."</div>";
		}
    }
} elseif ($profile_method == "display") {
    if (infusion_exists('shoutbox')) {
		$user_fields = array(
			'title' => $locale['uf_shouts-stat'],
			'value' => number_format(dbcount("(shout_id)", DB_SHOUTBOX, "shout_name='".intval($_GET['lookup'])."'")));
	}
}
