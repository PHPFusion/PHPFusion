<?php

class FDATA_Deprecated {

    /*
 * Displays Checkout
 * Callback on Order and Processing for Payment.
 * Make sure telephone number, billing address are all filled in. Transform to field on first time.
 *
 * PORT TO REMOTE CHECKOUT --- payments file
 *
 */

    public function checkout($options) {
        // Debugger
        $debug = FALSE;
        $post_debug = FALSE;

        $settings = fusion_get_settings();

        $tpl = Template::getInstance('checkout_firstdata');
        $tpl->set_template(self::$checkout_template);
        $tpl->set_file([IMAGES]);

        if ($post_debug === TRUE) {
            print_P($_POST);
        }

        // Sandbox Activation
        if (Wallet::walletSettings('firstdata_sandbox') == 1) {
            $tpl->set_block('alerts', ['alert' => alert(
                "<strong><i class='fas fa-exclamation-triangle m-r-10'></i>First Data Development Sandbox is Enabled.</strong> There will be no actual transaction."
                , [
                    'class'   => 'alert-warning',
                    'dismiss' => TRUE
                ]
            )
            ]);
        }

        $user_id = iMEMBER ? fusion_get_userdata("user_id") : USER_IP;

        add_breadcrumb([
            'link'  => BASEDIR.$settings['opening_page'],
            'title' => $settings['sitename'],
        ]);
        add_breadcrumb([
            'link'  => clean_request('', ['error'], FALSE),   // top up link.
            'title' => 'Credit Card Checkout'
        ]);

        // get currency
        $tpl->set_tag('sitename', $settings['sitename']);
        $tpl->set_tag('sitebanner', $settings['site_path'].$settings['sitebanner']);
        $tpl->set_tag('breadcrumb', render_breadcrumbs());

        $notices = getNotices(['all', FUSION_SELF]);
        if (!empty($notices)) {
            $tpl->set_block('notices', ['content' => renderNotices($notices)]);
        }

        $tpl->set_tag('siteurl', BASEDIR.$settings['opening_page']);
        $tpl->set_tag('payment_id', 'Invalid Payment ID');
        $tpl->set_tag('payment_type', "Invalid");
        $tpl->set_tag('order_subtotal', number_format(0, 2));
        $tpl->set_tag('order_total_shipping', number_format(0, 2));
        $tpl->set_tag('order_total_tax', number_format(0, 2));
        $tpl->set_tag('order_total', number_format(0, 2));
        $tpl->set_tag('card_ending', "No Description");
        $tpl->set_block('payment_method', [
            'payment_title' => "Credit Card",
            'payment_image' => "<div style=\"margin-left:-3px; margin-top:10px; padding:5px; display:inline-block;\" class=\"list-group-inverse\">
                        <small><a href='#' id='msv_btn'>Verified by Visa</a>,
                        <a href='#' id='mss_btn'>Mastercard SecureCode</a>,
                        <a href='https://network.americanexpress.com/globalnetwork/products-and-services/security/safekey/' target='_blank'>American Express Safekey</a>
                        </small>
                        </div>
            ",
        ]);
        $tpl->set_block('payment_message', [
            'text' => 'Your card may be eligible or enrolled in Verified by Visa or Mastercard SecureCode payer authentication programs. After clicking the \'Pay Online\' button, your Card Issuer may prompt you for your payer authentication password to complete your purchase.',
        ]);

        if (isset($_REQUEST['payment_id']) && isnum($_REQUEST['payment_id']) && fusion_safe()) {

            $PaymentID = stripinput($_REQUEST['payment_id']);

            $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_ref=:payment_id AND transaction_method='firstdata'", [':payment_id' => $PaymentID]);

            if (dbrows($result)) {

                $data = dbarray($result);

                // Other Variables
                $tpl->set_tag('currency', $data['transaction_currency']);
                // Prepare Globals
                $config = self::get_config();
                $wallet_data = Wallet::getUserWallet($user_id);
                $user_email = fusion_get_user($wallet_data['user_id'], 'user_email');
                $currency_number = $this->get_transaction_currency($data['transaction_currency']);
                $transaction_total = number_format($data['transaction_amount'], 2);

                // Error validation
                if (isset($_REQUEST['error']) && isset($_REQUEST['status']) && $_REQUEST['status'] == 'FAILED' && !empty($_REQUEST['fail_reason'])) {

                    $result = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_tid=:tid", [
                        ':tid' => $data['transaction_id'],
                    ]);

                    // Create a New Invoice and Set the Old Invoice as Failed. Need? no. just ask user to resubmit. coz we have the payment id.
                    $new_data = $data;
                    $new_paymentID = str_shuffle(time().rand());
                    $new_ref = Wallet::generateRandomString();

                    if (dbrows($result)) {
                        while ($odata = dbarray($result)) {
                            // new orders
                            // need to generate a new reference.
                            $odata['order_reference'] = $new_ref;
                            $odata['order_tid'] = 0;
                            $odata['order_id'] = 0;
                            $new_order_id[] = dbquery_insert(DB_WALLET_ORDERS, $odata, 'save');
                            // fail previous orders
                            dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_paid=2 WHERE order_id=:oid", [':oid' => $odata['order_id']]);
                        }
                    }

                    // fail previous transaction
                    dbquery("UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_status=2, transaction_response=:resp WHERE transaction_ref=:payid AND transaction_method='firstdata'", [
                        ':payid' => $PaymentID,
                        ':resp'  => \Defender::encode($_REQUEST)
                    ]);

                    if (!empty($new_order_id)) {

                        $new_data['transaction_id'] = 0;
                        $new_data['transaction_ref'] = $new_paymentID;
                        $new_data['transaction_number'] = $new_ref;
                        $new_data['transaction_oid'] = implode('.', $new_order_id);

                        $new_transaction_id = dbquery_insert(DB_WALLET_TRANSACTIONS, $new_data, 'save');

                        dbquery("UPDATE `".DB_WALLET_ORDERS."` SET `order_tid`=:tid WHERE `order_id` IN (".$new_data['transaction_oid'].")", [':tid' => intval($new_transaction_id)]);

                        // ok, now we redirect with new payment reference.
                        $message = "Payment validation has failed.";

                        if (iSUPERADMIN) {

                            $message .= "<br/>Fail reason: ".$_REQUEST['fail_reason'];
                        }
                        // we need to log this transaction and generate another transaction.
                        add_notice('danger', $message);

                        redirect(clean_request('payment_id='.$new_paymentID, ['payment_id', 'error'], FALSE));

                    } else {

                        $message = "Payment validation has failed.";
                        add_notice("danger", $message);
                        redirect(BASEDIR.$settings['opening_page']);
                    }
                }

                /**
                 * Credit Card Payment Processing (IPN)
                 * Documentation:
                 * IPG_IntegrationGuide_Connect_V23018-3_130818.pfd (see documentation folder)
                 * */

                if (isset($_POST['form_id']) && $_POST['form_id'] == 'checkout_frm' && $wallet_data['wallet_id']) {

                    // Find items.
                    $order_result = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_id IN (:oid)", [':oid' => str_replace('.', ',', $data['transaction_oid'])]);
                    if ($rowCount = dbrows($order_result)) {
                        // Validate that the data response from First Data is coming from our server.
                        $fusion_token = Token::generate_token($this->firstdata_token, 1, \Defender::pageHash());
                        $datetime = $this->get_current_datetime();
                        $fields = [
                            'txntype'               => 'sale',
                            'cardnumber'            => form_sanitizer($_POST['card_no'], '', 'card_no'),
                            'expmonth'              => form_sanitizer($_POST['card_exp_1'], '', 'card_exp_1'),
                            'expyear'               => form_sanitizer($_POST['card_exp_2'], '', 'card_exp_2'),
                            'expmonth'              => form_sanitizer($_POST['card_exp_1'], '', 'card_exp_1'),
                            'sname'                 => form_sanitizer($_POST['card_holder'], '', 'card_holder'),
                            'cvm'                   => form_sanitizer($_POST['card_CVV2'], '', 'card_CVV2'),
                            'mode'                  => 'payonly', // just for one off payment
                            'full_bypass'           => 'true',
                            'oid'                   => $PaymentID, // order invoice number
                            'checkoutoption'        => 'combinedpage', // inline?
                            'customerid'            => $user_id, // our system user id
                            'invoicenumber'         => $data['transaction_number'], // unique transaction hash ---------------------- THIS ONE IN PAYPAL
                            'language'              => 'en_US',
                            'responseFailURL'       => $_SERVER['HTTP_REFERER']."&error=1", // response fail.
                            'responseSuccessURL'    => $config['DefaultCallbackURL'], // The success page.
                            // Customization
                            'customParam_walletID'  => $options['user']['wallet_id'],
                            'customParam_paymentID' => $data['transaction_ref'],
                            'customParam_payment'   => 'firstdata',
                            'customParam_token'     => $fusion_token,
                            // hash component
                            'storename'             => $config['merchant_id'], // Provided by First Data
                            'timezone'              => $this->get_current_timezone(),
                            'txndatetime'           => $datetime['datetime'],
                            'currency'              => $currency_number, // code number
                            'hash_algorithm'        => 'sha256',
                            'hash'                  => self::createHash("$transaction_total", "$currency_number"),
                            'dynamicMerchantName'   => $config['merchant_name'], // Company Name "PHP-Fusion Inc"
                            //'cardFunction'          => 'credit',
                            'merchantTransactionId' => $data['transaction_id'],
                            // transaction amount details
                            'shipping'              => $data['transaction_shipping'] > 0 ? number_format($data['transaction_shipping'], 2) : '0',
                            'vattax'                => $data['transaction_tax'] > 0 ? number_format($data['transaction_tax'], 2) : '0',
                            'chargetotal'           => number_format($data['transaction_amount'], 2),
                            'subtotal'              => number_format($data['transaction_item_total'], 2),
                            // Customer Information from Wallet
                        ];

                        $bname = $wallet_data['type'] == 2 ? $wallet_data['company'] : $wallet_data['first_name'].' '.$wallet_data['last_name'];
                        $phone = ($wallet_data['mobile'] ? "+".$wallet_data['mobile_cc']."-".$wallet_data['mobile'] : "");
                        $fax = ($wallet_data['fax'] ? "+".$wallet_data['fax_cc']."-".$wallet_data['fax'] : "");

                        $default_customer_fields = [
                            'bname'    => $bname,
                            'baddr1'   => $wallet_data['address'],
                            'baddr2'   => $wallet_data['address_2']." ".$wallet_data['address_3'],
                            'bcity'    => $wallet_data['city'],
                            'bstate'   => $wallet_data['region'],
                            'bcountry' => $wallet_data['country'],
                            'bzip'     => $wallet_data['postcode'],
                            'phone'    => $phone,
                            'fax'      => $fax,
                            'email'    => $user_email,
                        ];

                        $fields += $default_customer_fields;
                        //print_p($fields);

                        if (fusion_safe()) {
                            // Start IPN Form
                            $action_url = 'https://www4.ipg-online.com/connect/gateway/processing';

                            $form = "<form id='fdms_submitfrm' name='firstdata_submit_frm' method='post' action='$action_url'>\n";
                            foreach ($fields as $key => $val) {
                                $form .= "<input type='hidden' name='$key' value='$val'/>\n";
                            }

                            $x = 1;
                            $shipping_fees = 0;
                            while ($rows = dbarray($order_result)) {
                                if (!empty($rows['order_total_shipping'])) {
                                    $shipping_fees = $rows['order_total_shipping'] + $shipping_fees;
                                }
                                // format is id;description;quantity;item_total_price;sub_total;tax;shipping as 0;
                                // shippping add extra line: IPG_SHIPPING; as 'id' followed by the other param
                                // handling add extra line: IPG_HANDLING; as 'id' followed by the other param
                                if ($x === 999)
                                    break;
                                $item_arr = [
                                    'id'            => $rows['order_item_id'].'-'.$rows['order_item_type'],
                                    'description'   => $rows['order_title'],
                                    'qty'           => $rows['order_item_quantity'],
                                    'line_total'    => $rows['order_total'] > 0 ? number_format($rows['order_total'], 2) : '0',
                                    'item_price'    => $rows['order_item_value'] > 0 ? number_format($rows['order_item_value'], 2) : '0',
                                    'tax'           => $rows['order_total_tax'] > 0 ? number_format($rows['order_total_tax'], 2) : '0',
                                    'shipping_fees' => $rows['order_item_tax_rate'] > 0 ? number_format($rows['order_total_shipping'], 2) : '0',
                                ];
                                $item_value = implode(';', $item_arr);
                                $form .= "<input type='hidden' name='item_$x' value='$item_value' />";
                                $x++;
                            }
                            if (!empty($shipping_fees)) {
                                $form .= "<input type='hidden' name='item_$x' value='IPG_SHIPPING;Shipping costs;1;".number_format($shipping_fees, 2).";".number_format($shipping_fees, 2).";0;0' />";
                                $x++;
                            }
                            // For Fullbypass set to true, they need an item with ID IPG_HANDLING.
                            $form .= "<input type='hidden' name='item_$x' value='IPG_HANDLING;Transaction Fee;1;0;0;0;0'/>";
                            $form .= "<input type='hidden' name='cardFunction' value='credit'/>";
                            unset($item_arr);
                            $form .= "</form>";

                            dbquery("UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_datestamp=:time WHERE transaction_id=:id", [
                                ':id'   => $data['transaction_id'],
                                ':time' => $datetime['timestamp']
                            ]);

                            print_p($datetime['timestamp']);
                            print_P(showdate('longdate', $datetime['timestamp']));
                            print_p($this->get_current_timezone());

                            // Send The Form
                            if ($debug === TRUE) {
                                print_p($form);
                            } else {

                                // Don't use the modal, but blur out and show a loader.
                                $form .= "<script>setTimeout(function(e) { $('#fdms_submitfrm').submit(); }, 3);</script>\n";
                                $chtml = Template::getInstance('fdata_processing_frm');
                                $chtml->set_tag('card_image', WALLET.'drivers/firstdata/fdata.jpg');
                                $chtml->set_block('payment_block', ['ipn_form' => $form]);
                                $chtml->set_template(self::$processing_template);
                                $modal = openmodal("payment", "", ['static' => TRUE]).$chtml->get_output().closemodal();
                                add_to_footer($modal);
                            }
                        }
                    }
                }

                // Display order details
                $cresult = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_id IN (:oid)",
                    [
                        ':oid' => str_replace('.', ',', $data['transaction_oid'])
                    ]
                );
                if ($rowCount = dbrows($cresult)) {

                    $data['item'] = [];
                    add_to_jquery("$('button#pay').bind('click', function(e) { $('#checkout_frm').submit(); });");

                    while ($rows = dbarray($cresult)) {
                        $current_item_id = $rows['order_item_id'].'-'.$rows['order_item_type'];
                        $data['item'][$current_item_id] = [
                            'order_title'         => $rows['order_title'],
                            'order_description'   => $rows['order_description'],
                            'order_item_quantity' => number_format($rows['order_item_quantity'], 2),
                            'order_item_id'       => $rows['order_item_id'],
                            'order_item_value'    => number_format($rows['order_item_value'], 2)
                        ];
                    }

                    if ($rowCount > 1) {
                        foreach ($data['item'] as $item_id => $item_data) {
                            $tpl->set_block("single_item_block", $item_data);
                        }
                    } else {
                        $item_data = array_values($data['item']);
                        if (!empty($item_data)) {
                            $tpl->set_block("single_item_block", $item_data[0]);
                        }
                    }

                    if (iMEMBER) {
                        $tpl->set_block('member_field_block', Wallet::info_hero_form());
                    } else {
                        $tpl->set_block('non_member_field_block', [
                            'link'  => BASEDIR.'register.php',
                            'title' => 'Register new PHP-Fusion user',
                        ]);
                    }

                    $tpl->set_block('credit_card', [
                            'card_openform'  => openform('checkout_frm', 'post', FUSION_REQUEST),
                            'card_closeform' => closeform()
                        ] + Wallet::cc_form([], $debug));


                    // This form does not send to paypal, but send to our own driver formatter.
                    // This is a measure to prevent field manipulation or hijack posts values.
                    $tpl->set_tag('payment_id', $PaymentID);
                    $tpl->set_tag('order_subtotal', number_format($data['transaction_item_total'], 2));
                    $tpl->set_tag('order_total_shipping', number_format($data['transaction_shipping'], 2));
                    $tpl->set_tag('order_total_tax', number_format($data['transaction_tax'], 2));
                    $tpl->set_tag('order_total', number_format($data['transaction_amount'], 2));
                    $tpl->set_tag('card_ending', $data['transaction_description']);
                    $tpl->set_tag('payment_type', $data['transaction_type']);

                } else {
                    $tpl->set_block('alerts', [
                        'alert' => alert("<strong><i class='fas fa-exclamation-triangle m-r-10'></i>No Items Found.</strong>", ['class' => 'alert-danger'])
                    ]);
                }
            } else {
                $tpl->set_block('alerts', ['alert' => alert("<strong><i class='fas fa-exclamation-triangle m-r-10'></i>No Transactions Found.</strong>", ['class' => 'alert-danger'])]);
            }


        } else {
            // Log him as danger person.
            $tpl->set_block('alerts', [
                'alert' => alert("<strong><i class='fas fa-exclamation-triangle m-r-10'></i>Invalid Checkout Request.</strong> Please contact system administrator to assist you on this matter.", ['class' => 'alert-danger'])
            ]);
        }

        return $tpl->get_output();

    }


}
