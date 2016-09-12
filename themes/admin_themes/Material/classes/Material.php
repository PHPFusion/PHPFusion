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

class Material extends Dashboard {
    public static function AddTo() {
        add_to_head('<script type="text/javascript" src="'.MATERIAL.'assets/js/scripts.js"></script>');
        add_to_head('<link rel="stylesheet" href="'.MATERIAL.'assets/scrollbar/jquery.mCustomScrollbar.css"/>');
        add_to_footer('<script type="text/javascript" src="'.MATERIAL.'assets/scrollbar/jquery.mCustomScrollbar.min.js"></script>');
        add_to_footer('<script type="text/javascript">$(".sidebar").mCustomScrollbar({theme: "minimal-dark", axis: "yx", scrollInertia: 100, mouseWheel: {enable: !0, axis: "y", preventDefault: !0},});</script>');
    }

    public static function Login() {
        $locale   = fusion_get_locale();
        $userdata = fusion_get_userdata();
        $aidlink  = fusion_get_aidlink();
      
        add_to_head('<style type="text/css">body{background: #2c3e50;}</style>');
        add_to_jquery('$("#admin_password").focus();');
        
        echo '<div class="login-bg">';
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
                        echo form_text('admin_password', '', '', array(
                                                                   'callback_check'   => 'check_admin_pass',
                                                                   'placeholder'      => $locale['281'],
                                                                   'error_text'       => $locale['global_182'],
                                                                   'autocomplete_off' => TRUE,
                                                                   'type'             => 'password',
                                                                   'required'         => TRUE
                                                                ));
                        echo form_button('admin_login', $locale['login'], $locale['login'], array('class' => 'btn-primary btn-block'));
                    echo closeform();
                echo '</div>';
                
                echo '<div class="copyright clearfix m-t-10 text-left">';
                    echo 'Material Admin Theme &copy; '.date("Y").' created by <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a><br/>';
                    echo showcopyright();
                echo '</div>';
            echo'</div>';
        echo '</div>';
    }

    public static function AdminPanel() {
        $admin     = new Admin();
        $sections  = $admin->getAdminSections();
        $aidlink   = fusion_get_aidlink();

        echo '<main class="clearfix">';
            self::Sidebar();
            self::TopMenu();
            
            echo '<div class="content">';
                echo '<ul class="nav nav-tabs nav-justified hidden-lg" style="margin-bottom: 20px;">';
                    if (!empty($sections)) {
                    $i = 0;
                    foreach ($sections as $section_name) {
                        $active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && $admin->_isActive() == $i) ? ' class="active"' : '';
                        echo "<li".$active."><a href='".ADMIN."index.php".$aidlink."&amp;pagenum=".$i."'>".$section_name."</a></li>\n";
                        $i++;
                    }
                    }
                echo '</ul>';
            
                echo render_breadcrumbs();
            
                echo renderNotices(getNotices());
            
                echo CONTENT;
            
                echo '<footer class="copyright">';
                    if (fusion_get_settings("rendertime_enabled")) {
                        echo showrendertime().showMemoryUsage().'<br />';
                    }
                
                    echo 'Material Admin Theme &copy; '.date("Y").' created by <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a> | '.str_replace('<br />', ' | ', showcopyright());
                echo '</footer>';
            echo '</div>'; // .content
            
