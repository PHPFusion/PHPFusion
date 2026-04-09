<?php

use PHPFusion\Infusions\Wallet\Classes\Wallet;

/**
 * Display Wallet Terminal
 * @param $config
 *
 * @return string
 */
function display_wallet($config): string {
    if (session_get("login_as")) {
        return "<h4>Wallet is disabled for Admin Login Session</h4>";
    }
    return Wallet::getInstance()->displayPaymentForm($config);
}

/**
 * Get User Wallet Information
 * @param int $user_id          - must have user id, if 0, then return errors
 * @param     $non_static       - set to true to force db cache again
 *
 * @return array
 */
function fusion_get_user_wallet($user_id = 0, $non_static = FALSE): array {
    return Wallet::getInstance()->getUserWallet($user_id, $non_static);
}


/**
 * Get transaction record
 * @param $order_id
 *
 * @return array
 */
function get_order_transaction($order_id) {
    static $transaction = [];
    if (isnum($order_id) && empty($transaction)) {
        $transaction = dbarray(dbquery("SELECT wt.* FROM ".DB_WALLET_TRANSACTIONS." wt
        INNER JOIN ".DB_WALLET_ORDERS." wo ON wo.order_ref=wt.transaction_number
        WHERE wo.order_id=:oid AND wo.order_paid=0", [':oid'=>intval($order_id)]));
    }

    return $transaction;
}

