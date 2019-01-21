<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AdminLTE/classes/AdminPanel.inc
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

use \PHPFusion\Admins;
use \PHPFusion\OutputHandler;

class AdminPanel {
    private static $breadcrumbs = FALSE;

    public function __construct() {
        OutputHandler::addToHead('<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">');
        OutputHandler::addToFooter('<script type="text/javascript" src="'.ADMINLTE.'js/adminlte.min.js"></script>');

        echo '<div class="wrapper">';
            $this->MainHeader();
            $this->MainSidebar();

            echo '<div class="content-wrapper">';
                echo CONTENT;
            echo '</div>';

            $this->MainFooter();
        echo '</div>';
    }

    private function MainHeader() {
        $aidlink = fusion_get_aidlink();

        echo '<header class="main-header">';
            echo '<a href="'.ADMIN.'index.php'.$aidlink.'" class="logo">';
                echo '<span class="logo-mini"><img class="img-responsive p-5" src="'.IMAGES.'php-fusion-icon.png" alt="Logo"></span>';
                echo '<span class="logo-lg">PHP-Fusion</span>';
            echo '</a>';

            echo '<nav class="navbar navbar-static-top">';
                echo '<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button"><i class="fa fa-fw fa-bars"></i></a>';

                echo '<ul class="nav navbar-nav navbar-left hidden-xs">';
                    $sections = Admins::getInstance()->getAdminSections();
                    if (!empty($sections)) {
                        $i = 0;

                        foreach ($sections as $section_name) {
                            $active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && Admins::getInstance()->_isActive() == $i) ? ' class="active"' : '';
                            echo '<li'.$active.'><a href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum='.$i.'" data-toggle="tooltip" data-placement="bottom" title="'.$section_name.'">'.Admins::getInstance()->get_admin_section_icons($i).'</a></li>';
                            $i++;
                        }
                    }
                echo '</ul>';

                echo '<div class="navbar-custom-menu">';
                    echo '<ul class="nav navbar-nav">';
                        $languages = fusion_get_enabled_languages();
                        if (count($languages) > 1) {
                            echo '<li class="dropdown languages-menu">';
                                echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown">';
                                    echo '<i class="fa fa-globe"></i> <img style="margin-top: -3px;" src="'.BASEDIR.'locale/'.LANGUAGE.'/'.LANGUAGE.'-s.png" alt="'.translate_lang_names(LANGUAGE).'"/>';
                                    echo '<span class="caret"></span>';
                                echo '</a>';
                                echo '<ul class="dropdown-menu">';
                                    foreach ($languages as $language_folder => $language_name) {
                                        echo '<li><a class="display-block" href="'.clean_request('lang='.$language_folder, ['lang'], FALSE).'"><img class="m-r-5" src="'.BASEDIR.'locale/'.$language_folder.'/'.$language_folder.'-s.png" alt="'.$language_folder.'"/> '.$language_name.'</a></li>';
                                    }
                                echo '</ul>';
                            echo '</li>';
                        }

                        $this->UserMenu();

