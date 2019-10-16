<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom-X/classes/Templates/News.inc
| Author: PHP-Fusion Inc
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
namespace AtomXTheme\Templates;

use AtomXTheme\Components;
use AtomXTheme\Core;
use \PHPFusion\News\NewsServer;

class News extends Core {
    public static function display_main_news($info) {
        $locale = fusion_get_locale();
        $locale += self::GetInstance()->SetLocale();
        $settings = fusion_get_settings();
        $news_settings = NewsServer::get_news_settings();

        Components::HidePanels();
        self::RightSide($info);

        if (!empty($info['news_items'])) {
            echo '<div class="row">';
                echo '<div class="col-xs-12 col-sm-6 col-md-7 col-lg-7">';
                    $i = 0;
                    foreach ($info['news_items'] as $id => $data) {
                        $i++;

                        echo '<div class="panel panel-default">';
                            echo '<div class="panel-heading p-0 news-image overflow-hide">'.$data['news_image'].'</div>';
                            echo '<div class="panel-body">';
                                echo '<a href="'.INFUSIONS.'news/news.php?cat_id='.$data['news_cat_id'].'"><small>'.$data['news_cat_name'].'</small></a>';
                                echo ' <span class="text-uppercase text-lighter small">'.showdate($settings['newsdate'], $data['news_date']).'</span>';
                                echo '<h4 class="m-t-0 m-b-0"><a class="text-dark text-underline" href="'.$data['news_link'].'"><b>'.$data['news_subject'].'</b></a></h4>';
                                echo trim_text($data['news_news'], 400);
                            echo '</div>';
                            echo '<div class="panel-footer">';
                                echo '<span><i class="fa fa-window-maximize"></i> <a href="'.$data['news_link'].'">'.$locale['AX9_047'].'</a></span>';

                                if ($data['news_allow_comments'] && fusion_get_settings('comments_enabled') == 1) {
                                    if ($data['news_comments'] < 1) {
                                        echo '<span class="pull-right"><i class="fa fa-commenting-o text-lighter"></i> <a href="'.$data['news_link'].'#comment">'.$locale['AX9_048'].'</a></span>';
                                    } else {
                                        echo '<span class="pull-right"><i class="fa fa-comment'.($data['news_comments'] > 1 ? 's' : '').'-o text-lighter"></i> '.$data['news_display_comments'].'</span>';
                                    }
                                }
                            echo '</div>';
                        echo '</div>';

                        if ($i == 4) {
                            break;
                        }
                    }
                echo '</div>';

                echo '<div class="col-xs-12 col-sm-6 col-md-5 col-lg-5">';
                    $news_items = array_slice($info['news_items'], 4, NULL, TRUE);
                    $i = 0;
                    foreach ($news_items as $id => $data) {
                        echo '<div class="m-t-10 m-b-10"'.($i > 0 ? ' style="border-top: 1px dashed #ccc;"' : '').'>';
                            echo '<h4 class="m-b-10"><a class="text-dark text-underline" href="'.$data['news_link'].'"><b>'.$data['news_subject'].'</b></a></h4>';
                            echo '<div class="clearfix m-b-10">';
                                echo '<div class="pull-left m-t-5 news-image-small overflow-hide" style="height: 100px;"><img class="img-responsive" src="'.$data['news_image_optimized'].'" alt="'.$data['news_subject'].'"/></div>';
                                echo '<a href="'.INFUSIONS.'news/news.php?cat_id='.$data['news_cat_id'].'"><small>'.$data['news_cat_name'].'</small></a>';
                                echo ' <span class="text-uppercase text-lighter small">'.showdate($settings['newsdate'], $data['news_date']).'</span>';
                                echo '<div>'.trim_text($data['news_news'], 100).'</div>';
                            echo '</div>';
                            echo '<div class="small text-uppercase clearfix">';
                                if ($data['news_allow_comments'] && fusion_get_settings('comments_enabled') == 1) {
                                    if ($data['news_comments'] < 1) {
                                        echo '<span><i class="fa fa-commenting-o text-lighter"></i> <a href="'.$data['news_link'].'#comment">'.$locale['AX9_048'].'</a></span>';
                                    } else {
                                        echo '<span><i class="fa fa-comment'.($data['news_comments'] > 1 ? 's' : '').'-o text-lighter"></i> '.$data['news_display_comments'].'</span>';
                                    }
                                }
                                echo '<span class="m-l-10"><i class="fa fa-window-maximize"></i> <a href="'.$data['news_link'].'">'.$locale['AX9_047'].'</a></span>';
                            echo '</div>';
                        echo '</div>';
                        $i++;
                    }
                echo '</div>';
            echo '</div>';

            if ($info['news_total_rows'] > $news_settings['news_pagination']) {
                $type_start = isset($_GET['type']) ? 'type='.$_GET['type'].'&amp;' : '';
                $cat_start = isset($_GET['cat_id']) ? 'cat_id='.$_GET['cat_id'].'&amp;' : '';
                echo '<div class="text-center m-t-10 m-b-10">';
                    echo makepagenav($_GET['rowstart'], $news_settings['news_pagination'], $info['news_total_rows'], 3, INFUSIONS.'news/news.php?'.$cat_start.$type_start);
                echo '</div>';
            }
        } else {
            echo '<div class="well text-center">'.$locale['news_0005'].'</div>';
        }
    }

