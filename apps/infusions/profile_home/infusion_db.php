<?php
use PHPFusion\Admins;

// Database constants
const DB_PROFILE_PANEL = DB_PREFIX.'profile_panel';
const DB_PROFILE_JOURNAL_COLLECTIONS = DB_PREFIX.'profile_journal_collections';
const DB_PROFILE_JOURNAL_CATS = DB_PREFIX.'profile_journal_cats';
const DB_PROFILE_JOURNALS = DB_PREFIX.'profile_journals';
// define locale path
define( 'PROFILE_HOME_LOCALE', get_inf_locale( INFUSIONS.'profile_home/locale/' ) );

if ( infusion_exists( 'profile_home' ) ) {
    // Add Page Links on Profile Navbar
    //$userFields = \PHPFusion\UserFields::getInstance();
    //$userFields->addOutputPage( 'home', 'Home', INFUSIONS.'profile_home/profile_home.php' );
    //$userFields->addOutputPage( 'journals', 'Journals', INFUSIONS.'profile_home/journals.php');
    //Admins::getInstance()->setSubmitData( 'j', [
    //    'infusion_name' => 'profile_home',
    //    'link'          => INFUSIONS."profile_home/journal_submit.php",
    //    'submit_link'   => "submit.php?stype=j",
    //    'submit_locale' => 'Submit Journal',
    //    'title'         => 'Submit Journal',
    //    'admin_link'    => INFUSIONS."profile_home/administration.php".fusion_get_aidlink()."&amp;ref=submissions&amp;submit_id=%s"
    //] );
}

// Public functions
function callbackGroup($data) {
    return getgroupname($data[':journal_cat_visibility']);
}

function get_inf_locale( $locale_folder ) {
    $base_path = rtrim( $locale_folder, '/' ).DIRECTORY_SEPARATOR;
    if ( is_file( $base_path.LOCALESET.'.php' ) ) {
        return $base_path.LOCALESET.'.php';
    }
    return $base_path.'English.php';
}
