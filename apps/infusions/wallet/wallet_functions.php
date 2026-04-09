<?php
// Native Function Files for NON-OOP Approach
/**
 * Logs a wallet transaction
 *
 * @param int $user             (int) the user_id of DB_USERS of the person that this transaction belongs to, 0 for system
 * @param int $payee            (int) the user_id of DB_USERS of the person that paid, 0 for system
 * @param     $title            (text) the title of the transaction
 * @param int $amount           (decimal values) the amount of the transaction
 * @param int $charge           (decimal values) if any charges imposed (tax) by PHP-Fusion Inc.
 * @param     $type             (text) 'Purchase', 'Sales', 'Transfer', 'Payout'
 * @param     $db               (text) The table name for reference of the order id
 * @param     $method           (text) credit, payment methods.
 * @param     $db               (text) database
 * @param     $orderid          (int) any reference payment id
 * @param     $ip               (varchar) IP Address of the person that invokes this transaction
 * @param     $status           (int) 1 for success, 0 for pending, 2 for failure
 * @param int $transaction_id   (int) If set, the function will edit instead of creating a new record.
 *
 * @return int
 */
/*
function walletTransaction($user = 0, $payee = 0, $title, $amount = 0, $charge = 0, $type, $method, $db = "", $orderid = 0, $ip, $status, $transaction_id = 0) {
    return \PHPFusion\Wallet::log_walletTransaction($user, $payee, $title, $amount, $charge, $type, $method, $db, $orderid, $ip, $status, $transaction_id);
}*/