    public static function render_news_item($info) {
        $locale = fusion_get_locale();
        $locale += self::GetInstance()->SetLocale();
        $data = $info['news_item'];

        Components::HidePanels();

        self::RightSide($info, $data['news_show_ratings']);

        echo '<div class="clearfix">';
            echo '<h1 class="m-t-0 display-inline">'.$data['news_subject'].'</h1>';
            echo '<div class="pull-right">';
                $action = $data['news_admin_actions'];
                if (!empty($action)) {
                    echo '<div class="btn-group">';
                        echo '<a href="'.$data['print_link'].'" class="btn btn-primary btn-sm" title="'.$locale['print'].'" target="_blank"><i class="fa fa-print"></i></a>';
                        echo '<a href="'.$action['edit']['link'].'" class="btn btn-warning btn-sm" title="'.$locale['edit'].'"><i class="fa fa-pencil"></i></a>';
                        echo '<a href="'.$action['delete']['link'].'" class="btn btn-danger btn-sm" title="'.$locale['delete'].'"><i class="fa fa-trash"></i></a>';
                    echo '</div>';
                } else {
                    echo '<a href="'.$data['print_link'].'" class="btn btn-primary btn-sm" title="'.$locale['print'].'" target="_blank"><i class="fa fa-print"></i></a>';
                }
            echo '</div>';
        echo '</div>';

        echo '<h2 class="m-r-20 m-b-20 display-inline"><b>'.format_word($data['news_reads'], $locale['fmt_views']).'</b></h2>';

        if (SOCIAL_SHARE == TRUE) {
            $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            echo '<div class="m-r-5 display-inline">';
                echo social_media_links($url);
            echo '</div>';
        }

        if ($data['news_image_align'] == 'news-img-center') {
            echo '<div class="news-image-main m-t-20 overflow-hide">'.$data['news_image'].'</div>';
        }

        echo '<div class="row m-t-20">';
            echo '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">';
                echo display_avatar($data, '50px', '', TRUE, 'img-rounded pull-right m-l-5');
                echo '<div class="pull-right text-uppercase m-t-5">'.$locale['AX9_050'].profile_link($data['user_id'], $data['user_name'], $data['user_status']).'</div>';
                echo'<div class="pull-right text-uppercase m-t-5 p-t-5" style="border-top: 1px solid #ccc;">'.showdate('newsdate', $data['news_datestamp']).'</div>';
            echo '</div>';

            echo '<div class=" col-xs-12 col-sm-10 col-md-10 col-lg-10">';
                echo '<div class="text-uppercase m-b-20">'.$locale['AX9_051'].'<a href="'.INFUSIONS.'news/news.php?cat_id='.$data['news_cat_id'].'">'.$data['news_cat_name'].'</a></div>';

                if ($data['news_image_align'] == 'pull-left' || $data['news_image_align'] == 'pull-right') {
                    echo '<div class="'.$data['news_image_align'].' m-r-10" style="max-width: 250px;">'.$data['news_image'].'</div>';
                }

                echo $data['news_news'];
                echo !empty($data['news_extended']) ? '<p class="m-t-10">'.$data['news_extended'].'</p>' : '';

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

                echo $data['news_pagenav'] ? '<div class="text-center m-10">'.$data['news_pagenav'].'</div>' : '';
            echo '</div>';
        echo '</div>';

        echo '<hr/>'.$data['news_show_comments'];
    }

