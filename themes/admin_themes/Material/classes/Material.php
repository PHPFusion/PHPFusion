<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/classes/Material.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

use PHPFusion\Admin;

class Material extends Components {
    public static function AddTo() {
        add_to_head('<script type="text/javascript" src="'.MATERIAL.'assets/js/scripts.min.js"></script>');
        add_to_head('<link rel="stylesheet" href="'.MATERIAL.'assets/scrollbar/jquery.mCustomScrollbar.css"/>');
        add_to_footer('<script type="text/javascript" src="'.MATERIAL.'assets/scrollbar/jquery.mCustomScrollbar.min.js"></script>');
        add_to_footer('<script type="text/javascript">$(".sidebar, .messages-box").mCustomScrollbar({theme: "minimal-dark", axis: "y", scrollInertia: 100, mouseWheel: {enable: !0, axis: "y", preventDefault: !0}});</script>');
        add_to_jquery('$(".sidebar-sm .admin-submenu, .sidebar-sm .search-box").mCustomScrollbar({theme: "minimal-dark", axis: "y", scrollInertia: 100, mouseWheel: {enable: !0, axis: "y", preventDefault: !0}});');
    }

    public static function Login() {
        $locale   = fusion_get_locale();
        $userdata = fusion_get_userdata();
        $aidlink  = fusion_get_aidlink();

        add_to_head('<style type="text/css">body{background: #2c3e50!important;}</style>');
        add_to_jquery('$("#admin_password").focus();');

        echo '<div class="login-container">';
            echo renderNotices(getNotices());

            echo '<div class="logo">';
                echo '<img src="'.IMAGES.'php-fusion-logo.png" class="pf-logo" alt="PHP-Fusion"/>';
                echo '<h1><strong>'.$locale['280'].'</strong></h1>';
            echo '</div>';

            echo '<div class="login-box">';
                echo '<div class="pull-right text-smaller">'.$locale['version'].fusion_get_settings('version').'</div>';

                echo '<div class="clearfix m-b-20">';
                    echo '<div class="pull-left">';
                        echo  display_avatar($userdata, '90px', '', FALSE, 'avatar');
                    echo '</div>';
                    echo '<div class="text-left">';
                        echo "<h3><strong>".$locale['welcome'].", ".$userdata['user_name']."</strong></h3>";
                        echo '<p>'.getuserlevel($userdata['user_level']).'</p>';
                    echo '</div>';
                echo '</div>';

                echo openform('admin-login-form', 'post', ADMIN."index.php".$aidlink."&amp;pagenum=0");
                    echo form_text('admin_password', '', '', array('type' => 'password', 'callback_check' => 'check_admin_pass', 'placeholder' => $locale['281'], 'error_text' => $locale['global_182'], 'autocomplete_off' => TRUE, 'required' => TRUE));
                    echo form_button('admin_login', $locale['login'], $locale['login'], array('class' => 'btn-primary btn-block'));
                echo closeform();
            echo '</div>';

            echo '<div class="copyright clearfix m-t-10 text-left">';
                echo 'Material Admin Theme &copy; '.date("Y").' created by <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a><br/>';
                echo showcopyright();
            echo '</div>';
        echo'</div>';
    }

    public static function AdminPanel() {
        $admin     = new Admin();
        $sections  = $admin->getAdminSections();
        $aidlink   = fusion_get_aidlink();

        echo '<main class="clearfix">';
            self::Sidebar();
            self::TopMenu();

            echo '<div class="content">';

                echo '<ul class="nav nav-tabs '.(self::IsMobile() ? '' : 'nav-justified ').'hidden-lg" style="margin-bottom: 20px;">';
                    if (!empty($sections)) {
                        $i = 0;
                        foreach ($sections as $section_name) {
                            $active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && $admin->_isActive() == $i) ? ' class="active"' : '';
                            echo '<li'.$active.'><a href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum='.$i.'">'.(self::IsMobile() ? $admin->get_admin_section_icons($i) : $section_name).'</a></li>';
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

                    echo 'Material Admin Theme &copy; '.date("Y").' created by <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a> | '.str_replace('<br />', ' | ', showcopyright());
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
