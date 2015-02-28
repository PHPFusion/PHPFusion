<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: submissions.php
| Author: Nick Jones (Digitanium)
| Co-Author: Daywalker
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

if (!checkrights("SU") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header_mce.php";
if ($settings['tinymce_enabled'] != 1) {
	require_once INCLUDES."html_buttons_include.php";
}
include LOCALE.LOCALESET."admin/submissions.php";

$links = ""; $news = ""; $articles = ""; $photos = ""; $downloads = "";

if (!isset($_GET['action']) || $_GET['action'] == "1") {
	if (isset($_GET['delete']) && isnum($_GET['delete'])) {
		$result = dbquery("SELECT submit_type, submit_criteria FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['delete']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			if ($data['submit_type'] == "p") {
				$submit_criteria = unserialize($data['submit_criteria']);
				@unlink(PHOTOS."submissions/".$submit_criteria['photo_file']);
			}
			if ($data['submit_type'] == "d") {
				$submit_criteria = unserialize($data['submit_criteria']);
				if ($submit_criteria['download_file']) @unlink(DOWNLOADS."submissions/".$submit_criteria['download_file']);
				if ($submit_criteria['download_image']) {
					@unlink(DOWNLOADS."submissions/images/".$submit_criteria['download_image']);
					@unlink(DOWNLOADS."submissions/images/".$submit_criteria['download_image_thumb']);
				}
			}
			opentable($locale['400']);
			$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['delete']."'");
			echo "<br /><div style='text-align:center'>".$locale['401']."<br /><br />\n";
			echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
			echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$result = dbquery("SELECT submit_id, submit_criteria FROM ".DB_SUBMISSIONS." WHERE submit_type='l' ORDER BY submit_datestamp DESC");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$submit_criteria = unserialize($data['submit_criteria']);
				$links .= "<tr>\n<td class='tbl1'>".$submit_criteria['link_name']."</td>\n";
				$links .= "<td align='right' width='1%' class='tbl1' style='white-space:nowrap'><span class='small'><a href='".FUSION_SELF.$aidlink."&amp;action=2&amp;t=l&amp;submit_id=".$data['submit_id']."'>".$locale['417']."</a></span> |\n";
				$links .= "<span class='small'><a href='".FUSION_SELF.$aidlink."&amp;delete=".$data['submit_id']."'>".$locale['418']."</a></span></td>\n</tr>\n";
			}
		} else {
			$links = "<tr>\n<td colspan='2' class='tbl1'>".$locale['414']."</td>\n</tr>\n";
		}
		$result = dbquery("SELECT submit_id, submit_criteria FROM ".DB_SUBMISSIONS." WHERE submit_type='n' ORDER BY submit_datestamp DESC");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$submit_criteria = unserialize($data['submit_criteria']);
				$news .= "<tr>\n<td class='tbl1'>".$submit_criteria['news_subject']."</td>\n";
				$news .= "<td align='right' width='1%' class='tbl1' style='white-space:nowrap'><span class='small'><a href='".FUSION_SELF.$aidlink."&amp;action=2&amp;t=n&amp;submit_id=".$data['submit_id']."'>".$locale['417']."</a></span> |\n";
				$news .= "<span class='small'><a href='".FUSION_SELF.$aidlink."&amp;delete=".$data['submit_id']."'>".$locale['418']."</a></span></td>\n</tr>\n";
			}
		} else {
			$news = "<tr>\n<td colspan='2' class='tbl1'>".$locale['415']."</td>\n</tr>\n";
		}
		$result = dbquery("SELECT submit_id, submit_criteria FROM ".DB_SUBMISSIONS." WHERE submit_type='a' ORDER BY submit_datestamp DESC");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$submit_criteria = unserialize($data['submit_criteria']);
				$articles .= "<tr>\n<td class='tbl1'>".$submit_criteria['article_subject']."</td>\n";
				$articles .= "<td align='right' width='1%' class='tbl1' style='white-space:nowrap'><span class='small'><a href='".FUSION_SELF.$aidlink."&amp;action=2&amp;t=a&amp;submit_id=".$data['submit_id']."'>".$locale['417']."</a></span> |\n";
				$articles .= "<span class='small'><a href='".FUSION_SELF.$aidlink."&amp;delete=".$data['submit_id']."'>".$locale['418']."</a></span></td>\n</tr>\n";
			}
		} else {
			$articles = "<tr>\n<td colspan='2' class='tbl1'>".$locale['416']."</td>\n</tr>\n";
		}
		$result = dbquery("SELECT submit_id, submit_criteria FROM ".DB_SUBMISSIONS." WHERE submit_type='p' ORDER BY submit_datestamp DESC");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$submit_criteria = unserialize($data['submit_criteria']);
				$photos .= "<tr>\n<td class='tbl1'>".$submit_criteria['photo_title']."</td>\n";
				$photos .= "<td align='right' width='1%' class='tbl1' style='white-space:nowrap'><span class='small'><a href='".FUSION_SELF.$aidlink."&amp;action=2&amp;t=p&amp;submit_id=".$data['submit_id']."'>".$locale['417']."</a></span> |\n";
				$photos .= "<span class='small'><a href='".FUSION_SELF.$aidlink."&amp;delete=".$data['submit_id']."'>".$locale['418']."</a></span></td>\n</tr>\n";
			}
		} else {
			$photos = "<tr>\n<td colspan='2' class='tbl1'>".$locale['420']."</td>\n</tr>\n";
		}
		$result = dbquery("SELECT submit_id, submit_criteria FROM ".DB_SUBMISSIONS." WHERE submit_type='d' ORDER BY submit_datestamp DESC");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$submit_criteria = unserialize($data['submit_criteria']);
				$downloads .= "<tr>\n<td class='tbl1'>".$submit_criteria['download_title']."</td>\n";
				$downloads .= "<td align='right' width='1%' class='tbl1' style='white-space:nowrap'><span class='small'><a href='".FUSION_SELF.$aidlink."&amp;action=2&amp;t=d&amp;submit_id=".$data['submit_id']."'>".$locale['417']."</a></span> |\n";
				$downloads .= "<span class='small'><a href='".FUSION_SELF.$aidlink."&amp;delete=".$data['submit_id']."'>".$locale['418']."</a></span></td>\n</tr>\n";
			}
		} else {
			$downloads = "<tr>\n<td colspan='2' class='tbl1'>".$locale['422']."</td>\n</tr>\n";
		}
		opentable($locale['410']);
		echo "<table cellpadding='0' cellspacing='1' width='400' class='tbl-border center'>\n<tr>\n";
		echo "<td colspan='2' class='tbl2'><a id='link_submissions' name='link_submissions'></a>\n".$locale['411']."</td>\n";
		echo "</tr>".$links."<tr>\n";
		echo "<td colspan='2' class='tbl2'><a id='news_submissions' name='news_submissions'></a>\n".$locale['412']."</td>\n";
		echo "</tr>\n".$news."<tr>\n";
		echo "<td colspan='2' class='tbl2'><a id='article_submissions' name='article_submissions'></a>\n".$locale['413']."</td>\n";
		echo "</tr>\n".$articles."<tr>\n";
		echo "<td colspan='2' class='tbl2'><a id='photo_submissions' name='photo_submissions'></a>\n".$locale['419']."</td>\n";
		echo "</tr>\n".$photos."<tr>\n";
		echo "<td colspan='2' class='tbl2'><a id='downloads_submissions' name='downloads_submissions'></a>\n".$locale['421']."</td>\n";
		echo "</tr>\n".$downloads."</table>\n";
		closetable();
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "2") && (isset($_GET['t']) && $_GET['t'] == "l")) {
	if (isset($_POST['add']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$link_name = stripinput($_POST['link_name']);
		$link_url = stripinput($_POST['link_url']);
		$link_description = stripinput($_POST['link_description']);
		$result = dbquery("INSERT INTO ".DB_WEBLINKS." (weblink_name, weblink_description, weblink_url, weblink_cat, weblink_datestamp, weblink_count) VALUES ('$link_name', '$link_description', '$link_url', '".$_POST['link_category']."', '".time()."', '0')");
		$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
		opentable($locale['430']);
		echo "<br /><div style='text-align:center'>".$locale['431']."<br /><br />\n";
		echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
		echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
		closetable();
	} else if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		opentable($locale['432']);
		$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
		echo "<br /><div style='text-align:center'>".$locale['433']."<br /><br />\n";
		echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
		echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
		closetable();
	} else {
		$result = dbquery(
			"SELECT ts.submit_criteria, ts.submit_datestamp, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'"
		);
		if (dbrows($result)) {
			$data = dbarray($result);
			$opts = ""; $sel = "";
			$submit_criteria = unserialize($data['submit_criteria']);
			$posted = showdate("longdate", $data['submit_datestamp']);
			$result2 = dbquery("SELECT weblink_cat_id, weblink_cat_name FROM ".DB_WEBLINK_CATS." ORDER BY weblink_cat_name");
			if (dbrows($result2) != 0) {
				while($data2 = dbarray($result2)) {
					if (isset($submit_criteria['link_category'])) {
						$sel = ($submit_criteria['link_category'] == $data2['weblink_cat_id'] ? " selected='selected'" : "");
					}
					$opts .= "<option value='".$data2['weblink_cat_id']."'$sel>".$data2['weblink_cat_name']."</option>\n";
				}
			} else {
				$opts .= "<option value='0'>".$locale['434']."</option>\n";
			}
			add_to_title($locale['global_200'].$locale['448'].$locale['global_201'].$submit_criteria['link_name']."?");
			opentable($locale['440']);
			echo "<form name='publish' method='post' action='".FUSION_SELF.$aidlink."&amp;action=2&amp;t=l&amp;submit_id=".$_GET['submit_id']."'>\n";
			echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
			echo "<td style='text-align:center;' class='tbl'>".$locale['441'].profile_link($data['user_id'], $data['user_name'], $data['user_status']).$locale['442'].$posted."</td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td style='text-align:center;' class='tbl'><a href='".ADMIN."go.php?id=".$_GET['submit_id']."' target='_blank'>".$submit_criteria['link_name']."</a> - ".$submit_criteria['link_url']."</td>\n";
			echo "</tr>\n</table>\n";
			echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
			echo "<td class='tbl'>".$locale['443']."</td>\n";
			echo "<td class='tbl'><select name='link_category' class='textbox'>\n".$opts."</select></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td class='tbl'>".$locale['444']."</td>\n";
			echo "<td class='tbl'><input type='text' name='link_name' value='".$submit_criteria['link_name']."' class='textbox' style='width:300px' /></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td class='tbl'>".$locale['445']."</td>\n";
			echo "<td class='tbl'><input type='text' name='link_url' value='".$submit_criteria['link_url']."' class='textbox' style='width:300px' /></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td class='tbl'>".$locale['446']."</td>\n";
			echo "<td class='tbl'><input type='text' name='link_description' value='".$submit_criteria['link_description']."' class='textbox' style='width:300px' /></td>\n";
			echo "</tr>\n</table>\n";
			echo "<div style='text-align:center'><br />\n";
			echo $locale['447']."<br />\n";
			echo "<input type='submit' name='add' value='".$locale['448']."' class='button' />\n";
			echo "<input type='submit' name='delete' value='".$locale['449']."' class='button' /></div>\n";
			echo "</form>\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "2") && (isset($_GET['t']) && $_GET['t'] == "n")) {
	if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$result = dbquery(
			"SELECT ts.*, tu.user_id, tu.user_name FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'"
		);
		if (dbrows($result)) {
			$data = dbarray($result);
			$news_subject = stripinput($_POST['news_subject']);
			$news_cat = isnum($_POST['news_cat']) ? $_POST['news_cat'] : "0";
			$news_snippet = addslash($_POST['news_snippet']);
			$news_body = addslash($_POST['news_body']);
			$news_breaks = ($_POST['news_breaks'] == "y") ? "y" : "n";
			$result = dbquery("INSERT INTO ".DB_NEWS." (news_subject, news_cat, news_news, news_extended, news_breaks, news_name, news_datestamp, news_start, news_end, news_visibility, news_reads, news_allow_comments, news_allow_ratings) VALUES ('$news_subject', '$news_cat', '$news_snippet', '$news_body', '$news_breaks', '".$data['user_id']."', '".time()."', '0', '0', '0', '0', '1', '1')");
			$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
			opentable($locale['490']);
			echo "<br /><div style='text-align:center'>".$locale['491']."<br /><br />\n";
			echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
			echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		opentable($locale['492']);
		$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
		echo "<br /><div style='text-align:center'>".$locale['493']."<br /><br />\n";
		echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
		echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
		closetable();
	} else {
		if ($settings['tinymce_enabled'] == 1) echo "<script type='text/javascript'>advanced();</script>\n";
		$result = dbquery(
			"SELECT ts.submit_criteria, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'"
		);
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$news_subject = $submit_criteria['news_subject'];
			$news_cat = $submit_criteria['news_cat'];
			if (isset($submit_criteria['news_snippet'])) {
				$news_snippet = phpentities(stripslashes($submit_criteria['news_snippet']));
			} else {
				$news_snippet = "";
			}
			$news_body = phpentities(stripslashes($submit_criteria['news_body']));
			$news_breaks = "";
			$news_cat_opts = ""; $sel = "";
			$result2 = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ORDER BY news_cat_name");
			if (dbrows($result2)) {
				while ($data2 = dbarray($result2)) {
					if (isset($news_cat)) $sel = ($news_cat == $data2['news_cat_id'] ? " selected='selected'" : "");
					$news_cat_opts .= "<option value='".$data2['news_cat_id']."'$sel>".$data2['news_cat_name']."</option>\n";
				}
			}
			add_to_title($locale['global_200'].$locale['503'].$locale['global_201'].$news_subject."?");
			if (isset($_POST['preview']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
				$news_subject = stripinput($_POST['news_subject']);
				$news_cat = isnum($_POST['news_cat']) ? $_POST['news_cat'] : "0";
				$news_snippet = stripslash($_POST['news_snippet']);
				$news_body = stripslash($_POST['news_body']);
				$breaks = (isset($_POST['line_breaks']) ? " checked='checked'" : "");
				opentable($news_subject);
				echo $locale['509']." ".(isset($_POST['line_breaks']) ? nl2br($news_snippet) : $news_snippet)."<br /><br />";
				echo $locale['508']." ".(isset($_POST['line_breaks']) ? nl2br($news_body) : $news_body);
				closetable();
			}
			opentable($locale['500']);
			echo "<form name='publish' method='post' action='".FUSION_SELF.$aidlink."&amp;sub=submissions&amp;action=2&amp;t=n&amp;submit_id=".$_GET['submit_id']."'>\n";
			echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
			echo "<td width='100' class='tbl'>".$locale['505']."</td>\n";
			echo "<td width='80%' class='tbl'><input type='text' name='news_subject' value='$news_subject' class='textbox' style='width: 250px' /></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td width='100' class='tbl'>".$locale['506']."</td>\n";
			echo "<td width='80%' class='tbl'><select name='news_cat' class='textbox'>\n";
			echo "<option value='0'>".$locale['507']."</option>\n".$news_cat_opts."</select></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td valign='top' width='100' class='tbl'>".$locale['509']."</td>\n";
			echo "<td width='80%' class='tbl'><textarea name='news_snippet' cols='60' rows='10' class='textbox' style='width:300px;'>".$news_snippet."</textarea></td>\n";
			echo "</tr>\n";
			if ($settings['tinymce_enabled'] != 1) {
				echo "<tr>\n<td class='tbl'></td>\n<td class='tbl'>\n";
				echo display_html("publish", "news_snippet", true, true, true);
				echo "</td>\n</tr>\n";
			}
			echo "<tr>\n";
			echo "<td valign='top' width='100' class='tbl'>".$locale['508']."</td>\n";
			echo "<td width='80%' class='tbl'><textarea name='news_body' cols='60' rows='10' class='textbox' style='width:300px;'>".$news_body."</textarea></td>\n";
			echo "</tr>\n";
			if ($settings['tinymce_enabled'] != 1) {
				echo "<tr>\n<td class='tbl'></td>\n<td class='tbl'>\n";
				echo display_html("publish", "news_body", true, true, true);
				echo "</td>\n</tr>\n";
			}
			echo "<tr>\n";
			echo "<td align='center' colspan='2' class='tbl1'><br />\n";
			echo $locale['501'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br /><br />\n";
			echo $locale['502']."<br />\n";
			echo "<input type='hidden' name='news_breaks' value='".$news_breaks."' />\n";
			echo "<input type='submit' name='preview' value='".$locale['510']."' class='button' />\n";
			echo "<input type='submit' name='publish' value='".$locale['503']."' class='button' />\n";
			echo "<input type='submit' name='delete' value='".$locale['504']."' class='button' />\n";
			echo "</td>\n</tr>\n</table>\n</form>\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "2") && (isset($_GET['t']) && $_GET['t'] == "a")) {
	if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$result = dbquery(
			"SELECT ts.submit_criteria, user_id
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'"
		);
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$article_cat = isnum($_POST['article_cat']) ? $_POST['article_cat'] : 0;
			$article_subject = stripinput($_POST['article_subject']);
			$article_snippet = addslash($_POST['article_snippet']);
			$article_body = addslash($_POST['article_body']);
			$article_breaks = ($_POST['article_breaks'] == "y") ? "y" : "n";
			$result = dbquery("INSERT INTO ".DB_ARTICLES." (article_cat, article_subject, article_snippet, article_article, article_breaks, article_name, article_datestamp, article_reads, article_allow_comments, article_allow_ratings) VALUES ('$article_cat', '$article_subject', '$article_snippet', '$article_body', '$article_breaks', '".$data['user_id']."', '".time()."', '0', '1', '1')");
			$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
			opentable($locale['530']);
			echo "<br /><div style='text-align:center'>".$locale['531']."<br /><br />\n";
			echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
			echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		opentable($locale['532']);
		$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
		echo "<br /><div style='text-align:center'>".$locale['533']."<br /><br />\n";
		echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
		echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
		closetable();
	} else {
		if ($settings['tinymce_enabled'] == 1) {
			echo "<script type='text/javascript'>advanced();</script>\n";
		}
		$result = dbquery(
			"SELECT ts.submit_criteria, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'"
		);
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$article_cat = $submit_criteria['article_cat'];
			$article_subject = $submit_criteria['article_subject'];
			$article_snippet = phpentities(stripslashes($submit_criteria['article_snippet']));
			$article_body = phpentities(stripslashes($submit_criteria['article_body']));
			$article_breaks = "";
			$result2 = dbquery("SELECT article_cat_id, article_cat_name FROM ".DB_ARTICLE_CATS." ORDER BY article_cat_name DESC");
			$article_cat_opts = ""; $sel = "";
			while ($data2 = dbarray($result2)) {
				if (isset($article_cat)) $sel = ($article_cat == $data2['article_cat_id'] ? " selected='selected'" : "");
				$article_cat_opts .= "<option value='".$data2['article_cat_id']."'$sel>".$data2['article_cat_name']."</option>\n";
			}
			add_to_title($locale['global_200'].$locale['543'].$locale['global_201'].$article_subject."?");
			if (isset($_POST['preview']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
				$article_cat = isnum($_POST['article_cat']) ? $_POST['article_cat'] : "0";
				$article_subject = stripinput($_POST['article_subject']);
				$article_snippet = stripslash($_POST['article_snippet']);
				$article_body = stripslash($_POST['article_body']);
				$breaks = (isset($_POST['line_breaks']) ? " checked='checked'" : "");
				opentable($article_subject);
				echo $locale['547']." ".(isset($_POST['line_breaks']) ? nl2br($article_snippet) : $article_snippet)."<br /><br />";
				echo $locale['548']." ".(isset($_POST['line_breaks']) ? nl2br($article_body) : $article_body);
				closetable();
			}
			opentable($locale['540']);
			echo "<form name='publish' method='post' action='".FUSION_SELF.$aidlink."&amp;sub=submissions&amp;action=2&amp;t=a&amp;submit_id=".$_GET['submit_id']."'>\n";
			echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
			echo "<td width='100' class='tbl'>".$locale['506']."</td>\n";
			echo "<td width='80%' class='tbl'><select name='article_cat' class='textbox'>\n".$article_cat_opts."</select></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td width='100' class='tbl'>".$locale['505']."</td>\n";
			echo "<td width='80%' class='tbl'><input type='text' name='article_subject' value='$article_subject' class='textbox' style='width: 250px' /></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td valign='top' width='100' class='tbl'>".$locale['547']."</td>\n";
			echo "<td width='80%' class='tbl'><textarea name='article_snippet' cols='60' rows='5' class='textbox' style='width:300px;'>".$article_snippet."</textarea></td>\n";
			echo "</tr>\n";
			if ($settings['tinymce_enabled'] != 1) {
				echo "<tr>\n<td class='tbl'></td>\n<td class='tbl'>\n";
				echo display_html("publish", "article_body", true, true, true);
				echo "</td>\n</tr>\n";
			}
			echo "<tr>\n";
			echo "<td valign='top' width='100' class='tbl'>".$locale['548']."</td>\n";
			echo "<td width='80%' class='tbl'><textarea name='article_body' cols='60' rows='10' class='textbox' style='width:300px;'>".$article_body."</textarea></td>\n";
			echo "</tr>\n";
			if ($settings['tinymce_enabled'] != 1) {
				echo "<tr>\n<td class='tbl'></td>\n<td class='tbl'>\n";
				echo display_html("publish", "article_body", true, true, true);
				echo "</td>\n</tr>\n";
			}
			echo "<tr>\n";
			echo "<td align='center' colspan='2' class='tbl1'><br />\n";
			echo $locale['541'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br /><br />\n";
			echo $locale['542']."<br />\n";
			echo "<input type='hidden' name='article_breaks' value='".$article_breaks."' />\n";
			echo "<input type='submit' name='preview' value='".$locale['549']."' class='button' />\n";
			echo "<input type='submit' name='publish' value='".$locale['543']."' class='button' />\n";
			echo "<input type='submit' name='delete' value='".$locale['544']."' class='button' />\n";
			echo "</td>\n</tr>\n</table>\n</form>\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "2") && (isset($_GET['t']) && $_GET['t'] == "p")) {
	if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		define("SAFEMODE", @ini_get("safe_mode") ? true : false);
		require_once INCLUDES."photo_functions_include.php";
		$photo_file = ""; $photo_thumb1 = ""; $photo_thumb2 = "";
		$result = dbquery(
			"SELECT ts.submit_user, ts.submit_criteria
			FROM ".DB_SUBMISSIONS." ts
			WHERE submit_id='".$_GET['submit_id']."'"
		);
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$photo_title = stripinput($_POST['photo_title']);
			$photo_description = stripinput($_POST['photo_description']);
			$album_id = isnum($_POST['album_id']) ? $_POST['album_id'] : "0";
			$photo_name = strtolower(substr($submit_criteria['photo_file'], 0, strrpos($submit_criteria['photo_file'], ".")));
			$photo_ext = strtolower(strrchr($submit_criteria['photo_file'],"."));
			$photo_dest = PHOTOS.(!SAFEMODE ? "album_".$album_id."/" : "");
			$photo_file = image_exists($photo_dest, $photo_name.$photo_ext);

			copy(PHOTOS."submissions/".$submit_criteria['photo_file'], $photo_dest.$photo_file);
			chmod($photo_dest.$photo_file, 0644);
			unlink(PHOTOS."submissions/".$submit_criteria['photo_file']);
			$imagefile = @getimagesize($photo_dest.$photo_file);

			$photo_thumb1 = image_exists($photo_dest, $photo_name."_t1".$photo_ext);
			createthumbnail($imagefile[2], $photo_dest.$photo_file, $photo_dest.$photo_thumb1, $settings['thumb_w'], $settings['thumb_h']);
			if ($imagefile[0] > $settings['photo_w'] || $imagefile[1] > $settings['photo_h']) {
				$photo_thumb2 = image_exists($photo_dest, $photo_name."_t2".$photo_ext);
				createthumbnail($imagefile[2], $photo_dest.$photo_file, $photo_dest.$photo_thumb2, $settings['photo_w'], $settings['photo_h']);
			}
			$photo_order = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." WHERE album_id='$album_id'"), 0) + 1;
			$result = dbquery("INSERT INTO ".DB_PHOTOS." (album_id, photo_title, photo_description, photo_filename, photo_thumb1, photo_thumb2, photo_datestamp, photo_user, photo_views, photo_order, photo_allow_comments, photo_allow_ratings) VALUES ('$album_id', '$photo_title', '$photo_description', '$photo_file', '$photo_thumb1', '$photo_thumb2', '".time()."', '".$data['submit_user']."', '0', '$photo_order', '1', '1')");
			$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
			opentable($locale['580']);
			echo "<br /><div style='text-align:center'>".$locale['581']."<br /><br />\n";
			echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
			echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		opentable($locale['582']);
		$data = dbarray(dbquery("SELECT submit_criteria FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'"));
		$submit_criteria = unserialize($data['submit_criteria']);
		@unlink(PHOTOS."submissions/".$submit_criteria['photo_file']);
		$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
		echo "<br /><div style='text-align:center'>".$locale['583']."<br /><br />\n";
		echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
		echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
		closetable();
	} else {
		$result = dbquery(
			"SELECT ts.submit_criteria, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'"
		);
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$photo_title = $submit_criteria['photo_title'];
			$photo_description = $submit_criteria['photo_description'];
			$photo_file = $submit_criteria['photo_file'];
			$album_id = $submit_criteria['album_id'];
			$photo_albums = ""; $sel = "";
			$result2 = dbquery("SELECT album_id, album_title FROM ".DB_PHOTO_ALBUMS." ORDER BY album_title");
			if (dbrows($result2)) {
				while ($data2 = dbarray($result2)) {
					if (isset($album_id)) $sel = ($album_id == $data2['album_id'] ? " selected='selected'" : "");
					$photo_albums .= "<option value='".$data2['album_id']."'$sel>".$data2['album_title']."</option>\n";
				}
			}
			add_to_title($locale['global_200'].$locale['594'].$locale['global_201'].$photo_title."?");
			opentable($locale['580']);
			echo "<form name='publish' method='post' action='".FUSION_SELF.$aidlink."&amp;sub=submissions&amp;action=2&amp;t=p&amp;submit_id=".$_GET['submit_id']."'>\n";
			echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
			echo "<td width='100' class='tbl'>".$locale['596']."</td>\n";
			echo "<td width='80%' class='tbl'><input type='text' name='photo_title' value='".$photo_title."' class='textbox' style='width: 250px' /></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td width='100' class='tbl'>".$locale['597']."</td>\n";
			echo "<td width='80%' class='tbl'><textarea name='photo_description' cols='60' rows='5' class='textbox' style='width:300px;'>".$photo_description."</textarea></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td width='100' class='tbl'>".$locale['598']."</td>\n";
			echo "<td width='80%' class='tbl'><select name='album_id' class='textbox'>\n";
			echo "<option value='0'>".$locale['507']."</option>\n".$photo_albums."</select></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td align='center' colspan='2' class='tbl1'><br />\n";
			echo "<a href='".PHOTOS."submissions/".$photo_file."' target='_blank'>".$locale['591']."</a><br /><br />\n";
			echo $locale['592'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br /><br />\n";
			echo $locale['593']."<br />\n";
			echo "<input type='submit' name='publish' value='".$locale['594']."' class='button' />\n";
			echo "<input type='submit' name='delete' value='".$locale['595']."' class='button' />\n";
			echo "</td>\n</tr>\n</table>\n</form>\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "2") && (isset($_GET['t']) && $_GET['t'] == "d")) {
	if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		require_once INCLUDES."infusions_include.php";
		$result = dbquery(
			"SELECT submit_user, submit_criteria
			FROM ".DB_SUBMISSIONS."
			WHERE submit_id='".$_GET['submit_id']."'"
		);
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$download_title = stripinput($_POST['download_title']);
			$download_description = stripinput($_POST['download_description']);
			$download_description_short = stripinput($_POST['download_description_short']);
			$download_cat = isnum($_POST['download_cat']) ? $_POST['download_cat'] : "0";
			$download_allow_comments = (isset($_POST['download_allow_comments']) && isnum($_POST['download_allow_comments'])) ? $_POST['download_allow_comments'] : "0";
			$download_allow_ratings = (isset($_POST['download_allow_ratings']) && isnum($_POST['download_allow_ratings'])) ? $_POST['download_allow_ratings'] : "0";
			$download_url = (isset($_POST['download_url']) ? stripinput($_POST['download_url']) : "");
			$download_os = stripinput($_POST['download_os']);
			$download_license = stripinput($_POST['download_license']);
			$download_copyright = stripinput($_POST['download_copyright']);
			$download_homepage = stripinput($_POST['download_homepage']);
			$download_version = stripinput($_POST['download_version']);
			$download_filesize = stripinput($_POST['download_filesize']);
			$download_file = ((isset($submit_criteria['download_file']) && file_exists(DOWNLOADS."submissions/".$submit_criteria['download_file'])) ? $submit_criteria['download_file'] : "");
			$download_image = ((isset($submit_criteria['download_image']) && file_exists(DOWNLOADS."submissions/images/".$submit_criteria['download_image'])) ? $submit_criteria['download_image'] : "");
			$download_image_thumb = ((isset($submit_criteria['download_image_thumb']) && file_exists(DOWNLOADS."submissions/images/".$submit_criteria['download_image_thumb'])) ? $submit_criteria['download_image_thumb'] : "");
			if (isset($_POST['del_image'])) {
				if (file_exists(DOWNLOADS."submissions/images/".$download_image)) {
					@unlink(DOWNLOADS."submissions/images/".$download_image);
					$download_image = "";
				}
				if (file_exists(DOWNLOADS."submissions/images/".$download_image_thumb)) {
					@unlink(DOWNLOADS."submissions/images/".$download_image_thumb);
					$download_image_thumb = "";
				}
			}
			if ($download_file) {
				$dest = DOWNLOADS;
				$file = filename_exists($dest, $download_file);
				copy(DOWNLOADS."submissions/".$submit_criteria['download_file'], $dest.$file);
				chmod($dest.$file, 0644);
				unlink(DOWNLOADS."submissions/".$submit_criteria['download_file']);
				$download_file = $file;
				$download_url = "";
			}
			if ($download_image) {
				$dest =	DOWNLOADS."images/";
				$file = filename_exists($dest, $download_image);
				copy(DOWNLOADS."submissions/images/".$submit_criteria['download_image'], $dest.$file);
				chmod($dest.$file, 0644);
				unlink(DOWNLOADS."submissions/images/".$submit_criteria['download_image']);
				$download_image = $file;
			}
			if ($download_image_thumb) {
				$dest = DOWNLOADS."images/";
				$file = filename_exists($dest, $download_image_thumb);
				copy(DOWNLOADS."submissions/images/".$submit_criteria['download_image_thumb'], $dest.$file);
				chmod($dest.$file, 0644);
				unlink(DOWNLOADS."submissions/images/".$download_image_thumb);
				$download_image_thumb = $file;
			}
			$result = dbquery("INSERT INTO ".DB_DOWNLOADS." SET
				download_user = '".$data['submit_user']."',
				download_title = '".$download_title."',
				download_description_short = '".$download_description_short."',
				download_description = '".$download_description."',
				download_image = '".$download_image."',
				download_image_thumb = '".$download_image_thumb."',
				download_url = '".$download_url."',
				download_file = '".$download_file."',
				download_cat = '".$download_cat."',
				download_license = '".$download_license."',
				download_copyright = '".$download_copyright."',
				download_homepage = '".$download_homepage."',
				download_os = '".$download_os."',
				download_version = '".$download_version."',
				download_filesize = '".$download_filesize."',
				download_allow_comments = '".$download_allow_comments."',
				download_allow_ratings = '".$download_allow_ratings."',
				download_datestamp = '".time()."',
				download_count = '0'");
			$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
			opentable($locale['630']);
			echo "<br /><div style='text-align:center'>".$locale['631']."<br /><br />\n";
			echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
			echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		opentable($locale['582']);
		$data = dbarray(dbquery("SELECT submit_criteria FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'"));
		$submit_criteria = unserialize($data['submit_criteria']);
		if ($submit_criteria['download_file']) @unlink(DOWNLOADS."submissions/".$submit_criteria['download_file']);
		if ($submit_criteria['download_image']) {
			@unlink(DOWNLOADS."submissions/images/".$submit_criteria['download_image']);
			@unlink(DOWNLOADS."submissions/images/".$submit_criteria['download_image_thumb']);
		}
		$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
		echo "<br /><div style='text-align:center'>".$locale['633']."<br /><br />\n";
		echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n";
		echo "<a href='index.php".$aidlink."'>".$locale['403']."</a></div><br />\n";
		closetable();
	} else {
		$result = dbquery(
			"SELECT ts.submit_criteria, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'"
		);
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$editlist = ""; $sel = "";
			$result2 = dbquery("SELECT download_cat_id, download_cat_name FROM ".DB_DOWNLOAD_CATS." ORDER BY download_cat_name");
			if (dbrows($result2) != 0) {
				while ($data2 = dbarray($result2)) {
					if (isset($_GET['action']) && $_GET['action'] == "edit") { $sel = ($data['download_cat'] == $data2['download_cat_id'] ? " selected='selected'" : ""); }
					$editlist .= "<option value='".$data2['download_cat_id']."'$sel>".$data2['download_cat_name']."</option>\n";
				}
			}
			$photo_albums = ""; $sel = "";
			$editlist = ""; $sel = "";
			$result2 = dbquery("SELECT download_cat_id, download_cat_name FROM ".DB_DOWNLOAD_CATS." ORDER BY download_cat_name");
			if (dbrows($result2) != 0) {
				while ($data2 = dbarray($result2)) {
					$sel = ($data2['download_cat_id'] == $submit_criteria['download_cat'] ? " selected='selected'" : "");
					$editlist .= "<option value='".$data2['download_cat_id']."'$sel>".$data2['download_cat_name']."</option>\n";
				}
			}
			add_to_title($locale['global_200'].$locale['643'].$locale['global_201'].$submit_criteria['download_title']."?");
			opentable($locale['640']);
			require_once INCLUDES."bbcode_include.php";
			echo "<form name='publish' method='post' action='".FUSION_SELF.$aidlink."&amp;sub=submissions&amp;action=2&amp;t=d&amp;submit_id=".$_GET['submit_id']."'>\n";
			echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
			echo "<td class='tbl'>".$locale['645']."</td>\n";
			echo "<td width='80%' class='tbl'><input type='text' name='download_title' value='".$submit_criteria['download_title']."' class='textbox' style='width: 250px' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n<td class='tbl1' style='width:80px;vertical-align:top;'>".$locale['646b']."<br /><br />";
			echo "<span id='shortdesc_display' style='padding: 1px 3px 1px 3px; border:1px solid; display:none;'>";
			echo "<strong>".(500 - mb_strlen($submit_criteria['download_description_short']))."</strong>";
			echo "</span>";
			echo "</td>\n";
			echo "<td class='tbl1'><textarea name='download_description_short' cols='60' rows='4' class='textbox' style='width:380px;' onKeyDown=\"shortdesc_counter(this,'shortdesc_display',500);\" onKeyUp=\"shortdesc_counter(this,'shortdesc_display',500);\">".$submit_criteria['download_description_short']."</textarea></td>\n";
			echo "</tr>\n";
			echo "<tr>\n<td class='tbl1' style='width:80px; vertical-align:top;'>".$locale['646']."</td>\n";
			echo "<td class='tbl1'><textarea name='download_description' cols='60' rows='5' class='textbox' style='width:380px;'>".$submit_criteria['download_description']."</textarea></td>\n";
			echo "</tr>\n";
			echo "<tr>\n<td class='tbl1'></td><td class='tbl1'>\n";
			echo display_bbcodes("100%", "download_description", "downloadform")."</td>\n";
			echo "</tr>\n<tr>\n";
			if (!empty($submit_criteria['download_file'])) {
				echo "<td class='tbl1' style='width:80px; vertical-align:top;'>".$locale['647b']."</td>\n<td class='tbl1' style='vertical-align:top;'>\n";
				echo "<a href='".DOWNLOADS."submissions/".$submit_criteria['download_file']."'>".DOWNLOADS."submissions/".$submit_criteria['download_file']."</a><br />\n";
				echo "</td>\n</tr>\n";
			} else {
				echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['647']."</td>\n";
				echo "<td class='tbl1'><input type='text' name='download_url' value='".$submit_criteria['download_url']."' class='textbox' style='width:380px;' /></td>\n";
				echo "</tr>\n";
			}
			if (!empty($submit_criteria['download_image']) && !empty($submit_criteria['download_image_thumb'])) {
				echo "<tr>\n";
				echo "<td class='tbl1' style='width:80px; vertical-align:top;'>".$locale['653']."</td>\n<td class='tbl1' style='vertical-align:top;'>\n";
				echo "<a href='".DOWNLOADS."submissions/images/".$submit_criteria['download_image']."' target='_blank'>";
				echo "<img src='".DOWNLOADS."submissions/images/".$submit_criteria['download_image_thumb']."' alt='' /></a><br />\n";
				echo "<label><input type='checkbox' name='del_image' value='1' /> ".$locale['658']."</label>\n";
				echo "</td>\n</tr>\n";
			}
			echo "<tr>\n";
			echo "<td width='100' class='tbl'>".$locale['648']."</td>\n";
			echo "<td width='80%' class='tbl'><select name='download_cat' class='textbox'>\n".$editlist."</select></td>\n";
			echo "</tr>\n";
			echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['649']."</td>\n";
			echo "<td class='tbl1'><input type='text' name='download_license' value='".$submit_criteria['download_license']."' class='textbox' style='width:150px;' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['650']."</td>\n";
			echo "<td class='tbl1'><input type='text' name='download_os' value='".$submit_criteria['download_os']."' class='textbox' style='width:150px;' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['651']."</td>\n";
			echo "<td class='tbl1'><input type='text' name='download_version' value='".$submit_criteria['download_version']."' class='textbox' style='width:150px;' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['654']."</td>\n";
			echo "<td class='tbl1'><input type='text' name='download_homepage' value='".$submit_criteria['download_homepage']."' class='textbox' style='width:380px;' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['655']."</td>\n";
			echo "<td class='tbl1'><input type='text' name='download_copyright' value='".$submit_criteria['download_copyright']."' class='textbox' style='width:380px;' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['652']."</td>\n";
			echo "<td class='tbl1'><input type='text' name='download_filesize' id='download_filesize' value='".$submit_criteria['download_filesize']."' class='textbox' style='width:150px;' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['656']."</td>\n";
			echo "<td class='tbl1'><input type='checkbox' name='download_allow_comments' value='1' />";
			if ($settings['comments_enabled'] == "0") {
				echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
			}
			echo "</td>\n";
			echo "</tr>\n";
			echo "<tr>\n<td class='tbl1' style='width:80px;'>".$locale['657']."</td>\n";
			echo "<td class='tbl1'><input type='checkbox' name='download_allow_ratings' value='1' />";
			if ($settings['ratings_enabled'] == "0") {
				echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
			}
			echo "</td>\n";
			echo "</tr>\n";
			if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
				$sys = "";
				if ($settings['comments_enabled'] == "0" &&  $settings['ratings_enabled'] == "0") {
					$sys = $locale['663'];
				} elseif ($settings['comments_enabled'] == "0") {
					$sys = $locale['661'];
				} else {
					$sys = $locale['662'];
				}
				echo "<tr>\n<td colspan='2' class='tbl1' style='font-weight:bold;text-align:left; color:black !important; background-color:#FFDBDB;'>";
				echo "<span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['660'], $sys);
				echo "</td>\n</tr>";
			}
			echo "<tr>\n";
			echo "<td align='center' colspan='2' class='tbl1'>\n";
			echo $locale['641'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br /><br />\n";
			echo $locale['642']."<br />\n";
			echo "<input type='submit' name='publish' value='".$locale['643']."' class='button' />\n";
			echo "<input type='submit' name='delete' value='".$locale['644']."' class='button' />\n";
			echo "</td>\n</tr>\n</table>\n</form>\n";
			echo "<script language='JavaScript' type='text/javascript'>\n";
			echo "/* <![CDATA[ */\n";
			echo "jQuery(document).ready(function() {\n";
			echo "jQuery('#shortdesc_display').show();\n";
			echo "jQuery('#calc_upload').click(\n";
			echo "function() {\n";
			echo "if (jQuery('#calc_upload').attr('checked')) {\n";
			echo "jQuery('#download_filesize').attr('readonly', 'readonly');\n";
			echo "jQuery('#download_filesize').val('');\n";
			echo "jQuery('#calc_upload').attr('checked', 'checked');\n";
			echo "} else {\n";
			echo "jQuery('#download_filesize').removeAttr('readonly');\n";
			echo "jQuery('#calc_upload').removeAttr('checked');\n";
			echo "}\n";
			echo "});\n";
			echo "});\n";
			echo "function shortdesc_counter(textarea, counterID, maxLen) {\n";
			echo "cnt = document.getElementById(counterID);\n";
			echo "if (textarea.value.length >= maxLen) {\n";
			echo "textarea.value = textarea.value.substring(0,maxLen);\n";
			echo "}\n";
			echo "cnt.innerHTML = maxLen - textarea.value.length;\n";
			echo "}";
			echo "/* ]]>*/\n";
			echo "</script>\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
}

require_once THEMES."templates/footer.php";
?>