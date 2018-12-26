<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: search_rewrite_include.php
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
$regex = [
    "%stext%"     => "([0-9a-zA-Z._\W]+)",
    "%stype%"     => "([0-9a-zA-Z._]+)",
    "%method%"    => "([0-9a-zA-Z._]+)",
    "%datelimit%" => "([0-9]+)",
    "%fields%"    => "([0-9]+)",
    "%sort%"      => "([\p{L}a-zA-Z]+)",
    "%order%"     => "([0-9]+)",
    "%chars%"     => "([\p{L}a-zA-Z]+)",
    "%forum_id%"  => "([0-9]+)"
];

$pattern = [
    "search"                                                                                                            => "search.php",
    "search?stext=%stext%"                                                                                              => "search.php?stext=%stext%",
    "search?stext=%stext%/%stype%"                                                                                      => "search.php?stext=%stext%&amp;stype=%stype%",
    "search?stext=%stext%/%stype%/%sort%"                                                                               => "search.php?stext=%stext%&amp;stype=%stype%&amp;sort=%sort%",
    "search?stext=%stext%?search=Search/%method%/%forum_id%/%stype%/%datelimit%/fields=%fields%/%sort%/%order%/%chars%" => "search.php?stext=%stext%&amp;search=Search&amp;method=%method%&amp;forum_id=%forum_id%&amp;stype=%stype%&amp;datelimit=%datelimit%&amp;fields=%fields%&amp;sort=%sort%&amp;order=%order%&amp;chars=%chars%",
];
