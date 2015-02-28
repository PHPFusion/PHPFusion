<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_aim_include.php
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
	$user_aim = isset($user_data['user_aim']) ? $user_data['user_aim'] : "";
	if ($this->isError()) { $user_aim = isset($_POST['user_aim']) ? stripinput($_POST['user_aim']) : $user_aim; }
	
	echo "<tr>\n";
	echo "<td class='tbl".$this->getErrorClass("user_aim")."'><label for='user_aim'>".$locale['uf_aim'].$required."</label></td>\n";
	echo "<td class='tbl".$this->getErrorClass("user_aim")."'>";
	echo "<input type='text' id='user_aim' name='user_aim' value='".$user_aim."' maxlength='16' class='textbox' style='width:200px;' />";
	echo "</td>\n</tr>\n";
	
	if ($required) { $this->setRequiredJavaScript("user_aim", $locale['uf_aim_error']); }
		
// Display in profile
} elseif ($profile_method == "display") {
	if ($user_data['user_aim']) {
		echo "<tr>\n";
		echo "<td class='tbl1'>".$locale['uf_aim']."</td>\n";
		echo "<td align='right' class='tbl1'>".$user_data['user_aim']."</td>\n";
		echo "</tr>\n";
	}
	
// Insert and update
} elseif ($profile_method == "validate_insert"  || $profile_method == "validate_update") {
	// Get input data
	if (isset($_POST['user_aim']) && ($_POST['user_aim'] != "" || $this->_isNotRequired("user_aim"))) {
		// Set update or insert user data
		$this->_setDBValue("user_aim", stripinput(trim($_POST['user_aim'])));
	} else {
		$this->_setError("user_aim", $locale['uf_aim_error'], true);	
	}
}
?>