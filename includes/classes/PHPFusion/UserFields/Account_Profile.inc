<?php

namespace PHPFusion\UserFields;

use PHPFusion\UserFieldsQuantum;

class Account_Profile extends UserFieldsQuantum {

    public $post_name;
    public $inline_field = TRUE;
    public $admin_mode = FALSE;
    public $registration = FALSE;
    public $user_name_change = FALSE;
    public $show_admin_password = FALSE;
    public $user_data = [];
    public $options = [];
    public $is_admin_panel = FALSE;

    private function cacheSecurityPlugins() {
        static $security_fields = [];
        if (dbcount("(field_id)", DB_USER_FIELDS, "field_section='security'")) {
            $result = dbquery("SELECT * FROM ".DB_USER_FIELDS." WHERE field_section='security' ORDER BY field_order ASC");
            if (dbrows($result)) {
                while ($rows = dbarray($result)) {
                    $security_fields[$rows['field_name']] = $rows;
                }
            }
        }
        return $security_fields;
    }

    public function get_info() {
        $current_section = get('section');
        $current_section = $current_section && in_array($current_section, ['account', 'preferences', 'security']) ? $current_section : 'account';
        $security_fields = $this->cacheSecurityPlugins();

        $info['section'] = [
            'account' => [
                'id'     => 'account',
                'active' => ($current_section == 'account' ? TRUE : FALSE),
                'link'   => clean_request('section=acc_settings',['section'], FALSE),
                'name'   => 'Account Settings',
            ],
            'preferences' => [
                'id'     => 'preferences',
                'active' => ($current_section == 'preferences' ? TRUE : FALSE),
                'link'   => clean_request('section=preferences', ['section'], FALSE),
                'name'   => 'Account Preferences',
            ]
        ];
        if (!empty($security_fields)) {
            $info['section']['security'] = [
                'id'     => 'security',
                'active' => ($current_section == 'security' ? TRUE : FALSE),
                'link'   => clean_request('section=security', ['section'], FALSE),
                'name'   => 'Account Security',
            ];
        }

        // only happens on Front End only
        $info['current_section'] = $current_section;
        $info['section_title'] = isset($info[$current_section]) ? $info[$current_section] : 'Account Settings';

        switch ($info['current_section']) {
            default:
            case 'account':
                return $info + $this->account_settings();
                break;
            case 'preferences':
                return $info + $this->account_preferences();
                break;
            case 'security':
                return $info + $this->security();
        }
    }

    // Language, Currency, Location, Region
    private function account_preferences() {
        $locale = fusion_get_locale("", [LOCALE.LOCALESET."messages.php"]);
        $languages = fusion_get_enabled_languages();
        $info['user_language'] = form_btngroup('user_language', "", $this->user_data['user_language'], [
            'options' => $languages,
            'class'   => 'btn-stacked'
        ]);
        $info['language'] = "You are viewing the site in English (US)";

        $info['user_location'] = form_location('user_location', 'Choose your location to help us show you custom content from your area.', $this->user_data['user_location'], ['inline' => FALSE]);

        $info['update_preference_button'] = form_button($this->post_name.'_pref', 'Update Preferences', $this->post_name.'_pref', ['class' => $this->options['btn_post_class']]);

        $info['pm_notify'] = form_checkbox('pm_email_notify', $locale['621'], $this->user_data['user_pm_email_notify'], [
                'options' => [
                    '0' => $locale['520'],
                    '1' => $locale['521'],
                    '2' => $locale['522'],
                ],
                'type'    => 'radio',
                'inline'  => $this->inline_field
            ]
        );
        $user_hide_email = isset($_POST['user_hide_email']) ? $_POST['user_hide_email'] : $this->user_data['user_hide_email'];
        $info['user_hide_email'] = form_checkbox('user_hide_email', $locale['u051'], $user_hide_email,
            [
                'inline'  => $this->inline_field,
                'type'    => 'radio',
                'options' => [
                    1 => $locale['u052'],
                    0 => $locale['u053'],
                ],
            ]
        );

        $this->plugin_folder = INCLUDES.'user_fields/preference/';
        $this->load_fields();

        return (array)$info;
    }

