<?php
(defined("IN_FUSION") || exit);

// Paths
define("WALLET", INFUSIONS."wallet/");
define("WALLET_ADMIN", INFUSIONS."wallet/administration/");
define('WALLET_OPENING_PAGE', WALLET.'wallet_profile.php');
if (!defined("WALLET_LOCALE")) {
    if (file_exists(WALLET."locale/".LANGUAGE.".php")) {
        define("WALLET_LOCALE", WALLET."locale/".LANGUAGE.".php");
    } else {
        define("WALLET_LOCALE", WALLET."locale/English.php");
    }
}

// Transaction code
const TRANSACTION_PENDING = 0;
const TRANSACTION_PAID = 1;
const TRANSACTION_FAILED = 2;
const TRANSACTION_CLOSED = 3;

const DIAMOND = "💎 %s";
const GOLD = "💰 %s";

/**
 * @param $value
 *
 * @return string
 */
function diamonds($value) {
    return sprintf(DIAMOND, format_word(number_format($value,2), "diamond|diamonds"));
}

/**
 * @param $value
 *
 * @return string
 */
function gold($value) {
    return sprintf(GOLD, format_word(number_format($value,2), "gold coin|gold coins"));
}


// Wallet Account
const DB_USER_WALLET = DB_PREFIX."user_wallet";
// Verification and authentication of coin accounts
const DB_USER_WALLET_VERIFICATION = DB_PREFIX."user_wallet_verification";
// Coin Transactions
const DB_COIN_TRANSACTIONS = DB_PREFIX."coin_transactions";
// Package DB
const DB_COIN_PACKS = DB_PREFIX."coin_packages";
// Package Voucher DB for redemption of Packages
const DB_COIN_VOUCHERS = DB_PREFIX."coin_vouchers";
// Orders
const DB_WALLET_ORDERS = DB_PREFIX."user_wallet_orders";
// Transactions
const DB_WALLET_TRANSACTIONS = DB_PREFIX."user_wallet_transactions";
// Maybe Not needed
const DB_WALLET_ORDER_ITEMS = DB_PREFIX."user_wallet_order_items";
// Payout for Marketplace
const DB_USER_PAYMENTS = DB_PREFIX."user_payments";
// Gateway Driver Installations
const DB_WALLET_DRIVERS = DB_PREFIX."wallet_drivers";
// Logging Checkout errors
const DB_WALLET_LOGS = DB_PREFIX."wallet_logs";

$wallet_link = BASEDIR."edit_profile.php";

$wallet_section = (int)dbresult(dbquery("SELECT ufr.field_cat_id 
                    FROM ".DB_USER_FIELDS." uf 
                    INNER JOIN ".DB_USER_FIELD_CATS." ufc ON uf.field_cat=ufc.field_cat_id
                    INNER JOIN ".DB_USER_FIELD_CATS." ufr ON ufr.field_cat_id=ufc.field_parent
                    WHERE uf.field_name='user_transaction'"), 0);

define("WALLET_SECTION_ID", $wallet_section);

if ($wallet_section) {
    $wallet_link = BASEDIR."edit_profile.php?section=".$wallet_section;
}

define("WALLET_SECTION_LINK", $wallet_link);
define("WALLET_SECTION_TABLE_LINK", $wallet_link."#wallet_transaction_table_wrapper");
