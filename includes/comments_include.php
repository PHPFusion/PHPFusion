<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: comments_include.php
| Author: PHP-Fusion Development Team
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

include LOCALE.LOCALESET."comments.php";

function showcomments($ctype, $cdb, $ccol, $cid, $clink) {

	global $settings, $locale, $userdata, $aidlink;

	$link = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
	$link = preg_replace("^(&amp;|\?)c_action=(edit|delete)&amp;comment_id=\d*^", "", $link);

	$cpp = $settings['comments_per_page'];

	if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "delete")
				&& (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
		if ((iADMIN && checkrights("C")) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS,
				"comment_id='".$_GET['comment_id']."' AND comment_name='".$userdata['user_id']."'"))) {
			$result = dbquery(
				"DELETE FROM ".DB_COMMENTS."
				WHERE comment_id='".$_GET['comment_id']."'".(iADMIN ? "" : "
					AND comment_name='".$userdata['user_id']."'")
			);
		}
		redirect($clink.($settings['comments_sorting'] == "ASC" ? "" : "&amp;c_start=0"));
	}

	if ($settings['comments_enabled'] == "1") {
		if ((iMEMBER || $settings['guestposts'] == "1") && isset($_POST['post_comment'])) {

			if (iMEMBER) {
				$comment_name = $userdata['user_id'];
			} elseif ($settings['guestposts'] == "1") {
				if (!isset($_POST['comment_name'])) { redirect($link); }
				$comment_name = trim(stripinput($_POST['comment_name']));
				$comment_name = preg_replace("(^[+0-9\s]*)", "", $comment_name);
				if (isnum($comment_name)) { $comment_name = ""; }
				$_CAPTCHA_IS_VALID = false;
				include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php";
				if (!isset($_POST['captcha_code']) || $_CAPTCHA_IS_VALID == false) {
					redirect($link);
				}
			}

			$comment_message = trim(stripinput(censorwords($_POST['comment_message'])));

			if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit")
						&& (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
				$comment_updated = false;
				if ((iADMIN && checkrights("C")) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS,
						"comment_id='".$_GET['comment_id']."' AND comment_item_id='".$cid."'
						AND comment_type='".$ctype."' AND comment_name='".$userdata['user_id']."'
						AND comment_hidden='0'"))) {
					if ($comment_message) {
						$result = dbquery("UPDATE ".DB_COMMENTS." SET comment_message='".$comment_message."'
											WHERE comment_id='".$_GET['comment_id']."'".(iADMIN ? "" : "
											AND comment_name='".$userdata['user_id']."'"));
						$comment_updated = true;
					}
				}
				if ($comment_updated) {
					if ($settings['comments_sorting'] == "ASC") {
						$c_operator = "<=";
					} else {
						$c_operator = ">=";
					}

					$c_count = dbcount("(comment_id)", DB_COMMENTS,
								"comment_id".$c_operator."'".$_GET['comment_id']."'
								AND comment_item_id='".$cid."'
								AND comment_type='".$ctype."'");
					$c_start = (ceil($c_count / $cpp) - 1) * $cpp;
				}
				redirect($clink."&amp;c_start=".(isset($c_start) && isnum($c_start) ? $c_start : ""));
			} else {
				if (!dbcount("(".$ccol.")", $cdb, $ccol."='".$cid."'")) { redirect(BASEDIR."index.php"); }
				if ($comment_name && $comment_message) {
					require_once INCLUDES."flood_include.php";
					if (!flood_control("comment_datestamp", DB_COMMENTS, "comment_ip='".USER_IP."'")) {
						$result = dbquery(
							"INSERT INTO ".DB_COMMENTS." (
								comment_item_id, comment_type, comment_name, comment_message, comment_datestamp,
								comment_ip, comment_ip_type, comment_hidden
							) VALUES (
								'".$cid."', '".$ctype."', '".$comment_name."', '".$comment_message."', '".time()."',
								'".USER_IP."', '".USER_IP_TYPE."', '0'
							)"
						);
					}
				}
				if ($settings['comments_sorting'] == "ASC") {
					$c_count = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$cid."'
										AND comment_type='".$ctype."'");
					$c_start = (ceil($c_count / $cpp) - 1) * $cpp;
				} else {
					$c_start = 0;
				}
				redirect($clink."&amp;c_start=".$c_start);
			}
		}
		$c_arr = array(
			"c_con" => array(),
			"c_info" => array(
				"c_makepagenav" => false,
				"admin_link" => false
			)
		);
		$c_rows = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$cid."'
							AND comment_type='".$ctype."' AND comment_hidden='0'");
		if (!isset($_GET['c_start']) && $c_rows > $cpp) {
			$_GET['c_start'] = (ceil($c_rows / $cpp) - 1) * $cpp;
		}
		if (!isset($_GET['c_start']) || !isnum($_GET['c_start'])) { $_GET['c_start'] = 0; }
		$result = dbquery(
			"SELECT tcm.comment_id, tcm.comment_name, tcm.comment_message, tcm.comment_datestamp,
					tcu.user_name, tcu.user_avatar, tcu.user_status
			FROM ".DB_COMMENTS." tcm
			LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
			WHERE comment_item_id='".$cid."' AND comment_type='".$ctype."' AND comment_hidden='0'
			ORDER BY comment_datestamp ".$settings['comments_sorting']." LIMIT ".$_GET['c_start'].",".$cpp
		);
		if (dbrows($result)) {
			$i = ($settings['comments_sorting'] == "ASC" ? $_GET['c_start']+1 : $c_rows - $_GET['c_start']);
			if ($c_rows > $cpp) {
				$c_arr['c_info']['c_makepagenav'] = makepagenav($_GET['c_start'], $cpp, $c_rows, 3, $clink."&amp;", "c_start");
			}
			while ($data = dbarray($result)) {
				$c_arr['c_con'][$i]['comment_id'] = $data['comment_id'];
				$c_arr['c_con'][$i]['edit_dell'] = false;
				$c_arr['c_con'][$i]['i'] = $i;
				if ($data['user_name']) {
					$c_arr['c_con'][$i]['comment_name'] = profile_link($data['comment_name'], $data['user_name'], $data['user_status']);
				} else {
					$c_arr['c_con'][$i]['comment_name'] = $data['comment_name'];
				}
				
				//Add user avatar in comments new feature in v7.02.04
				if ($data['user_avatar'] && file_exists(IMAGES."avatars/".$data['user_avatar']) && $data['user_status']!=6 && $data['user_status']!=5) {
					$c_arr['c_con'][$i]['user_avatar'] = "<img src='".IMAGES."avatars/".$data['user_avatar']."' width='50' height='50' alt='".$data['comment_name']."' />";
				} else {
					$c_arr['c_con'][$i]['user_avatar'] = "<img src='".IMAGES."avatars/noavatar50.png' alt='".$data['comment_name']."' />";
				}
				$c_arr['c_con'][$i]['comment_datestamp'] = $locale['global_071'].showdate("longdate", $data['comment_datestamp']);
				$c_arr['c_con'][$i]['comment_message'] = "<!--comment_message-->\n".nl2br(parseubb(parsesmileys($data['comment_message'])));

				if ((iADMIN && checkrights("C")) || (iMEMBER && $data['comment_name'] == $userdata['user_id'] && isset($data['user_name']))) {
					$c_arr['c_con'][$i]['edit_dell'] = "<!--comment_actions-->\n";
					$c_arr['c_con'][$i]['edit_dell'] .= "<a href='".FUSION_REQUEST."&amp;c_action=edit&amp;comment_id=".$data['comment_id']."#edit_comment'>";
					$c_arr['c_con'][$i]['edit_dell'] .= $locale['c108']."</a> |\n";
					$c_arr['c_con'][$i]['edit_dell'] .= "<a href='".FUSION_REQUEST."&amp;c_action=delete&amp;comment_id=".$data['comment_id']."'>";
					$c_arr['c_con'][$i]['edit_dell'] .= $locale['c109']."</a>";
				}
				$settings['comments_sorting'] == "ASC" ? $i++ :	$i--;
			}
			if (iADMIN && checkrights("C")) {
				$c_arr['c_info']['admin_link'] = "<!--comment_admin-->\n";
				$c_arr['c_info']['admin_link'] .= "<a href='".ADMIN."comments.php".$aidlink."&amp;ctype=".$ctype."&amp;cid=".$cid."'>".$locale['c106']."</a>";
			}
		}

		// Render comments
		echo "<a id='comments' name='comments'></a>";
		render_comments($c_arr['c_con'], $c_arr['c_info']);

		// Add / edit comment
		opentable($locale['c102']);
		if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit")
					&& (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
			$eresult = dbquery(
				"SELECT tcm.comment_id, tcm.comment_name, tcm.comment_message, tcu.user_name
				FROM ".DB_COMMENTS." tcm
				LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
				WHERE comment_id='".$_GET['comment_id']."' AND comment_item_id='".$cid."'
					AND comment_type='".$ctype."' AND comment_hidden='0'"
			);
			if (dbrows($eresult)) {
				$edata = dbarray($eresult);
				if ((iADMIN && checkrights("C"))
						|| (iMEMBER && $edata['comment_name'] == $userdata['user_id']
						&& isset($edata['user_name']))) {
					$clink .= "&amp;c_action=edit&amp;comment_id=".$edata['comment_id'];
					$comment_message = $edata['comment_message'];
				}
			} else {
				$comment_message = "";
			}
		} else {
			$comment_message = "";
		}
		if (iMEMBER || $settings['guestposts'] == "1") {
			require_once INCLUDES."bbcode_include.php";
			echo "<a id='edit_comment' name='edit_comment'></a>\n";
			echo "<form name='inputform' method='post' action='".$clink."'>\n";
			if (iGUEST) {
				echo "<div align='center' class='tbl'>\n".$locale['c104']."<br />\n";
				echo "<input type='text' name='comment_name' maxlength='30' class='textbox' style='width:360px' />\n";
				echo "</div>\n";
			}
			echo "<div align='center' class='tbl'>\n";
			echo "<textarea name='comment_message' cols='70' rows='6' class='textbox' style='width:360px'>".$comment_message."</textarea><br />\n";
			echo display_bbcodes("360px", "comment_message");
			if (iGUEST && (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT))) {
				$_CAPTCHA_HIDE_INPUT = false;
				echo "<div style='width:360px; margin:10px auto;'>";
				echo $locale['global_150']."<br />\n";
				include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
				if (!$_CAPTCHA_HIDE_INPUT) {
					echo "<br />\n<label for='captcha_code'>".$locale['global_151']."</label>";
					echo "<br />\n<input type='text' id='captcha_code' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' />\n";
				}
				echo "</div>\n";
			}
			echo "<input type='submit' name='post_comment' value='".($comment_message ? $locale['c103'] : $locale['c102'])."' class='button' />";
			echo "</div>\n</form>\n";
		} else {
			echo $locale['c105']."\n";
		}
		closetable();
	}
}
?>