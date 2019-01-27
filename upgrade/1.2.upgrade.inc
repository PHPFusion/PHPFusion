<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog/upgrade/1.2.upgrade.inc
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
$inf_version = '1.2';
require_once INCLUDES."infusions_include.php";

// Update Navigational Links
$inf_updatedbrow[] = DB_ADMIN." SET admin_image='../infusions/blog/blog.svg', admin_link='../infusions/blog/blog_admin.php' WHERE admin_rights='BLOG'";

$check_array = [
    'blog_file_types' => '.pdf,.gif,.jpg,.png,.svg,.zip,.rar,.tar,.bz2,.7z'
];

$settings = get_settings('blog');
foreach ($check_array as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'blog')";
    }
}

if (column_exists(DB_BLOG, 'blog_cat')) {
    $inf_altertable[] = DB_BLOG." CHANGE blog_cat blog_cat VARCHAR(50) NOT NULL DEFAULT '0'";
} else {
    $inf_newcol[] = [
        'table'       => DB_BLOG,
        'column'      => 'blog_cat',
        'column_type' => "VARCHAR(50) NOT NULL DEFUALT '0'"
    ];
}
