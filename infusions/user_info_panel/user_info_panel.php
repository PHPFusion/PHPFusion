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
echo "<div class='avatar-row text-center'>\n";
echo "<div class='pull-left m-r-10'>\n".display_avatar($userdata, '90px')."</div>\n";
echo "</div>\n";
echo "<h4 class='m-t-10 m-b-0'><strong>".ucwords($userdata['user_name'])."</strong></h4>\n";
echo "<small>".getuserlevel($userdata['user_level'])."</small>\n<br/>";
echo "</div>\n";
echo "<ul class='user-info-bar'>\n";
$msg_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'");
echo ($msg_count) ? "<li><a href='".BASEDIR."messages.php?folder=inbox' title='".sprintf($locale['UM085'], $msg_count).($msg_count == 1 ? $locale['UM086'] : $locale['UM087'])."' ><i class='entypo icomment'></i><label style='position:absolute; margin-left:-20px;' class='pointer label label-danger'>$msg_count</label></a>\n</li>\n" : "";
echo "</ul>\n";
$result = dbquery("SELECT * FROM ".DB_PREFIX."messages_options WHERE user_id='0'");
$data = dbarray($result);
$inbox_cfg = ($data['pm_inbox'] != 0 ? $data['pm_inbox'] : 1);
$inbox_percent = $inbox_cfg !== 1 ? number_format(($inbox_count/$inbox_cfg)*99, 0) : number_format(0*99,0);
echo progress_bar($inbox_percent, $locale['UM098']);
$outbox_cfg = ($data['pm_sentbox'] != 0 ? $data['pm_sentbox'] : 1);
$outbox_percent = $outbox_cfg !== 1 ? number_format(($outbox_count/$outbox_cfg)*99, 0) : number_format(0*99,0);
echo progress_bar($outbox_percent, $locale['UM099']);
$archive_cfg = ($data['pm_savebox'] != 0 ? $data['pm_savebox'] : 1);
$archive_percent = $archive_cfg !== 1 ? number_format(($archive_count/$archive_cfg)*99, 0) : number_format(0*99,0);
echo progress_bar($archive_percent, $locale['UM100']);
if (sizeof($enabled_languages) > 1) {
echo "<h5><strong>".$locale['UM101']."</strong></h5>\n";
echo lang_switcher();
}
echo "<div id='navigation-user'>\n";
echo "<h5><strong>".$locale['UM097']."</strong></h5>\n";
echo "<hr class='side-hr'>\n";
echo "<ul>\n";
echo "<li><a class='side' href='".BASEDIR."edit_profile.php'>".$locale['UM080']." <i class='pull-right entypo suitcase'></i></a></li>\n";
echo "<li><a class='side' href='".BASEDIR."messages.php'>".$locale['UM081']." <i class='pull-right entypo mail'></i></a></li>\n";
if (db_exists(DB_THREADS)) {
	echo "<li><a class='side' href='".INFUSIONS."forum_threads_list_panel/my_tracked_threads.php'>".$locale['UM088']." <i class='pull-right entypo eye'></i></a></li>\n";
}
echo "<li><a class='side' href='".BASEDIR."members.php'>".$locale['UM082']." <i class='pull-right entypo users'></i></a></li>\n";
echo (iADMIN) ? "<li><a class='side' href='".ADMIN."index.php".$aidlink."&amp;pagenum=0'>".$locale['UM083']." <i class='pull-right entypo cog'></i></a></li>\n" : '';
echo "<li><a class='side' href=\"javascript:show_hide('ShowHide001')\">".$locale['UM089']." <i class='pull-right entypo upload-cloud'></i></a></li>\n";
echo "<li>\n";
echo "<div id='ShowHide001' style='display:none'>\n";
echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=n'>".$locale['UM090']."</a>\n";
echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=b'>".$locale['UM095']."</a>\n";
echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=l'>".$locale['UM091']."</a>\n";
echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=a'>".$locale['UM092']."</a>\n";
echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=p'>".$locale['UM093']."</a>\n";
echo "<a class='side p-l-20' href='".BASEDIR."submit.php?stype=d'>".$locale['UM094']."</a>\n";
echo "</div>\n";
echo "</li>\n";
echo "</ul>\n";
echo "</div>\n";
echo "<div class='m-t-20'>\n";
echo "<a class='btn btn-block btn-primary' href='".BASEDIR."index.php?logout=yes'>".$locale['UM084']."</a>\n";
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
echo "<h5 class='m-b-10'><strong>".$locale['UM101']."</strong></h5>";
echo lang_switcher();
}
echo "<div class='m-t-10'>\n";
echo openform('loginform', 'loginform', 'post', $action_url, array('downtime' => 1, 'notice'=>0));
echo form_text($locale['global_101'], 'user_name', 'user_name_uip', '', array('placeholder' => $locale['global_101'],'required' => 1));
echo form_text($locale['global_102'], 'user_pass', 'user_pass_uip', '', array('placeholder' => $locale['global_102'],'password' => 1,'required' => 1));
echo "<label><input type='checkbox' name='remember_me' value='y' title='".$locale['global_103']."'/> ".$locale['global_103']."</label>\n";
echo form_button($locale['global_104'], 'login', 'login', '', array('class' => 'm-t-20 m-b-20 btn-block btn-primary'));
echo closeform();

if ($settings['enable_registration']) {
	echo $locale['global_105']."<br /><br />\n";
}

echo $locale['global_106']."\n</div>\n";
closeside();
	}
}
?>
