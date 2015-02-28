<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_birthdate_include.php
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
	if (isset($user_data['user_birthdate']) && $user_data['user_birthdate'] != "0000-00-00") {
		$user_birthdate = $user_data['user_birthdate'];
	} else {
		$user_birthdate = "0-0-0";
	}
	
	$user_birthdate = explode("-", $user_birthdate);
	$user_year = number_format($user_birthdate['0'], 0, ".", "");
	$user_month = number_format($user_birthdate['1'], 0, ".", "");
	$user_day = number_format($user_birthdate['2'], 0, ".", "");

	echo "<tr>\n";
	echo "<td class='tbl".$this->getErrorClass("user_birthdate")."'>";
	echo "<label for='user_day_input'>".$locale['uf_birthdate'].$required." <span class='small2'>(dd/mm/yyyy)</span></label></td>\n";
	echo "<td class='tbl".$this->getErrorClass("user_birthdate")."'>";
	echo "<select id='user_day_input' name='user_day' class='textbox'>\n<option value=''>&nbsp;</option>\n";
	for ($bi = 1; $bi <= 31; $bi++) { echo "<option value='".$bi."'".($user_day == $bi ? " selected='selected'" : "").">".$bi."</option>\n"; }
	echo "</select>\n<select id='user_month_input' name='user_month' class='textbox'>\n<option value=''>&nbsp;</option>\n";
	for ($bi = 1; $bi <= 12; $bi++) { echo "<option value='".$bi."'".($user_month == $bi ? " selected='selected'" : "").">".$bi."</option>\n"; }
	echo "</select>\n<select id='user_year_input' name='user_year' class='textbox'>\n<option value=''>&nbsp;</option>\n";
	for ($bi = date("Y"); $bi > (date("Y") - 99); $bi--) { echo "<option value='".$bi."'".($user_year == $bi ? " selected='selected'" : "").">".$bi."</option>\n"; }
	echo "</select>\n</td>\n";
	echo "</tr>\n";

	if ($required) { 
		$this->setRequiredJavaScript("user_day", $locale['uf_birthdate_error']); 
		$this->setRequiredJavaScript("user_month", $locale['uf_birthdate_error']); 
		$this->setRequiredJavaScript("user_year", $locale['uf_birthdate_error']); 
	}

// Display in profile
} elseif ($profile_method == "display") {
	if ($user_data['user_birthdate'] != "0000-00-00") {
		echo "<tr>\n";
		echo "<td class='tbl1'>".$locale['uf_birthdate']."</td>\n";
		echo "<td align='right' class='tbl1'>";
		$months = explode("|", $locale['months']);
		$user_birthdate = explode("-", $user_data['user_birthdate']);
		echo $months[number_format($user_birthdate['1'])]." ".number_format($user_birthdate['2'])." ".$user_birthdate['0'];
		echo "</td>\n</tr>\n";
	}

// Insert and update
} elseif ($profile_method == "validate_insert" || $profile_method == "validate_update") {
	// Get input data
	$user_month = 0; $user_day = 0; $user_year = 0;
	if (isset($_POST['user_year']) && isnum($_POST['user_year']) && $_POST['user_year'] != 0) {
		$user_year = $_POST['user_year'];
	}
	if (isset($_POST['user_month']) && isnum($_POST['user_month']) && $_POST['user_month'] != 0) {
		$user_month = $_POST['user_month'];
	}
	if (isset($_POST['user_day'])&& isnum($_POST['user_day']) && $_POST['user_day'] != 0 ) {
		$user_day = $_POST['user_day'];
	}
	
	if (($user_month != 0 && $user_day != 0 && $user_year != 0)  || $this->_isNotRequired("user_birthdate")) {
		// Set update or insert user data
		$this->_setDBValue("user_birthdate", $user_year."-".$user_month."-".$user_day);
	} else {
		$this->_setError("user_birthdate", $locale['uf_birthdate_error'], true);	
	}
}
?>