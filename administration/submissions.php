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
pageAccess('SU');
require_once THEMES."templates/admin_header.php";
if ($settings['tinymce_enabled'] != 1) {
	require_once INCLUDES."html_buttons_include.php";
}
include LOCALE.LOCALESET."admin/submissions.php";

add_breadcrumb(array('link'=>ADMIN.'submissions.php'.$aidlink, 'title'=>$locale['410']));

$links = "";
$news = "";
$blog = "";
$articles = "";
$photos = "";
$downloads = "";
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
				$result = dbquery("SELECT submit_id, submit_criteria FROM ".DB_SUBMISSIONS." WHERE submit_type='b' ORDER BY submit_datestamp DESC");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$submit_criteria = unserialize($data['submit_criteria']);
				$blog .= "<tr>\n<td class='tbl1'>".$submit_criteria['blog_subject']."</td>\n";
				$blog .= "<td align='right' width='1%' class='tbl1' style='white-space:nowrap'><span class='small'><a href='".FUSION_SELF.$aidlink."&amp;action=2&amp;t=b&amp;submit_id=".$data['submit_id']."'>".$locale['417b']."</a></span> |\n";
				$blog .= "<span class='small'><a href='".FUSION_SELF.$aidlink."&amp;delete=".$data['submit_id']."'>".$locale['418b']."</a></span></td>\n</tr>\n";
			}
		} else {
			$blog = "<tr>\n<td colspan='2' class='tbl1'>".$locale['415b']."</td>\n</tr>\n";
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
		echo "<table class='table table-responsive tbl-border center'>\n<tbody>\n<tr>\n";
		echo "<td colspan='2' class='tbl2'><a id='link_submissions' name='link_submissions'></a>\n".$locale['411']."</td>\n";
		echo "</tr>".$links."<tr>\n";
		echo "<td colspan='2' class='tbl2'><a id='news_submissions' name='news_submissions'></a>\n".$locale['412']."</td>\n";
		echo "</tr>\n".$news."<tr>\n";
		echo "<td colspan='2' class='tbl2'><a id='news_submissions' name='news_submissions'></a>\n".$locale['412b']."</td>\n";
		echo "</tr>\n".$blog."<tr>\n";
		echo "<td colspan='2' class='tbl2'><a id='article_submissions' name='article_submissions'></a>\n".$locale['413']."</td>\n";
		echo "</tr>\n".$articles."<tr>\n";
		echo "<td colspan='2' class='tbl2'><a id='photo_submissions' name='photo_submissions'></a>\n".$locale['419']."</td>\n";
		echo "</tr>\n".$photos."<tr>\n";
		echo "<td colspan='2' class='tbl2'><a id='downloads_submissions' name='downloads_submissions'></a>\n".$locale['421']."</td>\n";
		echo "</tr>\n".$downloads."</tbody>\n</table>\n";
		closetable();
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "2") && (isset($_GET['t']) && $_GET['t'] == "l")) {
	// weblink bye bye
}
if ((isset($_GET['action']) && $_GET['action'] == "2") && (isset($_GET['t']) && $_GET['t'] == "n")) {
	if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$result = dbquery("SELECT ts.*, tu.user_id, tu.user_name FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$news_subject = stripinput($_POST['news_subject']);
			$news_cat = isnum($_POST['news_cat']) ? $_POST['news_cat'] : "0";
			$news_snippet = addslash($_POST['news_snippet']);
			$news_body = addslash($_POST['news_body']);
			$news_breaks = ($_POST['news_breaks'] == "y") ? "y" : "n";
			$result = dbquery("INSERT INTO ".DB_NEWS." (news_subject, news_cat, news_news, news_extended, news_breaks, news_name, news_datestamp, news_start, news_end, news_visibility, news_reads, news_allow_comments, news_allow_ratings, news_language) VALUES ('$news_subject', '$news_cat', '$news_snippet', '$news_body', '$news_breaks', '".$data['user_id']."', '".time()."', '0', '0', '0', '0', '1', '1' ,'".LANGUAGE."')");
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
		$result = dbquery("SELECT ts.submit_criteria, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'");
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
			$news_cat_opts = "";
			$sel = "";
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
				echo display_html("publish", "news_snippet", TRUE, TRUE, TRUE);
				echo "</td>\n</tr>\n";
			}
			echo "<tr>\n";
			echo "<td valign='top' width='100' class='tbl'>".$locale['508']."</td>\n";
			echo "<td width='80%' class='tbl'><textarea name='news_body' cols='60' rows='10' class='textbox' style='width:300px;'>".$news_body."</textarea></td>\n";
			echo "</tr>\n";
			if ($settings['tinymce_enabled'] != 1) {
				echo "<tr>\n<td class='tbl'></td>\n<td class='tbl'>\n";
				echo display_html("publish", "news_body", TRUE, TRUE, TRUE);
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
if ((isset($_GET['action']) && $_GET['action'] == "2") && (isset($_GET['t']) && $_GET['t'] == "b")) {
	if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$result = dbquery("SELECT ts.*, tu.user_id, tu.user_name FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$blog_subject = stripinput($_POST['blog_subject']);
			$blog_cat = isnum($_POST['blog_cat']) ? $_POST['blog_cat'] : "0";
			$blog_snippet = addslash($_POST['blog_snippet']);
			$blog_body = addslash($_POST['blog_body']);
			$blog_breaks = ($_POST['blog_breaks'] == "y") ? "y" : "n";
			$result = dbquery("INSERT INTO ".DB_BLOG." (blog_subject, blog_cat, blog_blog, blog_extended, blog_breaks, blog_name, blog_datestamp, blog_start, blog_end, blog_visibility, blog_reads, blog_allow_comments, blog_allow_ratings, blog_language) VALUES ('$blog_subject', '$blog_cat', '$blog_snippet', '$blog_body', '$blog_breaks', '".$data['user_id']."', '".time()."', '0', '0', '0', '0', '1', '1' ,'".LANGUAGE."')");
			$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
			opentable($locale['490b']);
			echo "<br /><div style='text-align:center'>".$locale['491b']."<br /><br />\n";
			echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402b']."</a><br /><br />\n";
			echo "<a href='index.php".$aidlink."'>".$locale['403b']."</a></div><br />\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		opentable($locale['492b']);
		$result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
		echo "<br /><div style='text-align:center'>".$locale['493b']."<br /><br />\n";
		echo "<a href='".FUSION_SELF.$aidlink."'>".$locale['402b']."</a><br /><br />\n";
		echo "<a href='index.php".$aidlink."'>".$locale['403b']."</a></div><br />\n";
		closetable();
	} else {
		if ($settings['tinymce_enabled'] == 1) echo "<script type='text/javascript'>advanced();</script>\n";
		$result = dbquery("SELECT ts.submit_criteria, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$blog_subject = $submit_criteria['blog_subject'];
			$blog_cat = $submit_criteria['blog_cat'];
			if (isset($submit_criteria['blog_snippet'])) {
				$blog_snippet = phpentities(stripslashes($submit_criteria['blog_snippet']));
			} else {
				$blog_snippet = "";
			}
			$blog_body = phpentities(stripslashes($submit_criteria['blog_body']));
			$blog_breaks = "";
			$blog_cat_opts = "";
			$sel = "";
			$result2 = dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." ORDER BY blog_cat_name");
			if (dbrows($result2)) {
				while ($data2 = dbarray($result2)) {
					if (isset($blog_cat)) $sel = ($blog_cat == $data2['blog_cat_id'] ? " selected='selected'" : "");
					$blog_cat_opts .= "<option value='".$data2['blog_cat_id']."'$sel>".$data2['blog_cat_name']."</option>\n";
				}
			}
			add_to_title($locale['global_200'].$locale['503b'].$locale['global_201'].$blog_subject."?");
			if (isset($_POST['preview']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
				$blog_subject = stripinput($_POST['blog_subject']);
				$blog_cat = isnum($_POST['blog_cat']) ? $_POST['blog_cat'] : "0";
				$blog_snippet = stripslash($_POST['blog_snippet']);
				$blog_body = stripslash($_POST['blog_body']);
				$breaks = (isset($_POST['line_breaks']) ? " checked='checked'" : "");
				opentable($blog_subject);
				echo $locale['509b']." ".(isset($_POST['line_breaks']) ? nl2br($blog_snippet) : $blog_snippet)."<br /><br />";
				echo $locale['508b']." ".(isset($_POST['line_breaks']) ? nl2br($blog_body) : $blog_body);
				closetable();
			}
			opentable($locale['500b']);
			echo "<form name='publish' method='post' action='".FUSION_SELF.$aidlink."&amp;sub=submissions&amp;action=2&amp;t=b&amp;submit_id=".$_GET['submit_id']."'>\n";
			echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
			echo "<td width='100' class='tbl'>".$locale['505b']."</td>\n";
			echo "<td width='80%' class='tbl'><input type='text' name='blog_subject' value='$blog_subject' class='textbox' style='width: 250px' /></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td width='100' class='tbl'>".$locale['506b']."</td>\n";
			echo "<td width='80%' class='tbl'><select name='blog_cat' class='textbox'>\n";
			echo "<option value='0'>".$locale['507b']."</option>\n".$blog_cat_opts."</select></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td valign='top' width='100' class='tbl'>".$locale['509b']."</td>\n";
			echo "<td width='80%' class='tbl'><textarea name='blog_snippet' cols='60' rows='10' class='textbox' style='width:300px;'>".$blog_snippet."</textarea></td>\n";
			echo "</tr>\n";
			if ($settings['tinymce_enabled'] != 1) {
				echo "<tr>\n<td class='tbl'></td>\n<td class='tbl'>\n";
				echo display_html("publish", "blog_snippet", TRUE, TRUE, TRUE);
				echo "</td>\n</tr>\n";
			}
			echo "<tr>\n";
			echo "<td valign='top' width='100' class='tbl'>".$locale['508b']."</td>\n";
			echo "<td width='80%' class='tbl'><textarea name='blog_body' cols='60' rows='10' class='textbox' style='width:300px;'>".$blog_body."</textarea></td>\n";
			echo "</tr>\n";
			if ($settings['tinymce_enabled'] != 1) {
				echo "<tr>\n<td class='tbl'></td>\n<td class='tbl'>\n";
				echo display_html("publish", "blog_body", TRUE, TRUE, TRUE);
				echo "</td>\n</tr>\n";
			}
			echo "<tr>\n";
			echo "<td align='center' colspan='2' class='tbl1'><br />\n";
			echo $locale['501b'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br /><br />\n";
			echo $locale['502b']."<br />\n";
			echo "<input type='hidden' name='blog_breaks' value='".$blog_breaks."' />\n";
			echo "<input type='submit' name='preview' value='".$locale['510b']."' class='button' />\n";
			echo "<input type='submit' name='publish' value='".$locale['503b']."' class='button' />\n";
			echo "<input type='submit' name='delete' value='".$locale['504b']."' class='button' />\n";
			echo "</td>\n</tr>\n</table>\n</form>\n";
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "2") && (isset($_GET['t']) && $_GET['t'] == "a")) {
	if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		$result = dbquery("SELECT ts.submit_criteria, user_id
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'");
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
		$result = dbquery("SELECT ts.submit_criteria, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$article_cat = $submit_criteria['article_cat'];
			$article_subject = $submit_criteria['article_subject'];
			$article_snippet = phpentities(stripslashes($submit_criteria['article_snippet']));
			$article_body = phpentities(stripslashes($submit_criteria['article_body']));
			$article_breaks = "";
			$result2 = dbquery("SELECT article_cat_id, article_cat_name FROM ".DB_ARTICLE_CATS." ORDER BY article_cat_name DESC");
			$article_cat_opts = "";
			$sel = "";
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
				echo display_html("publish", "article_body", TRUE, TRUE, TRUE);
				echo "</td>\n</tr>\n";
			}
			echo "<tr>\n";
			echo "<td valign='top' width='100' class='tbl'>".$locale['548']."</td>\n";
			echo "<td width='80%' class='tbl'><textarea name='article_body' cols='60' rows='10' class='textbox' style='width:300px;'>".$article_body."</textarea></td>\n";
			echo "</tr>\n";
			if ($settings['tinymce_enabled'] != 1) {
				echo "<tr>\n<td class='tbl'></td>\n<td class='tbl'>\n";
				echo display_html("publish", "article_body", TRUE, TRUE, TRUE);
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
if ((isset($_GET['action']) && $_GET['action'] == "2") && (isset($_GET['t']) && $_GET['t'] == "p")) { photo_submissions_review(); }
if ((isset($_GET['action']) && $_GET['action'] == "2") && (isset($_GET['t']) && $_GET['t'] == "d")) { download_submissions_review(); }
require_once THEMES."templates/footer.php";

/**
 * The Download Submissions Review Form
 */
function download_submissions_review() {
	global $locale, $aidlink;
	// Publish
	if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		require_once INCLUDES."infusions_include.php";
		$result = dbquery("SELECT submit_user, submit_criteria
			FROM ".DB_SUBMISSIONS."
			WHERE submit_id='".$_GET['submit_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			// @todo: transfer form_sanitizer();
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
				$dest = DOWNLOADS."images/";
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
	}
	// Delete
	else if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
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
	}
	else {
		$result = dbquery("SELECT ts.submit_criteria, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			add_to_title($locale['global_200'].$locale['643'].$locale['global_201'].$submit_criteria['download_title']."?");
			opentable($locale['640']);
			echo openform('publish', 'post', FUSION_SELF.$aidlink."&amp;sub=submissions&amp;action=2&amp;t=d&amp;submit_id=".$_GET['submit_id'], array('max_tokens' => 1));
			echo "<div class='well'>\n";
			echo "<div class='pull-right'>\n";
			echo form_button('publish', $locale['643'], $locale['643'], array('class'=>'btn-primary m-r-10'));
			echo form_button('delete', $locale['644'], $locale['644'], array('class'=>'btn-default', 'icon'=>'fa fa-trash fa-fw'));
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo $locale['641'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br /><br />\n";
			echo $locale['642']."<br />\n";
			echo "</div>\n";
			echo "</div>\n";

			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-8'>\n";

			echo form_text('download_title', $locale['645'], $submit_criteria['download_title'], array('inline'=>1));
			echo form_textarea('download_description_short', $locale['645'], $submit_criteria['download_description_short'], array('inline'=>1, 'autosize'=>1));
			echo form_textarea('download_description', $locale['646'], $submit_criteria['download_description'], array('bbcode'=>1, 'form_name'=>'publish', 'inline'=>1));

			if (!empty($submit_criteria['download_file'])) {
				echo "<div class='col-sm-offset-3'>\n";
				echo "<span class='strong'>".$locale['647b']."</span> <a href='".DOWNLOADS."submissions/".$submit_criteria['download_file']."'>".DOWNLOADS."submissions/".$submit_criteria['download_file']."</a><br />\n";
				echo "</div>\n";
			} else {
				echo form_text('download_url', $locale['647'], $submit_criteria['download_url'], array('inline'=>1));
			}

			echo "</div>\n";
			echo "<div class='col-xs-12 col-sm-4'>\n";
			$cat_hidden = array();
			echo form_select_tree("download_cat", $locale['648'], $submit_criteria['download_cat'], array("disable_opts" => $cat_hidden, "hide_disabled" => 1, 'width'=>'100%'), DB_DOWNLOAD_CATS, "download_cat_name", "download_cat_id", "download_cat_parent");
			if (!empty($submit_criteria['download_image']) && !empty($submit_criteria['download_image_thumb'])) {
				echo "<div class='list-group-item clearfix'>\n";
				echo "<div class='pull-left'>".thumbnail(DOWNLOADS."submissions/images/".$submit_criteria['download_image_thumb'], '80px')."</div>\n";
				echo "<div class='overflow-hide'>\n";
				echo "<span class='strong'>".$locale['653']."</span><br/>\n";
				echo "<a href='".DOWNLOADS."submissions/images/".$submit_criteria['download_image']."' target='_blank'>File </a>";
				echo form_checkbox('del_image', $locale['658'], '');
				echo "</div>\n";
			}
			echo form_text('download_license', $locale['649'], $submit_criteria['download_license'], array('inline'=>1));
			echo form_text('download_os', $locale['650'], $submit_criteria['download_os'], array('inline'=>1));
			echo form_text('download_version', $locale['651'], $submit_criteria['download_version'], array('inline'=>1));
			echo form_text('download_homepage', $locale['654'], $submit_criteria['download_homepage'], array('inline'=>1));
			echo form_text('download_copyright', $locale['645'], $submit_criteria['download_copyright'], array('inline'=>1));
			echo form_text('download_filesize', $locale['652'], $submit_criteria['download_filesize'], array('inline'=>1));
			echo form_checkbox('download_allow_comments', $locale['656'], '', array('inline'=>1));
			echo form_checkbox('download_allow_ratings', $locale['657'], '', array('inline'=>1));
			echo "</div>\n";
			echo "</div>\n";
			echo closeform();

			/*
			 * Change to add notice here.
			if ($settings['ratings_enabled'] == "0") {
				echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
			}
			if ($settings['comments_enabled'] == "0") {
				echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
			}
			if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
				$sys = "";
				if ($settings['comments_enabled'] == "0" && $settings['ratings_enabled'] == "0") {
					$sys = $locale['663'];
				} elseif ($settings['comments_enabled'] == "0") {
					$sys = $locale['661'];
				} else {
					$sys = $locale['662'];
				}
				echo "<tr>\n<td colspan='2' class='tbl1' style='font-weight:bold;text-align:left; color:black !important; background-color:#FFDBDB;'>";
				echo "<span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['660'], $sys);
				echo "</td>\n</tr>";
			} */
			closetable();
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
}

/**
 * The Photo Submissions Review Form
 */
function photo_submissions_review() {
	global $locale, $aidlink;

	if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
		define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
		require_once INCLUDES."photo_functions_include.php";
		$photo_file = "";
		$photo_thumb1 = "";
		$photo_thumb2 = "";
		$result = dbquery("SELECT ts.submit_user, ts.submit_criteria
			FROM ".DB_SUBMISSIONS." ts
			WHERE submit_id='".$_GET['submit_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$photo_title = stripinput($_POST['photo_title']);
			$photo_description = stripinput($_POST['photo_description']);
			$album_id = isnum($_POST['album_id']) ? $_POST['album_id'] : "0";
			$photo_name = strtolower(substr($submit_criteria['photo_file'], 0, strrpos($submit_criteria['photo_file'], ".")));
			$photo_ext = strtolower(strrchr($submit_criteria['photo_file'], "."));
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
			$photo_order = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." WHERE album_id='$album_id'"), 0)+1;
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
		$result = dbquery("SELECT ts.submit_criteria, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_SUBMISSIONS." ts
			LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
			WHERE submit_id='".$_GET['submit_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$submit_criteria = unserialize($data['submit_criteria']);
			$photo_title = $submit_criteria['photo_title'];
			$photo_description = $submit_criteria['photo_description'];
			$photo_file = $submit_criteria['photo_file'];
			$album_id = $submit_criteria['album_id'];
			$photo_albums = "";
			$sel = "";
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