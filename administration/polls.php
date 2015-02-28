<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: polls.php
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

if (!checkrights("PO") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/polls.php";

if (isset($_GET['poll_id']) && !isnum($_GET['poll_id'])) { redirect(FUSION_SELF); }

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['410'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['411'];
	} elseif ($_GET['status'] == "del") {
		$message = $locale['412'];
	}
	if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}

if (isset($_POST['save'])) {
	$poll_title = trim(stripinput($_POST['poll_title']));
	$poll_option = array();
	foreach($_POST['poll_option'] as $key => $value) {
		$poll_option[$key] = trim(stripinput($_POST['poll_option'][$key]));
	}
	if (isset($_GET['poll_id']) && isnum($_GET['poll_id'])) {
		if ($poll_title && count($poll_option)) {
			$ended = (isset($_POST['close']) ? time() : 0);
			$values = "";
			for ($i = 0; $i < count($poll_option); $i++) {
				$values .= ", poll_opt_".$i."='".$poll_option[$i]."'";
			}
			$result = dbquery("UPDATE ".DB_POLLS." SET poll_title='".$poll_title."' ".$values.", poll_ended='".$ended."' WHERE poll_id='".$_GET['poll_id']."'");
			redirect(FUSION_SELF.$aidlink."&amp;status=su");
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		if ($poll_title && count($poll_option)) {
			$values = "";
			for ($i = 0; $i < 10; $i++) {
				$values .= ", '".(isset($poll_option[$i]) ? $poll_option[$i] : "")."'";
			}
			$result = dbquery("UPDATE ".DB_POLLS." SET poll_ended='".time()."' WHERE poll_ended='0'");
			$result = dbquery("INSERT INTO ".DB_POLLS." (poll_title, poll_opt_0, poll_opt_1, poll_opt_2, poll_opt_3, poll_opt_4, poll_opt_5, poll_opt_6, poll_opt_7, poll_opt_8, poll_opt_9, poll_started, poll_ended) VALUES ('".$poll_title."' ".$values.", '".time()."', '0')");
			redirect(FUSION_SELF.$aidlink."&amp;status=sn");
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
} else if (isset($_POST['delete']) && (isset($_POST['poll_id']) && isnum($_POST['poll_id']))) {
	$result = dbcount("(poll_id)", DB_POLLS, "poll_id='".$_POST['poll_id']."'");
	if (!empty($result)) $result = dbquery("DELETE FROM ".DB_POLLS." WHERE poll_id='".$_POST['poll_id']."'");
	redirect(FUSION_SELF.$aidlink."&amp;status=del");
} else {
	if (isset($_POST['preview'])) {
		$poll = ""; $i = 0;
		$poll_title = stripinput($_POST['poll_title']);
		while ($i < count($_POST['poll_option'])) {
			$poll_option[$i] = trim(stripinput($_POST['poll_option'][$i]));
			if (!$poll_option[$i]) { $poll_option[$i] = $locale['439']; }
			$poll .= "<label><input type='radio' name='option[]' /> ".$poll_option[$i]."</label><br /><br />\n";
			$i++;
		}
		$opt_count = (isset($_POST['opt_count']) && $_POST['opt_count'] != 10 ? count($poll_option) : $_POST['opt_count']);
		if ($poll_title) {
			opentable($locale['403']);
			echo "<table cellpadding='0' cellspacing='0' width='280' class='center'>\n<tr>\n";
			echo "<td class='tbl'><strong>".$poll_title."</strong><br /><br />\n".$poll."</td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td align='center' class='tbl'><input type='button' name='blank' value='".$locale['430']."' class='button' /></td>\n";
			echo "</tr>\n</table>\n";
			closetable();
		}
	}
	$editlist = "";
	$result = dbquery("SELECT poll_id, poll_title FROM ".DB_POLLS." ORDER BY poll_id DESC");
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			$editlist .= "<option value='".$data['poll_id']."'>".$data['poll_title']."</option>\n";
		}
		opentable($locale['402']);
		echo "<div style='text-align:center'>\n<form name='editform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
		echo "<select name='poll_id' class='textbox' style='width:200px;'>\n".$editlist."</select>\n";
		echo "<input type='submit' name='edit' value='".$locale['421']."' class='button' />\n";
		echo "<input type='submit' name='delete' value='".$locale['422']."' class='button' />\n";
		echo "</form>\n</div>\n";
		closetable();
	}
	if (isset($_POST['edit']) && (isset($_POST['poll_id']) && isnum($_POST['poll_id']))) {
		$_GET['poll_id'] = $_POST['poll_id'];
		$data = dbarray(dbquery("SELECT poll_title, poll_opt_0, poll_opt_1, poll_opt_2, poll_opt_3, poll_opt_4, poll_opt_5, poll_opt_6, poll_opt_7, poll_opt_8, poll_opt_9, poll_started, poll_ended FROM ".DB_POLLS." WHERE poll_id='".$_POST['poll_id']."'"));
		$poll_title = $data['poll_title'];
		$poll_option = array();
		for ($i = 0; $i <= 9; $i++) {
			if ($data["poll_opt_".$i]) { $poll_option[$i] = $data["poll_opt_".$i]; }
		}
		$opt_count = count($poll_option);
		$poll_started = $data['poll_started'];
		$_GET['poll_ended'] = $data['poll_ended'];
	}
	if (isset($_POST['addoption'])) {
		$poll_title = stripinput($_POST['poll_title']);
		if (isset($_POST['poll_option']) && is_array($_POST['poll_option'])) {
			foreach($_POST['poll_option'] as $key => $value) {
				$poll_option[$key] = stripinput($_POST['poll_option'][$key]);
			}
			$opt_count = ($_POST['opt_count'] != 10 ? count($poll_option) + 1 : $_POST['opt_count']);
		} else {
			$poll_option[0] = "";
			$opt_count = 1;
		}
	}
	$i = 0; $opt = 1;
	$poll_title = isset($poll_title) ? $poll_title : "";
	$opt_count = isset($opt_count) ? $opt_count : 2;
	if (isset($poll_id)) $poll_ended = isset($poll_ended) ? $poll_ended : 0;
	opentable((isset($_GET['poll_id']) ? $locale['401'] : $locale['400']));
	echo "<form name='pollform' method='post' action='".FUSION_SELF.$aidlink.(isset($_GET['poll_id']) ? "&amp;poll_id=".$_GET['poll_id']."&amp;poll_ended=".$_GET['poll_ended'] : "")."'>\n";
	echo "<table cellpadding='0' cellspacing='0' width='280' class='center'>\n<tr>\n";
	echo "<td width='80' class='tbl'>".$locale['431']."</td>\n";
	echo "<td class='tbl'><input type='text' name='poll_title' value='".$poll_title."' class='textbox' style='width:200px' /></td>\n";
	echo "</tr>\n";
	while ($i != $opt_count) {
		$poll_opt = isset($poll_option[$i]) ? $poll_option[$i] : "";
		echo "<tr>\n<td width='80' class='tbl'>".$locale['432']."$opt</td>\n";
		echo "<td class='tbl'><input type='text' name='poll_option[".$i."]' value='".$poll_opt."' class='textbox' style='width:200px' /></td>\n</tr>\n";
		$i++; $opt++;
	}
	echo "</table>\n";
	echo "<table cellpadding='0' cellspacing='0' width='280' class='center'>\n<tr>\n";
	echo "<td align='center' class='tbl'><br />\n";
	if (isset($_GET['poll_id']) && !$_GET['poll_ended']) {
		echo "<input type='checkbox' name='close' value='yes' />".$locale['433']."<br /><br />\n";
	}
	if (!isset($_GET['poll_id']) || (isset($_GET['poll_id']) && !$_GET['poll_ended'])) {
		echo "<input type='hidden' name='opt_count' value='".$opt_count."' />\n";
		echo "<input type='submit' name='addoption' value='".$locale['436']."' class='button' />\n";
		echo "<input type='submit' name='preview' value='".$locale['437']."' class='button' />\n";
		echo "<input type='submit' name='save' value='".$locale['438']."' class='button' />\n";
	} else {
		echo $locale['434'].showdate("shortdate", $poll_started)."<br />\n";
		echo $locale['435'].showdate("shortdate", $_GET['poll_ended'])."<br />\n";
	}
	echo "</td>\n</tr>\n</table>\n</form>\n";
	closetable();
}

require_once THEMES."templates/footer.php";
?>
