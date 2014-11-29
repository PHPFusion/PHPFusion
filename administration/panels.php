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
add_to_jquery("
$('.panels-list').sortable({
		handle : '.handle',
		placeholder: 'state-highlight',
		connectWith: '.connected',
		scroll: true,
		axis: 'auto',
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
");
add_to_breadcrumbs(array('link'=>ADMIN.'index.php'.$aidlink.'&amp;pagenum=0', 'title'=>'Admin Dashboard'));
add_to_breadcrumbs(array('link'=>FUSION_SELF.$aidlink, 'title'=>$locale['600']));


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

echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
echo panel_reactor(5);
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
echo panel_reactor(1);
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-6 col-lg-6'>\n";
echo panel_reactor(2);
echo "<div class='well text-center strong text-dark'>".$locale['606']."</div>\n";
echo panel_reactor(3);
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
echo panel_reactor(4);
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
echo panel_reactor(6);
echo "</div>\n</div>\n";


//Unused Panels in the directory
$panel_list = panels_list();
$title = $locale['602'].": ".count($panel_list)." ".(count($panel_list) == 1 ? $locale['605'] : $locale['604']);
//opentable($title, "off");
echo "<div class='panel panel-default'>\n";
echo "<div class='panel-heading'>".$title."</div>\n";
echo "<div class='panel-body text-dark'>\n";
$k = 0;
for ($i = 0; $i < count($panel_list); $i++) {
	echo "<div style='float:left;'>".$panel_list[$i]."</div>\n";
	echo "<div style='float:right; width:250px;'>";
	echo "</div>\n";
	echo "<div style='float:right; width:10%;'>File</div>\n";
	echo "<div style='clear:both;'></div>\n";
	$k++;
}
echo "</div>\n</div>\n";
closetable();


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

function panel_reactor($side) {
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

	$k = 0;
	$count = dbcount("('panel_id')", DB_PANELS, "panel_side='".$side."'");
	$title = $type.": ".$count." ".($count == 1 ? $locale['605'] : $locale['604']);
	$result = dbquery("SELECT * FROM ".DB_PANELS." WHERE panel_side = '".$side."' ORDER BY panel_order ASC");
	$html = '';
	$html .= "<div class='m-b-10 text-right'>\n";
	$html .= "<a class='button btn btn-default btn-xs' href='panel_editor.php".$aidlink."&amp;panel_side=".$side."'><i class='entypo plus-circled'></i> ".$locale['438']."</a>\n";
	$html .= "</div>\n";

	$html .= "<div class='panel panel-default'>\n<div class='panel-heading clearfix'>\n";
	$html .= "<strong>$title</strong>";
	$html .= "</div>\n";
	if (dbrows($result)>0) {
		$html .= "<ul id='panel-side".$side."' data-side='".$side."' style='list-style: none;' class='panels-list connected list-group p-10'>\n";
		while ($data = dbarray($result)) {
			$row_color = ($k%2 == 0 ? "tbl1" : "tbl2");
			$type = $data['panel_type'] == "file" ? $locale['423'] : $locale['424'];
			$html .= "<li id='listItem_".$data['panel_id']."' style='border:1px solid #ddd;' class='pointer list-group-item ".$row_color.($data['panel_status'] == 0 ? " pdisabled" : '')."'>\n";

			$html .= "<div class='handle'>\n";
			$html .= "<img class='pull-left m-r-10' src='".IMAGES."arrow.png' alt='move'/>\n";

			$html .= "<div class='overflow-hide'>\n";
			$html .= "<a class='dropdown-toggle' data-toggle='dropdown'>\n";
			$html .= "<strong>".$data['panel_name']."</strong> <span class='caret'></span>\n\n";
			$html .= "</a>\n";

			$html .= "<ul class='dropdown-menu' role='panel-options'>\n";
			$html .= "<li style='padding:3px 20px;'>\n<i class='entypo users m-t-5'></i> ".getgroupname($data['panel_access'])."</li>\n";
			$html .= "<li style='padding:3px 20px;'>\n<i class='entypo window m-t-5'></i> ".$type."</li>\n";
			$html .= "<li style='padding:3px 20px;'>\n<i class='entypo arrow-combo m-t-5'></i> ".$data['panel_order']."</li>\n";
			$html .= "<li class='divider'></li>\n";
			$html .= "<li>\n<a href='panel_editor.php".$aidlink."&amp;action=edit&amp;panel_id=".$data['panel_id']."&amp;panel_side=".$data['panel_side']."'><i class='entypo pencil m-t-5'></i> ".$locale['434']."</a>\n</li>\n";
			if ($data['panel_status'] == 0) {
				$html .= "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;action=setstatus&amp;status=1&amp;panel_id=".$data['panel_id']."'><i class='entypo check m-t-5'></i> ".$locale['435']."</a>\n</li>\n";
			} else {
				$html .= "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;action=setstatus&amp;status=0&amp;panel_id=".$data['panel_id']."'><i class='entypo icancel m-t-5'></i> ".$locale['436']."</a>\n</li>\n";
			}
			$html .= "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;panel_id=".$data['panel_id']."&amp;panel_side=".$data['panel_side']."' onclick=\"return confirm('".$locale['440']."');\"><i class='entypo trash m-t-5'></i>  ".$locale['437']."</a>\n</li>\n";
			$html .= "</ul>\n";
			$html .= "</div>\n";
			$html .= "</div>\n";

			$html .= "</li>\n";
			$k++;
		}
		$html .= "</ul>\n";
	} else {
		$html .= "<div class='panel-body text-center'>No Panels Added</div>\n";
	}
	$html .= "</div>\n";
	return $html;
}

require_once THEMES."templates/footer.php";
?>