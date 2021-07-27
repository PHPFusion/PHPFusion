<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 2.0.0.upgrade.inc
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
$inf_version = '2.0.0';

$change_news = [
    'news_visibility' => " CHANGE news_visibility news_visibility VARCHAR(50) NOT NULL DEFAULT '0'",
];
foreach ($change_news as $key => $value) {
    if (column_exists(DB_NEWS, $key, FALSE)) {
        $inf_altertable[] = DB_NEWS.$value;
    }
}

$change_news_cats = [
    'news_cat_visibility' => " CHANGE news_cat_visibility news_cat_visibility VARCHAR(50) NOT NULL DEFAULT '0'",
];
foreach ($change_news_cats as $key => $value) {
    if (column_exists(DB_NEWS_CATS, $key, FALSE)) {
        $inf_altertable[] = DB_NEWS_CATS.$value;
    }
}

$settings = get_settings('news');
$check_array = [
    'news_submission_access' => USER_LEVEL_MEMBER
];
foreach ($check_array as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'news')";
    }
}
