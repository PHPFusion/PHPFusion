<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: panels.php
| Author: Nick Jones (Digitanium)
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

if (!checkrights("P") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/panels.php";

add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery-ui.js'></script>");
add_to_head("<link rel='stylesheet' href='".THEMES."templates/panels.css' type='text/css' media='all' />");
add_to_head("<script type='text/javascript'>
$(document).ready(function() {
	$('.pdisabled').fadeTo(0, .5);
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

opentable($locale['400']);
echo "<div id='info'></div>\n";

$side = array("1" => $locale['420'], "2" => $locale['421'], "3" => $locale['425'], "4" => $locale['422']);
$panels = array("1" => array(), "2" => array(), "3" => array(), "4" => array());

$result = dbquery(
	"SELECT panel_id, panel_name, panel_side, panel_order, panel_type, panel_access, panel_status
	FROM ".DB_PANELS."
	ORDER BY panel_side,panel_order"
);
while ($data = dbarray($result)) {
	$panels[$data['panel_side']][] = $data;
}

for ($i = 1; $i <= 4; $i++) {
	$k = 0;
	echo "<div style='width:700px;' class='panels tbl-border center floatfix'><div class='tbl2'>\n";
	echo "<div style='float:left; padding-left:30px;'>";
	echo "<strong>".$side[$i]." [<a href='panel_editor.php".$aidlink."&amp;panel_side=".$i."'>".$locale['438']."</a>]</strong>";
	echo "</div>\n<div style='float:right; width:230px;'><strong>".$locale['406']."</strong></div>\n";
	echo "<div style='float:right; width:150px;'><strong>".$locale['405']."</strong></div>\n";
	echo "<div style='float:right; width:10%;'><strong>".$locale['404']."</strong></div>\n";
	//echo "<div style='float:right; width:10%;'><strong>".$locale['403']."</strong></div>\n";
	//echo "<div style='float:right; width:10%;'><strong>".$locale['402']."</strong></div>\n";
	echo "<div style='clear:both;'></div>\n</div>\n";

	echo "<ul id='panel-side".$i."' data-side='".$i."' style='list-style: none;' class='panels-list connected'>\n";

	foreach($panels[$i] as $data) {
		$row_color = ($k % 2 == 0 ? "tbl1" : "tbl2");
		$type = $data['panel_type'] == "file" ? $locale['423'] : $locale['424'];

		echo "<li id='listItem_".$data['panel_id']."' class='".$row_color.($data['panel_status'] == 0 ? " pdisabled" : "")."'>\n";
		echo "<div style='float:left; width:30px;'><img src='".IMAGES."arrow.png' alt='move' class='handle' /></div>\n";
		echo "<div style='float:left;'>".$data['panel_name']."</div>\n";
		echo "<div style='float:right; width:230px;'>";
		echo "[<a href='panel_editor.php".$aidlink."&amp;action=edit&amp;panel_id=".$data['panel_id']."&amp;panel_side=".$i."'>".$locale['434']."</a>]\n";
		if ($data['panel_status'] == 0) {
			echo "[<a href='".FUSION_SELF.$aidlink."&amp;action=setstatus&amp;status=1&amp;panel_id=".$data['panel_id']."'>".$locale['435']."</a>]\n";
		} else {
			echo "[<a href='".FUSION_SELF.$aidlink."&amp;action=setstatus&amp;status=0&amp;panel_id=".$data['panel_id']."'>".$locale['436']."</a>]\n";
		}
		echo "[<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;panel_id=".$data['panel_id']."&amp;panel_side=".$data['panel_side']."' onclick=\"return confirm('".$locale['440']."');\">".$locale['437']."</a>]\n";
		echo "</div>\n";
		echo "<div style='float:right; width:150px;'>".getgroupname($data['panel_access'])."</div>\n";
		echo "<div style='float:right; width:10%;'>".$type."</div>\n";
		//echo "<div class='num' style='float:right; width:10%;'>".$data['panel_order']."</div>\n";
		//echo "<div class='pside' style='float:right; width:10%;'>".$side[$i]."</div>\n";
		echo "<div style='clear:both;'></div>\n";
		echo "</li>\n";
		$k++;
	}

	echo "</ul>\n</div>\n";
	echo "<div style='margin:5px;'></div>\n";
}
closetable();

require_once THEMES."templates/footer.php";
?>