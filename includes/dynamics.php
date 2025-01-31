<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: dynamics.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Class Dynamics
 *
 * @package PHPFusion
 */
class Dynamics {

    private static $instance = NULL;

    private function __construct() {
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$instance->__load_dynamic_components();
        }

        return self::$instance;
    }

    private function __load_dynamic_components() {
        $dynamic_folder = makefilelist(DYNAMICS.'includes/', '.|..|.htaccess|index.php|._DS_Store|.tmp');

        if (!empty($dynamic_folder)) {
            foreach ($dynamic_folder as $folder) {
                require_once DYNAMICS."includes/".$folder;
            }
        }
    }
}
