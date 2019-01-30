<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: functions.php
| Author: Hien (Frederick MC Chan)
| Author: Falk (Jocke Falk)
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

$license = showcopyright();

// set_image("pollbar", THEME."images/blank.gif");
set_image("edit", THEME."images/edit.png");
set_image("printer", THEME."images/printer.png");
set_image("link", THEME."images/link.png");

//Arrows
set_image("up", THEME."images/up.png");
set_image("down", THEME."images/down.png");
set_image("left", THEME."images/left.png");
set_image("right", THEME."images/right.png");

//Forum folders icons
set_image("folder", THEME."forum/folder.png");
set_image("foldernew", THEME."forum/foldernew.png");
set_image("folderlock", THEME."forum/folderlock.png");
set_image("stickythread", THEME."forum/stickythread.png");

//Forum buttons
set_image("reply", THEME."forum/reply.gif");
set_image("newthread", THEME."forum/newthread.gif");
set_image("web", THEME."forum/web.gif");
set_image("pm", THEME."forum/pm.gif");
set_image("quote", THEME."forum/quote.gif");
set_image("forum_edit", THEME."forum/edit.gif");

// Atom X Navigation
function horizontalnav() {
    global $settings, $userdata, $locale, $aidlink, $menu_item;

    $action_url = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");

    if (isset($_GET['redirect']) && strstr($_GET['redirect'], "/")) {
        $action_url = cleanurl(urldecode($_GET['redirect']));
    }

    $html = "";
    $html .= "<div class='navbar-atom m-t-15'>";
    $html .= "<div class='navbar-header'>\n";
    $html .= "<button type='button' class='navbar-toggle' data-toggle='collapse' data-target='#navbar-atom'>
              <span class='sr-only'>Toggle Navigation</span>
              <span class='icon-bar'></span>
              <span class='icon-bar'></span>
              <span class='icon-bar'></span>
            </button>";
    $html .= "</div>\n";

    $html .= "<div id='navbar-atom' class='navbar-collapse collapse'>\n";

    $result = dbquery(
        "SELECT link_name, link_url, link_window, link_visibility FROM ".DB_SITE_LINKS."
        ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")."
         link_position='3' ".(SUBNAV ? "" : " OR link_position='2' ".(multilang_table("SL") ? "AND link_language='".LANGUAGE."'" : "")."")." ORDER BY link_order"
    );

    $result = dbquery("SELECT * FROM ".DB_SITE_LINKS." WHERE ".groupaccess('link_visibility')." ".(multilang_table("SL") ? "AND link_language='".LANGUAGE."' AND" : "AND")." link_position='3' OR link_position='2' ".(multilang_table("SL") ? "AND link_language='".LANGUAGE."'" : "")."  ORDER BY link_order ASC");

    $html .= "<ul class='nav navbar-nav text-left' >\n";
    if (dbrows($result) > 0) {
        $i = 0;
        while ($data = dbarray($result)) {
            $li_class = '';
            $link_target = ($data['link_window'] == "1" ? " target='_blank'" : "");
            if (!strstr($data['link_url'], "http://") && !strstr($data['link_url'], "https://")) {
                $data['link_url'] = BASEDIR.$data['link_url'];
            }
            if ($data['link_url'] != "---" && checkgroup($data['link_visibility'])) {

                $li_class = preg_match("/^".preg_quote(START_PAGE, '/')."/i", $data['link_url']) ? "current_page_item" : "";

            }
            if (strstr($data['link_name'], "%submenu%") && SUBNAV) {
                $html .= "<li class='$li_class dropdown' >\n<a href='".$data['link_url']."' class='dropdown-toggle' data-toggle='dropdown'><span>".parseubb(str_replace("%submenu% ", "", $data['link_name']), "b|i|u|color")."</span> <b class='caret'></b></a>\n<ul class='dropdown-menu' role='menu' aria-labelledby='".$data['link_name']."' >\n";
            } else if (strstr($data['link_name'], "%endmenu% ") && SUBNAV) {
                $html .= "<li class='$li_class'><a href='".$data['link_url']."' $link_target><span>".parseubb(str_replace("%endmenu% ", "", $data['link_name']), "b|i|u|color")."</span></a></li>\n</ul>\n</li>\n";
            } else if (strstr($data['link_name'], "%ssmenu%") && SUBNAV) {
                $html .= "<li class='$li_class'><a href='".$data['link_url']."' class='dropdown-toggle' data-toggle='dropdown' ><span >".parseubb(str_replace("%ssmenu% ", "", $data['link_name']), "b|i|u|color")."</span> <b class='caret'></b></a>\n<ul class='dropdown-menu sub-menu' >\n";
            } else if (strstr($data['link_name'], "%endssmenu% ") && SUBNAV) {
                $html .= "<li class='$li_class' ><a href='".$data['link_url']."' $link_target><span>".parseubb(str_replace("%endssmenu% ", "", $data['link_name']), "b|i|u|color")."</span></a>\n</li>\n</ul>\n</li>\n";
            } else {
                if (strstr($data['link_name'], "---")) {
                    $html .= "<li class='divider' ></li>\n";
                } else if (strstr($data['link_name'], "%head%")) {
                    $html .= "<li class='dropdown-header' role='presentation' >\n<span>".parseubb(str_replace("%head%", "", $data['link_name']), "b|i|u|color")."</span>\n</li>\n";
                } else {
                    $html .= "<li class='$li_class' >\n<a href='".$data['link_url']."' $link_target><span>".parseubb($data['link_name'], "b|i|u|color")."</span></a>\n</li>\n";
                }
            }
            $i++;
        }
    } else {
        $html .= "<li>".$locale['ax8_01']."</li>\n";
    }
    $html .= "</ul>\n";
    $html .= "</div></div></div>";
    return $html;
}

