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

if (!defined("BLOG_LOCALE")) {
    if (file_exists(INFUSIONS."blog/locale/".LOCALESET."blog.php")) {
        define("BLOG_LOCALE", INFUSIONS."blog/locale/".LOCALESET."blog.php");
    } else {
        define("BLOG_LOCALE", INFUSIONS."blog/locale/English/blog.php");
    }
}
if (!defined("BLOG_ADMIN_LOCALE")) {
    if (file_exists(INFUSIONS."blog/locale/".LOCALESET."blog_admin.php")) {
        define("BLOG_ADMIN_LOCALE", INFUSIONS."blog/locale/".LOCALESET."blog_admin.php");
    } else {
        define("BLOG_ADMIN_LOCALE", INFUSIONS."blog/locale/English/blog_admin.php");
    }
}

if (!defined("IMAGES_B")) {
    define("IMAGES_B", INFUSIONS."blog/images/");
}
if (!defined("IMAGES_B_T")) {
    define("IMAGES_B_T", INFUSIONS."blog/images/thumbs/");
}
if (!defined("IMAGES_BC")) {
    define("IMAGES_BC", INFUSIONS."blog/blog_cats/");
}
if (!defined("DB_BLOG")) {
    define("DB_BLOG", DB_PREFIX."blog");
}
if (!defined("DB_BLOG_CATS")) {
    define("DB_BLOG_CATS", DB_PREFIX."blog_cats");
}

// Admin Settings
\PHPFusion\Admins::getInstance()->setAdminPageIcons("BLOG", "<i class='admin-ico fa fa-fw fa-graduation-cap'></i>");
\PHPFusion\Admins::getInstance()->setCommentType('B', fusion_get_locale('BLOG', LOCALE.LOCALESET."admin/main.php"));
\PHPFusion\Admins::getInstance()->setLinkType('B', fusion_get_settings("siteurl")."infusions/blog/blog.php?readmore=%s");

$inf_settings = get_settings('blog');
if ((!empty($inf_settings['blog_allow_submission']) && $inf_settings['blog_allow_submission']) && (!empty($inf_settings['blog_submission_access']) && checkgroup($inf_settings['blog_submission_access']))) {
    \PHPFusion\Admins::getInstance()->setSubmitData('b', [
        'infusion_name' => 'blog',
        'link'          => INFUSIONS."blog/blog_submit.php",
        'submit_link'   => "submit.php?stype=b",
        'submit_locale' => fusion_get_locale('BLOG', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('blog_submit', LOCALE.LOCALESET."submissions.php"),
        'admin_link'    => INFUSIONS."blog/blog_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
    ]);
}

\PHPFusion\Admins::getInstance()->setFolderPermissions('blog', [
    'infusions/blog/images/'        => TRUE,
    'infusions/blog/images/thumbs/' => TRUE
]);

\PHPFusion\Admins::getInstance()->setCustomFolder('BLOG', [
    [
        'path'  => IMAGES_B,
        'URL'   => fusion_get_settings('siteurl').'infusions/blog/images/',
        'alias' => 'blog'
    ],
    [
        'path'  => IMAGES_BC,
        'URL'   => fusion_get_settings('siteurl').'infusions/blog_cats/',
        'alias' => 'blog_cats'
    ]
]);

if (defined('BLOG_EXISTS')) {
    function blog_home_module($limit) {
        $locale = fusion_get_locale();

        if (fusion_get_settings('comments_enabled') == 1) {
            $comments_query = "(SELECT COUNT(c1.comment_id) FROM ".DB_COMMENTS." c1 WHERE c1.comment_item_id = bl.blog_id AND c1.comment_type = 'BL') AS comments_count,";
        }

        if (fusion_get_settings('ratings_enabled') == 1) {
            $ratings_query = "(SELECT COUNT(r1.rating_id) FROM ".DB_RATINGS." r1 WHERE r1.rating_item_id = bl.blog_id AND r1.rating_type = 'BL') AS ratings_count,";
        }

        $result = dbquery("SELECT
            bl.blog_id AS id,
            bl.blog_subject AS title,
            bl.blog_blog AS content,
            bl.blog_reads AS views_count,
            bl.blog_datestamp AS datestamp,
            bc.blog_cat_id AS cat_id,
            bc.blog_cat_name AS cat_name,
            bl.blog_image AS image_main,
            bl.blog_image_t1 AS image_thumb,
            bl.blog_image_t2 AS image_thumb2,
            bc.blog_cat_image AS cat_image,
            ".(!empty($comments_query) ? $comments_query : '')."
            ".(!empty($ratings_query) ? $ratings_query : '')."
            u.user_id, u.user_name, u.user_status
            FROM ".DB_BLOG." AS bl
            LEFT JOIN ".DB_BLOG_CATS." AS bc ON bc.blog_cat_id = bl.blog_cat
            LEFT JOIN ".DB_USERS." AS u ON bl.blog_name = u.user_id
            WHERE (".time()." > bl.blog_start OR bl.blog_start = 0)
            AND bl.blog_draft = 0
            AND (".time()." < bl.blog_end OR bl.blog_end = 0)
            AND ".groupaccess('bl.blog_visibility')." ".(multilang_table("BL") ? "AND ".in_group('blog_language', LANGUAGE) : "")."
            GROUP BY bl.blog_id
            ORDER BY bl.blog_datestamp DESC LIMIT ".$limit
        );

        $module = [];
        $module[DB_BLOG]['blockTitle'] = $locale['home_0002'];
        $module[DB_BLOG]['inf_settings'] = get_settings('blog');

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $data['content'] = parse_text($data['content'], [
                    'parse_smileys'        => FALSE,
                    'parse_bbcode'         => FALSE,
                    'default_image_folder' => IMAGES_B
                ]);
                $data['url'] = INFUSIONS.'blog/blog.php?readmore='.$data['id'];
                $data['category_link'] = INFUSIONS.'blog/blog.php?cat_id='.$data['cat_id'];
                $data['views'] = format_word($data['views_count'], $locale['fmt_read']);

                if ($data['image_main'] || $data['cat_image']) {
                    if ($data['image_thumb'] && file_exists(INFUSIONS.'blog/images/thumbs/'.$data['image_thumb'])) {
                        $data['image'] = INFUSIONS.'blog/images/thumbs/'.$data['image_thumb'];
                    } else if ($data['image_thumb2'] && file_exists(INFUSIONS.'blog/images/thumbs/'.$data['image_thumb2'])) {
                        $data['image'] = INFUSIONS.'blog/images/thumbs/'.$data['image_thumb2'];
                    } else if ($data['image_main'] && file_exists(INFUSIONS.'blog/images/'.$data['image_main'])) {
                        $data['image'] = INFUSIONS.'blog/images/'.$data['image_main'];
                    } else if ($data['cat_image']) {
                        $data['image'] = INFUSIONS.'blog/blog_cats/'.$data['cat_image'];
                    } else {
                        $data['image'] = get_image('imagenotfound');
                    }
                } else {
                    $data['image'] = get_image('imagenotfound');
                }

                $module[DB_BLOG]['data'][] = $data;
            }
        } else {
            $module[DB_BLOG]['norecord'] = $locale['home_0052'];
        }

        return $module;
    }

    /**
     * @uses blog_home_module()
     */
    fusion_add_hook('home_modules', 'blog_home_module');
}
