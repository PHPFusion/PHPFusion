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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

include_once INFUSIONS."latest_articles_panel/templates.php";

$article_result = "SELECT a.article_id, a.article_subject, tu.user_id, tu.user_name, tu.user_status, tu.user_avatar
    FROM ".DB_ARTICLES." AS a
    INNER JOIN ".DB_ARTICLE_CATS." AS ac ON a.article_cat=ac.article_cat_id
    LEFT JOIN ".DB_USERS." tu ON tu.user_id = a.article_name
    WHERE a.article_draft='0' AND ac.article_cat_status='1' AND ".groupaccess("a.article_visibility")." AND ".groupaccess("ac.article_cat_visibility")."
    ".(multilang_table("AR") ? "AND a.article_language='".LANGUAGE."' AND ac.article_cat_language='".LANGUAGE."'" : "")."
    ORDER BY a.article_datestamp DESC
    LIMIT 0,5
";
$result = dbquery($article_result);

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
                'user_avatar' => $data['user_avatar'],
                'user_status' => $data['user_status']
            ],
            'profile_link'  => profile_link($data['user_id'], $data['user_name'], $data['user_status'])
        ];

        $info['item'][] = $item;
    }
} else {
    $info['no_item'] = $locale['global_031'];
}

render_latest_articles($info);
