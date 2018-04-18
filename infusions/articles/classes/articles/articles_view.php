<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/classes/articles/articles_view.php
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
            $info = $this->set_ArticlesItemInfo($_GET['article_id']);
            render_article_item($info);
            OpenGraphArticles::ogArticle($_GET['article_id']);

            // Display Category
        } else if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
            $info = $this->set_ArticlesCatInfo($_GET['cat_id']);
            display_main_articles($info);
            OpenGraphArticles::ogArticleCat($_GET['cat_id']);
        } else {
            // Display Overview
            $info = $this->set_ArticlesInfo();
            display_main_articles($info);
        }
    }
}
