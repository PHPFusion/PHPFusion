<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /upgrade/1.0.5.upgrade.inc
| Upgrade Articles to Version 9.0.2 (RC5)
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

if (!column_exists(DB_ARTICLE_CATS, 'article_cat_language')) {
    $inf_altertable[] = DB_ARTICLE_CATS." ADD article_cat_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER article_cat_name";
}
if (!column_exists(DB_ARTICLE_CATS, 'article_cat_parent')) {
    $inf_altertable[] = DB_ARTICLE_CATS." ADD article_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER article_cat_id";
}
if (!column_exists(DB_ARTICLES, 'article_keywords')) {
    $inf_altertable[] = DB_ARTICLES." ADD article_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER article_article";
}
if (!column_exists(DB_ARTICLES, 'article_visibility')) {
    $inf_altertable[] = DB_ARTICLES." ADD article_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER article_datestamp";
}
// Add Multilingual support per article
if (!column_exists(DB_ARTICLES, 'article_language')) {
    $inf_altertable[] = DB_ARTICLES." ADD article_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER article_visibility";
}
$result = dbquery("SELECT article_cat_id, article_cat_access FROM ".DB_ARTICLE_CATS);
if (dbrows($result)) {
    while ($data = dbarray($result)) {
        $inf_updatedbrow[] = DB_ARTICLES." SET article_visibility='-".$data['article_cat_access']."' WHERE article_cat='".$data['article_cat_id']."'";
    }
}
// Remove old cat access table
if (column_exists(DB_ARTICLE_CATS, 'article_cat_access')) {
    $inf_altertable[] = DB_ARTICLE_CATS." DROP COLUMN article_cat_access";
}

// Insert new and old settings tables to Infusions table
$settings = get_settings('A');
$check_array = [
    'article_pagination' => 15,
    'article_extended_required' => 0,
    'article_allow_submission' => 1
];
foreach ($check_array as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'article')";
    }
}

// Remove old cats link and update new path for admin link
$inf_deldbrow[] = DB_PREFIX."admin WHERE admin_link='article_cats.php'";
$inf_updatedbrow[] = DB_PREFIX."admin SET admin_link='../infusions/articles/articles_admin.php' WHERE admin_link='articles.php'";