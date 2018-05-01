<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_social_include.php
| Author: PHP-Fusion Inc.
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
        $user_fields = "<div class='well m-t-5 text-center strong'>Fusion Social Interface</div>";
    }
} elseif ($profile_method == 'display') {
    global $userFields;
    // Load Fusion Social UF
    require_once INFUSIONS.'social/autoloader.php';
    $html = new \FusionSocial\View\SocialProfile();
    $html->set_userdata($userFields->getUserData());
    $user_fields = array(
        'title' => '',
        'value' => $html->viewProfile(),
    );
}