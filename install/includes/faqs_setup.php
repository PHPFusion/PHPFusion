<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/faqs_setup.php
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
if (isset($_POST['uninstall'])) {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."faq_cats");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."faqs");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='FQ'");
	$result = dbquery("DELETE FROM ".$db_prefix."site_links WHERE link_url='faq.php'");
} else {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."faq_cats");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."faqs");
	$result = dbquery("CREATE TABLE ".$db_prefix."faq_cats (
			faq_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			faq_cat_name VARCHAR(200) NOT NULL DEFAULT '',
			faq_cat_description VARCHAR(250) NOT NULL DEFAULT '',
			faq_cat_language VARCHAR(50) NOT NULL DEFAULT '".$_POST['localeset']."',
			PRIMARY KEY(faq_cat_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."faqs (
			faq_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			faq_cat_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			faq_question VARCHAR(200) NOT NULL DEFAULT '',
			faq_answer TEXT NOT NULL,
			PRIMARY KEY(faq_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) 	$fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('FQ', 'faq.gif', '".$locale['setup_3011']."', 'faq.php', '1')");
	if (!$result) 	$fail = TRUE;

	$links_sql = "INSERT INTO ".$db_prefix."site_links (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES \n";
	$links_sql .= implode(",\n", array_map(function ($language) {
		include LOCALE.$language."/setup.php";
		return "('".$locale['setup_3303']."', 'faq.php', '0', '1', '0', '4', '".$language."')";
	}, explode('.', $settings['enabled_languages'])));
	if(!dbquery($links_sql)) {
		$fail = TRUE;
	}
}
?>