<?php

use PHPFusion\Infusions\Profile_Home\Classes\JournalForm;
use PHPFusion\Infusions\Profile_Home\ProfileJournals;
use PHPFusion\UserFields\Pages\ProfileOutput;

/**
 * @param ProfileOutput $profile_output
 *
 * @return string
 * @throws Exception
 */
function show_profile_journals( ProfileOutput $profile_output) {
    // do the page for profile journals
    new JournalForm();
    $profile_journals = new ProfileJournals($profile_output);
    return $profile_journals->show();
}

fusion_add_hook( 'fusion_profile_page', 'show_profile_journals' );
