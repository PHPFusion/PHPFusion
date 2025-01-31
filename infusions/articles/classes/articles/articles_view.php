<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: articles_view.php
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

use PHPFusion\OpenGraphArticles;

/**
 * Controller package for if/else
 * Class ArticlesView
 *
 * @package PHPFusion\Articles
 */
class ArticlesView extends Articles {
    public function display_articles() {
        // Display Article
        if (isset($_GET['article_id']) && isnum($_GET['article_id'])) {
            $info = $this->setArticlesItemInfo($_GET['article_id']);
            render_article_item($info);
            OpenGraphArticles::ogArticle($_GET['article_id']);

            // Display Category
        } else if (isset($_GET['cat_id'])) {
            if (isnum($_GET['cat_id'])) {
                $info = $this->setArticlesCatInfo($_GET['cat_id']);
                display_main_articles($info);
                OpenGraphArticles::ogArticleCat($_GET['cat_id']);
            } else {
                redirect(INFUSIONS.'articles/articles.php');
            }
        } else {
            // Display Overview
            $info = $this->setArticlesInfo();
            display_main_articles($info);
        }
    }
}
