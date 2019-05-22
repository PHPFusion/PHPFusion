<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/admin/controllers/news_admin_view.php
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

namespace PHPFusion\News;

use PHPFusion\BreadCrumbs;

/**
 * Class NewsAdminView
 *
 * @package PHPFusion\News
 */
class NewsAdminView extends NewsAdminModel {

    private $allowed_pages = ["news", "news_category", "news_form", "submissions", "settings"];

    public function display_admin() {

        $section = get('section');
        $refs = get('ref');
        $action = get('action');
        $news_get_cat_id = get('action', FILTER_VALIDATE_INT);

        if ($section == 'back') {
            redirect(clean_request('', ['ref', 'section', 'news_id', 'action', 'cat_id'], FALSE));
        }

        $locale = self::get_newsAdminLocale();

        $section = in_array($section, $this->allowed_pages) ? $section : $this->allowed_pages[0];

        BreadCrumbs::getInstance()->addBreadCrumb(['link' => INFUSIONS."news/news_admin.php".fusion_get_aidlink(), 'title' => $locale['news_0001']]);

        add_to_title($locale['news_0001']);

        if (!empty($refs)) {
            $tab['title'][] = $locale['back'];
            $tab['id'][] = 'back';
            $tab['icon'][] = 'fa fa-arrow-left';
        }

        $news_title = $locale['news_0001'];
        $news_icon = 'fa fa-newspaper-o m-r-5';
        if ($refs == "news_form") {
            $news_get_id = get('news_id');

            $news_title = $locale['news_0002'];
            $news_icon = 'fa fa-plus m-r-5';
            if ($news_get_id) {
                $news_title = $locale['edit'];
                $news_icon = 'fa fa-pencil m-r-5';
            }
        }

        $tab['title'][] = $news_title;
        $tab['id'][] = 'news';
        $tab['icon'][] = $news_icon;

        $news_cat_title = $locale['news_0020'];
        if ($refs == "news_cat_form") {
            $news_cat_title = $locale['news_0022'];
            if (isset($_GET['news_cat_id'])) {
                $news_cat_title = $locale['news_0021'];
            }
        }

        if ($section) {
            switch ($section) {
                case "news_category":
                    BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $news_cat_title]);
                    break;
                case "settings":
                    BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['news_0004']]);
                    break;
                case "submissions":
                    BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['news_0023']]);
                    break;
                default:
            }
        }

        $edit = ($action == 'edit' && $news_get_cat_id) ? TRUE : FALSE;

        if ($submissions = dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='n'")) {
            addNotice("info", sprintf($locale['news_0137'], format_word($submissions, $locale['fmt_submission'])));
        }

        $tab['title'][] = $news_cat_title;
        $tab['id'][] = 'news_category';
        $tab['icon'][] = $edit ? 'fa fa-pencil m-r-5' : 'fa fa-folder m-r-5';

        $tab['title'][] = $locale['news_0023'];
        $tab['id'][] = 'submissions';
        $tab['icon'][] = 'fa fa-inbox m-r-5';

        $tab['title'][] = $locale['news_0004'];
        $tab['id'][] = 'settings';
        $tab['icon'][] = 'fa fa-cogs m-r-5';

        opentable($locale['news_0001']);
        echo opentab($tab, $section, 'news_admin', TRUE, '', 'section');
        switch ($section) {
            case 'news_category':
                NewsCategoryAdmin::getInstance()->displayNewsAdmin();
                break;
            case 'settings':
                NewsSettingsAdmin::getInstance()->displayNewsAdmin();
                break;
            case 'submissions':
                NewsSubmissionsAdmin::getInstance()->displayNewsAdmin();
                break;
            default:
                NewsAdmin::getInstance()->displayNewsAdmin();
        }
        echo closetab();
        closetable();
    }

}
