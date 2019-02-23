<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/classes/server.php
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
namespace PHPFusion\Weblinks;

class WeblinksServer {
    private static $weblink_settings = [];
    private static $weblink_instance = NULL;
    private static $weblink_submit_instance = NULL;
    private static $weblink_admin_instance = NULL;

    protected $def_cat = [
        'weblink_categories' => [],
        'weblink_parent'     => '',
        'weblink_tablename'  => '',
        'weblink_filter'     => []
    ];

    protected $def_data = [
        'weblink_categories' => [],
        'weblink_parent'     => '',
        'weblink_item_rows'  => 0,
        'weblink_tablename'  => '',
        'weblink_filter'     => [],
        'weblink_items'      => []
    ];

    public static function Weblinks() {
        if (self::$weblink_instance === NULL) {
            self::$weblink_instance = new WeblinksView();
        }

        return self::$weblink_instance;
    }

    public static function WeblinksSubmit() {
        if (self::$weblink_submit_instance === NULL) {
            self::$weblink_submit_instance = new WeblinksSubmissions();
        }

        return self::$weblink_submit_instance;
    }

    public static function WeblinksAdmin() {
        if (self::$weblink_admin_instance === NULL) {
            self::$weblink_admin_instance = new WeblinksAdminView();
        }

        return self::$weblink_admin_instance;
    }

    public static function get_weblink_settings($key = NULL) {
        if (empty(self::$weblink_settings)) {
            self::$weblink_settings = get_settings("weblinks");
        }
        return $key === NULL ? self::$weblink_settings : (isset(self::$weblink_settings[$key]) ? self::$weblink_settings[$key] : NULL);
    }

}
