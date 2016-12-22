<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_forums_include.php
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
if (db_exists(DB_FORUMS)) {

    if (Search_Engine::get_param('stype') == 'forums' || Search_Engine::get_param('stype') == 'all') {

        $formatted_result = '';
        $locale = fusion_get_locale('', LOCALE.LOCALESET.'search/forums.php');
        $item_count = "0 ".$locale['f403']." ".$locale['522']."<br  />\n";

        $sort_by = array(
            'datestamp' => "post_datestamp",
            'subject' => "thread_subject",
            'author' => "post_author",
        );

        $order_by = array(
            '0' => ' DESC',
            '1' => ' ASC',
        );
        $sortby = !empty(Search_Engine::get_param('sort')) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : '';
        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');
        $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND post_datestamp >='.(TIME - Search_Engine::get_param('datelimit')) : '');

        switch(Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('thread_subject', 0);
                Search_Engine::search_column('post_message', 1);
                Search_Engine::search_column('forum_name', 2);
                break;
            case 1:
                Search_Engine::search_column('post_message', 0);
                Search_Engine::search_column('forum_description', 1);
                break;
            default:
                Search_Engine::search_column('thread_subject', 0);
        }

        if (!empty(Search_Engine::get_param('search_param'))) {

            $query = "
            SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_message, tt.thread_subject, tf.forum_access, tf.forum_name, tf.forum_description
            FROM ".DB_FORUM_POSTS." tp
            LEFT JOIN ".DB_FORUMS." tf ON tf.forum_id = tp.forum_id
            LEFT JOIN ".DB_FORUM_THREADS." tt ON tt.thread_id = tp.thread_id
            ".(multilang_table("FR") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('forum_access').(Search_Engine::get_param('forum_id') != 0 ? " AND tf.forum_id=".Search_Engine::get_param('forum_id') : "")."
            AND ".Search_Engine::search_conditions().$date_search;

            $result = dbquery($query, Search_Engine::get_param('search_param'));
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }

        if ($rows !=0) {
            $item_count = "<a href='".FUSION_SELF."?stype=forums&amp;stext=".$_POST['stext']."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['f402'] : $locale['f403'])." ".$locale['522']."</a><br  />\n";

            $query = "
            SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_message, tp.post_datestamp, tt.thread_subject,
            tt.thread_sticky, tf.forum_access, tu.user_id, tu.user_name, tu.user_status
            FROM ".DB_FORUM_POSTS." tp
            LEFT JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id = tt.thread_id
            LEFT JOIN ".DB_FORUMS." tf ON tp.forum_id = tf.forum_id
            LEFT JOIN ".DB_USERS." tu ON tp.post_author=tu.user_id
            ".(multilang_table("FR") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('forum_access').
            (Search_Engine::get_param('forum_id') != 0 ? " AND tf.forum_id=".Search_Engine::get_param('forum_id') : '')."
            AND ".Search_Engine::search_conditions().$date_search.$sortby.$limit;

            $result = dbquery($query, Search_Engine::get_param('search_param'));

            $search_result = "<ul class='block spacer-xs'>\n";
            while ($data = dbarray($result)) {

                $text_all = Search_Engine::search_striphtmlbbcodes(iADMIN ? $data['post_message'] : preg_replace("#\[hide\](.*)\[/hide\]#si", '', $data['post_message']));
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['thread_subject']);
                $text_c = Search_Engine::search_stringscount($data['post_message']);;

                $search_result .= "<li>\n";
                $search_result .= ($data['thread_sticky'] == 1 ? "<strong>".$locale['f404']."</strong> " : "")."<a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;highlight=".urlencode($_POST['stext'])."&amp;pid=".$data['post_id']."#post_".$data['post_id']."'>".$data['thread_subject']."</a>"."<br  /><br  />\n";
                $search_result .= "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br  />";
                $search_result .= "<span class='small2'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."\n";
                $search_result .= $locale['global_071'].showdate("longdate", $data['post_datestamp'])."</span><br  />\n";
                $search_result .= "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['f406']." ".$locale['f407'].", ";
                $search_result .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['f406']." ".$locale['f408']."</span></li>\n";
            }
            $search_result .= "</ul>\n";

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item(), [
                '{%image%}' => ImageRepo::getimage('ac_F'),
                '{%icon_class%}' => "fa fa-comments-o fa-lg fa-fw",
                '{%search_title%}' => $locale['f400'],
                '{%search_result%}' => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}