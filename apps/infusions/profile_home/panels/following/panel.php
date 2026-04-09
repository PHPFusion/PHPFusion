<?php

use PHPFusion\Infusions\Profile_Home\Panels\Following\Following;

function show_following( $data ) {
    $follow_panel = new Following($data);
    
    return show_profile_panel( 'following', 'Watching', $follow_panel->viewFollowing() );
}

fusion_add_hook( 'profile_home_panels', 'show_following' );
