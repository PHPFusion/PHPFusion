<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/admin/weblinks_admin_model.php
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

class WeblinksAdminModel extends WeblinksServer {

    private static $admin_locale = [];

    /**
     * Articles Table
     *
     * @var array
     */
    protected $default_weblink_data = [
        'weblink_id'          => 0,
        'weblink_name'        => '',
        'weblink_description' => '',
        'weblink_url'         => '',
        'weblink_cat'         => 0,
        'weblink_datestamp'   => TIME,
        'weblink_visibility'  => 0,
        'weblink_status'      => 0,
        'weblink_count'       => 0,
        'weblink_language'    => LANGUAGE,
    ];

    public static function get_WeblinkAdminLocale() {
        if (empty(self::$admin_locale)) {
            $admin_locale_path = LOCALE."English/admin/settings.php";
            if (file_exists(LOCALE.LOCALESET."admin/settings.php")) {
                $admin_locale_path = LOCALE.LOCALESET."admin/settings.php";
            }
            $locale = fusion_get_locale("", [WEBLINK_ADMIN_LOCALE, $admin_locale_path]);

            self::$admin_locale = $locale;
        }

        return self::$admin_locale;
    }
}
