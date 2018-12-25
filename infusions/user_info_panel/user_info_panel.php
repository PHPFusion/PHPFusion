<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

if (iMEMBER) {
	if (preg_match('/administration/i', $_SERVER['PHP_SELF'])) {
		opensidex($locale['UM096'].$userdata['user_name'], "off");
	} else {
		openside($locale['UM096'].$userdata['user_name']);
}
	
$inbox_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_folder='0'");
$outbox_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_folder='1'");
$archive_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_folder='2'");

echo "<div class='clearfix'>\n";
echo "<h4 class='text-center'><strong>".ucwords($userdata['user_name'])."</strong></h4>\n";
echo "<div class='avatar-row text-center'>\n";
echo "<div class='p-10' style='background: #f1f1f1; border: 1px solid #d4d6d8; border-radius: 4px;'>\n".display_avatar($userdata, '85px')."</div>\n";
echo "<small>".getuserlevel($userdata['user_level'])."</small>\n";
echo "</div>\n";
echo "</div>\n";
echo "<ul class='user-info-bar'>\n";
$msg_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'");
echo ($msg_count) ? "<li><a href='".BASEDIR."messages.php?folder=inbox' title='".sprintf($locale['UM085'], $msg_count).($msg_count == 1 ? $locale['UM086'] : $locale['UM087'])."' ><i class='entypo icomment'></i><label style='position:absolute; margin-left:-20px;' class='pointer label label-danger'>$msg_count</label></a>\n</li>\n" : "";
echo "</ul>\n";
$result = dbquery("SELECT * FROM ".DB_PREFIX."messages_options WHERE user_id='0'");
$data = dbarray($result);

$inbox_cfg = ($data['pm_inbox']!=0 ? $data['pm_inbox'] : 1);
	$inbox_percent = number_format(($inbox_count/$inbox_cfg)*99, 0);

	echo "<div style='width:99%;margin-bottom:5px' class='tbl-border'><a href='".BASEDIR."messages.php?folder=inbox' title='".$locale['UM098']." ".$inbox_percent."% ".$locale['UM098']."'><img src='".THEME."images/pollbar.gif' alt='".$inbox_percent."%' height='12' width='".$inbox_percent."%' class='poll'></a></div>";

	$outbox_cfg = ($data['pm_sentbox']!=0 ? $data['pm_sentbox'] : 1);
	$outbox_percent = number_format(($outbox_count/$outbox_cfg)*99, 0);

	echo "<div style='width:99%;margin-bottom:5px' class='tbl-border'><a href='".BASEDIR."messages.php?folder=outbox' title='".$locale['UM099']." ".$outbox_percent."% ".$locale['UM099']."'><img src='".THEME."images/pollbar.gif' alt='".$outbox_percent."%' height='12' width='".$outbox_percent."%' class='poll'></a></div>";

	$archive_cfg = ($data['pm_savebox']!=0 ? $data['pm_savebox'] : 1);
	$archive_percent = number_format(($archive_count/$archive_cfg)*99, 0);

	echo "<div style='width:99%;margin-bottom:5px' class='tbl-border'><a href='".BASEDIR."messages.php?folder=archive' title='".$locale['UM100']." ".$archive_percent."% ".$locale['UM100']."'><img src='".THEME."images/pollbar.gif' alt='".$archive_percent."%' height='12' width='".$archive_percent."%' class='poll'></a></div>";

    $msg_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'");
	if ($msg_count) {
	   echo "<center><img src='".THEME."images/bullet.gif' alt='' border='0'>\n<b><a class='side' href='".BASEDIR."messages.php'>".sprintf($locale['UM085'], $msg_count).($msg_count == 1 ? $locale['UM086'] : $locale['UM087'])."</a></b></center>\n";
	}

	echo "<div id='navigation-user'>\n";
echo "<h5><strong>".$locale['UM097']."</strong></h5>\n";

if (sizeof($enabled_languages) > 1) {
echo "<hr class='side-hr'>\n";
echo "<div style='text-align:center'>\n";
echo "<h5 class='m-t-10'><strong>".$locale['global_ML102']."</strong></h5>";
echo lang_switcher();
echo "</div>\n";
}

echo "<hr class='side-hr'>\n";
echo "<ul>\n";
echo "<li><a class='side' href='".BASEDIR."edit_profile.php'>".$locale['UM080']." <i class='pull-right entypo suitcase'></i></a></li>\n";
echo "<li><a class='side' href='".BASEDIR."messages.php'>".$locale['UM081']." <i class='pull-right entypo mail'></i></a></li>\n";
echo "<li><a class='side' href='".INFUSIONS."forum_threads_list_panel/my_tracked_threads.php'>".$locale['UM088']." <i class='pull-right entypo eye'></i></a></li>\n";
echo "<li><a class='side' href='".BASEDIR."members.php'>".$locale['UM082']." <i class='pull-right entypo users'></i></a></li>\n";
echo (iADMIN) ? "<li><a class='side' href='".ADMIN."index.php".$aidlink."'>".$locale['UM083']." <i class='pull-right entypo cog'></i></a></li>\n" : '';
echo "<li><a class='side' href=\"javascript:show_hide('ShowHide001')\">".$locale['UM089']." <i class='pull-right entypo upload-cloud'></i></a></li>\n";
echo "<li>\n";
echo "<div id='ShowHide001' style='display:none'>\n";
echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=n'>".$locale['UM090']."</a><br />\n";
echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=l'>".$locale['UM091']."</a><br />\n";
echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=a'>".$locale['UM092']."</a><br />\n";
echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=p'>".$locale['UM093']."</a><br />\n";
echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=d'>".$locale['UM094']."</a><br />\n";
echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=b'>".$locale['UM095']."</a><br />\n";

echo "</div>\n";
echo "</li>\n";
echo "</ul>\n";
echo "</div>\n";
echo "<div class='m-t-20'>\n";
echo "<a class='".($settings['bootstrap'] || defined('BOOTSTRAP') ? 'btn btn-block btn-primary' : 'button')." center' href='".BASEDIR."setuser.php?logout=yes'>".$locale['UM084']."</a>\n";
echo "</div>\n";
if (preg_match('/administration/i', $_SERVER['PHP_SELF'])) {
		closesidex();
	} else {
		closeside();
	}
} else {
    if (!preg_match('/login.php/i', FUSION_SELF)) {

        $action_url = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
        if (isset($_GET['redirect']) && strstr($_GET['redirect'], "/")) {
            $action_url = cleanurl(urldecode($_GET['redirect']));
        }

	openside($locale['global_100']);
	if (sizeof($enabled_languages) > 1) {
		echo "<div style='text-align:center'>\n";
		echo "<h5 class='m-t-10'><strong>".$locale['global_ML102']."</strong></h5>";
		echo lang_switcher();
		echo "</div>\n";
		echo "<hr />";
	}

	switch ($settings['login_method']) {
		case 2 :
			$placeholder = $locale['global_101c'];
			break;
		case 1 :
			$placeholder = $locale['global_101b'];
			break;
		default:
			$placeholder = $locale['global_101a'];
	}
	
	echo "<div style='text-align:center; m-t-10;'>\n";
	echo "<form name='loginform' method='post' action='".$action_url."'>\n";
	echo $placeholder."<br />\n<input type='text' name='user_name' class='textbox' style='width:100px' /><br />\n";
	echo $locale['global_102']."<br />\n<input type='password' name='user_pass' class='textbox' style='width:100px' /><br />\n";
	echo "<label><input type='checkbox' name='remember_me' value='y' title='".$locale['global_103']."' style='vertical-align:middle;' /></label>\n";
	echo "<input type='submit' name='login' value='".$locale['global_104']."' class='button' /><br />\n";
	echo "</form>\n<br />\n";
		
	if ($settings['enable_registration']) {
		echo $locale['global_105']."<br /><br />\n";
	}
	echo $locale['global_106']."\n</div>\n";
closeside();
	}
}
?>