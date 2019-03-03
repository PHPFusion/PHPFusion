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
defined('IN_FUSION') || exit;

/**
 * @param            $comment_type
 * @param            $comment_db
 * @param            $comment_col
 * @param            $comment_item_id
 * @param            $clink
 * @param bool|FALSE $ratings
 */
function showcomments($comment_type, $comment_db, $comment_col, $comment_item_id, $clink, $ratings = FALSE) {
    $html = PHPFusion\Feedback\Comments::getInstance(
        [
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
