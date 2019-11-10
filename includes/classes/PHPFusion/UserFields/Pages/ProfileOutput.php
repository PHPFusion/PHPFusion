<?php
namespace PHPFusion\UserFields\Pages;

use PHPFusion\OpenGraph;
use PHPFusion\UserFields;
use PHPFusion\UserFields\Profile_Activity;
use PHPFusion\UserFields\Profile_Groups;
use PHPFusion\UserFields\Public_Profile;
use PHPFusion\UserGroups;

class ProfileOutput {
    
    public $show_admin_options = FALSE;
    public $profile_id = 0;
    public $user_data = [];
    
    public $skip_password = FALSE;
    public $user_name_change = FALSE;
    
    public $form_name = '';
    public $post_value = '';
    public $post_name = '';
    public $display_terms = FALSE;
    public $display_validation = FALSE;
    public $inline_field = FALSE;
    
    private $info = [];
    private $method = 'display';
    private $registration = FALSE;
    private $userFields;
    
    public function __construct( UserFields $userFields ) {
        $this->userFields = $userFields;
    }
    
    public function getInfo() {
        $this->setInfo();
        return $this->info;
    }
    
    private function setInfo() {
        
        $locale = fusion_get_locale();
        
        OpenGraph::ogUserProfile( $this->user_data['user_id'] );
        
        // info
        $this->info = [
                'profile_id'   => $this->profile_id,
                'pages'        => $this->userFields->getOutputPages( $this->profile_id ),
                'total_groups' => UserGroups::get_userGroupCount( $this->user_data['user_groups'] )
            ] + $this->user_data;
        
        $this->info['current_page'] = $this->userFields->getCurrentOutputPage( $this->profile_id );
        
        $this->info['page_content'] = $this->getProfileContent( $this->info['current_page'] );
        
        // This is for profile.
        $this->info['core_field'] = [
            'profile_user_avatar' => [
                'title'  => $locale['u186'],
                'value'  => $this->user_data['user_avatar'],
                'status' => $this->user_data['user_status']
            ],
            'profile_user_name'   => [
                'title' => $locale['u068'],
                'value' => $this->user_data['user_name']
            ],
            'profile_user_level'  => [
                'title' => $locale['u063'],
                'value' => getgroupname( $this->user_data['user_level'] )
            ],
            'profile_user_joined' => [
                'title' => $locale['u066'],
                'value' => showdate( "longdate", $this->user_data['user_joined'] )
            ],
            'profile_user_visit'  => [
                'title' => $locale['u067'],
                'value' => $this->user_data['user_lastvisit'] ? showdate( "longdate", $this->user_data['user_lastvisit'] ) : $locale['u042']
            ],
        ];
        
        // user email
        if ( iADMIN || $this->user_data['user_hide_email'] == 0 ) {
            $this->info['core_field']['profile_user_email'] = [
                'title' => $locale['u064'],
                'value' => hide_email( $this->user_data['user_email'], fusion_get_locale( "UM061a" ) )
            ];
        }
        
        // user status
        if ( iADMIN && $this->user_data['user_status'] > 0 ) {
            $this->info['core_field']['profile_user_status'] = [
                'title' => $locale['u055'],
                'value' => getuserstatus( $this->user_data['user_status'] )
            ];
            if ( $this->user_data['user_status'] == 3 ) {
                $this->info['core_field']['profile_user_reason'] = [
                    'title' => $locale['u056'],
                    'value' => $this->user_data['suspend_reason']
                ];
            }
        }
        
        // IP
        if ( iADMIN && checkrights( 'M' ) ) {
            $this->info['core_field']['profile_user_ip'] = [
                'title' => $locale['u049'],
                'value' => $this->user_data['user_ip']
            ];
        }
        
        // Not own
        if ( iMEMBER && fusion_get_userdata( "user_id" ) != $this->user_data['user_id'] ) {
            $this->info['buttons'] = [
                'user_pm_title' => $locale['u043'],
                'user_pm_link'  => BASEDIR."messages.php?msg_send=".$this->user_data['user_id']
            ];
        }
        
        if ( $this->userFields->checkModAccess() ) {
            $aidlink = fusion_get_aidlink();
            $this->info['user_admin'] = [
                'user_edit_title'     => $locale['edit'],
                'user_edit_link'      => ADMIN."members.php".$aidlink."&amp;ref=edit&amp;lookup=".$this->user_data['user_id'],
                'user_ban_title'      => $this->user_data['user_status'] == 1 ? $locale['u074'] : $locale['u070'],
                'user_ban_link'       => ADMIN."members.php".$aidlink."&amp;action=".( $this->user_data['user_status'] == 1 ? 2 : 1 )."&amp;lookup=".$this->user_data['user_id'],
                'user_suspend_title'  => $locale['u071'],
                'user_suspend_link'   => ADMIN."members.php".$aidlink."&amp;action=3&amp;lookup=".$this->user_data['user_id'],
                'user_delete_title'   => $locale['delete'],
                'user_delete_link'    => ADMIN."members.php".$aidlink."&amp;ref=delete&amp;lookup=".$this->user_data['user_id'],
                'user_delete_onclick' => "onclick=\"return confirm('".$locale['u073']."');\"",
                'user_susp_title'     => $locale['u054'],
                'user_susp_link'      => ADMIN."members.php".$aidlink."&amp;ref=log&amp;lookup=".$this->user_data['user_id']
            ];
            
        }
        
    }
    
    /**
     * @Require $current_page
     * @return string
     */
    private function getProfileContent() {
        switch ( $this->info['current_page'] ) {
            case 'profile':
                
                $public_profile = new Public_Profile( $this->userFields );
                $public_profile->user_data = $this->user_data;
                $public_profile->profile_id = $this->profile_id;
                $public_profile->post_name = 'update_profile';
                $public_profile->registration = $this->registration;
                $public_profile->display_validation = $this->display_validation;
                $public_profile->display_terms = $this->display_terms;
                $public_profile->inline_field = $this->inline_field;
                $public_profile->method = $this->method;
                
                return display_public_profile( $public_profile->outputInfo() );
                
                break;
            case 'friends':
                return "The friend page is currently under development.";
                break;
            case 'groups':
                
                $class = new Profile_Groups( $this->profile_id, $this->user_data );
                
                return display_profile_groups( $class->showGroupProfile() );
                
                break;
            case 'activity':
                
                $class = new Profile_Activity( $this->profile_id, $this->user_data );
                
                return $class->showActivityProfile();
                
                break;
            
            default:
                
                $output = $this->showCustomProfile();
                
                return !empty( $output ) ? $output : "This page is currently unavailable.";
        }
    }
    
    /**
     * @return string
     */
    private function showCustomProfile() {
        if ( !empty( $this->info['pages'][ $this->info['current_page'] ]['file'] ) ) {
            $this->loadPage( $this->info['pages'][ $this->info['current_page'] ]['file'] );
            $output = fusion_filter_hook( 'fusion_profile_page', $this );
            return implode( '', $output );
        }
        return '';
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
    
}
