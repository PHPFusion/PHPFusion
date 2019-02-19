<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: contact_form_panel.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', LOCALE.LOCALESET.'contact.php');
$settings = fusion_get_settings();

$input = [
    'mailname'     => '',
    'email'        => '',
    'subject'      => '',
    'message'      => '',
    'captcha_code' => ''
];

if (isset($_POST['sendmessage'])) {
    foreach ($input as $key => $value) {
        if (isset($_POST[$key])) {
            if ($key == 'subject') {
                $input['subject'] = substr(str_replace(['\r', '\n', '@'], '', descript(stripslash(trim($_POST['subject'])))), 0, 128);
                $input['subject'] = form_sanitizer($input['subject'], $input[$key], $key);
            } else {
                $input[$key] = form_sanitizer($_POST[$key], $input[$key], $key);
            }
        } else {
            $input[$key] = form_sanitizer($input[$key], $input[$key], $key);
        }
    }

    if (!iADMIN && $settings['display_validation']) {
        $_CAPTCHA_IS_VALID = FALSE;

        include INCLUDES.'captchas/'.$settings['captcha'].'/captcha_check.php';
        if ($_CAPTCHA_IS_VALID == FALSE) {
            \defender::stop();
            addNotice('warning', $locale['CT_424']);
        }
    }

    if (\defender::safe()) {
        require_once INCLUDES.'sendmail_include.php';

        $template_result = dbquery("
            SELECT template_key, template_active, template_sender_name, template_sender_email
            FROM ".DB_EMAIL_TEMPLATES."
            WHERE template_key='CONTACT'
            LIMIT 1
        ");

        if (dbrows($template_result)) {
            $template_data = dbarray($template_result);
            if ($template_data['template_active'] == '1') {
                if (!sendemail_template('CONTACT', $input['subject'], $input['message'], '', $template_data['template_sender_name'], '', $template_data['template_sender_email'], $input['mailname'], $input['email'])) {
                    \defender::stop();
                    addNotice('warning', $locale['CT_425']);
                }
            } else {
                if (!sendemail($settings['siteusername'], $settings['siteemail'], $input['mailname'], $input['email'], $input['subject'], $input['message'])) {
                    \defender::stop();
                    addNotice('warning', $locale['CT_425']);
                }
            }
        } else {
            if (!sendemail($settings['siteusername'], $settings['siteemail'], $input['mailname'], $input['email'], $input['subject'], $input['message'])) {
                \defender::stop();
                addNotice('warning', $locale['CT_425']);
            }
        }

        if (\defender::safe()) {
            addNotice('warning', $locale['CT_425']);
            redirect(FUSION_SELF);
        }
    }
}

$captcha = '';
if (!iADMIN && $settings['display_validation']) {
    if (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT)) {
        $captcha = form_text('captcha_code', $locale['CT_408'], '', ['required' => TRUE, 'autocomplete_off' => TRUE]);
    }
}

$message = str_replace("[SITE_EMAIL]", hide_email(fusion_get_settings('siteemail')), $locale['CT_401']);
$message = str_replace("[PM_LINK]", "<a href='messages.php?msg_send=1'>".$locale['global_121']."</a>", $message);

$info = [
    'tablename'    => $locale['CT_400'],
    'prmessages'   => $message,
    'openform'     => openform('contactform', 'post', FUSION_SELF, ['max_tokens' => 1]),
    'mail_name'    => form_text('mailname', $locale['CT_402'], $input['mailname'], ['required' => TRUE, 'error_text' => $locale['CT_420'], 'max_length' => 64]),
    'email'        => form_text('email', $locale['CT_403'], $input['email'], ['required' => TRUE, 'error_text' => $locale['CT_421'], 'type' => 'email', 'max_length' => 64]),
    'subject'      => form_text('subject', $locale['CT_404'], $input['subject'], ['required' => TRUE, 'error_text' => $locale['CT_422'], 'max_length' => 64]),
    'message'      => form_textarea('message', $locale['CT_405'], $input['message'], ['required' => TRUE, 'error_text' => $locale['CT_423'], 'max_length' => 128]),
    'captcha_code' => $captcha,
    'button'       => form_button('sendmessage', $locale['CT_406'], $locale['CT_406'], ['class' => 'btn-primary', 'icon' => 'fa fa-send']),
    'closeform'    => closeform()
];

ob_start();

require_once INFUSIONS.'contact_form_panel/templates/contact_form_panel.php';
render_contact_panel($info);

echo strtr(ob_get_clean(), [
    '{%tablename%}'       => $info['tablename'],
    '{%prmessages%}'      => $info['prmessages'],
    '{%open_form%}'       => $info['openform'],
    '{%mail_name_field%}' => $info['mail_name'],
    '{%email_field%}'     => $info['email'],
    '{%subject_field%}'   => $info['subject'],
    '{%message_field%}'   => $info['message'],
    '{%captcha%}'         => $info['captcha_code'],
    '{%send_button%}'     => $info['button'],
    '{%close_form%}'      => $info['closeform']
]);
