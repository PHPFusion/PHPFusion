<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom9/theme_autoloader.php
| Author: Frederick MC Chan (Chan)
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
defined('IN_FUSION') || exit;

spl_autoload_register(function ($class_name) {
    $path = THEME.'classes'.str_replace(['\\', 'Atom9Theme'], ['/', ''], $class_name).'.inc';

    if (strpos($path, 'IgnitionPacks') !== FALSE) {
        $path = str_replace('classes/', '', $path);
    }

    if (file_exists($path)) {
        require_once $path;
    }
});
