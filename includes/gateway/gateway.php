<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: gateway.php
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
defined( 'IN_FUSION' ) || exit;

require_once "constants_include.php";

require_once "functions_include.php";

require_once THEMES.'templates/global/gateway.php';

// No access to registration unless ban time is over
if ( file_exists( CONTROL_LOCK_FILE ) ) {

    if ( time() - filemtime( CONTROL_LOCK_FILE ) > CONTROL_BAN_TIME ) {
        // this user has complete his punishment
        unlink( CONTROL_LOCK_FILE );

    } else {

        redirect( BASEDIR."error.php?code=401" );

        touch( CONTROL_LOCK_FILE );

        die;
    }
}

echo display_gateway( get_gateway_info() );
