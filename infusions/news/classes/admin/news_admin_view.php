<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/admin/controllers/news_admin_view.php
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
namespace PHPFusion\News;

class NewsAdminView extends NewsAdminModel {

    private $allowed_pages = array("news", "news_category", "news_form", "submissions", "settings");

    public function display_admin() {

        $locale = self::get_newsAdminLocale();

        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $this->allowed_pages) ? $_GET['section'] : $this->allowed_pages[0];

        add_breadcrumb(array('link' => INFUSIONS."news/news_admin.php".fusion_get_aidlink(), 'title' => $locale['news_0000']));

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

        opentable($locale['news_0001']);

        echo opentab($master_title, $_GET['section'], "news_admin", 1);
        switch ($_GET['section']) {
            case "news_category":
                NewsCategoryAdmin::getInstance()->displayNewsAdmin();
                break;
            case "settings":
                NewsSettingsAdmin::getInstance()->displayNewsAdmin();
                break;
            case "submissions":
                NewsSubmissionsAdmin::getInstance()->displayNewsAdmin();
                break;
            default:
                NewsAdmin::getInstance()->displayNewsAdmin();
        }
        echo closetab();
        closetable();
    }

}