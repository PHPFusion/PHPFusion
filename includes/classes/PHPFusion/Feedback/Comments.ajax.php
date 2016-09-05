<?php

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