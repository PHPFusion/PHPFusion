<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: index.php
| Author: Frederick MC Chan (Hien)
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
if (!iADMIN || $userdata['user_rights'] == "" || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}

require_once THEMES."templates/admin_header.php";
if (!isset($_GET['pagenum']) || !isnum($_GET['pagenum'])) $_GET['pagenum'] = 1;
$admin_images = TRUE;

// Work out which tab is the active default (terminate if no tab available)
$default = FALSE;
for ($i = 5; $i > 0; $i--) {
	if ($pages[$i]) {
		$default = $i;
	}
}

if (!$default) {
	die("Denied"); exit;
}

// Dashboard vars
$pages['0'] = 'AcpHome';
if (!$pages[$_GET['pagenum']]) {
	die("Denied"); exit;
}

// Members stats
$members_registered = dbcount("(user_id)", DB_USERS, "user_status<='1' OR user_status='3' OR user_status='5'");
$members_unactivated = dbcount("(user_id)", DB_USERS, "user_status='2'");
$members_security_ban = dbcount("(user_id)", DB_USERS, "user_status='4'");
$members_canceled = dbcount("(user_id)", DB_USERS, "user_status='5'");
$members['registered'] = dbcount("(user_id)", DB_USERS, "user_status<='1' OR user_status='3' OR user_status='5'");
$members['unactivated'] = dbcount("(user_id)", DB_USERS, "user_status='2'");
$members['security_ban'] = dbcount("(user_id)", DB_USERS, "user_status='4'");
$members['cancelled'] = dbcount("(user_id)", DB_USERS, "user_status='5'");
if ($settings['enable_deactivation'] == "1") {
	$time_overdue = time()-(86400*$settings['deactivation_period']);
	$members['inactive'] = dbcount("(user_id)", DB_USERS, "user_lastvisit<'$time_overdue' AND user_actiontime='0' AND user_joined<'$time_overdue' AND user_status='0'");
}

// Get Core Infusion´s stats
if (db_exists(DB_FORUMS)) {
	$forum['count'] = dbcount("('forum_id')", DB_FORUMS);
	$forum['thread'] = dbcount("('post_id')", DB_FORUM_THREADS);
	$forum['post'] = dbcount("('post_id')", DB_FORUM_POSTS);
	$forum['users'] = dbcount("('user_id')", DB_USERS, "user_posts > '0'");
}

if (db_exists(DB_DOWNLOADS)) {
	$download['download'] = dbcount("('download_id')", DB_DOWNLOADS);
	$download['comment'] = dbcount("('comment_id')", DB_COMMENTS, "comment_type='d'");
	$download['submit'] = dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='d'");
}

if (db_exists(DB_ARTICLES)) {
	$articles['article'] = dbcount("('article_id')", DB_ARTICLES);
	$articles['comment'] = dbcount("('comment_id')", DB_COMMENTS, "comment_type='A'");
	$articles['submit'] = dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='a'");
}

if (db_exists(DB_WEBLINKS)) {
	$weblinks['weblink'] = dbcount("('weblink_id')", DB_WEBLINKS);
	$weblinks['comment'] = dbcount("('comment_id')", DB_COMMENTS, "comment_type='L'");
	$weblinks['submit'] = dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='l'");
}

if (db_exists(DB_NEWS)) {
	$news['news'] = dbcount("('news_id')", DB_NEWS);
	$news['comment'] = dbcount("('comment_id')", DB_COMMENTS, "comment_type='n'");
	$news['submit'] = dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='n'");
}

if (db_exists(DB_BLOG)) {
	$blog['blog'] = dbcount("('blog_id')", DB_BLOG);
	$blog['comment'] = dbcount("('comment_id')", DB_COMMENTS, "comment_type='b'");
	$blog['submit'] = dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='b'");
}

if (db_exists(DB_PHOTOS)) {
	$photos['photo'] = dbcount("('photo_id')", DB_PHOTOS);
	$photos['comment'] = dbcount("('comment_id')", DB_COMMENTS, "comment_type='P'");
	$photos['submit'] = dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='p'");
}

$comments_type = array(
	'N' => $locale['269'],
	'D' => $locale['268'],
	'P' => $locale['272'],
	'A' => $locale['270'],
	'B' => $locale['269b'],
	'C' => $locale['272a'],
	'PH' => $locale['261'],
);

$submit_type = array(
	'n' => $locale['269'],
	'd' => $locale['268'],
	'p' => $locale['272'],
	'a' => $locale['270'],
	'l' => $locale['271'],
	'b' => $locale['269b'],
);

