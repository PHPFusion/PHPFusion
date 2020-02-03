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

defined('IN_FUSION') || exit;

if (defined('BLOG_EXIST')) {
    $locale = fusion_get_locale('', INFUSIONS."blog/locale/".LOCALESET."search/blog.php");
    $formatted_result = '';
    $item_count = "0 ".$locale['b402']." ".$locale['522']."<br />\n";
    if (Search_Engine::get_param('stype') == "blog" || Search_Engine::get_param('stype') == "all") {
        $sort_by = [
            'datestamp' => "blog_datestamp",
            'subject'   => "blog_subject",
            'author'    => "blog_name",
        ];
        $order_by = [
            '0' => ' DESC',
            '1' => ' ASC',
        ];
        $sortby = !empty($_POST['sort']) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : "";
        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');
        $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND blog_datestamp >='.(TIME - Search_Engine::get_param('datelimit')) : '');

        switch (Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('blog_subject', 'blog');
                Search_Engine::search_column('blog_blog', 'blog');
                Search_Engine::search_column('blog_extended', 'blog');
                break;
            case 1:
                Search_Engine::search_column('blog_blog', 'blog');
                Search_Engine::search_column('blog_extended', 'blog');
                break;
            case 0:
                Search_Engine::search_column('blog_subject', 'blog');
                break;
        }

        if (!empty(Search_Engine::get_param('search_param'))) {
            $query = "SELECT blog_id FROM ".DB_BLOG."
            ".(multilang_table('BL') ? "WHERE ".in_group('blog_language', LANGUAGE)." AND " : "WHERE ").groupaccess('blog_visibility')."
            AND ".Search_Engine::search_conditions('blog')." AND (blog_start='0'||blog_start<=NOW())".$date_search;
            $result = dbquery($query, Search_Engine::get_param('search_param'));
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }

        if ($rows != 0) {
            $item_count = "<a href='".BASEDIR."search.php?stype=blog&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['b401'] : $locale['b402'])." ".$locale['522']."</a><br />\n";

            $query = "
            SELECT tn.*, tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_joined, tu.user_level
            FROM ".DB_BLOG." tn
            LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
            ".(multilang_table("BL") ? "WHERE ".in_group('tn.blog_language', LANGUAGE)." AND " : "WHERE ").groupaccess('blog_visibility')."
            AND (blog_start='0'||blog_start<=NOW()) AND (blog_end='0'||blog_end>=NOW()) AND ".Search_Engine::search_conditions('blog')." ".$date_search.$sortby.$limit;

            $result = dbquery($query, Search_Engine::get_param('search_param'));

            $search_result = '';

            while ($data = dbarray($result)) {
                $text_all = $data['blog_blog']." ".$data['blog_extended'];
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['blog_subject']);
                $text_c = Search_Engine::search_stringscount($data['blog_blog']);
                $text_c2 = Search_Engine::search_stringscount($data['blog_extended']);

                $context = "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";

                $meta = "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."\n";
                $meta .= $locale['global_071'].showdate("longdate", $data['blog_datestamp'])."</span><br />\n";

                $criteria = "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['b403']." ".$locale['b404'].", ";
                $criteria .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['b403']." ".$locale['b405'].", ";
                $criteria .= $text_c2." ".($text_c2 == 1 ? $locale['520'] : $locale['521'])." ".$locale['b403']." ".$locale['b406']."</span>";

                $search_result .= strtr(Search::render_search_item_list(), [
                        '{%item_url%}'             => INFUSIONS."blog/blog.php?readmore=".$data['blog_id'],
                        '{%item_target%}'          => 'self',
                        '{%item_image%}'           => '',
                        '{%item_title%}'           => $data['blog_subject'],
                        '{%item_description%}'     => $meta,
                        '{%item_search_criteria%}' => $criteria,
                        '{%item_search_context%}'  => $context
                    ]
                );

            }

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item_wrapper(), [
                '{%image%}'          => "<img src='".ImageRepo::getimage('ac_BLOG')."' alt='".$locale['b400']."' style='width:32px;'/>",
                '{%icon_class%}'     => "fa fa-pencil-square fa-lg fa-fw",
                '{%search_title%}'   => $locale['b400'],
                '{%search_result%}'  => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}
