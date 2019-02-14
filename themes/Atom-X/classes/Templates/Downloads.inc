<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom-X/classes/Templates/Downloads.inc
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

use AtomXTheme\Core;
use \PHPFusion\Panels;

class Downloads extends Core {
    public static function render_downloads($info) {
        $locale = fusion_get_locale();
        $locale += self::GetInstance()->SetLocale();

        Panels::getInstance(TRUE)->hide_panel('LEFT');
        Panels::getInstance(TRUE)->hide_panel('RIGHT');

        $header = '<ul class="menu">';
            $header .= '<li><a href="'.DOWNLOADS.'downloads.php">'.$locale['download_1001'].'</a></li>';
            $download_cat_menu = self::display_cat_menu($info['download_categories']);

            if (!empty($download_cat_menu)) {
                $header .= '<li class="dropdown">';
                    $header .= '<a class="dropdown-toggle" data-toggle="dropdown" href="#">'.$locale['AX9_055'].' <span class="caret"></span></a>';
                    $header .= '<ul class="dropdown-menu">';
                        $header .= $download_cat_menu;
                    $header .= '</ul>';
                $header .= '</li>';
            }

            foreach ($info['download_filter'] as $key => $filter) {
                $active = isset($_GET['type']) && $_GET['type'] === $key ? ' class="active"' : '';
                $header .= '<li'.$active.'><a href="'.$filter['link'].'">'.$filter['title'].'</a></li>';
            }
        $header .= '</ul>';

        self::SetParam('section_header', $header);
        self::SetParam('section_header_class', 'atom-subnav');

        if (isset($_GET['download_id']) && !empty($info['download_item'])) {
            self::display_download_item($info);
        } else {
            self::display_download_index($info);
        }
    }

    private static function display_download_index($info) {
        $locale = fusion_get_locale();
        $dl_settings = get_settings('downloads');

        if (!empty($info['download_cat_description'])) {
            echo '<div class="display-block p-10">'.$info['download_cat_description'].'</div><hr/>';
        }

        if (!empty($info['download_item'])) {
            echo '<div class="row m-b-10 m-l-0 m-r-0">';
                foreach ($info['download_item'] as $download_id => $data) {
                    echo '<div class="col-xs-6 col-sm-3 col-md-3 col-lg-2 p-l-5 p-r-5" style="height: 230px;">';
                        echo '<div class="item-container">';
                            $link = DOWNLOADS.'downloads.php?cat_id='.$data['download_cat_id'].'&download_id='.$data['download_id'];

                            echo '<h3 class="m-t-0 m-b-0 text-center"><a style="font-weight: bold;font-size: 13px;" href="'.$link.'">'.trimlink($data['download_title'], 20).'</a></h3>';
                            if ($dl_settings['download_screenshot'] == 1) {
                                echo '<div class="item-image-holder"><a href="'.$link.'">';
                                    if ($data['download_thumb'] && file_exists($data['download_thumb'])) {
                                        echo '<img src="'.$data['download_thumb'].'" alt="'.$data['download_title'].'" style="height: 100px;">';
                                    } else {
                                        echo get_image('imagenotfound', $data['download_title'], 'height: 100px;');
                                    }
                                echo '</a></div>';
                            }

                            echo '<div class="text-center">'.$data['download_count'].'</div>';

                            echo '<div class="p-5"><a href="'.$link.'" class="btn btn-primary btn-sm btn-block"><i class="fa fa-download fa-fw"></i> '.$locale['download_1007'].'</a></div>';
                        echo '</div>';
                    echo '</div>';
                }
            echo '</div>';

            echo !empty($info['download_nav']) ? '<div class="text-center m-b-20">'.$info['download_nav'].'</div>' : '';
        } else {
            echo '<div class="card text-center">'.$locale['download_3000'].'</div>';
        }
    }

