<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: shoutbox_admin.php
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
require_once "../../maincore.php";
require_once THEMES."templates/admin_header.php";

include INFUSIONS."shoutbox_panel/infusion_db.php";

// Check if a locale file is available that match the selected locale.
if (file_exists(INFUSIONS."shoutbox_panel/locale/".LANGUAGE.".php")) {
	// Load the locale file matching selection.
	include INFUSIONS."shoutbox_panel/locale/".LANGUAGE.".php";
} else {
	// Load the default locale file.
	include INFUSIONS."shoutbox_panel/locale/English.php";
}


if (!checkrights("S") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../../index.php");
}

$nav = "<table cellpadding='0' cellspacing='0' class='tbl-border' align='center' style='width:300px; margin-bottom:20px; text-align:center;'>\n<tr>\n";
$nav .= "<td class='".(!isset($_GET['page']) || $_GET['page'] != "settings" ? "tbl2" : "tbl1")."'><a href='".FUSION_SELF.$aidlink."'>".$locale['SB_admin1']."</a></td>\n";
$nav .= "<td class='".(isset($_GET['page']) && $_GET['page'] == "settings" ? "tbl2" : "tbl1")."'><a href='".FUSION_SELF.$aidlink."&amp;page=settings'>".$locale['SB_settings']."</a></td>\n";
$nav .= "</tr>\n</table>\n";

include_once INCLUDES."bbcode_include.php";

