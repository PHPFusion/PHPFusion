<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: home.php
| Author: Chubatyj Vitalij (Rizado)
| Co-Author: Takács Ákos (Rimelek)
| Co-Author: Frederick MC Chan (Chan)
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
require_once THEMES."templates/global/home.php";
require_once INCLUDES."infusions_include.php";
$locale = fusion_get_locale('', LOCALE.LOCALESET.'homepage.php');
$configs = array();
$configs[DB_NEWS] = array(
    'select'              => "SELECT
    ns.news_id as id, ns.news_subject as title, ns.news_news as content,
    ns.news_datestamp as datestamp, us.user_id, us.user_name,
    us.user_status, nc.news_cat_id as cat_id, nc.news_cat_name as cat_name,
    ni.news_image as image,
    nc.news_cat_image as cat_image,
    count(c1.comment_id) as comment_count,
    count(r1.rating_id) as rating_count
    FROM ".DB_NEWS." as ns
    LEFT JOIN ".DB_NEWS_IMAGES." as ni ON ni.news_id=ns.news_id
    LEFT JOIN ".DB_NEWS_CATS." as nc ON nc.news_cat_id = ns.news_cat
    LEFT JOIN ".DB_COMMENTS." as c1 on (c1.comment_item_id = ns.news_id and c1.comment_type = 'NS')
    LEFT JOIN ".DB_RATINGS." as r1 on (r1.rating_item_id = ns.news_id AND r1.rating_type = 'NS')
    INNER JOIN ".DB_USERS." as us ON ns.news_name = us.user_id
    WHERE (".time()." > ns.news_start OR ns.news_start = 0)
    AND ns.news_draft = 0
    AND (".time()." < ns.news_end OR ns.news_end = 0)
    AND ".groupaccess('ns.news_visibility')." ".(multilang_table("NS") ? "AND news_language='".LANGUAGE."'" : "")."
    group by ns.news_id
    ORDER BY ns.news_datestamp DESC LIMIT 3",
    'locale'              => array(
        'norecord'   => $locale['home_0050'],
        'blockTitle' => $locale['home_0000'],
    ),
    'infSettings'         => get_settings("news"),
    'categoryLinkPattern' => INFUSIONS."news/news.php?cat_id={cat_id}",
    'contentLinkPattern'  => INFUSIONS."news/news.php?readmore={id}",
);
$configs[DB_ARTICLES] = array(
    'select'              => "SELECT
    ar.article_id as id, ar.article_subject as title, ar.article_snippet as content,
    ar.article_datestamp as datestamp, ac.article_cat_id as cat_id, ac.article_cat_name as cat_name,
    us.user_id, us.user_name, us.user_status
    FROM ".DB_ARTICLES." as ar
    INNER JOIN ".DB_ARTICLE_CATS." as ac ON ac.article_cat_id = ar.article_cat
    INNER JOIN ".DB_USERS." as us ON us.user_id = ar.article_name
    WHERE ar.article_draft = 0
    AND ".groupaccess('ar.article_visibility')." ".(multilang_table("AR") ? "AND ac.article_cat_language='".LANGUAGE."'" : "")."
    ORDER BY ar.article_datestamp DESC LIMIT 3",
    'locale'              => array(
        'norecord'   => $locale['home_0051'],
        'blockTitle' => $locale['home_0001'],
    ),
    'infSettings'         => get_settings("article"),
    'categoryLinkPattern' => INFUSIONS."articles/articles.php?cat_id={cat_id}",
    'contentLinkPattern'  => INFUSIONS."articles/articles.php?article_id={id}",
);
$configs[DB_BLOG] = array(
    'select'              => "SELECT
    bl.blog_id as id, bl.blog_subject as title, bl.blog_blog as content,
    bl.blog_datestamp as datestamp, us.user_id, us.user_name,
    us.user_status, bc.blog_cat_id as cat_id, bc.blog_cat_name as cat_name,
    bl.blog_image as image,
    bc.blog_cat_image as cat_image,
    count(c1.comment_id) as comment_count,
    count(r1.rating_id) as rating_count
    FROM ".DB_BLOG." as bl
    LEFT JOIN ".DB_BLOG_CATS." as bc ON bc.blog_cat_id = bl.blog_cat
    LEFT JOIN ".DB_COMMENTS." as c1 on (c1.comment_item_id = bl.blog_id and c1.comment_type = 'BL')
    LEFT JOIN ".DB_RATINGS." as r1 on (r1.rating_item_id = bl.blog_id AND r1.rating_type = 'BL')
    INNER JOIN ".DB_USERS." as us ON bl.blog_name = us.user_id
    WHERE (".time()." > bl.blog_start OR bl.blog_start = 0)
    AND bl.blog_draft = 0
    AND (".time()." < bl.blog_end OR bl.blog_end = 0)
    AND ".groupaccess('bl.blog_visibility')." ".(multilang_table("BL") ? "AND blog_language='".LANGUAGE."'" : "")."
    group by bl.blog_id
    ORDER BY bl.blog_datestamp DESC LIMIT 3",
    'locale'              => array(
        'norecord'   => $locale['home_0052'],
        'blockTitle' => $locale['home_0002']
    ),
    'infSettings'         => get_settings("blog"),
    'categoryLinkPattern' => INFUSIONS."blog/blog.php?cat_id={cat_id}",
    'contentLinkPattern'  => INFUSIONS."blog/blog.php?readmore={id}",
);
$configs[DB_DOWNLOADS] = array(
    'select'              => "SELECT
    dl.download_id as id, dl.download_title as title, dl.download_description_short as content,
    dl.download_datestamp as datestamp, dc.download_cat_id as cat_id, dc.download_cat_name as cat_name,
    us.user_id, us.user_name, us.user_status,
    dl.download_image as image,
    count(c1.comment_id) as comment_count,
    count(r1.rating_id) as rating_count
    FROM ".DB_DOWNLOADS." dl
    INNER JOIN ".DB_DOWNLOAD_CATS." dc ON dc.download_cat_id = dl.download_cat
    INNER JOIN ".DB_USERS." us ON us.user_id = dl.download_user
    LEFT JOIN ".DB_COMMENTS." as c1 on (c1.comment_item_id = dl.download_id and c1.comment_type = 'D')
    LEFT JOIN ".DB_RATINGS." as r1 on (r1.rating_item_id = dl.download_id AND r1.rating_type = 'D')
    WHERE ".groupaccess('dl.download_visibility')." ".(multilang_table("DL") ? "AND dc.download_cat_language='".LANGUAGE."'" : "")."
    group by dl.download_id
    ORDER BY dl.download_datestamp DESC LIMIT 3",
    'locale'              => array(
        'norecord'   => $locale['home_0053'],
        'blockTitle' => $locale['home_0003']
    ),
    'infSettings'         => get_settings("downloads"),
    'categoryLinkPattern' => DOWNLOADS."downloads.php?cat_id={cat_id}",
    'contentLinkPattern'  => DOWNLOADS."downloads.php?download_id={id}",
);
$contents = array();
foreach ($configs as $table => $config) {
    if (!db_exists($table)) {
        continue;
    }
    $contents[$table] = array(
        'data'        => array(),
        'colwidth'    => 0,
        'norecord'    => $config['locale']['norecord'],
        'blockTitle'  => $config['locale']['blockTitle'],
        'infSettings' => $config['infSettings']
    );
    $result = dbquery($config['select']);
    $items_count = dbrows($result);
    if (!$items_count) {
        continue;
    }
    $contents[$table]['colwidth'] = floor(12 / $items_count);
    $data = array();
    $count = 1;
    while ($row = dbarray($result)) {
        $keys = array_keys($row);
        foreach ($keys as $i => $key) {
            $keys[$i] = '{'.$key.'}';
        }
        $row['content'] = str_replace("../../images", IMAGES, $row['content']);
        $pairs = array_combine($keys, array_values($row));
        $cat = $row['cat_id'] ? "<a href='".strtr($config['categoryLinkPattern'], $pairs)."'>".$row['cat_name']."</a>" : $locale['home_0102'];
        $data[$count] = array(
            'cat'       => $cat,
            'url'       => strtr($config['contentLinkPattern'], $pairs),
            'title'     => $row['title'],
            'meta'      => $locale['home_0105'].profile_link($row['user_id'], $row['user_name'], $row['user_status'])." ".showdate('newsdate', $row['datestamp']).$locale['home_0106'].$cat,
            'content'   => parse_textarea($row['content']),
            'datestamp' => $row['datestamp'],
            'cat_name'  => $row['cat_name'],
        );
        /* Infusion Settings Readings */
        switch ($table) {
            case DB_NEWS:
                if ($config['infSettings']['news_image_frontpage']) { // if it's 0 use uploaded photo, 1 always use category image
                    // go for cat image always
                    if ($row['cat_image']) {
                        $data[$count]['image'] = INFUSIONS."news/news_cats/".$row['cat_image'];
                    }
                } else {
                    // go for image if available
                    if ($row['image'] || $row['cat_image']) {
                        if ($row['cat_image']) {
                            $data[$count]['image'] = INFUSIONS."news/news_cats/".$row['cat_image'];
                        }
                        if ($row['image']) {
                            $data[$count]['image'] = INFUSIONS."news/images/".$row['image'];
                        }
                    }
                }
                break;
            case DB_BLOG:
                if ($row['image'] || $row['cat_image']) {
                    if ($row['cat_image']) {
                        $data[$count]['image'] = INFUSIONS."blog/blog_cats/".$row['cat_image'];
                    }
                    if ($row['image']) {
                        $data[$count]['image'] = INFUSIONS."blog/images/".$row['image'];
                    }
                }
                break;
            case DB_DOWNLOADS:
                if ($row['image']) {
                    $data[$count]['image'] = INFUSIONS."downloads/images/".$row['image'];
                }
            // end switch
        }
        $count++;
    }
    $contents[$table]['data'] = $data;
}
if (!$contents) {
    display_no_item();
} else {
    display_home($contents);
}
