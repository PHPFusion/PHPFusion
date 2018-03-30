<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login/user_fields/facebook_connect/facebook_verify.php
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

require_once(__DIR__.'/../../../../maincore.php');
require_once THEMES.'templates/header.php';

if (isset($_REQUEST['code'])) {
    $locale = fusion_get_locale('', LOGIN_LOCALESET.'user_fb_connect.php');
    $code = \defender::decrypt_string($_REQUEST['code'], SECRET_KEY_SALT);
    $token = json_decode($code, true);
    $tpl = \PHPFusion\Template::getInstance('verify_facebook');
    $tpl->set_template(__DIR__.'/templates/connect_verify.html');
    $tpl->set_tag('title', $locale['uf_fb_connect_verify']);
    $tpl->set_tag('back_title', $locale['uf_fb_connect_406']);
    $tpl->set_tag('back_link', BASEDIR.'index.php');
    if (!empty($token['datestamp']) && !empty($token['user_id']) && !empty($token['email_address'])) {
        if (isnum($token['user_id'])) {
            if (isnum($token['datestamp']) && $token['datestamp'] >= TIME - (3600 * 24 * 3)) {
                $email_address = stripinput($token['email_address']);
                $user_id = $token['user_id'];
                $sql_cond = "email_address=:email AND email_user=:uid AND email_verified=0";
                $sql_param = array(
                    ':email' => $email_address,
                    ':uid'   => $user_id
                );
                if (dbcount("(email_address)", DB_LOGIN_EMAILS, $sql_cond, $sql_param)) {
                    dbquery("UPDATE ".DB_LOGIN_EMAILS." SET email_verified=1 WHERE $sql_cond", $sql_param);
                    $tpl->set_block('success', ['title' => $locale['uf_fb_connect_300'], 'description' => $locale['uf_fb_connect_301']]);
                } else {
                    $tpl->set_block('error', ['title' => $locale['uf_fb_connect_302'], 'description' => $locale['uf_fb_connect_303']]);
                }
            } else {
                $tpl->set_block('error', ['title' => $locale['uf_fb_connect_304'], 'description' => $locale['uf_fb_connect_305']]);
            }
        } else {
            $tpl->set_block('error', ['title' => $locale['uf_fb_connect_306'], 'description' => $locale['uf_fb_connect_307']]);
        }
    } else {
        $tpl->set_block('error', ['title' => $locale['uf_fb_connect_306'], 'description' => $locale['uf_fb_connect_307']]);
    }
    echo $tpl->get_output();
}

require_once THEMES.'templates/footer.php';
