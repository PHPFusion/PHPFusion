<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: homepage.php
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
defined('IN_FUSION') || exit;

PHPFusion\HomePage::setLimit(3); // Here you can change number of items

function display_home($info) {
    if (!empty($info)) {
        // Push News to top
        if (defined('NEWS_EXISTS') && !empty($info[DB_NEWS])) {
            $temp = [DB_NEWS => $info[DB_NEWS]];
            unset($info[DB_NEWS]);
            $info = $temp + $info;
        }

        foreach ($info as $module) {
            echo '<h2>'.$module['blockTitle'].'</h2>';
            if (!empty($module['data'])) {
                echo '<div class="row equal-height">';
                foreach ($module['data'] as $data) {
                    echo '<div class="col-xs-12 col-sm-4 content m-b-10">';
                        echo '<div class="post-item">';

                            if (!empty($data['image'])) {
                                echo '<a href="'.$data['url'].'" class="thumb overflow-hide">';
                                    echo '<img class="img-responsive" src="'.$data['image'].'" alt="'.$data['title'].'">';
                                echo '</a>';
                            }

                            echo '<div class="post-meta">';
                                echo '<h4 class="title"><a href="'.$data['url'].'">'.$data['title'].'</a></h4>';
                                echo '<div class="small m-b-10 overflow-hide">'.$data['meta'].'</div>';
                                echo '<div class="overflow-hide hidden-xs">'.nl2br(trim_text(strip_tags($data['content']), 200)).'</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<div class="m-t-10 m-b-10">'.$module['norecord'].'</div>';
            }
        }
    }
}
