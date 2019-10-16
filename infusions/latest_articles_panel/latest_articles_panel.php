<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: latest_articles_panel.php
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

if (defined('ARTICLES_EXIST')) {
    include_once INFUSIONS."latest_articles_panel/templates.php";

    $result = dbquery("SELECT a.article_id, a.article_subject, u.user_id, u.user_name, u.user_status, u.user_avatar
        FROM ".DB_ARTICLES." AS a
        INNER JOIN ".DB_ARTICLE_CATS." AS ac ON a.article_cat=ac.article_cat_id
        LEFT JOIN ".DB_USERS." u ON u.user_id = a.article_name
        WHERE a.article_draft='0' AND ac.article_cat_status='1' AND ".groupaccess("a.article_visibility")." AND ".groupaccess("ac.article_cat_visibility")."
        ".(multilang_table("AR") ? "AND ".in_group('a.article_language', LANGUAGE)." AND ".in_group('ac.article_cat_language', LANGUAGE) : "")."
        ORDER BY a.article_datestamp DESC
        LIMIT 5
    ");

    $info = [];

    $info['title'] = $locale['global_030'];
    $info['theme_bullet'] = THEME_BULLET;

    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $item = [
                'article_url'   => INFUSIONS."articles/articles.php?article_id=".$data['article_id'],
                'article_title' => $data['article_subject'],
                'userdata'      => [
                    'user_id'     => $data['user_id'],
                    'user_name'   => $data['user_name'],
                    'user_status' => $data['user_status'],
                    'user_avatar' => $data['user_avatar']
                ],
                'profile_link'  => profile_link($data['user_id'], $data['user_name'], $data['user_status'])
            ];

            $info['item'][] = $item;
        }
    } else {
        $info['no_item'] = $locale['global_031'];
    }

    render_latest_articles($info);
}
