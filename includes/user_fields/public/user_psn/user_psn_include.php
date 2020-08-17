<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: user_psn_include.php
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

$icon = "<img src='".INCLUDES."user_fields/public/user_psn/images/psn.svg' title='".$locale['uf_psn']."' alt='".$locale['uf_psn']."'/>";
// Display user field input
if ( $profile_method == "input" ) {
    $options = [
        'inline'           => TRUE,
        'error_text'       => $locale['uf_psn_error'],
        'regex_error_text' => $locale['uf_psn_error_1'],
        'placeholder'      => $locale['uf_psn_desc'],
        'label_icon'       => $icon
    ] + $options;

    $user_fields = form_text( 'user_psn', $locale['uf_psn'], $field_value, $options );
    // Display in profile
} else if ( $profile_method == "display" ) {
    $link = '';
    $index_userweb = fusion_get_settings( 'index_url_userweb' );
    if ( $field_value ) {
        $link = !preg_match( "@^http(s)?\:\/\/@i", $field_value ) ? "https://my.playstation.com/".$field_value : $field_value;
        $field_value = ( $index_userweb ? '' : "<!--noindex-->" )."<a href='".$link."' title='".$field_value."' ".( $index_userweb ? '' : "rel='nofollow noopener noreferrer' " )."target='_blank'>".$locale['uf_psn_desc']."</a>".( $index_userweb ? '' : "<!--/noindex-->" );
    }
    $user_fields = [
        'icon'  => $icon,
        'link'  => $link,
        'type'  => 'social',
        'title' => $locale['uf_psn'],
        'value' => $field_value ?: ''
    ];
}
