<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom9/IgnitionPacks/StarCity/Templates/Downloads.inc
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

class Downloads extends Core {
    public static function render_downloads($info) {
        $locale = fusion_get_locale('', ATOM9_LOCALE);
        self::SetParam('panels', FALSE);

        Producer::Util();

        add_to_head('<link rel="stylesheet" href="'.IGNITION_PACK.'Templates/css/downloads.css"/>');

        echo '<div class="downloads">';
            echo '<div class="heading filter clearfix">';
                echo '<div class="btn-group">';
                    echo '<a class="btn btn-default" href="'.DOWNLOADS.'downloads.php"><i class="fa fa-home"></i> '.$locale['download_1001'].'</a>';
                    $icons = [
                        'download' => 'cloud-download',
                        'recent'   => 'bookmark',
                        'comments' => 'comments',
                        'ratings'  => 'star-o'
                    ];
                    foreach ($info['download_filter'] as $filter_key => $filter) {
                        $active = isset($_GET['type']) && $_GET['type'] === $filter_key ? ' active' : '';
                        echo '<a class="btn btn-default'.$active.'" href="'.$filter['link'].'"><i class="fa fa-'.$icons[$filter_key].'"></i> '.$filter['title'].'</a>';
                    }
                echo '</div>';

                echo '<div class="dropdown pull-right">';
                    echo '<button class="btn btn-default dropdown-toggle" type="button" id="categories" data-toggle="dropdown">'.$locale['A9_106'].' <span class="caret"></span></button>';
                    echo '<ul class="dropdown-menu" aria-labelledby="categories">';
                        $download_cat_menu = self::display_cat_menu($info['download_categories']);
                        echo !empty($download_cat_menu) ? $download_cat_menu : '<li>'.$locale['download_3001'].'</li>';
                    echo '</ul>';
                echo '</div>';
            echo '</div>';

            if (isset($_GET['download_id']) && !empty($info['download_item'])) {
                self::display_download_item($info);
            } else {
                self::display_download_index($info);
            }
        echo '</div>';
    }

