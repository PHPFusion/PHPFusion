<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Author: Hien (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

$regex = array(
    "%msg_id%"    => "([0-9]+)",
    "%msg_const%" => "(new)",
    "%folder%"    => "([a-zA-Z._]+)",
);

$pattern = array(
    "message/%msg_const%/new-message"                         => "messages.php?msg_send=%msg_const%",
    "message/send/%msg_id%/send-message-to-%user_name%"       => "messages.php?msg_send=%msg_id%",
	"message/%folder%" => "messages.php?folder=%folder%",
    "message/read/%folder%/%msg_id%/message-from-%user_name%" => "messages.php?folder=%folder%&amp;msg_read=%msg_id%",
    "message"                                                 => "messages.php",
);


$pattern_tables["%msg_id%"] = array(
    "table"       => DB_USERS,
    "primary_key" => "user_id",
    "id"          => array("%msg_id%" => "user_id"),
    "columns"     => array("%user_name%" => "user_name"),
);
