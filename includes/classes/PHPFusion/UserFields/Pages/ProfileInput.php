<?php
namespace PHPFusion\UserFields\Pages;

use PHPFusion\UserFields;
use PHPFusion\UserFields\Account_Profile;
use PHPFusion\UserFields\Public_Profile;
use ReflectionException;

class ProfileInput {
    
    public $skip_password = FALSE;
    public $registration = FALSE;
    public $user_name_change = FALSE;
    public $user_data = [];
    public $form_name = '';
    public $post_value = '';
    public $post_name = '';
    public $display_terms = FALSE;
    public $display_validation = FALSE;
    public $inline_field = FALSE;
    private $userFields;
    private $method = 'input';
    private $info = [];
    
    public function __construct( UserFields $userFields ) {
        $this->userFields = $userFields;
    }
    
    /**
     * Info for Edit Profile and Registration
     *
     * @return array
     * @throws ReflectionException
     */
    public function getInfo() {
        $this->setInfo();
        return (array)$this->info;
    }
    
    /**
     * Input data for Edit Profile and Registration Page
     *
     * @return array
     * @throws ReflectionException
     */
    private function setInfo() {
        $this->method = 'input';
        $locale = fusion_get_locale();
        // user id
        $this->info = [
            'register'             => $this->registration,
            'title'                => 'Edit Profile',
            'sitename'             => fusion_get_settings( 'sitename' ),
            'pages'                => $this->userFields->getInputPages(),
            'user_id'              => form_hidden( 'user_id', '', $this->getUserId() ),
            'name'                 => $this->user_data['user_name'],
            'user_name'            => '',
            'joined_date'          => showdate( 'longdate', $this->user_data['user_joined'] ),
            'email'                => $this->user_data['user_email'],
            'user_password'        => '',
            'user_admin_password'  => '',
            'user_email'           => '',
            'user_hide_email'      => '',
            'user_avatar'          => '',
            'user_reputation'      => '',
            'validate'             => '',
            'terms'                => '',
            'user_close_message'   => '',
            'custom_page'          => FALSE,
            'openform'             => openform( $this->form_name, 'post', FUSION_REQUEST, [ 'enctype' => ( $this->registration == FALSE ? TRUE : FALSE ) ] ),
            'closeform'            => closeform(),
            'button'               => $this->renderButton(),
            'user_password_verify' => ( iADMIN && checkrights( 'M' ) && defined( 'IN_ADMIN' ) ) ? '' : form_hidden( 'user_password_verify', '', $this->user_data['user_password'] )
        ];
        
        $this->info['current_page'] = $this->userFields->getCurrentInputPage();
        
        if ( $this->registration ) {
            
            $this->info = array_merge( $this->info, $this->registrationInfo() );
            
        } else {
            
            $this->info = array_merge( $this->info, $this->editProfileInfo() );
        }
        
        return (array)$this->info;
    }
    
    private function getUserId() {
        $user_id = get( 'lookup', FILTER_VALIDATE_INT ) ?: fusion_get_userdata( 'user_id' );
        if ( $this->registration ) {
            $user_id = 0;
        }
        return $user_id;
    }
    
    /**
     * Display button post button
     *
     * @return string
     */
    private function renderButton() {
        
        $disabled = $this->display_terms ? TRUE : FALSE;
        $html = ( !$this->skip_password ) ? form_hidden( 'user_hash', '', $this->user_data['user_password'] ) : '';
        $html .= form_button( $this->post_name, $this->post_value, $this->post_value,
            [
                "deactivate" => $disabled,
                "class"      => 'btn-primary'
            ] );
        
        return (string)$html;
    }
    
