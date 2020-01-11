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

use PHPFusion\UserFields\Pages\ProfileInput;
use PHPFusion\UserFields\Pages\ProfileOutput;
use ReflectionException;

/**
 * Class UserFields
 *
 * @package PHPFusion
 */
class UserFields extends UserFieldsQuantum {
    
    private $total_groups = 0;
    private $profile_id = 0;
    public $display_terms = 0;
    public $display_validation = 0;
    public $errors = [];
    public $form_action = FUSION_REQUEST; // changed in API 1.02
    public $form_name = "userfieldsform";
    public $post_name;
    public $post_value;
    public $inline_field = TRUE;
    public $show_admin_password = TRUE;
    public $skip_password = FALSE;
    public $registration = FALSE;
    public $user_data = [
        "user_id"             => '',
        "user_name"           => '',
        "user_password"       => '',
        "user_admin_password" => '',
        "user_email"          => '',
        'user_hide_email'     => 0,
        "user_language"       => LANGUAGE,
        'user_timezone'       => 'Europe/London',
        'user_reputation'     => 0,
        'user_joined'         => TIME,
    ];
    /* Quantum Fields Extensions */
    public $admin_rights = '';
    public $category_db = '';
    public $field_db = '';
    public $plugin_folder = '';
    public $plugin_locale_folder = '';
    public $debug = FALSE;
    public $method;
    public $user_name_change = FALSE;
    private static $pages = [];
    protected static $input_page = [];
    protected static $output_page = [];
    protected $info = [
        'terms'               => '',
        'validate'            => '',
        'user_avatar'         => '',
        'user_admin_password' => '',
    ];
    
    public $options = [];
    private static $instance = NULL;
    private $aidlink = '';
    
    /**
     * Get a user field instance
     *
     * @return null|UserFields
     */
    public static function getInstance() {
        if ( empty( self::$instance ) ) {
            self::$instance = new Static();
        }
        return self::$instance;
    }
    
    /**
     * UserFields constructor.
     * Note: leave constructor blank
     */
    public function __construct() {
        $this->aidlink = get( 'aid' ) && iADMIN && checkrights( 'M' ) ? fusion_get_aidlink() : '';
    }
    
    /**
     * Check whether a user field is available/installed
     *
     * @param $field_name
     *
     * @return bool
     */
    public function checkInstalledFields( $field_name ) {
        static $list;
        $result = dbquery( "SELECT field_name FROM ".DB_USER_FIELDS );
        if ( dbrows( $result ) > 0 ) {
            while ( $data = dbarray( $result ) ) {
                $list[] = $data['field_name'];
            }
        }
        return in_array( $field_name, $list ) ? TRUE : FALSE;
    }
    
    /**
     * Custom add files into the edit profile page.
     *
     * @param $key
     * @param $title
     * @param $file
     */
    public function addInputPage( $key, $title, $file ) {
        $_ref = get( 'ref' );
        self::$input_page[ $key ] = [
            'id'     => $key,
            'title'  => $title,
            'file'   => $file,
            'active' => $_ref == 'key' ? TRUE : FALSE,
        ];
    }
    
    /**
     * Add profile view output page
     *
     * @param $key   - unique id
     * @param $title - localized title of the page
     * @param $file  - the url of the file to be included
     */
    public function addOutputPage( $key, $title, $file ) {
        $_ref = get( 'profile_page' );
    
        self::$output_page[ $key ] = [
            'id'    => $key,
            'title' => $title,
            'file'  => $file,
            'class' => $_ref == $key ? TRUE : FALSE,
        ];
    }
    
    /**
     * Edit profile view sections
     *
     * @return array
     */
    public function getInputPages() {
        if ( empty( self::$pages ) ) {
            $_ref = get( 'ref' );
            $profile_page = [
                'pu_profile' => [ 'title' => 'Public Profile' ],
                'se_profile' => [ 'title' => 'Account Information' ]
            ];
            $profile_page = array_merge( $profile_page, self::$input_page );
            $count = 0;
            foreach ( $profile_page as $key => $arr ) {
                $profile_page[ $key ]['active'] = ( ( $_ref && $_ref == $key || !$_ref && !$count ) ? TRUE : FALSE );
                $profile_page[ $key ]['link'] = clean_request( 'ref='.$key, [ 'ref' ], FALSE );
                $count++;
            }
            self::$pages = $profile_page;
        }
        
        return (array)self::$pages;
    }
    
