<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login/user_fields/user_fb_connect_include.php
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
// What do i need here?
// user_fb_id to be installed in the user fields. nvm. alter and modify user database manually until i get the plugin to work before coding this part.
// i'm not even sure the whole login flow yet.
// be right back.
if ($profile_method == "input") {

    if (defined('IN_QUANTUM')) {
        $user_fields = "<div class='p-15 p-l-0 p-r-0'>\n<div class='row m-0'>\n
        <div class='col-xs-12 col-sm-3'><strong>".$locale['uf_fb_connect']."</strong></div>
        <div class='col-xs-12 col-sm-9'><div class='strong'>".$locale['uf_fb_connect_desc']."</div></div>
        </div>\n</div>\n";

    } else {

        /**
         * Further roadmap in future...
         * We will need email merging for this to work.
         * Problem:
         * The facebook button uses your current login session with Facebook, which may be on a different email account.
         * Now, if the email account is different here, we need to bind both email to one single account.
         * We will need an email table creation table...
         *
         *
         *
         */
        /*require_once(INFUSIONS.'login/user_fields/facebook_connect/facebook_connect.php');
        $fb = new Facebook_Connect();
        $user = fusion_get_userdata();
        $locale = fusion_get_locale();

        $locale['user_fb_connect_400'] = 'Facebook is not yet connected to your user account';
        $locale['user_fb_connect_401'] = 'Facebook is connected to your account';
        $locale['user_fb_connect_402'] = 'Connect to Facebook';
        $locale['user_fb_connect_403'] = 'Disconnect from Facebook';

        $tpl = \PHPFusion\Template::getInstance('facebook_connect_admin');
        $tpl->set_template(__DIR__.'/facebook_connect/templates/connect_admin.html');
        $tpl->set_tag('header_id', 'fbc_header');
        $tpl->set_tag('site_name', fusion_get_settings('sitename'));
        $tpl->set_tag('header_text', $locale['user_fb_connect_400']);
        $tpl->set_tag('button_text', $locale['user_fb_connect_402']);
        $tpl->set_tag('content', $fb->display_login(array('skip_auth'=>true, 'facebook_button'=>false)));
        $user_fields = $tpl->get_output(); */

    }
    // Display in profile
} else if ($profile_method == "display") {
    // nothing to display
}