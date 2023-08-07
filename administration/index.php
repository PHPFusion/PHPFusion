<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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

use PHPFusion\Admins;

require_once __DIR__ . '/../maincore.php';

if (!iADMIN || fusion_get_userdata( 'user_rights' ) == '' || !defined( 'iAUTH' ) || !check_get( 'aid' ) || get( 'aid' ) != iAUTH) {
    redirect( "../index.php" );
}
require_once THEMES . 'templates/admin_header.php';

$locale = fusion_get_locale( '', LOCALE . LOCALESET . 'admin/main.php' );
$settings = fusion_get_settings();
$aidlink = fusion_get_aidlink();

if (defined( 'ARTICLES_EXISTS' )) {
    $article_query = "(SELECT COUNT(article_id) FROM " . DB_PREFIX . "articles) AS article_items,
        (SELECT COUNT(comment_id) FROM " . DB_COMMENTS . " WHERE comment_type='A') AS article_comments,
        (SELECT COUNT(submit_id) FROM " . DB_SUBMISSIONS . " WHERE submit_type='a') AS article_submissions
    ";
}
if (defined( 'BLOG_EXISTS' )) {
    $blog_query = "(SELECT COUNT(blog_id) FROM " . DB_PREFIX . "blog) AS blog_items,
        (SELECT COUNT(comment_id) FROM " . DB_COMMENTS . " WHERE comment_type='B') AS blog_comments,
        (SELECT COUNT(submit_id) FROM " . DB_SUBMISSIONS . " WHERE submit_type='b') AS blog_submissions
    ";
}
if (defined( 'DOWNLOADS_EXISTS' )) {
    $download_query = "(SELECT COUNT(download_id) FROM " . DB_PREFIX . "downloads) AS download_items,
        (SELECT COUNT(comment_id) FROM " . DB_COMMENTS . " WHERE comment_type='D') AS download_comments,
        (SELECT COUNT(submit_id) FROM " . DB_SUBMISSIONS . " WHERE submit_type='d') AS download_submissions
    ";
}
if (defined( 'FORUM_EXISTS' )) {
    $forum_query = "(SELECT COUNT(forum_id) FROM " . DB_PREFIX . "forums) AS forums,
        (SELECT COUNT(thread_id) FROM " . DB_PREFIX . "forum_threads) AS threads,
        (SELECT COUNT(post_id) FROM " . DB_PREFIX . "forum_posts) AS posts,
        (SELECT COUNT(user_id) FROM " . DB_USERS . " WHERE user_posts > '0') AS user_posts
    ";
}
if (defined( 'GALLERY_EXISTS' )) {
    $photo_query = "(SELECT COUNT(photo_id) FROM " . DB_PREFIX . "photos) AS photo_items,
        (SELECT COUNT(comment_id) FROM " . DB_COMMENTS . " WHERE comment_type='P') AS photo_comments,
        (SELECT COUNT(submit_id) FROM " . DB_SUBMISSIONS . " WHERE submit_type='p') AS photo_submissions
    ";
}
if (defined( 'NEWS_EXISTS' )) {
    $news_query = "
        (SELECT COUNT(news_id) FROM " . DB_PREFIX . "news) AS news_items,
        (SELECT COUNT(comment_id) FROM " . DB_COMMENTS . " WHERE comment_type='N') AS news_comments,
        (SELECT COUNT(submit_id) FROM " . DB_SUBMISSIONS . " WHERE submit_type='n') AS news_submissions
    ";
}
if (defined( 'WEBLINKS_EXISTS' )) {
    $weblink_query = "(SELECT COUNT(weblink_id) FROM " . DB_PREFIX . "weblinks) AS weblink_items,
        (SELECT COUNT(submit_id) FROM " . DB_SUBMISSIONS . " WHERE submit_type='l') AS weblink_submissions
    ";
}

if ($settings['enable_deactivation'] == 1) {
    $m_inactive = "(SELECT COUNT(user_id) FROM " . DB_USERS . " WHERE user_status=8) AS members_inactive";
}

