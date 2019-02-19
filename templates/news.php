<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news.php
| Author: PHP Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

if (!function_exists('display_main_news')) {
    /**
     * News Main Page Template
     *
     * @param $info
     */
    function display_main_news($info) {
        $news_settings = \PHPFusion\News\NewsServer::get_news_settings();
        $locale = fusion_get_locale();

        add_to_head("<link href='".INFUSIONS."news/templates/html/news.css' rel='stylesheet'/>\n");
        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>");

        $tpl = \PHPFusion\Template::getInstance('news');
        $tpl->set_template(__DIR__.'/html/news.html');
        $tpl->set_locale(fusion_get_locale());
        $tpl->set_tag('opentable', fusion_get_function('opentable', $locale['news_0004']));
        $tpl->set_tag('closetable', fusion_get_function('closetable'));
        $tpl->set_tag('news_cat_name', $info['news_cat_name']);
        $tpl->set_tag('pagenav', '');

        // Carousel -- make it just sticky
        $ni_html = '';
        $carousel = FALSE;
        if (!empty($info['news_items'])) {

            if ($info['news_total_rows'] > $news_settings['news_pagination']) {
                $type_start = isset($_GET['type']) ? "type=".$_GET['type']."&amp;" : '';
                $cat_start = isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '';
                $tpl->set_tag('pagenav', makepagenav($_GET['rowstart'], $news_settings['news_pagination'], $info['news_total_rows'], 3, INFUSIONS."news/news.php?".$cat_start.$type_start));
            }

            $i = 0;
            $x = 1;
            foreach ($info['news_items'] as $news) {
                $news['news_profile_link'] = profile_link($news['user_id'], $news['user_name'], $news['user_status'], '', FALSE);
                $news['news_news'] = fusion_first_words($news['news_news'], 20);
                $news['news_link'] = INFUSIONS.'news/news.php?readmore='.$news['news_id'];
                $news['news_phpdate'] = date('c', $news['news_date']);
                $news['news_date'] = showdate('newsdate', $news['news_date']);
                //2013-02-14T20:26:08
                // Carousel Item
                if ($news['news_sticky']) {
                    if ($news['news_image_src'] && file_exists($news['news_image_src'])) {
                        $carousel = TRUE;
                        $news['carousel_active'] = ($i == 0 ? ' class="active"' : '');
                        $news['carousel_item_active'] = ($i == 0 ? ' active' : '');
                        $tpl->set_block('carousel_item', $news);
                        $tpl->set_block('carousel_indicators', [
                            'indicator_num'   => $i,
                            'indicator_class' => $news['carousel_active']
                        ]);
                    }
                } else {

                    $news['news_news'] = strip_tags($news['news_news']);

                    // Add support compatibility for render_news
                    if (function_exists('render_news')) {

                        ob_start();
                        render_news($news['news_subject'], $news['news_news'], $news);
                        $ni_html .= ob_get_clean();

                    } else {
                        $news['news_admin_actions'] = '';
                        if ($x > 6) {
                            $x = 1;
                        }
                        $x_logic = (empty($x % 3) or empty($x % 4) ? TRUE : FALSE);
                        if ($x === 6) {
                            $x_logic = FALSE;
                        }
                        $ntpl = \PHPFusion\Template::getInstance('news_item');
                        $ntpl->set_template(__DIR__.'/html/news_item.html');
                        $ntpl->set_locale(fusion_get_locale());
                        $block_name = ($x_logic === TRUE ? 'news_item_lg' : 'news_item_sm');
                        $ntpl->set_block($block_name, $news);
                        $output = $ntpl->get_output();
                        $ni_html .= $output;
                        $x++;
                    }
                }
                $i++;
            }
        } else {
            $ntpl = \PHPFusion\Template::getInstance('news_item');
            $ntpl->set_template(__DIR__.'/html/news_item.html');
            $ntpl->set_locale(fusion_get_locale());
            $ntpl->set_block('no_news', []);
            $output = $ntpl->get_output();
            $ni_html = $output;
        }

        $tpl->set_tag("news_items", $ni_html);

        if ($carousel) {
            $tpl->set_block('carousel_1_start', []);
            $tpl->set_block('carousel_2_start', []);
            $tpl->set_block('carousel_3_start', []);
            $tpl->set_block('carousel_1_end', []);
            $tpl->set_block('carousel_2_end', []);
            $tpl->set_block('carousel_3_end', []);
        }

        $tpl->set_tag('breadcrumb', render_breadcrumbs());
        $tpl->set_tag('last_update', !empty($info['news_last_updated']) ? $info['news_last_updated'] : $locale['na']);

        // Use Navbar
        $i = 1;
        $sec_nav = [];
        foreach ($info['news_filter'] as $link => $title) {
            $sec_nav[0]['fltr_'.$i] = [
                'link_id'     => 'fltr_'.$i,
                'link_name'   => $title,
                'link_active' => ((!isset($_GET['type']) && $i == '0') || isset($_GET['type']) && stristr($link, $_GET['type']) ? 'active' : ''),
                'link_url'    => $link,
            ];
            $i++;
        }

        $nav = [];

        if (!empty($info['news_categories'])) {
            $nav[0]['link_all'] = [
                'link_id'     => 'link_all',
                'link_active' => (!isset($_GET['cat_id']) ? 'active' : ''),
                'link_name'   => $locale['news_0018'],
                'link_url'    => INFUSIONS.'news/news.php',
            ];
            foreach ($info['news_categories'] as $subID => $subData) {
                foreach ($subData as $cat_id => $catData) {
                    $cat_id = $cat_id ?: '012';
                    $nav[$subID][$cat_id] = [
                        'link_id'     => $cat_id,
                        'link_name'   => $catData['name'],
                        'link_url'    => INFUSIONS.'news/news.php?cat_id='.$cat_id,
                        'link_active' => (isset($_GET['cat_id']) && $_GET['cat_id'] == $cat_id ? 'active' : ''),
                    ];
                }
            }
        }

        $tpl->set_tag('menu', \PHPFusion\SiteLinks::setSubLinks([
            'id'              => 'news-nav',
            'navbar_class'    => 'navbar-default',
            'locale'          => fusion_get_locale(),
            'links_per_page'  => 4,
            'grouping'        => 5,
            'callback_data'   => $nav,
            'additional_data' => $sec_nav,
        ])->showSubLinks());

        echo $tpl->get_output();

    }
}

