<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: search.php
| Author: Robert Gaudyn (Wooya)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__."/maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."search.php";

add_to_title($locale['global_202']);

if (!isset($_REQUEST['rowstart']) || !isnum($_REQUEST['rowstart'])) {
    $_REQUEST['rowstart'] = 0;
}

if (isset($_REQUEST['stext'])) {
    if (is_array($_REQUEST['stext'])) {
        redirect(FUSION_SELF);
    } else {
        $_REQUEST['stext'] = urlencode(stripinput($_REQUEST['stext']));
    }
} else {
    $_REQUEST['stext'] = "";
}

if (isset($_REQUEST['method'])) {
    $_REQUEST['method'] = ($_REQUEST['method'] == "OR" || $_REQUEST['method'] == "AND") ? $_REQUEST['method'] : "OR";
} else {
    $_REQUEST['method'] = "OR";
}

if (isset($_REQUEST['datelimit'])) {
    $_REQUEST['datelimit'] = isnum($_REQUEST['datelimit']) ? $_REQUEST['datelimit'] : 0;
} else {
    $_REQUEST['datelimit'] = 0;
}

if (isset($_REQUEST['fields'])) {
    $_REQUEST['fields'] = isnum($_REQUEST['fields']) ? $_REQUEST['fields'] : 2;
} else {
    $_REQUEST['fields'] = 2;
}

if (isset($_REQUEST['sort'])) {
    $_REQUEST['sort'] = in_array($_REQUEST['sort'], ["datestamp", "subject", "author"]) ? $_REQUEST['sort'] : "datestamp";
} else {
    $_REQUEST['sort'] = "datestamp";
}

if (isset($_REQUEST['order'])) {
    $_REQUEST['order'] = isnum($_REQUEST['order']) ? $_REQUEST['order'] : 0;
} else {
    $_REQUEST['order'] = 0;
}

if (isset($_REQUEST['chars'])) {
    $_REQUEST['chars'] = isnum($_REQUEST['chars']) ? ($_REQUEST['chars'] > 200 ? 200 : $_REQUEST['chars']) : 50;
} else {
    $_REQUEST['chars'] = 50;
}

if (isset($_REQUEST['forum_id'])) {
    $_REQUEST['forum_id'] = isnum($_REQUEST['forum_id']) ? $_REQUEST['forum_id'] : 0;
} else {
    $_REQUEST['forum_id'] = 0;
}

$radio_button = [];
$form_elements = [];
$available = [];
$dh = opendir(INCLUDES."search");
while (FALSE !== ($entry = readdir($dh))) {
    if ($entry != "." && $entry != ".." && preg_match("/include_button.php/i", $entry)) {
        $available[] = str_replace("search_", "", str_replace("_include_button.php", "", $entry));
    }
}
closedir($dh);
$available[] = "all";

if (isset($_REQUEST['stype'])) {
    $_REQUEST['stype'] = in_array($_REQUEST['stype'], $available) ? $_REQUEST['stype'] : "all";
}
if (!isset($_REQUEST['stype'])) {
    $_REQUEST['stype'] = $settings['default_search'];
}

$c_available = count($available);
for ($i = 0; $i < $c_available - 1; $i++) {
    include(INCLUDES."search/search_".$available[$i]."_include_button.php");
}
sort($radio_button);

opentable(str_replace('[SITENAME]', $settings['sitename'], $locale['400']));

// maybe rewrite with jQuery
$search_js = "<script type='text/javascript'>\n/*<![CDATA[*/\n";
$search_js .= "function display(val) {\nswitch (val) {\n";
foreach ($form_elements as $type => $array1) {
    $search_js .= "case '".$type."':\n";
    foreach ($array1 as $what => $array2) {
        foreach ($array2 as $elements => $value) {
            if ($what == "enabled") {
                $search_js .= "document.getElementById('".$value."').disabled = false;\n";
            } else if ($what == "disabled") {
                $search_js .= "document.getElementById('".$value."').disabled = true;\n";
            } else if ($what == "display") {
                $search_js .= "document.getElementById('".$value."').style.display = 'block';\n";
            } else if ($what == "nodisplay") {
                $search_js .= "document.getElementById('".$value."').style.display = 'none';\n";
            }
        }
    }
    $search_js .= "break;\n";
}

$search_js .= "case 'all':\n";
$search_js .= "document.getElementById('datelimit').disabled = false;\n";
$search_js .= "document.getElementById('fields1').disabled = false;\n";
$search_js .= "document.getElementById('fields2').disabled = false;\n";
$search_js .= "document.getElementById('fields3').disabled = false;\n";
$search_js .= "document.getElementById('sort').disabled = false;\n";
$search_js .= "document.getElementById('order1').disabled = false;\n";
$search_js .= "document.getElementById('order2').disabled = false;\n";
$search_js .= "document.getElementById('chars').disabled = false;\n";
$search_js .= "break;\n}\n}\n/*]]>*/\n</script>";
add_to_footer($search_js);

