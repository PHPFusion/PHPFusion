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
                addNotice('success', "You have successfully activated the Two Step Authentication login on your user account.");
                redirect(FUSION_REQUEST);
            } else {
                // unsuccessful. try again.
                addNotice('danger', "Your code failed to be authenticated. Please try again.");
                redirect(FUSION_REQUEST);
            }
        } elseif (isset($_POST['deactivate'])) {
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
                addNotice('success', "You have successfully deactivated the Two Step Authentication login on your user account.");
                redirect(FUSION_REQUEST);
            } else {
                // unsuccessful. try again.
                addNotice('danger', "Your code failed to be authenticated. Please try again.");
                redirect(FUSION_REQUEST);
            }
        }
        if (!empty($field_value)) {
            // Reset options
            $tpl->set_block('current_block', [
                'text_input' => form_text('g_code', 'Authentication Code', '', ['type' => 'password', 'required' => true, 'placeholder' => 'Enter Google Authentication Code']),
                'button'     => form_button('deactivate', 'Deactivate', 'deactivate', ['class' => 'btn-primary btn-bordered'])
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
                'radio'        => form_checkbox('enable_2step', 'Enable Two-Step Authentication', '', [
                        'options' => [
                            0 => 'No, I do not want to use 2-Step Authentication',
                            1 => 'Yes, I wish to enroll 2-Step Authentication'
                        ],
                        'type'    => 'radio']).form_hidden('secret', '', $secret),
                'account_name' => $account_name,
                'key'          => $secret,
                'image_src'    => $qrCodeUrl,
                'text_input'   => form_text('g_code', 'Authentication Code', '', ['type' => 'password', 'required' => true, 'placeholder' => 'Enter Google Authentication Code']),
                'button'       => form_button('authenticate', 'Verify Code', 'Verify Code', ['class' => 'btn-primary btn-bordered'])
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