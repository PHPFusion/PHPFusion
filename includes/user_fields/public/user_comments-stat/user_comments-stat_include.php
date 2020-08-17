<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: user_comments-stat_include.php
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

if ( $profile_method == "input" ) {
    $user_fields = '';
    if ( defined( 'ADMIN_PANEL' ) ) {
        $user_fields = "<div class='well m-t-5 text-center'>".$locale['uf_comments-stat']."</div>";
    }
} else if ( $profile_method == "display" ) {
    $user_fields = [
        'title' => $locale['uf_comments-stat'],
        'value' => number_format( dbcount( "(comment_id)", DB_COMMENTS, "comment_name = :cname", [ ':cname' => (int)get( 'lookup' ) ] ) ).""
    ];
}
