<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: acp_search.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../../maincore.php';

$settings = fusion_get_settings();
if (preg_match("/^([a-z0-9_-]){2,50}$/i", $settings['admin_theme']) && file_exists(THEMES."admin_themes/".$settings['admin_theme']."/acp_theme.php")) {
    require_once THEMES."admin_themes/".$settings['admin_theme']."/acp_theme.php";
}

header("Cache-control: max-age=290304000, public");
$tsstring = gmdate('D, d M Y H:i:s ', TIME).'GMT';
$etag = LANGUAGE.TIME;
$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : FALSE;
$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : FALSE;
if ((($if_none_match && $if_none_match == $etag) || (!$if_none_match)) &&
    ($if_modified_since && $if_modified_since == $tsstring)) {
    header('HTTP/1.1 304 Not Modified');
    exit();
} else {
    header("Last-Modified: $tsstring");
    header("ETag: \"{$etag}\"");
}

header('Content-Type: application/json');

$search = new PHPFusion\AdminSearch();
echo $search->result();

/**
 * Example usage
 */
/*echo '<div class="search-box">';
    echo '<input type="text" id="search_box" name="search_box" class="form-control" placeholder="'.$locale['search'].'"/>';
    echo '<ul id="search_result" style="display: none;"></ul>';
    echo '<img id="ajax-loader" style="width: 30px; display: none;" class="img-responsive center-x m-t-10" alt="Ajax Loader" src="'.IMAGES.'loader.svg"/>';
echo '</div>';

add_to_jquery('
    search_ajax("'.ADMIN.'includes/acp_search.php'.fusion_get_aidlink().'");

    function search_ajax(url) {
        $("#search_box").bind("keyup", function () {
            $.ajax({
                url: url,
                method: "get",
                data: $.param({"pagestring": $(this).val()}),
                dataType: "json",
                beforeSend: function () {
                    $("#ajax-loader").show();
                },
                success: function (e) {
                    if ($("#search_box").val() === "") {
                        $("#adl").show();
                        $("#search_result").html(e).hide();
                        $("#search_result li").html(e).hide();
                    } else {
                        var result = "";

                        if (!e.status) {
                            $.each(e, function (i, data) {
                                if (data) {
                                    result += "<li><a href=\"" + data.link + "\"><img class=\"admin-image\" alt=\"" + data.title + "\" src=\"" + data.icon + "\"/> " + data.title + "</a></li>";
                                }
                            });
                        } else {
                            result = "<li><span id=\"search-status\">" + e.status + "</span></li>";
                        }

                        $("#search_result").html(result).show();
                    }
                },
                complete: function () {
                    $("#ajax-loader").hide();
                }
            });
        });
    }
');*/
