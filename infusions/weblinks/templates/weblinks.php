<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/templates/weblinks.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists("display_main_weblinks")) {
    /**
     * Weblink Page Template
     * @param $info
     */
    function display_main_weblinks($info) {
        $weblink_settings = \PHPFusion\Weblinks\WeblinksServer::get_weblink_settings();
        $locale = fusion_get_locale("", WEBLINK_LOCALE);

        opentable($locale['web_0000']);
        echo render_breadcrumbs();

        if (is_array($info['weblink_categories']) && !empty($info['weblink_categories'])) {
            echo "<div class='row'>";
            foreach ($info['weblink_categories'] as $cat_id => $cat_data) {
                echo "<div id='".$cat_id."' class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>";
                echo "<div class='spacer-xs'>\n";
                    echo "<a class='display-block' href='".$cat_data['link']."'>".$cat_data['name']." (".$cat_data['count'].")</a>\n";
                echo $cat_data['description'];
                echo "</div>\n";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<div class='well text-center'>".$locale['web_0060']."</div>";
        }
        closetable();
    }
}

if (!function_exists("render_weblinks_item")) {
    function render_weblinks_item($info) {
        $locale = fusion_get_locale();

        opentable($locale['web_0000']);
        echo render_breadcrumbs();

        if (!empty($info['weblink_items'])) {
            echo "<div class='row m-t-15'>";
            foreach ($info['weblink_items'] as $id => $info) {
                echo "<div id='weblink-".$id."' class='col-xs-12 col-sm-4'>";
                    echo '<div class="panel panel-default">';
                        echo '<div class="panel-body">';
                            echo '<h4 class="weblink_title panel-title">';
                                echo '<i class="fa fa-fw fa-link"></i>';
                                echo '<a target="_blank" href="'.INFUSIONS.'weblinks/weblinks.php?weblink_id='.$info['weblink_id'].'" class="strong">'.$info['weblink_name'].'</a>';
                            echo '</h4>';
                echo $info['weblink_description'] ? '<div class="weblink_text m-t-5">'.parse_textarea($info['weblink_description'], FALSE, FALSE, TRUE, '', TRUE).'</div>' : '';
                            echo '<div class="weblink-category m-t-5">';
                                echo '<i class="fa fa-fw fa-folder"></i>';
                                echo '<a href="'.INFUSIONS.'weblinks/weblinks.php?cat_id='.$info['weblink_cat'].'">'.$info['weblink_cat_name'].'</a>';
                            echo '</div>';
                        echo '</div>';

                        echo '<div class="panel-footer">';
                            echo '<i class="fa fa-fw fa-eye"></i>'.$info['weblink_count'];
                            echo '<i class="fa fa-fw fa-upload m-l-10"></i>'.showdate('shortdate', $info['weblink_datestamp']);
                            if (!empty($info['admin_actions'])) {
                                echo '<a href="'.$info['admin_actions']['edit']['link'].'" title="'.$info['admin_actions']['edit']['title'].'"><i class="fa fa-fw fa-pencil m-l-10"></i></a>';
                                echo '<a href="'.$info['admin_actions']['delete']['link'].'" title="'.$info['admin_actions']['delete']['title'].'"><i class="fa fa-fw fa-trash m-l-10"></i></a>';
                            }
                        echo '</div>';
                    echo '</div>';
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<div class='well text-center m-t-15'>".$locale['web_0062']."</div>";
        }
        closetable();
    }
}
