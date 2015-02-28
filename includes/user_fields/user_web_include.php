<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_web_include.php
| Author: Digitanium
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

// Display user field input
if ($profile_method == "input") {
	$user_web = isset($user_data['user_web']) ? $user_data['user_web'] : "";
	if ($this->isError()) { $user_web = isset($_POST['user_web']) ? stripinput($_POST['user_web']) : $user_web; }

	echo "<tr>\n";
	echo "<td class='tbl".$this->getErrorClass("user_web")."'><label for='user_web'>".$locale['uf_web'].$required."</label></td>\n";
	echo "<td class='tbl".$this->getErrorClass("user_web")."'>";
	echo "<input type='text' id='user_web' name='user_web' value='".$user_web."' maxlength='50' class='textbox' style='width:200px;' />";
	echo "</td>\n</tr>\n";

	if ($required) { $this->setRequiredJavaScript("user_web", $locale['uf_web_error']); }

// Display in profile
} elseif ($profile_method == "display") {
	if ($user_data['user_web']) {
		echo "<tr>\n";
		echo "<td class='tbl1'>".$locale['uf_web']."</td>\n";
		echo "<td align='right' class='tbl1'>";
		echo "<a href='".$user_data['user_web']."' title='".$user_data['user_web']."' target='_blank'>".$locale['uf_web_001']."</a>\n";
		echo "</td>\n</tr>\n";
	}

// Insert and update
} elseif ($profile_method == "validate_insert"  || $profile_method == "validate_update") {
	// Get input data
	if (isset($_POST['user_web']) && ($_POST['user_web'] != "" || $this->_isNotRequired("user_web"))) {
		// Set update or insert user data
		$user_web = stripinput($_POST['user_web']);
		if (!preg_match("#^http(s)?://#i", $user_web) && $user_web != "") {
			$user_web = "http://".$user_web;
		}
		$this->_setDBValue("user_web", $user_web);
	} else {
		$this->_setError("user_web", $locale['uf_web_error'], true);
	}
}
?>