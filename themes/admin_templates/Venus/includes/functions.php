<?php
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