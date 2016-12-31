<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/classes/Main.inc
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

use PHPFusion\Admins;

class Main extends Components {
    public static function AddTo() {
        add_to_footer('<script type="text/javascript" src="'.INCLUDES.'jquery/jquery.cookie.js"></script>');
        add_to_head('<script type="text/javascript" src="'.MATERIAL.'assets/js/scripts.min.js"></script>');
        add_to_head('<link rel="stylesheet" href="'.MATERIAL.'assets/scrollbar/jquery.mCustomScrollbar.min.css"/>');
        add_to_footer('<script type="text/javascript" src="'.MATERIAL.'assets/scrollbar/jquery.mCustomScrollbar.min.js"></script>');
        add_to_footer('<script type="text/javascript">$(".sidebar, .messages-box").mCustomScrollbar({theme: "minimal-dark", axis: "y", scrollInertia: 100, mouseWheel: {enable: !0, axis: "y", preventDefault: !0}});</script>');
        add_to_jquery('$(".sidebar-sm .admin-submenu, .sidebar-sm .search-box").mCustomScrollbar({theme: "minimal-dark", axis: "y", scrollInertia: 100, mouseWheel: {enable: !0, axis: "y", preventDefault: !0}});');
    }

    public static function Login() {
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
                echo 'Material Admin Theme &copy; '.date("Y").' Created by <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a><br/>';
                echo showcopyright();
            echo '</div>';
        echo'</div>';
    }

    public static function AdminPanel() {
        $sections = Admins::getInstance()->getAdminSections();

        echo '<main class="clearfix">';
            self::Sidebar();
            self::TopMenu();

            echo '<div class="content">';

                echo '<ul class="nav nav-tabs '.(self::IsMobile() ? '' : 'nav-justified ').'hidden-lg" style="margin-bottom: 20px;">';
                    if (!empty($sections)) {
                        $i = 0;
                        foreach ($sections as $section_name) {
                            $active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && Admins::getInstance()->_isActive() == $i) ? ' class="active"' : '';
                            echo '<li'.$active.'><a href="'.ADMIN.'index.php'.fusion_get_aidlink().'&amp;pagenum='.$i.'">'.(self::IsMobile() ? Admins::getInstance()->get_admin_section_icons($i) : $section_name).'</a></li>';
                            $i++;
                        }
                    }
                echo '</ul>';

                echo '<div class="hidden-xs">';
                    echo render_breadcrumbs();
                echo '</div>';

                echo renderNotices(getNotices());
                echo CONTENT;

                echo '<footer class="copyright">';
                    if (fusion_get_settings("rendertime_enabled")) {
                        echo showrendertime().showMemoryUsage().'<br />';
                    }

                    echo 'Material Admin Theme &copy; '.date("Y").' Created by <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a> | '.str_replace('<br />', ' | ', showcopyright());
                echo '</footer>';

                $errors = showFooterErrors();
                if ($errors) {
                    echo '<div class="errors fixed hidden-xs hidden-sm hidden-md">'.$errors.'</div>';
                }
            echo '</div>';

            if (self::IsMobile()) {
                // Mobile
            } else {
                // PC
                self::MessagesBox();
                self::ThemeSettings();
            }

        echo '</main>';

        echo '<div id="backtotop"><div class="btn btn-primary"><i class="fa fa-chevron-up"></i></div></div>';
        echo '<div class="overlay"></div>';
    }

    public static function OpenSide($title = FALSE, $class = NULL) {
        echo '<div class="panel panel-default openside '.$class.'">';
        echo $title ? '<div class="panel-heading">'.$title.'</div>' : '';
        echo '<div class="panel-body">';
    }

    public static function CloseSide($title = FALSE) {
        echo '</div>';
        echo $title ? '<div class="panel-footer">'.$title.'</div>' : '';
        echo '</div>';
    }

    public static function OpenTable($title = FALSE, $class = NULL) {
        echo '<div class="panel opentable '.$class.'">';
        echo $title ? '<header><h3>'.$title.'</h3></header>' : '';
        echo '<div class="panel-body">';
    }

    public static function CloseTable() {
        echo '</div>';
        echo '</div>';
    }
}