    private function security() {

        $info['user_lastvisit'] = showdate('longdate', $this->user_data['user_lastvisit']);
        $info['update_security_button'] = form_button($this->post_name.'_security', 'Update Security', $this->post_name.'_security', ['class' => $this->options['btn_post_class']]);

        $info['user_block'] = form_user_select("user_block", "Block users", "", ["stacked" => form_button($this->post_name."_block", "Block", $this->post_name."_block")]);
        $blocked_users = '';
        if (!empty($this->user_data['user_block'])) {
            $buid = ltrim(str_replace(".", ",", $this->user_data['user_block']), ",");
            $result = dbquery("SELECT user_id, user_name, user_status, user_avatar FROM ".DB_USERS." WHERE user_id IN ($buid) ORDER BY user_name ASC");
            if (dbrows($result)) {
                $base_url = clean_request("", [$this->post_name.'_unblock'], FALSE);
                while ($data = dbarray($result)) {
                    $blocked_users .= "<li class='m-b-5'>\n<div class='pull-left m-r-10'>\n".display_avatar($data, "25px", "", "", "")."</div>\n
                    <div class='overflow-hide'>\n".$data['user_name']." <a href='$base_url&amp;".$this->post_name."_unblock=".$data['user_id']."'>Unblock</a></div>\n
                    </li>\n";
                }
            }
        }
        $info['user_block_content'] = "<ul class='list'>$blocked_users</ul>";

        $security_plugins = $this->cacheSecurityPlugins();
        if (!empty($security_plugins)) {
            // now we need to load these stuff.
            foreach($security_plugins as $plugin_name => $plugin) {
                $options['plugin_folder'] = INCLUDES.'user_fields/security/';
                $options['plugin_locale_folder'] =  INCLUDES.'user_fields/security/'.$plugin_name.'/locale/';
                $field = $this->display_fields($plugin, $this->user_data, $this->method, $options);
                $info['security_connectors'][] = [
                    'title' => $plugin['field_title'],
                    'icon' => '',
                    'connector' => $field,
                ];
            }
        }

        return (array)$info;
    }

