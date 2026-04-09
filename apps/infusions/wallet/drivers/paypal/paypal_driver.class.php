<?php

namespace PHPFusion\Infusions\Wallet\Drivers\Paypal;

use DateTime;
use Defender;
use Exception;
use PHPFusion\Geomap;
use PHPFusion\Infusions\Wallet\Classes\Wallet;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Transaction;
use ThemeFactory\Core;

/**
 * Class PayPal_Driver
 * Documentation https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNIntro/
 *
 * @package PHPFusion\Infusions\Wallet\Drivers\Paypal
 */
class Paypal_Driver {

    private static $instance = NULL;

    private $paypal_token = 'paypal_return';

    private $token_user_id = 0;

    private $IPN = TRUE;

    private $defender = NULL;

    private $wallet = NULL;

    private $wallet_settings = [];

    private $errors = [
        80  => [
            "title"       => "Payment Error: Invalid payment amount (Error Code: 80)",
            "description" => "The bill and the payment made are different.",
            "code"        => 80,
        ],
        100 => [
            "title"       => "Payment Error: Payment was not completed (Error Code: 100)",
            "description" => "Your payment has not gone through or is currently pending.",
            "code"        => 100,
        ],
        200 => [
            "title"       => "Payment Error: Invalid merchant mail verification (Error Code: 200)",
            "description" => "The mail verification failed.",
            "code"        => 200,
        ],
        300 => [
            "title"       => "Payment Error: Invalid currency verification (Error Code: 300)",
            "description" => "The currency verification failed.",
            "code"        => 300,
        ],
        400 => [
            "title"       => "Payment Error: Paypal error (Error Code: 400)",
            "description" => "There was no transaction id being sent by Paypal.",
            "code"        => 400,
        ],
        500 => [
            'title'       => "Payment Error: Paypal IPN Error (Error Code: 500)",
            'description' => "Your last transaction has an invalid transaction token.",
            "code"        => 500,
        ],
        600 => [
            'title'       => "Payment Error: Invalid Payment Verification (Error Code: 600)",
            'description' => "Your last transaction has an invalid transaction token.",
            "code"        => 600,
        ],
        700 => [
            'title'       => "Payment Error:Unknown Error (Error Code: 700)",
            'description' => "Transaction contains an invalid security token.",
            "code"        => 700,
        ],
        800 => [
            'title'       => "Delivery cannot be made (Error Code: 800)",
            'description' => "No transaction file defined.",
            "code"        => 800,
        ],
        900 => [
            'title'       => 'No transaction found (Error Code: 900)',
            'description' => 'No transaction can be found for this request.',
            "code"        => 900,
        ]
    ];

    private $info = [];

    public function __construct() {
        $this->defender = Defender::getInstance();
        $this->wallet = new Wallet();
        $this->wallet_settings = Wallet::walletSettings();
    }

    public static function __getOption() {
    }

    public static function getDummyResponse() {
        $_REQUEST = [
            'payment_method'            => 'paypal',
            'payer_email'               => 'buy.sandbox@paypal.com',
            'payer_id'                  => 'YPYL4DWUZG6AA',
            'payer_status'              => 'VERIFIED',
            'first_name'                => 'Test',
            'last_name'                 => 'Buyer',
            'txn_id'                    => '4EY52366C5891133L',
            'mc_currency'               => 'USD',
            'mc_fee'                    => '3.16',
            'mc_gross'                  => '83.99',
            'protection_eligibility'    => 'ELIGIBLE',
            'payment_fee'               => '3.16',
            'payment_gross'             => '83.99',
            'payment_status'            => 'Completed',
            'payment_type'              => 'instant',
            'item_name1'                => 'PHP-Fusion Bronze Hosting Pack',
            'quantity1'                 => '1',
            'mc_gross_1'                => '54.00',
            'item_name2'                => 'dgag.beer',
            'quantity2'                 => '1',
            'mc_gross_2'                => '29.99',
            'num_cart_items'            => '2',
            'txn_type'                  => 'cart',
            'payment_date'              => '2019-01-01T13:49:12Z',
            'business'                  => 'commercial@php-fusion.co.uk',
            'receiver_id'               => 'RDQ7ZQ27P44XY',
            'notify_version'            => 'UNVERSIONED',
            'custom'                    => '16331-1546350415-4a01e1c8784bf9fedd8050934c766b7703111a5ffaad6ec7ae222ef84fde4570',
            'invoice'                   => '5564612502458730305',
            'verify_sign'               => 'AbzUKcezB8naVHpx90zPdz4StQqYAHcgJLFhM1G9CKe.wzgCqe.ew5LY',
            'fusiont489H_user'          => '16331.1546491213.6a72b9f3db316fe5d77a03059f4a15178ab95f8a93e4705cbf62055fe3d24f03',
            'fusiont489H_admin'         => '16331.1546491321.b95b54966f610d5295b86c2e54d43dfc174ff7118b9b58681388889917bc136d',
            'fusiont489H_lastvisit'     => '1546347089',
            'fusionQHYB9_user'          => '16331.1546489506.f2e9d2a644a9dfb83b8c99204f391bd7380e334fb1e79e62f71bda4ecca4bd9a',
            'fusionQHYB9_admin'         => '16331.1546489517.e4e7fed1dc5bb4c5eabf31e3255f101af9d13ae60484e0e5d4b58059ac3b431f',
            'fusiont489H_session'       => 'fb9354854b5209dc244f6cdfe944363c',
            'fusiont489H_visited'       => 'yes',
            'fusiont489H_cookieconsent' => 'yes',
            'timezone'                  => 'Asia/Shanghai',
            'cpsession'                 => 'nextphpf:rRf2rfyitmC8TIOS,db963406a805e8e4e23b968c7e547159'
        ];
    }

    /*
     * PHP-Fusion Wallet Module
     */

    public static function refund() {
    }

    public static function record() {
    }

    public static function read() {
    }

    /**
     * @return static
     */
    public function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function __clone() {
        die('Cloning of this class is prohibited');
    }

    public function __Properties() {
        return [
            'title'                      => 'PayPal',
            'description'                => 'Safe Money Transfer with your PayPal.',
            'admin_description'          => 'Payment Gateway for PayPal',
            'link'                       => 'https://developer.paypal.com/docs/',
            'author'                     => 'PHP Fusion Inc',
            'author_web'                 => 'https://www.php-fusion.co.uk',
            'author_email'               => 'mt@php-fusion.co.uk',
            'version'                    => '1.00',
            'pay_method'                 => 'PayPal Checkout',
            'pay_image'                  => "<img alt='Pay with Paypal' style='padding-right:15px;' src='".rtrim(fusion_get_settings('siteurl'), "/")."/infusions/wallet/drivers/paypal/paypal.svg'>",
            // Driver Directory Specs
            'callback_settings_function' => 'settings_admin',
            'callback_charge_function'   => 'checkout',
            'callback_validate_function' => 'validate',
            'callback_refund_function'   => 'refund',
            'callback_record_function'   => 'record',
            'callback_read_function'     => 'read',
            'callback_form_function'     => 'form',
        ];
    }

    public function __Enabled() {
        return !empty($this->wallet_settings["paypal_enabled"]);
    }

    public function get_PaypalToken() {
        return $this->paypal_token;
    }

