<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/classes/news/news_view.php
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
use PHPFusion\OpenGraph;
use PHPFusion\OpenGraphNews;

/**
 * Controller package for if/else
 * Class NewsView
 * @package PHPFusion\News
 */
class NewsView extends News {

    public function display_news() {
        if (isset($_GET['readmore']) && isnum($_GET['readmore'])) {
            // Item Result
            $info = $this->set_NewsItemInfo($_GET['readmore']);
            render_news_item($info);
	        OpenGraphNews::ogNews($_GET['readmore']);
        } elseif (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
            // Category Result
            $info = $this->set_NewsCatInfo($_GET['cat_id']);
            display_main_news($info);
	        OpenGraphNews::ogNewsCat($_GET['cat_id']);
        } else {
            // All Results
            $info = $this->set_NewsInfo();
            display_main_news($info);
        }
    }

}
