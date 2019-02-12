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

class AdminPanel {
    protected static $instance = NULL;
    private $messages = [];
    private $pagenum;
    private static $breadcrumbs = FALSE;

    public function __construct() {
        add_to_head('<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">');
        add_to_footer('<script type="text/javascript" src="'.ADMINLTE.'js/adminlte.min.js"></script>');

        $this->pagenum = (int)filter_input(INPUT_GET, 'pagenum');

        $html = '<div class="wrapper">';
            $html .= $this->MainHeader();
            $html .= $this->MainSidebar();

            $html .= '<div class="content-wrapper">';
                $html .= '<div class="notices">';
                    $html .= renderNotices(getNotices());
                $html .= '</div>';

                $html .= CONTENT;
            $html .= '</div>';

            $html .= $this->MainFooter();

            if (!$this->IsMobile()) {
                $html .= $this->ControlSidebar();
            }

        $html .= '</div>';

        echo $html;
    }

    private function MainHeader() {
        $aidlink = fusion_get_aidlink();

        $html = '<header class="main-header">';
            $html .= '<a href="'.ADMIN.'index.php'.$aidlink.'" class="logo">';
                $html .= '<span class="logo-mini"><i class="php-fusion"></i></span>';
                $html .= '<span class="logo-lg">PHP-Fusion</span>';
            $html .= '</a>';

            $html .= '<nav class="navbar navbar-static-top">';
                $html .= '<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button"><i class="fa fa-fw fa-bars"></i></a>';

                $html .= '<ul class="nav navbar-nav navbar-left hidden-xs">';
                    $sections = Admins::getInstance()->getAdminSections();
                    if (!empty($sections)) {
                        $i = 0;

                        foreach ($sections as $section_name) {
                            $active = ((isset($_GET['pagenum']) && $this->pagenum === $i) || (!$this->pagenum && Admins::getInstance()->_isActive() === $i)) ? TRUE : FALSE;
                            $html .= '<li'.($active ? ' class="active"' : '').'><a href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum='.$i.'" data-toggle="tooltip" data-placement="bottom" title="'.$section_name.'">'.Admins::getInstance()->get_admin_section_icons($i).'</a></li>';
                            $i++;
                        }
                    }
                $html .= '</ul>';

                $html .= '<div class="navbar-custom-menu">';
                    $html .= '<ul class="nav navbar-nav">';
                        $languages = fusion_get_enabled_languages();
                        if (count($languages) > 1) {
                            $html .= '<li class="dropdown languages-menu">';
                                $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">';
                                    $html .= '<i class="fa fa-globe"></i> <img style="margin-top: -3px;" src="'.BASEDIR.'locale/'.LANGUAGE.'/'.LANGUAGE.'-s.png" alt="'.translate_lang_names(LANGUAGE).'"/>';
                                    $html .= '<span class="caret"></span>';
                                $html .= '</a>';
                                $html .= '<ul class="dropdown-menu">';
                                    foreach ($languages as $language_folder => $language_name) {
                                        $html .= '<li><a class="display-block" href="'.clean_request('lang='.$language_folder, ['lang'], FALSE).'"><img class="m-r-5" src="'.BASEDIR.'locale/'.$language_folder.'/'.$language_folder.'-s.png" alt="'.$language_folder.'"/> '.$language_name.'</a></li>';
                                    }
                                $html .= '</ul>';
                            $html .= '</li>';
                        }

                        $html .= $this->MessagesMenu();
                        $html .= $this->UserMenu();

                        $html .= '<li><a href="'.BASEDIR.'index.php"><i class="fa fa-home"></i></a></li>';

                        if (!$this->IsMobile()) {
                            $html .= '<li><a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a></li>';
                        }
                    $html .= '</ul>';
                $html .= '</div>';
            $html .= '</nav>';
        $html .= '</header>';

        return $html;
    }