    public function settings_admin() {

        $defaults = [
            'paypal_merchant_name'  => '',
            'paypal_merchant_email' => '',
            'paypal_currency'       => '',
            "paypal_enabled"        => 0,
            'paypal_sandbox'        => 0,
        ];

        $settings = $this->wallet_settings;

        $data = $settings + $defaults;

        if (post('save_paypal')) {
            //do manual
            $data = [
                'paypal_merchant_name'  => sanitizer('paypal_merchant_name', '', 'paypal_merchant_name'),
                'paypal_merchant_email' => sanitizer('paypal_merchant_email', '', 'paypal_merchant_email'),
                'paypal_currency'       => sanitizer(['paypal_currency'], '', 'paypal_currency'),
                'paypal_sandbox'        => (check_post("paypal_sandbox") ? 1 : 0),
                'paypal_enabled'        => (check_post("paypal_enabled") ? 1 : 0),
            ];

            if (fusion_safe()) {
                foreach ($data as $key => $input_value) {
                    $sql_param = [
                        ':val' => $input_value,
                        ':key' => $key,
                        ':inf' => 'wallet'
                    ];
                    if (isset($settings[$key])) {
                        dbquery(
                            "UPDATE `".DB_SETTINGS_INF."` SET `settings_value`=:val WHERE `settings_name`=:key AND `settings_inf`=:inf",
                            $sql_param
                        );
                    } else {
                        dbquery(
                            "INSERT INTO `".DB_SETTINGS_INF."` (`settings_name`, `settings_value`, `settings_inf`) VALUES (:key, :val, :inf)",
                            $sql_param
                        );
                    }
                }
                add_notice('success', 'Paypal payment gateway settings have been updated');
                if (post("save_paypal") === 'close') {
                    redirect(clean_request('', ['configure'], FALSE));
                }
                redirect(FUSION_REQUEST);
            }
        }

        if (empty($data['paypal_merchant_name'])) {
            $var['paypal_merchant_name'] = fusion_get_settings('sitename');
        }

        $html = "";
        $html .= "<h4>PayPal Gateway Configuration</h4><hr/>";
        $html .= openform('paypal_form', 'post', FUSION_REQUEST);
        $html .= "<img class='img-responsive' style='max-width:150px;margin-left:10px;' src='".WALLET."drivers/paypal/paypal.svg'>";
        $html .= "<div class='row'>\n";
        $html .= "<div class='col-xs-12 col-sm-3'><strong>Merchant Account Credentials</strong></div>";
        $html .= "<div class='col-xs-12 col-sm-9'>\n";
        $html .= form_checkbox('paypal_enabled', 'Enable Paypal', $data['paypal_enabled']);
        $html .= form_text('paypal_merchant_name', 'Merchant Name', $data['paypal_merchant_name'], ['required' => TRUE]);
        $html .= form_text(
            'paypal_merchant_email', 'Merchant Email Address', $data['paypal_merchant_email'], ['required' => TRUE]
        );
        $html .= "</div>";
        $html .= "</div>\n<hr/>";
        $html .= "<div class='row'>\n";
        $html .= "<div class='col-xs-12 col-sm-3'><strong>Gateway Configurations</strong></div>";
        $html .= "<div class='col-xs-12 col-sm-9'>\n";
        $html .= form_select(
            'paypal_currency[]', "Merchant Acceptable Currency", $data['paypal_currency'], [
                "options"     => Geomap::get_Currency(),
                "multiple"    => TRUE,
                "width"       => "100%",
                "inner_width" => "100%",
                "ext_tip"     => "Please ensure that the processing currency is acceptable by Paypal before enabling each of them."
            ]
        );
        $html .= form_checkbox("paypal_sandbox", "Enable Sandbox", $data['paypal_sandbox'], ['reverse_label' => TRUE]);
        $html .= "</div>";
        $html .= "</div>\n<hr/>";

        // we need a checkbox for all available payment gateways
        $html .= form_button('cancel', 'Cancel', 'cancel', ['class' => 'btn-default m-r-10']);
        $html .= form_button(
            'save_paypal', 'Save', 'open', ['class' => 'btn-default m-r-10', 'input_id' => 'just_save']
        );
        $html .= form_button('save_paypal', 'Save and Close', 'close', ['class' => 'btn-primary']);

        return $html;
    }

    /**
     * Confirmation page
     * Return and read records of the transaction status and provide a method to check whether it is verified or not.
     * if it is, then we will update the transaction and orders SQL
     * and then we will include the transaction file.
     *
     * @return array
     */
    public function validate() {

        $nav_config = [
            [
                "profile-nav"        => [
                    "link_id"    => "profile-nav",
                    "link_name"  => "Go to Profile",
                    "link_cat"   => 0,
                    "link_class" => "btn btn-sm btn-primary",
                    "link_url"   => BASEDIR."edit_profile.php"
                ],
                "print-invoice-link" => [
                    "link_id"    => "print-invoice-link",
                    "link_name"  => "Print this Invoice",
                    "link_cat"   => 0,
                    "link_class" => "btn btn-sm btn-inverse print-invoice",
                    "link_url"   => "#"
                ],
            ]
        ];

        Core::replaceAdditionalNav($nav_config);

        $payer_user_id = 0;
        if (!post("custom")) {
            $settings = fusion_get_settings();
            add_notice("info", "Paypal returned an invalid response.");
            redirect(BASEDIR.$settings["opening_page"]);
        } else {
            list($payer_user_id, $timestamp, $user_token) = explode("-", post("custom"));
        }

        $transaction_ref = post("invoice");

        try {
            $date = new DateTime(post("payment_date"));
            $timestamp = $date->getTimestamp();
        } catch (Exception $e) {
            set_error(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine());
        }

        $this->info = [
            'store_name'         => $this->get_config('merchant_name') ?: Wallet_Model::walletSettings('store_name'),
            'invoice'            => post('invoice'),
            'datestamp'          => date('j M Y, H:M:s', $timestamp),
            'payment_status'     => post('payment_status'),
            'currency'           => post('mc_currency'),
            'transaction_status' => TRANSACTION_FAILED,
            'mc_gross'           => post('mc_gross'),
            'business_email'     => post('business'),
        ];

        // Log Requests
        $transaction = new Wallet_Transaction();

        if ($transaction->getRef($transaction_ref)) {

            $transaction_data = $transaction->transactionData();

            // Log Paypal Response
            $_REQUEST['transaction_id'] = $transaction_data['transaction_id'];
            $_REQUEST['transaction_ref'] = $transaction_data['transaction_ref'];
            $array = Defender::sanitize_array($_REQUEST);
            dbquery("UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_response=:response WHERE transaction_id=:id", [
                ':response' => fusion_encode($array),
                ":id"       => $transaction_data["transaction_id"]
            ]);

            $transaction_data["eta_date"] = date("j M Y, H:m:s", $transaction_data["transaction_datestamp"] + 86400);

            $transaction_data["date"] = date("j M Y, h:m:s", $transaction_data["transaction_datestamp"]);

            $transaction_data["interval_date"] = date("j M Y, H:m:s", $transaction_data["transaction_datestamp"] + 3600);

            $wallet_info = Wallet::getInstance()->getUserWallet($transaction_data["transaction_user"]);

            $this->info["wallet"] = $wallet_info;

            $this->info["transaction"] = $transaction_data;

            $this->info["transaction"]["transaction_orders"] = $transaction->getOrders();

            if ($this->info['payment_status'] == "Completed") {
                $this->info["transaction"]["transaction_status"] = TRANSACTION_PAID;
            } else {
                $this->setError(100);
                $this->info["transaction"]["transaction_status"] = TRANSACTION_FAILED;
            }

            // Log Payment to Transaction and Orders - Checked
            $transaction->setPayment($this->info["transaction"]["transaction_status"], $timestamp, $payer_user_id);

            // Make sure seller email matches your primary account email.
            if ($this->info['business_email'] != $this->get_config('merchant_email')) {
                $this->setError(200);
            }

            // Make sure the currency code matches
            $error_currency = TRUE;

            if ($acceptable_currency = explode(",", $this->get_config("merchant_currency"))) {
                if (in_array($this->info['currency'], $acceptable_currency)) {
                    $error_currency = FALSE;
                }
            }

            if ($error_currency) {
                $this->setError(300);
            }

            if (empty($this->info['invoice']) or empty($timestamp)) {
                $this->setError(400);
            }

            if ($this->info['mc_gross'] != number_format($transaction_data["transaction_amount"], 2)) {
                $this->setError(80);
            }

            if ((int)$this->info["transaction"]["transaction_status"] == TRANSACTION_PAID) {

                // IPN check to deliver item
                $ipn = new PaypalIPN();
                if ($this->get_config('sandbox')) {
                    $ipn->useSandbox();
                }

                if (($this->IPN === TRUE && $ipn->verifyIPN()) || !$this->IPN) { // Remember to invert this function in the online server

                    if ($this->verifyUserToken() === FALSE) { // Generates $token_user_id

                        $transaction_file = str_replace(fusion_get_settings('siteurl'), BASEDIR, rawurldecode($transaction_data["transaction_file"]));

                        // we need to run
                        if (is_file($transaction_file)) {

                            require_once $transaction_file;

                            // To return order_id and order info array
                            flatten_array(fusion_filter_hook("wallet_checkout", $transaction));

                            if ($completed_orders = $transaction->getCompletedOrders()) {
                                $completed_orders = implode("','", $completed_orders);
                                $order_result = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_id IN ('$completed_orders')");
                                if (dbrows($order_result)) {
                                    while ($order_data = dbarray($order_result)) {
                                        $this->info["transaction"]["transaction_orders"][$order_data["order_id"]] = $order_data;
                                    }
                                }
                            }

                        } else {
                            $this->setError(800);
                        }
                    } else {
                        $this->setError(500);
                    }
                } else {
                    $this->setError(600);
                }
            }
        } else {
            $this->setError(900);
        }

        return (array)$this->info;
    }

