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
pageAccess('A');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/articles.php";

add_to_breadcrumbs(array('link'=>ADMIN.'articles.php'.$aidlink,'title'=>$locale['articles_0001']));
$settings = fusion_get_settings();
if ($settings['tinymce_enabled'] == 1) {
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
} else {
	require_once INCLUDES."html_buttons_include.php";
}

$message = '';
if (isset($_GET['status'])) {
	switch($_GET['status']) {
		case 'sn':
			$message = $locale['articles_0100'];
			$status = 'success';
			$icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
			break;
		case 'su':
			$message = $locale['articles_0101'];
			$status = 'info';
			$icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
			break;
		case 'del':
			$message = $locale['articles_0102'];
			$status = 'danger';
			$icon = "<i class='fa fa-trash fa-lg fa-fw'></i>";
			break;
	}
	if ($message) {
		addNotice($status, $icon.$message);
	}
}

$result = dbcount("(article_cat_id)", DB_ARTICLE_CATS." ".(multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."'" : "")."");

if (!empty($result)) {
	if (isset($_POST['save'])) {
		$subject = stripinput($_POST['subject']);
		$body = addslash($_POST['body']);
		$body2 = addslash($_POST['body2']);
		$keywords = stripinput($_POST['keywords']);
		$article_visibility = form_sanitizer($_POST['article_visibility'], '0', 'article_visibility');
		$draft = isset($_POST['article_draft']) ? "1" : "0";
		if ($settings['tinymce_enabled'] != 1) {
			$breaks = isset($_POST['line_breaks']) ? "y" : "n";
		} else {
			$breaks = "n";
		}
		$comments = isset($_POST['article_comments']) ? "1" : "0";
		$ratings = isset($_POST['article_ratings']) ? "1" : "0";
		if (isset($_POST['article_id']) && isnum($_POST['article_id']) && !defined("FUSION_NULL")) {
			$result = dbquery("UPDATE ".DB_ARTICLES." SET article_cat='".intval($_POST['article_cat'])."', article_subject='$subject', article_snippet='$body', article_article='$body2', article_keywords='$keywords', article_visibility='$article_visibility', article_draft='$draft', article_breaks='$breaks', article_allow_comments='$comments', article_allow_ratings='$ratings' WHERE article_id='".$_POST['article_id']."'");
			redirect(FUSION_SELF.$aidlink."&status=su");
		} elseif (!defined("FUSION_NULL")) {
			$result = dbquery("INSERT INTO ".DB_ARTICLES." (article_cat, article_subject, article_snippet, article_article, article_keywords, article_draft, article_breaks, article_name, article_datestamp, article_visibility, article_reads, article_allow_comments, article_allow_ratings) VALUES ('".intval($_POST['article_cat'])."', '$subject', '$body', '$body2', '$keywords', '$draft', '$breaks', '".$userdata['user_id']."', '".time()."', '$article_visibility', '0', '$comments', '$ratings')");
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
			$keywords = stripinput($_POST['keywords']);
			$bodypreview = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, stripslash($_POST['body']));
			$body2preview = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, stripslash($_POST['body2']));
			$article_visibility = form_sanitizer($_POST['article_visibility'], '0', 'article_visibility');
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
			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-body'>\n";
			echo "<div class='well'>\n";
			echo "<small><strong>".$locale['articles_0202']."</strong></small><br/>";
			echo $bodypreview."\n";
			echo "</div>\n";
			echo "<small><strong>".$locale['articles_0203']."</strong></small><br/>";
			echo $body2preview."\n";
			echo "</div>\n</div>\n";
			closetable();
		}
		$result = dbquery("SELECT article_id, article_subject, article_draft FROM ".DB_ARTICLES." ORDER BY article_draft DESC, article_datestamp DESC");
		if (dbrows($result)) {
			$editlist = array();
			while ($data = dbarray($result)) {
				$editlist[$data['article_id']] = "".($data['article_draft'] ? $locale['articles_0210']." " : "").$data['article_subject']."";
			}
			opentable($locale['articles_0000']);
			echo openform('selectform', 'post', FUSION_SELF.$aidlink."&amp;action=edit", array('max_tokens' => 1));
			echo "<div class='text-center'>\n";
			echo form_select('', 'article_id', 'article_id', $editlist, '', array('placeholder' => $locale['choose'], 'inline' => 1, 'class' => 'pull-left'));
			echo form_button('edit', $locale['edit'], $locale['edit'], array('class' => 'pull-left btn-primary m-l-10 m-r-10'));
			echo form_button('delete', $locale['delete'], $locale['delete'], array('class' => 'pull-left btn-primary'));
			add_to_jquery("
                $('#delete').bind('click',function(e){ DeleteArticle(); });
                ");
			echo "</div>\n";
			echo closeform();
			closetable();
		}
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['article_id']) && isnum($_POST['article_id'])) || (isset($_GET['article_id']) && isnum($_GET['article_id']))) {
			$id = "";
			if (isset($_POST['article_id']) && isnum($_POST['article_id'])) {
				$id = $_POST['article_id'];
			} elseif (isset($_GET['article_id']) && isnum($_GET['article_id'])) {
				$id = $_GET['article_id'];
			}
			$result = dbquery("SELECT article_cat, article_subject, article_snippet, article_article, article_keywords, article_visibility, article_draft, article_breaks, article_allow_comments, article_allow_ratings FROM ".DB_ARTICLES." WHERE article_id='".$id."'");
			if (dbrows($result)) {
				$data = dbarray($result);
				$article_cat = $data['article_cat'];
				$subject = $data['article_subject'];
				$body = phpentities(stripslashes($data['article_snippet']));
				$body2 = phpentities(stripslashes($data['article_article']));
				$keywords = $data['article_keywords'];
				$article_visibility = $data['article_visibility'];
				$draft = $data['article_draft'] ? " checked='checked'" : "";
				$breaks = $data['article_breaks'] == "y" ? " checked='checked'" : "";
				$comments = $data['article_allow_comments'] ? " checked='checked'" : "";
				$ratings = $data['article_allow_ratings'] ? " checked='checked'" : "";
			} else {
				redirect(FUSION_SELF.$aidlink);
			}
		}
		if ((isset($_POST['article_id']) && isnum($_POST['article_id'])) || (isset($_GET['article_id']) && isnum($_GET['article_id']))) {
			opentable($locale['articles_0003']);

		} else {
			if (!isset($_POST['preview'])) {
				$article_cat = '';
				$subject = "";
				$body = "";
				$body2 = "";
				$keywords = "";
				$article_visibility = "0";
				$draft = "";
				$breaks = " checked='checked'";
				$comments = " checked='checked'";
				$ratings = " checked='checked'";
			}
			opentable($locale['articles_0002']);
		}

		$visibility_opts = array();
		$user_groups = getusergroups();
		while (list($key, $user_group) = each($user_groups)) {
			$visibility_opts[$user_group['0']] = $user_group['1'];
		}

		echo openform('input_form', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
		echo "<table cellpadding='0' cellspacing='0' class='table table-responsive center'>\n<tr>\n";
		echo "<td width='100' class='tbl'><label for='article_cat'>".$locale['articles_0201']."</label></td>\n";
		echo "<td class='tbl'>\n";
		echo form_select_tree("", "article_cat", "article_cat", $article_cat, array("no_root" => 1, "placeholder" => $locale['choose'], "query" => (multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."'" : "")), DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent");
		echo "</td>\n</tr>\n<tr>\n";
		echo "<td width='100' class='tbl'><label for='subject'>".$locale['articles_0200']." <span class='required'>*</span></label></td>\n";
		echo "<td class='tbl'>\n";
		echo form_text('subject', '', $subject, array('required' => 1));
		echo "</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td valign='top' width='100' class='tbl'><label for='body'>".$locale['articles_0202']."</label></td>\n";
		echo "<td class='tbl'>\n";
		echo form_textarea('body', '', $body);
		echo "</td>\n";
		echo "</tr>\n";
		if ($settings['tinymce_enabled'] != 1) {
			echo "<tr>\n<td class='tbl'></td>\n<td class='tbl'>\n";
			echo display_html("input_form", "body", TRUE, TRUE, TRUE, IMAGES_A);
			echo "</td>\n</tr>\n";
		}
		echo "<tr>\n<td valign='top' width='100' class='tbl'><label for='body2'>".$locale['articles_0203']."</label></td>\n";
		echo "<td class='tbl'>\n";
		echo form_textarea('body2', '', $body2);
		echo "</tr>\n";
		if ($settings['tinymce_enabled'] != 1) {
			echo "<tr>\n<td class='tbl'></td><td class='tbl'>\n";
			echo "<input type='button' value='".$locale['articles_0209']."' class='button' onclick=\"insertText('body2', '&lt;!--PAGEBREAK--&gt;', 'input_form');\" />\n";
			echo display_html("input_form", "body2", TRUE, TRUE, TRUE, IMAGES_A);
			echo "</td>\n</tr>\n";
		}

		echo "<tr>\n<td valign='top' width='100' class='tbl'><label for='keywords'>".$locale['articles_0204']."</label></td>\n";
		echo "<td class='tbl'>\n";
		echo form_select('', 'keywords', 'keywords', array(), $keywords, array('max_length' => 320, 'width'=>'100%', 'error_text' => $locale['articles_0257'], 'tags'=>1, 'multiple' => 1));
		echo "</td>\n</tr>\n";
					
		echo "<tr>\n<td valign='top' width='100' class='tbl'><label for='article_visibility'>".$locale['articles_0211']."</label></td>\n";
		echo "<td class='tbl'>\n";
		echo form_select("", 'article_visibility', 'article_visibility', $visibility_opts, $data['article_visibility'], array('placeholder' => $locale['choose']));
		echo "</td>\n</tr>\n";
					
		echo "<tr>\n";
		echo "<td class='tbl'></td><td class='tbl'>\n";
		echo "<label><input type='checkbox' name='article_draft' value='yes'".$draft." /> ".$locale['articles_0205']."</label><br />\n";
		if ($settings['tinymce_enabled'] != 1) {
			echo "<label><input type='checkbox' name='line_breaks' value='yes'".$breaks." /> ".$locale['articles_0206']."</label><br />\n";
		}
		echo "<label><input type='checkbox' name='article_comments' value='yes'".$comments." /> ".$locale['articles_0207']."</label>";
		if ($settings['comments_enabled'] == "0") {
			echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
		}
		echo "<br />\n";
		echo "<label><input type='checkbox' name='article_ratings' value='yes'".$ratings." /> ".$locale['articles_0208']."</label>";
		if ($settings['ratings_enabled'] == "0") {
			echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
		}
		echo "</td>\n";
		echo "</tr>\n";
		if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
			$sys = "";
			if ($settings['comments_enabled'] == "0" && $settings['ratings_enabled'] == "0") {
				$sys = $locale['comments_ratings'];
			} elseif ($settings['comments_enabled'] == "0") {
				$sys = $locale['comments'];
			} else {
				$sys = $locale['ratings'];
			}
			echo "<tr>\n<td colspan='2' class='tbl1' style='font-weight:bold;text-align:left; color:black !important; background-color:#FFDBDB;'>";
			echo "<span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['articles_0256'], $sys);
			echo "</td>\n</tr>";
		}
		echo "<tr>\n";
		echo "<td align='center' colspan='2' class='tbl'><br />\n";
		if ((isset($_POST['article_id']) && isnum($_POST['article_id'])) || (isset($_GET['article_id']) && isnum($_GET['article_id']))) {
			echo form_hidden('', 'article_id', 'article_id', isset($_POST['article_id']) ? $_POST['article_id'] : $_GET['article_id']);
			//echo "<input type='hidden' name='article_id' value='".(isset($_POST['article_id']) ? $_POST['article_id'] : $_GET['article_id'])."' />\n";
		}
		echo form_button('preview', $locale['articles_0240'], $locale['articles_0240'], array('class' => 'btn-primary m-r-10'));
		echo form_button('save', $locale['articles_0241'], $locale['articles_0241'], array('class' => 'btn-primary'));
		echo "</tr>\n</table>\n</form>\n";
		closetable();
		add_to_jquery("
            function DeleteArticle() { return confirm('".$locale['articles_0251']."');}
            $('#save, #preview').bind('click', function(e) {
            var subject = $('#subject').val();
            if (subject == '') { alert('".$locale['articles_0250']."'); return false; }
            });
            ");
	}
} else {
	opentable($locale['articles_0001']);
	echo "<div style='text-align:center'>".$locale['articles_0252']."<br />\n".$locale['articles_0253']."<br />\n";
	echo "<a href='article_cats.php".$aidlink."'>".$locale['articles_0254']."</a>".$locale['articles_0255']."</div>\n";
	closetable();
}

require_once THEMES."templates/footer.php";
?>
