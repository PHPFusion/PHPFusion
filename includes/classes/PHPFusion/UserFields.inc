<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: UserFields.inc
| Author: Hans Kristian Flaatten (Starefossen)
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

require_once THEMES."templates/global/profile.php";

class UserFields extends QuantumFields {

    public $displayTerms = 0;
    public $displayValidation = 0;
    public $errorsArray = [];
    public $formaction = FUSION_REQUEST; // changed in API 1.02
    public $formname = "userfieldsform";
    public $postName;
    public $postValue;
    public $showAdminOptions = FALSE;
    public $showAdminPass = TRUE;
    public $showAvatarInput = TRUE;
    public $baseRequest = FALSE; // new in API 1.02 - turn fusion_self to fusion_request - 3rd party pages. Turn this on if you have more than one $_GET pagination str.
    public $skipCurrentPass = FALSE;
    public $registration = FALSE;
    public $userData = [
        "user_id"             => '',
        "user_name"           => '',
        "user_password"       => '',
        "user_admin_password" => '',
        "user_email"          => '',
        'user_hide_email'     => 0,
        "user_language"       => LANGUAGE,
        'user_timezone'       => 'Europe/London',
        'user_reputation'     => 0
    ];
    /* Quantum Fields Extensions */
    public $system_title = '';
    public $admin_rights = '';
    public $locale_file = '';
    public $category_db = '';
    public $field_db = '';
    public $plugin_folder = '';
    public $plugin_locale_folder = '';
    public $debug = FALSE;
    public $method;
    public $paginate = TRUE;
    public $admin_mode = FALSE;
    private $_userNameChange = TRUE;

    private $info = [
        'terms'               => '',
        'validate'            => '',
        'user_avatar'         => '',
        'user_admin_password' => '',
    ];

    public $options = [];
    private $default_options = [
        'btn_post_class' => 'btn-default spacer-sm',
        'btn_class'      => 'btn btn-default',
    ];

