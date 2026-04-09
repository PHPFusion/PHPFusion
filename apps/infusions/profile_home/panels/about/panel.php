<?php
/**
 * Show about user panel
 *
 * @param $data
 *
 * @return string
 */
function showAboutUser( $data ) {
    // do content here.
    $about = new AboutPanel( $data );
    return show_profile_panel( 'about', 'About '.$data['user_name'], $about->viewPanel(), ( iPROFILE || iADMIN && checkrights( 'M' ) ? '#' : '' ), BASEDIR.'profile.php?lookup='.$data['user_id'].'&amp;section=1' );
}

fusion_add_hook( 'profile_home_panels', 'showAboutUser' );

require_once __DIR__.'/about.php';
