<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles.php
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

if (!checkrights("A") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header_mce.php";
include LOCALE.LOCALESET."admin/articles.php";

if ($settings['tinymce_enabled'] == 1) {
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
} else {
	require_once INCLUDES."html_buttons_include.php";
}

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['410'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['411'];
	} elseif ($_GET['status'] == "del") {
		$message = $locale['412'];
	}
	if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}

$result = dbcount("(article_cat_id)", DB_ARTICLE_CATS);
if (!empty($result)) {
	if (isset($_POST['save'])) {
		$subject = stripinput($_POST['subject']);
		$body = addslash($_POST['body']);
		$body2 = addslash($_POST['body2']);
		$draft = isset($_POST['article_draft']) ? "1" : "0";
		if ($settings['tinymce_enabled'] != 1) { $breaks = isset($_POST['line_breaks']) ? "y" : "n"; } else { $breaks = "n"; }
		$comments = isset($_POST['article_comments']) ? "1" : "0";
		$ratings = isset($_POST['article_ratings']) ? "1" : "0";
		if (isset($_POST['article_id']) && isnum($_POST['article_id'])) {
			$result = dbquery("UPDATE ".DB_ARTICLES." SET article_cat='".intval($_POST['article_cat'])."', article_subject='$subject', article_snippet='$body', article_article='$body2', article_draft='$draft', article_breaks='$breaks', article_allow_comments='$comments', article_allow_ratings='$ratings' WHERE article_id='".$_POST['article_id']."'");
			redirect(FUSION_SELF.$aidlink."&status=su");
		} else {
			$result = dbquery("INSERT INTO ".DB_ARTICLES." (article_cat, article_subject, article_snippet, article_article, article_draft, article_breaks, article_name, article_datestamp, article_reads, article_allow_comments, article_allow_ratings) VALUES ('".intval($_POST['article_cat'])."', '$subject', '$body', '$body2', '$draft', '$breaks', '".$userdata['user_id']."', '".time()."', '0', '$comments', '$ratings')");
			redirect(FUSION_SELF.$aidlink."&status=sn");
		}
	} else if (isset($_POST['delete']) && (isset($_POST['article_id']) && isnum($_POST['article_id']))) {
		$result = dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_id='".$_POST['article_id']."'");
		$result = dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_item_id='".$_POST['article_id']."' and comment_type='A'");
		$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$_POST['article_id']."' and rating_type='A'");
		redirect(FUSION_SELF.$aidlink."&status=del");
	} else {
		if (isset($_POST['preview'])) {
			$article_cat = $_POST['article_cat'];
			$subject = stripinput($_POST['subject']);
			$body = phpentities(stripslash($_POST['body']));
			$body2 = phpentities(stripslash($_POST['body2']));
			$bodypreview = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, stripslash($_POST['body']));
			$body2preview = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, stripslash($_POST['body2']));
			$draft = isset($_POST['article_draft']) ? " checked='checked'" : "";
			if (isset($_POST['line_breaks'])) {
				$breaks = " checked='checked'";
				$bodypreview = nl2br($bodypreview);
				$body2preview = nl2br($body2preview);
			} else {
				$breaks = "";
			}
			$comments = isset($_POST['article_comments']) ? " checked='checked'" : "";
			$ratings = isset($_POST['article_ratings']) ? " checked='checked'" : "";
			opentable($subject);
			echo $bodypreview."\n";
			closetable();
			opentable($subject);
			echo $body2preview."\n";
			closetable();
		}
		$result = dbquery("SELECT article_id, article_subject, article_draft FROM ".DB_ARTICLES." ORDER BY article_draft DESC, article_datestamp DESC");
		if (dbrows($result)) {
			$editlist = ""; $sel = "";
			while ($data = dbarray($result)) {
				if ((isset($_POST['article_id']) && isnum($_POST['article_id'])) || (isset($_GET['article_id']) && isnum($_GET['article_id']))) {
					$article_id = isset($_POST['article_id']) ? $_POST['article_id'] : $_GET['article_id'];
					$sel = ($article_id == $data['article_id'] ? " selected='selected'" : "");
				} else {
					$sel = "";
				}
				$editlist .= "<option value='".$data['article_id']."'".$sel.">".($data['article_draft'] ? $locale['433']." " : "").$data['article_subject']."</option>\n";
			}
			opentable($locale['402']);
			echo "<div style='text-align:center'>\n<form name='selectform' method='post' action='".FUSION_SELF.$aidlink."&amp;action=edit'>\n";
			echo "<select name='article_id' class='textbox' style='width:250px;'>\n".$editlist."</select>\n";
			echo "<input type='submit' name='edit' value='".$locale['420']."' class='button' />\n";
			echo "<input type='submit' name='delete' value='".$locale['421']."' onclick='return DeleteArticle();' class='button' />\n";
			echo "</form>\n</div>\n";
			closetable();
		}

		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['article_id']) && isnum($_POST['article_id'])) || (isset($_GET['article_id']) && isnum($_GET['article_id']))) {
			$id = "";
			if (isset($_POST['article_id']) && isnum($_POST['article_id'])) {
				$id = $_POST['article_id'];
			} elseif (isset($_GET['article_id']) && isnum($_GET['article_id'])) {
				$id = $_GET['article_id'];
			}
			$result = dbquery("SELECT article_cat, article_subject, article_snippet, article_article, article_draft, article_breaks, article_allow_comments, article_allow_ratings FROM ".DB_ARTICLES." WHERE article_id='".$id."'");
			if (dbrows($result)) {
				$data = dbarray($result);
				$article_cat = $data['article_cat'];
				$subject = $data['article_subject'];
				$body = phpentities(stripslashes($data['article_snippet']));
				$body2 = phpentities(stripslashes($data['article_article']));
				$draft = $data['article_draft'] ? " checked='checked'" : "";
				$breaks = $data['article_breaks'] == "y" ? " checked='checked'" : "";
				$comments = $data['article_allow_comments'] ? " checked='checked'" : "";
				$ratings = $data['article_allow_ratings'] ? " checked='checked'" : "";
			} else {
				redirect(FUSION_SELF.$aidlink);
			}
		}
		if ((isset($_POST['article_id']) && isnum($_POST['article_id'])) || (isset($_GET['article_id']) && isnum($_GET['article_id']))) {
			opentable($locale['401']);
		} else {
			if (!isset($_POST['preview'])) {
				$subject = "";
				$body = "";
				$body2 = "";
				$draft = "";
				$breaks = " checked='checked'";
				$comments = " checked='checked'";
				$ratings = " checked='checked'";
			}
			opentable($locale['400']);
		}
		$result = dbquery("SELECT article_cat_id, article_cat_name FROM ".DB_ARTICLE_CATS." ORDER BY article_cat_name DESC");
		$catlist = ""; $sel = "";
		while ($data = dbarray($result)) {
			if (isset($article_cat)) $sel = ($article_cat == $data['article_cat_id'] ? " selected='selected'" : "");
			$catlist .= "<option value='".$data['article_cat_id']."'$sel>".$data['article_cat_name']."</option>\n";
		}
		echo "<form name='inputform' method='post' action='".FUSION_SELF.$aidlink."' onsubmit='return ValidateForm(this)'>\n";
		echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
		echo "<td width='100' class='tbl'>".$locale['422']."</td>\n";
		echo "<td class='tbl'><select name='article_cat' class='textbox' style='width:250px;'>".$catlist."</select></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='100' class='tbl'>".$locale['423']."</td>\n";
		echo "<td class='tbl'><input type='text' name='subject' value='$subject' class='textbox' style='width:250px;' /></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td valign='top' width='100' class='tbl'>".$locale['424']."</td>\n";
		echo "<td class='tbl'><textarea name='body' cols='95' rows='5' class='textbox' style='width:98%'>".$body."</textarea></td>\n";
		echo "</tr>\n";
		if ($settings['tinymce_enabled'] != 1) {
			echo "<tr>\n<td class='tbl'></td>\n<td class='tbl'>\n";
			echo display_html("inputform", "body", true, true, true, IMAGES_A);
			echo "</td>\n</tr>\n";
		}
		echo "<tr>\n<td valign='top' width='100' class='tbl'>".$locale['425']."</td>\n";
		echo "<td class='tbl'><textarea name='body2' cols='95' rows='10' class='textbox' style='width:98%'>".$body2."</textarea></td>\n";
		echo "</tr>\n";
		if ($settings['tinymce_enabled'] != 1) {
			echo "<tr>\n<td class='tbl'></td><td class='tbl'>\n";
			echo "<input type='button' value='".$locale['432']."' class='button' onclick=\"insertText('body2', '&lt;!--PAGEBREAK--&gt;');\" />\n";
			echo display_html("inputform", "body2", true, true, true, IMAGES_A);
			echo "</td>\n</tr>\n";
		}
		echo "<tr>\n";
		echo "<td class='tbl'></td><td class='tbl'>\n";
		echo "<label><input type='checkbox' name='article_draft' value='yes'".$draft." /> ".$locale['426']."</label><br />\n";
		if ($settings['tinymce_enabled'] != 1) { echo "<label><input type='checkbox' name='line_breaks' value='yes'".$breaks." /> ".$locale['427']."</label><br />\n"; }
		echo "<label><input type='checkbox' name='article_comments' value='yes'".$comments." /> ".$locale['428']."</label>";
		if ($settings['comments_enabled'] == "0") {
			echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
		}
		echo "<br />\n";
		echo "<label><input type='checkbox' name='article_ratings' value='yes'".$ratings." /> ".$locale['429']."</label>";
		if ($settings['ratings_enabled'] == "0") {
			echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
		}
		echo "</td>\n";
		echo "</tr>\n";
		if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
			$sys = "";
			if ($settings['comments_enabled'] == "0" &&  $settings['ratings_enabled'] == "0") {
				$sys = $locale['459'];
			} elseif ($settings['comments_enabled'] == "0") {
				$sys = $locale['457'];
			} else {
				$sys = $locale['458'];
			}
			echo "<tr>\n<td colspan='2' class='tbl1' style='font-weight:bold;text-align:left; color:black !important; background-color:#FFDBDB;'>";
			echo "<span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['456'], $sys);
			echo "</td>\n</tr>";
		}
		echo "<tr>\n";
		echo "<td align='center' colspan='2' class='tbl'><br />\n";
		if ((isset($_POST['article_id']) && isnum($_POST['article_id'])) || (isset($_GET['article_id']) && isnum($_GET['article_id']))) {
			echo "<input type='hidden' name='article_id' value='".(isset($_POST['article_id']) ? $_POST['article_id'] : $_GET['article_id'])."' />\n";
		}
		echo "<input type='submit' name='preview' value='".$locale['430']."' class='button' />\n";
		echo "<input type='submit' name='save' value='".$locale['431']."' class='button' /></td>\n";
		echo "</tr>\n</table>\n</form>\n";
		closetable();
		echo "<script type='text/javascript'>\n";
		echo "/* <![CDATA[ */\n";
		echo "function DeleteArticle() {\n";
		echo "return confirm('".$locale['451']."');\n}\n";
		echo "function ValidateForm(frm) {"."\n"."if(frm.subject.value=='') {\n";
		echo "alert('".$locale['450']."');"."\n"."return false;\n}\n}\n";
		echo "/* ]]>*/\n";
		echo "</script>\n";
	}
} else {
	opentable($locale['403']);
	echo "<div style='text-align:center'>".$locale['452']."<br />\n".$locale['453']."<br />\n";
	echo "<a href='article_cats.php".$aidlink."'>".$locale['454']."</a>".$locale['455']."</div>\n";
	closetable();
}

require_once THEMES."templates/footer.php";
?>
