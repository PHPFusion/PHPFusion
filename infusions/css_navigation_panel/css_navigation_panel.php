<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: navigation_panel.php
| Author: PHP-Fusion Development Team
| Co-Author: Chubatyj Vitalij (Rizado)
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

$locale = fusion_get_locale();
openside($locale['global_001']);

if (!function_exists("showsidelinks")) {
    function showsidelinks(array $options = array(), $id = 0) {
        $userdata = fusion_get_userdata();
        static $data = array();
        $settings = fusion_get_settings();
        $acclevel = isset($userdata['user_level']) ? $userdata['user_level'] : 0;
        $res = &$res;
        if (empty($data)) {
            $data = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat",
                                      "WHERE link_position <= 2".(multilang_table("SL") ? " AND link_language='".LANGUAGE."'" : "")." AND ".groupaccess('link_visibility')." AND link_status=1 ORDER BY link_cat, link_order");
        }
        if (!$id) {
            $res .= "<ul class='main-nav'>\n";
        } else {
            $res .= "\n<ul class='sub-nav p-l-10' style='display: none;'>\n";
        }
        if (!empty($data[$id])) {
            foreach ($data[$id] as $link_id => $link_data) {
                $li_class = "";
                if ($link_data['link_name'] != "---" && $link_data['link_name'] != "===") {

                    $link_target = ($link_data['link_window'] == "1" ? " target='_blank'" : "");

                    if (START_PAGE == $link_data['link_url']) {
                        $li_class .= ($li_class ? " " : "")."current-link";
                    }

                    if (preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url'])) {
                        $item_link = $link_data['link_url'];
                    } else {
                        $item_link = BASEDIR.$link_data['link_url'];
                    }

                    $link_icon = "";
                    if ($link_data['link_icon']) {
                        $link_icon = "<i class='".$link_data['link_icon']."'></i>";
                    }

                    $res .= "<li".($li_class ? " class='".$li_class."'" : "").">";
                    $res .= "<a class='display-block p-5 p-l-0 p-r-0' href='".$item_link."' ".$link_target.">";
                    $res .= $link_icon.$link_data['link_name'];
                    $res .= "</a>";

                    if (isset($data[$link_id])) {
                        $res .= showsidelinks($options, $link_data['link_id']);
                    }
                    $res .= "</li>\n";

                } elseif ($link_data['link_name'] == '---' || $link_data['link_name'] == '===') {
                    $res .= "<li class='divider'></li>\n";
                }
            }
        }
        $res .= "</ul>\n";
        return $res;
    }

    add_to_jquery("
    $('.fusion_css_navigation_panel li:has(ul)').on('click', function(e) {
        e.preventDefault();
        $('.fusion_css_navigation_panel').find('.sub-nav').slideToggle();
    });
    $('.fusion_css_navigation_panel').find('.sub-nav li').click(function(e) {
        e.stopPropagation();
    });
    $('.fusion_css_navigation_panel li:has(ul)').find('a:first').append(' »');
    ");
}

echo "<div class='fusion_css_navigation_panel'>\n";
echo showsidelinks();
echo "</div>\n";
closeside();
