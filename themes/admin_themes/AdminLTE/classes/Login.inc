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

        echo '<div class="lockscreen-wrapper">';
            echo '<div class="lockscreen-logo">';
                echo '<a href="'.BASEDIR.fusion_get_settings('opening_page').'"><b>Admin</b>LTE</a>';
            echo '</div>';

            echo '<div class="lockscreen-name">'.$userdata['user_name'].'</div>';

            echo '<div class="lockscreen-item">';
                echo '<div class="lockscreen-image">';
                    echo  display_avatar($userdata, '70px', '', FALSE, 'img-circle');
                echo '</div>';

                \defender::getInstance()->add_field_session([
                    'input_name'     => 'admin_password',
                    'id'             => 'admin_password',
                    'type'           => 'password',
                    'callback_check' => 'check_admin_pass',
                    'required'       => TRUE,
                    'min_length'     => '',
                    'max_length'     => ''
                ]);

                $form_action = FUSION_SELF.fusion_get_aidlink() == ADMIN.'index.php'.fusion_get_aidlink().'&amp;pagenum=0' ? FUSION_SELF.fusion_get_aidlink().'&amp;pagenum=0' : FUSION_REQUEST;
                echo openform('admin-login-form', 'post', $form_action, ['class' => 'lockscreen-credentials']);
                    echo '<div class="input-group">';
                        echo '<input type="password" name="admin_password" id="admin_password" class="form-control" placeholder="'.$locale['ALT_007'].'">';

                        echo '<div class="input-group-btn">';
                            echo '<button type="submit" name="admin_login" class="btn" title="'.$locale['login'].'"><i class="fa fa-arrow-right text-muted"></i></button>';
                        echo '</div>';
                    echo '</div>';

                    if (\defender::inputHasError('admin_password')) {
                        echo '<div class="label label-danger error-text">'.$locale['global_182'].'</div>';
                    }
                echo closeform();
            echo '</div>';

            echo '<div class="lockscreen-footer text-center">';
                echo 'AdminLTE Admin Theme &copy; '.date('Y').'<br/>';
                echo $locale['ALT_006'].' <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a> ';
                echo $locale['and'].' <a href="https://adminlte.io" target="_blank">Almsaeed Studio</a><br/>';
                echo showcopyright();
            echo '</div>';
        echo '</div>';
    }
}
