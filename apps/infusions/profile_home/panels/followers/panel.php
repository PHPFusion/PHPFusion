<?php

use PHPFusion\Infusions\Profile_Home\Panels\Followers\Followers;

function showFollowers( $data ) {
    $follow_panel = new Followers( $data );
    return show_profile_panel( 'followers', 'Watchers', $follow_panel->viewFollowers() );
}

fusion_add_hook( 'profile_home_panels', 'showFollowers' );
