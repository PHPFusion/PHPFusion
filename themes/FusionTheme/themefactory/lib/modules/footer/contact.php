<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Contact.php
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
namespace ThemeFactory\Lib\Modules\Footer;

class Contact {

    public function __construct() {

        $locale = fusion_get_locale('', LOCALE.LOCALESET."contact.php");
        $settings = fusion_get_settings();
        $input = [
            'mailname'     => '',
            'email'        => '',
            'subject'      => '',
            'message'      => '',
            'captcha_code' => '',
        ];

        if (isset($_POST['sendmessages'])) {

            foreach ($input as $key => $value) {
                if (isset($_POST[$key])) {
                    // Subject needs 'special' treatment
                    if ($key == 'subject') {
                        $input['subject'] = substr(str_replace(["\r", "\n", "@"], "", descript(stripslash(trim($_POST['subject'])))), 0, 128); // most unique in the entire CMS. keep.
                        $input['subject'] = form_sanitizer($input['subject'], $input[$key], $key);
                    } else {
                        $input[$key] = form_sanitizer($_POST[$key], $input[$key], $key);
                    }
                    // Input not posted, fallback to the default
                } else {
                    $input[$key] = form_sanitizer($input[$key], $input[$key], $key);
                }
            }

            if (iGUEST) {
                $_CAPTCHA_IS_VALID = FALSE;
                include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php"; // Dynamics need to develop Captcha. Before that, use method 2.
                if ($_CAPTCHA_IS_VALID == FALSE) {
                    \defender::stop();
                    addNotice('warning', $locale['424']);
                }
            }

            if (\defender::safe()) {
                die('contact form sent');
                require_once INCLUDES."sendmail_include.php";
                $template_result = dbquery("
                SELECT template_key, template_active, template_sender_name, template_sender_email
                FROM ".DB_EMAIL_TEMPLATES."
                WHERE template_key='CONTACT'
                LIMIT 1");
                if (dbrows($template_result)) {
                    $template_data = dbarray($template_result);
                    if ($template_data['template_active'] == "1") {
                        if (!sendemail_template("CONTACT", $input['subject'], $input['message'], "", $template_data['template_sender_name'], "",
                            $template_data['template_sender_email'], $input['mailname'], $input['email'])
                        ) {
                            \defender::stop();
                            addNotice('warning', $locale['425']);
                        }
                    } else {
                        if (!sendemail($settings['siteusername'], $settings['siteemail'], $input['mailname'], $input['email'], $input['subject'],
                            $input['message'])
                        ) {
                            \defender::stop();
                            addNotice('warning', $locale['425']);
                        }
                    }
                } else {
                    if (!sendemail($settings['siteusername'], $settings['siteemail'], $input['mailname'], $input['email'], $input['subject'],
                        $input['message'])
                    ) {
                        \defender::stop();
                        addNotice('warning', $locale['425']);
                    }
                }

                if (\defender::safe()) {
                    addNotice('warning', $locale['425']);
                    redirect(FORM_REQUEST);
                }

            }
        }

        echo "<h4>".$locale['400']."</h4>\n";
        echo "<!--contact_pre_idx-->";
        echo openform('contactform', 'post', FORM_REQUEST);
        echo form_text('mailname', $locale['402'], $input['mailname'], ['required' => TRUE, 'error_text' => $locale['420'], 'max_length' => 64]);
        echo form_text('email', $locale['403'], $input['email'],
            ['required' => TRUE, 'error_text' => $locale['421'], 'type' => 'email', 'max_length' => 64]);
        echo form_text('subject', $locale['404'], $input['subject'], ['required' => TRUE, 'error_text' => $locale['422'], 'max_length' => 64]);
        echo form_textarea('message', $locale['405'], $input['message'], ['required' => TRUE, 'error_text' => $locale['423'], 'max_length' => 128]);
        if (iGUEST) {
            include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
                echo display_captcha([
                'captcha_id' => 'captcha_fusiontheme',
                'input_id'   => 'captcha_code_fusiontheme',
                'image_id'   => 'captcha_image_fusiontheme'
            ]);
            if (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT)) {
                echo form_text('captcha_code', $locale['408'], '', ['required' => TRUE, 'autocomplete_off' => TRUE]);
            }
        }
        echo form_button('sendmessages', $locale['406'], $locale['406'], ['class' => 'btn-primary btn-lg']);
        echo closeform();
        echo "<!--contact_sub_idx-->";
    }
}