    /**
     * @param null $key
     *
     * @return mixed
     */
    public function get_config($key = NULL) {
        // get wallet settings
        /*
         *
                    $data = array(
                        'merchant_email' => $config['merchant_email'],
                        'paypal_sandbox' => $config['sandbox'],
                        'thanks_page'    => fusion_get_settings('siteurl').'infusions/wallet/wallet.php', // to return url
                        'notify_url'     => fusion_get_settings('siteurl').'infusions/wallet/paypal_ipn_verify.php', // ? to return.
                        'cancel_url'     => fusion_get_settings('siteurl').'infusions/wallet/wallet.php', // to origin url
                    );
         */
        $wallet_settings = $this->wallet_settings;

        $config['base_url'] = (server('HTTPS') ? "https" : "http")."://".server('SERVER_NAME').(server(
                'SERVER_PORT'
            ) != 80 ? ":".server('SERVER_PORT') : '');

        $config['merchant_name'] = $wallet_settings['paypal_merchant_name'];

        if (empty($config['merchant_name']))
            add_notice('danger', 'PayPal merchant name is not set.');

        $config['merchant_email'] = $wallet_settings['paypal_merchant_email'];

        if (empty($config['merchant_email']))
            add_notice('danger', 'PayPal merchant email address is not set.');

        $config['merchant_currency'] = $wallet_settings['paypal_currency'];

        if (empty($config['merchant_currency'])) {
            add_notice('danger', 'PayPal merchant currency is not set.');
        }

        $config['sandbox'] = $wallet_settings['paypal_sandbox'] ? TRUE : FALSE;
        $config['DefaultCancelURL'] = isset($_REQUEST['origin_url']) ? $_REQUEST['origin_url'] : '';
        $site_url = rtrim(fusion_get_settings('siteurl'), "/").'/';
        // The checkout url for paypal driver
        $config['DefaultCheckoutURL'] = $site_url.'infusions/wallet/checkout.php?payment_method=paypal'.(isset($_REQUEST['payment_id']) ? "&payment_id=".$_REQUEST['payment_id'] : '');
        // Return checkout complete - the return url after completing purchase
        $config['DefaultCallbackURL'] = $site_url.'infusions/wallet/confirmation.php?payment_method=paypal';
        // The notification processing url
        $config['DefaultNotificationURL'] = $site_url.'infusions/wallet/ipn.php?payment_method=paypal'; // this is the IPN file
        // After done payment
        $config['DefaultReturnURL'] = $site_url.'infusions/wallet/confirmation.php?payment_method=paypal'; // this is the thank you page.
        $config['UserID'] = fusion_get_userdata('user_id') ? fusion_get_userdata('user_id') : USER_IP;
        $config['PageTimeout'] = 15 * 60;
        $config['CustIP'] = FUSION_IP;

        if (!defined('PAYPAL_CONST')) {
            define('PAYPAL_CONST', TRUE);
            define('PAYPAL_SSL_URL', 'https://www.paypal.com/cgi-bin/webscr');
            define('PAYPAL_SSL_SAND_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr');
        }

        // do notices for settings validation
        return $key === NULL ? $config : (isset($config[$key]) ? $config[$key] : NULL);
    }

    /**
     * @param $error_code
     */
    private function setError($error_code) {
        fusion_stop();

        $errors = $this->errors[$error_code];

        add_notice('danger', "<strong>".$errors['title']."</strong> ".$errors["description"]);

        if ($this->IPN === TRUE) {
            $log = [
                'log_errors'    => '(PAYPAL) '.$errors['title'],
                'log_id'        => 0,
                'log_user'      => fusion_get_userdata('user_id'),
                'log_data'      => Defender::sanitize_array($_REQUEST),
                'log_datestamp' => TIME,
            ];
            dbquery_insert(DB_WALLET_LOGS, $log, 'save');
        } else {
            $this->info['errors'] = $errors;
        }
    }

