<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: panels.php
| Author: Nick Jones (Digitanium)
| Author: Robert Gaudyn (Wooya)
| Author: Joakim Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
if (!checkrights("P") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/panels.php";
add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery-ui.js'></script>");
add_to_head("<script type='text/javascript'>
    $(document).ready(function() {
//	$('.pdisabled').fadeTo(0, .5);
	$('.panels-list').sortable({
		handle : '.handle',
		placeholder: 'state-highlight',
		connectWith: '.connected',
		scroll: true,
		axis: 'y',
		update: function () {
			var ul = $(this),
				order = ul.sortable('serialize'),
				i = 0;
			$('#info').load('panels_updater.php".$aidlink."&'+order);
			ul.find('.num').each(function(i) {
				$(this).text(i+1);
			});
			ul.find('li').removeClass('tbl2').removeClass('tbl1');
			ul.find('li:odd').addClass('tbl2');
			ul.find('li:even').addClass('tbl1');
			window.setTimeout('closeDiv();',2500);
		},
		receive: function () {
			var ul = $(this),
				order = ul.sortable('serialize'),
				pdata = ul.attr('data-side');
				if (pdata == 1) { var psidetext = '".$locale['420']."'; }
				if (pdata == 2) { var psidetext = '".$locale['421']."'; }
				if (pdata == 3) { var psidetext = '".$locale['425']."'; }
				if (pdata == 4) { var psidetext = '".$locale['422']."'; }
			ul.find('.pside').each(function() {
				$(this).text(psidetext);
			});
			$('#info').load('panels_updater.php".$aidlink."&panel_side='+pdata+'&'+order);
		}
	});
    });
    </script>");
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['panel_id']) && isnum($_GET['panel_id']))) {
	$data = dbarray(dbquery("SELECT panel_side, panel_order FROM ".DB_PANELS." WHERE panel_id='".$_GET['panel_id']."'"));
	$result = dbquery("DELETE FROM ".DB_PANELS." WHERE panel_id='".$_GET['panel_id']."'");
	$result = dbquery("UPDATE ".DB_PANELS." SET panel_order=panel_order-1 WHERE panel_side='".$data['panel_side']."' AND panel_order>='".$data['panel_order']."'");
	redirect(FUSION_SELF.$aidlink);
}
if ((isset($_GET['action']) && $_GET['action'] == "setstatus") && (isset($_GET['panel_id']) && isnum($_GET['panel_id']))) {
	$result = dbquery("UPDATE ".DB_PANELS." SET panel_status='".intval($_GET['status'])."' WHERE panel_id='".$_GET['panel_id']."'");
}
opentable($locale['600']);
echo "<div id='info'></div>\n";
function panels_list($panel_id = NULL) {
	$panel_list = "";
	$result = dbquery("SELECT panel_id, panel_filename FROM ".DB_PANELS." ORDER BY panel_id");
	while ($data = dbarray($result)) {
		$panels[] = $data['panel_filename'];
	}
	$temp = opendir(INFUSIONS);
	while ($folder = readdir($temp)) {
		if (!in_array($folder, array(".", "..")) && strstr($folder, "_panel")) {
			if (is_dir(INFUSIONS.$folder)) {
				if (!in_array($folder, $panels)) {
					$panel_list[] = ucwords(str_replace('_', ' ', $folder));
				}
			}
		}
	}
	closedir($temp);
	if ($panel_list > 0) {
		if (count($panel_list)) sort($panel_list);
		if ($panel_id != NULL) {
			$panel_name = $panel_list[$panel_id];
			return $panel_name;
		} else {
			return $panel_list;
		}
	}
}

