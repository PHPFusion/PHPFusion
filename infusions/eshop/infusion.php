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
$inf_title = $locale['eshop']['title'];
$inf_description = $locale['eshop']['description'];
$inf_version = "1.00";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "eshop";

// Multilanguage table for Administration
$inf_mlt[1] = array(
"title" => $locale['eshop']['title'], 
"rights" => "ES",
);

// Create tables
$inf_newtable[1] = DB_ESHOP." (
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
	product_languages VARCHAR(200) NOT NULL DEFAULT '".$settings['enabled_languages']."',
	dateadded int(10) unsigned NOT NULL default '1',
	PRIMARY KEY  (id),
	KEY cid (cid)	
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[2] = DB_ESHOP_CATS." (
	cid mediumint(8) unsigned NOT NULL auto_increment,
	title TEXT NOT NULL default '',
	access TINYINT(4) NOT NULL default '0',
	image varchar(45) NOT NULL default '0',
	parentid mediumint(8) NOT NULL default '0',
	status char(1) NOT NULL default '0',
	cat_order mediumint(8) unsigned NOT NULL,
	cat_languages VARCHAR(200) NOT NULL DEFAULT '".$settings['enabled_languages']."',
	PRIMARY KEY  (cid)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[3] = DB_ESHOP_COUPONS." (
	cuid VARCHAR( 15 ) NOT NULL DEFAULT  '',
	cuname varchar(50) NOT NULL default '',
	cutype char(1) NOT NULL default '',
	cuvalue smallint(5) NOT NULL default '0',
	custart INT( 10 ) NOT NULL ,
	cuend INT( 10 ) NOT NULL ,
	active CHAR( 1 ) NOT NULL DEFAULT  '',
	PRIMARY KEY ( cuid )
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[4] = DB_ESHOP_PHOTOS." (
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
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[5] = DB_ESHOP_ALBUMS." (
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
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[6] = DB_ESHOP_CART." (
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
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[7] = DB_ESHOP_CUSTOMERS." (
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
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[8] = DB_ESHOP_FEATBANNERS." (
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
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[9] = DB_ESHOP_FEATITEMS." (
	featitem_id mediumint(8) unsigned NOT NULL auto_increment,
	featitem_title varchar(100) NOT NULL default '',
	featitem_description text NOT NULL,
	featitem_item mediumint(8) unsigned NOT NULL default '0',
	featitem_cid mediumint(8) unsigned NOT NULL default '0',
	featitem_order smallint(5) unsigned NOT NULL default '0',
	PRIMARY KEY  (featitem_id),
	KEY cid (featitem_item)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[10] = DB_ESHOP_SHIPPINGCATS." (
	cid mediumint(8) NOT NULL auto_increment,
	title varchar(50) NOT NULL default '',
	image varchar(100) NOT NULL default '',
	PRIMARY KEY  (cid)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[11] = DB_ESHOP_SHIPPINGITEMS." (
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
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[12] = DB_ESHOP_PAYMENTS." (
	pid mediumint(8) NOT NULL auto_increment,
	method text NOT NULL,
	description text NOT NULL,
	image varchar(100) NOT NULL default '',
	surcharge smallint(5) NOT NULL default '0',
	code text NOT NULL,
	cfile varchar(100) NOT NULL default '',
	active char(1) NOT NULL default '',
	PRIMARY KEY  (pid)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[13] = DB_ESHOP_ORDERS." (
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
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Automatic enable of the latest articles panel
$inf_insertdbrow[1] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES('eShop cart panel', 'eshop_cart_panel', '', '4', '3', 'file', '0', '1', '1', '', '')";

// Position these links under Content Administration
$inf_insertdbrow[2] = DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('ESHP', 'eshop.gif', '".$locale['eshop']['title']."', '".SHOP."admin/eshop.php', '1')";

// Create a link for all installed languages
if (!empty($settings['enabled_languages'])) {
$enabled_languages = explode('.', $settings['enabled_languages']);
$k = 3;
	for ($i = 0; $i < count($enabled_languages); $i++) {
		$inf_insertdbrow[$k] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['eshop']['title']."', 'infusions/eshop/eshop.php', '0', '2', '0', '2', '".$enabled_languages[$i]."')";
		$k++;
	}
} else {
		$inf_insertdbrow[3] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['eshop']['title']."', 'infusions/eshop/eshop.php', '0', '2', '0', '2', '".LANGUAGE."')";
}

$inf_insertdbrow[4] = DB_ESHOP_SHIPPINGCATS." (cid, title, image) VALUES
(5, 'DHL', 'dhl.png'),
(4, 'FedEX', 'fedex.png'),
(6, 'UPS', 'ups.png'),
(7, 'Generic', 'generic.png'),
(10, 'Post Office', 'postoffice.png'),
(11, 'Ptt', 'ptt.png'),
(12, 'TNT', 'tnt.png')";

