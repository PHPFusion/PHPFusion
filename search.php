<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
require_once "maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."search.php";
add_to_title($locale['global_202']);
if (!isset($_POST['rowstart']) || !isnum($_POST['rowstart'])) {
	$_POST['rowstart'] = 0;
}
if (isset($_POST['stext'])) {
	if (is_array($_POST['stext'])) {
		redirect(FUSION_SELF);
	} else {
		$_POST['stext'] = urlencode(stripinput($_POST['stext']));
	}
} else {
	$_POST['stext'] = (isset($_GET['stext']) && $_GET['stext']) ? $_GET['stext'] : '';
}
if (isset($_POST['method'])) {
	$_POST['method'] = ($_POST['method'] == "OR" || $_POST['method'] == "AND") ? $_POST['method'] : "OR";
} else {
	$_POST['method'] = (isset($_GET['method']) && $_GET['method'] == "OR" || $_GET['method'] == "AND") ? $_GET['method'] : 'OR';
}
if (isset($_POST['datelimit'])) {
	$_POST['datelimit'] = isnum($_POST['datelimit']) ? $_POST['datelimit'] : 0;
} else {
	$_POST['datelimit'] = (isset($_GET['datelimit']) && $_GET['datelimit']) ? $_GET['datelimit'] : 0;
}

if (isset($_POST['fields'])) {
	$_POST['fields'] = isnum($_POST['fields']) ? $_POST['fields'] : 2;
} else {
	$_POST['fields'] = (isset($_GET['fields']) && isnum($_GET['fields'])) ? $_GET['fields'] : 2;
}

if (isset($_POST['sort'])) {
	$_POST['sort'] = in_array($_POST['sort'], array("datestamp", "subject", "author")) ? $_POST['sort'] : "datestamp";
} else {
	$_POST['sort'] = (isset($_GET['sort']) && in_array($_GET['sort'], array('datestamp', 'subject', 'author'))) ? $_GET['sort'] : "datestamp";
}
if (isset($_POST['order'])) {
	$_POST['order'] = isnum($_POST['order']) ? $_POST['order'] : 0;
} else {
	$_POST['order'] = (isset($_GET['order']) && isnum($_GET['order'])) ? $_GET['order'] : 0;
}

if (isset($_POST['chars'])) {
	$_POST['chars'] = isnum($_POST['chars']) ? ($_POST['chars'] > 200 ? 200 : $_POST['chars']) : 50;
} else {
	$_POST['chars'] = (isset($_GET['chars']) && isnum($_GET['chars'])) ? ($_GET['chars'] > 200 ? 200 : $_GET['chars']) : 50;
}

if (isset($_POST['forum_id'])) {
	$_POST['forum_id'] = isnum($_POST['forum_id']) ? $_POST['forum_id'] : 0;
} else {
	$_POST['forum_id'] = (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) ? $_GET['forum_id'] : 0;
}

$radio_button = array();
$form_elements = array();
$available = array();
$dh = opendir(INCLUDES."search");
while (FALSE !== ($entry = readdir($dh))) {
	if ($entry != "." && $entry != ".." && preg_match("/include_button.php/i", $entry)) {
		$available[] = str_replace("search_", "", str_replace("_include_button.php", "", $entry));
	}
}
closedir($dh);
$available[] = "all";

if (isset($_GET['stype']) || isset($_POST['stype'])) {
	if (isset($_GET['stype']) && in_array($_GET['stype'], $available) || isset($_POST['stype']) && in_array($_POST['stype'], $available)) {
		$_GET['stype'] = isset($_POST['stype']) ? $_POST['stype'] : $_GET['stype'];
	} else {
		$_GET['stype'] = "all";
	}
}
if (!isset($_GET['stype'])) {
	$_GET['stype'] = isset($_POST['stype']) ? $_POST['stype'] : $settings['default_search'];
}

