<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: members.php
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
require_once "maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."members.php";

add_to_title($locale['global_200'].$locale['400']);

opentable($locale['400']);
if (iMEMBER) {
	if (!isset($_GET['sortby']) || !ctype_alnum($_GET['sortby'])) { $_GET['sortby'] = "all"; }
	$orderby = ($_GET['sortby'] == "all" ? "" : " AND user_name LIKE '".stripinput($_GET['sortby'])."%'");
	$result = dbquery("SELECT user_id FROM ".DB_USERS." WHERE user_status='0'".$orderby);
	$rows = dbrows($result);
	if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
	if ($rows) {
		$i = 0;
		echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
		echo "<td class='tbl2'><strong>".$locale['401']."</strong></td>\n";
		echo "<td class='tbl2'><strong>".$locale['405']."</strong></td>\n";
		echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['402']."</strong></td>\n";
		echo "</tr>\n";
		$result = dbquery("SELECT user_id, user_name, user_status, user_level, user_groups FROM ".DB_USERS." WHERE user_status='0'".$orderby." ORDER BY user_level DESC, user_name LIMIT ".$_GET['rowstart'].",20");
		while ($data = dbarray($result)) {
			$cell_color = ($i % 2 == 0 ? "tbl1" : "tbl2"); $i++;
			echo "<tr>\n<td class='$cell_color'>\n".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
			$groups = "";
			$user_groups = explode(".", $data['user_groups']);
			$j = 0;
			foreach ($user_groups as $key => $value) {
				if ($value) {
					$groups .= "<a href='profile.php?group_id=".$value."'>".getgroupname($value)."</a>".($j < count($user_groups)-1 ? ", " : "");
				}
				$j++;
			}
			echo "<td class='$cell_color'>\n".($groups ? $groups : ($data['user_level'] == 103 ? $locale['407'] : $locale['406']))."</td>\n";
			echo "<td align='center' width='1%' class='$cell_color' style='white-space:nowrap'>".getuserlevel($data['user_level'])."</td>\n</tr>";
		}
		echo "</table>\n"; 
	} else {
		echo "<div style='text-align:center'><br />\n".$locale['403'].$_GET['sortby']."<br /><br />\n</div>\n";
	}
	$search = array(
		"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R",
		"S","T","U","V","W","X","Y","Z","0","1","2","3","4","5","6","7","8","9"
	);
	echo "<hr />\n<table cellpadding='0' cellspacing='1' class='tbl-border center'>\n<tr>\n";
	echo "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF."?sortby=all'>".$locale['404']."</a></td>";
	for ($i = 0; $i < 36 != ""; $i++) {
		echo "<td align='center' class='tbl1'><div class='small'><a href='".FUSION_SELF."?sortby=".$search[$i]."'>".$search[$i]."</a></div></td>";
		echo ($i == 17 ? "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF."?sortby=all'>".$locale['404']."</a></td>\n</tr>\n<tr>\n" : "\n");
	}
	echo "</tr>\n</table>\n";
} else {
	redirect("index.php");
}
closetable();
if ($rows > 20) { echo "<div align='center' style='margin-top:5px;'>".makepagenav($_GET['rowstart'], 20, $rows, 3, FUSION_SELF."?sortby=".$_GET['sortby']."&amp;")."</div>\n"; }

require_once THEMES."templates/footer.php";
?>
