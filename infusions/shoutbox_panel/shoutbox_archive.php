<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: shoutbox_archive.php
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
require_once THEMES."templates/header.php";

include_once INFUSIONS."shoutbox_panel/infusion_db.php";
include_once INCLUDES."infusions_include.php";

// Check if locale file is available matching the current site locale setting.
if (file_exists(INFUSIONS."shoutbox_panel/locale/".$settings['locale'].".php")) {
	// Load the locale file matching the current site locale setting.
	include INFUSIONS."shoutbox_panel/locale/".$settings['locale'].".php";
} else {
	// Load the infusion's default locale file.
	include INFUSIONS."shoutbox_panel/locale/English.php";
}

$shout_settings = get_settings("shoutbox_panel");

$archive_shout_link = ""; $archive_shout_message = "";

$result = dbquery("SELECT panel_access FROM ".DB_PANELS." WHERE panel_filename='shoutbox_panel' AND panel_status='1'");
if (dbrows($result)) {
	$data = dbarray($result);
	if (!checkgroup($data['panel_access'])) {
		redirect(BASEDIR."index.php");
	}
} else {
	redirect(BASEDIR."index.php");
}

if (iMEMBER && (isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
	if ((iADMIN && checkrights("S")) || (iMEMBER && dbcount("(shout_id)", DB_SHOUTBOX, "shout_id='".$_GET['shout_id']."' AND shout_name='".$userdata['user_id']."' AND shout_hidden='0'"))) {
		$result = dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id='".$_GET['shout_id']."'".(iADMIN ? "" : " AND shout_name='".$userdata['user_id']."'"));
	}
	redirect(FUSION_SELF);
}

function sbawrap($text) {
	global $locale;

	$i = 0; $tags = 0; $chars = 0; $res = "";

	$str_len = strlen($text);

	for ($i = 0; $i < $str_len; $i++) {
		$chr = mb_substr($text, $i, 1, $locale['charset']);
		if ($chr == "<") {
			if (mb_substr($text, ($i + 1), 6, $locale['charset']) == "a href" || mb_substr($text, ($i + 1), 3, $locale['charset']) == "img") {
				$chr = " ".$chr;
				$chars = 0;
			}
			$tags++;
		} elseif ($chr == "&") {
			if (mb_substr($text, ($i + 1), 5, $locale['charset']) == "quot;") {
				$chars = $chars - 5;
			} elseif (mb_substr($text, ($i + 1), 4, $locale['charset']) == "amp;" || mb_substr($text, ($i + 1), 4, $locale['charset']) == "#39;" || mb_substr($text, ($i + 1), 4, $locale['charset']) == "#92;") {
				$chars = $chars - 4;
			} elseif (mb_substr($text, ($i + 1), 3, $locale['charset']) == "lt;" || mb_substr($text, ($i + 1), 3, $locale['charset']) == "gt;") {
				$chars = $chars - 3;
			}
		} elseif ($chr == ">") {
			$tags--;
		} elseif ($chr == " ") {
			$chars = 0;
		} elseif (!$tags) {
			$chars++;
		}

		if (!$tags && $chars == 40) {
			$chr .= " ";
			$chars = 0;
		}
		$res .= $chr;
	}

	return $res;
}

add_to_title($locale['global_200'].$locale['SB_archive']);

opentable($locale['SB_archive']);
if (iMEMBER || $shout_settings['guest_shouts'] == "1") {
	include_once INCLUDES."bbcode_include.php";
	if (isset($_POST['post_archive_shout'])) {
		$flood = false;
		if (iMEMBER) {
			$archive_shout_name = $userdata['user_id'];
		} elseif ($shout_settings['guest_shouts'] == "1") {
			$archive_shout_name = trim(stripinput($_POST['archive_shout_name']));
			$archive_shout_name = preg_replace("(^[+0-9\s]*)", "", $archive_shout_name);
			if (isnum($archive_shout_name)) { $archive_shout_name = ""; }
			include_once INCLUDES."captchas/securimage/securimage.php";
			$securimage = new Securimage();
			if (!isset($_POST['captcha_code']) || $securimage->check($_POST['captcha_code']) == false) { redirect($link); }
		}
		$archive_shout_message = str_replace("\n", " ", $_POST['archive_shout_message']);
		$archive_shout_message = preg_replace("/^(.{255}).*$/", "$1", $archive_shout_message);
		$archive_shout_message = trim(stripinput(censorwords($archive_shout_message)));
		if (iMEMBER && (isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
			$comment_updated = false;
			if ((iADMIN && checkrights("S")) || (iMEMBER && dbcount("(shout_id)", DB_SHOUTBOX, "shout_id='".$_GET['shout_id']."' AND shout_name='".$userdata['user_id']."' AND shout_hidden='0'"))) {
				if ($archive_shout_message) {
					$result = dbquery("UPDATE ".DB_SHOUTBOX." SET shout_message='$archive_shout_message' WHERE shout_id='".$_GET['shout_id']."'".(iADMIN ? "" : " AND shout_name='".$userdata['user_id']."'"));
				}
			}
			redirect(FUSION_SELF);
		} elseif ($archive_shout_name && $archive_shout_message) {
			require_once INCLUDES."flood_include.php";
			if (!flood_control("shout_datestamp", DB_SHOUTBOX, "shout_ip='".USER_IP."'")) {
				$result = dbquery("INSERT INTO ".DB_SHOUTBOX." (shout_name, shout_message, shout_datestamp, shout_ip, shout_ip_type) VALUES ('$archive_shout_name', '$archive_shout_message', '".time()."', '".USER_IP."', '".USER_IP_TYPE."')");
			}
			redirect(FUSION_SELF);
		}
	}
	if (iMEMBER && (isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
		$esresult = dbquery(
			"SELECT ts.shout_id, ts.shout_name, ts.shout_message, tu.user_id, tu.user_name
			FROM ".DB_SHOUTBOX." ts
			LEFT JOIN ".DB_USERS." tu ON ts.shout_name=tu.user_id
			WHERE ts.shout_id='".$_GET['shout_id']."' AND shout_hidden='0'"
		);
		if (dbrows($esresult)) {
			$esdata = dbarray($esresult);
			if ((iADMIN && checkrights("S")) || (iMEMBER && $esdata['shout_name'] == $userdata['user_id'] && isset($esdata['user_name']))) {
				if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
					$edit_url = "?action=edit&amp;shout_id=".$esdata['shout_id'];
				} else {
					$edit_url = "";
				}
				$archive_shout_link = FUSION_SELF.$edit_url;
				$archive_shout_message = $esdata['shout_message'];
			}
		} else {
			$archive_shout_link = FUSION_SELF;
			$archive_shout_message = "";
		}
	} else {
		$archive_shout_link = FUSION_SELF;
		$archive_shout_message = "";
	}
	echo "<form name='archive_form' method='post' action='".$archive_shout_link."'>\n";
	echo "<div style='text-align:center'>\n";
	if (iGUEST) {
		echo $locale['SB_name']."<br />\n";
		echo "<input type='text' name='archive_shout_name' value='' class='textbox' maxlength='30' style='width:200px;' /><br />\n";
		echo $locale['SB_message']."<br />\n";
	}
	echo "<textarea name='archive_shout_message' rows='4' cols='50' class='textbox'>".$archive_shout_message."</textarea><br />\n";
	echo "<div style='text-align:center'>".display_bbcodes("100%", "archive_shout_message", "archive_form", "smiley|b|i|u|url|color")."</div>\n";
	if (iGUEST) {
		echo $locale['SB_validation_code']."<br />\n";
		echo "<img id='captcha' src='".INCLUDES."captchas/securimage/securimage_show.php' alt='' /><br />\n";
		echo "<a href='".INCLUDES."captchas/securimage/securimage_play.php'><img src='".INCLUDES."captchas/securimage/images/audio_icon.gif' alt='' class='tbl-border' style='margin-bottom:1px' /></a>\n";
		echo "<a href='#' onclick=\"document.getElementById('captcha').src = '".INCLUDES."captchas/securimage/securimage_show.php?sid=' + Math.random(); return false\"><img src='".INCLUDES."captchas/securimage/images/refresh.gif' alt='' class='tbl-border' /></a><br />\n";
		echo $locale['SB_enter_validation_code']."<br />\n<input type='text' name='captcha_code' class='textbox' style='width:100px' /><br />\n";
	}
	echo "<br /><input type='submit' name='post_archive_shout' value='".$locale['SB_shout']."' class='button' />\n";
	echo "</div>\n</form>\n<br />\n";
} else {
	echo "<div style='text-align:center'>".$locale['SB_login_req']."</div>\n";
}
$rows = dbcount("(shout_id)", DB_SHOUTBOX,"shout_hidden='0'");
if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
if ($rows != 0) {
	$result = dbquery(
		"SELECT s.shout_id, s.shout_name, s.shout_message, s.shout_datestamp, u.user_id, u.user_name, u.user_status
		FROM ".DB_SHOUTBOX." s
		LEFT JOIN ".DB_USERS." u ON s.shout_name=u.user_id
		WHERE s.shout_hidden='0'
		ORDER BY s.shout_datestamp DESC LIMIT ".$_GET['rowstart'].",20"
	);
	while ($data = dbarray($result)) {
		echo "<div class='tbl2'>\n";
		if ((iADMIN && checkrights("S")) || (iMEMBER && $data['shout_name'] == $userdata['user_id'] && isset($data['user_name']))) {
			echo "<div style='float:right'>\n<a href='".FUSION_SELF."?action=edit&amp;shout_id=".$data['shout_id']."'>".$locale['SB_edit']."</a> |\n";
			echo "<a href='".FUSION_SELF."?action=delete&amp;shout_id=".$data['shout_id']."'>".$locale['SB_delete']."</a>\n</div>\n";
		}
		if ($data['user_name']) {
			echo "<span class='comment-name'><span class='slink'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</span>\n</span>\n";
		} else {
			echo "<span class='comment-name'>".$data['shout_name']."</span>\n";
		}
		echo "<span class='small'>".showdate("longdate", $data['shout_datestamp'])."</span>";
		echo "</div>\n<div class='tbl1'>\n".sbawrap(parseubb(parsesmileys($data['shout_message']), "b|i|u|url|color"))."</div>\n";
	}
} else {
	echo "<div style='text-align:center'><br />\n".$locale['SB_no_msgs']."<br /><br />\n</div>\n";
}
closetable();

echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 20, $rows, 3, FUSION_SELF."?")."\n</div>\n";

require_once THEMES."templates/footer.php";
?>
