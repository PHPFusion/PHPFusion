<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: infusion_db.php
| Author: Core Development Team
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
define('ARTICLE_LOCALE', fusion_get_inf_locale_path('articles.php', INFUSIONS.'articles/locale/'));
define('ARTICLE_ADMIN_LOCALE', fusion_get_inf_locale_path('article_admin.php', INFUSIONS.'articles/locale/'));

// Paths
const ARTICLES = INFUSIONS.'articles/';
const ARTICLE_CLASSES = INFUSIONS.'articles/classes/';
const IMAGES_A = INFUSIONS.'articles/images/';
const IMAGES_A_T = INFUSIONS.'articles/images/thumbs/';

// Database
const DB_ARTICLES = DB_PREFIX.'articles';
const DB_ARTICLE_CATS = DB_PREFIX.'article_cats';

// Admin Settings
Admins::getInstance()->setAdminPageIcons("A", "<i class='admin-ico fa fa-fw fa-book'></i>");
Admins::getInstance()->setCommentType("A", fusion_get_locale("A", LOCALE.LOCALESET."admin/main.php"));
Admins::getInstance()->setLinkType("A", fusion_get_settings("siteurl")."infusions/articles/articles.php?article_id=%s");

$inf_settings = get_settings('articles');
if (
    (!empty($inf_settings['article_allow_submission']) && $inf_settings['article_allow_submission']) &&
    (!empty($inf_settings['article_submission_access']) && checkgroup($inf_settings['article_submission_access']))
) {
    Admins::getInstance()->setSubmitData('a', [
        'infusion_name' => 'articles',
        'link'          => INFUSIONS."articles/article_submit.php",
        'submit_link'   => "submit.php?stype=a",
        'submit_locale' => fusion_get_locale('A', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('article_submit', LOCALE.LOCALESET."submissions.php"),
        'admin_link'    => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&section=submissions&submit_id=%s"
    ]);
}

Admins::getInstance()->setFolderPermissions('articles', [
    'infusions/articles/images/'        => TRUE,
    'infusions/articles/images/thumbs/' => TRUE
]);

Admins::getInstance()->setCustomFolder('A', [
    [
        'path'  => IMAGES_A,
        'URL'   => fusion_get_settings('siteurl').'infusions/articles/images/',
        'alias' => 'articles'
    ]
]);

if (defined('ARTICLES_EXISTS')) {
    function articles_home_module($limit) {
        $locale = fusion_get_locale();

        if (fusion_get_settings('comments_enabled') == 1) {
            $comments_query = "(SELECT COUNT(c1.comment_id) FROM ".DB_COMMENTS." c1
                WHERE c1.comment_item_id = ar.article_id AND c1.comment_type = 'A') AS comments_count,";
        }

        if (fusion_get_settings('ratings_enabled') == 1) {
            $ratings_query = "(SELECT COUNT(r1.rating_id) FROM ".DB_RATINGS." r1
                WHERE r1.rating_item_id = ar.article_id AND r1.rating_type = 'A') AS ratings_count,";
        }

        $result = dbquery("SELECT
            ar.article_id AS id,
            ar.article_subject AS title,
            ar.article_snippet AS content,
            ar.article_reads AS views_count,
            ar.article_datestamp AS datestamp,
            ac.article_cat_id AS cat_id,
            ac.article_cat_name AS cat_name,
            ar.article_thumbnail AS image_main,
            ".(!empty($comments_query) ? $comments_query : '')."
            ".(!empty($ratings_query) ? $ratings_query : '')."
            u.user_id, u.user_name, u.user_status
            FROM ".DB_ARTICLES." AS ar
            LEFT JOIN ".DB_ARTICLE_CATS." AS ac ON ac.article_cat_id = ar.article_cat
            LEFT JOIN ".DB_USERS." AS u ON u.user_id = ar.article_name
            WHERE ar.article_draft = 0
            AND ".groupaccess('ar.article_visibility')." ".(multilang_table("AR") ? "
            AND ".in_group('ac.article_cat_language', LANGUAGE) : "")."
            ORDER BY ar.article_datestamp DESC LIMIT ".$limit
        );

        $module = [];
        $module[DB_ARTICLES]['blockTitle'] = $locale['home_0001'];
        $module[DB_ARTICLES]['inf_settings'] = get_settings('articles');

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $data['content'] = parse_text($data['content'], ['parse_smileys' => FALSE, 'default_image_folder' => NULL]);
                $data['url'] = INFUSIONS.'articles/articles.php?article_id='.$data['id'];
                $data['category_link'] = INFUSIONS.'articles/articles.php?cat_id='.$data['cat_id'];
                $data['views'] = format_word($data['views_count'], $locale['fmt_read']);

                if (!empty($data['image_main']) && file_exists(IMAGES_A_T.$data['image_main'])) {
                    $data['image'] = IMAGES_A_T.$data['image_main'];
                }

                $module[DB_ARTICLES]['data'][] = $data;
            }
        } else {
            $module[DB_ARTICLES]['norecord'] = $locale['home_0051'];
        }

        return $module;
    }

    /**
     * @uses articles_home_module()
     */
    fusion_add_hook('home_modules', 'articles_home_module');

    function articles_cron_job24h_users_data($data) {
        dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_name=:user_id", [':user_id' => $data['user_id']]);
    }

    /**
     * @uses articles_cron_job24h_users_data()
     */
    fusion_add_hook('cron_job24h_users_data', 'articles_cron_job24h_users_data');
}