echo "<form id='searchform' name='searchform' method='POST' action='".BASEDIR."search.php'>\n";
echo "<table width='100%' cellpadding='0' cellspacing='1' class='tbl-border'>\n<tr>\n";
echo "<td class='tbl2' colspan='2'><strong>".$locale['401']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1' style='width:50%;'>\n";
echo "<input type='text' name='stext' value='".urldecode($_REQUEST['stext'])."' class='textbox' style='width:200px' />\n";
echo "<input type='submit' name='search' value='".$locale['402']."' class='button' />\n</td>\n";
echo "<td class='tbl1' align='left' style='width:50%;'>\n";
echo "<label><input type='radio' name='method' value='OR'".($_REQUEST['method'] == "OR" ? " checked='checked'" : "")." /> ".$locale['403']."</label><br />\n";
echo "<label><input type='radio' name='method' value='AND'".($_REQUEST['method'] == "AND" ? " checked='checked'" : "")." /> ".$locale['404']."</label></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl2'><strong>".$locale['405']."</strong></td>\n";
echo "<td class='tbl2'><strong>".$locale['406']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>\n";
echo "<table width='100%' cellpadding='0' cellspacing='0'>\n";
foreach ($radio_button as $key => $value) {
    echo "<tr>\n<td>".$value."</td>\n</tr>\n";
}
echo "<tr>\n";
echo "<td><label><input type='radio' name='stype' value='all'".($_REQUEST['stype'] == "all" ? " checked='checked'" : "")." onclick=\"display(this.value)\" /> ".$locale['407']."</label></td>\n";
echo "</tr>\n</table>\n</td>\n";
echo "<td align='left' valign='top'>\n";
echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
echo "<td class='tbl1'>".$locale['420']."</td>\n";
echo "<td class='tbl1'><select id='datelimit' name='datelimit' class='textbox'".($_REQUEST['stype'] != "all" ? (in_array("datelimit", $form_elements[$_REQUEST['stype']]['disabled']) ? " disabled='disabled'" : "") : "").">\n";
echo "<option value='0'".($_REQUEST['datelimit'] == 0 ? " selected='selected'" : "").">".$locale['421']."</option>\n";
echo "<option value='86400'".($_REQUEST['datelimit'] == 86400 ? " selected='selected'" : "").">".$locale['422']."</option>\n";
echo "<option value='604800'".($_REQUEST['datelimit'] == 604800 ? " selected='selected'" : "").">".$locale['423']."</option>\n";
echo "<option value='1209600'".($_REQUEST['datelimit'] == 1209600 ? " selected='selected'" : "").">".$locale['424']."</option>\n";
echo "<option value='2419200'".($_REQUEST['datelimit'] == 2419200 ? " selected='selected'" : "").">".$locale['425']."</option>\n";
echo "<option value='7257600'".($_REQUEST['datelimit'] == 7257600 ? " selected='selected'" : "").">".$locale['426']."</option>\n";
echo "<option value='14515200'".($_REQUEST['datelimit'] == 14515200 ? " selected='selected'" : "").">".$locale['427']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>&nbsp;</td>\n";
echo "<td class='tbl1'><label><input type='radio' id='fields1' name='fields' value='2'".($_REQUEST['fields'] == 2 ? " checked='checked'" : "").($_REQUEST['stype'] != "all" ? (in_array("fields1", $form_elements[$_REQUEST['stype']]['disabled']) ? " disabled='disabled'" : "") : "")." /> ".$locale['430']."</label><br />\n";
echo "<label><input type='radio' id='fields2' name='fields' value='1'".($_REQUEST['fields'] == 1 ? " checked='checked'" : "").($_REQUEST['stype'] != "all" ? (in_array("fields2", $form_elements[$_REQUEST['stype']]['disabled']) ? " disabled='disabled'" : "") : "")." /> ".$locale['431']."</label><br />\n";
echo "<label><input type='radio' id='fields3' name='fields' value='0'".($_REQUEST['fields'] == 0 ? " checked='checked'" : "").($_REQUEST['stype'] != "all" ? (in_array("fields3", $form_elements[$_REQUEST['stype']]['disabled']) ? " disabled='disabled'" : "") : "")." /> ".$locale['432']."</label></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>".$locale['440']."&nbsp;</td>\n";
echo "<td class='tbl1'><select id='sort' name='sort' class='textbox'".($_REQUEST['stype'] != "all" ? (in_array("sort", $form_elements[$_REQUEST['stype']]['disabled']) ? " disabled='disabled'" : "") : "").">\n";
echo "<option value='datestamp'".($_REQUEST['sort'] == "datestamp" ? " selected='selected'" : "").">".$locale['441']."</option>\n";
echo "<option value='subject'".($_REQUEST['sort'] == "subject" ? " selected='selected'" : "").">".$locale['442']."</option>\n";
echo "<option value='author'".($_REQUEST['sort'] == "author" ? " selected='selected'" : "").">".$locale['443']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>&nbsp;</td>\n";
echo "<td class='tbl1'><label><input type='radio' id='order1' name='order' value='0'".($_REQUEST['order'] == 0 ? " checked='checked'" : "").($_REQUEST['stype'] != "all" ? (in_array("order1", $form_elements[$_REQUEST['stype']]['disabled']) ? " disabled='disabled'" : "") : "")." /> ".$locale['450']."</label><br />\n";
echo "<label><input type='radio' id='order2' name='order' value='1'".($_REQUEST['order'] == 1 ? " checked='checked'" : "").($_REQUEST['stype'] != "all" ? (in_array("order2", $form_elements[$_REQUEST['stype']]['disabled']) ? " disabled='disabled'" : "") : "")." /> ".$locale['451']."</label><br /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>".$locale['460']."</td>\n";
echo "<td class='tbl1'><select id='chars' name='chars' class='textbox'".($_REQUEST['stype'] != "all" ? (in_array("chars", $form_elements[$_REQUEST['stype']]['disabled']) ? " disabled='disabled'" : "") : "").">\n";
echo "<option value='50'".($_REQUEST['chars'] == 50 ? " selected='selected'" : "").">50</option>\n";
echo "<option value='100'".($_REQUEST['chars'] == 100 ? " selected='selected'" : "").">100</option>\n";
echo "<option value='150'".($_REQUEST['chars'] == 150 ? " selected='selected'" : "").">150</option>\n";
echo "<option value='200'".($_REQUEST['chars'] == 200 ? " selected='selected'" : "").">200</option>\n";
echo "</select> ".$locale['461']."</td>\n";
echo "</tr>\n</table>\n";
echo "</td>\n</tr>\n</table>\n</form>\n";
closetable();

