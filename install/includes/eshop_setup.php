<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/eshop_setup.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

if (isset($_POST['uninstall'])) {
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_cats");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_coupons");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_photo_albums");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_photos");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_cart");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_customers");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_featbanners");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_featitems");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_shippingcats");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_shippingitems");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_payments");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_orders");
	// Remove Custom Inserts
	dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='ESHP'");
	dbquery("DELETE FROM ".$db_prefix."site_links WHERE link_name='".$locale['setup_3053']."'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_cats'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_cat_disp'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_nopp'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_noppf'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_target'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_folderlink'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_selection'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_cookies'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_bclines'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_icons'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_statustext'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_closesamelevel'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_inorder'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_shopmode'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_returnpage'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_ppmail'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_ipr'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_ratios'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_idisp_h'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_idisp_w'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_idisp_h2'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_idisp_w2'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_catimg_w'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_catimg_h'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_image_w'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_image_h'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_image_b'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_image_tw'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_image_th'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_image_t2w'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_image_t2h'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_buynow_color'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_checkout_color'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_cart_color'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_addtocart_color'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_info_color'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_return_color'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_pretext'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_pretext_w'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_listprice'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_currency'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_shareing'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_weightscale'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_vat'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_vat_default'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_terms'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_itembox_w'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_itembox_h'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_cipr'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_newtime'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_freeshipsum'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_coupons'");
	dbquery("DELETE FROM ".$db_prefix."settings WHERE settings_name='eshop_ipn'");
} else {
	// Flush
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_cats");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_coupons");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_photo_albums");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_photos");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_cart");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_featbanners");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_featitems");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_shippingcats");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_shippingitems");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_payments");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_orders");
	dbquery("DROP TABLE IF EXISTS ".$db_prefix."eshop_settings");
	// Create Tables
		$result = dbquery("CREATE TABLE ".$db_prefix."eshop (
				id mediumint(8) unsigned NOT NULL auto_increment,
				title TEXT NOT NULL default '',
				cid mediumint(8) NOT NULL default '0',
				picture varchar(200) NOT NULL default '',
				thumb varchar(200) NOT NULL default '',
				thumb2 varchar(200) NOT NULL default '',
				introtext varchar(255) NOT NULL default '',
				description text NOT NULL,
				anything1 text NOT NULL,
				anything1n varchar(50) NOT NULL default '',
				anything2 text NOT NULL,
				anything2n varchar(50) NOT NULL default '',
				anything3 text NOT NULL,
				anything3n varchar(50) NOT NULL default '',
				weight varchar(10) NOT NULL default '',
				price varchar(15) NOT NULL default '',
				xprice varchar(15) NOT NULL default '',
				stock mediumint(8) NOT NULL default '0',
				version char(3) NOT NULL default '0',
				status char(1) NOT NULL default '',
				active char(1) NOT NULL default '',
				gallery_on char(1) NOT NULL default '',
				delivery varchar(250) NOT NULL default '',
				demo varchar(100) NOT NULL default '',
				cart_on char(1) NOT NULL default '',
				buynow char(1) NOT NULL default '',
				rpage varchar(20) NOT NULL default '',
				icolor text NOT NULL,
				dynf varchar(50) NOT NULL default '',
				dync text NOT NULL,
				qty char(1) NOT NULL default '',
				sellcount mediumint(8) NOT NULL default '0',
				iorder smallint(5) NOT NULL default '0',
				artno varchar(15) NOT NULL default '',
				sartno varchar(15) NOT NULL default '',
				instock mediumint(8) NOT NULL default '0',
				dmulti mediumint(8) NOT NULL default '0',
				cupons char(1) NOT NULL default '',
				access TINYINT(4) NOT NULL default '0',
				campaign char(1) NOT NULL default '',
				comments char(1) NOT NULL default '',
				ratings char(1) NOT NULL default '',
				linebreaks char(1) NOT NULL default '',
				keywords varchar(255) NOT NULL default '',
				product_languages VARCHAR(200) NOT NULL DEFAULT '".fusion_get_settings('enabled_languages')."',
				dateadded int(10) unsigned NOT NULL default '1',
				PRIMARY KEY  (id),
				KEY cid (cid)
				) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if (!$result) {
			$fail = TRUE;
		}

		$result = dbquery("CREATE TABLE ".$db_prefix."eshop_cats (
				cid mediumint(8) unsigned NOT NULL auto_increment,
				title TEXT NOT NULL default '',
				access TINYINT(4) NOT NULL default '0',
				image varchar(45) NOT NULL default '0',
				parentid mediumint(8) NOT NULL default '0',
				status char(1) NOT NULL default '0',
				cat_order mediumint(8) unsigned NOT NULL,
				cat_languages VARCHAR(200) NOT NULL DEFAULT '".fusion_get_settings('enabled_languages')."',
				PRIMARY KEY  (cid)
				) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if (!$result) {
			$fail = TRUE;
		}
	
	$result = dbquery("CREATE TABLE ".$db_prefix."eshop_customers (
			  cuid mediumint(8) NOT NULL default '0',
			  cfirstname varchar(50) NOT NULL default '',
			  clastname varchar(50) NOT NULL default '',
			  cdob varchar(20) NOT NULL default '',
			  ccountry varchar(100) NOT NULL default '',
			  cregion varchar(50) NOT NULL default '',
			  ccity varchar(50) NOT NULL default '',
			  caddress varchar(55) NOT NULL default '',
			  caddress2 varchar(55) NOT NULL default '',
			  cpostcode varchar(10) NOT NULL default '',
			  cphone varchar(20) NOT NULL default '',
			  cfax varchar(20) NOT NULL default '',
			  cemail varchar(50) NOT NULL default '',
			  ccupons text NOT NULL,
			  PRIMARY KEY  (cuid)
			  ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}

	$result = dbquery("CREATE TABLE ".$db_prefix."eshop_photo_albums (
				album_id mediumint(8) unsigned not null auto_increment,
				album_title varchar(100) not null default '',
				album_description text not null,
				album_thumb varchar(100) not null default '',
				album_user mediumint(11) unsigned not null default '0',
				album_access TINYINT(4) not null default '0',
				album_order smallint(5) unsigned not null default '0',
				album_datestamp int(10) unsigned not null default '0',
				album_language varchar(50) not null default '',
				PRIMARY KEY (album_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");

	if (!$result) {
		$fail = TRUE;
	}

	$result = dbquery("CREATE TABLE ".$db_prefix."eshop_photos (
			photo_id mediumint(8) unsigned NOT NULL auto_increment,
			album_id mediumint(8) unsigned NOT NULL default '0',
			photo_title varchar(100) NOT NULL default '',
			photo_description text NOT NULL,
			photo_filename varchar(100) NOT NULL default '',
			photo_thumb1 varchar(100) NOT NULL default '',
			photo_thumb2 varchar(100) NOT NULL default '',
			photo_datestamp int(10) unsigned NOT NULL default '0',
			photo_user mediumint(8) unsigned NOT NULL default '0',
			photo_views mediumint(8) unsigned NOT NULL default '0',
			photo_order smallint(5) unsigned NOT NULL default '0',
			photo_allow_comments tinyint(1) unsigned NOT NULL default '1',
			photo_allow_ratings tinyint(1) unsigned NOT NULL default '1',
			photo_last_viewed int(10) unsigned NOT NULL default '1',
			PRIMARY KEY  (photo_id),
			KEY photo_user (photo_user)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if (!$result) {
			$fail = TRUE;
		}

		$result = dbquery("CREATE TABLE ".$db_prefix."eshop_cart (
			tid mediumint(8) NOT NULL auto_increment,
			puid varchar(45) NOT NULL default '0',
			prid mediumint(8) unsigned NOT NULL default '0',
			artno varchar(50) NOT NULL default '',
			citem varchar(250) NOT NULL default '',
			cimage varchar(50) NOT NULL default '',
			cqty mediumint(8) NOT NULL default '0',
			cclr varchar(50) NOT NULL default '',
			cdyn varchar(50) NOT NULL default '',
			cdynt varchar(55) NOT NULL default '',
			cprice varchar(15) NOT NULL default '',
			cweight varchar(10) NOT NULL default '',
			ccupons tinyint(1) NOT NULL default '0',
			cadded int(10) NOT NULL default '0',
			PRIMARY KEY  (tid),
			KEY puid (puid)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if (!$result) {
			$fail = TRUE;
		}

		$result = dbquery("CREATE TABLE ".$db_prefix."eshop_shippingcats (
			cid mediumint(8) NOT NULL auto_increment,
			title varchar(50) NOT NULL default '',
			image varchar(100) NOT NULL default '',
			PRIMARY KEY  (cid)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if (!$result) {
			$fail = TRUE;
		}

		$result = dbquery("CREATE TABLE ".$db_prefix."eshop_shippingitems (
			sid mediumint(8) NOT NULL auto_increment,
			cid mediumint(8) NOT NULL default '0',
			method varchar(100) NOT NULL default '',
			dtime tinyint(1) NOT NULL default '0',
			destination tinyint(1) NOT NULL default '0',
			weightmin decimal(10,0) NOT NULL default '0.00',
			weightmax decimal(10,0) NOT NULL default '0.00',
			weightcost smallint(5) NOT NULL default '0',
			initialcost smallint(5) NOT NULL default '0',
			active char(1) NOT NULL default '',
			PRIMARY KEY  (sid)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if (!$result) {
			$fail = TRUE;
		}

		$result = dbquery("CREATE TABLE ".$db_prefix."eshop_payments (
			pid mediumint(8) NOT NULL auto_increment,
			method text NOT NULL,
			description text NOT NULL,
			image varchar(100) NOT NULL default '',
			surcharge smallint(5) NOT NULL default '0',
			code text NOT NULL,
			cfile varchar(100) NOT NULL default '',
			active char(1) NOT NULL default '',
			PRIMARY KEY  (pid)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if (!$result) {
			$fail = TRUE;
		}

		$result = dbquery("CREATE TABLE ".$db_prefix."eshop_orders (
			oid mediumint(8) NOT NULL auto_increment,
			ouid varchar(15) NOT NULL default '',
			oname varchar(50) NOT NULL default '',
			oitems text NOT NULL,
			oorder text NOT NULL,
			oemail varchar(50) NOT NULL default '',
			opaymethod tinyint(2) NOT NULL default '0',
			oshipmethod tinyint(2) NOT NULL default '0',
			odiscount varchar(10) NOT NULL default '',
			ovat mediumint(8) NOT NULL default '0',
			ototal varchar(50) NOT NULL default '',
			omessage varchar(255) NOT NULL default '',
			oamessage varchar(255) NOT NULL default '',
			ocompleted char(1) NOT NULL default '',
			opaid char(1) NOT NULL default '',
			odate int(10) NOT NULL default '0',
			PRIMARY KEY  (oid),
			KEY ouid (ouid)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if (!$result) {
			$fail = TRUE;
		}

		$result = dbquery("CREATE TABLE ".$db_prefix."eshop_coupons (
			cuid VARCHAR( 15 ) NOT NULL DEFAULT  '',
			cuname varchar(50) NOT NULL default '',
			cutype char(1) NOT NULL default '',
			cuvalue smallint(5) NOT NULL default '0',
			custart INT( 10 ) NOT NULL ,
			cuend INT( 10 ) NOT NULL ,
			active CHAR( 1 ) NOT NULL DEFAULT  '',
			PRIMARY KEY ( cuid )
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if (!$result) {
			$fail = TRUE;
		}
		
		$result = dbquery("CREATE TABLE ".$db_prefix."eshop_featitems (
			featitem_id mediumint(8) unsigned NOT NULL auto_increment,
			featitem_title varchar(100) NOT NULL default '',
			featitem_description text NOT NULL,
			featitem_item mediumint(8) unsigned NOT NULL default '0',
			featitem_cid mediumint(8) unsigned NOT NULL default '0',
			featitem_order smallint(5) unsigned NOT NULL default '0',
			PRIMARY KEY  (featitem_id),
			KEY cid (featitem_item)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if (!$result) {
			$fail = TRUE;
		}

		$result = dbquery("CREATE TABLE ".$db_prefix."eshop_featbanners (
			featbanner_aid mediumint(8) unsigned NOT NULL auto_increment,
			featbanner_title varchar(50) NOT NULL default '',
			featbanner_id mediumint(8) unsigned NOT NULL default '0',
			featbanner_url varchar(100) NOT NULL default '',
			featbanner_cat mediumint(8) unsigned NOT NULL default '0',
			featbanner_banner varchar(100) NOT NULL default '',
			featbanner_cid mediumint(8) unsigned NOT NULL default '0',
			featbanner_order smallint(5) unsigned NOT NULL default '0',
			PRIMARY KEY  (featbanner_aid),
			KEY featbanner_id (featbanner_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		if (!$result) {
			$fail = TRUE;
		}

	// Core Inserts
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('ESHP', 'eshop.gif', '".$locale['setup_3053']."', 'eshop.php', '1')");
	if (!$result) $fail = TRUE;
	// go for settings.
	$links_sql = "INSERT INTO ".$db_prefix."site_links (link_name, link_cat, link_icon, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES \n";
	$links_sql .= implode(",\n", array_map(function ($language) {
		include LOCALE.$language."/setup.php";
		return "('".$locale['setup_3053']."', '0', '', 'eshop.php', '0', '2', '0', '3', '".$language."')";
	}, explode('.', fusion_get_settings('enabled_languages'))));
	if(!dbquery($links_sql)) {
		$fail = TRUE;
	}
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_ipn', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_cats', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_cat_disp', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_nopp', '6')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_noppf', '9')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_target', '_self')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_folderlink', '0')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_selection', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_cookies', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_bclines', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_icons', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_statustext', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_closesamelevel', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_inorder', '0')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_shopmode', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_returnpage', 'ordercompleted.php')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_ppmail', '')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_ipr', '3')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_ratios', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_idisp_h', '130')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_idisp_w', '100')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_idisp_h2', '180')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_idisp_w2', '250')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_catimg_w', '100')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_catimg_h', '100')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_image_w', '6400')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_image_h', '6400')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_image_b', '9999999')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_image_tw', '150')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_image_th', '100')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_image_t2w', '250')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_image_t2h', '250')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_buynow_color', 'blue')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_checkout_color', 'green')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_cart_color', 'red')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_addtocart_color', 'magenta')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_info_color', 'orange')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_return_color', 'yellow')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_pretext', '0')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_pretext_w', '190px')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_listprice', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_currency', 'USD')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_shareing', '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_weightscale', 'KG')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_vat', '25')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_vat_default', '0')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_terms', '<h2> Ordering </h2><br />\r\nWhilst all efforts are made to ensure accuracy of description, specifications and pricing there may <br />be occasions where errors arise. Should such a situation occur [Company name] cannot accept your order. <br /> In the event of a mistake you will be contacted with a full explanation and a corrected offer. <br />The information displayed is considered as an invitation to treat not as a confirmed offer for sale. \r\nThe contract is confirmed upon supply of goods.\r\n<br /><br /><br />\r\n<h2>Delivery and Returns</h2><br />\r\n[Company name] returns policy has been set up to keep costs down and to make the process as easy for you as possible. You must contact us and be in receipt of a returns authorisation (RA) number before sending any item back. Any product without a RA number will not be refunded. <br /><br /><br />\r\n<h2> Exchange </h2><br />\r\n If when you receive your product(s), you are not completely satisfied you may return the items to us, within seven days of exchange or refund. Returns will take approximately 5 working days for the process once the goods have arrived. Items must be in original packaging, in all original boxes, packaging materials, manuals blank warranty cards and all accessories and documents provided by the manufacturer.<br /><br /><br />\r\n\r\nIf our labels are removed from the product â€“ the warranty becomes void.<br /><br /><br />\r\n\r\nWe strongly recommend that you fully insure your package that you are returning. We suggest the use of a carrier that can provide you with a proof of delivery. [Company name] will not be held responsible for items lost or damaged in transit.<br /><br /><br />\r\n\r\nAll shipping back to [Company name] is paid for by the customer. We are unable to refund you postal fees.<br /><br /><br />\r\n\r\nAny product returned found not to be defective can be refunded within the time stated above and will be subject to a 15% restocking fee to cover our administration costs. Goods found to be tampered with by the customer will not be replaced but returned at the customers expense. <br /><br /><br />\r\n\r\n If you are returning items for exchange please be aware that a second charge may apply. <br /><br /><br />\r\n\r\n<h2>Non-Returnable </h2><br />\r\n For reasons of hygiene and public health, refunds/exchanges are not available for used ......... (this does not apply to faulty goods â€“ faulty products will be exchanged like for like)<br /><br /><br />\r\n\r\nDiscounted or our end of line products can only be returned for repair no refunds of replacements will be made.<br /><br /><br />\r\n\r\n<h2> Incorrect/Damaged Goods </h2><br />\r\n\r\n We try very hard to ensure that you receive your order in pristine condition. If you do not receive your products ordered. Please contract us. In the unlikely event that the product arrives damaged or faulty, please contact [Company name] immediately, this will be given special priority and you can expect to receive the correct item within 72 hours. Any incorrect items received all delivery charges will be refunded back onto you credit/debit card.<br /><br /><br />\r\n\r\n<h2>Delivery service</h2><br />\r\nWe try to make the delivery process as simple as possible and our able to send your order either you home or to your place of work.<br /><br /><br />\r\n\r\nDelivery times are calculated in working days Monday to Friday. If you order after 4 pm the next working day will be considered the first working day for delivery. In case of bank holidays and over the Christmas period, please allow an extra two working days.<br /><br /><br />\r\n\r\nWe aim to deliver within 3 working days but sometimes due to high order volume certain in sales periods please allow 4 days before contacting us. We will attempt to email you if we become aware of an unexpected delay. <br /><br /><br />\r\n\r\nAll small orders are sent out via royal mail 1st packets post service, if your order is over Â£15.00 it will be sent out via royal mails recorded packet service, which will need a signature, if you are not present a card will be left to advise you to pick up your goods from the local sorting office.<br /><br /><br />\r\n\r\nEach item will be attempted to be delivered twice. Failed deliveries after this can be delivered at an extra cost to you or you can collect the package from your local post office collection point.<br /><br /><br />\r\n\r\n<h2>Export restrictions</h2><br /><br /><br />\r\n\r\nAt present [Company name] only sends goods within the [Country]. We plan to add exports to our services in the future. If however you have a special request please contact us your requirements.<br /><br /><br />\r\n\r\n<h2> Privacy Notice </h2><br />\r\n\r\nThis policy covers all users who register to use the website. It is not necessary to purchase anything in order to gain access to the searching facilities of the site.<br /><br /><br />\r\n\r\n<h2> Security </h2><br />\r\nWe have taken the appropriate measures to ensure that your personal information is not unlawfully processed. [Company name] uses industry standard practices to safeguard the confidentiality of your personal identifiable information, including â€˜firewallsâ€™ and secure socket layers. <br /><br /><br />\r\n\r\nDuring the payment process, we ask for personal information that both identifies you and enables us to communicate with you. <br /><br /><br />\r\n\r\nWe will use the information you provide only for the following purposes.<br /><br /><br />\r\n\r\n* To send you newsletters and details of offers and promotions in which we believe you will be interested. <br />\r\n* To improve the content design and layout of the website. <br />\r\n* To understand the interest and buying behavior of our registered users<br />\r\n* To perform other such general marketing and promotional focused on our products and activities. <br />\r\n\r\n<h2> Conditions Of Use </h2><br />\r\n[Company name] and its affiliates provide their services to you subject to the following conditions. If you visit our shop at [Company name] you accept these conditions. Please read them carefully, [Company name] controls and operates this site from its offices within the [Country]. The laws of [Country] relating to including the use of, this site and materials contained. <br /><br /><br />\r\n\r\nIf you choose to access from another country you do so on your own initiave and are responsible for compliance with applicable local lands. <br /><br /><br />\r\n\r\n<h2> Copyrights </h2><br />\r\nAll content includes on the site such as text, graphics logos button icons images audio clips digital downloads and software are all owned by [Company name] and are protected by international copyright laws. <br /><br /><br />\r\n\r\n<h2> License and Site Access </h2><br />\r\n[Company name] grants you a limited license to access and make personal use of this site. This license doses not include any resaleâ€™s of commercial use of this site or its contents any collection and use of any products any collection and use of any product listings descriptions or prices any derivative use of this site or its contents, any downloading or copying of account information. For the benefit of another merchant or any use of data mining, robots or similar data gathering and extraction tools.<br /><br /><br />\r\n\r\nThis site may not be reproduced duplicated copied sold â€“ resold or otherwise exploited for any commercial exploited without written consent of [Company name].<br /><br /><br />\r\n\r\n<h2> Product Descriptions </h2><br />\r\n[Company name] and its affiliates attempt to be as accurate as possible however we do not warrant that product descriptions or other content is accurate complete reliable, or error free.<br /><br /><br />\r\nFrom time to time there may be information on [Company name] that contains typographical errors, inaccuracies or omissions that may relate to product descriptions, pricing and availability.<br /><br /><br />\r\nWe reserve the right to correct ant errors inaccuracies or omissions and to change or update information at any time without prior notice. (Including after you have submitted your order) We apologies for any inconvenience this may cause you. <br /><br /><br />\r\n\r\n<h2> Prices </h2><br />\r\nPrices and availability of items are subject to change without notice the prices advertised on this site are for orders placed and include VAT and delivery.<br /><br /><br />\r\n<br /><br /><br />\r\nPlease review our other policies posted on this site. These policies also govern your visit to [Company name]')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_itembox_w', '200px')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_itembox_h', '300px')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_cipr', '3')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_newtime', '604800')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_freeshipsum', '0')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."settings (settings_name, settings_value) VALUES ('eshop_coupons', '0')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('ESHP', 'eshop.gif', '".$locale['setup_3053']."', 'settings_eshop.php', '4')");
	if (!$result) $fail = TRUE;
	// Local Inserts
	$result = dbquery("INSERT INTO ".$db_prefix."eshop_shippingcats (cid, title, image) VALUES 
				(1, 'Generic', 'generic.png'),
				(2, 'DHL', 'dhl.png'),
				(3, 'FedEX', 'fedex.png'),	
				(4, 'UPS', 'ups.png'), 
				(5, 'Post Office', 'postoffice.png'), 
				(6, 'Ptt', 'ptt.png'),
				(7, 'TNT', 'tnt.png')");
	if (!$result) $fail = TRUE;

	$result = dbquery("INSERT INTO ".$db_prefix."eshop_shippingitems (sid, cid, method, dtime, destination, weightmin, weightmax, weightcost, initialcost, active) VALUES
				(1, 1, 'No Shipping - Visit store', '0', '0', '0.00', '0', 0, 0, '1'),
				(2, 4, 'UPS Express', '1', '2', '0.00', '150', 0, 250, '1'),
				(3, 4, 'UPS Express', '1', '2', '0.00', '150', 0, 250, '1'),
				(4, 2, 'DHL Worldwide Priority Express', '2', '3', '0.00', '150', 6, 69, '1'),
				(5, 2, 'DHL National Priority Express', '2', '2', '0.00', '150', 0, 150, '1')");
	if (!$result) $fail = TRUE;
	$result = dbquery("INSERT INTO ".$db_prefix."eshop_payments (pid, method, description, image, surcharge, code, cfile, active) VALUES
				(1, 'Invoice', 'We will send an Invoice to your adress. \r\nA credit check will be run.\r\nIn order to make a credit check we need your complete date of birth.', 'invoice.png', 2, '', 'invoice.php', '1'),
				(2, 'PayPal', 'Checkout with PayPal, It´s safe and fast. \r\nYou can use most credit cards here.', 'Paypal.png', 0, '', 'paypal.php', '1'),
				(3, 'Prepayment', 'If you select this option you will need to transfer money directly to our account from your account. \r\nSubmit this order for account details.', 'creditcards.png', 0, '', 'prepayment.php', '1'),
				(4, 'Visit store', 'If you select this option you will need to visit our store and pay your order.\r\n Please bring your OrderID.', 'cash.png', 0, '', '', '1')");
				if (!$result) $fail = TRUE;
}
?>