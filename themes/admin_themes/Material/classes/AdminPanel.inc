<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/classes/AdminPanel.inc
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

use \PHPFusion\Admins;
use \PHPFusion\OutputHandler;

/**
 * Class AdminPanel
 * @package Material
 */
class AdminPanel extends Components {
    public function __construct() {
        OutputHandler::addToHead('<script type="text/javascript" src="'.MATERIAL.'assets/js/scripts.min.js"></script>');
        OutputHandler::addToHead('<link rel="stylesheet" href="'.MATERIAL.'assets/scrollbar/jquery.mCustomScrollbar.min.css"/>');
        OutputHandler::addToFooter('<script type="text/javascript" src="'.INCLUDES.'jquery/jquery.cookie.js"></script>');
        OutputHandler::addToFooter('<script type="text/javascript" src="'.MATERIAL.'assets/scrollbar/jquery.mCustomScrollbar.min.js"></script>');
        OutputHandler::addToFooter('<script type="text/javascript">$(".sidebar, .messages-box").mCustomScrollbar({theme: "minimal-dark", axis: "y", scrollInertia: 100, mouseWheel: {enable: !0, axis: "y", preventDefault: !0}});</script>');
        OutputHandler::addToJquery('$(".sidebar-sm .admin-submenu, .sidebar-sm .search-box").mCustomScrollbar({theme: "minimal-dark", axis: "y", scrollInertia: 100, mouseWheel: {enable: !0, axis: "y", preventDefault: !0}});');

        $sections = Admins::getInstance()->getAdminSections();

        echo '<main class="clearfix">';
            self::TopMenu();
            self::Sidebar();

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
                    if (fusion_get_settings('rendertime_enabled')) {
                        echo showrendertime().showMemoryUsage().'<br />';
                    }

                    echo 'Material Admin Theme &copy; '.date('Y').' '.self::SetLocale('013').' <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a> | '.str_replace('<br />', ' | ', showcopyright());
                echo '</footer>';

                $errors = showFooterErrors();
                if ($errors) {
                    echo '<div class="errors fixed">'.$errors.'</div>';
                }
            echo '</div>';

            if (!self::IsMobile()) {
                self::MessagesBox();
                self::ThemeSettings();
            }
        echo '</main>';

        echo '<div id="backtotop"><div class="btn btn-primary"><i class="fa fa-chevron-up"></i></div></div>';
        echo '<div class="overlay"></div>';
    }

    private static function TopMenu() {
        $sections  = Admins::getInstance()->getAdminSections();
        $locale    = fusion_get_locale();
        $aidlink   = fusion_get_aidlink();
        $userdata  = fusion_get_userdata();
        $languages = fusion_get_enabled_languages();
        $messages  = self::Messages();
        $messages  = !empty($messages) ? '<span class="label label-danger messages">'.count($messages).'</span>' : '';

        echo '<div class="top-menu navbar fixed">';
            echo '<div class="toggleicon" data-action="togglemenu"><span></span></div>';
            echo '<div class="brand"><img src="'.IMAGES.'php-fusion-icon.png" alt="PHP Fusion 9"/> PHP Fusion 9</div>';
            echo '<div class="pull-right hidden-sm hidden-md hidden-lg home-xs"><a title="'.fusion_get_settings('sitename').'" href="'.BASEDIR.'index.php"><i class="fa fa-home"></i></a></div>';

            echo '<ul class="nav navbar-nav navbar-left hidden-xs hidden-sm hidden-md">';
                if (!empty($sections)) {
                    $i = 0;

                    foreach ($sections as $section_name) {
                        $active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && Admins::getInstance()->_isActive() == $i) ? ' class="active"' : '';
                        echo '<li'.$active.'><a href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum='.$i.'" data-toggle="tooltip" data-placement="bottom" title="'.$section_name.'">'.Admins::getInstance()->get_admin_section_icons($i).'</a></li>';
                        $i++;
                    }
                }

            echo '</ul>';

