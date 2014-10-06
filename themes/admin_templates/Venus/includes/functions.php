<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Venus/includes/functions.php
| Author: PHP-Fusion Inc.
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function openside($title = FALSE, $class = FALSE) {
	echo "<div class='panel panel-default $class'>\n";
	echo ($title) ? "<div class='panel-heading'>$title</div>\n" : '';
	echo "<div class='panel-body'>\n";
}
function closeside($title = FALSE) {
	echo "</div>\n";
	echo ($title) ? "<div class='panel-footer'>$title</div>\n" : '';
	echo "</div>\n";
}
function opentable($title) {
	echo "<div class='panel panel-default box-shadow' style='border:none;'>\n<div class='panel-body'>\n";
	echo "<h3 class='m-b-20'>".$title."</h3>\n";
}
function closetable() {
	echo "</div>\n</div>\n";
}
?>