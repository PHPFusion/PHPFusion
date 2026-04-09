<?php
namespace PHPFusion\Infusions\Wallet\Classes;

class Withdraw {

    /*
     * Withdraw Payments Form
     * Need to @fix : All variables from wallet settings not used.
     */
    public function display_withdraw_form($balance, $user_id, $source_account_title = 'PHP Fusion', $options = array()) {
        $wallet = Wallet::getInstance();

        // Execute Payment and Store A Payment.
        if (isset($_POST['form_id']) && !empty($_POST['payout_data'])) {

            $postdata = unserialize($_POST['payout_data']);

            $data['payment_type'] = form_sanitizer($postdata['payment_type']);

            $data['payment_amount'] = form_sanitizer($postdata['payment_amount']);

            $payment_ref = form_sanitizer($postdata['payment_ref']);

            $payment_no = form_sanitizer($postdata['payment_no']);

            switch ($postdata['payment_method']) {

                case 'credits':

                    if ($_POST['form_id'] == 'credit_review_frm' && $balance >= $data['payment_amount'] && fusion_safe()) {

                        // do credit transfer now.
                        $wallet_data = $wallet->getUserWallet($user_id)->getWalletInfo();

                        $new_wallet['wallet_id'] = $wallet::createWalletAccount($user_id);

                        $wallet_data['wallet_id'] = $wallet_data['wallet_id'] ?: $new_wallet['wallet_id'];

                        $wallet_data['balance'] = $wallet_data['balance'] + $data['payment_amount'];

                        dbquery_insert(DB_USER_WALLET, $wallet_data, 'update');

                        $new_rows = [
                            'payment_id'        => 0,
                            'payment_ref'       => $payment_ref,
                            'payment_no'        => $payment_no,
                            'payment_type'      => 'credits',
                            'payment_user'      => $user_id,
                            'payment_amount'    => $data['payment_amount'],
                            'payment_datestamp' => TIME,
                            'payment_status'    => 1,
                            'payment_method'    => $postdata['payment_method'],
                        ];

                        add_notice('success', 'Withdrawal funds has been transferred to your wallet credits account. Please check your new account balance.');

                        $order_id = dbquery_insert(DB_USER_PAYMENTS, $new_rows, 'save');

                        // add 1 transaction to show that deposit has been made into the account
                        $transaction_data = [
                            'transaction_id'          => 0,
                            'transaction_ref'         => $wallet->get_PaymentID($options),
                            'transaction_number'      => $wallet->get_RandomString(),
                            'transaction_user'        => fusion_get_userdata('user_id'),
                            'transaction_title'       => 'Fusion Credits',
                            'transaction_description' => 'Purchase of credits AddonDB cash balance',
                            'transaction_amount'      => $data['payment_amount'],
                            'transaction_item_total'  => $data['payment_amount'],
                            'transaction_type'        => 'Fusion Credits',
                            'transaction_method'      => 'contra',
                            'transaction_oid'         => $order_id,
                            'transaction_ip'          => USER_IP,
                            'transaction_status'      => 1,
                            'transaction_datestamp'   => TIME,
                        ];

                        $transaction_data['transaction_id'] = dbquery_insert(DB_WALLET_TRANSACTIONS, $transaction_data, 'save', ['keep_session' => TRUE]);

                        $bot_id = 15756;
                        $subject = "A Payout have been requested from your AddonDB Merchant Account";
                        $message = "AddonDB Merchant Account withdraw request: <br />
                                Transaction Reference No: ".$new_rows['payment_ref']."-".$order_id."<br/>
                                Wallet Credit Reference No: ".$transaction_data['transaction_ref']."<br/>
                                A total of $".number_format($data['payment_amount'])." USD have been requested for a payout from your AddonDB Merchant Account to your Fusion Wallet Account<br />
                                If this is incorrect please contact PHP-Fusion Technical support immediately and reset your account password.<br />
                                Best regards <br /> PHP-Fusion";

                        send_pm($wallet_data['user_id'], $bot_id, $subject, $message);

                        require_once INCLUDES."sendmail_include.php";
                        $toemail = fusion_get_userdata("user_email");
                        $toname = fusion_get_userdata("user_firstname").' '.fusion_get_userdata("user_lastname");

                        $html = Template::getInstance('paymentConfirmation');
                        $html->set_template(WALLET.'templates/payouts/confirmation.html');
                        $html->set_tag('total_amount', '$'.number_format($new_rows['payment_amount'], 2).' USD');
                        $html->set_tag('nett_amount', '$'.number_format($new_rows['payment_amount'], 2).' USD');
                        $html->set_tag('to', 'Fusion Credits');
                        $html->set_tag('status', 'Completed');
                        $html->set_tag('tax', '$'.number_format(0, 2).' USD');
                        $html->set_tag('transaction_id', $new_rows['payment_ref']);
                        $html->set_tag('longdate', showdate('longdate', $new_rows['payment_datestamp']));
                        $html->set_tag('from', 'PHP Fusion Addondb Merchant Account');
                        $html->set_tag('back_link', clean_request('', ['tid', 'payment'], FALSE));
                        $message = $html->get_output();
                        sendemail($toname, $toemail, "PHP-Fusion Inc", fusion_get_settings("site_email"), $subject, $message);
                        // redirect to view bill
                        redirect(FUSION_REQUEST.'&tid='.$new_rows['payment_ref']);

                    } else {
                        add_notice('danger', "Payment could not be processed due to data integrity error. Please contact administrator.");
                    }
                    break;
                case 'paypal':

                    if ($_POST['form_id'] == 'paypal_review_frm' && $balance >= $data['payment_amount'] && fusion_safe()) {
                        $new_rows = [
                            'payment_id'        => 0,
                            'payment_ref'       => $payment_ref,
                            'payment_no'        => $payment_no,
                            'payment_type'      => 'credits',
                            'payment_user'      => $user_id,
                            'payment_amount'    => $data['payment_amount'],
                            'payment_datestamp' => TIME,
                            'payment_status'    => 2,
                            'payment_method'    => $postdata['payment_method'],
                            'payment_info'      => \Defender::encrypt_string($postdata['payment_info'], fusion_get_user($user_id, 'user_password')),
                        ];
                        add_notice('success', 'Funds withdraw has been processed.');
                        $payment_info = \Defender::decrypt_string($new_rows['payment_info'], fusion_get_user($user_id, 'user_password'));
                        $order_id = dbquery_insert(DB_USER_PAYMENTS, $new_rows, 'save');
                        $bot_id = 15756;
                        $subject = "A Payout have been requested from your AddonDB Merchant Account";
                        $message = "AddonDB Merchant Account withdraw request: <br />
                                Transaction Reference No: ".$new_rows['payment_ref']."<br/>
                                Wallet Credit Reference No: ".$new_rows['payment_ref']."<br/>
                                A total of $".number_format($data['payment_amount'])." USD have been requested for a payout from your PayPal Account at ".$payment_info."<br />
                                Please allow up to 5 working days to complete this transaction request.<br/>
                                If this is incorrect please contact PHP-Fusion Technical support immediately and reset your account password.<br />
                                Best regards <br /> PHP-Fusion";
                        send_pm($user_id, $bot_id, $subject, $message);

                        require_once INCLUDES."sendmail_include.php";
                        $toemail = fusion_get_userdata("user_email");
                        $toname = fusion_get_userdata("user_firstname").' '.fusion_get_userdata("user_lastname");

                        $html = Template::getInstance('confirmation');
                        $html->set_template(WALLET.'templates/payouts/confirmation.html');
                        $html->set_tag('total_amount', '$'.number_format($new_rows['payment_amount'], 2).' USD');
                        $html->set_tag('nett_amount', '$'.number_format($new_rows['payment_amount'], 2).' USD');
                        $html->set_tag('to', 'Paypal Account -'.$payment_info);
                        $html->set_tag('status', 'Completed');
                        $html->set_tag('tax', '$'.number_format(0, 2).' USD');
                        $html->set_tag('transaction_id', $new_rows['payment_ref']);
                        $html->set_tag('longdate', showdate('longdate', $new_rows['payment_datestamp']));
                        $html->set_tag('from', 'PHP Fusion Addondb Merchant Account');
                        $html->set_tag('back_link', clean_request('', ['tid', 'payment'], FALSE));
                        $message = $html->get_output();
                        sendemail($toname, $toemail, "PHP-Fusion Inc", fusion_get_settings("site_email"), $subject, $message);
                        // redirect to view bill
                        redirect(FUSION_REQUEST.'&tid='.$new_rows['payment_ref']);
                    } else {
                        add_notice('danger', "Payment could not be processed due to data integrity error. Please contact administrator.");
                    }
            }
        }

        // now show the credit confirmation
        if (isset($_GET['tid']) && isnum($_GET['tid'])) {
            $result = dbquery("SELECT * FROM ".DB_USER_PAYMENTS." WHERE payment_ref=:tid", [':tid' => stripinput($_GET['tid'])]);
            if (dbrows($result)) {
                $data = dbarray($result);
                $method = [
                    'credits' => 'PHP-Fusion Wallet Credits Account',
                    'paypal'  => 'PayPal Account',
                    'local'   => 'Local Bank Account'
                ];
                $payment_info = '';
                if ($data['payment_info']) {
                    $payment_info = "<br/>\n".\Defender::decrypt_string($data['payment_info'], fusion_get_user($data['payment_user'], 'user_password'));
                }
                $html = Template::getInstance('confirmation');
                $html->set_template(WALLET.'templates/payouts/confirmation.html');
                $html->set_tag('total_amount', '$'.number_format($data['payment_amount'], 2).' USD');
                $html->set_tag('nett_amount', '$'.number_format($data['payment_amount'], 2).' USD');
                $html->set_tag('to', $method[$data['payment_method']].$payment_info);
                $html->set_tag('status', 'Completed');
                $html->set_tag('tax', '$'.number_format(0, 2).' USD');
                $html->set_tag('transaction_id', $data['payment_ref']);
                $html->set_tag('longdate', showdate('longdate', $data['payment_datestamp']));
                $html->set_tag('from', $source_account_title);
                $html->set_tag('back_link', clean_request('', ['tid', 'payment'], FALSE));

                return $html->get_output();
            }
        }

        if (isset($_POST['withdraw_funds'])) {

            $data['payment_type'] = form_sanitizer($_POST['payment_type'], '', 'payment_type');
            $data['payment_amount'] = form_sanitizer($_POST['payment_amount'], '', 'payment_amount');
            $data['payment_amount'] = form_sanitizer($_POST['payment_amount'], '', 'payment_amount');
            if (fusion_safe()) {
                if ($balance >= $data['payment_amount']) {
                    // add a payout entry.
                    $user_id = 276;
                    $payment_ref = $wallet->get_PaymentID($options);
                    $payment_no = $wallet->get_RandomString();
                    // add that such payment is success
                    // deliver
                    switch ($data['payment_type']) {
                        case 'credits': // send a payment credit.
                            $new_rows = [
                                'payment_id'        => 0,
                                'payment_ref'       => $payment_ref,
                                'payment_no'        => $payment_no,
                                'payment_type'      => 'Fusion Credits',
                                'payment_user'      => $user_id,
                                'payment_amount'    => $data['payment_amount'],
                                'payment_currency'  => 'USD',
                                'payment_datestamp' => TIME,
                                'payment_ip'        => USER_IP,
                                'payment_status'    => 1,
                                'payment_method'    => 'credits',
                            ];
                            $form = openform('credit_review_frm', 'post', FUSION_REQUEST).form_hidden('payout_data', '', serialize($new_rows)).form_button('confirm_payout', 'Transfer', 'confirm_payout', ['class' => 'btn-success btn-block btn-md btn-bordered']).closeform();
                            $html = Template::getInstance('payout_review');
                            $html->set_template(WALLET.'templates/payouts/review.html');
                            $html->set_tag('from', $source_account_title);
                            $html->set_tag('to', 'PHP-Fusion User Wallet Credits Account');
                            $html->set_tag('amount', '$'.number_format($data['payment_amount'], 2).' USD');
                            $html->set_tag('form', $form);

                            return $html->get_output();
                            break;
                        case 'paypal':
                            $paypal_email = form_sanitizer($_POST['paypal_email'], '', 'paypal_email');
                            if (!$paypal_email) {
                                add_notice('danger', 'Please enter a valid PayPal account email address');
                            }
                            if (fusion_safe()) {
                                $new_rows = [
                                    'payment_id'        => 0,
                                    'payment_ref'       => $payment_ref,
                                    'payment_no'        => $payment_no,
                                    'payment_type'      => 'PayPal',
                                    'payment_user'      => $user_id,
                                    'payment_amount'    => $data['payment_amount'],
                                    'payment_currency'  => 'USD',
                                    'payment_datestamp' => TIME,
                                    'payment_ip'        => USER_IP,
                                    'payment_status'    => 1,
                                    'payment_method'    => 'paypal',
                                    'payment_info'      => $paypal_email
                                ];
                                $form = openform('paypal_review_frm', 'post', FUSION_REQUEST).form_hidden('payout_data', '', serialize($new_rows)).form_button('confirm_payout', 'Transfer', 'confirm_payout', ['class' => 'btn-success btn-block btn-md btn-bordered']).closeform();
                                $html = Template::getInstance('payout_review');
                                $html->set_template(WALLET.'templates/payouts/review.html');
                                $html->set_tag('from', $source_account_title);
                                $html->set_tag('to', 'Paypal Email - '.$paypal_email);
                                $html->set_tag('amount', '$'.number_format($data['payment_amount'], 2).' USD');
                                $html->set_tag('form', $form);
                                $html->set_block('process_time_block', []);

                                return $html->get_output();
                            }
                            break;
                        default:
                            add_notice('danger', 'Unsupported Payment Type');
                            redirect(clean_request(''));
                    }
                }
            }
        }

        $return_url = fusion_get_settings('siteurl').'profile.php'.$_SERVER['QUERY_STRING'].'&amp;withdraw=complete';
        add_to_jquery("
            $('#payment_type').bind('click', function(e) {
                var val = $('#payment_type').val();
                console.log(val);
                if (val == 'paypal') {
                    $('#cc_paypal_form').show();
                } else {
                    $('#cc_paypal_form').hide();
                }
            });
        ");

        return "<div class='row'>\n<div class='col-xs-12 col-sm-8'>\n"
            .openform('payment_frm', 'post', FUSION_REQUEST).
            form_select('payment_type', 'Deposit to', '', [
                    'options'     => [
                        //'local' => "Deposit to Local Bank Account",
                        'credits' => "Deposit to Wallet Credits",
                        'paypal'  => "Deposit to PayPal Account"
                    ],
                    'inner_width' => '100%',
                    'width'       => '100%'
                ]
            ).
            "<div id='cc_paypal_form' style='display:none;'>".form_text('paypal_email', 'Paypal Email Account', '', ['type' => 'email'])."</div>".
            form_hidden('return_url', '', $return_url).
            form_text('payment_amount', 'Withdraw Amount', '', [
                'type'          => 'number',
                'class'         => 'decimal',
                'placeholder'   => '0.00',
                'prepend'       => TRUE,
                'prepend_value' => "USD",
                'number_step'   => '0.1',
            ]).
            form_button('withdraw_funds', 'Confirm Withdraw Amount', 'withdraw_funds', ['class' => "btn btn-success btn-bordered"])
            .closeform().
            "</div><div class='col-xs-12 col-sm-4'>
            <div class='well'>
            <strong>Notice of Payments</strong><br/>
            <p>Please note that local bank account transfer requires offline processing which may take up to 5-7 working days to complete.</p>
            </div>
            </div></div>";

    }


}
