<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: relations.ajax.php
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

use PHPFusion\UserFields\Pages\ProfileOutput;
use PHPFusion\UserRelations;

require_once __DIR__.'/../../../../../maincore.php';
include INCLUDES.'ajax_include.php';

//print_P( $_POST );
// command available
$response['error'] = 'You are not authorized to perform this action.';
if ( iMEMBER ) {
    $userFields = new \PHPFusion\UserFields();
    $relation = new ProfileOutput( $userFields );
    $request_type = array_flip( $relation->getRequestType() );
    $post_request = post( 'request_type' );
    // to disable, to switchText
    $response['error'] = 'Wrong request was used.';
    $response['post_request'] = $post_request;
    $response['friend_id'] = post( 'friend_id', FILTER_VALIDATE_INT );
    $friend_id = $response['friend_id'];
    if ( isset( $request_type[ $post_request ] ) && $friend_id ) {
        $user_id = fusion_get_userdata( 'user_id' );
        $user_name = fusion_get_user( $friend_id, 'user_name' );
        $user_relations = new UserRelations();
        // validated method
        //print_p($post_request);
        //print_P($friend_id);
        // now we will go in and build our relation.
        switch ( $post_request ) {
            case 'friend_request':
                if ( $user_relations->friendRequest( $user_id, $friend_id ) ) {
                    $response = [
                        'error'           => FALSE,
                        'notice'          => 'You have requested to be friends with '.$user_name,
                        'disable'         => 'friend_request', // disable the current button
                        'disable_text'    => 'Friend Request Sent',
                        'hide'            => 'block_user',
                        'show_after_id'   => 'friend_request',
                        'show_after_hide' => form_button( 'cancel_request', 'Cancel Request', $friend_id, [ 'class' => 'btn-default' ] )
                    ];
                }
                break;
            case 'accept_request':
                if ( $user_relations->acceptFriendRequest( $user_id, $friend_id ) ) {
                    add_notice( 'success', 'You are now friends with '.$this->user_data['user_name'] );
                    redirect( FUSION_REQUEST );
                }
                break;
            case 'cancel_request':
                if ( $user_relations->cancelFriendRequest( $user_id, $friend_id ) ) {
                    $response = [
                        'error'           => FALSE,
                        'notice'          => 'Your friendship request with '.$user_name.' has been cancelled',
                        'enable'          => 'friend_request',
                        'enable_text'     => 'Add Friend',
                        'show'            => 'block_user',
                        'hide'            => 'cancel_request',
                        'show_after_id'   => 'friend_request',
                        'show_after_hide' => form_button( 'block_user', 'Block '.$user_name, $friend_id, [ 'class' => 'btn-danger' ] )
                    ];
                }
                break;
            case 'block_user':
                if ( $user_relations->blockRequest( $user_id, $friend_id ) ) {
                    add_notice( 'success', $this->user_data['user_name'].' is now added to your blocked list' );
                    redirect( FUSION_REQUEST );
                }
                break;
            case 'unblock_user':
                if ( $user_relations->unblockRequest( $user_id, $friend_id ) ) {
                    add_notice( 'success', $this->user_data['user_name'].' is removed from your blocked list' );
                    redirect( FUSION_REQUEST );
                }
            case 'unfriend_request':
                if ( $user_relations->unfriendRequest( $user_id, $friend_id ) ) {
                    add_notice( 'success', $this->user_data['user_name'].' is no longer your friend' );
                    redirect( FUSION_REQUEST );
                }
        }
    }


}

echo json_encode( $response );
