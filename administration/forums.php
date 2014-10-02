<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forums.php
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
require_once "../maincore.php";
require_once INCLUDES."bbcode_include.php";
if (!checkrights("F") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/forums.php";
if (isset($_GET['action']) && $_GET['action'] == "prune") {
	require_once "forums_prune.php";
}
if (isset($_GET['action']) && $_GET['action'] == "refresh") {
	$i = 1;
	$k = 1;
	$result = dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='0' ORDER BY forum_order");
	while ($data = dbarray($result)) {
		$result2 = dbquery("UPDATE ".DB_FORUMS." SET forum_order='$i' ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$data['forum_id']."'");
		$result2 = dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='".$data['forum_id']."' ORDER BY forum_order");
		while ($data2 = dbarray($result2)) {
			$result3 = dbquery("UPDATE ".DB_FORUMS." SET forum_order='$k' ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$data2['forum_id']."'");
			$k++;
		}
		$i++;
		$k = 1;
	}
	redirect(FUSION_SELF.$aidlink);
}
if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "savecn") {
		$message = $locale['410'];
	} elseif ($_GET['status'] == "savecu") {
		$message = $locale['411'];
		//} elseif ($_GET['status'] == "savect") {
		//$message = $locale['516'];
		//} elseif ($_GET['status'] == "saveft") { // enter a unique name.
		//  $message = $locale['517'];
	} elseif ($_GET['status'] == "savefn") {
		$message = $locale['510'];
	} elseif ($_GET['status'] == "savefu") {
		$message = $locale['511'];
	} elseif ($_GET['status'] == "savefm") {
		$message = $locale['515'];
	} elseif ($_GET['status'] == "delcn") {
		$message = $locale['412']."<br />\n<span class='small'>".$locale['413']."</span>";
	} elseif ($_GET['status'] == "delcy") {
		$message = $locale['414'];
	} elseif ($_GET['status'] == "delfn") {
		$message = $locale['512']."<br />\n<span class='small'>".$locale['513']."</span>";
	} elseif ($_GET['status'] == "delfy") {
		$message = $locale['514'];
	}
	if ($message) {
		echo "<div id='close-message'><div class='alert alert-info m-t-10 admin-message'>".$message."</div></div>\n";
	}
}
if (isset($_POST['save_cat'])) {
	$cat_name = form_sanitizer($_POST['cat_name'], '', 'cat_name'); // trim(stripinput($_POST['cat_name'])); // required.
	$cat_description = trim(stripinput($_POST['cat_description']));
	$forum_language = stripinput($_POST['forum_language']);
	if (!defined('FUSION_NULL')) {
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) && (isset($_GET['t']) && $_GET['t'] == "cat")) {
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_name='$cat_name', forum_description='$cat_description', forum_language='$forum_language' WHERE forum_id='".$_GET['forum_id']."'");
			redirect(FUSION_SELF.$aidlink."&status=savecu");
		} else {
			$cat_order = isnum($_POST['cat_order']) ? $_POST['cat_order'] : "";
			if (!$cat_order) $cat_order = dbresult(dbquery("SELECT MAX(forum_order) FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='0'"), 0)+1;
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='0' AND forum_order>='$cat_order'");
			$result = dbquery("INSERT INTO ".DB_FORUMS." (forum_cat, forum_name, forum_order, forum_description, forum_moderators, forum_access, forum_post, forum_reply, forum_poll, forum_vote, forum_attach, forum_lastpost, forum_lastuser, forum_merge, forum_language) VALUES ('0', '$cat_name', '$cat_order', '$cat_description', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '$forum_language')");
			redirect(FUSION_SELF.$aidlink."&status=savecn");
		}
	}
} elseif (isset($_POST['save_forum'])) {
	$forum_name = form_sanitizer($_POST['forum_name'], '', 'forum_name'); //trim(stripinput($_POST['forum_name']));
	$forum_description = trim(stripinput($_POST['forum_description']));
	$forum_cat = isnum($_POST['forum_cat']) ? $_POST['forum_cat'] : 0;
	$forum_language = stripinput($_POST['forum_language_2']);
	if ($forum_name != "") {
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) && (isset($_GET['t']) && $_GET['t'] == "forum")) {
			$forum_mods = $_POST['forum_mods'];
			$forum_access = isnum($_POST['forum_access']) ? $_POST['forum_access'] : 0;
			$forum_post = isnum($_POST['forum_post']) ? $_POST['forum_post'] : 0;
			$forum_reply = isnum($_POST['forum_reply']) ? $_POST['forum_reply'] : 0;
			$forum_attach = isnum($_POST['forum_attach']) ? $_POST['forum_attach'] : 0;
			$forum_attach_download = isnum($_POST['forum_attach_download']) ? $_POST['forum_attach_download'] : 0;
			$forum_poll = isnum($_POST['forum_poll']) ? $_POST['forum_poll'] : 0;
			$forum_vote = isnum($_POST['forum_vote']) ? $_POST['forum_vote'] : 0;
			$forum_merge = (isset($_POST['forum_merge']) && isnum($_POST['forum_merge']) ? $_POST['forum_merge'] : 0);
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_name='".$forum_name."', forum_cat='".$forum_cat."', forum_description='".$forum_description."', forum_moderators='".$forum_mods."', forum_access='".$forum_access."', forum_post='".$forum_post."', forum_reply='".$forum_reply."', forum_attach='".$forum_attach."', forum_attach_download='".$forum_attach_download."', forum_poll='".$forum_poll."', forum_vote='".$forum_vote."', forum_merge='".$forum_merge."', forum_language='".$forum_language."' WHERE forum_id='".$_GET['forum_id']."'");
			redirect(FUSION_SELF.$aidlink."&status=savefu");
		} else {
			$uniqueCheck = dbcount("(forum_id)", DB_FORUMS, "".(multilang_table("FO") ? "forum_language='".LANGUAGE."' AND" : "")." forum_cat='".$forum_cat."' AND forum_name='".$forum_name."'");
			if ($uniqueCheck != 0) {
				$defender->stop();
				$defender->addNotice($locale['517']);
			}
			$forum_order = isnum($_POST['forum_order']) ? $_POST['forum_order'] : "";
			if (!$forum_order) $forum_order = dbresult(dbquery("SELECT MAX(forum_order) FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='$forum_cat'"), 0)+1;
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='$forum_cat' AND forum_order>='$forum_order'");
			$result = dbquery("INSERT INTO ".DB_FORUMS." (forum_cat, forum_name, forum_order, forum_description, forum_moderators, forum_access, forum_post, forum_reply, forum_attach, forum_attach_download, forum_poll, forum_vote, forum_lastpost, forum_lastuser, forum_merge, forum_language) VALUES ('".$forum_cat."', '".$forum_name."', '".$forum_order."', '".$forum_description."', '103', '101', '101', '101', '0', '0', '0', '0', '0', '0', '0', '".$forum_language."')");
			redirect(FUSION_SELF.$aidlink."&status=savefn");
		}
	}
} elseif ((isset($_GET['action']) && $_GET['action'] == "mu") && (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) && (isset($_GET['order']) && isnum($_GET['order']))) {
	if (isset($_GET['t']) && $_GET['t'] == "cat") {
		$data = dbarray(dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='0' AND forum_order='".$_GET['order']."'"));
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$data['forum_id']."'");
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$_GET['forum_id']."'");
	} elseif ((isset($_GET['t']) && $_GET['t'] == "forum") && (isset($_GET['cat']) && isnum($_GET['cat']))) {
		$data = dbarray(dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='".$_GET['cat']."' AND forum_order='".$_GET['order']."'"));
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$data['forum_id']."'");
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$_GET['forum_id']."'");
	}
	redirect(FUSION_SELF.$aidlink);
} elseif ((isset($_GET['action']) && $_GET['action'] == "md") && (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) && (isset($_GET['order']) && isnum($_GET['order']))) {
	if (isset($_GET['t']) && $_GET['t'] == "cat") {
		$data = dbarray(dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='0' AND forum_order='".$_GET['order']."'"));
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$data['forum_id']."'");
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$_GET['forum_id']."'");
	} elseif ((isset($_GET['t']) && $_GET['t'] == "forum") && (isset($_GET['cat']) && isnum($_GET['cat']))) {
		$data = dbarray(dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='".$_GET['cat']."' AND forum_order='".$_GET['order']."'"));
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$data['forum_id']."'");
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$_GET['forum_id']."'");
	}
	redirect(FUSION_SELF.$aidlink);
} elseif ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) && (isset($_GET['t']) && $_GET['t'] == "cat")) {
	if (!dbcount("(forum_id)", DB_FORUMS, "forum_cat='".$_GET['forum_id']."'")) {
		$data = dbarray(dbquery("SELECT forum_order FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$_GET['forum_id']."'"));
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='0' AND forum_order>'".$data['forum_order']."'");
		$result = dbquery("DELETE FROM ".DB_FORUMS." WHERE forum_id='".$_GET['forum_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=delcy");
	} else {
		redirect(FUSION_SELF.$aidlink."&status=delcn");
	}
} elseif ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) && (isset($_GET['t']) && $_GET['t'] == "forum")) {
	if (!dbcount("(thread_id)", DB_THREADS, "forum_id='".$_GET['forum_id']."'")) {
		$data = dbarray(dbquery("SELECT forum_cat, forum_order FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$_GET['forum_id']."'"));
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='".$data['forum_cat']."' AND forum_order>'".$data['forum_order']."'");
		$result = dbquery("DELETE FROM ".DB_FORUMS." WHERE forum_id='".$_GET['forum_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=delfy");
	} else {
		redirect(FUSION_SELF.$aidlink."&status=delfn");
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['forum_id']) && isnum($_GET['forum_id']))) {
	if (isset($_GET['t']) && $_GET['t'] == "cat") {
		$result = dbquery("SELECT forum_id, forum_name, forum_description,forum_language FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$_GET['forum_id']."' LIMIT 1");
		if (dbrows($result)) {
			$data = dbarray($result);
			$cat_name = $data['forum_name'];
			$cat_description = $data['forum_description'];
			$forum_language = $data['forum_language'];
			$cat_title = $locale['401'];
			$cat_action = FUSION_SELF.$aidlink."&amp;action=edit&amp;forum_id=".$data['forum_id']."&amp;t=cat";
			$forum_title = $locale['500'];
			$forum_action = FUSION_SELF.$aidlink;
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} elseif (isset($_GET['t']) && $_GET['t'] == "forum") {
		$result = dbquery("SELECT
                forum_name, forum_description, forum_cat, forum_moderators,
                forum_access, forum_post, forum_reply, forum_attach, forum_attach_download, forum_poll,
                forum_vote, forum_merge, forum_language
                FROM ".DB_FORUMS."
                WHERE forum_id='".$_GET['forum_id']."'
                LIMIT 1");
		if (dbrows($result)) {
			$data = dbarray($result);
			$forum_name = $data['forum_name'];
			$forum_description = $data['forum_description'];
			$forum_cat = $data['forum_cat'];
			$forum_access = $data['forum_access'];
			$forum_post = $data['forum_post'];
			$forum_reply = $data['forum_reply'];
			$forum_attach = $data['forum_attach'];
			$forum_attach_download = $data['forum_attach_download'];
			$forum_poll = $data['forum_poll'];
			$forum_vote = $data['forum_vote'];
			$forum_merge = $data['forum_merge'];
			$forum_language = $data['forum_language'];
			$forum_title = $locale['501'];
			$forum_action = FUSION_SELF.$aidlink."&amp;action=edit&amp;forum_id=".$_GET['forum_id']."&amp;t=forum";
			$cat_title = $locale['400'];
			$cat_action = FUSION_SELF.$aidlink;
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
} else {
	$cat_name = "";
	$cat_description = "";
	$cat_order = "";
	$cat_title = $locale['400'];
	$cat_action = FUSION_SELF.$aidlink;
	$forum_name = "";
	$forum_description = "";
	$forum_cat = 0;
	$forum_order = "";
	$forum_access = 0;
	$forum_post = 0;
	$forum_reply = 0;
	$forum_attach = 0;
	$forum_attach_download = 0;
	$forum_poll = 0;
	$forum_vote = 0;
	$forum_merge = 0;
	$forum_language = LANGUAGE;
	$forum_title = $locale['500'];
	$forum_action = FUSION_SELF.$aidlink;
}
if (!isset($_GET['t']) || $_GET['t'] != "forum") {
	opentable($cat_title);
	echo openform('addcat', 'addcat', 'post', $cat_action);
	echo "<table align='center' cellpadding='0' cellspacing='0' class='table table-responsive'>\n<tr>\n";
	echo "<td class='tbl'>\n";
	echo form_text($locale['420'], 'cat_name', 'cat_name', $cat_name, array('required' => 1,
																			'error_text' => $locale['516']));
	echo "</td>\n";
	if (!isset($_GET['action']) || $_GET['action'] != "edit") {
		echo "<td width='150' class='tbl'>";
		echo form_text($locale['421'], 'cat_order', 'cat_order', $cat_order, array('number' => 1));
		echo "</td>\n";
	}
	echo "</tr>\n";
	if (multilang_table("FO")) {
		echo "<tr><td class='tbl' colspan='2'>\n";
		echo form_select($locale['global_ML100'], 'forum_language', 'forum_language', $language_opts, $forum_language, array('placeholder' => $locale['choose']));
		$opts = get_available_languages_list($selected_language = "$forum_language");
		echo "</td>\n";
		echo "</tr>\n";
	} else {
		echo form_hidden('', 'forum_language', 'forum_language', $forum_language);
	}
	echo "<tr>\n<td class='tbl' colspan='2'>\n";
	echo form_textarea($locale['420b'], 'cat_description', 'cat_description', $cat_description);
	echo display_bbcodes("280px;", "cat_description", "addcat", "b|i|u|color|url|center|size|big|small")."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>\n";
	echo form_button($locale['422'], 'save_cat', 'save_cat', $locale['422'], array('class' => 'btn-primary'));
	echo "</td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();
}
if (!isset($_GET['t']) || $_GET['t'] != "cat") {
	$cat_opts = array();
	$result2 = dbquery("SELECT forum_id, forum_name, forum_moderators, forum_language FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='0' ORDER BY forum_order");
	if (dbrows($result2)) {
		while ($data2 = dbarray($result2)) {
			$cat_opts[$data2['forum_id']] = $data2['forum_name'];
		}
		$_access = getusergroups();
		$access_opts['0'] = $locale['531'];
		while (list($key, $option) = each($_access)) {
			$access_opts[$option['0']] = $option['1'];
		}
		opentable($forum_title);
		echo openform('addforum', 'addforum', 'post', $forum_action, array('downtime' => 0, 'notice' => 0));
		echo "<table cellpadding='0' cellspacing='0' class='table table-responsive'>\n<tr>\n";
		echo "<td colspan='2' class='tbl'>\n";
		echo form_text($locale['520'], 'forum_name', 'forum_name', $forum_name, array('required' => 1,
																					  'error_text' => $locale['517']));
		echo "</td>\n";
		echo "</tr>\n";
		if (multilang_table("FO")) {
			echo "<tr><td class='tbl' colspan='2'>\n";
			echo form_select($locale['global_ML100'], 'forum_language_2', 'forum_language_2', $language_opts, $forum_language, array('placeholder' => $locale['choose']));
			echo "</td>\n";
			echo "</tr>\n";
		} else {
			echo form_hidden('', 'forum_language_2', 'forum_language_2', $forum_language);
		}
		echo "<tr>\n";
		echo "<td colspan='2' class='tbl'>\n";
		echo form_textarea($locale['521'], 'forum_description', 'forum_description', $forum_description);
		echo display_bbcodes("280px;", "forum_description", "addforum", "b|i|u|color|url|center|size|big|small")."</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td class='tbl'>\n";
		echo form_select($locale['522'], 'forum_cat', 'forum_cat', $cat_opts, $forum_cat, array('placeholder' => $locale['choose']));
		echo "</td>\n<td width='25%' class='tbl'>\n";
		if (!isset($_GET['action']) || $_GET['action'] != "edit") {
			echo form_text($locale['523'], 'forum_order', 'forum_order', $forum_order, array('number' => 1,
																							 'inline' => 1));
			echo "</td>\n</tr>\n<tr>\n";
			echo "<td align='center' colspan='2' class='tbl'>\n";
			echo form_button($locale['532'], 'save_forum', 'save_forum', $locale['532'], array('class' => 'btn-primary'));
		}
		echo "</td>\n</tr>\n</table>\n";
		if (isset($_GET['action']) && $_GET['action'] == "edit") {
			echo "<table align='center' cellpadding='0' cellspacing='0' class='table table-responsive'>\n<tr>\n";
			echo "<td class='tbl2' colspan='2'><strong>".$locale['524']."</strong></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='forum_access'>".$locale['525']."</label></td>\n";
			echo "<td class='tbl'>\n";
			echo form_select('', 'forum_access', 'forum_access', $access_opts, $forum_access, array('placeholder' => $locale['choose']));
			echo "</td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='forum_post'>".$locale['526']."</label></td>\n<td class='tbl'>\n";
			echo form_select('', 'forum_post', 'forum_post', $access_opts, $forum_post, array('placeholder' => $locale['choose']));
			echo "</td>\n</tr>\n<tr>\n";
			echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='forum_reply'>".$locale['527']."</label></td>\n<td class='tbl'>\n";
			echo form_select('', 'forum_reply', 'forum_reply', $access_opts, $forum_reply, array('placeholder' => $locale['choose']));
			echo "</td>\n</tr>\n<tr>\n";
			echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='forum_attach'>".$locale['528']."</label></td>\n<td class='tbl'>\n";
			echo form_select('', 'forum_attach', 'forum_attach', $access_opts, $forum_attach, array('placeholder' => $locale['choose']));
			echo "</td>\n</tr>\n<tr>\n";
			echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='forum_attach_download'>".$locale['535']."</label></td>\n<td class='tbl'>\n";
			echo form_select('', 'forum_attach_download', 'forum_attach_download', $access_opts, $forum_attach_download, array('placeholder' => $locale['choose']));
			echo "</td>\n</tr>\n<tr>\n";
			echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='forum_poll'>".$locale['529']."</label></td>\n<td class='tbl'>\n";
			echo form_select('', 'forum_poll', 'forum_poll', $access_opts, $forum_poll, array('placeholder' => $locale['choose']));
			echo "</td>\n</tr>\n<tr>\n";
			echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='forum_vote'>".$locale['530']."</label></td>\n<td class='tbl'>\n";
			echo form_select('', 'forum_vote', 'forum_vote', $access_opts, $forum_poll, array('placeholder' => $locale['choose']));
			echo "</tr>\n<tr>\n";
			echo "<td class='tbl2' colspan='2' style='font-weight:bold;'>".$locale['540']."</td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td width='1%' class='tbl' style='white-space:nowrap'><label for='forum_merge'>".$locale['541']."</label></td>\n<td class='tbl'>\n";
			$array = array('1' => $locale['542'], '0' => $locale['543']);
			echo form_select('', 'forum_merge', 'forum_merge', $array, $forum_merge, array('placeholder' => $locale['choose']));
			echo "</td>\n</tr>\n";
			if (!isset($_GET['action']) || $_GET['action'] != "edit") {
				echo "<tr>\n<td align='center' colspan='2' class='tbl'>\n";
				echo form_button($locale['532'], 'save_forum', 'save_forum', $locale['532'], array('class' => 'btn-primary'));
				echo "</td>\n";
				echo "</tr>\n</table>\n";
			}
		}
		if (!isset($_GET['action'])) echo "\n</form>";
		if (isset($_GET['action']) && $_GET['action'] == "edit") {
			$mod_groups = getusergroups();
			$mods1_user_id = array();
			$mods1_user_name = array();
			while (list($key, $mod_group) = each($mod_groups)) {
				if ($mod_group['0'] != "0" && $mod_group['0'] != "101" && $mod_group['0'] != "103") {
					if (!preg_match("(^{$mod_group['0']}$|^{$mod_group['0']}\.|\.{$mod_group['0']}\.|\.{$mod_group['0']}$)", $data['forum_moderators'])) {
						$mods1_user_id[] = $mod_group['0'];
						$mods1_user_name[] = $mod_group['1'];
					} else {
						$mods2_user_id[] = $mod_group['0'];
						$mods2_user_name[] = $mod_group['1'];
					}
				}
			}
			echo "<tr>\n<td class='tbl2' colspan='2'><strong>".$locale['533']."</strong></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td align='center' colspan='2' class='tbl'>\n<select multiple='multiple' size='10' name='modlist1' id='modlist1' class='form-control textbox m-r-10' style='float:left; width:45%;' onchange=\"addUser('modlist2','modlist1');\">\n";
			for ($i = 0; $i < count($mods1_user_id); $i++) {
				echo "<option value='".$mods1_user_id[$i]."'>".$mods1_user_name[$i]."</option>\n";
			}
			echo "</select>\n";
			echo "<select multiple='multiple' size='10' name='modlist2' id='modlist2' class='form-control textbox' style='width:45%;' onchange=\"addUser('modlist1','modlist2');\">\n";
			if (isset($mods2_user_id) && is_array($mods2_user_id)) {
				for ($i = 0; $i < count($mods2_user_id); $i++) {
					echo "<option value='".$mods2_user_id[$i]."'>".$mods2_user_name[$i]."</option>\n";
				}
			}
			echo "</select>\n";
			echo "</td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td align='center' colspan='2'><br />\n";
			echo form_hidden('', 'forum_mods', 'forum_mods', '');
			echo form_hidden('', 'save_forum', 'save_forum', '');
			//echo "<input type='hidden' name='forum_mods' />\n";
			//echo "<input type='hidden' name='save_forum' />\n";
			echo form_button($locale['532'], 'save', 'save', $locale['532'], array('class' => 'btn-primary'));
			add_to_jquery("
                $('#save').bind('click', function() { saveMods(); });
                ");
			//echo "<input type='button' name='save' value='".$locale['532']."' class='button' onclick='saveMods();' /></td>\n";
			echo "</tr>\n</table>\n</form>\n";
			echo "<script type='text/javascript'>\n"."function addUser(toGroup,fromGroup) {\n";
			echo "var listLength = document.getElementById(toGroup).length;\n";
			echo "var selItem = document.getElementById(fromGroup).selectedIndex;\n";
			echo "var selText = document.getElementById(fromGroup).options[selItem].text;\n";
			echo "var selValue = document.getElementById(fromGroup).options[selItem].value;\n";
			echo "var i; var newItem = true;\n";
			echo "for (i = 0; i < listLength; i++) {\n";
			echo "if (document.getElementById(toGroup).options[i].text == selText) {\n";
			echo "newItem = false; break;\n}\n}\n"."if (newItem) {\n";
			echo "document.getElementById(toGroup).options[listLength] = new Option(selText, selValue);\n";
			echo "document.getElementById(fromGroup).options[selItem] = null;\n}\n}\n";
			echo "function saveMods() {\n"."var strValues = \"\";\n";
			echo "var boxLength = document.getElementById('modlist2').length;\n";
			echo "var count = 0;\n"."	if (boxLength != 0) {\n"."for (i = 0; i < boxLength; i++) {\n";
			echo "if (count == 0) {\n"."strValues = document.getElementById('modlist2').options[i].value;\n";
			echo "} else {\n"."strValues = strValues + \".\" + document.getElementById('modlist2').options[i].value;\n";
			echo "}\n"."count++;\n}\n}\n";
			echo "if (strValues.length == 0) {\n"."document.forms['addforum'].submit();\n";
			echo "} else {\n"."document.forms['addforum'].forum_mods.value = strValues;\n";
			echo "document.forms['addforum'].submit();\n}\n}\n</script>\n";
		}
		closetable();
	}
}
opentable($locale['550']);
$i = 1;
$k = 1;
echo "<table cellpadding='0' cellspacing='1' width='100%' class='table table-responsive tbl-border'>\n<thead>\n";
$result = dbquery("SELECT forum_id, forum_name, forum_description, forum_order FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='0' ORDER BY forum_order");
if (dbrows($result) != 0) {
	echo "<tr>\n<th class='tbl2'><strong>".$locale['551']."</strong></th>\n";
	echo "<th align='center' colspan='2' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['552']."</strong></th>\n";
	echo "<th align='center' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['553']."</strong></th>\n";
	echo "</tr>\n";
	echo "</thead>\n<tbody>\n";
	$i = 1;
	while ($data = dbarray($result)) {
		echo "<tr>\n<td class='tbl2'><strong>".$data['forum_name']."</strong><br />\n";
		echo ($data['forum_description'] ? "<span class='small'>".nl2br(parseubb($data['forum_description']))."</span>" : "")."</td>\n";
		echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$data['forum_order']."</td>\n";
		echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>\n";
		if (dbrows($result) != 1) {
			$up = $data['forum_order']-1;
			$down = $data['forum_order']+1;
			if ($i == 1) {
				echo "<a href='".FUSION_SELF.$aidlink."&amp;action=md&amp;order=$down&amp;forum_id=".$data['forum_id']."&amp;t=cat'><img src='".get_image("down")."' alt='".$locale['557']."' title='".$locale['557']."' style='border:0px;' /></a>\n";
			} elseif ($i < dbrows($result)) {
				echo "<a href='".FUSION_SELF.$aidlink."&amp;action=mu&amp;order=$up&amp;forum_id=".$data['forum_id']."&amp;t=cat'><img src='".get_image("up")."' alt='".$locale['556']."' title='".$locale['558']."' style='border:0px;' /></a>\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;action=md&amp;order=$down&amp;forum_id=".$data['forum_id']."&amp;t=cat'><img src='".get_image("down")."' alt='".$locale['557']."' title='".$locale['557']."' style='border:0px;' /></a>\n";
			} else {
				echo "<a href='".FUSION_SELF.$aidlink."&amp;action=mu&amp;order=$up&amp;forum_id=".$data['forum_id']."&amp;t=cat'><img src='".get_image("up")."' alt='".$locale['556']."' title='".$locale['558']."' style='border:0px;' /></a>\n";
			}
		}
		$i++;
		echo "</td>\n";
		echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;forum_id=".$data['forum_id']."&amp;t=cat'>".$locale['554']."</a> ::\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;forum_id=".$data['forum_id']."&amp;t=cat' onclick=\"return confirm('".$locale['440']."');\">".$locale['555']."</a></td>\n";
		echo "</tr>\n";
		$result2 = dbquery("SELECT forum_id, forum_name, forum_description, forum_cat, forum_order FROM ".DB_FORUMS." WHERE forum_cat='".$data['forum_id']."' ORDER BY forum_order");
		if (dbrows($result2)) {
			$k = 1;
			while ($data2 = dbarray($result2)) {
				echo "<tr>\n";
				echo "<td class='tbl1'><span class='alt'><strong>".$data2['forum_name']."</strong></span>\n";
				echo "<a class='btn btn-default btn-xs pull-right' href='".FUSION_SELF.$aidlink."&amp;action=prune&amp;forum_id=".$data2['forum_id']."'>".$locale['563']."</a><br />\n";
				echo ($data2['forum_description'] ? "<span class='small'>".nl2br(parseubb($data2['forum_description']))."</span>" : "")."</td>\n";
				echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$data2['forum_order']."</td>\n";
				echo "<td align='center' width='1%' class='tbl1' style='white-space:nowrap'>\n";
				if (dbrows($result2) != 1) {
					$up = $data2['forum_order']-1;
					$down = $data2['forum_order']+1;
					if ($k == 1) {
						echo "<a href='".FUSION_SELF.$aidlink."&amp;action=md&amp;order=$down&amp;forum_id=".$data2['forum_id']."&amp;t=forum&amp;cat=".$data2['forum_cat']."'><img src='".get_image("down")."' alt='".$locale['557']."' title='".$locale['557']."' style='border:0px;' /></a>\n";
					} elseif ($k < dbrows($result2)) {
						echo "<a href='".FUSION_SELF.$aidlink."&amp;action=mu&amp;order=$up&amp;forum_id=".$data2['forum_id']."&amp;t=forum&amp;cat=".$data2['forum_cat']."'><img src='".get_image("up")."' alt='".$locale['556']."' title='".$locale['558']."' style='border:0px;' /></a>\n";
						echo "<a href='".FUSION_SELF.$aidlink."&amp;action=md&amp;order=$down&amp;forum_id=".$data2['forum_id']."&amp;t=forum&amp;cat=".$data2['forum_cat']."'><img src='".get_image("down")."' alt='".$locale['557']."' title='".$locale['557']."' style='border:0px;' /></a>\n";
					} else {
						echo "<a href='".FUSION_SELF.$aidlink."&amp;action=mu&amp;order=$up&amp;forum_id=".$data2['forum_id']."&amp;t=forum&amp;cat=".$data2['forum_cat']."'><img src='".get_image("up")."' alt='".$locale['556']."' title='".$locale['558']."' style='border:0px;' /></a>\n";
					}
				}
				$k++;
				echo "</td>\n";
				echo "<td align='center' width='1%' class='tbl1' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;forum_id=".$data2['forum_id']."&amp;t=forum'>".$locale['554']."</a> ::\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;forum_id=".$data2['forum_id']."&amp;t=forum' onclick=\"return confirm('".$locale['570']."');\">".$locale['555']."</a></td>\n";
				echo "</tr>\n";
			}
		}
	}
	echo "<tr>\n<td align='center' colspan='5' class='tbl2'><a class='btn btn-primary btn-block' href='".FUSION_SELF.$aidlink."&amp;action=refresh'>".$locale['562']."</a></td>\n</tr>\n";
} else {
	echo "<tr>\n<td align='center' class='tbl1'>".$locale['560']."</td>\n</tr>\n";
}
echo "</tbody>\n";
echo "</table>\n";
closetable();
require_once THEMES."templates/footer.php";
?>