    /**
     * Current page - pu_profile , pu_security, pu_prefs etc. this is for edit profile only.
     *
     * @return mixed|string
     */
    public function getCurrentInputPage() {
        $all_pages = $this->getInputPages();
        $refs = get( 'ref' );
        return $refs && isset( $all_pages[ $refs ] ) ? $refs : 'pu_profile';
    }
    
    /**
     * @param $profile_id
     *
     * @return mixed|string
     */
    public function getCurrentOutputPage( $profile_id ) {
        $all_pages = $this->getOutputPages( $profile_id );
        $refs = get( "profile_page" );
        return $refs && isset( $all_pages[ $refs ] ) ? $refs : 'profile';
    }
    
    /**
     * @param $profile_id
     *
     * @return array
     */
    public function getOutputPages( $profile_id ) {
        static $profile_page = [];
        
        if ( empty( $profile_page ) ) {
            $get_page = get( 'profile_page' );
            $active_class = ' class="active"';
            // Core pages
            
            $profile_page = [
                'activity' => [ 'id'    => 'activity',
                                'title' => 'User Activity',
                                //'link'  => BASEDIR.'profile.php?lookup='.$profile_id.'&amp;profile_page=activity',
                                'class' => $get_page == 'activity' ? $active_class : ""
                ],
                'profile'  => [ 'id'    => 'profile',
                                'title' => 'User Profile',
                                //'link'  => BASEDIR.'profile.php?lookup='.$profile_id.'&amp;profile_page=profile',
                                'class' => $get_page == 'profile' ? $active_class : ""
                
                ],
                'friends'  => [ 'id'    => 'friends',
                                'title' => 'User Friends',
                                //'link'  => BASEDIR.'profile.php?lookup='.$profile_id.'&amp;profile_page=friends',
                                'class' => $get_page == 'friends' ? $active_class : ""
                ],
                'groups'   => [ 'id'    => 'groups',
                                'title' => 'User Groups <span class="badge">'.format_num( $this->total_groups ).'</span>',
                                //'link'  => BASEDIR.'profile.php?lookup='.$profile_id.'&amp;profile_page=groups',
                                'class' => $get_page == 'groups' ? $active_class : ""
                ]
            ];
            
            $profile_page = array_merge( $profile_page, self::$output_page );
            
            $output_pages_keys = array_keys( $profile_page );
            foreach ( $output_pages_keys as $key ) {
                $profile_page[ $key ]['link'] = BASEDIR.'profile.php?lookup='.$profile_id.'&amp;profile_page='.$key;
            }
            
        }
        return (array)$profile_page;
    }
    
    /**
     * @return bool
     */
    public function checkModAccess() {
        return ( checkrights( 'M' ) && fusion_get_userdata( 'user_level' ) <= USER_LEVEL_ADMIN && $this->profile_id != '1' || iSUPERADMIN ) ? TRUE : FALSE;
    }
    
    
    public function registerInfo() {
        if ( iMEMBER ) {
            redirect( BASEDIR.fusion_get_settings( 'opening_page' ) );
        }
        $settings = fusion_get_settings();
        
        $userProfile = new ProfileInput( $this );
        // Preconfigured behavior
        $userProfile->form_name = 'registerFrm';
        $userProfile->post_name = 'register';
        $userProfile->post_value = fusion_get_locale( 'u105' );
        $userProfile->user_data = fusion_get_userdata();
        $userProfile->user_name_change = fusion_get_settings( 'userNameChange' );
        $userProfile->skip_password = TRUE;
        $userProfile->registration = TRUE;
        $userProfile->display_terms = TRUE;
        $userProfile->display_validation = $settings['display_validation'];;
        $userProfile->inline_field = FALSE;
    
        // The input class
        $userInput = new UserFieldsInput();
        $userInput->post_name = 'register';
        $userInput->registration = TRUE;
        $userInput->skip_password = TRUE;
        $userInput->user_name_change = TRUE;
        $userInput->verifyNewEmail = TRUE;
        $userInput->user_data = fusion_get_userdata();
        $userInput->hide_user_email = TRUE; // make settings for this.
        $userInput->admin_activation = $settings['admin_activation'];
        $userInput->email_verification = $settings['email_verification'];
        $userInput->validation = $settings['display_validation'];
        $userInput->redirect_uri = BASEDIR.$settings['opening_page'];
        $userInput->saveInsert();
        
        return $userProfile->getInfo();
    }
    
