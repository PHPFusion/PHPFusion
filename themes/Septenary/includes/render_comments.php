<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: render_comments.php
| Version: 1.00
| Author: PHP-Fusion Mods UK
| Developer & Designer: Craig, Chan
| Site: http://www.phpfusionmods.co.uk
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
function render_comments($c_data, $c_info) {
	global $locale, $settings;
	if (!empty($c_data)) {
		echo "<div class='clearfix m-b-20'>\n";
		if ($c_info['admin_link'] !== FALSE) {
			echo "<div class='pull-right'>".$c_info['admin_link']."</div>\n";
		}
		echo "<h2 class='m-t-0 pull-left display-inline-block'>".format_word(count($c_data), $locale['fmt_comment'])."</h2>\n";
		echo "</div>\n";
		echo "<div class='comments clearfix'>\n";
		foreach ($c_data as $data) {
			$comm_count = "<a href='".FUSION_REQUEST."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a>";
			echo "<div class='m-t-20 comment-main clearfix'>\n";
			echo ($settings['comments_avatar']) ? "<div class='comment-avatar pull-left m-r-10'>".$data['user_avatar']."</div>\n" : '';
			echo "<div class='overflow-hide'>\n";
			echo "<div class='comment-header'>\n";
				echo "<div class='pull-right'>\n";
				echo "<span class='comment-count text-dark m-r-10'>".$comm_count."</span>\n";
				if ($data['edit_dell'] !== FALSE) {
					echo "<span class='comment_actions'>".$data['edit_dell']."\n</span>\n";
				}
				echo "</div>";
				echo "<h4 class='display-inline'>".$data['comment_name']."</h4>\n<span class='m-l-5 text-smaller m-t-5 text-lighter'>".$data['comment_datestamp']."</span>";
			echo "</div>\n";
			echo "<div class='comments-body'>\n";
			echo $data['comment_message'];
			echo "</div>\n";
			echo "</div>\n";
			echo "</div>\n";
		}
		echo $c_info['c_makepagenav'] ? "<div class='flleft'>".$c_info['c_makepagenav']."</div>\n" : "";
		echo "</div>\n";
	} else {
		echo "<div class='nocomments-message spacer'>".$locale['c101']."</div>\n";
	}
}
