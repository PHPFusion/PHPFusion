<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles.php
| Author: Nick Jones (Digitanium)
| Co-Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "maincore.php";
define('ARTICLES', TRUE);

require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."articles.php";

if (isset($_GET['article_id']) && isnum($_GET['article_id'])) {
	$result = dbquery("SELECT
		ta.article_cat,
			tac.article_cat_name,
		ta.article_id,
		ta.article_subject
		FROM ".DB_ARTICLES." ta
		LEFT JOIN ".DB_ARTICLE_CATS." tac
		ON ta.article_cat=tac.article_cat_id
		".(multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."' AND" : "WHERE")." article_id='".$_GET['article_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		// Render the breadcrumbs
		echo render_breadcrumbs($data);
	}
} elseif (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
	$result = dbquery("SELECT article_cat_name FROM ".DB_ARTICLE_CATS."
		".(multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."' AND" : "WHERE")." article_cat_id='".$_GET['cat_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		// Render the breadcrumbs
		echo render_breadcrumbs($data);
	}
}

add_to_title($locale['global_200'].$locale['400']);
if (isset($_GET['article_id']) && isnum($_GET['article_id'])) {
	$result = dbquery("SELECT ta.article_subject, ta.article_article, ta.article_breaks,
		ta.article_datestamp, ta.article_reads, ta.article_allow_comments, ta.article_allow_ratings,
		tac.article_cat_id, tac.article_cat_name,
		tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_joined, tu.user_level
		FROM ".DB_ARTICLES." ta
		INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
		LEFT JOIN ".DB_USERS." tu ON ta.article_name=tu.user_id
		WHERE ".groupaccess('article_cat_access')." AND article_id='".$_GET['article_id']."' AND article_draft='0'");
	if (dbrows($result)) {
		require_once INCLUDES."comments_include.php";
		require_once INCLUDES."ratings_include.php";
		$data = dbarray($result);
		if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
			$_GET['rowstart'] = 0;
		}
		if ($_GET['rowstart'] == 0) {
			$result = dbquery("UPDATE ".DB_ARTICLES." SET article_reads=article_reads+1 WHERE article_id='".$_GET['article_id']."'");
		}
		$article = preg_split("/<!?--\s*pagebreak\s*-->/i", stripslashes($data['article_article']));
		$pagecount = count($article);
		$article_subject = stripslashes($data['article_subject']);
		$article_info = array("article_id" => $_GET['article_id'], "cat_id" => $data['article_cat_id'],
							  "cat_name" => $data['article_cat_name'], "user_id" => $data['user_id'],
							  "user_name" => $data['user_name'], "user_status" => $data['user_status'],
							  "user_avatar" => $data['user_avatar'], "user_joined" => $data['user_joined'],
							  "user_level" => $data['user_level'], "article_date" => $data['article_datestamp'],
							  "article_breaks" => $data['article_breaks'],
							  "article_comments" => dbcount("(comment_id)", DB_COMMENTS, "comment_type='A' AND comment_item_id='".$_GET['article_id']."'"),
							  "article_reads" => $data['article_reads'],
							  "article_allow_comments" => $data['article_allow_comments']);
		add_to_title($locale['global_201'].$article_subject);
		echo "<!--pre_article-->";
		render_article($article_subject, $article[$_GET['rowstart']], $article_info);
		echo "<!--sub_article-->";
		if ($pagecount > 1) {
			echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 1, $pagecount, 3, BASEDIR."articles.php?article_id=".$_GET['article_id']."&amp;")."\n</div>\n";
		}
		if ($data['article_allow_comments']) {
			showcomments("A", DB_ARTICLES, "article_id", $_GET['article_id'], BASEDIR."articles.php?article_id=".$_GET['article_id']);
		}
		if ($data['article_allow_ratings']) {
			showratings("A", $_GET['article_id'], BASEDIR."articles.php?article_id=".$_GET['article_id']);
		}
	} else {
		redirect(FUSION_SELF);
	}
} elseif (!isset($_GET['cat_id']) || !isnum($_GET['cat_id'])) {
	opentable($locale['400']);
	echo "<!--pre_article_idx-->\n";
	$result = dbquery("SELECT ac.article_cat_id, ac.article_cat_name, ac.article_cat_description, COUNT(a.article_cat) AS article_count FROM ".DB_ARTICLES." a
		LEFT JOIN ".DB_ARTICLE_CATS." ac ON a.article_cat=ac.article_cat_id
		".(multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('ac.article_cat_access')."
		GROUP BY ac.article_cat_id
		ORDER BY ac.article_cat_name");
	$rows = dbrows($result);
	if ($rows) {
		$counter = 0;
		$columns = 2;
		echo "<div class='row'>\n";
		while ($data = dbarray($result)) {
			if ($counter != 0 && ($counter%$columns == 0)) {
				echo "</div>\n<div class='row'>\n";
			}
			echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
			echo "<!--article_idx_cat_name-->\n";
			echo "<h4><a href='".BASEDIR."articles.php?cat_id=".$data['article_cat_id']."'><strong>".$data['article_cat_name']."</a></strong> <span class='small2'>(".$data['article_count'].")</span></h4>";
			echo ($data['article_cat_description'] != "") ? $data['article_cat_description'] : "";
			echo "</div>\n";
			$counter++;
		}
		echo "</div>\n";
	} else {
		echo "<div style='text-align:center'><br />\n".$locale['401']."<br /><br />\n</div>\n";
	}
	echo "<!--sub_article_idx-->\n";
	closetable();
} else {
	$res = 0;
	$result = dbquery("SELECT article_cat_name, article_cat_sorting, article_cat_access FROM ".DB_ARTICLE_CATS." WHERE article_cat_id='".$_GET['cat_id']."'");
	if (dbrows($result) != 0) {
		$cdata = dbarray($result);
		if (checkgroup($cdata['article_cat_access'])) {
			$res = 1;
			add_to_title($locale['global_201'].$cdata['article_cat_name']);
			opentable($locale['400'].": ".$cdata['article_cat_name']);
			echo "<!--pre_article_cat-->";
			$rows = dbcount("(article_id)", DB_ARTICLES, "article_cat='".$_GET['cat_id']."' AND article_draft='0'");
			if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
				$_GET['rowstart'] = 0;
			}
			if ($rows != 0) {
				$result = dbquery("SELECT article_id, article_subject, article_snippet, article_datestamp FROM ".DB_ARTICLES."
					WHERE article_cat='".$_GET['cat_id']."' AND article_draft='0' ORDER BY ".$cdata['article_cat_sorting']." LIMIT ".$_GET['rowstart'].",".$settings['articles_per_page']);
				$numrows = dbrows($result);
				$i = 1;
				while ($data = dbarray($result)) {
					$class = ($i%2 ? "tbl1" : "tbl2");
					$new = ($data['article_datestamp']+604800 > time()+($settings['timeoffset']*3600)) ? "&nbsp;<label class='label label-success'>".$locale['402']."</label>" : '';
					echo "<h4><strong><a href='".BASEDIR."articles.php?article_id=".$data['article_id']."'>".$data['article_subject']."</a></strong>".$new."</h4>\n";
					echo preg_replace("/<!?--\s*pagebreak\s*-->/i", "", stripslashes($data['article_snippet']))."\n";
					$i++;
				}
				echo "<!--sub_article_cat-->";
				closetable();
				if ($rows > $settings['articles_per_page']) echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], $settings['articles_per_page'], $rows, 3, BASEDIR."articles.php?cat_id=".$_GET['cat_id']."&amp;")."\n</div>\n";
			} else {
				echo "<div style='text-align:center'>".$locale['403']."</div>\n";
				echo "<!--sub_article_cat-->";
				closetable();
			}
		}
	}
	if ($res == 0) {
		redirect(FUSION_SELF);
	}
}
require_once THEMES."templates/footer.php";
?>