    private function MessagesMenu() {
        $locale = fusion_get_locale('', ALTE_LOCALE);
        $messages = $this->Messages();
        $msg_icon = !empty($messages) ? '<span class="label label-danger" style="margin-top: inherit;">'.count($messages).'</span>' : '';

        $html = '<li class="dropdown messages-menu">';
            $html .= '<a href="'.BASEDIR.'messages.php" class="dropdown-toggle" data-toggle="dropdown">';
                $html .= '<i class="fa fa-envelope-o"></i>'.$msg_icon;
                $html .= '<span class="caret"></span>';
            $html .= '</a>';
            $html .= '<ul class="dropdown-menu">';
                $html .= '<li class="header text-center">'.$locale['ALT_001'].' '.format_word(count($messages), $locale['fmt_message']).'</li>';
                $html .= '<li><ul class="menu">';
                    if (!empty($messages)) {
                        foreach ($messages as $message) {
                            $html .= '<li>';
                                $html .= '<a href="'.BASEDIR.'messages.php?folder=inbox&amp;msg_read='.$message['link'].'">';
                                    $html .= '<div class="pull-left">';
                                        $html .= display_avatar($message['user'], '40px', '', FALSE, 'img-circle');
                                    $html .= '</div>';
                                    $html .= '<h4>';
                                        $html .= $message['user']['user_name'];
                                        $html .= '<small><i class="fa fa-clock-o"></i> '.$message['datestamp'].'</small>';
                                    $html .= '</h4>';
                                    $html .= '<p>'.trim_text($message['title'], 20).'</p>';
                                $html .= '</a>';
                            $html .= '</li>';
                        }
                    } else {
                        $html .= '<li class="text-center">'.$locale['ALT_002'].'</li>';
                    }

                $html .= '</ul></li>';
                $html .= '<li class="footer"><a href="'.BASEDIR.'messages.php?msg_send=new" class="text-bold">'.$locale['ALT_003'].'</a></li>';
            $html .= '</ul>';
        $html .= '</li>';

        return $html;
    }

    private function UserMenu() {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();

        $html = '<li class="dropdown user user-menu">';
            $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">';
                $html .= display_avatar($userdata, '25px', '', FALSE, 'user-image img-circle');
                $html .= '<span class="hidden-xs">'.$userdata['user_name'].'</span>';
                $html .= '<span class="caret"></span>';
            $html .= '</a>';
            $html .= '<ul class="dropdown-menu">';
                $html .= '<li class="user-header">';
                    $html .= display_avatar($userdata, '90px', '', FALSE, 'img-circle');
                    $html .= '<p>'.$userdata['user_name'].'<small>'.$locale['ALT_004'].' '.showdate('longdate', $userdata['user_joined']).'</small></p>';
                $html .= '</li>';
                $html .= '<li class="user-body">';
                    $html .= '<div class="row">';
                        $html .= '<div class="col-xs-6 text-center">';
                            $html .= '<a href="'.BASEDIR.'edit_profile.php"><i class="fa fa-pencil fa-fw"></i> '.$locale['UM080'].'</a>';
                        $html .= '</div>';
                        $html .= '<div class="col-xs-6 text-center">';
                            $html .= '<a href="'.BASEDIR.'profile.php?lookup='.$userdata['user_id'].'"><i class="fa fa-eye fa-fw"></i> '.$locale['view'].' '.$locale['profile'].'</a>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</li>';
                $html .= '<li class="user-footer">';
                    $html .= '<div class="pull-left">';
                        $html .= '<a href="'.FUSION_REQUEST.'&amp;logout" class="btn btn-default btn-flat">'.$locale['admin-logout'].'</a>';
                    $html .= '</div>';
                    $html .= '<div class="pull-right">';
                        $html .= '<a href="'.BASEDIR.'index.php?logout=yes" class="btn btn-default btn-flat">'.$locale['logout'].'</a>';
                    $html .= '</div>';
                $html .= '</li>';
            $html .= '</ul>';
        $html .= '</li>';

        return $html;
    }

    private function MainSidebar() {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        $useronline = $userdata['user_lastvisit'] >= time() - 900 ? TRUE : FALSE;

        $html = '<aside class="main-sidebar">';
            $html .= '<section class="sidebar">';
                $html .= '<div class="user-panel">';
                    $html .= '<div class="pull-left image">';
                        $html .= display_avatar($userdata, '45px', '', FALSE, 'img-circle');
                    $html .= '</div>';
                    $html .= '<div class="pull-left info">';
                        $html .= '<p>'.$userdata['user_name'].'</p>';
                        $html .= '<a href="#">';
                            $html .= '<i class="fa fa-circle '.($useronline ? 'text-success' : 'text-danger').'"></i> ';
                            $html .= $useronline ? $locale['online'] : $locale['offline'];
                        $html .= '</a>';
                    $html .= '</div>';
                $html .= '</div>';

                $html .= '<div class="sidebar-form">';
                    $html .= '<input type="text" id="search_pages" name="search_pages" class="form-control" placeholder="'.$locale['ALT_005'].'">';
                $html .= '</div>';
                $html .= '<ul class="sidebar-menu" id="search_result" style="display: none;"></ul>';
                $html .= '<img id="ajax-loader" style="width: 30px; display: none;" class="img-responsive center-x m-t-10" alt="Ajax Loader" src="'.ADMINLTE.'images/loader.svg"/>';

                $this->SearchAjax();

                $html .= $this->SidebarMenu();

            $html .= '</section>';
        $html .= '</aside>';

        return $html;
    }

