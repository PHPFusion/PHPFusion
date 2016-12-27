<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /articles/upgrade/1.2.upgrade.inc
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


// Upgrade 1.2
if (column_exists(DB_ARTICLE_CATS, "article_cat_sorting", FALSE)) {
	dbquery("ALTER TABLE ".DB_ARTICLE_CATS." DROP article_cat_sorting");
}

if (!column_exists(DB_ARTICLE_CATS, "article_cat_visibility", FALSE)) {
	dbquery("ALTER TABLE ".DB_ARTICLE_CATS." ADD article_cat_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER article_cat_name");
}

if (!column_exists(DB_ARTICLE_CATS, "article_cat_status", FALSE)) {
	dbquery("ALTER TABLE ".DB_ARTICLE_CATS." ADD article_cat_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER article_cat_visibility");
}


// Upgrade 1.0.5
if (!column_exists(DB_ARTICLE_CATS, 'article_cat_language', FALSE)) {
    $inf_altertable[] = DB_ARTICLE_CATS." ADD article_cat_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER article_cat_name";
}

if (!column_exists(DB_ARTICLE_CATS, 'article_cat_parent', FALSE)) {
    $inf_altertable[] = DB_ARTICLE_CATS." ADD article_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER article_cat_id";
}

if (!column_exists(DB_ARTICLES, 'article_keywords', FALSE)) {
    $inf_altertable[] = DB_ARTICLES." ADD article_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER article_article";
}

if (!column_exists(DB_ARTICLES, 'article_visibility', FALSE)) {
    $inf_altertable[] = DB_ARTICLES." ADD article_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER article_datestamp";
}

if (!column_exists(DB_ARTICLES, 'article_language', FALSE)) {
    $inf_altertable[] = DB_ARTICLES." ADD article_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER article_visibility";
}

if (column_exists(DB_ARTICLE_CATS, 'article_cat_access', FALSE)) {
	$result = dbquery("SELECT article_cat_id, article_cat_access FROM ".DB_ARTICLE_CATS);
	if (dbrows($result)) {
	    while ($data = dbarray($result)) {
	        $inf_updatedbrow[] = DB_ARTICLES." SET article_visibility='-".$data['article_cat_access']."' WHERE article_cat='".$data['article_cat_id']."'";
	    }
	}
}

if (column_exists(DB_ARTICLE_CATS, 'article_cat_access', FALSE)) {
    $inf_altertable[] = DB_ARTICLE_CATS." DROP COLUMN article_cat_access";
}

// Insert new and old settings tables to Infusions table
$settings = get_settings('article');
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

$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name = 'articles_per_page'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_link='article_cats.php'";
$inf_updatedbrow[] = DB_ADMIN." SET admin_image='../infusions/articles/articles.png', admin_link='../infusions/articles/articles_admin.php' WHERE admin_link='articles.php'";