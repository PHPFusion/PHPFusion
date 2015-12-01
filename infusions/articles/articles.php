<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
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
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";
if (!db_exists(DB_ARTICLES)) { redirect(BASEDIR."error.php?code=404"); }
require_once THEMES."templates/header.php";
require_once INCLUDES."infusions_include.php";
include INFUSIONS."articles/locale/".LOCALESET."articles.php";
include INFUSIONS."articles/templates/articles.php";

$info = array();

add_to_title($locale['global_200'].\PHPFusion\SiteLinks::get_current_SiteLinks("", "link_name"));

add_breadcrumb(array('link' => INFUSIONS.'articles/articles.php', 'title' => \PHPFusion\SiteLinks::get_current_SiteLinks("", "link_name")));
$article_settings = get_settings("article");
/* Render Articles */
if (isset($_GET['article_id']) && isnum($_GET['article_id'])) {
	$result = dbquery("SELECT ta.article_subject, ta.article_snippet, ta.article_article, ta.article_keywords, ta.article_breaks,
		ta.article_datestamp, ta.article_reads, ta.article_allow_comments, ta.article_allow_ratings,
		tac.article_cat_id, tac.article_cat_name,
		tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_joined, tu.user_level
		FROM ".DB_ARTICLES." ta
		INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
		LEFT JOIN ".DB_USERS." tu ON ta.article_name=tu.user_id
		".(multilang_table("AR") ? "WHERE tac.article_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('article_visibility')." AND article_id='".$_GET['article_id']."' AND article_draft='0'");
	if (dbrows($result) > 0) {
		$data = dbarray($result);
		require_once INCLUDES."comments_include.php";
		require_once INCLUDES."ratings_include.php";

        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;
		if (($_GET['rowstart'] == 0) && empty($_POST)) {
			dbquery("UPDATE ".DB_ARTICLES." SET article_reads=article_reads+1 WHERE article_id='".$_GET['article_id']."'");
		}

        $article = preg_split("/<!?--\s*pagebreak\s*-->/i", parse_textarea($data['article_article']));

        $pagecount = count($article);

        $article_subject = stripslashes($data['article_subject']);

        add_breadcrumb(array(
						   'link' => INFUSIONS.'articles/articles.php?cat_id='.$data['article_cat_id'],
						   'title' => $data['article_cat_name']
					   ));

        add_breadcrumb(array(
						   'link' => INFUSIONS.'articles/articles.php?article_id='.$_GET['article_id'],
						   'title' => $data['article_subject']
					   ));

        if ($data['article_keywords'] !== "") {
			set_meta("keywords", $data['article_keywords']);
		}

        $article_info = array(
			"article_id" => $_GET['article_id'],
			"article_subject" => $article_subject,
			"article_snippet" => parse_textarea($data['article_snippet']),
			"article_article" => $article,
			"cat_id" => $data['article_cat_id'],
			"cat_name" => $data['article_cat_name'],
			"user_id" => $data['user_id'],
			"user_name" => $data['user_name'],
			"user_status" => $data['user_status'],
			"user_avatar" => $data['user_avatar'],
			"user_joined" => $data['user_joined'],
			"user_level" => $data['user_level'],
			"article_date" => $data['article_datestamp'],
			"article_breaks" => $data['article_breaks'],
			"article_comments" => dbcount("(comment_id)", DB_COMMENTS, "comment_type='A' AND comment_item_id='".$_GET['article_id']."'"),
			"article_reads" => $data['article_reads'],
			"article_allow_comments" => $data['article_allow_comments'],
			"article_allow_ratings" => $data['article_allow_ratings'],
			"page_nav" => $pagecount > 1 ? makepagenav($_GET['rowstart'], 1, $pagecount, 3, INFUSIONS."articles/articles.php?article_id=".$_GET['article_id']."&amp;") : "",
			"edit_link" => "",
		);
		if (iADMIN && checkrights("A")) {
			$article_info['edit_link'] = INFUSIONS."articles/articles_admin.php".$aidlink."&amp;action=edit&amp;section=article_form&amp;article_id=".$article_info['article_id'];
		}

        set_title($article_subject.$locale['global_200'].$locale['400']);

		render_article($article_subject, $article[$_GET['rowstart']], $article_info);
	} else {
		redirect(INFUSIONS."articles/articles.php");
	}
} elseif (!isset($_GET['cat_id']) || !isnum($_GET['cat_id'])) {
	// category query
	$result = dbquery("SELECT
		ac.article_cat_id, ac.article_cat_name, ac.article_cat_description, count(a.article_id) 'article_count'
		FROM ".DB_ARTICLE_CATS." ac
		LEFT JOIN ".DB_ARTICLES." a on a.article_cat=ac.article_cat_id
		".(multilang_table("AR") ? "WHERE ac.article_cat_language='".LANGUAGE."' AND" : "WHERE")."
		ac.article_cat_parent = '0'
		GROUP BY ac.article_cat_id
		ORDER BY ac.article_cat_name
		");
	$info['articles_rows'] = dbrows($result);
	if ($info['articles_rows'] > 0) {
		while ($data = dbarray($result)) {
			$data['article_cat_description'] = parse_textarea($data['article_cat_description']);
			$info['articles']['item'][] = $data;
		}
	}
	render_articles_main($info);
} else {
	// View articles in a category
	$result = dbquery("SELECT * FROM ".DB_ARTICLE_CATS." where article_cat_id='".intval($_GET['cat_id'])."' ORDER BY article_cat_name");
	if (dbrows($result) != 0) {
		$cdata = dbarray($result);
		$info['articles']['child_categories'] = array();
		// get child category
		$child_result = dbquery("SELECT
		ac.article_cat_id, ac.article_cat_name, ac.article_cat_description, count(a.article_id) 'article_count'
		FROM ".DB_ARTICLE_CATS." ac
		LEFT JOIN ".DB_ARTICLES." a on a.article_cat=ac.article_cat_id AND a.article_draft ='0' AND ".groupaccess("a.article_visibility")."
		".(multilang_table("AR") ? "and a.article_language='".LANGUAGE."'" : "")."
		".(multilang_table("AR") ? "WHERE ac.article_cat_language='".LANGUAGE."' AND" : "WHERE")."
		ac.article_cat_parent = '".intval($cdata['article_cat_id'])."'
		GROUP BY ac.article_cat_id
		ORDER BY ac.article_cat_name
		");
		if (dbrows($child_result) > 0) {
			while ($childData = dbarray($child_result)) {
				$info['articles']['child_categories'][$childData['article_cat_id']] = $childData;
			}
		}
		set_title($cdata['article_cat_name'].$locale['global_200'].$locale['400']);
		add_breadcrumb(array(
						   'link' => INFUSIONS.'articles/articles.php?cat_id='.$_GET['cat_id'],
						   'title' => $cdata['article_cat_name']
					   ));
		$info['articles']['category'] = $cdata;
		// xss
		$info['articles_max_rows'] = dbcount("(article_id)", DB_ARTICLES, "article_cat='".$_GET['cat_id']."' AND article_draft='0'");
		$_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['articles_max_rows']) ? $_GET['rowstart'] : 0;
		if ($info['articles_max_rows'] > 0) {
			$a_result = dbquery("SELECT article_id, article_subject, article_snippet, article_article, article_datestamp FROM ".DB_ARTICLES."
						WHERE article_cat='".$_GET['cat_id']."' AND article_draft='0' AND ".groupaccess('article_visibility')." ORDER BY ".$cdata['article_cat_sorting']."
						LIMIT ".$_GET['rowstart'].", ".$article_settings['article_pagination']);
			$info['articles_rows'] = dbrows($a_result);
			while ($data = dbarray($a_result)) {
				$data['article_snippet'] = parse_textarea($data['article_snippet']);
				$data['article_article'] = preg_split("/<!?--\s*pagebreak\s*-->/i", parse_textarea($data['article_article']));
				$data['new'] = ($data['article_datestamp']+604800 > time()+(fusion_get_settings("timeoffset")*3600)) ? $locale['402'] : '';
				$info['articles']['item'][] = $data;
			}
			$info['page_nav'] = ($info['articles_rows'] > fusion_get_settings("article_pagination")) ? makepagenav($_GET['rowstart'], fusion_get_settings("article_pagination"), $info['articles_rows'], 3, FUSION_SELF."?cat_id=".$_GET['cat_id']."&amp;") : '';
		}
	} else {
		redirect(INFUSIONS.'articles/articles.php');
	}
	render_articles_category($info);
}
require_once THEMES."templates/footer.php";