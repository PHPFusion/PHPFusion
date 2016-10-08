<?php
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists('render_poll')) {
	function render_poll($info) {
		if (!empty($info['poll_table'])){
			openside($info['poll_tablename']);
			echo "<div class='row m-t-20'>\n";
			foreach ($info['poll_table'] as $key => $inf) {
				echo "<div class='col-xs-12 col-sm-12'>\n";
				echo "<div class='panel panel-default'>\n";
				echo "<div class='panel-heading text-center'>\n";
				echo $inf['poll_title'];
				echo "</div>\n";
				echo "<div class='overflow-hide m-t-20' >\n";
				foreach ($inf['poll_option'] as $key => $inf_opt) {
					echo "<p class='m-l-20'>".$inf_opt."</p>\n";
				}
				echo "</div>\n";
				echo "<div class='panel-heading text-center'>\n";
				foreach ($inf['poll_foot'] as $key => $inf_opt) {
				   echo "<p class='text-center'>".$inf_opt."</p>\n";
				}
				echo "</div>\n";
				echo "</div>\n";
				echo "</div>\n";
			}
			echo "<div class='panel-body text-center'>\n";
			echo !empty($info['poll_arch']) ? $info['poll_arch'] : "";
			echo "</div>\n";
			echo "</div>\n";
			closeside();
		}
    }
}
