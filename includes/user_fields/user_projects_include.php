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

/**
 * Implements UF to make the render installable to UProfile Engine.
 */
if ($profile_method == "input") {
    //Nothing here
    $user_fields = '';
    if (defined('ADMIN_PANEL')) { // To show in admin panel only.
        $user_fields = "<div class='list-group-item strong spacer-sm text-center'>".$locale['uf_projects']."</div>";
    }
} elseif ($profile_method == "display") {
    global $userFields;
    $user = $userFields->getUserData();
    require_once ROADMAP.'classes/user_fields.php';

    $html = new \Roadmap\User_Fields();
    $value = $html->view_plugin($user);
    \ThemeFactory\Core::setParam('profile_nav', $html->get_subnav());

    $user_fields = array(
        'title' => $html::get_title(),
        'value' => $value,
    );
}