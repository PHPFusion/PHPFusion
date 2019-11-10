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

use Exception;
use PHPFusion\UserFields\Pages\ProfileInput;
use PHPFusion\UserFields\Profile_Activity;
use PHPFusion\UserFields\Profile_Groups;
use PHPFusion\UserFields\Public_Profile;

/**
 * Class UserFields
 *
 * @package PHPFusion
 */
class UserFields extends UserFieldsQuantum {
    
    private $profile_id = 0;
    public $display_terms = 0;
    public $display_validation = 0;
    public $errors = [];
    public $form_action = FUSION_REQUEST; // changed in API 1.02
    public $form_name = "userfieldsform";
    public $post_name;
    public $post_value;
    public $inline_field = TRUE;
    public $show_admin_options = FALSE;
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
    protected static $additional_pages = [];
    
    private $current_page = '';
    public $current_output_page = 'profile';
    
    private static $output_page = [];
    
    protected $info = [
        'terms'               => '',
        'validate'            => '',
        'user_avatar'         => '',
        'user_admin_password' => '',
    ];
    
    public $options = [];
    
    private static $instance = NULL;
    
    private $settings = [];
    
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
        self::$additional_pages[ $key ] = [
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
        $active_class = ' class="active"';
        self::$output_page[ $key ] = [
            'id'    => $key,
            'title' => $title,
            'class' => $this->current_output_page == $key ? $active_class : '',
            'file'  => $file,
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
            $profile_page = array_merge( $profile_page, self::$additional_pages );
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
     * Public profile view sections
     *
     * @return array
     */
    private function getOutputPages() {
        $this->current_output_page = get( "profile_page" );
        $ppCache = [];
        if ( empty( $ppCache ) ) {
            $active_class = ' class="active"';
            $ppCache = [
                'activity' => [ 'id'    => 'activity',
                                'title' => 'User Activity',
                                'link'  => BASEDIR.'profile.php?lookup='.$this->profile_id.'&amp;profile_page=activity',
                                'class' => $this->current_output_page == 'activity' ? $active_class : ""
                ],
                'profile'  => [ 'id'    => 'profile',
                                'title' => 'User Profile',
                                'link'  => BASEDIR.'profile.php?lookup='.$this->profile_id.'&amp;profile_page=profile',
                                'class' => $this->current_output_page == 'profile' ? $active_class : ""
                
                ],
                'friends'  => [ 'id'    => 'friends',
                                'title' => 'User Friends',
                                'link'  => BASEDIR.'profile.php?lookup='.$this->profile_id.'&amp;profile_page=friends',
                                'class' => $this->current_output_page == 'friends' ? $active_class : ""
                ],
                'groups'   => [ 'id'    => 'groups',
                                'title' => 'User Groups <span class="badge">'.format_num( $this->total_groups ).'</span>',
                                'link'  => BASEDIR.'profile.php?lookup='.$this->profile_id.'&amp;profile_page=groups',
                                'class' => $this->current_output_page == 'groups' ? $active_class : ""
                ]
            ];
    
            if ( !empty( self::$output_page ) ) {
                $output_pages_keys = array_keys( self::$output_page );
                foreach ( $output_pages_keys as $key ) {
                    self::$output_page[ $key ]['link'] = BASEDIR.'profile.php?lookup='.$this->profile_id.'&amp;profile_page='.$key;
                }
        
                $ppCache = array_merge( $ppCache, self::$output_page );
            }
    
            $this->current_page = isset( $ppCache[ $this->current_output_page ] ) ? $ppCache[ $this->current_output_page ]['id'] : "profile";
        }
    
        return (array)$ppCache;
    }
    
    /**
     * @return bool
     */
    public function userModeratorAccess() {
        return ( checkrights( 'M' ) && fusion_get_userdata( 'user_level' ) <= USER_LEVEL_ADMIN && $this->profile_id != '1' || iSUPERADMIN ) ? TRUE : FALSE;
    }
    
    /**
     * @return bool
     */
    public function groupModeratorAccess() {
        return ( checkrights( 'UG' ) && fusion_get_userdata( 'user_level' ) <= USER_LEVEL_ADMIN && $this->profile_id != '1' || iSUPERADMIN ) ? TRUE : FALSE;
    }
    
    /**
     * Show Registration Form
     * Registration    user-register-form
     *
     * @return string|void
     * @throws Exception
     */
    public function registration() {
        $this->setInputInfo();
        return display_register_form( $this->info );
    }

    /**
     * CHECK 1 - OK
     * Edit Profile Info
     *
     * @return array
     * @throws \ReflectionException
     */
    public function editProfileInfo() {
        
        if ( !iMEMBER ) {
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
     * @return array
     */
    private function getCurrentUserData() {
        static $userdata = [];
        
        if ( empty( $userdata ) ) {
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
                ':uid' => (int)$this->profile_id
            ] );
            if ( dbrows( $result ) ) {
                
                $userdata = dbarray( $result );
                
                if ( empty( $userdata['user_avatar'] ) && !file_exists( IMAGES."avatars/".$userdata['user_avatar'] ) ) {
                    $userdata['user_avatar'] = get_image( 'noavatar' );
                }
                
                unset( $userdata['user_algo'] );
                unset( $userdata['user_salt'] );
                unset( $userdata['user_password'] );
                unset( $userdata['user_admin_algo'] );
                unset( $userdata['user_admin_salt'] );
                unset( $userdata['user_admin_password'] );
                
            } else {
                redirect( BASEDIR."index.php" );
            }
        }
        
        return (array)$userdata;
    
    }
    
    /**
     * @return string
     */
    private function showPublicProfile() {
        // Public Profile Page
        $public_profile = new Public_Profile( $this );
        $public_profile->user_data = $this->getCurrentUserData();
        $public_profile->profile_id = $this->profile_id;
        $public_profile->post_name = $this->post_name;
        $public_profile->options = $this->options;
        $public_profile->registration = $this->registration;
        $public_profile->display_validation = $this->display_validation;
        $public_profile->display_terms = $this->display_terms;
        $public_profile->plugin_folder = $this->plugin_folder;
        $public_profile->plugin_locale_folder = $this->plugin_locale_folder;
        $public_profile->inline_field = $this->inline_field;
        $public_profile->method = $this->method;
        
        return display_public_profile( $public_profile->outputInfo() );
    }
    
    /**
     * Profile View Output ---- VIEW PROFILE
     *
     * @return false|mixed|string|void
     */
    private function getProfileContent() {
        switch ( $this->current_page ) {
            case 'profile':
                return $this->showPublicProfile();
                break;
            case 'friends':
                return "The friend page is currently under development.";
                break;
            case 'groups':
                $class = new Profile_Groups( $this->profile_id, $this->getCurrentUserData() );
                return display_profile_groups( $class->showGroupProfile() );
                break;
            case 'activity':
                $class = new Profile_Activity( $this->profile_id, $this->getCurrentUserData() );
                return $class->showActivityProfile();
                break;
            default:
                // find API added pages
                if ( !empty( self::$output_page[ $this->current_page ]['file'] ) ) {
    
                    $this->loadPage( self::$output_page[ $this->current_page ]['file'] );
    
                    $output = fusion_filter_hook( 'fusion_profile_page', $this );
                    $output = implode( '', $output );
    
                }
    
                return !empty( $output ) ? $output : "This page is currently unavailable.";
        }
    }
    
    /**
     * Load the file - load the hook page.
     *
     * @param $file_link
     *
     * @return false|string
     */
    private function loadPage( $file_link ) {
        if ( is_file( $file_link ) ) {
            require_once $file_link;
        }
    }
    
    private $total_groups = 0;
    
    /**
     * Lookup View Page
     */
    public function display_profile_output() {
        
        require_once THEMES."templates/global/profile.php";
        
        if ( !defined( 'ADMIN_PANEL' ) ) {
            if ( !iMEMBER && fusion_get_settings( 'hide_userprofiles' ) == 1 ) {
                redirect( BASEDIR."index.php" );
            }
        }
        
        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $this->settings = fusion_get_settings();
        
        $this->profile_id = get( "lookup", FILTER_VALIDATE_INT );
        OpenGraph::ogUserProfile( $this->profile_id );
        
        $this->user_data = $this->getCurrentUserData();
        $this->total_groups = UserGroups::get_userGroupCount( $this->user_data['user_groups'] );
        
        // info
        $this->info = [
                'profile_id'      => $this->profile_id,
                'profile_pages'   => $this->getOutputPages(),
                'profile_content' => $this->getProfileContent(),
                'total_groups'    => $this->total_groups,
            ] + $this->user_data;
        
        // This is for profile.
        $this->info['core_field'] = [
            'profile_user_avatar' => [
                'title'  => $locale['u186'],
                'value'  => $this->info['user_avatar'],
                'status' => $this->info['user_status']
            ],
            'profile_user_name'   => [
                'title' => $locale['u068'],
                'value' => $this->info['user_name']
            ],
            'profile_user_level'  => [
                'title' => $locale['u063'],
                'value' => getgroupname( $this->info['user_level'] )
            ],
            'profile_user_joined' => [
                'title' => $locale['u066'],
                'value' => showdate( "longdate", $this->info['user_joined'] )
            ],
            'profile_user_visit'  => [
                'title' => $locale['u067'],
                'value' => $this->info['user_lastvisit'] ? showdate( "longdate", $this->info['user_lastvisit'] ) : $locale['u042']
            ],
        ];
        
        // user email
        if ( iADMIN || $this->info['user_hide_email'] == 0 ) {
            $this->info['core_field']['profile_user_email'] = [
                'title' => $locale['u064'],
                'value' => hide_email( $this->user_data['user_email'], fusion_get_locale( "UM061a" ) )
            ];
        }
        // user status
        if ( iADMIN && $this->info['user_status'] > 0 ) {
            $this->info['core_field']['profile_user_status'] = [
                'title' => $locale['u055'],
                'value' => getuserstatus( $this->info['user_status'] )
            ];
            if ( $this->info['user_status'] == 3 ) {
                $this->info['core_field']['profile_user_reason'] = [
                    'title' => $locale['u056'],
                    'value' => $this->info['suspend_reason']
                ];
            }
        }
        
        // IP
        if ( iADMIN && checkrights( 'M' ) ) {
            $this->info['core_field']['profile_user_ip'] = [
                'title' => $locale['u049'],
                'value' => $this->info['user_ip']
            ];
        }
        
        // Not own
        if ( iMEMBER && fusion_get_userdata( "user_id" ) != $this->info['user_id'] ) {
            $this->info['buttons'] = [
                'user_pm_title' => $locale['u043'],
                'user_pm_link'  => BASEDIR."messages.php?msg_send=".$this->info['user_id']
            ];
        }
        
        if ( $this->userModeratorAccess() ) {
            
            $this->info['user_admin'] = [
                'user_edit_title'     => $locale['edit'],
                'user_edit_link'      => ADMIN."members.php".$aidlink."&amp;ref=edit&amp;lookup=".$this->info['user_id'],
                'user_ban_title'      => $this->info['user_status'] == 1 ? $locale['u074'] : $locale['u070'],
                'user_ban_link'       => ADMIN."members.php".$aidlink."&amp;action=".( $this->info['user_status'] == 1 ? 2 : 1 )."&amp;lookup=".$this->info['user_id'],
                'user_suspend_title'  => $locale['u071'],
                'user_suspend_link'   => ADMIN."members.php".$aidlink."&amp;action=3&amp;lookup=".$this->info['user_id'],
                'user_delete_title'   => $locale['delete'],
                'user_delete_link'    => ADMIN."members.php".$aidlink."&amp;ref=delete&amp;lookup=".$this->info['user_id'],
                'user_delete_onclick' => "onclick=\"return confirm('".$locale['u073']."');\"",
                'user_susp_title'     => $locale['u054'],
                'user_susp_link'      => ADMIN."members.php".$aidlink."&amp;ref=log&amp;lookup=".$this->info['user_id']
            ];
            
        }
        
        echo display_profile( $this->info );
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