$inf_insertdbrow[5] = DB_ESHOP_SHIPPINGITEMS." (sid, cid, method, dtime, destination, weightmin, weightmax, weightcost, initialcost, active) VALUES
(13, 6, 'UPS Express', '1 Day', '2', '0.00', '150', 0, 250, '1'),
(10, 5, 'DHL Worldwide Priority Express', '1 - 2 Days', '3', '0.00', '150', 6, 69, '1'),
(12, 5, 'DHL National Priority Express', '1 - 2 Days', '2', '0.00', '150', 0, 150, '1')";

$inf_insertdbrow[6] = DB_ESHOP_PAYMENTS." (pid, method, description, image, surcharge, code, cfile, active) VALUES
(1, 'Invoice', 'We will send an Invoice to your adress. \r\nA credit check will be run.\r\nIn order to make a credit check we need your complete date of birth.', 'invoice.png', 2, '', 'invoice.php', '1'),
(2, 'PayPal', 'Checkout with PayPal, It´s safe and fast. \r\nYou can use most credit cards here.', 'Paypal.png', 0, '', 'paypal.php', '1'),
(3, 'Prepayment', 'If you select this option you will need to transfer money directly to our account from your account. \r\nSubmit this order for account details.', 'creditcards.png', 0, '', 'prepayment.php', '1')";

