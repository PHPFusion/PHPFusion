<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/classes/admin/faq_admin_model.inc
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
namespace PHPFusion\FAQ;

class FaqAdminModel extends FaqServer {
    private static $admin_locale = [];
    protected $default_data = [
        'faq_id'         => 0,
        'faq_cat_id'     => 0,
        'faq_question'   => '',
        'faq_answer'     => '',
        'faq_datestamp'  => TIME,
        'faq_name'       => 0,
        'faq_breaks'     => 'n',
        'faq_visibility' => 0,
        'faq_status'     => 1,
        'faq_language'   => LANGUAGE
    ];

    public function __construct() {
        parent::__construct();

        self::$faq_settings = get_settings("faq");
    }

    public static function get_faqAdminLocale() {
        if (empty(self::$admin_locale)) {
            $admin_locale_path = LOCALE.'English/admin/settings.php';
            if (file_exists(LOCALE.LOCALESET.'admin/settings.php')) {
                $admin_locale_path = LOCALE.LOCALESET.'admin/settings.php';
            }
            $locale = fusion_get_locale('', [FAQ_LOCALE, $admin_locale_path]);
            self::$admin_locale = $locale;
        }

        return self::$admin_locale;
    }
}
