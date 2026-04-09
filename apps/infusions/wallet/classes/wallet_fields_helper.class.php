<?php

namespace PHPFusion\Infusions\Wallet\Classes;

use PHPFusion\Geomap;
use PHPFusion\Template;

class Wallet_Fields_Helper {

    private $wallet = [];

    public function __construct(Wallet $wallet) {
        $this->wallet = $wallet->getUserWallet(fusion_get_userdata('user_id'));
    }

    public function validate_nameInputFields() {
        if (post('save_name')) {
            $this->wallet['first_name'] = sanitizer('first_name', '', 'first_name');
            $this->wallet['last_name'] = sanitizer('last_name', '', 'last_name');
            if (fusion_safe()) {
                dbquery_insert(DB_USER_WALLET, $this->wallet, 'update');
                add_notice('success', 'Your name has been updated successfully');
                redirect(FUSION_REQUEST);
            }
        }
    }

    public function validate_addressInputFields() {
        if (post('save_address')) {
            // we can use different names now.
            $address_input = sanitizer(['address'], '', 'address');
            // ok now we need to split to column
            if (!empty($address_input)) {
                list($this->wallet['address'],
                    $this->wallet['address_2'],
                    $this->wallet['country'],
                    $this->wallet['region'],
                    $this->wallet['city'],
                    $this->wallet['postcode']
                    ) = explode('|', $address_input);
            }
            if (fusion_safe()) {
                //print_p($wallet);
                dbquery_insert(DB_USER_WALLET, $this->wallet, 'update');
                add_notice('success', "Your billing address has been updated successfully");
                redirect(FUSION_REQUEST);
            }
        }
    }

    public function validate_phoneInputFields() {
        $calling_codes = "(+".Geomap::get_CallingCodes($this->wallet['country']).")";
        if (post('save_phone')) {
            $this->wallet['phone'] = sanitizer('phone', '', 'phone');
            $this->wallet['phone_cc'] = $calling_codes;
            if (fusion_safe()) {
                dbquery_insert(DB_USER_WALLET, $this->wallet, 'update');
                add_notice('success', "Your phone number has been updated successfully");
                redirect(FUSION_REQUEST);
            }
        }
    }

    public function validate_mobileInputFields() {
        $calling_codes = "(+".Geomap::get_CallingCodes($this->wallet['country']).")";
        if (post('save_phone')) {
            $this->wallet['mobile'] = sanitizer('phone', '', 'phone');
            $this->wallet['mobile_cc'] = $calling_codes;
            if (fusion_safe()) {
                dbquery_insert(DB_USER_WALLET, $this->wallet, 'update');
                add_notice('success', "Your phone number has been updated successfully");
                redirect(FUSION_REQUEST);
            }
        }
    }

    public function validate_faxInputFields() {
        $calling_codes = "(+".Geomap::get_CallingCodes($this->wallet['country']).")";
        if (post('save_phone')) {
            $this->wallet['fax'] = sanitizer('phone', '', 'phone');
            $this->wallet['fax_cc'] = $calling_codes;
            if (fusion_safe()) {
                dbquery_insert(DB_USER_WALLET, $this->wallet, 'update');
                add_notice('success', "Your phone number has been updated successfully");
                redirect(FUSION_REQUEST);
            }
        }
    }

    /**
     * Name field
     *
     * @return string
     * @throws \ReflectionException
     */
    public function show_nameInputFields() {

        $input_value = [
            0 => $this->wallet['first_name'],
            1 => $this->wallet['last_name']
        ];

        if (isset($input_value) && (!empty($input_value))) {
            if (!is_array($input_value)) {
                $input_value = construct_array($input_value, "", "|");
            }
        } else {
            $input_value['0'] = "";
            $input_value['1'] = "";
        }

        $html = "<h6 class='text-uppercase strong text-dark m-0'>Name</h6>";
        $html .= "<div class='row'>\n";
        $html .= "<div class='col-xs-12 col-sm-6 m-b-10'>\n";
        $html .= form_text('first_name', 'First Name', $input_value[0], [
            'required'   => TRUE,
            'class'      => 'label-float',
            'error_text' => 'You need to fill in your first name'
        ]);
        $html .= "</div>\n<div class='col-xs-12 col-sm-6 m-b-10'>\n";
        $html .= form_text('last_name', 'Last Name', $input_value[1], [
            'required'   => TRUE,
            'class'      => 'label-float',
            'error_text' => 'You need to fill in your last name'

        ]);
        $html .= "</div>\n</div>\n";

        return (string)$html;
    }

