<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_custompages_include.php
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
namespace PHPFusion\Search;

use PHPFusion\ImageRepo;
use \PHPFusion\Search;
use PHPFusion\SiteLinks;

if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (Search_Engine::get_param('stype') == 'sitelinks' || Search_Engine::get_param('stype') == 'all') {

    $locale = fusion_get_locale('', LOCALE.LOCALESET."search/sitelinks.php");
    $formatted_result = '';
    $item_count = "0 ".$locale['s402']." ".$locale['522']."<br />\n";


    $order_by = array(
        '0' => ' DESC',
        '1' => ' ASC',
    );

    $sortby = !empty(Search_Engine::get_param('order')) ? " ORDER BY link_title".$order_by[Search_Engine::get_param('order')] : '';
    $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');

    Search_Engine::search_column('link_name', 0);
    Search_Engine::search_column('link_url', 1);
    Search_Engine::search_column('link_name', 2);

    if (!empty(Search_Engine::get_param('search_param'))) {

        $query = "SELECT * FROM ".DB_SITE_LINKS.(multilang_table('SL') ? " WHERE link_language='".LANGUAGE."' AND " : "WHERE ")
            .groupaccess('link_visibility')." AND link_url !='' AND link_name != '---' AND link_name != '===' AND link_status = '1' AND ".Search_Engine::search_conditions();

        $param = Search_Engine::get_param('search_param');

        $result = dbquery($query, $param);

        if (dbrows($result)) {
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }

        if ($rows != 0) {

            $default_link_data = array(
                "link_id" => 0,
                "link_name" => "",
                "link_cat" => 0,
                "link_url" => "",
                "link_icon" => "",
                "link_class" => "",
                "link_active" => '',
                "link_title" => FALSE, // true to add dropdown-header class to li.
                "link_disabled" => FALSE, // true to disable link
                "link_window" => FALSE,
            );

            $item_count = "<a href='".FUSION_SELF."?stype=sitelinks&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['s401'] : $locale['s402'])." ".$locale['522']."</a><br />\n";

            $result = dbquery($query.$sortby.$limit, $param);

            $search_result = "<ul class='block spacer-xs'>\n";

            while ($link_data = dbarray($result)) {

                $link_data += $default_link_data;

                $link_data['link_name'] = parsesmileys(parseubb($link_data['link_name']));

                if (!empty($link_data['link_url'])) {
                    $itemlink = " href='".BASEDIR.$link_data['link_url']."' ";
                    // if link has site protocol
                    if (preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url'])
                        or (BASEDIR !== '' && stristr($link_data['link_url'], BASEDIR))
                    ) {
                        $itemlink = " href='".$link_data['link_url']."' ";
                    }
                }

                $link_target = ($link_data['link_window'] == "1" ? " target='_blank'" : '');
                $link_icon = (!empty($link_data['link_icon']) ? "<i class='".$link_data['link_icon']."'></i>\n" : '');
                $search_result .= "<li><a $itemlink $link_target>".$link_icon.$link_data['link_name']."</a></li>";
            }

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item(), [
                '{%image%}' => ImageRepo::getimage('ac_CP'),
                '{%icon_class%}' => "fa fa-sitemap fa-lg fa-fw",
                '{%search_title%}' => $locale['s400'],
                '{%search_result%}' => $item_count,
                '{%search_content%}' => $search_result
            ]);

        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}