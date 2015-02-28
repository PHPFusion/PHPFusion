<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_icq_include.php
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
	$user_icq = isset($user_data['user_icq']) ? $user_data['user_icq'] : "";
	if ($this->isError()) { $user_icq = isset($_POST['user_icq']) ? stripinput($_POST['user_icq']) : $user_icq; }

	echo "<tr>\n";
	echo "<td class='tbl".$this->getErrorClass("user_icq")."'><label for='user_icq'>".$locale['uf_icq'].$required."</label></td>\n";
	echo "<td class='tbl".$this->getErrorClass("user_icq")."'>";
	echo "<input type='text' id='user_icq' name='user_icq' value='".$user_icq."' maxlength='16' class='textbox' style='width:200px;' />";
	echo "</td>\n</tr>\n";
	
	if ($required) { $this->setRequiredJavaScript("user_icq", $locale['uf_icq_error']); }
	
// Display in profile
} elseif ($profile_method == "display") {
	if ($user_data['user_icq']) {
		echo "<tr>\n";
		echo "<td class='tbl1'>".$locale['uf_icq']."</td>\n";
		echo "<td align='right' class='tbl1'>".$user_data['user_icq']."</td>\n";
		echo "</tr>\n";
	}
	
// Insert and update
} elseif ($profile_method == "validate_insert"  || $profile_method == "validate_update") {
	// Get input data
	if (isset($_POST['user_icq']) && ($_POST['user_icq'] != "" || $this->_isNotRequired("user_icq"))) {
		if (isnum($_POST['user_icq']) || $_POST['user_icq'] == "") {
			// Set update or insert user data
			$this->_setDBValue("user_icq", $_POST['user_icq']);
		} else {
			$this->_setError("user_icq", $locale['uf_icq_error2']);	
		}
	} else {
		$this->_setError("user_icq", $locale['uf_icq_error'], true);	
	}
}
?>