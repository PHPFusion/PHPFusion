<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_news_include.php
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
use PHPFusion\News\News;
use PHPFusion\Search;

defined('IN_FUSION') || exit;

if (defined('NEWS_EXIST')) {

    if (Search_Engine::get_param('stype') == 'news' || Search_Engine::get_param('stype') == 'all') {
        $formatted_result = '';
        $locale = fusion_get_locale('', INFUSIONS.'news/locale/'.LOCALESET.'search/news.php');
        $item_count = "0 ".$locale['n402']." ".$locale['522']."<br />\n";

        $sort_by = [
            'datestamp' => "news_datestamp",
            'subject'   => "news_subject",
            'author'    => "news_name",
        ];

        $order_by = [
            '0' => ' DESC',
            '1' => ' ASC',
        ];

        $sortby = !empty(Search_Engine::get_param('sort')) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : '';
        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');
        $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND news_datestamp>='.(TIME - Search_Engine::get_param('datelimit')) : '');

        switch (Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('news_subject', 'news');
                Search_Engine::search_column('news_news', 'news');
                Search_Engine::search_column('news_extended', 'news');
                break;
            case 1:
                Search_Engine::search_column('news_news', 'news');
                Search_Engine::search_column('news_extended', 'news');
                break;
            default:
                Search_Engine::search_column('news_subject', 'news');
        }

        if (!empty(Search_Engine::get_param('search_param'))) {
            $rows = dbcount("(news_id)", DB_NEWS, (multilang_table("NS") ? in_group('news_language', LANGUAGE)." AND " : "").groupaccess('news_visibility')." AND ".Search_Engine::search_conditions('news')." AND (news_start='0'||news_start<=NOW()) AND (news_end='0'||news_end>=NOW()) ".$date_search, Search_Engine::get_param('search_param'));
        } else {
            $rows = 0;
        }
        if ($rows != 0) {
            require_once INFUSIONS.'news/classes/autoloader.php';
            $item_count = "<a href='".BASEDIR."search.php?stype=news&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['n401'] : $locale['n402'])." ".$locale['522']."</a><br />\n";

            $result = dbquery("SELECT tn.*, nc.*, tu.user_id, tu.user_name, tu.user_status,
                ni.news_image, ni.news_image_t1, ni.news_image_t2
                FROM ".DB_NEWS." tn
                LEFT JOIN ".DB_USERS." tu ON tn.news_name=tu.user_id
                LEFT JOIN ".DB_NEWS_CATS." nc ON tn.news_cat=nc.news_cat_id
                LEFT JOIN ".DB_NEWS_IMAGES." ni ON ni.news_id=tn.news_id AND tn.news_image_front_default=ni.news_image_id
                ".(multilang_table("NS") ? "WHERE ".in_group('tn.news_language', LANGUAGE)." AND " : "WHERE ").groupaccess('news_visibility')."
                AND (news_start='0'||news_start<=NOW())
                AND (news_end='0'||news_end>=NOW()) AND ".Search_Engine::search_conditions('news').$date_search.$sortby.$limit
                , Search_Engine::get_param('search_param')
            );

            $search_result = '';

            while ($data = dbarray($result)) {

                $text_all = $data['news_news']." ".$data['news_extended'];
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['news_subject']);
                $text_c = Search_Engine::search_stringscount($data['news_news']);
                $text_c2 = Search_Engine::search_stringscount($data['news_extended']);

                $context = "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div>";

                $meta = "<span class='small2'>".$locale['global_070'].$data['user_name']." ".$locale['global_071'].showdate("longdate", $data['news_datestamp'])."</span><br />\n";

                $criteria = "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['n403']." ".$locale['n404'].", ";
                $criteria .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['n403']." ".$locale['n405'].", ";
                $criteria .= $text_c2." ".($text_c2 == 1 ? $locale['520'] : $locale['521'])." ".$locale['n403']." ".$locale['n406']."</span>";

                $search_result .= strtr(Search::render_search_item_list(), [
                        '{%item_url%}'             => INFUSIONS."news/news.php?readmore=".$data['news_id'],
                        '{%item_target%}'          => '',
                        '{%item_image%}'           => News::get_NewsImage($data, TRUE, FALSE, '100'),
                        '{%item_title%}'           => $data['news_subject'],
                        '{%item_description%}'     => $meta,
                        '{%item_search_criteria%}' => $criteria,
                        '{%item_search_context%}'  => $context,
                    ]
                );
            }

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item_wrapper(), [
                '{%image%}'          => "<img src='".ImageRepo::getimage('ac_N')."' alt='".$locale['n400']."' style='width:32px;'/>",
                '{%icon_class%}'     => "fa fa-newspaper-o fa-lg fa-fw",
                '{%search_title%}'   => $locale['n400'],
                '{%search_result%}'  => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}
