<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user-forms.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Administration\Members;

use PHPFusion\Geomap;
use PHPFusion\UserFieldsQuantum;

/**
 * Class UserAccount
 *
 * @package PHPFusion
 */
class UserForms {

    public $user_data = [
        'user_name'        => '',
        'user_displayname' => '',
        'user_display'     => '',
        'user_level'       => 0,
        'user_firstname'   => '',
        'user_lastname'    => '',
        'user_birthdate'   => '',
        'user_email'       => '',
        'user_hide_email'  => '',
        'user_bio'         => '',
        'user_avatar'      => '',
        'user_language'    => LANGUAGE,
        'user_location'    => '',
        'user_password'    => '',
        'user_salt'        => '',
        'user_algo'        => '',
    ];

    private $helper = NULL;

    const VERIFY_USER_EMAIL = 0;    // waiting for review email
    const VERIFY_USER_REVIEW = 1;   // specific for administrator to approve after review email
    const VERIFY_USER_INACTIVE = 2; // did not validate code within specific timeframe
    const VERIFY_USER_REJECTED = 3; // banned and cannot pass validation code.

    public function __construct() {
        $this->helper = new User_Helper($this);
    }

    public function adminEdit() {
        $tab['title'][] = 'Profile';
        $tab['id'][] = 'basic';
        $tab['title'][] = 'User Fields';
        $tab['id'][] = 'field';
        $tab_active = tab_active($tab, 0, 'section');
        $html = opentab($tab, $tab_active, 'profile-tab', TRUE, FALSE, 'section');
        foreach ($tab['id'] as $index => $id) {
            $html .= opentabbody($tab['title'][$index], $id, $tab_active, TRUE, 'section');
            switch ($id) {
                case 'basic':
                    $html .= $this->basicEdit();
                    break;
                case 'field':
                    $html .= $this->fieldEdit();
                    break;
            }
            $html .= closetabbody();
        }
        $html .= closetab();
        return (string)$html;
    }

    private function updateBasicProfile() {

        if (post('update_profile')) {

            $user_data = [
                'user_id'          => $this->user_data['user_id'],
                'user_name'        => $this->helper->checkUserName(),
                'user_email'       => $this->helper->checkUserEmail(),
                'user_displayname' => sanitizer('user_displayname', '', 'user_displayname'),
                'user_display'     => sanitizer('user_display', '', 'user_display'),
                'user_firstname'   => sanitizer('user_firstname', '', 'user_firstname'),
                'user_lastname'    => sanitizer('user_lastname', '', 'user_lastname'),
                'user_birthdate'   => sanitizer('user_birthdate', '', 'user_birthdate'),
                'user_hide_email'  => sanitizer('user_hide_email', 0, 'user_hide_email'),
                'user_bio'         => sanitizer('user_bio', '', 'user_bio'),
                'user_location'    => sanitizer('user_location', '', 'user_location'),
                'user_language'    => sanitizer('user_language', '', 'user_language'),
            ];

            $user_password = $this->helper->checkUserPass(TRUE);

            if (!empty($user_password['user_password']) && !empty($user_password['user_algo']) && !empty($user_password['user_salt'])) {
                $user_data['user_password'] = $user_password['user_password'];
                $user_data['user_algo'] = $user_password['user_algo'];
                $user_data['user_salt'] = $user_password['user_salt'];
                $new_pass = TRUE;
            }

            // handles user avatar upload
            $user_avatar = $this->helper->checkUserAvatar();
            if (!empty($user_avatar)) {
                $user_data['user_avatar'] = $user_avatar;
            }

            $this->user_data = $user_data;

            if (fusion_safe()) {

                dbquery_insert(DB_USERS, $this->user_data, 'update');

                // send email.
                if (isset($new_pass)) {
                    $this->helper->sendNewPasswordEmail();
                }

                add_notice('success', 'User profile has been updated.');
                redirect(FUSION_REQUEST);
            }
        }

        return FALSE;

    }

