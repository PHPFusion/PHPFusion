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
    private static $messages = [];
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

            if (!self::IsMobile()) {
                $this->ControlSidebar();
            }

        echo '</div>';
    }

    private function MainHeader() {
        $aidlink = fusion_get_aidlink();

        echo '<header class="main-header">';
            echo '<a href="'.ADMIN.'index.php'.$aidlink.'" class="logo">';
                echo '<span class="logo-mini"><i class="php-fusion"></i></span>';
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

                        $this->MessagesMenu();
                        $this->UserMenu();

                        echo '<li><a href="'.BASEDIR.'index.php"><i class="fa fa-home"></i></a></li>';

                        if (!self::IsMobile()) {
                            echo '<li><a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a></li>';
                        }
                    echo '</ul>';
                echo '</div>';
            echo '</nav>';
        echo '</header>';
    }

    private function MessagesMenu() {
        $locale = fusion_get_locale('', ALTE_LOCALE);
        $messages = self::Messages();
        $msg_icon = !empty($messages) ? '<span class="label label-danger" style="margin-top: inherit;">'.count($messages).'</span>' : '';

        echo '<li class="dropdown messages-menu">';
            echo '<a href="'.BASEDIR.'messages.php" class="dropdown-toggle" data-toggle="dropdown">';
                echo '<i class="fa fa-envelope-o"></i>'.$msg_icon;
                echo '<span class="caret"></span>';
            echo '</a>';
            echo '<ul class="dropdown-menu">';
                echo '<li class="header text-center">'.$locale['ALT_001'].' '.format_word(count($messages), $locale['fmt_message']).'</li>';
                echo '<li><ul class="menu">';
                    if (!empty($messages)) {
                        foreach ($messages as $message) {
                            echo '<li>';
                                echo '<a href="'.BASEDIR.'messages.php?folder=inbox&amp;msg_read='.$message['link'].'">';
                                    echo '<div class="pull-left">';
                                        echo display_avatar($message['user'], '40px', '', FALSE, 'img-circle');
                                    echo '</div>';
                                    echo '<h4>';
                                        echo $message['user']['user_name'];
                                        echo '<small><i class="fa fa-clock-o"></i> '.$message['datestamp'].'</small>';
                                    echo '</h4>';
                                echo '</a>';
                            echo '</li>';
                        }
                    } else {
                        echo '<li class="text-center">'.$locale['ALT_002'].'</li>';
                    }

                echo '</ul></li>';
                echo '<li class="footer"><a href="'.BASEDIR.'messages.php?msg_send=new" class="text-bold">'.$locale['ALT_003'].'</a></li>';
            echo '</ul>';
        echo '</li>';
    }

    private function UserMenu() {
        $locale = fusion_get_locale();
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
        $useronline = $userdata['user_lastvisit'] >= time() - 900;

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

        echo '<ul id="adl" class="sidebar-menu" data-widget="tree">';
            foreach ($admin_sections as $i => $section_name) {
                $active = ((isset($_GET['pagenum']) && $_GET['pagenum'] == $i) || (!isset($_GET['pagenum']) && Admins::getInstance()->_isActive() == $i));

                if (!empty($admin_pages[$i])) {
                    echo '<li class="treeview'.($active ? ' active' : '').'">';
                        echo '<a href="#">';
                            echo Admins::getInstance()->get_admin_section_icons($i).' <span>'.$section_name.'</span>';
                            echo '<span class="pull-right-container">';
                                echo '<i class="fa fa-angle-left pull-right"></i>';
                                echo ($i > 4 ? '<small class="label pull-right bg-blue">'.count($admin_pages[$i]).'</small>' : '');
                            echo '</span>';
                        echo '</a>';
                        echo '<ul class="treeview-menu">';
                            foreach ($admin_pages[$i] as $key => $data) {
                                if (checkrights($data['admin_rights'])) {
                                    $sub_active = $data['admin_link'] == Admins::getInstance()->_currentPage();

                                    $title = $data['admin_title'];
                                    if ($data['admin_page'] !== 5) {
                                        $title = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $title;
                                    }

                                    $icon = '<img class="m-r-5" src="'.get_image('ac_'.$data['admin_rights']).'" alt="'.$title.'"/>';

                                    if (!empty($admin_pages[$data['admin_rights']])) {
                                        if (checkrights($data['admin_rights'])) {
                                            echo '<li class="treeview'.($sub_active ? ' menu-open' : '').'">';
                                                echo '<a href="#">'.$icon.' '.$title.'<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>';
                                                echo '<ul class="treeview-menu"'.($sub_active ? ' style="display: block;"' : '').'>';
                                                    foreach ($admin_pages[$data['admin_rights']] as $sub_page) {
                                                        echo '<li><a href="'.$sub_page['admin_link'].'">'.$sub_page['admin_title'].'</a></li>';
                                                    }
                                                echo '</ul>';
                                            echo '</li>';
                                        }
                                    } else {
                                        echo '<li'.($sub_active ? ' class="active"' : '').'><a href="'.ADMIN.$data['admin_link'].$aidlink.'">'.$icon.' '.$title.'</a></li>';
                                    }
                                }
                            }
                        echo '</ul>';
                    echo '</li>';
                } else {
                    echo '<li'.($active ? ' class="active"' : '').'><a href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum=0">';
                        echo Admins::getInstance()->get_admin_section_icons($i).' <span>'.$section_name.'</span>';
                    echo '</a></li>';
                }
            }
        echo '</ul>';
    }

    private function MainFooter() {
        global $_errorHandler;
        $locale = fusion_get_locale();

        echo '<footer class="main-footer">';
            if (iADMIN && checkrights('ERRO') && count($_errorHandler) > 0) {
                echo '<div>'.str_replace('[ERROR_LOG_URL]', ADMIN.'errors.php'.fusion_get_aidlink(), $locale['err_101']).'</div>';
            }

            if (fusion_get_settings('rendertime_enabled')) {
                echo showrendertime().' '.showMemoryUsage().'<br />';
            }

            echo '<strong>';
                echo 'AdminLTE Admin Theme &copy; '.date('Y').' '.$locale['ALT_006'].' <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a> ';
                echo $locale['and'].' <a href="https://adminlte.io" target="_blank">Almsaeed Studio</a>';
            echo '</strong>';
            echo '<br/>'.str_replace('<br />', ' | ', showcopyright());
        echo '</footer>';
    }

    private function ControlSidebar() {
        $locale = fusion_get_locale('', ALTE_LOCALE);

        OutputHandler::addToFooter('<script type="text/javascript" src="'.ADMINLTE.'js/control-sidebar.min.js"></script>');
        ?>
        <aside class="control-sidebar control-sidebar-dark">
            <div class="content">
                <h4 class="control-sidebar-heading"><?php echo $locale['ALT_008']; ?></h4>

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        <input type="checkbox" data-layout="fixed" class="pull-right"> <?php echo $locale['ALT_009']; ?>
                    </label>
                </div>

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        <input type="checkbox" data-layout="sidebar-collapse" class="pull-right"> <?php echo $locale['ALT_010']; ?>
                    </label>
                </div>

                <h4 class="control-sidebar-heading"><?php echo $locale['ALT_011']; ?></h4>
                <h5><?php echo $locale['ALT_012']; ?></h5>

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

                <h5><?php echo $locale['ALT_013']; ?></h5>

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

        <div class="control-sidebar-bg"></div>
        <?php
    }

    public static function Messages() {
        $userdata = fusion_get_userdata();

        $result = dbquery("
            SELECT message_id, message_subject, message_to, user_id, u.user_name, u.user_status, u.user_avatar, message_datestamp
            FROM ".DB_MESSAGES."
            INNER JOIN ".DB_USERS." u ON u.user_id=message_from
            WHERE message_to='".$userdata['user_id']."' AND message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'
            GROUP BY message_id
        ");

        if (dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'")) {
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    self::$messages[] = [
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

        return self::$messages;
    }

    public static function IsMobile() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER['HTTP_USER_AGENT']);
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
