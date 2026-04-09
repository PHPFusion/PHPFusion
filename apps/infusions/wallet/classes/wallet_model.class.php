<?php

namespace PHPFusion\Infusions\Wallet\Classes;

use Exception;

/**
 * Class Wallet_Model
 *
 * @package PHPFusion\Infusions\Wallet\Classes
 */
class Wallet_Model {

    protected static $section = [];
    protected static $page = [];
    protected static $settings = [];
    /**
     * @param $user_id
     *
     * @return object
     * @throws Exception
     */
    protected static $user_id = 0; // user wallet data
    protected static $wallet = [];
    private static $instance = NULL;
    public $pocket = [];

    private $membership_opts = [
        1 => 'Personal/Individual Account',
        2 => 'Enterprise/Government (including enterprises, governments, public institutions, groups and other organizations)',
    ];
    private $membership_short_opts = [
        1 => 'Individual',
        2 => 'Corporate'
    ];
    private $company_period_opts = [
        1  => "Less than 3 years",
        3  => "3 to 5 years",
        5  => "5 to 10 years",
        10 => "More than 10 years"
    ];
    private $company_employees = [
        1   => "Less than 100 employees",
        100 => "100 to 300 employees",
        300 => "300 to 500 employees",
        500 => "More than 500 employees"
    ];
    private $membership_status_opts = [
        0 => 'Unverified',
        1 => 'Verified',
        2 => 'Pending Verification'
    ];
    private $validate_code = [
        1 => "The Document fails to proof verification information",
        2 => "The Document is not relevant",
        3 => "Failed to attach or view the document.",
    ];

    private $company_industry =
        ['web'          => "Websites",
         'apps'         => "Mobile Apps",
         'it'           => "IT and Software Development",
         'news'         => "News Media & Portal",
         'social'       => "Social Communications",
         'ecom'         => "E-commerce",
         'gaming'       => "Gaming",
         'av'           => "Audio/Visual",
         'finance'      => "Finance",
         'edu'          => "Education",
         'health'       => "Healthcare",
         'tourism'      => "Tourism",
         'internet'     => "Internet of Things",
         'automotive'   => "Automotive",
         'o2o'          => "Online to Offline (o2o)",
         "electric"     => "Electrical Power/Renewable",
         'transport'    => "Transportation",
         'manufacture'  => "Manufacturing",
         'construction' => "Construction/Real Estate",
         'govt'         => "Government/Institutions",
         'genomics'     => "Genomics",
         "logistics"    => "Logistics",
         "tobacco"      => "Tobacco Industry",
         "isp"          => "IDC/ISP",
         "energy"       => "Energy/Heavy Industry",
         "public"       => "Public Services/Urban Services",
         "others"       => "Others"
        ];

    private $default_options = [
        // driver options
        'PaymentID'              => '',
        'PaymentMethod'          => '',
        'PaymentDesc'            => '',
        // user options
        'order_payment_method'   => '',
        'order_payment_type'     => '',
        'order_payment_currency' => 'USD',
        // system options
        'return_url'             => '',
        'user_id'                => '',
    ];

    public static function walletSettings($key = NULL) {
        if (empty(self::$settings)) {
            self::$settings = get_settings('wallet');
        }
        return $key === NULL ? self::$settings : (isset(self::$settings[$key]) ? self::$settings[$key] : NULL);
    }

    public static function display_date($datestamp) {
        return date('d.M.Y', $datestamp).' - '.timer($datestamp);
    }

    /**
     * Automatically create a new wallet account for the user
     *
     * @param $user_id
     *
     * @return int
     */
    public static function createWalletAccount($user_id) {
        if (!dbcount('(user_id)', DB_USER_WALLET, 'user_id=:uid', [':uid' => $user_id])) {
            $blank_walet_data = [
                'wallet_id'  => 0,
                'user_id'    => $user_id,
                'lastupdate' => TIME
            ];
            return dbquery_insert(DB_USER_WALLET, $blank_walet_data, 'save', ['keep_session' => TRUE]);
        }

        return 0;
    }

    public static function send_message($user_id, $pm_subject, $pm_message, $bot_id = 15756) {
        dbquery("INSERT INTO ".DB_MESSAGES." (message_id, message_to, message_from, message_user, message_subject, message_message,
        message_smileys, message_read, message_datestamp, message_folder) VALUES ('', '$user_id', '$bot_id','$user_id', '$pm_subject', '$pm_message', 'n', '0', '".TIME."', '0')");
    }

    /*
     * Returns a standard date time for the entire wallet
     */

    public static function get_driver_default_options() {

        $baseurl = ((isset($_SERVER['HTTPS'])) ? "https" : "http")."://".$_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT'] != 80 ? ":".$_SERVER['SERVER_PORT'] : '');

        $default_options = [
            'debug'             => FALSE,
            'order_item_id'     => 0, // this will be eGHL's PaymentID
            'order_item_type'   => '',
            'order_title'       => '',
            'order_description' => '',
            'order_quantity'    => 1,
            'order_amount'      => 0,
            'order_currency'    => '',
            'order_tax'         => 0,
            'order_shipping'    => 0,
            'currency'          => 'USD',
            "callback_url"      => $baseurl.$_SERVER['REQUEST_URI'], // call to validate payments done
            "return_url"        => $baseurl.$_SERVER['REQUEST_URI'], // redirect back to the same page after finish
        ];

        return (array)$default_options;
    }