    /**
     * Authenticate paypal response to identify valid user
     * Does not require iMEMBER
     *
     * @return bool|string
     */
    private function verifyUserToken() {
        // Verify our Custom Hash
        $settings = fusion_get_settings();

        $locale = fusion_get_locale();

        $token_data = explode('-', post('custom'));

        $error = $locale['token_error_8'];

        if (count($token_data) == 3) {
            $error = "";

            list($this->token_user_id, $token_time, $hash) = $token_data;

            $userdata = fusion_get_user($this->token_user_id);

            $algo = $settings['password_algorithm'];

            $salt = md5(isset($userdata['user_salt']) ? $userdata['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);

            // check if the logged user has the same ID as the one in token
            if ($this->token_user_id != $userdata["user_id"]) {
                $error = $locale['token_error_4'];
                // make sure the token datestamp is a number
            } else if (!isnum($token_time)) {
                $error = $locale['token_error_5'];
                // check if the hash is valid
            } else if ($hash !== hash_hmac($algo, $this->token_user_id.$token_time.$this->paypal_token.SECRET_KEY, $salt)) {
                $error = $locale['token_error_7'];
            }
        }

        if ($error) {
            return $error;
        }

        return FALSE;
    }

    /**
     * Displays Paypal Wallet Form
     *
     * @param $options
     *
     * @return string
     */
    public function form($options) {

        $config = $this->get_config();

        if ($config['sandbox'] && iADMIN || !$config['sandbox']) {

            if ($config['sandbox']) {
                add_notice('danger', '<strong>Paypal Driver Notice:</strong> Development Sandbox Mode Enabled. No actual transactions will be made.');
            }

            $options += Wallet::get_driver_default_options();

            $site_path = fusion_get_settings('site_path');

            $data['payment_id'] = $this->wallet->get_PaymentID($options);
            $data['transaction_shipping'] = $options['order_shipping'];
            $data['transaction_currency'] = $options['currency'];
            $data['items'] = $options['items'];

            $action_url = $site_path.'infusions/wallet/checkout.php?payment_method=paypal&payment_id='.$data['payment_id'];

            $order_item_input = '';
            if (!empty($options['items'])) {
                foreach ($options['items'] as $item_id => $item) {
                    $default = [
                        'id'          => '',
                        'type'        => '',
                        'title'       => '',
                        'description' => '',
                        'tax'         => '',
                        'shipping'    => '',
                        'quantity'    => '',
                        'price'       => '',
                        'currency'    => '',
                        'options'     => '',
                        'info'        => '',
                    ];
                    $item += $default;

                    $order_item_input .= form_hidden('order_item_id[]', '', $item['id'], ['input_id' => 'pp_oid_'.$item_id]);
                    $order_item_input .= form_hidden('order_item_type[]', '', $item['type'], ['input_id' => 'pp_type'.$item_id]);
                    $order_item_input .= form_hidden('order_title[]', '', strip_tags($item['title']), ['input_id' => 'pp_title_'.$item_id]);
                    $order_item_input .= form_hidden('order_description[]', '', strip_tags($item['description']), ['input_id' => 'pp_desc_'.$item_id]);
                    $order_item_input .= form_hidden('order_tax[]', '', $item['tax'], ['input_id' => 'pp_tax_'.$item_id]);
                    $order_item_input .= form_hidden('order_shipping[]', '', $item['shipping'], ['input_id' => 'pp_shipping_'.$item_id]);
                    $order_item_input .= form_hidden('order_quantity[]', '', $item['quantity'], ['input_id' => 'pp_qty_'.$item_id]);
                    $order_item_input .= form_hidden('order_amount[]', '', $item['price'], ['input_id' => 'pp_amt_'.$item_id]);
                    $order_item_input .= form_hidden('order_currency[]', '', $item['currency'], ['input_id' => 'pp_currency_'.$item_id]);
                    $order_item_input .= form_hidden('order_options[]', '', $item['options'], ['input_id' => 'pp_opts_'.$item_id]);
                    $order_item_input .= form_hidden('order_info[]', '', $item['info'], ['input_id' => 'pp_info_'.$item_id]);
                }
            }

            /**
             * Sandbox account
             * buy.sandbox@paypal.com
             * pass:qwerty1234
             */
            $wallet = new Wallet();
            //$paypal_driver = new Paypal_Driver($this->wallet);
            $config = $this->get_config();
            $paypalURL = ($config['sandbox']) ? PAYPAL_SSL_SAND_URL : PAYPAL_SSL_URL;

            // Check if all address, etc is filled up.
            // user_mobile, user_address
            $wallet_info = $wallet->getUserWallet(fusion_get_userdata("user_id"));

            $info = [
                "form" => [
                    "openform"    => openform("paypalPaymentFrm", "POST", $action_url, ["remote_url" => $site_path."infusions/wallet/api/?api=checkout"]),
                    "closeform"   => closeform(),
                    "order_items" => [
                        form_hidden("origin_url", "", $config["base_url"].server("REQUEST_URI"), ["input_id" => "origin_url_paypal"]),
                        form_hidden("callback_url", "", "", ["input_id" => "callback_url_paypal"]),
                        form_hidden("return_url", "", $options["return_url"], ["input_id" => "return_url_paypal"]),
                        form_hidden("cmd", "", "_cart", ["input_id" => "cmd_paypal"]),
                        form_hidden("upload", "", 1, ["input_id" => "upload_paypal"]),
                        form_hidden("business", "", $config["merchant_email"], ["input_id" => "business_paypal"]),
                        form_hidden("cancel_return", "", $config["base_url"].server("REQUEST_URI"), ["input_id" => "cancel_return_paypal"]),
                        form_hidden("notify_url", "", $options["return_url"], ["input_id" => "notify_url_paypal"]),
                        form_hidden("order_payment_type", "", "paypal", ["input_id" => "payment_type_paypal"]),
                        form_hidden("order_payment_method", "", "paypal", ["input_id" => "payment_method_paypal"]),
                        form_hidden("order_payment_currency", "", "USD", ["input_id" => "payment_currency_paypal"]),
                        $order_item_input
                    ],
                    "rform"       => "<form id=\"paypal_frm\" name=\"paypal_frm_payment_method\" action=\"$paypalURL\" method=\"post\"></form>",
                    "submit"      => '<button id="pay_Paypal" value="pay_Paypal" class="btn btn-paypal dark btn-md btn-block">
                    <img class="paypal-button-logo paypal-button-logo-pp paypal-button-logo-black" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAyNCAzMiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJ4TWluWU1pbiBtZWV0Ij4KICAgIDxwYXRoIGZpbGw9IiNmZmZmZmYiIG9wYWNpdHk9IjAuNyIgZD0iTSAyMC43MDIgOS40NDYgQyAyMC45ODIgNy4zNDcgMjAuNzAyIDUuOTQ3IDE5LjU3OCA0LjU0OCBDIDE4LjM2MSAzLjE0OCAxNi4yMDggMi41NDggMTMuNDkzIDIuNTQ4IEwgNS41MzYgMi41NDggQyA0Ljk3NCAyLjU0OCA0LjUwNiAyLjk0OCA0LjQxMiAzLjU0OCBMIDEuMTM2IDI1Ljc0IEMgMS4wNDIgMjYuMjM5IDEuMzIzIDI2LjYzOSAxLjc5MSAyNi42MzkgTCA2Ljc1MyAyNi42MzkgTCA2LjM3OCAyOC45MzggQyA2LjI4NSAyOS4yMzggNi42NTkgMjkuNjM4IDYuOTQgMjkuNjM4IEwgMTEuMTUzIDI5LjYzOCBDIDExLjYyMSAyOS42MzggMTEuOTk1IDI5LjIzOCAxMi4wODkgMjguNzM5IEwgMTIuMTgyIDI4LjUzOSBMIDEyLjkzMSAyMy4zNDEgTCAxMy4wMjUgMjMuMDQxIEMgMTMuMTE5IDIyLjQ0MSAxMy40OTMgMjIuMTQxIDEzLjk2MSAyMi4xNDEgTCAxNC42MTYgMjIuMTQxIEMgMTguNjQyIDIyLjE0MSAyMS43MzEgMjAuMzQyIDIyLjY2OCAxNS40NDMgQyAyMy4wNDIgMTMuMzQ0IDIyLjg1NSAxMS41NDUgMjEuODI1IDEwLjM0NSBDIDIxLjQ1MSAxMC4wNDYgMjEuMDc2IDkuNjQ2IDIwLjcwMiA5LjQ0NiBMIDIwLjcwMiA5LjQ0NiI+PC9wYXRoPgogICAgPHBhdGggZmlsbD0iI2ZmZmZmZiIgb3BhY2l0eT0iMC43IiBkPSJNIDIwLjcwMiA5LjQ0NiBDIDIwLjk4MiA3LjM0NyAyMC43MDIgNS45NDcgMTkuNTc4IDQuNTQ4IEMgMTguMzYxIDMuMTQ4IDE2LjIwOCAyLjU0OCAxMy40OTMgMi41NDggTCA1LjUzNiAyLjU0OCBDIDQuOTc0IDIuNTQ4IDQuNTA2IDIuOTQ4IDQuNDEyIDMuNTQ4IEwgMS4xMzYgMjUuNzQgQyAxLjA0MiAyNi4yMzkgMS4zMjMgMjYuNjM5IDEuNzkxIDI2LjYzOSBMIDYuNzUzIDI2LjYzOSBMIDcuOTcgMTguMzQyIEwgNy44NzYgMTguNjQyIEMgOC4wNjMgMTguMDQzIDguNDM4IDE3LjY0MyA5LjA5MyAxNy42NDMgTCAxMS40MzMgMTcuNjQzIEMgMTYuMDIxIDE3LjY0MyAxOS41NzggMTUuNjQzIDIwLjYwOCA5Ljk0NiBDIDIwLjYwOCA5Ljc0NiAyMC42MDggOS41NDYgMjAuNzAyIDkuNDQ2Ij48L3BhdGg+CiAgICA8cGF0aCBmaWxsPSIjZmZmZmZmIiBkPSJNIDkuMjggOS40NDYgQyA5LjI4IDkuMTQ2IDkuNDY4IDguODQ2IDkuODQyIDguNjQ2IEMgOS45MzYgOC42NDYgMTAuMTIzIDguNTQ2IDEwLjIxNiA4LjU0NiBMIDE2LjQ4OSA4LjU0NiBDIDE3LjIzOCA4LjU0NiAxNy44OTMgOC42NDYgMTguNTQ4IDguNzQ2IEMgMTguNzM2IDguNzQ2IDE4LjgyOSA4Ljc0NiAxOS4xMSA4Ljg0NiBDIDE5LjIwNCA4Ljk0NiAxOS4zOTEgOC45NDYgMTkuNTc4IDkuMDQ2IEMgMTkuNjcyIDkuMDQ2IDE5LjY3MiA5LjA0NiAxOS44NTkgOS4xNDYgQyAyMC4xNCA5LjI0NiAyMC40MjEgOS4zNDYgMjAuNzAyIDkuNDQ2IEMgMjAuOTgyIDcuMzQ3IDIwLjcwMiA1Ljk0NyAxOS41NzggNC42NDggQyAxOC4zNjEgMy4yNDggMTYuMjA4IDIuNTQ4IDEzLjQ5MyAyLjU0OCBMIDUuNTM2IDIuNTQ4IEMgNC45NzQgMi41NDggNC41MDYgMy4wNDggNC40MTIgMy41NDggTCAxLjEzNiAyNS43NCBDIDEuMDQyIDI2LjIzOSAxLjMyMyAyNi42MzkgMS43OTEgMjYuNjM5IEwgNi43NTMgMjYuNjM5IEwgNy45NyAxOC4zNDIgTCA5LjI4IDkuNDQ2IFoiPjwvcGF0aD4KICAgIDxnIHRyYW5zZm9ybT0ibWF0cml4KDAuNDk3NzM3LCAwLCAwLCAwLjUyNjEyLCAxLjEwMTQ0LCAwLjYzODY1NCkiIG9wYWNpdHk9IjAuMiI+CiAgICAgICAgPHBhdGggZmlsbD0iIzIzMWYyMCIgZD0iTTM5LjMgMTYuN2MwLjkgMC41IDEuNyAxLjEgMi4zIDEuOCAxIDEuMSAxLjYgMi41IDEuOSA0LjEgMC4zLTMuMi0wLjItNS44LTEuOS03LjgtMC42LTAuNy0xLjMtMS4yLTIuMS0xLjdDMzkuNSAxNC4yIDM5LjUgMTUuNCAzOS4zIDE2Ljd6Ij48L3BhdGg+CiAgICAgICAgPHBhdGggZmlsbD0iIzIzMWYyMCIgZD0iTTAuNCA0NS4yTDYuNyA1LjZDNi44IDQuNSA3LjggMy43IDguOSAzLjdoMTZjNS41IDAgOS44IDEuMiAxMi4yIDMuOSAxLjIgMS40IDEuOSAzIDIuMiA0LjggMC40LTMuNi0wLjItNi4xLTIuMi04LjRDMzQuNyAxLjIgMzAuNCAwIDI0LjkgMEg4LjljLTEuMSAwLTIuMSAwLjgtMi4zIDEuOUwwIDQ0LjFDMCA0NC41IDAuMSA0NC45IDAuNCA0NS4yeiI+PC9wYXRoPgogICAgICAgIDxwYXRoIGZpbGw9IiMyMzFmMjAiIGQ9Ik0xMC43IDQ5LjRsLTAuMSAwLjZjLTAuMSAwLjQgMC4xIDAuOCAwLjQgMS4xbDAuMy0xLjdIMTAuN3oiPjwvcGF0aD4KICAgIDwvZz4KPC9zdmc+Cg==" alt="" aria-label="pp">
                    <img class="paypal-button-logo paypal-button-logo-paypal paypal-button-logo-black" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjMyIiB2aWV3Qm94PSIwIDAgMTAwIDMyIiB4bWxucz0iaHR0cDomI3gyRjsmI3gyRjt3d3cudzMub3JnJiN4MkY7MjAwMCYjeDJGO3N2ZyIgcHJlc2VydmVBc3BlY3RSYXRpbz0ieE1pbllNaW4gbWVldCI+PHBhdGggZmlsbD0iI2ZmZmZmZiIgZD0iTSAxMiA0LjkxNyBMIDQuMiA0LjkxNyBDIDMuNyA0LjkxNyAzLjIgNS4zMTcgMy4xIDUuODE3IEwgMCAyNS44MTcgQyAtMC4xIDI2LjIxNyAwLjIgMjYuNTE3IDAuNiAyNi41MTcgTCA0LjMgMjYuNTE3IEMgNC44IDI2LjUxNyA1LjMgMjYuMTE3IDUuNCAyNS42MTcgTCA2LjIgMjAuMjE3IEMgNi4zIDE5LjcxNyA2LjcgMTkuMzE3IDcuMyAxOS4zMTcgTCA5LjggMTkuMzE3IEMgMTQuOSAxOS4zMTcgMTcuOSAxNi44MTcgMTguNyAxMS45MTcgQyAxOSA5LjgxNyAxOC43IDguMTE3IDE3LjcgNi45MTcgQyAxNi42IDUuNjE3IDE0LjYgNC45MTcgMTIgNC45MTcgWiBNIDEyLjkgMTIuMjE3IEMgMTIuNSAxNS4wMTcgMTAuMyAxNS4wMTcgOC4zIDE1LjAxNyBMIDcuMSAxNS4wMTcgTCA3LjkgOS44MTcgQyA3LjkgOS41MTcgOC4yIDkuMzE3IDguNSA5LjMxNyBMIDkgOS4zMTcgQyAxMC40IDkuMzE3IDExLjcgOS4zMTcgMTIuNCAxMC4xMTcgQyAxMi45IDEwLjUxNyAxMy4xIDExLjIxNyAxMi45IDEyLjIxNyBaIj48L3BhdGg+PHBhdGggZmlsbD0iI2ZmZmZmZiIgZD0iTSAzNS4yIDEyLjExNyBMIDMxLjUgMTIuMTE3IEMgMzEuMiAxMi4xMTcgMzAuOSAxMi4zMTcgMzAuOSAxMi42MTcgTCAzMC43IDEzLjYxNyBMIDMwLjQgMTMuMjE3IEMgMjkuNiAxMi4wMTcgMjcuOCAxMS42MTcgMjYgMTEuNjE3IEMgMjEuOSAxMS42MTcgMTguNCAxNC43MTcgMTcuNyAxOS4xMTcgQyAxNy4zIDIxLjMxNyAxNy44IDIzLjQxNyAxOS4xIDI0LjgxNyBDIDIwLjIgMjYuMTE3IDIxLjkgMjYuNzE3IDIzLjggMjYuNzE3IEMgMjcuMSAyNi43MTcgMjkgMjQuNjE3IDI5IDI0LjYxNyBMIDI4LjggMjUuNjE3IEMgMjguNyAyNi4wMTcgMjkgMjYuNDE3IDI5LjQgMjYuNDE3IEwgMzIuOCAyNi40MTcgQyAzMy4zIDI2LjQxNyAzMy44IDI2LjAxNyAzMy45IDI1LjUxNyBMIDM1LjkgMTIuNzE3IEMgMzYgMTIuNTE3IDM1LjYgMTIuMTE3IDM1LjIgMTIuMTE3IFogTSAzMC4xIDE5LjMxNyBDIDI5LjcgMjEuNDE3IDI4LjEgMjIuOTE3IDI1LjkgMjIuOTE3IEMgMjQuOCAyMi45MTcgMjQgMjIuNjE3IDIzLjQgMjEuOTE3IEMgMjIuOCAyMS4yMTcgMjIuNiAyMC4zMTcgMjIuOCAxOS4zMTcgQyAyMy4xIDE3LjIxNyAyNC45IDE1LjcxNyAyNyAxNS43MTcgQyAyOC4xIDE1LjcxNyAyOC45IDE2LjExNyAyOS41IDE2LjcxNyBDIDMwIDE3LjQxNyAzMC4yIDE4LjMxNyAzMC4xIDE5LjMxNyBaIj48L3BhdGg+PHBhdGggZmlsbD0iI2ZmZmZmZiIgZD0iTSA1NS4xIDEyLjExNyBMIDUxLjQgMTIuMTE3IEMgNTEgMTIuMTE3IDUwLjcgMTIuMzE3IDUwLjUgMTIuNjE3IEwgNDUuMyAyMC4yMTcgTCA0My4xIDEyLjkxNyBDIDQzIDEyLjQxNyA0Mi41IDEyLjExNyA0Mi4xIDEyLjExNyBMIDM4LjQgMTIuMTE3IEMgMzggMTIuMTE3IDM3LjYgMTIuNTE3IDM3LjggMTMuMDE3IEwgNDEuOSAyNS4xMTcgTCAzOCAzMC41MTcgQyAzNy43IDMwLjkxNyAzOCAzMS41MTcgMzguNSAzMS41MTcgTCA0Mi4yIDMxLjUxNyBDIDQyLjYgMzEuNTE3IDQyLjkgMzEuMzE3IDQzLjEgMzEuMDE3IEwgNTUuNiAxMy4wMTcgQyA1NS45IDEyLjcxNyA1NS42IDEyLjExNyA1NS4xIDEyLjExNyBaIj48L3BhdGg+PHBhdGggZmlsbD0iI2ZmZmZmZiIgZD0iTSA2Ny41IDQuOTE3IEwgNTkuNyA0LjkxNyBDIDU5LjIgNC45MTcgNTguNyA1LjMxNyA1OC42IDUuODE3IEwgNTUuNSAyNS43MTcgQyA1NS40IDI2LjExNyA1NS43IDI2LjQxNyA1Ni4xIDI2LjQxNyBMIDYwLjEgMjYuNDE3IEMgNjAuNSAyNi40MTcgNjAuOCAyNi4xMTcgNjAuOCAyNS44MTcgTCA2MS43IDIwLjExNyBDIDYxLjggMTkuNjE3IDYyLjIgMTkuMjE3IDYyLjggMTkuMjE3IEwgNjUuMyAxOS4yMTcgQyA3MC40IDE5LjIxNyA3My40IDE2LjcxNyA3NC4yIDExLjgxNyBDIDc0LjUgOS43MTcgNzQuMiA4LjAxNyA3My4yIDYuODE3IEMgNzIgNS42MTcgNzAuMSA0LjkxNyA2Ny41IDQuOTE3IFogTSA2OC40IDEyLjIxNyBDIDY4IDE1LjAxNyA2NS44IDE1LjAxNyA2My44IDE1LjAxNyBMIDYyLjYgMTUuMDE3IEwgNjMuNCA5LjgxNyBDIDYzLjQgOS41MTcgNjMuNyA5LjMxNyA2NCA5LjMxNyBMIDY0LjUgOS4zMTcgQyA2NS45IDkuMzE3IDY3LjIgOS4zMTcgNjcuOSAxMC4xMTcgQyA2OC40IDEwLjUxNyA2OC41IDExLjIxNyA2OC40IDEyLjIxNyBaIj48L3BhdGg+PHBhdGggZmlsbD0iI2ZmZmZmZiIgZD0iTSA5MC43IDEyLjExNyBMIDg3IDEyLjExNyBDIDg2LjcgMTIuMTE3IDg2LjQgMTIuMzE3IDg2LjQgMTIuNjE3IEwgODYuMiAxMy42MTcgTCA4NS45IDEzLjIxNyBDIDg1LjEgMTIuMDE3IDgzLjMgMTEuNjE3IDgxLjUgMTEuNjE3IEMgNzcuNCAxMS42MTcgNzMuOSAxNC43MTcgNzMuMiAxOS4xMTcgQyA3Mi44IDIxLjMxNyA3My4zIDIzLjQxNyA3NC42IDI0LjgxNyBDIDc1LjcgMjYuMTE3IDc3LjQgMjYuNzE3IDc5LjMgMjYuNzE3IEMgODIuNiAyNi43MTcgODQuNSAyNC42MTcgODQuNSAyNC42MTcgTCA4NC4zIDI1LjYxNyBDIDg0LjIgMjYuMDE3IDg0LjUgMjYuNDE3IDg0LjkgMjYuNDE3IEwgODguMyAyNi40MTcgQyA4OC44IDI2LjQxNyA4OS4zIDI2LjAxNyA4OS40IDI1LjUxNyBMIDkxLjQgMTIuNzE3IEMgOTEuNCAxMi41MTcgOTEuMSAxMi4xMTcgOTAuNyAxMi4xMTcgWiBNIDg1LjUgMTkuMzE3IEMgODUuMSAyMS40MTcgODMuNSAyMi45MTcgODEuMyAyMi45MTcgQyA4MC4yIDIyLjkxNyA3OS40IDIyLjYxNyA3OC44IDIxLjkxNyBDIDc4LjIgMjEuMjE3IDc4IDIwLjMxNyA3OC4yIDE5LjMxNyBDIDc4LjUgMTcuMjE3IDgwLjMgMTUuNzE3IDgyLjQgMTUuNzE3IEMgODMuNSAxNS43MTcgODQuMyAxNi4xMTcgODQuOSAxNi43MTcgQyA4NS41IDE3LjQxNyA4NS43IDE4LjMxNyA4NS41IDE5LjMxNyBaIj48L3BhdGg+PHBhdGggZmlsbD0iI2ZmZmZmZiIgZD0iTSA5NS4xIDUuNDE3IEwgOTEuOSAyNS43MTcgQyA5MS44IDI2LjExNyA5Mi4xIDI2LjQxNyA5Mi41IDI2LjQxNyBMIDk1LjcgMjYuNDE3IEMgOTYuMiAyNi40MTcgOTYuNyAyNi4wMTcgOTYuOCAyNS41MTcgTCAxMDAgNS42MTcgQyAxMDAuMSA1LjIxNyA5OS44IDQuOTE3IDk5LjQgNC45MTcgTCA5NS44IDQuOTE3IEMgOTUuNCA0LjkxNyA5NS4yIDUuMTE3IDk1LjEgNS40MTcgWiI+PC9wYXRoPjwvc3ZnPg==" alt="" aria-label="paypal">
                    <span>Checkout</span></button>
                    ',
                    /*
                     * <button id="pay_Paypal" value="pay_Paypal" class="btn btn-paypal btn-md btn-block">
                    <img class="paypal-button-logo paypal-button-logo-pp paypal-button-logo-gold" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAyNCAzMiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJ4TWluWU1pbiBtZWV0Ij4KICAgIDxwYXRoIGZpbGw9IiMwMDljZGUiIGQ9Ik0gMjAuOTA1IDkuNSBDIDIxLjE4NSA3LjQgMjAuOTA1IDYgMTkuNzgyIDQuNyBDIDE4LjU2NCAzLjMgMTYuNDExIDIuNiAxMy42OTcgMi42IEwgNS43MzkgMi42IEMgNS4yNzEgMi42IDQuNzEgMy4xIDQuNjE1IDMuNiBMIDEuMzM5IDI1LjggQyAxLjMzOSAyNi4yIDEuNjIgMjYuNyAyLjA4OCAyNi43IEwgNi45NTYgMjYuNyBMIDYuNjc1IDI4LjkgQyA2LjU4MSAyOS4zIDYuODYyIDI5LjYgNy4yMzYgMjkuNiBMIDExLjM1NiAyOS42IEMgMTEuODI1IDI5LjYgMTIuMjkyIDI5LjMgMTIuMzg2IDI4LjggTCAxMi4zODYgMjguNSBMIDEzLjIyOCAyMy4zIEwgMTMuMjI4IDIzLjEgQyAxMy4zMjIgMjIuNiAxMy43OSAyMi4yIDE0LjI1OCAyMi4yIEwgMTQuODIxIDIyLjIgQyAxOC44NDUgMjIuMiAyMS45MzUgMjAuNSAyMi44NzEgMTUuNSBDIDIzLjMzOSAxMy40IDIzLjE1MyAxMS43IDIyLjAyOSAxMC41IEMgMjEuNzQ4IDEwLjEgMjEuMjc5IDkuOCAyMC45MDUgOS41IEwgMjAuOTA1IDkuNSI+PC9wYXRoPgogICAgPHBhdGggZmlsbD0iIzAxMjE2OSIgZD0iTSAyMC45MDUgOS41IEMgMjEuMTg1IDcuNCAyMC45MDUgNiAxOS43ODIgNC43IEMgMTguNTY0IDMuMyAxNi40MTEgMi42IDEzLjY5NyAyLjYgTCA1LjczOSAyLjYgQyA1LjI3MSAyLjYgNC43MSAzLjEgNC42MTUgMy42IEwgMS4zMzkgMjUuOCBDIDEuMzM5IDI2LjIgMS42MiAyNi43IDIuMDg4IDI2LjcgTCA2Ljk1NiAyNi43IEwgOC4yNjcgMTguNCBMIDguMTczIDE4LjcgQyA4LjI2NyAxOC4xIDguNzM1IDE3LjcgOS4yOTYgMTcuNyBMIDExLjYzNiAxNy43IEMgMTYuMjI0IDE3LjcgMTkuNzgyIDE1LjcgMjAuOTA1IDEwLjEgQyAyMC44MTIgOS44IDIwLjkwNSA5LjcgMjAuOTA1IDkuNSI+PC9wYXRoPgogICAgPHBhdGggZmlsbD0iIzAwMzA4NyIgZD0iTSA5LjQ4NSA5LjUgQyA5LjU3NyA5LjIgOS43NjUgOC45IDEwLjA0NiA4LjcgQyAxMC4yMzIgOC43IDEwLjMyNiA4LjYgMTAuNTEzIDguNiBMIDE2LjY5MiA4LjYgQyAxNy40NDIgOC42IDE4LjE4OSA4LjcgMTguNzUzIDguOCBDIDE4LjkzOSA4LjggMTkuMTI3IDguOCAxOS4zMTQgOC45IEMgMTkuNTAxIDkgMTkuNjg4IDkgMTkuNzgyIDkuMSBDIDE5Ljg3NSA5LjEgMTkuOTY4IDkuMSAyMC4wNjMgOS4xIEMgMjAuMzQzIDkuMiAyMC42MjQgOS40IDIwLjkwNSA5LjUgQyAyMS4xODUgNy40IDIwLjkwNSA2IDE5Ljc4MiA0LjYgQyAxOC42NTggMy4yIDE2LjUwNiAyLjYgMTMuNzkgMi42IEwgNS43MzkgMi42IEMgNS4yNzEgMi42IDQuNzEgMyA0LjYxNSAzLjYgTCAxLjMzOSAyNS44IEMgMS4zMzkgMjYuMiAxLjYyIDI2LjcgMi4wODggMjYuNyBMIDYuOTU2IDI2LjcgTCA4LjI2NyAxOC40IEwgOS40ODUgOS41IFoiPjwvcGF0aD4KPC9zdmc+Cg==" alt="" aria-label="pp">
                    <img class="paypal-button-logo paypal-button-logo-paypal paypal-button-logo-gold" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjMyIiB2aWV3Qm94PSIwIDAgMTAwIDMyIiB4bWxucz0iaHR0cDomI3gyRjsmI3gyRjt3d3cudzMub3JnJiN4MkY7MjAwMCYjeDJGO3N2ZyIgcHJlc2VydmVBc3BlY3RSYXRpbz0ieE1pbllNaW4gbWVldCI+PHBhdGggZmlsbD0iIzAwMzA4NyIgZD0iTSAxMiA0LjkxNyBMIDQuMiA0LjkxNyBDIDMuNyA0LjkxNyAzLjIgNS4zMTcgMy4xIDUuODE3IEwgMCAyNS44MTcgQyAtMC4xIDI2LjIxNyAwLjIgMjYuNTE3IDAuNiAyNi41MTcgTCA0LjMgMjYuNTE3IEMgNC44IDI2LjUxNyA1LjMgMjYuMTE3IDUuNCAyNS42MTcgTCA2LjIgMjAuMjE3IEMgNi4zIDE5LjcxNyA2LjcgMTkuMzE3IDcuMyAxOS4zMTcgTCA5LjggMTkuMzE3IEMgMTQuOSAxOS4zMTcgMTcuOSAxNi44MTcgMTguNyAxMS45MTcgQyAxOSA5LjgxNyAxOC43IDguMTE3IDE3LjcgNi45MTcgQyAxNi42IDUuNjE3IDE0LjYgNC45MTcgMTIgNC45MTcgWiBNIDEyLjkgMTIuMjE3IEMgMTIuNSAxNS4wMTcgMTAuMyAxNS4wMTcgOC4zIDE1LjAxNyBMIDcuMSAxNS4wMTcgTCA3LjkgOS44MTcgQyA3LjkgOS41MTcgOC4yIDkuMzE3IDguNSA5LjMxNyBMIDkgOS4zMTcgQyAxMC40IDkuMzE3IDExLjcgOS4zMTcgMTIuNCAxMC4xMTcgQyAxMi45IDEwLjUxNyAxMy4xIDExLjIxNyAxMi45IDEyLjIxNyBaIj48L3BhdGg+PHBhdGggZmlsbD0iIzAwMzA4NyIgZD0iTSAzNS4yIDEyLjExNyBMIDMxLjUgMTIuMTE3IEMgMzEuMiAxMi4xMTcgMzAuOSAxMi4zMTcgMzAuOSAxMi42MTcgTCAzMC43IDEzLjYxNyBMIDMwLjQgMTMuMjE3IEMgMjkuNiAxMi4wMTcgMjcuOCAxMS42MTcgMjYgMTEuNjE3IEMgMjEuOSAxMS42MTcgMTguNCAxNC43MTcgMTcuNyAxOS4xMTcgQyAxNy4zIDIxLjMxNyAxNy44IDIzLjQxNyAxOS4xIDI0LjgxNyBDIDIwLjIgMjYuMTE3IDIxLjkgMjYuNzE3IDIzLjggMjYuNzE3IEMgMjcuMSAyNi43MTcgMjkgMjQuNjE3IDI5IDI0LjYxNyBMIDI4LjggMjUuNjE3IEMgMjguNyAyNi4wMTcgMjkgMjYuNDE3IDI5LjQgMjYuNDE3IEwgMzIuOCAyNi40MTcgQyAzMy4zIDI2LjQxNyAzMy44IDI2LjAxNyAzMy45IDI1LjUxNyBMIDM1LjkgMTIuNzE3IEMgMzYgMTIuNTE3IDM1LjYgMTIuMTE3IDM1LjIgMTIuMTE3IFogTSAzMC4xIDE5LjMxNyBDIDI5LjcgMjEuNDE3IDI4LjEgMjIuOTE3IDI1LjkgMjIuOTE3IEMgMjQuOCAyMi45MTcgMjQgMjIuNjE3IDIzLjQgMjEuOTE3IEMgMjIuOCAyMS4yMTcgMjIuNiAyMC4zMTcgMjIuOCAxOS4zMTcgQyAyMy4xIDE3LjIxNyAyNC45IDE1LjcxNyAyNyAxNS43MTcgQyAyOC4xIDE1LjcxNyAyOC45IDE2LjExNyAyOS41IDE2LjcxNyBDIDMwIDE3LjQxNyAzMC4yIDE4LjMxNyAzMC4xIDE5LjMxNyBaIj48L3BhdGg+PHBhdGggZmlsbD0iIzAwMzA4NyIgZD0iTSA1NS4xIDEyLjExNyBMIDUxLjQgMTIuMTE3IEMgNTEgMTIuMTE3IDUwLjcgMTIuMzE3IDUwLjUgMTIuNjE3IEwgNDUuMyAyMC4yMTcgTCA0My4xIDEyLjkxNyBDIDQzIDEyLjQxNyA0Mi41IDEyLjExNyA0Mi4xIDEyLjExNyBMIDM4LjQgMTIuMTE3IEMgMzggMTIuMTE3IDM3LjYgMTIuNTE3IDM3LjggMTMuMDE3IEwgNDEuOSAyNS4xMTcgTCAzOCAzMC41MTcgQyAzNy43IDMwLjkxNyAzOCAzMS41MTcgMzguNSAzMS41MTcgTCA0Mi4yIDMxLjUxNyBDIDQyLjYgMzEuNTE3IDQyLjkgMzEuMzE3IDQzLjEgMzEuMDE3IEwgNTUuNiAxMy4wMTcgQyA1NS45IDEyLjcxNyA1NS42IDEyLjExNyA1NS4xIDEyLjExNyBaIj48L3BhdGg+PHBhdGggZmlsbD0iIzAwOWNkZSIgZD0iTSA2Ny41IDQuOTE3IEwgNTkuNyA0LjkxNyBDIDU5LjIgNC45MTcgNTguNyA1LjMxNyA1OC42IDUuODE3IEwgNTUuNSAyNS43MTcgQyA1NS40IDI2LjExNyA1NS43IDI2LjQxNyA1Ni4xIDI2LjQxNyBMIDYwLjEgMjYuNDE3IEMgNjAuNSAyNi40MTcgNjAuOCAyNi4xMTcgNjAuOCAyNS44MTcgTCA2MS43IDIwLjExNyBDIDYxLjggMTkuNjE3IDYyLjIgMTkuMjE3IDYyLjggMTkuMjE3IEwgNjUuMyAxOS4yMTcgQyA3MC40IDE5LjIxNyA3My40IDE2LjcxNyA3NC4yIDExLjgxNyBDIDc0LjUgOS43MTcgNzQuMiA4LjAxNyA3My4yIDYuODE3IEMgNzIgNS42MTcgNzAuMSA0LjkxNyA2Ny41IDQuOTE3IFogTSA2OC40IDEyLjIxNyBDIDY4IDE1LjAxNyA2NS44IDE1LjAxNyA2My44IDE1LjAxNyBMIDYyLjYgMTUuMDE3IEwgNjMuNCA5LjgxNyBDIDYzLjQgOS41MTcgNjMuNyA5LjMxNyA2NCA5LjMxNyBMIDY0LjUgOS4zMTcgQyA2NS45IDkuMzE3IDY3LjIgOS4zMTcgNjcuOSAxMC4xMTcgQyA2OC40IDEwLjUxNyA2OC41IDExLjIxNyA2OC40IDEyLjIxNyBaIj48L3BhdGg+PHBhdGggZmlsbD0iIzAwOWNkZSIgZD0iTSA5MC43IDEyLjExNyBMIDg3IDEyLjExNyBDIDg2LjcgMTIuMTE3IDg2LjQgMTIuMzE3IDg2LjQgMTIuNjE3IEwgODYuMiAxMy42MTcgTCA4NS45IDEzLjIxNyBDIDg1LjEgMTIuMDE3IDgzLjMgMTEuNjE3IDgxLjUgMTEuNjE3IEMgNzcuNCAxMS42MTcgNzMuOSAxNC43MTcgNzMuMiAxOS4xMTcgQyA3Mi44IDIxLjMxNyA3My4zIDIzLjQxNyA3NC42IDI0LjgxNyBDIDc1LjcgMjYuMTE3IDc3LjQgMjYuNzE3IDc5LjMgMjYuNzE3IEMgODIuNiAyNi43MTcgODQuNSAyNC42MTcgODQuNSAyNC42MTcgTCA4NC4zIDI1LjYxNyBDIDg0LjIgMjYuMDE3IDg0LjUgMjYuNDE3IDg0LjkgMjYuNDE3IEwgODguMyAyNi40MTcgQyA4OC44IDI2LjQxNyA4OS4zIDI2LjAxNyA4OS40IDI1LjUxNyBMIDkxLjQgMTIuNzE3IEMgOTEuNCAxMi41MTcgOTEuMSAxMi4xMTcgOTAuNyAxMi4xMTcgWiBNIDg1LjUgMTkuMzE3IEMgODUuMSAyMS40MTcgODMuNSAyMi45MTcgODEuMyAyMi45MTcgQyA4MC4yIDIyLjkxNyA3OS40IDIyLjYxNyA3OC44IDIxLjkxNyBDIDc4LjIgMjEuMjE3IDc4IDIwLjMxNyA3OC4yIDE5LjMxNyBDIDc4LjUgMTcuMjE3IDgwLjMgMTUuNzE3IDgyLjQgMTUuNzE3IEMgODMuNSAxNS43MTcgODQuMyAxNi4xMTcgODQuOSAxNi43MTcgQyA4NS41IDE3LjQxNyA4NS43IDE4LjMxNyA4NS41IDE5LjMxNyBaIj48L3BhdGg+PHBhdGggZmlsbD0iIzAwOWNkZSIgZD0iTSA5NS4xIDUuNDE3IEwgOTEuOSAyNS43MTcgQyA5MS44IDI2LjExNyA5Mi4xIDI2LjQxNyA5Mi41IDI2LjQxNyBMIDk1LjcgMjYuNDE3IEMgOTYuMiAyNi40MTcgOTYuNyAyNi4wMTcgOTYuOCAyNS41MTcgTCAxMDAgNS42MTcgQyAxMDAuMSA1LjIxNyA5OS44IDQuOTE3IDk5LjQgNC45MTcgTCA5NS44IDQuOTE3IEMgOTUuNCA0LjkxNyA5NS4yIDUuMTE3IDk1LjEgNS40MTcgWiI+PC9wYXRoPjwvc3ZnPg==" alt="" aria-label="paypal">
                    <span>Checkout</span></button>
                     */
                ]
            ];

            // Look into wwpp function button click
            fusion_load_script(WALLET."drivers/paypal/js/wwpp.js");
            add_to_jquery(/** @lang JavaScript */ "wwpp.init('".$data['payment_id']."', '".$options['display_amount_field']."', '".$options['order_amount']."', '$this->paypal_token', '".fusion_get_token($this->paypal_token, 1)."');");

            return fusion_render(INFUSIONS.'wallet/drivers/paypal/templates', 'form.twig', $info, TRUE);
        }

        return 'Wallet cannot be viewed due to insufficient rights';
    }

}
