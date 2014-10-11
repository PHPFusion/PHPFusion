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

// articles.php
if (defined('ARTICLES')) {
	// Articles page
	add_to_breadcrumbs(array(BASEDIR.'articles.php' => $locale['400']));
	// Category
	$cat_id = isset($_GET['cat_id']) ? $_GET['cat_id'] : $data['article_cat'];
	add_to_breadcrumbs(array(BASEDIR.'articles.php?cat_id='.$cat_id => $data['article_cat_name']));
	// Article
	if (isset($_GET['article_id'])) {
		add_to_breadcrumbs(array(BASEDIR.'articles.php?article_id='.$_GET['article_id'] => $data['article_subject']));
	}
}

// news_cat.php
if (defined('NEWS_CAT') or (defined('NEWS') && isset($_GET['readmore']))) {
	add_to_breadcrumbs(array(BASEDIR.'news.php' => 'News')); // News need to be localised
	add_to_breadcrumbs(array(BASEDIR.'news_cats.php?cat_id='.$data['news_cat_id'] => $data['news_cat_name']));
}

// news.php
if (defined('NEWS') && isset($_GET['readmore'])) {
	add_to_breadcrumbs(array(BASEDIR.'news.php?readmore='.$_GET['readmore'] => $data['news_subject']));
}