    /**
     * Address Field
     *
     * @return string
     */
    public function show_addressInputFields() {

        $input_name = 'address';

        $label = [
            0 => 'Unit No, Block No, Apartment Unit',
            1 => 'Street',
            2 => 'Country',
            3 => 'State/Region',
            4 => 'City',
            5 => 'Postcode/Zipcode'
        ];
        $options['required'] = TRUE;
        $options['class'] = 'label-float filled';

        $input_value = [
            0 => $this->wallet['address'],
            1 => $this->wallet['address_2'],
            2 => $this->wallet['country'],
            3 => $this->wallet['region'],
            4 => $this->wallet['city'],
            5 => $this->wallet['postcode']
        ];

        $html = "<h6 class='text-uppercase strong text-dark'>Billing Address</h6>";
        $html .= form_geo($input_name, $label, $input_value, $options);

        return (string)$html;
    }

    // Email field
    public static function show_emailInputFIeld($input_value) {

    }

    // Phone field
    public function show_phoneInputFields() {
        $calling_codes = "(+".Geomap::get_CallingCodes($this->wallet['country']).")";
        $html = "<h6 class='text-uppercase strong text-dark'>Contact</h6>";
        $html .= form_text('phone', 'Phone Number '.$calling_codes, $this->wallet['phone'], ['required' => FALSE, 'class' => 'label-float']);

        return (string)$html;
    }

    // Fax field
    public function show_faxInputFields() {
        $calling_codes = "(+".Geomap::get_CallingCodes($this->wallet['country']).")";
        $html = form_text('fax', 'Fax Number '.$calling_codes, $this->wallet['fax'], ['required' => FALSE, 'class' => 'label-float']);

        return (string)$html;
    }

    // Mobile field
    public function show_mobileInputFields() {
        $calling_codes = "(+".Geomap::get_CallingCodes($this->wallet['country']).")";
        $html = form_text('mobile', 'Mobile Number '.$calling_codes, $this->wallet['mobile'], ['required' => TRUE, 'class' => 'label-float']);

        return (string)$html;
    }

