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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
/**
 * @param $comment_type - abbr or short ID
 * @param $comment_db - Current Application DB - DB_BLOG for example.
 * @param $comment_col - current sql primary key column - 'blog_id' for example
 * @param $comment_item_id - current sql primary key value '$_GET['blog_id']' for example
 * @param $clink - current page link 'FUSION_SELF' is ok.
 */
function showcomments($comment_type, $comment_db, $comment_col, $comment_item_id, $clink) {
    PHPFusion\Feedback\Comments::getInstance(
        array(
            'comment_item_type' => $comment_type,
            'comment_db' => $comment_db,
            'comment_col' => $comment_col,
            'comment_item_id' => $comment_item_id,
            'clink' => $clink,
            'comment_echo' => TRUE,
        )
    )->showComments();
}