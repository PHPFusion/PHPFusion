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
if (!iADMIN || fusion_get_userdata('user_rights') == "" || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
    redirect("../index.php");
}
require_once THEMES.'templates/admin_header.php';
$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/main.php');
if (!isset($_GET['pagenum']) || !isnum($_GET['pagenum'])) {
    $_GET['pagenum'] = 1;
}

$settings = fusion_get_settings();

$admin_images = TRUE;

if (defined('ARTICLES_EXIST')) {
    $article_query = "(SELECT COUNT(article_id) FROM ".DB_PREFIX."articles) AS article_items,
        (SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='A') AS article_comments,
        (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='a') AS article_submissions";
}
if (defined('BLOG_EXIST')) {
    $blog_query = "(SELECT COUNT(blog_id) FROM ".DB_PREFIX."blog) AS blog_items,
        (SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='B') AS blog_comments,
        (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='b') AS blog_submissions";
}
if (defined('DOWNLOADS_EXIST')) {
    $download_query = "(SELECT COUNT(download_id) FROM ".DB_PREFIX."downloads) AS download_items,
        (SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='D') AS download_comments,
        (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='d') AS download_submissions";
}
if (defined('FORUM_EXIST')) {
    $forum_query = "(SELECT COUNT(forum_id) FROM ".DB_PREFIX."forums) AS forums,
        (SELECT COUNT(thread_id) FROM ".DB_PREFIX."forum_threads) AS threads,
        (SELECT COUNT(post_id) FROM ".DB_PREFIX."forum_posts) AS posts,
        (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_posts > '0') AS user_posts";
}
if (defined('GALLERY_EXIST')) {
    $photo_query = "(SELECT COUNT(photo_id) FROM ".DB_PREFIX."photos) AS photo_items,
        (SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='P') AS photo_comments,
        (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='p') AS photo_submissions";
}
if (defined('NEWS_EXIST')) {
    $news_query = "
        (SELECT COUNT(news_id) FROM ".DB_PREFIX."news) AS news_items,
        (SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='N') AS news_comments,
        (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='n') AS news_submissions";
}
if (defined('WEBLINKS_EXIST')) {
    $weblink_query = "(SELECT COUNT(weblink_id) FROM ".DB_PREFIX."weblinks) AS weblink_items,
        (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='l') AS weblink_submissions";
}

if ($settings['enable_deactivation'] == 1) {
    $m_inactive = "(SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status=8) AS members_inactive";
}

$queries = dbarray(dbquery("SELECT
    ".(!empty($article_query) ? $article_query.',' : '')."
    ".(!empty($blog_query) ? $blog_query.',' : '')."
    ".(!empty($download_query) ? $download_query.',' : '')."
    ".(!empty($forum_query) ? $forum_query.',' : '')."
    ".(!empty($photo_query) ? $photo_query.',' : '')."
    ".(!empty($news_query) ? $news_query.',' : '')."
    ".(!empty($weblink_query) ? $weblink_query.',' : '')."
    ".(!empty($m_inactive) ? $m_inactive.',' : '')."
    (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status<=1 OR user_status=3 OR user_status=5) AS members_registered,
    (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status=2) AS members_unactivated,
    (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status=4) AS members_security_ban,
    (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status=5) AS members_canceled
"));

// Members stats
$members['registered'] = $queries['members_registered'];
$members['unactivated'] = $queries['members_unactivated'];
$members['security_ban'] = $queries['members_security_ban'];
$members['cancelled'] = $queries['members_canceled'];
$members['inactive'] = $settings['enable_deactivation'] == 1 ? $queries['members_inactive'] : 0;

// Get Core Infusion's stats
if (defined('ARTICLES_EXIST')) {
    $articles = [];
    $articles['article'] = $queries['article_items'];
    $articles['comment'] = $queries['article_comments'];
    $articles['submit'] = $queries['article_submissions'];
}
if (defined('BLOG_EXIST')) {
    $blog = [];
    $blog['blog'] = $queries['blog_items'];
    $blog['comment'] = $queries['blog_comments'];
    $blog['submit'] = $queries['blog_submissions'];
}
if (defined('DOWNLOADS_EXIST')) {
    $download = [];
    $download['download'] = $queries['download_items'];
    $download['comment'] = $queries['download_comments'];
    $download['submit'] = $queries['download_submissions'];
}
if (defined('FORUM_EXIST')) {
    $forum = [];
    $forum['count'] = $queries['forums'];
    $forum['thread'] = $queries['threads'];
    $forum['post'] = $queries['posts'];
    $forum['users'] = $queries['user_posts'];
}
if (defined('GALLERY_EXIST')) {
    $photos = [];
    $photos['photo'] = $queries['photo_items'];
    $photos['comment'] = $queries['photo_comments'];
    $photos['submit'] = $queries['photo_submissions'];
}
if (defined('NEWS_EXIST')) {
    $news = [];
    $news['news'] = $queries['news_items'];
    $news['comment'] = $queries['news_comments'];
    $news['submit'] = $queries['news_submissions'];
}
if (defined('WEBLINKS_EXIST')) {
    $weblinks = [];
    $weblinks['weblink'] = $queries['weblink_items'];
    $weblinks['submit'] = $queries['weblink_submissions'];
}
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
        if (file_exists(INFUSIONS.$_inf['inf_folder'])) {
            $global_infusions[$_inf['inf_id']] = $_inf;
        }
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
    $result = dbquery("SELECT * FROM ".DB_ADMIN." WHERE admin_page=:adminpage AND admin_language=:language ORDER BY admin_page DESC, admin_id ASC, admin_title ASC",
        [':adminpage' => $_GET['pagenum'], ':language' => LANGUAGE]);
    $admin_icons['rows'] = dbrows($result);
    $admin_icons['data'] = [];
    if (dbrows($result)) {
        while ($_idata = dbarray($result)) {
            if (file_exists(ADMIN.$_idata['admin_link']) || file_exists(INFUSIONS.$_idata['admin_link'])) {
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
}

// Update checker
if ($settings['update_checker'] == 1) {
    function get_http_response_code($url) {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }

    $url = 'https://www.php-fusion.co.uk/updates/9.txt';
    if (get_http_response_code($url) == 200) {
        $file = @file_get_contents($url);
        $array = explode("\n", $file);
        $version = $array[0];

        if (version_compare($version, $settings['version'], '>')) {
            addNotice('info', str_replace(['[LINK]', '[/LINK]', '[VERSION]'], ['<a href="'.$array[1].'" target="_blank">', '</a>', $version], $locale['new_update_avalaible']));
        }
    }
}

render_admin_dashboard();
require_once THEMES.'templates/footer.php';
