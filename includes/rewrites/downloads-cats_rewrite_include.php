<?php
    /*-------------------------------------------------------+
    | PHP-Fusion Content Management System
    | Copyright (C) 2002 - 2011 Nick Jones
    | http://www.php-fusion.co.uk/
    +--------------------------------------------------------+
    | Filename: articles_rewrite_include.php
    | Author: Hien
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
        "%download_cat_id%" => "([0-9]+)",
        "%download_cat_name%" => "([a-zA-Z0-9-]+)"
    );
    $pattern = array(
    "dl-cats/%download_cat_id%/%download_cat_name%" => "downloads.php?cat_id=%download_cat_id%",
    );

    $dir_path = ROOT;
    $dbname = DB_DOWNLOAD_CATS;
    $dbid = array("%download_cat_id%" => "download_cat_id");
    $dbinfo = array("%download_cat_name%" => "download_cat_name");

?>
