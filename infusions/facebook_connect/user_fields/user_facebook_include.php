<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: user_facebook_include.php
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

use PHPFusion\Infusions\Facebook_Connect\FacebookConnect;

defined( 'IN_FUSION' ) || exit;

$locale = fusion_get_locale( '', __DIR__.'/locale/'.LANGUAGE.'.php' );

$icon = "<img src='".INCLUDES."user_fields/public/user_facebook/images/facebook.svg' title='Facebook' alt='Facebook'/>";
// Display user field input
if ( $profile_method == "input" ) {

    $fb = new FacebookConnect();
    $user_fields = $fb->displayField( $field_value, $options );

    // Display in profile
} else if ( $profile_method == "display" ) {

    $user_fields = [];
    //$user_fields = [
    //    'icon'  => $icon,
    //    'link'  => $link,
    //    'type'  => 'social',
    //    'title' => $locale['uf_facebook'],
    //    'value' => $field_value ?: ''
    //];

}
