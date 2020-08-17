<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: user_line_include.php
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

$icon = "<img src='".INCLUDES."user_fields/public/user_line/images/line.svg' title='".$locale['uf_line']."' alt='".$locale['uf_line']."'/>";
// Display user field input
if ( $profile_method == "input" ) {
    $options = [
        'inline'           => TRUE,
        'max_length'       => 16,
        'regex'            => '[a-z](?=[\w.]{3,31}$)\w*\.?\w*',
        'error_text'       => $locale['uf_line_error'],
        'regex_error_text' => $locale['uf_line_error_1'],
        'placeholder'      => $locale['uf_line'],
        'label_icon'       => $icon
    ] + $options;
    $user_fields = form_text( 'user_line', $locale['uf_line'], $field_value, $options );
    // Display in profile
} else if ( $profile_method == "display" ) {
    $user_fields = [
        'icon'  => $icon,
        'title' => $locale['uf_line'],
        'value' => $field_value ?: ''
    ];
}
