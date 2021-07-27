<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: homepage.tpl.php
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
/**
 * Show home modules info
 */
if (!function_exists('display_home')) {
    function display_home($info) {
        $locale = fusion_get_locale();

        if (!empty($info)) {
            add_to_css('
                .item {
                    padding: 0;
                    height: inherit;
                }

                .item .thumb {
                    margin: 0;
                    padding: 0;
                    border: 0;
                    font: inherit;
                    vertical-align: baseline;
                    display: block;
                    float: left;
                    height: 120px;
                    overflow: hidden;
                    margin-right: 10px;
                }

                .item .thumb img {
                    vertical-align: middle;
                    object-fit: contain;
                    width: 100%;
                    max-width: 140px;
                    -webkit-transform: scale(1.5);
                    -ms-transform: scale(1.5);
                    -o-transform: scale(1.5);
                    transform: scale(1.5);
                    margin-top: 10px;
                }

                @media (min-width: 900px) {
                    .item .thumb {
                        float: inherit;
                        height: 150px;
                        margin-right: inherit;
                    }
                    .item .thumb img {
                        max-width: 100%;
                        max-height: 100%;
                        -webkit-transform: scale(1.5);
                        -ms-transform: scale(1.5);
                        -o-transform: scale(1.5);
                        transform: scale(1.5);
                        margin-top: 15px;
                    }
                    .item .post .meta {
                        margin: 0;
                        padding: 3px 0 10px;
                        font-size: 12px;
                    }
                }
            ');

            // Push News to top
            if (defined('NEWS_EXISTS') && !empty($info[DB_NEWS])) {
                $temp = [DB_NEWS => $info[DB_NEWS]];
                unset($info[DB_NEWS]);
                $info = $temp + $info;
            }

            foreach ($info as $module) {
                opentable($module['blockTitle']);
                if (!empty($module['data'])) {
                    echo '<div class="row equal-height">';
                    foreach ($module['data'] as $data) {
                        echo '<div class="col-xs-12 col-sm-4 content"><div class="item">';
                            if (!empty($data['image'])) {
                                echo '<figure class="thumb">';
                                    echo '<a href="'.$data['url'].'">';
                                        echo '<img style="max-height: 120px;" class="img-responsive" src="'.$data['image'].'" alt="'.$data['title'].'">';
                                    echo '</a>';
                                echo '</figure>';
                            }

                            echo '<div class="post">';
                                echo '<h4><a href="'.$data['url'].'">'.$data['title'].'</a></h4>';
                                echo '<div class="small m-b-10 overflow-hide">'.$data['meta'].'</div>';
                                echo '<div class="overflow-hide hidden-xs">'.nl2br(trim_text(strip_tags($data['content']), 200)).'</div>';
                            echo '</div>';
                        echo '</div></div>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="m-t-10 m-b-10">'.$module['norecord'].'</div>';
                }
                closetable();
            }
        } else {
            opentable($locale['home_0100']);
            echo $locale['home_0101'];
            closetable();
        }
    }
}
