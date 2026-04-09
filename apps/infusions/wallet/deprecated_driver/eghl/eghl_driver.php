<?php
namespace PHPFusion\Infusions\Wallet\Drivers\eGHL;
use PHPFusion\Geomap;
use PHPFusion\Infusions\Wallet\Classes\Wallet;
use PHPFusion\Template;

/**
 * Class eGHL_Driver
 */
class eGHL_Driver {

    private static $instance = NULL;

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
            'title'                      => 'Credit Cards',
            'description'                => 'Payment Gateway for eGHL',
            'link'                       => WALLET.'drivers/eGHL/doc/eGHL_API_v2.8m.pdf',
            'author'                     => 'PHP-Fusion Inc',
            'author_web'                 => 'https://www.php-fusion.co.uk',
            'author_email'               => 'mt@php-fusion.co.uk',
            'version'                    => '1.00',
            'pay_method'                 => 'Credit Card',
            'pay_image'                  =>
                "<img alt='Visa' style='width:38px; height:22px; max-width:none;' src='".fusion_get_settings('siteurl')."infusions/wallet/drivers/eGHL/images/logo-visa.png'>
                <img alt='Master Card' style='width:38px; height:22px; max-width:none;' src='".fusion_get_settings('siteurl')."infusions/wallet/drivers/eGHL/images/logo-mastercard.png'>",
            // Driver Directory Specs
            'callback_settings_function' => 'settings',
            'callback_charge_function'   => 'checkout',
            'callback_validate_function' => 'validate',
            'callback_refund_function'   => 'refund',
            'callback_record_function'   => 'record',
            'callback_read_function'     => 'read',
            'callback_form_function'     => 'form',
        );
    }

    public static function __getOption() {

    }

    public function settings() {

        $defaults = [
            'eghl_merchant_id'      => '',
            'eghl_merchant_name'    => '',
            'eghl_merchant_pass'    => '',
            'eghl_currency'         => '',
            'eghl_shipping_enabled' => '',
        ];
        $settings = get_settings('wallet');
        $var = $settings;
        $var += $defaults;

        if (isset($_POST['save_eGHL'])) {
            foreach (array_keys($_POST) as $key) {
                if ($key == 'na') {
                    $input_value = !empty($_POST[$key]) ? 1 : 0;
                } else {
                    $input_value = form_sanitizer($_POST[$key], '', $key);
                }
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
                add_notice('success', 'eGHL Gateway Configuration Complete');
                if ($_POST['save_eGHL'] == 'close') {
                    redirect(clean_request('', ['configure'], FALSE));
                }
                redirect(FUSION_REQUEST);
            }
        }

        if (empty($var['eghl_merchant_name'])) {
            $var['eghl_merchant_name'] = fusion_get_settings('sitename');
        }

        $html = "<div class='spacer-sm'>";
        $html .= "<img class='img-responsive' style='max-width:150px;' src='".WALLET."drivers/eGHL/eghl.png'/>";
        $html .= "<h4>eGHL Gateway Configuration</h4><hr/>";
        $html .= "</div>\n";
        $html .= openform('ghl_form', 'post', FUSION_REQUEST);
        $html .= "<div class='row'>\n";
        $html .= "<div class='col-xs-12 col-sm-3'><strong>Merchant Account Credentials</strong></div>";
        $html .= "<div class='col-xs-12 col-sm-9'>\n";
        $html .= form_text('eghl_merchant_id', 'eGHL Pay Merchant ID', $var['eghl_merchant_id'], ['required' => TRUE]);
        $html .= form_text('eghl_merchant_pass', 'eGHL Pay Merchant Password', $var['eghl_merchant_pass'], ['required' => TRUE]);
        $html .= "</div>";
        $html .= "</div>\n<hr/>";
        $html .= "<div class='row'>\n";
        $html .= "<div class='col-xs-12 col-sm-3'><strong>Gateway Configurations</strong></div>";
        $html .= "<div class='col-xs-12 col-sm-9'>\n";
        $html .= form_text('eghl_merchant_name', 'Merchant Printed Name', $var['eghl_merchant_name'], ['required' => TRUE]);
        $html .= form_select('eghl_currency[]', "Merchant Acceptable Currency", $var['eghl_currency'], array(
            "options"     => Geomap::get_Currency(),
            "multiple"    => TRUE,
            "width"       => "100%",
            "inner_width" => "100%",
            "ext_tip"     => "eGHL impose a one-time setup charge of MYR100.00 for each currency enabled."
        ));
        $html .= form_checkbox("eghl_shipping_enabled", "Enable Shipping Fields", $var['eghl_shipping_enabled'], ['reverse_label' => TRUE]);
        $html .= "</div>";
        $html .= "</div>\n<hr/>";

        // we need a checkbox for all available payment gateways
        $html .= form_button('cancel', 'Cancel', 'cancel', ['class' => 'btn-default m-r-10']);
        $html .= form_button('save_eGHL', 'Save', 'open', ['class' => 'btn-default m-r-10']);
        $html .= form_button('save_eGHL', 'Save and Close', 'close', ['class' => 'btn-primary']);

        return $html;
    }

    public static function refund() {

    }

    /**
     * Return
     */
    public static function validate() {
        $config = self::get_config();
        $eGHLHashObj = new eGHL_Hash(array(
            'TxnID'        => $_REQUEST['TxnID'],
            'ServiceID'    => $_REQUEST['ServiceID'],
            'PaymentID'    => $_REQUEST['PaymentID'],
            'TxnStatus'    => $_REQUEST['TxnStatus'],
            'Amount'       => $_REQUEST['Amount'],
            'CurrencyCode' => $_REQUEST['CurrencyCode'],
            'AuthCode'     => $_REQUEST['AuthCode'],
            'OrderNumber'  => $_REQUEST['OrderNumber'],
            'Param6'       => $_REQUEST['Param6'],
            'Param7'       => $_REQUEST['Param7']
        ));
        $hashvalue2 = $eGHLHashObj->generateHashValueForPaymentInfo('HASHVALUE2', $config['MerchantPass'], true);
        $response = array(
            'status' => '',

        );
        if (strcasecmp($hashvalue2, $_REQUEST['HashValue2']) != 0) //Different hash between what we calculate and the hash ent by the payment platform so we do not do anything as we consider that the notification doesn't come from the payment platform.
        {
            if ($_REQUEST['urlType'] == 'return') {
                $response['status'] = 'Hash Value Mismatch';
                add_notice('danger', $response['status']);

                return false;
            }
        } else {
            switch ($_REQUEST['TxnStatus']) {
                case 0:
                    if (isset($_REQUEST['PaymentID']) && isset($_REQUEST['OrderNumber'])) {
                        $cond = "transaction_ref=:ref_id AND transaction_number=:order_id AND transaction_user=:user_id";
                        $param = [
                            ':ref_id'   => stripinput($_REQUEST['PaymentID']),
                            ':order_id' => stripinput($_REQUEST['OrderNumber']),
                            ':user_id'  => fusion_get_userdata('user_id')
                        ];
                        if (dbcount("(transaction_id)", DB_WALLET_TRANSACTIONS, $cond, $param)) {
                            // Update the transaction as Paid.
                            dbquery("UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_status=1 WHERE $cond", $param);

                            // Now re-query our status
                            $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE $cond", $param);
                            if (dbrows($result)) {
                                $data = dbarray($result);
                                // update all orders to paid
                                dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_paid=1, order_paid_datestamp='".TIME."', order_paid_user='".fusion_get_userdata('user_id')."' WHERE order_id IN (".str_replace(".", ",", $data['transaction_oid']).")");

                                if (!empty($data['transaction_file'])) {
                                    $_REQUEST += $data;
                                    $data['transaction_file'] = strtr(urldecode($data['transaction_file']), [fusion_get_settings('siteurl') => BASEDIR]);
                                    // we need to strip the url query and declare as $_REQUEST.
                                    if (file_exists($data['transaction_file'])) {
                                        include $data['transaction_file'];
                                        $response['status'] = "Congratulations! Payment Successful. Thanks for your Purchase.";
                                        add_notice('success', $response['status']);
                                    } else {
                                        add_notice('danger', "Internal Error: There are no response from the server. Please contact the administrator (RefCode: ".$_REQUEST['PaymentID'].")");
                                    }
                                }
                            }
                        } else {
                            add_notice('danger', "Internal Error: Please contact administrator. (RefCode: ".$_REQUEST['PaymentID'].")");
                        }
                    } else {
                        add_notice('danger', "Internal Error: Credit Card Payment Fail");
                    }
                    break;
                case 1 :
                    if (urldecode($_REQUEST['TxnMessage']) == 'Buyer cancelled') {
                        $response['status'] = "Payment is canceled by you.";
                        add_notice('info', $response['status']);
                    } else {
                        $response['status'] = "Payment Failed due to some error.";
                        add_notice('danger', $response['status']);

                        return false;
                    }
                    break;
                case 2 :
                    $response['status'] = "Your Payment is still Pending. We will automatically complete the order when Payment is completed.";
                    add_notice('warning', $response['status']);

                    return false;
                    break;
                default:
                    $response['status'] = "Payment aborted due to communication error.";
                    add_notice('warning', $response['status']);

                    return false;
            }
        }
    }

    public static function record() {

    }

    public static function read() {

    }

    /*
     * Displays Checkout
     * Callback on Order and Processing for Payment.
     * Make sure telephone number, billing address are all filled in. Transform to field on first time.
     */
    public function checkout($options) {

        $html = Template::getInstance('eGHL_checkout');
        $html->set_template(WALLET.'drivers/eGHL/templates/checkout.html');
        $config = $this->get_config();

        if (isset($_REQUEST['Token']) && isset($_REQUEST['PaymentID'])) {
            $PaymentID = stripinput($_REQUEST['PaymentID']);
            $Token = stripinput($_REQUEST['Token']);
            $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_ref=:payment_id AND transaction_method='eGHL'", [
                ':payment_id' => $PaymentID,
            ]);
            if (dbrows($result)) {
                $data = dbarray($result);
                $html->set_tag('payment_id', $PaymentID);
                $html->set_tag('order_subtotal', number_format($data['transaction_item_total'], 2));
                $html->set_tag('order_total_shipping', number_format($data['transaction_shipping'], 2));
                $html->set_tag('order_total_tax', number_format($data['transaction_tax'], 2));
                $html->set_tag('order_total', number_format($data['transaction_amount'], 2));
                $html->set_tag('card_ending', $data['transaction_description']);
                $html->set_tag('card_image', WALLET.'drivers/eGHL/svg/l-'.strtolower($data['transaction_title']).'.svg');

                // get the orders
                $order_ids = str_replace('.', ',', $data['transaction_oid']);
                $cresult = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_id IN ($order_ids)");
                $rowCount = dbrows($cresult);
                if ($rowCount) {
                    $data['item'] = array();
                    while ($rows = dbarray($cresult)) {
                        $current_item_id = $rows['order_item_id'].'-'.$rows['order_item_type'];
                        $data['item'][$current_item_id] = array(
                            'order_title'         => $rows['order_title'],
                            'order_description'   => $rows['order_description'],
                            'order_item_quantity' => $rows['order_item_quantity'],
                            'order_item_id'       => $rows['order_item_id'],
                            'order_item_value'    => number_format($rows['order_item_value'], 2),
                            'order_total' => number_format($rows['order_total'],2)
                        );
                        $order_title = $rows['order_title'];
                    }
                    if ($rowCount > 1) {
                        $html->set_block('item_header', array("text" => "Your Items"));
                        foreach ($data['item'] as $item_id => $item_data) {
                            $html->set_block("item_block", $item_data);
                        }
                    } else {
                        $item_data = array_values($data['item']);
                        if (!empty($item_data)) {
                            $html->set_block("single_item_block", $item_data[0]);
                        }
                    }

                    if (iMEMBER) {
                        //$html->set_block('member_field_block', Wallet::info_hero_form());
                    } else {
                        $html->set_block('non_member_field_block', array(
                            'link'  => BASEDIR.'register.php',
                            'title' => 'Create a PHP-Fusion Account',
                        ));
                    }

                    // Eghl Submission Form
                    $eGHLHashObj = new eGHL_Hash(array(
                        'PaymentID'         => $data['transaction_ref'],
                        'Amount'            => number_format($data['transaction_amount'], 2),
                        'CurrencyCode'      => $data['transaction_currency'],
                        'MerchantReturnURL' => $config['DefaultCheckoutValidateURL'],
                        //'MerchantCallBackURL' => $session_data['callback_url'],
                        'ServiceID'         => $config['ServiceID'],
                        'CustIP'            => $config['CustIP'],
                        'PageTimeout'       => $config['PageTimeout'],
                        'Token'             => $Token
                    ));
                    //print_p($eGHLHashObj);
                    $HashValue = $eGHLHashObj->generateHashValueForPaymentInfo('sale', $config['MerchantPass']);
                    $SOPHash = $eGHLHashObj->generateHashValueForPaymentInfo('sop', $config['MerchantPass']);;
                    $CustName = "John Doe";
                    //$CustName = $options['user']['first_name'].' '.$options['user']['last_name'];
                    $hidden_options = array(
                        'show_title' => false
                    );


                    $fields = array(
                        'TransactionType'   => form_hidden('TransactionType', '', 'SALE', $hidden_options),
                        'PymtMethod'        => form_hidden('PymtMethod', '', 'CC', $hidden_options),
                        'ServiceID'         => form_hidden('ServiceID', '', $config['ServiceID'], $hidden_options),
                        'PaymentID'         => form_hidden('PaymentID', '', $PaymentID, $hidden_options), // unique
                        'OrderNumber'       => form_hidden('OrderNumber', '', $data['transaction_number'], $hidden_options), // decorative
                        'PaymentDesc'       => form_hidden('PaymentDesc', '', $order_title, $hidden_options), // might not have
                        'MerchantReturnURL' => form_hidden('MerchantReturnURL', '', $config['DefaultCheckoutValidateURL'], $hidden_options),
                        // not support callback url because not needed
                        //'MerchantCallBackURL' => form_hidden('MerchantCallBackURL', '', $session_data['callback_url'], $hidden_options),
                        'MerchantCallBackURL' => form_hidden('MerchantCallBackURL', '', '', $hidden_options),
                        'Amount'            => form_hidden('Amount', '', number_format($data['transaction_amount'], 2), $hidden_options),
                        'CurrencyCode'      => form_hidden('CurrencyCode', '', $data['transaction_currency'], $hidden_options),
                        'CustName'          => form_hidden('CustName', '', $CustName, $hidden_options),
                        'CustPhone'         => form_hidden('CustPhone', '', $options['user']['phone'], $hidden_options),
                        'CustEmail'         => form_hidden('CustEmail', '', $options['user']['email'], $hidden_options),
                        'CustIP'            => form_hidden('CustIP', '', $config['CustIP'], $hidden_options),
                        'Token'             => form_hidden('Token', '', $Token, $hidden_options),
                        'TokenType'         => form_hidden('TokenType', '', 'SOP', $hidden_options),
                        'PageTimeout'       => form_hidden('PageTimeout', '', $config['PageTimeout'], $hidden_options),
                        'LanguageCode'      => form_hidden('LanguageCode', '', fusion_get_locale('xml_lang'), $hidden_options),
                        'HashValue'         => form_hidden('HashValue', '', $HashValue, $hidden_options),
                        'SOPHash'           => form_hidden('SOPHash', '', $SOPHash, $hidden_options),
                        'Param6'            => form_hidden('Param6', '', 'test-param-6', $hidden_options),
                        'Param7'            => form_hidden('Param7', '', 'test-param-7', $hidden_options),
                    );

                    $eGHL_fields = implode(PHP_EOL, $fields);

                    $html->set_tag("eGHL_openform", "<form name='eGHL_form', method='post', action='".$config['payment_url']."'/>");
                    $html->set_tag('eGHL_fields', $eGHL_fields);
                    $html->set_tag("eGHL_closeform", closeform());

                    return $html->get_output();

                } else {
                    die("No Items found");
                }
            } else {
                die("No transaction found");
            }
        } else {
            die("Invalid Request");
        }
    }

    public static function get_config() {
        // get wallet settings
        $wallet_settings = get_settings('wallet');
        $config['base_url'] = ((isset($_SERVER['HTTPS'])) ? "https" : "http")."://".$_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT'] != 80 ? ":".$_SERVER['SERVER_PORT'] : '');
        $config['eGHLBaseURL'] = 'https://test2pay.ghl.com/IPGSG/';
        $config['payment_url'] = $config['eGHLBaseURL'].'payment.aspx';
        $config['ServiceID'] = 'SIT'; //$wallet_settings['eghl_merchant_id']; //'SIT';
        $config['MerchantPass'] = 'sit12345'; //$wallet_settings['eghl_merchant_pass']; //'sit12345';
        $config['MerchantName'] = $wallet_settings['eghl_merchant_name'];
        $config['UserID'] = fusion_get_userdata('user_id') ?: USER_IP;
        $config['PageTimeout'] = 15 * 60;
        //$config['CustIP'] = (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $config['CustIP'] = '::1'; //USER_IP;
        $config['DefaultCheckoutURL'] = fusion_get_settings('siteurl').'infusions/wallet/checkout.php?payment_method=eghl';
        $config['DefaultCheckoutValidateURL'] = fusion_get_settings('siteurl').'infusions/wallet/return.php?payment_method=eghl';

        return $config;
    }

    /**
     * Displays a blank form
     *
     * @param $options
     *
     * @return string
     */
    public function form($options) {

        $config = $this->get_config();
        $options += \PHPFusion\Wallet::get_driver_default_options();

        $currentYear = date('Y');
        $nextYears = $currentYear + 10;
        $year_opts = array();
        $years = range($currentYear, $nextYears);
        foreach ($years as $year) {
            $year_opts[$year] = $year;
        }

        $month_opts = array();
        $months = range(1, 12);
        foreach ($months as $month) {
            $month_opts[str_pad($month, 2, '0', STR_PAD_LEFT)] = $month;
        }

        $PaymentID = str_shuffle(time().rand()); //$options['order_item_id'].'-'.$options['order_item_type'];

        $eGHLHashObj = new eGHL_Hash(array(
            'PaymentID' => $PaymentID,
            'ServiceID' => $config['ServiceID'],
            'CustIP'    => $config['CustIP'],
        ));

        $SOPHash = $eGHLHashObj->generateHashValueForPaymentInfo('sop', $config['MerchantPass']);

        $cc_info = array();
        $test_card_info = array(
            'CardType'    => 'VISA',
            'CardNo'      => '4444333322221111',
            'exp_1'       => '09',
            'exp_2'       => date('Y') + 3,
            'CardHolder'  => 'John Doe',
            'IssuingBank' => 'Any Bank',
            'CardCVV2'    => '123',
        );
        $test = true;
        if ($test === true) {
            $cc_info += $test_card_info;
        }

        // here we can have the form and then we use Ajax to send request server to server.
        $html = openform('cc_form', 'post', '');
        $html .= form_select('CardType', 'Card Type', $cc_info['CardType'],
            ['options'          =>
                 array(
                     'VISA'       => 'Visa',
                     'MASTERCARD' => 'Mastercard',
                     'UNION'      => 'Union Pay',
                     'DINERS'     => 'Diners Card',
                     'JCB'        => 'JCB',
                 ),
             "select2_disabled" => true,
            ]);
        $html .= form_text('CardNo', 'Card Number', $cc_info['CardNo'], ['placeholder' => 'Valid Card Number', 'type' => 'number']);
        $html .= "<div class='display-block'>\n";
        $html .= form_select('exp_1', 'Expiration Date', $cc_info['exp_1'], [
            'inner_width' => '100px',
            'options'     => $month_opts,
            'stacked'     =>
                form_select('exp_2', '', $cc_info['exp_2'], [
                    "width"       => "100px",
                    'inner_width' => "100px",
                    "class"       => "display-inline",
                    'options'     => $year_opts
                ])
        ]);
        $html .= "</div>\n";
        $html .= form_hidden('CardExp', '', $cc_info['exp_1'].$cc_info['exp_2']);
        $html .= form_text('CardHolder', 'Card Holder Name', $cc_info['CardHolder'], ['placeholder' => 'The written name on Card']);
        $html .= form_text('IssuingBank', 'IssuingBank', $cc_info['IssuingBank'], ['placeholder' => 'The issuer Bank Name']);
        $html .= form_text('CardCVV2', 'CVV/CVC Number', $cc_info['CardCVV2'], ['type' => 'number', 'max_length' => '3', 'ext_tip' => "<a id='ccv_m' href='#'>What is this?</a>"]);

        $html .= "<input type='submit' id='eGHL_Submit' style='display: none;'/>";
        $button = form_button('eGHL_B', "Pay Online", "eGHL_B", ['class' => 'btn-success btn-bordered btn-md btn-block text-bigger']);
        $html .= $button;
        $html .= closeform();

        $html .= "<hr/>\n";
        $html .= "<div class='no-shadow m-b-20'>\n";
        $html .= "<p>\n";
        $html .= "Your card may be eligible or enrolled in Verified by Visa or Mastercard SecureCode payer authentication programs. After clicking the 'Pay Online' button, your Card Issuer may prompt you for your payer authentication password to complete your purchase.";
        $html .= "</p>\n";
        $html .= "<div class='block'>\n";
        $html .= "<div class='display-inline-block'><a href='#' id='mss_btn'><img src='".WALLET."drivers/eGHL/images/msc_learn_more.gif'></a></div>\n";
        $html .= "<div class='display-inline-block'><a href='#' id='msv_btn'><img src='".WALLET."drivers/eGHL/images/vbv_learn_more.gif'></a></div>\n";
        $html .= "<div class='display-inline-block'><a href='https://network.americanexpress.com/globalnetwork/products-and-services/security/safekey/' target='_blank'><img src='".WALLET."drivers/eGHL/images/amex_learn_more.gif'></a></div>\n";
        $html .= "</div>\n";
        $html .= "</div>\n";

        // this is a single one. if many then we will need to log many

        // I can remove this to ajax data param in another file too.
        $chtml = openform('order_form', 'post', FUSION_REQUEST, ['remote_url' => fusion_get_settings('site_path').'infusions/wallet/checkout.json.php']);
        $chtml .= form_hidden('origin_url', 'Origin URL', $config['base_url'].$_SERVER['REQUEST_URI']);
        $chtml .= form_hidden('callback_url', 'Callback URL', '');
        //$chtml .= form_hidden('callback_url', 'Callback URL', $options['callback_url']);
        $chtml .= form_hidden('return_url', 'Return URL', $options['return_url']);

        if (!empty($options['items'])) {
            foreach($options['items'] as $item_id => $item) {
                $chtml .= form_hidden('order_item_id[]', '', $item['id']);
                $chtml .= form_hidden('order_item_type[]', '', $item['type']);
                $chtml .= form_hidden('order_title[]', '', strip_tags($item['title']));
                $chtml .= form_hidden('order_description[]', '', strip_tags($item['description']));
                $chtml .= form_hidden('order_tax[]', '', $item['tax']);
                $chtml .= form_hidden('order_shipping[]', '', $item['shipping']);
                $chtml .= form_hidden('order_quantity[]', '', $item['quantity']);
                $chtml .= form_hidden('order_amount[]', '', $item['price']);
                $chtml .= form_hidden('order_currency[]', '', $item['currency']);
            }
        } else {
            $chtml .= form_hidden('order_item_id', 'Order ID', $options['order_item_id']); // the payment ID
            $chtml .= form_hidden('order_item_type', 'Order Item Type', $options['order_item_type']); // the payment ID
            $chtml .= form_hidden('order_title', 'Title', $options['order_title']);
            $chtml .= form_hidden('order_description', 'Description', $options['order_description']);
            $chtml .= form_hidden('order_quantity', 'Quantity', $options['order_quantity']);
            $chtml .= form_hidden('order_amount', 'Amount', $options['order_amount']);
            $chtml .= form_hidden('order_currency', 'Currency', $options['currency']);
            $chtml .= form_hidden('order_tax', '', $options['order_tax']);
            $chtml .= form_hidden('order_shipping', '', $options['order_shipping']);
        }

        $chtml .= form_hidden('order_payment_type', '', 'Credit Card');
        $chtml .= form_hidden('order_payment_method', '', 'eGHL');
        $chtml .= closeform();

        $html .= $chtml;

        echo "<script type='text/javascript' src='".$config['eGHLBaseURL']."optimize/eGHL_SOP-1.1.js'></script>
        <script type='text/javascript'>
        window.onload=function() {
            eGHL_SOP.init('cc_form', '".$config['ServiceID']."', '".$PaymentID."', '".$SOPHash."', '".$config['CustIP']."', '".$config['DefaultCheckoutURL']."');
        }
        </script>
        ";

        $amount_js = '';
        $validate_amount_js = '';

        if ($options['display_amount_field']) {
            if (empty($options['order_amount'])) {
                $amount_js = "
                $('#amount').on('input propertychange paste', function(e) {
                var amount_val = $(this).val();
                $('#order_amount').val(amount_val);                
                });
                ";
                $validate_amount_js = "
                var c_val = $('#order_amount').val();
                if (!c_val || c_val == 0) {
                    alert('Please fill in the amount field');
                    return false;                   
                }
                ";
            }
        }

        $callback_js = "
            e.preventDefault();                                
            var data = { 
            'PaymentID': '$PaymentID',
            'PaymentMethod': $('#CardType').val(), 
            'PaymentDesc' : 'Credit Card - ' + $('#CardType').val() + ' - **** ' + $('#CardNo').val().substr(-4)              
            }                        
            var formData = $('#order_form').serialize() + '&' + $.param(data);            
            $validate_amount_js            
            $.ajax({
                url: '".$options['callback_file']."', // independent instance
                data: formData,
                dataType: 'json',
                method: 'post',
                success: function(result) {
                    // validate.                    
                    //console.log(result);                    
                    if (result.status == 'OK') {
                        $('#eGHL_Submit').click();
                    }
                },
                error: function(e) {
                    console.log('eGHL transactions cannot be made. Invalid wallet driver');
                }
            });                
            ";
        if ($options['callback_form'] && $options['callback_file']) {
            $callback_js = "
            e.preventDefault();
            var form_extData = $('#".$options['callback_form']."').serialize();                                            
            var data = { 
            'PaymentID': '$PaymentID',
            'PaymentMethod': $('#CardType').val(), 
            'PaymentDesc' : 'Credit Card - ' + $('#CardType').val() + ' - **** ' + $('#CardNo').val().substr(-4)              
            }                        
            var formData = $('#order_form').serialize() + '&' + $.param(data) + '&' + form_extData;            
            $validate_amount_js            
            $.ajax({
                url: '".$options['callback_file']."', // independent instance
                data: formData,
                dataType: 'json',
                method: 'post',
                success: function(result) {
                    // validate.                    
                    console.log(result);                    
                    if (result.status == 'OK') {
                        $('#eGHL_Submit').click();
                    }
                },
                error: function(e) {
                    console.log('eGHL transactions cannot be made. Invalid wallet driver');
                }
            }); 
            ";
        }
        // need to substr with transaction value, and credits for safety
        // 'transaction_title'       => stripinput($_POST['CardType']), // there are many items, then this title is not suitable.
        //'transaction_description' => 'Credit Card - '.stripinput($_POST['CardType']).' - ****'.substr($_POST['CardNo'], -4),
        add_to_jquery("                        
        ".$amount_js."
        // Simulate click
        $('#eGHL_B').bind('click', function(e) { ".$callback_js." });                
        // Bind expiry dropdown change to a hidden field  
        function monthExp() {
            $('#exp_1').bind('change', function(e) {
                return $(this).val();
            });
            return $('#exp_1').val();
        }
        function yearExp() {
            $('#exp_2').bind('change', function(e) {
                return $(this).val();
            });           
            return $('#exp_2').val();
        }
        $('#CardExp').val(monthExp()+''+yearExp());
        $('#exp_1, #exp_2').bind('change', function() {            
            $('#CardExp').val(monthExp()+''+yearExp());
        });        
        // now i need to cURL into the server with all the information crunched. Let's build a test page.
        function show_cardIcon(item) {
            if(!item.id) {return item.text;}
            var icon = '".WALLET."drivers/eGHL/svg/l-'+ item.id.replace(/-/gi,'_').toLowerCase() +'.svg';
            return '<img style=\"width:26px; float:left; margin-right:5px; margin-top:-1px;\" src=\"' + icon + '\"/></i>' + item.text;
        }
         $('#CardType').select2({
            formatResult: show_cardIcon,
            formatSelection: show_cardIcon,
            escapeMarkup: function(m) { return m; },    
            placeholder: 'Select Card Type *'
        });                  
        ");
        $this->display_info_modal();

        return $html;
    }

    /**
     * Modal for information
     */
    private function display_info_modal() {
        $modal1html = Template::getInstance('mastercardsc');
        $modal1html->set_template(WALLET.'drivers/eGHL/templates/cvv.html');
        $modal1html->set_tag("image_1", WALLET."drivers/eGHL/templates/images/logo-visa-lg.png");
        $modal1html->set_tag("image_2", WALLET."drivers/eGHL/templates/images/logo-mastercard-lg.png");
        $modal1html->set_tag("image_3", WALLET."drivers/eGHL/templates/images/logo-amex-lg.png");
        $modal_1 = openmodal("ccv_info", "<h3 class='m-t-10 m-b-0 strong'>Credit Verification Value/Card Verification Code (CVV/CVC)</h3>", ['button_id' => "ccv_m", "class" => 'modal-md']);
        $modal_1 .= $modal1html->get_output();
        $modal_1 .= closemodal();
        add_to_footer($modal_1);

        $modal2html = Template::getInstance('mastercardsc');
        $modal2html->set_template(WALLET.'drivers/eGHL/templates/mastercard_more.html');
        $modal2html->set_tag("image_1", WALLET.'drivers/eGHL/templates/images/msc_learn_more_1.gif');
        $modal2html->set_tag("image_2", WALLET.'drivers/eGHL/templates/images/msc_learn_more_2.gif');
        $modal2html->set_tag("image_3", WALLET.'drivers/eGHL/templates/images/msc_learn_more_3.gif');
        $modal_2 = openmodal("mss_info", "<h3 class='m-t-10 m-b-0 strong'>Mastercard SecureCode</h3>", ['button_id' => "mss_btn", "class" => "modal-md"]);
        $modal_2 .= $modal2html->get_output();
        $modal_2 .= closemodal();
        add_to_footer($modal_2);

        $modal3html = Template::getInstance('visasc');
        $modal3html->set_template(WALLET.'drivers/eGHL/templates/visa_more.html');
        $modal3html->set_tag("image_1", WALLET.'drivers/eGHL/templates/images/msv_learn_more.png');
        $modal_3 = openmodal("msv_info", "<h3 class='m-t-10 m-b-0 strong'>Verified by Visa</h3>", ['button_id' => "msv_btn", "class" => "modal-md"]);
        $modal_3 .= $modal3html->get_output();
        $modal_3 .= closemodal();
        add_to_footer($modal_3);
    }

}

require_once __DIR__.'/eghl_hash.php';