    //protected static function credit_checkout($options): string {
    //
    //    $settings = fusion_get_settings();
    //
    //    $tpl = Template::getInstance('checkout_credit');
    //    $tpl->set_template(__DIR__.'/../templates/payment/checkout.html');
    //    $tpl->set_file([IMAGES]);
    //
    //    add_breadcrumb([
    //        'link'  => BASEDIR.$settings['opening_page'],
    //        'title' => $settings['sitename'],
    //    ]);
    //    add_breadcrumb([
    //        'link'  => clean_request('', ['error'], FALSE),   // top up link.
    //        'title' => 'PHP-Fusion Coins Credit Checkout'
    //    ]);
    //    $tpl->set_tag('sitename', $settings['sitename']);
    //    $tpl->set_tag('sitebanner', $settings['site_path'].$settings['sitebanner']);
    //    $tpl->set_tag('breadcrumb', render_breadcrumbs());
    //    $tpl->set_tag('siteurl', BASEDIR.$settings['opening_page']);
    //
    //    $payment_image = WALLET."images/credits.svg";
    //
    //    $tpl->set_block('payment_method', [
    //        'payment_title' => "<strong><i style=\"color:#193881;\">Coins</i> ",
    //        'payment_image' => "<img style='width:50px; position: relative; margin-top:20px;' src='$payment_image'/>",
    //    ]);
    //
    //    $tpl->set_block('payment_message', ['text' => "PHP-Fusion Credit Checkout"]);
    //
    //    // Defaults
    //    $tpl->set_tag('payment_id', 'Invalid Payment ID');
    //    $tpl->set_tag('order_subtotal', number_format(0, 2));
    //    $tpl->set_tag('order_total_shipping', number_format(0, 2));
    //    $tpl->set_tag('order_total_tax', number_format(0, 2));
    //    $tpl->set_tag('order_total', number_format(0, 2));
    //    $tpl->set_tag('card_ending', "No Description");
    //    $tpl->set_tag('payment_type', "Invalid");
    //
    //    $tpl->set_tag("paypal_openform", "");
    //    $tpl->set_tag('paypal_fields', "");
    //    $tpl->set_tag("paypal_button_class", " disabled");
    //    $tpl->set_tag("paypal_closeform", "");
    //    $tpl->set_tag('card_image', $payment_image);
    //
    //    //print_P($_POST);
    //    // Posting for Credit Payment.
    //    $_payment = stripinput(get('payment_id'));
    //    if ($_payment && fusion_safe()) {
    //        // the payment id is a random number generated.
    //        $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_ref=:payment_id AND transaction_method='credit'", [
    //            ':payment_id' => $_payment,
    //        ]);
    //        if (dbrows($result)) {
    //
    //            $data = dbarray($result);
    //            $tpl->set_tag('currency', $data['transaction_currency']);
    //            $tpl->set_tag('payment_id', $_payment);
    //            $tpl->set_tag('order_subtotal', number_format($data['transaction_item_total'], 2));
    //            $tpl->set_tag('order_total_shipping', number_format($data['transaction_shipping'], 2));
    //            $tpl->set_tag('order_total_tax', number_format($data['transaction_tax'], 2));
    //            $tpl->set_tag('order_total', number_format($data['transaction_amount'], 2));
    //            $tpl->set_tag('card_ending', $data['transaction_description']);
    //            $tpl->set_tag('card_image', WALLET.'images/credits.svg');
    //            $tpl->set_tag('payment_type', $data['transaction_type']);
    //            /**
    //             * Order Details
    //             */
    //            $cresult = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_ref=:thash", [':thash' => $data['transaction_number']]);
    //            if ($rowCount = dbrows($cresult)) {
    //                $data['item'] = [];
    //                while ($rows = dbarray($cresult)) {
    //                    $current_item_id = $rows['order_item_type'].'-'.$rows['order_item_id'];
    //                    $data['item'][$current_item_id] = [
    //                            'order_title'         => $rows['order_title'],
    //                            'order_description'   => nl2br($rows['order_description']),
    //                            'order_item_quantity' => number_format($rows['order_item_quantity'], 2),
    //                            'order_item_id'       => $rows['order_item_type'].'-'.$rows['order_item_id'],
    //                            'order_item_value'    => number_format($rows['order_item_value'], 2),
    //                        ] + $rows;
    //                }
    //
    //                /**
    //                 * Credit Payment Processing
    //                 */
    //                $wallet = Wallet::getInstance()->getUserWallet(fusion_get_userdata('user_id'));
    //
    //                if (post('credit_payment')) {
    //                    // Check if sufficient funds
    //                    if ($wallet['balance'] >= $data['transaction_amount'] && !empty($data['item'])) {
    //
    //                        $wallet['balance'] = $wallet['balance'] - $data['transaction_amount'];
    //                        foreach ($data['item'] as $item_id => $item) {
    //                            // Add a coin transactions
    //                            $coin_transactions = [
    //                                'ct_id'                  => 0,
    //                                'ct_wallet_id'           => $wallet['wallet_id'],
    //                                'ct_user'                => $wallet['user_id'],
    //                                'ct_ref'                 => $data['transaction_ref'],
    //                                'ct_number'              => $data['transaction_number'],
    //                                'ct_order_id'            => $item['order_id'],
    //                                'ct_datestamp'           => TIME,
    //                                'ct_title'               => $item['order_title'],
    //                                'ct_description'         => $item['order_description'],
    //                                'ct_paid'                => 1,
    //                                'ct_paid_datestamp'      => TIME,
    //                                'ct_completed'           => 1,
    //                                'ct_completed_datestamp' => TIME,
    //                                'ct_item_id'             => $item_id,
    //                                'ct_item_type'           => $item['order_item_type'],
    //                                'ct_item_value'          => $item['order_item_value'],
    //                                'ct_item_quantity'       => $item['order_item_quantity'],
    //                                'ct_item_tangible'       => $item['order_item_tangible'],
    //                                'ct_total_shipping'      => $item['order_total_shipping'],
    //                                'ct_total_tax'           => $item['order_total_tax'],
    //                                'ct_item_taxable'        => $item['order_item_taxable'],
    //                                'ct_item_tax_rate'       => $item['order_item_tax_rate'],
    //                                'ct_total_in'            => 0,
    //                                'ct_total_out'           => $item['order_total'],
    //                            ];
    //                            dbquery_insert(DB_COIN_TRANSACTIONS, $coin_transactions, 'save', ['keep_session' => TRUE]);
    //                        }
    //                        dbquery_insert(DB_USER_WALLET, $wallet, 'update');
    //
    //                        echo openform('validatedForm', 'post', WALLET.'confirmation.php', ['remote_url' => WALLET.'confirmation.php']);
    //                        echo form_hidden('transaction_ref', '', $data['transaction_ref']);
    //                        echo form_hidden('order_id', '', $data['transaction_number']);
    //                        echo form_hidden('payment_method', '', 'credit');
    //                        echo form_hidden('payment_date', '', showdate('longdate', TIME));
    //                        echo closeform();
    //                        echo "<script>$('#validatedForm').submit(); </script>\n";
    //                    } else {
    //                        add_notice('danger', 'Transactions cannot be processed due to insufficient credit funds. Please top up your balance.');
    //                    }
    //                }
    //
    //                if ($rowCount > 1) {
    //                    foreach ($data['item'] as $item_id => $item_data) {
    //                        $tpl->set_block("single_item_block", $item_data);
    //                    }
    //                } else {
    //                    $item_data = array_values($data['item']);
    //                    if (!empty($item_data)) {
    //                        $tpl->set_block("single_item_block", $item_data[0]);
    //                    }
    //                }
    //
    //                if (iMEMBER) {
    //
    //                    $tpl->set_block('member_field_block', Wallet::info_hero_form());
    //
    //                } else {
    //
    //                    $tpl->set_block('non_member_field_block', [
    //                        'link'  => BASEDIR.'register.php',
    //                        'title' => 'Create a PHP-Fusion Account',
    //                    ]);
    //                }
    //
    //                $fields = [
    //                    'origin_url'   => form_hidden('origin_url', '', ''), // @todo: origin url detection
    //                    'payment_id'   => form_hidden('PaymentID', '', $_payment),
    //                    'wallet_id'    => form_hidden('wallet_id', '', $options['user']['wallet_id']),
    //                    'post_payment' => form_hidden('credit_payment', '', 'credit_payment'),
    //                ];
    //
    //                $credit_fields = implode(PHP_EOL, $fields);
    //                $tpl->set_block('form', ['content' => openform('creditPaymentFrm', 'post', FUSION_REQUEST).$credit_fields.closeform()]);
    //                add_to_jquery("$('button#pay').bind('click', function(e) { $('#creditPaymentFrm').submit(); });");
    //
    //            } else {
    //
    //                $tpl->set_block('alerts', ['alert' => alert(
    //                    "<strong><i class='fas fa-exclamation-triangle m-r-10'></i>No Items Found.</strong>"
    //                    , [
    //                        'class' => 'alert-danger',
    //                    ]
    //                )
    //                ]);
    //
    //            }
    //        } else {
    //            $tpl->set_block('alerts', ['alert' => alert(
    //                "<strong><i class='fas fa-exclamation-triangle m-r-10'></i>No Transactions Found.</strong>"
    //                , [
    //                    'class' => 'alert-danger',
    //                ]
    //            )
    //            ]);
    //        }
    //    } else {
    //        // Log him as danger person.
    //        $tpl->set_block('alerts', ['alert' => alert(
    //            "<strong><i class='fas fa-exclamation-triangle m-r-10'></i>Invalid Checkout Request.</strong> Please contact system administrator to assist you on this matter."
    //            , [
    //                'class' => 'alert-danger',
    //            ]
    //        )
    //        ]);
    //    }
    //
    //    $notices = getNotices(['all', FUSION_SELF]);
    //    if (!empty($notices)) {
    //        $tpl->set_block('notices', ['content' => renderNotices($notices)]);
    //    }
    //
    //    return (string)$tpl->get_output();
    //}

