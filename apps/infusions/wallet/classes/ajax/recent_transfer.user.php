<?php

use PHPFusion\Infusions\Wallet\Classes\Wallet_Transaction;

// we will need to auth somehow.
require_once __DIR__.'/../../../../maincore.php';
require_once INCLUDES.'ajax_include.php';

$list = array();
/*
 *     'date'   => showdate('shortdate', $trows['transaction_datestamp']).'<br/><span class="time">'.date('j:m').'</span>',
                    'title'  => '<i class="fad fa-file-invoice m-r-10"></i>'.$trows['transaction_number'],
                    'user'   => fusion_get_user($orders['order_item_id'], 'user_name'),
                    'status' => ($orders['order_paid_user'] == $user_id ? 'Send' : 'Received'),
                    'amount' => get_amount($orders['order_item_type'], $orders['order_amount']),
 */
$user_token = get("user_token");
if ($user_id = fusion_authenticate_user($user_token)) {
    $transactions = new Wallet_Transaction();
    $transactions->setOrderFilter("order_item_type='FUND'");
    if ($transactions->getUserTransactions($user_id)) {
        foreach ($transactions->getTransaction() as $tid => $trows) {
            //print_P($trows);
            $method = [
                'credit'    => 'Fusion Credit',
                'paypal'    => 'Paypal',
                'stripes'   => 'Credit Card (Stripe)',
                'firstdata' => 'Credit Card (FirstData)'
            ];
            // get orders
            if (!isset($method[$trows['transaction_method']])) {
                $method_text = 'Pending';
                //set_error(E_USER_NOTICE, 'Method not acquired: tid #'.$trows['transaction_id'], 'recent_transactions.user.php', '23', 'Transaction Listing Error');
            } else {
                $method_text = $method[$trows['transaction_method']];
            }
            // determine the type of transaction
            foreach ($transactions->getOrders() as $order_id => $orders) {
                //print_P($orders);
                $list[] = [
                    'date'   => showdate('shortdate', $trows['transaction_datestamp']).'<br/><span class="time">'.date('j:m').'</span>',
                    'title'  => '<i class="fad fa-file-invoice m-r-10"></i>'.$trows['transaction_number'],
                    'user'   => fusion_get_user($orders['order_item_id'], 'user_name'),
                    'status' => ($orders['order_paid_user'] == $user_id ? 'Send' : 'Received'),
                    'amount' => get_amount($orders['order_item_type'], $orders['order_amount']),
                ];
            }
        }
    }
}

function get_amount($type, $amount) {
    if ($type == 'COINP') {
        return '+$'.$amount;
    }
    return '-$'.$amount;
}

function get_order_item_type($type) {
    $type_arr = [
        'HOST'  => 'Hosting',
        'HOSTR' => 'Hosting',
        'TLD'   => 'Domain Name',
        'TLDR'  => 'Domain Name',
        'IDP'   => 'Domain Privacy',
        'IDPR'  => 'Domain Privacy',
        'SSL'   => 'SSL Certificate',
        'SSLR'  => 'SSL Certificate',
        'MPI'   => 'Marketplace',
        'CRL'   => 'CMS Licensing'
    ];
    if (isset($type_arr[$type])) {
        return $type_arr[$type];
    } else {
        set_error(E_USER_NOTICE, 'Error for Type - '.$type, 'infusions/wallet/classes/ajax/recent_transactions.user.php', '64', 'function error');
    }
}

//print_p($list);
echo json_encode(['data' => $list]);