    /**
     * @return array
     * @throws ReflectionException
     */
    private function registrationInfo() {
        $class = new Public_Profile( $this->userFields );
        
        // Can remove all these, as we do DI approach
        $class->user_data = $this->user_data;
        $class->form_name = $this->form_name;
        $class->post_name = $this->post_name;
        $class->options = $this->options;
        $class->user_name_change = TRUE;
        $class->registration = $this->registration;
        $class->is_admin_panel = $this->is_admin_panel;
        $class->display_validation = $this->display_validation;
        $class->display_terms = $this->display_terms;
        $class->inline_field = $this->inline_field;
        
        $this->info = array_merge( $this->info, $class->inputInfo() );
        
        // Edit Profile Fields
        $class = new Account_Profile();
        $class->user_data = $this->user_data;
        $class->options = $this->options;
        $class->is_admin_panel = $this->is_admin_panel;
        $class->registration = $this->registration;
        $class->post_name = $this->post_name;
        $class->user_name_change = TRUE;
        $class->inline_field = $this->inline_field;
        
        return $class->get_info();
    }
    
    /**
     * @return array
     * @throws ReflectionException
     */
    private function editProfileInfo() {
        $settings = fusion_get_settings();
        $locale = fusion_get_locale();
        
        // Notice to tell that there are email pending for verification.
        if ( $settings['email_verification'] ) {
            $result = dbquery( "SELECT user_email FROM ".DB_EMAIL_VERIFY." WHERE user_id=:selfid", [
                ':selfid' => fusion_get_userdata( 'user_id' )
            ] );
            if ( dbrows( $result ) ) {
                $data = dbarray( $result );
                addNotice( 'info', sprintf( $locale['u200'], $data['user_email'] )."\n<br />\n".$locale['u201'] );
            }
        }
        
        // info output
        switch ( $this->info['current_page'] ) {
            default:
                
                if ( isset( $this->info['pages'][ $this->info['current_page'] ]['file'] ) && is_file( $this->info['pages'][ $this->info['current_page'] ]['file'] ) ) {
                    
                    return $this->loadCustomPage();
                    
                } else {
                    
                    redirect( BASEDIR.'edit_profile.php' );
                }
                
                
                break;
            
            case 'pu_profile':
                
                $class = new Public_Profile();
                $class->user_data = $this->user_data;
                $class->form_name = $this->post_name;
                $class->post_name = $this->post_name;
                $class->registration = $this->registration;
                $class->display_validation = $this->display_validation;
                $class->display_terms = $this->display_terms;
                $class->inline_field = $this->inline_field;
                $this->info['user_hash'] = form_hidden( 'user_hash', '', $this->user_data['user_password'] );
                return $class->inputInfo();
                break;
            
            case 'se_profile':
                
                $class = new Account_Profile();
                $class->user_data = $this->user_data;
                $class->registration = $this->registration;
                $class->post_name = $this->post_name;
                $class->user_name_change = $this->user_name_change;
                $class->show_admin_password = ( iADMIN ? TRUE : FALSE );
                $class->inline_field = $this->inline_field;
                
                return $class->inputInfo();
                break;
        }
    }
    
    /**
     * @return array
     */
    private function loadCustomPage() {
        $locale = fusion_get_locale();
        
        $this->info['custom_page'] = TRUE;
        $this->info['title'] = $this->info['pages'][ $this->info['current_page'] ]['title'];
        $this->info['page_content'] = 'There are no page content yet.';
        $user_fields = '';
        $user_fields_meta = '';
        $user_fields_section = [];
        $user_fields_title = '';
        //$default_section = 'default';
        
        include $this->info['pages'][ $this->info['current_page'] ]['file'];
        
        if ( $user_fields ) {
            // Title info
            if ( $user_fields_title ) {
                $this->info['title'] = $user_fields_title;
            }
            // Meta info
            if ( $user_fields_meta ) {
                add_to_meta( 'keywords', $user_fields_meta );
            }
            // Navigation
            if ( !empty( $user_fields_section ) ) {
                $this->info['section'] = $user_fields_section;
            }
            // Additional navigation
            if ( !empty( $user_fields_nav ) ) {
                $this->info['section_nav'] = $user_fields_nav;
            }
            // Page content info
            $this->info['page_content'] = $user_fields;
        }
        // Custom title
        add_to_title( $locale['global_201'].$this->info['title'] );
        
        return $this->info;
    }
}
