<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/admin/controllers/article_admin_model.php
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

class ArticlesAdminModel extends ArticlesServer {
    private static $admin_locale = [];

    /**
     * Articles Table
     *
     * @var array
     */
    protected $default_article_data = [
        'article_id'             => 0,
        'article_draft'          => 0,
        'article_snippet'        => '',
        'article_article'        => '',
        'article_datestamp'      => TIME,
        'article_keywords'       => '',
        'article_breaks'         => 'n',
        'article_allow_comments' => 1,
        'article_allow_ratings'  => 1,
        'article_language'       => LANGUAGE,
        'article_visibility'     => 0,
        'article_subject'        => '',
        'article_cat'            => 0
    ];

    public static function get_articleAdminLocale() {
        if (empty(self::$admin_locale)) {
            $admin_locale_path = LOCALE."English/admin/settings.php";
            if (file_exists(LOCALE.LOCALESET."admin/settings.php")) {
                $admin_locale_path = LOCALE.LOCALESET."admin/settings.php";
            }
            $locale = fusion_get_locale('', [ARTICLE_ADMIN_LOCALE, $admin_locale_path]);
            self::$admin_locale = $locale;
        }

        return self::$admin_locale;
    }
}