    /**
     * Credit Card Form
     * @param array $cc_info
     * @param bool  $test_field
     *
     * @return string
     * @throws \ReflectionException
     */
    public function show_ccInputForm($cc_info = [], $test_field = FALSE) {

        $default_info = [
            'card_no'     => sanitizer('card_no', '', 'card_no'),
            'card_exp_1'  => sanitizer('cart_exp_1', '', 'card_exp_1'),
            'card_exp_2'  => sanitizer('cart_exp_2', '', 'card_exp_2'),
            'card_holder' => sanitizer('card_holder', '', 'card_holder'),
            'card_CVV2'   => sanitizer('card_CVV2', '', 'card_CVV2'),
            //'card_issued'   => sanitizer('card_issued', '', 'card_issued'),
        ];

        $cc_info += $default_info;

        $test_card_info = [
            //'card_type'   => 'VISA',
            'card_no'     => '4444333322221111',
            'card_exp_1'  => '09',
            'card_exp_2'  => date('Y') + 3,
            'card_holder' => 'Frederick Chan',
            //'card_issued' => 'Any Bank',
            'card_CVV2'   => '123',
        ];

        if ($test_field === TRUE) {
            $cc_info = $test_card_info;
        }

        $currentYear = date('Y');
        $nextYears = $currentYear + 10;
        $year_opts = [];
        $years = range($currentYear, $nextYears);
        foreach ($years as $year) {
            $year_opts[$year] = $year;
        }

        $month_opts = [];
        $months = range(1, 12);
        $months_word = fusion_get_locale('shortmonths');
        $months_word = explode('|', $months_word);

        foreach ($months as $month) {
            $month_opts[str_pad($month, 2, '0', STR_PAD_LEFT)] = $months_word[$month];
        }
        $html = "<h6 class='text-uppercase strong text-dark'>Credit Card Details</h6>";
        $html .= form_text('card_holder', 'Name written on the card', $cc_info['card_holder'], [
            'class'       => 'label-float',
            'placeholder' => 'The written name on Card',
            'required'    => TRUE
        ]);
        $html .= "<div class='clearfix'>";
        $html .= "<div class='display-inline-block pull-right m-l-10'>";
        $html .= form_text('card_CVV2', 'CVV/CVC Number', $cc_info['card_CVV2'], [
            'class'      => 'label-float m-b-0',
            'type'       => 'number',
            'max_length' => 3,
            'width' => '180px',
            'inner_width' => '180px',
            'required' => TRUE,
        ]);
        $html .= "<a id='ccv_m' href='#' class='small'>What is this?</a>";
        $html .= "</div><div class='overflow-hide'>";
        $html .= form_text('card_no', 'Card Number', $cc_info['card_no'], [
            'placeholder' => 'Valid Card Number',
            'class'       => 'label-float m-b-0',
            'required'    => TRUE,
            'ext_tip'     => '',
            'stacked'     => '<!-- Card Images Output --><div id="accepted-cards-images" class="display-inline-block"></div> <span id="credit-card-type-text" class="pull-right small strong text-success"></span>',
        ]);
        $html .= "</div></div>";

        $html .= "<div class='clearfix'>";
        $html .= "<div class='display-inline-block m-r-10'>";
        $html .= form_select('card_exp_1', 'Exp Month', $cc_info['card_exp_1'], [
            'inner_width'      => '150px',
            'class'            => 'label-float',
            'options'          => $month_opts,
            'select2_disabled' => FALSE,
        ]);
        $html .= "</div><div class='display-inline-block m-r-10'>";
        $html .= form_select('card_exp_2', 'Exp Year', $cc_info['card_exp_2'], [
            "width"            => "150px",
            'inner_width'      => "150px",
            "class"            => "label-float",
            'select2_disabled' => FALSE,
            'options'          => $year_opts,
        ]);
        $html .= "</div></div>";
        $html .= $this->displayCvvInfo();
        //$card_issued_field = form_text('card_issued', 'Card Issuing Bank', $cc_info['card_issued'], ['placeholder' => 'The issuer Bank Name', 'required' => TRUE]);

        add_to_jquery("
        var creditCard = $('#card_no'), 
        
        cp = creditCard.parent(), 
        
        cardType = $('#credit-card-type-text');
                
        creditCard.on('cc:onReset cc:onGuess', function() {
                
                cp.removeClass().addClass('form-group label-float filled');
                
        }).on('cc:onInvalid', function() {
            
            cp.removeClass().addClass('form-group has-error filled label-float');
            cardType.text('');
              
        }).on('cc:onValid', function(event, card, xhrName) {
            
            cp.removeClass().addClass('form-group label-float filled success');
            
        }).on('cc:onCardChange', function(event, card, xhrName) {   
            
            cardType.text(xhrName);
                       
        }).cardcheck({ iconLocation: '#accepted-cards-images' });
        ");

        $html .= "<script src='".INCLUDES."jquery/ccard/ccard.min.js'></script>";

        return $html;

    }

    // DOB field

    //

    /**
     * Modal for information
     */

    private function displayCvvInfo() {

        require_once(INCLUDES.'theme_functions_include.php');

        $cvv_template = __DIR__.'/../templates/payment/info/cvv.html';
        $msc_info_template = __DIR__.'/../templates/payment/info/mastercard_more.html';
        $visa_info_template = __DIR__.'/../templates/payment/info/visa_more.html';

        $modal1html = Template::getInstance('mastercardsc');
        $modal1html->set_template($cvv_template);
        $modal1html->set_tag("image_1", WALLET."drivers/firstdata/images/logo-visa-lg.png");
        $modal1html->set_tag("image_2", WALLET."drivers/firstdata/images/logo-mastercard-lg.png");
        $modal1html->set_tag("image_3", WALLET."drivers/firstdata/images/logo-amex-lg.png");
        $modal_1 = openmodal("ccv_info", "<h4 class='m-t-10 m-b-0 strong'>Credit Verification Value/Card Verification Code (CVV/CVC)</h4>", [
            'button_id' => "ccv_m"]);
        $modal_1 .= $modal1html->get_output();
        $modal_1 .= closemodal();
        add_to_footer($modal_1);

        $modal2html = Template::getInstance('mastercardsc');
        $modal2html->set_template($msc_info_template);
        $modal2html->set_tag("image_1", WALLET.'drivers/firstdata/images/msc_learn_more_1.gif');
        $modal2html->set_tag("image_2", WALLET.'drivers/firstdata/images/msc_learn_more_2.gif');
        $modal2html->set_tag("image_3", WALLET.'drivers/firstdata/images/msc_learn_more_3.gif');
        $modal_2 = openmodal("mss_info", "<h4 class='m-t-10 m-b-0 strong'>Mastercard SecureCode</h4>", ['button_id' => "mss_btn"]);
        $modal_2 .= $modal2html->get_output();
        $modal_2 .= closemodal();
        add_to_footer($modal_2);

        $modal3html = Template::getInstance('visasc');
        $modal3html->set_template($visa_info_template);
        $modal3html->set_tag("image_1", WALLET.'drivers/firstdata/images/msv_learn_more.png');
        $modal_3 = openmodal("msv_info", "<h4 class='m-t-10 m-b-0 strong'>Verified by Visa</h4>", ['button_id' => "msv_btn"]);
        $modal_3 .= $modal3html->get_output();
        $modal_3 .= closemodal();
        add_to_footer($modal_3);
    }

}
