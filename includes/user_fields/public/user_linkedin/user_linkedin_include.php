<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: user_linkedin_include.php
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

$icon = "<img src='".INCLUDES."user_fields/public/user_linkedin/images/linkedin.svg' title='".$locale['uf_linkedin']."' alt='".$locale['uf_linkedin']."'/>";
// Display user field input
if ( $profile_method == "input" ) {
    $options = [
        'inline'           => TRUE,
        'max_length'       => 16,
        'regex'            => '[a-z](?=[\w.]{3,31}$)\w*\.?\w*',
        'error_text'       => $locale['uf_linkedin_error'],
        'regex_error_text' => $locale['uf_linkedin_error_1'],
        'placeholder'      => $locale['uf_linkedin'],
        'label_icon'       => $icon
    ] + $options;
    $user_fields = form_text( 'user_linkedin', $locale['uf_linkedin'], $field_value, $options );
    // Display in profile
} else if ( $profile_method == "display" ) {
    $link = '';
    if ($field_value) {
        $index_userweb = fusion_get_settings( 'index_url_userweb' );
        $link = !preg_match( "@^http(s)?\:\/\/@i", $field_value ) ? "https://www.linkedin.com/in/".$field_value : $field_value;
        $field_value = ( $index_userweb ? '' : "<!--noindex-->" )."<a href='".$link."' title='".$field_value."' ".( $index_userweb ? '' : "rel='nofollow noopener noreferrer' " )."target='_blank'>".$locale['uf_linkedin_desc']."</a>".( $index_userweb ? '' : "<!--/noindex-->" );
    }
    $user_fields = [
        'icon'  => $icon,
        'link'  => $link,
        'type'  => 'social',
        'title' => $locale['uf_linkedin'],
        'value' => $field_value ?: ''
    ];
}