$queries = dbarray( dbquery( "SELECT
    " . (!empty( $article_query ) ? $article_query . ',' : '') . "
    " . (!empty( $blog_query ) ? $blog_query . ',' : '') . "
    " . (!empty( $download_query ) ? $download_query . ',' : '') . "
    " . (!empty( $forum_query ) ? $forum_query . ',' : '') . "
    " . (!empty( $photo_query ) ? $photo_query . ',' : '') . "
    " . (!empty( $news_query ) ? $news_query . ',' : '') . "
    " . (!empty( $weblink_query ) ? $weblink_query . ',' : '') . "
    " . (!empty( $m_inactive ) ? $m_inactive . ',' : '') . "
    (SELECT COUNT(user_id) FROM " . DB_USERS . " WHERE user_status<=1 OR user_status=3 OR user_status=5) AS members_registered,
    (SELECT COUNT(user_id) FROM " . DB_USERS . " WHERE user_status=2) AS members_unactivated,
    (SELECT COUNT(user_id) FROM " . DB_USERS . " WHERE user_status=4) AS members_security_ban,
    (SELECT COUNT(user_id) FROM " . DB_USERS . " WHERE user_status=5) AS members_canceled
" ) );

// Members stats
$members = [
    'registered'   => $queries['members_registered'],
    'unactivated'  => $queries['members_unactivated'],
    'security_ban' => $queries['members_security_ban'],
    'cancelled'    => $queries['members_canceled'],
    'inactive'     => $settings['enable_deactivation'] == 1 ? $queries['members_inactive'] : 0
];

// Get Core Infusion's stats
if (defined( 'ARTICLES_EXISTS' )) {
    $articles = [
        'article' => $queries['article_items'],
        'comment' => $queries['article_comments'],
        'submit'  => $queries['article_submissions']
    ];
}
if (defined( 'BLOG_EXISTS' )) {
    $blog = [
        'blog'    => $queries['blog_items'],
        'comment' => $queries['blog_comments'],
        'submit'  => $queries['blog_submissions']
    ];
}
if (defined( 'DOWNLOADS_EXISTS' )) {
    $download = [
        'download' => $queries['download_items'],
        'comment'  => $queries['download_comments'],
        'submit'   => $queries['download_submissions']
    ];
}
if (defined( 'FORUM_EXISTS' )) {
    $forum = [
        'count'  => $queries['forums'],
        'thread' => $queries['threads'],
        'post'   => $queries['posts'],
        'users'  => $queries['user_posts']
    ];
}
if (defined( 'GALLERY_EXISTS' )) {
    $photos = [
        'photo'   => $queries['photo_items'],
        'comment' => $queries['photo_comments'],
        'submit'  => $queries['photo_submissions']
    ];
}
if (defined( 'NEWS_EXISTS' )) {
    $news = [
        'news'    => $queries['news_items'],
        'comment' => $queries['news_comments'],
        'submit'  => $queries['news_submissions']
    ];
}
if (defined( 'WEBLINKS_EXISTS' )) {
    $weblinks = [
        'weblink' => $queries['weblink_items'],
        'submit'  => $queries['weblink_submissions']
    ];
}
$comments_type = [
    'C'  => $locale['272a'],
    'UP' => $locale['UP']
];
$comments_type += Admins::getInstance()->getCommentType();

$submit_type = Admins::getInstance()->getSubmitType();
$submit_link = Admins::getInstance()->getSubmitLink();
$submit_data = Admins::getInstance()->getSubmitData();

$link_type = [
    'C'  => $settings['siteurl'] . "viewpage.php?page_id=%s",
    'UP' => $settings['siteurl'] . "profile.php?lookup=%s"
];
$link_type += Admins::getInstance()->getLinkType();

// Infusions count
$infusions_count = dbcount( "(inf_id)", DB_INFUSIONS );
$global_infusions = [];
if ($infusions_count > 0) {
    $inf_result = dbquery( "SELECT *
        FROM " . DB_INFUSIONS . "
        ORDER BY inf_id ASC
    " );
    while ($_inf = dbarray( $inf_result )) {
        if (file_exists( INFUSIONS . $_inf['inf_folder'] )) {
            $global_infusions[$_inf['inf_id']] = $_inf;
        }
    }
}

// Latest Comments
$global_comments = [];
if ($settings['comments_enabled'] == 1) {
    $global_comments['rows'] = dbcount( "('comment_id')", DB_COMMENTS );
    $c_rowstart = check_get( 'c_rowstart' ) && get( 'c_rowstart', FILTER_SANITIZE_NUMBER_INT ) <= $global_comments['rows'] ? get( 'c_rowstart' ) : 0;
    $comments_result = dbquery( "SELECT c.*, u.user_id, u.user_name, u.user_status, u.user_avatar
        FROM " . DB_COMMENTS . " c
        LEFT JOIN " . DB_USERS . " u on u.user_id=c.comment_name
        ORDER BY comment_datestamp DESC LIMIT " . $c_rowstart . ", 5
    " );

    if ($global_comments['rows'] > 10) {
        $global_comments['comments_nav'] = makepagenav( $c_rowstart, 10, $global_comments['rows'], 2, FUSION_SELF . $aidlink . '&pagenum=0&', 'c_rowstart' );
    }

    $global_comments['data'] = [];

    if (dbrows( $comments_result )) {
        while ($_comdata = dbarray( $comments_result )) {
            $global_comments['data'][] = $_comdata;
        }
    } else {
        $global_comments['nodata'] = $locale['254c'];
    }
}

// Latest Ratings
$global_ratings = [];
if ($settings['ratings_enabled'] == 1) {
    $global_ratings['rows'] = dbcount( "('rating_id')", DB_RATINGS );
    $r_rowstart = check_get( 'r_rowstart' ) && get( 'r_rowstart', FILTER_SANITIZE_NUMBER_INT ) <= $global_ratings['rows'] ? get( 'r_rowstart' ) : 0;
    $result = dbquery( "SELECT r.*, u.user_id, u.user_name, u.user_status, u.user_avatar
        FROM " . DB_RATINGS . " r
        LEFT JOIN " . DB_USERS . " u on u.user_id=r.rating_user
        ORDER BY rating_datestamp DESC LIMIT " . $r_rowstart . ", 5
    " );

    $global_ratings['data'] = [];
    if (dbrows( $result ) > 0) {
        while ($_ratdata = dbarray( $result )) {
            $global_ratings['data'][] = $_ratdata;
        }
    } else {
        $global_ratings['nodata'] = $locale['254b'];
    }

    if ($global_ratings['rows'] > 10) {
        $global_ratings['ratings_nav'] = makepagenav( $r_rowstart, 10, $global_comments['rows'], 2, FUSION_SELF . $aidlink . '&pagenum=0&', 'r_rowstart' );
    }
}

// Latest Submissions
$global_submissions = [];
if (!empty( Admins::getInstance()->getSubmitData() )) {
    $global_submissions['rows'] = dbcount( "('submit_id')", DB_SUBMISSIONS );
    $s_rowstart = check_get( 's_rowstart' ) && get( 's_rowstart', FILTER_SANITIZE_NUMBER_INT ) <= $global_submissions['rows'] ? get( 's_rowstart' ) : 0;
    $result = dbquery( "SELECT s.*, u.user_id, u.user_name, u.user_status, u.user_avatar
        FROM " . DB_SUBMISSIONS . " s
        LEFT JOIN " . DB_USERS . " u on u.user_id=s.submit_user
        ORDER BY submit_datestamp DESC LIMIT " . $s_rowstart . ", 5
    " );

    $global_submissions['data'] = [];

    if (dbrows( $result ) > 0) {
        while ($_subdata = dbarray( $result )) {
            $global_submissions['data'][] = $_subdata;
        }
    } else {
        $global_submissions['nodata'] = $locale['254a'];
    }

    if ($global_submissions['rows'] > 10) {
        $global_submissions['submissions_nav'] = makepagenav( $s_rowstart, 10, $global_submissions['rows'], 2, FUSION_SELF . $aidlink . '&pagenum=0&', 's_rowstart' );
    }
}

// Icon Grid
if (check_get( 'pagenum' ) && get( 'pagenum', FILTER_SANITIZE_NUMBER_INT )) {
    $pages = Admins::getInstance()->getAdminPages( get( 'pagenum' ) );
    $admin_icons = [
        'data' => $pages,
        'rows' => count( $pages )
    ];
}

if (checkrights( 'M' )) {
    if ($settings['admin_activation'] == 1 && dbcount( '(user_id)', DB_USERS, "user_status=2" ) > 0) {
        addnotice( 'info', str_replace( ['[LINK]', '[/LINK]'], ['<a href="' . ADMIN . 'members.php' . $aidlink . '&status=2">', '</a>'], $locale['unactivated_users'] ) );
    }
}

if (checkrights( 'I' )) {

    $infusions = PHPFusion\Installer\Infusions::updateChecker();

    if ($infusions > 0) {
        addnotice( 'info', $locale['infusions_updates_avalaible'] . ' <a class="btn btn-primary btn-sm m-l-10" href="' . ADMIN . 'infusions.php' . fusion_get_aidlink() . '">' . $locale['update_now'] . '</a>' );
    }
}

render_admin_dashboard();

require_once THEMES . 'templates/footer.php';
