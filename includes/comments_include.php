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

/**
 * @param $comment_type - abbr or short ID
 * @param $comment_db - Current Application DB - DB_BLOG for example.
 * @param $comment_col - current sql primary key column - 'blog_id' for example
 * @param $comment_item_id - current sql primary key value '$_GET['blog_id']' for example
 * @param $clink - current page link 'FUSION_SELF' is ok.
 */

 function showcomments($comment_type, $comment_db, $comment_col, $comment_item_id, $clink) {
	global $settings, $locale, $userdata, $aidlink;
	$link = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
	$link = preg_replace("^(&amp;|\?)c_action=(edit|delete)&amp;comment_id=\d*^", "", $link);
	$_GET['comment'] = isset($_GET['comment']) && isnum($_GET['comment']) ? $_GET['comment'] : 0;
	$cpp = $settings['comments_per_page'];
	if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "delete") && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
		if ((iADMIN && checkrights("C")) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS, "comment_id='".$_GET['comment_id']."' AND comment_name='".$userdata['user_id']."'"))) {
			$result = dbquery("DELETE FROM ".DB_COMMENTS."
				WHERE comment_id='".$_GET['comment_id']."'".(iADMIN ? "" : "
				AND comment_name='".$userdata['user_id']."'"));
		}
		redirect($clink.($settings['comments_sorting'] == "ASC" ? "" : "&amp;c_start=0"));
	}

	if ($settings['comments_enabled'] == "1") {
		if ((iMEMBER || $settings['guestposts'] == "1") && isset($_POST['post_comment'])) {
			if (!iMEMBER && $settings['guestpost'] == 1) {
				if (!isset($_POST['comment_name'])) {
					redirect($link);
				}
				if (isnum($_POST['comment_name'])) {
					$_POST['comment_name'] = '';
				}
				$_CAPTCHA_IS_VALID = FALSE;
				include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php";
				if (!isset($_POST['captcha_code']) || $_CAPTCHA_IS_VALID == FALSE) {
					redirect($link);
				}
			}

			$comment_data = array(
				'comment_id' => isset($_GET['comment_id']) && isnum($_GET['comment_id']) ? $_GET['comment_id'] : 0,
				'comment_name' => iMEMBER ? $userdata['user_id'] : form_sanitizer($_POST['comment_name'], '', 'comment_name'),
				'comment_message' => form_sanitizer($_POST['comment_message'], '', 'comment_message'),
				'comment_datestamp' => time(),
				'comment_item_id' => $comment_item_id,
				'comment_type' => $comment_type,
				'comment_cat' => 0,
				'comment_ip' => USER_IP,
				'comment_ip_type' => USER_IP_TYPE,
				'comment_hidden' => 0,
			);

			if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit") && $comment_data['comment_id']) {
				$comment_updated = FALSE;
				if ((iADMIN && checkrights("C")) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS, "comment_id='".$comment_data['comment_id']."' 
				AND comment_item_id='".$comment_item_id."'															
				AND comment_type='".$comment_type."' 
				AND comment_name='".$userdata['user_id']."' 
				AND comment_hidden='0'"))) {
				dbquery_insert(DB_COMMENTS, $comment_data, 'update');

				if ($comment_data['comment_message']) {
					$result = dbquery("UPDATE ".DB_COMMENTS." SET comment_message='".$comment_data['comment_message']."'
  									   WHERE comment_id='".$_GET['comment_id']."' ".(iADMIN ? "" : "AND comment_name='".$userdata['user_id']."'"));
						if ($result) $comment_updated = TRUE;
					}
				}

				if ($comment_updated) {
					if ($settings['comments_sorting'] == "ASC") {
						$c_operator = "<=";
					} else {
						$c_operator = ">=";
					}
					$c_count = dbcount("(comment_id)", DB_COMMENTS, "comment_id".$c_operator."'".$comment_data['comment_id']."'
								AND comment_item_id='".$comment_item_id."'
								AND comment_type='".$comment_type."'");
					$c_start = (ceil($c_count/$cpp)-1)*$cpp;
				}
				redirect($clink."&amp;c_start=".(isset($c_start) && isnum($c_start) ? $c_start : ""));
			} else {
				if (!dbcount("(".$comment_col.")", $comment_db, $comment_col."='".$comment_item_id."'")) redirect(BASEDIR."index.php");
                $id = 0;
				if ($comment_data['comment_name'] && $comment_data['comment_message']) {
					require_once INCLUDES."flood_include.php";
					if (!flood_control("comment_datestamp", DB_COMMENTS, "comment_ip='".USER_IP."'")) {
						dbquery_insert(DB_COMMENTS, $comment_data, 'save');
						$id = dblastid();
					}
				}

				if ($settings['comments_sorting'] == "ASC") {
					$c_count = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$comment_item_id."' AND comment_type='".$comment_type."'");
					$c_start = (ceil($c_count/$cpp)-1)*$cpp;
				} else {
					$c_start = 0;
				}
                //if (!$settings['site_seo']) {
                redirect($clink."&amp;c_start=".$c_start."#c".$id);
                //}
			}
		}
		$c_arr = array(
			"c_con" => array(),
			"c_info" => array("c_makepagenav" => FALSE, "admin_link" => FALSE)
		);

		$c_rows = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$comment_item_id."' AND comment_type='".$comment_type."' AND comment_hidden='0'");

		if (!isset($_GET['c_start']) && $c_rows > $cpp) {
			$_GET['c_start'] = (ceil($c_rows/$cpp)-1)*$cpp;
		}

		if (!isset($_GET['c_start']) || !isnum($_GET['c_start'])) {
			$_GET['c_start'] = 0;
		}

		$result = dbquery("SELECT tcm.comment_id, tcm.comment_name, tcm.comment_message, tcm.comment_datestamp,
					tcu.user_id, tcu.user_name, tcu.user_avatar, tcu.user_status
					FROM ".DB_COMMENTS." tcm
					LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
					WHERE comment_item_id='".$comment_item_id."' AND comment_type='".$comment_type."' AND comment_hidden='0'
					ORDER BY comment_datestamp ".$settings['comments_sorting']." LIMIT ".$_GET['c_start'].",".$cpp);

			if (dbrows($result)>0) {
				$i = ($settings['comments_sorting'] == "ASC" ? $_GET['c_start']+1 : $c_rows-$_GET['c_start']);
				if ($c_rows > $cpp) {
					$c_arr['c_info']['c_makepagenav'] = makepagenav($_GET['c_start'], $cpp, $c_rows, 3, $clink."&amp;", "c_start");
				}
			while ($data = dbarray($result)) {
				$c_arr['c_con'][$i]['comment_id'] = $data['comment_id'];
				$c_arr['c_con'][$i]['edit_dell'] = FALSE;
				$c_arr['c_con'][$i]['i'] = $i;
				if ($data['user_name']) {
					$c_arr['c_con'][$i]['comment_name'] = profile_link($data['comment_name'], $data['user_name'], $data['user_status'], 'strong text-dark');
				} else {
					$c_arr['c_con'][$i]['comment_name'] = $data['comment_name'];
				}
				$c_arr['c_con'][$i]['user_avatar'] = display_avatar($data, '35px', '', true, 'img-rounded');
				$c_arr['c_con'][$i]['user'] = array(
					'user_id' => $data['user_id'],
					'user_name' => $data['user_name'],
					'user_avatar' => $avatar = ($data['user_avatar'] !=='') && file_exists(IMAGES.'avatars/'.$data['user_avatar']) ? IMAGES.'avatars/'.$data['user_avatar'] : IMAGES."avatars/noavatar50.png",
					'user_status' => $data['user_status'],
				);

				$c_arr['c_con'][$i]['comment_datestamp'] = showdate('shortdate', $data['comment_datestamp']);
				$c_arr['c_con'][$i]['comment_time'] = timer($data['comment_datestamp']);
				$c_arr['c_con'][$i]['comment_message'] = "<!--comment_message-->\n".nl2br(parseubb(parsesmileys($data['comment_message'])));
				if ((iADMIN && checkrights("C")) || (iMEMBER && $data['comment_name'] == $userdata['user_id'] && isset($data['user_name']))) {
					$edit_link = clean_request('c_action=edit&comment_id='.$data['comment_id'], array('c_action', 'comment_id'), false)."#edit_comment";
					$delete_link = clean_request('c_action=delete&comment_id='.$data['comment_id'], array('c_action', 'comment_id'), false);
					$c_arr['c_con'][$i]['edit_link'] = array('link'=>$edit_link, 'name'=>$locale['c108']);
					$c_arr['c_con'][$i]['delete_link'] = array('link'=>$delete_link, 'name'=>$locale['c109']);
					$c_arr['c_con'][$i]['edit_dell'] = "<!--comment_actions-->\n";
					$c_arr['c_con'][$i]['edit_dell'] .= "<div class='btn-group'>";
					$c_arr['c_con'][$i]['edit_dell'] .= "<a class='btn btn-xs btn-default' href='".$edit_link."'>";
					$c_arr['c_con'][$i]['edit_dell'] .= $locale['c108']."</a>\n";
					$c_arr['c_con'][$i]['edit_dell'] .= "<a class='btn btn-xs btn-default' href='".$delete_link."' onclick=\"return confirm('".$locale['c110']."');\">";
					$c_arr['c_con'][$i]['edit_dell'] .= "<i class='fa fa-trash'></i> ".$locale['c109']."</a>";
					$c_arr['c_con'][$i]['edit_dell'] .= "</div>\n";
				}
				$settings['comments_sorting'] == "ASC" ? $i++ : $i--;
			}
			if (iADMIN && checkrights("C")) {
				$c_arr['c_info']['admin_link'] = "<!--comment_admin-->\n";
				$c_arr['c_info']['admin_link'] .= "<a href='".ADMIN."comments.php".$aidlink."&amp;ctype=".$comment_type."&amp;comment_item_id=".$comment_item_id."'>".$locale['c106']."</a>";
			}
		}

		opentable($locale['c102']);
		$comment_message = "";
		if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit") && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
			$eresult = dbquery("SELECT tcm.comment_id, tcm.comment_name, tcm.comment_message, tcu.user_name
				FROM ".DB_COMMENTS." tcm
				LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
				WHERE comment_id='".$_GET['comment_id']."' AND comment_item_id='".$comment_item_id."'
				AND comment_type='".$comment_type."' AND comment_hidden='0'");
			if (dbrows($eresult)>0) {
				$edata = dbarray($eresult);
				if ((iADMIN && checkrights("C")) || (iMEMBER && $edata['comment_name'] == $userdata['user_id'] && isset($edata['user_name']))) {
					$clink .= "&amp;c_action=edit&amp;comment_id=".$edata['comment_id'];
					$comment_message = $edata['comment_message'];
				}
			} else {
				$comment_message = "";
			}
		}

		if (iMEMBER || $settings['guestposts'] == "1") {
			require_once INCLUDES."bbcode_include.php";
			echo "<a id='edit_comment' name='edit_comment'></a>\n";
            echo openform('inputform', 'post', $clink, array('class' => 'm-b-20', 'max_tokens' => 1));
			if (iGUEST) {
				echo form_text('comment_name', $locale['c104'], '', array('max_length'=>30));
			}
			echo form_textarea('comment_message', '', $comment_message, array('required' => 1, 'autosize'=>1, 'form_name'=>'inputform', 'bbcode'=>1));

			if (iGUEST && (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT))) {
				$_CAPTCHA_HIDE_INPUT = FALSE;
				echo "<div style='width:360px; margin:10px auto;'>";
				echo $locale['global_150']."<br />\n";
				include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
				if (!$_CAPTCHA_HIDE_INPUT) {
					echo "<br />\n<label for='captcha_code'>".$locale['global_151']."</label>";
					echo "<br />\n<input type='text' id='captcha_code' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' />\n";
				}
				echo "</div>\n";
			}
			echo form_button('post_comment', $comment_message ? $locale['c103'] : $locale['c102'], $comment_message ? $locale['c103'] : $locale['c102'], array('class' => 'btn-success m-t-10'));
			echo closeform();
		} else {
			echo "<div class='well'>\n";
			echo $locale['c105']."\n";
			echo "</div>\n";
		}
		closetable();

		echo "<a id='comments' name='comments'></a>";
		render_comments($c_arr['c_con'], $c_arr['c_info']);
	}
}