            echo '<ul class="nav navbar-nav navbar-right hidden-xs">';
                if (count($languages) > 1) {
                    echo '<li class="dropdown languages-switcher">';
                        echo '<a class="dropdown-toggle pointer" data-toggle="dropdown" title="'.$locale['282'].'"><i class="fa fa-globe"></i><img class="current" src="'.BASEDIR.'locale/'.LANGUAGE.'/'.LANGUAGE.'.png" alt="'.translate_lang_names(LANGUAGE).'"/><span class="caret"></span></a>';
                        echo '<ul class="dropdown-menu">';
                            foreach ($languages as $language_folder => $language_name) {
                                echo '<li><a class="display-block" href="'.clean_request('lang='.$language_folder, ['lang'], FALSE).'"><img class="m-r-5" src="'.BASEDIR.'locale/'.$language_folder.'/'.$language_folder.'-s.png" alt="'.$language_folder.'"/> '.$language_name.'</a></li>';
                            }
                        echo '</ul>';
                    echo '</li>';
                }

                echo '<li class="dropdown user-s">';
                    echo '<a href="#" class="dropdown-toggle pointer" data-toggle="dropdown">'.display_avatar($userdata, '30px', '', FALSE, 'avatar').' '.$locale['logged'].' <strong>'.$userdata['user_name'].'</strong><span class="caret"></span></a>';
                    echo '<ul class="dropdown-menu" role="menu">';
                        echo '<li><a href="'.BASEDIR.'edit_profile.php"><i class="fa fa-pencil fa-fw"></i> '.$locale['UM080'].'</a></li>';
                        echo '<li><a href="'.BASEDIR.'profile.php?lookup='.$userdata['user_id'].'"><i class="fa fa-eye fa-fw"></i> '.$locale['view'].' '.$locale['profile'].'</a></li>';
                        echo '<li class="divider"></li>';
                        echo '<li><a href="'.FUSION_REQUEST.'&amp;logout"><i class="fa fa-sign-out fa-fw"></i> '.$locale['admin-logout'].'</a></li>';
                        echo '<li><a href="'.BASEDIR.'index.php?logout=yes"><i class="fa fa-sign-out fa-fw"></i> <span class="text-danger">'.$locale['logout'].'</span></a></li>';
                    echo '</ul>';
                echo '</li>';

                echo '<li><a title="'.$locale['settings'].'" href="'.ADMIN.'settings_main.php'.$aidlink.'"><i class="fa fa-cog"></i></a></li>';

                if (self::IsMobile()) {
                    echo '<li><a title="'.$locale['message'].'" href="'.BASEDIR.'messages.php"><i class="fa fa-envelope-o"></i>'.$messages.'</a></li>';
                } else {
                    echo '<li><a title="'.$locale['message'].'" href="#" data-action="messages"><i class="fa fa-envelope-o"></i>'.$messages.'</a></li>';
                }

                echo '<li><a title="'.fusion_get_settings('sitename').'" href="'.BASEDIR.'index.php"><i class="fa fa-home"></i></a></li>';
            echo '</ul>';
        echo '</div>';
    }

    private static function Sidebar() {
        echo '<aside class="sidebar fixed">';
            echo '<div class="header fixed hidden-xs hidden-sm hidden-md">';
                echo '<div class="pf-logo"></div>';
                echo '<div class="version">PHP Fusion 9</div>';
            echo '</div>';

            echo '<div class="sidebar-menu">';
                echo '<div id="searchBox" data-action="search-box" style="display: none;"><a href="#"><i class="fa fa-search"></i></a></div>';
                echo '<div class="search-box">';
                    echo '<i class="fa fa-search input-search-icon"></i>';
                    echo '<input type="text" id="search_box" name="search_box" class="form-control" placeholder="'.self::SetLocale('001').'"/>';
                    echo '<ul id="search_result" style="display: none;"></ul>';
                echo '</div>';

                echo Admins::getInstance()->vertical_admin_nav(TRUE);
            echo '</div>';
        echo '</aside>';

        add_to_jquery("$('#search_box').bind('keyup', function(e) {
            var data = {
                'pagestring': $(this).val(),
                'url': '".$_SERVER['REQUEST_URI']."',
            };

            $.ajax({
                url: '".MATERIAL."search.php".fusion_get_aidlink()."',
                dataType: 'html',
                method: 'get',
                data: $.param(data),
                success: function(e) {
                    if ($('#search_box').val() == '') {
                        $('#adl').show();
                        $('#search_result').html(e).hide();
                        $('#search_result li').html(e).hide();
                    } else {
                        if ($('body').hasClass('sidebar-sm')) {
                            $('#adl').show();
                        } else {
                            $('#adl').hide();
                        }

                        $('#search_result').html(e).show();
                    }
                }
            });
        });");
    }
}
