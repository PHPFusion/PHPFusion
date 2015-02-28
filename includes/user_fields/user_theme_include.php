<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_theme_include.php
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
	if ($settings['userthemes'] == 1 || iADMIN) {
		$user_theme = isset($user_data['user_theme']) ? $user_data['user_theme'] : "";
		if ($this->isError()) { $user_theme = isset($_POST['user_theme']) ? stripinput($_POST['user_theme']) : $user_theme; }

		$theme_files = makefilelist(THEMES, ".|..|templates|.svn", true, "folders");
		array_unshift($theme_files, "Default");
		echo "<tr>\n";
		echo "<td class='tbl".$this->getErrorClass("user_theme")."'><label for='user_theme_input'>".$locale['uf_theme'].$required."</label></td>\n";
		echo "<td class='tbl".$this->getErrorClass("user_theme")."'>";
		echo "<select id='user_theme_input' name='user_theme' class='textbox' style='width:100px;'>\n".makefileopts($theme_files, $user_theme)."</select>";
		echo "</td>\n</tr>\n";
	}

	if ($required) { $this->setRequiredJavaScript("user_theme", $locale['uf_theme_error']); }

// Display in profile
} elseif ($profile_method == "display") {

// Insert and update
} elseif ($profile_method == "validate_insert"  || $profile_method == "validate_update") {
	if ($settings['userthemes'] == 1 || iADMIN) {
		// Get input data
		$input_theme = isset($_POST['user_theme']) ? stripinput($_POST['user_theme']) : "";
		if (theme_exists($input_theme)) {
			// Set update or insert user data
			$this->_setDBValue("user_theme", $input_theme);
			if (isset($this->userData['user_theme'])) {
				if ($input_theme != $this->userData['user_theme']) $this->_themeChanged = true;
			}
		} else {
			$this->_setError("user_theme", $locale['uf_theme_error'], true);	
		}
	}
}
?>