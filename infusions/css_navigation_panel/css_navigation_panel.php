<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: css_navigation_panel.php
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

use PHPFusion\Rewrite\Router;

defined('IN_FUSION') || exit;

if (!function_exists('show_nav_sublinks')) {
    function show_nav_sublinks($id = 0) {
        $res = '';

        if (empty($id)) {
            $data = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat",
                "WHERE link_position <= 2".(multilang_table("SL") ? " AND link_language='".LANGUAGE."'" : "")." 
                AND ".groupaccess('link_visibility')." AND link_status=1 ORDER BY link_cat, link_order");

            $res = show_nav_links($id, $data);
        }

        return $res;
    }
}

if (!function_exists('show_nav_links')) {
    function show_nav_links($id, $data) {
        $res = '';

        $pageInfo = pathinfo($_SERVER['REQUEST_URI']);
        $start_page = $pageInfo['dirname'] !== "/" ? ltrim($pageInfo['dirname'], "/")."/" : "";
        $site_path = ltrim(fusion_get_settings("site_path"), "/");
        $start_page = str_replace([$site_path, '\/'], ['', ''], $start_page);
        $start_page .= $pageInfo['basename'];

        if (fusion_get_settings("site_seo") && defined('IN_PERMALINK') && !isset($_GET['aid'])) {
            $filepath = Router::getRouterInstance()->getFilePath();
            $start_page = $filepath;
        }

        if (!empty($data[$id])) {
            $i = 0;

            foreach ($data[$id] as $link_id => $link_data) {
                $li_class = [];

                if (empty($link_data['link_url'])) {
                    $li_class[] = "no-link";
                }

                $link_is_active = FALSE;
                $secondary_active = FALSE;

                if ($link_data['link_name'] != "---" && $link_data['link_name'] != "===") {

                    $link_data['link_name'] = fusion_get_settings('link_bbcode') ? parseubb($link_data['link_name']) : $link_data['link_name'];
                    $link_data["link_name"] = html_entity_decode($link_data["link_name"], ENT_QUOTES);

                    $link_target = ($link_data['link_window'] == "1" ? " target='_blank'" : '');
                    if ($secondary_active) {
                        $link_is_active = TRUE;
                    } else if (
                        strtr(FUSION_REQUEST, [fusion_get_settings('site_path') => '', '&amp;' => '&']) ==
                        str_replace('../', '', $link_data['link_url'])
                    ) {
                        $link_is_active = TRUE;
                    } else if ($start_page == $link_data['link_url']) {
                        $link_is_active = TRUE;
                    } else if (fusion_get_settings('site_path').$start_page == $link_data['link_url']) {
                        $link_is_active = TRUE;
                    } else if (($start_page == fusion_get_settings("opening_page") && $i == 0 && $id === 0)) {
                        $link_is_active = TRUE;
                    } else if ($link_data['link_url'] === '#') {
                        $link_is_active = FALSE;
                    }
                    if ($link_is_active) {
                        $li_class[] = "current-link active";
                    }
                    $itemlink = '';
                    if (!empty($link_data['link_url'])) {
                        $itemlink = " href='".BASEDIR.$link_data['link_url']."' ";
                        // if link has site protocol
                        if (preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url'])
                            or (BASEDIR !== '' && stristr($link_data['link_url'], BASEDIR))
                        ) {
                            $itemlink = " href='".$link_data['link_url']."' ";
                        }
                    }

                    $itemlink = str_replace('%aidlink%', fusion_get_aidlink(), $itemlink);

                    $has_child = FALSE;
                    $l_1 = "";
                    $l_2 = "";

                    if (isset($data[$link_id])) {
                        $has_child = TRUE;
                        $link_class = " class='display-block p-5 p-l-0 p-r-0 dropdown-toggle'";
                        $l_1 = " id='nav_ddlink".$link_data['link_id']."' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'";
                        $l_1 .= (empty($id) && $has_child ? " data-submenu " : "");
                        $l_2 = (empty($id) ? "<span class='fa fa-caret-down'></i>" : "");
                        $li_class[] = (!empty($id) ? "dropdown-submenu" : "dropdown");
                    } else {
                        $link_class = " class='display-block p-5 p-l-0 p-r-0 ".(!empty($link_data['link_class']) ? $link_data['link_class'] : '')."'";
                    }

                    $li_class = array_filter($li_class);

                    $res .= "<li".(!empty($li_class) ? " class='".implode(" ", $li_class)."'" : '').">";

                    $res .= ($itemlink ? "<a".$l_1.$itemlink.$link_target.$link_class.">" : "");
                    $res .= (!empty($link_data['link_icon']) ? "<i class='".$link_data['link_icon']." m-r-5'></i>" : "");
                    $res .= $link_data['link_name']." ".$l_2;
                    $res .= ($itemlink ? "</a>" : '');
                    if ($has_child) {
                        $res .= "\n<ul id='nav_menu-".$link_data['link_id']."' aria-labelledby='nav_ddlink".$link_data['link_id']."' class='dropdown-menu'>\n";
                        if (!empty($link_data['link_url']) and $link_data['link_url'] !== "#") {
                            $res .= "<li".(!$itemlink ? " class='no-link'" : '').">\n";
                            $link_class = strtr($link_class, [
                                'nav-link'        => 'dropdown-item',
                                'dropdown-toggle' => ''
                            ]);
                            $res .= ($itemlink ? "<a ".$itemlink.$link_target.$link_class.">\n" : '');
                            $res .= (!empty($link_data['link_icon']) ? "<i class='".$link_data['link_icon']." m-r-5'></i>\n" : "");
                            $res .= $link_data['link_name'];
                            $res .= ($itemlink ? "\n</a>\n" : '');
                            $res .= "</li>\n";
                        }
                        $res .= show_nav_links($link_data['link_id'], $data);
                        $res .= "</ul>\n";
                    }
                    $res .= "</li>\n";
                } else {
                    $res .= "<li class='divider'></li>\n";
                }
                $i++;
            }
        }

        return $res;
    }
}


openside(fusion_get_locale('global_001'));
echo '<div class="fusion_css_navigation_panel">';
    echo '<ul class="main-nav block">';
    echo show_nav_sublinks();
    echo '</ul>';
echo '</div>';
closeside();