    private function SearchAjax() {
        add_to_jquery('$("#search_pages").bind("keyup", function (e) {
            $.ajax({
                url: "'.ADMIN.'includes/acp_search.php'.fusion_get_aidlink().'",
                method: "get",
                data: $.param({"pagestring": $(this).val()}),
                dataType: "json",
                beforeSend: function () {
                    $("#ajax-loader").show();
                },
                success: function (e) {
                    if ($("#search_pages").val() == "") {
                        $("#adl").show();
                        $("#search_result").html(e).hide();
                        $("#search_result li").html(e).hide();
                    } else {
                        var result = "";

                        if (!e.status) {
                            $.each(e, function (i, data) {
                                if (data) {
                                    result += "<li><a href=\"" + data.link + "\"><img class=\"admin-image\" alt=\"" + data.title + "\" src=\"" + data.icon + "\"/> " + data.title + "</a></li>";
                                }
                            });
                        } else {
                            result = "<li class=\"header text-white\">" + e.status + "</li>";
                        }

                        $("#search_result").html(result).show();
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
        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $admin_sections = Admins::getInstance()->getAdminSections();
        $admin_pages = Admins::getInstance()->getAdminPages();

        $html = '<ul id="adl" class="sidebar-menu" data-widget="tree">';
            foreach ($admin_sections as $i => $section_name) {
                $active = ((isset($_GET['pagenum']) && $this->pagenum === $i) || (!$this->pagenum && Admins::getInstance()->_isActive() === $i)) ? TRUE : FALSE;

                if (!empty($admin_pages[$i])) {
                    $html .= '<li class="treeview'.($active ? ' active' : '').'">';
                        $html .= '<a href="#">';
                            $html .= Admins::getInstance()->get_admin_section_icons($i).' <span>'.$section_name.'</span>';
                            $html .= '<span class="pull-right-container">';
                                $html .= '<i class="fa fa-angle-left pull-right"></i>';
                                $html .= ($i > 4 ? '<small class="label pull-right bg-blue">'.count($admin_pages[$i]).'</small>' : '');
                            $html .= '</span>';
                        $html .= '</a>';
                        $html .= '<ul class="treeview-menu">';
                            foreach ($admin_pages[$i] as $key => $data) {
                                if (checkrights($data['admin_rights'])) {
                                    $sub_active = $data['admin_link'] == Admins::getInstance()->_currentPage() ? TRUE : FALSE;

                                    $title = $data['admin_title'];
                                    if ($data['admin_page'] !== 5) {
                                        $title = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $title;
                                    }

                                    $icon = '<img class="m-r-5" src="'.get_image('ac_'.$data['admin_rights']).'" alt="'.$title.'"/>';

                                    if (!empty($admin_pages[$data['admin_rights']])) {
                                        if (checkrights($data['admin_rights'])) {
                                            $html .= '<li class="treeview'.($sub_active ? ' menu-open' : '').'">';
                                                $html .= '<a href="#">'.$icon.' '.$title.'<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>';
                                                $html .= '<ul class="treeview-menu"'.($sub_active ? ' style="display: block;"' : '').'>';
                                                    foreach ($admin_pages[$data['admin_rights']] as $sub_page) {
                                                        $html .= '<li><a href="'.$sub_page['admin_link'].'">'.$sub_page['admin_title'].'</a></li>';
                                                    }
                                                $html .= '</ul>';
                                            $html .= '</li>';
                                        }
                                    } else {
                                        $html .= '<li'.($sub_active ? ' class="active"' : '').'><a href="'.ADMIN.$data['admin_link'].$aidlink.'">'.$icon.' '.$title.'</a></li>';
                                    }
                                }
                            }
                        $html .= '</ul>';
                    $html .= '</li>';
                } else {
                    $html .= '<li'.($active ? ' class="active"' : '').'><a href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum=0">';
                        $html .= Admins::getInstance()->get_admin_section_icons($i).' <span>'.$section_name.'</span>';
                    $html .= '</a></li>';
                }
            }
        $html .= '</ul>';

        return $html;
    }

    private function MainFooter() {
        $locale = fusion_get_locale();

        $html = '<footer class="main-footer">';
            $html .= showFooterErrors();

            if (fusion_get_settings('rendertime_enabled')) {
                $html .= showrendertime().' '.showMemoryUsage().'<br />';
            }

            $html .= '<strong>';
                $html .= 'AdminLTE Admin Theme &copy; '.date('Y').' '.$locale['ALT_006'].' <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a> ';
                $html .= $locale['and'].' <a href="https://adminlte.io" target="_blank">Almsaeed Studio</a>';
            $html .= '</strong>';
            $html .= '<br/>'.str_replace('<br />', ' | ', showcopyright());
        $html .= '</footer>';

        return $html;
    }

    private function ControlSidebar() {
        $locale = fusion_get_locale('', ALTE_LOCALE);

        add_to_footer('<script type="text/javascript" src="'.ADMINLTE.'js/control-sidebar.min.js"></script>');

        $html = '
        <aside class="control-sidebar control-sidebar-dark">
            <div class="content">
                <h4 class="control-sidebar-heading">'.$locale['ALT_008'].'</h4>

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        <input type="checkbox" data-layout="fixed" class="pull-right"> '.$locale['ALT_009'].'
                    </label>
                </div>

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        <input type="checkbox" data-layout="sidebar-collapse" class="pull-right"> '.$locale['ALT_010'].'
                    </label>
                </div>

                <h4 class="control-sidebar-heading">'.$locale['ALT_011'].'</h4>
                <h5>'.$locale['ALT_012'].'</h5>

                <ul class="list-unstyled clearfix">
                    <li class="skin-preview">
                        <a href="javascript:void(0)" data-skin="skin-blue" class="clearfix full-opacity-hover skin">
                            <div><span class="header-left" style="background: #367fa9;"></span><span class="bg-light-blue header-right"></span></div>
                            <div><span class="body-left" style="background: #222d32;"></span><span class="body-right" style="background: #f4f5f7;"></span></div>
                        </a>
                    </li>
                    <li class="skin-preview">
                        <a href="javascript:void(0)" data-skin="skin-black" class="clearfix full-opacity-hover skin">
                            <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1);" class="clearfix"><span class="header-left" style="background: #fefefe;"></span><span class="header-right" style="background: #fefefe;"></span></div>
                            <div><span class="body-left" style="background: #222;"></span><span class="body-right" style="background: #f4f5f7;"></span></div>
                        </a>
                    </li>
                    <li class="skin-preview">
                        <a href="javascript:void(0)" data-skin="skin-purple" class="clearfix full-opacity-hover skin">
                            <div><span class="header-left bg-purple-active"></span><span class="bg-purple header-right"></span></div>
                            <div><span class="body-left" style="background: #222d32;"></span><span class="body-right" style="background: #f4f5f7;"></span></div>
                        </a>
                    </li>
                    <li class="skin-preview">
                        <a href="javascript:void(0)" data-skin="skin-green" class="clearfix full-opacity-hover skin">
                            <div><span class="header-left bg-green-active"></span><span class="bg-green header-right"></span></div>
                            <div><span class="body-left" style="background: #222d32;"></span><span class="body-right" style="background: #f4f5f7;"></span></div>
                        </a>
                    </li>
                    <li class="skin-preview">
                        <a href="javascript:void(0)" data-skin="skin-red" class="clearfix full-opacity-hover skin">
                            <div><span class="header-left bg-red-active"></span><span class="bg-red header-right"></span></div>
                            <div><span class="body-left" style="background: #222d32;"></span><span class="body-right" style="background: #f4f5f7;"></span></div>
                        </a>
                    </li>
                    <li class="skin-preview">
                        <a href="javascript:void(0)" data-skin="skin-yellow" class="clearfix full-opacity-hover skin">
                            <div><span class="header-left bg-yellow-active"></span><span class="bg-yellow header-right"></span></div>
                            <div><span class="body-left" style="background: #222d32;"></span><span class="body-right" style="background: #f4f5f7;"></span></div>
                        </a>
                    </li>
                </ul>

                <h5>'.$locale['ALT_013'].'</h5>

                <ul class="list-unstyled clearfix">
                    <li class="skin-preview">
                        <a href="javascript:void(0)" data-skin="skin-blue-light" class="clearfix full-opacity-hover skin">
                            <div><span class="header-left" style="background: #367fa9;"></span><span class="bg-light-blue header-right"></span></div>
                            <div><span class="body-left" style="background: #f9fafc;"></span><span class="body-right" style="background: #f4f5f7;"></span></div>
                        </a>
                    </li>
                    <li class="skin-preview">
                        <a href="javascript:void(0)" data-skin="skin-black-light" class="clearfix full-opacity-hover skin">
                            <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1);" class="clearfix"><span class="header-left" style="background: #fefefe;"></span><span class="header-right" style="background: #fefefe;"></span></div>
                            <div><span class="body-left" style="background: #f9fafc;"></span><span class="body-right" style="background: #f4f5f7;"></span></div>
                        </a>
                    </li>
                    <li class="skin-preview">
                        <a href="javascript:void(0)" data-skin="skin-purple-light" class="clearfix full-opacity-hover skin">
                            <div><span class="header-left bg-purple-active"></span><span class="bg-purple header-right"></span></div>
                            <div><span class="body-left" style="background: #f9fafc;"></span><span class="body-right" style="background: #f4f5f7;"></span></div>
                        </a>
                    </li>
                    <li class="skin-preview">
                        <a href="javascript:void(0)" data-skin="skin-green-light" class="clearfix full-opacity-hover skin">
                            <div><span class="header-left bg-green-active"></span><span class="bg-green header-right"></span></div>
                            <div><span class="body-left" style="background: #f9fafc;"></span><span class="body-right" style="background: #f4f5f7;"></span></div>
                        </a>
                    </li>
                    <li class="skin-preview">
                        <a href="javascript:void(0)" data-skin="skin-red-light" class="clearfix full-opacity-hover skin">
                            <div><span class="header-left bg-red-active"></span><span class="bg-red header-right"></span></div>
                            <div><span class="body-left" style="background: #f9fafc;"></span><span class="body-right" style="background: #f4f5f7;"></span></div>
                        </a>
                    </li>
                    <li class="skin-preview">
                        <a href="javascript:void(0)" data-skin="skin-yellow-light" class="clearfix full-opacity-hover skin">
                            <div><span class="header-left bg-yellow-active"></span><span class="bg-yellow header-right"></span></div>
                            <div><span class="body-left" style="background: #f9fafc;"></span><span class="body-right" style="background: #f4f5f7;"></span></div>
                        </a>
                    </li>
                </ul>

            </div>
        </aside>

        <div class="control-sidebar-bg"></div>';

        return $html;
    }

    public function Messages() {
        $userdata = fusion_get_userdata();

        $result = dbquery("
            SELECT message_id, message_subject, message_from, user_id, u.user_name, u.user_status, u.user_avatar, message_datestamp
            FROM ".DB_MESSAGES."
            INNER JOIN ".DB_USERS." u ON u.user_id=message_from
            WHERE message_to='".$userdata['user_id']."' AND message_user='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'
            GROUP BY message_id
        ");

        if (dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_user='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'")) {
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->messages[] = [
                        'link'      => $data['message_id'],
                        'title'     => $data['message_subject'],
                        'user'      => [
                            'user_id'     => $data['user_id'],
                            'user_name'   => $data['user_name'],
                            'user_status' => $data['user_status'],
                            'user_avatar' => $data['user_avatar']
                        ],
                        'datestamp' => timer($data['message_datestamp'])
                    ];
                }
            }
        }

        return $this->messages;
    }

    public function GetMessages() {
        return $this->messages;
    }

    public function IsMobile() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER['HTTP_USER_AGENT']);
    }

    public static function OpenTable($title = FALSE, $class = NULL, $bg = TRUE) {
        $html = '';

        if (!empty($title)) {
            $html .= '<section class="content-header">';
            $html .= '<h1>'.$title.'</h1>';

            if (self::$breadcrumbs == FALSE) {
                $html .= render_breadcrumbs();
                self::$breadcrumbs = TRUE;
            }
            $html .= '</section>';
        }

        $html .= '<section class="content '.$class.'">';

        if ($bg == TRUE) $html .= '<div class="p-15" style="background-color: #fff;">';

        echo $html;
    }

    public static function CloseTable($bg = TRUE) {
        $html = '';
        if ($bg === TRUE) $html .= '</div>';
        $html .= '</section>';

        echo $html;
    }
}
