<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: filter.inc.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

function filter_query($request_key, $field_name) {
    if (isset($_GET[$request_key]) && isnum($_GET[$request_key])) {
        return "$field_name='".stripinput($_GET[$request_key])."'";
    }
}

function filter_access_query($group, $field) {
    $res = '';
    $value = filter_input(INPUT_GET, $group, FILTER_VALIDATE_INT);
    if (is_int($value)) {
        switch ($value) {
            case USER_LEVEL_PUBLIC:
                $res = "$field = ".USER_LEVEL_PUBLIC;
                break;
            case USER_LEVEL_SUPER_ADMIN:
                $res = "1 = 1";
                break;
            case USER_LEVEL_ADMIN:
                "$field in (".USER_LEVEL_PUBLIC.", ".USER_LEVEL_MEMBER.", ".USER_LEVEL_ADMIN.")";
                break;
            case USER_LEVEL_MEMBER:
                $res .= $field." in (".USER_LEVEL_PUBLIC.", ".USER_LEVEL_MEMBER.")";
                break;
            default:
                $res .= $field." in ('".$group."', ".USER_LEVEL_MEMBER.")";
                break;
        }
    }
    return $res;
}

function combine_filter_query($array) {
    $statement = "WHERE";
    // filter out blanks
    $conditions = '';
    foreach ($array as $arr => $value) {
        if ($value) {
            $conditions[] = $value;
        }
    }
    // reloop
    $sql = "";
    if (is_array($conditions)) {
        foreach ($conditions as $arr => $value) {
            if ($value) {
                $sql .= ($arr == count($conditions) - 1) ? $value : "$value AND ";
            }
        }
    }
    if ($sql) {
        $sql = "$statement $sql";
    }
    return $sql;
}

function filter_page_range() {
    return ['5'   => '5', '10' => '10', '15' => '15', '20' => '20', '25' => '25', '30' => '30', '50' => '50',
            '100' => '100', '-' => 'All'];
}

function filter_show($row_start_key = FALSE, $items_per_page_key = FALSE) {
    if (isset($_GET[$row_start_key]) || isset($_GET[$items_per_page_key])) {
        $condition = '';
        if ($_GET[$items_per_page_key]) { // WHEN show is available we set to rowstat, and show items.
            $condition = " LIMIT ";
            $condition .= (isset($_GET[$row_start_key]) && isnum($_GET[$row_start_key])) ? stripinput($_GET[$row_start_key]) : 0;
            if (isset($_GET[$items_per_page_key]) && isnum($_GET[$items_per_page_key])) {
                $condition .= ",".stripinput($_GET[$items_per_page_key]);
            }
        }
        return $condition;
    }
}

// Making Page Navigation
function makepagenav_filter($start, $count, $total, $range = 0, $link = "", $getname = "rowstart", $array = FALSE) {
    if (!defined("PAGENAV")) {
        define("PAGENAV", TRUE);
        add_to_head("<script src='".INCLUDES."filter/paginator.js'></script>");
    }
    return makepagenav_js($start, $count, $total, $range = 0, $link = "", $getname = "rowstart", $showname = "show", $array = FALSE);
}

function makepagenav_nojs($start, $count, $total, $range = 0, $link = "", $getname = "rowstart", $array = FALSE) {
    // start = 0, - is the get rowstart.
    // count = item per page.
    // total = total entries
    // range = total buttons to show after 1
    // link = append custom links
    // getname = no need
    // showname = no need
    global $locale, $aidlink;
    $fusion_query = ($_SERVER['QUERY_STRING']) ? str_replace("&amp;", "&", $_SERVER['QUERY_STRING']) : "";
    if ($link) {
        $link = FUSION_SELF."?$fusion_query&";
    }
    if (!preg_match("#[0-9]+#", $count) || $count == 0)
        return FALSE;
    $getname = (empty($getname)) ? 'rowstart' : $getname;
    $showname = (empty($showname)) ? 'show' : $showname;
    $pg_cnt = ceil($total / $count);
    if ($pg_cnt <= 1) {
        return "";
    }
    $idx_back = $start - $count;
    $idx_next = $start + $count;
    $cur_page = ceil(($start + 1) / $count);
    $res = "<div class='pull-left' style='margin-top:5px; margin-right:20px;'>".$locale['global_092']." ".$cur_page.$locale['global_093'].$pg_cnt.":</div>\n";
    if ($idx_back >= 0) {
        if ($cur_page > ($range + 1)) {
            $res .= "<li><a href='".$link.$getname."=0'>1</a></li>";
            if ($cur_page != ($range + 2)) {
                $res .= "<li>...</li>";
            }
        }
    }
    $idx_fst = max($cur_page - $range, 1);
    $idx_lst = min($cur_page + $range, $pg_cnt);
    if ($range == 0) {
        $idx_fst = 1;
        $idx_lst = $pg_cnt;
    }
    for ($i = $idx_fst; $i <= $idx_lst; $i++) {
        $offset_page = ($i - 1) * $count;
        if ($i == $cur_page) {
            $res .= "<li><span><strong>".$i."</strong></span></li>";
        } else {
            $res .= "<li><a href='".$link.$getname."=".$offset_page."'>".$i."</a></li>";
        }
    }
    $resl = "";
    if ($idx_next < $total) {
        if ($cur_page < ($pg_cnt - $range)) {
            if ($cur_page != ($pg_cnt - $range - 1)) {
                $resl .= "...";
            }
            $res .= "<li><a href='".$link.$getname."=".($pg_cnt - 1) * $count."'>$resl ".$pg_cnt."</a></li>\n";
        }
    }
    return "<noscript><ul class='pagination'>\n".$res."</ul></noscript>\n";
}

