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

if ($profile_method == "input") {

    if (defined('IN_QUANTUM')) {

        $user_fields = "<div class='p-15 p-l-0 p-r-0'>\n<div class='row m-0'>\n
        <div class='col-xs-12 col-sm-3'><strong>".$locale['uf_fb_connect']."</strong></div>
        <div class='col-xs-12 col-sm-9'><div class='strong'>".$locale['uf_fb_connect_desc']."</div></div>
        </div>\n</div>\n";

    } else {

        require_once(INFUSIONS.'login/user_fields/facebook_connect/facebook_connect.php');

        $fb = new Facebook_Connect();
        $user = fusion_get_userdata();
        $locale = fusion_get_locale();

        $tpl = \PHPFusion\Template::getInstance('facebook_connect_admin');
        $tpl->set_template(__DIR__.'/facebook_connect/templates/connect_admin.html');
        $tpl->set_tag('header_id', 'fbc_header');
        $tpl->set_tag('site_name', fusion_get_settings('sitename'));
        $tpl->set_tag('title', $locale['uf_fb_connect_desc']);
        $tpl->set_tag('header_text', $locale['uf_fb_connect_400']);
        $tpl->set_tag('header_description', str_replace('{SITE_NAME}', fusion_get_settings('sitename'), $locale['uf_fb_connect_404']));
        if (!empty($user['user_fb_connect'])) {
            $tpl->set_tag('header_text', $locale['uf_fb_connect_401']);
        }
        $tpl->set_tag('content', $fb->display_login(
            array(
                'skip_auth'          => true,
                'facebook_button'    => true,
                'display_connection' => true,
                'redirect_link'      => FUSION_REQUEST
            )
        ));

        if (dbcount("(email_address)", DB_LOGIN_EMAILS, "email_user=:uid AND email_type=:t AND email_verified=0", [
            ':uid' => $user['user_id'],
            ':t'   => 'facebook'
        ])) {
            $tpl->set_block('notice', array('text' => nl2br($locale['uf_fb_connect_405'])));
        }

        $email = 'meang.czac@outlook.com';
        $user_id = '16331';
        $code = json_encode(array('email_address' => $email, 'user_id' => $user_id, 'datestamp' => TIME));
        $code = \defender::encrypt_string($code, SECRET_KEY_SALT);
        $link = INFUSIONS.'login/user_fields/facebook_connect/facebook_verify.php?code='.urlencode($code);
        echo "<a href='$link'>$link</a>";

        $user_fields = $tpl->get_output();
    }

}