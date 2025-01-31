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
define('DOWNLOAD_LOCALE', fusion_get_inf_locale_path('downloads.php', INFUSIONS.'downloads/locale/'));
define('DOWNLOAD_ADMIN_LOCALE', fusion_get_inf_locale_path('downloads_admin.php', INFUSIONS.'downloads/locale/'));

// Paths
const DOWNLOADS = INFUSIONS."downloads/";
const DOWNLOADS_FILES = INFUSIONS."downloads/files/";
const IMAGES_D = INFUSIONS."downloads/images/";

// Database
const DB_DOWNLOADS = DB_PREFIX."downloads";
const DB_DOWNLOAD_CATS = DB_PREFIX."download_cats";

// Admin Settings
Admins::getInstance()->setAdminPageIcons("D", "<i class='admin-ico fa fa-fw fa-cloud-download'></i>");
Admins::getInstance()->setCommentType('D', fusion_get_locale('D', LOCALE.LOCALESET."admin/main.php"));
Admins::getInstance()->setLinkType('D', fusion_get_settings("siteurl")."infusions/downloads/downloads.php?download_id=%s");

$inf_settings = get_settings('downloads');
if (
    (!empty($inf_settings['download_allow_submission']) && $inf_settings['download_allow_submission']) &&
    (!empty($inf_settings['download_submission_access']) && checkgroup($inf_settings['download_submission_access']))
) {
    Admins::getInstance()->setSubmitData('d', [
        'infusion_name' => 'downloads',
        'link'          => INFUSIONS."downloads/download_submit.php",
        'submit_link'   => "submit.php?stype=d",
        'submit_locale' => fusion_get_locale('D', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('download_submit', LOCALE.LOCALESET."submissions.php"),
        'admin_link'    => INFUSIONS."downloads/downloads_admin.php".fusion_get_aidlink()."&section=submissions&submit_id=%s"
    ]);
}

Admins::getInstance()->setFolderPermissions('downloads', [
    'infusions/downloads/files/'              => TRUE,
    'infusions/downloads/images/'             => TRUE,
    'infusions/downloads/submissions/'        => TRUE,
    'infusions/downloads/submissions/images/' => TRUE
]);

Admins::getInstance()->setCustomFolder('D', [
    [
        'path'  => IMAGES_D,
        'URL'   => fusion_get_settings('siteurl').'infusions/download/images/',
        'alias' => 'downloads'
    ]
]);

if (defined('DOWNLOADS_EXISTS')) {
    function downloads_home_module($limit) {
        $locale = fusion_get_locale();

        if (fusion_get_settings('comments_enabled') == 1) {
            $comments_query = "(SELECT COUNT(c1.comment_id) FROM ".DB_COMMENTS." c1
                WHERE c1.comment_item_id = dl.download_id AND c1.comment_type = 'D') AS comments_count,";
        }

        if (fusion_get_settings('ratings_enabled') == 1) {
            $ratings_query = "(SELECT COUNT(r1.rating_id) FROM ".DB_RATINGS." r1
                WHERE r1.rating_item_id = dl.download_id AND r1.rating_type = 'D') AS ratings_count,";
        }

        $result = dbquery("SELECT
            dl.download_id AS id,
            dl.download_title AS title,
            dl.download_description_short AS content,
            dl.download_count AS views_count,
            dl.download_datestamp AS datestamp,
            dc.download_cat_id AS cat_id,
            dc.download_cat_name AS cat_name,
            dl.download_image AS image_main,
            dl.download_image_thumb AS image_thumb,
            ".(!empty($comments_query) ? $comments_query : '')."
            ".(!empty($ratings_query) ? $ratings_query : '')."
            u.user_id, u.user_name, u.user_status
            FROM ".DB_DOWNLOADS." dl
            LEFT JOIN ".DB_DOWNLOAD_CATS." dc ON dc.download_cat_id = dl.download_cat
            LEFT JOIN ".DB_USERS." u ON u.user_id = dl.download_user
            WHERE ".groupaccess('dl.download_visibility')." ".(multilang_table("DL") ? "
            AND ".in_group('dc.download_cat_language', LANGUAGE) : "")."
            GROUP BY dl.download_id
            ORDER BY dl.download_datestamp DESC LIMIT ".$limit
        );

        $module = [];
        $module[DB_DOWNLOADS]['blockTitle'] = $locale['home_0003'];
        $module[DB_DOWNLOADS]['inf_settings'] = get_settings('downloads');

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $data['content'] = parse_text($data['content'], ['decode' => FALSE, 'default_image_folder' => NULL]);
                $data['url'] = INFUSIONS.'downloads/downloads.php?download_id='.$data['id'];
                $data['category_link'] = INFUSIONS.'downloads/downloads.php?cat_id='.$data['cat_id'];
                $data['views'] = format_word($data['views_count'], $locale['fmt_download']);

                if ($module[DB_DOWNLOADS]['inf_settings']['download_screenshot']) {
                    if (!empty($data['image_thumb']) && file_exists(IMAGES_D.$data['image_thumb'])) {
                        $data['image'] = IMAGES_D.$data['image_thumb'];
                    } else if (!empty($data['image_main']) && file_exists(IMAGES_D.$data['image_main'])) {
                        $data['image'] = IMAGES_D.$data['image_main'];
                    } else {
                        $data['image'] = get_image('imagenotfound');
                    }
                }

                $module[DB_DOWNLOADS]['data'][] = $data;
            }
        } else {
            $module[DB_DOWNLOADS]['norecord'] = $locale['home_0053'];
        }

        return $module;
    }

    /**
     * @uses downloads_home_module()
     */
    fusion_add_hook('home_modules', 'downloads_home_module');
}