    private static function RightSide($info, $additonal = '') {
        $locale = fusion_get_locale();
        $locale += self::GetInstance()->SetLocale();

        ob_start();
        if (!empty($info['news_last_updated'])) {
            echo '<span><strong class="text-dark">'.$locale['news_0008'].':</strong> '.(is_array($info['news_last_updated']) ? showdate('newsdate', $info['news_last_updated'][1]) : $info['news_last_updated']).'</span>';
        }

        $i = 0;
        echo '<ul class="list-style-none">';
            foreach ($info['news_filter'] as $link => $title) {
                $filter_active = (!isset($_GET['type']) && $i == 0) || isset($_GET['type']) && stristr($link, $_GET['type']) ? ' text-dark' : '';
                echo '<li><a href="'.$link.'" class="display-inline'.$filter_active.' m-r-10">'.$title.'</a></li>';
                $i++;
            }
        echo '</ul>';

        echo $additonal;
        echo '<div class="text-uppercase m-b-20" style="background: #121A23;color: #fff;padding: 1px 10px;"><h6><b>'.$locale['news_0009'].'</b></h6></div>';
        echo '<ul class="list-style-none">';
            foreach ($info['news_categories'][0] as $id => $data) {
                $active = isset($_GET['cat_id']) && $_GET['cat_id'] == $id ? ' class="text-dark"' : '';
                echo '<li><a'.$active.' href="'.INFUSIONS.'news/news.php?cat_id='.$id.'">'.$data['name'].'</a></li>';

                if ($id != 0 && $info['news_categories'] != 0) {
                    foreach ($info['news_categories'] as $sub_cats_id => $sub_cats) {
                        foreach ($sub_cats as $sub_cat_id => $sub_cat_data) {
                            if (!empty($sub_cat_data['parent']) && $sub_cat_data['parent'] == $id) {
                                $active = isset($_GET['cat_id']) && $_GET['cat_id'] == $sub_cat_id ? ' class="active"' : '';
                                echo '<li'.$active.'><a class="p-l-10" href="'.INFUSIONS.'news/news.php?cat_id='.$sub_cat_id.'">'.$sub_cat_data['name'].'</a></li>';
                            }
                        }
                    }
                }
            }
        echo '</ul>';

        $result = dbquery("SELECT n.*, nc.*, ni.news_image, count(c.comment_item_id) AS news_comments
            FROM ".DB_NEWS." n
            LEFT JOIN ".DB_NEWS_CATS." nc ON n.news_cat=nc.news_cat_id
            LEFT JOIN ".DB_NEWS_IMAGES." ni ON ni.news_id=n.news_id
            LEFT JOIN ".DB_COMMENTS." c ON (c.comment_item_id = n.news_id AND c.comment_type = 'N')
            ".(multilang_table('NS') ? "WHERE ".in_group('n.news_language', LANGUAGE)." AND " : "WHERE ").groupaccess('n.news_visibility')." AND (news_start='0'||news_start<='".TIME."')
            AND (news_end='0'||news_end>='".TIME."') AND news_draft='0'
            GROUP BY n.news_id
            ORDER BY n.news_reads DESC, n.news_datestamp ASC
            LIMIT 10
        ");

        if (dbrows($result)) {
            echo '<div class="text-uppercase m-t-20 m-b-20" style="background: #121A23;color: #fff;padding: 1px 10px;"><h6><b>'.$locale['AX9_049'].'</b></h6></div>';

            while ($data = dbarray($result)) {
                $image = \PHPFusion\News\News::get_NewsImage($data);

                echo '<div class="panel panel-default">';
                    echo '<div class="panel-heading p-0 news-image-popular overflow-hide">'.$image.'</div>';
                    echo '<div class="panel-body">';
                        echo '<a href="'.INFUSIONS.'news/news.php?cat_id='.$data['news_cat_id'].'"><small>'.$data['news_cat_name'].'</small></a>';
                        echo '<h4 class="m-t-0 m-b-0"><a class="text-dark text-underline" href="'.INFUSIONS.'news/news.php?readmore='.$data['news_id'].'"><b>'.$data['news_subject'].'</b></a></h4>';
                    echo '</div>';
                    echo '<div class="panel-footer clearfix">';
                        echo '<span><i class="fa fa-window-maximize"></i> <a href="'.INFUSIONS.'news/news.php?readmore='.$data['news_id'].'">'.$locale['AX9_047'].'</a></span>';
                        echo '<span class="m-l-5"><i class="fa fa-eye"></i> '.$data['news_reads'].'</span>';

                        if ($data['news_allow_comments'] && fusion_get_settings('comments_enabled') == 1) {
                            if ($data['news_comments'] < 1) {
                                echo '<span class="pull-right"><i class="fa fa-commenting-o text-lighter"></i> <a href="'.INFUSIONS.'news/news.php?readmore='.$data['news_id'].'#comment">'.$locale['AX9_048'].'</a></span>';
                            } else {
                                echo '<span class="pull-right"><i class="fa fa-comment'.($data['news_comments'] > 1 ? 's' : '').'-o text-lighter"></i> <a href="'.INFUSIONS.'news/news.php?readmore='.$data['news_id'].'#comment">'.format_word($data['news_comments'], $locale['fmt_comment']).'</a></span>';
                            }
                        }
                    echo '</div>';
                echo '</div>';
            }
        }

        $html = ob_get_contents();
        ob_end_clean();

        self::SetParam('right_content', $html);
    }
}
