<?php
namespace ThemeFactory\Lib\Modules\Footer;

use PHPFusion\News\NewsView;

class News {

    public function __construct() {
        ?>
        <h4>Recent Posts</h4>
        <?php
        if (db_exists(DB_PREFIX."news")) :
            // Latest News
            require_once INFUSIONS."news/infusion_db.php";
            require_once NEWS_CLASS."autoloader.php";
            $data = NewsView::News()->get_NewsItem(array("limit" => "0,3", "order" => "news_datestamp DESC"));
            if (!empty($data['news_items'])) : ?>
                <ul>
                <?php foreach ($data['news_items'] as $news_id => $news_data) : ?>
                    <li class="m-b-20">
                        <div class="pull-left m-r-15" style="width:30%">
                            <div class="display-block" style="position:relative; border-radius: 50%; height: 70px; width: 70px;">
                                <img class="center-x" style="position:absolute; height: 140px; width: 140px;"
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