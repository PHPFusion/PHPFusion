<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: ProfileOutput.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\UserFields\Pages;

use PHPFusion\OpenGraph;
use PHPFusion\UserFields;
use PHPFusion\UserFields\Profile_Activity;
use PHPFusion\UserFields\Profile_Groups;
use PHPFusion\UserFields\Public_Profile;
use PHPFusion\UserGroups;
use PHPFusion\UserRelations;

class ProfileOutput {

    private $userFields;

    private $userRelation;

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


    /**
     * ProfileOutput constructor.
     *
     * @param UserFields $userFields
     */
    public function __construct( UserFields $userFields ) {
        $this->userFields = $userFields;
        $this->userRelation = new UserRelations();
    }

    /**
     * @return array
     */
    public function getInfo() {
        $this->setInfo();
        return $this->info;
    }

    private function setInfo() {

        $locale = fusion_get_locale();
        // current user is the profile owner.
        define( 'iPROFILE', fusion_get_userdata( "user_id" ) == $this->user_data['user_id'] ? TRUE : FALSE );

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
        if ( iMEMBER && !iPROFILE ) {

            // Enable PHP Listener
            $this->requestAction();

            $this->info['buttons'] = [
                'user_pm_title' => $locale['u043'],
                'user_pm_link'  => BASEDIR."messages.php?msg_send=".$this->user_data['user_id']
            ];

            $this->info['relations_button'] = openform( 'relationsfrm', 'post' ).$this->showRelationButton().closeform();
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

    private $request_type = [ 'friend_request', 'accept_request', 'cancel_request', 'block_user', 'unblock_user', 'unfriend_request' ];

    public function getRequestType() {
        return $this->request_type;
    }

    /**
     * Request Actions Listener
     *
     * @return array
     */
    public function detectRequest() {
        foreach ( $this->request_type as $type ) {
            if ( $value = post( $type, FILTER_VALIDATE_INT ) ) {
                return [
                    'friend_id'    => $value,
                    'request_type' => $type
                ];
            }
        }
        return [];
    }

    /**
     * Request Action Execution
     */
    public function requestAction() {
        if ( iMEMBER ) {
            $user_id = fusion_get_userdata( 'user_id' );
            $request = $this->detectRequest();
            if ( !empty( $request['request_type'] ) && !empty( $request['friend_id'] ) ) {
                $friend_id = $request['friend_id'];
                switch ( $request['request_type'] ) {
                    case 'friend_request':
                        if ( $this->userRelation->friendRequest( $user_id, $friend_id ) ) {
                            add_notice( 'success', 'You have requested to be friends with '.$this->user_data['user_name'] );
                            redirect( FUSION_REQUEST );
                        }
                        break;
                    case 'accept_request':
                        if ( $this->userRelation->acceptFriendRequest( $user_id, $friend_id ) ) {
                            add_notice( 'success', 'You are now friends with '.$this->user_data['user_name'] );
                            redirect( FUSION_REQUEST );
                        }
                        break;
                    case 'cancel_request':
                        if ( $this->userRelation->cancelFriendRequest( $user_id, $friend_id ) ) {
                            add_notice( 'success', 'Your friendship request with '.$this->user_data['user_name'].' has been cancelled' );
                            redirect( FUSION_REQUEST );
                        }
                        break;
                    case 'block_user':
                        if ( $this->userRelation->blockRequest( $user_id, $friend_id ) ) {
                            add_notice( 'success', $this->user_data['user_name'].' is now added to your blocked list' );
                            redirect( FUSION_REQUEST );
                        }
                        break;
                    case 'unblock_user':
                        if ( $this->userRelation->unblockRequest( $user_id, $friend_id ) ) {
                            add_notice( 'success', $this->user_data['user_name'].' is removed from your blocked list' );
                            redirect( FUSION_REQUEST );
                        }
                    case 'unfriend_request':
                        if ( $this->userRelation->unfriendRequest( $user_id, $friend_id ) ) {
                            add_notice( 'success', $this->user_data['user_name'].' is no longer your friend' );
                            redirect( FUSION_REQUEST );
                        }
                }
            }
        }
    }

    /**
     * @return string
     */
    private function showRelationButton() {

        $row = $this->userRelation->getRelation( $this->user_data['user_id'] );
        switch ( $row['relation_status'] ) {
            case 0:
                if ( $row['relation_action'] === fusion_get_userdata( 'user_id' ) ) {
                    // Show, "Friend request sent" button. Show options to cancel the friend request.
                    return
                        form_button( 'friend_request', 'Friend Request Sent', $this->user_data['user_id'], [ 'class' => 'btn-primary', 'type' => 'button', 'deactivate' => TRUE ] ).
                        form_button( 'cancel_request', 'Cancel Request', $this->user_data['user_id'], [ 'class' => 'btn-default' ] );

                } else if ( $row['relation_action'] === $this->user_data['user_id'] ) {
                    // Show, "Accept Friend request" button. Show options to block and reject friend request.
                    return form_button( 'accept_request', 'Accept Friend Request', $this->user_data['user_id'], [ 'class' => 'btn-default' ] );
                }
                break;
            case 1:
                // Check and show options to unfriend the user.
                return form_button( 'unfriend_request', 'Remove Friend', $this->user_data['user_id'], [ 'class' => 'btn-default' ] );
                break;
            case 3:
                // Check and show options to unblock. If the other user's visit's this profile show "profile does'nt exists"
                return form_button( 'unblock_user', 'Unblock '.$this->user_data['user_name'], $this->user_data['user_id'], [ 'class' => 'btn-default' ] );
                break;
        }
        return
            form_button( 'friend_request', 'Add Friend', $this->user_data['user_id'], [ 'class' => 'btn-default' ] ).
            form_button( 'block_user', 'Block '.$this->user_data['user_name'], $this->user_data['user_id'], [ 'class' => 'btn-danger' ] );
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
