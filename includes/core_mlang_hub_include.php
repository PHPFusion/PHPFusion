<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: includes/core_mlang_hub_include.php
| Author: J.Falk (Falk)
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
 * This file is 1st approach to deliver all language content despite of user language preference.
 * It's primary role is to declare LANGUAGE and site LOCALESET so all URL will work.
 */
if (!preg_match('/administration/i', $_SERVER['PHP_SELF'])) {

    // Articles
    if (preg_match('/articles.php/i', $_SERVER['PHP_SELF']) && multilang_table("AR")) {

        preg_match('|/articles/([0-9]+)/|', $_SERVER['REQUEST_URI'], $article_matches);
        preg_match('|/articles/category/([0-9]+)/|', $_SERVER['REQUEST_URI'], $article_cat_matches);

        if (isset($_GET['article_id']) && isnum($_GET['article_id']) || !empty($article_matches) && $article_matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT ac.article_cat_id,ac.article_cat_language, a.article_id
                                     FROM ".DB_PREFIX.'article_cats'." ac
                                     LEFT JOIN ".DB_PREFIX.'articles'." a ON ac.article_cat_id = a.article_cat
                                     WHERE a.article_id='".(isset($_GET['article_id']) ? $_GET['article_id'] : $article_matches['1'])."'
                                     GROUP BY a.article_id"));
            if ($data['article_cat_language'] != $current_user_language) {
                define_site_language($data['article_cat_language']);
            }
        }

        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) || !empty($article_cat_matches) && $article_cat_matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT article_cat_language FROM ".DB_PREFIX."article_cats WHERE article_cat_id='".(isset($_GET['cat_id']) ? $_GET['cat_id'] : $article_cat_matches['1'])."'"));
            if ($data['article_cat_language'] != $current_user_language) {
                define_site_language($data['article_cat_language']);
            }
        }

    } // Blog
    else if (preg_match('/blog.php/i', $_SERVER['PHP_SELF']) && multilang_table("BL")) {

        preg_match('|/blogs/([0-9]+)/|', $_SERVER['REQUEST_URI'], $blog_matches);
        preg_match('|/blogs/category/([0-9]+)/|', $_SERVER['REQUEST_URI'], $blog_cat_matches);

        if (isset($_GET['readmore']) && isnum($_GET['readmore']) || !empty($blog_matches) && $blog_matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT blog_language FROM ".DB_PREFIX."blog WHERE blog_id='".(isset($_GET['readmore']) ? $_GET['readmore'] : $blog_matches['1'])."'"));
            if ($data['blog_language'] != $current_user_language) {
                define_site_language($data['blog_language']);
            }
        }

        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) || !empty($blog_cat_matches) && $blog_cat_matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT blog_cat_language FROM ".DB_PREFIX."blog_cats WHERE blog_cat_id='".(isset($_GET['cat_id']) ? $_GET['cat_id'] : $blog_cat_matches['1'])."'"));
            if ($data['blog_cat_language'] != $current_user_language) {
                define_site_language($data['blog_cat_language']);
            }
        }

    } // Custom Pages
    else if (preg_match('/viewpage.php/i', $_SERVER['PHP_SELF']) || preg_match('|/pages/([0-9]+)/|', $_SERVER['REQUEST_URI'], $matches) && multilang_table("CP")) {
        $matches = [];
        if (isset($_GET['page_id']) && isnum($_GET['page_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT page_language FROM ".DB_CUSTOM_PAGES." WHERE page_id='".(isset($_GET['page_id']) ? $_GET['page_id'] : $matches['1'])."'"));
            $page_lang = explode(".", $data['page_language']);
            // this must show in Malay.
            if (!in_array($current_user_language, $page_lang)) {
                define_site_language($page_lang['0']);
            }
        }
    } // Downloads
    else if (preg_match('/downloads.php/i', $_SERVER['PHP_SELF']) && multilang_table("DL")) {

        preg_match('|/downloads/([0-9]+)/|', $_SERVER['REQUEST_URI'], $dl_matches);
        preg_match('|/downloads/category/([0-9]+)/|', $_SERVER['REQUEST_URI'], $dlc_matches);

        if (isset($_GET['download_id']) && isnum($_GET['download_id']) || !empty($dl_matches) && $dl_matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT dlc.download_cat_id,dlc.download_cat_language, dl.download_id
                                    FROM ".DB_PREFIX."download_cats dlc
                                    LEFT JOIN ".DB_PREFIX."downloads dl ON dlc.download_cat_id = dl.download_cat
                                    WHERE dl.download_id='".(isset($_GET['download_id']) ? $_GET['download_id'] : $dl_matches['1'])."'
                                    GROUP BY dl.download_id"));
            if ($data['download_cat_language'] != $current_user_language) {
                define_site_language($data['download_cat_language']);
            }
        }

        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) || !empty($dlc_matches) && $dlc_matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT download_cat_language FROM ".DB_PREFIX."download_cats WHERE download_cat_id='".(isset($_GET['cat_id']) ? $_GET['cat_id'] : $dlc_matches['1'])."'"));
            if ($data['download_cat_language'] != $current_user_language) {
                define_site_language($data['download_cat_language']);
            }
        }

    } // News
    else if (preg_match('/news.php/i', $_SERVER['PHP_SELF']) && multilang_table("NS")) {

        preg_match('|/news/([0-9]+)/|', $_SERVER['REQUEST_URI'], $news_matches);
        preg_match('|/news/category/([0-9]+)/|', $_SERVER['REQUEST_URI'], $nc_matches);

        if (isset($_GET['readmore']) && isnum($_GET['readmore']) || !empty($news_matches) && $news_matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT news_language FROM ".DB_PREFIX."news WHERE news_id='".(isset($_GET['readmore']) ? $_GET['readmore'] : $news_matches['1'])."'"));
            if ($data['news_language'] != $current_user_language) {
                define_site_language($data['news_language']);
            }
        }

        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) || !empty($nc_matches) && $nc_matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT news_cat_language FROM ".DB_PREFIX."news_cats WHERE news_cat_id='".(isset($_GET['cat_id']) ? $_GET['cat_id'] : $nc_matches['1'])."'"));
            if ($data['news_cat_language'] != $current_user_language) {
                define_site_language($data['news_cat_language']);
            }
        }

    } // FaQ´s
    else if (preg_match('/faq.php/i', $_SERVER['PHP_SELF']) || preg_match('|/faq/category/([0-9]+)/|', $_SERVER['REQUEST_URI'],
            $matches) && multilang_table("FQ")
    ) {
        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT fq.faq_id, fq.faq_cat_id, fq.faq_language
                                    FROM ".DB_PREFIX."faqs fq
                                    WHERE fq.faq_id='".(isset($_GET['cat_id']) ? $_GET['cat_id'] : $matches['1'])."'
                                    GROUP BY fq.faq_id"));
            if ($data['faq_language'] != $current_user_language) {
                define_site_language($data['faq_language']);
            }
        }
    } // Forum threads
    else if (preg_match('/viewthread.php/i', $_SERVER['PHP_SELF']) || preg_match('|/thread/([0-9]+)/|', $_SERVER['REQUEST_URI'],
            $matches) && multilang_table("FO")
    ) {
        if (isset($_GET['thread_id']) && isnum($_GET['thread_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT f.forum_id,f.forum_language, t.thread_id
                                    FROM ".DB_PREFIX."forums f
                                    LEFT JOIN ".DB_PREFIX."forum_threads t ON f.forum_id = t.forum_id
                                    WHERE t.thread_id='".(isset($_GET['thread_id']) ? $_GET['thread_id'] : $matches['1'])."'
                                    GROUP BY t.thread_id"));
            if ($data['forum_language'] != $current_user_language) {
                define_site_language($data['forum_language']);
            }
        }
    } // Forum topics, still need to be Permalink fixed when we settled the Forum / Thread linking.
    else if (preg_match('/index.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['viewforum']) && isnum($_GET['forum_id'])) && multilang_table("FO")) {
        $data = dbarray(dbquery("SELECT forum_cat, forum_branch, forum_language FROM ".DB_PREFIX."forums WHERE forum_id='".stripinput($_GET['forum_id'])."'"));
        if ($data['forum_language'] != $current_user_language) {
            define_site_language($data['forum_language']);
        }
    } // Photo´s
    else if (preg_match('/gallery.php/i', $_SERVER['PHP_SELF']) || preg_match('|/gallery/photo/([0-9]+)/|', $_SERVER['REQUEST_URI'],
            $matches) && multilang_table("PG")
    ) {
        if (isset($_GET['photo_id']) && isnum($_GET['photo_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT pha.album_id, pha.album_language, ph.album_id
                                    FROM ".DB_PREFIX."photo_albums pha
                                    LEFT JOIN ".DB_PREFIX."photos ph ON pha.album_id = ph.album_id
                                    WHERE ph.photo_id='".(isset($_GET['photo_id']) ? $_GET['photo_id'] : $matches['1'])."'
                                    GROUP BY ph.photo_id"));
            if ($data['album_language'] != $current_user_language) {
                define_site_language($data['album_language']);
            }
        }
    } // Photo Albums
    else if (preg_match('/gallery.php/i', $_SERVER['PHP_SELF']) || preg_match('|/gallery/([0-9]+)/|', $_SERVER['REQUEST_URI'],
            $matches) && multilang_table("PG")
    ) {
        if (isset($_GET['album_id']) && isnum($_GET['album_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT album_language FROM ".DB_PREFIX."photo_albums WHERE album_id='".(isset($_GET['album_id']) ? $_GET['album_id'] : $matches['1'])."'"));
            if ($data['album_language'] != $current_user_language) {
                define_site_language($data['album_language']);
            }
        }
    } // Weblinks
    else if (preg_match('/weblinks.php/i', $_SERVER['PHP_SELF']) || preg_match('|/weblinks/([0-9]+)/|', $_SERVER['REQUEST_URI'],
            $matches) && multilang_table("WL")
    ) {
        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT weblink_cat_language FROM ".DB_PREFIX."weblink_cats WHERE weblink_cat_id='".(isset($_GET['cat_id']) ? $_GET['cat_id'] : $matches['1'])."'"));
            if ($data['weblink_cat_language'] != $current_user_language) {
                define_site_language($data['weblink_cat_language']);
            }
        }
    }
}
