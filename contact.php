<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: contact.php
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
require_once "maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."contact.php";
add_to_title($locale['global_200'].$locale['contact_400']);
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
        $defender->stop();
        addNotice('warning', $locale['contact_424']);
    }
    if (!defined('FUSION_NULL')) {
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
                    $defender->stop();
                    addNotice('warning', $locale['contact_425']);
                }
            } else {
                if (!sendemail($settings['siteusername'], $settings['siteemail'], $input['mailname'], $input['email'], $input['subject'],
                               $input['message'])
                ) {
                    $defender->stop();
                    addNotice('warning', $locale['contact_425']);
                }
            }
        } else {
            if (!sendemail($settings['siteusername'], $settings['siteemail'], $input['mailname'], $input['email'], $input['subject'],
                           $input['message'])
            ) {
                $defender->stop();
                addNotice('warning', $locale['contact_425']);
            }
        }

        if (defender::safe()) {
            addNotice('warning', $locale['contact_425']);
            redirect(FUSION_SELF);
        }

    }
}
opentable($locale['contact_400']);
$message = str_replace("[SITE_EMAIL]", hide_email(fusion_get_settings('siteemail')), $locale['contact_401']);
$message = str_replace("[PM_LINK]", "<a href='messages.php?msg_send=1'>".$locale['global_121']."</a>", $message);
echo $message."<br /><br />\n";
echo "<!--contact_pre_idx-->";
echo openform('contactform', 'post', FUSION_SELF, array('max_tokens' => 1));
echo "<div class='panel panel-default tbl-border'>\n";
echo "<div class='panel-body'>\n";
echo form_text('mailname', $locale['contact_402'], $input['mailname'], array('required' => 1, 'error_text' => $locale['contact_420'], 'max_length' => 64));
echo form_text('email', $locale['contact_403'], $input['email'],
               array('required' => 1, 'error_text' => $locale['contact_421'], 'type' => 'email', 'max_length' => 64));
echo form_text('subject', $locale['contact_404'], $input['subject'], array('required' => 1, 'error_text' => $locale['contact_422'], 'max_length' => 64));
echo form_textarea('message', $locale['contact_405'], $input['message'], array('required' => 1, 'error_text' => $locale['contact_423'], 'max_length' => 128));
echo "<div class='panel panel-default tbl-border'>\n";
echo "<div class='panel-body clearfix'>\n";
echo "<div class='row m-0'>\n<div class='col-xs-12 col-sm-12 col-md-6 col-lg-6 p-b-20'>\n";
include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-6 col-lg-6'>\n";
if (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT)) {
    echo form_text('captcha_code', $locale['contact_408'], '', array('required' => 1, 'autocomplete_off' => 1));
}
echo "</div>\n</div>\n";
echo "</div>\n</div>\n";
echo form_button('sendmessage', $locale['contact_406'], $locale['contact_406'], array('class' => 'btn-primary m-t-10'));
echo "</div>\n</div>\n";
echo closeform();
echo "<!--contact_sub_idx-->";
closetable();
require_once THEMES."templates/footer.php";
