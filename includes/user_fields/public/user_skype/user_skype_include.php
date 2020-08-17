<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: user_skype_include.php
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

// Display user field input
$icon = "<img src='".INCLUDES."user_fields/public/user_skype/images/skype.svg' title='".$locale['uf_skype']."' alt='".$locale['uf_skype']."'/>";
if ( $profile_method == 'input' ) {
    $options = [
        'inline'           => TRUE,
        'max_length'       => 32,
        // TODO: Also accept MS accounts which are email addresses
        'regex'            => '[a-z.0-9]{5,31}',
        'regex_error_text' => $locale['uf_skype_error_1'],
        'error_text'       => $locale['uf_skype_error'],
        'placeholder'      => $locale['uf_skype_id'],
        'label_icon'       => $icon
    ] + $options;
    $user_fields = form_text( 'user_skype', $locale['uf_skype'], $field_value, $options );
    // Display user field input
} else if ( $profile_method == 'display' ) {
    $user_fields = [
        'icon'  => $icon,
        'title' => $locale['uf_skype'],
        'value' => $field_value ?: ''
    ];
}
