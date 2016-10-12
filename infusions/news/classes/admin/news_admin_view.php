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

        //@todo: remove this after beta rc5
        self::upgrade_news_gallery();

        if (isset($_GET['section']) && $_GET['section'] == 'back') {
            redirect(clean_request('', array('ref', 'section', 'news_id', 'action', 'cat_id'), FALSE));
        }

        $locale = self::get_newsAdminLocale();

        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $this->allowed_pages) ? $_GET['section'] : $this->allowed_pages[0];

        add_breadcrumb(array('link' => INFUSIONS."news/news_admin.php".fusion_get_aidlink(), 'title' => $locale['news_0000']));
        add_to_title($locale['news_0001']);

        if (!empty($_GET['ref'])) {
            $master_title['title'][] = $locale['back'];
            $master_title['id'][] = 'back';
            $master_title['icon'] = '';
        }

        $news_title = $locale['news_0001'];
        if (isset($_GET['ref']) && $_GET['ref'] == "news_form") {
            $news_title = $locale['news_0002'];
            if (isset($_GET['news_id'])) {
                $news_title = $locale['news_0003'];
            }
        }

        $master_title['title'][] = $news_title;
        $master_title['id'][] = 'news';
        $master_title['icon'] = '';

        $news_cat_title = $locale['news_0020'];
        if (isset($_GET['ref']) && $_GET['ref'] == "news_cat_form") {
            $news_cat_title = $locale['news_0022'];
            if (isset($_GET['news_cat_id'])) {
                $news_cat_title = $locale['news_0021'];
            }
        }

        $master_title['title'][] = $news_cat_title;
        $master_title['id'][] = 'news_category';
        $master_title['icon'] = '';
        $master_title['title'][] = isset($_GET['settings']) ? $locale['news_0004'] : $locale['news_0004'];
        $master_title['id'][] = 'settings';
        $master_title['icon'] = '';
        $master_title['title'][] = $locale['news_0023'];
        $master_title['id'][] = 'submissions';
        $master_title['icon'] = '';

        add_breadcrumb(array('link' => FUSION_REQUEST, 'title' => $news_title));

        opentable($locale['news_0001']);

        echo opentab($master_title, $_GET['section'], "news_admin", TRUE, '', 'section');
        switch ($_GET['section']) {
            case "news_category":
                add_breadcrumb(array('link' => FUSION_REQUEST, 'title' => $master_title['title'][1]));
                NewsCategoryAdmin::getInstance()->displayNewsAdmin();
                break;
            case "settings":
                NewsSettingsAdmin::getInstance()->displayNewsAdmin();
                break;
            case "submissions":
                add_breadcrumb(array('link' => FUSION_REQUEST, 'title' => $master_title['title'][3]));
                NewsSubmissionsAdmin::getInstance()->displayNewsAdmin();
                break;
            default:
                NewsAdmin::getInstance()->displayNewsAdmin();
        }
        echo closetab();
        closetable();
    }

}