    /**
     * Check whether a user field is available/installed
     *
     * @param $field_name
     *
     * @return bool
     */
    public static function check_user_field($field_name) {
        static $list;
        $result = dbquery("SELECT field_name FROM ".DB_USER_FIELDS);
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $list[] = $data['field_name'];
            }
        }

        return in_array($field_name, $list) ? TRUE : FALSE;
    }

    /* Page Navigation with UF Cats */

    public function setUserNameChange($value) {
        $this->_userNameChange = $value;
    }

    private function getProfileSections() {
        $result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_parent=:field_parent ORDER BY field_cat_order ASC", [':field_parent' => 0]);
        $section = [];
        if (dbrows($result) > 0) {
            $aid = isset($_GET['aid']) ? fusion_get_aidlink() : '';
            $i = 0;
            while ($data = dbarray($result)) {
                $section[$data['field_cat_id']] = [
                    "id"     => $data['field_cat_id'],
                    'active' => (isset($_GET['section']) && $_GET['section'] == $data['field_cat_id']) ? 1 : (!isset($_GET['section']) && $i == 0 ? 1 : 0),
                    'link'   => clean_request($aid.'section='.$data['field_cat_id'].'&lookup='.$this->userData['user_id'], ['section'], FALSE),
                    'name'   => ucwords(self::parse_label($data['field_cat_name'])),
                    'icon'   => $data['field_cat_class']
                ];
                $i++;
            }
        }

        return (array)$section;
    }

    /**
     * Display Input Fields
     * @param array $input
     *
     * @throws \Exception
     */
    public function display_profile_input(array $input = []) {
        $this->method = 'input';
        $locale = fusion_get_locale();
        $this->info = [
            'section'             => $this->getProfileSections(),
            'user_id'             => form_hidden('user_id', '', isset($_GET['lookup']) && isnum($_GET['lookup']) ? $_GET['lookup'] : 0),
            'user_name'           => '',
            'user_password'       => '',
            'user_admin_password' => '',
            'user_email'          => '',
            'user_hide_email'     => '',
            'user_avatar'         => '',
            'user_reputation'     => '',
            'validate'            => '',
            'terms'               => ''
        ];

        $_GET['section'] = isset($_GET['section']) && isset($this->info['section'][$_GET['section']]) ? $_GET['section'] : 1;
        $this->options += $this->default_options;

        if ($_GET['section'] == 1) {

            $user_name = isset($_POST['user_name']) ? form_sanitizer($_POST['user_name'], '', 'user_name') : $this->userData['user_name'];
            $user_email = isset($_POST['user_email']) ? $_POST['user_email'] : $this->userData['user_email'];
            $user_hide_email = isset($_POST['user_hide_email']) ? $_POST['user_hide_email'] : $this->userData['user_hide_email'];
            $this->info['user_name'] = form_para($locale['u129'], 'account', 'profile_category_name');
            $user_reputation = '';
            if (defined('FORUM_EXIST')) {
                $user_reputation = isset($_POST['user_reputation']) ? $_POST['user_reputation'] : $this->userData['user_reputation'];
            }

            if (iADMIN || $this->_userNameChange) {
                $this->info['user_name'] .= form_text('user_name', $locale['u127'], $user_name, [
                    'max_length' => 30,
                    'required'   => 1,
                    'error_text' => $locale['u122'],
                    'inline'     => TRUE
                ]);
            }

            $this->info['user_password'] = form_para($locale['u132'], 'password', 'profile_category_name');

            if ($this->registration || $this->admin_mode) {
                $this->info['user_password'] .= form_text('user_password1', $locale['u134a'], '', [
                        'type'              => 'password',
                        'autocomplete_off'  => 1,
                        'inline'            => TRUE,
                        'max_length'        => 64,
                        'error_text'        => $locale['u134'].$locale['u143a'],
                        'required'          => $this->admin_mode ? FALSE : TRUE,
                        'password_strength' => TRUE
                    ]
                );
                $this->info['user_password'] .= form_text('user_password2', $locale['u134b'], '', [
                        'type'             => 'password',
                        'autocomplete_off' => 1,
                        'inline'           => TRUE,
                        'max_length'       => 64,
                        'error_text'       => $locale['u133'],
                        'required'         => $this->admin_mode ? FALSE : TRUE
                    ]
                );
            } else {

                $this->info['user_password'] .= form_hidden('user_id', '', isset($this->userData['user_id']) && isnum($this->userData['user_id']) ? $this->userData['user_id'] : 0, ['input_id' => 'userid']);

                $this->info['user_password'] .= form_text('user_password', $locale['u135a'], '', [
                        'type'             => 'password',
                        'autocomplete_off' => 1,
                        'inline'           => TRUE,
                        'max_length'       => 64,
                        'error_text'       => $locale['u133'],
                        'stacked'          => "<div id='fusion_verify_password'></div>"
                    ]
                );
                $this->info['user_password'] .= form_text('user_password1', $locale['u135b'], '', [
                        'type'              => 'password',
                        'autocomplete_off'  => 1,
                        'inline'            => TRUE,
                        'max_length'        => 64,
                        'error_text'        => $locale['u133'],
                        'password_strength' => TRUE
                    ]
                );
                $this->info['user_password'] .= form_text('user_password2', $locale['u135c'], '', [
                        'class'            => 'm-b-0',
                        'type'             => 'password',
                        'autocomplete_off' => 1,
                        'inline'           => TRUE,
                        'max_length'       => 64,
                        'error_text'       => $locale['u133']
                    ]
                );
                $this->info['user_password'] .= form_hidden('user_hash', '', $this->userData['user_password'], ['input_id' => 'userhash']);
            }
            $this->info['user_password'] .= "<div class='col-xs-12 col-sm-9 col-sm-offset-3 col-md-offset-3 col-lg-offset-3'><span class='text-smaller'>".$locale['u147']."</span></div><br/>";

            // Admin Password - not available for everyone except edit profile.
            if (!$this->registration && iADMIN && !defined('ADMIN_PANEL')) {
                $this->info['user_admin_password'] = form_para($locale['u131'], 'adm_password', 'profile_category_name');
                if ($this->userData['user_admin_password']) {
                    // This is for changing password
                    $this->info['user_admin_password'] .= form_text('user_admin_password', $locale['u144a'], '', [
                            'type'             => 'password',
                            'autocomplete_off' => 1,
                            'inline'           => TRUE,
                            'max_length'       => 64,
                            'error_text'       => $locale['u136']
                        ]
                    );
                    $this->info['user_admin_password'] .= form_text('user_admin_password1', $locale['u144'], '', [
                            'type'             => 'password',
                            'autocomplete_off' => 1,
                            'inline'           => TRUE,
                            'max_length'       => 64,
                            'error_text'       => $locale['u136']
                        ]
                    );
                } else {
                    // This is just setting new password off blank records
                    $this->info['user_admin_password'] .= form_text('user_admin_password', $locale['u144'], '', [
                            'type'             => 'password',
                            'autocomplete_off' => 1,
                            'inline'           => TRUE,
                            'max_length'       => 64,
                            'error_text'       => $locale['u136']
                        ]
                    );
                }

                $this->info['user_admin_password'] .= form_text('user_admin_password2', $locale['u145'], '', [
                        'class'            => 'm-b-0',
                        'type'             => 'password',
                        'autocomplete_off' => 1,
                        'inline'           => TRUE,
                        'max_length'       => 64,
                        'error_text'       => $locale['u136']
                    ]
                );

                $this->info['user_admin_password'] .= "<div class='col-xs-12 col-sm-9 col-sm-offset-3 col-md-offset-3 col-lg-offset-3'><span class='text-smaller'>".$locale['u147']."</span></div>\n";

            }

            // User Password Verification for Email Change
            /*$this->info['user_password_verify'] = (iADMIN && checkrights("M")) ? "" : form_text('user_password_verify',
                $locale['u135a'], '',
                [
                    'type'             => 'password',
                    'autocomplete_off' => 1,
                    'placeholder' => $locale['u100'],
                    'inline'           => TRUE,
                    'max_length'       => 64,
                    'error_text'       => $locale['u133'],
                    'class' => 'display-none'
                ]
            );*/

            // Avatar Field
            if (!$this->registration) {

                if (isset($this->userData['user_avatar']) && $this->userData['user_avatar'] != "") {
                    $this->info['user_avatar'] = "
                    <div class='row'>\n
                    <div class='col-xs-12 col-sm-3'><strong>".$locale['u185']."</strong></div>
                    <div class='col-xs-12 col-sm-9'>
                        <div class='p-l-10'>
                        <label for='user_avatar_upload'>".display_avatar($this->userData, '150px', '', FALSE, 'img-thumbnail')."</label>\n<br />\n".form_checkbox("delAvatar", $locale['delete'], '', ['reverse_label' => TRUE])."
                        </div>
                    </div></div>
                    ";
                } else {
                    $this->info['user_avatar'] = form_fileinput('user_avatar', $locale['u185'], '', [
                            'upload_path'     => IMAGES."avatars/",
                            'input_id'        => 'user_avatar_upload',
                            'type'            => 'image',
                            'max_byte'        => fusion_get_settings('avatar_filesize'),
                            'max_height'      => fusion_get_settings('avatar_width'),
                            'max_width'       => fusion_get_settings('avatar_height'),
                            'inline'          => TRUE,
                            'thumbnail'       => 0,
                            'width'           => '100%',
                            "delete_original" => FALSE,
                            'class'           => 'm-t-10 m-b-0',
                            "error_text"      => $locale['u180'],
                            "template"        => "modern",
                            'ext_tip'         => sprintf($locale['u184'], parsebytesize(fusion_get_settings('avatar_filesize')), fusion_get_settings('avatar_width'), fusion_get_settings('avatar_height'))
                        ]
                    );
                }
            }

            $ext_tip = '';
            if (!$this->registration) {
                $ext_tip = (iADMIN && checkrights('M')) ? '' : $locale['u100'];
            }

            $this->info['user_email'] = form_text('user_email', $locale['u128'], $user_email, [
                    'type'       => 'email',
                    "required"   => TRUE,
                    'inline'     => TRUE,
                    'max_length' => '100',
                    'error_text' => $locale['u126'],
                    'ext_tip' => $ext_tip
                ]
            );

            $this->info['user_hide_email'] = form_checkbox('user_hide_email', $locale['u051'], $user_hide_email,
                [
                    'inline'         => TRUE,
                    'inline_options' => TRUE,
                    'type'           => 'radio',
                    'options'        => [
                        1 => $locale['u052'],
                        0 => $locale['u053'],
                    ],
                ]
            );

            if (iSUPERADMIN && defined('FORUM_EXIST')) {
                $this->info['user_reputation'] = form_text('user_reputation', $locale['u210'], $user_reputation, [
                        'type'   => 'number',
                        'inline' => TRUE
                    ]
                );
            }

            if ($this->displayValidation == 1 && !defined('ADMIN_PANEL')) {
                $this->info['validate'] = $this->renderValidation();
            }
            if ($this->displayTerms == 1) {
                $this->info['terms'] = $this->renderTerms();
            }
        } else {
            if (empty($this->userData['user_hash'])) {
                $this->userData['user_hash'] = $this->userData['user_password'];
            }
            // requires password
            $this->info['user_password'] = form_hidden('user_id', '', isset($this->userData['user_id']) && isnum($this->userData['user_id']) ? $this->userData['user_id'] : 0);
            $this->info['user_password'] .= form_hidden('user_hash', '', $this->userData['user_hash']);
        }

        $this->info += [
            'register'  => $this->registration,
            'pages'     => ($this->paginate && !$this->registration) ? $_GET['section'] : '',
            'openform'  => openform($this->formname, 'post', FUSION_REQUEST, [
                'enctype' => $this->showAvatarInput ? TRUE : FALSE,
            ]),
            'closeform' => closeform(),
            'button'    => $this->renderButton(),
        ];

        $this->info = $this->info + $this->get_user_fields();

        /*
         * Template
         */
        $user_fields = '';
        if (!empty($this->info['user_field'])) {
            foreach ($this->info['user_field'] as $catID => $fieldData) {
                if (!empty($fieldData['title'])) {
                    $user_fields .= form_para($fieldData['title'], 'fieldcat'.$catID);
                }
                if (!empty($fieldData['fields'])) {
                    foreach ($fieldData['fields'] as $_id => $_fields) {
                        $user_fields .= $_fields;
                    }
                }
            }
        }

        if (isset($this->info['section']) && count($this->info['section']) > 1) {
            $tab_title = [];
            foreach ($this->info['section'] as $section) {
                $tab_title['title'][$section['id']] = $section['name'];
                $tab_title['id'][$section['id']] = $section['id'];
                $tab_title['icon'][$section['id']] = $section['icon'];
            }
            $this->info['tab_info'] = $tab_title;
        }

        /*
         * Template Output
         */
        ob_start();
        $this->registration ? display_register_form($this->info) : display_profile_form($this->info);
        echo strtr(ob_get_clean(), [
                '{%tab_header%}'                => isset($tab_title) ? opentab($tab_title, $_GET['section'], 'user-profile-form', TRUE) : '',
                '{%open_form%}'                 => $this->info['openform'],
                '{%user_id%}'                   => $this->info['user_id'],
                '{%user_name_field%}'           => $this->info['user_name'],
                '{%user_email_field%}'          => $this->info['user_email'],
                //'{%user_password_verify%}'      => $this->info['user_password_verify'],
                '{%user_hide_email_field%}'     => $this->info['user_hide_email'],
                '{%user_avatar_field%}'         => $this->info['user_avatar'],
                '{%user_reputation_field%}'     => $this->info['user_reputation'],
                '{%user_password_field%}'       => $this->info['user_password'],
                '{%user_admin_password_field%}' => $this->info['user_admin_password'],
                '{%custom_fields%}'             => $user_fields,
                '{%captcha_fields%}'            => $this->info['validate'],
                '{%eula%}'                      => $this->info['terms'],
                '{%post_button%}'               => $this->info['button'],
                '{%close_form%}'                => $this->info['closeform'],
                '{%tab_footer%}'                => isset($tab_title) ? closetab() : '',
                '{%opentable%}'                 => fusion_get_function('opentable', ''),
                '{%closetable%}'                => fusion_get_function('closetable')
            ]
        );
        /*
        add_to_jquery("
        var current_email = $('#user_email').val();
        $('#user_email').on('input change propertyChange paste', function(e){
            if (current_email !== $(this).val()) {
                $('#user_password_verify-field').removeClass('display-none');
            } else {
            $('#user_password_verify-field').addClass('display-none');
            }
        });
        ");
        */
    }

    /**
     * Display Captcha
     *
     * @todo: remove locale.
     * @return string
     */
    private function renderValidation() {
        $locale = fusion_get_locale();
        $_CAPTCHA_HIDE_INPUT = FALSE;
        include INCLUDES."captchas/".fusion_get_settings("captcha")."/captcha_display.php";
        $html = "<div class='form-group m-t-20'>\n";
        if (!$_CAPTCHA_HIDE_INPUT) {
            $html .= "<label for='captcha_code' class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0'>\n".$locale['u190']." <span class='required'>*</span></label>\n";
        }

        $html .= "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>";

        $html .= display_captcha([
            'captcha_id' => 'captcha_userfields',
            'input_id'   => 'captcha_code_userfields',
            'image_id'   => 'captcha_image_userfields'
        ]);
        if (!$_CAPTCHA_HIDE_INPUT) {
            $html .= form_text('captcha_code', '', '', [
                'inline'           => 1,
                'required'         => 1,
                'autocomplete_off' => 1,
                'width'            => '200px',
                'class'            => 'm-t-15',
                'placeholder'      => $locale['u191']
            ]);
        }
        $html .= "</div>\n";
        $html .= "</div>\n";

        return (string)$html;
    }

    /**
     * Display Terms of Agreement Field
     *
     * @todo: remove locale.
     * @return string
     */
    private function renderTerms() {
        $locale = fusion_get_locale();
        $agreement = strtr($locale['u193'], [
                '[LINK]'  => "<a href='".BASEDIR."print.php?type=T' id='license_agreement'><strong>",
                '[/LINK]' => "</strong></a>"
            ]
        );

        $modal = openmodal('license_agreement', $locale['u192'], ['button_id' => 'license_agreement']);
        $modal .= parse_textarea(self::parse_label(fusion_get_settings('license_agreement')));
        $modal_content = '<p class="pull-left">'.$locale['u193a'].' '.ucfirst(showdate('shortdate', fusion_get_settings('license_lastupdate'))).'</p>';
        $modal_content .= '<button type="button" id="agree" class="btn btn-success" data-dismiss="modal">'.$locale['u193b'].'</button>';
        $modal .= modalfooter($modal_content, TRUE);
        $modal .= closemodal();
        add_to_footer($modal);
        add_to_jquery('
            $("#agree").on("click", function() {
                $("#register").attr("disabled", false).removeClass("disabled");
                $("#agreement").attr("checked", true);
            });
        ');

        $html = "<div class='form-group clearfix'>";
        $html .= "<label class='control-label col-xs-12 col-sm-3 p-l-0'>".$locale['u192']."</label>";
        $html .= "<div class='col-xs-12 col-sm-9'>\n";
        $html .= form_checkbox('agreement', $agreement, '', ["required" => TRUE, "reverse_label" => TRUE]);
        $html .= "</div>\n</div>\n";
        add_to_head("<script type='text/javascript'>$(function() {
        $('#agreement').bind('click', function() {
            var regBtn = $('#register');
            if ($(this).is(':checked')) {
                regBtn.attr('disabled', false).removeClass('disabled');
            } else {
                regBtn.attr('disabled', true).addClass('disabled');
            }
        });
        });</script>");

        return $html;
    }

    private function renderButton() {
        $disabled = $this->displayTerms == 1 ? TRUE : FALSE;
        $this->options += $this->default_options;
        $html = (!$this->skipCurrentPass) ? form_hidden('user_hash', '', $this->userData['user_password']) : '';
        $html .= form_button($this->postName, $this->postValue, $this->postValue,
            [
                "deactivate" => $disabled,
                "class"      => $this->options['btn_post_class']
            ]);

        return (string)$html;
    }

    /**
     * Fetch User Fields Array to templates
     * Toggle with class string method - input or display
     * output to array
     */
    private function get_user_fields() {
        $fields = [];
        $category = [];
        $item = [];

        $this->callback_data = $this->userData;

        switch ($this->method) {
            case 'input':
                $this->info['user_field'][0]['fields']['user_id'] = form_hidden('user_id', '', $this->callback_data['user_id']);
                if ($this->registration == FALSE) {
                    if (isset($this->info['user_field'][0]['fields']['user_name'])) {
                        $this->info['user_field'][0]['fields']['user_name'] = form_hidden('user_name', '', $this->callback_data['user_name']);
                    }
                }
                break;
            case 'display':
                $this->info['user_field'] = [];
        }

        $index_page_id = isset($_GET['section']) && isnum($_GET['section']) && isset($this->getProfileSections()[$_GET['section']]) ? intval($_GET['section']) : 1;

        $registration_cond = ($this->registration == TRUE ? ' AND field.field_registration=:field_register' : '');
        $registration_bind = ($this->registration == TRUE ? [':field_register' => 1] : []);

        $query = "SELECT field.*, cat.field_cat_id, cat.field_cat_name, cat.field_parent, root.field_cat_id as page_id, root.field_cat_name as page_name, root.field_cat_db, root.field_cat_index
                  FROM ".DB_USER_FIELDS." field
                  INNER JOIN ".DB_USER_FIELD_CATS." cat ON (cat.field_cat_id = field.field_cat)
                  INNER JOIN ".DB_USER_FIELD_CATS." root on (cat.field_parent = root.field_cat_id)
                  WHERE (cat.field_cat_id=:index00 OR root.field_cat_id=:index01) $registration_cond
                  ORDER BY root.field_cat_order, cat.field_cat_order, field.field_order
                  ";
        $bind = [
            ':index00' => $index_page_id,
            ':index01' => $index_page_id,
        ];
        $bind = $bind + $registration_bind;
        $result = dbquery($query, $bind);
        $rows = dbrows($result);
        if ($rows != '0') {
            while ($data = dbarray($result)) {
                if ($data['field_cat_id']) {
                    $category[$data['field_parent']][$data['field_cat_id']] = self::parse_label($data['field_cat_name']);
                }
                if ($data['field_cat']) {
                    $item[$data['field_cat']][] = $data;
                }
            }
            if (isset($category[$index_page_id])) {
                foreach ($category[$index_page_id] as $cat_id => $cat) {
                    if ($this->registration || $this->method == 'input') {
                        if (isset($item[$cat_id])) {
                            $fields['user_field'][$cat_id]['title'] = $cat;
                            foreach ($item[$cat_id] as $field_id => $field) {
                                $options = [
                                    'show_title' => TRUE,
                                    'inline'     => TRUE,
                                    'required'   => (bool)$field['field_required']
                                ];
                                if ($field['field_type'] == 'file') {
                                    $options += [
                                        'plugin_folder'        => $this->plugin_folder,
                                        'plugin_locale_folder' => $this->plugin_locale_folder
                                    ];
                                }
                                $field_output = $this->display_fields($field, $this->callback_data, $this->method, $options);
                                $fields['user_field'][$cat_id]['fields'][$field['field_id']] = $field_output;
                                $fields['extended_field'][$field['field_name']] = $field_output; // for the gets
                            }
                        }
                    } else {
                        // Display User Fields
                        if (isset($item[$cat_id])) {
                            $fields['user_field'][$cat_id]['title'] = $cat;
                            foreach ($item[$cat_id] as $field_id => $field) {
                                // Outputs array
                                $field_output = $this->display_fields($field, $this->callback_data, $this->method);
                                //$fields['user_field'][$cat_id]['fields'][$field['field_id']] = $field_output; // relational to the category
                                $fields['extended_field'][$field['field_name']] = $field_output; // for the gets
                                if (!empty($field_output)) {
                                    $fields['user_field'][$cat_id]['fields'][$field['field_id']] = array_merge($field, $field_output);
                                }
                                //$fields['user_field'][$cat_id]['fields'][$field['field_id']] = array_merge($field, $fields['user_field'][$cat_id]['fields'][$field['field_id']]);
                            }
                        }
                    }
                }
            }
        }

        return (array)$fields;
    }

    /***
     * Fetch profile output data
     * - Display Profile (View)
     */
    public function display_profile_output() {
        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();

        // Add User to Groups
        if (iADMIN && checkrights("UG") && isset($_GET['lookup']) && $_GET['lookup'] !== fusion_get_userdata('user_id')) {
            if ((isset($_POST['add_to_group'])) && (isset($_POST['user_group']) && isnum($_POST['user_group']))) {
                $lookup = stripinput($_GET['lookup']);
                $user_group = stripinput($_POST['user_group']);
                if (!preg_match("(^\.{$user_group}$|\.{$user_group}\.|\.{$user_group}$)", $this->userData['user_groups'])) {
                    $userdata = [
                        'user_groups' => $this->userData['user_groups'].".".$user_group,
                        'user_id'     => $lookup
                    ];
                    dbquery_insert(DB_USERS, $userdata, 'update');
                }
                if (defined('ADMIN_PANEL') && isset($_GET['step']) && $_GET['step'] == "view") {
                    redirect(ADMIN."members.php".fusion_get_aidlink()."&amp;step=view&amp;user_id=".$this->userData['user_id']);
                } else {
                    redirect(BASEDIR."profile.php?lookup=".$lookup);
                }
            }
        }

        $this->info['section'] = $this->getProfileSections();
        $this->info['user_id'] = $this->userData['user_id'];
        $this->info['user_name'] = $this->userData['user_name'];

        $current_section = [['id' => 1]];
        if (!empty($this->info['section'])) {
            $current_section = current($this->info['section']);
        }
        $_GET['section'] = isset($_GET['section']) && isset($this->info['section'][$_GET['section']]) ? $_GET['section'] : $current_section['id'];
        if (empty($this->userData['user_avatar']) && !file_exists(IMAGES."avatars/".$this->userData['user_avatar'])) {
            $this->userData['user_avatar'] = get_image('noavatar');
        }

        $this->info['core_field']['profile_user_avatar'] = [
            'title'  => $locale['u186'],
            'value'  => $this->userData['user_avatar'],
            'status' => $this->userData['user_status']
        ];

        // user name
        $this->info['core_field']['profile_user_name'] = [
            'title' => $locale['u068'],
            'value' => $this->userData['user_name']
        ];

        // user level
        $this->info['core_field']['profile_user_level'] = [
            'title' => $locale['u063'],
            'value' => getgroupname($this->userData['user_level'])
        ];

        // user email
        if (iADMIN || $this->userData['user_hide_email'] == 0) {
            $this->info['core_field']['profile_user_email'] = [
                'title' => $locale['u064'],
                'value' => hide_email($this->userData['user_email'], fusion_get_locale("UM061a"))
            ];
        }

        // user joined
        $this->info['core_field']['profile_user_joined'] = [
            'title' => $locale['u066'],
            'value' => showdate("longdate", $this->userData['user_joined'])
        ];

        // Last seen
        $this->info['core_field']['profile_user_visit'] = [
            'title' => $locale['u067'],
            'value' => $this->userData['user_lastvisit'] ? showdate("longdate", $this->userData['user_lastvisit']) : $locale['u042']
        ];

        // user status
        if (iADMIN && $this->userData['user_status'] > 0) {
            $this->info['core_field']['profile_user_status'] = [
                'title' => $locale['u055'],
                'value' => getuserstatus($this->userData['user_status'])
            ];

            if ($this->userData['user_status'] == 3) {
                $this->info['core_field']['profile_user_reason'] = [
                    'title' => $locale['u056'],
                    'value' => $this->userData['suspend_reason']
                ];
            }
        }

        // IP
        //$this->info['core_field']['profile_user_ip'] = [];
        if (iADMIN && checkrights("M")) {
            $this->info['core_field']['profile_user_ip'] = [
                'title' => $locale['u049'],
                'value' => $this->userData['user_ip']
            ];
        }

        // Groups - need translating.
        $this->info['core_field']['profile_user_group']['title'] = $locale['u057'];
        $this->info['core_field']['profile_user_group']['value'] = '';
        $user_groups = strpos($this->userData['user_groups'], ".") == 0 ? substr($this->userData['user_groups'], 1) : $this->userData['user_groups'];
        $user_groups = explode(".", $user_groups);
        $user_groups = array_filter($user_groups);
        $group_info = [];
        if (!empty($user_groups)) {
            for ($i = 0; $i < count($user_groups); $i++) {
                if ($group_name = getgroupname($user_groups[$i])) {
                    $group_info[] = [
                        'group_url'  => BASEDIR."profile.php?group_id=".$user_groups[$i],
                        'group_name' => $group_name
                    ];
                }
            }
            $this->info['core_field']['profile_user_group']['value'] = $group_info; // is array
        }

        $this->info = $this->info + $this->get_user_fields();

        if (iMEMBER && fusion_get_userdata('user_id') != $this->userData['user_id']) {

            $this->info['buttons'] = [
                'user_pm_title' => $locale['u043'],
                'user_pm_link'  => BASEDIR."messages.php?msg_send=".$this->userData['user_id']
            ];

            if (checkrights('M') && fusion_get_userdata('user_level') <= USER_LEVEL_ADMIN && $this->userData['user_id'] != '1') {
                $groups_cache = cache_groups();
                $user_groups_opts = [];
                $this->info['user_admin'] = [
                    'user_edit_title'     => $locale['edit'],
                    'user_edit_link'      => ADMIN."members.php".$aidlink."&amp;ref=edit&amp;lookup=".$this->userData['user_id'],
                    'user_ban_title'      => $this->userData['user_status'] == 1 ? $locale['u074'] : $locale['u070'],
                    'user_ban_link'       => ADMIN."members.php".$aidlink."&amp;action=".($this->userData['user_status'] == 1 ? 2 : 1)."&amp;lookup=".$this->userData['user_id'],
                    'user_suspend_title'  => $locale['u071'],
                    'user_suspend_link'   => ADMIN."members.php".$aidlink."&amp;action=3&amp;lookup=".$this->userData['user_id'],
                    'user_delete_title'   => $locale['delete'],
                    'user_delete_link'    => ADMIN."members.php".$aidlink."&amp;ref=delete&amp;lookup=".$this->userData['user_id'],
                    'user_delete_onclick' => "onclick=\"return confirm('".$locale['delete']."');\"",
                    'user_susp_title'     => $locale['u054'],
                    'user_susp_link'      => ADMIN."members.php".$aidlink."&amp;ref=log&amp;lookup=".$this->userData['user_id']
                ];
                if (count($groups_cache) > 0) {
                    foreach ($groups_cache as $group) {
                        if (!preg_match("(^{$group['group_id']}|\.{$group['group_id']}\.|\.{$group['group_id']}$)", $this->userData['user_groups'])) {
                            $user_groups_opts[$group['group_id']] = $group['group_name'];
                        }
                    }
                    if (iADMIN && checkrights("UG") && !empty($user_groups_opts)) {
                        $submit_link = BASEDIR."profile.php?lookup=".$this->userData['user_id'];
                        if (defined('ADMIN_PANEL') && isset($_GET['step']) && $_GET['step'] == "view") {
                            $submit_link = ADMIN."members.php".$aidlink."&amp;step=view&amp;user_id=".$this->userData['user_id']."&amp;lookup=".$this->userData['user_id'];
                        }
                        $this->info['group_admin']['ug_openform'] = openform("admin_grp_form", "post", $submit_link);
                        $this->info['group_admin']['ug_closeform'] = closeform();
                        $this->info['group_admin']['ug_title'] = $locale['u061'];
                        $this->info['group_admin']['ug_dropdown_input'] = form_select("user_group", '', "", ["options" => $user_groups_opts, "width" => "100%", "inner_width" => "100%", "inline" => FALSE, 'class' => 'm-0']);
                        $this->info['group_admin']['ug_button'] = form_button("add_to_group", $locale['u059'], $locale['u059']);
                    }
                }
            }
        }

        // Display Template
        echo display_user_profile($this->info);
    }

    /*
     * Render Listing Functions
     */
    protected static function list_func($user_id, $list, $selected_fields) {
        $html = "<tr>\n
                <td class='p-10'>\n".$list[$user_id]['checkbox']."</td>\n
                <td>".$list[$user_id]['user_name']."</td>\n
                <td class='no-break'>\n".$list[$user_id]['user_level']."</td>\n
                <td>\n".$list[$user_id]['user_email']."</td>\n";
        foreach ($selected_fields as $column) {
            $html .= "<td>".(!empty($list[$user_id][$column]) ? $list[$user_id][$column] : "-")."</td>\n";
        }

        return $html;
    }


    /**
     * Get User Data of the current page.
     *
     * @param $key
     *
     * @return array|null
     */
    public function getUserData($key = NULL) {
        static $userData = [];
        if (empty($userData)) {
            $userData = $this->userData;
        }

        return $key === NULL ? $userData : (isset($userData[$key]) ? $userData[$key] : NULL);
    }

}