    /**
     * Fetch a user wallet information
     *
     * @param int  $user_id
     * @param bool $fetch
     *
     * @return array
     */
    public function getUserWallet($user_id, $fetch = FALSE) {

        self::$user_id = $user_id;

        if (empty(self::$wallet[$user_id]) || (!empty(self::$wallet[$user_id]) && $fetch === TRUE)) {

            self::$wallet[$user_id] = (array)self::getEmptyWalletInfo();

            $wallet_result = dbquery("SELECT * FROM ".DB_USER_WALLET." WHERE user_id=:uid", [':uid' => (int)$user_id]);

            if (dbrows($wallet_result)) {

                $wallet_data = dbarray($wallet_result);

                if ($user_data = fusion_get_user($user_id)) {

                    $wallet_data += $user_data + [
                        "street"        => "",
                        "street2"       => "",
                        "county"        => "",
                        "region"        => "",
                        "city"          => "",
                        "postcode"      => "",
                        "phone_prefix"  => "",
                        "phone"         => "",
                        "fax_prefix"    => "",
                        "fax"           => "",
                        "mobile_prefix" => "",
                        "mobile"        => ""
                    ];

                    if (!empty($user_data["user_address"])) {
                        list(
                            $wallet_data["street"],
                            $wallet_data["street2"],
                            $wallet_data["country"],
                            $wallet_data["region"],
                            $wallet_data["city"],
                            $wallet_data["postcode"]
                            ) = explode("|", $user_data["user_address"]);
                    }


                    if (!empty($user_data["user_fax"])) {
                        list($wallet_data["phone_prefix"], $wallet_data["phone"]) = explode("|", $user_data["user_phone"]);
                        if ($wallet_data["phone_prefix"]) {
                            $wallet_data["phone_prefix"] = display_contact_prefix($wallet_data["phone_prefix"]);
                        }
                    }

                    if (!empty($user_data["user_fax"])) {
                        list($wallet_data["fax_prefix"], $wallet_data["fax"]) = explode("|", $user_data["user_fax"]);
                        if ($wallet_data["fax_prefix"]) {
                            $wallet_data["fax_prefix"] = display_contact_prefix($wallet_data["fax_prefix"]);
                        }
                    }

                    if (!empty($user_data["user_mobile"])) {
                        list($wallet_data["mobile_prefix"], $wallet_data["mobile"]) = explode("|", $user_data["user_mobile"]);
                        if ($wallet_data["mobile_prefix"]) {
                            $wallet_data["mobile_prefix"] = display_contact_prefix($wallet_data["mobile_prefix"]);
                        }
                    }

                }

                self::$wallet[$user_id] = (array)$wallet_data;
            }
        }

        return (array)self::$wallet[$user_id];
    }

