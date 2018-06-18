<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/admin/controllers/article_admin_view.php
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
namespace PHPFusion\Articles;

use \PHPFusion\BreadCrumbs;

class ArticlesAdminView extends ArticlesAdminModel {
    private $allowed_pages = ["article", "article_category", "article_form", "submissions", "settings"];
    private static $locale = [];

    public function display_admin() {
        self::$locale = self::get_articleAdminLocale();

        // Back and Check Section
        if (isset($_GET['section']) && $_GET['section'] == "back") {
            redirect(clean_request("", ["ref", "section", "article_id", "action", "cat_id", "submit_id"], FALSE));
        }
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $this->allowed_pages) ? $_GET['section'] : $this->allowed_pages[0];

        // Sitetitle
        add_to_title(self::$locale['article_0000']);

        // Handle Breadcrumbs and Titles
        BreadCrumbs::getInstance()->addBreadCrumb(["link" => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink(), "title" => self::$locale['article_0000']]);

        $articleTitle = self::$locale['article_0000'];
        $articleCatTitle = self::$locale['article_0004'];

        if (!empty($_GET['section'])) {
            switch ($_GET['section']) {
                case "article":
                    if (isset($_GET['ref']) && $_GET['ref'] == "article_form") {
                        if (!isset($_GET['article_id'])) {
                            BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => self::$locale['article_0002']]);
                            $articleTitle = self::$locale['article_0002'];
                        } else {
                            BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => self::$locale['article_0003']]);
                            $articleTitle = self::$locale['article_0003'];
                        }
                    }
                    break;
                case "article_category":
                    BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => self::$locale['article_0004']]);
                    if (isset($_GET['ref']) && $_GET['ref'] == "article_cat_form") {
                        if (!isset($_GET['cat_id'])) {
                            BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => self::$locale['article_0005']]);
                            $articleCatTitle = self::$locale['article_0005'];
                        } else {
                            BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => self::$locale['article_0006']]);
                            $articleCatTitle = self::$locale['article_0006'];
                        }
                    }
                    break;
                case "settings":
                    BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => self::$locale['article_0008']]);
                    break;
                case "submissions":
                    BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => self::$locale['article_0007']]);
                    break;
                default:
            }
        }

        // Handle Tabs
        if (!empty($_GET['ref']) || isset($_GET['submit_id'])) {
            $tab['title'][] = self::$locale['back'];
            $tab['id'][] = "back";
            $tab['icon'][] = "fa fa-fw fa-arrow-left";
        }
        $tab['title'][] = $articleTitle;
        $tab['id'][] = "article";
        $tab['icon'][] = "fa fa-fw fa-file-text";
        $tab['title'][] = $articleCatTitle;
        $tab['id'][] = "article_category";
        $tab['icon'][] = "fa fa-fw fa-folder";
        $tab['title'][] = self::$locale['article_0007']."&nbsp;<span class='badge'>".dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='a'")."</span>";
        $tab['id'][] = "submissions";
        $tab['icon'][] = "fa fa-fw fa-inbox";
        $tab['title'][] = self::$locale['article_0008'];
        $tab['id'][] = "settings";
        $tab['icon'][] = "fa fa-fw fa-cogs";

        // Display Content
        opentable(self::$locale['article_0000']);

        echo opentab($tab, $_GET['section'], "articles_admin", TRUE, "", "section", ['article_display', 'rowstart']);
        switch ($_GET['section']) {
            case "article_category":
                ArticlesCategoryAdmin::getInstance()->displayArticlesAdmin();
                break;
            case "submissions":
                ArticlesSubmissionsAdmin::getInstance()->displayArticlesAdmin();
                break;
            case "settings":
                ArticlesSettingsAdmin::getInstance()->displayArticlesAdmin();
                break;
            default:
                ArticlesAdmin::getInstance()->displayArticlesAdmin();
        }
        echo closetab();
        closetable();
    }
}
