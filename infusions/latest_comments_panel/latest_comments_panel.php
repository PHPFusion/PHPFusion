<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: latest_comments_panel.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

include_once INFUSIONS.'latest_comments_panel/templates.php';
$displayComments = 10;
$comments_per_page = fusion_get_settings('comments_per_page');
$comments_sorting_asc = fusion_get_settings('comments_sorting');

$result = dbquery(
    'SELECT c.comment_id, c.comment_item_id, c.comment_type, c.comment_message, u.user_id, u.user_name, u.user_status, u.user_avatar
    FROM '.DB_COMMENTS.' c
    LEFT JOIN '.DB_USERS.' u ON u.user_id = c.comment_name
    WHERE c.comment_hidden=0
    ORDER BY c.comment_datestamp DESC
    LIMIT '.$displayComments
);

$info = [];

$info['title'] = $locale['global_025'];
$info['theme_bullet'] = THEME_BULLET;

function latest_comments_get_item_title($type, $item_id) {
    static $cache = [];
    $key = $type.$item_id;

    if (!isset($cache[$key])) {
        switch ($type) {
            case 'A':
                $query = 'SELECT ar.article_subject as title
                FROM '.DB_ARTICLES.' AS ar
                WHERE ar.article_id=:id AND ar.article_draft=0
                AND '.groupaccess('ar.article_visibility').'
                '.(multilang_table('AR') ? "AND ".in_group('ar.article_language', LANGUAGE) : '');
                break;
            case 'B':
                $query = 'SELECT b.blog_subject as title
                FROM '.DB_BLOG.' AS b
                WHERE b.blog_id=:id AND '.groupaccess('b.blog_visibility').'
                '.(multilang_table('BL') ? 'AND '.in_group('b.blog_language', LANGUAGE) : '');
                break;
            case 'D':
                $query = 'SELECT d.download_title as title
                FROM '.DB_DOWNLOADS.' AS d
                INNER JOIN '.DB_DOWNLOAD_CATS.' AS c ON c.download_cat_id=d.download_cat
                WHERE d.download_id=:id AND '.groupaccess('d.download_visibility').'
                '.(multilang_table('DL') ? 'AND '.in_group('c.download_cat_language', LANGUAGE) : '');
                break;
            case 'N':
                $query = 'SELECT ns.news_subject as title
                FROM '.DB_NEWS.' AS ns
                WHERE ns.news_id=:id AND (ns.news_start=0 OR ns.news_start<="'.TIME.'")
                AND (ns.news_end=0 OR ns.news_end>="'.TIME.'") AND ns.news_draft=0
                AND '.groupaccess('ns.news_visibility').'
                '.(multilang_table('NS') ? 'AND '.in_group('ns.news_language', LANGUAGE) : '');
                break;
            case 'P':
                $query = 'SELECT p.photo_title as title
                FROM '.DB_PHOTOS.' AS p
                INNER JOIN '.DB_PHOTO_ALBUMS.' AS a ON p.album_id=a.album_id
                WHERE p.photo_id=:id AND '.groupaccess('a.album_access').'
                '.(multilang_table('PG') ? 'AND '.in_group('a.album_language', LANGUAGE) : '');
                break;
            default:
                $cache[$key] = FALSE;
                return FALSE;
        }

        $result = dbquery($query, [':id' => $item_id]);
        $cache[$key] = dbrows($result) ? dbarray($result)['title'] : FALSE;
    }

    return $cache[$key];
}

function latest_comments_get_comment_start($type, $item_id, $comment_id) {
    static $cache = [];
    $key = $type.$item_id;

    if (!isset($cache[$key])) {
        $cache[$key] = dbcount('(comment_id)', DB_COMMENTS, 'comment_item_id="'.$item_id.'" AND comment_type="'.$type.'" AND comment_id<'.$comment_id);
    }

    return $cache[$key]--;
}

if (dbrows($result)) {
    while ($data = dbarray($result)) {
        $item_title = latest_comments_get_item_title($data['comment_type'], $data['comment_item_id']);
        if (!$item_title) {
            continue;
        }

        $comment_start = latest_comments_get_comment_start($data['comment_type'], $data['comment_item_id'], $data['comment_id']);

        switch ($data['comment_type']) {
            case 'A':
                $comment_start = $comments_sorting_asc || $comment_start >= $comments_per_page ? '&amp;c_start_A'.$data['comment_item_id'].'='.(floor($comment_start / $comments_per_page) * $comments_per_page) : '';
                $url = INFUSIONS.'articles/articles.php?article_id='.$data['comment_item_id'];
                break;
            case 'B':
                $comment_start = $comments_sorting_asc || $comment_start >= $comments_per_page ? '&amp;c_start_blog_comments='.(floor($comment_start / $comments_per_page) * $comments_per_page) : '';
                $url = INFUSIONS.'blog/blog.php?readmore='.$data['comment_item_id'];
                break;
            case 'D':
                $comment_start = $comments_sorting_asc || $comment_start >= $comments_per_page ? '&amp;c_start_D'.$data['comment_item_id'].'='.(floor($comment_start / $comments_per_page) * $comments_per_page) : '';
                $url = INFUSIONS.'downloads/downloads.php?download_id='.$data['comment_item_id'];
                break;
            case 'N':
                $comment_start = $comments_sorting_asc || $comment_start >= $comments_per_page ? '&amp;c_start_news_comments='.(floor($comment_start / $comments_per_page) * $comments_per_page) : '';
                $url = INFUSIONS.'news/news.php?readmore='.$data['comment_item_id'];
                break;
            case 'P':
                $comment_start = $comments_sorting_asc || $comment_start >= $comments_per_page ? '&amp;c_start_P'.$data['comment_item_id'].'='.(floor($comment_start / $comments_per_page) * $comments_per_page) : '';
                $url = INFUSIONS.'gallery/gallery.php?photo_id='.$data['comment_item_id'];
                break;
            default:
                continue 2;
        }

        $info['item'][] = [
            'data'  => $data,
            'url'   => $url,
            'title' => $item_title,
            'c_url' => $url.$comment_start.'#c'.$data['comment_id']
        ];
    }
} else {
    $info['no_rows'] = $locale['global_026'];
}

render_latest_comments($info);
