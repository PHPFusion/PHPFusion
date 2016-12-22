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

if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (Search_Engine::get_param('stype') == 'custompages' || Search_Engine::get_param('stype') == 'all') {

    $locale = fusion_get_locale('', LOCALE.LOCALESET."search/custompages.php");
    $formatted_result = '';
    $item_count = "0 ".$locale['c402']." ".$locale['522']."<br />\n";

    $order_by = array(
        '0' => ' DESC',
        '1' => ' ASC',
    );

    $sortby = !empty(Search_Engine::get_param('order')) ? " ORDER BY page_title".$order_by[Search_Engine::get_param('order')] : '';
    $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');

    switch (Search_Engine::get_param('fields')) {
        case 2:
            Search_Engine::search_column('page_content', 0);
            Search_Engine::search_column('page_title', 1);
            Search_Engine::search_column('page_title', 2);
            break;
        case 1:
            Search_Engine::search_column('page_content', 0);
            Search_Engine::search_column('page_title', 1);
            break;
        default:
            Search_Engine::search_column('page_title', 0);
    }

    if (!empty(Search_Engine::get_param('search_param'))) {

        $query = "SELECT * FROM ".DB_CUSTOM_PAGES.(multilang_table('CP') ? " WHERE page_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('page_access')." AND ".Search_Engine::search_conditions();

        $result = dbquery($query, Search_Engine::get_param('search_param'));

        if (dbrows($result)) {
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }

        if ($rows != 0) {

            $item_count = "<a href='".FUSION_SELF."?stype=custompages&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['c401'] : $locale['c402'])." ".$locale['522']."</a><br />\n";
            $result = dbquery($query.$sortby.$limit, Search_Engine::get_param('search_param'));

            $search_result = "<ul class='block spacer-xs'>\n";
            while ($data = dbarray($result)) {
                $search_result = "";
                $text_all = stripslashes($data['page_content']);
                ob_start();
                eval ("?>".$text_all."<?php ");
                $text_all = ob_get_contents();
                ob_end_clean();
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['page_title']);
                $text_c = Search_Engine::search_stringscount($text_all);
                $search_result .= "<li><a href='viewpage.php?page_id=".$data['page_id']."'>".$data['page_title']."</a>"."<br /><br />\n";
                $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />\n";
                $search_result .= "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['c403']." ".$locale['c404'].", ";
                $search_result .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['c403']." ".$locale['c405']."</span></li>\n";

            }

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item(), [
                '{%image%}' => ImageRepo::getimage('ac_CP'),
                '{%icon_class%}' => "fa fa-sticky-note-o fa-lg fa-fw",
                '{%search_title%}' => $locale['c400'],
                '{%search_result%}' => $item_count,
                '{%search_content%}' => $search_result
            ]);

        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);

    }
}