    private function basicEdit() {

        $html = '';

        if (!$this->updateBasicProfile()) {

            $usrname_display[0] = $this->user_data['user_name'];
            if (!empty($this->user_data['user_displayname'])) {
                $usrname_display[1] = $this->user_data['user_displayname'];
            }

            $html = openform('profile-form', 'post', FORM_REQUEST, ['enctype' => TRUE]);

            $html .= '<div class="'.grid_row().'">';

            $html .= '<div class="'.grid_column_size(100, 70, 70, 50).'">';

            $html .= form_para('Names', 'p-names');

            $html .= form_text('user_name', 'User Name', $this->user_data['user_name'], ['required' => TRUE, 'inline' => TRUE]);

            $html .= form_text('user_displayname', 'User Display Name', $this->user_data['user_displayname'], ['inline' => TRUE]);

            $html .= form_select('user_display', 'Display name publicly as', $this->user_data['user_display'], ['inline' => TRUE, 'options' => $usrname_display, 'select_alt' => TRUE]);

            $html .= form_text('user_firstname', 'First Name', $this->user_data['user_firstname'], ['inline' => TRUE]);

            $html .= form_text('user_lastname', 'Last Name', $this->user_data['user_lastname'], ['inline' => TRUE]);

            $html .= form_datepicker('user_birthdate', 'Birthdate', $this->user_data['user_birthdate'], [
                    'inline'          => TRUE,
                    'type'            => 'date',
                    'date_format_js'  => 'DD MMMM, YYYY',
                    'date_format_php' => 'Y-m-d',
                    'width'           => '250px']
            );

            // this one need to change.
            $html .= '<div class="form-group"><label class="control-label '.grid_column_size(100, 100, 20, 20).'">New Password</label>
            <div class="'.grid_column_size(100, 100, 80, 80).'">
            '.form_button('generate_pass', 'Generate Password', 1, [
                    'type' => 'button',
                    'data' => [
                        'size'          => 16,
                        'character-set' => "a-z,A-Z,0-9,#",
                    ]
                ]).form_button('hideShowPass', 'Hide', 1, ['icon' => 'fas fa-eye-slash']).form_button('cancelPass', 'Cancel', 'cancelPass').
                form_text('user_password', '', '', ['type' => 'password', 'password_strength' => TRUE, 'class' => 'm-t-5']).'
                </div></div>';

            $html .= form_para('Contact', 'p-contact');

            $html .= form_text('user_email', 'Email address', $this->user_data['user_email'], ['inline' => TRUE, 'type' => 'email', 'ext_tip' => 'If you change this we will send you an email at your new address to confirm it. The new address will not become active until confirmed.']);

            $html .= form_checkbox('user_hide_email', 'Hide Email?', $this->user_data['user_hide_email'], ['inline' => TRUE, 'options' => [0 => 'No', 1 => 'Yes'], 'type' => 'radio', 'inline_options' => TRUE]);

            $html .= form_para('About', 'p-about');

            $html .= form_textarea('user_bio', 'User Biography', $this->user_data['user_bio'], ['inline' => TRUE]);

            $html .= form_para('Account', 'p-account');

            $html .= form_select('user_location', 'Location', $this->user_data['user_location'], ['inline' => TRUE, 'input_id' => 'ulc', 'select_alt' => TRUE, 'options' => Geomap::get_Country()]);

            $html .= form_select('user_language', 'Language', $this->user_data['user_language'], ['inline' => TRUE, 'select_alt' => TRUE, 'options' => fusion_get_enabled_languages()]);

            $html .= '</div><div class="'.grid_column_size(100, 30, 30, 50).'">';