$link_type = array(
	'N' => $settings['siteurl']."infusions/news/news.php?readmore=%s",
	'D' => $settings['siteurl']."infusions/downloads/downloads.php?download_id=%s",
	'P' => $settings['siteurl']."infusions/gallery/gallery.php?photo_id=%s",
	'A' => $settings['siteurl']."infusions/articles/articles.php?article_id=%s",
	'B' => $settings['siteurl']."infusions/blog/blog.php?readmore=%s",
	'C' => $settings['siteurl']."viewpage.php?page_id=%s",
	'PH' => $settings['siteurl']."infusions/gallery/gallery.php?photo_id=%s",
);

// Infusions count
$infusions_count = dbcount("(inf_id)", DB_INFUSIONS);

// Latest Comments
$global_comments['rows'] = dbcount("('comment_id')", DB_COMMENTS);
$_GET['c_rowstart'] = isset($_GET['c_rowstart']) && $_GET['c_rowstart'] <= $global_comments['rows'] ? $_GET['c_rowstart'] : 0;
$comments_result = dbquery("SELECT c.*, u.user_id, u.user_name, u.user_status, u.user_avatar
							FROM ".DB_COMMENTS." c LEFT JOIN ".DB_USERS." u on u.user_id=c.comment_name
							ORDER BY comment_datestamp DESC LIMIT ".$_GET['c_rowstart'].", ".$settings['comments_per_page']."
							");
if ($global_comments['rows'] > $settings['comments_per_page']) {
	$global_comments['nav'] = makepagenav($_GET['c_rowstart'], $settings['comments_per_page'], $global_comments['rows'], 2);
}

$global_comments['data'] = array();
if (dbrows($comments_result)) {
	while ($_comdata = dbarray($comments_result)) {
		$global_comments['data'][] = $_comdata;
	}
} else {
	$global_comments['nodata'] = $locale['254c'];
}

// Latest Ratings
$global_ratings['rows'] = dbcount("('rating_id')", DB_RATINGS);
$_GET['r_rowstart'] = isset($_GET['r_rowstart']) && $_GET['r_rowstart'] <= $global_ratings['rows'] ? $_GET['r_rowstart'] : 0;
$result = dbquery("SELECT r.*, u.user_id, u.user_name, u.user_status, u.user_avatar
					FROM ".DB_RATINGS." r LEFT JOIN ".DB_USERS." u on u.user_id=r.rating_user
					ORDER BY rating_datestamp DESC LIMIT ".$_GET['r_rowstart'].", ".$settings['comments_per_page']."
					");
$global_ratings['data'] = array();
if (dbrows($result) > 0) {
	while ($_ratdata = dbarray($result)) {
		$global_ratings['data'][] = $_ratdata;
	}
} else {
	$global_ratings['nodata'] = $locale['254b'];
}
if ($global_ratings['rows'] > $settings['comments_per_page']) {
	$global_ratings['ratings_nav'] = makepagenav($_GET['r_rowstart'], $settings['comments_per_page'], $global_ratings['rows'], 2);
}

// Latest Submissions
$global_submissions['rows'] = dbcount("('submit_id')", DB_SUBMISSIONS);
$_GET['s_rowstart'] = isset($_GET['s_rowstart']) && $_GET['s_rowstart'] <= $global_submissions['rows'] ? $_GET['s_rowstart'] : 0;
$result = dbquery("SELECT s.*, u.user_id, u.user_name, u.user_status, u.user_avatar
				FROM ".DB_SUBMISSIONS." s LEFT JOIN ".DB_USERS." u on u.user_id=s.submit_user
				ORDER BY submit_datestamp DESC LIMIT ".$_GET['s_rowstart'].", ".$settings['comments_per_page']."
				");
$global_submissions['data'] = array();
if (dbrows($result) > 0 && checkrights('SU')) {
	while ($_subdata = dbarray($result)) {
		$global_submissions['data'][] = $_subdata;
	}
} else {
	$global_submissions['nodata'] = $locale['254a'];
}
if ($global_submissions['rows'] > $settings['comments_per_page']) {
	$global_submissions['submissions_nav'] = "<span class='pull-right text-smaller'>".makepagenav($_GET['s_rowstart'], $settings['comments_per_page'], $global_submissions['rows'], 2)."</span>\n";
}

// Icon Grid
if (isset($_GET['pagenum']) && isnum($_GET['pagenum'])) {
	$result = dbquery("SELECT * FROM ".DB_ADMIN." WHERE admin_page='".$_GET['pagenum']."' ORDER BY admin_title");
	$admin_icons['rows'] = dbrows($result);
	$admin_icons['data'] = array();
	if (dbrows($result)) {
		while ($_idata = dbarray($result)) {
			if (checkrights($_idata['admin_rights']) && $_idata['admin_link'] != "reserved") {
				$admin_icons['data'][] = $_idata;
			}
		}
	}
}

render_admin_dashboard();
require_once THEMES."templates/footer.php";
