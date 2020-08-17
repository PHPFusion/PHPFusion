<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: profile.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/maincore.php';
require_once THEMES.'templates/header.php';
$locale = fusion_get_locale( '', [ LOCALE.LOCALESET.'user_fields.php' ] );
require_once THEMES."templates/global/profile.php";

if ( $profile_id = get( 'lookup', FILTER_VALIDATE_INT ) ) {
    /*
     * Show user profile
     */
    $userFields = \PHPFusion\UserFields::getInstance();
    echo display_profile( $userFields->profileInfo( $profile_id ) );

} else if ( $group_id = get( 'group_id', FILTER_VALIDATE_INT ) ) {
    /*
     * Show group
     */
    \PHPFusion\UserGroups::getInstance()->setGroup($group_id)->showGroup();

} else {
    redirect(BASEDIR."index.php");
}

require_once THEMES."templates/footer.php";
