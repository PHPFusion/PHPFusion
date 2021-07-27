<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: comments_include.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

/**
 * Display a comments form.
 *
 * @param string $comment_type    The comment type you want to display.
 * @param string $comment_db      The database table where the item you want to comment on resides in.
 * @param string $comment_col     The field in the table which holds the id for the item you want to comment on.
 * @param int    $comment_item_id The actual id to the item you are commenting on.
 * @param string $clink           The actual id to the item you are commenting on.
 * @param bool   $ratings         Display ratings.
 */
function showcomments($comment_type, $comment_db, $comment_col, $comment_item_id, $clink, $ratings = FALSE) {
    $html = PHPFusion\Comments::getInstance([
        'comment_item_type'     => $comment_type,
        'comment_db'            => $comment_db,
        'comment_col'           => $comment_col,
        'comment_item_id'       => $comment_item_id,
        'clink'                 => $clink,
        'comment_echo'          => FALSE,
        'comment_allow_subject' => FALSE,
        'comment_allow_ratings' => $ratings
    ], '_'.$comment_type.$comment_item_id)->showComments();
    echo $html;
}
