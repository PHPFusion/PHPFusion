<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: themes/templates/global/contact.php
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
/**
 * Contact form
 *
 * @param $info
 *
 * @return string
 */
if (!function_exists("render_contact_form")) {
    function render_contact_form($info) {
        $locale = fusion_get_locale();

        $tpl = \PHPFusion\Template::getInstance('contact');
        $tpl->set_template(__DIR__.'/tpl/contact.html');
        $tpl->set_tag('message', $info['message']);
        $tpl->set_tag('input_mailname', form_text('mailname', $locale['CT_402'], $info['input']['mailname'], ['required' => TRUE, 'error_text' => $locale['CT_420'], 'max_length' => 64]));
        $tpl->set_tag('input_email', form_text('email', $locale['CT_403'], $info['input']['email'], ['required' => TRUE, 'error_text' => $locale['CT_421'], 'type' => 'email', 'max_length' => 64]));
        $tpl->set_tag('input_subject', form_text('subject', $locale['CT_404'], $info['input']['subject'], ['required' => TRUE, 'error_text' => $locale['CT_422'], 'max_length' => 64]));
        $tpl->set_tag('input_message', form_textarea('message', $locale['CT_405'], $info['input']['message'], ['required' => TRUE, 'error_text' => $locale['CT_423'], 'max_length' => 128]));
        if (iGUEST)
            $tpl->set_block('input_captcha', ['captcha' => $info['captcha'], 'captcha_code' => $info['captcha_code']]);
        $tpl->set_tag('input_button', form_button('sendmessage', $locale['CT_406'], $locale['CT_406'], ['class' => 'btn-primary', 'icon' => 'fa fa-send-o']));

        return fusion_get_function('opentable', $locale['CT_400']).$tpl->get_output().fusion_get_function('closetable');
    }
}
