<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules for 7.03
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$regex = [
    "%msg_send%" => "([0-9]+)",
    "%msg_read%" => "([0-9]+)",
    "%msg_id%"   => "([0-9]+)",
    "%folder%"   => "([a-zA-Z0-9-_]+)"
];

$pattern = [
    "message"                     => "messages.php",
    "message/%msg_send%/send"     => "messages.php?msg_send=%msg_send%",
    "message/%msg_send%/reply"    => "messages.php?folder=%folder%&amp;msg_send=%msg_send%&amp;msg_id=%msg_id%",
    "message/%folder%"            => "messages.php?folder=%folder%",
    "message/%folder%/%msg_read%" => "messages.php?folder=%folder%&amp;msg_read=%msg_read%",
];
