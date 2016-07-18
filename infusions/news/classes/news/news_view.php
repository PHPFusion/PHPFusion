<?php


namespace PHPFusion\News;

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

        } elseif (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {

            // Category Result
            $info = $this->set_NewsCatInfo($_GET['cat_id']);
            render_main_news($info);

        } else {

            // All Results
            $info = $this->set_NewsInfo();
            render_main_news($info);
        }
    }
}
