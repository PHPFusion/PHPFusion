<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/classes/Login.inc
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
namespace Material;

class Login {
    public function __construct() {
        $locale = fusion_get_locale('', MDT_LOCALE);
        $userdata = fusion_get_userdata();

        add_to_css('body {background-color: #2c3e50 !important;}');
        add_to_jquery('$("#admin_password").focus();');

        $html = '<div class="login-container">';
            $html .= renderNotices(getNotices());

            $html .= '<div class="logo">';
                $html .= '<img src="'.IMAGES.'php-fusion-logo.png" class="pf-logo img-responsive" alt="PHP-Fusion"/>';
                $html .= '<h1><strong>'.$locale['280'].'</strong></h1>';
            $html .= '</div>';

            $html .= '<div class="login-box">';
                $html .= '<div class="pull-right text-smaller">'.$locale['version'].fusion_get_settings('version').'</div>';

                $html .= '<div class="clearfix m-b-20">';
                    $html .= '<div class="pull-left m-r-10">';
                        $html .= display_avatar($userdata, '90px', '', FALSE, 'avatar');
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

            $html .= '<div class="copyright clearfix m-t-10 text-left">';
                $html .= 'Material Admin Theme &copy; '.date('Y').' '.$locale['material_013'].' <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a>.<br/>';
                $html .= showcopyright();
            $html .= '</div>';
        $html .= '</div>';

        echo $html;
    }
}
