<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_downloads_include.php
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
use PHPFusion\Search;

if (!defined("IN_FUSION")) {
    die("Access Denied");
}
if (db_exists(DB_DOWNLOADS)) {
    $formatted_result = '';
    $settings = fusion_get_settings();
    $locale = fusion_get_locale('', LOCALE.LOCALESET."search/downloads.php");
    $item_count = "0 ".$locale['d402']." ".$locale['522']."<br />\n";
    $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND download_datestamp>='.(TIME - Search_Engine::get_param('datelimit')) : '');

    if (Search_Engine::get_param('stype') == 'articles' || Search_Engine::get_param('stype') == 'all') {

        $sort_by = array(
            'datestamp' => "download_datestamp",
            'subject' => "download_title",
            'author' => "download_user",
        );

        $order_by = array(
            '0' => ' DESC',
            '1' => ' ASC',
        );

        $sortby = !empty(Search_Engine::get_param('sort')) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : '';

        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');

        switch (Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('download_title', 0);
                Search_Engine::search_column('download_description', 1);
                Search_Engine::search_column('download_user', 2);
                break;
            case 1:
                Search_Engine::search_column('download_description', 0);
                Search_Engine::search_column('download_title', 1);
                break;
            default:
                Search_Engine::search_column('download_title', 0);
        }


        if (!empty(Search_Engine::get_param('search_param'))) {

            $query = "SELECT td.*,tdc.*
            FROM ".DB_DOWNLOADS." td
            INNER JOIN ".DB_DOWNLOAD_CATS." tdc ON td.download_cat=tdc.download_cat_id
            ".(multilang_table("DL") ? "WHERE tdc.download_cat_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('download_visibility')." AND ".Search_Engine::search_conditions().$date_search;

            $result = dbquery($query, Search_Engine::get_param('search_param'));

            $rows = dbrows($result);
        } else {
            $rows = 0;
        }
        if ($rows != 0) {

            $item_count = "<a href='".FUSION_SELF."?stype=downloads&amp;stext=".$_POST['stext']."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['d401'] : $locale['d402'])." ".$locale['522']."</a><br />\n";

            $result = dbquery("SELECT td.*,tdc.*
            tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_joined, tu.user_level
            FROM ".DB_DOWNLOADS." td
            INNER JOIN ".DB_DOWNLOAD_CATS." tdc ON td.download_cat=tdc.download_cat_id
            LEFT JOIN ".DB_USERS." tu ON td.download_user=tu.user_id
            ".(multilang_table("DL") ? "WHERE tdc.download_cat_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('download_cat_access')." AND
            ".Search_Engine::search_conditions().$date_search.$sortby.$limit, Search_Engine::get_param('search_param'));

            $search_result = "<ul class='block spacer-xs'>\n";

            while ($data = dbarray($result)) {
                $search_result = "";
                if ($data['download_datestamp'] + 604800 > time() + ($settings['timeoffset'] * 3600)) {
                    $new = " <span class='small'>".$locale['d403']."</span>";
                } else {
                    $new = "";
                }

                $text_all = $data['download_description'];
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['download_title']);
                $text_c = Search_Engine::search_stringscount($data['download_description']);
                $search_result .= "<li>\n";
                $search_result .= "<a href='".DOWNLOADS."downloads.php?cat_id=".$data['download_cat']."&amp;download_id=".$data['download_id']."' target='_blank'>".$data['download_title']."</a> - ".$data['download_filesize']." ".$new."<br /><br />\n";
                if ($text_frag != "") {
                    $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
                }
                $search_result .= "<span class='small'><span class='alt'>".$locale['d404']."</span> ".$data['download_license']." |\n";
                $search_result .= "<span class='alt'>".$locale['d405']."</span> ".$data['download_os']." |\n";
                $search_result .= "<span class='alt'>".$locale['d406']."</span> ".$data['download_version']."<br />\n";
                $search_result .= "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'],
                                                                                             $data['user_status'])."\n";
                $search_result .= "<span class='alt'>".$locale['d407']."</span> ".showdate("%d.%m.%y", $data['download_datestamp'])." |\n";
                $search_result .= "<span class='alt'>".$locale['d408']."</span> ".$data['download_count']."</span><br /><br />\n";
                $search_result .= "</li>\n";
            }
            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item(), [
                '{%image%}' => ImageRepo::getimage('ac_D'),
                '{%icon_class%}' => "fa fa-cloud-download fa-lg fa-fw",
                '{%search_title%}' => $locale['d400'],
                '{%search_result%}' => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}