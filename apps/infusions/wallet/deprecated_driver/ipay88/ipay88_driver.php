<?php
namespace PHPFusion\Infusions\Wallet\Drivers\IPay88;

use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;

class IPay88_Driver extends ipay88 {

    private static $instance = NULL;

    public function __construct() {}

    /**
     * @return object
     */
    public function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return (object)self::$instance;
    }

    /*
     * PHP-Fusion Wallet Module
     */
    public function __Properties() {
        return array(
            'title'                      => 'IPay88 Payment Gateway',
            'description'                => 'This module allows you to accept payments by iPay88',
            'link'                       => 'https://www.ipay88.com.my',
            'author'                     => 'PHP-Fusion Inc',
            'author_web'                 => 'https://www.php-fusion.co.uk',
            'author_email'               => 'mt@php-fusion.co.uk',
            'version'                    => '1.00',
            'pay_method'                 => 'Credit Card',
            'pay_image'                  => "
            <img alt='Visa' src='".fusion_get_settings('siteurl')."infusions/wallet/drivers/IPay88/images/visa.png'>
            <img alt='Visa Electron Card' src='".fusion_get_settings('siteurl')."infusions/wallet/drivers/IPay88/images/visa_electron.png'>
            <img alt='Master Card' src='".fusion_get_settings('siteurl')."infusions/wallet/drivers/IPay88/images/master.png'>
            ",
            // Driver Directory Specs
            'callback_settings_function' => '__Settings',
            'callback_charge_function'   => 'charge',
            'callback_validate_function' => 'validate',
            'callback_refund_function'   => 'refund',
            'callback_record_function'   => 'record',
            'callback_read_function'     => 'read',
        );
    }

    public static function __getOption() {

    }

    public function __Settings() {
        //require(INCLUDES."infusions_include.php");
        $defaults = array(
            'ipay88_merchant_code' => "",
            'ipay88_merchant_key'  => ""
        );
        $settings = get_settings('wallet');
        $var = $settings;
        $var += $defaults;

        if (isset($_POST['save_ipay88'])) {
            foreach (array_keys($_POST) as $key) {
                $input_value = form_sanitizer($_POST[$key], '', $key);
                if (fusion_safe() && ($key !== 'form_id' || $key !== 'fusion_token')) {
                    if (isset($settings[$key])) {
                        dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$input_value' WHERE settings_name='$key' AND settings_inf='wallet'");
                    } else {
                        dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$input_value', 'wallet')");
                    }
                }
                $var[$key] = $input_value;
            }

            if (fusion_safe()) {
                add_notice('success', 'IPay88 Module Configuration Complete');
                if ($_POST['save_ipay88'] == 'close') {
                    redirect(clean_request('', ['configure'], FALSE));
                }
                redirect(FUSION_REQUEST);
            }
        }

        $html = "<h5>IPay88 Details</h5><hr/>";
        $html .= openform('ipay88_form', 'post', FUSION_REQUEST);
        $html .= form_text('ipay88_merchant_code', 'Merchant Code', $var['ipay88_merchant_code'], ['required' => TRUE]);
        $html .= form_text('ipay88_merchant_key', 'Mechant Key', $var['ipay88_merchant_key'], ['required' => TRUE]);
        $html .= form_button('cancel', 'Cancel', 'cancel', ['class' => 'btn-default m-r-10']);
        $html .= form_button('save_ipay88', 'Save', 'open', ['class' => 'btn-default m-r-10']);
        $html .= form_button('save_ipay88', 'Save and Close', 'close', ['class' => 'btn-primary']);

        return $html;

    }

    public static function charge($method, $driver_title, $order_id, $userdata, $items) {
        ob_start();
        $settings = get_settings('Wallet');
        $privateKey = $settings['2co_private_key']; //"167D9C7F-E71F-47A5-A52B-3CCFD618634B";
        $sellerID = $settings['2co_sid']; //"901333413";
        $admin_username = $settings['2co_admin_username']; //"php-fusion";
        $admin_password = $settings['2co_admin_password']; //"uDVb6h$ztX=-duV4;&Dvs8-q";
        $sandbox = $settings['2co_sandbox'] ? FALSE : TRUE; //"uDVb6h$ztX=-duV4;&Dvs8-q";
        $secret_return = "MjAwMjAyMjgtYjI5Yi00MTg3LTg5NjQtNjg4NDY1ZDM0NGNj";

        Twocheckout::privateKey($privateKey);
        Twocheckout::sellerId($sellerID);
        // Your username and password are required to make any Admin API call.
        Twocheckout::username($admin_username);
        Twocheckout::password($admin_password);
        // If you want to turn off SSL verification (Please don't do this in your production environment)
        Twocheckout::verifySSL(false);  // this is set to true by default
        // To use your sandbox account set sandbox to true
        Twocheckout::sandbox($sandbox);

        $total_shipping = 0;
        $total_tax = 0;

        $items = array_filter($items);
        // I fill in the first one in ... safe this way?
        foreach ($items as $count => $itemData) {
            $params['li_'.$count.'_id'] = $itemData['order_item_id'];
            $params['li_'.$count.'_product_id'] = $itemData['order_item_id'];
            $params['li_'.$count.'_name'] = $itemData['order_item_name'];
            $params['li_'.$count.'_description'] = $itemData['order_item_description'];
            $params['li_'.$count.'_type'] = $itemData['order_item_type'];
            $params['li_'.$count.'_price'] = Wallet_Model::parse_price($itemData['subtotal']); // price inclusive tax and shipping
            $params['li_'.$count.'_quantity'] = 1;
            $params['li_'.$count.'_tangible'] = $itemData['order_item_tangible'];
            $params['li_'.$count.'_order_item_type'] = $itemData['order_item_type'];
            $params['li_'.$count.'_order_item_quantity'] = $itemData['order_item_quantity'];
            $params['li_'.$count.'_order_item_value'] = Wallet_Model::parse_price($itemData['order_item_value']);
            $params['li_'.$count.'_order_item_tangible'] = $itemData['order_item_tangible'];
            $params['li_'.$count.'_order_item_shipping'] = Wallet_Model::parse_price($itemData['order_total_shipping']);
            $params['li_'.$count.'_order_item_taxable'] = $itemData['order_item_taxable'];
            $params['li_'.$count.'_order_item_tax'] = Wallet_Model::parse_price($itemData['order_total_tax']);
            $params['li_'.$count.'_order_item_tax_rate'] = $itemData['order_item_tax_rate'];
            $params['li_'.$count.'_order_subtotal'] = Wallet_Model::parse_price($itemData['order_total']);

            $total_shipping = $total_shipping + $itemData['order_total_shipping'];
            $total_tax = $total_tax + $itemData['order_total_tax'];
        }

        if ($total_shipping) {
            $count = $count + 1;
            $params['li_'.$count.'_type'] = 'shipping';
            $params['li_'.$count.'_name'] = 'Shipping Charge';
            $params['li_'.$count.'_price'] = Wallet_Model::parse_price($total_shipping);
        }

        if ($total_tax) {
            $count = $count + 1;
            $params['li_'.$count.'_type'] = 'tax';
            $params['li_'.$count.'_name'] = 'GST Tax';
            $params['li_'.$count.'_price'] = Wallet_Model::parse_price($total_tax);
        }

        $params = array_merge($params,
            array(
                'sid'                    => $sellerID,
                'mode'                   => '2CO',
                // Begin Unique Identifiers
                'merchant_order_id'      => $order_id,
                'merchant_gateway_id'    => $method,
                'merchant_gateway_title' => $driver_title,
                'user_wallet_id'         => $userdata['wallet_id'],
                'user_id'                => $userdata['user_id'],
                // End of Unique Identifiers
                'card_holder_name'       => $userdata['first_name']." ".$userdata['last_name'],
                'street_address'         => $userdata['address'],
                'street_address2'        => $userdata['address_2'],
                'city'                   => $userdata['city'],
                'state'                  => $userdata['region'],
                'zip'                    => $userdata['postcode'],
                'country'                => $userdata['country'],
                'email'                  => $userdata['email'],
                'phone'                  => $userdata['phone'],
                'fax'                    => $userdata['fax'],
                'x_receipt_link_url'     => FUSION_REQUEST,
            )
        );
        print_p($params);

        Twocheckout_Charge::direct($params, 'direct');
        $charge = ob_get_contents();
        ob_end_clean();
        echo $charge;
    }

    public static function refund() {

    }

    /*
     * 2co validation method
     */
    public static function validate() {
        $secret_return = Wallet_Model::walletSettings('2co_secret_return');
        if ($secret_return) {

            require_once INFUSIONS.'wallet/autoloader.php';

            require_once INFUSIONS.'wallet/drivers/Twocheckout/Twocheckout.php';

            $params = array();
            foreach ($_REQUEST as $k => $v) {
                $params[$k] = $v;
            }

            $passback = Twocheckout_Return::check($params, $secret_return);
            ksort($params);

            if ($passback['response_code'] == "Success" && $passback['response_message'] == "Hash Matched") {

                if ($params['credit_card_processed'] == 'Y') {

                    $merchant_order_id = explode(',', $params['merchant_order_id']);

                    // There could be multiple bills. Use array!
                    if (!empty($merchant_order_id)) {

                        $merchant_order_id = \Defender::sanitize_array($merchant_order_id);
                        $sanitized_order_id = implode(',', $merchant_order_id);
                        $result = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_id IN ($sanitized_order_id)");
                        if (dbrows($result) > 0) {
                            $bill_due = 0;
                            while ($data = dbarray($result)) {
                                $bill_due = $data['order_total'] + $bill_due;
                            }
                            $bill_due = Wallet_Model::parse_price($bill_due);
                            $bill_currency = Wallet_Model::walletSettings('wallet_base_currency');
                            if ($params['total'] >= $bill_due && $params['currency_code'] == $bill_currency) {
                                $payment_updated = FALSE;
                                foreach ($merchant_order_id as $bill_id) {
                                    if (dbcount('(order_id)', DB_WALLET_ORDERS, "order_id='$bill_id' AND order_paid='0'")) {
                                        $order_update = [
                                            'order_id'              => $bill_id,
                                            'order_paid'            => 1,
                                            'order_paid_datestamp'  => TIME,
                                            'order_pay_method'      => $params['merchant_gateway_id'],
                                            'order_pay_method_name' => $params['merchant_gateway_title'],
                                            'order_pay_method_ref'  => $params['order_number'],
                                            'order_paid_wallet'     => $params['user_wallet_id'],
                                            'order_paid_user'       => $params['user_id'],
                                            'order_bill'            => \Defender::encode($params),
                                        ];
                                        dbquery_insert(DB_WALLET_ORDERS, $order_update, 'update', ['keep_session' => TRUE]);
                                        $payment_updated = TRUE;
                                    }

                                    $bill = new \Wallet\Bill();
                                    $bill->order_user = $params['user_id'];
                                    $bill->order_id = $bill_id;
                                    $bill_html[] = $bill->bill_view();
                                }

                                if ($payment_updated) {
                                    add_notice('success', 'Your order payment is successful and has been processed');
                                }

                                echo self::display_return_message(
                                    'Thank you for your order',
                                    'Your order has been submitted to us and will be processed shortly.<br/>
                        We have sent a confirmation email to your registered Fusion Pay registered email account.',
                                    implode('<hr/>', $bill_html)
                                );

                            } else {
                                // Amount fall short
                                echo self::display_return_message(
                                    'Your Payment failed to process',
                                    'Your order submitted is now pending for processing. For your convenience, an order bill has been created for you in your account profile.<br/>
                        Should you have any inquiries, please contact the administrator with the following reference <strong>2co Ref: '.$params['order_number'].'</strong>                        '
                                );
                            }
                        } else {
                            throw new Exception('Error in Payment Gateway. Please contact administrator immediately. 2co Ref: '.$params['order_number']);
                        }
                    } else {
                        throw new Exception('Error in Payment Gateway. Please contact administrator immediately. 2co Ref: '.$params['order_number']);
                    }
                } else {
                    // Amount fall short
                    echo self::display_return_message(
                        'Your Payment failed to process',
                        'Your order submitted is now pending for processing. For your convenience, an order bill has been created for you in your account profile.<br/>
                        Should you have any inquiries, please contact the administrator with the following reference <strong>2co Ref: '.$params['order_number'].'</strong>                        '
                    );
                }
            } else {
                //throw new Exception('Error in Payment Gateway. Please contact administrator immediately. 2co Ref: '.$params['order_number']);
                add_notice('danger', 'Payment Gateway Expired. Please try again.');
                redirect(BASEDIR.fusion_get_settings('opening_page'));
            }

        } else {
            echo self::display_return_message(
                'Your Payment Gateway Not Ready',
                'The 2Co Gateway is not ready to use. Please come back later.'
            );
        }
    }

    /*
     * Template
     */
    private static function display_return_message($title, $message, $extra, $redirect = FALSE) {
        $html = '';
        $html .= "<div class='logo text-left spacer-sm'>\n";
        $html .= "<img src='".IMAGES."php-fusion-icon.png' style='width:50px;' title='PHP-Fusion Inc.'><h3 class='display-inline-block m-l-5 va'>StorePay</h4>\n";
        $html .= "</div>\n";
        $html .= "<div class='spacer-sm well'>\n";
        $html .= "<h1 class='text-light text-left'>$title</h1>\n";
        $html .= "<div class='text-left spacer-md'>\n";
        $html .= $message;
        $html .= "<hr/>\n";
        $html .= "<p>Visit your <a href=''>order status</a> to make changes to your order, communicate with us, tracking of your shipment and more.</p>";
        $html .= "</div>\n";
        $html .= $extra;

        return $html;
    }

    public static function record() {

    }

    public static function read() {

    }

    public static function display_form($transaction_id) {
        // log from transaction id.
        $wallet_query = dbquery("SELECT * FROM ".DB_USER_WALLET." WHERE user_id=:user_id", [':user_id' => fusion_get_userdata('user_id')]);
        if (dbrows($wallet_query)) {
            $walletData = dbarray($wallet_query);
        } else {
            \Defender::stop('You do not have a wallet account yet.');
        }

        $wallet_settings = get_settings("wallet");
        if (!$wallet_settings['ipay88_merchant_code']) {
            \Defender::stop('Merchant Code does not exist.');
        }
        if (!$wallet_settings['ipay88_merchant_key']) {
            \Defender::stop('Merchant Key does not exist.');
        }

        if (isnum($transaction_id) && fusion_safe()) {
            $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_id=:id", array(":id" => $transaction_id));
            if (dbrows($result)) {
                $data = dbarray($result);
                $receiving_amount = number_format($data['transaction_amount'], 2, '.', ',');

                // Template
                $tpl = \PHPFusion\Template::getInstance("ccform");
                $tpl->set_template(dirname(__FILE__)."/IPay88_form.html");

                // Get the product title and description and amount
                $tpl->set_block('product_block', array(
                    'title' => $data['transaction_title'],
                    'amount' => "$".$receiving_amount."<strong>USD</strong>",
                ));
                $tpl->set_tag('subtotal', "$".$receiving_amount."<strong>USD</strong>");
                $tpl->set_tag('subtotal', "$".$receiving_amount."<strong>USD</strong>");
                $tpl->set_tag('gtotal', "$".$receiving_amount."<strong>USD</strong>");



                $ipay88 = new ipay88($wallet_settings['ipay88_merchant_code'], $wallet_settings['ipay88_merchant_key']); // Merchant Code
                $ipay88->setTransactionType(ipay88::TRANSACTION_TYPE_PAYMENT);
                //$ipay88->setTransactionType(IPay88::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION);
                //$ipay88->setTransactionType(IPay88::TRANSACTION_TYPE_RECURRING_TERMINATION);

                $ipay88->setField('PaymentId', "Payment ID", 25); // Transaction Payment Mode in USD

                // Credit Card Details
                //$ipay88->setField('CC_Name', "Name on Credit Card", $walletData['first_name']." ".$walletData['last_name']);
                //$ipay88->setField('CC_PAN', "Credit Card Number", "");
                //$ipay88->setField('CC_CVC', "CVC", "");
                //$ipay88->setField('CC_ExpiryDate', "Expiry Date", "");
                $ipay88->setField('RefNo', "Reference No", $data['transaction_id']);
                $ipay88->setField('Amount', "Amount", $receiving_amount);
                $ipay88->setField('Currency', "Currency", 'myr');
                $ipay88->setField('ProdDesc', "Title", $data['transaction_title']);
                $ipay88->setField('UserName', "Name", $walletData['first_name']." ".$walletData['last_name']);
                $ipay88->setField('UserEmail', "Email", $walletData['email']);
                $ipay88->setField('UserContact', "Contact", $walletData['phone']);

                $ipay88->setField('P_Name', "Name", $walletData['first_name']." ".$walletData['last_name']);
                $ipay88->setField('P_Email', "Email", $walletData['email']);
                $ipay88->setField('P_Phone', "Phone", $walletData['phone']);

                // use a form geo, we need a precise control.
                $ipay88->setField('P_Addrl1', "Street", $walletData['address']);
                $ipay88->setField('P_Addrl2', "Street2", $walletData['address_2']);
                $ipay88->setField('P_City', "City", $walletData['city']);
                $ipay88->setField('P_State', "Region", $walletData['region']);
                $ipay88->setField('P_Zip', "Postcode", $walletData['postcode']);
                $ipay88->setField('P_Country', "Country", $walletData['country']);

                /*
                 *     $ipay88->setField('CC_Name', 'John Doe');
    $ipay88->setField('CC_PAN', '1111111111111111');
    $ipay88->setField('CC_CVC', '111');

    $ipay88->setField('CC_ExpiryDate', '122020');
    $ipay88->setField('CC_Country', 'Malaysia');

    $ipay88->setField('CC_Bank', 'Maybank');
    $ipay88->setField('CC_Ic', '888888888888');
    $ipay88->setField('CC_Email', 'your@email.com');
    $ipay88->setField('CC_Phone', '0123456789');
    $ipay88->setField('CC_Remark', 'Bla bla..');
                 */



                $ipay88->setField('Remark', "", "");
                $ipay88->setField('Lang', "Language", 'utf-8');
                $ipay88->setField('ResponseURL', "Response URL", fusion_get_settings('siteurl').'infusions/wallet/wallet.php?ref=cc&amp;response');

                $ipay88->generateSignature();
                $ipay88_fields = $ipay88->getFields();

                // we need a cc form template
                //$tpl->set_tag('form_head', openform('ccform', 'post', $ipay88->getTransactionUrl()));
                $tpl->set_tag('form_head', openform('ccform', 'post', FUSION_REQUEST));


                if (!empty($ipay88_fields)) {
                    foreach ($ipay88_fields as $key => $val) {
                        switch ($key) {
                            case 'P_Country':
                                $values = array(
                                    $ipay88_fields['P_Addrl1'],
                                    $ipay88_fields['P_Addrl2'],
                                    $ipay88_fields['P_Country'],
                                    $ipay88_fields['P_State'],
                                    $ipay88_fields['P_City'],
                                    $ipay88_fields['P_Zip'],
                                );
                                $tpl->set_block("field_block", array(
                                    "input_field" => form_geo("address_book", "Address", implode("|", $values), array('required'=>TRUE))
                                ));
                                $tpl->set_block("field_block", array(
                                    "input_field" => form_hidden($key, $key, $val)
                                ));
                                break;
                            case 'P_Name':
                                $values = array(
                                    $walletData['first_name'],
                                    $walletData['last_name']
                                );
                                $tpl->set_block("field_block", array(
                                    "input_field" => form_name("name", "Name", implode("|", $values), array('required'=>TRUE))
                                ));
                                $tpl->set_block("field_block", array(
                                    "input_field" => form_hidden($key, $key, $val)
                                ));
                                break;
                            case 'UserEmail':
                            case 'UserContact':
                            case 'UserName':
                            case 'P_Addrl1':
                            case 'P_Addrl2':
                            case 'P_City':
                            case 'P_State':
                            case 'P_Zip':
                            case "Lang":
                            case "ResponseURL":
                            case "Signature":
                            case "Remark":
                            case "ProdDesc":
                            case "Amount":
                            case "Currency":
                            case "RefNo":
                            case "PaymentId":
                            case "MerchantCode":
                                $tpl->set_block("field_block", array(
                                    "input_field" => form_hidden($key, $key, $val)
                                ));
                                break;
                            default:
                                $tpl->set_block("field_block", array(
                                    "input_field" => form_text($key, $ipay88->getFieldLabel($key), $val, array("required"=>TRUE))
                                ));
                        }
                    }
                }

                $tpl->set_tag(
                    "button", form_button("submit", "Place Order", "Submit", array("class"=>"btn-info btn-md"))
                );

                return $tpl->get_output();
            }
        }
        // once we do something we check on the order and generate a redirect with transaction_id=number
    }

    /**
     * The response page
     */
    public static function response() {
        $walletSettings = get_settings('wallet');
        $ipay88 = new ipay88($walletSettings['ipay88_merchant_code'], $walletSettings['ipay88_merchant_key']);
        $response = $ipay88->getResponse();
        print_p($response);
    }

    /**
     * Transaction Log ID for Wallet, to log payment options.
     * The fields required.
     *
     * Merchant Code        string(20)  merchant code provided by iPay88 and use to uniquely identify the Merchant
     * PaymentId            integer(0)  appendix II.pdf             (optional)
     * RefNo                string(30)  unique-transaction number
     * Amount               decimal
     * Currency             string(5)   appendix II.pdf
     * ProdDesc             String(100) Product Description
     * UserName             Stirng(100) Customer Name in Full
     * UserEmail                        Email address for receiving receipt
     * UserContact          String(20)  Customer Contact No
     * Remark               String(100) Merchant Remarks            (optional)
     * Lang                 String(20)  ISO-8859-1 - English        (optional)
     *                                  UTF-8 - Unicode
     *                                  GB2312 - Chinese Simplified
     *                                  BIG5 - Chinese Traditional
     * SignatureType        String(100) SHA-256
     * ResponseURL          String(200) Payment Response Page
     * BackendURL           String(200) Backend Response Page
     */

}
