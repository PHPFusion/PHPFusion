<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: infusion_db.php
| Author: Core Development Team (coredevs@phpfusion.com)
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

use PHPFusion\Admins;

// Locales
define('NEWS_LOCALE', fusion_get_inf_locale_path('news.php', INFUSIONS.'news/locale/'));
define('NEWS_ADMIN_LOCALE', fusion_get_inf_locale_path('news_admin.php', INFUSIONS.'news/locale/'));

// Paths
const NEWS = INFUSIONS.'news/';
const NEWS_CLASSES = INFUSIONS.'news/classes/';
const IMAGES_N = INFUSIONS.'news/images/';
const IMAGES_N_T = INFUSIONS."news/images/thumbs/";
const IMAGES_NC = INFUSIONS.'news/news_cats/';

// Database
const DB_NEWS = DB_PREFIX.'news';
const DB_NEWS_CATS = DB_PREFIX.'news_cats';
const DB_NEWS_IMAGES = DB_PREFIX.'news_gallery';

// Admin Settings
Admins::getInstance()->setAdminPageIcons("N", "<i class='admin-ico fa fa-fw fa-newspaper-o'></i>");
Admins::getInstance()->setCommentType('N', fusion_get_locale('N', LOCALE.LOCALESET."admin/main.php"));
Admins::getInstance()->setLinkType('N', fusion_get_settings("siteurl")."infusions/news/news.php?readmore=%s");

$inf_settings = get_settings('news');
if (
    (!empty($inf_settings['news_allow_submission']) && $inf_settings['news_allow_submission']) &&
    (!empty($inf_settings['news_submission_access']) && checkgroup($inf_settings['news_submission_access']))
) {
    Admins::getInstance()->setSubmitData('n', [
        'infusion_name' => 'news',
        'link'          => INFUSIONS."news/news_submit.php",
        'submit_link'   => "submit.php?stype=n",
        'submit_locale' => fusion_get_locale('N', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('news_submit', LOCALE.LOCALESET."submissions.php"),
        'admin_link'    => INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&section=submissions&submit_id=%s"
    ]);
}

Admins::getInstance()->setFolderPermissions('news', [
    'infusions/news/images/'        => TRUE,
    'infusions/news/images/thumbs/' => TRUE
]);

Admins::getInstance()->setCustomFolder('N', [
    [
        'path'  => IMAGES_N,
        'URL'   => fusion_get_settings('siteurl').'infusions/news/images/',
        'alias' => 'news'
    ],
    [
        'path'  => IMAGES_NC,
        'URL'   => fusion_get_settings('siteurl').'infusions/news/news_cats/',
        'alias' => 'news_cats'
    ]
]);

if (defined('NEWS_EXISTS')) {
    function news_home_module($limit) {
        $locale = fusion_get_locale();

        if (fusion_get_settings('comments_enabled') == 1) {
            $comments_query = "(SELECT COUNT(c1.comment_id) FROM ".DB_COMMENTS." c1
                WHERE c1.comment_item_id = ns.news_id AND c1.comment_type = 'N') AS comments_count,";
        }

        if (fusion_get_settings('ratings_enabled') == 1) {
            $ratings_query = "(SELECT COUNT(r1.rating_id) FROM ".DB_RATINGS." r1
                WHERE r1.rating_item_id = ns.news_id AND r1.rating_type = 'NS') AS ratings_count,";
        }

        $result = dbquery("SELECT
            ns.news_id AS id,
            ns.news_subject AS title,
            ns.news_news AS content,
            ns.news_reads AS views_count,
            ns.news_datestamp AS datestamp,
            nc.news_cat_id AS cat_id,
            nc.news_cat_name AS cat_name,
            ni.news_image AS image_main,
            ni.news_image_t1 AS image_thumb,
            ni.news_image_t2 AS image_thumb2,
            nc.news_cat_image AS cat_image,
             ".(!empty($comments_query) ? $comments_query : '')."
            ".(!empty($ratings_query) ? $ratings_query : '')."
            u.user_id, u.user_name, u.user_status
            FROM ".DB_NEWS." AS ns
            LEFT JOIN ".DB_NEWS_IMAGES." AS ni ON ni.news_id=ns.news_id
            LEFT JOIN ".DB_NEWS_CATS." AS nc ON nc.news_cat_id = ns.news_cat
            LEFT JOIN ".DB_USERS." AS u ON ns.news_name = u.user_id
            WHERE (".time()." > ns.news_start OR ns.news_start = 0)
            AND ns.news_draft = 0
            AND (".time()." < ns.news_end OR ns.news_end = 0)
            AND ".groupaccess('ns.news_visibility')." ".(multilang_table("NS") ? "AND ".in_group('news_language', LANGUAGE) : "")."
            GROUP BY ns.news_id
            ORDER BY ns.news_datestamp DESC LIMIT ".$limit
        );

        $module = [];
        $module[DB_NEWS]['blockTitle'] = $locale['home_0000'];
        $module[DB_NEWS]['inf_settings'] = get_settings('news');

        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $data['content'] = parse_text($data['content'], ['parse_bbcode' => FALSE, 'default_image_folder' => NULL]);
                $data['url'] = INFUSIONS.'news/news.php?readmore='.$data['id'];
                $data['category_link'] = INFUSIONS.'news/news.php?cat_id='.$data['cat_id'];
                $data['views'] = format_word($data['views_count'], $locale['fmt_read']);

                if ($module[DB_NEWS]['inf_settings']['news_image_frontpage']) {
                    if ($data['cat_image']) {
                        $data['image'] = INFUSIONS.'news/news_cats/'.$data['cat_image'];
                    }
                } else {
                    if (!empty($data['image_main']) || !empty($data['cat_image'])) {
                        if (!empty($data['image_thumb']) && file_exists(INFUSIONS.'news/images/thumbs/'.$data['image_thumb'])) {
                            $data['image'] = INFUSIONS.'news/images/thumbs/'.$data['image_thumb'];
                        } else if (!empty($data['image_thumb2']) && file_exists(INFUSIONS.'news/images/thumbs/'.$data['image_thumb2'])) {
                            $data['image'] = INFUSIONS.'news/images/thumbs/'.$data['image_thumb2'];
                        } else if (!empty($data['image_main']) && file_exists(INFUSIONS.'news/images/'.$data['image_main'])) {
                            $data['image'] = INFUSIONS.'news/images/'.$data['image_main'];
                        } else if (!empty($data['cat_image'])) {
                            $data['image'] = INFUSIONS.'news/news_cats/'.$data['cat_image'];
                        } else {
                            $data['image'] = get_image('imagenotfound');
                        }
                    } else {
                        $data['image'] = get_image('imagenotfound');
                    }
                }

                $module[DB_NEWS]['data'][] = $data;
            }
        } else {
            $module[DB_NEWS]['norecord'] = $locale['home_0050'];
        }

        return $module;
    }

    /**
     * @uses news_home_module()
     */
    fusion_add_hook('home_modules', 'news_home_module');

    function news_cron_job24h_users_data($data) {
        dbquery("DELETE FROM ".DB_NEWS." WHERE news_name=:user_id", [':user_id' => $data['user_id']]);
    }

    /**
     * @uses news_cron_job24h_users_data()
     */
    fusion_add_hook('cron_job24h_users_data', 'news_cron_job24h_users_data');
}
