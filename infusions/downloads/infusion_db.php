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

if (!defined("DOWNLOAD_LOCALE")) {
    if (file_exists(INFUSIONS."downloads/locale/".LOCALESET."downloads.php")) {
        define("DOWNLOAD_LOCALE", INFUSIONS."downloads/locale/".LOCALESET."downloads.php");
    } else {
        define("DOWNLOAD_LOCALE", INFUSIONS."downloads/locale/English/downloads.php");
    }
}

if (!defined("DOWNLOAD_ADMIN_LOCALE")) {
    if (file_exists(INFUSIONS."downloads/locale/".LOCALESET."downloads_admin.php")) {
        define("DOWNLOAD_ADMIN_LOCALE", INFUSIONS."downloads/locale/".LOCALESET."downloads_admin.php");
    } else {
        define("DOWNLOAD_ADMIN_LOCALE", INFUSIONS."downloads/locale/English/downloads_admin.php");
    }
}

if (!defined("DOWNLOADS")) {
    define("DOWNLOADS", INFUSIONS."downloads/");
}
if (!defined("IMAGES_D")) {
    define("IMAGES_D", INFUSIONS."downloads/images/");
}
if (!defined("DB_DOWNLOAD_CATS")) {
    define("DB_DOWNLOAD_CATS", DB_PREFIX."download_cats");
}
if (!defined("DB_DOWNLOADS")) {
    define("DB_DOWNLOADS", DB_PREFIX."downloads");
}

// Admin Settings
\PHPFusion\Admins::getInstance()->setAdminPageIcons("D", "<i class='admin-ico fa fa-fw fa-cloud-download'></i>");
\PHPFusion\Admins::getInstance()->setCommentType('D', fusion_get_locale('D', LOCALE.LOCALESET."admin/main.php"));
\PHPFusion\Admins::getInstance()->setLinkType('D', fusion_get_settings("siteurl")."infusions/downloads/downloads.php?download_id=%s");

$inf_settings = get_settings('downloads');
if ((!empty($inf_settings['download_allow_submission']) && $inf_settings['download_allow_submission']) && (!empty($inf_settings['download_submission_access']) && checkgroup($inf_settings['download_submission_access']))) {
    \PHPFusion\Admins::getInstance()->setSubmitData('d', [
        'infusion_name' => 'downloads',
        'link'          => INFUSIONS."downloads/download_submit.php",
        'submit_link'   => "submit.php?stype=d",
        'submit_locale' => fusion_get_locale('D', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('download_submit', LOCALE.LOCALESET."submissions.php"),
        'admin_link'    => INFUSIONS."downloads/downloads_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
    ]);
}

\PHPFusion\Admins::getInstance()->setFolderPermissions('downloads', [
    'infusions/downloads/files/'              => TRUE,
    'infusions/downloads/images/'             => TRUE,
    'infusions/downloads/submissions/'        => TRUE,
    'infusions/downloads/submissions/images/' => TRUE
]);

\PHPFusion\Admins::getInstance()->setCustomFolder('D', [
    [
        'path'  => IMAGES_D,
        'URL'   => fusion_get_settings('siteurl').'infusions/download/images/',
        'alias' => 'downloads'
    ]
]);

if (db_exists(DB_DOWNLOADS)) {
    function downloads_home_module() {
        $locale = fusion_get_locale();
        $limit = PHPFusion\HomePage::getLimit();

        $result = dbquery("SELECT
            dl.download_id AS id,
            dl.download_title AS title,
            dl.download_description_short AS content,
            dl.download_count AS count,
            dl.download_datestamp AS datestamp,
            dc.download_cat_id AS cat_id,
            dc.download_cat_name AS cat_name,
            dl.download_image AS image_main,
            dl.download_image_thumb AS image_thumb,
            count(c1.comment_id) AS comment_count,
            count(r1.rating_id) AS rating_count,
            u.user_id, u.user_name, u.user_status
            FROM ".DB_DOWNLOADS." dl
            INNER JOIN ".DB_DOWNLOAD_CATS." dc ON dc.download_cat_id = dl.download_cat
            INNER JOIN ".DB_USERS." u ON u.user_id = dl.download_user
            LEFT JOIN ".DB_COMMENTS." AS c1 on (c1.comment_item_id = dl.download_id and c1.comment_type = 'D')
            LEFT JOIN ".DB_RATINGS." AS r1 on (r1.rating_item_id = dl.download_id AND r1.rating_type = 'D')
            WHERE ".groupaccess('dl.download_visibility')." ".(multilang_table("DL") ? "AND ".in_group('dc.download_cat_language', LANGUAGE) : "")."
            GROUP BY dl.download_id
            ORDER BY dl.download_datestamp DESC LIMIT ".$limit
        );

        $module = [];
        $module[DB_DOWNLOADS]['module_title'] = $locale['home_0003'];
        $module[DB_DOWNLOADS]['inf_settings'] = get_settings('downloads');

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $data['content'] = parse_textarea($data['content'], TRUE, TRUE, FALSE, NULL);
                $data['url'] = INFUSIONS.'downloads/downloads.php?download_id='.$data['id'];
                $data['category_link'] = INFUSIONS.'downloads/downloads.php?cat_id='.$data['cat_id'];
                $data['item_count'] = format_word($data['count'], $locale['fmt_download']);

                if ($module[DB_DOWNLOADS]['inf_settings']['download_screenshot']) {
                    if ($data['image_thumb'] && file_exists(INFUSIONS.'downloads/images/'.$data['image_thumb'])) {
                        $data['image'] = INFUSIONS.'downloads/images/'.$data['image_thumb'];
                    } else if ($data['image_main'] && file_exists(INFUSIONS.'downloads/images/'.$data['image_main'])) {
                        $data['image'] = INFUSIONS.'downloads/images/'.$data['image_main'];
                    } else {
                        $data['image'] = get_image('imagenotfound');
                    }
                }

                $module[DB_DOWNLOADS]['items'][] = $data;
            }
        } else {
            $module[DB_DOWNLOADS]['norecord'] = $locale['home_0053'];
        }

        return $module;
    }

    fusion_add_hook('home_modules', 'downloads_home_module');
}
