<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: core_mlang_hub_include.php
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
defined('IN_FUSION') || exit;

/**
 * Multi-language Hub
 * This file is 1st approach to deliver all language content despite user language preference.
 * It's primary role is to declare LANGUAGE and site LOCALESET so all URL will work.
 */

if (!defined('ADMIN_PANEL')) {
    global $current_user_language;

    if (preg_match('/viewpage.php/i', server('PHP_SELF')) && multilang_table('CP')) {
        preg_match('|/pages/([0-9]+)/|', server('REQUEST_URI'), $cp_matches);

        if (check_get('page_id') || !empty($cp_matches) && $cp_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT page_language
                FROM ".DB_PREFIX."custom_pages
                WHERE page_id=:id
            ", [':id' => check_get('page_id') ? get('page_id') : $cp_matches[1]]));

            if (!empty($data['page_language'])) {
                $lang = explode(',', $data['page_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }
    } else if (preg_match('/articles.php/i', server('PHP_SELF')) && multilang_table('AR')) {
        preg_match('|/articles/([0-9]+)/|', server('REQUEST_URI'), $a_matches);
        preg_match('|/articles/category/([0-9]+)/|', server('REQUEST_URI'), $ac_matches);

        if (check_get('article_id') || !empty($a_matches) && $a_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT article_language
                FROM ".DB_PREFIX."articles
                WHERE article_id=:id
            ", [':id' => check_get('article_id') ? get('article_id') : $a_matches[1]]));

            if (!empty($data['article_language'])) {
                $lang = explode(',', $data['article_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }

        if (check_get('cat_id') || !empty($ac_matches) && $ac_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT article_cat_language
                FROM ".DB_PREFIX."article_cats
                WHERE article_cat_id=:id
            ", [':id' => check_get('cat_id') ? get('cat_id') : $a_matches[1]]));

            if (!empty($data['article_cat_language'])) {
                $lang = explode(',', $data['article_cat_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }
    } else if (preg_match('/blog.php/i', server('PHP_SELF')) && multilang_table('BL')) {
        preg_match('|/blogs/([0-9]+)/|', server('REQUEST_URI'), $b_matches);
        preg_match('|/blogs/category/([0-9]+)/|', server('REQUEST_URI'), $bc_matches);

        if (check_get('readmore') || !empty($b_matches) && $b_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT blog_language
                FROM ".DB_PREFIX."blog
                WHERE blog_id=:id
            ", [':id' => check_get('readmore') ? get('readmore') : $b_matches[1]]));

            if (!empty($data['blog_language'])) {
                $lang = explode(',', $data['blog_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }

        if (check_get('cat_id') || !empty($bc_matches) && $bc_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT blog_cat_language
                FROM ".DB_PREFIX."blog_cats
                WHERE blog_cat_id=:id
            ", [':id' => check_get('cat_id') ? get('cat_id') : $bc_matches[1]]));

            if (!empty($data['blog_cat_language'])) {
                $lang = explode(',', $data['blog_cat_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }
    } else if (preg_match('/downloads.php/i', server('PHP_SELF')) && multilang_table('DL')) {
        preg_match('|/downloads/([0-9]+)/|', server('REQUEST_URI'), $d_matches);
        preg_match('|/downloads/category/([0-9]+)/|', server('REQUEST_URI'), $dc_matches);

        if (check_get('download_id') || !empty($d_matches) && $d_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT dlc.download_cat_id,dlc.download_cat_language, dl.download_id
                FROM ".DB_PREFIX."download_cats dlc
                LEFT JOIN ".DB_PREFIX."downloads dl ON dlc.download_cat_id = dl.download_cat
                WHERE dl.download_id=:id
                GROUP BY dl.download_id
            ", [':id' => check_get('download_id') ? get('download_id') : $d_matches[1]]));

            if (!empty($data['download_cat_language'])) {
                $lang = explode(',', $data['download_cat_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }

        if (check_get('cat_id') || !empty($dc_matches) && $dc_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT download_cat_language
                FROM ".DB_PREFIX."download_cats
                WHERE download_cat_id=:id
            ", [':id' => check_get('cat_id') ? get('cat_id') : $dc_matches[1]]));

            if (!empty($data['download_cat_language'])) {
                $lang = explode(',', $data['download_cat_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }
    } else if (preg_match('/faq.php/i', server('PHP_SELF')) && multilang_table('FQ')) {
        preg_match('|/faq/category/([0-9]+)/|', server('REQUEST_URI'), $f_matches);

        if (check_get('cat_id') || !empty($f_matches) && $f_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT faq_language
                FROM ".DB_PREFIX."faqs
                WHERE faq_id=:id
            ", [':id' => check_get('cat_id') ? get('cat_id') : $f_matches[1]]));

            if (!empty($data['faq_language'])) {
                $lang = explode(',', $data['faq_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }
    } else if (preg_match('/viewthread.php/i', server('PHP_SELF')) && multilang_table('FO')) {
        preg_match('|/thread/([0-9]+)/|', server('REQUEST_URI'), $f_matches);

        if (check_get('thread_id') || !empty($f_matches) && $f_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT f.forum_id,f.forum_language, t.thread_id
                FROM ".DB_PREFIX."forums f
                LEFT JOIN ".DB_PREFIX."forum_threads t ON f.forum_id = t.forum_id
                WHERE t.thread_id=:id
                GROUP BY t.thread_id
            ", [':id' => check_get('thread_id') ? get('thread_id') : $f_matches[1]]));

            if (!empty($data['forum_language'])) {
                $lang = explode(',', $data['forum_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }
    } else if (preg_match('/index.php/i', server('PHP_SELF')) && (check_get('viewforum') && check_get('forum_id')) && multilang_table('FO')) {
        $data = dbarray(dbquery("SELECT forum_cat, forum_branch, forum_language
            FROM ".DB_PREFIX."forums
            WHERE forum_id=:id
        ", [':id' => get('forum_id')]));

        if (!empty($data['forum_language'])) {
            $lang = explode(',', $data['forum_language']);

            if (!in_array($current_user_language, $lang)) {
                define_site_language($lang[0]);
            }
        }
    } else if (preg_match('/gallery.php/i', server('PHP_SELF')) && multilang_table('PG')) {
        preg_match('|/gallery/photo/([0-9]+)/|', server('REQUEST_URI'), $g_matches);
        preg_match('|/gallery/([0-9]+)/|', server('REQUEST_URI'), $a_matches);

        if (check_get('photo_id') || !empty($g_matches) && $g_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT pha.album_id, pha.album_language, ph.album_id
                FROM ".DB_PREFIX."photo_albums pha
                LEFT JOIN ".DB_PREFIX."photos ph ON pha.album_id = ph.album_id
                WHERE ph.photo_id=:id
                GROUP BY ph.photo_id
            ", [':id' => check_get('photo_id') ? get('photo_id') : $g_matches[1]]));

            if (!empty($data['album_language'])) {
                $lang = explode(',', $data['album_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }

        if (check_get('album_id') || !empty($a_matches) && $a_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT album_language
                FROM ".DB_PREFIX."photo_albums
                WHERE album_id=:id
            ", [':id' => check_get('album_id') ? get('album_id') : $a_matches[1]]));

            if (!empty($data['album_language'])) {
                $lang = explode(',', $data['album_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }
    } else if (preg_match('/news.php/i', server('PHP_SELF')) && multilang_table('NS')) {
        preg_match('|/news/([0-9]+)/|', server('REQUEST_URI'), $n_matches);
        preg_match('|/news/category/([0-9]+)/|', server('REQUEST_URI'), $nc_matches);

        if (check_get('readmore') || !empty($n_matches) && $n_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT news_language
                FROM ".DB_PREFIX."news
                WHERE news_id=:id
            ", [':id' => check_get('readmore') ? get('readmore') : $n_matches[1]]));

            if (!empty($data['news_language'])) {
                $lang = explode(',', $data['news_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }

        if (check_get('cat_id') || !empty($nc_matches) && $nc_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT news_cat_language
                FROM ".DB_PREFIX."news_cats
                WHERE news_cat_id=:id
            ", [':id' => check_get('cat_id') ? get('cat_id') : $nc_matches[1]]));

            if (!empty($data['news_cat_language'])) {
                $lang = explode(',', $data['news_cat_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }
    } else if (preg_match('/weblinks.php/i', server('PHP_SELF')) && multilang_table('WL')) {
        preg_match('|/weblinks/([0-9]+)/|', server('REQUEST_URI'), $w_matches);

        if (check_get('cat_id') || !empty($w_matches) && $w_matches[1] > 0) {
            $data = dbarray(dbquery("SELECT weblink_cat_language
                FROM ".DB_PREFIX."weblink_cats
                WHERE weblink_cat_id=:id
            ", [':id' => check_get('cat_id') ? get('cat_id') : $w_matches[1]]));

            if (!empty($data['weblink_cat_language'])) {
                $lang = explode(',', $data['weblink_cat_language']);

                if (!in_array($current_user_language, $lang)) {
                    define_site_language($lang[0]);
                }
            }
        }
    }
}
