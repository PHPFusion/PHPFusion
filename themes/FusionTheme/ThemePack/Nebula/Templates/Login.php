<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /Nebula/Templates/Login.php
| Author: Hien (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace ThemePack\Nebula\Templates;

use PHPFusion\Panels;
use ThemeFactory\Core;

/**
 * Login Template
 */
class Login {

    public static function login_form($info) {

        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        $aidlink = fusion_get_aidlink();
        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        Core::setParam('body_container', FALSE);
        Core::setParam('copyright', FALSE);

        if (iMEMBER) {
            $msg_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'");
            opentable($userdata['user_name']);
            echo "<div style='text-align:center'><br />\n";
            echo THEME_BULLET." <a href='".BASEDIR."edit_profile.php' class='side'>".$locale['global_120']."</a><br />\n";
            echo THEME_BULLET." <a href='".BASEDIR."messages.php' class='side'>".$locale['global_121']."</a><br />\n";
            echo THEME_BULLET." <a href='".BASEDIR."members.php' class='side'>".$locale['global_122']."</a><br />\n";
            if (iADMIN && (iUSER_RIGHTS != "" || iUSER_RIGHTS != "C")) {
                echo THEME_BULLET." <a href='".ADMIN."index.php".$aidlink."' class='side'>".$locale['global_123']."</a><br />\n";
            }
            echo THEME_BULLET." <a href='".BASEDIR."index.php?logout=yes' class='side'>".$locale['global_124']."</a>\n";
            if ($msg_count) {
                echo "<br /><br />\n";
                echo "<strong><a href='".BASEDIR."messages.php' class='side'>".sprintf($locale['global_125'], $msg_count);
                echo ($msg_count == 1 ? $locale['global_126'] : $locale['global_127'])."</a></strong>\n";
            }
            closetable();
        } else {

            ?>
            <div class="row">
                <div class="col-xs-12 col-sm-5 login-column">
                    <div class="login-panel center-x" style="height:100vh; overflow: hidden;">
                        <div class="center-y">
                            <img src="<?php echo BASEDIR.fusion_get_settings("sitebanner") ?>" alt="<?php echo fusion_get_settings("sitename") ?>">

                            <h2><?php echo fusion_get_settings("sitename") ?></h2>
                        <?php
                        echo $info['open_form'];
                        echo $info['user_name'];
                        echo $info['user_pass'];
                        echo $info['remember_me'];
                        echo $info['login_button'];
                        echo $info['registration_link']."<br/><br/>";
                        echo $info['forgot_password_link']."<br/><br/>";
                        echo $info['close_form'];
                        echo showcopyright();
                        ?>
                        </div>
                    </div>
                </div>
                <div class="hidden-xs col-sm-7 login-bg">

                </div>
            </div>
            <?php
        }
    }

    public static function register_form($info) {

        $locale = fusion_get_locale();

        Panels::getInstance(TRUE)->hide_panel('RIGHT');

        $banner = fusion_get_settings("sitebanner") ? "<img class='m-t-0 m-b-15 m-l-15' src='".BASEDIR.fusion_get_settings("sitebanner")."' alt='".fusion_get_settings("sitename")."'/>" : fusion_get_settings("sitename");
        ?>
        <section id="registerForm" class="login-bg" style="left: 0; top: 0; right: 0; bottom: 0; position: fixed; overflow-y:auto">
            <div class="container">
                <div class="col-xs-12 text-center">
                    <div class="text-center display-block"><?php echo $banner ?></div>
               			<?php $notices = getNotices();
                        if ($notices) {
                             echo renderNotices($notices);
                        }?>
                    <div class="panel panel-default" style="text-align:left;">
                        <div class="panel-body p-20">
                            <h3 class="text-bigger text-uppercase text-dark"><?php echo $locale['u101'] ?></h3>
                            <h4><?php echo fusion_get_settings('sitename') ?></h4>
                            <?php
                            $open = "";
                            $close = "";
                            if (isset($info['section']) && count($info['section']) > 1) {
                                foreach ($info['section'] as $page_section) {
                                    $tab_title['title'][$page_section['id']] = $page_section['name'];
                                    $tab_title['id'][$page_section['id']] = $page_section['id'];
                                    $tab_title['icon'][$page_section['id']] = '';
                                }
                                $open = opentab($tab_title, $_GET['section'], 'user-profile-form', TRUE);
                                $close = closetab();
                            }
                            echo $open;
                            if (empty($info['user_name']) && empty($info['user_field'])) {
                                global $locale;
                                echo "<div class='well text-center'>\n";
                                echo $locale['uf_108'];
                                echo "</div>\n";
                            } else {
                                echo "<!--editprofile_pre_idx-->";
                                echo "<div id='register_form' class='row m-t-20'>\n";
                                echo "<div class='col-xs-12 col-sm-12'>\n";
                                if (!empty($info['openform'])) {
                                    echo $info['openform'];
                                }
                                if (!empty($info['user_name'])) {
                                    echo $info['user_name'];
                                }
                                if (!empty($info['user_email'])) {
                                    echo $info['user_email'];
                                }
                                if (!empty($info['user_hide_email'])) {
                                    echo $info['user_hide_email'];
                                }
                                if (!empty($info['user_avatar'])) {
                                    echo $info['user_avatar'];
                                }
                                if (!empty($info['user_password'])) {
                                    echo $info['user_password'];
                                }
                                if (!empty($info['user_admin_password']) && iADMIN) {
                                    echo $info['user_admin_password'];
                                }

                                if (!empty($info['user_field'])) {
                                    foreach ($info['user_field'] as $field => $fieldData) {
                                        if (!empty($fieldData['title'])) {
                                            echo $fieldData['title'];
                                        }
                                        if (!empty($fieldData['fields']) && is_array($fieldData['fields'])) {
                                            foreach ($fieldData['fields'] as $cField => $cFieldData) {
                                                if (!empty($cFieldData)) {
                                                    echo $cFieldData;
                                                }
                                            }
                                        }
                                    }
                                }

                                if (!empty($info['validate'])) {
                                    echo $info['validate'];
                                }
                                if (!empty($info['terms'])) {
                                    echo $info['terms'];
                                }
                                if (!empty($info['button'])) {
                                    echo $info['button'];
                                }
                                if (!empty($info['closeform'])) {
                                    echo $info['closeform'];
                                }
                                echo "</div>\n</div>\n";
                                echo "<!--editprofile_sub_idx-->";
                            }
                            echo $close;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}