function user_login() {
    global $locale, $userdata, $aidlink, $settings;

    if (iMEMBER) {
        $name = "".$locale['welcome'].", ".ucfirst($userdata['user_name']);
    } else {
        $name = $locale['login']." / ".$locale['register'];
    }

    $html = "<ul class='nav navbar-nav pull-right m-r-20'>";

    //Search bar (Courtesy iTheme II)
    $locale['search'] = str_replace($locale['global_200'], "", $locale['global_202']);

    $html .= "<li id='user-info' class='dropdown' >\n";
    $html .= "<button type='button' class='btn btn-primary btn-sm dropdown-toggle' data-toggle='dropdown' style='margin-top: 8px;' >$name <span class='caret'></span></button>";
    if (iMEMBER) {
        $html .= "<ul class='dropdown-menu text-left'>";
        $html .= "<li><a href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'>".$locale['view']." ".$locale['profile']."</a></li>";
        $html .= "<li><a href='".BASEDIR."messages.php'>".$locale['global_121']."</a></li>";
        $html .= "<li class='divider'></li>";
        $html .= "<li class='dropdown-header' role='presentation'>".$locale['settings']."</li>";
        $html .= "<li><a href='".BASEDIR."edit_profile.php'>".$locale['edit']." ".$locale['profile']."</a></li>";
        $html .= (iADMIN) ? "<li><a href='".ADMIN."index.php$aidlink'>".$locale['global_123']."</a></li>\n" : "";
        $html .= "<li class='divider'></li>";
        $html .= "<li><a href='".BASEDIR."index.php?logout=yes'>".$locale['global_124']."</a></li>";
        $html .= "</ul>";
        $html .= "</li>";

    } else {

        $action_url = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");

        if (isset($_GET['redirect']) && strstr($_GET['redirect'], "/")) {
            $action_url = cleanurl(urldecode($_GET['redirect']));
        }

        $html .= "<ul class='dropdown-menu login-menu text-left'>\n";
        $html .= "<li class='dropdown-header' role='presentation'>\n </li>\n";
        $html .= "<li>\n";

        $html .= "<form name='loginform' method='post' action='$action_url' >\n";
        $html .= "<input type='text' id='username' name='user_name' class='form-control m-b-10 input-sm' placeholder='".$locale['global_101']."' >\n";
        $html .= "<input type='password' id='user_pass' name='user_pass' class='form-control m-b-10 input-sm' placeholder='".$locale['global_102']."' >\n";
        $html .= "<label><input type='checkbox' name='remember_me' value='y' title='".$locale['global_103']."' style='vertical-align:top; margin-right:5px;' > ".$locale['global_103']."</label>\n";
        $html .= "<button type='submit' name='login' class='m-t-10 m-b-10 btn btn-primary btn-sm' >".$locale['global_104']."</button> <br >\n";
        $html .= "</form>\n";

        if ($settings['enable_registration']) {
            $html .= "<li><a href='".BASEDIR."register.php'>".$locale['register']."</a></li>\n";
        }
        $html .= "<li><a href='".BASEDIR."lostpassword.php'>".$locale['global_108']."</a></li>\n";
        $html .= "</li>\n</ul>\n";
    }
    add_to_footer("<script type='text/javascript'>$('.dropdown-menu input, .dropdown-menu label').click(function(e) {e.stopPropagation();});</script>");
    $html .= "</ul>";

    $html .= "<div style='margin-top:7px;' class='pull-right m-r-15'>
    <form action='".BASEDIR."search.php' id='searchform' method='get'>
    <input type='text' class='textbox' onblur='if (this.value == \"\") {this.value = \"".$locale['search']."...\";}' onfocus='if (this.value == \"".$locale['search']."...\") {this.value = \"\";}' id='stext' name='stext' value='".$locale['search']."...' />
    </form></div>";
    return $html;
}

