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

/**
 * Class Login
 * @package Material
 */
class Login extends Components {
    public function __construct() {
        $locale   = fusion_get_locale();
        $userdata = fusion_get_userdata();

        add_to_head('<style type="text/css">body{background: #2c3e50!important;}</style>');
        add_to_jquery('$("#admin_password").focus();');

        echo '<div class="login-container">';
            echo renderNotices(getNotices());

            echo '<div class="logo">';
                echo '<img src="'.IMAGES.'php-fusion-logo.png" class="pf-logo img-responsive" alt="PHP-Fusion"/>';
                echo '<h1><strong>'.$locale['280'].'</strong></h1>';
            echo '</div>';

            echo '<div class="login-box">';
                echo '<div class="pull-right text-smaller">'.$locale['version'].fusion_get_settings('version').'</div>';

                echo '<div class="clearfix m-b-20">';
                    echo '<div class="pull-left m-r-10">';
                        echo  display_avatar($userdata, '90px', '', FALSE, 'avatar');
                    echo '</div>';
                    echo '<div class="text-left">';
                        echo '<h3><strong>'.$locale['welcome'].', '.$userdata['user_name'].'</strong></h3>';
                        echo '<p>'.getuserlevel($userdata['user_level']).'</p>';
                    echo '</div>';
                echo '</div>';

                $form_action = FUSION_SELF.fusion_get_aidlink() == ADMIN.'index.php'.fusion_get_aidlink() ? FUSION_SELF.fusion_get_aidlink().'&amp;pagenum=0' : FUSION_REQUEST;
                echo openform('admin-login-form', 'post', $form_action);
                    echo form_text('admin_password', '', '', array('type' => 'password', 'callback_check' => 'check_admin_pass', 'placeholder' => $locale['281'], 'error_text' => $locale['global_182'], 'autocomplete_off' => TRUE, 'required' => TRUE));
                    echo form_button('admin_login', $locale['login'], $locale['login'], array('class' => 'btn-primary btn-block'));
                echo closeform();
            echo '</div>';

            echo '<div class="copyright clearfix m-t-10 text-left">';
                echo 'Material Admin Theme &copy; '.date('Y').' '.self::SetLocale('013').' <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a>.<br/>';
                echo showcopyright();
            echo '</div>';
        echo'</div>';
    }
}