            $html .= form_fileinput('user_avatar', 'Profile Picture', $this->user_data['user_avatar'], [
                'input_id'             => 'avatar-fileinput',
                'upload_path'          => IMAGES.'avatars/',
                'placeholder'          => 'Upload Avatar',
                'required'             => FALSE,
                'preview_off'          => FALSE,
                'type'                 => 'image', //// ['image', 'html', 'text', 'video', 'audio', 'flash', 'object']
                'width'                => '100%',
                'inline'               => FALSE,
                'btn_class'            => 'btn-success',
                'icon'                 => 'fa fa-upload',
                'jsonurl'              => FALSE,
                'valid_ext'            => '.jpg,.png,.PNG,.JPG,.JPEG,.gif,.GIF,.bmp,.BMP',
                'thumbnail'            => FALSE,
                'thumbnail_w'          => 300,
                'thumbnail_h'          => 300,
                'default_preview'      => IMAGES.'avatars/no-avatar.jpg',
                'max_width'            => 300,
                'max_height'           => 300,
                'max_byte'             => 1500000,
                'max_count'            => 1,
                'replace_upload'       => TRUE, // makes upload unique (i.e. overwrite instead of creating new)
                'croppie'              => TRUE,
                'croppie_resize'       => TRUE,
                'cropper_zoom'         => TRUE,
                'crop_viewport_width'  => 200, // 200px default
                'crop_viewport_height' => 200,
                'crop_box_width'       => 300, // 300 px default
                'crop_box_height'      => 300,
                'template'             => 'avatar',
            ]);

            $html .= '</div></div>';

            $html .= form_button('update_profile', 'Update Profile', 'update_profile', ['class' => 'btn-primary']);


            $html .= closeform();
            // default hides using css
            echo '<style>#user_password, #hideShowPass, #cancelPass, .pwstrength_viewport_progress { display:none; }</style>';
            // default hides using jquery
            $html .= "<script>$('#user_password, #hideShowPass, #cancelPass, .pwstrength_viewport_progress').hide();</script>";
            // password javascript
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

