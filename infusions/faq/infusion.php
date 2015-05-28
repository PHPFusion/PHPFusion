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
$inf_title = $locale['setup_3303'];
$inf_description = $locale['setup_3303'];
$inf_version = "1.00";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "faq";

// Multilanguage table for Administration
$inf_mlt[1] = array(
"title" => $locale['setup_3001'], 
"rights" => "FQ",
);

// Create tables
$inf_newtable[1] = DB_FAQS." (
	faq_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	faq_cat_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	faq_question VARCHAR(200) NOT NULL DEFAULT '',
	faq_answer TEXT NOT NULL,
	PRIMARY KEY(faq_id)	
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[2] = DB_FAQ_CATS." (
	faq_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	faq_cat_name VARCHAR(200) NOT NULL DEFAULT '',
	faq_cat_description VARCHAR(250) NOT NULL DEFAULT '',
	faq_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
	PRIMARY KEY(faq_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Position these links under Content Administration
$inf_insertdbrow[1] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('FQ', 'faq.gif', '".$locale['setup_3011']."', '".INFUSIONS."faq/faq_admin.php', '1')";

// Create a link for all installed languages
if (!empty($settings['enabled_languages'])) {
$enabled_languages = explode('.', $settings['enabled_languages']);
$k = 2;
	for ($i = 0; $i < count($enabled_languages); $i++) {
	include LOCALE."".$enabled_languages[$i]."/setup.php";
		$inf_insertdbrow[$k] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['setup_3303']."', 'infusions/faq/faq.php', '0', '2', '0', '2', '".$enabled_languages[$i]."')";
		$k++;
	}
} else {
	$inf_insertdbrow[2] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES('".$locale['setup_3303']."', 'infusions/faq/faq.php', '0', '2', '0', '2', '".LANGUAGE."')";
}

// Defuse cleaning	
$inf_droptable[1] = DB_FAQS;
$inf_droptable[2] = DB_FAQ_CATS;
$inf_deldbrow[1] = DB_ADMIN." WHERE admin_rights='FQ'";
$inf_deldbrow[2] = DB_SITE_LINKS." WHERE link_url='infusions/faq/faq.php'";
