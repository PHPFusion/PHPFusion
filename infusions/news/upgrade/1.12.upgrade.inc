<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/upgrade/1.12.upgrade.inc
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
$inf_version = '1.12';

// News upgrade file.
// Fix submissions for news gallery
$check_array = [
    'submit_id' => " ADD submit_id MEDIUMINT(11) NOT NULL DEFAULT '0' AFTER news_id",
];
foreach ($check_array as $key => $value) {
    if (!column_exists(DB_NEWS_IMAGES, $key)) {
        $inf_altertable[] = DB_NEWS_IMAGES.$value;
    }
}
