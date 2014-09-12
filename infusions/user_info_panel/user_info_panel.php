<?php
    /*-------------------------------------------------------+
    | PHP-Fusion Content Management System
    | Copyright (C) PHP-Fusion Inc
    | http://www.php-fusion.co.uk/
    +--------------------------------------------------------+
    | Filename: user_info_panel.php
    | Author: Nick Jones (Digitanium)
    +--------------------------------------------------------+
    | This program is released as free software under the
    | Affero GPL license. You can redistribute it and/or
    | modify it under the terms of this license which you
    | can read by viewing the included agpl.txt or online
    | at www.gnu.org/licenses/agpl.html. Removal of this
    | copyright header is strictly prohibited without
    | written permission from the original author(s).
    +--------------------------------------------------------*/

    if (!defined("IN_FUSION")) { header("Location:../../index.php"); exit; }

    // Check if a locale file is available that match the selected locale.
    if (file_exists(INFUSIONS."user_info_panel/locale/".LANGUAGE.".php")) {
        // Load the locale file matching selection.
        include INFUSIONS."user_info_panel/locale/".LANGUAGE.".php";
    } else {
        // Load the default locale file.
        include INFUSIONS."user_info_panel/locale/English.php";
    }

    if (!defined('bootstrapped')) {
        // if used on a theme without bootstrap, load automatically.
        load_bootstrap();
    }

    if (iMEMBER) {
        if (preg_match('/administration/i', $_SERVER['PHP_SELF'])) {
            opensidex($locale['WWOLD_001'].$userdata['user_name'], "off");
        } else {
            openside($locale['WWOLD_001'].$userdata['user_name']);
        }

        $inbox_count =  dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_folder='0'");
        $outbox_count =  dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_folder='1'");
        $archive_count =  dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_folder='2'");

        // Avatar
        echo "<div class='avatar-row'>\n";
        echo "<div class='pull-left m-r-10'>\n";
        echo display_avatar($userdata, '90px');
        echo "</div>\n";
        echo "<div class='clearfix'>\n";
        echo "<h4 class='m-t-10 m-b-0'><strong>".ucwords($userdata['user_name'])."</strong></h4>\n";
        echo "<small>".getuserlevel($userdata['user_level'])."</small>\n<br/>";
        // sigh go for notification icons.
        echo "<ul class='user-info-bar'>\n";
        $msg_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'");
        echo ($msg_count) ? "<li><a href='".BASEDIR."messages.php?folder=inbox' title='".sprintf($locale['UM085'], $msg_count).($msg_count == 1 ? $locale['UM086'] : $locale['UM087'])."' ><i class='entypo icomment'></i><label style='position:absolute; margin-left:-20px;' class='pointer label label-danger'>$msg_count</label></a>\n</li>\n" : "";
        echo "<li></li>\n";
        echo "<li></li>\n";
        echo "</ul>\n";
        echo "</div>\n";
        echo "</div>\n";

        // inbox percentage full issit.. sigh.
        $result = dbquery("SELECT * FROM ".DB_PREFIX."messages_options WHERE user_id='0'");
        $data = dbarray($result);
        $inbox_cfg = ($data['pm_inbox']!=0 ? $data['pm_inbox'] : 1);
        $inbox_percent = number_format(($inbox_count/$inbox_cfg)*99, 0);
        echo progress_bar($inbox_percent, $locale['WWOLD_940']);
        $outbox_cfg = ($data['pm_sentbox']!=0 ? $data['pm_sentbox'] : 1);
        $outbox_percent = number_format(($outbox_count/$outbox_cfg)*99, 0);
        echo progress_bar($outbox_percent, $locale['WWOLD_941']);
        $archive_cfg = ($data['pm_savebox']!=0 ? $data['pm_savebox'] : 1);
        $archive_percent = number_format(($archive_count/$archive_cfg)*99, 0);
        echo progress_bar($archive_percent, $locale['WWOLD_942']);

        echo "<h5><strong>".$locale['WWOLD_003']."</strong></h5>\n";
        echo lang_switcher();


        echo "<div id='navigation'>\n";
        echo "<h5><strong>".$locale['WWOLD_002']."</strong></h5>\n";
        echo "<hr class='side-hr'>\n";
        echo "<ul>\n";
        echo "<li><a class='side' href='".BASEDIR."edit_profile.php'>".$locale['UM080']." <i class='pull-right entypo suitcase'></i></a></li>\n";
        echo "<li><a class='side' href='".BASEDIR."messages.php'>".$locale['UM081']." <i class='pull-right entypo mail'></i></a></li>\n";
        echo "<li><a class='side' href='".INFUSIONS."forum_threads_list_panel/my_tracked_threads.php'>".$locale['UM088']." <i class='pull-right entypo eye'></i></a></li>\n";
        echo "<li><a class='side' href='".BASEDIR."members.php'>".$locale['UM082']." <i class='pull-right entypo users'></i></a></li>\n";
        echo (iADMIN) ? "<li><a class='side' href='".ADMIN."index.php".$aidlink."'>".$locale['UM083']." <i class='pull-right entypo cog'></i></a></li>\n" : '';
        echo "<li><a class='side' href=\"javascript:show_hide('ShowHide001')\">".$locale['WWOLD_101']." <i class='pull-right entypo upload-cloud'></i></a></li>\n";
        echo "<li>\n";
        echo "<div id='ShowHide001' style='display:none'><a class='side' href='".BASEDIR."submit.php?stype=n'>".$locale['WWOLD_102']."</a>";
	    echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=l'>".$locale['WWOLD_103']."</a>";
        echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=a'>".$locale['WWOLD_104']."</a>";
        echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=p'>".$locale['WWOLD_105']."</a>";
        echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=f'>".$locale['WWOLD_106']."</a>";
        echo "</div>\n";
        echo "</li>\n";
        echo "</ul>\n";
        echo "</div>\n";

        echo "<div class='m-t-20'>\n";
        echo "<a class='btn btn-block btn-primary' href='".BASEDIR."setuser.php?logout=yes'>".$locale['UM084']."</a>\n";
        echo "</div>\n";

        if (preg_match('/administration/i', $_SERVER['PHP_SELF'])) {
            closesidex();
        } else {
            closeside();
        }

    } else {
        //in visitor
        if (!preg_match('/login.php/i',FUSION_SELF)) {
            $action_url = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
            if (isset($_GET['redirect']) && strstr($_GET['redirect'], "/")) {
                $action_url = cleanurl(urldecode($_GET['redirect']));
            }

            openside($locale['global_100']);
            echo "<h5><strong>".$locale['WWOLD_003']."</strong></h5>";
            echo lang_switcher();
            echo "<div class='m-t-10'>\n";
            //echo "<form name='loginform' method='post' action='".$action_url."'>\n";
            echo openform('loginform', 'loginform', 'post', $action_url, array('downtime'=>10));
            echo form_text($locale['global_101'], 'user_name', 'user_name', '', array('placeholder'=>$locale['global_101'], 'required'=>1));
            echo form_text($locale['global_102'], 'user_pass', 'user_pass', '', array('placeholder'=>$locale['global_102'], 'password'=>1, 'required'=>1));
            echo "<label><input type='checkbox' name='remember_me' value='y' title='".$locale['global_103']."'/> ".$locale['global_103']."</label>\n";
            echo "<div>\n";
            echo form_button($locale['global_104'], 'login', 'login', '', array('class'=>'m-t-10 m-b-20 btn btn-primary'));
            echo "</div>\n";
            echo closeform();


            //echo "<input type='submit' name='login' value='".$locale['global_104']."' class='button' /><br />\n";
            //echo "</form>\n<br />\n";

            if ($settings['enable_registration']) {
                echo $locale['global_105']."<br /><br />\n";
            }
            echo $locale['global_106']."\n</div>\n";
            closeside();
        }
    }
?>