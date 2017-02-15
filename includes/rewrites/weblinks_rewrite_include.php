<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: weblinks_rewrite_include.php
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
    "%weblink_name%" => "([0-9a-zA-Z._\W]+)",
    "%weblink_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%weblink_id%" => "([0-9]+)",
    "%weblink_cat_id%" => "([0-9]+)",
    "%rowstart%" => "([0-9]+)",
    "%stype%" => "(l)"
);

$pattern = array(
    "weblinks/%weblink_cat_id%/%weblink_id%/%weblink_name%" => "infusions/weblinks/weblinks.php?cat_id=%weblink_cat_id%&amp;weblink_id=%weblink_id%",
    "submit/%stype%/weblink" => "submit.php?stype=%stype%",
    "submit/%stype%/weblink/submitted-and-thank-you" => "submit.php?stype=%stype%&amp;submitted=l",
    "weblinks/%weblink_cat_id%/%weblink_cat_name%" => "infusions/weblinks/weblinks.php?cat_id=%weblink_cat_id%",
    "weblinks/%weblink_id%/browse/%weblink_cat_id%/%rowstart%" => "infusions/weblinks/weblinks.php?cat_id=%weblink_cat_id%&amp;rowstart=%rowstart%",
    "weblinks" => "infusions/weblinks/weblinks.php",
);

$pattern_tables["%weblink_id%"] = array(
    "table" => DB_WEBLINKS,
    "primary_key" => "weblink_id",
    "id" => array("%weblink_id%" => "weblink_id"),
    "columns" => array(
        "%weblink_name%" => "weblink_name",
    )
);

$pattern_tables["%weblink_cat_id%"] = array(
    "table" => DB_WEBLINK_CATS,
    "primary_key" => "weblink_cat_id",
    "id" => array("%weblink_cat_id%" => "weblink_cat_id"),
    "columns" => array(
        "%weblink_cat_name%" => "weblink_cat_name"
    )
);