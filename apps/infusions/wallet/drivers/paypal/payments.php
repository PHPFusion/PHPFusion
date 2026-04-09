<?php
/**
 * Paypal form generator from Ajax request from Paypal Payment Button
 */

use PHPFusion\Infusions\Wallet\Drivers\Paypal\Paypal_Driver;

require_once __DIR__.'/../../../../maincore.php';

require_once INCLUDES.'ajax_include.php';

$settings = fusion_get_settings();
$payment_id = post('payment_id');
$status = 'ERROR';
$html = 'Please contact administrator.';

if ($payment_id) {

    $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_ref=:pid AND transaction_user=:uid", [
        ':pid' => $payment_id,
        ':uid' => fusion_get_userdata('user_id')
    ]);

    if (dbrows($result)) {

        $data = dbarray($result);

        $paypal = new Paypal_Driver();

        $config = $paypal->get_config();
        // need to verify the token at the confirmation page
        $fusion_token = fusion_get_token($paypal->get_PaypalToken(), 1);

        // convert everything to data
        $paypal_data = [
            'cmd'           => '_cart',
            'upload'        => 1,
            'method'        => 'SetExpressCheckout',
            'business'      => $config['merchant_email'],
            'cbt'           => 'Return to '.$config['merchant_name'],
            'cancel_return' => $config['DefaultCancelURL'],
            'return'        => $config['DefaultReturnURL'],
            'rm'            => 2,
            'lc'            => fusion_get_locale('xml_lang'),
            'custom'        => $fusion_token,
            'currency_code' => $data['transaction_currency'],
            'charset'       => 'utf-8',
            'invoice'       => $payment_id,
        ];
        // Transaction Shipping
        if ($data['transaction_shipping'] < 1) {
            // 0 - Prompt for an address, but do not require one.
            // 1 - Do not prompt for an address.
            // 2 - Prompt for an address and require one.
            // Defaults 0
            $paypal_data['no_shipping'] = 1;

        } else {
            // @wip
            // there are shipping and handling API
            //Handling charges. This variable is not quantity-specific. The same handling cost applies, regardless of the number of items on the order.
            //$form .= '<input type="hidden" name="handling" value="1" />';
        }

        $order_result = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_ref=:thash", [':thash' => $data['transaction_number']]);
        if ($rowCount = dbrows($order_result)) {
            $x = 1;
            while ($rows = dbarray($order_result)) {
                $paypal_data['item_name_'.$x] = $rows['order_title'];
                $paypal_data['amount_'.$x] = $rows['order_item_value'];
                $paypal_data['quantity_'.$x] = $rows['order_item_quantity'];
                $x++;
            }
        }

        $paypalUrl = ($config['sandbox']) ? PAYPAL_SSL_SAND_URL : PAYPAL_SSL_URL;
        $html = "";
        if (!empty($paypal_data)) {
            //$html = "<form id='paypal_frm' name='paypal_frm_payment_method' action='$paypalUrl' method='post'>\n";
            foreach ($paypal_data as $key => $value) {
                $html .= "<input type='hidden' name='$key' value='".htmlentities($value)."'>";
            }
            //$html .= "</form>";
            $status = 'OK';
        }
    }
}


echo json_encode(['form' => $html, 'status' => $status]);