function display_header($side) {
	global $locale, $aidlink;
	if ($side == 1) {
		$type = $locale['420'];
	} elseif ($side == 2) {
		$type = $locale['421'];
	} elseif ($side == 3) {
		$type = $locale['425'];
	} elseif ($side == 4) {
		$type = $locale['422'];
	} elseif ($side == 5) {
		$type = $locale['426'];
	} elseif ($side == 6) {
		$type = $locale['427'];
	}
	$panel_header = "<div class='panels panel panel-default clearfix'>\n<div class='panel-heading'>\n"; // .floatfix removed
	$panel_header .= "<strong>$type <a class='pull-right' href='panel_editor.php".$aidlink."&amp;panel_side=".$side."'>".$locale['438']."</a></strong>";
	$panel_header .= "</div>\n";
	$panel_header .= "</div>\n";
	return $panel_header;
}

function display_footer() {
	echo "</div>\n";
}

function show_panels() {
	global $locale, $aidlink, $data, $k;
	$row_color = ($k%2 == 0 ? "tbl1" : "tbl2");
	$type = $data['panel_type'] == "file" ? $locale['423'] : $locale['424'];
	echo "<li id='listItem_".$data['panel_id']."' class='pointer list-group-item ".$row_color.($data['panel_status'] == 0 ? " pdisabled" : "")."'>\n";
	echo "<div class='handle'>\n";
	echo "<img class='pull-left m-r-10' src='".IMAGES."arrow.png' alt='move'/>\n";
	echo "<a class='dropdown-toggle' data-toggle='dropdown'>\n";
	echo "<strong>".$data['panel_name']."</strong> <span class='caret'></span>\n\n";
	echo "</a>\n";
	echo "<ul class='dropdown-menu' role='panel-options'>\n";
	echo "<li style='padding:3px 20px;'>\n<i class='entypo users m-t-5'></i> ".getgroupname($data['panel_access'])."</li>\n";
	echo "<li style='padding:3px 20px;'>\n<i class='entypo window m-t-5'></i> ".$type."</li>\n";
	echo "<li style='padding:3px 20px;'>\n<i class='entypo arrow-combo m-t-5'></i> ".$data['panel_order']."</li>\n";
	echo "<li class='divider'></li>\n";
	echo "<li>\n<a href='panel_editor.php".$aidlink."&amp;action=edit&amp;panel_id=".$data['panel_id']."&amp;panel_side=".$data['panel_side']."'><i class='entypo pencil m-t-5'></i> ".$locale['434']."</a>\n</li>\n";
	if ($data['panel_status'] == 0) {
		echo "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;action=setstatus&amp;status=1&amp;panel_id=".$data['panel_id']."'><i class='entypo check m-t-5'></i> ".$locale['435']."</a>\n</li>\n";
	} else {
		echo "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;action=setstatus&amp;status=0&amp;panel_id=".$data['panel_id']."'><i class='entypo icancel m-t-5'></i> ".$locale['436']."</a>\n</li>\n";
	}
	echo "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;panel_id=".$data['panel_id']."&amp;panel_side=".$data['panel_side']."' onclick=\"return confirm('".$locale['440']."');\"><i class='entypo trash m-t-5'></i>  ".$locale['437']."</a>\n</li>\n";
	echo "</ul>\n";
	echo "</div>\n";
	echo "</li>\n";
	$k++;
}

