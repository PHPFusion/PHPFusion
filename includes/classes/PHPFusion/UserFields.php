<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: UserFields.php
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

use PHPFusion\Userfields\Accounts\AccountsForm;
use PHPFusion\Userfields\Privacy\PrivacyForm;

/**
 * Class UserFields
 *
 * @package PHPFusion
 */
class UserFields extends QuantumFields {

    public $userData = [
        'user_id'             => '',
        'user_name'           => '',
        'user_firstname'      => '',
        'user_lastname'       => '',
        'user_addname'        => '',
        'user_phone'          => '',
        'user_hide_phone'     => 1,
        'user_bio'            => '',
        'user_password'       => '',
        'user_admin_password' => '',
        'user_email'          => '',
        'user_hide_email'     => 1,
        'user_language'       => LANGUAGE,
        'user_timezone'       => 'Europe/London'
    ];

    public $displayTerms = 0;

    public $displayValidation = 0;

    public $postName;

    public $postValue;

    public $showAdminOptions = FALSE;

    public $showAdminPass = TRUE;

    public $showAvatarInput = TRUE;

    public $baseRequest = FALSE; // new in API 1.02 - turn fusion_self to fusion_request - 3rd party pages. Turn this on if you have more than one $_GET pagination str.

    public $skipCurrentPass = FALSE;

    public $registration = FALSE;

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

    /**
     * Sets moderation mode - previously admin_mode
     *
     * @var bool
     */
    public $moderation = 0;

    public $inputInline = TRUE;

    public $options = [];

    public $username_change = TRUE;

    private $info = [
        'terms'               => '',
        'validate'            => '',
        'user_avatar'         => '',
        'user_admin_password' => '',
    ];

    public $defaultInputOptions = [];

    /**
     * Display Input Fields
     */
    public function displayProfileInput() {

        $this->method = 'input';
        $this->options += $this->defaultInputOptions;

        $this->info = match (get( 'section' )) {
            default => $this->displayAccountInput(),
            'notifications' => $this->getEmptyInputInfo() + $this->displayNotificationInput(),
            'privacy' => $this->getEmptyInputInfo() + $this->displayPrivacyInput(),
        };

        /*
         * Template Output
         */
        $this->registration ? display_register_form( $this->info ) : display_profile_form( $this->info );
    }

    /**
     * Notification input
     * @return array
     */
    private function displayNotificationInput() {

        $locale = fusion_get_locale();

        return [
            'user_comments_notify'    => form_checkbox( 'n_comments', $locale['u502'], $this->userData['user_comments_notify'], ['toggle' => TRUE, 'ext_tip' => $locale['u503'], 'class' => 'form-check-lg'] ),
            'user_tag_notify'        => form_checkbox( 'n_tags', $locale['u504'], $this->userData['user_tag_notify'], ['toggle' => TRUE, 'ext_tip' => $locale['u505'], 'class' => 'form-check-lg'] ),
            'user_newsletter_notify' => form_checkbox( 'n_newsletter', $locale['u506'], $this->userData['user_newsletter_notify'], ['toggle' => TRUE, 'ext_tip' => $locale['u507'], 'class' => 'form-check-lg'] ),
            'user_follow_notify'     => form_checkbox( 'n_follow', $locale['u508'], $this->userData['user_follow_notify'], ['toggle' => TRUE, 'ext_tip' => $locale['u509'], 'class' => 'form-check-lg'] ),
            'user_pm_notify'         => form_checkbox( 'n_pm', $locale['u510'], $this->userData['user_pm_notify'], ['toggle' => TRUE, 'ext_tip' => $locale['u511'], 'class' => 'form-check-lg'] ),
            'user_pm_email'          => form_checkbox( 'e_pm', $locale['u510'], $this->userData['user_pm_email'] ),
            'user_follow_email'      => form_checkbox( 'e_follow', $locale['u514'], $this->userData['user_follow_email'] ),
            'user_feedback_email'    => form_checkbox( 'e_feedback', $locale['u515'], $this->userData['user_feedback_email'] ),
            'user_email_duration'    => form_checkbox( 'e_duration', $locale['u516'], $this->userData['user_email_duration'], [
                'type'    => 'radio',
                'options' => [
                    '1' => $locale['u517'],
                    '2' => $locale['u518'],
                    '3' => $locale['u519'],
                    '0' => $locale['u520'],
                ],
                'class'   => 'form-check-lg'
            ] ),
            'notify_button'          => form_button( 'save_notify', $locale['save_changes'], 'save_notify', ['class' => 'btn-primary'] ),
        ];
    }