    private static function display_download_index($info) {
        $locale = fusion_get_locale('', ATOM9_LOCALE);
        $dl_settings = get_settings('downloads');

        if (!empty($info['download_item'])) {
            echo !empty($info['download_nav']) ? '<div class="heading text-center">'.$info['download_nav'].'</div>' : '';

            echo '<div class="p-20 p-b-0"><div class="row equal-height">';
                foreach ($info['download_item'] as $download_id => $data) {
                    echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-2">';
                        echo '<div class="download-item">';
                            if ($dl_settings['download_screenshot'] == 1) {
                                echo '<a href="'.$data['download_link'].'" class="thumb overflow-hide">';
                                    if ($data['download_thumb'] && file_exists($data['download_thumb'])) {
                                        echo '<img style="width: 155px;height: 155px;" class="img-responsive" src="'.$data['download_thumb'].'" alt="'.$data['download_title'].'"/>';
                                    } else {
                                        echo get_image('imagenotfound', $data['download_title'], 'width: 155px;height: 155px;');
                                    }
                                echo '</a>';
                            }
                            echo '<div class="body">';
                                echo '<a href="'.$data['download_link'].'" class="display-block"><b>'.trim_text($data['download_title'], 20).'</b></a>';
                                echo '<small>'.trim_text(strip_tags($data['download_description_short']), 90).'</small>';
                            echo '</div>';
                            echo '<div class="footer clearfix overflow-hide">';
                                echo '<a class="btn btn-default btn-sm" href="'.$data['download_link'].'">'.$locale['A9_107'].'</a>';
                                echo '<a class="btn btn-primary btn-sm pull-right" href="'.$data['download_file_link'].'">'.$locale['A9_108'].'</a>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }
            echo '</div></div>';

            if (!empty($info['download_nav'])) {
                $dl_settings['download_pagination'] = !empty($dl_settings['download_pagination']) ? $dl_settings['download_pagination'] : 15;

                echo '<div class="footer text-center">';
                    $page_nav_link = (!empty($_GET['type']) ? '?type='.$_GET['type'].'&amp;' : '');
                    if (!empty($_GET['cat_id']) && isnum($_GET['cat_id'])) {
                        $page_nav_link = INFUSIONS.'downloads/downloads.php?cat_id='.$_GET['cat_id'].(!empty($_GET['type']) ? '&amp;type='.$_GET['type'] : '').'&amp;';
                    } else if (!empty($_GET['author']) && isnum($_GET['author'])) {
                        $page_nav_link = INFUSIONS.'downloads/downloads.php?author='.$_GET['author'].'&amp;';
                    }

                    $pagenav = makepagenav($_GET['rowstart'], $dl_settings['download_pagination'], $info['download_max_rows'], 3, $page_nav_link, 'rowstart', TRUE);

                    echo strtr($pagenav, [
                        'btn-block' => '',
                        '\'>'       => '\'><i class="fa fa-refresh"></i> '
                    ]);
                echo '</div>';
            }
        } else {
            echo '<div class="heading text-center">'.$locale['download_3000'].'</div>';
        }
    }

    private static function display_download_item($info) {
        $locale = fusion_get_locale('', ATOM9_LOCALE);
        $dl_settings = get_settings('downloads');
        $data = $info['download_item'];

        echo '<div class="heading p-0">';
            echo '<div class="row equal-height">';
                echo '<div class="col-xs-12 col-sm-6"><div class="download-left p-15 p-r-0">';
                    if ($dl_settings['download_screenshot'] == 1 && $data['download_image'] && file_exists(DOWNLOADS.'images/'.$data['download_image'])) {
                        echo '<img src="'.DOWNLOADS.'images/'.$data['download_image'].'" alt="'.$data['download_title'].'" class="img-responsive"/>';
                    }

                    echo '<div class="dl-desc">';
                        echo '<div class="m-t-20">'.$data['download_description'].'</div>';
                        echo '<a href="'.$data['download_file_link'].'" class="btn btn-primary btn-block m-t-10"><i class="fa fa-download"></i> '.$locale['download_1007'].($data['download_filesize'] ? ' ('.$data['download_filesize'].')' : '').'</a>';
                    echo '</div>';
                echo '</div></div>';

                echo '<div class="col-xs-12 col-sm-6"><div style="background: rgba(0, 0, 0, 0.3);height: 100%;" class="download-right p-15 p-l-0">';
                    echo '<div class="clearfix m-l-15 m-b-20">';
                        if ($data['admin_link']) {
                            $admin_actions = $data['admin_link'];
                            echo '<div class="btn-group pull-right">';
                                echo '<a class="btn btn-default" href="'.$admin_actions['edit'].'"><i class="fa fa-pencil"></i> '.$locale['edit'].'</a>';
                                echo '<a class="btn btn-danger " href="'.$admin_actions['delete'].'"><i class="fa fa-trash"></i> '.$locale['delete'].'</a>';
                            echo '</div>';
                        }
                        echo '<h4>'.$data['download_title'].'</h4>';

                    echo '</div>';

                    echo '<div class="dl-desc">';
                        echo $data['download_description_short'];
                        echo '<div class="m-t-10">'.$data['download_post_time2'].' '.$data['download_post_cat'].'</div>';

                        echo '<p class="m-t-20 m-b-10">'.$locale['A9_109'].'</p>';
                        echo '<div class="description">';
                            echo '<small class="display-block"><b>'.$locale['download_1021'].': </b>'.$data['download_post_time'].'</small>';
                            echo '<small class="display-block"><b>'.$locale['A9_110'].': </b>'.profile_link($data['user_id'], $data['user_name'], $data['user_status'], 'profile-link').'</small>';
                            echo '<small class="display-block"><b>'.$locale['download_1017'].': </b>'.$data['download_homepage'].'</small>';
                            echo '<small class="display-block"><b>'.$locale['download_1015'].': </b>'.$data['download_copyright'].'</small>';
                        echo '</div>';

                        echo '<p class="m-t-20 m-b-10">'.$locale['download_1010'].'</p>';
                        echo '<div class="description">';
                            echo '<small class="display-block"><b>'.$locale['download_1011'].': </b>'.$data['download_version'].'</small>';
                            echo '<small class="display-block"><b>'.$locale['download_1014'].': </b>'.$data['download_os'].'</small>';
                            echo '<small class="display-block"><b>'.$locale['download_1013'].': </b>'.$data['download_license'].'</small>';
                            echo '<small class="display-block"><b>'.$locale['download_1012'].': </b>'.$data['download_count'].'</small>';
                        echo '</div>';
                    echo '</div>';
                echo '</div></div>';
            echo '</div>';
        echo '</div>';

        echo '<div class="footer">';
            echo $data['download_show_ratings'];
            echo $data['download_show_comments'];
        echo '</div>';
    }

    private static function display_cat_menu($info, $cat_id = 0) {
        $html = '';

        if (!empty($info[$cat_id])) {
            foreach ($info[$cat_id] as $download_cat_id => $cdata) {
                $active = !empty($_GET['cat_id']) && $_GET['cat_id'] == $download_cat_id ? TRUE : FALSE;
                $active = $active ? ' class="active"' : '';
                $html .= '<li'.$active.'><a href="'.DOWNLOADS.'downloads.php?cat_id='.$download_cat_id.'">'.$cdata['download_cat_name'].'</a></li>';

                if (!empty($info[$download_cat_id])) {
                    $html .= self::display_cat_menu($info, $download_cat_id);
                }
            }
        }

        return $html;
    }
}
