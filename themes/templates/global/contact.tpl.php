<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: contact.tpl.php
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

        opentable($locale['CT_400']);
        //echo '<div class="text-center well">'.$info['message'].'</div>';

        echo form_text('mailname', $locale['CT_402'], $info['input']['mailname'], ['required' => TRUE, 'error_text' => $locale['CT_420'], 'max_length' => 64]);
        echo form_text('email', $locale['CT_403'], $info['input']['email'], ['required' => TRUE, 'error_text' => $locale['CT_421'], 'type' => 'email', 'max_length' => 64]);
        echo form_text('subject', $locale['CT_404'], $info['input']['subject'], ['required' => TRUE, 'error_text' => $locale['CT_422'], 'max_length' => 64]);
        echo form_textarea('message', $locale['CT_405'], $info['input']['message'], ['required' => TRUE, 'error_text' => $locale['CT_423'], 'max_length' => 128]);

        if (iGUEST) {
            echo '<div class="row">
                <div class="col-xs-12 col-sm-6">'.$info['captcha'].'</div>
                <div class="col-xs-12 col-sm-6">'.$info['captcha_code'].'</div>
            </div>';
        }

        echo form_button('sendmessage', $locale['CT_406'], $locale['CT_406'], ['class' => 'btn-primary', 'icon' => 'fa fa-send-o']);
        closetable();
    }
}