function search_striphtmlbbcodes($text) {
    $text = preg_replace("[\[(.*?)\]]", "", $text);
    $text = preg_replace("<\<(.*?)\>>", "", $text);
    return $text;
}

function search_textfrag($text) {
    if ($_REQUEST['chars'] != 0) {
        if (function_exists('mb_substr')) {
            $text = nl2br(stripslashes(mb_substr($text, 0, $_REQUEST['chars'], 'UTF-8')."..."));
        } else {
            $text = nl2br(stripslashes(substr($text, 0, $_REQUEST['chars'])."..."));
        }
    } else {
        $text = nl2br(stripslashes($text));
    }
    return $text;
}

function search_stringscount($text) {
    global $swords;
    $count = 0;
    if (is_array($swords)) {
        $c_swords = count($swords); //sizeof($swords)
        for ($i = 0; $i < $c_swords; $i++) {
            $count += substr_count(strtolower($text), strtolower($swords[$i]));
        }
    }

    return $count;
}

function search_querylike($field) {
    global $swords;
    $querylike = "";
    $c_swords = number_format(count((array)$swords)); //sizeof($swords)
    for ($i = 0; $i < $c_swords; $i++) {
        $querylike .= $field." LIKE '%".(!empty($swords[$i]) ? $swords[$i] : ' ')."%'".($i < $c_swords - 1 ? " ".$_REQUEST['method']." " : "");
    }

    return $querylike;
}

function search_fieldsvar() {
    $fieldsvar = "(";
    $numargs = func_num_args();
    for ($i = 0; $i < $numargs; $i++) {
        $fieldsvar .= func_get_arg($i).($i < $numargs - 1 ? " || " : "");
    }
    $fieldsvar .= ")";
    return $fieldsvar;
}

function search_globalarray($search_result) {
    global $search_result_array, $global_string_count, $memory_limit;
    $global_string_count += strlen($search_result);
    if ($memory_limit > $global_string_count) {
        $search_result_array[] = $search_result;
        $memory_exhaused = FALSE;
    } else {
        $memory_exhaused = TRUE;
    }
    return $memory_exhaused;
}

function search_navigation($rows) {
    global $site_search_count, $composevars;
    $site_search_count += $rows;
    $navigation_result = "<div align='center' style='margin-top:5px;'>\n".makePageNav($_REQUEST['rowstart'], 20, ($site_search_count > 100 || search_globalarray("") ? 100 : $site_search_count), 3, BASEDIR."search.php?stype=".$_REQUEST['stype']."&amp;stext=".urlencode($_REQUEST['stext'])."&amp;".$composevars)."\n</div>\n";
    return $navigation_result;
}

