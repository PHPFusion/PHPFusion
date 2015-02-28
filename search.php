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

if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }

if (isset($_GET['stext'])) { 
	if (is_array($_GET['stext'])) {
		redirect(FUSION_SELF);
	} else {
		$_GET['stext'] = urlencode(stripinput($_GET['stext'])); 
	}
} else {
	$_GET['stext'] = ""; 
}

if (isset($_GET['method'])) { $_GET['method'] = ($_GET['method']=="OR" || $_GET['method']=="AND") ? $_GET['method'] : "OR"; }
else { $_GET['method'] = "OR"; }

if (isset($_GET['datelimit'])) { $_GET['datelimit'] = isnum($_GET['datelimit']) ? $_GET['datelimit'] : 0; }
else { $_GET['datelimit'] = 0; }

if (isset($_GET['fields'])) { $_GET['fields'] = isnum($_GET['fields']) ? $_GET['fields'] : 2; }
else { $_GET['fields'] = 2; }

if (isset($_GET['sort'])) { $_GET['sort'] = in_array($_GET['sort'], array("datestamp", "subject", "author")) ? $_GET['sort'] : "datestamp"; }
else { $_GET['sort'] = "datestamp"; }

if (isset($_GET['order'])) { $_GET['order'] = isnum($_GET['order']) ? $_GET['order'] : 0; }
else { $_GET['order'] = 0; }

if (isset($_GET['chars'])) { $_GET['chars'] = isnum($_GET['chars']) ? ($_GET['chars'] > 200 ? 200 : $_GET['chars']) : 50; }
else { $_GET['chars'] = 50; }

if (isset($_GET['forum_id'])) { $_GET['forum_id'] = isnum($_GET['forum_id']) ? $_GET['forum_id'] : 0; }
else { $_GET['forum_id'] = 0; }

$radio_button = array();
$form_elements = array();
$available = array();
$dh = opendir(INCLUDES."search");
while (false !== ($entry = readdir($dh))) {
	if ($entry != "." && $entry != ".." && preg_match("/include_button.php/i", $entry)) {
		$available[] = str_replace("search_", "", str_replace("_include_button.php", "", $entry));
	}
}
closedir($dh);
$available[] = "all";

if (isset($_GET['stype'])) { $_GET['stype'] = in_array($_GET['stype'], $available) ? $_GET['stype'] : "all"; }
if (!isset($_GET['stype'])) { $_GET['stype'] = $settings['default_search']; }

$c_available = count($available);
for ($i = 0; $i < $c_available - 1; $i++) {
	include (INCLUDES."search/search_".$available[$i]."_include_button.php");
}
sort($radio_button);

opentable($locale['400']);

