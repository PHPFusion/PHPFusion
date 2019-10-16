<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_faqs_include.php
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

if (defined('FAQ_EXIST')) {

    if (Search_Engine::get_param('stype') == 'faqs' || Search_Engine::get_param('stype') == 'all') {
        $locale = fusion_get_locale('', INFUSIONS."faq/locale/".LOCALESET."search/faqs.php");
        $formatted_result = '';
        $item_count = "0 ".$locale['fq402']." ".$locale['522']."<br />\n";
        $order_by = [
            '0' => ' DESC',
            '1' => ' ASC',
        ];
        $sortby = !empty(Search_Engine::get_param('order')) ? " ORDER BY faq_id".$order_by[Search_Engine::get_param('order')] : '';

        switch (Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('faq_question', 'faqs');
                Search_Engine::search_column('faq_answer', 'faqs');
                Search_Engine::search_column('faq_name', 'faqs');
                break;
            case 1:
                Search_Engine::search_column('faq_answer', 'faqs');
                break;
            default:
                Search_Engine::search_column('faq_question', 'faqs');
        }

        $result = '';

        if (!empty(Search_Engine::get_param('search_param'))) {
            $query = "SELECT faq_question, faq_answer, faq_cat_id
                FROM ".DB_FAQS."
                ".(multilang_table("FQ") ? "WHERE ".in_group('faq_language', LANGUAGE)." AND " : "WHERE ").Search_Engine::search_conditions('faqs').$sortby;
            $result = dbquery($query, Search_Engine::get_param('search_param'));
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }

        if ($rows != 0) {
            $item_count = "<a href='".BASEDIR."search.php?stype=faqs&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['fq401'] : $locale['fq402'])." ".$locale['522']."</a><br />\n";
            $search_result = '';

            while ($data = dbarray($result)) {
                $text_all = $data['faq_answer'];
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['faq_question']);
                $text_c = Search_Engine::search_stringscount($data['faq_answer']);

                $context = "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
                $criteria = "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['fq403']." ".$locale['fq404'].", ";
                $criteria .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['fq403']." ".$locale['fq405']."</span>";

                $search_result .= strtr(Search::render_search_item(), [
                        '{%item_url%}'             => INFUSIONS."faq/faq.php?cat_id=".$data['faq_cat_id'],
                        '{%item_target%}'          => '',
                        '{%item_image%}'           => '',
                        '{%item_title%}'           => $data['faq_question'],
                        '{%item_description%}'     => html_entity_decode($data['faq_answer']),
                        '{%item_search_criteria%}' => $criteria,
                        '{%item_search_context%}'  => $context,
                    ]
                );
            }

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item_wrapper(), [
                '{%image%}'          => "<img src='".ImageRepo::getimage('ac_FQ')."' alt='".$locale['fq400']."' style='width:32px;'/>",
                '{%icon_class%}'     => "fa fa-question-circle fa-lg fa-fw",
                '{%search_title%}'   => $locale['fq400'],
                '{%search_result%}'  => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }
        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}
