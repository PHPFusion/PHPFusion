<?php

namespace PHPFusion\Infusions\Wallet\Classes\Account;

use PHPFusion\Geomap;
use PHPFusion\Infusions\Account\Classes\Dashboard;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;
use PHPFusion\Template;

/**
 * Class Wallet_Account
 *
 * @package PHPFusion\Infusions\Wallet\Classes\Account
 */
class Wallet_Account extends Wallet_Model {

    private $default_section = 'basic_info';

    public function firstTimeRegisterForm() {

        $default_country = '';

        $tel_prefix = [
            'prepend'       => TRUE,
            'prepend_value' => '+'.Geomap::get_CallingCodes('AW'),
        ];

        if (!empty($user_country['country'])) {

            $country_code = $user_country['country']['iso_code']; // this is what we want

            $default_country = $country_code;

            $calling_codes = Geomap::get_CallingCodes($country_code);
            // Bind to dynamics $options var
            $tel_prefix = [
                'prepend'       => TRUE,
                'prepend_value' => "+$calling_codes"
            ];
        }

        $data = [
            'email'              => fusion_get_userdata('user_email'),
            'type'               => 1,
            'last_name'          => '',
            'first_name'         => '',
            'job_title'          => '',
            'mobile'             => '',
            'phone'              => '',
            'fax'                => '',
            'company'            => '',
            'company_period'     => '',
            'company_employees'  => '',
            'company_industry'   => '',
            'company_product'    => '',
            'web'                => '',
            'country'            => $default_country,
            'postcode'           => '',
            'newsletter'         => 0,
            'tou'                => 0,
            'marketing_disabled' => 0,
            'tou'                => 0,
        ];

        $billing_address = ['', '', '', '', '', ''];

        // Save goes here

        if (post('save_info')) {

            $data = [
                'last_name'          => '',
                'first_name'         => '',
                'company'            => '',
                'company_period'     => '',
                'company_employees'  => '',
                'company_industry'   => '',
                'company_product'    => '',
                'user_id'            => fusion_get_userdata('user_id'),
                'email'              => sanitizer('email', '', 'email'),
                'type'               => sanitizer('type', '', 'type'),
                'job_title'          => sanitizer('job_title', '', 'job_title'),
                'mobile'             => sanitizer('mobile', '', 'mobile'),
                'phone'              => sanitizer('phone', '', 'phone'),
                'fax'                => sanitizer('fax', '', 'fax'),
                'web'                => sanitizer('web', '', 'web'),
                'address'            => sanitizer(['address'], '', 'address'),
                'marketing_disabled' => check_post('marketing_disabled') ? 1 : 0,
                'lastupdate'         => TIME,
                'tou'                => check_post('tou') ? 1 : 0,
            ];

            $billing_address = explode('|', $data['address']);
            list($data['address'], $data['address_2'], $data['country'], $data['region'], $data['city'], $data['postcode']) = $billing_address;

            if ($data['type'] == 1) {
                $data['last_name'] = form_sanitizer($_POST['last_name'], '', 'last_name');
                $data['first_name'] = form_sanitizer($_POST['first_name'], '', 'first_name');

                $post_check = [
                    'first_name' => 'First name is a required field',
                    'last_name'  => 'Last name is a required field',
                ];
                foreach ($post_check as $field => $warning) {
                    if (empty($data[$field])) {
                        fusion_stop();
                        add_notice('danger', $warning);
                    }
                }

            } else {

                $data['company'] = form_sanitizer($_POST['company'], '', 'company');
                $data['company_period'] = form_sanitizer($_POST['company_period'], '', 'company_period');
                $data['company_employees'] = form_sanitizer($_POST['company_employees'], '', 'company_employees');
                $data['company_industry'] = form_sanitizer($_POST['company_industry'], '', 'company_industry');
                $data['company_product'] = form_sanitizer($_POST['company_product'], '', 'company_product');
                $post_check = [
                    'company'           => 'Company Name is a required field',
                    'company_period'    => 'Company Period is a required field',
                    'company_employees' => 'Company employees is a required field.',
                    'company_industry'  => 'Company employees is a required field.',
                    'company_product'   => 'Company employees is a required field.'
                ];
                foreach ($post_check as $field => $warning) {
                    if (empty($data[$field])) {
                        fusion_stop();
                        add_notice('danger', $warning);
                    }
                }
            }

            if (!check_post('tou')) {
                add_notice("danger", "You need to check and agree to the Terms and Conditions of use.");
                fusion_stop();
            }

            if (fusion_safe()) {
                $cc_prefix = Geomap::get_CallingCodes($data['country']);
                $data['mobile_cc'] = $cc_prefix;
                $data['phone_cc'] = $cc_prefix;
                $data['fax_cc'] = $cc_prefix;

                dbquery_insert(DB_USER_WALLET, $data, 'save');
                add_notice("success", "Your Fusion Coin Account has been successfully activated.");
                redirect(FUSION_REQUEST);
            }
        }

        $info = [
            'form' => [
                'openform'          => openform('ftregfrm', 'post'),
                'closeform'         => closeform(),
                'email'             => form_text('email', 'Account Email', $data['email'], ['inline' => TRUE]),
                'type'              => form_checkbox(
                    'type', 'Account Type', $data['type'], [
                        'inline' => TRUE, 'options' => $this->get_fusion_membership(), 'type' => 'radio'
                    ]
                ),
                'job_title'         => form_text(
                    'job_title', 'Occupation/Job Title', $data['job_title'], ['inline' => TRUE]
                ),
                'last_name'         => form_text(
                    'last_name', 'Last Name: <span class="required">*</span>', $data['last_name'], ['inline' => TRUE]
                ),
                'first_name'        => form_text(
                    'first_name', 'First Name:  <span class="required">*</span>', $data['first_name'],
                    ['inline' => TRUE]
                ),
                'mobile'            => form_text(
                    'mobile', 'Mobile No:', $data['mobile'],
                    ['inline' => TRUE, 'required' => TRUE, 'type' => 'number'] + $tel_prefix
                ),
                'phone'             => form_text(
                    'phone', 'Phone No:', $data['phone'],
                    ['inline' => TRUE, 'required' => FALSE, 'type' => 'number'] + $tel_prefix
                ),
                'fax'               => form_text(
                    'fax', 'Fax No:', $data['fax'],
                    ['inline' => TRUE, 'required' => FALSE, 'type' => 'number'] + $tel_prefix
                ),
                'company'           => form_text(
                    'company', 'Company Name: <span class="required">*</span>', $data['company'], ['inline' => TRUE]
                ),
                'company_period'    => form_select(
                    'company_period', 'Operation Period: <span class="required">*</span>', $data['company_period'], [
                        'options' => $this->get_company_period(),
                        'inline'  => TRUE,
                    ]
                ),
                'company_employees' => form_select(
                    'company_employees', 'Enterprise Scale: <span class="required">*</span>',
                    $data['company_employees'], [
                        'options' => $this->get_company_employees(),
                        'inline'  => TRUE,
                    ]
                ),
                'geo'               => form_geo(
                    'address', 'Billing Address', $billing_address, ['required' => TRUE, 'inline' => TRUE]
                ),
                'company_industry'  => form_select(
                    'company_industry', 'Primary Business: <span class="required">*</span>', $data['company_industry'],
                    [
                        'options'    => $this->get_company_industry(),
                        'chained_to' => 'company_business',
                        'inline'     => TRUE,
                    ]
                ),
                'company_product'   => form_text(
                    'company_product', 'Main Product: <span class="required">*</span>', $data['company_product'], [
                        'placeholder' => 'Product Name',
                        'inline'      => TRUE,
                    ]
                ),
                'web'               => form_text(
                    'web', 'Website', $data['web'], [
                        'placeholder' => 'www.yourwebsite.com',
                        'inline'      => TRUE,
                    ]
                ),
                'button'            => form_button(
                    'save_info', 'Create a Wallet Account', 'save_info',
                    ['class' => 'btn-primary m-b-20 col-xs-offset-3']
                ),
                'newsletter'        => form_checkbox(
                    'marketing_disabled', 'PHP-Fusion may not call me to discusss deals and offers.',
                    $data['marketing_disabled'],
                    ['reverse_label' => TRUE, 'type' => 'checkbox', 'class' => 'text-left']
                ),
                'tou'               => form_checkbox(
                    'tou', 'Please check the Terms of Use & Conditions of PHP-Fusion Coin', $data['tou'],
                    [
                        'reverse_label' => TRUE,
                        'type'          => 'checkbox',
                        'error_text'    => 'In order to activate and use your PHP-Fusion Coin, you need to agree to the terms and conditions',
                        'ext_tip'       => 'I hereby agree to the PHP-Fusion International Website Membership Agreement, Privacy Policy, Product Terms and Terms of Use, under which I am contracting with PHP-Fusion (Malaysia) Private Limited.',
                        'class'         => 'text-left'
                    ]
                )
            ]
        ];


        $twig = twig_init(WALLET.'templates/', TRUE);
        $output = $twig->render('registration.twig', $info);


        $js = ($data['type'] == 1 ? "$('#individual-container').show();" : "$('#company-container').show();");
        $js .= "
        $('#type-field input[type=\"radio\"]').bind('click', function(e) {
            $('#individual-container').show();
            $('#company-container').hide();
            if ($(this).val() == 2) {
                $('#company-container').show();
                $('#individual-container').hide();
            }
        });
        ";
        $js .= "$('#country').bind('change', function(e) {
            $.ajax({
                url: '".fusion_get_settings('site_path').CLASSES."PHPFusion/Geomap/ajax/CallingCodes.php',
                dataType: 'json',
                method : 'GET',
                type: 'json',
                data: {q : $(this).val()},
                success: function(e) {
                    $('#p-mobile-prepend, #p-phone-prepend, #p-fax-prepend').text('+'+ e);
                },
                error : function(e) {
                    console.log('Could not fetch document');
                }
            });
        });";

        // Change Country Code Prefix
        $js .= /*@language=Javascript*/
            "$('#address-country-field').bind('change', function(e) {
            var country_code = e.val;
            $.ajax({
            method: 'GET',
            url: INFUSIONS + 'wallet/classes/ajax/get_phone_prefix.php',
            data: { country: country_code },
            dataType: 'json',
            success: function(response) {
                $('#mobile-field .input-group-addon').text(response.phone_prefix);
                $('#phone-field .input-group-addon').text(response.phone_prefix);
                $('#fax-field .input-group-addon').text(response.phone_prefix);     
            },
            error: function(response) {
                alert('Country Code could not be obtained. Please make sure you have selected the correct country.');
            }
            });
        });
        ";

        add_to_jquery($js);

        return $output;
    }

    /**
     * Basic Information Form
     *
     * @return string
     * @throws \ReflectionException
     */
    public function walletBasicForm() {
        $locale = fusion_get_locale();

        $field_inline = TRUE;

        // change this
        $default_country = $this->pocket['country'];

        $calling_codes = Geomap::get_CallingCodes($default_country);

        // Bind to dynamics $options var
        $tel_prefix = [
            'prepend'       => TRUE,
            'prepend_value' => "+$calling_codes"
        ];

        $data = [
            'first_name'        => $this->pocket['first_name'],
            'last_name'         => $this->pocket['last_name'],
            'email'             => $this->pocket['email'],
            'job_title'         => $this->pocket['job_title'],
            'company_period'    => $this->pocket['company_period'],
            'company_employees' => $this->pocket['company_employees'],
            'company_industry'  => $this->pocket['company_industry'],
            'company_product'   => $this->pocket['company_product'],
            'web'               => $this->pocket['web'],
            'country'           => $this->pocket['country'],
            'address'           => $this->pocket['address'],
            'address_2'         => $this->pocket['address_2'],
            'city'              => $this->pocket['city'],
            'postcode'          => $this->pocket['postcode'],
            'region'            => $this->pocket['region'],
            'phone'             => $this->pocket['phone'],
            'mobile'            => $this->pocket['mobile'],
            'fax'               => $this->pocket['fax']
        ];

        if (post('save_info')) {

            $data = [
                'wallet_id'         => $this->pocket['wallet_id'],
                'last_name'         => $this->pocket['last_name'],
                'first_name'        => $this->pocket['first_name'],
                'company'           => $this->pocket['company'],
                'company_period'    => $this->pocket['company_period'],
                'company_employees' => $this->pocket['company_employees'],
                'company_industry'  => $this->pocket['company_industry'],
                'company_product'   => $this->pocket['company_product'],
                'email'             => sanitizer('email', '', 'email'),
                'job_title'         => sanitizer('job_title', '', 'job_title'),
                'mobile'            => sanitizer('mobile', '', 'mobile'),
                'phone'             => sanitizer('phone', '', 'phone'),
                'fax'               => sanitizer('fax', '', 'fax'),
                'web'               => sanitizer('web', '', 'web'),
                'address'           => '',
                'address_2'         => '',
                'region'            => '',
                'country'           => '',
                'city'              => '',
                'postcode'          => '',
                'lastupdate'        => TIME,
            ];

            $geo = sanitizer(['user_address'], '', 'user_address');
            $geo = explode('|', $geo);
            list($data['address'], $data['address_2'], $data['country'], $data['region'], $data['city'], $data['postcode']) = $geo;

            if ($this->pocket['type'] == 1) {
                $data['last_name'] = sanitizer('last_name', '', 'last_name');
                $data['first_name'] = sanitizer('first_name', '', 'first_name');
            } else {
                $data['company_period'] = sanitizer('company_period', '', 'company_period');
                $data['company_employees'] = sanitizer('company_employees', '', 'company_employees');
                $data['company_industry'] = sanitizer('company_industry', '', 'company_industry');
                $data['company_product'] = sanitizer('company_product', '', 'company_product');
            }

            if (fusion_safe()) {
                dbquery_insert(DB_USER_WALLET, $data, 'update');
                add_notice("success", "Your information has been successfully updated");
                redirect(FUSION_REQUEST);
            } else {
                add_notice("danger", "Something went wrong..");
            }

        }

        $tpl = Template::getInstance('wallet-basic-form');

        $tpl->set_template(__DIR__.'/../../templates/basic_form.html');

        //$tpl->set_tag('openform', '');
        //$tpl->set_tag('closeform', '');
        $tpl->set_tag('openform', openform('basicWalletForm', 'post'));
        $tpl->set_tag('closeform', closeform());

        $tpl->set_tag('button', form_button('save_info', 'Save Changes', 'save_info', ['class' => 'btn-primary']));

        $tpl->set_tag('type', $this->get_fusion_membership($this->pocket['type']));

        $tpl->set_tag('job_title', $this->pocket['job_title']);

        $tpl->set_tag('web', $this->pocket['web'] ?: $locale['na']);

        if ($this->pocket['type'] == 1) {
            $tpl->set_block(
                'individual', [
                    'first_name' => form_text(
                        'first_name', '', $this->pocket['first_name'],
                        ['required' => TRUE, 'placeholder' => 'First Name']
                    ),
                    'last_name'  => form_text(
                        'last_name', '', $this->pocket['last_name'],
                        ['required' => TRUE, 'placeholder' => 'Last Name']
                    ),
                ]
            );
        } else {
            $tpl->set_block(
                'company',
                [
                    'company'           => form_text(
                        'company', 'Organization Name', $this->pocket['company'],
                        ['required' => TRUE, 'inline' => $field_inline]
                    ),
                    'company_period'    => form_select(
                        'company_period', 'Operation Period', $data['company_period'], [
                            'options'  => $this->get_company_period(),
                            'inline'   => $field_inline,
                            'required' => TRUE
                        ]
                    ),
                    'company_employees' => form_select(
                        'company_employees', 'Enterprise Scale', $data['company_employees'], [
                            'options'  => $this->get_company_employees(),
                            'inline'   => $field_inline,
                            'required' => TRUE
                        ]
                    ),
                    'company_industry'  => form_select(
                        'company_industry', 'Primary Business', $data['company_industry'], [
                            'options'    => $this->get_company_industry(),
                            'chained_to' => 'company_business',
                            'inline'     => $field_inline,
                            'required'   => TRUE,
                        ]
                    ),
                    'company_product'   => form_text(
                        'company_product', 'Main Product', $data['company_product'], [
                            'placeholder' => 'Product Name',
                            'inline'      => $field_inline,
                            'required'    => TRUE,
                        ]
                    ),
                ]
            );
        }

        $tpl->set_tag(
            'web', form_text(
                'web', 'Website', $data['web'], ['placeholder' => 'www.yourwebsite.com', 'inline' => $field_inline]
            )
        );

        $tpl->set_tag(
            'email', form_text(
                'email', 'Email:', $data['email'],
                ['inline' => $field_inline, 'required' => TRUE, 'type' => 'email']
            )
        );

        $tpl->set_tag(
            'mobile', form_text(
                'mobile', 'Mobile No:', $data['mobile'],
                ['inline' => $field_inline, 'required' => TRUE, 'type' => 'number'] + $tel_prefix
            )
        );

        $tpl->set_tag(
            'phone', form_text(
                'phone', 'Phone No:', $data['phone'],
                ['inline' => $field_inline, 'required' => FALSE, 'type' => 'number'] + $tel_prefix
            )
        );

        $tpl->set_tag(
            'fax', form_text(
                'fax', 'Fax No:', $data['fax'],
                ['inline' => $field_inline, 'required' => FALSE, 'type' => 'number'] + $tel_prefix
            )
        );

        $tpl->set_tag(
            'job_title', form_text(
                'job_title', 'Job Title:', $data['job_title'],
                ['inline' => $field_inline, 'required' => FALSE]
            )
        );

        $tpl->set_tag(
            'geo', form_geo(
                'user_address', 'Address:', [
                $data['address'],
                $data['address_2'],
                $data['country'],
                $data['region'],
                $data['city'],
                $data['postcode'],
            ], [
                    'inline' => TRUE,
                ]
            )
        );

        return (string)$tpl->get_output();
    }

    /**
     * Account Security Form
     *
     * @return string
     */
    public function walletSecurityForm() {

        if (isset($_POST['close_account'])) {
            if (isset($_POST['dac_check']) && fusion_safe()) {
                if (dbcount(
                    "(wallet_id)", DB_USER_WALLET, "`wallet_id`=:wallet_id",
                    [':wallet_id' => $this->pocket['wallet_id']]
                )) {
                    dbquery(
                        "DELETE FROM ".DB_USER_WALLET." WHERE `wallet_id`=:wallet_id",
                        [':wallet_id' => $this->pocket['wallet_id']]
                    );
                    if (dbcount(
                        "(validate_id)", DB_USER_WALLET_VERIFICATION, "`user_id`=:user_id",
                        [':user_id' => $this->pocket['user_id']]
                    )) {
                        dbquery(
                            "DELETE FROM ".DB_USER_WALLET_VERIFICATION." WHERE `wallet_id`=:wallet_id",
                            [':wallet_id' => $this->pocket['wallet_id']]
                        );
                    }
                    add_notice("success", "Your PHP-Fusion Coin Account has been closed and terminated.");
                    redirect(FUSION_REQUEST);
                }
            }
        }

        $tpl = Template::getInstance('security_form');
        $tpl->set_template(__DIR__.'/../../templates/security_form.html');

        // Verification Status Output
        switch ($this->pocket['verified']) {
            case 2: // Under Review
                $case = "Account Identity Under Review";
                $class = "label label-default label-bordered";
                break;
            case 1:
                $case = "You have passed identity verification";
                $class = "label label-success label-bordered";
                break;
            default:
                $case = "Unverified Account";
                $class = "label label-default label-bordered";
        }
        $tpl->set_tag('verification_status', "<span class='$class'>$case</span>");

        $tpl->set_tag('avatar', display_avatar(fusion_get_userdata(), '100px', '', FALSE, 'img-rounded'));
        $tpl->set_tag('avatar_link', clean_request('change_avatar=true', ['change_avatar'], FALSE));
        $username = fusion_get_userdata('user_name');
        $first_char = $username[0];
        $length = strlen($username);
        $char = '';
        for ($i = 2; $i <= $length; $i++) {
            $char .= "*";
        }
        $username = $first_char.$char;
        $tpl->set_tag('user_name', $username);
        $tpl->set_tag('user_id', fusion_get_userdata('user_id'));
        $tpl->set_tag('user_joined', showdate('longdate', fusion_get_userdata('user_joined')));
        $tpl->set_tag('dac_openform', openform('dacfrm', 'post', FUSION_REQUEST));
        $tpl->set_tag(
            'dac_checkbox', form_checkbox(
                'dac_check', "I understand that by clicking this checkbox my PHP-Fusion Coin Account will be removed and all balance value and contents associated to this account will be forfeited.<br/>
        Please ensure that your hosting account if any available is terminated. All your contents in this site may be removed, and if they are not, will be made anonymized.<br/>
        Note that the account cannot be reopened nor recovered once deleted.", '', [
                    'reverse_label' => TRUE, 'class' => 'text-left',
                ]
            )
        );
        $tpl->set_tag(
            'dac_button', form_button(
                'close_account', "Delete Account", 'close_account',
                ['class' => 'btn-danger', 'deactivate' => TRUE]
            )
        );
        $tpl->set_tag('dac_closeform', closeform());
        add_to_jquery(
            "
        $('#close_account').bind('click', function(e) {
           if (confirm('WARNING: Confirm deleting PHP-Fusion Coin Account? This action is irreversible!')) {
                return true;
            } else {
                return false;
            }
        });
        $('#dac_check').bind('click', function(e) {
            var is_checked = $(this).prop('checked');
            if (is_checked) {
                $('#close_account').removeClass('disabled').prop('disabled', false);
            } else {
                $('#close_account').addClass('disabled').prop('disabled', true);
            }
        });
        "
        );

        if (isset($_GET['change_avatar'])) {
            Dashboard::getAvatarConsole();
        }

        return $tpl->get_output();

    }

    /**
     * The profile verification form
     *
     * @return string
     * @throws \ReflectionException
     */
    public function walletIdentityForm() {

        $tpl = Template::getInstance('real_form');

        $tpl->set_template(__DIR__.'/../../templates/real_form.html');

        $data = [
            'type'        => $this->pocket['type'],
            'country'     => $this->pocket['country'],
            'mobile'      => $this->pocket['mobile'],
            'phone'       => $this->pocket['phone'],
            'address'     => $this->pocket['address'],
            'address_2'   => $this->pocket['address_2'],
            'address_3'   => $this->pocket['address_3'],
            'city'        => $this->pocket['city'],
            'region'      => $this->pocket['region'],
            'postcode'    => $this->pocket['postcode'],
            'company'     => $this->pocket['company'],
            'company_no'  => $this->pocket['company_no'],
            'first_name'  => $this->pocket['first_name'],
            'last_name'   => $this->pocket['last_name'],
            'identity_no' => $this->pocket['identity_no'],
        ];

        // Check if there is any document relevant here.
        $result = dbquery(
            "SELECT * FROM ".DB_USER_WALLET_VERIFICATION." WHERE validate_user_id=:uid",
            [':uid' => $this->pocket['user_id']]
        );
        if (dbrows($result)) {
            $l_data = dbarray($result);
            $data = \Defender::decode($l_data['validate_data']);
            switch ($l_data['validate_status']) {
                case 1:
                    $tpl->set_block('in_verification', []);
                    break;
                case 2:
                    $tpl->set_block('success_verification', []);
                    break;
                case 3:
                    $tpl->set_block(
                        'fail_verification', [
                            'reason'      => $this->get_validate_code($l_data['validate_code']),
                            'admin_notes' => !empty($l_data['validate_message']) ? nl2br(
                                $l_data['validate_message']
                            ) : ""
                        ]
                    );
                default:
            }
        }

        // Here we will not incorporate change. We will just save everything and wait for admin to approve within 24 hours.
        if (post('save_info') || post('form_id') == 'verification_frm') {

            $data = [
                "type"        => sanitizer('type', '', 'type'),
                "country"     => sanitizer('country', '', 'country'),
                "mobile"      => sanitizer('mobile', '', 'mobile'),
                "phone"       => sanitizer('phone', '', 'phone'),
                'company'     => '',
                'company_no'  => '',
                'first_name'  => '',
                'last_name'   => '',
                'identity_no' => '',
            ];

            if ($data['type'] == 2) {
                $data['company'] = sanitizer('company', '', 'company');
                $data['company_no'] = sanitizer('company_no', '', 'company_no');
            } else {
                $data["first_name"] = sanitizer('first_name', '', 'first_name');
                $data["last_name"] = sanitizer('last_name', '', 'last_name');
                $data["identity_no"] = sanitizer('identity_no', '', 'identity_no');
            }

            // parse address
            $geo = sanitizer(['geo'], '', 'geo');
            $geo = explode('|', $geo);
            list($data['address'], $data['address_2'], $data['country'], $data['region'], $data['city'], $data['postcode']) = $geo;

            if (!post('tou')) {
                \Defender::stop();
                add_notice(
                    'warning', "We cannot process the submitted information until you agree to the terms of use."
                );
            }

            if (fusion_safe()) {
                if (!empty($_FILES['fileinput']['tmp_name'])) {
                    $file_upload = form_sanitizer($_FILES['fileinput'], '', 'fileinput');
                    if (!empty($file_upload['image_name']) && empty($file_upload['error'])) {
                        $data['filename'] = $file_upload['image_name'];
                    }

                    if (fusion_safe()) {

                        $vdata = [
                            'validate_id'        => 0,
                            'validate_type'      => $data['type'],
                            'validate_user_id'   => fusion_get_userdata('user_id'),
                            'validate_data'      => \Defender::encode($data),
                            'validate_filename'  => $data['filename'],
                            'validate_datestamp' => TIME,
                            'validate_status'    => 0,
                        ];

                        // check if there are any previous submission.
                        if (dbcount(
                            "(validate_id)", DB_USER_WALLET_VERIFICATION, "validate_user_id=:uid",
                            [':uid' => $this->pocket['user_id']]
                        )) {
                            // delete old images
                            $file_result = dbquery(
                                "SELECT filename FROM ".DB_USER_WALLET_VERIFICATION." WHERE `validate_user_id`=:uid",
                                [':uid' => $this->pocket['user_id']]
                            );
                            if (dbrows($file_result)) {
                                while ($file_data = dbarray($file_result)) {
                                    $file_name = WALLET.'attachments/'.$file_data['filename'];
                                    if (is_file($file_name)) {
                                        @unlink($file_name);
                                    }
                                }
                            }
                            dbquery(
                                "DELETE FROM ".DB_USER_WALLET_VERIFICATION." WHERE validate_user_id=:uid",
                                [':uid' => $this->pocket['user_id']]
                            );
                        }

                        // Set the current user coin status as being pending for verified.
                        dbquery(
                            "UPDATE ".DB_USER_WALLET." SET `verified`=:two WHERE `wallet_id`=:wallet",
                            [':wallet' => $this->pocket['wallet_id'], ':two' => 2]
                        );


                        // Submit new information
                        dbquery_insert(DB_USER_WALLET_VERIFICATION, $vdata, 'save');
                        add_notice("success", "Your information has been successfully submitted for approval");
                        redirect(FUSION_REQUEST);
                    }
                } else {
                    add_notice("danger", "You need to upload a verification document.");
                }
            }
        }
        $membership_js = ($data['type'] == 1 ? "$('#individual-container').show();" : "$('#company-container').show();");
        $membership_js .= "$('#type-field input[type=\"radio\"]').bind('click', function(e) {
            $('#individual-container').show();
            $('#company-container').hide();
            if ($(this).val() == 2) {
                $('#company-container').show();
                $('#individual-container').hide();
            }
        });
        ";
        add_to_jquery($membership_js);

        $calling_codes = Geomap::get_CallingCodes($this->pocket['country']);

        $tel_prefix = [
            'prepend'       => TRUE,
            'prepend_value' => "+$calling_codes"
        ];
        $inline_field = TRUE;
        $tpl->set_tag('openform', openform('verification_frm', 'post', FORM_REQUEST, ['enctype' => TRUE]));
        $tpl->set_tag('closeform', closeform());

        $tpl->set_tag(
            'geo', form_geo(
                'geo', 'Billing Address',
                [$data['address'], $data['address_2'], $data['country'], $data['region'], $data['city'], $data['postcode']],
                ['required' => TRUE, 'inline' => TRUE]
            )
        );

        $tpl->set_tag(
            'type', form_checkbox(
                'type', 'Identity Registration', $data['type'], [
                    'options' => $this->get_fusion_membership(), 'type' => 'radio', 'inline' => $inline_field, 'deactivate' => FALSE
                ]
            )
        );

        $tpl->set_tag(
            'tou', form_checkbox(
                'tou', "I hereby confirm that the information above, including information provided at the time of registration of this PHP-Fusion Coin account, is complete, truthful and accurate, and will promptly provide PHP-Fusion Coin with written notice of any updates thereto. I consent to the collection, use, storage and disclosure of this information for the purposes of risk control and compliance with applicable law.
        I understand and consent to the transmission of this information to PHP Fusion Sdn Bhd in Malaysia for these purposes.
        ", (post('tou') ? 1 : 0), [
                    'class' => 'text-left', 'inline' => FALSE, 'reverse_label' => TRUE
                ]
            )
        );

        $tpl->set_tag(
            'mobile', form_text(
                'mobile', 'Mobile Number', $data['mobile'],
                ['inline' => $inline_field, 'required' => TRUE, 'type' => 'number'] + $tel_prefix
            )
        );

        $tpl->set_tag(
            'phone', form_text(
                'phone', 'Phone Number', $data['phone'],
                ['inline' => $inline_field, 'required' => FALSE, 'type' => 'number'] + $tel_prefix
            )
        );

        $tpl->set_tag('address_title', 'Company Registered Address');

        $tpl->set_tag(
            'company_name', form_text(
                'company', 'Company Name:', $data['company'],
                ['inline' => $inline_field, 'required' => TRUE]
            )
        );

        $tpl->set_tag(
            'company_no', form_text(
                'company_no', 'Registered No:', $data['company_no'],
                ['inline' => $inline_field, 'required' => TRUE]
            )
        );

        // Personal
        $tpl->set_tag(
            'first_name', form_text(
                'first_name', 'First Name:', $data['first_name'],
                ['required' => TRUE, 'inline' => $inline_field]
            )
        );

        $tpl->set_tag(
            'last_name', form_text(
                'last_name', 'Last Name:', $data['last_name'],
                ['required' => TRUE, 'inline' => $inline_field]
            )
        );

        $tpl->set_tag(
            'identity_no', form_text(
                'identity_no', 'Identification No:', $data['identity_no'],
                ['required' => TRUE, 'inline' => $inline_field]
            )
        );

        if (!empty($data['filename']) && is_file(WALLET.'attachments/'.$data['filename'])) {
            $tpl->set_block(
                'img_attach', [
                    'image' => colorbox(
                        WALLET.'attachments/'.$data['filename'], "Document Uploaded", TRUE,
                        "img-thumbnail"
                    )
                ]
            );
        }

        $tpl->set_tag(
            'fileinput_text',
            "<i class='fas fa-exclamation-triangle m-r-10'></i>Please upload the picture of the certification. <a href='#' id='certificate_info'>Please see the requirements and example here.</a>"
        );

        $tpl->set_tag(
            'fileinput', form_fileinput(
                'fileinput', '', '', [
                    'required'       => TRUE,
                    'upload_path'    => WALLET.'attachments/',
                    'max_byte'       => 5000000,
                    'max_width'      => 1600,
                    'max_height'     => 1600,
                    'replace_upload' => TRUE,
                    'hide_upload'    => TRUE,
                    'hide_remove'    => TRUE,
                    'inline'         => TRUE,
                    'class'          => 'm-b-0',
                    'template'       => 'modern'
                ]
            )
        );


        $tpl->set_tag('submit', form_button('save_info', 'Save Changes', 'save_info', ['class' => 'btn-primary']));

        $fmodal = openmodal('certInfoModal', 'Certification Photo Requirements', ['button_id' => 'certificate_info']);
        $fmodal .= "<img src='".WALLET."images/certificate_sample.png' class='img-responsive'/>\n<hr/>\n";
        $fmodal .= "Please submit a photo of your passport/identity card/ or driver license.<br/>Please see example above:";
        $fmodal .= "<ol class='spacer-sm'><li>Dimensions: 500 * 500 pixels minimum.</li><li>Color: Must be in color</li><li>File Format:< PDF, JPG or PNG only</li><li>File Size: 5 Megabytes(MB) maximum</li>";
        $fmodal .= closemodal();
        add_to_footer($fmodal);

        return $tpl->get_output();
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function walletPrivacyForm() {

        $data['marketing_disabled'] = $this->pocket['marketing_disabled'];

        if (isset($_POST['save_info']) || isset($_POST['form_id']) && $_POST['form_id'] === 'marketing_form') {
            $data['marketing_disabled'] = (isset($_POST['marketing_disabled']) ? 1 : 0); // 1 for opt out.
            dbquery(
                "UPDATE ".DB_USER_WALLET." SET `marketing_disabled`=:status",
                [':status' => $data['marketing_disabled']]
            );
            add_notice("success", "Your preference has been successfully saved");
            redirect(FUSION_REQUEST);
        }

        $tpl = Template::getInstance('privacy_form');

        $tpl->set_template(__DIR__.'/../../templates/privacy_form.html');

        $tpl->set_tag('openform', openform('marketing_form', 'post', FUSION_REQUEST));
        $tpl->set_tag('closeform', closeform());
        $tpl->set_tag('button', form_button('save_info', 'Save Changes', 'save_info', ['class' => 'btn-primary']));
        $tpl->set_tag(
            'marketing', form_checkbox(
                'marketing_disabled',
                "I do not want to receive telephone calls from PHP-Fusion that provide advice on how to better use PHP-Fusion products and services or information about new releases and promotions.",
                $data['marketing_disabled'],
                [
                    'reverse_label' => TRUE,
                    'class'         => "text-left",
                ]
            )
        );

        return $tpl->get_output();
    }

    /**
     *
     * @return string
     * @throws \ReflectionException
     */
    public function viewPage() {
        // set the title
        add_to_title('Account Management');

        if (!$this->pocket['wallet_id']) {
            return $this->firstTimeRegisterForm();
        }

        $ref = get('sref');
        $ref = ($ref && in_array($ref, ['security', 'identity', 'privacy', 'basic'])) ? $ref : $this->default_section;
        switch ($ref) {
            case 'security':
                add_to_title(' | Security');

                return $this->walletSecurityForm();
                break;
            case 'identity':
                add_to_title(' | Identity');

                return $this->walletIdentityForm();
                break;
            case 'privacy':
                add_to_title(' | Privacy');

                return $this->walletPrivacyForm();
                break;
            default:
            case 'basic_info':
                add_to_title(' | Basic Information');

                return $this->walletBasicForm();
                break;
        }

    }

}