// START PANEL RENDER
// Removed dependencies on themes's opentable(); especially on drag/drop.
echo "<table width='100%'>\n";
echo "<tr><td>\n";
echo "<div class='row m-0'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
$count = dbcount('(*)', DB_PANELS, 'panel_side=5');
$title = $locale['426'].": ".$count." ".($count == 1 ? $locale['605'] : $locale['604']);
echo display_header(5);
echo "<ul id='panel-side5' data-side='5' style='list-style: none;' class='panels-list connected'>\n";
$k = 0;
$result = dbquery("SELECT * FROM ".DB_PANELS." WHERE panel_side = '5' ORDER BY panel_order ASC");
while ($data = dbarray($result)) {
	show_panels();
}
echo "</ul>\n";
echo "<div style='margin:15px;'></div>\n";
// responsive - might be the trigger..
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3' style='position:inherit'>\n";
$count = dbcount('(*)', DB_PANELS, 'panel_side=1');
$title = $locale['420'].": ".$count." ".($count == 1 ? $locale['605'] : $locale['604']);
echo display_header(1);
echo "<ul id='panel-side1' data-side='1' style='list-style: none;' class='panels-list connected'>\n";
$k = 0;
$result = dbquery("SELECT * FROM ".DB_PANELS." WHERE panel_side = '1' ORDER BY panel_order ASC");
while ($data = dbarray($result)) {
	show_panels();
}
echo "</ul>\n";
echo "<div style='margin:15px;'></div>\n";
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-6 col-lg-6' style='position:inherit;'>\n";
$count = dbcount('(*)', DB_PANELS, 'panel_side=2');
$title = $locale['421'].": ".$count." ".($count == 1 ? $locale['605'] : $locale['604']);
echo display_header(2);
echo "<ul id='panel-side2' data-side='2' style='list-style: none;' class='panels-list connected list-group'>\n";
$k = 0;
$result = dbquery("SELECT * FROM ".DB_PANELS." WHERE panel_side = '2' ORDER BY panel_order ASC");
while ($data = dbarray($result)) {
	show_panels();
}
echo "</ul>\n";
echo "<div style='margin:15px;'></div>\n";
echo "<div class='tbl1' style='height:70px; text-align:center'><b>".$locale['606']."</b></div>";
$count = dbcount('(*)', DB_PANELS, 'panel_side=3');
$title = $locale['425'].": ".$count." ".($count == 1 ? $locale['605'] : $locale['604']);
echo display_header(3);
echo "<ul id='panel-side3' data-side='3' style='list-style: none;' class='panels-list connected'>\n";
$k = 0;
$result = dbquery("SELECT * FROM ".DB_PANELS." WHERE panel_side = '3' ORDER BY panel_order ASC");
while ($data = dbarray($result)) {
	show_panels();
}
echo "</ul>\n";
echo "<div style='margin:15px;'></div>\n";
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3' style='position:inherit'>\n";
$count = dbcount('(*)', DB_PANELS, 'panel_side=4');
$title = $locale['422'].": ".$count." ".($count == 1 ? $locale['605'] : $locale['604']);
echo display_header(4);
echo "<ul id='panel-side4' data-side='4' style='list-style: none;' class='panels-list connected'>\n";
$k = 0;
$result = dbquery("SELECT * FROM ".DB_PANELS." WHERE panel_side = '4' ORDER BY panel_order ASC");
while ($data = dbarray($result)) {
	show_panels();
}
echo "</ul>\n";
echo "<div style='margin:15px;'></div>\n";
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12' style='position:inherit;'>\n";
$count = dbcount('(*)', DB_PANELS, 'panel_side=6');
$title = $locale['427'].": ".$count." ".($count == 1 ? $locale['605'] : $locale['604']);
echo display_header(6);
echo "<ul id='panel-side6' data-side='6' style='list-style: none;' class='panels-list connected'>\n";
$k = 0;
$result = dbquery("SELECT * FROM ".DB_PANELS." WHERE panel_side = '6' ORDER BY panel_order ASC");
while ($data = dbarray($result)) {
	show_panels();
}
echo "</ul>\n";
echo "<div style='margin:5px;'></div>\n";
echo "</div>\n";
echo "</div>\n";
echo "</td></tr></table>\n";
//Unused Panels in the directory
$panel_list = panels_list();
$title = $locale['602'].": ".count($panel_list)." ".(count($panel_list) == 1 ? $locale['605'] : $locale['604']);
opentable($title, "off");
for ($i = 0; $i < count($panel_list); $i++) {
	echo "<div style='float:left;'>".$panel_list[$i]."</div>\n";
	echo "<div style='float:right; width:250px;'>";
	echo "</div>\n";
	echo "<div style='float:right; width:10%;'>File</div>\n";
	echo "<div style='clear:both;'></div>\n";
	$k++;
}
echo "<div style='margin:5px;'></div>\n";
closetable();
require_once THEMES."templates/footer.php";
?>