if (!function_exists('render_news_item')) {
    /**
     * News Item Page Template
     *
     * @param $info
     */
    function render_news_item($info) {

        $locale = fusion_get_locale();
        $news_settings = \PHPFusion\News\NewsServer::get_news_settings();
        $data = $info['news_item'];

        add_to_head("<link rel='stylesheet' href='".INFUSIONS."news/templates/html/news.css' type='text/css'>");
        add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
        add_to_footer('<script type="text/javascript">'.jsminify('
            $(document).ready(function() {
                $(".news-image-overlay").colorbox({
                    transition: "elasic",
                    height:"100%",
                    width:"100%",
                    maxWidth:"98%",
                    maxHeight:"98%",
                    scrolling:false,
                    overlayClose:true,
                    close:false,
                    photo:true
               });
            });
            ').'</script>');

        opentable($locale['news_0004']);
        echo render_breadcrumbs();
        echo "<!--news_pre_readmore-->";
        echo "<article class='news-item' style='display:block; width:100%; overflow:hidden;'>\n";
        if (!empty($data['news_admin_actions'])) {
            $admin_actions = $data['news_admin_actions'];
            echo "<div class='btn-group m-l-10 pull-right'>";
            echo "<a class='btn btn-default btn-sm' href='".$admin_actions['edit']['link']."' title='".$admin_actions['edit']['title']."' /><i class='fa fa-pencil'></i> ".$admin_actions['edit']['title']."</a> \n";
            echo "<a class='btn btn-danger btn-sm' href='".$admin_actions['delete']['link']."' title='".$admin_actions['delete']['title']."' /><i class='fa fa-trash'></i> ".$admin_actions['delete']['title']."</a>\n";
            echo "</div>\n";
        }
        echo "<h2 class='text-left m-t-0 m-b-0'>".$data['news_subject']."</h2>\n";
        echo "<div class='news_news text-dark m-t-20 m-b-20 overflow-hide'>\n";
        if ($data['news_image_src']) {
            echo "<a class='news-image-overlay' href='".$data['news_image_src']."'>
            <img class='img-responsive ".$data['news_image_align']." m-r-10' src='".$data['news_image_src']."' alt='".$data['news_subject']."' style='padding:5px; width: 30%; max-height:".$news_settings['news_photo_h']."px; overflow:hidden;' /></a>";
        }

        echo $data['news_news'];
        echo $data['news_extended'];
        echo "</div>\n";
        echo $data['news_pagenav'];

        if (!empty($data['news_gallery'])) {
            echo '<hr/>';
            openside(fusion_get_locale('news_0019')) ?>
            <div class='post-gallery overflow-hide m-b-20'>
                <?php foreach ($data['news_gallery'] as $news_image_id => $news_image) : ?>
                    <div class='post-gallery-item overflow-hide pull-left' style='width: 250px; height: 120px;'>
                        <?php echo colorbox(IMAGES_N.$news_image['news_image'], '', FALSE, 'pull-left') ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php closeside();
        }

        echo "<div class='well m-t-15 text-center'>\n";
        echo "<span class='news-action'><i class='fa fa-user'></i> ".profile_link($data['user_id'], $data['user_name'],
                $data['user_status'])."</span>\n";
        echo "<span class='news-action m-l-10'><i class='fa fa-calendar'></i> ".showdate("newsdate", $data['news_datestamp'])."</span>\n";
        echo "<span class='news-action m-l-10'><i class='fa fa-eye'></i> <span class='text-dark'>".number_format($data['news_reads'])."</span>\n</span>";

        if ($data['news_allow_comments'] && fusion_get_settings('comments_enabled') == 1) {
            echo '<i class="fa fa-comments-o m-l-10"></i> '.$data['news_display_comments'];
        }

        if ($data['news_allow_ratings'] && fusion_get_settings('ratings_enabled') == 1) {
            echo $data['news_display_ratings'];
        }

        echo "<i class='fa fa-print m-l-10'></i> <a title='".$locale['news_0002']."' href='".BASEDIR."print.php?type=N&amp;item_id=".$data['news_id']."' target='_blank'>".$locale['print']."</a>";
        echo "</div>";
        echo "<!--news_sub_readmore-->";
        echo !isset($_GET['readmore']) && $data['news_ext'] == "y" ? "<div class='m-t-20'>\n<a href='".INFUSIONS."news/news.php?readmore=".$data['news_id']."' class='button'>".$locale['news_0001']."</a>\n</div>\n" : "";

        echo $data['news_show_comments'] ? "<hr />".$data['news_show_comments']."\n" : '';
        echo $data['news_show_ratings'] ? "<hr />".$data['news_show_ratings']."\n" : '';

        echo "</article>\n";
        closetable();
    }
}
