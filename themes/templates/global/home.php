<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: themes/templates/global/home.php
| Author: Chubatyj Vitalij (Rizado)
| Web: http://chubatyj.ru/
| Co-Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Show home modules info
 */
if (!function_exists('display_home')) {
	function display_home($info) {
		foreach($info as $db_id => $content) {
			$colwidth = $content['colwidth'];
			opentable($content['blockTitle']);
			if ($colwidth) {
				$classes = "col-xs-".$colwidth." col-sm-".$colwidth." col-md-".$colwidth." col-lg-".$colwidth." content";
				echo "<div class='row'>";
				foreach($content['data'] as $data) {
					echo "<div class='".$classes." clearfix'>";
					echo "<h3><a href='".$data['url']."'>".$data['title']."</a></h3>";
					echo "<div class='small m-b-10'>".$data['meta']."</div>";
					echo "<div class='overflow-hide'>".fusion_first_words(html_entity_decode(stripslashes($data['content'])), 100)."</div>";
					echo "</div>";
				}
				echo "</div>";
			} else {
				echo $content['norecord'];
			}
			closetable();
		}
	}
}
/**
 * Show that no module have been installed
 */
if (!function_exists('display_no_item')) {
	function display_no_item() {
		global $locale;
		opentable($locale['home_0100']);
		echo $locale['home_0101'];
		closetable();
	}
}