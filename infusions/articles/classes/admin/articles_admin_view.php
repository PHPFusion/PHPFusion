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

    private $allowed_pages = array("article", "article_category", "article_form", "submissions", "settings");

    public function display_admin() {
        $this->locale = self::get_articleAdminLocale();

        // Back and Check Section
        if (isset($_GET['section']) && $_GET['section'] == "back") {
            redirect(clean_request("", array("ref", "section", "article_id", "action", "cat_id", "submit_id"), FALSE));
        }
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $this->allowed_pages) ? $_GET['section'] : $this->allowed_pages[0];

        // Sitetitle
        add_to_title($this->locale['article_0000']);

        // Handle Breadcrumbs and Titles
        BreadCrumbs::getInstance()->addBreadCrumb(array("link" => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink(), "title" => $this->locale['article_0000']));

        $articleTitle = $this->locale['article_0001'];
        $articleCatTitle = $this->locale['article_0004'];

        if ($_GET['section'] == "article") {
            if (isset($_GET['ref']) && $_GET['ref'] == "article_form") {
                if (!isset($_GET['article_id'])) {
                    BreadCrumbs::getInstance()->addBreadCrumb(array("link" => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;section=article&amp;ref=article_form", "title" => $this->locale['article_0002']));
                    $articleTitle = $this->locale['article_0002'];
                } else {
                    BreadCrumbs::getInstance()->addBreadCrumb(array("link" => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;section=article&amp;ref=article_form", "title" => $this->locale['article_0003']));
                    $articleTitle = $this->locale['article_0003'];
                }
            }
        }

        if ($_GET['section'] == "article_category") {
            BreadCrumbs::getInstance()->addBreadCrumb(array("link" => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;section=article_category", "title" => $this->locale['article_0004']));
            if (isset($_GET['ref']) && $_GET['ref'] == "article_cat_form") {
                if (!isset($_GET['cat_id'])) {
                    BreadCrumbs::getInstance()->addBreadCrumb(array("link" => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;section=article_category&amp;ref=article_cat_form", "title" => $this->locale['article_0005']));
                    $articleCatTitle = $this->locale['article_0005'];
                } else {
                    BreadCrumbs::getInstance()->addBreadCrumb(array("link" => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;section=article_category&amp;ref=article_cat_form", "title" => $this->locale['article_0006']));
                    $articleCatTitle = $this->locale['article_0006'];
                }
            }
        }

        // Handle Tabs
        if (!empty($_GET['ref']) || isset($_GET['submit_id'])) {
            $master_title['title'][] = $this->locale['back'];
            $master_title['id'][] = "back";
            $master_title['icon'][] = "fa fa-fw fa-arrow-left";
        }
        $master_title['title'][] = $articleTitle;
        $master_title['id'][] = "article";
        $master_title['icon'][] = "fa fa-fw fa-file-text";
        $master_title['title'][] = $articleCatTitle;
        $master_title['id'][] = "article_category";
        $master_title['icon'][] = "fa fa-fw fa-folder";
        $master_title['title'][] = $this->locale['article_0007']."&nbsp;<span class='badge'>".dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='a'")."</span>";
        $master_title['id'][] = "submissions";
        $master_title['icon'][] = "fa fa-fw fa-inbox";
        $master_title['title'][] = $this->locale['article_0008'];
        $master_title['id'][] = "settings";
        $master_title['icon'][] = "fa fa-fw fa-cogs";

        // Display Content
        opentable($this->locale['article_0000']);

        echo opentab($master_title, $_GET['section'], "articles_admin", TRUE, "", "section");
        switch ($_GET['section']) {
            case "article_category":
                ArticlesCategoryAdmin::getInstance()->displayArticlesAdmin();
                break;
            case "submissions":
                ArticlesSubmissionsAdmin::getInstance()->displayArticlesAdmin();
                BreadCrumbs::getInstance()->addBreadCrumb(array("link" => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;section=submissions", "title" => $this->locale['article_0007']));
                break;
            case "settings":
                ArticlesSettingsAdmin::getInstance()->displayArticlesAdmin();
                BreadCrumbs::getInstance()->addBreadCrumb(array("link" => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;section=settings", "title" => $this->locale['article_0008']));
                break;
            default:
                ArticlesAdmin::getInstance()->displayArticlesAdmin();
        }
        echo closetab();
        closetable();
    }
}
