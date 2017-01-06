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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
if (!preg_match('/administration/i', $_SERVER['PHP_SELF'])) {

    // Articles
    if (preg_match('/articles.php/i', $_SERVER['PHP_SELF']) || preg_match('|/articles/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                          $matches) && multilang_table("AR")
    ) {
        if (isset($_GET['article_id']) && isnum($_GET['article_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT ac.article_cat_id,ac.article_cat_language, a.article_id
									 FROM ".DB_ARTICLE_CATS." ac
									 LEFT JOIN ".DB_ARTICLES." a ON ac.article_cat_id = a.article_cat
									 WHERE a.article_id='".(isset($_GET['article_id']) ? $_GET['article_id'] : $matches['1'])."'
									 GROUP BY a.article_id"));
            if ($data['article_cat_language']." != ".LANGUAGE) {
                echo set_language($data['article_cat_language']);
            }
        }
    } // Article Cats
    elseif (preg_match('/articles.php/i', $_SERVER['PHP_SELF']) || preg_match('|/articles/category/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                              $matches) && multilang_table("AR")
    ) {
        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT article_cat_language FROM ".DB_ARTICLE_CATS." WHERE article_cat_id='".(isset($_GET['cat_id']) ? $_GET['cat_id'] : $matches['1'])."'"));
            if ($data['article_cat_language']." != ".LANGUAGE) {
                echo set_language($data['article_cat_language']);
            }
        }
    } // Blog
    elseif (preg_match('/blog.php/i', $_SERVER['PHP_SELF']) || preg_match('|/blogs/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                          $matches) && multilang_table("BL")
    ) {
        if (isset($_GET['readmore']) && isnum($_GET['readmore']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT blog_language FROM ".DB_BLOG." WHERE blog_id='".(isset($_GET['readmore']) ? $_GET['readmore'] : $matches['1'])."'"));
            if ($data['blog_language']." != ".LANGUAGE) {
                echo set_language($data['blog_language']);
            }
        }
    } // Blog Cats
    elseif (preg_match('/blog.php/i', $_SERVER['PHP_SELF']) || preg_match('|/blogs/category/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                          $matches) && multilang_table("BL")
    ) {
        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT blog_cat_language FROM ".DB_BLOG_CATS." WHERE blog_cat_id='".(isset($_GET['cat_id']) ? $_GET['cat_id'] : $matches['1'])."'"));
            if ($data['blog_cat_language']." != ".LANGUAGE) {
                echo set_language($data['blog_cat_language']);
            }
        }
    } // Custom Pages
    elseif (preg_match('/viewpage.php/i', $_SERVER['PHP_SELF']) || preg_match('|/pages/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                              $matches) && multilang_table("CP")
    ) {
        if (isset($_GET['page_id']) && isnum($_GET['page_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT page_language FROM ".DB_CUSTOM_PAGES." WHERE page_id='".(isset($_GET['page_id']) ? $_GET['page_id'] : $matches['1'])."'"));
            $page_lang = explode(".", $data['page_language']);
            if (!in_array(LANGUAGE, $page_lang)) {
                echo set_language($page_lang['0']);
            }
        }
    } // Downloads
    elseif (preg_match('/downloads.php/i', $_SERVER['PHP_SELF']) || preg_match('|/downloads/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                               $matches) && multilang_table("DL")
    ) {
        if (isset($_GET['download_id']) && isnum($_GET['download_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT dlc.download_cat_id,dlc.download_cat_language, dl.download_id
									FROM ".DB_DOWNLOAD_CATS." dlc
									LEFT JOIN ".DB_DOWNLOADS." dl ON dlc.download_cat_id = dl.download_cat
									WHERE dl.download_id='".(isset($_GET['download_id']) ? $_GET['download_id'] : $matches['1'])."'
									GROUP BY dl.download_id"));
            if ($data['download_cat_language']." != ".LANGUAGE) {
                echo set_language($data['download_cat_language']);
            }
        }
    } // Download Cats
    elseif (preg_match('/downloads.php/i', $_SERVER['PHP_SELF']) || preg_match('|/downloads/category/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                               $matches) && multilang_table("DL")
    ) {
        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT download_cat_language FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_id='".(isset($_GET['cat_id']) ? $_GET['cat_id'] : $matches['1'])."'"));
            if ($data['download_cat_language']." != ".LANGUAGE) {
                echo set_language($data['download_cat_language']);
            }
        }
    } // News
    elseif (preg_match('/news.php/i', $_SERVER['PHP_SELF']) || preg_match('|/news/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                          $matches) && multilang_table("NS")
    ) {
        if (isset($_GET['readmore']) && isnum($_GET['readmore']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT news_language FROM ".DB_NEWS." WHERE news_id='".(isset($_GET['readmore']) ? $_GET['readmore'] : $matches['1'])."'"));
            if ($data['news_language']." != ".LANGUAGE) {
                echo set_language($data['news_language']);
            }
        }
    } // News Cats
    elseif (preg_match('/news.php/i', $_SERVER['PHP_SELF']) || preg_match('|/news/category/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                          $matches) && multilang_table("NS")
    ) {
        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT news_cat_language FROM ".DB_NEWS_CATS." WHERE news_cat_id='".(isset($_GET['cat_id']) ? $_GET['cat_id'] : $matches['1'])."'"));
            if ($data['news_cat_language']." != ".LANGUAGE) {
                echo set_language($data['news_cat_language']);
            }
        }
    } // FaQ´s
    elseif (preg_match('/faq.php/i', $_SERVER['PHP_SELF']) || preg_match('|/faq/category/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                         $matches) && multilang_table("FQ")
    ) {
        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT fq.faq_id, fq.faq_cat_id, fq.faq_language
									FROM ".DB_FAQS." fq
									WHERE fq.faq_id='".(isset($_GET['cat_id']) ? $_GET['cat_id'] : $matches['1'])."'
									GROUP BY fq.faq_id"));
            if ($data['faq_language']." != ".LANGUAGE) {
                echo set_language($data['faq_language']);
            }
        }
    } // Forum threads
    elseif (preg_match('/viewthread.php/i', $_SERVER['PHP_SELF']) || preg_match('|/thread/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                                $matches) && multilang_table("FO")
    ) {
        if (isset($_GET['thread_id']) && isnum($_GET['thread_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT f.forum_id,f.forum_language, t.thread_id
									FROM ".DB_FORUMS." f
									LEFT JOIN ".DB_FORUM_THREADS." t ON f.forum_id = t.forum_id
									WHERE t.thread_id='".(isset($_GET['thread_id']) ? $_GET['thread_id'] : $matches['1'])."'
									GROUP BY t.thread_id"));
            if ($data['forum_language']." != ".LANGUAGE) {
                echo set_language($data['forum_language']);
            }
        }
    } // Forum topics, still need to be Permalink fixed when we settled the Forum / Thread linking.
    elseif (preg_match('/index.php/i', $_SERVER['PHP_SELF']) && (isset($_GET['viewforum']) && isnum($_GET['forum_id'])) && multilang_table("FO")) {
        $data = dbarray(dbquery("SELECT forum_cat, forum_branch, forum_language FROM ".DB_FORUMS." WHERE forum_id='".stripinput($_GET['forum_id'])."'"));
        if ($data['forum_language']." != ".LANGUAGE) {
            echo set_language($data['forum_language']);
        }
    } // Photo´s
    elseif (preg_match('/photogallery.php/i', $_SERVER['PHP_SELF']) || preg_match('|/gallery/photo/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                                  $matches) && multilang_table("PG")
    ) {
        if (isset($_GET['photo_id']) && isnum($_GET['photo_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT pha.album_id, pha.album_language, ph.album_id
									FROM ".DB_PHOTO_ALBUMS." pha
									LEFT JOIN ".DB_PHOTOS." ph ON pha.album_id = ph.album_id
									WHERE ph.photo_id='".(isset($_GET['photo_id']) ? $_GET['photo_id'] : $matches['1'])."'
									GROUP BY ph.photo_id"));
            if ($data['album_language']." != ".LANGUAGE) {
                echo set_language($data['album_language']);
            }
        }
    } // Photo Albums
    elseif (preg_match('/photogallery.php/i', $_SERVER['PHP_SELF']) || preg_match('|/gallery/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                                  $matches) && multilang_table("PG")
    ) {
        if (isset($_GET['album_id']) && isnum($_GET['album_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT album_language FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".(isset($_GET['album_id']) ? $_GET['album_id'] : $matches['1'])."'"));
            if ($data['album_language']." != ".LANGUAGE) {
                echo set_language($data['album_language']);
            }
        }
    } // Weblinks
    elseif (preg_match('/weblinks.php/i', $_SERVER['PHP_SELF']) || preg_match('|/weblinks/([0-9]+)/|', $_SERVER['REQUEST_URI'],
                                                                              $matches) && multilang_table("WL")
    ) {
        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) || !empty($matches) && $matches['1'] > 0) {
            $data = dbarray(dbquery("SELECT weblink_cat_language FROM ".DB_WEBLINK_CATS." WHERE weblink_cat_id='".(isset($_GET['cat_id']) ? $_GET['cat_id'] : $matches['1'])."'"));
            if ($data['weblink_cat_language']." != ".LANGUAGE) {
                echo set_language($data['weblink_cat_language']);
            }
        }
    }
}
