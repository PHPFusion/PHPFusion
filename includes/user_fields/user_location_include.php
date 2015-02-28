<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_location_include.php
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
	$user_location = isset($user_data['user_location']) ? stripinput($user_data['user_location']) : "";
	if ($this->isError()) { $user_location = isset($_POST['user_location']) ? stripinput($_POST['user_location']) : $user_location; }

	echo "<tr>\n";
	echo "<td class='tbl".$this->getErrorClass("user_location")."'><label for='user_location'>".$locale['uf_location'].$required."</label></td>\n";
	echo "<td class='tbl".$this->getErrorClass("user_location")."'>";
	echo "<input type='text' id='user_location' name='user_location' value='".$user_location."' maxlength='50' class='textbox' style='width:200px;' />";
	echo "</td>\n</tr>\n";

	if ($required) { $this->setRequiredJavaScript("user_location", $locale['uf_location_error']); }
	
// Display in profile
} elseif ($profile_method == "display") {
	if ($user_data['user_location']) {
		echo "<tr>\n";
		echo "<td class='tbl1'>".$locale['uf_location']."</td>\n";
		echo "<td align='right' class='tbl1'>".$user_data['user_location']."</td>\n";
		echo "</tr>\n";
	}

// Insert and update
} elseif ($profile_method == "validate_insert"  || $profile_method == "validate_update") {
	// Get input data
	if (isset($_POST['user_location']) && ($_POST['user_location'] != "" || $this->_isNotRequired("user_location"))) {
		// Set update or insert user data
		$this->_setDBValue("user_location", stripinput(trim($_POST['user_location'])));
	} else {
		$this->_setError("user_location", $locale['uf_location_error'], true);	
	}
}
?>