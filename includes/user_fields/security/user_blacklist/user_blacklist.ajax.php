<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_blacklist.ajax.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__."../../../maincore.php";

$locale = fusion_get_locale( '', __DIR__.'/locale/'.LANGUAGE.'.php' );

$user_id = post( 'user_id' );
$userid = fusion_get_userdata( 'user_id' );
$saveblacklist = '';
$result = dbquery( "SELECT * FROM ".DB_USERS." WHERE user_id = :usrid LIMIT 1", [ ':usrid' => (int)$userid ] );
if ( dbrows( $result ) > 0 ) {
    $data = dbarray( $result );
    $user_blacklist = $data['user_blacklist'] ? explode( '.', $data['user_blacklist'] ) : [];
    if ( in_array( $user_id, $user_blacklist ) ) {
        $user_blacklist = array_flip( $user_blacklist );
        unset( $user_blacklist[$user_id] );
        $saveblacklist = implode( '.', array_flip( $user_blacklist ) );
        $result = dbquery("UPDATE ".DB_USERS." SET user_blacklist = :blacklist WHERE user_id = :userid", [ ':blacklist' => $saveblacklist, ':userid' => (int)$userid ]);
        if ( $result ) {
            $users = dbarray( dbquery( "SELECT user_name FROM ".DB_USERS." WHERE user_id = :userid LIMIT 1", [ ':userid' => (int)$user_id ] ) );
            echo sprintf( $locale['uf_blacklist_004'], $users['user_name'] );
        }
    } else {
        echo $locale['uf_blacklist_005'];
    }
} else {
    echo $locale['uf_blacklist_006'];
}
