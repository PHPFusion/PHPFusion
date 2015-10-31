<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: maintenance.php
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
$locale = array();
require_once "maincore.php";
if (!fusion_get_settings("maintenance")) {
	redirect("index.php");
}
$info = array();
if (!iMEMBER) {
    switch(fusion_get_settings("login_method")) {
        case "2" :
            $placeholder = $locale['global_101c'];
            break;
        case "1" :
            $placeholder = $locale['global_101b'];
            break;
        default:
            $placeholder = $locale['global_101a'];
    }
    $_POST['user_name'] = isset($_POST['user_name']) ? form_sanitizer($_POST['user_name'], "", "user_name") : "";
    $_POST['user_pass'] = isset($_POST['user_pass']) ? form_sanitizer($_POST['user_pass'], "", "user_pass") : "";
    $info = array(
        "open_form" =>openform('loginpageform', 'POST', fusion_get_settings("opening_page")),
        "user_name" => form_text('user_name', "", $_POST['user_name'], array('placeholder' => $placeholder, "inline"=>TRUE)),
        "user_pass" => form_text('user_pass', "", $_POST['user_pass'], array('placeholder' => $locale['global_102'],'type' => 'password', "inline"=>TRUE)),
        "remember_me" => form_checkbox("remember_me", $locale['global_103'], ""),
        "login_button" => form_button('login', $locale['global_104'], $locale['global_104'], array('class' => 'btn-primary btn-block m-b-20')),
        "registration_link" => (fusion_get_settings("enable_registration")) ? "<p>".$locale['global_105']."</p>\n" : "",
        "forgot_password_link" => $locale['global_106'],
        "close_form" => closeform()
    );
}

ob_start();
require_once INCLUDES."header_includes.php";
require_once INCLUDES."theme_functions_include.php";
require_once THEMES."templates/render_functions.php";
include THEMES."templates/layout.php";
include THEMES."templates/global/maintenance.php";
display_maintenance($info);
$content = ob_get_contents();
ob_end_clean();
echo $content;