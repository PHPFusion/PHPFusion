<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_blacklist_include.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

// Display user field input
if ($profile_method == "input") {
    $user_fields = '';
    if (defined('ADMIN_PANEL')) {
        $user_fields = "<div class='well m-t-5 text-center'>".$locale['uf_blacklist']."</div>";
    }

    // Display in profile
} else if ( $profile_method == "display" ) {
    $text = '';
    $lookup = get( 'lookup' );
    $userdat = fusion_get_userdata();
    $sendblacklist = post( 'send_blacklist' );

    $userdata_blacklist = explode( '.', $field_value );
    $iblacklist = explode( '.', $userdat['user_blacklist'] );

    if ( in_array( $userdat['user_id'], $userdata_blacklist ) ) {
        redirect( BASEDIR.'index.php' );
    }

    if ( iMEMBER && !empty( $sendblacklist ) && ( $userdat['user_id'] != $lookup ) ) {
        if ( !in_array( $lookup, $userdata_blacklist ) ) {
            $userdat['user_blacklist'] = $lookup.( empty( $userdat['user_blacklist'] ) ? '' : '.' ).$userdat['user_blacklist'];

            dbquery_insert( DB_USERS, $userdat, 'update' );
            $field_value = $userdat['user_blacklist'];
            addNotice( 'success', $locale['uf_blacklist_009'] );
        }
    }

    if ( iMEMBER ) {
        if ( !in_array( $lookup, $iblacklist ) && ( $userdat['user_id'] != $lookup ) ) {
            $action_url = FUSION_SELF.( FUSION_QUERY ? "?".FUSION_QUERY : "" );
            $text .= openform( 'black_form', 'post', $action_url );
            $text .= form_button( 'send_blacklist', $locale['uf_blacklist_008'], $locale['uf_blacklist_008'], [ 'class' => 'btn-danger' ] );
            $text .= closeform();
        }
    }

    if ( is_array( $userdata_blacklist ) && count( $userdata_blacklist ) > 0  && ( $userdat['user_id'] == $lookup ) ) {
        foreach ( $userdata_blacklist as $blackid ) {
            $result = dbquery("SELECT user_id, user_name, user_status, user_avatar FROM ".DB_USERS." WHERE user_id = :userid ORDER BY user_id ASC", [ ':userid' => $blackid ] );
            if ( dbrows( $result ) > 0 ) {
                while ( $data = dbarray( $result ) ) {
                    $text .= "<div id='".$data['user_id']."-user-list'>\n<div class='panel-body'>\n";
                    $text .= "<button type='button' value='".$data['user_id']."' class='unblock pull-right m-t-5 btn btn-sm btn-primary'>".$locale['uf_blacklist_001']."</button>\n";
                    $text .= "<div class='pull-left m-r-10'>".display_avatar($data, '50px', '', TRUE, 'img-rounded')."</div>\n";
                    $text .= "<div class='clearfix'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/>\n";
                    $text .= "<span class='text-lighter'>".$locale['uf_blacklist_002']."</span>\n";
                    $text .= "</div>\n";
                    $text .= "</div>\n</div>\n";
                }
            }
        }
    }

    $user_fields = [
        'title' => $locale['uf_blacklist'],
        'value' => $userdata_blacklist ? $text : ''
    ];

    add_to_jquery( "
        $('.unblock').bind('click', function(e) {
        var user_id = $(this).val();
        $.ajax({
            type: 'POST',
            url: '".INCLUDES."user_fields/user_blacklist.ajax.php',
            data: { user_id : user_id },
            dataType: 'html',
            success: function(data) {
                alert(data);
                $('#'+user_id+'-user-list').addClass('display-none');
                $('#ignore-message').html(data).removeClass('display-none');
            },
            error: function() {
                alert('".$locale['uf_blacklist_desc']."');
            }
            });
        });
    " );
}
