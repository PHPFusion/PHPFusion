<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: server.php
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
namespace PHPFusion\Articles;

class ArticlesServer {
    protected static $article_settings = [];
    private static $article_instance = NULL;
    private static $article_admin_instance = NULL;

    public static function articles() {
        if (self::$article_instance === NULL) {
            self::$article_instance = new ArticlesView();
        }

        return self::$article_instance;
    }

    public static function articlesAdmin() {
        if (self::$article_admin_instance === NULL) {
            self::$article_admin_instance = new ArticlesAdminView();
        }

        return self::$article_admin_instance;
    }

    public static function getArticleSettings() {
        if (empty(self::$article_settings)) {
            self::$article_settings = get_settings("articles");
        }

        return self::$article_settings;
    }

    /**
     * @deprecated use getArticleSettings()
     */
    public static function get_article_settings() {
        return self::getArticleSettings();
    }
}