    /**
     * Privacy Input
     *
     * @return array
     */
    private function displayPrivacyInput() {
        $input = new PrivacyForm($this);
        return $input->displayInputFields();
    }

    private function getEmptyInputInfo() {
        return [
            'section'   => $this->getProfileSections(),
            'userdata'  => $this->userData,
            'user_id'   => form_hidden( 'user_id', '', $this->userData["user_id"] ),
            'user_hash' => form_hidden( 'user_hash', '', $this->userData['user_password'] ),
        ];
    }



    /**
     * Account Input
     *
     * @return array|string[]
     */
    private function displayAccountInput() {

        $input = new AccountsForm( $this );
        $locale = fusion_get_locale();

        $this->info = [
            'section'             => $this->getProfileSections(),
            'show_avatar'         => $this->showAvatarInput,
            'user_id'             => form_hidden( 'user_id', '', $this->userData["user_id"] ),
            'user_hash'           => form_hidden( 'user_hash', '', '' ),
            'user_name'           => '',
            'user_firstname'      => '',
            'user_lastname'       => '',
            'user_addname'        => '',
            'user_phone'          => '',
            'user_bio'            => '',
            'user_password'       => '', //form_hidden( 'user_hash', '', $this->userData['user_hash'] ),
            'user_admin_password' => '',
            'user_email'          => '',
            'user_hide_email'     => '',
            'user_avatar'         => '',
            'validate'            => '',
            'terms'               => '',
        ];

        $this->info['user_name'] = $input->usernameInputField();
        $this->info['user_firstname'] = form_text( 'user_firstname', $locale['u010'], $this->userData['user_firstname'], ['inline' => $this->inputInline] );
        $this->info['user_lastname'] = form_text( 'user_lastname', $locale['u011'], $this->userData['user_lastname'], ['inline' => $this->inputInline] );
        $this->info['user_addname'] = form_text( 'user_addname', $locale['u012'], $this->userData['user_addname'], ['inline' => $this->inputInline] );
        $this->info['user_phone'] = form_text( 'user_phone', $locale['u013'], $this->userData['user_phone'], ['inline' => $this->inputInline, 'placeholder' => '(678) 3241521'] );
        $this->info['user_hide_phone'] = $input->phoneHideInputField();
        $this->info['user_bio'] = form_textarea( 'user_bio', $locale['u015'], $this->userData['user_bio'], ['inline' => $this->inputInline, 'wordcount' => TRUE, 'maxlength' => 255] );
        //$this->info['user_password'] = form_para( $locale['u132'], 'password', 'profile_category_name' );
        $this->info['user_password'] = $input->passwordInputField();
        //$this->info['user_admin_password'] = $locale['u131'];
        $this->info['user_admin_password'] = $input->adminpasswordInputField();
        $this->info['user_email'] = $input->emailInputField();
        $this->info['user_hide_email'] = $input->emailHideInputField();
        $this->info['user_avatar'] = $input->avatarInput();
        $this->info['validate'] = $input->captchaInput();
        $this->info['terms'] = $input->termInput();
        $this->info['button'] = $input->renderButton();

        if ($this->method == 'validate_update') {
            // User Password Verification for Email Change
            $footer = openmodal( 'verifyPassword', 'Verify password', ['hidden' => TRUE] )
                . '<p class="small">Your password is required to proceed. Please enter your current password to update your profile.</p>'
                . form_text( 'user_verify_password', $locale['u135a'], '', ['required' => TRUE, 'type' => 'password', 'autocomplete_off' => TRUE, 'max_length' => 64, 'error_text' => $locale['u133'], 'placeholder' => $locale['u100'],] )
                . modalfooter( form_button( 'confirm_password', $locale['save_changes'], 'confirm_password', ['id' => 'updateProfilePass', 'class' => 'btn-primary'] ) )
                . closemodal();

            add_to_footer( $footer );

            // Port to edit profile.js
            add_to_jquery( "            
            var submitCallModal = function(dom) {
               var form = dom.closest('form'), hashInput = form.find('input[name=\"user_hash\"]');                                   
                $('button[name=\"" . $this->postName . "_btn\"]').on('click', function(e) {
                   e.preventDefault();
                   $(this).prop('disabled', true);                                    
                   $('#verifyPassword-Modal').modal('show');
                   $('#user_verify_password').on('input propertychange paste', function() {                        
                        hashInput.val( $(this).val() );                                                
                   });                 
                   $('button[name=\"confirm_password\"]').on('click', function() {
                        $('#verifyPassword-Modal').modal('hide');
                        form[0].submit();
                   });                                                    
                });                           
            };
            
            var email = $('#user_email').val();            
            $('#user_email').on('input propertychange paste', function() {
                var requireModal = false;
                if ($(this).val() != email) {
                    requireModal = true;
                } else {
                    requireModal = false;
                }
                if (requireModal) {
                    // when postname button is clicked, require the modal.                    
                    submitCallModal($(this));
                }                                     
            });           
            " );
        }

        $this->info = $this->info + $this->getUserFields();

        /*
         * Template
         */
        $this->info['user_custom'] = $this->getCustomFields();

        return $this->info;
    }

    public function getCustomFields() {
        $user_fields = '';
        if (!empty( $this->info['user_field'] ) && is_array( $this->info['user_field'] )) {
            foreach ($this->info['user_field'] as $catID => $fieldData) {
                if (!empty( $fieldData['title'] )) {
                    $user_fields .= form_para( $fieldData['title'], 'fieldcat' . $catID );
                }
                if (!empty( $fieldData['fields'] )) {
                    $user_fields .= implode( '', $fieldData['fields'] );
                }
            }
        }

        return $user_fields;
    }

    /**
     * @return array
     */
    private function getProfileSections() {

        $link_prefix = BASEDIR . 'edit_profile.php?section=';
        if ($this->moderation) {
            $link_prefix = ADMIN . 'members.php?lookup=' . $this->userData['user_id'] . '&action=edit&';
        }

        return [
            'account'        => ['link' => $link_prefix . 'account', 'title' => 'Account'],
            'notifications'  => ['link' => $link_prefix . 'notifications', 'title' => 'Notifications'],
            'privacy'        => ['link' => $link_prefix . 'privacy', 'title' => 'Privacy and safety'],
            'communications' => ['link' => $link_prefix . 'communications', 'title' => 'Communications'],
            'message'        => ['link' => $link_prefix . 'message', 'title' => 'Messaging'],
            'close'          => ['link' => $link_prefix . 'close', 'title' => 'Close account']
        ];

    }


    /**
     * Fetch User Fields Array to templates
     * Toggle with class string method - input or display
     * output to array
     */
    private function getUserFields() {
        $fields = [];
        $category = [];
        $item = [];

        $this->callback_data = $this->userData;

        switch ($this->method) {
            case 'input':
                if ($this->registration == FALSE) {
                    if (isset( $this->info['user_field'][0]['fields']['user_name'] )) {
                        $this->info['user_field'][0]['fields']['user_name'] = form_hidden( 'user_name', '', $this->callback_data['user_name'] );
                    }
                }
                break;
            case 'display':
                $this->info['user_field'] = [];
        }

        $index_page_id = isset( $_GET['section'] ) && isnum( $_GET['section'] ) && isset( $this->getProfileSections()[$_GET['section']] ) ? intval( $_GET['section'] ) : 1;

        $registration_cond = ($this->registration == TRUE ? ' AND field.field_registration=:field_register' : '');
        $registration_bind = ($this->registration == TRUE ? [':field_register' => 1] : []);

        $query = "SELECT field.*, cat.field_cat_id, cat.field_cat_name, cat.field_parent, root.field_cat_id as page_id, root.field_cat_name as page_name, root.field_cat_db, root.field_cat_index
                  FROM " . DB_USER_FIELDS . " field
                  INNER JOIN " . DB_USER_FIELD_CATS . " cat ON (cat.field_cat_id = field.field_cat)
                  INNER JOIN " . DB_USER_FIELD_CATS . " root on (cat.field_parent = root.field_cat_id)
                  WHERE (cat.field_cat_id=:index00 OR root.field_cat_id=:index01) $registration_cond
                  ORDER BY root.field_cat_order, cat.field_cat_order, field.field_order
                  ";
        $bind = [
            ':index00' => $index_page_id,
            ':index01' => $index_page_id,
        ];
        $bind = $bind + $registration_bind;
        $result = dbquery( $query, $bind );
        $rows = dbrows( $result );
        if ($rows != '0') {
            while ($data = dbarray( $result )) {
                if ($data['field_cat_id']) {
                    $category[$data['field_parent']][$data['field_cat_id']] = self::parseLabel( $data['field_cat_name'] );
                }
                if ($data['field_cat']) {
                    $item[$data['field_cat']][] = $data;
                }
            }
            if (isset( $category[$index_page_id] )) {
                foreach ($category[$index_page_id] as $cat_id => $cat) {

                    if ($this->registration || $this->method == 'input') {

                        if (isset( $item[$cat_id] )) {

                            $fields['user_field'][$cat_id]['title'] = $cat;

                            foreach ($item[$cat_id] as $field) {
                                $options = [
                                    'show_title' => TRUE,
                                    'inline'     => $this->inputInline,
                                    'required'   => (bool)$field['field_required']
                                ];
                                if ($field['field_type'] == 'file') {
                                    $options += [
                                        'plugin_folder'        => $this->plugin_folder,
                                        'plugin_locale_folder' => $this->plugin_locale_folder
                                    ];
                                }

                                $field_output = $this->displayFields( $field, $this->callback_data, $this->method, $options );

                                $fields['user_field'][$cat_id]['fields'][$field['field_id']] = $field_output;
                                $fields['extended_field'][$field['field_name']] = $field_output; // for the gets
                            }
                        }
                    } else {

                        // Display User Fields

                        if (isset( $item[$cat_id] )) {

                            $fields['user_field'][$cat_id]['title'] = $cat;

                            foreach ($item[$cat_id] as $field) {

                                // Outputs array
                                $field_output = $this->displayFields( $field, $this->callback_data, $this->method );

                                //$fields['user_field'][$cat_id]['fields'][$field['field_id']] = $field_output; // relational to the category
                                $fields['extended_field'][$field['field_name']] = $field_output; // for the gets

                                if (!empty( $field_output )) {
                                    $fields['user_field'][$cat_id]['fields'][$field['field_id']] = array_merge( $field, $field_output );
                                }
                            }
                        }
                    }
                }
            }
        }

        return $fields;
    }

    /***
     * Fetch profile output data
     * Display Profile (View)
     */
    public function displayProfileOutput() {

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $lookup = get( 'lookup', FILTER_VALIDATE_INT );

        // Add User to Groups
        if (iADMIN && checkrights( "UG" ) && get( 'lookup', FILTER_VALIDATE_INT ) !== fusion_get_userdata( 'user_id' )) {

            if (check_post( 'add_to_group' ) && $user_group = post( 'user_group', FILTER_VALIDATE_INT )) {

                if (!preg_match( "(^\.$user_group$|\.$user_group\.|\.$user_group$)", $this->userData['user_groups'] )) {
                    $userdata = [
                        'user_groups' => $this->userData['user_groups'] . "." . $user_group,
                        'user_id'     => $lookup
                    ];
                    dbquery_insert( DB_USERS, $userdata, 'update' );
                }

                if (defined( 'ADMIN_PANEL' ) && get( 'step' ) === 'view') {
                    redirect( ADMIN . "members.php" . fusion_get_aidlink() . "&amp;step=view&amp;user_id=" . $this->userData['user_id'] );
                } else {
                    redirect( BASEDIR . "profile.php?lookup=" . $lookup );
                }

            }
        }

        $this->info['section'] = $this->getProfileSections();

        $this->info['user_id'] = $this->userData['user_id'];

        $this->info['user_name'] = $this->userData['user_name'];

        $current_section = ['id' => 1];
        if (!empty( $this->info['section'] )) {
            $current_section = current( $this->info['section'] );
        }

        $_GET['section'] = isset( $_GET['section'] ) && isset( $this->info['section'][$_GET['section']] ) ? $_GET['section'] : $current_section['id'];

        if (empty( $this->userData['user_avatar'] ) && !file_exists( IMAGES . "avatars/" . $this->userData['user_avatar'] )) {
            $this->userData['user_avatar'] = get_image( 'noavatar' );
        }

        $this->info['core_field']['profile_user_avatar'] = [
            'title'  => $locale['u186'],
            'value'  => $this->userData['user_avatar'],
            'status' => $this->userData['user_status']
        ];

        // username
        $this->info['core_field']['profile_user_name'] = [
            'title' => $locale['u068'],
            'value' => $this->userData['user_name']
        ];

        // user level
        $this->info['core_field']['profile_user_level'] = [
            'title' => $locale['u063'],
            'value' => getgroupname( $this->userData['user_level'] )
        ];

        // user email
        if (iADMIN || $this->userData['user_hide_email'] == 0) {
            $this->info['core_field']['profile_user_email'] = [
                'title' => $locale['u064'],
                'value' => hide_email( $this->userData['user_email'], fusion_get_locale( "UM061a" ) )
            ];
        }

        // user joined
        $this->info['core_field']['profile_user_joined'] = [
            'title' => $locale['u066'],
            'value' => showdate( "longdate", $this->userData['user_joined'] )
        ];

        // Last seen
        $this->info['core_field']['profile_user_visit'] = [
            'title' => $locale['u067'],
            'value' => $this->userData['user_lastvisit'] ? showdate( "longdate", $this->userData['user_lastvisit'] ) : $locale['u042']
        ];

        // user status
        if (iADMIN && $this->userData['user_status'] > 0) {
            $this->info['core_field']['profile_user_status'] = [
                'title' => $locale['u055'],
                'value' => getuserstatus( $this->userData['user_status'], $this->userData['user_lastvisit'] )
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
        if (iADMIN && checkrights( "M" )) {
            $this->info['core_field']['profile_user_ip'] = [
                'title' => $locale['u049'],
                'value' => $this->userData['user_ip']
            ];
        }

        // Groups - need translating.
        $this->info['core_field']['profile_user_group']['title'] = $locale['u057'];
        $this->info['core_field']['profile_user_group']['value'] = '';
        $user_groups = strpos( $this->userData['user_groups'], "." ) == 0 ? substr( $this->userData['user_groups'], 1 ) : $this->userData['user_groups'];
        $user_groups = explode( ".", $user_groups );
        $user_groups = (array)array_filter( $user_groups );

        $group_info = [];
        if (!empty( $user_groups )) {
            for ($i = 0; $i < count( $user_groups ); $i++) {
                if ($group_name = getgroupname( $user_groups[$i] )) {
                    $group_info[] = [
                        'group_url'  => BASEDIR . "profile.php?group_id=" . $user_groups[$i],
                        'group_name' => $group_name
                    ];
                }
            }
            $this->info['core_field']['profile_user_group']['value'] = $group_info;
        }

        $this->info = $this->info + $this->getUserFields();

        if (iMEMBER && fusion_get_userdata( 'user_id' ) != $this->userData['user_id']) {

            $this->info['buttons'] = [
                'user_pm_title' => $locale['u043'],
                'user_pm_link'  => BASEDIR . "messages.php?msg_send=" . $this->userData['user_id']
            ];

            if (checkrights( 'M' ) && fusion_get_userdata( 'user_level' ) <= USER_LEVEL_ADMIN && $this->userData['user_id'] != '1') {
                $groups_cache = cache_groups();
                $user_groups_opts = [];
                $this->info['user_admin'] = [
                    'user_edit_title'     => $locale['edit'],
                    'user_edit_link'      => ADMIN . "members.php" . $aidlink . "&amp;ref=edit&amp;lookup=" . $this->userData['user_id'],
                    'user_ban_title'      => $this->userData['user_status'] == 1 ? $locale['u074'] : $locale['u070'],
                    'user_ban_link'       => ADMIN . "members.php" . $aidlink . "&amp;action=" . ($this->userData['user_status'] == 1 ? 2 : 1) . "&amp;lookup=" . $this->userData['user_id'],
                    'user_suspend_title'  => $locale['u071'],
                    'user_suspend_link'   => ADMIN . "members.php" . $aidlink . "&amp;action=3&amp;lookup=" . $this->userData['user_id'],
                    'user_delete_title'   => $locale['delete'],
                    'user_delete_link'    => ADMIN . "members.php" . $aidlink . "&amp;ref=delete&amp;lookup=" . $this->userData['user_id'],
                    'user_delete_onclick' => "onclick=\"return confirm('" . $locale['delete'] . "');\"",
                    'user_susp_title'     => $locale['u054'],
                    'user_susp_link'      => ADMIN . "members.php" . $aidlink . "&amp;ref=log&amp;lookup=" . $this->userData['user_id']
                ];
                if (count( $groups_cache ) > 0) {
                    foreach ($groups_cache as $group) {
                        if (!preg_match( "(^{$group['group_id']}|\.{$group['group_id']}\.|\.{$group['group_id']}$)", $this->userData['user_groups'] )) {
                            $user_groups_opts[$group['group_id']] = $group['group_name'];
                        }
                    }
                    if (iADMIN && checkrights( "UG" ) && !empty( $user_groups_opts )) {
                        $submit_link = BASEDIR . "profile.php?lookup=" . $this->userData['user_id'];
                        if (defined( 'ADMIN_PANEL' ) && isset( $_GET['step'] ) && $_GET['step'] == "view") {
                            $submit_link = ADMIN . "members.php" . $aidlink . "&amp;step=view&amp;user_id=" . $this->userData['user_id'] . "&amp;lookup=" . $this->userData['user_id'];
                        }
                        $this->info['group_admin']['ug_openform'] = openform( "admin_grp_form", "post", $submit_link );
                        $this->info['group_admin']['ug_closeform'] = closeform();
                        $this->info['group_admin']['ug_title'] = $locale['u061'];
                        $this->info['group_admin']['ug_dropdown_input'] = form_select( "user_group", '', "", ["options" => $user_groups_opts, "width" => "100%", "inner_width" => "100%", "inline" => FALSE, 'class' => 'm-0'] );
                        $this->info['group_admin']['ug_button'] = form_button( "add_to_group", $locale['u059'], $locale['u059'] );
                    }
                }
            }
        }

        // Display Template
        display_user_profile( $this->info );
    }

}
