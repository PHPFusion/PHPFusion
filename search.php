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

$locale = fusion_get_locale("", LOCALE.LOCALESET."search.php");

add_to_title($locale['global_202']);
$_POST['rowstart'] = 0;
if (isset($_GET['rowstart']) && isnum($_GET['rowstart'])) {
    $_POST['rowstart'] = $_GET['rowstart'];
}
if (isset($_POST['stext'])) {
    if (is_array($_POST['stext'])) {
        redirect(FUSION_SELF);
    } else {
        $_POST['stext'] = urlencode(stripinput($_POST['stext']));
    }
} else {
    $_POST['stext'] = (isset($_GET['stext']) && $_GET['stext']) ? urlencode(stripinput($_GET['stext'])) : '';
}

if (isset($_POST['method'])) {
    $_POST['method'] = ($_POST['method'] == "OR" || $_POST['method'] == "AND") ? $_POST['method'] : "OR";
} else {
    $_POST['method'] = (isset($_GET['method']) && ($_GET['method'] == "OR" || $_GET['method'] == "AND")) ? $_GET['method'] : 'OR';
}

if (isset($_POST['datelimit'])) {
    $_POST['datelimit'] = isnum($_POST['datelimit']) ? $_POST['datelimit'] : 0;
} else {
    $_POST['datelimit'] = (isset($_GET['datelimit']) && isnum($_GET['datelimit'])) ? $_GET['datelimit'] : 0;
}

if (isset($_POST['fields'])) {
    $_POST['fields'] = isnum($_POST['fields']) ? $_POST['fields'] : 2;
} else {
    $_POST['fields'] = (isset($_GET['fields']) && isnum($_GET['fields'])) ? $_GET['fields'] : 2;
}