$c_available = count($available);
for ($i = 0; $i < $c_available-1; $i++) {
	include(INCLUDES."search/search_".$available[$i]."_include_button.php");
}
sort($radio_button);
opentable($locale['400']);
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
echo openform('searchform', 'searchform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : BASEDIR)."search.php", array('downtime'=>0));
echo "<div class='panel panel-default tbl-border'>\n";
echo "<div class='panel-body'>\n";

echo "<div class='row'>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
echo "<label class='label-control'>".$locale['401']."</label>\n<br/>";
echo form_text('', 'stext', 'stext', urldecode($_POST['stext']), array('class'=>'p-l-0 p-r-0 col-xs-12 col-sm-9 col-md-9 col-lg-9'));
echo form_button($locale['402'], 'search', 'search', $locale['402'], array('class'=>'btn-primary flright pull-right'));
echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
echo "<label><input type='radio' name='method' value='OR'".($_POST['method'] == "OR" ? " checked='checked'" : "")." /> ".$locale['403']."</label><br />\n";
echo "<label><input type='radio' name='method' value='AND'".($_POST['method'] == "AND" ? " checked='checked'" : "")." /> ".$locale['404']."</label>\n";
echo "</div>\n</div>\n";

echo "<div class='row'>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
echo "<span><strong>".$locale['405']."</strong></span><br/>\n";
echo "<table width='100%' cellpadding='0' cellspacing='0'>\n";
foreach ($radio_button as $key => $value) {
	echo "<tr>\n<td>".$value."</td>\n</tr>\n";
}
echo "<tr>\n";
echo "<td><label><input type='radio' name='stype' value='all'".($_GET['stype'] == "all" ? " checked='checked'" : "")." onclick=\"display(this.value)\" /> ".$locale['407']."</label></td>\n";
echo "</tr>\n</table>\n";
echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
echo "<span><strong>".$locale['406']."</strong></span><br/>\n";
echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
echo "<td class='tbl1'>".$locale['420']."</td>\n";
echo "<td class='tbl1'>\n";
$date_opts = array(
	'0' => $locale['421'],
	'86400' => $locale['422'],
	'604800' => $locale['423'],
	'1209600' => $locale['424'],
	'2419200' => $locale['425'],
	'7257600' => $locale['426'],
	'14515200' => $locale['427'],
);
echo form_select('', 'datelimit', 'datelimit', $date_opts, $_POST['datelimit'], array('disabled'=> ($_GET['stype'] != "all" ? (in_array("datelimit", $form_elements[$_GET['stype']]['disabled']) ? "1" : "0") : "0")));
echo "</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>&nbsp;</td>\n";
echo "<td class='tbl1'><label><input type='radio' id='fields1' name='fields' value='2'".($_POST['fields'] == 2 ? " checked='checked'" : "").($_GET['stype'] != "all" ? (in_array("fields1", $form_elements[$_GET['stype']]['disabled']) ? " disabled='disabled'" : "") : "")." /> ".$locale['430']."</label><br />\n";
echo "<label><input type='radio' id='fields2' name='fields' value='1'".($_POST['fields'] == 1 ? " checked='checked'" : "").($_GET['stype'] != "all" ? (in_array("fields2", $form_elements[$_GET['stype']]['disabled']) ? " disabled='disabled'" : "") : "")." /> ".$locale['431']."</label><br />\n";
echo "<label><input type='radio' id='fields3' name='fields' value='0'".($_POST['fields'] == 0 ? " checked='checked'" : "").($_GET['stype'] != "all" ? (in_array("fields3", $form_elements[$_GET['stype']]['disabled']) ? " disabled='disabled'" : "") : "")." /> ".$locale['432']."</label></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>".$locale['440']."&nbsp;</td>\n";
echo "<td class='tbl1'>\n";
$sort_opts = array(
	'datestamp' => $locale['441'],
	'subject' => $locale['442'],
	'author' => $locale['443']
);
echo form_select('', 'sort', 'sort', $sort_opts, $_POST['sort'], array('disabled'=> ($_GET['stype'] != "all" ? (in_array("sort", $form_elements[$_GET['stype']]['disabled']) ? "1" : "0") : "0")));
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl1'>&nbsp;</td>\n";
echo "<td class='tbl1'><label><input type='radio' id='order1' name='order' value='0'".($_POST['order'] == 0 ? " checked='checked'" : "").($_GET['stype'] != "all" ? (in_array("order1", $form_elements[$_GET['stype']]['disabled']) ? " disabled='disabled'" : "") : "")." /> ".$locale['450']."</label><br />\n";
echo "<label><input type='radio' id='order2' name='order' value='1'".($_POST['order'] == 1 ? " checked='checked'" : "").($_GET['stype'] != "all" ? (in_array("order2", $form_elements[$_GET['stype']]['disabled']) ? " disabled='disabled'" : "") : "")." /> ".$locale['451']."</label><br /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>".$locale['460']."</td>\n";
echo "<td class='tbl1'>\n";
$char_opts = array(
	'50' => '50',
	'100' => '100',
	'150' => '150',
	'200' => '200'
);
echo form_select('', 'chars', 'chars', $sort_opts, $_POST['sort'], array('disabled'=> ($_GET['stype'] != "all" ? (in_array("chars", $form_elements[$_GET['stype']]['disabled']) ? "1" : "0") : "0")));
echo "</td>\n</tr>\n</tbody>\n</table>\n";
echo "</div>\n</div>\n";
echo "</div></div>\n";
echo closeform();
closetable();

function search_striphtmlbbcodes($text) {
	$text = preg_replace("[\[(.*?)\]]", "", $text);
	$text = preg_replace("<\<(.*?)\>>", "", $text);
	return $text;
}

function search_textfrag($text) {
	if ($_POST['chars'] != 0) {
		$text = nl2br(stripslashes(substr($text, 0, $_POST['chars'])."..."));
	} else {
		$text = nl2br(stripslashes($text));
	}
	return $text;
}

function search_stringscount($text) {
	global $swords;
	$count = 0;
	$c_swords = count($swords); //sizeof($swords)
	for ($i = 0; $i < $c_swords; $i++) {
		$count += substr_count(strtolower($text), strtolower($swords[$i]));
	}
	return $count;
}

function search_querylike($field) {
	global $swords;
	$querylike = "";
	$c_swords = count($swords); //sizeof($swords)
	for ($i = 0; $i < $c_swords; $i++) {
		$querylike .= $field." LIKE '%".$swords[$i]."%'".($i < $c_swords-1 ? " ".$_POST['method']." " : "");
	}
	return $querylike;
}

function search_fieldsvar() {
	$fieldsvar = "(";
	$numargs = func_num_args();
	for ($i = 0; $i < $numargs; $i++) {
		$fieldsvar .= func_get_arg($i).($i < $numargs-1 ? " || " : "");
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
	$navigation_result = "<div align='center' style='margin-top:5px;'>\n".makePageNav($_POST['rowstart'], 10, ($site_search_count > 100 || search_globalarray("") ? 100 : $site_search_count), 3, BASEDIR."search.php?stype=".$_GET['stype']."&amp;stext=".urlencode($_POST['stext'])."&amp;".$composevars)."\n</div>\n";
	return $navigation_result;
}
$composevars = "method=".$_POST['method']."&amp;datelimit=".$_POST['datelimit']."&amp;fields=".$_POST['fields']."&amp;sort=".$_POST['sort']."&amp;order=".$_POST['order']."&amp;chars=".$_POST['chars']."&amp;forum_id=".$_POST['forum_id']."&amp;";
$memory_limit = str_replace("m", "", strtolower(ini_get("memory_limit")))*1024*1024;
$memory_limit = !isnum($memory_limit) ? 8*1024*1024 : $memory_limit < 8*1024*1024 ? 8*1024*1024 : $memory_limit;
$memory_limit = $memory_limit-ceil($memory_limit/4);
$global_string_count = 0;
$site_search_count = 0;
$search_result_array = array();
$navigation_result = "";
$items_count = "";
$_POST['stext'] = urldecode($_POST['stext']);

if ($_POST['stext'] != "" && strlen($_POST['stext']) >= 3) {
	add_to_title($locale['global_201'].$locale['408']);
	opentable($locale['408']);
	$fswords = explode(" ", $_POST['stext']);
	$swords = array();
	$iwords = array();
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
	if ($_GET['stype'] == "all") {
		$dh = opendir(INCLUDES."search");
		while (FALSE !== ($entry = readdir($dh))) {
			if ($entry != "." && $entry != ".." && preg_match("/include.php/i", $entry)) {
				include(INCLUDES."search/".$entry);
			}
		}
		closedir($dh);
	} else {
		include INCLUDES."search/search_".$_GET['stype']."_include.php";
	}
	$c_iwords = count($iwords);
	if ($c_iwords) {
		$txt = "";
		for ($i = 0; $i < $c_iwords; $i++) {
			$txt .= $iwords[$i].($i < $c_iwords-1 ? ", " : "");
		}
		echo "<div class='well m-t-10' style='text-align:center;font-weight:bold'>".sprintf($locale['502'], $txt)."</div><br />";
	}
	if ($_GET['stype'] == "all") {
		$navigation_result = search_navigation(0);
		echo "<div class='quote'>".$items_count."<hr />".THEME_BULLET."&nbsp;<strong>".(($site_search_count > 100 || search_globalarray("")) ? sprintf($locale['530'], $site_search_count) : $site_search_count." ".$locale['510'])."</strong></div><hr />";
	} else {
		echo $items_count."<hr />";
		echo(($site_search_count > 100 || search_globalarray("")) ? "<strong>".sprintf($locale['530'], $site_search_count)."</strong><hr />" : "");
	}
	$c_search_result_array = count($search_result_array);
	if ($_GET['stype'] == "all") {
		$from = $_POST['rowstart'];
		$to = ($c_search_result_array-($_POST['rowstart']+10)) <= 0 ? $c_search_result_array : $_POST['rowstart']+10;
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

} elseif (isset($_POST['stext'])) {
	add_to_title($locale['global_201'].$locale['408']);
	opentable($locale['408']);
	echo "<div class='alert alert-warning m-t-10'>".$locale['501']."</div>\n";
	closetable();
}
require_once THEMES."templates/footer.php";
?>