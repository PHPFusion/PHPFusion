<?php
(defined('IN_FUSION') || exit);

use PHPFusion\Infusions\Wallet\Classes\Wallet;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Transaction;

/**
 * @param Wallet_Transaction $transaction
 * @Condition Stable
 *
 * @return array
 */
function topup_user(Wallet_Transaction $transaction) {

    if ($transaction_data = $transaction->getTransaction()) {

        if ($orders = $transaction->getOrders()) {

            foreach ($orders as $order_id => $order) {

                // Get the Coin Package
                $wallet_info = Wallet::getInstance()->getUserWallet($order["order_user"]);

                if ($order['order_item_type'] == "COINP" && $wallet_info["wallet_id"]) {

                    if (!empty($wallet_info['wallet_id'])) {

                        // get package
                        $result = dbquery("SELECT p.*,        
                           (
                               SELECT p1.package_promotion_bonus
                               FROM fusion_coin_packages p1
                               WHERE p1.package_promotion = '1' AND (p1.package_promotion_start >= '".time()."' AND p1.package_promotion_end <= '".time()."' AND p1.package_id = p.package_id)
                               OR p1.package_promotion=1 AND (p1.package_promotion_start = '' AND p1.package_promotion_end = '' AND p1.package_id = p.package_id)
                               
                           ) 'package_promotion_bonus'      
                           FROM ".DB_COIN_PACKS." p WHERE p.package_status=1 AND package_id=:id ORDER BY p.package_price", [":id" => $order["order_item_id"]]);

                        if (dbrows($result)) {

                            // Coin Package Data
                            $cdata = dbarray($result);

                            if (!$order["order_completed"]) {

                                // Deliver

                                $package_coins = $cdata['package_coin_quantity'];

                                $additional_coins = $cdata['package_promotion_bonus'];

                                // Deliver the Coins
                                $total_coins = $package_coins + $additional_coins;

                                $time = time();

                                $transaction_data = flatten_array($transaction_data);

                                // Add a coin transactions
                                $coin_transactions = [
                                    'ct_id'                  => 0,
                                    'ct_wallet_id'           => $wallet_info['wallet_id'],
                                    'ct_user'                => $wallet_info['user_id'],
                                    'ct_ref'                 => $transaction_data['transaction_ref'],
                                    'ct_number'              => $transaction_data['transaction_number'],
                                    'ct_order_id'            => $order['order_id'],
                                    'ct_datestamp'           => $time,
                                    'ct_title'               => 'Funding',
                                    'ct_description'         => 'Top Up Fusion Coins',
                                    'ct_paid'                => 1,
                                    'ct_paid_datestamp'      => $time,
                                    'ct_completed'           => 1,
                                    'ct_completed_datestamp' => $time,
                                    'ct_item_id'             => $order['order_item_id'],
                                    'ct_item_type'           => $order['order_item_type'],
                                    'ct_item_value'          => $order['order_item_value'],
                                    'ct_item_quantity'       => $order['order_item_quantity'],
                                    'ct_item_tangible'       => $order['order_item_tangible'],
                                    'ct_total_shipping'      => $order['order_total_shipping'],
                                    'ct_total_tax'           => $order['order_total_tax'],
                                    'ct_item_taxable'        => $order['order_item_taxable'],
                                    'ct_item_tax_rate'       => $order['order_item_tax_rate'],
                                    'ct_total_in'            => $total_coins,
                                    'ct_total_out'           => 0,
                                ];
                                dbquery_insert(DB_COIN_TRANSACTIONS, $coin_transactions, 'save', ['keep_session' => TRUE]);

                                dbquery("UPDATE `".DB_USER_WALLET."` SET gold_balance=:balance_amt WHERE wallet_id=:wallet_id AND user_id=:uid", [
                                    ':uid'         => $wallet_info['user_id'],
                                    ':wallet_id'   => $wallet_info['wallet_id'],
                                    ':balance_amt' => $wallet_info['gold_balance'] + $total_coins
                                ]);

                                // Immediately mark the order completion for the current oid.
                                $transaction->markOrderCompleted($order_id, $wallet_info['user_id']);

                                $bonus_message = "";
                                if ($additional_coins) {
                                    $bonus_message = " We also have have added an extra of ".format_word($additional_coins, "bonus gold coin|bonus gold coins")." to your wallet account.";
                                }

                                send_pm($wallet_info["user_id"], 0, "Top up coins successful", "Your order for [b]".format_word($package_coins, "gold coin|gold coins")." has been credited to your account[/b].".$bonus_message." Thank you.");

                                add_notice("success", format_word($total_coins, "gold coin|gold coins")." has been added to your account successfully.");

                            } else {
                                // Already completed, send to cache.
                                $transaction->addCompletedOrder($order["order_id"]);
                            }
                        }
                    }
                } else {
                    send_pm(16331, 0, "Attempt to illegal coin top up", "Please check user id ".$order["order_user"]." with order #".$order["order_id"]." for proper valid wallet account");
                }
            }
        }
    }

    // check if transaction order. This will be based on $completed_order count
    $transaction->completeTransaction();

    return $transaction->getOrders();
}

fusion_add_hook("wallet_checkout", "topup_user");