    /**
     * Blank Wallet Information
     *
     * @return array
     */
    private static function getEmptyWalletInfo() {
        return [
            'wallet_id'          => 0,
            "wallet_status"      => 0,
            'user_id'            => 0,
            'gold_balance'       => 0,
            'diamond_balance'    => 0,
            "verified"           => 0,
            "user_firstname"     => "",
            "user_lastname"      => "",
            "user_phone_prefix"  => "",
            "user_phone"         => "",
            "user_mobile_prefix" => "",
            "user_mobile"        => "",
            "user_fax_prefix"    => "",
            "user_fax"           => "",
            "user_street"        => "",
            "user_street2"       => "",
            "user_country"       => "",
            "user_region"        => "",
            "user_city"          => "",
            "user_postcode"      => ""
        ];
    }

    public function get_company_industry($value = NULL) {
        if ($value) {
            return $this->company_industry[$value];
        }

        return $this->company_industry;
    }

    public function get_company_employees($value = NULL) {
        if ($value) {
            return $this->company_employees[$value];
        }

        return $this->company_employees;
    }

    public function get_company_period($value = NULL) {
        if ($value) {
            return $this->company_period_opts[$value];
        }

        return $this->company_period_opts;
    }

    public function get_fusion_membership($value = NULL, $short = TRUE) {
        if ($value) {
            return ($short ? $this->membership_short_opts[$value] : $this->membership_opts[$value]);
        }

        return ($short ? $this->membership_short_opts : $this->membership_opts);
    }

    public function get_membership_status($value = NULL) {
        if ($value) {
            return $this->membership_status_opts[$value];
        }

        return $this->membership_status_opts;
    }

