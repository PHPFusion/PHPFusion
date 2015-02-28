<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_offset_include.php
| Author: Maarten Kossen (mistermartin75)
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
	$user_offset = isset($user_data['user_offset']) ? $user_data['user_offset'] : "0.0";
	if ($this->isError()) { 
		$user_offset = isset($_POST['user_offset']) && is_numeric($_POST['user_offset']) ? $_POST['user_offset'] : $user_offset; 
	}

	echo "<tr>\n";
	echo "<td class='tbl".$this->getErrorClass("user_offset")."'><label for='user_offset_input'>".$locale['uf_offset'].$required."</label></td>\n";
	echo "<td class='tbl".$this->getErrorClass("user_offset")."'><select id='user_offset_input' name='user_offset' class='textbox' style='width:200px;'>\n";
	echo "<option value='-12.0'".($user_offset == "-12.0" ? " selected='selected'" : "").">".$locale['offset_n1200']."</option>\n";
    echo "<option value='-11.0'".($user_offset == "-11.0" ? " selected='selected'" : "").">".$locale['offset_n1100']."</option>\n";
    echo "<option value='-10.0'".($user_offset == "-10.0" ? " selected='selected'" : "").">".$locale['offset_n1000']."</option>\n";
    echo "<option value='-9.0'".($user_offset == "-9.0" ? " selected='selected'" : "").">".$locale['offset_n0900']."</option>\n";
    echo "<option value='-8.0'".($user_offset == "-8.0" ? " selected='selected'" : "").">".$locale['offset_n0800']."</option>\n";
    echo "<option value='-7.0'".($user_offset == "-7.0" ? " selected='selected'" : "").">".$locale['offset_n0700']."</option>\n";
    echo "<option value='-6.0'".($user_offset == "-6.0" ? " selected='selected'" : "").">".$locale['offset_n0600']."</option>\n";
    echo "<option value='-5.0'".($user_offset == "-5.0" ? " selected='selected'" : "").">".$locale['offset_n0500']."</option>\n";
    echo "<option value='-4.0'".($user_offset == "-4.0" ? " selected='selected'" : "").">".$locale['offset_n0400']."</option>\n";
    echo "<option value='-3.5'".($user_offset == "-3.5" ? " selected='selected'" : "").">".$locale['offset_n0350']."</option>\n";
    echo "<option value='-3.0'".($user_offset == "-3.0" ? " selected='selected'" : "").">".$locale['offset_n0300']."</option>\n";
    echo "<option value='-2.0'".($user_offset == "-2.0" ? " selected='selected'" : "").">".$locale['offset_n0200']."</option>\n";
    echo "<option value='-1.0'".($user_offset == "-1.0" ? " selected='selected'" : "").">".$locale['offset_n0100']."</option>\n";
    echo "<option value='0.0'".($user_offset == "0.0" ? " selected='selected'" : "").">".$locale['offset_p0000']."</option>\n";
    echo "<option value='1.0'".($user_offset == "1.0" ? " selected='selected'" : "").">".$locale['offset_p0100']."</option>\n";
    echo "<option value='2.0'".($user_offset == "2.0" ? " selected='selected'" : "").">".$locale['offset_p0200']."</option>\n";
    echo "<option value='3.0'".($user_offset == "3.0" ? " selected='selected'" : "").">".$locale['offset_p0300']."</option>\n";
    echo "<option value='3.5'".($user_offset == "3.5" ? " selected='selected'" : "").">".$locale['offset_p0350']."</option>\n";
    echo "<option value='4.0'".($user_offset == "4.0" ? " selected='selected'" : "").">".$locale['offset_p0400']."</option>\n";
    echo "<option value='4.5'".($user_offset == "4.5" ? " selected='selected'" : "").">".$locale['offset_p0450']."</option>\n";
    echo "<option value='5.0'".($user_offset == "5.0" ? " selected='selected'" : "").">".$locale['offset_p0500']."</option>\n";
    echo "<option value='5.5'".($user_offset == "5.5" ? " selected='selected'" : "").">".$locale['offset_p0550']."</option>\n";
    echo "<option value='5.75'".($user_offset == "5.75" ? " selected='selected'" : "").">".$locale['offset_p0575']."</option>\n";
    echo "<option value='6.0'".($user_offset == "6.0" ? " selected='selected'" : "").">".$locale['offset_p0600']."</option>\n";
    echo "<option value='7.0'".($user_offset == "7.0" ? " selected='selected'" : "").">".$locale['offset_p0700']."</option>\n";
    echo "<option value='8.0'".($user_offset == "8.0" ? " selected='selected'" : "").">".$locale['offset_p0800']."</option>\n";
    echo "<option value='9.0'".($user_offset == "9.0" ? " selected='selected'" : "").">".$locale['offset_p0900']."</option>\n";
    echo "<option value='9.5'".($user_offset == "9.5" ? " selected='selected'" : "").">".$locale['offset_p0950']."</option>\n";
    echo "<option value='10.0'".($user_offset == "10.0" ? " selected='selected'" : "").">".$locale['offset_p1000']."</option>\n";
    echo "<option value='11.0'".($user_offset == "11.0" ? " selected='selected'" : "").">".$locale['offset_p1100']."</option>\n";
    echo "<option value='12.0'".($user_offset == "12.0" ? " selected='selected'" : "").">".$locale['offset_p1200']."</option>\n";
	echo "</select></td>\n";
	echo "</tr>\n";

// Display in profile
} elseif ($profile_method == "display") {

// Insert and update
} elseif ($profile_method == "validate_insert"  || $profile_method == "validate_update") {
	// Get input data
	if (isset($_POST['user_offset']) && is_numeric($_POST['user_offset'])) {
		// Set update or insert user data
		$this->_setDBValue("user_offset", $_POST['user_offset']);
	} else {
		$this->_setError("user_offset", $locale['uf_offset_error'], true);	
	}
}
?>
