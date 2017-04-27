<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: custom_pages_rewrite_include.php
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
    "%page_id%" => "([0-9]+)",
    "%page_title%" => "([0-9a-zA-Z._\W]+)",
    "%rowstart%" => "([0-9]+)",
    "%comment%" => "([0-9]+)",
    "%c_start%" => "([0-9]+)",
    "%lang%" => "([a-zA-Z._]+)",
);

$pattern = array(
    'pages/%page_id%/%page_title%' => 'viewpage.php?page_id=%page_id%',
    "pages/%page_id%/row-%rowstart%/c-%comment%/comments-%c_start%/%page_title%" => "viewpage.php?page_id=%page_id%&amp;rowstart=%rowstart%&amp;comment=%comment%&amp;c_start=%c_start%",
    "pages/%page_id%/language-%lang%/row-%rowstart%/%page_title%" => "viewpage.php?page_id=%page_id%&amp;rowstart=%rowstart%&amp;lang=%lang%",
    "pages/%page_id%/%page_title%" => "viewpage.php?page_id=%page_id%",
    "pages/%page_id%/row-%rowstart%/%page_title%" => "viewpage.php?page_id=%page_id%&amp;rowstart=%rowstart%",
    "pages/%page_id%/comments-%c_start%-%rowstart%/%page_title%" => "viewpage.php?page_id=%page_id%&amp;rowstart=%rowstart%&amp;c_start=%c_start%",
    "pages/%page_id%/comments-%c_start%/%page_title%" => "viewpage.php?page_id=%page_id%&amp;c_start=%c_start%",
);

$pattern_tables["%page_id%"] = array(
    "table" => DB_CUSTOM_PAGES,
    "primary_key" => "page_id",
    "id" => array("%page_id%" => "page_id"),
    "columns" => array(
        "%page_title%" => "page_title",
    )
);