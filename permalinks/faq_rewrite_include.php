<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: faq_rewrite_include.php
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
    "%faq_cat_id%"   => "([0-9]+)",
    "%faq_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%type%"         => "(FQ)",
    "%stype%"        => "(q)"
];

$pattern = [
    "print/%type%/%cat_id%/%faq_cat_name%"             => "print.php?type=%type%&amp;item_id=%cat_id%",
    "submit-%stype%/faq"                               => "submit.php?stype=%stype%",
    "submit-%stype%/faq/submitted-and-thank-you"       => "submit.php?stype=%stype%&amp;submitted=q",
    "frequently-asked-questions"                       => "infusions/faq/faq.php",
    "frequently-asked-questions/category/%faq_cat_id%" => "infusions/faq/faq.php?cat_id=%faq_cat_id%"
];

$pattern_tables["%cat_id%"] = [
    "table"       => DB_FAQS,
    "primary_key" => "faq_id",
    "id"          => ["%faq_id%" => "cat_id"],
    "columns"     => []
];
