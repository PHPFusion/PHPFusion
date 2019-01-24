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

class AdminPanel {
    private $messages = [];

    public function __construct() {
        $locale = fusion_get_locale('', MDT_LOCALE);

        OutputHandler::addToHead('<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Noto+Sans"/>');
        OutputHandler::addToFooter('<script src="'.INCLUDES.'jquery/jquery.cookie.js"></script>');
        OutputHandler::addToFooter('<script src="'.MDT.'assets/js/scripts.min.js"></script>');
        OutputHandler::addToHead('<link rel="stylesheet" href="'.MDT.'assets/mCustomScrollbar/jquery.mCustomScrollbar.min.css"/>');
        OutputHandler::addToFooter('<script src="'.MDT.'assets/mCustomScrollbar/jquery.mCustomScrollbar.min.js"></script>');
        OutputHandler::addToHead('<script src="'.MDT.'assets/js/jquery.mousewheel.min.js"></script>');
        OutputHandler::addToJquery('$(".sidebar, .sidebar-sm .admin-submenu, .sidebar-sm .search-box, .messages-box").mCustomScrollbar({theme: "minimal-dark", axis: "y", scrollInertia: 550, mouseWheel: {enable: !0, axis: "y", preventDefault: !0}});');

        echo '<main class="clearfix">';
            $this->TopMenu();
            $this->Sidebar();

            echo '<div class="content">';
                echo '<ul id="nav-sections" class="nav nav-tabs nav-justified hidden-lg" style="margin-bottom: 20px;">';
                    $sections = Admins::getInstance()->getAdminSections();
                    if (!empty($sections)) {
                        $i = 0;
                        foreach ($sections as $section_name) {
                            $active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && Admins::getInstance()->_isActive() == $i) ? ' class="active"' : '';
                            echo '<li'.$active.'><a href="'.ADMIN.'index.php'.fusion_get_aidlink().'&amp;pagenum='.$i.'"><span class="visible-xs">'.Admins::getInstance()->get_admin_section_icons($i).'</span><span class="hidden-xs">'.$section_name.'</span></a></li>';
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

                    echo 'Material Admin Theme &copy; '.date('Y').' '.$locale['material_013'].' <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a> | '.showcopyright('', TRUE);
                echo '</footer>';

                $errors = showFooterErrors();
                if ($errors) {
                    echo '<div class="errors fixed">'.$errors.'</div>';
                }
            echo '</div>';

            if (!$this->IsMobile()) {
                $this->MessagesBox();
                $this->ThemeSettings();
            }
        echo '</main>';

        echo '<div class="overlay"></div>';
    }

    private function TopMenu() {
        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $settings = fusion_get_settings();
        $userdata = fusion_get_userdata();

        echo '<div class="top-menu navbar fixed">';
            echo '<div class="toggleicon" data-action="togglemenu"><span></span></div>';
            echo '<div class="brand"><img src="'.IMAGES.'php-fusion-icon.png" alt="PHP Fusion 9"/> PHP Fusion 9</div>';
            echo '<div class="pull-right hidden-sm hidden-md hidden-lg home-xs"><a title="'.$settings['sitename'].'" href="'.BASEDIR.'index.php"><i class="fa fa-home"></i></a></div>';

            echo '<ul class="nav navbar-nav navbar-left hidden-xs hidden-sm hidden-md">';
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

            echo '<ul class="nav navbar-nav navbar-right hidden-xs">';
                $languages = fusion_get_enabled_languages();
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
                    echo '<a href="#" class="dropdown-toggle pointer" data-toggle="dropdown">'.display_avatar($userdata, '30px', '', FALSE, 'img-rounded').' <strong>'.$userdata['user_name'].'</strong><span class="caret"></span></a>';
                    echo '<ul class="dropdown-menu" role="menu">';
                        echo '<li><a href="'.BASEDIR.'edit_profile.php"><i class="fa fa-pencil fa-fw"></i> '.$locale['UM080'].'</a></li>';
                        echo '<li><a href="'.BASEDIR.'profile.php?lookup='.$userdata['user_id'].'"><i class="fa fa-eye fa-fw"></i> '.$locale['view'].' '.$locale['profile'].'</a></li>';
                        echo '<li class="divider"></li>';
                        echo '<li><a href="'.FUSION_REQUEST.'&amp;logout"><i class="fa fa-sign-out fa-fw"></i> '.$locale['admin-logout'].'</a></li>';
                        echo '<li><a href="'.BASEDIR.'index.php?logout=yes"><i class="fa fa-sign-out fa-fw"></i> <span class="text-danger">'.$locale['logout'].'</span></a></li>';
                    echo '</ul>';
                echo '</li>';

                $messages = $this->Messages();
                $messages = !empty($messages) ? '<span class="label label-danger messages">'.count($messages).'</span>' : '';

                if ($this->IsMobile()) {
                    echo '<li><a title="'.$locale['message'].'" href="'.BASEDIR.'messages.php"><i class="fa fa-envelope-o"></i>'.$messages.'</a></li>';
                } else {
                    echo '<li><a title="'.$locale['message'].'" href="#" data-action="messages"><i class="fa fa-envelope-o"></i>'.$messages.'</a></li>';
                }

                echo '<li><a title="'.$settings['sitename'].'" href="'.BASEDIR.'index.php"><i class="fa fa-home"></i></a></li>';
            echo '</ul>';
        echo '</div>';
    }

    private function Sidebar() {
        $locale = fusion_get_locale('', MDT_LOCALE);

        echo '<aside class="sidebar fixed">';
            echo '<div class="header fixed hidden-xs hidden-sm hidden-md">';
                echo '<div class="pf-logo"></div>';
                echo '<div class="version">PHP Fusion 9</div>';
            echo '</div>';

            echo '<div class="sidebar-menu">';
                echo '<div id="searchBox" data-action="search-box" style="display: none;"><a href="#"><i class="fa fa-search"></i></a></div>';
                echo '<div class="search-box">';
                    echo '<i class="fa fa-search input-search-icon"></i>';
                    echo '<input type="text" id="search_box" name="search_box" class="form-control" placeholder="'.$locale['material_001'].'"/>';
                    echo '<ul id="search_result" style="display: none;"></ul>';
                    echo '<img id="ajax-loader" style="width: 30px; display: none;" class="img-responsive center-x m-t-10" alt="Ajax Loader" src="'.IMAGES.'loader.svg"/>';
                echo '</div>';

                echo Admins::getInstance()->vertical_admin_nav(TRUE);
            echo '</div>';
        echo '</aside>';

        OutputHandler::addToJQuery('search_ajax("'.MDT.'acp_search.php'.fusion_get_aidlink().'");');
    }

    public function ThemeSettings() {
        $locale = fusion_get_locale('', MDT_LOCALE);

        echo '<aside id="theme-settings" class="hidden-xs">';
            echo '<a href="#" title="'.$locale['material_002'].'" data-action="theme-settings" class="btn-theme-settings cogs-animation">';
                echo '<i class="fa fa-cog fa-spin"></i>';
                echo '<i class="fa fa-cog fa-spin"></i>';
                echo '<i class="fa fa-cog fa-spin"></i>';
            echo '</a>';

            echo '<div class="settings-box">';
                echo '<h4>'.$locale['material_002'].'</h4>';

                echo '<ul class="settings-menu">';
                    $theme_settings = [
                        ['name' => 'hide-sidebar',      'title' => '003'],
                        ['name' => 'sidebar-sm',        'title' => '004'],
                        ['name' => 'fixedmenu',         'title' => '005', 'toggle' => 'on'],
                        ['name' => 'fixedsidebar',      'title' => '006', 'toggle' => 'on'],
                        ['name' => 'fixedfootererrors', 'title' => '007', 'toggle' => 'on'],
                        ['name' => 'fullscreen',        'title' => '008']
                    ];

                    foreach ($theme_settings as $setting) {
                        echo '<li><a href="#" data-action="'.$setting['name'].'" id="'.$setting['name'].'">'.$locale['material_'.$setting['title']].'<div class="btn-toggle pull-right '.(!empty($setting['toggle']) ? $setting['toggle'] : '').'"></div></a></li>';
                    }
                echo '</ul>';
            echo '</div>';
        echo '</aside>';
    }

    public function MessagesBox() {
        $locale = fusion_get_locale('', MDT_LOCALE);

        echo '<aside class="messages-box hidden-xs">';
            echo '<div class="button-group">';
                echo '<a href="#" id="messages-box-close">'.fusion_get_locale('close').'</a>';
                echo '<a href="'.BASEDIR.'messages.php?msg_send=new" class="new-message">'.$locale['material_011'].'</a>';
            echo '</div>';
            echo '<h3 class="title">'.$locale['material_009'].'</h3>';

            $messages = $this->GetMessages();
            if (!empty($messages)) {
                echo '<ul>';
                    foreach ($messages as $message) {
                        echo '<li>';
                            echo '<div class="message-block">';
                                echo display_avatar($message['user'], '40px', '', FALSE, 'avatar m-r-5');
                                echo '<div class="block">';
                                    echo '<span class="title">'.$message['user']['user_name'].' <small>'.$message['datestamp'].'</small></span>';
                                    echo '<br /><small>'.trim_text($message['title'], 20).'</small>';
                                    echo '<a href="'.BASEDIR.'messages.php?folder=inbox&amp;msg_read='.$message['link'].'" class="read-message">'.$locale['material_010'].'</a>';
                                echo '</div>';
                            echo '</div>';
                        echo '</li>';
                    }
                echo '</ul>';
            } else {
                echo '<div class="no-messages">';
                    echo '<i class="fa fa-envelope icon"></i><br />';
                    echo $locale['material_012'];
                echo '</div>';
            }
        echo '</aside>';
    }

    public function Messages() {
        $userdata = fusion_get_userdata();

        $result = dbquery("
            SELECT message_id, message_subject, message_from user_id, u.user_name, u.user_status, u.user_avatar, message_datestamp
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
}
