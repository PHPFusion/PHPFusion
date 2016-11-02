<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/theme_autoloader.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined('IN_FUSION')) {
    die('Access Denied');
}

spl_autoload_register(function ($class_name) {
    $class_path = array(
        'Material\\Main'       => MATERIAL.'classes/Main.inc',
        'Material\\Dashboard'  => MATERIAL.'classes/Dashboard.inc',
        'Material\\Components' => MATERIAL.'classes/Components.inc',
        'Material\\Search'     => MATERIAL.'classes/Search.inc'
    );

    if (isset($class_path[$class_name])) {
        $full_path = $class_path[$class_name];

        if (file_exists($full_path)) {
            require $full_path;
        }
    }
});
