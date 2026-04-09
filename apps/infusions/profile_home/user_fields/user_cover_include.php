<?php

use PHPFusion\Infusions\Profile_Home\Classes\ProfileHeader;

( defined( 'IN_FUSION' ) || exit );

if ( $profile_method == 'input' && defined( 'ADMIN_PANEL' ) ) {
    
    $user_fields = alert( '<strong>User Cover Fields</strong>' );
    
} else if ( $profile_method == 'display' ) {
    $user_fields = [];
    
    if (defined('fusion_main_profile')) {
        $upheader = new ProfileHeader($user_data);
    
        $user_fields = [
            'value' => $upheader->show()
        ];
    }
    
}
