<?php
namespace ThemeFactory\Lib\Modules\Footer;

class Contact {

    public function __construct() {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."contact.php");
        $settings = fusion_get_settings();
        $input = array(
            'mailname' => '',
            'email' => '',
            'subject' => '',
            'message' => '',
            'captcha_code' => '',
        );

        if (isset($_POST['sendmessage'])) {
            foreach ($input as $key => $value) {
                if (isset($_POST[$key])) {
                    // Subject needs 'special' treatment
                    if ($key == 'subject') {
                        $input['subject'] = substr(str_replace(array("\r", "\n", "@"), "", descript(stripslash(trim($_POST['subject'])))), 0,
                                                   128); // most unique in the entire CMS. keep.
                        $input['subject'] = form_sanitizer($input['subject'], $input[$key], $key);
                        // Others don't
                    } else {
                        $input[$key] = form_sanitizer($_POST[$key], $input[$key], $key);
                    }
                    // Input not posted, fallback to the default
                } else {
                    $input[$key] = form_sanitizer($input[$key], $input[$key], $key);
                }
            }

            $_CAPTCHA_IS_VALID = FALSE;
            include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php"; // Dynamics need to develop Captcha. Before that, use method 2.
            if ($_CAPTCHA_IS_VALID == FALSE) {
                \defender::stop();
                addNotice('warning', $locale['424']);
            }
            if (defender::safe()) {
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

                if (defender::safe()) {
                    addNotice('warning', $locale['425']);
                    redirect(FUSION_SELF);
                }

            }
        }

        echo "<h4>".$locale['400']."</h4>\n";
        echo "<!--contact_pre_idx-->";
        echo openform('contactform', 'post', FUSION_SELF, array('max_tokens' => 1));
        echo form_text('mailname', $locale['402'], $input['mailname'], array('required' => 1, 'error_text' => $locale['420'], 'max_length' => 64));
        echo form_text('email', $locale['403'], $input['email'],
                       array('required' => 1, 'error_text' => $locale['421'], 'type' => 'email', 'max_length' => 64));
        echo form_text('subject', $locale['404'], $input['subject'], array('required' => 1, 'error_text' => $locale['422'], 'max_length' => 64));
        echo form_textarea('message', $locale['405'], $input['message'], array('required' => 1, 'error_text' => $locale['423'], 'max_length' => 128));
        if (iGUEST) {
            include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
            if (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT)) {
                echo form_text('captcha_code', $locale['408'], '', array('required' => 1, 'autocomplete_off' => 1));
            }
        }
        echo form_button('sendmessage', $locale['406'], $locale['406'], array('class' => 'btn-primary m-t-10'));
        echo closeform();
        echo "<!--contact_sub_idx-->";
    }

}
