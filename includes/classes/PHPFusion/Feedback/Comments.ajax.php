<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: /PHPFusion/Feedback/Comments.JSON.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../../../../maincore.php';
require_once THEME."theme.php";
require_once THEMES."templates/render_functions.php";
require_once INCLUDES."comments_include.php";
$comments = PHPFusion\Feedback\Comments::getInstance(
    array(
        'comment_item_type' => $_POST['comment_item_type'],
        'comment_db' => $_POST['comment_db'],
        'comment_col' => $_POST['comment_col'],
        'comment_item_id' => $_POST['comment_item_id'],
        'clink' => $_POST['clink'],
    )
)->showComments();