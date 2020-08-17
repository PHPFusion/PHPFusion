<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: user_location_include.php
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

$locale = fusion_get_locale( '', __DIR__.'/locale/'.LANGUAGE.'.php' );

//Display user field input
if ( $profile_method == "input" ) {
    $options = [
        'inline'     => TRUE,
        'max_length' => 50
    ] + $options;
    $user_fields = form_text( 'user_location', $locale['uf_location'], $field_value, $options );

    //Display in profile
} else if ( $profile_method == "display" ) {
    $user_fields = [
        'title' => $locale['uf_location'],
        'value' => $field_value ?: ''
    ];
}
