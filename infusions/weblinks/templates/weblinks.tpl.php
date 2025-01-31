<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: weblinks.tpl.php
| Author: Core Development Team
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

if (!function_exists('display_main_weblinks')) {
    function display_main_weblinks($info) {
        opentable($info['weblink_tablename']);
        echo render_breadcrumbs();

        echo '<div class="weblinks-index">';

        if (!empty($info['weblink_categories'])) {
            echo '<div class="row">';
            foreach ($info['weblink_categories'][0] as $cat_id => $cat_data) {
                echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 weblink-index-item">
                    <div class="spacer-xs">
                        <h3><a class="display-block" href="'.INFUSIONS."weblinks/weblinks.php?cat_id=".$cat_data['weblink_cat_id'].'">'.$cat_data['weblink_cat_name'].' ('.$cat_data['weblink_count'].')</a></h3>
                        '.$cat_data['weblink_cat_description'];
                if ($cat_id != 0 && $info['weblink_categories'] != 0) {
                    add_to_css('.sub-cats-icon {
                                -webkit-transform: scaleX(-1) rotate(90deg);
                                transform: scaleX(-1) rotate(90deg);
                            }');
                    echo '<div class="m-l-10">';
                    foreach ($info['weblink_categories'] as $sub_cats) {
                        foreach ($sub_cats as $sub_cat_data) {
                            if (!empty($sub_cat_data['weblink_cat_parent']) && $sub_cat_data['weblink_cat_parent'] == $cat_id) {
                                $link = INFUSIONS."weblinks/weblinks.php?cat_id=".$sub_cat_data['weblink_cat_id'];
                                echo '<div class="clearfix">
                                            <h4 class="m-b-5"><i class="fas fa-level-down-alt sub-cats-icon"></i> <a href="'.$link.'">'.$sub_cat_data['weblink_cat_name'].' ('.$sub_cat_data['weblink_count'].')</a></h4>
                                            '.$sub_cat_data['weblink_cat_description'].'
                                        </div>';
                            }
                        }
                    }
                    echo '</div>';
                }
                echo '</div>
                </div>';
            }
            echo '</div>';
        } else {
            echo '<div class="well text-center m-t-15">'.fusion_get_locale('web_0062').'</div>';
        }

        echo '</div>';
        closetable();
    }
}

if (!function_exists('display_weblinks_item')) {
    function display_weblinks_item($info) {
        opentable($info['weblink_tablename']);
        echo render_breadcrumbs();

        echo '<div class="weblink-item">';
        echo '<ul class="list-group-item list-style-none">';
        foreach ($info['weblink_filter'] as $page_link) {
            echo '<li class="display-inline-block m-r-10 '.$page_link['active'].'"><a href="'.$page_link['link'].'" class="display-inline m-r-10 '.$page_link['active'].'">'.$page_link['name'].'</a></li>';
        }
        echo '</ul>';

        if (!empty($info['pagenav'])) {
            echo '<div class="text-right">'.$info['pagenav'].'</div>';
        }

        if (!empty($info['weblink_items'])) {
            echo '<div class="row equal-height">';
            foreach ($info['weblink_items'] as $web_data) {
                $date = showdate('shortdate', $web_data['weblink_datestamp']);

                echo '<div id="weblink-'.$web_data['weblink_id'].'" class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                    <div class="panel panel-default m-b-20">
                        <div class="panel-body">
                            <h4 class="weblink-title panel-title"><i class="fa fa-fw fa-link"></i><a target="_blank" href="'.$web_data['weblinks_url'].'" class="strong">'.$web_data['weblink_name'].'</a></h4>
                            <div class="weblink-text overflow-hide m-t-5">'.$web_data['weblink_description'].'</div>
                            <div class="weblink-category m-t-5"><i class="fa fa-fw fa-folder"></i><a href="'.$web_data['weblinks_cat_url'].'">'.$web_data['weblink_cat_name'].'</a></div>
                        </div>
                        <div class="panel-footer">
                            <i class="fa fa-fw fa-eye"></i> '.$web_data['weblink_count'].'
                            <i class="fa fa-fw fa-clock-o m-l-10"></i> '.$date.'
                            '.(!empty($web_data['admin_actions']) ? "<a href='".$web_data['admin_actions']['edit']['link']."' title='".$web_data['admin_actions']['edit']['title']."'><i class='fa fa-fw fa-pencil m-l-10'></i></a>" : '').'
                            '.(!empty($web_data['admin_actions']) ? "<a href='".$web_data['admin_actions']['delete']['link']."' title='".$web_data['admin_actions']['delete']['title']."'><i class='fa fa-fw fa-trash m-l-10'></i></a>" : '').'
                        </div>
                    </div>
                </div>';
            }
            echo '</div>';
        } else {
            echo '<div class="well text-center m-t-15">'.fusion_get_locale('web_0062').'</div>';
        }

        if (!empty($info['pagenav'])) {
            echo '<div class="text-right">'.$info['pagenav'].'</div>';
        }

        echo '</div>';
        closetable();
    }
}

if (!function_exists('display_weblink_submissions')) {
    function display_weblink_submissions($info) {
        opentable($info['weblink_tablename']);

        if (!empty($info['item'])) {
            echo '<div class="well spacer-xs">'.$info['item']['guidelines'].'</div>';
            echo $info['item']['openform'];
            echo $info['item']['weblink_cat'];
            echo $info['item']['weblink_name'];
            echo $info['item']['weblink_url'];
            echo $info['item']['weblink_language'];
            echo $info['item']['weblink_description'];
            echo $info['item']['weblink_submit'];
            echo closeform();
        }

        if (!empty($info['confirm'])) {
            echo '<div class="well text-center">
                <p class="strong">'.$info['confirm']['title'].'</p>
                <p class="strong">'.$info['confirm']['submit_link'].'</p>
                <p class="strong">'.$info['confirm']['index_link'].'</p>
            </div>';
        }

        if (!empty($info['no_submissions'])) {
            echo '<div class="well text-center">'.$info['no_submissions'].'</div>';
        }

        closetable();
    }
}
