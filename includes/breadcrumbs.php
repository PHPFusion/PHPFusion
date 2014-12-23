<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: breadcrumbs.php
| Author: JoiNNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

// This file is under development and it's content
// is subjected to being moved into a function

if (!defined('IN_FUSION')) { die('Access Denied'); }

function generate_breadcrumbs($data) {
	global $breadcrumbs, $locale;

	// Should we also show the Home link ?
	//if ($show_home) {
	//	$breadcrumbs[] = array('link' => BASEDIR.'index.php', 'title' => 'Home'); // Home needs localised
	//}

	// articles.php
	if (defined('ARTICLES')) {
		// Articles page
		add_to_breadcrumbs(array('link' => BASEDIR.'articles.php', 'title' => $locale['400']));
		// Category
		$cat_id = isset($_GET['cat_id']) ? $_GET['cat_id'] : $data['article_cat'];
		add_to_breadcrumbs(array('link' => BASEDIR.'articles.php?cat_id='.$cat_id, 'title' => $data['article_cat_name']));
		// Article
		if (isset($_GET['article_id'])) {
			add_to_breadcrumbs(array('link' => BASEDIR.'articles.php?article_id='.$_GET['article_id'], 'title' => $data['article_subject']));
		}
	} elseif (defined('NEWS_CAT') or (defined('NEWS'))) {
		// news_cat.php
		if (!$data['news_cat_id']) {
			$data['news_cat_id'] = 0;
			$data['news_cat_name'] = $locale['global_080'];
		}
		add_to_breadcrumbs(array('link' => BASEDIR.'news.php', 'title' => 'News')); // News needs to be localised
		add_to_breadcrumbs(array('link' => BASEDIR.'news_cats.php?cat_id='.$data['news_cat_id'], 'title' => $data['news_cat_name']));
		// news.php
		if (defined('NEWS') && isset($_GET['readmore'])) {
			add_to_breadcrumbs(array('link' => BASEDIR.'news.php?readmore='.$_GET['readmore'], 'title' => $data['news_subject']));
		}
	}
	// No link for last item ?
	//if ($last_no_link) {
	//	$last_link = array_keys($breadcrumbs);
	//	$last_link = array_pop($last_link);
	//	$breadcrumbs[$last_link]['link'] = '';
	//}
	//var_dump($breadcrumbs);
}

function catFullPath($cat_id, $cat_tbl, $col_id, $col_parent, $col_title) {
	$tmp_id = $cat_id;
	while ($tmp_id > 0) {
		$result = dbquery("SELECT ".$col_id.", ".$col_parent.", ".$col_title." FROM ".$cat_tbl." WHERE ".$col_id."='".$tmp_id."'");
		$tmp_id = 0;
		if (dbrows($result)) {
			$data = dbarray($result);
			$cat_item = array('id' => $data[$col_id], 'parent' => $data[$col_parent], 'title' => $data[$col_title]);
			$cat_list[] = $cat_item;
			$tmp_id = $data[$col_parent];
		} else {
			return false;
		}
	}
	return array_reverse($cat_list);
}

?>