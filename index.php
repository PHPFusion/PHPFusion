<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: downloads/index.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";
if (!defined('DOWNLOADS_EXIST')) {
    redirect(BASEDIR."error.php?code=404");
}

$request = pathinfo($_SERVER['REQUEST_URI']);
$result = dbquery("SELECT download_file FROM ".DB_DOWNLOADS." WHERE ".groupaccess('download_visibility')." AND download_file='".form_sanitizer($request['basename'],
        '')."' ");
if (dbrows($result) > 0) {
    $data = dbarray($result);
    require_once INCLUDES."class.httpdownload.php";
    $object = new PHPFusion\httpdownload;
    $object->set_byfile(DOWNLOADS."/files/".$data['download_file']);
    $object->use_resume = TRUE;
    $object->download();
} else {
    redirect(BASEDIR."index.php");
}