if (isset($_POST['sort'])) {
    $_POST['sort'] = in_array($_POST['sort'], array("datestamp", "subject", "author")) ? $_POST['sort'] : "datestamp";
} else {
    $_POST['sort'] = (isset($_GET['sort']) && in_array($_GET['sort'], array(
            'datestamp',
            'subject',
            'author'
        ))) ? $_GET['sort'] : "datestamp";
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

// Format string stype
if (isset($_GET['stype']) || isset($_POST['stype']) && in_array(isset($_GET['stype']), $available)) {
    if (isset($_GET['stype']) && in_array($_GET['stype'], $available) || isset($_POST['stype']) && in_array($_POST['stype'], $available)) {
        $_GET['stype'] = (isset($_POST['stype']) ? lcfirst($_POST['stype']) : (isset($_GET['stype']) ? lcfirst($_GET['stype']) : lcfirst(str_replace(".php", "", fusion_get_settings('default_search')))));
    } else {
        $_GET['stype'] = "all";
    }
} else {
    $_GET['stype'] = (isset($_POST['stype']) && in_array($_POST['stype'], $available, TRUE)) ? $_POST['stype'] : lcfirst(str_replace(".php", "", fusion_get_settings('default_search')));
}
$form_elements = array();

$c_available = count($available);
for ($i = 0; $i < $c_available - 1; $i++) {
    include(INCLUDES."search/search_".$available[$i]."_include_button.php");
}
sort($radio_button);


opentable($locale['400']);
// maybe rewrite with jQuery
$search_js = "function display(val) {\nswitch (val) {\n";
foreach ($form_elements as $type => $array1) {
    $search_js .= "case '".$type."':\n";
    foreach ($array1 as $what => $array2) {
        foreach ($array2 as $elements => $value) {
            if ($what == "enabled") {
                $search_js .= "document.getElementById('".$value."').disabled = false;\n";
            } else {
                if ($what == "disabled") {
                    $search_js .= "document.getElementById('".$value."').disabled = true;\n";
                } else {
                    if ($what == "display") {
                        $search_js .= "document.getElementById('".$value."').style.display = 'block';\n";
                    } else {
                        if ($what == "nodisplay") {
                            $search_js .= "document.getElementById('".$value."').style.display = 'none';\n";
                        }
                    }
                }
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
$search_js .= "break;}}";
add_to_footer("<script type='text/javascript'>".jsminify($search_js)."</script>");

echo openform('advanced_search_form', 'post', BASEDIR."search.php");

echo "<div class='row'>\n<div class='col-xs-12 col-sm-6'>\n";
echo form_text('stext', $locale['401'], urldecode($_POST['stext']), array("required" => TRUE));
echo form_button('search', $locale['402'], $locale['402'], array('class' => 'btn-primary m-t-20 m-b-20'));
echo "</div>\n<div class='col-xs-12 col-sm-6 p-t-20'>\n";
echo form_checkbox('method', '', $_POST['method'],
					array(
						"options" => array(
							'OR' => $locale['403'],
							'AND' => $locale['404']
							),
						'type' 			=> 'radio',
                        'reverse_label' => TRUE,
						)
					);
echo "</div>\n</div>\n";

echo "<div class='row'>\n<div class='col-xs-12 col-sm-6'>\n";
echo "<span><strong>".$locale['405']."</strong></span><br/>\n";
echo "<table width='100%' cellpadding='0' cellspacing='0'>\n";
foreach ($radio_button as $key => $value) {
    echo "<tr>\n<td>".$value."</td>\n</tr>\n";
}
echo "<tr>\n";
echo "<td>\n";
echo form_checkbox('stype', $locale['407'], $_GET['stype'],
					array(
						'type' 			=> 'radio',
						'value' 		=> 'all',
						'onclick' => 'display(this.value)',
                        'reverse_label' => TRUE,
						)
					);

echo "</td>\n</tr>\n</table>\n";
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

$disabled_status = FALSE;
if (isset($form_elements[$_GET['stype']]['disabled'])) {
    $disabled_status = !empty($form_elements[$_GET['stype']]['disabled']) ? TRUE : FALSE;
    if ($_GET['stype'] != 'all') {
        $disabled_status = in_array("datelimit", $form_elements[$_GET['stype']]['disabled']) ? TRUE : FALSE;
    }
}
if ($_GET['stype'] == "all") {
    $disabled_status = TRUE;
}

echo form_select('datelimit', '', $_POST['datelimit'],
                 array(
                 	'inner_width' => '150px',
                    'options' => $date_opts,
                    'deactivate' => $disabled_status
                 ));
echo "</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1'>&nbsp;</td>\n";

echo "<td class='tbl1'>";
echo form_checkbox('fields', $locale['430'], $_POST['fields'],
					array(
						'type' 			=> 'radio',
						'value' 		=> '2',
                        'reverse_label' => TRUE,
            			'input_id' => 'fields1',
                    	'deactivate' => ($_GET['stype'] != "all" ? (isset($form_elements[$_GET['stype']]) && in_array("fields1",$form_elements[$_GET['stype']]['disabled']) ? TRUE : FALSE) : FALSE)
						)
					);
echo form_checkbox('fields', $locale['431'], $_POST['fields'],
					array(
						'type' 			=> 'radio',
						'value' 		=> '1',
                        'reverse_label' => TRUE,
            			'input_id' => 'fields2',
                    	'deactivate' => ($_GET['stype'] != "all" ? (isset($form_elements[$_GET['stype']]) && in_array("fields2",$form_elements[$_GET['stype']]['disabled']) ? TRUE : FALSE) : FALSE)
						)
					);
echo form_checkbox('fields', $locale['432'], $_POST['fields'],
					array(
						'type' 			=> 'radio',
						'value' 		=> '0',
                        'reverse_label' => TRUE,
            			'input_id' => 'fields3',
                    	'deactivate' => ($_GET['stype'] != "all" ? (isset($form_elements[$_GET['stype']]) && in_array("fields3",$form_elements[$_GET['stype']]['disabled']) ? TRUE : FALSE) : FALSE)
						)
					);

echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl1'>".$locale['440']."&nbsp;</td>\n";
echo "<td class='tbl1'>\n";
$sort_opts = array(
    'datestamp' => $locale['441'],
    'subject' => $locale['442'],
    'author' => $locale['443']
);
echo form_select('sort', '', $_POST['sort'], array(
					'inner_width' => '150px',
					'options' => $sort_opts,
				    'deactivate' => ($_GET['stype'] != "all" ? (isset($form_elements[$_GET['stype']]) && in_array("sort", $form_elements[$_GET['stype']]['disabled']) ? TRUE : FALSE) : FALSE)
));
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl1'>&nbsp;</td>\n";
echo "<td class='tbl1'>";
echo form_checkbox('order', $locale['450'], $_POST['order'],
					array(
						'type' 			=> 'radio',
						'value' 		=> '0',
                        'reverse_label' => TRUE,
            			'input_id' => 'order1',
                    	'deactivate' => ($_GET['stype'] != "all" ? (isset($form_elements[$_GET['stype']]) && in_array("order1", $form_elements[$_GET['stype']]['disabled']) ? TRUE : FALSE) : FALSE)
						)
					);

echo form_checkbox('order', $locale['451'], $_POST['order'],
					array(
						'type' 			=> 'radio',
						'value' 		=> '1',
                        'reverse_label' => TRUE,
            			'input_id' => 'order2',
                    	'deactivate' => ($_GET['stype'] != "all" ? (isset($form_elements[$_GET['stype']]) && in_array("order2", $form_elements[$_GET['stype']]['disabled']) ? TRUE : FALSE) : FALSE)
						)
					);
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl1'>".$locale['460']."</td>\n";
echo "<td class='tbl1'>\n";

$char_opts = array(
    '50' => '50',
    '100' => '100',
    '150' => '150',
    '200' => '200'
);
echo form_select('chars', '', $_POST['chars'], array(
					'inner_width' => '150px',
					'options' => $char_opts,
					'deactivate' => ($_GET['stype'] != "all" ? (isset($form_elements[$_GET['stype']]) && in_array("chars", $form_elements[$_GET['stype']]['disabled']) ? TRUE : FALSE) : FALSE)
					)
				);
echo "</td>\n</tr>\n</tbody>\n</table>\n";
echo "</div>\n</div>\n";
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
    $c_swords = count($swords); //count($swords)
    for ($i = 0; $i < $c_swords; $i++) {
        $count += substr_count(strtolower($text), strtolower($swords[$i]));
    }

    return $count;
}

function search_querylike_safe($field, $swords_keys_for_query, $swords_count, $fields_count, $field_index) {
    $querylike = "";
    $last_sword_index = $swords_count - 1;
    for ($i = 0; $i < $swords_count; $i++) {
        $sword_var = $swords_keys_for_query[$i*$fields_count + $field_index];
        $querylike .= $field ." LIKE {$sword_var}" .($i < $last_sword_index ? " ".$_POST['method']." " : "");
    }

    return $querylike;
}

function search_querylike($field) {
    global $swords;
    $querylike = "";
    $c_swords = count($swords); //count($swords)
    for ($i = 0; $i < $c_swords; $i++) {
        $querylike .= $field." LIKE '%".$swords[$i]."%'".($i < $c_swords - 1 ? " ".$_POST['method']." " : "");
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
    $navigation_result = "<div align='center' style='margin-top:5px;'>\n".makePageNav($_POST['rowstart'], 10,
            ($site_search_count > 100 || search_globalarray("") ? 100 : $site_search_count), 3,
                                                                                      BASEDIR."search.php?stype=".$_GET['stype']."&amp;stext=".urlencode($_POST['stext'])."&amp;".$composevars)."\n</div>\n";

    return $navigation_result;
}

$composevars = "method=".$_POST['method']."&amp;datelimit=".$_POST['datelimit']."&amp;fields=".$_POST['fields']."&amp;sort=".$_POST['sort']."&amp;order=".$_POST['order']."&amp;chars=".$_POST['chars']."&amp;forum_id=".$_POST['forum_id']."&amp;";
$memory_limit = str_replace("m", "", strtolower(ini_get("memory_limit"))) * 1024 * 1024;
$memory_limit = !isnum($memory_limit) ? 8 * 1024 * 1024 : $memory_limit < 8 * 1024 * 1024 ? 8 * 1024 * 1024 : $memory_limit;
$memory_limit = $memory_limit - ceil($memory_limit / 4);
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
    $c_fswords = count($fswords); //count($fswords)
    // array for dbquery call with parameters
    $swords_for_query = array();
    $fields_count = $_POST['fields'] + 1;
    for ($i = 0, $k = 0; $i < $c_fswords; $i++) {
        if (strlen($fswords[$i]) >= 3) {
            $swords[] = $fswords[$i];
            for ($j = 0; $j < $fields_count; $j++) {
                $swords_for_query[':sword'.$k.$j] = '%'.$fswords[$i].'%';
            }
            $k++;
        } else {
            $iwords[] = $fswords[$i];
        }
    }
    unset($fswords);
    $c_swords = count($swords);
    if ($c_swords == 0) {
        redirect(FUSION_SELF);
    } //count($swords)
    $swords_keys_for_query = array_keys($swords_for_query);
    $higlight = "";
    $i = 1;
    foreach ($swords as $hlight) {
        $higlight .= "'".$hlight."'";
        $higlight .= ($i < $c_swords ? "," : "");
        $i++;
    }
    add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.highlight.js'></script>");
    add_to_jquery("
        $('.search_result').highlight([".$higlight."],{wordsOnly:true});
        $('.highlight').css({backgroundColor:'#FFFF88'});
    ");

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
            $txt .= $iwords[$i].($i < $c_iwords - 1 ? ", " : "");
        }
        echo "<div class='well m-t-10' style='text-align:center;font-weight:bold'>".sprintf($locale['502'], $txt)."</div><br />";
    }
    if ($_GET['stype'] == "all") {
        $navigation_result = search_navigation(0);
        echo "<div class='quote'>".$items_count."<hr />".THEME_BULLET."&nbsp;<strong>".(($site_search_count > 100 || search_globalarray("")) ? sprintf($locale['530'],
                                                                                                                                                       $site_search_count) : $site_search_count." ".$locale['510'])."</strong></div><hr />";
    } else {
        echo $items_count."<hr />";
        echo(($site_search_count > 100 || search_globalarray("")) ? "<strong>".sprintf($locale['530'], $site_search_count)."</strong><hr />" : "");
    }
    $c_search_result_array = count($search_result_array);
    if ($_GET['stype'] == "all") {
        $from = $_POST['rowstart'];
        $to = ($c_search_result_array - ($_POST['rowstart'] + 10)) <= 0 ? $c_search_result_array : $_POST['rowstart'] + 10;
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
