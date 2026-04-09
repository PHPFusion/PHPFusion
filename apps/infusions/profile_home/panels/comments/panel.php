<?php
/**
 * -------------------------------------------------------+
 * | PHP-Fusion Content Management System
 * | Copyright (C) PHP-Fusion Inc
 * | https://www.php-fusion.co.uk/
 * +--------------------------------------------------------+
 * | Filename:
 * | Author:
 * +--------------------------------------------------------+
 * | This program is released as free software under the
 * | Affero GPL license. You can redistribute it and/or
 * | modify it under the terms of this license which you
 * | can read by viewing the included agpl.txt or online
 * | at www.gnu.org/licenses/agpl.html. Removal of this
 * | copyright header is strictly prohibited without
 * | written permission from the original author(s).
 * +--------------------------------------------------------
 */

/**
 * @param $data
 *
 * @return string
 */
function phcomments( $data ) {
    // do content here.
    require_once INCLUDES.'comments_include.php';
    $html = '<div class="journal-comments m-0"><div class="panel panel-profile"><div class="panel-body">';
    $html .= showcomments( 'PH', DB_USERS, 'user_id', $data['user_id'], BASEDIR.'profile.php?lookup='.$data['user_id'].'&amp;profile_page=home', FALSE, '', FALSE );
    $html .= '</div></div></div>';
    
    return show_profile_panel( 'comments', 'Comments', $html );
}

fusion_add_hook( 'profile_home_panels', 'phcomments' );
