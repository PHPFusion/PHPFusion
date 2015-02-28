<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: robots.php
| Author: MarcusG
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

if (!checkrights("ROB") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/robots.php";

function openFile($file, $mode, $input = "") {
	if ($mode == "READ") {
		if (file_exists($file)) {
			$handle = fopen($file, "rb");
			$output = fread($handle, filesize($file));
			return $output; // output file text
		} else {
			return false; // failed.
		}
	} elseif ($mode == "WRITE") {
		$handle = fopen($file, "wb");
		if (!fwrite($handle, $input)) {
		return false; // failed.
		} else {
		return true; //success.
		}
	} else {
		return false; // failed.
	}
	fclose($handle);
}

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "su") {
		$message = $locale['412'];
	} elseif ($_GET['status'] == "se") {
		$message = $locale['413']."<br />\n";
		switch ($_GET['error']) {
			case 1:
				$message .= "<span class='small'>".$locale['414']."</span>";
			break;
			case 2:
				$message .= "<span class='small'>".$locale['415']."</span>";
			break;
		}
	}
	if ($message) { echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}

if (isset($_POST['save_robots'])) {
	$error = 0;
	$file = BASEDIR."robots.txt";
	$robots_content = $_POST['robots_content'];
	if (!is_writable($file)) {
		$error = 1;
	}
	if ($error == 0) {
		if(openFile($file, "WRITE", stripslash($robots_content))) {
			redirect(FUSION_SELF.$aidlink."&amp;status=su");
		} else {
			redirect(FUSION_SELF.$aidlink."&amp;status=se&amp;error=2");
		}
	} else {
		redirect(FUSION_SELF.$aidlink."&amp;status=se&amp;error=".$error);
	}
}

if (isset($_POST['set_default'])) {
	$error = 0;
	$file = BASEDIR."robots.txt";
	$robots_content = "User-agent: *\n";
	$robots_content .= "Disallow: /administration/\n";
	$robots_content .= "Disallow: /locale/\n";
	$robots_content .= "Disallow: /themes/\n";
	$robots_content .= "Disallow: /print.php\n";
	if (!is_writable($file)) {
		$error = 1;
	}
	if ($error == 0) {
		if(openFile($file, "WRITE", $robots_content)) {
			redirect(FUSION_SELF.$aidlink."&amp;status=su");
		} else {
			redirect(FUSION_SELF.$aidlink."&amp;status=se&amp;error=3");
		}
	} else {
		redirect(FUSION_SELF.$aidlink."&amp;status=se&amp;error=".$error);
	}
}

opentable($locale['400']);
if (file_exists(BASEDIR."robots.txt")) {
	$file = BASEDIR."robots.txt";
	echo "<form name='robotsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
	echo "<table class='tbl-border center' cellpadding='1' cellspacing='0' style='width:460px;'>\n";
	echo "<tr>\n";
	echo "<td class='tbl2' style='text-align:center;font-weight:bold;'>".$locale['420']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1' style='text-align:center;'><a href='http://www.robotstxt.org/' target='_blank'>".$locale['421']."</a></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1' style='text-align:center;'>";
	echo "<textarea name='robots_content' class='textbox' rows='20' cols='40' style='width:100%;'>".openFile($file, "READ")."</textarea>";
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1' style='text-align:center;'>";
	echo "<input type='submit' name='save_robots' class='button' value='".$locale['422']."' />\n";
	echo "<input type='submit' name='set_default' class='button' value='".$locale['423']."' onclick=\"return confirm('".$locale['410']."');\" />\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</form>\n";
} else {
	echo "<div class='admin-message'>".$locale['411']."</div>\n";
}
closetable();

require_once THEMES."templates/footer.php";
?>