    // Generate Transaction Ref

    /**
     * Insert transaction and orders during payment confirmation
     * Source of $_POST from relevant drivers.
     *
     * @param array $config
     *
     * @return array
     */
    public function createTransactionfromPost($config = []) {

        $wallet = Wallet::getInstance();
        // Uncomment to debug
        //return array(
        //    'request' => $_POST,
        //    'status' => '',
        //    'response' => FALSE
        //);

        if (fusion_safe()) {
            // regardless of what payment id will exist.
            $transaction_reference = sanitizer("PaymentID");
            $transaction_type = sanitizer("order_payment_type");
            $transaction_method = sanitizer("order_payment_method");
            $transaction_title = "Payment via ".sanitizer("PaymentMethod"); //stripinput($_POST['CardType']), // there are many items, then this title is not suitable.
            $transaction_description = sanitizer("PaymentDesc"); //'Credit Card - '.stripinput($_POST['CardType']).' - ****'.substr($_POST['CardNo'], -4),
            $transaction_file = urlencode(sanitizer("return_url"));
            $transaction_currency = sanitizer("order_payment_currency");
            $userdata = fusion_get_userdata();
            $user_id = $userdata["user_id"];

            if (!empty($config)) {

                $default_config = [
                    // driver options
                    'PaymentID'              => '',
                    'PaymentMethod'          => '',
                    'PaymentDesc'            => '',
                    // user options
                    'order_payment_method'   => '',
                    'order_payment_type'     => '',
                    'order_payment_currency' => 'USD',
                    // system options
                    'return_url'             => '',
                    'user_id'                => '',
                ];

                $config += $default_config;

                $transaction_reference = $config['PaymentID'];
                $transaction_type = $config['order_payment_type'];
                $transaction_method = $config['order_payment_method'];
                $transaction_title = "Payment via ".$config['PaymentMethod'];
                $transaction_description = $config['PaymentDesc'];
                $transaction_file = ltrim(urlencode($config['return_url']), '/');
                $transaction_currency = $config['order_payment_currency'];
                $user_id = $config['user_id'];
            }

            if ($transaction_id = dbresult(dbquery("SELECT transaction_id FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_ref=:ref", [':ref' => $transaction_reference]), 0)) {

                $tdata['transaction_id'] = $transaction_id;
                $tdata['transaction_method'] = $transaction_method;
                $tdata['transaction_title'] = $transaction_title;
                $tdata['transaction_description'] = $transaction_description;
                $tdata['transaction_file'] = $transaction_file;

                dbquery_insert(DB_WALLET_TRANSACTIONS, $tdata, 'update', ['keep_session' => TRUE]);

                return [
                    'status'   => 'OK',
                    'tdata'    => $tdata,
                    'data'     => 'Existing transaction and order bill found. Skipping generating new orders.',
                    'response' => $transaction_reference
                ];

            }

            $total_amount = 0;
            $total_item = 0;
            $total_tax = 0;
            $total_shipping = 0;
            $transaction_status = '0'; // 0 is pending, 1 is success, 2 is pending, 3 is fail.
            // The transaction_number
            $order_reference = $wallet->get_RandomString();
            $oids = post(['order_item_id']);

            if (!empty($oids)) {
                // @todo: do a currency check of the current driver.
                //$accepted_currency = $transaction_currency;
                // @todo: do a taxation global settings
                //$wallet_settings = $wallet::walletSettings();
                //$order_item_taxable  = $wallet_settings['']
                if (is_array($oids)) {
                    // Multiple Items Single Transaction
                    $order_ids = [];

                    foreach ($oids as $i => $item_id) {

                        $order_title = sanitizer(['order_title', $i], '');
                        $order_description = sanitizer(['order_description', $i], '');

                        // Custom amount
                        $price = sanitizer(["order_amount", $i]);
                        if ($price == "custom") {
                            $price = sanitizer("wallet_custom_price", "", "wallet_custom_price");
                        }
                        $order_amount = floatval($price);

                        $order_quantity = sanitizer(['order_quantity', $i], 0);
                        $order_item_id = sanitizer(['order_item_id', $i], '');
                        $order_item_type = sanitizer(['order_item_type', $i], '');
                        $order_currency = sanitizer(['order_currency', $i], ""); //stripinput($_POST['order_currency'][$i]),
                        $order_shipping_cost = floatval(sanitizer(['order_shipping', $i], 0));
                        $order_shipping_tax = floatval(sanitizer(['order_shipping_tax', $i], 0));
                        $order_info = sanitizer(['order_info', $i], "");
                        $order_item_interval = sanitizer(["order_interval", $i], "");
                        $order_item_cycle = sanitizer(["order_cycle", $i], "");

                        $order_total_shipping = 0;
                        if ($order_shipping_cost && $order_shipping_tax) {
                            $order_total_shipping = $order_shipping_cost * $order_shipping_tax;
                        }

                        $order_total_shipping = $order_total_shipping ? $order_total_shipping / 100 + $order_shipping_cost : 0;
                        $item_total = $order_amount * $order_quantity;
                        $order_item_tax = sanitizer(['order_tax', $i], '');
                        $order_item_taxable = $order_item_tax ? 'Y' : 'N';
                        $order_total_tax = $order_item_taxable == 'Y' && $order_item_tax ? ($item_total * $order_item_tax) / 100 : 0;
                        $order_total = $item_total + $order_total_shipping + $order_total_tax;

                        $data = [
                            'order_id'             => 0,
                            'order_ref'            => $order_reference,
                            'order_tid'            => 0, ## need to update this.
                            'order_user'           => $user_id,
                            'order_title'          => $order_title,
                            'order_description'    => $order_description,
                            'order_datestamp'      => TIME,
                            'order_item_id'        => $order_item_id,
                            'order_item_type'      => $order_item_type,
                            'order_item_value'     => $order_amount,
                            'order_item_quantity'  => $order_quantity,
                            'order_item_tangible'  => 'N',
                            'order_shipping'       => $order_shipping_cost, //stripinput($_POST['order_shipping'][$i]), // in decimal system only
                            'order_shiping_tax'    => $order_shipping_tax,
                            'order_total_shipping' => $order_total_shipping,
                            'order_item_taxable'   => $order_item_taxable,
                            'order_item_tax_rate'  => $order_item_tax,
                            'order_tax_total'      => $order_total_tax,
                            'order_total'          => $order_total,
                            'order_currency'       => $order_currency,
                            'order_info'           => $order_info,
                            "order_item_interval"  => $order_item_interval,
                            "order_item_cycle"     => $order_item_cycle
                        ];

                        $currency[] = $data['order_currency'];

                        if (empty($data['order_user'])) {

                            return ['status' => 'Invalid User ID', 'response' => FALSE];

                        } else if (!$data['order_title'] || !$data['order_item_id'] || !$data['order_item_type'] || !$data['order_item_quantity']) {

                            return ['status' => 'Invalid product item_id, title, type and quantity', 'response' => FALSE, 'data' => $data, 'request' => $_REQUEST];

                        } else if (fusion_safe()) {
                            // Save current order
                            $order_ids[] = dbquery_insert(DB_WALLET_ORDERS, $data, 'save', ['keep_session' => TRUE]);
                            // Transaction total amount
                            // @todo: tax system
                            $current_tax = $data['order_item_taxable'] == 'Y' ? ($data['order_item_value'] * $data['order_item_quantity'] * $data['order_item_tax_rate']) : 0;
                            // @todo: shipping system
                            $current_shipping = 0; //$data['order_item_tangible'] == 'Y' ? 0 : 0;
                            $item_value = $data['order_item_value'] * $data['order_item_quantity'];
                            $total_tax = $total_tax + $current_tax;
                            $total_shipping = $total_shipping + $current_shipping;
                            $total_item = $total_item + $order_total;
                            // out of loop
                            $total_amount = $total_amount + $item_value + $total_tax + $total_shipping;
                        }
                    }

                    $transaction_data = [
                        'transaction_id'          => 0,
                        'transaction_ref'         => $transaction_reference,
                        'transaction_number'      => $order_reference,
                        'transaction_user'        => $user_id,
                        'transaction_title'       => $transaction_title,
                        'transaction_description' => $transaction_description,
                        'transaction_amount'      => $total_amount,
                        'transaction_item_total'  => $total_item,
                        'transaction_shipping'    => $total_shipping,
                        'transaction_tax'         => $total_tax,
                        'transaction_currency'    => $transaction_currency,
                        'transaction_type'        => $transaction_type,
                        'transaction_method'      => $transaction_method,
                        'transaction_oid'         => implode('.', $order_ids),
                        'transaction_ip'          => USER_IP,
                        'transaction_status'      => $transaction_status,
                        'transaction_datestamp'   => TIME,
                        'transaction_file'        => $transaction_file
                    ];

                    // Store transaction
                    $transaction_id = dbquery_insert(DB_WALLET_TRANSACTIONS, $transaction_data, 'save', ['keep_session' => TRUE]);
                    $sql_order_id = implode(',', $order_ids);
                    dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_tid=:tid WHERE order_id IN ($sql_order_id)", [':tid' => $transaction_id]);

                    return [
                        'status'   => 'OK',
                        'data'     => $sql_order_id,
                        'response' => $transaction_data['transaction_ref']
                    ];
                }

                return [
                    'status'   => 'Order Error: Order Items must be in array format',
                    'response' => FALSE
                ];

            } else {
                return ['status' => 'Order Error: Order is not set', 'response' => $_POST];
            }
        }

        return [
            'status'   => 'Order Error: Session unsafe',
            'response' => FALSE
        ];
    }

