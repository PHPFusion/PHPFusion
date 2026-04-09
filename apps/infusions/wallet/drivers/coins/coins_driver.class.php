<?php

namespace PHPFusion\Infusions\Wallet\Drivers\Coins;


use DateTime;
use Defender;
use Exception;
use PHPFusion\Infusions\Wallet\Classes\Wallet;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Transaction;
use ThemeFactory\Core;

/**
 * Class Coins_Driver
 *
 * @package PHPFusion\Infusions\Wallet\Drivers\Coins
 */
class Coins_Driver {

    private static $instance = NULL;

    // Wallet class
    private $wallet = NULL;

    // Defender class
    private $defender = NULL;

    // Wallet settings
    private $wallet_settings = [];

    private $coin_token = '0qw9wuscn)D%';
    private $info = [];
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

    public function __construct() {

        $this->defender = Defender::getInstance();

        $this->wallet = Wallet::getInstance();

        $this->wallet_settings = Wallet::walletSettings();
    }

    /*
     * PHP-Fusion Wallet Module
     */

    /**
     * @return object
     */

    public function __clone() {
        die('Cloning of this class is prohibited');
    }

    /**
     * @return object
     */

    public function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return (object)self::$instance;
    }

    public function __Properties() {
        return [
            'title'                      => 'Fusion Coins',
            'description'                => 'The cheapest payment with your wallet gold coins.',
            'admin_description'          => 'Payment Gateway for Fusion Coins',
            'link'                       => '',
            'author'                     => 'PHP-Fusion Inc',
            'author_web'                 => 'https://www.php-fusion.co.uk',
            'author_email'               => 'mt@php-fusion.co.uk',
            'version'                    => '1.00',
            'pay_method'                 => 'Wallet Checkout',
            "pay_image"                  => "",
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
        return TRUE;
        //return !empty($this->wallet_settings["paypal_enabled"]);
    }

    /*
     * Credit Checkout Confirmation Page
     * Focus on finding the transaction and payment and set it as paid.
     */

    public function form($options) {

        $wallet = $this->wallet;

        $config['base_url'] = (server('HTTPS') ? "https" : "http")."://".server('SERVER_NAME').(server('SERVER_PORT') != 80 ? ":".server('SERVER_PORT') : '');

        $options += Wallet::get_driver_default_options();

        // This one is not used.
        if (post('form_id') == 'creditPaymentForm') {

            $info = [
                'origin_url'        => sanitizer('origin_url', '', 'origin_url'),
                'callback_url'      => sanitizer('callback_url', '', 'callback_url'),
                'return_url'        => sanitizer('return_url', '', 'return_url'),
                'order_item_id'     => sanitizer('order_item_id', '', 'order_item_id'),
                'order_item_type'   => sanitizer('order_item_type', '', 'order_item_type'),
                'order_title'       => sanitizer('order_title', '', 'order_title'),
                'order_description' => sanitizer('order_description', '', 'order_description'),
                'order_quantity'    => sanitizer('order_quantity', '', 'order_quantity'),
                'order_amount'      => sanitizer('order_amount', '', 'order_amount'),
                'order_currency'    => sanitizer('order_currency', '', 'order_currency'),
                'payment_method'    => 'credit'
            ];

            // use jquery to log the transaction.
            if (fusion_safe()) {
                if ($wallet['balance'] > $info['order_amount']) {
                    add_notice("success", "OK");
                } else {
                    add_notice('danger', "Insufficient coins for the transaction. Please top-up your wallet balance in your wallet.");
                }
            }
        }

        $site_path = fusion_get_settings('site_path');

        $data['payment_id'] = $wallet->get_PaymentID($options);
        $data['transaction_shipping'] = $options['order_shipping'];
        $data['transaction_currency'] = $options['currency'];
        $data['items'] = $options['items'];

        $action_url = $site_path.'infusions/wallet/checkout.php?payment_method=credit&payment_id='.$data['payment_id'];

        // this one is the one we post to checkout. We can skip this because we do not need it, as it is now replaced by JS script service.

        $credit_fail = post('credit_fail');
        if ($credit_fail) {
            add_notice('warning', $credit_fail);
        }
        $html = openform('creditPaymentForm', 'post', $action_url, ['remote_url' => $site_path.'infusions/wallet/checkout.json.php']); // we need to post to a checkout page.
        $html .= form_hidden('origin_url', 'Origin URL', $config['base_url'].server('REQUEST_URI'));
        $html .= form_hidden('callback_url', 'Callback URL', '');
        $html .= form_hidden('return_url', 'Return URL', $options['return_url']);
        $html .= form_hidden('order_payment_type', '', 'Fusion Coin Credits');
        $html .= form_hidden('order_payment_method', '', 'credit');
        $html .= form_hidden('order_payment_currency', '', 'coins');
        if (!empty($options['items'])) {
            foreach ($options['items'] as $item_id => $item) {
                $html .= form_hidden('order_item_id[]', '', $item['id']);
                $html .= form_hidden('order_item_type[]', '', $item['type']);
                $html .= form_hidden('order_title[]', '', strip_tags($item['title']));
                $html .= form_hidden('order_description[]', '', strip_tags($item['description']));
                $html .= form_hidden('order_tax[]', '', $item['tax']);
                $html .= form_hidden('order_shipping[]', '', $item['shipping']);
                $html .= form_hidden('order_quantity[]', '', $item['quantity']);
                $html .= form_hidden('order_amount[]', '', $item['price']);
                $html .= form_hidden('order_currency[]', '', $item['currency']);
            }
        }
        $html .= form_button('pay_credit', 'Pay with Fusion Coins', 'pay_credit', ['class' => 'btn-primary small text-expanded btn-md btn-block']);
        $html .= closeform();

        $html .= "<div id='credit-form-container'>";
        $html .= openform("credit_form", "post", WALLET.'confirmation.php?payment_method=credit');
        $html .= closeform();
        $html .= "</div>";

        fusion_load_script(WALLET."drivers/coins/wwpc.js");

        $fusion_token = fusion_get_token($this->coin_token, 1);

        add_to_jquery("wwpc.init('".$data['payment_id']."', '".$options['display_amount_field']."', '".$options['order_amount']."', '$this->coin_token', '$fusion_token');");

        return (string)$html;
    }

    /**
     * Validate a return
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

        $timestamp = time();

        $transaction_ref = post("transaction_ref");

        try {
            $date = new DateTime();
            $timestamp = $date->getTimestamp();
        } catch (Exception $e) {
            set_error(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine());
        }

        $this->info = [
            "transaction_ref"    => $transaction_ref,
            "transaction_number" => post("order_id"),
            "transaction_method" => post("payment_method"),
            "payment_date"       => post("payment_date"),
            'store_name'         => Wallet_Model::walletSettings('store_name'),
            'datestamp'          => date('j M Y, H:M:s', $timestamp),
            'currency'           => "USD",
            "payment_status"     => "Completed"
        ];

        $transaction = new Wallet_Transaction();

        if ($transaction->getRef($transaction_ref)) {

            $transaction_data = $transaction->transactionData();

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

            $transaction_data["transaction_status"] = "Completed";

            $wallet_info = Wallet::getInstance()->getUserWallet($transaction_data["transaction_user"]);

            $this->info["wallet"] = $wallet_info;

            $this->info["transaction"] = $transaction_data;

            // Log Payment
            $this->info["transaction"]["transaction_orders"] = $transaction->getOrders();

            $this->info["transaction"]["transaction_status"] = TRANSACTION_PAID;

            // Log Payment to Transaction and Orders - Checked
            $transaction->setPayment($this->info["transaction"]["transaction_status"], $timestamp, $transaction_data["transaction_user"]);

            $transaction_file = str_replace(fusion_get_settings('siteurl'), BASEDIR, rawurldecode($transaction_data["transaction_file"]));

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
            $this->setError(900);
        }

        return (array)$this->info;
    }

    /**
     * @param $error_code
     */
    private function setError($error_code) {
        fusion_stop();

        $errors = $this->errors[$error_code];

        add_notice('danger', "<strong>".$errors['title']."</strong> ".$errors["description"]);

        $log = [
            'log_errors'    => '(PAYPAL) '.$errors['title'],
            'log_id'        => 0,
            'log_user'      => fusion_get_userdata('user_id'),
            'log_data'      => Defender::sanitize_array($_REQUEST),
            'log_datestamp' => time(),
        ];

        dbquery_insert(DB_WALLET_LOGS, $log, 'save');
    }


}