    /**
     * GET: acc_settings
     * @return array
     * @throws \ReflectionException
     */
    private function account_settings() {
        $locale = fusion_get_locale();
        if ($this->registration === TRUE) {
            $input_value = post('user_name_reg');
            $info['user_name'] = form_text('user_name_reg', 'User Name', $input_value, ['required' => TRUE, 'deactivate' => FALSE, 'inline' => FALSE]);
        } else {

            $deactivate_user = $this->user_name_change ? FALSE : TRUE;
            $info['username_openform'] = openform('username_frm', 'post');
            $info['username_closeform'] = closeform();
            $info['user_name'] = form_text('user_name', 'User Name', $this->user_data['user_name'], ['required' => TRUE, 'deactivate' => $deactivate_user, 'inline' => $this->inline_field]);
            $info['update_user_name'] = form_button($this->post_name.'_username', "Update User Name", $this->post_name.'_username', ['class' => $this->options['btn_post_class'], 'deactivate' => $this->user_name_change ? FALSE : TRUE]);
        }
        $user_email = post('user_email') ?: $this->user_data['user_email'];
        $info['user_email'] = form_text('user_email', $locale['u128'], $user_email, [
                'type'       => 'email',
                "required"   => TRUE,
                'inline'     => $this->inline_field,
                'max_length' => '100',
                'error_text' => $locale['u126']
            ]
        );
        add_to_jquery("
            // Generate a password string
            function randString(id){
              var dataSet = $('#'+id).attr('data-character-set').split(',');
              var possible = '';
              if($.inArray('a-z', dataSet) >= 0){
                possible += 'abcdefghijklmnopqrstuvwxyz';
              }
              if($.inArray('A-Z', dataSet) >= 0){
                possible += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
              }
              if($.inArray('0-9', dataSet) >= 0){
                possible += '0123456789';
              }
              if($.inArray('#', dataSet) >= 0){
                possible += '![]{}()%&*$#^<>~@|';
              }
              var text = '';
              for(var i=0; i < $('#'+id).attr('data-size'); i++) {
                text += possible.charAt(Math.floor(Math.random() * possible.length));
              }
              return text;
            }
            // Generate user password
            $('#gen_user_passwd').click(function(){
              $('#user_password1').val(randString('gen_user_passwd'));
              $('#user_password1').attr('type', 'text');
              $('#user_password1_pwdToggle').text(locale['hide']);
            });
            // Generate user password
            $('#gen_admin_passwd').click(function(){
              $('#user_admin_password1').val(randString('gen_admin_passwd'));
              $('#user_admin_password1').attr('type', 'text');
              $('#user_admin_password1_pwdToggle').text(locale['hide']);
            });
            // Field Focus
            $('.field_focus').bind('click', function(e) {
                e.preventDefault();
                var field = $(this).data('focus');
                $('#'+field).focus();
            });
        ");

        // only in personal edit profile
        if ($this->registration === FALSE && $this->is_admin_panel === FALSE) {
            $info['user_email_openform'] = openform('useremail_frm', 'post', FUSION_REQUEST);
            $info['user_email_closeform'] = closeform();
            $info['update_email_button'] = form_button($this->post_name.'_email', 'Update Email', $this->post_name.'_email', ['class' => $this->options['btn_post_class']]);
            $info['user_close_openform'] = openform('userclose_frm', 'post', FUSION_REQUEST);
            $info['user_close_closeform'] = closeform();
            $info['user_close_message'] = form_textarea('user_close_message', 'Please help us improve by telling us why you are leaving', '', ['placeholder' => '(Optional)', 'inline' => FALSE]);
            $info['user_close_button'] = form_button($this->post_name.'_close', 'Close Account', $this->post_name.'_close', ['class' => $this->options['btn_post_class']]);
        }

        $info = $this->get_user_password() + $this->get_admin_password() + $info;

        return (array)$info;

    }

    private function get_user_password() {
        $locale = fusion_get_locale();
        if ($this->registration === TRUE) {
            // bypass need for password verification
            // New Password
            $info['user_password'] = form_text('user_password1', $locale['u134a'], post('user_password1'), [
                'type'              => 'password',
                'autocomplete_off'  => FALSE,
                'inline'            => $this->inline_field,
                'max_length'        => 64,
                'error_text'        => $locale['u134'].$locale['u143a'],
                'required'          => $this->admin_mode ? FALSE : TRUE,
                'password_strength' => FALSE,
            ]);
            $info['user_password'] .= form_text('user_password2', $locale['u134b'], '', [
                'type'             => 'password',
                'autocomplete_off' => FALSE,
                'inline'           => $this->inline_field,
                'max_length'       => 64,
                'error_text'       => $locale['u133'],
                'required'         => $this->admin_mode ? FALSE : TRUE
            ]);
            $info['user_password'] .= "<div class='clearfix text-right m-b-20 p-r-15'>".form_button('gen_user_passwd', "Generate Password", "", [
                    'type'  => 'button',
                    'class' => "btn-default spacer-sm m-b-0",
                    'icon'  => 'fas fa-key',
                    'data'  => [
                        'size'          => 16,
                        'character-set' => "a-z,A-Z,0-9,#",
                    ]
                ])."</div>\n";
            $info['user_password_notice'] = "<p>A more secure password will improve the security of your acocunt. It is recommended that you change your password regularly, and choose a password containing at least two types of characters (letters, symbols or numbers) and with a length of no less than six characters.</p>";
        } else {
            // Edit Profile
            $info['user_password_openform'] = openform('userpwd_frm', 'post');
            $info['user_password_closeform'] = closeform();
            $info['user_password_notice'] = "<p>To change your account password, enter your <a class='field_focus' data-focus='user_password' href='#'>current
            password</a>, <a class='field_focus' data-focus='user_password1' href='#'>new password</a> and <a class='field_focus' data-focus='user_password2' href='#'>confirm new password</a>.</p>";
            $info['update_password_button'] = form_button($this->post_name.'_password', 'Update Password', $this->post_name.'_password', ['class' => $this->options['btn_post_class']]);
            $info['user_password'] = form_hidden('user_id', '', isset($this->user_data['user_id']) && isnum($this->user_data['user_id']) ? $this->user_data['user_id'] : 0, ['input_id' => 'userid']);
            $info['user_password'] .= form_text('user_password', $locale['u135a'], '', [
                'type'             => 'password',
                'autocomplete_off' => TRUE,
                'inline'           => $this->inline_field,
                'max_length'       => 64,
                'error_text'       => $locale['u133']
            ]);
            $info['user_password'] .= form_text('user_password1', $locale['u135b'], '', [
                'type'             => 'password',
                'autocomplete_off' => 1,
                'inline'           => $this->inline_field,
                'max_length'       => 64,
                'error_text'       => $locale['u133'],
            ]);
            $info['user_password'] .= form_text('user_password2', $locale['u135c'], '', [
                'class'            => 'm-b-0',
                'type'             => 'password',
                'autocomplete_off' => 1,
                'inline'           => $this->inline_field,
                'max_length'       => 64,
                'error_text'       => $locale['u133']
            ]);
            $info['user_password'] .= "<div class='clearfix text-right m-b-20 p-r-15'>".form_button('gen_user_passwd',
                    "Generate Password", "", [
                        'type'  => 'button',
                        'class' => "btn-default m-b-10",
                        'icon'  => 'fas fa-key',
                        'data'  => [
                            'size'          => 16,
                            'character-set' => "a-z,A-Z,0-9,#",
                        ]
                    ])."</div>\n";
        }
        return $info;
    }

    private function get_admin_password() {
        $locale = fusion_get_locale();
        $info = [];
        if ($this->registration === TRUE) {

            $input_value = post('user_admin_password1');

            $info['user_admin_password'] = form_text('user_admin_password1', $locale['u144'], $input_value, [
                    'type'             => 'password',
                    'autocomplete_off' => FALSE,
                    'inline'           => $this->inline_field,
                    'max_length'       => 64,
                    'error_text'       => $locale['u136']
                ]
            );
            $info['user_admin_password'] .= form_text('user_admin_password2', $locale['u145'], '', [
                'class'            => 'm-b-0',
                'type'             => 'password',
                'autocomplete_off' => 1,
                'inline'           => $this->inline_field,
                'max_length'       => 64,
                'error_text'       => $locale['u136']
            ]);
            $info['user_admin_password'] .= "<div class='clearfix text-right m-b-20 p-r-15'>".form_button('gen_admin_passwd', "Generate Password", "", [
                    'type'  => 'button',
                    'class' => "btn-default m-b-10",
                    'icon'  => 'fas fa-key',
                    'data'  => [
                        'size'          => 16,
                        'character-set' => "a-z,A-Z,0-9,#",
                    ]
                ])."</div>\n";
            $info['user_admin_password_notice'] = "<p>To update user admin password, enter a new password <a class='field_focus' data-focus='user_admin_password1' href='#'>new admin password field</a>. <strong>This action will override current user password.</strong></p>";

        } else {

            if (iADMIN) {
                //                $info['user_admin_password'] = "<h4>".$locale['u131']."</h4>\n<br/>";
                $info['user_admin_password_notice'] = "<p>To update user admin password, enter a new password <a class='field_focus' data-focus='user_admin_password1' href='#'>new admin password field</a>. <strong>This action will override current user password.</strong></p>";

                if ($this->user_data['user_admin_password']) {

                    // This is for changing password
                    $info['user_admin_password'] = form_text('user_admin_password', $locale['u144a'], '', [
                            'type'             => 'password',
                            'autocomplete_off' => 1,
                            'inline'           => $this->inline_field,
                            'max_length'       => 64,
                            'error_text'       => $locale['u136']
                        ]
                    );
                    $info['user_admin_password'] .= form_text('user_admin_password1', $locale['u144'], '', [
                            'type'               => 'password',
                            'autocomplete_off'   => 1,
                            'inline'             => $this->inline_field,
                            'max_length'         => 64,
                            'error_text'         => $locale['u136'],
                            'append_button'      => TRUE,
                            'append_type'        => 'button',
                            'append_form_value'  => 'show',
                            'append_class'       => 'btn-default',
                            'append_value'       => 'Show',
                            'append_button_name' => 'toggle_admin_passwd',
                            'append_button_id'   => 'toggle_admin_passwd'
                        ]
                    );
                } else {

                    // This is just setting new password off blank records
                    $info['user_admin_password'] = form_text('user_admin_password', $locale['u144'], '', [
                            'type'             => 'password',
                            'autocomplete_off' => 1,
                            'inline'           => $this->inline_field,
                            'max_length'       => 64,
                            'error_text'       => $locale['u136']
                        ]
                    );
                }

                $info['user_admin_password'] .= form_text('user_admin_password2', $locale['u145'], '', [
                    'class'            => 'm-b-0',
                    'type'             => 'password',
                    'autocomplete_off' => 1,
                    'inline'           => $this->inline_field,
                    'max_length'       => 64,
                    'error_text'       => $locale['u136']
                ]);
                $info['user_admin_password'] .= "<div class='clearfix text-right m-b-20 p-r-15'>".form_button('gen_admin_passwd', "Generate Password", "", [
                        'type'  => 'button',
                        'class' => "btn-default m-b-0",
                        'icon'  => 'fas fa-key',
                        'data'  => [
                            'size'          => 16,
                            'character-set' => "a-z,A-Z,0-9,#",
                        ]
                    ])."</div>\n";
            }
        }

        return $info;
    }

}