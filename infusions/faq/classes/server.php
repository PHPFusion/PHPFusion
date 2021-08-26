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
namespace PHPFusion\FAQ;

class FaqServer {
    protected static $faq_settings = [
        'faq_allow_submission' => 0
    ];
    private static $faq_instance = NULL;
    private static $faq_submit_instance = NULL;
    private static $faq_admin_instance = NULL;
    public $save;
    public $faq_allow_submission;
    public $catid;

    public function __construct() {
        self::$faq_settings = get_settings("faq");
        $this->save = post('savesettings');
        $this->catid = check_get('cat_id') ? get('cat_id', FILTER_VALIDATE_INT) : 0;

    }

    public static function faq() {
        if (self::$faq_instance === NULL) {
            self::$faq_instance = new FaqView();
        }

        return self::$faq_instance;
    }

    public static function faqSubmit() {
        if (self::$faq_submit_instance === NULL) {
            self::$faq_submit_instance = new FaqSubmissions();
        }

        return self::$faq_submit_instance;
    }

    public static function faqAdmin() {
        if (self::$faq_admin_instance === NULL) {
            self::$faq_admin_instance = new FaqAdminView();
        }

        return self::$faq_admin_instance;
    }
}
