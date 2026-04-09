<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
| Author: Wallet/Infusion.php
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
// Infusion general information
$inf_title = fusion_get_locale("WAL_title", WALLET_LOCALE);
$inf_description = fusion_get_locale("WAL_desc", WALLET_LOCALE);
$inf_version = "1.00";
$inf_developer = "PHP-Fusion Inc";
$inf_email = "";
$inf_weburl = "http://php-fusion.co.uk/";
$inf_folder = "wallet";
$inf_image = "wallet.png";
$inf_rights = "WLT";

$inf_adminpanel[] = array(
    "title" => fusion_get_locale('WAL_title', WALLET_LOCALE),
    "image" => $inf_image,
    "panel" => "administration/",
    "rights" => $inf_rights,
);
/*
 * Change Logs:
 * Country will store as ISO2. Value as keys is always bad for universal application. Too many ISO freaks.
 * Added company profiling so later we know what kind of company we are dealing with
 * Added mobile number so that we can do SMS verification in the future date, phone is too vague to tie to SMS gateway
 * Added type to differentiate between company and individual registrant for licensing purposes
 */
//$inf_newtable[] = DB_USER_WALLET." (
//wallet_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
//type SMALLINT(1) NOT NULL DEFAULT '1',
//user_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
//company VARCHAR(50) NOT NULL default '',
//company_no VARCHAR(50) NOT NULL default '',
//company_period SMALLINT(5) NOT NULL DEFAULT '0',
//company_employees SMALLINT(5) NOT NULL DEFAULT '0',
//company_industry SMALLINT(5) NOT NULL DEFAULT '0',
//company_product VARCHAR(100) NOT NULL DEFAULT '',
//web VARCHAR(100) NOT NULL DEFAULT '',
//job_title VARCHAR(20) NOT NULL default '',
//first_name VARCHAR(50) NOT NULL DEFAULT '',
//last_name VARCHAR(50) NOT NULL DEFAULT '',
//identity_no VARCHAR(50) NOT NULL DEFAULT '',
//country VARCHAR(3) NOT NULL DEFAULT '',
//region VARCHAR(100) NOT NULL DEFAULT '',
//city VARCHAR(100) NOT NULL DEFAULT '',
//address VARCHAR(100) NOT NULL DEFAULT '',
//address_2 VARCHAR(100) NOT NULL DEFAULT '',
//address_3 VARCHAR(100) NOT NULL DEFAULT '',
//postcode VARCHAR(10) NOT NULL DEFAULT '',
//mobile VARCHAR(20) NOT NULL DEFAULT '',
//mobile_cc VARCHAR(5) NOT NULL DEFAULT '',
//phone VARCHAR(20) NOT NULL DEFAULT '',
//phone_cc VARCHAR(5) NOT NULL default '',
//fax VARCHAR(20) NOT NULL DEFAULT '',
//fax_cc VARCHAR(5) NOT NULL default '',
//email VARCHAR(50) NOT NULL DEFAULT '',
//birthdate VARCHAR(50) NOT NULL DEFAULT '',
//marketing_disabled TINYINT(1) NOT NULL DEFAULT '0',
//balance decimal(10,4) UNSIGNED NOT NULL DEFAULT '0',
//lastupdate INT(10) UNSIGNED NOT NULL DEFAULT '0',
//verified TINYINT(1) NOT NULL DEFAULT '0',
//PRIMARY KEY (wallet_id)
//) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_USER_WALLET." (
wallet_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
wallet_status TINYINT(1) unsigned not null default '0',
user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
gold_balance decimal(10,4) UNSIGNED NOT NULL DEFAULT '0',
diamond_balance decimal(10,4) unsigned not null default '0',
stripe_cid VARCHAR(100) NOT NULL DEFAULT '',
verified TINYINT(1) NOT NULL DEFAULT '0',
PRIMARY KEY (wallet_id),
KEY wallet_index (wallet_id, wallet_status, user_id, status)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";


