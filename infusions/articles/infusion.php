<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
| Author: J.Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."setup.php";

// Infusion general information
$inf_title = $locale['articles']['title'];
$inf_description = $locale['articles']['description'];
$inf_version = "1.00";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "articles";

// Multilanguage table for Administration
$inf_mlt[1] = array(
"title" => $locale['articles']['title'], 
"rights" => "AR",
);

// Create tables
$inf_newtable[1] = DB_ARTICLES." (
	article_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	article_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	article_subject VARCHAR(200) NOT NULL DEFAULT '',
	article_snippet TEXT NOT NULL,
	article_article TEXT NOT NULL,
	article_keywords VARCHAR(250) NOT NULL DEFAULT '',
	article_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	article_breaks CHAR(1) NOT NULL DEFAULT '',
	article_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
	article_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
	article_visibility TINYINT(4) NOT NULL DEFAULT '0',
	article_reads MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	article_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	article_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (article_id),
	KEY article_cat (article_cat),
	KEY article_datestamp (article_datestamp),
	KEY article_reads (article_reads)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[2] = DB_ARTICLE_CATS." (
	article_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	article_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	article_cat_name VARCHAR(100) NOT NULL DEFAULT '',
	article_cat_description VARCHAR(200) NOT NULL DEFAULT '',
	article_cat_sorting VARCHAR(50) NOT NULL DEFAULT 'article_subject ASC',
	article_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY (article_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Automatic enable of the latest articles panel
$inf_insertdbrow[1] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES('Latest articles panel', 'latest_articles_panel', '', '1', '5', 'file', '0', '0', '1', '', '')";

// Position these links under Content Administration
$inf_insertdbrow[2] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('AC', 'article_cats.gif', '".$locale['setup_3001']."', '".INFUSIONS."articles/article_cats_admin.php', '1')";
$inf_insertdbrow[3] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES('A', 'articles.png', '".$locale['setup_3002']."', '".INFUSIONS."articles/articles_admin.php', '1')";

// Create a link for all installed languages
if (!empty($settings['enabled_languages'])) {
$enabled_languages = explode('.', $settings['enabled_languages']);
$k = 4;
	for ($i = 0; $i < count($enabled_languages); $i++) {
		$inf_insertdbrow[$k] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['setup_3002']."', 'infusions/articles/articles.php', '0', '2', '0', '2', '".$enabled_languages[$i]."')";
		$k++;
	}
} else {
		$inf_insertdbrow[4] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['setup_3002']."', 'infusions/articles/articles.php', '0', '2', '0', '2', '".LANGUAGE."')";
}

// Defuse cleaning	
$inf_droptable[1] = DB_ARTICLES;
$inf_droptable[2] = DB_ARTICLE_CATS;
$inf_deldbrow[1] = DB_PANELS." WHERE panel_filename='latest_articles_panel'";
$inf_deldbrow[2] = DB_ADMIN." WHERE admin_rights='A'";
$inf_deldbrow[3] = DB_ADMIN." WHERE admin_rights='AC'";
$inf_deldbrow[4] = DB_SITE_LINKS." WHERE link_url='infusions/articles/articles.php'";
$inf_deldbrow[5] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=a'";
