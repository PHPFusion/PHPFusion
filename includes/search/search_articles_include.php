<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_articles_include.php
| Author: Robert Gaudyn (Wooya)
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

include LOCALE.LOCALESET."search/articles.php";

if ($_GET['stype'] == "articles" || $_GET['stype']=="all") {
	if ($_GET['sort'] == "datestamp") {
		$sortby = "article_datestamp";
	} else if ($_GET['sort'] == "subject") {
		$sortby = "article_subject";
	} else if ($_GET['sort'] == "author") {
		$sortby = "article_name";
	}
	$ssubject = search_querylike("article_subject");
	$smessage = search_querylike("article_article");
	$ssnippet = search_querylike("article_snippet");
	if ($_GET['fields'] == 0) {
		$fieldsvar = search_fieldsvar($ssubject);
	} else if ($_GET['fields'] == 1) {
		$fieldsvar = search_fieldsvar($smessage, $ssnippet);
	} else if ($_GET['fields'] == 2) {
		$fieldsvar = search_fieldsvar($ssubject, $ssnippet, $smessage);
	} else {
		$fieldsvar = "";
	}
	if ($fieldsvar) {
		$result = dbquery(
			"SELECT ta.*,tac.* FROM ".DB_ARTICLES." ta
			INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
			WHERE ".groupaccess('article_cat_access')." AND ".$fieldsvar."
			".($_GET['datelimit'] != 0 ? " AND article_datestamp>=".(time() - $_GET['datelimit']):"")
		);	 
		$rows = dbrows($result);
	} else {
		$rows = 0;
	}
	if ($rows != 0) {
		$items_count .= THEME_BULLET."&nbsp;<a href='".FUSION_SELF."?stype=articles&amp;stext=".$_GET['stext']."&amp;".$composevars."'>".$rows." ".($rows == 1 ? $locale['a401'] : $locale['a402'])." ".$locale['522']."</a><br />\n";
		$result = dbquery(
			"SELECT ta.*,tac.*, tu.user_id, tu.user_name, tu.user_status FROM ".DB_ARTICLES." ta
			INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
			LEFT JOIN ".DB_USERS." tu ON ta.article_name=tu.user_id
			WHERE ".groupaccess('article_cat_access')." AND ".$fieldsvar."
			".($_GET['datelimit'] != 0 ? " AND article_datestamp>=".(time() - $_GET['datelimit']):"")."
			ORDER BY ".$sortby." ".($_GET['order'] != 1 ? "ASC":"DESC").($_GET['stype'] != "all" ? " LIMIT ".$_GET['rowstart'].",10" : "")
		);
		while ($data = dbarray($result)) {
			$search_result = "";
			$text_all = search_striphtmlbbcodes($data['article_snippet']." ".$data['article_article']);
			$text_frag = search_textfrag($text_all);
			$subj_c = search_stringscount($data['article_subject']);
			$text_c = search_stringscount($data['article_snippet']." ".$data['article_article']);
			// $text_frag = highlight_words($swords, $text_frag);
			
			$search_result .= "<a href='readarticle.php?article_id=".$data['article_id']."'>".$data['article_subject']."</a>"."<br /><br />\n";
			// $search_result .= "<a href='readarticle.php?article_id=".$data['article_id']."'>".highlight_words($swords, $data['article_subject'])."</a>"."<br /><br />\n";
			$search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
			$search_result .= "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."\n";
			$search_result .= $locale['global_071'].showdate("longdate", $data['article_datestamp'])."</span><br />\n";
			$search_result .= "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['522']." ".$locale['a404'].", ";
			$search_result .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['522']." ".$locale['a405']."</span><br /><br />\n";
			search_globalarray($search_result);
		}
	} else {
		$items_count .= THEME_BULLET."&nbsp;0 ".$locale['a402']." ".$locale['522']."<br />\n";
	}
	$navigation_result = search_navigation($rows);
}
?>