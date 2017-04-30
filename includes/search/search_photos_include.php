<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_photos_include.php
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
if (db_exists(DB_PHOTOS)) {

    if (Search_Engine::get_param('stype') == 'photos' || Search_Engine::get_param('stype') == 'all') {
        $formatted_result = '';
        $locale = fusion_get_locale('', LOCALE.LOCALESET.'search/photos.php');
        $settings = fusion_get_settings();
        $item_count = "0 ".$locale['p402']." ".$locale['522']."<br />\n";

        if (!defined("SAFEMODE")) {
            define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
        }
        $sort_by = array(
            'datestamp' => "photo_datestamp",
            'subject'   => "photo_title",
            'author'    => "photo_user",
        );
        $order_by = array(
            '0' => ' DESC',
            '1' => ' ASC',
        );
        $sortby = !empty(Search_Engine::get_param('sort')) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : '';
        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');
        $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND photo_datestamp>='.(TIME - Search_Engine::get_param('datelimit').' OR album_datestamp>='.(TIME - Search_Engine::get_param('datelimit'))).' ' : '');

        switch (Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('photo_title', 'gallery');
                Search_Engine::search_column('photo_description', 'gallery');
                Search_Engine::search_column('album_title', 'gallery');
                Search_Engine::search_column('album_description', 'gallery');
                break;
            case 1:
                Search_Engine::search_column('photo_description', 'gallery');
                Search_Engine::search_column('album_description', 'gallery');
                break;
            default:
                Search_Engine::search_column('photo_title', 'gallery');
                Search_Engine::search_column('album_title', 'gallery');
        }

        if (!empty(Search_Engine::get_param('search_param'))) {

            $query = "
            SELECT tp.*,ta.*
            FROM ".DB_PHOTOS." tp
            INNER JOIN ".DB_PHOTO_ALBUMS." ta ON tp.album_id=ta.album_id
            ".(multilang_table("PG") ? "WHERE ta.album_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('album_access')." AND
            ".Search_Engine::search_conditions('gallery');
            $param = Search_Engine::get_param('search_param');
            $result = dbquery($query, $param);

            $rows = dbrows($result);
        } else {
            $rows = 0;
        }
        if ($rows != 0) {

            $item_count = "<a href='".BASEDIR."search.php?stype=photos&amp;stext=".$_POST['stext']."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['p401'] : $locale['p402'])." ".$locale['522']."</a><br />\n";

            $result = dbquery($query.$date_search.$sortby.$limit, $param);

            $search_result = '';
            while ($data = dbarray($result)) {
                $search_result = "";
                if ($data['photo_datestamp'] + 604800 > time() + ((float)$settings['timeoffset'] * 3600)) {
                    $new = " <span class='small'>".$locale['p403']."</span>";
                } else {
                    $new = "";
                }
                $text_all = $data['photo_description'];
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['photo_title']) + Search_Engine::search_stringscount($data['album_title']);
                $text_c = Search_Engine::search_stringscount($data['photo_description']) + Search_Engine::search_stringscount($data['album_description']);

                $image_link = INFUSIONS.'gallery/gallery.php?photo_id='.$data['photo_id'];

                if ($data['photo_thumb1'] != "" && file_exists(IMAGES_G_T.$data['photo_thumb1'])) {
                    $image = "<img src='".IMAGES_G_T.$data['photo_thumb1']."' style='border:none' alt='".$data['photo_title']."' />";
                } else {
                    if ($data['photo_thumb2'] != "" && file_exists(IMAGES_G_T.$data['photo_thumb2'])) {
                        $image = "<img src='".IMAGES_G_T.$data['photo_thumb2']."' style='border:none' alt='".$data['photo_title']."' />";
                    } else {
                        $image = "<img src='".get_image("imagenotfound")."' style='border:none' alt='".$data['photo_title']."' />";
                    }
                }

                $desc = '';
                if ($text_frag != "") {
                    $desc .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />\n";
                }
                $desc .= "<span class='small'><font class='alt'>".$locale['p405']."</font> ".showdate("%d.%m.%y", $data['photo_datestamp'])." | <span class='alt'>".$locale['p406']."</span> ".$data['photo_views']."</span>";

                $search_result .= strtr(Search::render_search_item_image(), [
                        '{%item_url%}'             => $image_link."&sref=search",
                        '{%item_target%}'          => '',
                        '{%item_image%}'           => $image,
                        '{%item_title%}'           => $data['photo_title']."</a>".$new." ".$locale['p404']." <a href='photogallery.php?album_id=".$data['album_id']."'>".$data['album_title'],
                        '{%item_description%}'     => $desc,
                        '{%item_search_criteria%}' => '',
                        '{%item_search_context%}'  => ''
                    ]
                );

            }

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item_wrapper(), [
                '{%image%}'          => "<img src='".ImageRepo::getimage('ac_PH')."' alt='".$locale['p400']."' style='width:32px;'/>",
                '{%icon_class%}'     => "fa fa-retro-camera fa-lg fa-fw",
                '{%search_title%}'   => $locale['p400'],
                '{%search_result%}'  => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}