                        echo '<li><a href="'.BASEDIR.'index.php"><i class="fa fa-home"></i></a></li>';
                    echo '</ul>';
                echo '</div>';
            echo '</nav>';
        echo '</header>';
    }

    private function UserMenu() {
        $locale = fusion_get_locale('', ALTE_LOCALE);
        $userdata = fusion_get_userdata();

        echo '<li class="dropdown user user-menu">';
            echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown">';
                echo display_avatar($userdata, '25px', '', FALSE, 'user-image img-circle');
                echo '<span class="hidden-xs">'.$userdata['user_name'].'</span>';
                echo '<span class="caret"></span>';
            echo '</a>';
            echo '<ul class="dropdown-menu">';
                echo '<li class="user-header">';
                    echo display_avatar($userdata, '90px', '', FALSE, 'img-circle');
                    echo '<p>'.$userdata['user_name'].'<small>'.$locale['ALT_004'].' '.showdate('longdate', $userdata['user_joined']).'</small></p>';
                echo '</li>';
                echo '<li class="user-body">';
                    echo '<div class="row">';
                        echo '<div class="col-xs-6 text-center">';
                            echo '<a href="'.BASEDIR.'edit_profile.php"><i class="fa fa-pencil fa-fw"></i> '.$locale['UM080'].'</a>';
                        echo '</div>';
                        echo '<div class="col-xs-6 text-center">';
                            echo '<a href="'.BASEDIR.'profile.php?lookup='.$userdata['user_id'].'"><i class="fa fa-eye fa-fw"></i> '.$locale['view'].' '.$locale['profile'].'</a>';
                        echo '</div>';
                    echo '</div>';
                echo '</li>';
                echo '<li class="user-footer">';
                    echo '<div class="pull-right">';
                        echo '<a href="'.BASEDIR.'index.php?logout=yes" class="btn btn-default btn-flat">'.$locale['logout'].'</a>';
                    echo '</div>';
                echo '</li>';
            echo '</ul>';
        echo '</li>';
    }

    private function MainSidebar() {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        $useronline = $userdata['user_lastvisit'] >= time() - 900 ? TRUE : FALSE;

        $this->SearchAjax();

        echo '<aside class="main-sidebar">';
            echo '<section class="sidebar">';
                echo '<div class="user-panel">';
                    echo '<div class="pull-left image">';
                        echo display_avatar($userdata, '45px', '', FALSE, 'img-circle');
                    echo '</div>';
                    echo '<div class="pull-left info">';
                        echo '<p>'.$userdata['user_name'].'</p>';
                        echo '<a href="#">';
                            echo '<i class="fa fa-circle '.($useronline ? 'text-success' : 'text-danger').'"></i> ';
                            echo $useronline ? $locale['online'] : $locale['offline'];
                        echo '</a>';
                    echo '</div>';
                echo '</div>';

                echo '<div class="sidebar-form">';
                    echo '<input type="text" id="search_pages" name="search_pages" class="form-control" placeholder="'.$locale['ALT_005'].'">';
                echo '</div>';
                echo '<ul class="sidebar-menu" id="search_result" style="display: none;"></ul>';
                echo '<img id="ajax-loader" style="width: 30px; display: none;" class="img-responsive center-x m-t-10" alt="Ajax Loader" src="'.ADMINLTE.'images/loader.svg"/>';

                $this->SidebarMenu();

            echo '</section>';
        echo '</aside>';
    }

    private function SearchAjax() {
        OutputHandler::addToJQuery('$("#search_pages").bind("keyup", function (e) {
            var data = {
                "pagestring": $(this).val(),
                "url": "'.$_SERVER['REQUEST_URI'].'",
            };

            $.ajax({
                url: "'.ADMINLTE.'acp_search.php'.fusion_get_aidlink().'",
                method: "get",
                data: $.param(data),
                beforeSend: function () {
                    $("#ajax-loader").show();
                },
                success: function (e) {
                    if ($("#search_pages").val() == "") {
                        $("#adl").show();
                        $("#search_result").html(e).hide();
                        $("#search_result li").html(e).hide();
                    } else {
                        $("#search_result").html(e).show();
                        $("#adl").hide();
                    }
                },
                complete: function () {
                    $("#ajax-loader").hide();
                }
            });
        });');
    }

    private function SidebarMenu() {
        global $aidlink, $locale;

        $admin_icon = [
            '0' => 'entypo gauge',
            '1' => 'entypo docs',
            '2' => 'entypo user',
            '3' => 'entypo drive',
            '4' => 'entypo cog',
            '5' => 'entypo magnet'
        ];

        echo '<ul id="adl" class="sidebar-menu" data-widget="tree">';
            for ($i = 0; $i < 6; $i++) {
                $result = dbquery("SELECT * FROM ".DB_ADMIN." WHERE admin_page='".$i."' AND admin_link !='reserved' ORDER BY admin_title ASC");
                $active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && $this->AdminActive() == $i) ? 1 : 0;

                if ($i == 0) {
                    echo '<li'.($active ? ' class="active"' : '').'><a href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum=0">';
                        echo '<i class="'.$admin_icon[$i].'"></i><span>'.$locale['ac0'.$i].'</span>';
                    echo '</a></li>';
                } else {
                    echo '<li class="treeview'.($active ? ' active' : '').'">';
                        echo '<a href="#">';
                            echo '<i class="'.$admin_icon[$i].'"></i><span>'.$locale['ac0'.$i].'</span>';
                            echo '<span class="pull-right-container">';
                                echo '<i class="fa fa-angle-left pull-right"></i>';
                            echo '</span>';
                        echo '</a>';
                        echo '<ul class="treeview-menu">';
                            if (dbrows($result) > 0) {
                                while ($data = dbarray($result)) {
                                    if (checkrights($data['admin_rights'])) {
                                        $secondary_active = FUSION_SELF == $data['admin_link'] ? TRUE : FALSE;

                                        $title = $data['admin_title'];
                                        if ($data['admin_page'] !== 5) {
                                            $title = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $title;
                                        }

                                        $icon = '<img class="m-r-5" src="'.(file_exists(ADMIN.'images/'.$data['admin_image']) ? ADMIN.'images/'.$data['admin_image'] : ADMIN.'images/notfound.png').'" alt="'.$title.'"/>';

                                        echo '<li'.($secondary_active ? ' class="active"' : '').'>';
                                            echo '<a href="'.ADMIN.$data['admin_link'].$aidlink.'">'.$icon.' '.$title.'</a>';
                                        echo '</li>';
                                    }
                                }
                            }
                        echo '</ul>';
                    echo '</li>';
                }
            }
        echo '</ul>';
    }

    private function AdminActive() {
        $result = dbquery("SELECT admin_title, admin_page, admin_rights, admin_link FROM ".DB_ADMIN." ORDER BY admin_page DESC, admin_title ASC");
        $admin_pages = [];
        while ($data = dbarray($result)) {
            if ($data['admin_link'] != "reserved" && checkrights($data['admin_rights'])) {
                $admin_pages[$data['admin_page']][$data['admin_title']] = $data['admin_link'];
            }
        }

        foreach ($admin_pages as $key => $data) {
            if (in_array(FUSION_SELF, $data)) {
                return $key;
            }
        }
        return '0';
    }


    private function MainFooter() {
        $locale = fusion_get_locale('', ALTE_LOCALE);

        echo '<footer class="main-footer">';
            //echo showFooterErrors();

            if (fusion_get_settings('rendertime_enabled')) {
                echo showrendertime().showMemoryUsage().'<br />';
            }

            echo '<strong>';
                echo 'AdminLTE Admin Theme &copy; '.date('Y').' '.$locale['ALT_006'].' <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a> ';
                echo $locale['and'].' <a href="https://adminlte.io" target="_blank">Almsaeed Studio</a>';
            echo '</strong>';
            echo '<br/>'.str_replace('<br />', ' | ', showcopyright());
        echo '</footer>';
    }

    public static function OpenTable($title = FALSE, $class = NULL, $bg = TRUE) {
        if (!empty($title)) {
            echo '<section class="content-header">';
            echo '<h1>'.$title.'</h1>';

            if (self::$breadcrumbs == FALSE) {
                echo render_breadcrumbs();
                self::$breadcrumbs = TRUE;
            }
            echo '</section>';
        }

        echo '<section class="content '.$class.'">';

        if ($bg == TRUE) echo '<div class="p-15" style="background-color: #fff;">';
    }

    public static function CloseTable($bg = TRUE) {
        if ($bg == TRUE) echo '</div>';
        echo '</section>';
    }
}
