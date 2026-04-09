<?php

namespace PHPFusion\Infusions\Wallet\Drivers\FirstData;

use PHPFusion\Geomap;
use PHPFusion\Infusions\Wallet\Classes\Receipt;
use PHPFusion\Infusions\Wallet\Classes\Wallet;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Fields_Helper;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;
use Yandex\Translate\Exception;

/**
 * Class FirstData_Driver
 *
 * @package PHPFusion\Infusions\Wallet\Drivers\FirstData
 */
class FirstData_Driver {

    /**
     * @var null
     */
    private static $instance = NULL;
    /**
     * @var string
     */
    private $firstdata_token = 'pfusion_return';
    /**
     * @var \Defender|null
     */
    private $defender = NULL;
    /**
     * @var int
     */
    private $token_user_id = 0;
    /**
     * @var bool
     */
    private $debug = FALSE;
    /**
     * @var string
     */
    private static $checkout_template = __DIR__.'/../../templates/payment/checkout.html';
    /**
     * @var string
     */
    private static $processing_template = __DIR__.'/../../templates/payment/processing.html';

    private $wallet = NULL;

    private $wallet_settings = [];

    /**
     * @return object
     */
    public function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return (object)self::$instance;
    }

    /**
     * FirstData_Driver constructor.
     *
     * @param Wallet $wallet
     */
    public function __construct(Wallet $wallet) {
        $this->defender = \Defender::getInstance();
        $this->wallet = $wallet::getInstance();
        $this->wallet_settings = $this->wallet::walletSettings();
    }

    public function get_FirstDataToken() {
        return $this->firstdata_token;
    }

    public function __clone() {
        die('Cloning of this class is prohibited');
    }

    /*
     * PHP-Fusion Wallet Module
     */
    public function __Properties() {
        return [
            'title'                      => 'Credit Card',
            'description'                => 'Payment Gateway for FirstData Global Credit Card System',
            'link'                       => '',
            'author'                     => 'PHP-Fusion Inc',
            'author_web'                 => 'https://www.php-fusion.co.uk',
            'author_email'               => 'mt@php-fusion.co.uk',
            'version'                    => '1.00',
            'pay_method'                 => 'Credit Card Checkout',
            'pay_image'                  => "
                <img alt='Visa' src='".fusion_get_settings('siteurl')."infusions/wallet/drivers/firstdata/images/logo-visa.png' />
                <img alt='Master Card' src='".fusion_get_settings('siteurl')."infusions/wallet/drivers/firstdata/images/logo-mastercard.png' />",
            //'pay_image'                  => "<img alt='Paypal' style='width: 78px; max-width: none; margin-top: -14px; right: 50px;' src='".fusion_get_settings('siteurl')."infusions/wallet/drivers/paypal/paypal.svg'>",
            // Driver Directory Specs
            'callback_settings_function' => 'settings',
            'callback_charge_function'   => 'checkout',
            'callback_validate_function' => 'validate',
            'callback_refund_function'   => 'refund',
            'callback_record_function'   => 'record',
            'callback_read_function'     => 'read',
            'callback_form_function'     => 'form',
        ];
    }

    public static function __getOption() {

    }

    /**
     *  Driver Settings form
     *
     * @return string
     * @throws \Exception
     */
    public function settings() {

        $defaults = [
            'fdata_name'     => '',
            'fdata_mid'      => '',
            'fdata_sid'      => '',
            'fdata_hash'     => '',
            'fdata_currency' => '',
        ];

        $settings = Wallet_Model::walletSettings();
        $data = $settings + $defaults;

        if (post('save_firstdata')) {
            //do manual
            $data = [
                'fdata_name'     => sanitizer('fdata_name', '', 'fdata_name'),
                'fdata_mid'      => sanitizer('fdata_mid', '', 'fdata_mid'),
                'fdata_sid'      => sanitizer('fdata_sid', '', 'fdata_sid'),
                'fdata_hash'     => sanitizer('fdata_hash', '', 'fdata_hash'),
                'fdata_currency' => sanitizer(['fdata_currency'], '', 'fdata_currency[]'),
            ];


            if (fusion_safe()) {
                foreach ($data as $key => $input_value) {
                    $sql_param = [
                        ':val'        => $input_value,
                        ':insert_key' => $key,
                        ':inf'        => 'wallet'
                    ];

                    if (isset($settings[$key])) {

                        dbquery(
                            "UPDATE ".DB_SETTINGS_INF." SET settings_value=:val WHERE settings_name=:insert_key AND settings_inf=:inf",
                            $sql_param
                        );
                    } else {

                        dbquery(
                            "INSERT INTO ".DB_SETTINGS_INF." (`settings_name`, `settings_value`, `settings_inf`) VALUES (:insert_key, :val, :inf)",
                            $sql_param
                        );
                    }
                }

                add_notice('success', 'First Data settings has been updated');

                if (post('save_firstdata') == 'close') {
                    redirect(clean_request('', ['configure'], FALSE));
                }

                redirect(FUSION_REQUEST);
            }
        }

        if (empty($data['fdata_name'])) {
            $var['fdata_name'] = fusion_get_settings('sitename');
        }

        $tpl = "<div class='spacer-sm'>";
        $tpl .= "<img class='img-responsive' style='max-width:150px' src='".WALLET."drivers/firstdata/fdata.jpg'/>";
        $tpl .= "<h4>First Data Gateway Configuration</h4><hr/>";
        $tpl .= "</div>\n";
        $tpl .= openform('paypal_form', 'post', FUSION_REQUEST);
        $tpl .= "<div class='row'>\n";
        $tpl .= "<div class='col-xs-12 col-sm-3'><strong>Merchant Account Credentials</strong></div>";
        $tpl .= "<div class='col-xs-12 col-sm-9'>\n";
        $tpl .= form_text('fdata_name', 'Merchant Billing Name', $data['fdata_name'], ['required' => TRUE]);
        $tpl .= form_text('fdata_mid', 'Merchant ID', $data['fdata_mid'], ['required' => TRUE]);
        $tpl .= form_text('fdata_sid', 'Merchant Store ID', $data['fdata_sid'], ['required' => TRUE]);
        $tpl .= form_text('fdata_hash', 'Merchant Secret Hash', $data['fdata_hash'], ['required' => TRUE]);
        $tpl .= "</div>";
        $tpl .= "</div>\n<hr/>";
        $tpl .= "<div class='row'>\n";
        $tpl .= "<div class='col-xs-12 col-sm-3'><strong>Gateway Configurations</strong></div>";
        $tpl .= "<div class='col-xs-12 col-sm-9'>\n";
        $tpl .= form_select(
            'fdata_currency[]', "Merchant Acceptable Currency", $data['fdata_currency'], [
                "options"     => Geomap::get_Currency(),
                "multiple"    => TRUE,
                "width"       => "100%",
                "inner_width" => "100%",
                "ext_tip"     => "Please ensure that the processing currency is acceptable by First Data before enabling each of them."
            ]
        );
        $tpl .= "</div>";
        $tpl .= "</div>\n<hr/>";

        // we need a checkbox for all available payment gateways
        $tpl .= form_button('cancel', 'Cancel', 'cancel', ['class' => 'btn-default m-r-10']);
        $tpl .= form_button(
            'save_firstdata', 'Save', 'open', ['class' => 'btn-default m-r-10', 'input_id' => 'just_save']
        );
        $tpl .= form_button('save_firstdata', 'Save and Close', 'close', ['class' => 'btn-primary']);

        return $tpl;
    }

    public static function refund() {

    }

    /**
     * Verify the custom firstdata token.
     *
     * @return bool
     */
    private function verify_token() {
        $error = FALSE;
        $settings = fusion_get_settings();
        $locale = fusion_get_locale();
        $wallet_info = Wallet::getInstance()->getUserWallet(fusion_get_userdata("user_id"));
        $token_data = explode('-', stripinput($_REQUEST['customParam_token']));

        if (count($token_data) == 3) {

            list($this->token_user_id, $token_time, $hash) = $token_data;
            $user_id = (iMEMBER ? $wallet_info['user_id'] : 0);
            $algo = $settings['password_algorithm'];
            $salt = md5(
                isset($userdata['user_salt']) && iMEMBER ? fusion_get_userdata(
                        'user_salt'
                    ).SECRET_KEY_SALT : SECRET_KEY_SALT
            );
            // check if the logged user has the same ID as the one in token
            if ($this->token_user_id != $user_id) {
                $error = $locale['token_error_4'];
                // make sure the token datestamp is a number
            } else if (!isnum($token_time)) {
                $error = $locale['token_error_5'];
                // check if the hash is valid
            } else if ($hash !== hash_hmac(
                    $algo, $this->token_user_id.$token_time.$this->firstdata_token.SECRET_KEY, $salt
                )) {
                $error = $locale['token_error_7'];
            } else if ((TIME - $token_time) < fusion_get_settings('flood_interval') && !iADMIN) {
                // check if a post wasn't made too fast. Set $post_time to 0 for instant. Go for System Settings later.
                $error = $locale['token_error_6'];
            }

        } else {
            // token format is incorrect
            $error = $locale['token_error_8'];
        }

        if ($error) {
            return $error;
        }

        return FALSE;
    }

    private static function send_message($user_id, $pm_subject, $pm_message, $bot_id = 15756) {
        dbquery(
            "INSERT INTO ".DB_MESSAGES." (message_id, message_to, message_from, message_user, message_subject, message_message,
        message_smileys, message_read, message_datestamp, message_folder) VALUES ('', '$user_id', '$bot_id','$user_id', '$pm_subject', '$pm_message', 'n', '0', '".TIME."', '0')"
        );
    }

    private function successLog() {
        /**
         * Array
         * (
         * [txndate_processed] => 25/07/19 08:06:22
         * [ccbin] => 458581
         * [timezone] => Europe/London
         * [oid] => 06103520681150645312
         * [customParam_walletID] => 2
         * [bzip] => 72590
         * [cccountry] => MYS
         * [expmonth] => 10
         * [merchantTransactionId] => 830
         * [hash_algorithm] => SHA256
         * [endpointTransactionId] => 920602032485
         * [currency] => 840
         * [processor_response_code] => 00
         * [chargetotal] => 1.00
         * [bcity] => Lindskarsvagen
         * [email] => meangczac.chan@gmail.com
         * [terminal_id] => 73903889
         * [invoicenumber] => ZCNxXG
         * [approval_code] => Y:565492:7666235792:PPX :920602032485
         * [customParam_payment] => firstdata
         * [customParam_token] => 16331-1564022176-fccdd255af0af5aaf7d4f86336f0e29d03734f2fda67ab38f98e379862bc5f69
         * [expyear] => 2021
         * [response_hash] => 8429eeac323bbd36c19f292cc955c58655015ec3c1fa64e611e16f8e96382f92
         * [response_code_3dsecure] => 1
         * [bstate] => Vastmanland,SE
         * [schemeTransactionId] => 589206094170213
         * [tdate] => 1564022182
         * [installments_interest] => false
         * [bname] => Frederick Chan
         * [phone] => +60-01116669222
         * [ccbrand] => VISA
         * [customerid] => 16331
         * [txntype] => sale
         * [paymentMethod] => V
         * [txndatetime] => 2019:07:25-03:36:16
         * [cardnumber] => (VISA) ... 2735
         * [ipgTransactionId] => 67666235792
         * [baddr1] => Lot 87 Taman Khidmat
         * [bcountry] => SE
         * [baddr2] => Lorong Pokok Seraya 3A
         * [status] => APPROVED
         * [customParam_paymentID] => 06103520681150645312
         * )
         */
    }

    /**
     * Return and read records of the transaction status and provide a method to check whether it is verified or not.
     * if it is, then we will update the transaction and orders SQL
     * and then we will include the transaction file.
     */
    private $info = [];

    public function validate() {
        $date = new \DateTime(post('payment_date'));
        $timestamp = $date->getTimestamp();
        $currency = post('currency');

        $currency_array = array_flip(self::$currency_code);
        $this->info = [
            'store_name'         => $this->get_config('merchant_name') ?: Wallet_Model::walletSettings('store_name'),
            'store_name'         => $this->get_config('merchant_name') ?: Wallet_Model::walletSettings('store_name'),
            'invoice'            => post('oid'),
            'datestamp'          => showdate('longdate', $timestamp),
            'payment_status'     => post('status'),
            'currency'           => (isset($currency_array[$currency]) ? $currency_array[$currency] : ''),
            'transaction_status' => TRANSACTION_FAILED,
            'mc_gross'           => post('chargetotal'),
            'business_email'     => fusion_get_settings('siteemail')
        ];

        // check if the transaction hash is correct.
        if ($this->verifyHash()) {
            if ($this->verify_token()) {
                // Make sure the payment status is "Completed"
                if ($this->info['payment_status'] == 'APPROVED') {
                    $payment_status = TRANSACTION_PAID;
                } else {
                    $this->setError(100);
                    $payment_status = TRANSACTION_FAILED;
                }

                if ($this->info['currency'] !== 'USD') {
                    $this->setError(300);
                }

                if (empty($this->info['invoice']) or empty($timestamp)) {
                    $this->setError(400);
                }

                if ($this->response_ref && $this->invoice_number && $this->token_user_id) {

                    if (intval($this->response_ref) == intval($this->info['invoice'])) {

                        $wallet_info = Wallet::getInstance()->getUserWallet($this->token_user_id);
                        $this->info += $wallet_info;

                        // Search the transaction only
                        $cond = "transaction_ref=:ref AND transaction_number=:inv AND transaction_user=:uid";
                        $param = [
                            ':ref' => $this->response_ref,
                            ':inv' => $this->invoice_number,
                            ':uid' => $this->token_user_id
                        ];

                        if (dbcount("(transaction_id)", DB_WALLET_TRANSACTIONS, $cond, $param)) {

                            // data
                            $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE $cond", $param);
                            $data = dbarray($result);

                            if (empty($data['transaction_response'])) {
                                // update transaction response
                                $data['transaction_response'] = \Defender::encode($_REQUEST);
                                dbquery(
                                    "UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_status=1, transaction_response=:response WHERE $cond",
                                    $param + [
                                        ':response' => $data['transaction_response']
                                    ]
                                );
                            }

                            if ($this->info['mc_gross'] != $data['transaction_amount']) {
                                $this->setError(80);
                            }

                            if (empty($data['transaction_file'])) {
                                $this->setError(800);
                            }

                            $this->info['transaction_status'] = $payment_status;

                            $transaction_file = str_replace(
                                fusion_get_settings('siteurl'), BASEDIR, rawurldecode($data['transaction_file'])
                            );

                            if (is_file($transaction_file)) {

                                $payment_response = \Defender::encode(\Defender::sanitize_array($_REQUEST));

                                if (iSUPERADMIN) {
                                    print_P($payment_response);
                                }


                                // Update transactions
                                dbquery(
                                    "UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_status=:status, transaction_response=:response WHERE $cond",
                                    $param + [
                                        ':response' => $payment_response,
                                        ':status'   => $payment_status
                                    ]
                                );

                                // update all orders to paid
                                dbquery(
                                    "UPDATE ".DB_WALLET_ORDERS." SET
                                order_paid='$payment_status',
                                order_paid_datestamp='".TIME."',
                                order_paid_user='".$this->token_user_id."'
                                WHERE order_id IN (".str_replace(".", ",", $data['transaction_oid']).")"
                                );

                                // Update $_REQUEST for Wallet_Checkout class API -- This is the fastest to deliver
                                $transaction_ref['transaction_id'] = $data['transaction_id'];
                                $transaction_ref['transaction_ref'] = $data['transaction_ref'];
                                $_REQUEST += $transaction_ref;

                                // run web hook.
                                require $transaction_file;

                                $this->info['order_results'] = fusion_filter_hook('wallet_checkout');

                            } else {
                                $this->setError(800);
                            }

                            $receipt = new Receipt();
                            $this->info['order_receipts'] = $receipt->displayOrderReceipt( $this->info['invoice'], $wallet_info );

                            //$this->info['order_receipts'] = $this->wallet->displayOrderReceipt(
                            //    $this->info['invoice'], $wallet_info
                            //);


                        } else {
                            $this->setError(700);
                        }
                    } else {
                        $this->setError(700);
                    }
                } else {
                    $this->setError(600);
                }
            } else {
                $this->setError(700);
            }
        } else {
            $this->setError(500);
        }

        return (array)$this->info;
    }

    private $errors = [
        80  => [
            "title"       => "Payment Error: Invalid payment amount",
            "description" => "The bill and the payment made are different."
        ],
        100 => [
            "title"       => "Payment Error: Payment was not completed",
            "description" => "Your payment has not gone through or is currently pending.",
        ],
        200 => [
            "title"       => "Payment Error: Invalid merchant mail verification",
            "description" => "The mail verification failed.",
        ],
        300 => [
            "title"       => "Payment Error: Invalid currency verification",
            "description" => "The currency verification failed.",
        ],
        400 => [
            "title"       => "Payment Error: Payment error",
            "description" => "There was no transaction id being sent by credit card.",
        ],
        500 => [
            'title'       => "Payment Error: Payment IPN Error",
            'description' => "Your last transaction has an invalid transaction token.",
        ],
        600 => [
            'title'       => "Payment Error: Invalid Payment Verification",
            'description' => "Your last transaction has an invalid transaction token.",
        ],
        700 => [
            'title'       => "Payment Error:Unknown Error",
            'description' => "Transaction contains an invalid security token.",
        ],
        800 => [
            'title'       => "Delivery cannot be made",
            'description' => "No transaction file defined."
        ],
        900 => [
            'title'       => 'No transaction found',
            'description' => 'No transaction can be found for this request.'
        ]
    ];

    private function setError($error_code) {
        $errors = $this->errors[$error_code];
        add_notice('danger', $errors['title']);
        if ($this->IPN === TRUE) {
            $log = [
                'log_errors'    => '(PAYPAL) '.$errors['title'],
                'log_id'        => 0,
                'log_user'      => fusion_get_userdata('user_id'),
                'log_data'      => \Defender::sanitize_array($_REQUEST),
                'log_datestamp' => TIME,
            ];
            dbquery_insert(DB_WALLET_LOGS, $log, 'save');
        } else {
            $this->info['errors'] = $errors;
        }
        \Defender::stop($error_code);
    }

    public static function record() {
    }

    public static function read() {
    }

    /**
     * First data charges MYR100.00 per year for each currency acceptable
     *
     * @var array
     */
    private static $currency_code = [
        'AWG' => '533',
        'AUD' => '036',
        'BSD' => '044',
        'BHD' => '048',
        'BBD' => '052',
        'MYR' => '458',
        'MXN' => '484',
        'USD' => '840',
        'ANG' => '532',
        'NZD' => '554',
        'NOK' => '578',
        'GBP' => '826',
        'TWD' => '901'
    ];

    public function get_transaction_currency($iso_code) {

        $default_currency = self::get_config('fdata_currency');

        return (string)(isset(self::$currency_code[$iso_code])) ? self::$currency_code[$iso_code] : self::$currency_code[$default_currency];
    }

    public function get_config($key = NULL) {
        // get wallet settings
        $wallet_settings = Wallet_Model::walletSettings();
        $config['base_url'] = ((isset($_SERVER['HTTPS'])) ? "https" : "http")."://".$_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT'] != 80 ? ":".$_SERVER['SERVER_PORT'] : '');
        $config['merchant_id'] = $wallet_settings['fdata_mid'];
        if (empty($config['merchant_id']))
            add_notice('danger', 'FirstData merchant id is not set.');

        $config['merchant_secret'] = $wallet_settings['fdata_hash'];
        if (empty($config['merchant_secret']))
            add_notice('danger', 'FirstData merchant secret hash is not set.');

        $config['merchant_store_id'] = $wallet_settings['fdata_sid'];
        if (empty($config['merchant_store_id']))
            add_notice('danger', 'FirstData merchant store id is not set.');

        $config['merchant_name'] = $wallet_settings['fdata_name'];

        if (empty($config['merchant_name']))
            add_notice('danger', 'FirstData merchant name is not set.');

        $config['merchant_currency'] = $wallet_settings['fdata_currency'];
        if (empty($config['merchant_currency']))
            add_notice('danger', 'FirstData currency is not set.');

        $config['DefaultCancelURL'] = isset($_REQUEST['origin_url']) ? $_REQUEST['origin_url'] : '';
        // Purchase Page
        $config['DefaultCheckoutURL'] = fusion_get_settings('siteurl').'infusions/wallet/checkout.php?payment_method=firstdata';
        // Return Checkout Complete
        $config['DefaultCallbackURL'] = fusion_get_settings('siteurl').'infusions/wallet/confirmation.php?payment_method=firstdata'; // this is the IPN file
        // I wonder about this.
        $config['DefaultReturnURL'] = fusion_get_settings('siteurl').'infusions/wallet/confirmation.php'; // this is the thank you page.

        $config['UserID'] = fusion_get_userdata('user_id') ?: USER_IP;
        $config['PageTimeout'] = 15 * 60;
        //$config['CustIP'] = (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $config['CustIP'] = USER_IP;

        return $key === NULL ? $config : (isset($config[$key]) ? $config[$key] : NULL);
    }

    /**
     * Returns user current timezone
     *
     * @return string
     */
    public function get_current_timezone() {

        //return ini_get('date.timezone');
        $tz_client = fusion_get_settings('timeoffset');
        $user_timezone = fusion_get_userdata('user_timezone');

        if (!empty($user_timezone)) {
            $tz_client = $user_timezone;
        }

        if (empty($tz_client)) {
            $tz_client = 'Europe/London';
        }

        return (string)$tz_client;
    }

    private $time = [];

    /**
     *  Returns a date formatted required by gateway
     *
     * @param null $timestamp Set if required to modify calculated output
     *                        - Y:m:d-H:i:s
     *
     * @return array
     */
    public function get_current_datetime($timestamp = NULL) {

        $tz_client = $this->get_current_timezone();

        $client_dtz = new \DateTime($tz_client);

        if ($timestamp !== NULL) {
            $client_dtz->setTimestamp($timestamp);
        }

        if (empty($this->time)) {
            $this->time['datetime'] = $client_dtz->format('Y:m:d-H:i:s');
            $this->time['timestamp'] = $client_dtz->getTimestamp();
        }

        return (array)$this->time;
    }

    /*
    Function that calculates the hash of the following parameters:
    - Store Id
    - Date/Time(see $dateTime above)
    - chargetotal
    - currency (numeric ISO value)
    - shared secret
    */
    public function createHash($chargetotal, $currency, $datetime = TIME) {
        $datetime = $this->get_current_datetime($datetime);
        $hash_components = [
            'store_id'    => self::get_config('merchant_id'),
            'txntimedate' => $datetime['datetime'],
            'chargetotal' => "$chargetotal",
            'currency'    => "$currency",
            'secret'      => self::get_config('merchant_secret')
        ];
        // Formula for hash
        $stringToHash = implode('', $hash_components);
        $ascii = bin2hex($stringToHash);

        return hash("sha256", $ascii);
    }


    /**
     * Verify the response hash
     * sharedsecret + approval_code + chargetotal + currency + txndatetime + storename
     *
     * @return bool
     *
     */
    function verifyHash() {

        if (!count($_POST)) {
            throw new Exception("Missing POST Data");
        }

        if (isset($_REQUEST['response_hash'])
            && isset($_REQUEST['approval_code'])
            && isset($_REQUEST['oid'])
            && isset($_REQUEST['invoicenumber'])
            && isset($_REQUEST['hash_algorithm'])) {

            $return_hash = stripinput($_REQUEST['response_hash']);

            $approval_code = stripinput($_REQUEST['approval_code']);

            $this->response_ref = stripinput($_REQUEST['oid']);

            $this->invoice_number = stripinput($_REQUEST['invoicenumber']);

            $algo = stripinput($_REQUEST['hash_algorithm']);

            $result = dbquery(
                "SELECT transaction_amount, transaction_currency, transaction_datestamp
            FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_ref=:ref AND transaction_number=:inv", [
                    ':ref' => $this->response_ref,
                    ':inv' => $this->invoice_number
                ]
            );

            if (dbrows($result)) {

                $data = dbarray($result);

                //print_P($data['transaction_datestamp']);

                $datetime = $this->get_current_datetime($data['transaction_datestamp']);

                // sharedsecret + apporval_code + chargetotal + currency number + txndatetime + storename
                $hash_components = [
                    'sharedsecret'  => self::get_config('merchant_secret'),
                    'approval_code' => $approval_code,
                    'chargetotal'   => number_format($data['transaction_amount'], 2),
                    'currency'      => $this->get_transaction_currency($data['transaction_currency']),
                    'txndatetime'   => $datetime['datetime'],
                    'store_id'      => self::get_config('merchant_id')
                ];


                // Formula for hash
                $stringToHash = implode('', $hash_components);
                $stringToHash = bin2hex($stringToHash);
                $hash = hash($algo, $stringToHash);

                //print_P($_REQUEST);
                //print_p($hash_components);
                //print_P($hash);
                //print_P($return_hash);

                if ($hash === $return_hash) {
                    return TRUE;
                }

            }

        }

        return FALSE;
    }

    private $response_ref = '';
    private $invoice_number = '';

    /**
     * Displays a cc form
     *
     * @param $options
     *
     * @return string
     * @throws \ReflectionException
     */
    public function form($options) {

        $tpl = '';
        $fail_title = post('fail_reason');
        $fail_reason = post('fail_reason_details');

        if ($fail_title && $fail_reason) {
            $tpl .= "<div class='alert alert-warning'><strong>Credit card could not be processed</strong><br/>$fail_title - $fail_reason \n</div>";
        }

        $settings = fusion_get_settings();

        $config = $this->get_config();
        $options += Wallet::get_driver_default_options();

        $data['payment_id'] = $this->wallet->get_PaymentID($options);

        //$action_url = fusion_get_settings('site_path').'infusions/wallet/checkout.php?payment_method=firstdata&payment_id='.$data['payment_id'];
        ///infusions/wallet/drivers/firstdata/payments.php
        $payment_url = fusion_get_settings('site_path').'infusions/wallet/drivers/firstdata/payments.php';

        //$checkout_url  = fusion_get_settings('site_path').'infusions/wallet/checkout.json.php'; // this file doesn't have any field sanitization.

        $tpl .= openform('firstdataPaymentFrm', 'post', $payment_url);
        $tpl .= form_hidden('origin_url', 'Origin URL', $config['base_url'].$_SERVER['REQUEST_URI'], ['input_id' => 'origin_url_fdata']);
        $tpl .= form_hidden('callback_url', 'Callback URL', '', ['input_id' => 'callback_url_fdata']);
        $tpl .= form_hidden('return_url', 'Return URL', $options['return_url'], ['input_id' => 'return_url_fdata']);
        $tpl .= form_hidden('order_payment_type', '', 'firstdata', ['input_id' => 'payment_type_fdata']);
        $tpl .= form_hidden('order_payment_method', '', 'firstdata', ['input_id' => 'payment_method_fdata']);
        $tpl .= form_hidden('order_payment_currency', '', $options['order_currency'], ['input_id' => 'payment_currency_fdata']);

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

                $tpl .= form_hidden('order_item_id[]', '', $item['id'], ['input_id'=>'fd_oid_'.$item_id]);
                $tpl .= form_hidden('order_item_type[]', '', $item['type'], ['input_id'=>'fd_type_'.$item_id]);
                $tpl .= form_hidden('order_title[]', '', strip_tags($item['title']), ['input_id'=>'fd_title_'.$item_id]);
                $tpl .= form_hidden('order_description[]', '', strip_tags($item['description']), ['input_id'=>'fd_desc_'.$item_id]);
                $tpl .= form_hidden('order_tax[]', '', $item['tax'], ['input_id'=>'fd_tax_'.$item_id]);
                $tpl .= form_hidden('order_shipping[]', '', $item['shipping'], ['input_id'=>'fd_shipping_'.$item_id]);
                $tpl .= form_hidden('order_quantity[]', '', $item['quantity'], ['input_id'=>'fd_qty_'.$item_id]);
                $tpl .= form_hidden('order_amount[]', '', $item['price'], ['input_id'=>'fd_price_'.$item_id]);
                $tpl .= form_hidden('order_currency[]', '', $item['currency'], ['input_id'=>'fd_currency_'.$item_id]);
                $tpl .= form_hidden('order_options[]', '', $item['options'], ['input_id'=>'fd_options_'.$item_id]);
                $tpl .= form_hidden('order_info[]', '', $item['info'], ['input_id'=>'fd_info_'.$item_id]);
            }
        }

        $helper = new Wallet_Fields_Helper($this->wallet);
        $tpl .= "<hr/>";
        $tpl .= $helper->show_ccInputForm();
        $tpl .= form_button(
            'pay_FirstData', 'Pay with Credit Card', 'pay_FirstData',
            ['class' => 'btn-success btn-pop small text-expanded btn-md btn-block']
        );
        $tpl .= closeform();
        $tpl .= "<div id='firstdata-form-container'></div>";
        $tpl .= "<script src='".WALLET."drivers/firstdata/wwpf.js'></script>";

        $fusion_token = fusion_get_token($this->firstdata_token, 1);

        add_to_jquery(
            "wwpf.init('".$data['payment_id']."', '".$options['display_amount_field']."', '".$options['order_amount']."', '$this->firstdata_token', '$fusion_token');"
        );

        return $tpl;
    }

}
require_once (INFUSIONS.'wallet/classes/wallet.php');
