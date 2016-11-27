<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: custom_news.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

if (!function_exists('render_main_news')) {
    /**
     * News Page Template
     * @param $info
     */
    function display_main_news($info) {

        $news_settings = \PHPFusion\News\NewsServer::get_news_settings();
        $locale = fusion_get_locale();


        /* Slideshow */
        $carousel_indicators = '';
        $carousel_item = '';
        $carousel_height = "350";
        $limit_per_showcase = 5;
        $carousel_count = 0;

        if (!empty($info['news_items'])) {

            $showcase_slides = array_chunk($info['news_items'], $limit_per_showcase, TRUE);

            foreach ($showcase_slides as $news_slides) {

                if (!empty($news_slides)) {

                    $item_count = 1;
                    $small_items = array();
                    $small_items_image = array();

                    $carousel_active = $carousel_count == 0 ? 'active' : '';

                    // Uncomment this to get the carousel indicator
                    //$carousel_indicators .= "<li data-target='#news-carousel' data-slide-to='$carousel_count' class='".$carousel_active."'></li>\n";

                    $carousel_count++;

                    foreach ($news_slides as $news_item) {

                        $image_src = !empty($news_item['news_image_src']) && file_exists($news_item['news_image_src']) ? $news_item['news_image_src'] : THEME."images/news.jpg";

                        ob_start();
                        ?>
                        <div class='item-caption overflow-hide'>
                            <label class="label label-news">
                                <?php echo $news_item['news_cat_name'] ?>
                            </label>
                            <span class="label-date">
                                <i class="fa fa-clock-o fa-fw m-r-5"></i>
                                <?php echo showdate('newsdate', $news_item['news_date']) ?>
                            </span>
                            <a class='text-white' href='<?php echo INFUSIONS."news/news.php?readmore=".$news_item['news_id'] ?>'>
                                <h4 class='text-white m-t-10'><?php echo $news_item['news_subject'] ?></h4>
                            </a>
                            <?php if ($news_item['news_allow_comments']) : ?>
                                <span class='m-r-10'><?php echo display_comments($news_item['news_comments'],
                                                                                 INFUSIONS."news/news.php?readmore=".$news_item['news_id']."#comments"); ?></span>
                            <?php endif; ?>

                            <?php if ($news_item['news_allow_ratings']) : ?>
                                <span class='m-r-10'><?php echo display_ratings($news_item['news_sum_rating'],
                                                                                $news_item['news_count_votes'],
                                                                                INFUSIONS."news/news.php?readmore=".$news_item['news_id']."#postrating"); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php
                        $item = ob_get_contents();
                        ob_end_clean();

                        if ($item_count == 1) {
                            // big sized ones
                            $big_item = $item;
                            $big_item_image = $image_src;

                        } else {
                            // small sized ones
                            $small_items[] = $item;
                            $small_items_image[] = $image_src;
                        }

                        $item_count++;
                        if ($item_count == $limit_per_showcase + 1) {
                            $item_count = 1;
                        }
                    }


                    $carousel_item .= "<div class='item ".$carousel_active."'>\n";
                    $carousel_item .= "<div class='col-xs-12 col-sm-6 item-lg' style='height: ".$carousel_height."px; background-image: url($big_item_image); background-size: cover;'>";
                    $carousel_item .= "<div class='item-inner'>\n";
                    $carousel_item .= $big_item;
                    $carousel_item .= "</div>";
                    $carousel_item .= "</div>";

                    $carousel_item .= "<div class='col-xs-6 col-sm-6 p-0'>\n";
                    if (!empty($small_items)) {
                        $i_count = 1;
                        foreach ($small_items as $iCount => $small_item_info) {
                            $carousel_item .= "<div class='col-xs-6 col-sm-6 p-0'>";
                            $carousel_item .= "<div class='item-sm' style='".($i_count > 2 ? "margin-left: 5px; margin-top:5px; height: ".(($carousel_height / 2) - 5)."px;" : "margin-left: 5px; height: ".($carousel_height / 2)."px;")." background-image: url($small_items_image[$iCount]); background-size: cover;'>\n";
                            $carousel_item .= "<div class='item-inner'>\n";
                            $carousel_item .= $small_item_info;
                            $carousel_item .= "</div>\n";
                            $carousel_item .= "</div>\n";
                            $carousel_item .= "</div>";
                            $i_count++;
                        }
                    }
                    $carousel_item .= "</div>\n";
                    $carousel_item .= "</div>\n";
                }
            }
        }

        if ($carousel_count) {

            $carousel_html = "<div id='news-carousel' class='carousel slide m-b-20'  data-interval='20000' data-ride='carousel'>\n";
            if ($carousel_count > 1 && !empty($carousel_indicators)) {
                $carousel_html .= "<ol class='carousel-indicators'>\n";
                $carousel_html .= $carousel_indicators;
                $carousel_html .= "</ol>";
            }
            $carousel_html .= "<div class='carousel-inner' style='height:".$carousel_height."px' role='listbox'>\n";
            $carousel_html .= $carousel_item;
            $carousel_html .= "</div>\n";

            if ($carousel_count > 1) {
                $carousel_html .= "
				<a class='left carousel-control' href='#news-carousel' role='button' data-slide='prev'>
					<span class='fa fa-chevron-left' aria-hidden='true'></span>
					<span class='sr-only'>".$locale['previous']."</span>
			  	</a>
			  	<a class='right carousel-control' href='#news-carousel' role='button' data-slide='next'>
					<span class='fa fa-chevron-right' aria-hidden='true'></span>
					<span class='sr-only'>".$locale['next']."</span>
			  	</a>\n
				";
            }

            $carousel_html .= "</div>\n";

            // Inject into header of Septenary
            \PHPFusion\SeptenaryTheme::Factory()->set_header_html($carousel_html);
        }

        // Process and inject all news categories to Left Panel
        ob_start();
        openside($locale['news_0009']);
        ?>
        <ul>
            <?php if (!empty($info['news_categories'])) :
                foreach ($info['news_categories'] as $cat_id => $cat_data) :
                    echo isset($_GET['cat_id']) && $_GET['cat_id'] == $cat_id ? '' : "<li>\n<a href='".INFUSIONS."news/news.php?cat_id=".$cat_id."'>".$cat_data['name']."</a>\n</li>\n";
                endforeach;
                echo "<li>\n<a href='".INFUSIONS."news/news.php?cat_id=0'>".$locale['news_0006']."</a>\n</li>\n";
            else:
                echo "<li>\n<a href='".INFUSIONS."news/news.php?cat_id=0'>".$locale['news_0006']."</a>\n</li>\n";
            endif;
            ?>
        </ul>
        <?php
        closeside();
        $left_html = ob_get_contents();
        ob_end_clean();
        \PHPFusion\SeptenaryTheme::Factory()->set_left_html($left_html);

        echo render_breadcrumbs();

        // Build filters
        $i = 0;
        foreach ($info['news_filter'] as $link => $title) {
            $tab_title['title'][] = $title;
            $tab_title['id'][] = $i;
            $i++;
        }
        $active_tab = tab_active($tab_title, 0, 'type');
        ?>
        <div id="news_filter_tab">
            <?php echo opentab($tab_title, $active_tab, 'filters', TRUE, '', 'type');

            if (!empty($info['news_items'])) {
                echo "<div class='row'>\n";
                foreach ($info['news_items'] as $i => $news_info) {
                    echo "<div class='col-xs-12 col-sm-6'>\n";
                    echo (isset($_GET['cat_id'])) ? "<!--pre_news_cat_idx-->\n" : "<!--news_prepost_".$i."-->\n";
                    render_news($news_info['news_subject'], $news_info['news_news'], $news_info, FALSE);
                    echo (isset($_GET['cat_id'])) ? "<!--sub_news_cat_idx-->" : "<!--sub_news_idx-->\n";
                    echo "</div>\n";
                }
                echo "</div>\n";

                if ($info['news_total_rows'] > $news_settings['news_pagination']) {
                    $type_start = isset($_GET['type']) ? "type=".$_GET['type']."&amp;" : '';
                    $cat_start = isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '';
                    echo "<div class='text-center m-t-10 m-b-10'>".makepagenav($_GET['rowstart'],
                                                                               $news_settings['news_pagination'],
                                                                               $info['news_total_rows'], 3,
                                                                               INFUSIONS."news/news.php?".$cat_start.$type_start)."</div>\n";
                }
            } else {
                echo "<div class='well text-center'>".$locale['news_0005']."</div>\n";
            }
            echo closetab(); ?>
        </div>
        <?php
    }
}

