<?php
/**
 * A wallet class for PHP-Fusion
 */

namespace PHPFusion\Infusions\Wallet\Classes;

use Exception;
use PHPFusion\Infusions\Wallet\Classes\Coins\Coins_Driver;

(defined('IN_FUSION') || exit);

class Wallet extends Wallet_Model {

    /**
     * This mode disabled IPN and only generate displays
     *
     * Main Script at confirmation.php
     *
     * Error Codes
     * 200 - Success
     * 201 - Fail Payment. Order will retained and can be paid via Console.
     * 202 - Internal System Failure - Paid but Not Yet Delivered (Delivery System Fails - Manual Delivery Required)
     * 404 - Transaction Fail (REQUEST/POST var corrupt) - Log system IP.
     *
     * @param bool $show_html
     *
     * @return string|void
     */
    public function displayConfirmation($show_html = FALSE) {

        // change to cron based delivery again for undelivered item.
        fusion_load_script(WALLET."wallet.js");
        fusion_load_script(WALLET."templates/css/wallet.css", "css");
        /**
         * All payments will return here.
         */
        $info = [];
        $gateway = new Gateways();

        if ($payment_method = get("payment_method")) {

            $wallet_settings = get_settings("wallet");

            $payment_driver = $gateway->getPaymentMethods($payment_method);

            try {
                if ($payment_method == "credit") {
                    $payment_method = "coins";
                }

                $function_name = (string)$payment_driver['callback_validate_function'];

                $validation_class = (object)$gateway->loadDriver($payment_method);

                $info = (array)$validation_class->$function_name(FALSE);

            } catch (Exception $e) {

                set_error(E_CORE_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
            }

            $store_address = [$wallet_settings["store_address"], $wallet_settings["store_address_2"], "<br/>".$wallet_settings["store_city"], $wallet_settings["store_region"], $wallet_settings["store_country"], "<br/>".$wallet_settings["store_postcode"]];

            if ($show_html) {

                $info["store"] = [
                    "name"    => $wallet_settings["store_name"],
                    "reg_no"  => $wallet_settings["store_registration_no"],
                    "fax"     => display_contact_prefix($wallet_settings["store_fax_cc"]).whitespace($wallet_settings["store_fax"]),
                    "phone"   => display_contact_prefix($wallet_settings["store_phone_cc"]).whitespace($wallet_settings["store_phone"]),
                    "email"   => "mailto:".$wallet_settings["store_email"],
                    "pm"      => BASEDIR."messages.php?send=1",
                    "address" => implode(",", $store_address),
                ];

                add_to_jquery(/** @lang JavaScript */ "
                $(document).on('click', '.print-invoice', function(e) {
                e.preventDefault();
                walletJs.printInvoice('invoice-print');
                });
                ");

                // wallet confirmation new template
                return fusion_render(__DIR__."/../templates/", "wallet-confirmation.twig", $info, TRUE);
            }

        }

    }

        /**
     * Displays a Payment Form
     *
     * How to pay again without creating new transaction/order
     * ----------------------------------------------------------
     * $config must put 'transaction_ref' number and then it will bypass.
     *   $config = [
     * 'transaction_ref' => $transaction['transaction_ref'], // this is the redirection id to reuse invoice
     * 'no_credits'        => FALSE,
     * 'return_url'        => fusion_get_settings('siteurl').'client_portal/domains/restoration-checkout.wallet.php'
     * ];
     *
     * @param array $options
     *
     * @return string
     */
    public function displayPaymentForm(array $options = []) {

        fusion_load_script(WALLET."templates/css/wallet.css", "css");
        //$coin_settings = get_settings('wallet');

        $default_options = [
            "transaction_ref"      => "",
            "return_url"           => "", // This is your delivery page when payment is success - e.g value : fusion_get_settings("siteurl")."infusions/roadmap/checkout.php";
            "display_amount_field" => FALSE,
            "display_amount"       => FALSE, // This displays the price tag
            "min_amount"           => 0,
            "min_error_message"    => "The minimum price is ",
            "max_amount"           => 0,
            "max_error_message"    => "The maximum price is ",
            "amount_label"         => "",
            "order_currency"       => "USD",
            "delimiter"            => 2,
            "label"                => TRUE,
            "reverse_display"      => FALSE,
            "no_credits"           => FALSE, // options for disabling credits driver from the payment form
            "credit_only"          => FALSE, // set to true if only coin payment options applicable.
            "items"                => [],
            /*
            observe the format as such for each item:
            $items = [
                    'id'                     => $addon_id,
                    'type'                   => 'ADDON',
                    'title'                  => "<a href='".$addon['item_link']."'>".$addon['title']."</a>",
                    'description'            => 'Single Site License Addon',
                    'price'                  => $addon['price'],
                    'tax'                    => 0,
                    'shipping'               => 0,
                    'quantity'               => 1,
                    'currency'               => $addon['currency'],
                ];
            */
            "callback_form"        => "",
            "callback_file"        => INFUSIONS."wallet/api/?api=checkout" // this one is wrong, need to change.
        ];

        $options += $default_options;

        $info = $this->getUserWallet(fusion_get_userdata('user_id'));

        $options['user'] = $info;

        // Fetch the amount of items, order title and order etc.
        $total_price = 0;
        $default['price'] = 0;

        foreach ($options['items'] as $item) {
            try {
                $item += $default;

                if ($item["price"] != "custom") {

                    $current_price = $item["price"] * $item["quantity"];

                    $total_price = $current_price + $total_price;

                }
            } catch (Exception $e) {

                set_error(E_CORE_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
            }
        }

        if (!empty($info['wallet_id'])) {

            /**
             * Display Custom Amount
             */
            $amount_field = '';
            $amount_display = '';

            if ($options['display_amount_field'] === TRUE) {
                $amount_field = form_text("wallet_custom_price", $options['amount_label'], '', [
                        'type'          => 'price',
                        'number_step'   => '1',
                        'required'      => TRUE,
                        'prepend'       => TRUE,
                        'placeholder'   => '0.00',
                        'class'         => 'flex flex-row',
                        "inner_width"   => "150px",
                        'prepend_value' => $options['order_currency']
                    ]);

                if ($options["min_amount"] || $options["max_amount"]) {

                    // These are not correct for global drivers
                    $js_script = "                    
                    disableWalletButtons = function() {
                        $('#paypal-form').find('button[type=\"submit\"]').prop('disabled', true);
                        $('#stripe-form').find('button[type=\"submit\"]').prop('disabled', true);
                        $('#credit-form').find('button[type=\"submit\"]').prop('disabled', true);
                    };
                    
                    enableWalletButtons = function() {
                        $('#paypal-form').find('button[type=\"submit\"]').prop('disabled', false);
                        $('#stripe-form').find('button[type=\"submit\"]').prop('disabled', false);
                        $('#credit-form').find('button[type=\"submit\"]').prop('disabled', false);
                    };
                
                    disableWalletButtons();
                    
                    let minAmountError = false, maxAmountError = false, amountDOM = $('#wallet_custom_price-field');
                    
                   $('#wallet_custom_price').bind('keyup paste', function(e) {                                                                                                        
                    ";

                    if ($options["min_amount"] && $options["min_error_message"]) {
                        $js_script .= "let wltMinWarningDOM = $('#amount-warning-min');
                        if ( $(this).val() < ".$options["min_amount"]." ) {
                            if (!wltMinWarningDOM.length) {
                                amountDOM.append('<span id=\"amount-warning-min\" class=\"amount-warning m-l-10\"><small class=\"text-danger\">".$options["min_error_message"]."$".$options["min_amount"]."</small></span>');
                                minAmountError = true;
                            }
                        } else {
                            wltMinWarningDOM.remove();
                            minAmountError = false;
                        }
                        ";
                    }
                    if ($options["max_amount"] && $options["max_error_message"]) {
                        $js_script .= "let wltMaxWarningDOM = $('#amount-warning-max');
                        if ( $(this).val() > ".$options["max_amount"]." ) {
                            if (!wltMaxWarningDOM.length) {
                                amountDOM.append('<span id=\"amount-warning-max\" class=\"amount-warning m-l-10\"><small class=\"text-danger\">".$options["max_error_message"]."$".$options["max_amount"]."</small></span>');
                                maxAmountError = true;
                            }
                        } else {
                            wltMaxWarningDOM.remove();
                            maxAmountError = false;
                        }
                        ";
                    }

                    $js_script .= "
                    if (minAmountError || maxAmountError) {
                        disableWalletButtons();
                    } else {
                        enableWalletButtons();
                    }                                            
                    });";

                    add_to_jquery($js_script);
                }
            }

            if ($options['display_amount'] === TRUE) {
                $amount_display = '<hr/><div class="flex flex-row"><h5><strong>Amount to be paid</strong></h5><h5 class="m-l-a">';

                if ($options['reverse_display']) {
                    $amount_display .= number_format($total_price, $options['delimiter'])." ".$options['order_currency'];
                } else {
                    $amount_display .= $options['order_currency']." ".number_format($total_price, $options['delimiter']);
                }

                if ($options['no_credits'] === FALSE) {
                    // we will calculate base value
                    $coin_value = ceil($total_price / 1); // 75 usd / 1 = 75 coins,
                    $amount_display .= " or $coin_value gold coins";
                }

                $amount_display .= "</h5></div>";
            }

            $gateway = new Gateways();
            $payment_opts = [];
            $forms = [];
            $gateway->setWalletInfo($info);
            $init_value = '';
            // select payment method.
            if ($drivers = $gateway->getPaymentMethods()) {

                foreach ($drivers as $driverName => $driver) {

                    if ($driverName === "credit") {
                        $driverName = "coins";
                    }

                    // options for disabling credits driver.
                    $show_driver = $options['no_credits'] === TRUE && $driverName == 'coins' ? FALSE : TRUE;
                    if ($options['credit_only']) {
                        $show_driver = $driverName === 'coins';
                    }

                    if ($show_driver) {

                        if (empty($init_value)) {
                            $init_value = $driverName;
                        }

                        $payment_opts[$driverName] = "<div class='clearfix wallet-opts'>
                        <div class='flex flex-row'>
                        <div class='text-left'><h4 class='m-0' style='line-height:1;'><strong>".$driver['title']."</strong></h4><span>".$driver['description']."</span></div>
                        <span class='m-l-a' style='width:35%;text-align:right;'>".$driver['pay_image']."</span>
                        </div></div>";

                        // load forms
                        if (isset($driver['callback_form_function'])) {
                            $form = $gateway->loadDriver($driverName);

                            $function_name = $driver['callback_form_function'];

                            if (method_exists($form, $function_name)) {
                                $forms[$driverName] = "<div id='$driverName-form' class='driversform' style='display:none;'>".$form->$function_name($options)."</div>";
                            }
                        }
                    }
                }
            }

            if (post('wallet_options')) {
                $init_value = sanitizer('wallet_options', '', 'wallet_options');
            }

            $header_content = "";
            if ($options["label"]) {
                $header_content .= '<h5 class="text-uppercase"><strong>Select your payment method</strong></h5>';
            }
            $header_content .= $amount_field.$amount_display;

            if ($header_content) {
                $header_content = '<div class="display-wallet-block" style="border-bottom:0;">'.$header_content.'</div>';
            }

            // Displays Wallet Form
            $html = openform('wallet_form', 'post').
                $header_content.
                '<div class="display-wallet-block">'
                .form_checkbox('wallet_options', '', $init_value, [
                    'options'  => $payment_opts,
                    'required' => TRUE,
                    'type'     => 'radio',
                    'class'    => 'wallet-block overflow-hide'
                ])
                .closeform()
                .implode('', $forms) // here we push a secret form out to checkout
                .'</div>';
            $html .= "<div class='text-center m-t-20'><small><i class='far fa-lock text-success m-r-10'></i>Secure checkout</small></div>";

            fusion_load_script(INFUSIONS."wallet/wallet.js");
            add_to_jquery("walletJs.displayWallet('$init_value');");

            return (string)$html;

        } else {

            // display a wallet account registration form here.
            if (iMEMBER) {

                if (get("activate_wallet")) {

                    $user_id = fusion_get_userdata("user_id");
                    // import data
                    if (!dbcount("(wallet_id)", DB_USER_WALLET, "user_id=:uid", [":uid" => $user_id])) {

                        $init_data = [
                            'wallet_id'  => '0',
                            'user_id'    => $user_id,
                        ];

                        add_notice("success", "We have activated your wallet account.");
                        dbquery_insert(DB_USER_WALLET, $init_data, 'save');
                        redirect(clean_request('', ['activate_wallet'], FALSE));
                    }
                }

                return "<div class='text-center p-20'><a href='".clean_request('activate_wallet=yes', ['activate_wallet'], FALSE)."' id='cc_wallet_acc' class='btn btn-md btn-block btn-rounded btn-pop small text-expanded btn-success'>Activate My Wallet</a></div>\n";

            } else {

                return "<div class='text-center p-20'>				
				<a href='".BASEDIR."login.php' class='btn btn-primary btn-rounded btn-pop btn-md btn-block' id='wallet_login'>Login to PHP-Fusion</a>
				</div>";

            }
        }
    }

    /**
     * Displays Order Receipt from all Payment Drivers
     *
     * @param $transaction_ref
     *
     * @return string|null
     * @throws Exception
     */
    public function displayOrderReceipt($transaction_ref) {
        $receipt = new Receipt();
        return $receipt->displayOrderReceipt($transaction_ref);
    }

}

// ccform validation helper
require_once INCLUDES.'theme_functions_include.php';
require_once THEMES.'/templates/render_functions.php';