// Insert settings
$inf_insertdbrow[7] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_ipn', '1', 'eshop')";
$inf_insertdbrow[8] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cats', '1', 'eshop')";
$inf_insertdbrow[9] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cat_disp', '1', 'eshop')";
$inf_insertdbrow[10] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_nopp', '6', 'eshop')";
$inf_insertdbrow[11] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_noppf', '9', 'eshop')";
$inf_insertdbrow[12] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_target', '_self', 'eshop')";
$inf_insertdbrow[13] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_folderlink', '0', 'eshop')";
$inf_insertdbrow[14] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_selection', '1', 'eshop')";
$inf_insertdbrow[15] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cookies', '1', 'eshop')";
$inf_insertdbrow[16] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_bclines', '1', 'eshop')";
$inf_insertdbrow[17] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_icons', '1', 'eshop')";
$inf_insertdbrow[19] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_statustext', '1', '1', 'eshop')";
$inf_insertdbrow[20] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_closesamelevel', '1', 'eshop')";
$inf_insertdbrow[21] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_inorder', '0', 'eshop')";
$inf_insertdbrow[22] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_shopmode', '1', 'eshop')";
$inf_insertdbrow[23] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_returnpage', 'ordercompleted.php', 'eshop')";
$inf_insertdbrow[24] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_ppmail', '', 'eshop')";
$inf_insertdbrow[25] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_ipr', '3', 'eshop')";
$inf_insertdbrow[26] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_ratios', '1', 'eshop')";
$inf_insertdbrow[27] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_idisp_h', '130', 'eshop')";
$inf_insertdbrow[28] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_idisp_w', '100', 'eshop')";
$inf_insertdbrow[29] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_idisp_h2', '180', 'eshop')";
$inf_insertdbrow[30] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_idisp_w2', '250', 'eshop')";
$inf_insertdbrow[31] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_catimg_w', '100', 'eshop')";
$inf_insertdbrow[32] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_catimg_h', '100', 'eshop')";
$inf_insertdbrow[33] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_w', '6400', 'eshop')";
$inf_insertdbrow[34] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_h', '6400', 'eshop')";
$inf_insertdbrow[35] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_b', '9999999', 'eshop')";
$inf_insertdbrow[36] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_tw', '150', 'eshop')";
$inf_insertdbrow[37] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_th', '100', 'eshop')";
$inf_insertdbrow[38] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_t2w', '250', 'eshop')";
$inf_insertdbrow[39] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_t2h', '250', 'eshop')";
$inf_insertdbrow[40] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_buynow_color', 'blue', 'eshop')";
$inf_insertdbrow[41] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_checkout_color', 'green', 'eshop')";
$inf_insertdbrow[42] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cart_color', 'red', 'eshop')";
$inf_insertdbrow[43] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_addtocart_color', 'magenta', 'eshop')";
$inf_insertdbrow[44] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_info_color', 'orange', 'eshop')";
$inf_insertdbrow[45] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_return_color', 'yellow', 'eshop')";
$inf_insertdbrow[46] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_pretext', '0', 'eshop')";
$inf_insertdbrow[47] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_pretext_w', '190px', 'eshop')";
$inf_insertdbrow[48] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_listprice', '1', 'eshop')";
$inf_insertdbrow[49] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_currency', 'USD', 'eshop')";
$inf_insertdbrow[50] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_shareing', '1', 'eshop')";
$inf_insertdbrow[51] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_weightscale', 'KG', 'eshop')";
$inf_insertdbrow[52] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_vat', '25', 'eshop')";
$inf_insertdbrow[53] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_vat_default', '0', 'eshop')";
$inf_insertdbrow[54] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_terms', '<h2> Ordering </h2><br />\r\nWhilst all efforts are made to ensure accuracy of description, specifications and pricing there may <br />be occasions where errors arise. Should such a situation occur [Company name] cannot accept your order. <br /> In the event of a mistake you will be contacted with a full explanation and a corrected offer. <br />The information displayed is considered as an invitation to treat not as a confirmed offer for sale. \r\nThe contract is confirmed upon supply of goods.\r\n<br /><br /><br />\r\n<h2>Delivery and Returns</h2><br />\r\n[Company name] returns policy has been set up to keep costs down and to make the process as easy for you as possible. You must contact us and be in receipt of a returns authorisation (RA) number before sending any item back. Any product without a RA number will not be refunded. <br /><br /><br />\r\n<h2> Exchange </h2><br />\r\n 
If when you receive your product(s), you are not completely satisfied you may return the items to us, within seven days of exchange or refund. Returns will take approximately 5 working days for the process once the goods have arrived. Items must be in original packaging, in all original boxes, packaging materials, manuals blank warranty cards and all accessories and documents provided by the manufacturer.<br /><br /><br />\r\n\r\nIf our labels are removed from the product â€“ the warranty becomes void.<br /><br /><br />\r\n\r\nWe strongly recommend that you fully insure your package that you are returning. We suggest the use of a carrier that can provide you with a proof of delivery. [Company name] will not be held responsible for items lost or damaged in transit.<br /><br /><br />\r\n\r\nAll shipping back to [Company name] is paid for by the customer. We are unable to refund you postal fees.<br /><br /><br />\r\n\r\nAny product returned found not to be defective can be refunded within the time stated above and will be subject to a 15% restocking fee to cover our administration costs. Goods found to be tampered with by the customer will not be replaced but returned at the customers expense. <br /><br /><br />\r\n\r\n If you are returning items for exchange please be aware that a second charge may apply. <br /><br /><br />\r\n\r\n<h2>Non-Returnable </h2><br />\r\n For reasons of hygiene and public health, refunds/exchanges are not available for used ......... (this does not apply to faulty goods â€“ faulty products will be exchanged like for like)<br /><br /><br />\r\n\r\nDiscounted or our end of line products can only be returned for repair no refunds of replacements will be made.<br /><br /><br />\r\n\r\n<h2> Incorrect/Damaged Goods </h2><br />\r\n\r\n We try very hard to ensure that you receive your order in pristine condition. If you do not receive your products ordered. Please contract us. In the unlikely event that the product arrives damaged or faulty, please contact [Company name] immediately, this will be given special priority and you can expect to receive the correct item within 72 hours. Any incorrect items received all delivery charges will be refunded back onto you credit/debit card.<br /><br /><br />\r\n\r\n<h2>Delivery service</h2><br />\r\nWe try to make the delivery process as simple as possible and our able to send your order either you home or to your place of work.<br /><br /><br />\r\n\r\nDelivery times are calculated in working days Monday to Friday. If you order after 4 pm the next working day will be considered the first working day for delivery. In case of bank holidays and over the Christmas period, please allow an extra two working days.<br /><br /><br />\r\n\r\nWe aim to deliver within 3 working days but sometimes due to high order volume certain in sales periods please allow 4 days before contacting us. We will attempt to email you if we become aware of an unexpected delay. <br /><br /><br />\r\n\r\nAll small orders are sent out via royal mail 1st packets post service, if your order is over Â£15.00 it will be sent out via royal mails recorded packet service, which will need a signature, if you are not present a card will be left to advise you to pick up your goods from the local sorting office.<br /><br /><br />\r\n\r\nEach item will be attempted to be delivered twice. Failed deliveries after this can be delivered at an extra cost to you or you can collect the package from your local post office collection point.<br /><br /><br />\r\n\r\n<h2>Export restrictions</h2><br /><br /><br />\r\n\r\nAt present [Company name] only sends goods within the [Country]. We plan to add exports to our services in the future. If however you have a special request please contact us your requirements.<br /><br /><br />\r\n\r\n<h2> Privacy Notice </h2><br />\r\n\r\nThis policy covers all users who register to use the website. It is not necessary to purchase anything in order to gain access to the searching facilities of the site.<br /><br /><br />\r\n\r\n<h2> Security </h2><br />\r\nWe have taken the appropriate measures to ensure that your personal information is not unlawfully processed. [Company name] uses industry standard practices to safeguard the confidentiality of your personal identifiable information, including â€˜firewallsâ€™ and secure socket layers. <br /><br /><br />\r\n\r\nDuring the payment process, we ask for personal information that both identifies you and enables us to communicate with you. <br /><br /><br />\r\n\r\nWe will use the information you provide only for the following purposes.<br /><br /><br />\r\n\r\n* To send you newsletters and details of offers and promotions in which we believe you will be interested. <br />\r\n* To improve the content design and layout of the website. <br />\r\n* To understand the interest and buying behavior of our registered users<br />\r\n* To perform other such general marketing and promotional focused on our products and activities. <br />\r\n\r\n<h2> Conditions Of Use </h2><br />\r\n[Company name] and its affiliates provide their services to you subject to the following conditions. If you visit our shop at [Company name] you accept these conditions. Please read them carefully, [Company name] controls and operates this site from its offices within the [Country]. The laws of [Country] relating to including the use of, this site and materials contained. <br /><br /><br />\r\n\r\nIf you choose to access from another country you do so on your own initiave and are responsible for compliance with applicable local lands. <br /><br /><br />\r\n\r\n<h2> Copyrights </h2><br />\r\nAll content includes on the site such as text, graphics logos button icons images audio clips digital downloads and software are all owned by [Company name] and are protected by international copyright laws. <br /><br /><br />\r\n\r\n<h2> License and Site Access </h2><br />\r\n[Company name] grants you a limited license to access and make personal use of this site. This license doses not include any resaleâ€™s of commercial use of this site or its contents any collection and use of any products any collection and use of any product listings descriptions or prices any derivative use of this site or its contents, any downloading or copying of account information. For the benefit of another merchant or any use of data mining, robots or similar data gathering and extraction tools.<br /><br /><br />\r\n\r\nThis site may not be reproduced duplicated copied sold â€“ resold or otherwise exploited for any commercial exploited without written consent of [Company name].<br /><br /><br />\r\n\r\n<h2> Product Descriptions </h2><br />\r\n[Company name] and its affiliates attempt to be as accurate as possible however we do not warrant that product descriptions or other content is accurate complete reliable, or error free.<br /><br /><br />\r\nFrom time to time there may be information on [Company name] that contains typographical errors, inaccuracies or omissions that may relate to product descriptions, pricing and availability.<br /><br /><br />\r\nWe reserve the right to correct ant errors inaccuracies or omissions and to change or update information at any time without prior notice. (Including after you have submitted your order) We apologies for any inconvenience this may cause you. <br /><br /><br />\r\n\r\n<h2> Prices </h2><br />\r\nPrices and availability of items are subject to change without notice the prices advertised on this site are for orders placed and include VAT and delivery.<br /><br /><br />\r\n<br /><br /><br />\r\nPlease review our other policies posted on this site. These policies also govern your visit to [Company name]', 'eshop')";
$inf_insertdbrow[55] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_itembox_w', '200px', 'eshop')";
$inf_insertdbrow[56] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_itembox_h', '300px', 'eshop')";
$inf_insertdbrow[57] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cipr', '3', 'eshop')";
$inf_insertdbrow[58] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_newtime', '604800', 'eshop')";
$inf_insertdbrow[59] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_freeshipsum', '0', 'eshop')";
$inf_insertdbrow[60] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_coupons', '0', 'eshop')";

