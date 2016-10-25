<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: PHPFusion/Feedback/Comments.ajax.php
| Author: Frederick MC Chan (Chan)
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
$ajax_respond = array(
    'comment_item_type' => $_POST['comment_item_type'],
    'comment_db' => $_POST['comment_db'],
    'comment_col' => $_POST['comment_col'],
    'comment_item_id' => $_POST['comment_item_id'],
    'clink' => $_POST['clink'],
    'comment_allow_reply' => ($_POST['comment_allow_reply'] == 'true' ? TRUE : FALSE),
    'comment_allow_post' => ($_POST['comment_allow_post'] == 'true' ? TRUE : FALSE),
    'comment_allow_ratings' => ($_POST['comment_allow_ratings'] == 'true' ? TRUE : FALSE),
    'comment_allow_vote' => ($_POST['comment_allow_vote'] == 'true' ? TRUE : FALSE),
    'comment_once' => ($_POST['comment_once'] == 'true' ? TRUE : FALSE),
    'comment_echo' => FALSE, //($_POST['comment_echo'] == 'true' ? true : false),
    'comment_title' => $_POST['comment_title'],
    'comment_form_title' => $_POST['comment_form_title'],
    'comment_count' => ($_POST['comment_count'] === TRUE ? TRUE : FALSE),
    'comment_instance' => (!empty($_POST['comment_instance']) ? $_POST['comment_instance'] : 'Default')
);

echo PHPFusion\Feedback\Comments::getInstance($ajax_respond, $ajax_respond['comment_instance'])->showComments();
