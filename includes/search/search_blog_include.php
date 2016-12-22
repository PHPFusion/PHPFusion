<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_blog_include.php
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

if (db_exists(DB_BLOG)) {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."search/blog.php");
    $formatted_result = '';
    $item_count = "0 ".$locale['n402']." ".$locale['522']."<br />\n";
    if (Search_Engine::get_param('stype') == "blog" || Search_Engine::get_param('stype') == "all") {
        $sort_by = array(
            'datestamp' => "blog_datestamp",
            'subject' => "blog_subject",
            'author' => "blog_name",
        );
        $order_by = array(
            '0' => ' DESC',
            '1' => ' ASC',
        );
        $sortby = !empty($_POST['sort']) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : "";
        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');
        $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND blog_datestamp >='.(TIME - Search_Engine::get_param('datelimit')) : '');

        switch(Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('blog_subject', 0);
                Search_Engine::search_column('blog_blog', 1);
                Search_Engine::search_column('blog_extended', 2);
                break;
            case 1:
                Search_Engine::search_column('blog_blog', 0);
                Search_Engine::search_column('blog_extended', 1);
                break;
            case 0:
                Search_Engine::search_column('blog_subject', 0);
                break;
        }

        if (!empty(Search_Engine::get_param('search_param'))) {
            $query = "SELECT blog_id FROM ".DB_BLOG."
            ".(multilang_table('BL') ? "WHERE blog_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('blog_visibility')." AND ".Search_Engine::search_conditions()." AND (blog_start='0'||blog_start<=NOW())".$date_search;
            $result = dbquery($query, Search_Engine::get_param('search_param'));
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }

        if ($rows != 0) {
            $item_count = "<a href='".FUSION_SELF."?stype=blog&amp;stext=".$_POST['stext']."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['n401'] : $locale['n402'])." ".$locale['522']."</a><br />\n";

            $query = "
            SELECT tn.*, tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_joined, tu.user_level
            FROM ".DB_BLOG." tn
            LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
            ".(multilang_table("BL") ? "WHERE tn.blog_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('blog_visibility')."
            AND (blog_start='0'||blog_start<=NOW()) AND (blog_end='0'||blog_end>=NOW()) AND ".Search_Engine::search_conditions()." ".$date_search.$sortby.$limit;

            $result = dbquery($query, Search_Engine::get_param('search_param'));

            $search_result = "<ul class='block spacer-xs'>\n";
            while ($data = dbarray($result)) {
                $text_all = $data['blog_blog']." ".$data['blog_extended'];
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['blog_subject']);
                $text_c = Search_Engine::search_stringscount($data['blog_blog']);
                $text_c2 = Search_Engine::search_stringscount($data['blog_extended']);
                $search_result .= "<li>\n";
                $search_result .= "<a href='".INFUSIONS."blog/blog.php?readmore=".$data['blog_id']."'>".$data['blog_subject']."</a>"."<br /><br />\n";
                $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
                $search_result .= "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."\n";
                $search_result .= $locale['global_071'].showdate("longdate", $data['blog_datestamp'])."</span><br />\n";
                $search_result .= "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['n403']." ".$locale['n404'].", ";
                $search_result .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['n403']." ".$locale['n405'].", ";
                $search_result .= $text_c2." ".($text_c2 == 1 ? $locale['520'] : $locale['521'])." ".$locale['n403']." ".$locale['n406']."</span><br /><br />\n";
                $search_result .= "</li>\n";
            }
            $search_result .= "</ul>\n";
            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item(), [
                '{%image%}' => ImageRepo::getimage('ac_BLOG'),
                '{%icon_class%}' => "fa fa-pencil-square fa-lg fa-fw",
                '{%search_title%}' => $locale['n400'],
                '{%search_result%}' => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }
        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}