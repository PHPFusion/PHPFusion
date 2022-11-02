<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: contact.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/maincore.php';
require_once THEMES.'templates/header.php';
require_once THEMES."templates/global/contact.tpl.php";

$settings = fusion_get_settings();
$locale = fusion_get_locale('', LOCALE.LOCALESET.'contact.php');
add_to_title($locale['CT_400']);

$input = [
    'mailname'     => '',
    'email'        => '',
    'subject'      => '',
    'message'      => '',
    'captcha_code' => '',
];

if (isset($_POST['sendmessage'])) {
    foreach ($input as $key => $value) {
        if (isset($_POST[$key])) {
            // Subject needs 'special' treatment
            if ($key == 'subject') {
                $input['subject'] = substr(str_replace(["\r", "\n", "@"], "", descript(stripslashes(trim($_POST['subject'])))), 0, 128);
                $input['subject'] = form_sanitizer($input['subject'], $input[$key], $key);
                // Others don't
            } else {
                $input[$key] = form_sanitizer($_POST[$key], $value, $key);
            }
            // Input not posted, fallback to the default
        } else {
            $input[$key] = form_sanitizer($value, $value, $key);
        }
    }

    if (!iMEMBER && $settings['display_validation']) {
        $_CAPTCHA_IS_VALID = FALSE;
        include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php"; // Dynamics need to develop Captcha. Before that, use method 2.
        if (!$_CAPTCHA_IS_VALID) {
            fusion_stop();
            addnotice('warning', $locale['CT_424']);
        }
    }

    if (fusion_safe()) {
        require_once INCLUDES."sendmail_include.php";

        $template_result = dbquery("SELECT template_key, template_active, template_sender_name, template_sender_email
            FROM ".DB_EMAIL_TEMPLATES."
            WHERE template_key='CONTACT'
            LIMIT 1
        ");

        if (dbrows($template_result)) {

            $template_data = dbarray($template_result);
            if ($template_data['template_active'] == "1") {
                if (!sendemail_template("CONTACT", $input['subject'], $input['message'], "",
                    $template_data['template_sender_name'], "", $template_data['template_sender_email'],
                    $input['mailname'], $input['email'])) {
                    fusion_stop();
                    addnotice('danger', $locale['CT_425']);
                }
            } else {
                if (!sendemail($settings['siteusername'], $settings['siteemail'], $input['mailname'], $input['email'], $input['subject'], $input['message'])) {
                    fusion_stop();
                    addnotice('danger', $locale['CT_425']);
                }
            }

        } else {
            if (!sendemail($settings['siteusername'], $settings['siteemail'], $input['mailname'], $input['email'], $input['subject'], $input['message'])) {
                fusion_stop();
                addnotice('danger', $locale['CT_425']);
            }
        }

        if (fusion_safe()) {
            addnotice('success', $locale['CT_440']);
            redirect(BASEDIR.'contact.php');
        }
    }
}

$site_email = hide_email(fusion_get_settings('siteemail'));
$info['message'] = str_replace(
    ["[PM_LINK]", "[SITE_EMAIL]"],
    ["<a href='messages.php?msg_send=1'>".$locale['global_121']."</a>", $site_email],
    $locale['CT_401']
);
$info['input'] = $input;

$info['captcha_code'] = '';

if (iGUEST) {
    include INCLUDES.'captchas/'.$settings['captcha'].'/captcha_display.php';
    $captcha_settings = [
        'captcha_id' => 'captcha_contact',
        'input_id'   => 'captcha_code_contact',
        'image_id'   => 'captcha_image_contact'
    ];

    $info['captcha'] = display_captcha($captcha_settings);
    if (!isset($_CAPTCHA_HIDE_INPUT) || (!$_CAPTCHA_HIDE_INPUT)) {
        $info['captcha_code'] = form_text('captcha_code', $locale['CT_408'], '', [
            'required'         => TRUE,
            'autocomplete_off' => TRUE,
            'input_id'         => 'captcha_code_contact'
        ]);
    }
}

echo openform('contactform', 'post');
render_contact_form($info);
echo closeform();

require_once THEMES.'templates/footer.php';
