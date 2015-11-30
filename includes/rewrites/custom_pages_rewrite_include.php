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
    "%page_id%" => "([0-9]+)",
    "%page_title%" => "([0-9a-zA-Z._\W]+)",
    "%rowstart%" => "([0-9]+)",
    "%c_start%" => "([0-9]+)",
);

$pattern = array(
    "pages/%page_id%/%page_title%" => "viewpage.php?page_id=%page_id%",
    "pages/browse/%page_id%/%rowstart%" => "viewpage.php?page_id=%page_id%&amp;rowstart=%rowstart%",
    "pages/comments/%page_id%/%c_start%" => "viewpage.php?page_id=%page_id%&amp;c_start=%c_start%",
);

$pattern_tables["%page_id%"] = array(
    "table" => DB_CUSTOM_PAGES,
    "primary_key" => "page_id",
    "id" => array("%page_id%" => "page_id"),
    "columns" => array(
        "%page_title%" => "page_title",
    )
);