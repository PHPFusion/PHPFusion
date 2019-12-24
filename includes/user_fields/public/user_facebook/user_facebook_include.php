<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_facebook_include.php
| Author: PHP-Fusion Development Team
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

$icon = "<img src='".INCLUDES."user_fields/public/user_facebook/images/facebook.svg' title='".$locale['uf_facebook']."' alt='".$locale['uf_facebook']."'/>";
// Display user field input
if ( $profile_method == "input" ) {
    $options = [
        'inline'      => TRUE,
        'placeholder' => $locale['uf_facebook_placeholder'],
        'error_text'  => $locale['uf_facebook_error'],
        'label_icon'  => $icon
    ] + $options;
    $user_fields = form_text( 'user_facebook', $locale['uf_facebook'], $field_value, $options );
    // Display in profile
} else if ( $profile_method == "display" ) {
    $link = '';
    if ( $field_value ) {
        $index_userweb = fusion_get_settings( 'index_url_userweb' );
        $link = !preg_match( "@^http(s)?\:\/\/@i", $field_value ) ? "https://www.facebook.com/".$field_value : $field_value;
        $field_value = ( $index_userweb ? '' : "<!--noindex-->" )."<a href='".$link."' title='".$field_value."' ".( $index_userweb ? '' : "rel='nofollow noopener noreferrer' " )."target='_blank'>".$locale['uf_facebook_desc']."</a>".( $index_userweb ? '' : "<!--/noindex-->" );
    }

    $user_fields = [
        'icon'  => $icon,
        'link'  => $link,
        'type'  => 'social',
        'title' => $locale['uf_facebook'],
        'value' => $field_value ?: ''
    ];

}
