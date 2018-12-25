<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: opentable.switch.php
| Author: Chan (Frederick MC Chan)
| Author: Falk (Joakim Falk)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (preg_match('/forum/i', $_SERVER['PHP_SELF'])) {
	// Define panels
	//define('PANELS_OFF', 'TRUE');
	 define('PANEL_RIGHT_OFF', 'TRUE');
	//define('PANEL_LEFT_OFF', 'TRUE');
	
	// Options Costumize opentable
	function opentable($title=false, $title_desc=false)	{
		echo "<div class='panel-atom panel-default m-b-15'>\n";
		echo ($title) ? "<div class='panel-heading'><b>$title</b>".($title_desc ? "<br/>$title_desc" : "")."</div>\n" : '';
		echo "<div class='panel-body'>\n";
	}

} elseif (preg_match('/profile/i', $_SERVER['PHP_SELF'])) {
	
	// Options Costumize opentable
	function opentable($title=false, $title_desc=false)	{
		echo "<div class='panel-atom panel-default'>\n";
		echo ($title) ? "<div class='panel-heading'><b>$title</b>".($title_desc ? "<br/>$title_desc" : "")."</div>\n" : '';
		echo "<div class='panel-body'>\n";
	}
	
} else {
	// For all other pages not defined, use default.
	function opentable($title=false, $title_desc=false)	{
		echo "<div class='panel-atom panel-default m-b-15'>\n";
		echo ($title) ? "<div class='panel-heading'><b>$title</b>".($title_desc ? "<br/>$title_desc" : "")."</div>\n" : '';
		echo "<div class='panel-body'>\n";
	}
}
