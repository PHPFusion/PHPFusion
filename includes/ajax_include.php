<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: ajax_include.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
header("Cache-control: max-age=290304000, public");
$tsstring = gmdate('D, d M Y H:i:s ', time()).'GMT';
$etag = LANGUAGE.time();
$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : FALSE;
$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : FALSE;
if ((($if_none_match && $if_none_match == $etag) || (!$if_none_match)) &&
    ($if_modified_since && $if_modified_since == $tsstring)) {
    header('HTTP/1.1 304 Not Modified');
    exit();
} else {
    header("Last-Modified: $tsstring");
    header("ETag: \"$etag\"");
}

(fusion_safe() || exit);

/**
 * Sets a header type
 *
 * @param string $value
 */
function header_content_type($value) {
    $output_type = [
        "json"  => "application/json",
        "text"  => "text/plain",
        "html"  => "text/html",
        "files" => "multipart/form-data",
    ];
    if (isset($output_type[$value])) {
        header("Content-Type: ".$output_type[$value]);
    } else {
        header("Content-Type: text/html");
    }
}
