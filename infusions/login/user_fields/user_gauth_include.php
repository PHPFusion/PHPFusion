<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login/user_fields/user_gauth_include.php
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

// Display user field input
if ($profile_method == "input") {

    if (defined('IN_QUANTUM')) {
        $user_fields = "<div class='row m-0'>
        <div class='col-xs-12 col-sm-3'><strong>".$locale['uf_gauth']."</strong></div>
        <div class='col-xs-12 col-sm-9'><div class='alert alert-warning strong'>".$locale['uf_gauth_desc']."</div></div>
        </div>";
    } else {
        require_once INFUSIONS.'login/user_fields/google_auth/google_auth.php';
        $tpl = \PHPFusion\Template::getInstance('gauth');
        $tpl->set_template(__DIR__.'/google_auth/templates/create.html');
        // html text replacement
        $tpl->set_tag('title', $locale['uf_gauth_108']);
        $tpl->set_tag('description', str_replace('{SITE_NAME}', fusion_get_settings('sitename'), $locale['uf_gauth_111']));

        if (isset($_POST['authenticate']) && isset($_POST['enable_2step']) && $_POST['enable_2step'] == 1) {
            $google = new GoogleAuthenticator();
            $gCode = form_sanitizer($_POST['g_code'], '', 'g_code');
            $secret = form_sanitizer($_POST['secret'], '', 'secret');
            $checkResult = $google->verifyCode($secret, $gCode, 2);    // 2 = 2*30sec clock tolerance
            if ($checkResult && \defender::safe()) {
                // successful paired
                $user = [
                    'user_id'    => fusion_get_userdata('user_id'),
                    'user_gauth' => $secret,
                ];
                dbquery_insert(DB_USERS, $user, 'update');
                addNotice('success', $locale['uf_gauth_140']);
                redirect(FUSION_REQUEST);
            } else {
                // unsuccessful. try again.
                addNotice('danger', $locale['uf_gauth_141']);
                redirect(FUSION_REQUEST);
            }
        } else if (isset($_POST['deactivate'])) {
            $google = new GoogleAuthenticator();
            $gCode = form_sanitizer($_POST['g_code'], '', 'g_code');
            $secret = fusion_get_userdata('user_gauth');
            $checkResult = $google->verifyCode($secret, $gCode, 2);    // 2 = 2*30sec clock tolerance
            if ($checkResult && \defender::safe()) {
                // successful paired
                $user = [
                    'user_id'    => fusion_get_userdata('user_id'),
                    'user_gauth' => '',
                ];
                dbquery_insert(DB_USERS, $user, 'update');
                addNotice('success', $locale['uf_gauth_142']);
                redirect(FUSION_REQUEST);
            } else {
                // unsuccessful. try again.
                addNotice('danger', $locale['uf_gauth_143']);
                redirect(FUSION_REQUEST);
            }
        }
        if (!empty($field_value)) {
            // Reset options
            $tpl->set_block('current_block', [
                'title'       => $locale['uf_gauth_112'],
                'description' => $locale['uf_gauth_113'],
                'detail'      => $locale['uf_gauth_114'],
                'text_input'  => form_text('g_code', $locale['uf_gauth_103'], '', ['type' => 'password', 'required' => TRUE, 'placeholder' => $locale['uf_gauth_105']]),
                'button'      => form_button('deactivate', $locale['uf_gauth_107'], $locale['uf_gauth_107'], ['class' => 'btn-primary'])
            ]);
            $user_fields = $tpl->get_output();
        } else {
            // do the tpl here
            $google = new GoogleAuthenticator();
            $account_name = fusion_get_userdata('user_email');
            $site_name = fusion_get_settings('sitename');
            $secret = $google->createSecret();
            $qrCodeUrl = $google->getQRCodeGoogleUrl($account_name, $secret, $site_name);

            $tpl->set_block('new_block', [
                'title'        => $locale['uf_gauth_115'],
                'subtitle'     => $locale['uf_gauth_116'],
                'i_title'      => $locale['uf_gauth_150'],
                'i_subtitle'   => $locale['uf_gauth_151'],
                'd_name'       => $locale['uf_gauth_152'],
                'd_key'        => $locale['uf_gauth_153'],
                'i_detail'     => $locale['uf_gauth_154'],
                'i_text'       => $locale['uf_gauth_155'],
                'm_title'      => $locale['uf_gauth_156'],
                'm_text_1'     => $locale['uf_gauth_157'],
                'm_text_2'     => $locale['uf_gauth_158'],
                'm_text_3'     => $locale['uf_gauth_159'],
                'm_text_4'     => $locale['uf_gauth_160'],
                'm_text_5'     => $locale['uf_gauth_161'],
                'm_text_6'     => $locale['uf_gauth_162'],
                'radio'        => form_checkbox('enable_2step', $locale['uf_gauth_108'], '', [
                        'options' => [
                            0 => $locale['uf_gauth_109'],
                            1 => $locale['uf_gauth_110']
                        ],
                        'type'    => 'radio']).form_hidden('secret', '', $secret),
                'account_name' => $account_name,
                'key'          => $secret,
                'image_src'    => $qrCodeUrl,
                'text_input'   => form_text('g_code', $locale['uf_gauth_103'], '', ['type' => 'password', 'required' => TRUE, 'placeholder' => $locale['uf_gauth_105']]),
                'button'       => form_button('authenticate', $locale['uf_gauth_106'], $locale['uf_gauth_106'], ['class' => 'btn-primary'])
            ]);

            add_to_jquery("
            $('input[name=\"enable_2step\"]').bind('change', function(e) {
                if ($(this).val() == 1) {
                    $('#gauth_setup_form').slideDown();

                } else {
                    $('#gauth_setup_form').slideUp();

                }
            });
            ");
            $user_fields = $tpl->get_output();
        }
        add_to_jquery("$('button#update_profile').prop('disabled', true).hide();");
    }
    // Display in profile
} else if ($profile_method == "display") {
    // nothing to display
}
