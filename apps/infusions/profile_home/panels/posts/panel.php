<?php

use PHPFusion\Infusions\Profile_Home\Panels\Posts\JournalPosts;

function show_journal_post( $data ) {
    $more_link = BASEDIR.'profile.php?lookup='.$data['user_id'].'&amp;profile_page=journals';
    $edit_link = '';
    $journalPanel = new JournalPosts( $data );
    return show_profile_panel( 'mpItems', 'Posts', $journalPanel->viewPanel(), $edit_link, $more_link );
}

fusion_add_hook( 'profile_home_panels', 'show_journal_post' );
