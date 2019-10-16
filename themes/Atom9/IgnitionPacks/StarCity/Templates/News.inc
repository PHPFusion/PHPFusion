<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom9/IgnitionPacks/StarCity/Templates/News.inc
| Author: Frederick MC Chan (Chan)
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
namespace Atom9Theme\IgnitionPacks\StarCity\Templates;

use Atom9Theme\Core;
use Atom9Theme\Producer;
use \PHPFusion\News\NewsServer;

class News extends Core {
    public static function display_main_news($info) {
        $locale = fusion_get_locale('', ATOM9_LOCALE);
        $news_settings = NewsServer::get_news_settings();

        self::SetParam('panels', FALSE);

        Producer::Util();

        add_to_head('<link rel="stylesheet" href="'.IGNITION_PACK.'Templates/css/news.css"/>');

        self::HorizontalMenu($info);

        echo '<div class="news-wrapper">';

        if (!empty($info['news_items'])) {
            foreach ($info['news_items'] as $data) {
                echo '<div class="news-container">';
                    echo '<div class="date">'.date('d M y', $data['news_date']).'</div>';
                    echo '<div class="image">';
                        echo $data['news_image'];
                        echo '<div class="actions">';
                            echo '<a href="'.$data['print_link'].'" title="'.$locale['print'].'" target="_blank"><i class="fa fa-print fa-lg"></i></a>';

                            $action = $data['news_admin_actions'];
                            if (!empty($action)) {
                                echo '<a href="'.$action['edit']['link'].'" title="'.$locale['edit'].'"><i class="fa fa-edit fa-lg"></i></a>';
                                echo '<a href="'.$action['delete']['link'].'" title="'.$locale['delete'].'"><i class="fa fa-trash-o fa-lg"></i></a>';
                            }
                        echo '</div>';
                    echo '</div>';

                    echo '<div class="text">';
                        echo $data['news_anchor'];

                        if ($data['news_ext'] == 'y') {
                            echo '<h2><a href="'.$data['news_url'].'">'.$data['news_subject'].'</a></h2>';
                        } else {
                            echo '<h2>'.$data['news_subject'].'</h2>';
                        }

                        echo '<p>';
                            echo trim_text($data['news_news'], 350);
                            echo $data['news_ext'] == 'y' ? ' <a href="'.$data['news_url'].'" class="readmore">'.$locale['A9_104'].'</a>' : '';
                        echo '</p>';

                        echo '<div class="news-stats">';
                            if ($data['news_allow_comments'] && fusion_get_settings('comments_enabled') == 1) {
                                echo '<span class="news-comments"><i class="fa fa-comment-o fa-lg fa-fw"></i> '.format_word($data['news_comments'], $locale['fmt_comment']).'</span>';
                            }

                            echo '<span class="m-l-10 news-reads"><i class="fa fa-eye fa-lg fa-fw"></i> '.format_word($data['news_reads'], $locale['fmt_read']).'</span>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            }

            if ($info['news_total_rows'] > $news_settings['news_pagination']) {
                $type_start = isset($_GET['type']) ? 'type='.$_GET['type'].'&amp;' : '';
                $cat_start = isset($_GET['cat_id']) ? 'cat_id='.$_GET['cat_id'].'&amp;' : '';
                echo '<div class="text-center m-t-10 m-b-10">';
                    echo makepagenav($_GET['rowstart'], $news_settings['news_pagination'], $info['news_total_rows'], 3, INFUSIONS.'news/news.php?'.$cat_start.$type_start);
                echo '</div>';
            }
        } else {
            echo '<div class="text-center p-20">'.$locale['news_0005'].'</div>';
        }

        echo '</div>';
    }

    public static function render_news_item($info) {
        $locale = fusion_get_locale('', ATOM9_LOCALE);
        $data = $info['news_item'];

        self::SetParam('left', FALSE);
        self::SetParam('right', FALSE);

        Producer::Util();

        add_to_head('<link rel="stylesheet" href="'.IGNITION_PACK.'Templates/css/news.css"/>');
        add_to_head('<link rel="stylesheet" type="text/css" href="'.INCLUDES.'jquery/colorbox/colorbox.css"/>');
        add_to_footer('<script type="text/javascript" src="'.INCLUDES.'jquery/colorbox/jquery.colorbox.js"></script>');
        add_to_jquery('$(".news-image-overlay").colorbox({
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
       });');

        self::HorizontalMenu($info);

        echo '<div class="news-wrapper p-20">';
            echo '<div class="clearfix m-b-20">';
                echo '<div class="pull-right">';
                    echo '<a href="'.$data['print_link'].'" title="'.$locale['print'].'" target="_blank"><i class="fa fa-print"></i></a>';
                    if (!empty($data['news_admin_actions'])) {
                        $admin_actions = $data['news_admin_actions'];
                        echo '<a class="m-l-5" href="'.$admin_actions['edit']['link'].'" title="'.$admin_actions['edit']['title'].'"><i class="fa fa-edit"></i></a>';
                        echo '<a class="m-l-5" href="'.$admin_actions['delete']['link'].'" title="'.$admin_actions['delete']['title'].'"><i class="fa fa-trash"></i></a>';
                    }
                echo '</div>';

                echo '<div class="pull-left m-r-5">'.display_avatar($data, '40px', '', TRUE, 'img-circle').'</div>';
                echo '<div class="news-author">';
                    echo profile_link($data['user_id'], $data['user_name'], $data['user_status']);
                    echo ' '.getuserlevel($data['user_level']);
                echo '</div><br/>';
                echo '<small>'.$locale['A9_105'].' '.timer($data['news_date']).' - '.showdate('newsdate', $data['news_date']).'</small>';
            echo '</div>';

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
                echo $data['news_pagenav'] ? '<div class="text-center m-10">'.$data['news_pagenav'].'</div>' : '';
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

            echo $data['news_show_comments'];
            echo $data['news_show_ratings'];
        echo '</div>';
    }

