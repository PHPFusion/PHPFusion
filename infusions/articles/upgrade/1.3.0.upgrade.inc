<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 1.3.0.upgrade.inc
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
$inf_version = '1.3.0';

$add_column = [
    'article_thumbnail' => " ADD article_thumbnail VARCHAR(100) NOT NULL DEFAULT '' AFTER article_keywords",
];

foreach ($add_column as $key => $value) {
    if (!column_exists(DB_ARTICLES, $key, FALSE)) {
        $inf_altertable[] = DB_ARTICLES.$value;
    }
}

$change_articles = [
    'article_visibility' => " CHANGE article_visibility article_visibility VARCHAR(50) NOT NULL DEFAULT '0'",
];

foreach ($change_articles as $key => $value) {
    if (column_exists(DB_ARTICLES, $key, FALSE)) {
        $inf_altertable[] = DB_ARTICLES.$value;
    }
}

$change_aricle_cats = [
    'article_cat_visibility' => " CHANGE article_cat_visibility article_cat_visibility VARCHAR(50) NOT NULL DEFAULT '0'",
];

foreach ($change_aricle_cats as $key => $value) {
    if (column_exists(DB_ARTICLE_CATS, $key, FALSE)) {
        $inf_altertable[] = DB_ARTICLE_CATS.$value;
    }
}

$settings = get_settings('articles');
$check_array = [
    'article_submission_access' => USER_LEVEL_MEMBER
];
foreach ($check_array as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'articles')";
    }
}
