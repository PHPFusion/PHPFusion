<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news_admin.php
| Author: PHP-Fusion Development Team
| Version: 9.2 prototype
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../../maincore.php";
pageAccess("N");
require_once THEMES."templates/admin_header.php";

if (file_exists(INFUSIONS."news/locale/".LOCALESET."news_admin.php")) {
    include INFUSIONS."news/locale/".LOCALESET."news_admin.php";
} else {
    include INFUSIONS."news/locale/English/news_admin.php";
}

if (file_exists(LOCALE.LOCALESET."admin/settings.php")) {
    include LOCALE.LOCALESET."admin/settings.php";
} else {
    include LOCALE."English/admin/settings.php";
}

require_once INCLUDES."infusions_include.php";
$news_settings = get_settings("news");
add_breadcrumb(array('link' => FUSION_SELF.$aidlink, 'title' => $locale['news_0000']));

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['news_id']) && isnum($_GET['news_id'])) {
    $del_data['news_id'] = $_GET['news_id'];
    $result = dbquery("SELECT news_image, news_image_t1, news_image_t2 FROM ".DB_NEWS." WHERE news_id='".$del_data['news_id']."'");
    if (dbrows($result)) {
        $data = dbarray($result);
        if (!empty($data['news_image']) && file_exists(IMAGES_N.$data['news_image'])) {
            unlink(IMAGES_N.$data['news_image']);
        }
        if (!empty($data['news_image_t1']) && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
            unlink(IMAGES_N_T.$data['news_image_t1']);
        }
        if (!empty($data['news_image_t2']) && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
            unlink(IMAGES_N_T.$data['news_image_t2']);
        }
        $result = dbquery("DELETE FROM ".DB_NEWS." WHERE news_id='".$del_data['news_id']."'");
        $result = dbquery("DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='".$del_data['news_id']."' and comment_type='N'");
        $result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$del_data['news_id']."' and rating_type='N'");
        dbquery_insert(DB_NEWS, $del_data, 'delete');
        addNotice('warning', $locale['news_0102']);
        redirect(FUSION_SELF.$aidlink);
    } else {
        redirect(FUSION_SELF.$aidlink);
    }
}
$allowed_pages = array("news", "news_category", "news_form", "submissions", "settings");

$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : 'news';

$news_title = "News";
if (isset($_GET['ref']) && $_GET['ref'] == "news_form") {
    if (isset($_GET['news_id'])) {
        $news_title = "Edit News";
    }
    $news_title = "Add News";
}

$master_title['title'][] = $news_title;
$master_title['id'][] = 'news';
$master_title['icon'] = '';

$news_cat_title = "News Category";
if (isset($_GET['ref']) && $_GET['ref'] == "news_cat_form") {
    if (isset($_GET['news_cat_id'])) {
        $news_cat_title = "Edit Category";
    }
    $news_cat_title = "Add Category";
}

$master_title['title'][] = $news_cat_title;
$master_title['id'][] = 'news_category';
$master_title['icon'] = '';

$master_title['title'][] = $locale['news_0023'];
$master_title['id'][] = 'submissions';
$master_title['icon'] = '';

$master_title['title'][] = isset($_GET['settings']) ? $locale['news_0004'] : $locale['news_0004'];
$master_title['id'][] = 'settings';
$master_title['icon'] = '';

$tab_active = $_GET['section'];

// Change that if add news - change link to news admin itself


opentable($locale['news_0001']);

echo opentab($master_title, $tab_active, "news_admin", 1);

switch ($_GET['section']) {
    case "news_category":
        include "admin/news_cat.php";
        if (isset($_GET['ref']) && $_GET['ref'] == "news_cat_form") {
            display_news_cat_form();
        } else {
            display_news_cat_listing();
        }
        break;
    case "settings":
        include "admin/news_settings.php";
        break;
    case "submissions":
        include "admin/news_submissions.php";
        break;
    default:
        include "admin/news.php";
        if (isset($_GET['ref']) && $_GET['ref'] == "news_form") {
            display_news_form();
        } else {
            display_news_listing();
        }
}
echo closetab();
closetable();
require_once THEMES."templates/footer.php";


/**
 * Returns nearest data unit
 * @param $total_bit
 * @return int
 */
function calculate_byte($total_bit) {
    $calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
    foreach ($calc_opts as $byte => $val) {
        if ($total_bit / $byte <= 999) {
            return (int)$byte;
        }
    }

    return 1000000;
}

/**
 * Function to progressively return closest full image_path
 * @param $news_image
 * @param $news_image_t1
 * @param $news_image_t2
 * @return string
 */
function get_news_image_path($news_image, $news_image_t1, $news_image_t2, $hiRes = FALSE) {
    if (!$hiRes) {
        if ($news_image_t1 && file_exists(IMAGES_N_T.$news_image_t1)) {
            return IMAGES_N_T.$news_image_t1;
        }
        if ($news_image_t1 && file_exists(IMAGES_N.$news_image_t1)) {
            return IMAGES_N.$news_image_t1;
        }
        if ($news_image_t2 && file_exists(IMAGES_N_T.$news_image_t2)) {
            return IMAGES_N_T.$news_image_t2;
        }
        if ($news_image_t2 && file_exists(IMAGES_N.$news_image_t2)) {
            return IMAGES_N.$news_image_t2;
        }
        if ($news_image && file_exists(IMAGES_N.$news_image)) {
            return IMAGES_N.$news_image;
        }
    } else {
        if ($news_image && file_exists(IMAGES_N.$news_image)) {
            return IMAGES_N.$news_image;
        }
        if ($news_image_t2 && file_exists(IMAGES_N.$news_image_t2)) {
            return IMAGES_N.$news_image_t2;
        }
        if ($news_image_t2 && file_exists(IMAGES_N_T.$news_image_t2)) {
            return IMAGES_N_T.$news_image_t2;
        }
        if ($news_image_t1 && file_exists(IMAGES_N.$news_image_t1)) {
            return IMAGES_N.$news_image_t1;
        }
        if ($news_image_t1 && file_exists(IMAGES_N_T.$news_image_t1)) {
            return IMAGES_N_T.$news_image_t1;
        }
    }

    return FALSE;
}