            $errors = showFooterErrors();
            if ($errors) {
                add_to_footer('<script type="text/javascript">$("#close-errors").click(function(){$(".errors").fadeOut(200,function(){$(this).css("display", "none");});});</script>');
                echo '<div class="errors hidden-xs hidden-sm hidden-md">'.$errors.' <span id="close-errors" class="close pull-right">&times;</span></div>';
            }
        echo '</main>';
    }

    public static function Messages() {
        $userdata  = fusion_get_userdata();
        $messages_count = dbquery("SELECT
            SUM(message_folder=0) AS inbox_count,
            SUM(message_folder=1) AS outbox_count,
            SUM(message_folder=2) AS archive_count,
            SUM(message_read=0 AND message_folder=0) AS unread_count
            FROM ".DB_MESSAGES." 
            WHERE message_to='".$userdata['user_id']."'
        ");
        $messages_count = dbarray($messages_count);
            
        return $messages_count;
    }

    public static function Sidebar() {
        $admin  = new Admin();

        echo '<aside class="sidebar">';
            echo '<div class="header hidden-xs hidden-sm hidden-md">';
                echo '<div class="pf-logo"></div>';
                echo '<div class="version">PHP Fusion 9</div>';
            echo '</div>';

            echo '<div class="sidebar-menu">';
                echo '<div class="search-box">';
                    echo '<input type="text" id="search_box" name="search_box" class="form-control" placeholder="'.fusion_get_locale('402', LOCALE.LOCALESET.'search.php').'"/>';
                    echo '<ul id="search_result" style="display: none;"></ul>';
                echo '</div>';

                echo $admin->vertical_admin_nav(TRUE);
            echo '</div>';
        echo '</aside>';

        add_to_jquery("
            $('#search_box').bind('keyup', function(e) {
                var data = {
                    'pagestring': $(this).val(),
                    'url': '".$_SERVER['REQUEST_URI']."',
                };
                var sendData = $.param(data);
                $.ajax({
                    url: '".MATERIAL."search.php".fusion_get_aidlink()."',
                    dataType: 'html',
                    method: 'get',
                    data: sendData,
                    success: function(e) {
                        if ($('#search_box').val() == '') {  
                            $('#adl').show();
                            $('#search_result').html(e).hide();
                            $('#search_result li').html(e).hide();
                        } else {
                            $('#adl').hide();
                            $('#search_result').html(e).show();
                        }
                    }
                });
            });
        ");
    }

    public static function TopMenu() {
        $admin     = new Admin();
        $sections  = $admin->getAdminSections();
        $locale    = fusion_get_locale();
        $aidlink   = fusion_get_aidlink();
        $userdata  = fusion_get_userdata();
        $languages = fusion_get_enabled_languages();
        $messages  = self::Messages();
        $messages  = !empty($messages['unread_count']) ? '<span class="label label-danger messages">'.$messages['unread_count'].'</span>' : '';

        echo '<div class="top-menu navbar">';
            echo '<div class="toggleicon" data-action="togglemenu"><span></span></div>';
            echo '<div class="brand"><img src="'.IMAGES.'php-fusion-icon.png" alt="PHP Fusion 9"/> PHP Fusion 9</div>';
            echo '<div class="pull-right hidden-sm hidden-md hidden-lg home-xs"><a title="'.fusion_get_settings('sitename').'" href="'.BASEDIR.'index.php"><i class="fa fa-home"></i></a></div>';
                
            echo '<ul class="nav navbar-nav navbar-left hidden-xs hidden-sm hidden-md">';
                if (!empty($sections)) {
                    $i = 0;
                        
                    foreach ($sections as $section_name) {
                        $active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && $admin->_isActive() == $i) ? ' class="active"' : '';
                        
                        echo '<li'.$active.'><a href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum='.$i.'" data-toggle="tooltip" data-placement="bottom" title="'.$section_name.'">'.$admin->get_admin_section_icons($i).'</a></li>';
                        
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
                                echo '<li><a class="display-block" href="'.clean_request('lang='.$language_folder, array('lang'), FALSE).'"><img class="m-r-5" src="'.BASEDIR.'locale/'.$language_folder.'/'.$language_folder.'-s.png" alt="'.$language_folder.'"/> '.$language_name.'</a></li>';
                            }
                        echo '</ul>';
                    echo '</li>';
                }

                echo '<li class="dropdown user-s">';
                    echo '<a href="#" class="dropdown-toggle pointer" data-toggle="dropdown">'.display_avatar($userdata, '30px', '', FALSE, 'avatar').' '.$locale['logged'].' <strong>'.$userdata['user_name'].'</strong><span class="caret"></span></a>';
                    echo '<ul class="dropdown-menu" role="menu">';
                        echo '<li><a class="display-block" href="'.BASEDIR.'edit_profile.php">'.$locale['UM080'].'</a></li>';
                        echo '<li><a class="display-block" href="'.BASEDIR.'profile.php?lookup='.$userdata['user_id'].'">'.$locale['view'].' '.$locale['profile'].'</a></li>';
                        echo '<li class="divider"></li>';
                        echo '<li><a class="display-block" href="'.FUSION_REQUEST.'&amp;logout">'.$locale['admin-logout'].'</a></li>';
                        echo '<li><a class="display-block" href="'.BASEDIR.'index.php?logout=yes">'.$locale['logout'].'</a></li>';
                        echo '</ul>';
                    echo '</li>'; // .dropdown
                    
                    echo '<li><a data-toggle="tooltip" data-placement="bottom" title="'.$locale['settings'].'" href="'.ADMIN.'settings_main.php'.$aidlink.'"><i class="fa fa-cog"></i></a></li>';
                    echo '<li><a data-toggle="tooltip" data-placement="bottom" title="'.$locale['message'].'" href="'.BASEDIR.'messages.php"><i class="fa fa-envelope-o"></i>'.$messages.'</a></li>';
                    echo '<li><a data-toggle="tooltip" data-placement="bottom" title="'.fusion_get_settings('sitename').'" href="'.BASEDIR.'index.php"><i class="fa fa-home"></i></a></li>';
            echo '</ul>'; 
        echo '</div>';
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
        echo '<div class="panel panel-default '.$class.'">';
        echo $title ? '<header><h3>'.$title.'</h3></header>' : '';
        echo '<div class="panel-body">';
    }

    public static function CloseTable() {
        echo '</div>';
        echo '</div>';
    }
}
