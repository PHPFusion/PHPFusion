<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_weblinks_include.php
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

if (defined('WEBLINKS_EXIST')) {

    if (Search_Engine::get_param('stype') == 'weblinks' || Search_Engine::get_param('stype') == 'all') {
        $formatted_result = '';
        $locale = fusion_get_locale('', INFUSIONS.'weblinks/locale/'.LOCALESET.'search/weblinks.php');
        $settings = fusion_get_settings();
        $item_count = "0 ".$locale['w402']." ".$locale['522']."<br />\n";

        $sort_by = [
            'datestamp' => "weblink_datestamp",
            'subject'   => "weblink_name",
            'author'    => "weblink_datestamp",
        ];
        $order_by = [
            '0' => ' DESC',
            '1' => ' ASC',
        ];
        $sortby = !empty(Search_Engine::get_param('sort')) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : "";
        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');
        $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND weblink_datestamp>='.(TIME - Search_Engine::get_param('datelimit')) : '');

        switch (Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('weblink_name', 'weblinks');
                Search_Engine::search_column('weblink_description', 'weblinks');
                Search_Engine::search_column('weblink_url', 'weblinks');
                break;
            case 1:
                Search_Engine::search_column('weblink_description', 'weblinks');
                Search_Engine::search_column('weblink_url', 'weblinks');
                break;
            default:
                Search_Engine::search_column('weblink_name', 'weblinks');
        }

        $query = '';

        if (!empty(Search_Engine::get_param('search_param'))) {

            $query = "SELECT tw.*,twc.*
            FROM ".DB_WEBLINKS." tw
            INNER JOIN ".DB_WEBLINK_CATS." twc ON tw.weblink_cat=twc.weblink_cat_id
            ".(multilang_table("WL") ? "WHERE ".in_group('twc.weblink_cat_language', LANGUAGE)." AND ".in_group('tw.weblink_language', LANGUAGE)." AND " : "WHERE ").groupaccess('weblink_visibility')."
            AND ".Search_Engine::search_conditions('weblinks').$date_search;

            $result = dbquery($query, Search_Engine::get_param('search_param'));
            $rows = dbrows($result);

        } else {
            $rows = 0;
        }

        if ($rows != 0) {
            $item_count = "<a href='".BASEDIR."search.php?stype=weblinks&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['w401'] : $locale['w402'])." ".$locale['522']."</a><br />\n";

            $result = dbquery($query.$date_search.$sortby.$limit, Search_Engine::get_param('search_param'));

            $search_result = '';
            while ($data = dbarray($result)) {
                $new = "";
                $timeoffset = timezone_offset_get(timezone_open($settings['timeoffset']), new \DateTime());
                if ($data['weblink_datestamp'] + 604800 > time() + ($timeoffset * 3600)) {
                    $new = " <span class='small'>".$locale['w403']."</span>";
                }

                $text_all = $data['weblink_description'];
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);
                // $subj_c = Search_Engine::search_stringscount($data['weblink_name']) + Search_Engine::search_stringscount($data['weblink_url']);
                // $text_c = Search_Engine::search_stringscount($data['weblink_description']);
                $desc = '';
                if ($text_frag != "") {
                    $desc .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
                }
                $desc .= "<span class='small'>".$locale['w404']." ".showdate("%d.%m.%y", $data['weblink_datestamp'])." | <span class='alt'>".$locale['w405']."</span> ".$data['weblink_count']."</span></li>\n";
                $tr = Search::render_search_item(TRUE);
                $search_result .= strtr($tr, [
                        '{%item_url%}'         => INFUSIONS."weblinks/weblinks.php?cat_id=".$data['weblink_cat']."&amp;weblink_id=".$data['weblink_id'],
                        '{%item_image%}'       => '',
                        '{%item_title%}'       => $data['weblink_name'].' '.$new,
                        '{%item_description%}' => strip_tags($desc),
                    ]
                );
            }

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item_wrapper(), [
                '{%image%}'          => "<img src='".ImageRepo::getimage('ac_W')."' alt='".$locale['w400']."' style='width:32px;'/>",
                '{%icon_class%}'     => "fa fa-link fa-lg fa-fw",
                '{%search_title%}'   => $locale['w400'],
                '{%search_result%}'  => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}
