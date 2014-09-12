<?php
    /*-------------------------------------------------------+
    | PHP-Fusion Content Management System
    | Copyright (C) 2002 - 2011 Nick Jones
    | http://www.php-fusion.co.uk/
    +--------------------------------------------------------+
    | Filename: articles_rewrite_include.php
    | Author: Ankur Thakur
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
        "%group_id%" => "([0-9]+)", // this too. fine, np.
        "%group_name%" => "([a-zA-Z0-9-_]+)", // this get from DB, fine np
        "%step%" => "([0-9]+)", // only this.
    );

    // for installations?
    $pattern = array(
        "guild/%group_id%/%group_name%" => "page.php?guild=%group_id%",
        "guild/%group_name%" => "page.php?guild=%group_id%",
        "page/%group_id%/%group_name%/%step%" => "page.php?guild=%group_id%&amp;step=%step%",
    );

    $dbname = DB_USER_GROUPS;
    $dbid = array("%group_id%" => "group_id");
    $dbinfo = array(
        "%group_name%" => "group_name"
    );

?>