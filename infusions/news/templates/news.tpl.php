<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: news.tpl.php
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
        $locale = fusion_get_locale();
        $news_settings = \PHPFusion\News\NewsServer::getNewsSettings();

        echo '<div class="news-index">';
        opentable($locale['news_0004']);
        echo render_breadcrumbs();

        add_to_head('<link rel="stylesheet" href="'.INFUSIONS.'news/templates/css/news.css?v='.filemtime(INFUSIONS.'news/templates/css/news.css').'">');

        if (!empty($info['news_items'])) {
            echo '<div class="row equal-height">';
            foreach ($info['news_items'] as $data) {
                $link = INFUSIONS.'news/news.php?readmore='.$data['news_id'];

                echo '<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 news-item m-b-20"><div>';
                    $thumbnail = !empty($data['news_image_optimized']) ? $data['news_image_optimized'] : get_image('imagenotfound');
                    echo '<div class="overflow-hide item-image"><div class="item-thumbnail" style="background-image: url('.$thumbnail.')">';
                    echo '<a class="img-link" href="'.$link.'"></a>';
                    echo '</div></div>';

                    echo '<div class="post-content">';
                        echo '<div class="m-t-10 m-b-10"><a class="label label-info" href="'.INFUSIONS.'news/news.php?cat_id='.$data['news_cat_id'].'">'.$data['news_cat_name'].'</a></div>';
                        echo '<h3 class="post-title"><a href="'.$link.'">'.$data['news_subject'].'</a></h3>';

                        echo '<div class="post-meta">';
                            echo '<span class="m-r-5"><i class="fa fa-user"></i> '.profile_link($data['user_id'], $data['user_name'], $data['user_status']).'</span>';
                            echo '<span class="m-r-5"><i class="fa fa-clock-o"></i> '.showdate(fusion_get_settings('newsdate'), $data['news_date']).'</span>';
                            echo '<span><i class="fa fa-folder-o"></i> <a href="'.INFUSIONS.'news/news.php?cat_id='.$data['news_cat_id'].'">'.$data['news_cat_name'].'</a></span>';
                        echo '</div>';

                        echo '<div>'.trim_text(strip_tags($data['news_news']), 100).'</div>';

                        echo '<a href="'.$link.'">'.$locale['news_0001'].'</a>';
                    echo '</div>';
                echo '</div></div>';
            }
            echo '</div>';

            if ($info['news_total_rows'] > $news_settings['news_pagination']) {
                $type_start = isset($_GET['type']) ? 'type='.$_GET['type'].'&' : '';
                $cat_start = isset($_GET['cat_id']) ? 'cat_id='.$_GET['cat_id'].'&' : '';
                echo '<div class="text-center m-t-10 m-b-10">';
                echo makepagenav($_GET['rowstart'], $news_settings['news_pagination'], $info['news_total_rows'], 3, INFUSIONS.'news/news.php?'.$cat_start.$type_start);
                echo '</div>';
            }
        } else {
            echo '<div class="text-center">'.$locale['news_0005'].'</div>';
        }

        closetable();
        echo '</div>';

        $side_panel = fusion_get_function('openside', '');
        if (!empty($info['news_last_updated'])) {
            $side_panel .= '<span class="m-r-10"><strong class="text-dark">'.$locale['news_0008'].':</strong> '.$info['news_last_updated'].'</span>';
        }

        if (!empty($info['news_filter'])) {
            $side_panel .= '<ul class="block news-filter">';
            $i = 0;
            foreach ($info['news_filter'] as $link => $title) {
                $filter_active = (!isset($_GET['type']) && $i == 0) || isset($_GET['type']) && stristr($link, $_GET['type']) ? ' text-dark' : '';
                $side_panel .= '<li><a href="'.$link.'" class="display-inline'.$filter_active.' m-r-10">'.$title.'</a></li>';
                $i++;
            }
            $side_panel .= '</ul>';
        }
        $side_panel .= fusion_get_function('closeside');
        \PHPFusion\Panels::addPanel('news_menu_panel', $side_panel, \PHPFusion\Panels::PANEL_RIGHT, iGUEST);
        \PHPFusion\Panels::addPanel('news_menu_panel', display_news_categories($info), \PHPFusion\Panels::PANEL_RIGHT, iGUEST, 9);
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
        $data = $info['news_item'];

        opentable($locale['news_0004']);
        echo render_breadcrumbs();
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
            echo '<a href="'.$data['news_image_src'].'" class="news-image-overlay">';
            $position = $data['news_image_align'] == 'news-img-center' ? 'center-x m-b-10' : $data['news_image_align'];
            $width = $data['news_image_align'] == 'news-img-center' ? '100%' : '200px';
            echo '<img class="img-responsive '.$position.' m-r-10" style="width: '.$width.';" src="'.$data['news_image_src'].'" alt="'.$data['news_subject'].'"/>';
            echo '</a>';
        }

        echo $data['news_news'];
        echo $data['news_extended'];
        echo "</div>\n";
        echo $data['news_pagenav'];

        if (!empty($data['news_gallery']) && count($data['news_gallery']) > 1) {
            echo '<hr/>';
            echo '<h3>'.$locale['news_0019'].'</h3>';

            echo '<div class="overflow-hide m-b-20">';
            foreach ($data['news_gallery'] as $id => $image) {
                echo '<div class="pull-left overflow-hide" style="width: 250px; height: 120px;">';
                echo colorbox(IMAGES_N.$image['news_image'], 'Image #'.$id);
                echo '</div>';
            }
            echo '</div>';
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

        \PHPFusion\Panels::addPanel('news_menu_panel', display_news_categories($info), \PHPFusion\Panels::PANEL_RIGHT, iGUEST, 9);
    }
}

if (!function_exists('display_news_categories')) {
    function display_news_categories($info) {
        $locale = fusion_get_locale();
        ob_start();
        openside($locale['news_0009']);
        echo '<ul class="list-style-none">';
        foreach ($info['news_categories'] as $cat) {
            echo '<li><a'.($cat['active'] ? ' class="text-dark"' : '').' href="'.$cat['link'].'">'.$cat['name'].'</a></li>';

            if (!empty($cat['sub'])) {
                foreach ($cat['sub'] as $sub_cat) {
                    echo '<li><a class="'.($sub_cat['active'] ? 'text-dark ' : '').'p-l-15" href="'.$sub_cat['link'].'">'.$sub_cat['name'].'</a></li>';
                }
            }
        }
        echo '</ul>';
        closeside();

        $str = ob_get_contents();
        ob_end_clean();

        return $str;
    }
}

