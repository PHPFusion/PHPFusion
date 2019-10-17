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

defined('IN_FUSION') || exit;

if (defined('DOWNLOADS_EXIST')) {
    $formatted_result = '';
    $settings = fusion_get_settings();
    $locale = fusion_get_locale('', INFUSIONS."downloads/locale/".LOCALESET."search/downloads.php");
    $item_count = "0 ".$locale['d402']." ".$locale['522']."<br />\n";
    $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND download_datestamp>='.(TIME - Search_Engine::get_param('datelimit')) : '');

    if (Search_Engine::get_param('stype') == 'downloads' || Search_Engine::get_param('stype') == 'all') {

        $sort_by = [
            'datestamp' => "download_datestamp",
            'subject'   => "download_title",
            'author'    => "download_user",
        ];

        $order_by = [
            '0' => ' DESC',
            '1' => ' ASC',
        ];

        $sortby = !empty(Search_Engine::get_param('sort')) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : '';

        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');

        switch (Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('download_title', 'downloads');
                Search_Engine::search_column('download_description', 'downloads');
                Search_Engine::search_column('download_user', 'downloads');
                break;
            case 1:
                Search_Engine::search_column('download_description', 'downloads');
                Search_Engine::search_column('download_title', 'downloads');
                break;
            default:
                Search_Engine::search_column('download_title', 'downloads');
        }

        if (!empty(Search_Engine::get_param('search_param'))) {
            $query = "SELECT td.*,tdc.*
            FROM ".DB_DOWNLOADS." td
            INNER JOIN ".DB_DOWNLOAD_CATS." tdc ON td.download_cat=tdc.download_cat_id
            ".(multilang_table("DL") ? "WHERE ".in_group('tdc.download_cat_language', LANGUAGE)." AND " : "WHERE ")
                .groupaccess('download_visibility')." AND ".Search_Engine::search_conditions('downloads').$date_search;
            $result = dbquery($query, Search_Engine::get_param('search_param'));
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }
        if ($rows != 0) {

            $item_count = "<a href='".BASEDIR."search.php?stype=downloads&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['d401'] : $locale['d402'])." ".$locale['522']."</a><br />\n";

            $result = dbquery("SELECT td.*, tdc.*, user_id, user_name, user_status, user_avatar, user_joined, user_level
            FROM ".DB_DOWNLOADS." td
            INNER JOIN ".DB_DOWNLOAD_CATS." tdc ON td.download_cat=tdc.download_cat_id
            LEFT JOIN ".DB_USERS." tu ON td.download_user=tu.user_id
            ".(multilang_table("DL") ? "WHERE ".in_group('tdc.download_cat_language', LANGUAGE)." AND " : "WHERE ").groupaccess('td.download_visibility')." AND
            ".Search_Engine::search_conditions('downloads').$date_search.$sortby.$limit, Search_Engine::get_param('search_param'));

            $search_result = '';

            while ($data = dbarray($result)) {
                $timeoffset = timezone_offset_get(timezone_open($settings['timeoffset']), new \DateTime());
                if ($data['download_datestamp'] + 604800 > time() + ($timeoffset * 3600)) {
                    $new = " <span class='small'>".$locale['d403']."</span>";
                } else {
                    $new = "";
                }
                $text_all = $data['download_description'];
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);
                // $subj_c = Search_Engine::search_stringscount($data['download_title']);
                // $text_c = Search_Engine::search_stringscount($data['download_description']);

                $context = '';
                if ($text_frag != "") {
                    $context .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
                }

                $meta = "<span class='small'><span class='alt'>".$locale['d404']."</span> ".$data['download_license']." |\n";
                $meta .= "<span class='alt'>".$locale['d405']."</span> ".$data['download_os']." |\n";
                $meta .= "<span class='alt'>".$locale['d406']."</span> ".$data['download_version']."<br />\n";
                $meta .= "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."\n";
                $meta .= "<span class='alt'>".$locale['d407']."</span> ".showdate("%d.%m.%y", $data['download_datestamp'])." |\n";
                $meta .= "<span class='alt'>".$locale['d408']."</span> ".$data['download_count']."</span>";

                $search_result .= strtr(Search::render_search_item(), [
                        '{%item_url%}'             => DOWNLOADS."downloads.php?cat_id=".$data['download_cat']."&amp;download_id=".$data['download_id'],
                        '{%item_image%}'           => "<i class='fa fa-download fa-lg'></i>",
                        '{%item_title%}'           => $data['download_title'].' - '.$data['download_filesize'].' '.$new,
                        '{%item_description%}'     => $meta,
                        '{%item_search_criteria%}' => '',
                        '{%item_search_context%}'  => $context
                    ]
                );
            }

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item_wrapper(), [
                '{%image%}'          => "<img src='".ImageRepo::getimage('ac_D')."' alt='".$locale['d400']."' style='width:32px;'/>",
                '{%icon_class%}'     => "fa fa-cloud-download fa-lg fa-fw",
                '{%search_title%}'   => $locale['d400'],
                '{%search_result%}'  => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}
