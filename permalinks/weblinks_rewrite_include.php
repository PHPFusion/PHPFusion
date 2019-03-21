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
defined('IN_FUSION') || exit;

$regex = [
    "%weblink_name%"     => "([0-9a-zA-Z._\W]+)",
    "%weblink_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%weblink_id%"       => "([0-9]+)",
    "%weblink_cat_id%"   => "([0-9]+)",
    "%rowstart%"         => "([0-9]+)",
    "%filter_type%"      => "([0-9a-zA-Z]+)",
    "%switch%"           => "([0-9a-zA-Z]+)",
    "%stype%"            => "(l)"
];

$pattern = [
    "submit-%stype%/weblink"                                                                => "submit.php?stype=%stype%",
    "submit-%stype%/weblink/submitted-and-thank-you"                                        => "submit.php?stype=%stype%&amp;submitted=l",
    "weblinks/%weblink_cat_id%/%weblink_id%/%weblink_name%"                                 => "infusions/weblinks/weblinks.php?cat_id=%weblink_cat_id%&amp;weblink_id=%weblink_id%",
    "weblinks/%weblink_cat_id%/%weblink_cat_name%"                                          => "infusions/weblinks/weblinks.php?cat_id=%weblink_cat_id%",
    "weblinks/%weblink_cat_id%/%weblink_cat_name%/filter/%filter_type%/switchview-%switch%" => "infusions/weblinks/weblinks.php?cat_id=%weblink_cat_id%&amp;type=%filter_type%&amp;switchview=%switch%",
    "weblinks/%weblink_cat_id%/%weblink_cat_name%/filter/%filter_type%"                     => "infusions/weblinks/weblinks.php?cat_id=%weblink_cat_id%&amp;type=%filter_type%",
    "weblinks/%weblink_cat_id%/%weblink_cat_name%/switchview-%switch%"                      => "infusions/weblinks/weblinks.php?cat_id=%weblink_cat_id%&amp;switchview=%switch%",
    "weblinks/%weblink_id%/browse/%weblink_cat_id%/%rowstart%"                              => "infusions/weblinks/weblinks.php?cat_id=%weblink_cat_id%&amp;rowstart=%rowstart%",
    "weblinks/filter/%filter_type%"                                                         => "infusions/weblinks/weblinks.php?type=%filter_type%",
    "weblinks"                                                                              => "infusions/weblinks/weblinks.php"
];

$pattern_tables["%weblink_id%"] = [
    "table"       => DB_WEBLINKS,
    "primary_key" => "weblink_id",
    "id"          => ["%weblink_id%" => "weblink_id"],
    "columns"     => [
        "%weblink_name%" => "weblink_name"
    ]
];

$pattern_tables["%weblink_cat_id%"] = [
    "table"       => DB_WEBLINK_CATS,
    "primary_key" => "weblink_cat_id",
    "id"          => ["%weblink_cat_id%" => "weblink_cat_id"],
    "columns"     => [
        "%weblink_cat_name%" => "weblink_cat_name"
    ]
];
