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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

include_once INFUSIONS."latest_comments_panel/templates.php";
$displayComments = 10;
$comments_per_page = fusion_get_settings('comments_per_page');

$result = dbquery("SELECT c.comment_id, c.comment_item_id, c.comment_type, c.comment_message, u.user_id, u.user_name, u.user_status, u.user_avatar
    FROM ".DB_COMMENTS." c
    LEFT JOIN ".DB_USERS." u ON u.user_id = c.comment_name
    WHERE c.comment_hidden='0'
    ORDER BY c.comment_datestamp DESC
");

$info = [];

$info['title'] = $locale['global_025'];
$info['theme_bullet'] = THEME_BULLET;

if (dbrows($result)) {
    $i = 0;

    while ($data = dbarray($result)) {
        if ($i == $displayComments) {
            break;
        }

        switch ($data['comment_type']) {
            case 'A':
                $result_a = dbquery("SELECT ar.article_subject
                    FROM ".DB_ARTICLES." AS ar
                    INNER JOIN ".DB_ARTICLE_CATS." AS ac ON ac.article_cat_id=ar.article_cat
                    WHERE ar.article_id=:id AND ar.article_draft=0
                    AND ".groupaccess('ar.article_visibility')."
                    ".(multilang_table('AR') ? " AND ar.article_language='".LANGUAGE."'" : '')."
                    ORDER BY ar.article_datestamp DESC
                ", [':id' => $data['comment_item_id']]);

                if (dbrows($result_a)) {
                    $article_data = dbarray($result_a);
                    $comment_start = dbcount('(comment_id)', DB_COMMENTS, "comment_item_id='".$data['comment_item_id']."' AND comment_type='A' AND comment_id<=".$data['comment_id']);
                    $comment_start = $comment_start > $comments_per_page ? '&amp;c_start_news_comments='.((floor($comment_start / $comments_per_page) * $comments_per_page) - $comments_per_page) : '';
                    $info['item'][] = [
                        'data'  => $data,
                        'url'   => INFUSIONS.'articles/articles.php?article_id='.$data['comment_item_id'],
                        'title' => $article_data['article_subject'],
                        'c_url' => INFUSIONS.'articles/articles.php?article_id='.$data['comment_item_id'].$comment_start.'#c'.$data['comment_id']
                    ];
                }
                continue;
            case 'B':
                $result_b = dbquery("SELECT d.blog_subject
                    FROM ".DB_BLOG." AS d
                    INNER JOIN ".DB_BLOG_CATS." AS c ON c.blog_cat_id=d.blog_cat
                    WHERE d.blog_id=:id AND ".groupaccess('d.blog_visibility')."
                    ".(multilang_table('BL') ? " AND d.blog_language='".LANGUAGE."'" : '')."
                    ORDER BY d.blog_datestamp DESC
                ", [':id' => $data['comment_item_id']]);

                if (dbrows($result_b)) {
                    $blog_data = dbarray($result_b);
                    $comment_start = dbcount('(comment_id)', DB_COMMENTS, "comment_item_id='".$data['comment_item_id']."' AND comment_type='B' AND comment_id<=".$data['comment_id']);
                    $comment_start = $comment_start > $comments_per_page ? '&amp;c_start_news_comments='.((floor($comment_start / $comments_per_page) * $comments_per_page) - $comments_per_page) : '';
                    $info['item'][] = [
                        'data'  => $data,
                        'url'   => INFUSIONS.'blog/blog.php?readmore='.$data['comment_item_id'],
                        'title' => $blog_data['blog_subject'],
                        'c_url' => INFUSIONS.'blog/blog.php?readmore='.$data['comment_item_id'].$comment_start.'#c'.$data['comment_id']
                    ];
                }
                continue;
            case 'N':
                $result_n = dbquery("SELECT ns.news_subject
                    FROM ".DB_NEWS." AS ns
                    LEFT JOIN ".DB_NEWS_CATS." AS nc ON nc.news_cat_id=ns.news_cat
                    WHERE ns.news_id=:id AND (ns.news_start=0 OR ns.news_start<='".TIME."')
                    AND (ns.news_end=0 OR ns.news_end>='".TIME."') AND ns.news_draft=0
                    AND ".groupaccess('ns.news_visibility')."
                    ".(multilang_table('NS') ? "AND ns.news_language='".LANGUAGE."'" : '')."
                    ORDER BY ns.news_datestamp DESC
                ", [':id' => $data['comment_item_id']]);

                if (dbrows($result_n)) {
                    $news_data = dbarray($result_n);
                    $comment_start = dbcount('(comment_id)', DB_COMMENTS, "comment_item_id='".$data['comment_item_id']."' AND comment_type='N' AND comment_id<=".$data['comment_id']);
                    $comment_start = $comment_start > $comments_per_page ? '&amp;c_start_news_comments='.((floor($comment_start / $comments_per_page) * $comments_per_page) - $comments_per_page) : '';
                    $info['item'][] = [
                        'data'  => $data,
                        'url'   => INFUSIONS.'news/news.php?readmore='.$data['comment_item_id'],
                        'title' => $news_data['news_subject'],
                        'c_url' => INFUSIONS.'news/news.php?readmore='.$data['comment_item_id'].$comment_start.'#c'.$data['comment_id']
                    ];
                }
                continue;
            case 'P':
                $result_p = dbquery("SELECT p.photo_title
                    FROM ".DB_PHOTOS." AS p
                    INNER JOIN ".DB_PHOTO_ALBUMS." AS a ON p.album_id=a.album_id
                    WHERE p.photo_id=:id AND ".groupaccess('a.album_access')."
                    ".(multilang_table('PG') ? " AND a.album_language='".LANGUAGE."'" : '')."
                    ORDER BY p.photo_datestamp DESC
                ", [':id' => $data['comment_item_id']]);

                if (dbrows($result_p)) {
                    $photo_data = dbarray($result_p);
                    $comment_start = dbcount('(comment_id)', DB_COMMENTS, "comment_item_id='".$data['comment_item_id']."' AND comment_type='P' AND comment_id<=".$data['comment_id']);
                    $comment_start = $comment_start > $comments_per_page ? '&amp;c_start_news_comments='.((floor($comment_start / $comments_per_page) * $comments_per_page) - $comments_per_page) : '';
                    $info['item'][] = [
                        'data'  => $data,
                        'url'   => INFUSIONS.'gallery/gallery.php?photo_id='.$data['comment_item_id'],
                        'title' => $photo_data['photo_title'],
                        'c_url' => INFUSIONS.'gallery/gallery.php?photo_id='.$data['comment_item_id'].$comment_start.'#c'.$data['comment_id']
                    ];
                }
                continue;
            case 'D':
                $result_d = dbquery("SELECT d.download_title
                    FROM ".DB_DOWNLOADS." AS d
                    INNER JOIN ".DB_DOWNLOAD_CATS." AS c ON c.download_cat_id=d.download_cat
                    WHERE d.download_id=:id AND ".groupaccess('d.download_visibility')."
                    ".(multilang_table("DL") ? " AND c.download_cat_language='".LANGUAGE."'" : '')."
                    ORDER BY d.download_datestamp DESC
                ", [':id' => $data['comment_item_id']]);

                if (dbrows($result_d)) {
                    $download_data = dbarray($result_d);
                    $comment_start = dbcount('(comment_id)', DB_COMMENTS, "comment_item_id='".$data['comment_item_id']."' AND comment_type='D' AND comment_id<=".$data['comment_id']);
                    $comment_start = $comment_start > $comments_per_page ? '&amp;c_start_news_comments='.((floor($comment_start / $comments_per_page) * $comments_per_page) - $comments_per_page) : '';
                    $info['item'][] = [
                        'data'  => $data,
                        'url'   => INFUSIONS.'downloads/downloads.php?download_id='.$data['comment_item_id'],
                        'title' => $download_data['download_title'],
                        'c_url' => INFUSIONS.'downloads/downloads.php?download_id='.$data['comment_item_id'].$comment_start.'#c'.$data['comment_id']
                    ];
                }
                break;
        }

        $i++;
    }
} else {
    $info['no_rows'] = $locale['global_026'];
}

render_latest_comments($info);
