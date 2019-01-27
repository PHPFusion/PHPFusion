<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/upgrade/1.2.upgrade.inc
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

$check_array = [
    // Add support for keywords in article cats items
    'article_cat_visibility' => " ADD article_cat_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER article_cat_name",
    'article_cat_status'     => " ADD article_cat_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER article_cat_visibility",
    'article_cat_parent'     => " ADD article_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER article_cat_id",
    'article_cat_language'   => " ADD article_cat_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER article_cat_name"
];

foreach ($check_array as $key => $value) {
    if (!column_exists(DB_ARTICLE_CATS, $key)) {
        $inf_altertable[] = DB_ARTICLE_CATS.$value;
    }
}

$check_array = [
    // Add support for keywords in article items
    'article_keywords'   => " ADD article_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER article_article",
    'article_visibility' => " ADD article_visibility TINYINT(4) NOT NULL DEFAULT '".USER_LEVEL_MEMBER."' AFTER article_datestamp",
    'article_language'   => " ADD article_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER article_visibility"
];

foreach ($check_array as $key => $value) {
    if (!column_exists(DB_ARTICLES, $key)) {
        $inf_altertable[] = DB_ARTICLES.$value;
    }
}

if (column_exists(DB_ARTICLE_CATS, 'article_cat_access', FALSE)) {
    $result = dbquery("SELECT article_cat_id, article_cat_access FROM ".DB_ARTICLE_CATS);
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            $inf_updatedbrow[] = DB_ARTICLES." SET article_visibility='-".$data['article_cat_access']."' WHERE article_cat='".$data['article_cat_id']."' AND article_visibility != 0";
        }
    }
}

// Update Navigational Links
$inf_updatedbrow[] = DB_ADMIN." SET admin_image='../infusions/articles/articles.svg', admin_link='../infusions/articles/articles_admin.php' WHERE admin_rights='A'";

// Update the existing one, then add the remaining one.
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='articles.php'";

// Multilanguage Sitelinks
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        $locale = fusion_get_locale("", LOCALE.$language."/setup.php");
        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3002']."', 'infusions/articles/articles.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3312']."', 'submit.php?stype=a', ".USER_LEVEL_MEMBER.", '1', '0', '20', '1', '".$language."')";
        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/articles/articles.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=a' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ARTICLE_CATS." WHERE article_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ARTICLES." WHERE article_language='".$language."'";
    }
} else {
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3002']."', 'infusions/articles/articles.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3312']."', 'submit.php?stype=a', ".USER_LEVEL_MEMBER.", '1', '0', '20', '1', '".LANGUAGE."')";
}

$check_array = [
    'article_cat_sorting' => " DROP article_cat_sorting",
    'article_cat_access'  => " DROP COLUMN article_cat_access"
];

foreach ($check_array as $key => $value) {
    if (column_exists(DB_ARTICLE_CATS, $key, FALSE)) {
        $inf_altertable[] = DB_ARTICLE_CATS.$value;
    }
}

// Insert new and old settings tables to Infusions table
$settings = get_settings('article');
$check_array = [
    'article_pagination'        => 15,
    'article_extended_required' => 0,
    'article_allow_submission'  => 1
];
foreach ($check_array as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'article')";
    }
}

if (file_exists(IMAGES."articles/")) {
    include_once BASEDIR.'upgrade/upgrade_functions_include.php';
    $attachment_files = makefilelist(IMAGES."articles/", ".|..|index.php", TRUE);
    foreach ($attachment_files as $file) {
        rename(IMAGES."articles/".$file, INFUSIONS."articles/images/".$file);
    }
    // Remove the whole old dir including rouge files
    rrmdir(IMAGES.'articles');
}

$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name = 'articles_per_page'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_link='article_cats.php'";