    private static function HorizontalMenu($info) {
        $locale = fusion_get_locale('', ATOM9_LOCALE);

        echo '<nav class="nav news-nav">';
            echo '<div class="main-news-nav">';
            echo '<nav class="navbar navbar-default news-cats">';
                echo '<div class="container-fluid">';
                    echo '<div class="navbar-header">';
                        echo '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#news-cats-nav" aria-expanded="false"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>';
                        echo '<a class="navbar-brand" href="#">'.$locale['news_0009'].'</a>';
                    echo '</div>';

                    echo '<div class="collapse navbar-collapse" id="news-cats-nav">';
                        echo '<ul class="nav navbar-nav">';
                            $info['news_categories'] = dbquery_tree_full(DB_NEWS_CATS, 'news_cat_id', 'news_cat_parent', "WHERE ".in_group('news_cat_language', LANGUAGE));
                            $current_parent = 0;

                            if (isset($_GET['cat_id'])) {
                                $news_index = dbquery_tree(DB_NEWS_CATS, 'news_cat_id', 'news_cat_parent', "WHERE ".in_group('news_cat_language', LANGUAGE));
                                $current_parent = get_parent($news_index, $_GET['cat_id']);
                            }

                            if (!empty($info['news_categories'][0])) {
                                $info['news_categories'][0] = sort_tree($info['news_categories'][0], 'news_cat_name');
                                $limit = 6;
                                $extended = count($info['news_categories'][0]) - $limit;
                                $child_more = FALSE;

                                echo '<li'.(isset($_GET['cat_id']) && isnum($_GET['cat_id']) && $_GET['cat_id'] == 0 ? ' class="active"' : '').'><a href="'.INFUSIONS.'news/news.php?cat_id=0">'.$locale['news_0006'].'</a></li>';

                                foreach ($info['news_categories'][0] as $cat_data) {
                                    $active = (isset($_GET['cat_id']) && isnum($_GET['cat_id']) && ($_GET['cat_id'] == $cat_data['news_cat_id'] || $current_parent == $cat_data['news_cat_id'])) ? ' class="active"' : '';

                                    if ($limit > 0) {
                                        if (isset($info['news_categories'][$cat_data['news_cat_id']])) {
                                            // Sub Cats
                                            echo '<li'.$active.'>';
                                                echo '<a class="dropdown-toggle" data-toggle="dropdown" href="#">'.$cat_data['news_cat_name'].' <span class="caret"></span></a>';
                                                echo '<ul class="dropdown-menu">';
                                                    echo '<li'.$active.'><a href="'.INFUSIONS.'news/news.php?cat_id='.$cat_data['news_cat_id'].'">'.$cat_data['news_cat_name'].'</a></li>';

                                                    foreach ($info['news_categories'][$cat_data['news_cat_id']] as $sub_cat_data) {
                                                        $sub_active = (isset($_GET['cat_id']) && isnum($_GET['cat_id']) && $_GET['cat_id'] == $sub_cat_data['news_cat_id']) ? ' class="active"' : '';
                                                        echo '<li'.$sub_active.'><a href="'.INFUSIONS.'news/news.php?cat_id='.$sub_cat_data['news_cat_id'].'">'.$sub_cat_data['news_cat_name'].'</a></li>';
                                                    }
                                                echo '</ul>';
                                            echo '</li>';
                                        } else {
                                            // Category
                                            echo '<li'.$active.'><a href="'.INFUSIONS.'news/news.php?cat_id='.$cat_data['news_cat_id'].'">'.$cat_data['news_cat_name'].'</a></li>';
                                        }
                                    } else {
                                        // Show More
                                        if ($child_more == FALSE && $extended > 0) {
                                            echo'<li><a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$locale['global_700'].' <span class="caret"></span></a>';
                                            echo '<ul class="dropdown-menu">';
                                            $child_more = TRUE;
                                        }

                                        echo '<li'.$active.'><a href="'.INFUSIONS.'news/news.php?cat_id='.$cat_data['news_cat_id'].'">'.$cat_data['news_cat_name'].'</a></li>';

                                        if ($child_more && $extended == 1) {
                                            echo '</ul></li>';
                                        }
                                        $extended--;
                                    }
                                    $limit--;
                                }
                            }
                        echo '</ul>';
                    echo '</div>';
                echo '</div>';
            echo '</nav>';

            echo '<ul class="sub-nav">';
                echo '<li class="active"><a href="'.INFUSIONS.'news/news.php">'.$locale['A9_103'].'</a></li>';
            echo '</ul>';
            echo '</div>';

            echo '<ul class="news-filters">';
                if (!empty($info['news_last_updated'])) {
                    echo '<span class="m-r-10"><strong class="text-light">'.$locale['news_0008'].':</strong> '.(is_array($info['news_last_updated']) ? showdate('newsdate', $info['news_last_updated'][1]) : $info['news_last_updated']).'</span>';
                }

                echo '<li><strong>'.$locale['show'].':</strong></li>';

                $i = 0;
                foreach ($info['news_filter'] as $link => $title) {
                    $active = (!isset($_GET['type']) && $i == 0) || isset($_GET['type']) && stristr($link, $_GET['type']) ? ' class="active"' : '';
                    echo '<li'.$active.'><a href="'.$link.'">'.$title.'</a></li>';
                    $i++;
                }
            echo '</ul>';
        echo '</nav>';
    }
}
