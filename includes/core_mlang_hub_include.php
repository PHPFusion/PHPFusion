<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/core_constants_include.php
| Author: J.Falk (Domi)
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

// Articles
if (preg_match('/articles.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['article_id']) && isnum($_GET['article_id']))) { 
$data = dbarray(dbquery("SELECT ac.article_cat_id,ac.article_cat_language, a.article_id
						FROM ".DB_ARTICLE_CATS." ac
						LEFT JOIN ".DB_ARTICLES." a ON ac.article_cat_id = a.article_cat 
						WHERE a.article_id='".stripinput($_GET['article_id'])."'
						GROUP BY a.article_id"));
	if ($data['article_cat_language']." != ".LANGUAGE) {
		echo set_language($data['article_cat_language']);
	}
}

// Article Cats
if (preg_match('/articles.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) { 
$data = dbarray(dbquery("SELECT article_cat_language FROM ".DB_ARTICLE_CATS." WHERE article_cat_id='".stripinput($_GET['cat_id'])."'"));
	if ($data['article_cat_language']." != ".LANGUAGE) {
		echo set_language($data['article_cat_language']);
	}
}

// Blog
if (preg_match('/blog.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['readmore']) && isnum($_GET['readmore']))) { 
$data = dbarray(dbquery("SELECT blog_language FROM ".DB_BLOG." WHERE blog_id='".stripinput($_GET['readmore'])."'"));
	if ($data['blog_language']." != ".LANGUAGE) {
		echo set_language($data['blog_language']);
	}
}

// Blog Cats
if (preg_match('/blog.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) { 
$data = dbarray(dbquery("SELECT blog_cat_language FROM ".DB_BLOG_CATS." WHERE blog_cat_id='".stripinput($_GET['cat_id'])."'"));
	if ($data['blog_cat_language']." != ".LANGUAGE) {
		echo set_language($data['blog_cat_language']);
	}
}

// Downloads
if (preg_match('/downloads.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['download_id']) && isnum($_GET['download_id']))) { 
$data = dbarray(dbquery("SELECT dlc.download_cat_id,dlc.download_cat_language, dl.download_id
						FROM ".DB_DOWNLOAD_CATS." dlc
						LEFT JOIN ".DB_DOWNLOADS." dl ON dlc.download_cat_id = dl.download_cat 
						WHERE dl.download_id='".stripinput($_GET['download_id'])."'
						GROUP BY dl.download_id"));
	if ($data['download_cat_language']." != ".LANGUAGE) {
		echo set_language($data['download_cat_language']);
	}
}

// Download Cats
if (preg_match('/downloads.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) { 
$data = dbarray(dbquery("SELECT download_cat_language FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_id='".stripinput($_GET['cat_id'])."'"));
	if ($data['download_cat_language']." != ".LANGUAGE) {
		echo set_language($data['download_cat_language']);
	}
}

// News
if (preg_match('/news.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['readmore']) && isnum($_GET['readmore']))) { 
$data = dbarray(dbquery("SELECT news_language FROM ".DB_NEWS." WHERE news_id='".stripinput($_GET['readmore'])."'"));
	if ($data['news_language']." != ".LANGUAGE) {
		echo set_language($data['news_language']);
	}
}

// News Cats
if (preg_match('/news.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) { 
$data = dbarray(dbquery("SELECT news_cat_language FROM ".DB_NEWS_CATS." WHERE news_cat_id='".stripinput($_GET['cat_id'])."'"));
	if ($data['news_cat_language']." != ".LANGUAGE) {
		echo set_language($data['news_cat_language']);
	}
}

// FaQ´s
if (preg_match('/faq.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) { 
$data = dbarray(dbquery("SELECT fqc.faq_cat_id, fqc.faq_cat_language, fq.faq_cat_id
						FROM ".DB_FAQ_CATS." fqc
						LEFT JOIN ".DB_FAQS." fq ON fqc.faq_cat_id = fq.faq_cat_id 
						WHERE fq.faq_cat_id='".stripinput($_GET['cat_id'])."'
						GROUP BY fq.faq_cat_id"));
	if ($data['faq_cat_language']." != ".LANGUAGE) {
		echo set_language($data['faq_cat_language']);
	}
}

// Forum threads
if (preg_match('/viewthread.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['thread_id']) && isnum($_GET['thread_id']))) {
$data = dbarray(dbquery("SELECT f.forum_id,f.forum_language, t.thread_id
						FROM ".DB_FORUMS." f
						LEFT JOIN ".DB_FORUM_THREADS." t ON f.forum_id = t.forum_id 
						WHERE t.thread_id='".stripinput($_GET['thread_id'])."'
						GROUP BY t.thread_id"));
	if ($data['forum_language']." != ".LANGUAGE) {
		echo set_language($data['forum_language']);
	}
}

// Forum topics
if (preg_match('/index.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['viewforum']) && isnum($_GET['forum_id']))) {
$data = dbarray(dbquery("SELECT forum_cat, forum_branch, forum_language FROM ".DB_FORUMS." WHERE forum_id='".stripinput($_GET['forum_id'])."'"));
	if ($data['forum_language']." != ".LANGUAGE) {
		echo set_language($data['forum_language']);
	}
}

// Photo´s
if (preg_match('/photogallery.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['photo_id']) && isnum($_GET['photo_id']))) { 
$data = dbarray(dbquery("SELECT pha.album_id, pha.album_language, ph.album_id
						FROM ".DB_PHOTO_ALBUMS." pha
						LEFT JOIN ".DB_PHOTOS." ph ON pha.album_id = ph.album_id
						WHERE ph.photo_id='".stripinput($_GET['photo_id'])."'
						GROUP BY ph.photo_id"));
	if ($data['album_language']." != ".LANGUAGE) {
		echo set_language($data['album_language']);
	}
}

// Photo Albums
if (preg_match('/photogallery.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['album_id']) && isnum($_GET['album_id']))) { 
$data = dbarray(dbquery("SELECT album_language FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".stripinput($_GET['album_id'])."'"));
	if ($data['album_language']." != ".LANGUAGE) {
		echo set_language($data['album_language']);
	}
}

// Weblinks
if (preg_match('/weblinks.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) { 
$data = dbarray(dbquery("SELECT weblink_cat_language FROM ".DB_WEBLINK_CATS." WHERE weblink_cat_id='".stripinput($_GET['cat_id'])."'"));
	if ($data['weblink_cat_language']." != ".LANGUAGE) {
		echo set_language($data['weblink_cat_language']);
	}
}
?>