if (!isset($_GET['page']) || $_GET['page'] != "settings") {
	if (isset($_GET['status']) && !isset($message)) {
		if ($_GET['status'] == "su") {
			$message = $locale['SB_shout_updated'];
		} elseif ($_GET['status'] == "del") {
			$message = $locale['SB_shout_deleted'];
		}
		if ($message) {
			echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
		}
	} elseif ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
		$result = dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id='".$_GET['shout_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=del");
	} else {
		if (isset($_POST['saveshout']) && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
			$shout_message = str_replace("\n", " ", $_POST['shout_message']);
			$shout_message = preg_replace("/^(.{255}).*$/", "$1", $shout_message);
			$shout_message = preg_replace("/([^\s]{25})/", "$1\n", $shout_message);
			$shout_message = form_sanitizer($shout_message, '', 'shout_message');
			$shout_message = str_replace("\n", "<br />", $shout_message);
			if (!defined('FUSION_NULL')) {
				$result = dbquery("UPDATE ".DB_SHOUTBOX." SET shout_message='$shout_message' WHERE shout_id='".$_GET['shout_id']."'");
				redirect(FUSION_SELF.$aidlink."&status=su");
			}
		}
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
			$result = dbquery("SELECT shout_id, shout_message FROM ".DB_SHOUTBOX." WHERE shout_id='".$_GET['shout_id']."'");
			if (dbrows($result)) {
				$data = dbarray($result);
				opentable($locale['SB_edit_shout']);
				echo openform('input_form', 'input_form', 'post', FUSION_SELF.$aidlink."&amp;shout_id=".$data['shout_id']."", array('downtime' => 0,
																																	'notice' => 0));
				echo form_textarea($locale['SB_message'], 'shout_message', 'shout_message', $data['shout_message'], array('required' => 1,
																														  'bbcode' => 1));
				echo form_button($locale['SB_save_shout'], 'saveshout', 'saveshout', $locale['SB_save_shout'], array('class' => 'btn-primary'));
				echo closeform();
				closetable();
			} else {
				redirect(FUSION_SELF.$aidlink);
			}
		}
		opentable($locale['SB_edit_shout']);
		echo $nav;
		$result = dbquery("SELECT * FROM ".DB_SHOUTBOX);
		$rows = dbrows($result);
		if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
			$_GET['rowstart'] = 0;
		}
		if ($rows != 0) {
			$i = 0;
			$result = dbquery("SELECT s.shout_id, s.shout_name, s.shout_message, s.shout_datestamp, s.shout_ip, u.user_id, u.user_name, u.user_avatar, u.user_status
				FROM ".DB_SHOUTBOX." s
				LEFT JOIN ".DB_USERS." u ON s.shout_name=u.user_id
				ORDER BY shout_datestamp DESC LIMIT ".$_GET['rowstart'].",20");
			echo "<div class='list-group'>\n";
			while ($data = dbarray($result)) {
				echo "<div class='list-group-item' style='min-height:100px;'>\n";
				echo "<div class='pull-left m-r-10'>".display_avatar($data, '80px')."</div>\n";
				echo "<div class='comment-name'>";
				echo $data['user_name'] ? "<span class='slink'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</span>" : $data['shout_name'];
				echo "</span>\n";
				echo "<span class='small'>".$locale['SB_on_date'].showdate("longdate", $data['shout_datestamp'])."</div>\n";
				echo "<div class='m-t-5'><small>\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;shout_id=".$data['shout_id']."'>".$locale['SB_edit']."</a> -\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;shout_id=".$data['shout_id']."' onclick=\"return confirm('".$locale['SB_warning_shout']."');\">".$locale['SB_delete']."</a> -\n";
				echo "<strong>".$locale['SB_userip'].$data['shout_ip']."</strong>\n";
				echo "</small>\n</div>\n";
				echo str_replace("<br />", "", parseubb(parsesmileys($data['shout_message']), "b|i|u|url|color"))."<br />\n";
				echo "</div>\n";
			}
			echo "</div>\n";
			echo "<div align='center' style='margin-top:5px;'>\n".makePageNav($_GET['rowstart'], 20, $rows, 3, FUSION_SELF.$aidlink."&amp;")."\n</div>\n";
		} else {
			echo "<div style='text-align:center'><br />\n".$locale['SB_no_msgs']."<br /><br />\n</div>\n";
		}
		closetable();
	}
} else {
	require_once INCLUDES."infusions_include.php";
	if (isset($_POST['sb_settings'])) {
		if (isset($_POST['visible_shouts']) && isnum($_POST['visible_shouts'])) {
			$setting = set_setting("visible_shouts", $_POST['visible_shouts'], "shoutbox_panel");
		}
		if (isset($_POST['guest_shouts']) && ($_POST['guest_shouts'] == 1 || $_POST['guest_shouts'] == 0)) {
			$setting = set_setting("guest_shouts", $_POST['guest_shouts'], "shoutbox_panel");
		}
		redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=update_ok");
	}
	if (isset($_POST['sb_delete_old']) && isset($_POST['num_days']) && isnum($_POST['num_days'])) {
		$deletetime = time()-($_POST['num_days']*86400);
		$numrows = dbcount("(shout_id)", DB_SHOUTBOX, "shout_datestamp < '".$deletetime."'");
		$result = dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_datestamp < '".$deletetime."'");
		redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=delall&numr=$numrows");
	}
	if (isset($_GET['status'])) {
		if ($_GET['status'] == "delall" && isset($_GET['numr']) && isnum($_GET['numr'])) {
			$message = number_format(intval($_GET['numr']))." ".$locale['SB_shouts_deleted'];
		} elseif ($_GET['status'] == "update_ok") {
			$message = $locale['SB_update_ok'];
		}
	}
	if (isset($message) && $message != "") {
		echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n";
	}
	$inf_settings = get_settings("shoutbox_panel");
	opentable($locale['SB_settings']);
	echo $nav;
	echo openform('shoutbox', 'shoutbox', 'post', FUSION_SELF.$aidlink."&amp;page=settings", array('downtime' => 0));
	echo "<div class='panel panel-default'>\n<div class='panel-body'>\n";
	$array = array('90' => "90 ".$locale['SB_days'], '60' => "60 ".$locale['SB_days'], '30' => "30 ".$locale['SB_days'],
				   '20' => "20 ".$locale['SB_days'], '10' => "10 ".$locale['SB_days'],);
	echo form_select($locale['SB_delete_old'], 'num_days', 'num_days', $array, '', array('inline' => 1));
	echo "<div class='m-t-5 m-b-0'/>\n&nbsp;</div>\n";
	echo form_button($locale['SB_submit'], 'sb_delete_old', 'sb_delete_old', $locale['SB_submit'], array('class' => 'btn-primary pull-right'));
	echo "</div>\n</div>\n";
	echo closeform();
	add_to_jquery("
        $('sb_delete_old').bind('click', function() { confirm('".$locale['SB_warning_shouts']."'); return false; });
    ");
	echo openform('shoutbox2', 'shoutbox2', 'post', FUSION_SELF.$aidlink."&amp;page=settings", array('downtime' => 0,
																									 'notice' => 0));
	echo "<div class='panel panel-default'>\n<div class='panel-body'>\n";
	echo form_text($locale['SB_visible_shouts'], 'visible_shouts', 'visible_shouts', $inf_settings['visible_shouts'], array('required' => 1,
																															'inline' => 1));
	$opts = array('1' => $locale['SB_yes'], '0' => $locale['SB_no'],);
	echo "<div class='m-t-5 m-b-0'/>\n&nbsp;</div>\n";
	echo form_select($locale['SB_guest_shouts'], 'guest_shouts', 'guest_shouts', $opts, $inf_settings['guest_shouts'], array('inline' => 1));
	echo "<div class='m-t-5 m-b-0'/>\n&nbsp;</div>\n";
	echo form_button($locale['SB_submit'], 'sb_settings', 'sb_settings', $locale['SB_submit'], array('class' => 'btn-primary pull-right m-l-20'));
	echo "</div>\n</div>\n";
	echo closeform();
	closetable();
}

require_once THEMES."templates/footer.php";
?>
