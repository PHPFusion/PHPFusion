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
            'subject' => "photo_title",
            'author' => "photo_user",
        );
        $order_by = array(
            '0' => ' DESC',
            '1' => ' ASC',
        );
        $sortby = !empty(Search_Engine::get_param('sort')) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : '';
        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');
        $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND photo_datestamp>='.(TIME - Search_Engine::get_param('datelimit').' OR album_datestamp>='.(TIME - Search_Engine::get_param('datelimit'))).' ' : '');

        switch(Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('photo_title', 0);
                Search_Engine::search_column('photo_description', 1);
                Search_Engine::search_column('album_title', 2);
                Search_Engine::search_column('album_description', 3);
                break;
            case 1:
                Search_Engine::search_column('photo_description', 0);
                Search_Engine::search_column('album_description', 1);
                break;
            default:
                Search_Engine::search_column('photo_title', 0);
                Search_Engine::search_column('album_title', 1);
        }

        if (!empty(Search_Engine::get_param('search_param'))) {

            $result = dbquery("SELECT tp.*,ta.*
            	FROM ".DB_PHOTOS." tp
				INNER JOIN ".DB_PHOTO_ALBUMS." ta ON tp.album_id=ta.album_id
				".(multilang_table("PG") ? "WHERE ta.album_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('album_access')." AND
				".Search_Engine::search_conditions(), Search_Engine::get_param('search_param'));

            $rows = dbrows($result);
        } else {
            $rows = 0;
        }
        if ($rows != 0) {

            $item_count = "<a href='".FUSION_SELF."?stype=photos&amp;stext=".$_POST['stext']."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['p401'] : $locale['p402'])." ".$locale['522']."</a><br />\n";

            $result = dbquery("SELECT tp.*,ta.*
            	FROM ".DB_PHOTOS." tp
				INNER JOIN ".DB_PHOTO_ALBUMS." ta ON tp.album_id=ta.album_id
				".(multilang_table("PG") ? "WHERE ta.album_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('album_access')." AND ".Search_Engine::search_conditions().
				$date_search.$sortby.$limit);

            $search_result = "<ul class='block spacer-xs'>\n";

            while ($data = dbarray($result)) {

                $search_result = "";
                if ($data['photo_datestamp'] + 604800 > time() + ($settings['timeoffset'] * 3600)) {
                    $new = " <span class='small'>".$locale['p403']."</span>";
                } else {
                    $new = "";
                }
                $text_all = $data['photo_description'];
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['photo_title']) + Search_Engine::search_stringscount($data['album_title']);
                $text_c = Search_Engine::search_stringscount($data['photo_description']) + Search_Engine::search_stringscount($data['album_description']);
                $search_result .= "<table width='100%'>";
                $search_result .= "<tr><td width='".$settings['thumb_w']."'>";
                $photodir = PHOTOS.(!SAFEMODE ? "album_".$data['album_id']."/" : "");

                $search_result .= "<li>\n";

                if ($data['photo_thumb1'] != "" && file_exists($photodir.$data['photo_thumb1'])) {
                    $search_result .= "<a href='photogallery.php?photo_id=".$data['photo_id']."'><img src='".$photodir.$data['photo_thumb1']."' style='border:none' alt='".$data['photo_title']."' /></a>";
                } else {
                    if ($data['photo_thumb2'] != "" && file_exists($photodir.$data['photo_thumb2'])) {
                        $search_result .= "<a href='photogallery.php?photo_id=".$data['photo_id']."'><img src='".$photodir.$data['photo_thumb2']."' style='border:none' alt='".$data['photo_title']."' /></a>";
                    } else {
                        $search_result .= "<a href='photogallery.php?photo_id=".$data['photo_id']."'><img src='".get_image("imagenotfound")."' style='border:none' alt='".$data['photo_title']."' /></a>";
                    }
                }
                $search_result .= "</td><td>";
                $search_result .= "<a href='photogallery.php?photo_id=".$data['photo_id']."'>".$data['photo_title']."</a>".$new." (".$locale['p404']." <a href='photogallery.php?album_id=".$data['album_id']."'>".$data['album_title']."</a>)"."<br /><br />\n";
                if ($text_frag != "") {
                    $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />\n";
                }
                $search_result .= "<span class='small'><font class='alt'>".$locale['p405']."</font> ".showdate("%d.%m.%y",
                                                                                                               $data['photo_datestamp'])." | <span class='alt'>".$locale['p406']."</span> ".$data['photo_views']."</span>";
                $search_result .= "</td></tr></table></li>\n";
            }


            $search_result .= "</ul>\n";

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item(), [
                '{%image%}' => ImageRepo::getimage('ac_A'),
                '{%icon_class%}' => "fa fa-retro-camera fa-lg fa-fw",
                '{%search_title%}' => $locale['a400'],
                '{%search_result%}' => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}