        $('#generate_pass').bind('click', function(e) {
            e.preventDefault();
            $('#user_password').val(randString('generate_pass'));
            $('#user_password').pwstrength('forceUpdate');
            $('#hideShowPass,#cancelPass,#user_password,.pwstrength_viewport_progress').show();
        });
        $('#hideShowPass').bind('click', function(e) {
            e.preventDefault();
            let type = 'text';
            if ($(this).val() == 1) {
                type = 'password';
                $(this).val(0);
                $(this).html('<i class=\"fas fa-eye m-r-10\"></i>Show');
            } else {
                $(this).val(1);
                  $(this).html('<i class=\"fas fa-eye-slash m-r-10\"></i>Hide');
            }
            $('#user_password').attr('type', type);
        });
        $('#cancelPass').bind('click', function(e) {
             e.preventDefault();
             $('#user_password').val('');
             $('#user_password, #hideShowPass,.pwstrength_viewport_progress').hide();
            $(this).hide();
        });
        ");
        }


        return (string)$html;
    }

    private function fieldEdit() {
        // edit fields ,, take from user fields? fuck u
        $field_info = $this->cacheUserFields();

        if (post('update_profile_fields')) {
            $user_data['user_id'] = $this->user_data['user_id'];
            foreach ($field_info as $cat_id => $fields) {
                if (!empty($fields['fields'])) {
                    foreach ($fields['fields'] as $field_id => $field) {
                        $user_data[$field['field_name']] = sanitizer($field['field_name'], $field['field_default'], $field['field_name']);
                    }
                }
            }
            $this->user_data = $user_data;
            if (fusion_safe()) {
                dbquery_insert(DB_USERS, $user_data, 'update', ['keep_session' => TRUE]);
                add_notice('success', 'Profile has been updated.');
                redirect(FUSION_REQUEST);
            }
        }


        $html = openform('profile-form', 'post', FORM_REQUEST, ['enctype' => TRUE]);
        $html .= '<div class="'.grid_row().'">';
        $html .= '<div class="'.grid_column_size(100, 100, 80, 80).'">';
        if (!empty($field_info)) {
            $quantum_class = new UserFieldsQuantum();
            $html .= opencollapse('user-fields');
            $count = 0;
            foreach ($field_info as $cat_id => $fields) {
                $html .= opencollapsebody($fields['title'], $cat_id, 'user-fields', !$count ? TRUE : FALSE);
                if (!empty($fields['fields'])) {
                    $field_count = 0;
                    $field_total_count = count($fields['fields']);
                    foreach ($fields['fields'] as $field_id => $field) {
                        $options = [
                            'show_title' => TRUE,
                            'inline'     => TRUE,
                            'required'   => (bool)$field['field_required']
                        ];
                        if ($field['field_type'] == 'file') {
                            $options += ['plugin_folder' => INCLUDES.'user_fields/public/'];
                        }
                        // print_p($field);
                        $html .= $quantum_class->displayFields($field, $this->user_data, 'input', $options);
                        if ($field_count !== $field_total_count - 1) {
                            $html .= '<hr/>';
                        }
                        $field_count++;
                    }
                } else {
                    $html .= '<div class="well">There are no user fields defined</div>';
                }
                $html .= closecollapsebody();
                $count++;
            }
            $html .= closecollapse();
        } else {
            $html .= '<div class="well">There are no user fields category defined</div>';
        }
        $html .= "<hr/>";
        $html .= form_button('update_profile_fields', 'Update Profile', 'update_profile_fields', ['class' => 'btn-primary']);
        $html .= '</div>';
        $html .= '</div></div>';
        $html .= closeform();

        return (string)$html;
    }

    private function cacheUserFields() {
        static $fields = [];
        $result = dbquery("SELECT uf.*, ufc.field_cat_name FROM ".DB_USER_FIELDS." uf INNER JOIN ".DB_USER_FIELD_CATS." ufc ON ufc.field_cat_id=uf.field_cat ORDER BY ufc.field_cat_order ASC, uf.field_order ASC");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $data['field_cat_name'] = fusion_parse_locale($data['field_cat_name']);
                $data['field_title'] = fusion_parse_locale($data['field_title']);
                $fields[$data['field_cat']]['title'] = $data['field_cat_name'];
                $fields[$data['field_cat']]['fields'][$data['field_id']] = $data;
            }
        }
        return $fields;
    }

    public function adminAdd() {

        if (post('add_user')) {

            $user_data = [
                'user_id'         => 0,
                'user_name'       => $this->helper->checkUserName(),
                'user_email'      => $this->helper->checkUserEmail(),
                'user_firstname'  => sanitizer('user_firstname', '', 'user_firstname'),
                'user_lastname'   => sanitizer('user_lastname', '', 'user_lastname'),
                'user_location'   => sanitizer('user_location', '', 'user_location'),
                'user_language'   => sanitizer('user_language', '', 'user_language'),
                'user_level'      => sanitizer('user_level', USER_LEVEL_MEMBER, 'user_level'),
                'user_hide_email' => sanitizer('user_hide_email', 0, 'user_hide_email'),
            ];

            $user_password = $this->helper->checkUserPass(FALSE);
            if (!empty($user_password)) {
                $user_data['user_password'] = $user_password['user_password'];
                $user_data['user_algo'] = $user_password['user_algo'];
                $user_data['user_salt'] = $user_password['user_salt'];
            }

            $this->user_data = $user_data;

            if (fusion_safe()) {
                if (post('send_email')) {
                    $this->helper->sendNewAccountEmail();
                } else {
                    dbquery_insert(DB_USERS, $this->user_data, 'save');
                    add_notice('success', 'User account has been created.');
                }
                redirect(FUSION_REQUEST);
            }

        }

        $html = openform('profile-form', 'post', FORM_REQUEST, ['enctype' => TRUE]);
        $html .= '<div class="'.grid_row().'">';
        $html .= '<div class="'.grid_column_size(100, 100, 80, 50).'">';
        $html .= form_text('user_name', 'User Name', $this->user_data['user_name'], ['required' => TRUE, 'inline' => TRUE, 'inner_width' => '400px']);
        $html .= form_text('user_firstname', 'First Name', $this->user_data['user_firstname'], ['inline' => TRUE, 'inner_width' => '400px']);
        $html .= form_text('user_lastname', 'Last Name', $this->user_data['user_lastname'], ['inline' => TRUE, 'inner_width' => '400px']);
        // this one need to change.
        $html .= '<div class="form-group overflow-hide"><label class="control-label '.grid_column_size(100, 100, 20, 20).'">New Password</label>
        <div class="'.grid_column_size(100, 100, 80, 80).'">
        '.form_button('generate_pass', 'Generate Password', 1, [
                'type' => 'button',
                'data' => [
                    'size'          => 16,
                    'character-set' => "a-z,A-Z,0-9,#",
                ]
            ]).
            form_text('user_password', '', post('user_password'), ['type' => 'password', 'password_strength' => TRUE, 'class' => 'm-t-5', 'required' => TRUE, 'inner_width' => '400px']).'
        </div></div>';

        $html .= form_text('user_email', 'Email address', $this->user_data['user_email'], ['required' => TRUE, 'inline' => TRUE, 'type' => 'email', 'inner_width' => '400px']);
        $html .= form_checkbox('user_hide_email', 'Hide Email?', $this->user_data['user_hide_email'], ['inline' => TRUE, 'options' => [0 => 'No', 1 => 'Yes'], 'type' => 'radio', 'inline_options' => TRUE]);
        $html .= '<div class="form-group overflow-hide"><label class="control-label '.grid_column_size(100, 100, 20, 20).'">Email notification</label>
        <div class="'.grid_column_size(100, 100, 80, 80).'">
        '.form_checkbox('send_email', 'Send new user an email about their account.', 1, ['reverse_label' => TRUE, 'class' => 'm-0']).'
        </div></div>';

        $html .= form_select('user_level', 'User Level', $this->user_data['user_level'], ['inline' => TRUE, 'select2_disabled' => TRUE, 'options' => $this->helper->getUserLevelOptions()]);
        $html .= form_select('user_location', 'Location', $this->user_data['user_location'], ['inline' => TRUE, 'select2_disabled' => TRUE, 'options' => Geomap::get_Country()]);
        $html .= form_select('user_language', 'Language', $this->user_data['user_language'], ['inline' => TRUE, 'select2_disabled' => TRUE, 'options' => fusion_get_enabled_languages()]);
        $html .= form_button('add_user', 'Add New User', 'add_user', ['class' => 'btn-primary']);
        $html .= '</div>';
        $html .= '</div></div>';
        $html .= closeform();

        echo "<style>.pwstrength_viewport_progress { width:400px; }</style>";
        if (empty($this->user_data['user_password'])) {
            // default hides using css
            echo '<style>#user_password, .pwstrength_viewport_progress { display:none; }</style>';
            // default hides using jquery
            $html .= "<script>$('#user_password,.pwstrength_viewport_progress').hide();</script>";
        }
        // password javascript
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

        $('#generate_pass').bind('click', function(e) {
            e.preventDefault();
            $('#user_password').val(randString('generate_pass'));
            $('#user_password').pwstrength('forceUpdate');
            $('#hideShowPass,#cancelPass,#user_password,.pwstrength_viewport_progress').show();
        });
        $('#hideShowPass').bind('click', function(e) {
            e.preventDefault();
            let type = 'text';
            if ($(this).val() == 1) {
                type = 'password';
                $(this).val(0);
                $(this).html('<i class=\"fas fa-eye m-r-10\"></i>Show');
            } else {
                $(this).val(1);
                  $(this).html('<i class=\"fas fa-eye-slash m-r-10\"></i>Hide');
            }
            $('#user_password').attr('type', type);
        });
        $('#cancelPass').bind('click', function(e) {
             e.preventDefault();
             $('#user_password').val('');
             $('#user_password, #hideShowPass,.pwstrength_viewport_progress').hide();
            $(this).hide();
        });
        ");

        return (string)$html;
    }

}
