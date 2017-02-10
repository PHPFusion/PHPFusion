<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: message_rewrite_include.php
| Author: Chan (Frederick MC Chan)
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

$regex = array(
    "%msg_id%" => "([0-9]+)",
    "%msg_const%" => "(new)",
    "%folder%" => "([a-zA-Z._]+)",
    "%folder_inbox%" => "(inbox)",
    "%folder_outbox%" => "(outbox)",
    "%folder_archive%" => "(archive)",
    "%user_name%" => "([a-zA-Z._]+)",
);

$pattern = array(
    "message/%msg_const%/new-message" => "messages.php?msg_send=%msg_const%",
    "message/send/%msg_id%/send-message-to-%user_name%" => "messages.php?msg_send=%msg_id%",
    "message/%folder_outbox%/%msg_id%/message-to-%user_name%" => "messages.php?folder=%folder_outbox%&amp;msg_read=%msg_id%",
    "message/%folder_inbox%/%msg_id%/message-from-%user_name%" => "messages.php?folder=%folder_inbox%&amp;msg_read=%msg_id%",
    "message/%folder_archive%/%msg_id%/message-with-%user_name%" => "messages.php?folder=%folder_archive%&amp;msg_read=%msg_id%",
    "message/%folder%" => "messages.php?folder=%folder%",
    "message" => "messages.php",
);

if (isset($_GET['folder'])) {
    global $userdata;

    $join_table = "";
    $folder = $_GET['folder'];

    switch ($_GET['folder']) {
        case "inbox":
            $join_table = "INNER JOIN ".DB_USERS." u ON m.message_from = u.user_id";
            break;
        case "outbox":
            $join_table = "INNER JOIN ".DB_USERS." u ON m.message_from = u.user_id";
            break;
        case "archive":
            $join_table = "INNER JOIN ".DB_USERS." u ON m.message_from = u.user_id";
            break;
    }

    $pattern_tables["%msg_id%"] = array(
        "table" => DB_MESSAGES." m $join_table",
        "primary_key" => "message_id",
        "query" => "message_user='".$userdata['user_id']."'",
        "id" => array("%msg_id%" => "message_id"),
        "columns" => array("%user_name%" => "user_name"),
    );
}
