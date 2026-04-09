<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: article_admin_view.php
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
namespace PHPFusion\Articles;

class ArticlesAdminView extends ArticlesAdminModel {
    private $allowed_pages = ["article", "article_category", "article_form", "submissions", "settings"];
    private static $locale = [];

    public function display_admin() {
        self::$locale = self::getArticleAdminLocales();

        // Back and Check Section
        if (check_get('section') && get('section') == "back") {
            redirect(clean_request("", ["ref", "section", "article_id", "action", "cat_id", "submit_id"], FALSE));
        }
        $sections = in_array(get('section'), $this->allowed_pages) ? get('section') : $this->allowed_pages[0];

        // Sitetitle
        add_to_title(self::$locale['article_0000']);

        // Handle Breadcrumbs and Titles
        add_breadcrumb(["link" => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink(), "title" => self::$locale['article_0000']]);

        $articleTitle = self::$locale['article_0000'];
        $articleCatTitle = self::$locale['article_0004'];

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

        echo opentab($tab, $sections, "articles_admin", TRUE, "", "section", ['ref', 'action', 'article_display', 'rowstart', 'cat_id', 'submit_id', 'article_id']);
        switch ($sections) {
            case "article_category":
                ArticlesCategoryAdmin::articles()->displayArticlesAdmin();
                break;
            case "submissions":
                ArticlesSubmissionsAdmin::articles()->displayArticlesAdmin();
                break;
            case "settings":
                ArticlesSettingsAdmin::articles()->displayArticlesAdmin();
                break;
            default:
                ArticlesAdmin::articles()->displayArticlesAdmin();
        }
        echo closetab();
        closetable();
    }
}
