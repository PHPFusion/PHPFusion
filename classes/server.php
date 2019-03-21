<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/classes/server.inc
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
        self::globinf();
    }

    public function globinf() {

        $this->save = (string)filter_input(INPUT_POST, 'savesettings', FILTER_DEFAULT);
        $this->faq_allow_submission = filter_input(INPUT_POST, 'faq_allow_submission', FILTER_DEFAULT);
        $this->catid = isset($_GET['cat_id']) ? $_GET['cat_id'] : 0;
    }

    public static function Faq() {
        if (self::$faq_instance === NULL) {
            self::$faq_instance = new FaqView();
        }

        return self::$faq_instance;
    }

    public static function FaqSubmit() {
        if (self::$faq_submit_instance === NULL) {
            self::$faq_submit_instance = new FaqSubmissions();
        }

        return self::$faq_submit_instance;
    }

    public static function FaqAdmin() {
        if (self::$faq_admin_instance === NULL) {
            self::$faq_admin_instance = new FaqAdminView();
        }

        return self::$faq_admin_instance;
    }
}
