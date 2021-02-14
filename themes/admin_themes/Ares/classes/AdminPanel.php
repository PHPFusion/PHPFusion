<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: AdminPanel.php
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

use \PHPFusion\Admins;

class AdminPanel {
    private $pagenum;

    public function __construct() {
        global $settings, $userdata;
        $locale = fusion_get_locale('', ARS_LOCALE);
        $aidlink = fusion_get_aidlink();

        add_to_footer('<script src="'.ARS.'js/scripts.min.js"></script>');

        $this->pagenum = (int)filter_input(INPUT_GET, 'pagenum');

        $html = '<div class="topnav-wrapper">';
            $html .= '<nav class="navbar navbar-default topnav-bar">';
                $html .= '<div class="navbar-header">';
                        $html .= '<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#ares-navbar"><span class="sr-only">'.$locale['global_017'].'</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>';
                        $html .= '<div class="visible-xs"><a href="#" class="navicon navtogglem"><i class="fa fa-bars"></i></a></div>';
                        $html .= '<div class="navbar-brand topnav-brand" id="ares-brand">';
                        $html .= '<img class="logo" src="'.IMAGES.'php-fusion-icon.png" alt="Logo"/>';
                        $html .= '<div class="version">PHP Fusion</div>';
                    $html .= '</div>';
                $html .= '</div>';

                $html .= '<div class="collapse navbar-collapse" id="ares-navbar">';
                    $html .= '<ul class="nav navbar-nav tool-navbar">';
                        $html .= '<li class="hidden-xs"><a href="#" class="navtoggle"><i class="fa fa-bars"></i></a></li>';
                        $sections = Admins::getInstance()->getAdminSections();
                        if (!empty($sections)) {
                            $html .= '<li class="dropdown">';
                                $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$locale['ares_005'].' <span class="caret"></span></a>';
                                $html .= '<ul class="dropdown-menu">';
                                    $i = 0;
                                    foreach ($sections as $section_name) {
                                        $active = ((isset($_GET['pagenum']) && $this->pagenum === $i) || (!$this->pagenum && Admins::getInstance()->_isActive() === $i));
                                        $html .= '<li'.($active ? ' class="active"' : '').'><a href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum='.$i.'">'.Admins::getInstance()->get_admin_section_icons($i).' '.$section_name.'</a></li>';
                                        $i++;
                                    }
                                $html .= '</ul>';
                            $html .= '</li>';
                        }
                    $html .= '</ul>';
                    $html .= '<ul class="nav navbar-nav tool-navbar navbar-right">';
                        $languages = fusion_get_enabled_languages();
                        if (count($languages) > 1) {
                            $html .= '<li class="dropdown languages-switcher">';
                                $html .= '<a class="dropdown-toggle pointer" data-toggle="dropdown" title="'.$locale['282'].'"><i class="fa fa-globe"></i> <img class="current" src="'.BASEDIR.'locale/'.LANGUAGE.'/'.LANGUAGE.'-s.png" alt="'.translate_lang_names(LANGUAGE).'"/> <span class="caret"></span></a>';
                                $html .= '<ul class="dropdown-menu">';
                                    foreach ($languages as $language_folder => $language_name) {
                                        $html .= '<li><a class="display-block" href="'.clean_request('lang='.$language_folder, ['lang'], FALSE).'"><img class="m-r-5" src="'.BASEDIR.'locale/'.$language_folder.'/'.$language_folder.'-s.png" alt="'.$language_folder.'"/> '.$language_name.'</a></li>';
                                    }
                                $html .= '</ul>';
                            $html .= '</li>';
                        }

                        $html .= '<li class="dropdown user">';
                            $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$userdata['user_name'].' <span class="caret"></span> '.display_avatar($userdata, '35px', '', FALSE, 'img-circle').'</a>';
                            $html .= '<ul class="dropdown-menu" role="menu">';
                                $html .= '<li><a href="'.BASEDIR.'edit_profile.php"><i class="fa fa-pencil fa-fw"></i> '.$locale['UM080'].'</a></li>';
                                $html .= '<li><a href="'.BASEDIR.'profile.php?lookup='.$userdata['user_id'].'"><i class="fa fa-eye fa-fw"></i> '.$locale['view'].' '.$locale['profile'].'</a></li>';
                                $html .= '<li class="divider"></li>';
                                $html .= '<li><a href="'.BASEDIR.'index.php?logout=yes"><i class="fa fa-sign-out fa-fw"></i> '.$locale['logout'].'</a></li>';
                            $html .= '</ul>';
                        $html .= '</li>';
                        $html .= '<li><a title="'.$settings['sitename'].'" href="'.BASEDIR.'index.php"><i class="fa fa-home"></i><span class="visible-xs m-l-5">'.$locale['home'].'</span></a></li>';
                    $html .= '</ul>';
                $html .= '</div>';
            $html .= '</nav>';
        $html .= '</div>'; // .topnav-wrapper

        $html .= '<div class="wrapper">';

        $html .= '<aside id="ares-nav">';
            $html .= '<ul id="aressub">';
                $html .= '<li class="uavatar">';
                    $html .= '<span>'.display_avatar($userdata, '90px', '', TRUE, 'img-circle').'</span>';
                    $html .= '<div id="admin-info" class="admin-info">';
                        $html .= '<span class="greetings">'.$locale['global_035'].'</span>';
                        $html .= '<span>'.$userdata['user_name'].'</span>';
                        $html .= getuserlevel($userdata['user_level']);
                    $html .= '</div>';
                $html .= '</li>';

                $html .= '<li class="search-box">';
                    $html .= '<input type="text" id="search_box" name="search_box" class="form-control" placeholder="'.$locale['search'].'"/>';
                    $html .= '<ul id="search_result" style="display: none;"></ul>';
                    $html .= '<img id="ajax-loader" style="width: 30px; display: none;" class="img-responsive center-x m-t-10" alt="Ajax Loader" src="'.IMAGES.'loader.svg"/>';
                $html .= '</li>';

                add_to_jquery('search_ajax("'.ADMIN.'includes/acp_search.php'.fusion_get_aidlink().'");');

                $html .= '<li id="ares-descriptor" class="ares-mitem description">'.$locale['ares_001'].'</li>';

                $admin_sections = Admins::getInstance()->getAdminSections();
                $admin_pages = Admins::getInstance()->getAdminPages();

                foreach ($admin_sections as $i => $section_name) {
                    $active = ((isset($_GET['pagenum']) && $this->pagenum === $i) || (!$this->pagenum && Admins::getInstance()->_isActive() === $i));

                    if (!empty($admin_pages[$i])) {
                        $html .= '<li class="ares-mitem dropdown'.($active ? ' active' : '').'">';
                            $html .= '<a class="dropdown-toggle pointer" data-toggle="collapse" data-parent="#aressub" data-target="#aresnavsection'.$i.'">';
                                $html .= Admins::getInstance()->get_admin_section_icons($i).' <span>'.$section_name.'</span>';
                                $html .= '<span class="caret"></span>';
                            $html .= '</a>';
                            $html .= '<ul class="collapse'.($active ? ' in' : '').'" id="aresnavsection'.$i.'">';
                                foreach ($admin_pages[$i] as $key => $data) {
                                    if (checkrights($data['admin_rights'])) {
                                        $sub_active = $data['admin_link'] == Admins::getInstance()->_currentPage();

                                        $title = $data['admin_title'];
                                        if ($data['admin_page'] !== 5) {
                                            $title = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $title;
                                        }

                                        $icon = '<img class="m-r-5 admin-image" src="'.get_image('ac_'.$data['admin_rights']).'" alt="'.$title.'"/>';

                                        if (!empty($admin_pages[$data['admin_rights']])) {
                                            if (checkrights($data['admin_rights'])) {
                                                $html .= '<li>';
                                                    $html .= '<a href="'.ADMIN.$data['admin_link'].$aidlink.'">'.$title.' '.$icon.'</a>';
                                                    $html .= '<ul>';
                                                        foreach ($admin_pages[$data['admin_rights']] as $sub_page) {
                                                            $html .= '<li><a class="aressubitem" href="'.$sub_page['admin_link'].'">'.$sub_page['admin_title'].'</a></li>';
                                                        }
                                                    $html .= '</ul>';
                                                $html .= '</li>';
                                            }
                                        } else {
                                            $html .= '<li'.($sub_active ? ' class="active"' : '').'><a href="'.ADMIN.$data['admin_link'].$aidlink.'">'.$title.' '.$icon.'</a></li>';
                                        }
                                    }
                                }
                            $html .= '</ul>';
                        $html .= '</li>';
                    } else {
                        $html .= '<li class="ares-mitem'.($active ? ' active' : '').'"><a href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum=0">';
                            $html .= Admins::getInstance()->get_admin_section_icons($i).' <span>'.$section_name.'</span>';
                        $html .= '</a></li>';
                    }
                }
            $html .= '</ul>';
        $html .= '</aside>';

        $html .= '<section id="ares-content">';
            $html .= render_breadcrumbs();

            $html .= '<div class="notice">';
            $html .= '<div id="updatechecker_result" class="alert alert-info m-b-0" style="display:none;"></div>';
            $html .= '</div>';

            $html .= '<div class="main-content">';
                $html .= CONTENT;
            $html .= '</div>';

            $html .= '<footer class="p-15">';
                global $_errorHandler;

                if (iADMIN && checkrights('ERRO') && count($_errorHandler) > 0) {
                    $html .= '<div>'.str_replace('[ERROR_LOG_URL]', ADMIN.'errors.php'.fusion_get_aidlink(), $locale['err_101']).'</div>';
                }

                if (fusion_get_settings('rendertime_enabled')) {
                    $html .= showrendertime().' '.showMemoryUsage().'<br />';
                }

                $html .= 'Ares Admin Theme &copy; '.date('Y').' '.$locale['ares_002'].' <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a> & PHP Fusion Inc.<br/>';
                $html .= showcopyright('', TRUE);
            $html .= '</footer>';
        $html .= '</section>';
        $html .= '</div>'; // .wrapper

        echo $html;
    }
}
