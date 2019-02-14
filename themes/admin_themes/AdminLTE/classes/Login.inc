<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AdminLTE/classes/Login.inc
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
namespace AdminLTE;

class Login {
    public function __construct() {
        $locale = fusion_get_locale('', ALTE_LOCALE);
        $userdata = fusion_get_userdata();

        add_to_jquery('$("#admin_password").focus();');

        $html = '<div class="lockscreen-wrapper">';
            $html .= '<div class="lockscreen-logo">';
                $html .= '<a href="'.BASEDIR.fusion_get_settings('opening_page').'"><b>Admin</b>LTE</a>';
            $html .= '</div>';

            $html .= '<div class="lockscreen-name">'.$userdata['user_name'].'</div>';

            $html .= '<div class="lockscreen-item">';
                $html .= '<div class="lockscreen-image">';
                    $html .= display_avatar($userdata, '70px', '', FALSE, 'img-circle');
                $html .= '</div>';

                if (class_exists('\defender')) {
                    \defender::getInstance()->add_field_session([
                        'input_name'     => 'admin_password',
                        'id'             => 'admin_password',
                        'type'           => 'password',
                        'callback_check' => 'check_admin_pass',
                        'required'       => TRUE,
                        'min_length'     => '',
                        'max_length'     => ''
                    ]);
                }

                $form_action = FUSION_SELF.fusion_get_aidlink() == ADMIN.'index.php'.fusion_get_aidlink().'&amp;pagenum=0' ? FUSION_SELF.fusion_get_aidlink().'&amp;pagenum=0' : FUSION_REQUEST;
                $html .= openform('admin-login-form', 'post', $form_action, ['class' => 'lockscreen-credentials']);
                    $html .= '<div class="input-group">';
                        $html .= '<input type="password" name="admin_password" id="admin_password" class="form-control" placeholder="'.$locale['ALT_007'].'">';

                        $html .= '<div class="input-group-btn">';
                            $html .= '<button type="submit" name="admin_login" class="btn" title="'.$locale['login'].'"><i class="fa fa-arrow-right text-muted"></i></button>';
                        $html .= '</div>';
                    $html .= '</div>';

                    if (class_exists('\defender') && \defender::inputHasError('admin_password')) {
                        $html .= '<div class="label label-danger error-text">'.$locale['global_182'].'</div>';
                    }
                $html .= closeform();
            $html .= '</div>';

            $html .= '<div class="lockscreen-footer text-center">';
                $html .= 'AdminLTE Admin Theme &copy; '.date('Y').'<br/>';
                $html .= $locale['ALT_006'].' <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a> ';
                $html .= $locale['and'].' <a href="https://adminlte.io" target="_blank">Almsaeed Studio</a><br/>';
                $html .= showcopyright();
            $html .= '</div>';
        $html .= '</div>';

        echo $html;
    }
}
