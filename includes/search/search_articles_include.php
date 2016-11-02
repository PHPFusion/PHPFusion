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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
if (db_exists(DB_ARTICLES)) {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."search/articles.php");
    if ($_GET['stype'] == "articles" || $_GET['stype'] == "all") {
	$sort_by = array(
		'datestamp' => "article_datestamp",
		'subject' => "article_subject",
		'author' => "article_name",
		);
	$order_by = array(
		'0' => ' DESC',
		'1' => ' ASC',
		);
	$sortby = !empty($_POST['sort']) ? "ORDER BY ".$sort_by[$_POST['sort']].$order_by[$_POST['order']] : "";

        if ($_POST['fields'] == 0) {
			$ssubject = search_querylike_safe("article_subject", $swords_keys_for_query, $c_swords, $fields_count, 0);
            $fieldsvar = search_fieldsvar($ssubject);

        } elseif ($_POST['fields'] == 1) {
			$smessage = search_querylike_safe("article_article", $swords_keys_for_query, $c_swords, $fields_count, 0);
			$ssnippet = search_querylike_safe("article_snippet", $swords_keys_for_query, $c_swords, $fields_count, 1);
			$fieldsvar = search_fieldsvar($smessage, $ssnippet);

        } elseif ($_POST['fields'] == 2) {
        	$ssubject = search_querylike_safe("article_subject", $swords_keys_for_query, $c_swords, $fields_count, 0);
        	$smessage = search_querylike_safe("article_article", $swords_keys_for_query, $c_swords, $fields_count, 1);
			$ssnippet = search_querylike_safe("article_snippet", $swords_keys_for_query, $c_swords, $fields_count, 2);
			$fieldsvar = search_fieldsvar($ssubject, $ssnippet, $smessage);

        } else{
			$fieldsvar = "";

        }

        if ($fieldsvar) {
            $datestamp = (time() - $_POST['datelimit']);
            $result = dbquery("SELECT ta.article_subject, ta.article_snippet, ta.article_article, ta.article_keywords, ta.article_breaks,
				ta.article_datestamp, ta.article_reads, ta.article_allow_comments, ta.article_allow_ratings,
				tac.article_cat_id, tac.article_cat_name
            	FROM ".DB_ARTICLES." ta
				INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
				".(multilang_table("AR") ? "WHERE tac.article_cat_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('article_visibility')." AND ".$fieldsvar."
				".($_POST['datelimit'] != 0 ? " AND article_datestamp>=".$datestamp : ""), $swords_for_query);
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }
        if ($rows != 0) {
            $items_count .= THEME_BULLET."&nbsp;<a href='".FUSION_SELF."?stype=articles&amp;stext=".$_POST['stext']."&amp;".$composevars."'>".$rows." ".($rows == 1 ? $locale['a401'] : $locale['a402'])." ".$locale['522']."</a><br />\n";
            $datestamp = (time() - $_POST['datelimit']);
            $result = dbquery("SELECT ta.article_id, ta.article_subject, ta.article_snippet, ta.article_article, ta.article_keywords, ta.article_breaks,
				ta.article_datestamp, ta.article_reads, ta.article_allow_comments, ta.article_allow_ratings,
				tac.article_cat_id, tac.article_cat_name,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_joined, tu.user_level
            	FROM ".DB_ARTICLES." ta
				INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
				LEFT JOIN ".DB_USERS." tu ON ta.article_name=tu.user_id
				".(multilang_table("AR") ? "WHERE tac.article_cat_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('article_visibility')." AND ".$fieldsvar."
				".($_POST['datelimit'] != 0 ? " AND article_datestamp>=".$datestamp : "")."
				".$sortby.($_GET['stype'] != "all" ? " LIMIT ".$_POST['rowstart'].",10" : ""), $swords_for_query
				);
            while ($data = dbarray($result)) {
                $search_result = "";
                $text_all = search_striphtmlbbcodes($data['article_snippet']." ".$data['article_article']);
                $text_frag = search_textfrag($text_all);
                $subj_c = search_stringscount($data['article_subject']);
                $text_c = search_stringscount($data['article_snippet']." ".$data['article_article']);
                $search_result .= "<a href='infusions/articles/articles.php?article_id=".$data['article_id']."'>".$data['article_subject']."</a>"."<br /><br />\n";
                $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
                $search_result .= "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'],
                                                                                             $data['user_status'])."\n";
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
}
