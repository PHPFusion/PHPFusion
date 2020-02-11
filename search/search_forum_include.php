<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_forum_include.php
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
if (defined('FORUM_EXIST')) {

    if (Search_Engine::get_param('stype') == 'forum' || Search_Engine::get_param('stype') == 'all') {

        $formatted_result = '';
        $locale = fusion_get_locale('', INFUSIONS."forum/locale/".LOCALESET."search/forum.php");
        $item_count = "0 ".$locale['f403']." ".$locale['522']."<br  />\n";
        $inf_settings = get_settings('forum');

        $sort_by = [
            'datestamp' => "post_datestamp",
            'subject'   => "thread_subject",
            'author'    => "post_author",
        ];

        $order_by = [
            '0' => ' DESC',
            '1' => ' ASC',
        ];
        $sortby = !empty(Search_Engine::get_param('sort')) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : '';
        $limit = (Search_Engine::get_param('stype') != "all" ? " LIMIT ".Search_Engine::get_param('rowstart').",10" : '');
        $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND tp.post_datestamp >='.(TIME - Search_Engine::get_param('datelimit')) : '');

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
            ".(multilang_table("FR") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND " : "WHERE ").groupaccess('forum_access')
                .(Search_Engine::get_param('forum_id') != 0 ? " AND tf.forum_id=".Search_Engine::get_param('forum_id') : "")."
            AND ".Search_Engine::search_conditions('forum')." GROUP BY tt.thread_id ".$date_search;
            $result = dbquery($query, Search_Engine::get_param('search_param'));
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }

        if ($rows != 0) {

            $item_count = "<a href='".BASEDIR."search.php?stype=forum&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['f402'] : $locale['f403'])." ".$locale['522']."</a><br  />\n";

            // Change from forum post to forum thread searching.

            $query = "
            SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_message, tp.post_datestamp, tt.thread_subject, tt.thread_postcount,
            tt.thread_sticky, tf.forum_access, tu.user_id, tu.user_name, tu.user_status, tu.user_avatar
            FROM ".DB_FORUM_POSTS." tp
            LEFT JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id = tt.thread_id
            LEFT JOIN ".DB_FORUMS." tf ON tp.forum_id = tf.forum_id
            LEFT JOIN ".DB_USERS." tu ON tp.post_author=tu.user_id
            ".(multilang_table("FR") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND " : "WHERE ").groupaccess('forum_access').
                (Search_Engine::get_param('forum_id') != 0 ? " AND tf.forum_id=".Search_Engine::get_param('forum_id') : '')."
            AND ".Search_Engine::search_conditions('forum')." GROUP BY tt.thread_id ".$date_search.$sortby.$limit;

            $result = dbquery($query, Search_Engine::get_param('search_param'));

            $search_result = '';

            while ($data = dbarray($result)) {

                $text_all = Search_Engine::search_striphtmlbbcodes(iADMIN ? $data['post_message'] : preg_replace("#\[hide\](.*)\[/hide\]#si", '', $data['post_message']));
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['thread_subject']);
                $text_c = Search_Engine::search_stringscount($data['post_message']);

                $context = "<div class='text-normal'>".$text_frag."</div>";

                $meta = "<span class='text-smaller'>".$locale['global_070'].$data['user_name']."\n";
                $meta .= $locale['global_071'].showdate("longdate", $data['post_datestamp'])."</span><br/>\n";

                $criteria = "<span class='text-smaller text-lighter'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['f406']." ".$locale['f407'].", ";
                $criteria .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['f406']." ".$locale['f408']."</span>";

                $thread_rowstart = '';
                if (!empty($inf_settings['posts_per_page']) && $data['thread_postcount'] > $inf_settings['posts_per_page']) {
                    $thread_posts = dbquery("SELECT p.post_id, p.forum_id, p.thread_id, p.post_author, p.post_datestamp
                                    FROM ".DB_FORUM_POSTS." p
                                    LEFT JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
                                    WHERE p.forum_id='".$data['forum_id']."' AND p.thread_id='".$data['thread_id']."' AND thread_hidden='0' AND post_hidden='0'
                                    ORDER BY post_datestamp ASC");
                    if (dbrows($thread_posts)) {
                        $counter = 1;
                        while ($thread_post_data = dbarray($thread_posts)) {
                            if ($thread_post_data['post_id'] == $data['post_id']) {
                                $thread_rowstart = $inf_settings['posts_per_page'] * floor(($counter - 1) / $inf_settings['posts_per_page']);
                                $thread_rowstart = "&amp;rowstart=".$thread_rowstart;
                            }
                            $counter++;
                        }
                    }
                }

                $search_result .= strtr(Search::render_search_item_list(), [
                        '{%item_url%}'             => FORUM."viewthread.php?thread_id=".$data['thread_id'].$thread_rowstart."&amp;highlight=".Search_Engine::get_param('stext')."&amp;pid=".$data['post_id']."#post_".$data['post_id'],
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
