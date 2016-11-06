<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /blog/upgrade/1.0.5.upgrade.inc
| Upgrade FAQ to Version 9.0.2 (RC5)
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
$inf_version = '1.0.5';
require_once INCLUDES."infusions_include.php";

if (column_exists(DB_BLOG, 'blog_cat')) {
    $inf_altertable[] = DB_BLOG." CHANGE blog_cat blog_cat VARCHAR(50) NOT NULL DEFAULT '0'";
} else {
    $inf_newcol[] = array(
        'table' => DB_BLOG,
        'column' => 'blog_cat',
        'column_type' => "VARCHAR(50) NOT NULL DEFUALT '0'"
    );
}