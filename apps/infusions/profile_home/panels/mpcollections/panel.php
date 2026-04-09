<?php

use PHPFusion\Infusions\Marketplace\Classes\Marketplace;
use PHPFusion\Infusions\Profile_Home\Panels\mpCollections\mpCollections;

function showMPCollections( $data ) {
    
    $itemPanel = new mpCollections( $data );
    //https://php-fusion.test/edit_profile.php?ref=marketplace
    $edit_link = '';
    // my store id
    if ( iMEMBER && $data['user_id'] == fusion_get_userdata( 'user_id' ) ) {
        $edit_link = BASEDIR.'edit_profile.php?ref=marketplace';
    }
    //https://php-fusion.test/infusions/marketplace/?view=stores&sid=92
    $more_link = '';
    if ($store_id = Marketplace::getInstance()->getStoreUID( $data['user_id'] )) {
        $more_link = MARKETPLACE.'?view=stores&sid='.$store_id;
    }
    
    return show_profile_panel( 'mpCollections', 'Marketplace Collections', $itemPanel->viewPanel(), $edit_link, $more_link );
    
}

fusion_add_hook( 'profile_home_panels', 'showMPCollections' );

