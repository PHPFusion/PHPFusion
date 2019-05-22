<?php

namespace PHPFusion\UserFields;

use PHPFusion\LoginAuth;
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
    private $locale = [];
    public $is_admin_panel = FALSE;

    public function __construct() {
        //print_p('yes');
        $this->locale = fusion_get_locale();
    }

    public function get_info() {
        if (isset($_GET['section'])) {
            switch ($_GET['section']) {
                default:
                case 'account':
                    return $this->account_settings();
                    break;
                case 'preferences':
                    return $this->preferences();
                    break;
                case 'security':
                    return $this->security();
            }
        } else {
            return $this->account_settings();
        }
    }

    // Language, Currency, Location, Region
    private function preferences() {
        $this->locale = fusion_get_locale("", [LOCALE.LOCALESET."messages.php"]);
        $languages = fusion_get_enabled_languages();
        $info['user_language'] = form_btngroup('user_language', "", $this->user_data['user_language'], [
            'options' => $languages,
            'class'   => 'btn-stacked'
        ]);
        $info['language'] = "You are viewing the site in English (US)";
        $info['user_location'] = form_location('user_location', "", $this->user_data['user_location'], ['inline' => $this->inline_field]);
        $info['update_preference_button'] = form_button($this->post_name.'_pref', 'Update Preferences', $this->post_name.'_pref', ['class' => $this->options['btn_post_class']]);
        $info['pm_notify'] = form_checkbox('pm_email_notify', $this->locale['621'], $this->user_data['user_pm_email_notify'], [
                'options' => [
                    '0' => $this->locale['520'],
                    '1' => $this->locale['521'],
                    '2' => $this->locale['522'],
                ],
                'type'    => 'radio',
                'inline'  => $this->inline_field
            ]
        );
        $user_hide_email = isset($_POST['user_hide_email']) ? $_POST['user_hide_email'] : $this->user_data['user_hide_email'];
        $info['user_hide_email'] = form_checkbox('user_hide_email', $this->locale['u051'], $user_hide_email,
            [
                'inline'  => $this->inline_field,
                'type'    => 'radio',
                'options' => [
                    1 => $this->locale['u052'],
                    0 => $this->locale['u053'],
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

        $info['user_block'] = form_user_select("user_block", "Block users", "", [
            "stacked" => form_button($this->post_name."_block", "Block", $this->post_name."_block")
        ]);
        $blocked_users = "";
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

        $login = new LoginAuth();
        $info['security_connectors'] = $login->get_security_connectors();

        $this->plugin_folder = INCLUDES.'user_fields/preference/';
        $this->load_fields();

        return (array)$info;
    }

    /**
     * GET: acc_settings
     * @return array
     */
    private function account_settings() {

        $deactivate_user = $this->user_name_change ? FALSE : TRUE;

        if ($this->registration === TRUE) {
            $input_value = isset($_POST['user_name_reg']) ? $_POST['user_name_reg'] : '';
            $info['user_name'] = form_text('user_name_reg', 'User Name', $input_value, ['required' => TRUE, 'deactivate' => FALSE, 'inline' => $this->inline_field]);
        } else {
            $info['username_openform'] = openform('username_frm', 'post', FUSION_REQUEST);
            $info['username_closeform'] = closeform();
            $info['user_name'] = form_text('user_name', 'User Name', $this->user_data['user_name'], ['required' => TRUE, 'deactivate' => $deactivate_user, 'inline' => $this->inline_field]);
            $info['update_user_name'] = form_button($this->post_name.'_username', "Update User Name", $this->post_name.'_username', ['class' => $this->options['btn_post_class'], 'deactivate' => $this->user_name_change ? FALSE : TRUE]);
            }
        $user_email = isset($_POST['user_email']) ? $_POST['user_email'] : $this->user_data['user_email'];
        $info['user_email'] = form_text('user_email', $this->locale['u128'], $user_email, [
                'type'       => 'email',
                "required"   => TRUE,
                'inline'     => $this->inline_field,
                'max_length' => '100',
                'error_text' => $this->locale['u126']
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
            $info['user_close_message'] = form_textarea('user_close_message', 'Please help us improve by telling us why you are leaving', '', ['placeholder' => '(Optional)', 'inline' => $this->inline_field]);
            $info['user_close_button'] = form_button($this->post_name.'_close', 'Close Account', $this->post_name.'_close', ['class' => $this->options['btn_post_class']]);
            // Social connectors
            $login = new LoginAuth();
            $info['social_connectors'] = $login->get_login_connectors();
        }
        
        $info = $this->get_user_password() + $this->get_admin_password() + $info;
        
        return (array)$info;

    }
    
    private function get_user_password() {
        if ($this->registration === TRUE || $this->is_admin_panel === TRUE) {
            // bypass need for password verification
            // New Password
            $input_value = isset($_POST['user_password1']) ? $_POST['user_password1'] : '';
            $info['user_password'] = form_text('user_password1', $this->locale['u134a'], $input_value, [
                    'type'              => 'password',
                    'autocomplete_off'  => FALSE,
                    'inline'            => $this->inline_field,
                    'max_length'        => 64,
                    'error_text'        => $this->locale['u134'].$this->locale['u143a'],
                    'required'          => $this->admin_mode ? FALSE : TRUE,
                    'password_strength' => FALSE,
                ]);
            $info['user_password'] .= form_text('user_password2', $this->locale['u134b'], '', [
                'type'             => 'password',
                'autocomplete_off' => FALSE,
                'inline'           => $this->inline_field,
                'max_length'       => 64,
                'error_text'       => $this->locale['u133'],
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
            $info['user_password_openform'] = openform('userpwd_frm', 'post', FUSION_REQUEST);
            $info['user_password_closeform'] = closeform();
            $info['user_password_notice'] = "<p>To change your account password, enter your <a class='field_focus' data-focus='user_password' href='#'>current
            password</a>, <a class='field_focus' data-focus='user_password1' href='#'>new password</a> and <a class='field_focus' data-focus='user_password2' href='#'>confirm new password</a>.</p>";
            $info['update_password_button'] = form_button($this->post_name.'_password', 'Update Password', $this->post_name.'_password', ['class' => $this->options['btn_post_class']]);
            $info['user_password'] = form_hidden('user_id', '', isset($this->user_data['user_id']) && isnum($this->user_data['user_id']) ? $this->user_data['user_id'] : 0, ['input_id' => 'userid']);
            $info['user_password'] .= form_text('user_password', $this->locale['u135a'], '', [
                'type'             => 'password',
                'autocomplete_off' => 1,
                'inline'           => $this->inline_field,
                'max_length'       => 64,
                'error_text'       => $this->locale['u133']
            ]);
            $info['user_password'] .= form_text('user_password1', $this->locale['u135b'], '', [
                'type'             => 'password',
                'autocomplete_off' => 1,
                'inline'           => $this->inline_field,
                'max_length'       => 64,
                'error_text'       => $this->locale['u133'],
            ]);
            $info['user_password'] .= form_text('user_password2', $this->locale['u135c'], '', [
                'class'            => 'm-b-0',
                'type'             => 'password',
                'autocomplete_off' => 1,
                'inline'           => $this->inline_field,
                'max_length'       => 64,
                'error_text'       => $this->locale['u133']
            ]);
            $info['user_password'] .= "<div class='clearfix text-right m-b-20 p-r-15'>".form_button('gen_user_passwd',
                    "Generate Password", "", [
                        'type'  => 'button',
                        'class' => "btn-default spacer-sm m-b-0",
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
        $info = [];
        if ($this->registration === TRUE || $this->is_admin_panel === TRUE) {
            
            $input_value = isset($_POST['user_admin_password1']) ? $_POST['user_admin_password1'] : '';
            $info['user_admin_password'] = form_text('user_admin_password1', $this->locale['u144'], $input_value, [
                    'type'             => 'password',
                    'autocomplete_off' => FALSE,
                    'inline'           => $this->inline_field,
                    'max_length'       => 64,
                    'error_text'       => $this->locale['u136']
                ]
            );
            $info['user_admin_password'] .= form_text('user_admin_password2', $this->locale['u145'], '', [
                'class'            => 'm-b-0',
                'type'             => 'password',
                'autocomplete_off' => 1,
                'inline'           => $this->inline_field,
                'max_length'       => 64,
                'error_text'       => $this->locale['u136']
            ]);
            $info['user_admin_password'] .= "<div class='clearfix text-right m-b-20 p-r-15'>".form_button('gen_admin_passwd', "Generate Password", "", [
                    'type'  => 'button',
                    'class' => "btn-default spacer-sm m-b-0",
                    'icon'  => 'fas fa-key',
                    'data'  => [
                        'size'          => 16,
                        'character-set' => "a-z,A-Z,0-9,#",
                    ]
                ])."</div>\n";
            $info['user_admin_password_notice'] = "<p>To update user admin password, enter a new password <a class='field_focus' data-focus='user_admin_password1' href='#'>new admin password field</a>. <strong>This action will override current user password.</strong></p>";
    
        } else {
            
            if (iADMIN) {
//                $info['user_admin_password'] = "<h4>".$this->locale['u131']."</h4>\n<br/>";
                if ($this->user_data['user_admin_password']) {
        
                    $info['user_admin_password_notice'] = "<p>To change your admin password, enter your <a class='field_focus' data-focus='user_admin_password' href='#'>current admin password</a>, <a class='field_focus' data-focus='user_admin_password1' href='#'>new admin password</a> and <a class='field_focus' data-focus='user_admin_password2' href='#'>confirm new admin password</a>.</p>";
                    // This is for changing password
                    $info['user_admin_password'] = form_text('user_admin_password', $this->locale['u144a'], '', [
                            'type'             => 'password',
                            'autocomplete_off' => 1,
                            'inline'           => $this->inline_field,
                            'max_length'       => 64,
                            'error_text'       => $this->locale['u136']
                        ]
                    );
                    $info['user_admin_password'] .= form_text('user_admin_password1', $this->locale['u144'], '', [
                            'type'               => 'password',
                            'autocomplete_off'   => 1,
                            'inline'             => $this->inline_field,
                            'max_length'         => 64,
                            'error_text'         => $this->locale['u136'],
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
        
                    $info['user_admin_password'] .= form_text('user_admin_password', $this->locale['u144'], '', [
                            'type'             => 'password',
                            'autocomplete_off' => 1,
                            'inline'           => $this->inline_field,
                            'max_length'       => 64,
                            'error_text'       => $this->locale['u136']
                        ]
                    );
                }
                $info['user_admin_password'] .= form_text('user_admin_password2', $this->locale['u145'], '', [
                    'class'            => 'm-b-0',
                    'type'             => 'password',
                    'autocomplete_off' => 1,
                    'inline'           => $this->inline_field,
                    'max_length'       => 64,
                    'error_text'       => $this->locale['u136']
                ]);
                $info['user_admin_password'] .= "<div class='clearfix text-right'>".form_button('gen_admin_passwd', "Generate Password", "", [
                        'type'  => 'button',
                        'class' => "btn-default spacer-sm m-b-0",
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