// maybe rewrite with jQuery
$search_js  = "<script type='text/javascript'>\n/*<![CDATA[*/\n";
$search_js .= "function display(val) {\nswitch (val) {\n";
foreach ($form_elements as $type => $array1) {
	$search_js .= "case '".$type."':\n";
	foreach ($array1 as $what => $array2) {
		foreach ($array2 as $elements => $value) {
			if ($what=="enabled") {
				$search_js .= "document.getElementById('".$value."').disabled = false;\n";
			} else if ($what=="disabled") {
				$search_js .= "document.getElementById('".$value."').disabled = true;\n";
			} else if ($what=="display") {
				$search_js .= "document.getElementById('".$value."').style.display = 'block';\n";
			} else if ($what=="nodisplay") {
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

echo "<form id='searchform' name='searchform' method='get' action='".FUSION_SELF."'>\n";
echo "<table width='100%' cellpadding='0' cellspacing='1' class='tbl-border'>\n<tr>\n";
echo "<td class='tbl2' colspan='2'><strong>".$locale['401']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1' style='width:50%;'>\n";
echo "<input type='text' name='stext' value='".urldecode($_GET['stext'])."' class='textbox' style='width:200px' />\n";
echo "<input type='submit' name='search' value='".$locale['402']."' class='button' />\n</td>\n";
echo "<td class='tbl1' align='left' style='width:50%;'>\n";
echo "<label><input type='radio' name='method' value='OR'".($_GET['method'] == "OR" ? " checked='checked'" : "")." /> ".$locale['403']."</label><br />\n";
echo "<label><input type='radio' name='method' value='AND'".($_GET['method'] == "AND" ? " checked='checked'" : "")." /> ".$locale['404']."</label></td>\n";
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
echo "<td><label><input type='radio' name='stype' value='all'".($_GET['stype'] == "all" ? " checked='checked'" : "")." onclick=\"display(this.value)\" /> ".$locale['407']."</label></td>\n";
echo "</tr>\n</table>\n</td>\n";
echo "<td align='left' valign='top'>\n";
echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
echo "<td class='tbl1'>".$locale['420']."</td>\n";
echo "<td class='tbl1'><select id='datelimit' name='datelimit' class='textbox'".($_GET['stype']!="all"?(in_array("datelimit", $form_elements[$_GET['stype']]['disabled'])?" disabled='disabled'":""):"").">\n";
echo "<option value='0'".($_GET['datelimit']==0?" selected='selected'":"").">".$locale['421']."</option>\n";
echo "<option value='86400'".($_GET['datelimit']==86400?" selected='selected'":"").">".$locale['422']."</option>\n";
echo "<option value='604800'".($_GET['datelimit']==604800?" selected='selected'":"").">".$locale['423']."</option>\n";
echo "<option value='1209600'".($_GET['datelimit']==1209600?" selected='selected'":"").">".$locale['424']."</option>\n";
echo "<option value='2419200'".($_GET['datelimit']==2419200?" selected='selected'":"").">".$locale['425']."</option>\n";
echo "<option value='7257600'".($_GET['datelimit']==7257600?" selected='selected'":"").">".$locale['426']."</option>\n";
echo "<option value='14515200'".($_GET['datelimit']==14515200?" selected='selected'":"").">".$locale['427']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>&nbsp;</td>\n";
echo "<td class='tbl1'><label><input type='radio' id='fields1' name='fields' value='2'".($_GET['fields']==2?" checked='checked'":"").($_GET['stype']!="all"?(in_array("fields1", $form_elements[$_GET['stype']]['disabled'])?" disabled='disabled'":""):"")." /> ".$locale['430']."</label><br />\n";
echo "<label><input type='radio' id='fields2' name='fields' value='1'".($_GET['fields']==1?" checked='checked'":"").($_GET['stype']!="all"?(in_array("fields2", $form_elements[$_GET['stype']]['disabled'])?" disabled='disabled'":""):"")." /> ".$locale['431']."</label><br />\n";
echo "<label><input type='radio' id='fields3' name='fields' value='0'".($_GET['fields']==0?" checked='checked'":"").($_GET['stype']!="all"?(in_array("fields3", $form_elements[$_GET['stype']]['disabled'])?" disabled='disabled'":""):"")." /> ".$locale['432']."</label></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>".$locale['440']."&nbsp;</td>\n";
echo "<td class='tbl1'><select id='sort' name='sort' class='textbox'".($_GET['stype']!="all"?(in_array("sort", $form_elements[$_GET['stype']]['disabled'])?" disabled='disabled'":""):"").">\n";
echo "<option value='datestamp'".($_GET['sort']=="datestamp"?" selected='selected'":"").">".$locale['441']."</option>\n";
echo "<option value='subject'".($_GET['sort']=="subject"?" selected='selected'":"").">".$locale['442']."</option>\n";
echo "<option value='author'".($_GET['sort']=="author"?" selected='selected'":"").">".$locale['443']."</option>\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>&nbsp;</td>\n";
echo "<td class='tbl1'><label><input type='radio' id='order1' name='order' value='0'".($_GET['order']==0?" checked='checked'":"").($_GET['stype']!="all"?(in_array("order1", $form_elements[$_GET['stype']]['disabled'])?" disabled='disabled'":""):"")." /> ".$locale['450']."</label><br />\n";
echo "<label><input type='radio' id='order2' name='order' value='1'".($_GET['order']==1?" checked='checked'":"").($_GET['stype']!="all"?(in_array("order2", $form_elements[$_GET['stype']]['disabled'])?" disabled='disabled'":""):"")." /> ".$locale['451']."</label><br /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>".$locale['460']."</td>\n";
echo "<td class='tbl1'><select id='chars' name='chars' class='textbox'".($_GET['stype']!="all"?(in_array("chars", $form_elements[$_GET['stype']]['disabled'])?" disabled='disabled'":""):"").">\n";
echo "<option value='50'".($_GET['chars']==50?" selected='selected'":"").">50</option>\n";
echo "<option value='100'".($_GET['chars']==100?" selected='selected'":"").">100</option>\n";
echo "<option value='150'".($_GET['chars']==150?" selected='selected'":"").">150</option>\n";
echo "<option value='200'".($_GET['chars']==200?" selected='selected'":"").">200</option>\n";
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
	if ($_GET['chars'] != 0) {
		$text = nl2br(stripslashes(substr($text, 0, $_GET['chars'])."..."));
	} else {
		$text = nl2br(stripslashes($text));
	}
	return $text;
}

function search_stringscount($text) {
	global $swords;
	$count = 0; $c_swords = count($swords); //sizeof($swords)
	for ($i = 0; $i < $c_swords; $i++) {
		$count += substr_count(strtolower($text), strtolower($swords[$i]));
	}  
	return $count;
}

function search_querylike($field) {
	global $swords;
	$querylike = ""; $c_swords = count($swords); //sizeof($swords)
	for ($i = 0; $i < $c_swords; $i++) {
		$querylike .= $field." LIKE '%".$swords[$i]."%'".($i < $c_swords - 1 ? " ".$_GET['method']." " : "");
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
		$memory_exhaused = false;
	} else {
		$memory_exhaused = true;
	}
	return $memory_exhaused;
}

function search_navigation($rows) {
	global $site_search_count, $composevars;
	$site_search_count += $rows;
	$navigation_result = "<div align='center' style='margin-top:5px;'>\n".makePageNav($_GET['rowstart'], 10, ($site_search_count > 100 || search_globalarray("") ? 100 : $site_search_count), 3, FUSION_SELF."?stype=".$_GET['stype']."&amp;stext=".urlencode($_GET['stext'])."&amp;".$composevars)."\n</div>\n";
	return $navigation_result;
}

$composevars = "method=".$_GET['method']."&amp;datelimit=".$_GET['datelimit']."&amp;fields=".$_GET['fields']."&amp;sort=".$_GET['sort']."&amp;order=".$_GET['order']."&amp;chars=".$_GET['chars']."&amp;forum_id=".$_GET['forum_id']."&amp;";

$memory_limit = str_replace("m", "", strtolower(ini_get("memory_limit"))) * 1024 * 1024;
$memory_limit = !isnum($memory_limit) ? 8 * 1024 * 1024 : $memory_limit < 8 * 1024 * 1024 ? 8 * 1024 * 1024 : $memory_limit;
$memory_limit = $memory_limit - ceil($memory_limit / 4);
$global_string_count = 0;
$site_search_count = 0;
$search_result_array = array();
$navigation_result = "";
$items_count = "";

$_GET['stext'] = urldecode($_GET['stext']);
if ($_GET['stext'] != "" && strlen($_GET['stext']) >= 3) {
	add_to_title($locale['global_201'].$locale['408']);
	opentable($locale['408']);
	$fswords = explode(" ", $_GET['stext']);
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
	if ($c_swords == 0) { redirect(FUSION_SELF); } //sizeof($swords)
	$higlight = ""; $i = 1; 
	foreach ($swords as $hlight) {
		$higlight .= "'".$hlight."'";
		$higlight .= ($i < $c_swords ? "," : "");
		$i++;
	}
	add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.highlight.js'></script>");
	$highlight_js  = "<script type='text/javascript'>";
	$highlight_js .= "/*<![CDATA[*/";
	$highlight_js .= "jQuery(document).ready(function(){";
	$highlight_js .=    "jQuery('.search_result').highlight([".$higlight."],{wordsOnly:true});";
	$highlight_js .=    "jQuery('.highlight').css({backgroundColor:'#FFFF88'});"; //better via theme or settings
	$highlight_js .= "});";
	$highlight_js .= "/*]]>*/";
	$highlight_js .= "</script>";
	add_to_footer($highlight_js);

	if ($_GET['stype'] == "all") {
		$dh = opendir(INCLUDES."search");
		while (false !== ($entry=readdir($dh))) {
			if ($entry != "." && $entry != ".." && preg_match("/include.php/i", $entry)) {
				include (INCLUDES."search/".$entry);
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
			$txt .= $iwords[$i].($i < $c_iwords - 1 ? ", " : "");
		}
		echo "<div style='text-align:center;font-weight:bold'>".sprintf($locale['502'], $txt)."</div><br />";
	}

	if ($_GET['stype'] == "all") {
		$navigation_result = search_navigation(0);
		echo "<div class='quote'>".$items_count."<hr />".THEME_BULLET."&nbsp;<strong>".(($site_search_count>100 || search_globalarray(""))?sprintf($locale['530'], $site_search_count):$site_search_count." ".$locale['510'])."</strong></div><hr />";
	} else {
		echo $items_count."<hr />";
		echo (($site_search_count>100 || search_globalarray("")) ? "<strong>".sprintf($locale['530'], $site_search_count)."</strong><hr />" : "");
	}

	$c_search_result_array = count($search_result_array);
	if ($_GET['stype'] == "all") {
		$from = $_GET['rowstart'];
		$to = ($c_search_result_array - ($_GET['rowstart'] + 10)) <= 0 ? $c_search_result_array : $_GET['rowstart'] + 10;
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

} elseif (isset($_GET['stext'])) {
	add_to_title($locale['global_201'].$locale['408']);
	opentable($locale['408']);
	echo $locale['501'];
	closetable();
}

require_once THEMES."templates/footer.php";
?>