// Defuse cleaning	
$inf_droptable[1] = DB_ESHOP;
$inf_droptable[2] = DB_ESHOP_CATS;
$inf_droptable[3] = DB_ESHOP_CART;
$inf_droptable[4] = DB_ESHOP_PHOTOS;
$inf_droptable[5] = DB_ESHOP_SHIPPINGCATS;
$inf_droptable[6] = DB_ESHOP_SHIPPINGITEMS;
$inf_droptable[7] = DB_ESHOP_PAYMENTS;
$inf_droptable[8] = DB_ESHOP_CUSTOMERS;
$inf_droptable[9] = DB_ESHOP_ORDERS;
$inf_droptable[10] = DB_ESHOP_COUPONS;
$inf_droptable[11] = DB_ESHOP_FEATITEMS;
$inf_droptable[12] = DB_ESHOP_FEATBANNERS;
$inf_deldbrow[1] = DB_PANELS." WHERE panel_filename='eshop_cart_panel'";
$inf_deldbrow[2] = DB_ADMIN." WHERE admin_rights='ESHP'";
$inf_deldbrow[3] = DB_SITE_LINKS." WHERE link_url='infusions/eshop/eshop.php'";
$inf_deldbrow[4] = DB_SETTINGS_INF." WHERE settings_inf='eshop'";
$inf_deldbrow[5] = DB_LANGUAGE_TABLES." WHERE mlt_rights='ES'";