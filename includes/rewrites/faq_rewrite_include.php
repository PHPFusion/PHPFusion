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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$regex = array(
    "%faq_cat_id%"   => "([0-9]+)",
    "%faq_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%type%"         => "(FQ)",
    "%stype%"        => "(q)",
);

$pattern = array(
    "print/%type%/%cat_id%"                                             => "print.php?type=%type%&amp;item_id=%cat_id%",
    "submit/%stype%/frequently-asked-questions"                         => "submit.php?stype=%stype%",
    "submit/%stype%/frequently-asked-questions/submitted-and-thank-you" => "submit.php?stype=%stype%&amp;submitted=FQ",
    "frequently-asked-questions"                                        => "infusions/faq/faq.php",
    "frequently-asked-questions/category/%faq_cat_id%"                  => "infusions/faq/faq.php?cat_id=%faq_cat_id%",
);

$pattern_tables["%cat_id%"] = array(
    "table"       => DB_FAQS,
    "primary_key" => "faq_id",
    "id"          => array("%faq_id%" => "cat_id"),
    "columns"     => array()
);