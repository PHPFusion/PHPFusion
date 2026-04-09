<?php
/**
 * Script to Record a transaction for Wallet.
 * Internal Ajax Trigger.
 * Secured File.
 *
 * The remote path : /infusions/wallet/checkout.json.php
 */

use PHPFusion\Infusions\Wallet\Classes\Wallet;

define('FUSION_ALLOW_REMOTE', TRUE);

require_once __DIR__.'/../../../maincore.php';

//header('Content-Type: application/json');
// Sample Data that was sent in
/**
 * PaymentDesc: "Transaction Payment via Paypal Account"
 * PaymentID: "71116795426134451151"
 * PaymentMethod: "Paypal"
 * callback_url: ""
 * form_id: "paypalPaymentFrm"
 * fusionQHYB9_user: "16331.1559714586.dc291a10eb4065dd774176c7aff5430c427ee3db77d3f6c0341581ff0c1310df"
 * fusion_token: "16331-1559617231-98857f252930bbb894974ddc133aef8444af84928045031e3dd1fd215fd9a12b"
 * order_amount: ["12.49"]
 * order_currency: ["USD"]
 * order_description: ["New Registration - $12.49 USD/year"]
 * order_item_id: ["41"]
 * order_item_type: ["TLD"]
 * order_payment_currency: "USD"
 * order_payment_method: "paypal"
 * order_payment_type: "paypal"
 * order_quantity: ["1"]
 * order_shipping: ["N"]
 * order_tax: ["0"]
 * order_title: ["Domain a-fresh-domain.com (New Registration)"]
 * origin_url: "https://cdn.php-fusion.co.uk:443/hosting/hosting.php?cart=checkout"
 * return_url: "https://cdn.php-fusion.co.uk/hosting/checkout.php"
 * timezone: "Asia/Shanghai"
 */
function wallet_checkout_json() {

    if (iMEMBER) {

        require_once INCLUDES."ajax_include.php";

        header_content_type("json");

        $wallet = new Wallet();

        function validate_checkout_error($wallet) {
            $settings = fusion_get_settings();

            $userdata = $wallet->getUserWallet(fusion_get_userdata("user_id"));

            if (empty($userdata["user_id"])) {

                return [
                    'status' => 'Restart',
                    'title'  => "Unknown error encountered",
                    'data'   => "Invalid wallet account",
                    'link'   => ""
                ];

            }

            if (empty($userdata["user_firstname"]) || empty($userdata["user_lastname"])) {

                $error_text = [];
                if (empty($userdata["user_firstname"])) {
                    $error_text[] = "First name";
                }
                if (empty($userdata["user_lastname"])) {
                    $error_text[] = "Last name";
                }

                $section_id = dbresult(dbquery("SELECT ufr.field_cat_id 
                FROM ".DB_USER_FIELDS." f
                INNER JOIN ".DB_USER_FIELD_CATS." ufc ON ufc.field_cat_id=f.field_cat
                INNER JOIN ".DB_USER_FIELD_CATS." ufr ON ufc.field_parent=ufr.field_cat_id
                WHERE field_name='user_firstname'
                "), 0);

                return [
                    'status' => 'Redirect',
                    'title'  => "Some information is missing",
                    'data'   => "Your ".implode(" and ", $error_text)." is empty",
                    'link'   => $settings["sitepath"]."edit_profile.php".($section_id ? "?section=$section_id" : "")
                ];

            }

            if (empty($userdata["user_address"])) {
                $section_id = dbresult(dbquery("SELECT ufr.field_cat_id 
                FROM ".DB_USER_FIELDS." f
                INNER JOIN ".DB_USER_FIELD_CATS." ufc ON ufc.field_cat_id=f.field_cat
                INNER JOIN ".DB_USER_FIELD_CATS." ufr ON ufc.field_parent=ufr.field_cat_id
                WHERE field_name='user_address'
                "), 0);

                return [
                    'status' => 'Redirect',
                    'title'  => "Some information is missing",
                    'data'   => "Your billing address is empty",
                    'link'   =>  $settings["sitepath"]."edit_profile.php".($section_id ? "?section=$section_id" : "")
                ];
            }

            if (empty($userdata["user_phone"])) {
                $section_id = dbresult(dbquery("SELECT ufr.field_cat_id 
                FROM ".DB_USER_FIELDS." f
                INNER JOIN ".DB_USER_FIELD_CATS." ufc ON ufc.field_cat_id=f.field_cat
                INNER JOIN ".DB_USER_FIELD_CATS." ufr ON ufc.field_parent=ufr.field_cat_id
                WHERE field_name='user_phone'
                "), 0);

                return [
                    'status' => 'Redirect',
                    'title'  => "Some information is missing",
                    'data'   => "Your phone number is empty",
                    'link'   =>  $settings["sitepath"]."edit_profile.php".($section_id ? "?section=$section_id" : "")
                ];

            }

            if (empty($userdata["user_mobile"])) {
                $section_id = dbresult(dbquery("SELECT ufr.field_cat_id 
                FROM ".DB_USER_FIELDS." f
                INNER JOIN ".DB_USER_FIELD_CATS." ufc ON ufc.field_cat_id=f.field_cat
                INNER JOIN ".DB_USER_FIELD_CATS." ufr ON ufc.field_parent=ufr.field_cat_id
                WHERE field_name='user_mobile'
                "), 0);

               return [
                    'status' => 'Redirect',
                    'tdata'  => [],
                    'title'  => "Some information is missing",
                    'data'   => "Your mobile phone number is empty",
                    'link'   =>  $settings["sitepath"]."edit_profile.php".($section_id ? "?section=$section_id" : "")
                ];

            }

            return [];
        }

        if ( $error = validate_checkout_error($wallet)) {

            echo json_encode($error);

        } else {

            $session_ = $wallet->createTransactionfromPost();

            if (iSUPERADMIN) {
                echo json_encode([
                    'status'   => $session_['status'],
                    'response' => $session_['response'],
                    'data'     => (isset($session_['data']) ? $session_['data'] : ''),
                    'request'  => (isset($session_['request']) ? $session_['request'] : '')
                ]);
            } else {
                echo json_encode(['status' => $session_['status'], 'response' => $session_['response']]);
            }
        }

    }
}

fusion_add_hook("wallet_filters", "wallet_checkout_json");
