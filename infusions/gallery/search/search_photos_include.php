<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: search_photos_include.php
| Author: Core Development Team
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

defined('IN_FUSION') || exit;

if (defined('GALLERY_EXISTS')) {

    if (Search_Engine::get_param('stype') == 'photos' || Search_Engine::get_param('stype') == 'all') {
        $formatted_result = '';
        $locale = fusion_get_locale('', INFUSIONS.'gallery/locale/'.LOCALESET.'search/photos.php');
        $settings = fusion_get_settings();
        $item_count = "0 ".$locale['p402']." ".$locale['522']."<br />\n";

        if (!defined("SAFEMODE")) {
            define("SAFEMODE", (bool)(@ini_get("safe_mode")));
        }
        $sort_by = [
            'datestamp' => "photo_datestamp",
            'subject'   => "photo_title",
            'author'    => "photo_user",
        ];
        $order_by = [
            '0' => ' DESC',
            '1' => ' ASC',
        ];
        $sortby = !empty(Search_Engine::get_param('sort')) ? " ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : '';
        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');
        $date_search = (Search_Engine::get_param('datelimit') != 0 && isnum(Search_Engine::get_param('datelimit')) ? ' AND photo_datestamp>='.(time() - Search_Engine::get_param('datelimit').' OR album_datestamp>='.(time() - Search_Engine::get_param('datelimit'))).' ' : '');

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

        $query = '';
        $param = '';

        if (!empty(Search_Engine::get_param('search_param'))) {

            $query = "
            SELECT tp.*,ta.*
            FROM ".DB_PHOTOS." tp
            INNER JOIN ".DB_PHOTO_ALBUMS." ta ON tp.album_id=ta.album_id
            ".(multilang_table("PG") ? "WHERE ".in_group('ta.album_language', LANGUAGE)." AND " : "WHERE ").groupaccess('album_access')." AND
            ".Search_Engine::search_conditions('gallery');
            $param = Search_Engine::get_param('search_param');
            $result = dbquery($query." LIMIT 100", $param);

            $rows = dbrows($result);
        } else {
            $rows = 0;
        }
        if ($rows != 0) {

            $item_count = "<a href='".BASEDIR."search.php?stype=photos&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['p401'] : $locale['p402'])." ".$locale['522']."</a><br />\n";

            $result = dbquery($query.$date_search.$sortby.$limit, $param);

            $search_result = '';
            while ($data = dbarray($result)) {
                $data['photo_description'] = strip_tags(htmlspecialchars_decode($data['photo_description']));
                $text_all = $data['photo_description'];
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);

                $image_link = INFUSIONS.'gallery/gallery.php?photo_id='.$data['photo_id'];

                require_once INFUSIONS.'gallery/functions.php';

                $img_path = return_photo_paths($data);
                if (!empty($img_path['photo_thumb1'])) {
                    $img_path = $img_path['photo_thumb1'];
                } else if (!empty($img_path['photo_thumb2'])) {
                    $img_path = $img_path['photo_thumb2'];
                } else {
                    $img_path = $img_path['photo_filename'];
                }

                $desc = '';
                if ($text_frag != "") {
                    $desc .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />\n";
                }
                $desc .= "<span class='small'><span class='alt'>".$locale['p405']."</span> ".showdate("%d.%m.%y", $data['photo_datestamp'])." | <span class='alt'>".$locale['p406']."</span> ".$data['photo_views']."</span>";

                $search_result .= render_search_item_image([
                    'item_url'         => $image_link,
                    'item_image'       => "<img src='".$img_path."' class='icon-xl' alt='".$data['photo_title']."'>",
                    'item_title'       => $data['photo_title'].'<br>'.$locale['p404']." <a href='".INFUSIONS."gallery/gallery.php?album_id=".$data['album_id']."'>".$data['album_title']."</a>",
                    'item_description' => $desc
                ]);
            }

            // Pass strings for theme developers
            $formatted_result = render_search_item_wrapper([
                'image'          => "<img src='".ImageRepo::getimage('ac_PH')."' alt='".$locale['p400']."' style='width:32px;'/>",
                'icon_class'     => "fa fa-retro-camera fa-lg fa-fw",
                'search_title'   => $locale['p400'],
                'search_result'  => $item_count,
                'search_content' => $search_result
            ]);
        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}
