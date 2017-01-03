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
            'subject'   => "thread_subject",
            'author'    => "post_author",
        );

        $order_by = array(
            '0' => ' DESC',
            '1' => ' ASC',
        );
        $sortby = !empty(Search_Engine::get_param('sort')) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : '';
        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');
        $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND post_datestamp >='.(TIME - Search_Engine::get_param('datelimit')) : '');

        switch (Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('thread_subject', 'forum');
                Search_Engine::search_column('post_message', 'forum');
                Search_Engine::search_column('forum_name', 'forum');
                break;
            case 1:
                Search_Engine::search_column('post_message', 'forum');
                Search_Engine::search_column('forum_description', 'forum');
                break;
            default:
                Search_Engine::search_column('thread_subject', 'forum');
        }

        if (!empty(Search_Engine::get_param('search_param'))) {
            /*
             * Group by the thread. We don't need about 100 results of the same thread.
             */
            $query = "
            SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_message, tt.thread_subject, tf.forum_access, tf.forum_name, tf.forum_description
            FROM ".DB_FORUM_POSTS." tp
            LEFT JOIN ".DB_FORUMS." tf ON tf.forum_id = tp.forum_id
            LEFT JOIN ".DB_FORUM_THREADS." tt ON tt.thread_id = tp.thread_id
            ".(multilang_table("FR") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('forum_access')
                .(Search_Engine::get_param('forum_id') != 0 ? " AND tf.forum_id=".Search_Engine::get_param('forum_id') : "")."
            AND ".Search_Engine::search_conditions('forum')." GROUP BY tt.thread_id ".$date_search;
            $result = dbquery($query, Search_Engine::get_param('search_param'));
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }

        if ($rows != 0) {

            $item_count = "<a href='".FUSION_SELF."?stype=forums&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['f402'] : $locale['f403'])." ".$locale['522']."</a><br  />\n";

            // Change from forum post to forum thread searching.

            $query = "
            SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_message, tp.post_datestamp, tt.thread_subject,
            tt.thread_sticky, tf.forum_access, tu.user_id, tu.user_name, tu.user_status, tu.user_avatar
            FROM ".DB_FORUM_POSTS." tp
            LEFT JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id = tt.thread_id
            LEFT JOIN ".DB_FORUMS." tf ON tp.forum_id = tf.forum_id
            LEFT JOIN ".DB_USERS." tu ON tp.post_author=tu.user_id
            ".(multilang_table("FR") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('forum_access').
                (Search_Engine::get_param('forum_id') != 0 ? " AND tf.forum_id=".Search_Engine::get_param('forum_id') : '')."
            AND ".Search_Engine::search_conditions('forum')." GROUP BY tt.thread_id ".$date_search.$sortby.$limit;

            $result = dbquery($query, Search_Engine::get_param('search_param'));

            $search_result = '';

            while ($data = dbarray($result)) {

                $text_all = Search_Engine::search_striphtmlbbcodes(iADMIN ? $data['post_message'] : preg_replace("#\[hide\](.*)\[/hide\]#si", '', $data['post_message']));
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['thread_subject']);
                $text_c = Search_Engine::search_stringscount($data['post_message']);;

                $context = "<div class='text-normal'>".$text_frag."</div>";

                $meta = "<span class='text-smaller'>".$locale['global_070'].$data['user_name']."\n";
                $meta .= $locale['global_071'].showdate("longdate", $data['post_datestamp'])."</span><br/>\n";

                $criteria = "<span class='text-smaller text-lighter'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['f406']." ".$locale['f407'].", ";
                $criteria .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['f406']." ".$locale['f408']."</span>";

                $search_result .= strtr(Search::render_search_item_list(), [
                        '{%item_url%}'             => FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;highlight=".Search_Engine::get_param('stext')."&amp;pid=".$data['post_id']."#post_".$data['post_id']."&sref=search",
                        '{%item_image%}'           => display_avatar($data, '70px', '', '', FALSE),
                        '{%item_title%}'           => ($data['thread_sticky'] == 1 ? '['.$locale['f404'].']' : '').$data['thread_subject'],
                        '{%item_description%}'     => $meta,
                        '{%item_search_context%}'  => $context,
                        '{%item_search_criteria%}' => $criteria,
                    ]
                );
            }

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item_wrapper(), [
                '{%image%}'          => "<img src='".ImageRepo::getimage('ac_F')."' alt='".$locale['f400']."' style='width:32px;'/>",
                '{%icon_class%}'     => "fa fa-comments-o fa-lg fa-fw",
                '{%search_title%}'   => $locale['f400'],
                '{%search_result%}'  => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}