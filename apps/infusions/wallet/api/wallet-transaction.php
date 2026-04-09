<?php

(defined("IN_FUSION") || exit);

/**
 * Function to display user wallet transactions
 *
 * @throws Exception
 */
function display_wallet_trans() {
    // always show latest month first
    // we need a month filter.
    $list = [];
    $rows = 0;
    $max_rows = 0;
    $sql = "";

    if (iMEMBER) {

        $user_id = fusion_get_userdata("user_id");

        $datetime = new DateTime();
        $datetime->modify('first day of this month');
        $start = $datetime->getTimestamp();
        $datetime->modify('first day of next month');
        $end = $datetime->getTimestamp();

        if ($filter_date = get("filter")) {
            list($start_check, $end_check) = explode("-", $filter_date);
            if (isnum($start_check) && isnum($end_check)) {
                $start = $start_check;
                $end = $end_check;
            }
        }

        $mysql = "SELECT t.transaction_ref, t.transaction_number, o.*, DAY(FROM_UNIXTIME(o.order_datestamp)) 'day',
        MONTH(FROM_UNIXTIME(o.order_datestamp)) 'month',
        YEAR(FROM_UNIXTIME(o.order_datestamp)) 'year',
        t.transaction_type, t.transaction_method, t.transaction_status
        FROM ".DB_WALLET_ORDERS." o
        INNER JOIN ".DB_WALLET_TRANSACTIONS." t ON o.order_tid=t.transaction_id
        WHERE o.order_user=:uid AND (o.order_datestamp BETWEEN '$start' AND '$end') 
        ORDER BY year DESC, month DESC, day DESC";

        if (iADMIN) {
            $sql = $mysql.", user_id=".$user_id;
        }

        $result = dbquery($mysql, [":uid" => $user_id]);

        if ($rows = (int)dbrows($result)) {

            $max_rows = $rows;

            while ($data = dbarray($result)) {
                // we need to get orders for this transaction
                $inwards = 0;

                $outwards = $data["order_total"];
                if ($data["order_item_type"] == "COINP") {
                    $inwards = $data["order_total"];
                    $outwards = 0;
                }

                $list[] = [
                    'transaction_date'        => showdate('shortdate', $data['order_datestamp']).'<br/><span class="time"><small>'.date('j:m', $data["order_datestamp"]).'</small></span>',
                    'transaction_description' => '<a href="'.INFUSIONS.'wallet/activity.php?id='.$data["transaction_ref"].'&token='.$data["transaction_number"].'"><strong>'.$data['order_title'].'</strong></a><br/>'.$data['order_description'],
                    //'transaction_type'        => get_order_item_type($data['order_item_type']),
                    'transaction_method'      => get_payment_method($data["transaction_method"]),
                    'transaction_status'      => get_transaction_status($data["transaction_status"]),
                    "transaction_in"          => "$".number_format($inwards, 2)." USD",
                    "transaction_out"         => "$".number_format($outwards, 2)." USD"
                ];
            }
        }
    }

    header('Content-Type: application/json', TRUE);

    $json = json_encode(['data' => $list, "recordsFiltered" => $rows, "recordsTotal" => $max_rows, "sql"=>$sql]);
    echo $json;
}

fusion_add_hook("wallet_filters", "display_wallet_trans");

/**
 * @param $status
 *
 * @return string
 */
function get_transaction_status($status) {
    switch ($status) {
        case 1:
            return "Success";
            break;
        case 2:
            return "Failed";
            break;
        default:
        case 0:
            return "Pending";
    }

}

/**
 * @param $type
 * @param $amount
 *
 * @return string
 */
function get_amount($type, $amount) {
    if ($type == 'COINP') {
        return $amount;
    }
    return (float)'-'.$amount;
}

/**
 * @param $type
 *
 * @return mixed|string
 */
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
        'CERT'  => 'CMS Licensing',
        'COINP' => 'Top Up Wallet Coins',
        'COINT' => 'Coin Transfer'
    ];
    if (isset($type_arr[$type])) {
        return $type_arr[$type];
    }
    return '-';
}

/**
 * @param $value
 *
 * @return mixed|string
 */
function get_payment_method($value) {
    $method = [
        'coins' => 'Coins',
        'credit'    => 'Fusion Credit',
        'paypal'    => 'Paypal',
        'stripe'    => 'Credit Card (Stripe)',
        'firstdata' => 'Credit Card (FirstData)'
    ];
    return (isset($method[$value]) ? $method[$value] : "Unknown");
}
