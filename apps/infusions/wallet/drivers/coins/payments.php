<?php
/**
 * Credit form generator to Process Payment
 */

use PHPFusion\Infusions\Wallet\Classes\Wallet;

//define('FUSION_ALLOW_REMOTE', TRUE);

require_once __DIR__.'/../../../../maincore.php';

function do_coin_payments() {

    require_once INCLUDES.'ajax_include.php';

    header_content_type("json");

    $payment_id = post('payment_id');

    $user_id = fusion_get_userdata('user_id');

    $form_html = 'Please contact administrator.';

    $status = 'ERROR';

    if ($payment_id) {

        $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_ref=:pid", [
            ':pid' => $payment_id,
        ]);

        if (dbrows($result)) {

            $data = dbarray($result);

            $wallet = Wallet::getInstance();

            $user_wallet = $wallet->getUserWallet($user_id);

            if ($user_wallet['gold_balance'] >= $data['transaction_amount']) {

                $user_wallet['gold_balance'] = $user_wallet['gold_balance'] - $data['transaction_amount'];

                // Log coin transaction
                // This will not amount to an invoice as it is just a full credit statement.
                $cresult = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_ref=:thash", [':thash' => $data['transaction_number']]);

                if ($rowCount = dbrows($cresult)) {

                    while ($rows = dbarray($cresult)) {

                        $current_item_id = $rows['order_item_type'].'-'.$rows['order_item_id'];

                        /**
                         * Coins transaction id
                         */
                        $coin_trans = [
                            'ct_id'                  => 0,
                            'ct_wallet_id'           => $user_wallet['wallet_id'],
                            'ct_user'                => $user_wallet['user_id'],
                            'ct_ref'                 => $data['transaction_ref'],
                            'ct_number'              => $data['transaction_number'],
                            'ct_order_id'            => $rows['order_id'],
                            'ct_datestamp'           => time(),
                            'ct_title'               => $rows['order_title'],
                            'ct_description'         => $rows['order_description'],
                            'ct_paid'                => 1,
                            'ct_paid_datestamp'      => time(),
                            'ct_completed'           => 1,
                            'ct_completed_datestamp' => time(),
                            'ct_item_id'             => $current_item_id,
                            'ct_item_type'           => $rows['order_item_type'],
                            'ct_item_value'          => $rows['order_item_value'],
                            'ct_item_quantity'       => $rows['order_item_quantity'],
                            'ct_item_tangible'       => $rows['order_item_tangible'],
                            'ct_total_shipping'      => $rows['order_total_shipping'],
                            'ct_total_tax'           => $rows['order_total_tax'],
                            'ct_item_taxable'        => $rows['order_item_taxable'],
                            'ct_item_tax_rate'       => $rows['order_item_tax_rate'],
                            'ct_total_in'            => 0,
                            'ct_total_out'           => $rows['order_total']];

                        dbquery_insert(DB_COIN_TRANSACTIONS, $coin_trans, 'save', ['keep_session' => TRUE]);
                    }

                    $wallet_data = [
                        "wallet_id"    => $user_wallet["wallet_id"],
                        "gold_balance" => $user_wallet["gold_balance"]
                    ];

                    dbquery_insert(DB_USER_WALLET, $wallet_data, 'update');

                    // Mimic the response data from some remote server.
                    //$html = openform( 'credit_frm', 'post', WALLET.'confirmation.php?payment_method=credit');

                    $form_html = form_hidden('transaction_ref', '', $data['transaction_ref']);

                    $form_html .= form_hidden('order_id', '', $data['transaction_number']);

                    $form_html .= form_hidden('payment_method', '', 'credit');

                    $form_html .= form_hidden('payment_date', '', time());

                    $form_html .= form_hidden("payment_user", "", $user_id);

                    $form_html .= form_hidden("payment_status", "", "success");
                    //$html .= closeform();
                    $status = 'OK';
                }
            }
        }
    }

    echo json_encode(['form' => $form_html, 'status' => $status]);
}

if (fusion_safe()) {
    do_coin_payments();
}

