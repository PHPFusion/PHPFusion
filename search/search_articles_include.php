<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_articles_include.php
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

if (defined('ARTICLES_EXIST')) {

    if (Search_Engine::get_param('stype') == 'articles' || Search_Engine::get_param('stype') == 'all') {

        $locale = fusion_get_locale('', INFUSIONS."articles/locale/".LOCALESET."search/articles.php");
        $formatted_result = '';
        $item_count = "0 ".$locale['a402']." ".$locale['522']."<br />\n";

        $sort_by = [
            'datestamp' => "article_datestamp",
            'subject'   => "article_subject",
            'author'    => "article_name",
        ];
        $order_by = [
            '0' => ' DESC',
            '1' => ' ASC',
        ];
        $sortby = !empty(Search_Engine::get_param('sort')) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : '';
        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : "");
        $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND article_datestamp>='.(TIME - Search_Engine::get_param('datelimit')) : '');

        switch (Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('article_subject', 'article');
                Search_Engine::search_column('article_article', 'article');
                Search_Engine::search_column('article_snippet', 'article');
                break;
            case 1:
                Search_Engine::search_column('article_article', 'article');
                Search_Engine::search_column('article_snippet', 'article');
                break;
            default:
                Search_Engine::search_column('article_subject', 'article');
        }

        $query = '';

        if (!empty(Search_Engine::get_param('search_param'))) {

            $query = "SELECT ta.*, tac.*, u.user_id, u.user_name, u.user_status, u.user_level, u.user_avatar
            FROM ".DB_ARTICLES." ta
            INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
            INNER JOIN ".DB_USERS." u ON ta.article_name=u.user_id
            ".(multilang_table('AR') ? "WHERE ".in_group('tac.article_cat_language', LANGUAGE)." AND " : "WHERE ")
                .groupaccess('article_visibility')." AND article_cat_status=1 AND article_draft='0' AND ".Search_Engine::search_conditions('article')
                .$date_search;
            $result = dbquery($query, Search_Engine::get_param('search_param'));
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }

        if ($rows != 0) {
            $item_count = "<a href='".BASEDIR."search.php?stype=articles&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['a401'] : $locale['a402'])." ".$locale['522']."</a><br />\n";

            $result = dbquery($query.$date_search.$sortby.$limit, Search_Engine::get_param('search_param'));

            $search_result = '';
            while ($data = dbarray($result)) {
                $text_all = Search_Engine::search_striphtmlbbcodes($data['article_snippet']." ".$data['article_article']);
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['article_subject']);
                $text_c = Search_Engine::search_stringscount($data['article_snippet']." ".$data['article_article']);

                $context = "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";

                $meta = "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."\n";
                $meta .= $locale['global_071'].showdate("longdate", $data['article_datestamp'])."</span><br />\n";

                $criteria = "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['522']." ".$locale['a404'].", ";
                $criteria .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['522']." ".$locale['a405']."</span>";

                $search_result .= strtr(Search::render_search_item_list(), [
                        '{%item_url%}'             => INFUSIONS."articles/articles.php?article_id=".$data['article_id'],
                        '{%item_target%}'          => 'self',
                        '{%item_image%}'           => '',
                        '{%item_title%}'           => $data['article_subject'],
                        '{%item_description%}'     => $meta,
                        '{%item_search_criteria%}' => $criteria,
                        '{%item_search_context%}'  => $context,
                    ]
                );
            }

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item_wrapper(), [
                '{%image%}'          => "<img src='".ImageRepo::getimage('ac_A')."' alt='".$locale['a400']."' style='width:32px;'/>",
                '{%icon_class%}'     => "fa fa-book fa-lg fa-fw",
                '{%search_title%}'   => $locale['a400'],
                '{%search_result%}'  => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}