function user_info_bar($data) {
    global $settings, $userdata, $locale;

    add_to_head("<link href='".THEME."tpl/tpl_css/profile.css' rel='stylesheet' media='screen'>");
    require_once LOCALE.LOCALESET."user_fields.php";
    require_once LOCALE.LOCALESET."user_fields/user_location.php";
    require_once LOCALE.LOCALESET."user_fields/user_comments-stat.php";
    require_once LOCALE.LOCALESET."user_fields/user_forum-stat.php";
    require_once LOCALE.LOCALESET."user_fields/user_shouts-stat.php";

    if (iMEMBER) {
        $uf_query = dbquery(
            "SELECT * FROM ".DB_USER_FIELDS." tuf
                INNER JOIN ".DB_USER_FIELD_CATS." tufc ON tuf.field_cat = tufc.field_cat_id
                ORDER BY field_cat_order, field_order"
        );
        $i = 0;
        if (dbrows($uf_query)) {
            while ($data = dbarray($uf_query)) {
                if ($i != $data['field_cat']) {
                    $i = $data['field_cat'];
                    $cats[$i] = [
                        "field_cat_name" => $data['field_cat_name'],
                        "field_cat"      => $data['field_cat']
                    ];
                }
                $fields[$i][] = (array_key_exists($data['field_name'], $userdata)) ? ['field_name' => $data['field_name'], 'value' => $userdata[$data['field_name']]] : ['field_name' => $data['field_name'], 'value' => 'N/A'];
            }
        }
        $avatar = ($userdata['user_avatar'] && file_exists(IMAGES."avatars/".$userdata['user_avatar'])) ? "<img class='img-rounded' src='".IMAGES."avatars/".$userdata['user_avatar']."' style='max-width:50px;' />" : "<img style='max-width:50px;' src='".IMAGES."avatars/noavatar2.png' />";
    } else {
        $avatar = "<img src='".IMAGES."avatars/noavatar100.png' style='max-width:50px;' />";
    }
    $html = "";
    // user stats and groups.
    if (iMEMBER) {
        $html .= "<div class='profile-center'>\n";
        $html .= "<div class='user-details'>\n";
        $html .= "<div class='pull-left m-l-20 m-r-8' style='margin-top:5px; position:absolute; z-index:9 '>\n $avatar \n</div>\n";
        $html .= "<div class='pull-left' style='margin-left:90px;'>\n";
        $html .= "<ul class='nav user-stats-bar'>\n";
        $html .= "<li class='dropdown'><a class='dropdown-toggle' data-toggle='dropdown' href='#'>\n";
        $html .= "<h4>".$userdata['user_name']." <b class='caret'></b></h4> <span><small>".getuserlevel($userdata['user_level'])."</small></span>\n</a>";
        $html .= "<ul class='dropdown-menu' style='width:400px;'><li>\n";
        $html .= "<p><strong>Fusioneer ".timer($userdata['user_joined'])."</strong>\n</p>\n";
        $html .= "<p class='pull-left' style='width:180px;'>\n";
        $html .= "<small>\n";
        $html .= "<strong>".$locale['u066']."</strong>: ".showdate("shortdate", $userdata['user_joined'])."</strong><br />\n";
        $lastVisit = ($userdata['user_lastvisit']) ? showdate("shortdate", $userdata['user_lastvisit']) : $locale['u042'];
        $html .= "<strong>".$locale['u067']."</strong>: $lastVisit<br />";
        $html .= "<strong>".$locale['uf_location']."</strong>: ".(($userdata['user_location']) ? $userdata['user_location'] : $locale['ax8_02'])."</strong>";
        $html .= "</small>\n";
        $html .= "</p>\n";
        $html .= "<p class='pull-left' style='width:100px;'>\n";
        $html .= "<small><strong>".$locale['u047']."</strong></small><br />\n";
        $html .= "<small>\n";
        $html .= "<strong>".$locale['uf_comments-stat']."</strong> : ".number_format(dbcount("(comment_id)", DB_COMMENTS, "comment_name='".$userdata['user_id']."'"))."<br />\n";
        $html .= "<strong>".$locale['uf_forum-stat']."</strong> : ".number_format($userdata['user_posts'])."<br />\n";
        $html .= "<strong>".$locale['u049']."</strong> : ".(($userdata['user_ip_type'] == '4') ? $userdata['user_ip'] : 'Local IP')."\n";
        $html .= "</small>\n";
        $html .= "</p>\n";
        $html .= "</li>\n</ul>\n</li></ul>\n";
        $html .= "\n</div>\n";
        $_pull = '';
        $_pull_width = '30%';
        if (iMEMBER) {
            // go to profile page.
            $html .= "<div class='user-fields user-icons hidden-xs hidden-sm hidden-md'><a style='border-left: 1px solid rgba(0,0,0,0.23);' href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'><i class='entypo user'></i></a>\n</div>\n";
            // private message
            // pm
            $message_count = dbcount('("message_id")', DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_read='0'");
            $message_count = ($message_count > 0) ? $message_count : '0';
            $html .= "<div class='user-fields hidden-xs hidden-sm hidden-md' style='margin:0px'>
            <ul class='nav user-stats-bar'>
            <li class='dropdown'><a class='icon dropdown-toggle' data-toggle='dropdown' href='#'><i class='entypo mail'></i> ".(($message_count) ? "<span class='label label-danger' style='font-size: 11px;color: #fff;position: absolute;top: 5px;right: 18px;'>$message_count</span>" : '')." <b class='caret'></b>\n</a>";
            $html .= "<ul class='dropdown-menu' style='width:280px; padding-top:0px;'>\n";
            $html .= "<li style='padding-bottom:0px; background: url(".THEME."images/pm_header.jpg); height:45px; padding-left:60px; padding-top:15px; color:#fff'><strong><a href='".BASEDIR."messages.php' style='color:#fff; line-height:5px; margin-bottom:0px;'>".$locale['global_121']."</a></strong>\n";

            $html .= "</li>\n";
            $get_latest_mail = dbquery("SELECT * FROM ".DB_MESSAGES." WHERE message_to='".$userdata['user_id']."' AND message_read='0' ORDER BY message_datestamp DESC LIMIT 0,5");
            if (dbrows($get_latest_mail) > 0) {
                $i = 0;
                while ($maildata = dbarray($get_latest_mail)) {
                    $html .= "<a href='".BASEDIR."messages.php?folder=inbox&msg_read=".$maildata['message_id']."'><li style='padding:3px 10px; ".($i > 0 ? 'border-top:1px dashed rgba(0,0,0,0.1);' : '')."'><span class='pull-right' style='font-weight:normal;'><small>".timer($maildata['message_datestamp'])."</small></span><br /><span style='color:#222; font-size:12px; font-weight:bold;'>".$maildata['message_subject']."</span></li></a>\n";
                    $i++;
                }
            } else {
                $html .= "<li style='padding:10px 10px;' class='text-center'><small>".sprintf($locale['UM085'], $message_count)." ".($message_count > 1 ? $locale['global_126'] : $locale['global_127']).".</small></li>\n";
                $html .= "<a href='".BASEDIR."messages.php'><li style='padding:10px 10px; border-top:1px dashed rgba(0,0,0,0.1);' class='text-left text-center'><span style='font-size:12px; color:#222; font-weight:bold'>".$locale['enter']." ".$locale['message']."</span></li></a>\n";
            }

            $html .= "</ul>\n</li></ul>\n";
            $html .= "\n</div>\n";
            // user groups
            $html .= "<div class='user-fields hidden-xs hidden-sm hidden-md' style='margin:0px'>
                <ul class='nav user-stats-bar'>
                <li class='dropdown'><a class='icon dropdown-toggle' data-toggle='dropdown' href='#' ><i class='entypo users'></i>\n <b class='caret'></b></a>";
            $html .= "<ul class='dropdown-menu'><li>\n";
            $html .= "<p><small><strong>".$locale['u057']."</strong></small>\n</p>\n";
            $user_groups = strpos($userdata['user_groups'], ".") == 0 ? substr($userdata['user_groups'], 1) : $userdata['user_groups'];
            $user_groups = explode(".", $user_groups);
            if (!empty($user_groups['0'])) {
                for ($i = 0; $i < count($user_groups); $i++) {
                    $html .= "<p><span><a href='".BASEDIR."profile.php?group_id=".$user_groups[$i]."'>".getgroupname($user_groups[$i])."</a></span> : ".getgroupname($user_groups[$i], TRUE)."</p>\n";
                }
            } else {
                $html .= "<p><span>".$locale['no']." ".$locale['u057']."</p>\n";
            }
            $html .= "</li>\n</ul>\n</li></ul>\n";
            $html .= "\n</div>\n";
        }
        $html .= "</div>\n";
        $html .= "</div>\n";
    } else {
        $_pull = 'pull-right';
        $_pull_width = '65%';
    }
    return $html;
}