$composevars = "method=".$_REQUEST['method']."&amp;datelimit=".$_REQUEST['datelimit']."&amp;fields=".$_REQUEST['fields']."&amp;sort=".$_REQUEST['sort']."&amp;order=".$_REQUEST['order']."&amp;chars=".$_REQUEST['chars']."&amp;forum_id=".$_REQUEST['forum_id']."&amp;";

$memory_limit = str_replace("m", "", strtolower(ini_get("memory_limit"))) * 1024 * 1024;
$memory_limit = (!isnum($memory_limit) ? 8 * 1024 * 1024 : $memory_limit < 8 * 1024 * 1024) ? 8 * 1024 * 1024 : $memory_limit;
$memory_limit = $memory_limit - ceil($memory_limit / 4);
$global_string_count = 0;
$site_search_count = 0;
$search_result_array = [];
$navigation_result = "";
$items_count = "";

$_REQUEST['stext'] = urldecode($_REQUEST['stext']);
if ($_REQUEST['stext'] != "" && strlen($_REQUEST['stext']) >= 3) {
    add_to_title($locale['global_201'].$locale['408']);
    opentable($locale['408']);
    $fswords = explode(" ", $_REQUEST['stext']);
    $swords = [];
    $iwords = [];
    $c_fswords = count($fswords); //sizeof($fswords)
    for ($i = 0; $i < $c_fswords; $i++) {
        if (strlen($fswords[$i]) >= 3) {
            $swords[] = $fswords[$i];
        } else {
            $iwords[] = $fswords[$i];
        }
    }
    unset($fswords);

    $c_swords = count($swords);
    if ($c_swords == 0) {
        redirect(FUSION_SELF);
    } //sizeof($swords)
    $higlight = "";
    $i = 1;
    foreach ($swords as $hlight) {
        $higlight .= "'".$hlight."'";
        $higlight .= ($i < $c_swords ? "," : "");
        $i++;
    }
    add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.highlight.js'></script>");
    $highlight_js = "<script type='text/javascript'>";
    $highlight_js .= "/*<![CDATA[*/";
    $highlight_js .= "jQuery(document).ready(function(){";
    $highlight_js .= "jQuery('.search_result').highlight([".$higlight."],{wordsOnly:true});";
    $highlight_js .= "jQuery('.highlight').css({backgroundColor:'#FFFF88'});"; //better via theme or settings
    $highlight_js .= "});";
    $highlight_js .= "/*]]>*/";
    $highlight_js .= "</script>";
    add_to_footer($highlight_js);

    if ($_REQUEST['stype'] == "all") {
        $dh = opendir(INCLUDES."search");
        while (FALSE !== ($entry = readdir($dh))) {
            if ($entry != "." && $entry != ".." && preg_match("/include.php/i", $entry)) {
                include(INCLUDES."search/".$entry);
            }
        }
        closedir($dh);
    } else {
        include INCLUDES."search/search_".$_REQUEST['stype']."_include.php";
    }

    $c_iwords = count($iwords);
    if ($c_iwords) {
        $txt = "";
        for ($i = 0; $i < $c_iwords; $i++) {
            $txt .= $iwords[$i].($i < $c_iwords - 1 ? ", " : "");
        }
        echo "<div style='text-align:center;font-weight:bold'>".sprintf($locale['502'], $txt)."</div><br />";
    }

    if ($_REQUEST['stype'] == "all") {
        $navigation_result = search_navigation(0);
        echo "<div class='quote'>".$items_count." <hr />".THEME_BULLET."&nbsp;<strong>".(($site_search_count > 100 || search_globalarray("")) ? sprintf($locale['530'], $site_search_count) : $site_search_count." ".$locale['510'])."</strong></div><hr />";
    } else {
        echo $items_count."<hr />";
        echo(($site_search_count > 100 || search_globalarray("")) ? "<strong>".sprintf($locale['530'], $site_search_count)."</strong><hr />" : "");
    }

    $c_search_result_array = count($search_result_array);
    if ($_REQUEST['stype'] == "all") {
        $from = $_REQUEST['rowstart'];
        $to = ($c_search_result_array - ($_REQUEST['rowstart'] + 10)) <= 0 ? $c_search_result_array : $_REQUEST['rowstart'] + 10;
    } else {
        $from = 0;
        $to = $c_search_result_array < 10 ? $c_search_result_array : 10;
    }

    echo "<div class='search_result'>\n";
    for ($i = $from; $i < $to; $i++) {
        echo $search_result_array[$i];
    }
    echo "</div>\n";
    echo $navigation_result;
    closetable();

} else if (isset($_REQUEST['stext'])) {
    add_to_title($locale['global_201'].$locale['408']);
    opentable($locale['408']);
    echo $locale['501'];
    closetable();
}

require_once THEMES."templates/footer.php";