$inf_newtable[] = DB_USER_WALLET_VERIFICATION." (
validate_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
validate_type TINYINT(1) NOT NULL DEFAULT '0',
validate_user_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
validate_data TEXT NOT NULL,
validate_filename VARCHAR(200) NOT NULL DEFAULT '',
validate_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY (validate_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_COIN_PACKS." (
package_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
package_status TINYINT(1) NOT NULL DEFAULT '0',
package_coin_quantity INT(5) NOT NULL DEFAULT '0',
package_price DECIMAL(10,4) NOT NULL DEFAULT '0',
package_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
package_promotion TINYINT(1) NOT NULL DEFAULT '0',
package_promotion_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
package_promotion_end INT(10) UNSIGNED NOT NULL DEFAULT '0',
package_promotion_value INT(5) NOT NULL DEFAULT '0',
package_promotion_bonus DECIMAL(10,4) NOT NULL DEFAULT '0',
PRIMARY KEY (package_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_COIN_VOUCHERS." (
voucher_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
voucher_status TINYINT(1) NOT NULL DEFAULT '0',
voucher_code VARCHAR(20) NOT NULL DEFAULT '',
voucher_price DECIMAL(10,4) NOT NULL DEFAULT '0',
voucher_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
voucher_stop INT(10) UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY (voucher_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

/**
 * Coin trail transactions
 */
$inf_newtable[] = DB_COIN_TRANSACTIONS." (
ct_id INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
ct_wallet_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0', #origin wallet_id
ct_user MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',  #origin user
ct_ref varchar(50) not null default '', #tie to order
ct_number varchar(50) not null default '', #tie to order hash number
ct_order_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0', #if present of purchase add
ct_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
ct_title VARCHAR(200) NOT NULL DEFAULT '', #transaction title
ct_description TEXT NOT NULL, #transaction description
ct_edited INT(10) UNSIGNED NOT NULL DEFAULT '0',
ct_edited_user INT(10) UNSIGNED NOT NULL DEFAULT '0',
ct_paid SMALLINT(1) NOT NULL DEFAULT '0', #is paid to the intendee?
ct_paid_datestamp INT(10) NOT NULL DEFAULT '0', #when payment was made
ct_completed SMALLINT(1) NOT NULL DEFAULT '0', #transaction fully complete
ct_completed_user SMALLINT(1) NOT NULL DEFAULT '0', ##who completed it. 0 for system, number for administrators
ct_completed_datestamp INT(10) NOT NULL DEFAULT '0', ## delivery of product
## transaction values here
ct_item_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0', 
ct_item_type VARCHAR(10) NOT NULL DEFAULT '', 
ct_item_value DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT '0',
ct_item_quantity MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT '0',
ct_item_tangible CHAR(1) NOT NULL DEFAULT 'N', 
ct_total_shipping decimal(10,4) UNSIGNED NOT NULL DEFAULT '0',
ct_item_taxable CHAR(1) NOT NULL DEFAULT 'Y',
ct_item_tax_rate BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
ct_total_tax DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT '0',
ct_total_in DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT '0', ## money in
ct_total_out DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT '0', ## money out.
PRIMARY KEY (ct_id),
KEY order_user (ct_item_id, ct_item_type, ct_user, ct_completed_user, ct_paid, ct_completed)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci
";

$inf_newtable[] = DB_WALLET_ORDERS." (
order_id INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
order_ref VARCHAR(50) NOT NULL DEFAULT '',
order_tid MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
order_user MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0', 
order_title VARCHAR(200) NOT NULL DEFAULT '',
order_description TEXT NOT NULL,
order_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
order_edited INT(10) UNSIGNED NOT NULL DEFAULT '0',
order_edited_user INT(10) UNSIGNED NOT NULL DEFAULT '0',
order_paid SMALLINT(1) NOT NULL DEFAULT '0',
order_paid_datestamp INT(10) NOT NULL DEFAULT '0',
order_paid_user MEDIUMINT(11) NOT NULL DEFAULT '0', ## the one who paid the bill
order_completed SMALLINT(1) NOT NULL DEFAULT '0',
order_completed_user SMALLINT(1) NOT NULL DEFAULT '0',
order_completed_datestamp INT(10) NOT NULL DEFAULT '0',
order_item_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
order_item_type VARCHAR(10) NOT NULL DEFAULT '',
order_item_value DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT '0',
order_item_quantity MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT '0',
order_item_tangible CHAR(1) NOT NULL DEFAULT 'N',
order_total_shipping decimal(10,4) UNSIGNED NOT NULL DEFAULT '0',
order_item_taxable CHAR(1) NOT NULL DEFAULT 'Y',
order_item_tax_rate BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
order_total_tax DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT '0',
order_total DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT '0',
order_info TEXT NOT NULL,
order_invoice_count CHAR(1) NOT NULL DEFAULT '0',
order_currency varchar(4) not null default '',
order_expire int(10) unsigned not null default '0',
PRIMARY KEY (order_id),
KEY order_user (order_item_id, order_item_type, order_user, order_completed_user, order_paid, order_completed)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_WALLET_TRANSACTIONS." (
transaction_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
transaction_ref varchar(50) not null default '',
transaction_number varchar(50) not null default '',
transaction_user MEDIUMINT(8) UNSIGNED NOT NULL,
transaction_title VARCHAR(50) NOT NULL DEFAULT '',
transaction_description text not null,
transaction_amount decimal(10,4) NOT NULL default '0.0000',
transaction_item_total decimal(10,4) not null default '0.0000',
transaction_shipping decimal(10,4) not null default '0.0000',
transaction_tax decimal(10,4) not null default '0.0000',
transaction_currency varchar(10) not null default '',
transaction_type VARCHAR(20) NOT NULL DEFAULT '',
transaction_method VARCHAR(15) NOT NULL DEFAULT '',
transaction_oid text NOT NULL,
transaction_ip VARCHAR(20) NOT NULL DEFAULT '',
transaction_status TINYINT(1) NOT NULL DEFAULT '0',
transaction_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
transaction_file text not null,
transaction_response text not null,
PRIMARY KEY (transaction_id),
KEY transaction_user (transaction_user)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

/*
 * Installs the drivers for the payment gateways
 */
$inf_newtable[] = DB_WALLET_DRIVERS." (
driver_folder VARCHAR(100) NOT NULL DEFAULT '',
driver_title VARCHAR(100) NOT NULL DEFAULT '',
driver_version VARCHAR(10) NOT NULL DEFAULT '',
PRIMARY KEY (driver_folder)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";


/**
 * Log transaction of payments to merchants
 */
$inf_newtable[] = DB_USER_PAYMENTS." (
payment_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
payment_ref varchar(100) not null,
payment_no varchar(50) not null,
payment_method varchar(50) not null,
payment_user MEDIUMINT(11) unsigned NOT NULL DEFAULT '0', 
payment_amount decimal(10,4) unsigned not null default '0',
payment_currency varchar(10) not null default 'USD',
payment_datestamp int(10) unsigned not null,
payment_status tinyint(1) not null,
payment_ip VARCHAR(50) not null,
payment_info text not null,
PRIMARY KEY (payment_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";


/*
 * PRODUCT - Anti Tampering, Monitor product half life.
 * An index to make items searcable within the wallet order
 * This is needed because we need to pull from somewhere the product to be charged in making a custom invoice
 */
$inf_newtable[] = DB_WALLET_ORDER_ITEMS." (
item_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
item_ref_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0', ## This is the ID stored in another Database  'comment_item_id'
item_type VARCHAR(50) NOT NULL DEFAULT '', ## This is the Prefix for your reference to another database 'comment_item_type'
item_title VARCHAR(50) NOT NULL DEFAULT '',
item_description TEXT NOT NULL,
item_tangible CHAR(1) NOT NULL DEFAULT '',
item_quantity BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
item_price DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT '0',
item_tax_rate SMALLINT(10) UNSIGNED NOT NULL DEFAULT '0',
item_tax DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT '0',
item_shipping DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT '0',
item_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
item_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
item_status TINYINT(1) NOT NULL DEFAULT '1',
PRIMARY KEY (item_id),
KEY item_type (item_type)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

/*
$inf_insertdbrow[] = DB_WALLET_ITEMS." (item_id, item_ref_id, item_type, item_description, item_tangible, item_quantity, item_price, item_tax_rate, item_tax, item_shipping, item_datestamp, item_user, item_status)
VALUES (1, 0, 'Hosting', 'A Hosting Pack', '', 'N', 1, 0, 0, 0, 1486393680, 1, 1)";

$inf_insertdbrow[] = DB_WALLET_ITEMS." (item_id, item_ref_id, item_type, item_description, item_tangible, item_quantity, item_price, item_tax_rate, item_tax, item_shipping, item_datestamp, item_user, item_status)
VALUES (2, 0, 'Domains & Hosting', 'Domains & Hosting', '', 'N', 1, 0, 0, 0, 1486923892, 1, 1)";

$inf_insertdbrow[] = DB_WALLET_ITEMS." (item_id, item_ref_id, item_type, item_description, item_tangible, item_quantity, item_price, item_tax_rate, item_tax, item_shipping, item_datestamp, item_user, item_status)
VALUES (3, 0, 'Domain Transfer', 'Domain Transfer', '', 'N', 1, 0, 0, 0, 1486923827, 1, 1)";

$inf_insertdbrow[] = DB_WALLET_ITEMS." (item_id, item_ref_id, item_type, item_description, item_tangible, item_quantity, item_price, item_tax_rate, item_tax, item_shipping, item_datestamp, item_user, item_status)
VALUES (4, 0, 'Domain ID Protection', 'Domain ID Protection', '', 'N', 1, 0, 0, 0, 1486925335, 1, 1)";
*/

/**
 * transaction_oid - for transaction_type :
 * Type `Transfer` , transaction_oid is `wallet_id` of DB_WALLET
 *
 */

// Store Identity
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_name', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_registration_no', '', '".$inf_folder."')";

// Store origins
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_address', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_address_2', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_address_3', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_city', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_region', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_country', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_postcode', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_phone', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_phone_cc', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_fax', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_fax_cc', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('store_email', '', '".$inf_folder."')";

// Credit Settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_base_currency', 'USD', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_currency_thousand_delim', ',', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_currency_decimal_delim', '.', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_currency_number_delim', '2', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_unit_value', '1000', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_min_purchase', '1', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_max_float', '0', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_tax_rate', '0', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_charging', '1', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_open', '1', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_balance_transfer', '1', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_payout_transfer', '1', '".$inf_folder."')";

// Wallet General Settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_location_restriction', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_sell_location', '0', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_customer_location', '0', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_notice_status', '0', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_notice_message', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_repeat_order', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('coin_clear_order', '', '".$inf_folder."')";

// Table to Drop
$inf_droptable[] = DB_USER_WALLET;
$inf_droptable[] = DB_USER_WALLET_VERIFICATION;
$inf_droptable[] = DB_COIN_TRANSACTIONS;
$inf_droptable[] = DB_COIN_PACKS;
$inf_droptable[] = DB_COIN_VOUCHERS;
$inf_droptable[] = DB_WALLET_ORDERS;
$inf_droptable[] = DB_WALLET_TRANSACTIONS;
$inf_droptable[] = DB_WALLET_ORDER_ITEMS;
$inf_droptable[] = DB_USER_PAYMENTS;
$inf_droptable[] = DB_WALLET_DRIVERS;

$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='$inf_rights'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='$inf_folder'";