    // Generate Invoice number

    /**
     * @return static|null
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function get_RandomString($length = 6) {
        return (string)random_string($length);
    }

    public function createTransactionfromArray($options = []) {
        // Uncomment to debug
        /*return array(
            'request' => $_POST,
            'status' => '',
            'response' => FALSE
        );*/

        if (fusion_safe()) {
            $options += $this->default_options;
            $transaction_type = $options['order_payment_type'];
            $transaction_method = $options['order_payment_method'];
            $transaction_title = "Payment via ".$options['PaymentMethod'];
            $transaction_description = $options['PaymentDesc'];
            $transaction_file = urlencode($options['return_url']);
            $transaction_currency = $options['order_payment_currency'];
            $transaction_reference = $options['PaymentID'];
            $order_reference = $this->get_RandomString();
            $user_id = $options['user_id'];
            $order_items = $options["order_item_ids"];

            // Compulsory checks
            if (!$user_id) {
                $this->throwException("Invalid Transaction: User ID is required");
            }

            // Checks if transaction already exist
            if ($transaction_id = dbresult(dbquery("SELECT transaction_id FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_ref=:ref", [':ref' => $transaction_reference]), 0)) {

                $tdata['transaction_id'] = $transaction_id;
                $tdata['transaction_method'] = $transaction_method;
                $tdata['transaction_title'] = $transaction_title;
                $tdata['transaction_description'] = $transaction_description;
                if ($transaction_file) {
                    $tdata['transaction_file'] = $transaction_file;
                }
                dbquery_insert(DB_WALLET_TRANSACTIONS, $tdata, 'update', ['keep_session' => TRUE]);

                return [
                    'status'   => 'OK',
                    'tdata'    => $tdata,
                    'data'     => 'Existing transaction and order bill found. Skipping generating new orders.',
                    'response' => $transaction_reference
                ];
            }

            $total_amount = 0;
            $total_item = 0;
            $total_tax = 0;
            $total_shipping = 0;
            $transaction_status = '0'; // 0 is pending, 1 is success, 2 is pending, 3 is fail.

            if (!empty($order_items)) {
                // @todo: do a currency check of the current driver.
                //$accepted_currency = $transaction_currency;
                // @todo: do a taxation global settings
                //$wallet_settings = $wallet::walletSettings();
                //$order_item_taxable  = $wallet_settings['']
                if (is_array($order_items)) {

                    $array_length = count($order_items["order_title"]);
                    $order_refs = [];
                    $order_ids = [];
                    for ($i = 0; $i < $array_length; $i++) {
                        $order_title = $order_items["order_title"][$i];
                        $order_description = stripinput($order_items["order_description"][$i]);
                        $order_amount = floatval($order_items["order_amount"][$i]);
                        $order_quantity = stripinput($order_items["order_quantity"][$i]);
                        $order_item_id = stripinput($order_items["order_item_id"][$i]);
                        $order_item_type = stripinput($order_items["order_item_type"][$i]);
                        $order_currency = stripinput($order_items["order_currency"][$i]);

                        $order_shipping_cost = floatval($order_items["order_shipping"][$i]);
                        $order_shipping_tax = floatval($order_items["order_shipping_tax"][$i]);

                        $order_info = stripinput($order_items["order_info"][$i]);
                        $order_item_tax = stripinput($order_items["order_tax"][$i]);

                        $order_total_shipping = 0;

                        if ($order_shipping_cost && $order_shipping_tax) {
                            $order_total_shipping = $order_shipping_cost * $order_shipping_tax;
                        }

                        $order_total_shipping = $order_total_shipping ? $order_total_shipping / 100 + $order_shipping_cost : 0;
                        $item_total = $order_amount * $order_quantity;
                        $order_item_taxable = $order_item_tax ? 'Y' : 'N';
                        $order_total_tax = $order_item_taxable == 'Y' && $order_item_tax ? ($item_total * $order_item_tax) / 100 : 0;
                        $order_total = $item_total + $order_total_shipping + $order_total_tax;

                        $data = [
                            "order_id"             => 0,
                            "order_ref"            => $order_reference,
                            "order_tid"            => 0, ## need to update this.
                            "order_user"           => $user_id,
                            "order_title"          => $order_title,
                            "order_description"    => $order_description,
                            "order_datestamp"      => TIME,
                            "order_item_id"        => $order_item_id,
                            "order_item_type"      => $order_item_type,
                            "order_item_value"     => $order_amount,
                            "order_item_quantity"  => $order_quantity,
                            "order_item_tangible"  => "N",
                            "order_shipping"       => $order_shipping_cost, //stripinput($_POST["order_shipping"][$i]), // in decimal system only
                            "order_shiping_tax"    => $order_shipping_tax,
                            "order_total_shipping" => $order_total_shipping,
                            "order_item_taxable"   => $order_item_taxable,
                            "order_item_tax_rate"  => $order_item_tax,
                            "order_tax_total"      => $order_total_tax,
                            "order_total"          => $order_total,
                            "order_currency"       => $order_currency,
                            "order_info"           => $order_info,
                        ];

                        $currency[] = $data['order_currency'];

                        if (empty($data['order_user'])) {

                            $this->throwException("Invalid User ID");
                            die();

                        } else if (!$data['order_title'] || !$data['order_item_id'] || !$data['order_item_type'] || !$data['order_item_quantity']) {

                            $this->throwException("Invalid product item_id, title, type and quantity");
                            die();

                        } else {
                            // Save current order
                            $order_ids[] = dbquery_insert(DB_WALLET_ORDERS, $data, 'save', ['keep_session' => TRUE]);
                            $order_refs[] = $order_reference;
                            // Transaction total amount
                            // @todo: tax system
                            $current_tax = $data['order_item_taxable'] == 'Y' ? ($data['order_item_value'] * $data['order_item_quantity'] * $data['order_item_tax_rate']) : 0;
                            // @todo: shipping system
                            $current_shipping = ($data['order_item_tangible'] == 'Y' ? 0 : 0);
                            $item_value = $data['order_item_value'] * $data['order_item_quantity'];
                            $total_tax = $total_tax + $current_tax;
                            $total_shipping = $total_shipping + $current_shipping;
                            $total_item = $total_item + $order_total;
                            // out of loop
                            $total_amount = $total_amount + $item_value + $total_tax + $total_shipping;
                        }

                    }
                    // Multiple Items Single Transaction

                    $transaction_data = [
                        'transaction_id'          => 0,
                        'transaction_ref'         => $transaction_reference,
                        'transaction_number'      => $order_reference,
                        'transaction_user'        => $user_id,
                        'transaction_title'       => $transaction_title,
                        'transaction_description' => $transaction_description,
                        'transaction_amount'      => $total_amount,
                        'transaction_item_total'  => $total_item,
                        'transaction_shipping'    => $total_shipping,
                        'transaction_tax'         => $total_tax,
                        'transaction_currency'    => $transaction_currency,
                        'transaction_type'        => $transaction_type,
                        'transaction_method'      => $transaction_method,
                        'transaction_oid'         => implode('.', $order_ids),
                        'transaction_ip'          => USER_IP,
                        'transaction_status'      => $transaction_status,
                        'transaction_datestamp'   => TIME,
                        'transaction_file'        => $transaction_file
                    ];

                    // Store transaction
                    $transaction_id = dbquery_insert(DB_WALLET_TRANSACTIONS, $transaction_data, 'save', ['keep_session' => TRUE]);
                    $sql_order_id = implode(',', $order_ids);
                    dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_tid=:tid WHERE order_id IN ($sql_order_id)", [':tid' => $transaction_id]);

                    return [
                        'status'          => 'OK',
                        "transaction_id"  => $transaction_id,
                        "transaction_ref" => $transaction_data["transaction_ref"],
                        "order_refs"      => $order_refs,
                        'data'            => $sql_order_id,
                        'response'        => $transaction_data['transaction_ref']
                    ];

                } else {
                    $this->throwException("Order Items must be in Array Format");
                    die();
                }
            } else {
                $this->throwException("Order Error: Order is not Set");
                die();
            }
        }

