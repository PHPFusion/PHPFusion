<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: render_functions.php
| Author: PHP-Fusion Development Team
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
// Render comments template
if (!function_exists("render_comments")) {
	function render_comments($c_data, $c_info) {
		global $locale;
		opentable($locale['c100']);
		if (!empty($c_data)) {
			echo "<div class='comments floatfix'>\n";
			$c_makepagenav = '';
			if ($c_info['c_makepagenav'] !== FALSE) {
				echo $c_makepagenav = "<div style='text-align:center;margin-bottom:5px;'>".$c_info['c_makepagenav']."</div>\n";
			}
			foreach ($c_data as $data) {
				echo "<div class='tbl2'>\n";
				if ($data['edit_dell'] !== FALSE) {
					echo "<div style='float:right' class='comment_actions'>".$data['edit_dell']."\n</div>\n";
				}
				echo "<a href='".FUSION_REQUEST."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a> |\n";
				echo "<span class='comment-name'>".$data['comment_name']."</span>\n";
				echo "<span class='small'>".$data['comment_datestamp']."</span>\n";
				echo "</div>\n<div class='tbl1 comment_message'>".$data['comment_message']."</div>\n";
			}
			echo $c_makepagenav;
			if ($c_info['admin_link'] !== FALSE) {
				echo "<div style='float:right' class='comment_admin'>".$c_info['admin_link']."</div>\n";
			}
			echo "</div>\n";
		} else {
			echo $locale['c101']."\n";
		}
		closetable();
	}
}

// Render breadcrumbs template
if (!function_exists("render_breadcrumbs")) {
	function render_breadcrumbs($data, $show_home = TRUE, $last_no_link = FALSE, $class = 'breadcrumb') {
		global $breadcrumbs, $locale;

		$html = "<ol class='$class'>\n";

		// Should we also show the Home link?
		if ($show_home) {
			$breadcrumbs = array_merge(array(BASEDIR.'index.php' => 'Home'), $breadcrumbs); // Home needs localised
		}

		require_once(BASEDIR.'breadcrumbs.php');

		$no_link = FALSE;
		// Get the last link
		$last_link = array_keys($breadcrumbs);
		$last_link = array_pop($last_link);
		// Loop through links and generate the HTML
		foreach ($breadcrumbs as $link => $title) {
			// Check if this item is the last and if we should make it a link or not
			if ($last_no_link && ($link == $last_link)) {
				$no_link = TRUE;
			}
			$html .= "<li class='crumb'>";
			$html .= ($no_link) ? $title : "<a href='".$link."'>".$title."</a>";
			$html .= "</li>\n";
		}

		$html .= "</ol>\n";

		return $html;
	}
}
?>