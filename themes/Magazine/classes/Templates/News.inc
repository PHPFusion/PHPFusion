<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Magazine/classes/Templates/News.inc
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Magazine\Templates;

use Magazine\Core;
use \PHPFusion\News\NewsServer;
use \PHPFusion\OutputHandler;
use \PHPFusion\Panels;

class News extends Core {
        public static function display_main_news($info) {
        $locale = fusion_get_locale('', MG_LOCALE);
        $news_settings = NewsServer::get_news_settings();

        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        Panels::getInstance(TRUE)->hide_panel('LEFT');
        Panels::getInstance(TRUE)->hide_panel('AU_CENTER');
        Panels::getInstance(TRUE)->hide_panel('U_CENTER');
        Panels::getInstance(TRUE)->hide_panel('L_CENTER');
        Panels::getInstance(TRUE)->hide_panel('BL_CENTER');

        echo '<div class="news-header">';
            echo '<h1>'.$locale['news_0004'].'</h1>';

            echo render_breadcrumbs();
        echo '</div>';

        if (!empty($info['news_items'])) {
            echo '<div class="card">';
                echo '<div class="clearfix">';
                    if (!empty($info['news_last_updated'])) {
                        echo '<span class="m-r-10"><strong class="text-dark">'.$locale['news_0008'].':</strong> '.(is_array($info['news_last_updated']) ? showdate('newsdate', $info['news_last_updated'][1]) : $info['news_last_updated']).'</span>';
                    }

                    echo '<span class="m-r-10">';
                        echo '<strong class="text-dark">'.$locale['show'].':</strong> ';
                        $i = 0;
                        foreach ($info['news_filter'] as $link => $title) {
                            $filter_active = (!isset($_GET['type']) && $i == 0) || isset($_GET['type']) && stristr($link, $_GET['type']) ? ' text-dark' : '';
                            echo '<a href="'.$link.'" class="display-inline'.$filter_active.' m-r-10">'.$title.'</a>';
                            $i++;
                        }
                    echo '</span>';

                    echo '<div class="dropdown display-inline pull-right">';
                        echo '<a href="#" id="news-cats" data-toggle="dropdown">'.$locale['news_0009'].' <span class="caret"></span></a>';
                        echo '<ul class="dropdown-menu" aria-labelledby="news-cats">';
                        foreach ($info['news_categories'][0] as $id => $data) {
                            $active = isset($_GET['cat_id']) && $_GET['cat_id'] == $id ? ' class="text-dark"' : '';
                            echo '<li><a'.$active.' href="'.INFUSIONS.'news/news.php?cat_id='.$id.'">'.$data['name'].'</a></li>';

                            if ($id != 0 && $info['news_categories'] != 0) {
                                foreach ($info['news_categories'] as $sub_cats_id => $sub_cats) {
                                    foreach ($sub_cats as $sub_cat_id => $sub_cat_data) {
                                        if (!empty($sub_cat_data['parent']) && $sub_cat_data['parent'] == $id) {
                                            $active = isset($_GET['cat_id']) && $_GET['cat_id'] == $sub_cat_id ? 'text-dark ' : '';
                                            echo '<li><a class="'.$active.'p-l-15" href="'.INFUSIONS.'news/news.php?cat_id='.$sub_cat_id.'">'.$sub_cat_data['name'].'</a></li>';
                                        }
                                    }
                                }
                            }
                        }
                        echo '</ul>';
                    echo '</div>';
                echo '</div>';

                echo '<div class="row equal-height">';
                    foreach ($info['news_items'] as $id => $data) {
                        $link = INFUSIONS.'news/news.php?readmore='.$data['news_id'];

                        echo '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 m-t-15">';
                            echo '<article class="post-item">';
                                echo '<a href="'.$link.'" class="thumb overflow-hide">';
                                    $thumb = !empty($data['news_image_optimized']) ? $data['news_image_optimized'] : get_image('imagenotfound');
                                    echo '<img class="img-responsive" src="'.$thumb.'" alt="'.$data['news_subject'].'"/>';
                                echo '</a>';
                                echo '<div class="post-meta clearfix">';
                                    echo '<div class="post-info">';
                                        echo showdate(fusion_get_settings('newsdate'), $data['news_date']);
                                        echo ' &middot; <a href="'.INFUSIONS.'news/news.php?cat_id='.$data['news_cat_id'].'">'.$data['news_cat_name'].'</a>';
                                    echo '</div>';
                                    echo '<h2 class="title m-t-0"><a href="'.$link.'">'.$data['news_subject'].'</a></h2>';
                                    echo '<p>'.fusion_first_words($data['news_news'], 20).'</p>';
                                    echo '<div class="author">'.ucfirst($locale['by']).' '.profile_link($data['user_id'], $data['user_name'], $data['user_status']).'</div>';
                                    echo '<a href="'.$link.'" class="readmore">'.$locale['MG_101'].'</a>';
                                echo '</div>';
                            echo '</article>';
                        echo '</div>';
                    }
                echo '</div>';

                if ($info['news_total_rows'] > $news_settings['news_pagination']) {
                    $type_start = isset($_GET['type']) ? 'type='.$_GET['type'].'&amp;' : '';
                    $cat_start = isset($_GET['cat_id']) ? 'cat_id='.$_GET['cat_id'].'&amp;' : '';
                    echo '<div class="text-center m-t-10 m-b-10">';
                        echo makepagenav($_GET['rowstart'], $news_settings['news_pagination'], $info['news_total_rows'], 3, INFUSIONS.'news/news.php?'.$cat_start.$type_start);
                    echo '</div>';
                }
            echo '</div>';
        } else {
            echo '<div class="card text-center">'.$locale['news_0005'].'</div>';
        }
    }

