<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: navigation_panel.php
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
defined('IN_FUSION') || exit;

openside(fusion_get_locale('global_001'));
echo '<div class="fusion_css_navigation_panel">';
echo '<ul class="main-nav block">';

$data = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat", "WHERE link_position <= 2".(multilang_table("SL") ? " AND link_language='".LANGUAGE."'" : "")." AND ".groupaccess('link_visibility')." AND link_status=1 ORDER BY link_cat, link_order");

if (!empty($data[0])) {
    $i = 0;
    foreach ($data[0] as $id => $link_data) {
        if ($link_data['link_name'] != '---' && $link_data['link_name'] != '===') {
            echo '<li>';
            create_link($link_data, $i, $id);

            if (isset($data[$id])) {
                $sub_i = 0;
                echo '<ul class="sub-nav p-l-10 block" style="display:none;">';
                create_link($link_data, $i, $id);
                foreach ($data[$id] as $sub_id => $sub_link_data) {
                    echo '<li>';
                    create_link($sub_link_data, $sub_i, $sub_id);
                    echo '</li>';
                }
                echo '</ul>';
            }
            echo '</li>';
        } else {
            echo '<li class="divider"></li>';
        }
    }
}

echo '</ul>';
echo '</div>';
closeside();

function create_link($data, $i, $id) {
    $pageInfo = pathinfo($_SERVER['REQUEST_URI']);
    $start_page = $pageInfo['dirname'] !== "/" ? ltrim($pageInfo['dirname'], '/').'/' : '';
    $site_path = ltrim(fusion_get_settings("site_path"), "/");
    $start_page = str_replace([$site_path, '\/'], ['', ''], $start_page);
    $start_page .= $pageInfo['basename'];

    if ($start_page == $data['link_url']) {
        $link_is_active = TRUE;
    } else if (fusion_get_settings('site_path').$start_page == $data['link_url']) {
        $link_is_active = TRUE;
    } else if (($start_page == fusion_get_settings("opening_page") && $i == 0 && $id === 0)) {
        $link_is_active = TRUE;
    } else {
        $link_is_active = FALSE;
    }

    if (preg_match("!^(ht|f)tp(s)?://!i", $data['link_url'])) {
        $item_link = $data['link_url'];
    } else {
        $item_link = BASEDIR.$data['link_url'];
    }

    $item_link = str_replace('%aidlink%', fusion_get_aidlink(), $item_link);
    $link_target = $data['link_window'] == 1 ? ' target="_blank"' : '';
    $active = $link_is_active ? ' current-link active' : '';

    echo '<a class="display-block p-5 p-l-0 p-r-0'.$active.'" href="'.$item_link.'"'.$link_target.'>';
        echo $data['link_icon'] ? ' <i class="'.$data['link_icon'].'"></i>' : '';
        echo $data['link_name'];
    echo '</a>';
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