function makepagenav_js($start, $count, $total, $range = 0, $link = "", $getname = "rowstart", $array = FALSE) {
    global $locale, $aidlink, $settings;
    // start = 0, - is the get rowstart.
    // count = item per page.
    // total = total entries
    // range = total buttons to show after 1
    // link = append custom links
    // getname = no need
    if (!defined("PAGENAV")) {
        define("PAGENAV", TRUE);
        add_to_head("<script src='".INCLUDES."filter/paginator.js'></script>");
    }
    $fusion_query = ($_SERVER['QUERY_STRING']) ? str_replace("&amp;", "&", $_SERVER['QUERY_STRING']) : "";
    if (isset($_GET['rowstart'])) { // override old query string because this adds in.
        $fusion_query = str_replace("&rowstart=".$_GET['rowstart']."", "", $fusion_query); // remove clean old base.
    }
    if ($link) {
        $link = FUSION_SELF."?$fusion_query&";
    }
    $getname = (empty($getname)) ? 'rowstart' : $getname;
    $pg_cnt = ceil($total / $count);
    if ($pg_cnt <= 1) {
        return "";
    }
    $number_of_pages = $total;
    $cur_page = ceil(($start + 1) / $count);
    if (!is_array($array)) {
        $size = "small";
        $alignment = "left";
        $tooltip = 1;
    } else {
        $size = (array_key_exists("size", $array)) ? $array['size'] : "small";
        $alignment = (array_key_exists("position", $array)) ? $array['position'] : "left";
        $tooltip = (array_key_exists("tooltip", $array) && ($array['tooltip'] == 0)) ? "false" : "true";
    }
    $html = add_to_jquery("
            var options = {
            bootstrapMajorVersion: 3,
            currentPage: $cur_page,
            numberOfPages: $number_of_pages,
            totalPages:  $pg_cnt,
            size: '$size',
            alignment: '$alignment',
            useBootstrapTooltip:$tooltip,
            itemTexts: function (type, page, current) {
                    switch (type) {
                    case 'first':
                        return '<i style=\"padding-bottom:5px;\" class=\"glyphicon glyphicon-fast-backward\"></i>';
                    case 'prev':
                        return '<i style=\"padding-bottom:5px;\" class=\"glyphicon glyphicon-backward\"></i>';
                    case 'next':
                        return '<i style=\"padding-bottom:5px;\" class=\"glyphicon glyphicon-forward\"></i>';
                    case 'last':
                        return '<i style=\"padding-bottom:5px;\" class=\"glyphicon glyphicon-fast-forward\"></i>';
                    case 'page':
                        return page;
                    }
            },

            tooltipTitles: function (type, page, current) {
                    switch (type) {
                    case 'first':
                        return 'Start';
                    case 'prev':
                        return 'Previous';
                    case 'next':
                        return 'Next';
                    case 'last':
                        return 'Last';
                    }
            },

            itemContainerClass: function (type, page, current) {
                return (page === current) ? 'active' : 'pointer-cursor';
            },


            pageUrl: function(type, page, current){

                var offset_page = (page - 1) * $count;
                return '".$link.$getname."='+offset_page;
            },

            onPageClicked: function(e,originalEvent,type,page){
                // development debug only
                //$('#alert-content').text('Page item clicked, type: '+type+' page: '+page);
            }
        }

         $('#makepagenav').bootstrapPaginator(options);
    ");
    $html .= "<ul id='makepagenav'></ul>";
    return $html;
}