    public static function render_news_item($info) {
        $locale = fusion_get_locale('', MG_LOCALE);
        $data = $info['news_item'];

        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        Panels::getInstance(TRUE)->hide_panel('LEFT');
        Panels::getInstance(TRUE)->hide_panel('AU_CENTER');
        Panels::getInstance(TRUE)->hide_panel('U_CENTER');
        Panels::getInstance(TRUE)->hide_panel('L_CENTER');
        Panels::getInstance(TRUE)->hide_panel('BL_CENTER');

        echo '<div class="news-header">';
            echo '<h1>'.$data['news_subject'].'</h1>';

            echo render_breadcrumbs();
        echo '</div>';

        OutputHandler::addToHead('<link rel="stylesheet" type="text/css" href="'.INCLUDES.'jquery/colorbox/colorbox.css"/>');
        OutputHandler::addToFooter('<script type="text/javascript" src="'.INCLUDES.'jquery/colorbox/jquery.colorbox.js"></script>');
        OutputHandler::addToJQuery('
            $(".news-image-overlay").colorbox({
                transition: "elasic",
                height: "100%",
                width: "100%",
                maxWidth: "95%",
                maxHeight: "95%",
                scrolling: false,
                overlayClose: true,
                close: false,
                photo: true,
                onComplete: function(result) {$("#colorbox").live("click", function() {$(this).unbind("click");$.fn.colorbox.close();});}
           });
        ');

        echo '<div class="card"><div class="row">';
            echo '<div class="col-xs-12 col-sm-9">';
                echo '<div class="overflow-hide">';
                    if ($data['news_image_src']) {
                        echo '<a href="'.$data['news_image_src'].'" class="news-image-overlay">';
                            $position = $data['news_image_align'] == 'news-img-center' ? 'center-x m-b-10' : $data['news_image_align'];
                            $width = $data['news_image_align'] == 'news-img-center' ? '100%' : '200px';
                            echo '<img class="img-responsive '.$position.' m-r-10" style="width: '.$width.';" src="'.$data['news_image_src'].'" alt="'.$data['news_subject'].'"/>';
                        echo '</a>';
                    }

                    echo '<div><b>'.$data['news_news'].'</b></div>';
                    echo '<br/>';
                    echo $data['news_extended'];
                    echo !empty($data['news_pagenav']) ? '<div class="text-center m-10">'.$data['news_pagenav'].'</div>' : '';
                echo '</div>';

                if (!empty($data['news_gallery'])) {
                    echo '<hr/>';
                    echo '<h3>'.$locale['news_0019'].'</h3>';

                    echo '<div class="overflow-hide m-b-20">';
                        foreach ($data['news_gallery'] as $id => $image) {
                            echo '<div class="pull-left overflow-hide" style="width: 250px; height: 120px;">';
                                echo colorbox(IMAGES_N.$image['news_image'], 'Image #'.$id, TRUE);
                            echo '</div>';
                        }

                    echo '</div>';
                }

                echo '<div class="text-center clearfix">';
                    $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    echo '<div class="m-r-5 display-inline">';
                        echo social_media_links($url);
                    echo '</div>';
                echo '</div>';

                echo '<div class="well text-center m-t-10 m-b-0">';
                    echo '<span class="m-l-10"><i class="fa fa-user"></i> '.profile_link($data['user_id'], $data['user_name'], $data['user_status']).'</span>';
                    echo '<span class="m-l-10"><i class="fa fa-calendar"></i> '.showdate('newsdate', $data['news_datestamp']).'</span>';
                    echo '<span class="m-l-10"><i class="fa fa-eye"></i> '.number_format($data['news_reads']).'</span>';

                    if ($data['news_allow_comments'] && fusion_get_settings('comments_enabled') == 1) {
                        echo '<span class="m-l-10"><i class="fa fa-comments-o"></i> '.$data['news_display_comments'].'</span>';
                    }

                    if ($data['news_allow_ratings'] && fusion_get_settings('ratings_enabled') == 1) {
                        echo '<span class="m-l-10">'.$data['news_display_ratings'].'</span>';
                    }
                echo '</div>';

                echo $data['news_show_comments'] ? '<div class="card">'.$data['news_show_comments'].'</div>' : '';
                echo $data['news_show_ratings'] ? '<div class="card">'.$data['news_show_ratings'].'</div>' : '';

            echo '</div>';
            echo '<div class="col-xs-12 col-sm-3">';

                $action = $data['news_admin_actions'];
                if (!empty($action)) {
                    echo '<div class="btn-group">';
                        echo '<a href="'.$data['print_link'].'" class="btn btn-primary btn-circle btn-xs" title="'.$locale['print'].'" target="_blank"><i class="fa fa-print"></i></a>';
                        echo '<a href="'.$action['edit']['link'].'" class="btn btn-warning btn-circle btn-xs" title="'.$locale['edit'].'"><i class="fa fa-pencil"></i></a>';
                        echo '<a href="'.$action['delete']['link'].'" class="btn btn-danger btn-circle btn-xs" title="'.$locale['delete'].'"><i class="fa fa-trash"></i></a>';
                    echo '</div>';
                } else {
                    echo '<a href="'.$data['print_link'].'" class="btn btn-primary btn-circle btn-xs" title="'.$locale['print'].'" target="_blank"><i class="fa fa-print"></i></a>';
                }

                echo '<ul class="list-style-none m-t-10">';
                    $i = 0;
                    foreach ($info['news_filter'] as $link => $title) {
                        $filter_active = (!isset($_GET['type']) && $i == 0) || isset($_GET['type']) && stristr($link, $_GET['type']) ? ' class="text-dark"' : '';
                        echo '<li'.$filter_active.'><a href="'.$link.'" class="display-inline m-r-10">'.$title.'</a></li>';
                        $i++;
                    }
                echo '</ul>';

                openside($locale['news_0009'], 'shadow p-t-0');
                echo '<ul class="list-style-none">';
                    foreach ($info['news_categories'][0] as $id => $data) {
                        $active = isset($_GET['cat_id']) && $_GET['cat_id'] == $id ? ' class="text-dark"' : '';
                        echo '<li><a'.$active.' href="'.INFUSIONS.'news/news.php?cat_id='.$id.'">'.$data['name'].'</a></li>';

                        if ($id != 0 && $info['news_categories'] != 0) {
                            foreach ($info['news_categories'] as $sub_cats_id => $sub_cats) {
                                foreach ($sub_cats as $sub_cat_id => $sub_cat_data) {
                                    if (!empty($sub_cat_data['parent']) && $sub_cat_data['parent'] == $id) {
                                        $active = isset($_GET['cat_id']) && $_GET['cat_id'] == $sub_cat_id ? 'text-dark ' : '';
                                        echo '<li><a class="'.$active.'p-l-15" href="'.INFUSIONS.'news/news.php?cat_id='.$sub_cat_id.'">'.$sub_cat_data['name'].'</a></li>';
                                    }
                                }
                            }
                        }
                    }
                echo '</ul>';
                closeside();

                // Popular News
                $result = dbquery("SELECT n.*, nc.*, ni.news_image, count(c.comment_item_id) AS news_comments
                    FROM ".DB_NEWS." n
                    LEFT JOIN ".DB_NEWS_CATS." nc ON n.news_cat=nc.news_cat_id
                    LEFT JOIN ".DB_NEWS_IMAGES." ni ON ni.news_id=n.news_id
                    LEFT JOIN ".DB_COMMENTS." c ON (c.comment_item_id = n.news_id AND c.comment_type = 'N')
                    ".(multilang_table('NS') ? "WHERE ".in_group('news_language', LANGUAGE)." AND " : "WHERE ").groupaccess('news_visibility')." AND (news_start='0'||news_start<='".TIME."')
                    AND (news_end='0'||news_end>='".TIME."') AND news_draft='0'
                    GROUP BY n.news_id
                    ORDER BY n.news_reads DESC, n.news_datestamp ASC
                    LIMIT 6
                ");

                if (dbrows($result)) {
                    openside($locale['MG_102'], 'shadow popular-items');

                    while ($data = dbarray($result)) {
                        $image = \PHPFusion\News\News::get_NewsImage($data);

                        echo '<div class="item clearfix">';
                            echo '<a class="text-dark title display-block" href="'.INFUSIONS.'news/news.php?readmore='.$data['news_id'].'">'.$image.'</a>';

                            echo '<div class="item-content">';
                                echo '<a class="text-dark title display-block" href="'.INFUSIONS.'news/news.php?readmore='.$data['news_id'].'"><b>'.$data['news_subject'].'</b></a>';
                                echo showdate('newsdate', $data['news_datestamp']).' | ';
                                echo '<a href="'.INFUSIONS.'news/news.php?cat_id='.$data['news_cat_id'].'">'.$data['news_cat_name'].'</a>';
                                echo '<div><i class="fa fa-eye"></i> '.$data['news_reads'].'</div>';
                            echo '</div>';
                        echo '</div>';
                    }

                    closeside();
                }
            echo '</div>';

        echo '</div></div>';
    }
}
