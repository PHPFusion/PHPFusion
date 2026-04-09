<?php

/**
 * Code archived.
 * @return array
 */
function info_hero_form() {

    /*
    if (isset($_POST['save_birthdate'])) {
        $birthdate = form_sanitizer($_POST['birthdate'], '', 'birthdate');
        if (fusion_safe()) {
            $wallet['birthdate'] = $birthdate;
            dbquery_insert(DB_USER_WALLET, $wallet, 'update');
            add_notice('success', "Date of Birth updated");
            request_redirect();
        }
    }
    */

    // Use the IP to track the idiot;
    $user_ip = USER_IP;
    //$user_ip = '219.93.183.103';
    // use geo location to determine current user accessing ip
    $country_code = IP::country($user_ip);

    $state_code = 0;
    if (!empty($wallet['region'])) {
        $region = explode(",", $wallet['region']);
        $state_code = $region[0];
        $country_code = $region[1];
    }

    $name = [
        $wallet['first_name'],
        $wallet['last_name'],
    ];
    $display_name = implode(' ', array_filter($name));

    $address = [
        $wallet['address'],
        $wallet['address_2'],
        $wallet['country'], // Parseback value as well.
        $wallet['region'], // Parseback value.
        $wallet['city'],
        $wallet['postcode'],
    ];

    $display_address = array_filter([
        rtrim($wallet['address'], ','),
        rtrim($wallet['address_2'], ','),
        $wallet['city'],
        //$wallet['region'],
        ($country_code && $state_code) ? Geomap::get_States(trim($country_code), trim($state_code)) : '',
        Geomap::get_Country($wallet['country']),
        $wallet['postcode'],
    ]);

    $display_address = implode(', ', $display_address);


    $Name_Field = '';
    // Name (Need to verify with us first)
    if ($wallet['type'] == 1) {
        $Name_Field = form_hero("name", "post", FUSION_REQUEST, "Name", $name, [
            'display_value' => $display_name ?: '<strong class="required">Your Name is a required field.</strong>',
            "field"         => [
                "function_type" => ["\\PHPFusion\\Infusions\\Wallet\\Classes\\Wallet", "nameInputFields"],
                "name"          => "name",
                "label"         => "Name",
                'options'       => [
                    'required' => TRUE,
                ],
            ],
            'save'          => [
                'name'    => 'save_name',
                'label'   => 'Confirm Name',
                'value'   => 'save_name',
                'options' => ['class' => 'btn-success'],
            ]
        ]);
    }

    // Email - Cannot change (Need to verify us first)
    $Email_Field = "<div class='row'>\n<div class='col-xs-12 col-sm-3'>\n<h5 class='m-0'>Email</h5></div>\n";

    $Email_Field .= "<div class='col-xs-12 col-sm-9'>\n".$wallet['email']."</div>\n</div>\n";

    $Email_Field .= form_hidden('email', $wallet['email'])."<br/>\n";

    // Address
    $Address_Field = form_hero("address", "post", FUSION_REQUEST, "Address", $address, [
        'display_value' => $display_address ?: "<strong class='required'>Address is a required field.</strong>",
        "field"         => [
            "function_type" => ["\\PHPFusion\\Infusions\\Wallet\\Classes\\Wallet", "addressInputFields"],
            "name"          => "address",
            "label"         => "Address",
            'options'       => [
                'required' => TRUE,
            ],
        ],
        'save'          => [
            'name'    => 'save_address',
            'label'   => 'Confirm Address',
            'value'   => 'save_address',
            'options' => ['class' => 'btn-success'],
        ]
    ]);

    $Phone_Field = form_hero("phone", "post", FUSION_REQUEST, "Phone", $wallet['phone'], [
        "display_value" => ((!empty($wallet['phone'])) ? $calling_codes." ".$wallet['phone'] : '<strong class="required">Phone is a required field.</strong>'),
        "field"         => [
            "function_type" => "form_text",
            "name"          => "phone",
            "label"         => "Phone Number $calling_codes",
            "options"       => [
                "required"    => TRUE,
                "placeholder" => "Phone Number",
                "type"        => "number",
                "class"       => "label-float",
            ]
        ],
        'save'          => [
            'name'    => 'save_phone',
            'label'   => 'Confirm Phone',
            'value'   => 'save_phone',
            'options' => ['class' => 'btn-success btn-bordered'],
        ]
    ]);

    $Fax_Field = form_hero("fax", "post", FUSION_REQUEST, "Fax", $wallet['fax'], [
        "display_value" => ((!empty($wallet['fax'])) ? $calling_codes." ".$wallet['fax'] : ''),
        "field"         => [
            "function_type" => "form_text",
            "name"          => "fax",
            "label"         => "Fax Number $calling_codes",
            "options"       => [
                "inline"      => FALSE,
                "placeholder" => "Fax Number",
                "type"        => "number",
                "class"       => "label-float",
                //                    "prepend_value" => "+".$calling_codes,
            ]
        ],
        'save'          => [
            'name'    => 'save_fax',
            'label'   => 'Confirm Fax',
            'value'   => 'save_fax',
            'options' => ['class' => 'btn-success btn-bordered'],
        ]
    ]);

    /*
    $DOB_Field = form_hero("birthdate", "post", FUSION_REQUEST, "Birthdate", $wallet['birthdate'], array(
        'field'         => array(
            'function_type' => 'form_datepicker',
            'name'          => 'birthdate',
            'label'         => 'Birthdate',
            'options'       => array(
                'required'        => TRUE,
                'placeholder'     => 'Your Birthdate',
                'class'           => 'm-b-10',
                'date_format_php' => 'd-m-Y',
                'date_format_js'  => 'D-M-YYYY',
                'type'            => 'timestamp',
                'stacked'         => display_request_input('birthdate', 'save_birthdate'),
            )
        ),
        'display_value' => '', // fuck i need birthdate
        //'display_value' => date('d-m-Y', $wallet['birthdate']),
        'save'          => [
            'name'    => 'save_birthdate',
            'label'   => 'Confirm Date of Birth',
            'value'   => 'save_birthdate',
            'options' => array('class' => 'btn-success btn-bordered'),
        ]
    ));
    */

    // fields
    $required = [
        'country', 'region', 'city', 'address', 'postcode', 'phone', 'phone_cc', 'email'
    ];
    $crequired = [
        'company'
    ];
    if ($wallet['type'] == 1) {
        $crequired = [
            'first_name',
            'last_name',
        ];
    }
    $required = array_merge($required, $crequired);
    $validated = TRUE;
    foreach ($required as $check) {
        if (empty($wallet[$check])) {
            $validated = FALSE;
        }
    }

    if ($validated === FALSE) {

        add_to_jquery("$('button#pay').prop('disabled', true);");
    }

    return [
        'name_field'    => openform('namefrm', 'post').$Name_Field.closeform(),
        'email_field'   => openform('emailfrm', 'post').$Email_Field.closeform(),
        'address_field' => openform('addressfrm', 'post').$Address_Field.closeform(),
        'phone_field'   => openform('phonefrm', 'post').$Phone_Field.closeform(),
        'fax_field'     => openform('faxfrm', 'post').$Fax_Field.closeform(),
    ];
}

