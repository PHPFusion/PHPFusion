<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/classes/server.php
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
namespace PHPFusion\Articles;

class ArticlesServer {
    protected static $article_settings = [];
    private static $article_instance = NULL;
    private static $article_admin_instance = NULL;

    public static function Articles() {
        if (self::$article_instance === NULL) {
            self::$article_instance = new ArticlesView();
        }

        return self::$article_instance;
    }

    public static function ArticlesAdmin() {
        if (self::$article_admin_instance === NULL) {
            self::$article_admin_instance = new ArticlesAdminView();
        }

        return self::$article_admin_instance;
    }

    public static function get_article_settings() {
        if (empty(self::$article_settings)) {
            self::$article_settings = get_settings("articles");
        }

        return self::$article_settings;
    }
}
