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
	function render_comments($c_data, $c_info){
		global $locale;
		opentable($locale['c100']);
		if (!empty($c_data)){
			echo "<div class='comments floatfix'>\n";
 			$c_makepagenav = '';
 			if ($c_info['c_makepagenav'] !== false) { 
				echo $c_makepagenav = "<div style='text-align:center;margin-bottom:5px;'>".$c_info['c_makepagenav']."</div>\n"; 
			}
 			foreach($c_data as $data) {
				echo "<div class='tbl2'>\n";
				if ($data['edit_dell'] !== false) { 
					echo "<div style='float:right' class='comment_actions'>".$data['edit_dell']."\n</div>\n";
				}
				echo "<a href='".FUSION_REQUEST."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a> |\n";
				echo "<span class='comment-name'>".$data['comment_name']."</span>\n";
				echo "<span class='small'>".$data['comment_datestamp']."</span>\n";
				echo "</div>\n<div class='tbl1 comment_message'>".$data['comment_message']."</div>\n";
			}
			echo $c_makepagenav;
			if ($c_info['admin_link'] !== false) {
				echo "<div style='float:right' class='comment_admin'>".$c_info['admin_link']."</div>\n";
			}
			echo "</div>\n";
		} else {
			echo $locale['c101']."\n";
		}
		closetable();   
	}
}
?>