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

header('Content-Type: application/json');

$search = new PHPFusion\AdminSearch();
echo $search->Result();

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