if (!function_exists('render_news')) {
    /**
     * News Item Container
     * @param      $info
     */
    function render_news($subject, $news, $info) {
        $locale = fusion_get_locale();
        $news_settings = \PHPFusion\News\NewsServer::get_news_settings();
        add_to_jquery("
			$('.news-img-header').hover(
				function() { $(this).closest('.news-article').find('.news-snippet').css({'opacity': 1, 'height': ".$news_settings['news_thumb_h']." }); },
				function() { $(this).closest('.news-article').find('.news-snippet').css({'opacity': 0}); }
			);
			");
        ?>
        <!--news_prepost_<?php echo $info['news_id'] ?>-->
        <article class="news-article">
            <div class="news-img-info" style="height: <?php echo $news_settings['news_thumb_h'] ?>px">
                <?php echo $info['news_image']; ?>
                <div class="news-box-overlay">
                    <a title="<?php echo $info['news_subject'] ?>" href="<?php echo $info['news_image_url'] ?>" rel="bookmark">
                        <h4><?php echo $info['news_subject'] ?></h4>
                        <?php echo trim_text(strip_tags($info['news_news']), 120); ?>
                    </a>
                </div>
            </div>

            <h4 class="news-title-info">
                <a class="strong text-dark" href="<?php echo $info['news_url'] ?>">
                    <?php ($info['news_sticky']) ? "<i class='pull-right fa fa-warning icon-sm'></i>\n" : '' ?>
                    <?php echo $info['news_subject'] ?>
                </a>
            </h4>

            <div class="news-poster-info">
                <div class="pull-left">
                    <?php echo display_avatar($info, '30px', '', FALSE, 'img-circle') ?>
                </div>
                <div class="overflow-hide">
                    <span class="news-author">
                        <?php echo profile_link($info['user_id'], $info['user_name'], $info['user_status'], "text-lighter"); ?>
                    </span>
                    <span class="news-date m-r-10">
                        <i class="fa fa-calendar fa-fw"></i> <?php echo showdate("newsdate", $info['news_date']) ?>
                    </span>
                    <?php if (fusion_get_settings('comments_enabled') && $info['news_display_comments']) : ?>
                        <span class="news-comments"><i class="fa fa-comment-o"></i> <?php echo $info['news_display_comments'] ?></span>
                    <?php endif; ?>
                    <?php if (fusion_get_settings('ratings_enabled') && $info['news_display_ratings']) : ?>
                        <span class="news-ratings"><i class="fa fa-star-o fa-fw"></i> <?php echo $info['news_display_ratings'] ?></span>
                    <?php endif; ?>
                    <span class="news-read">
                        <i class="fa fa-eye fa-fw"></i> <?php echo number_format($info['news_reads']) ?>
                    </span>
                </div>
            </div>

            <div class="news-description-info">
                <?php echo ucwords($locale['in']) ?>
                <?php echo $info['news_cat_name'] ? "<a href='".INFUSIONS."news/news.php?cat_id=".$info['news_cat_id']."'>".$info['news_cat_name']."</a>" : "<a href='".INFUSIONS."news/news.php?cat_id=0&amp;filter=false'>".$locale['news_0006']."</a>&nbsp;";
                ?>
                <br/>
                <?php echo trim_text(strip_tags($info['news_news']), 250) ?>
            </div>

        </article>
        <!--//news_prepost_<?php echo $info['news_id'] ?>-->
        <?php
    }
}