    private static function display_download_item($info) {
        $locale = fusion_get_locale();
        $locale += self::GetInstance()->SetLocale();
        $dl_settings = get_settings('downloads');
        $data = $info['download_item'];

        echo '<div class="row">';
            $grid = $data['download_allow_ratings'] && fusion_get_settings('ratings_enabled') == 1 ? 8 : 12;
            echo '<div class="col-xs-12 col-sm-'.$grid.' col-md-'.$grid.' col-lg-'.$grid.'">';
                echo '<div class="row">';
                    if ($dl_settings['download_screenshot'] == 1 && $data['download_image'] && file_exists(DOWNLOADS.'images/'.$data['download_image'])) {
                        echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">';
                            echo '<div style="border: 1px solid #ddd;border-radius: 6px;padding: 5px;">';
                                echo '<img src="'.DOWNLOADS.'images/'.$data['download_image'].'" alt="'.$data['download_title'].'" class="img-responsive"/>';
                            echo '</div>';
                        echo '</div>';

                        $grid = 9;
                    } else {
                        $grid = 12;
                    }

                    echo '<div class="col-xs-12 col-sm-'.$grid.' col-md-'.$grid.' col-lg-'.$grid.'">';
                        echo '<h2 class="m-t-0 m-b-0"><strong>'.$data['download_title'].'</strong></h2>';
                        echo '<strong>'.$locale['global_050'].' </strong>'.profile_link($data['user_id'], $data['user_name'], $data['user_status']).'<br/>';
                        echo '<strong>'.$locale['download_1017'].' </strong>'.$data['download_homepage'].'<br/>';
                        echo '<strong>'.$locale['AX9_051'].' </strong> <a href="'.DOWNLOADS.'downloads.php?cat_id='.$data['download_cat_id'].'">'.$data['download_cat_name'].'</a><br/>';

                        echo '<a href="'.$data['download_file_link'].'" class="btn btn-primary m-t-10"><i class="fa fa-download"></i> '.$locale['download_1007'].'</a>';

                        if ($data['admin_link']) {
                            $admin_actions = $data['admin_link'];
                            echo '<div class="btn-group m-t-10 m-l-5">';
                                echo '<a class="btn btn-default" href="'.$admin_actions['edit'].'"><i class="fa fa-pencil"></i> '.$locale['edit'].'</a>';
                                echo '<a class="btn btn-danger " href="'.$admin_actions['delete'].'"><i class="fa fa-trash"></i> '.$locale['delete'].'</a>';
                            echo '</div>';
                        }

                    echo '</div>';
                echo '</div>';

                echo '<div class="p-10 m-t-20 m-b-20" style="border: 1px solid #ddd;">';
                    echo '<h4><strong>'.$locale['AX9_052'].'</strong></h4><br/>';
                    echo $data['download_description_short'];
                    if ($data['download_description']) {
                        echo '<hr/>';
                        echo $data['download_description'];
                    }
                echo '</div>';

                if ($dl_settings['download_screenshot'] && $data['download_image'] && file_exists(DOWNLOADS.'images/'.$data['download_image']))  {
                    echo '<div class="p-10 m-t-20 m-b-20" style="border: 1px solid #ddd;">';
                        echo '<h4><strong>'.$locale['AX9_053'].'</strong></h4><br/>';
                        echo '<img src="'.DOWNLOADS.'images/'.$data['download_image'].'" alt="'.$data['download_title'].'" class="img-responsive"/>';
                    echo '</div>';
                }

            echo '</div>';

            if ($data['download_allow_ratings'] && fusion_get_settings('ratings_enabled') == 1) {
                echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">';
                    echo $data['download_show_ratings'];
                echo '</div>';
            }
        echo '</div>';

        echo '<div class="m-b-20">';
            echo '<h4 class="m-b-20"><b>'.$locale['AX9_054'].'</b></h4>';
            echo '<div class="row">';
                echo '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">';
                    echo '<h5 class="m-b-5"><strong>'.$locale['download_1021'].'</strong></h5><p class="m-b-20">'.$data['download_post_time'].'</p>';
                    echo '<h5 class="m-b-5"><strong>'.$locale['global_050'].'</strong></h5><p>'.profile_link($data['user_id'], $data['user_name'], $data['user_status']).'</p>';
                echo '</div>';

                echo '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">';
                    echo '<h5 class="m-b-5"><strong>'.$locale['download_1012'].'</strong></h5><p class="m-b-20">'.$data['download_count'].'</p>';
                    echo '<h5 class="m-b-5"><strong>'.$locale['download_1014'].'</strong></h5><p>'.$data['download_os'].'</p>';
                echo '</div>';

                echo '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">';
                    echo '<h5 class="m-b-5"><strong>'.$locale['download_1011'].'</strong></h5><p class="m-b-20">'.$data['download_version'].'</p>';
                    echo '<h5 class="m-b-5"><strong>'.$locale['download_1020'].'</strong></h5><p>'.(!empty($data['download_filesize']) ? $data['download_filesize'] : $locale['na']).'</p>';
                echo '</div>';

                echo '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">';
                    echo '<h5 class="m-b-5"><strong>'.$locale['download_1015'].'</strong></h5><p class="m-b-20">'.$data['download_copyright'].'</p>';
                    echo '<h5 class="m-b-5"><strong>'.$locale['download_1013'].'</strong></h5><p>'.$data['download_license'].'</p>';
                echo '</div>';
            echo '</div>';
        echo '</div>';

        echo $data['download_show_comments'];
    }

    private static function display_cat_menu($info, $cat_id = 0, $level = 0) {
        $html = '';

        if (!empty($info[$cat_id])) {
            foreach ($info[$cat_id] as $download_cat_id => $cdata) {
                $active = !empty($_GET['cat_id']) && $_GET['cat_id'] == $download_cat_id ? TRUE : FALSE;
                $active = $active ? ' class="active"' : '';
                $html .= str_repeat('&nbsp;', $level);
                $html .= '<li'.$active.'><a href="'.DOWNLOADS.'downloads.php?cat_id='.$download_cat_id.'">'.$cdata['download_cat_name'].'</a></li>';

                if (!empty($info[$download_cat_id])) {
                    $html .= self::display_cat_menu($info, $download_cat_id, $level + 1);
                }
            }
        }

        return $html;
    }
}
