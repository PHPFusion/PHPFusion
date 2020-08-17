<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: Ares/classes/Login.inc
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
namespace Ares;

class Login {
    public function __construct() {
        $locale = fusion_get_locale('', ARS_LOCALE);
        $userdata = fusion_get_userdata();

        add_to_jquery('$("#admin_password").focus();');

        $html = '<div class="login-container">';
            $html .= '<div class="logo">';
                $html .= '<img style="margin-bottom: 30px;" src="'.IMAGES.'php-fusion-logo.png" class="pf-logo img-responsive" alt="PHP-Fusion"/>';
            $html .= '</div>';

            $html .= '<div class="login-box">';
                $html .= '<div class="pull-right text-smaller">'.$locale['version'].fusion_get_settings('version').'</div>';

                $html .= '<div class="clearfix m-b-20">';
                    $html .= '<div class="pull-left m-r-10">';
                        $html .= display_avatar($userdata, '90px', '', FALSE, 'img-circle');
                    $html .= '</div>';
                    $html .= '<div class="text-left">';
                        $html .= '<h3><strong>'.$locale['welcome'].', '.$userdata['user_name'].'</strong></h3>';
                        $html .= '<p>'.getuserlevel($userdata['user_level']).'</p>';
                    $html .= '</div>';
                $html .= '</div>';

                $form_action = FUSION_SELF.fusion_get_aidlink() == ADMIN.'index.php'.fusion_get_aidlink() ? FUSION_SELF.fusion_get_aidlink().'&amp;pagenum=0' : FUSION_REQUEST;
                $html .= openform('admin-login-form', 'post', $form_action);
                    $html .= form_text('admin_password', '', '', ['type' => 'password', 'callback_check' => 'check_admin_pass', 'placeholder' => $locale['281'], 'error_text' => $locale['global_182'], 'autocomplete_off' => TRUE, 'required' => TRUE]);
                    $html .= form_button('admin_login', $locale['login'], $locale['login'], ['class' => 'btn-primary btn-block']);
                $html .= closeform();
            $html .= '</div>';
        $html .= '</div>';

        echo $html;
    }
}
