<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: News.php
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
namespace ThemeFactory\Lib\Modules\Footer;

use PHPFusion\News\NewsView;

class News {

    public function __construct() {
        echo "<h4>".fusion_get_locale('NEWS_001', THEME.'locale/'.LANGUAGE.'.php')."</h4>";

        if (db_exists(DB_PREFIX."news")) :
            // Latest News
            require_once INFUSIONS."news/infusion_db.php";
            require_once NEWS_CLASS."autoloader.php";
            $data = NewsView::News()->get_NewsItem(array("limit" => "0,3", "order" => "news_datestamp DESC"));
            if (!empty($data['news_items'])) : ?>
                <ul class="list-style-none">
                <?php foreach ($data['news_items'] as $news_id => $news_data) : ?>
                    <li class="m-b-20">
                        <div class="pull-left m-r-15" style="width:20%">
                            <div class="display-block overflow-hide" style="position:relative; border-radius: 50%; height: 70px; width: 70px;">
                                <img class="center-xy" style="position:absolute; min-height: 70px; min-width: 70px;"
                                     src="<?php echo $news_data['news_image_optimized'] ?>"
                                     title="<?php echo $news_data['news_subject'] ?>"/>
                            </div>
                        </div>
                        <div class="overflow-hide">
                            <a href="<?php echo $news_data['news_url'] ?>">
                                <?php echo $news_data['news_subject'] ?>
                            </a><br/>
                            <span class="news_date"><?php echo showdate('newsdate', $news_data['news_datestamp']) ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif;
        endif;
    }
}
