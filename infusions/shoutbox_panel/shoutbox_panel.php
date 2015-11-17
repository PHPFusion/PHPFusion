<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: shoutbox_panel.php
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
include_once INFUSIONS."shoutbox_panel/infusion_db.php";
include_once INCLUDES."infusions_include.php";
// Check if a locale file is available that match the selected locale.
if (file_exists(INFUSIONS."shoutbox_panel/locale/".LANGUAGE.".php")) {
	// Load the locale file matching selection.
	include INFUSIONS."shoutbox_panel/locale/".LANGUAGE.".php";
} else {
	// Load the default locale file.
	include INFUSIONS."shoutbox_panel/locale/English.php";
}
$shout_settings = get_settings("shoutbox_panel");
$link = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
$link = preg_replace("^(&amp;|\?)s_action=(edit|delete)&amp;shout_id=\d*^", "", $link);
$sep = stristr($link, "?") ? "&amp;" : "?";
$shout_link = "";
$shout_message = "";
if (iMEMBER && (isset($_GET['s_action']) && $_GET['s_action'] == "delete") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
	if ((iADMIN && checkrights("S")) || (iMEMBER && dbcount("(shout_id)", DB_SHOUTBOX, "shout_id='".$_GET['shout_id']."' AND shout_name='".$userdata['user_id']."'"))) {
		$result = dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id='".$_GET['shout_id']."'".(iADMIN ? "" : " AND shout_name='".$userdata['user_id']."'"));
	}
	redirect($link);
}
if (!function_exists("sbwrap")) {
	function sbwrap($text) {
		global $locale;
		$i = 0;
		$tags = 0;
		$chars = 0;
		$res = "";
		$str_len = strlen($text);
		for ($i = 0; $i < $str_len; $i++) {
			$chr = mb_substr($text, $i, 1, $locale['charset']);
			if ($chr == "<") {
				if (mb_substr($text, ($i+1), 6, $locale['charset']) == "a href" || mb_substr($text, ($i+1), 3, $locale['charset']) == "img") {
					$chr = " ".$chr;
					$chars = 0;
				}
				$tags++;
			} elseif ($chr == "&") {
				if (mb_substr($text, ($i+1), 5, $locale['charset']) == "quot;") {
					$chars = $chars-5;
				} elseif (mb_substr($text, ($i+1), 4, $locale['charset']) == "amp;" || mb_substr($text, ($i+1), 4, $locale['charset']) == "#39;" || mb_substr($text, ($i+1), 4, $locale['charset']) == "#92;") {
					$chars = $chars-4;
				} elseif (mb_substr($text, ($i+1), 3, $locale['charset']) == "lt;" || mb_substr($text, ($i+1), 3, $locale['charset']) == "gt;") {
					$chars = $chars-3;
				}
			} elseif ($chr == ">") {
				$tags--;
			} elseif ($chr == " ") {
				$chars = 0;
			} elseif (!$tags) {
				$chars++;
			}
			if (!$tags && $chars == 18) {
				$chr .= "<br />";
				$chars = 0;
			}
			$res .= $chr;
		}
		return $res;
	}
}
openside($locale['SB_title']);
if (iMEMBER || $shout_settings['guest_shouts'] == "1") {
	include_once INCLUDES."bbcode_include.php";
	if (isset($_POST['post_shout'])) {
		$flood = FALSE;
		if (iMEMBER) {
			$shout_name = $userdata['user_id'];
		} elseif ($shout_settings['guest_shouts'] == "1") {
			$shout_name = trim(stripinput($_POST['shout_name']));
			$shout_name = preg_replace("(^[+0-9\s]*)", "", $shout_name);
			if (isnum($shout_name)) {
				$shout_name = "";
			}
			include_once INCLUDES."captchas/securimage/securimage.php";
			$securimage = new Securimage();
			if (!isset($_POST['sb_captcha_code']) || $securimage->check($_POST['sb_captcha_code']) == FALSE) {
				redirect($link);
			}
		}
		$shout_message = str_replace("\n", " ", $_POST['shout_message']);
		$shout_message = preg_replace("/^(.{255}).*$/", "$1", $shout_message);
		$shout_message = trim(stripinput(censorwords($shout_message)));
		if (iMEMBER && (isset($_GET['s_action']) && $_GET['s_action'] == "edit") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
			$comment_updated = FALSE;
			if ((iADMIN && checkrights("S")) || (iMEMBER && dbcount("(shout_id)", DB_SHOUTBOX, "shout_id='".$_GET['shout_id']."' AND shout_name='".$userdata['user_id']."'"))) {
				if ($shout_message) {
					$result = dbquery("UPDATE ".DB_SHOUTBOX." SET shout_message='$shout_message' WHERE shout_id='".$_GET['shout_id']."'".(iADMIN ? "" : " AND shout_name='".$userdata['user_id']."'"));
				}
			}
			redirect($link);
		} elseif ($shout_name && $shout_message) {
			require_once INCLUDES."flood_include.php";
			if (!flood_control("shout_datestamp", DB_SHOUTBOX, "shout_ip='".USER_IP."'")) {
				$result = dbquery("INSERT INTO ".DB_SHOUTBOX." (shout_name, shout_message, shout_datestamp, shout_ip, shout_ip_type, shout_hidden".(multilang_table("SB") ? ", shout_language)" : ")")." VALUES ('$shout_name', '$shout_message', '".time()."', '".USER_IP."', '".USER_IP_TYPE."', '0'".(multilang_table("SB") ? ", '".LANGUAGE."')" : ")"));
			}
		}
		redirect($link);
	}
	if (iMEMBER && (isset($_GET['s_action']) && $_GET['s_action'] == "edit") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
		$esresult = dbquery("SELECT ts.shout_id, ts.shout_name, ts.shout_message, tu.user_id, tu.user_name
			FROM ".DB_SHOUTBOX." ts
			LEFT JOIN ".DB_USERS." tu ON ts.shout_name=tu.user_id
			".(multilang_table("SB") ? "WHERE shout_language='".LANGUAGE."' AND" : "WHERE")." ts.shout_id='".$_GET['shout_id']."'");
		if (dbrows($esresult)) {
			$esdata = dbarray($esresult);
			if ((iADMIN && checkrights("S")) || (iMEMBER && $esdata['shout_name'] == $userdata['user_id'] && isset($esdata['user_name']))) {
				if ((isset($_GET['s_action']) && $_GET['s_action'] == "edit") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
					$edit_url = $sep."s_action=edit&amp;shout_id=".$esdata['shout_id'];
				} else {
					$edit_url = "";
				}
				$shout_link = $link.$edit_url;
				$shout_message = $esdata['shout_message'];
			}
		} else {
			$shout_link = $link;
			$shout_message = "";
		}
	} else {
		$shout_link = $link;
		$shout_message = "";
	}
	echo "<a id='edit_shout' name='edit_shout'></a>\n";
	echo openform('shout_form', 'post', $shout_link, array('notice' => 0, 'max_tokens' => 1));
	if (iGUEST) {
		echo $locale['SB_name']."<br />\n";
		echo "<input type='text' name='shout_name' value='' class='textbox' maxlength='30' style='width:140px' /><br />\n";
		echo $locale['SB_message']."<br />\n";
	}
	echo display_bbcodes("150px;", "shout_message", "shout_form", "smiley|b|u|url|color")."\n";
	echo form_textarea('shout_message', '', $shout_message);
	if (iGUEST) {
		echo $locale['SB_validation_code']."<br />\n";
		echo "<img id='sb_captcha' src='".INCLUDES."captchas/securimage/securimage_show.php' alt='' /><br />\n";
		echo "<a href='".INCLUDES."captchas/securimage/securimage_play.php'><img src='".INCLUDES."captchas/securimage/images/audio_icon.gif' alt='' class='tbl-border' style='margin-bottom:1px' /></a>\n";
		echo "<a href='#' onclick=\"document.getElementById('sb_captcha').src = '".INCLUDES."captchas/securimage/securimage_show.php?sid=' + Math.random(); return false\"><img src='".INCLUDES."captchas/securimage/images/refresh.gif' alt='' class='tbl-border' /></a><br />\n";
		echo $locale['SB_enter_validation_code']."<br />\n<input type='text' name='sb_captcha_code' class='textbox' style='width:100px' /><br />\n";
	}
	echo form_button('post_shout', $locale['SB_shout'], $locale['SB_shout'], array('class' => 'btn-block btn-primary button',
																								 'icon' => "entypo icomment"));
	echo closeform();
} else {
	echo "<div style='text-align:center'>".$locale['SB_login_req']."</div><br />\n";
}
$numrows = dbcount("(shout_id)", DB_SHOUTBOX, "shout_hidden='0'");
$result = dbquery("SELECT ts.shout_id, ts.shout_name, ts.shout_message, ts.shout_datestamp, tu.user_id, tu.user_name, tu.user_status, tu.user_avatar
	FROM ".DB_SHOUTBOX." ts
	LEFT JOIN ".DB_USERS." tu ON ts.shout_name=tu.user_id
	".(multilang_table("SB") ? "WHERE shout_language='".LANGUAGE."' AND" : "WHERE")." shout_hidden='0'
	ORDER BY ts.shout_datestamp DESC LIMIT 0,".$shout_settings['visible_shouts']);
if (dbrows($result)) {
	$i = 0;
	while ($data = dbarray($result)) {
		echo "<div class='display-block shoutboxwrapper clearfix' style='width:100%;'>\n";
		echo "<div class='shoutboxavatar pull-left m-r-10 m-t-5'>\n";
		echo display_avatar($data, '50px');
		echo "</div>\n";
		if ((iADMIN && checkrights("S")) || (iMEMBER && $data['shout_name'] == $userdata['user_id'] && isset($data['user_name']))) {
			echo "<div class='pull-right btn-group'>\n";
			echo "<a class='btn btn-default btn-xs' title='".$locale['SB_edit']."' href='".$link.$sep."s_action=edit&amp;shout_id=".$data['shout_id']."#edit_shout"."' class='side'><i class='entypo pencil'></i></a>\n"; //
			echo "<a class='btn btn-default btn-xs' title='".$locale['SB_delete']."' href='".$link.$sep."s_action=delete&amp;shout_id=".$data['shout_id']."' onclick=\"return confirm('".$locale['SB_warning_shout']."');\" class='side'><i class='entypo trash'></i></a>\n"; //
			echo "</div>\n";
		}
		echo "<div class='shoutboxname'>\n";
		echo ($data['user_name']) ? "<span class='side'>".profile_link($data['shout_name'], $data['user_name'], $data['user_status'])."</span>\n" : $data['shout_name']."\n";
		echo "</div>\n";
		echo "<div class='shoutboxdate'>".timer($data['shout_datestamp'])."</div>\n"; //".showdate("forumdate", $data['shout_datestamp'])."</div>";
		echo "<div class='shoutbox'>".sbwrap(parseubb(parsesmileys($data['shout_message']), "b|i|u|url|color"))."</div>\n";
		//if ($i != $numrows) { echo "<br />\n"; }
		echo "</div>\n";
	}
	if ($numrows > $shout_settings['visible_shouts']) {
		echo "<div style='text-align:center'>\n<a href='".INFUSIONS."shoutbox_panel/shoutbox_archive.php' class='side'>".$locale['SB_archive']."</a>\n</div>\n";
	}
} else {
	echo "<div>".$locale['SB_no_msgs']."</div>\n";
}
closeside();
