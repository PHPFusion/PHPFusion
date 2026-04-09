<?php
/**
 * Paypal form generator from Ajax request from Paypal Payment Button
 */

use PHPFusion\Infusions\Wallet\Classes\Wallet;
use PHPFusion\Infusions\Wallet\Drivers\FirstData\FirstData_Driver;

require_once __DIR__.'/../../../../maincore.php';
require_once INCLUDES.'ajax_include.php';

$settings = fusion_get_settings();
$payment_id = post( 'payment_id' );
$status = 'ERROR';
$html = 'Please contact administrator.';
$field_error = [];

if ( $payment_id ) {

    $result = dbquery( "SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_ref=:pid AND transaction_user=:uid", [
        ':pid' => $payment_id,
        ':uid' => fusion_get_userdata( 'user_id' )
    ] );

    if ( dbrows( $result ) ) {

        $data = dbarray( $result );

        $wallet = new Wallet();

        $firstdata_driver = new FirstData_Driver( $wallet );

        $config = $firstdata_driver->get_config();

        $wallet_data = $wallet::getInstance()->getUserWallet( fusion_get_userdata( 'user_id' ) );

        $user_id = $wallet_data['user_id'];

        // need to verify the token at the confirmation page
        $fusion_token = fusion_get_token( $firstdata_driver->get_FirstDataToken(), 1 );

        $currency_number = $firstdata_driver->get_transaction_currency( $data['transaction_currency'] );

        $transaction_total = number_format( $data['transaction_amount'], 2 );

        $datetime = $firstdata_driver->get_current_datetime();

        // where is my csrf protection
        $failUrlRequest = $wallet->get_paymentRequestUrl();

        $firstdata_data = [
            'txntype'        => 'sale',
            'timezone'       => $firstdata_driver->get_current_timezone(),
            'txndatetime'    => $datetime['datetime'],
            'hash_algorithm' => 'sha256',
            'hash'           => $firstdata_driver->createHash( $transaction_total, $currency_number ),
            'storename'      => $config['merchant_id'], // Provided by First Data
            'mode'           => 'payonly', // just for one off payment
            'chargetotal'    => number_format( $data['transaction_amount'], 2 ),
            'currency'       => $currency_number, // code number
            'oid'            => $payment_id, // order invoice number

            'bname'                 => sanitizer( 'card_holder', '', 'card_holder' ),
            'cardnumber'            => sanitizer( 'card_no', '', 'card_no' ),
            'expmonth'              => sanitizer( 'card_exp_1', '', 'card_exp_1' ),
            'expyear'               => sanitizer( 'card_exp_2', '', 'card_exp_2' ),
            'cvm'                   => sanitizer( 'card_CVV2', '', 'card_CVV2' ),

            // Optional Parameter
            'cardFunction'          => 'credit',
            'checkoutoption'        => 'combinedpage', // inline?
            // 'comments'   => '',
            'customerid'            => $user_id, // our system user id
            // 'dccInquiryId' => '',
            'dynamicMerchantName'   => $config['merchant_name'], // Company Name "PHP-Fusion Inc"
            'invoicenumber'         => $data['transaction_number'], // unique transaction hash ---------------------- THIS ONE IN PAYPAL
            //'hashExtended' => '',
            //'hexColorCode' => '', // for iFrame integration only
            //'hostURI' => '', // for iFrame integration only
            'language'              => 'en_US',
            'merchantTransactionId' => $data['transaction_id'],
            'full_bypass'           => 'false',
            'mobileMode'            => 'true',

            'responseFailURL'       => $failUrlRequest, // response fail.
            'responseSuccessURL'    => $config['DefaultCallbackURL'], // The success page.

            // Customization
            'customParam_walletID'  => $wallet_data['wallet_id'],
            'customParam_paymentID' => $data['transaction_ref'],
            'customParam_payment'   => 'firstdata',
            'customParam_token'     => $fusion_token,
            // hash component
            //'cardFunction'          => 'credit',
            // transaction amount details
            'shipping'              => $data['transaction_shipping'] > 0 ? number_format( $data['transaction_shipping'], 2 ) : '0',
            'vattax'                => $data['transaction_tax'] > 0 ? number_format( $data['transaction_tax'], 2 ) : '0',
            'subtotal'              => number_format( $data['transaction_item_total'], 2 ),
            // Customer Information from Wallet
        ];

        $field_error = [
            'card_holder' => FALSE,
            'card_no'     => FALSE,
            'card_exp_1'  => FALSE,
            'card_exp_2'  => FALSE,
            'card_CVV2'   => FALSE,
            'card_issued' => FALSE,
        ];

        // Manual sanitization because field session could not be found.
        if ( !$firstdata_data['cardnumber'] ) {
            $field_error['card_no'] = TRUE;
            Defender::stop();
        }

        if ( !$firstdata_data['expmonth'] ) {
            $field_error['card_exp_1'] = TRUE;
            Defender::stop();
        }
        if ( !$firstdata_data['expyear'] ) {
            $field_error['card_exp_2'] = TRUE;
            Defender::stop();
        }
        if ( !$firstdata_data['bname'] ) {
            $field_error['card_holder'] = TRUE;
            Defender::stop();
        }
        if ( !$firstdata_data['cvm'] ) {
            $field_error['card_CVV2'] = TRUE;
            Defender::stop();
        }

        // There will be a need to update the wallet for any info changes in the first data form.
        if ( fusion_safe() ) {

            $order_result = dbquery( "SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_ref=:thash", [ ':thash' => $data['transaction_number'] ] );

            if ( $rowCount = dbrows( $order_result ) ) {

                $x = 1;
                $shipping_fees = 0;

                while ( $rows = dbarray( $order_result ) ) {

                    // first data supports up to 999 entries only
                    if ( $x === 999 )
                        break;

                    if ( !empty( $rows['order_total_shipping'] ) ) {
                        $shipping_fees = $rows['order_total_shipping'] + $shipping_fees;
                    }

                    $item_arr = [
                        'id'            => $rows['order_item_id'].'-'.$rows['order_item_type'],
                        'description'   => $rows['order_title'],
                        'qty'           => $rows['order_item_quantity'],
                        'line_total'    => $rows['order_total'] > 0 ? number_format( $rows['order_total'], 2 ) : '0',
                        'item_price'    => $rows['order_item_value'] > 0 ? number_format( $rows['order_item_value'], 2 ) : '0',
                        'tax'           => $rows['order_total_tax'] > 0 ? number_format( $rows['order_total_tax'], 2 ) : '0',
                        'shipping_fees' => $rows['order_item_tax_rate'] > 0 ? number_format( $rows['order_total_shipping'], 2 ) : '0',
                    ];

                    $item_value = implode( ';', $item_arr );

                    $firstdata_data[ 'item_'.$x ] = $item_value;

                    $x++;

                }

                if ( !empty( $shipping_fees ) ) {

                    $firstdata_data[ 'item_'.$x ] = 'IPG_SHIPPING;Shipping costs;1;'.number_format( $shipping_fees, 2 ).';'.number_format( $shipping_fees, 2 ).';0;0';

                    $x++;

                }

                $firstdata_data[ 'item_'.$x ] = 'IPG_HANDLING;Transaction Fee;1;0;0;0;0';
            }

            // Wallet Customers Info
            $bname = $wallet_data['type'] == 2 ? $wallet_data['company'] : $wallet_data['first_name'].' '.$wallet_data['last_name'];

            $phone = ( $wallet_data['mobile'] ? "+".$wallet_data['mobile_cc']."-".$wallet_data['mobile'] : '' );

            $fax = ( $wallet_data['fax'] ? "+".$wallet_data['fax_cc']."-".$wallet_data['fax'] : '' );

            $user_email = fusion_get_user( $wallet_data['user_id'], 'user_email' );

            $default_customer_fields = [
                'bname'    => $bname,
                'baddr1'   => $wallet_data['address'],
                'baddr2'   => $wallet_data['address_2'],
                'bcity'    => $wallet_data['city'],
                'bstate'   => $wallet_data['region'],
                'bcountry' => $wallet_data['country'],
                'bzip'     => $wallet_data['postcode'],
                'phone'    => $phone,
                'fax'      => $fax,
                'email'    => $user_email,
            ];

            $firstdata_data += $default_customer_fields;

            // print_p($datetime['timestamp']);
            // print_P(showdate('longdate', $datetime['timestamp']));
            // print_p($this->get_current_timezone());

            // We did not manage to get any sandbox url
            $firstdataUrl = 'https://www4.ipg-online.com/connect/gateway/processing';

            if ( !empty( $firstdata_data ) ) {

                $html = "<form id='firstdata_frm' name='firstdata_frm' action='$firstdataUrl' method='post'>\n";
                foreach ( $firstdata_data as $key => $value ) {
                    $html .= "<input type='hidden' name='$key' value='$value'>\n";
                }
                $html .= "</form>\n";
                $status = 'OK';

            }
        }
    }
}

echo json_encode( [ 'form' => $html, 'status' => $status, 'field_error' => $field_error ] );