        $this->throwException("Order Error: Transaction is invalid due to token errors");
        die();
    }

    private function throwException($exception) {
        setError(E_USER_NOTICE, $exception, "wallet-model.php", "0");
    }

    /**
     * Get transaction ref if exist or generate a new one.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function get_PaymentID($options = []) {
        static $transaction_ref = [];
        if (!empty($options['transaction_ref'])) {
            return $options['transaction_ref'];
        }
        if (empty($transaction_ref)) {
            $transaction_ref = $this->getTransactionReference();
        }
        return $transaction_ref;
    }

    public function getTransactionReference() {
        return (string)random_string(10).rand();
    }

    /**
     * Get the page that currently where display_wallet() is being used.
     *
     * @param bool $local_path TRUE for using sitepath for internal redirecting.
     *
     * @return string
     */
    function get_paymentRequestUrl($local_path = FALSE) {
        if ($local_path === TRUE) {
            //$httpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
            return (string)fusion_get_settings('site_path').ltrim(str_replace(fusion_get_settings('siteurl'), '', server("HTTP_REFERER")), '/');
        }
        return (string)server("HTTP_REFERER");
    }

    /*
     * Display credit checkout driver
     */

    /**
     * @param string $notice
     */
    protected function walletStop($notice = "") {
        if (!defined("WALLET_STOP")) {
            define("WALLET_STOP", TRUE);
            if ($notice) {
                add_notice("danger", $notice);
            }
        }
    }

    /*
     * Credit Driver
     */

    protected function get_validate_code($value = NULL) {
        if ($value) {
            return $this->validate_code[$value];
        }

        return $this->validate_code;
    }

    protected function setPage($page_title, $page_id, $icon = "") {
        self::$page['title'][] = $page_title;
        self::$page['id'][] = $page_id;
        self::$page['icon'][] = $icon;
    }

    protected function setSection($page_title, $page_id) {
        self::$section['title'][] = $page_title;
        self::$section['id'][] = $page_id;
    }

}