    /**
     * CHECK 1 - OK
     * Edit Profile Info
     *
     * @return array
     * @throws ReflectionException
     */
    public function editProfileInfo() {
        
        if ( !iMEMBER ) {
            addNotice( 'info', 'Please sign into your account to view your profile page settings page.', 'all' );
            redirect( BASEDIR.fusion_get_settings( 'opening_page' ) );
        }
        
        $userProfile = new ProfileInput( $this );
        // Preconfigured behavior
        $userProfile->form_name = 'editProfileFrm';
        $userProfile->post_name = 'update_profile';
        $userProfile->post_value = fusion_get_locale( 'u105' );
        $userProfile->user_data = fusion_get_userdata();
        $userProfile->user_name_change = fusion_get_settings( 'userNameChange' );
        $userProfile->skip_password = TRUE;
        $userProfile->registration = FALSE;
        $userProfile->display_terms = FALSE;
        $userProfile->display_validation = FALSE;
        $userProfile->inline_field = TRUE;
        // // The input class
        $userInput = new UserFieldsInput();
        $userInput->post_name = 'update_profile';
        $userInput->registration = FALSE;
        $userInput->skip_password = TRUE;
        $userInput->user_name_change = fusion_get_settings( 'userNameChange' );
        $userInput->verifyNewEmail = TRUE;
        $userInput->user_data = fusion_get_userdata();
        $userInput->saveUpdate();
        
        return $userProfile->getInfo();
        
    }
    
    /**
     * @param $profile_id
     *
     * @return mixed
     */
    public function profileInfo( $profile_id ) {
        
        if ( !defined( 'ADMIN_PANEL' ) ) {
            if ( !iMEMBER && fusion_get_settings( 'hide_userprofiles' ) == 1 ) {
                redirect( BASEDIR."index.php" );
            }
        }
        
        $userProfile = new ProfileOutput( $this );
        // This will redirect if profile_id is invalid
        $userProfile->user_data = $this->getProfileData( $profile_id );
        // Set other default behavior
        $userProfile->profile_id = (int)$profile_id;
        $userProfile->show_admin_options = ( iADMIN && checkrights( 'M' ) ? TRUE : FALSE );
        $userProfile->form_name = 'profileFrm';
        $userProfile->post_value = fusion_get_locale( 'u105' );
        $userProfile->post_name = 'update_profile';
        $userProfile->user_name_change = fusion_get_settings( 'userNameChange' );
        $userProfile->skip_password = TRUE;
        $userProfile->display_terms = FALSE;
        $userProfile->display_validation = FALSE;
        $userProfile->inline_field = TRUE;
        
        return $userProfile->getInfo();
        
    }
    
    /**
     * @param $profile_id
     *
     * @return array
     */
    private function getProfileData( $profile_id ) {
        static $profile_user = [];
        
        if ( empty( $profile_user ) ) {
            $user_status = " AND (user_status='0' OR user_status='3' OR user_status='7')";
            if ( iADMIN ) {
                $user_status = "";
            }
            $result = dbquery( "SELECT u.*, s.suspend_reason
            FROM ".DB_USERS." u
            LEFT JOIN ".DB_SUSPENDS." s ON u.user_id=s.suspended_user
            WHERE user_id=:uid".$user_status."
            ORDER BY suspend_date DESC
            LIMIT 1", [
                ':uid' => (int)$profile_id
            ] );
            if ( dbrows( $result ) ) {
    
                $profile_user = dbarray( $result );
    
                if ( empty( $profile_user['user_avatar'] ) && !file_exists( IMAGES."avatars/".$profile_user['user_avatar'] ) ) {
                    $profile_user['user_avatar'] = get_image( 'noavatar' );
                }
    
                unset( $profile_user['user_algo'] );
                unset( $profile_user['user_salt'] );
                unset( $profile_user['user_password'] );
                unset( $profile_user['user_admin_algo'] );
                unset( $profile_user['user_admin_salt'] );
                unset( $profile_user['user_admin_password'] );
                
            } else {
                redirect( BASEDIR."index.php" );
            }
        }
        
        return (array)$profile_user;
    }
    
    /**
     * Get User Data of the current page.
     *
     * @param $key
     *
     * @return array|null
     */
    public function getUserData( $key = NULL ) {
        static $userData = [];
        if ( empty( $userData ) ) {
            $userData = $this->user_data;
        }
        
        return $key === NULL ? $userData : ( isset( $userData[ $key ] ) ? $userData[ $key ] : NULL );
    }
    
}
