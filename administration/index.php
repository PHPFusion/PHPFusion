<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: index.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';
if (!iADMIN || $userdata['user_rights'] == "" || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}

require_once THEMES."templates/admin_header.php";

if (!isset($_GET['pagenum']) || !isnum($_GET['pagenum'])) {
    $_GET['pagenum'] = 0;
}

$admin_images = TRUE;

$members_count = dbarray(dbquery("SELECT
    (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status<='1' OR user_status='3' OR user_status='5') AS members_registered,
    (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status='2') AS members_unactivated,
    (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status='4') AS members_security_ban,
    (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status='5') AS members_canceled
"));

// Members stats
$members['registered'] = $members_count['members_registered'];
$members['unactivated'] = $members_count['members_unactivated'];
$members['security_ban'] = $members_count['members_security_ban'];
$members['cancelled'] = $members_count['members_canceled'];

if ($settings['enable_deactivation'] == "1") {
    $time_overdue = time() - (86400 * $settings['deactivation_period']);
    $members['inactive'] = dbcount("(user_id)", DB_USERS, "user_lastvisit<'$time_overdue' AND user_actiontime='0' AND user_joined<'$time_overdue' AND user_status='0'");
}

// Get Core Infusion's stats
    $forum = [];
    $f_count = dbarray(dbquery("SELECT
        (SELECT COUNT(forum_id) FROM ".DB_PREFIX."forums) AS forums,
        (SELECT COUNT(thread_id) FROM ".DB_PREFIX."threads) AS threads,
        (SELECT COUNT(post_id) FROM ".DB_PREFIX."posts) AS posts,
        (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_posts > '0') AS user_posts
    "));
    $forum['count'] = $f_count['forums'];
    $forum['thread'] = $f_count['threads'];
    $forum['post'] = $f_count['posts'];
    $forum['users'] = $f_count['user_posts'];

    $download = [];
    $d_count = dbarray(dbquery("SELECT
        (SELECT COUNT(download_id) FROM ".DB_PREFIX."downloads) AS items,
        (SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='D') AS comments,
        (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='d') AS submissions
    "));
    $download['download'] = $d_count['items'];
    $download['comment'] = $d_count['comments'];
    $download['submit'] = $d_count['submissions'];

    $articles = [];
    $a_count = dbarray(dbquery("SELECT
        (SELECT COUNT(article_id) FROM ".DB_PREFIX."articles) AS items,
        (SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='A') AS comments,
        (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='a') AS submissions
    "));
    $articles['article'] = $a_count['items'];
    $articles['comment'] = $a_count['comments'];
    $articles['submit'] = $a_count['submissions'];

    $weblinks = [];
    $w_count = dbarray(dbquery("SELECT
        (SELECT COUNT(weblink_id) FROM ".DB_PREFIX."weblinks) AS items,
        (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='l') AS submissions
    "));
    $weblinks['weblink'] = $w_count['items'];
    $weblinks['submit'] = $w_count['submissions'];

    $news = [];
    $n_count = dbarray(dbquery("SELECT
        (SELECT COUNT(news_id) FROM ".DB_PREFIX."news) AS items,
        (SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='N') AS comments,
        (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='n') AS submissions
    "));
    $news['news'] = $n_count['items'];
    $news['comment'] = $n_count['comments'];
    $news['submit'] = $n_count['submissions'];

	$blog = [];
	$b_count = dbarray(dbquery("SELECT
		(SELECT COUNT(blog_id) FROM ".DB_PREFIX."blog) AS items,
		(SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='B') AS comments,
		(SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='b') AS submissions
	"));
	$blog['blog'] = $b_count['items'];
	$blog['comment'] = $b_count['comments'];
	$blog['submit'] = $b_count['submissions'];
	
    $photos = [];
    $p_count = dbarray(dbquery("SELECT
        (SELECT COUNT(photo_id) FROM ".DB_PREFIX."photos) AS items,
        (SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='P') AS comments,
        (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='p') AS submissions
    "));
    $photos['photo'] = $p_count['items'];
    $photos['comment'] = $p_count['comments'];
    $photos['submit'] = $p_count['submissions'];

	$comments_type = [
    'C'  => $locale['272a'],
    'UP' => $locale['UP']
	];
$comments_type += \PHPFusion\Admins::getInstance()->getCommentType();

$submit_type = [];
$submit_type += \PHPFusion\Admins::getInstance()->getSubmitType();

$submit_link = [];
$submit_link += \PHPFusion\Admins::getInstance()->getSubmitLink();

$submit_data = [];
$submit_data += \PHPFusion\Admins::getInstance()->getSubmitData();

$link_type = [
    'C'  => $settings['siteurl']."viewpage.php?page_id=%s",
    'UP' => $settings['siteurl']."profile.php?lookup=%s"
];
$link_type += \PHPFusion\Admins::getInstance()->getLinkType();

// Infusions count
$infusions_count = dbcount("(inf_id)", DB_INFUSIONS);
$global_infusions = [];
if ($infusions_count > 0) {
    $inf_result = dbquery("SELECT *
        FROM ".DB_INFUSIONS."
        ORDER BY inf_id ASC
    ");
    while ($_inf = dbarray($inf_result)) {
        $global_infusions[$_inf['inf_id']] = $_inf;
    }
}

// Latest Comments
$global_comments = [];
$global_comments['rows'] = dbcount("('comment_id')", DB_COMMENTS);
$_GET['c_rowstart'] = isset($_GET['c_rowstart']) && $_GET['c_rowstart'] <= $global_comments['rows'] ? $_GET['c_rowstart'] : 0;
$comments_result = dbquery("SELECT c.*, u.user_id, u.user_name, u.user_status, u.user_avatar
    FROM ".DB_COMMENTS." c
    LEFT JOIN ".DB_USERS." u on u.user_id=c.comment_name
    ORDER BY comment_datestamp DESC LIMIT ".$_GET['c_rowstart'].", 5
");

if ($global_comments['rows'] > 10) {
    $global_comments['comments_nav'] = makepagenav($_GET['c_rowstart'], 10, $global_comments['rows'], 2, FUSION_SELF.$aidlink.'&amp;pagenum=0&amp;', 'c_rowstart');
}

$global_comments['data'] = [];

if (dbrows($comments_result)) {
    while ($_comdata = dbarray($comments_result)) {
        $global_comments['data'][] = $_comdata;
    }
} else {
    $global_comments['nodata'] = $locale['254c'];
}

// Latest Ratings
$global_ratings = [];
$global_ratings['rows'] = dbcount("('rating_id')", DB_RATINGS);
$_GET['r_rowstart'] = isset($_GET['r_rowstart']) && $_GET['r_rowstart'] <= $global_ratings['rows'] ? $_GET['r_rowstart'] : 0;
$result = dbquery("SELECT r.*, u.user_id, u.user_name, u.user_status, u.user_avatar
    FROM ".DB_RATINGS." r
    LEFT JOIN ".DB_USERS." u on u.user_id=r.rating_user
    ORDER BY rating_datestamp DESC LIMIT ".$_GET['r_rowstart'].", 5
");

$global_ratings['data'] = [];
if (dbrows($result) > 0) {
    while ($_ratdata = dbarray($result)) {
        $global_ratings['data'][] = $_ratdata;
    }
} else {
    $global_ratings['nodata'] = $locale['254b'];
}

if ($global_ratings['rows'] > 10) {
    $global_ratings['ratings_nav'] = makepagenav($_GET['r_rowstart'], 10, $global_comments['rows'], 2, FUSION_SELF.$aidlink.'&amp;pagenum=0&amp;', 'r_rowstart');
}

// Latest Submissions
$global_submissions = [];
$global_submissions['rows'] = dbcount("('submit_id')", DB_SUBMISSIONS);
$_GET['s_rowstart'] = isset($_GET['s_rowstart']) && $_GET['s_rowstart'] <= $global_submissions['rows'] ? $_GET['s_rowstart'] : 0;
$result = dbquery("SELECT s.*, u.user_id, u.user_name, u.user_status, u.user_avatar
    FROM ".DB_SUBMISSIONS." s
    LEFT JOIN ".DB_USERS." u on u.user_id=s.submit_user
    ORDER BY submit_datestamp DESC LIMIT ".$_GET['s_rowstart'].", 5
");

$global_submissions['data'] = [];

if (dbrows($result) > 0 && checkrights('SU')) {
    while ($_subdata = dbarray($result)) {
        $global_submissions['data'][] = $_subdata;
    }
} else {
    $global_submissions['nodata'] = $locale['254a'];
}

if ($global_submissions['rows'] > 10) {
    $global_submissions['submissions_nav'] = makepagenav($_GET['s_rowstart'], 10, $global_submissions['rows'], 2, FUSION_SELF.$aidlink.'&amp;pagenum=0&amp;', 's_rowstart');
}

// Icon Grid
if (isset($_GET['pagenum']) && isnum($_GET['pagenum'])) {

	$result = dbquery("SELECT * FROM ".DB_ADMIN." WHERE admin_page='".$_GET['pagenum']."' ORDER BY admin_title");
    $admin_icons['rows'] = dbrows($result);
    $admin_icons['data'] = [];
    if (dbrows($result)) {
        while ($_idata = dbarray($result)) {
            if (checkrights($_idata['admin_rights']) && $_idata['admin_link'] != "reserved") {
                // Current locale file have the admin title definitions paired by admin_rights.
                if ($_idata['admin_page'] !== 5) {
                    $_idata['admin_title'] = isset($locale[$_idata['admin_rights']]) ? $locale[$_idata['admin_rights']] : $_idata['admin_title'];
                }
                $admin_icons['data'][] = $_idata;
            }
        }
    }
}

render_admin_dashboard();
require_once THEMES."templates/footer.php";
