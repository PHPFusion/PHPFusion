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

if (!function_exists("showsidelinks")) {
    function showsidelinks(array $options = array(), $id = 0) {
        $userdata = fusion_get_userdata();
        $settings = fusion_get_settings();

        $pageInfo = pathinfo($_SERVER['REQUEST_URI']);
        $start_page = $pageInfo['dirname'] !== "/" ? ltrim($pageInfo['dirname'], "/")."/" : "";
        $site_path = ltrim(fusion_get_settings("site_path"), "/");
        $start_page = str_replace([$site_path, '\/'], ['', ''], $start_page);
        $start_page .= $pageInfo['basename'];

        $acclevel = isset($userdata['user_level']) ? $userdata['user_level'] : 0;
        static $data = array();

        if (empty($data)) {
            $data = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat", "WHERE link_position <= 2".(multilang_table("SL") ? " AND link_language='".LANGUAGE."'" : "")." AND ".groupaccess('link_visibility')." AND link_status=1 ORDER BY link_cat, link_order");
        }

        if (!$id) {
            echo "<ul class='main-nav block'>\n";
        } else {
            echo "<ul class='sub-nav p-l-10 block' style='display: none;'>\n";
        }

        $i = 0;
        if (!empty($data[$id])) {
            foreach ($data[$id] as $link_id => $link_data) {
                if ($link_data['link_name'] != "---" && $link_data['link_name'] != "===") {
                    $link_target = ($link_data['link_window'] == "1" ? " target='_blank'" : "");

                    if ($start_page == $link_data['link_url']) {
                        $link_is_active = TRUE;
                    } elseif (fusion_get_settings('site_path').$start_page == $link_data['link_url']) {
                        $link_is_active = TRUE;
                    } elseif (($start_page == fusion_get_settings("opening_page") && $i == 0 && $id === 0)) {
                        $link_is_active = TRUE;
                    } else {
                        $link_is_active = FALSE;
                    }

                    if (preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url'])) {
                        $item_link = $link_data['link_url'];
                    } else {
                        $item_link = BASEDIR.$link_data['link_url'];
                    }

                    $link_icon = $link_data['link_icon'] ? "<i class='".$link_data['link_icon']."'></i>" : "";

                    echo "<li".($link_is_active ? " class='current-link active'" : "").">";
                        echo "<a class='display-block p-5 p-l-0 p-r-0' href='".$item_link."' ".$link_target.">";
                            echo $link_icon.$link_data['link_name'];
                        echo "</a>";

                        if (isset($data[$link_id])) {
                            echo showsidelinks($options, $link_data['link_id']);
                        }
                    echo "</li>\n";
                } elseif ($link_data['link_name'] == '---' || $link_data['link_name'] == '===') {
                    echo "<li class='divider'></li>\n";
                }

                $i++;
            }
        }

        echo "</ul>\n";
    }

    add_to_jquery("
        $('.fusion_css_navigation_panel li:has(ul)').on('click', function(e) {
            e.preventDefault();
            $(this).find('.sub-nav').slideToggle('fast');
        });

        $('.fusion_css_navigation_panel').find('.sub-nav li').click(function(e) {
            e.stopPropagation();
        });

        $('.fusion_css_navigation_panel li:has(ul)').find('a:first').append(' »');
    ");
}

openside(fusion_get_locale('global_001'));
    echo "<div class='fusion_css_navigation_panel'>\n";
    showsidelinks();
    echo "</div>\n";
closeside();