function archive_form() {

    // $amount_js = '';
    // $validate_amount_js = '';
    // we will now need to convert the form of PHP to send through ajax.
    // $path_to_checkout = fusion_get_settings('site_path').'infusions/wallet/checkout.json.php';
    /*
     *         if ($options['display_amount_field']) {
        if (empty($options['order_amount'])) {
            $amount_js = "
            $('#amount').on('input propertychange paste', function(e) {
            var amount_val = $(this).val();
            $('#paypal_amount').val(amount_val);
            });
            ";


            $validate_amount_js = "
            var c_val = $('#paypal_amount').val();
            if (!c_val || c_val == 0) {
                alert('Please fill in the amount field');
                return false;
            }
            ";
        }
    }

     */
    /*add_to_jquery("
    $amount_js
    $('#pay_Paypal').bind('click', function(e) {

        e.preventDefault();

        var data = {
            'PaymentMethod': 'Paypal',
            'PaymentDesc' : 'Transaction Payment via Paypal Account',
            'PaymentID': '".$data['payment_id']."',
        }

        var formData = $('#paypalPaymentFrm').serialize() + '&' + $.param(data);


        $validate_amount_js

        $.ajax({

            url:  '$path_to_checkout', // log transactions

            data: formData,

            dataType: 'json', // debug with html

            method: 'post',


            success: function(result) {

                if (result.status == 'OK') {

                    $.ajax({

                        url: '".fusion_get_settings('siteurl')."infusions/wallet/drivers/paypal/payments.php',

                        data: {
                            'payment_id': result.response,
                            'form_id': '$this->paypal_token',
                            'fusion_token': '$fusion_token',
                        },

                        type: 'POST',

                        dataType: 'json',

                        beforeSend: function() {

                            $('#pay_Paypal').html(' Redirecting to Paypal <img src=\"$loader_path\" /> ');

                        },

                        success: function(result) {

                            if (result.status == 'OK' && result.form) {

                                $('#paypal-form-container').html(result.form);

                                $('#paypal_frm').submit();

                            }
                        },
                        error: function(e) {

                            console.log('Cannot fetch paypal payment form');

                        }
                     });

                }

            },
            error: function(e) {
                console.log('Paypal driver transaction error.');
            }
        });
    });
    ");*/
}

/**
 * WALLET 2.0 Deprecated this mode.
 * File: classes/wallet.php
 *
 * Displays Checkout Form and Handles Verification Methods (Set Order Paid or Unpaid)
 *
 * For delivery system, we need to have an active token for extra added security. We're professional system developer. We need the best system.
 * And ALSO The Return Form -- Change ALL API default return to checkout.php?payment_method=Paypal&amp;transaction_ref="" AND transaction_token=xxxxx
 *
 * @return string
 * @throws \Exception
 */
function displayCheckout() {

    $user_id = fusion_get_userdata('user_id');
    $info = self::getUserWallet($user_id);

    if (!empty($info)) {

        $gateway = new Gateways();
        $gateway->set_wallet($info);
        $options['user'] = $info;
        if ($CurrentDriver = get("payment_method")) { // This is the $_GET
            if ($CurrentDriver == 'credit') {
                return self::credit_checkout($options);
            } else {
                $driver = $gateway->getPaymentMethods($CurrentDriver);
                if (!empty($driver)) {
                    if (isset($driver['callback_charge_function'])) {
                        $checkout_driver = $gateway->loadDriver($CurrentDriver);
                        $checkout_function = $driver['callback_charge_function'];
                        return $checkout_driver->$checkout_function($options);
                    }
                } else {
                    Core::setParam('footer', FALSE);
                    Core::setParam('copyright', FALSE);
                    add_notice('danger', "Payment method error. Please choose another payment method");
                }
            }
        } else {
            add_notice('danger', "Payment method error. Please choose another payment method");
        }
    } else {

        $html = Template::getInstance('wallet_error');

        $html->set_template(__DIR__.'/../templates/payment/error.html');

        $html->set_tag('title', "Please Register");

        $html->set_tag('description', "You need to register or be logged in to make a purchase.");

        $html->set_tag('button_text', "Login to your Account");

        $html->set_tag('back_link', BASEDIR.'login.php');

        return (string) $html->get_output();
    }
}
