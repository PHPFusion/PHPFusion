<?php
/**
 * Babylon user profile forum extensions
 */

use PHPFusion\Infusions\Forum\Classes\Forum_Profile;

defined('IN_FUSION') || exit;

function display_forum_profile( \PHPFusion\UserFields\Pages\ProfileOutput $profile ) {
    
    $locale['forum_ufp_100'] = 'Summary';
    $locale['forum_ufp_101'] = 'Answers';
    $locale['forum_ufp_102'] = 'Questions';
    $locale['forum_ufp_103'] = 'Tags';
    $locale['forum_ufp_104'] = 'Tracked';
    $locale['forum_ufp_105'] = 'Bounties';
    $locale['forum_ufp_106'] = 'Reputation';
    $locale['forum_ufp_110'] = 'Votes';
    $locale['forum_ufp_111'] = 'Activity';
    $locale['forum_ufp_112'] = 'Latest';
    $locale['forum_ufp_113'] = 'post';
    $locale['forum_ufp_114'] = 'posts';
    
    $profile = new Forum_Profile( $profile->profile_id, $locale );
    return $profile->viewUserProfile();
}

fusion_add_hook( 'fusion_profile_page', 'display_forum_profile' );

