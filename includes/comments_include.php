<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: comments_include.php
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

/**
 * Show comments form
 *
 * @param        $comment_type    'A'
 * @param        $comment_db      'DB_ARTICLES'
 * @param        $comment_col     'article_id'
 * @param        $comment_item_id '$data['article_id']'
 * @param        $clink           'BASEDIR.'infusions/articles/articles.php?article_id='.$data['article_id']'
 * @param bool   $ratings         TRUE/FALSE
 * @param string $post_marker     '1.1' (optional)
 * @param bool   $echo            TRUE/FALSE
 *
 * @return string
 */
function showcomments( $comment_type, $comment_db, $comment_col, $comment_item_id, $clink, $ratings = FALSE, $post_marker = '', $echo = TRUE ) {
    $html = PHPFusion\Feedback\Comments::getInstance(
        [
            'comment_item_type'     => $comment_type,
            'comment_db'            => $comment_db,
            'comment_col'           => $comment_col,
            'comment_item_id'       => $comment_item_id,
            'comment_marker'        => $post_marker,
            'clink'                 => $clink,
            'comment_echo'          => FALSE,
            'comment_allow_subject' => FALSE,
            'comment_allow_ratings' => $ratings
        ] )->showComments();
    
    if ( $echo ) {
        echo $html;
    }
    return $html;
    
}
