<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/classes/server.php
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

namespace PHPFusion\News;

class NewsServer {

    protected static $news_settings = array();
    private static $news_instance = NULL;
    private static $news_admin_instance = NULL;

    public static function News() {
        if (self::$news_instance === NULL) {
            self::$news_instance = new NewsView();
        }
        return (object)self::$news_instance;
    }

    public static function NewsAdmin() {
        if (self::$news_admin_instance === NULL) {
            self::$news_admin_instance = new NewsAdminView();
        }

        return (object)self::$news_admin_instance;
    }

    public static function get_news_settings() {
        if (empty(self::$news_settings)) {
            self::$news_settings = get_settings("news");
        }
        return self::$news_settings;
    }


}