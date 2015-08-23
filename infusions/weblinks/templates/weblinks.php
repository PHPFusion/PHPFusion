<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks.php
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

if (!function_exists('render_weblinks_item')) {
	function render_weblinks_item($info) {
		global $locale;
		echo render_breadcrumbs();
		opentable($locale['400'].": ".$info['weblink_cat_name']);
		echo $info['page_nav'] ? "<<div class='text-right'>".$info['page_nav']."</div>" : '';
		if ($info['weblink_rows']) {
			foreach($info['item'] as $weblink_id => $data) {
				$new = $data['new'] == 1 ? "<span class='label label-success m-r-10' style='padding:3px 10px;'>".$locale['410']."</span>" : '';
				echo "<aside class='display-inline-block m-t-20' style='width:100%;'>\n";
				echo "<span class='weblink_title strong'><a href='".$data['weblink']['link']."' target='_blank'><strong>".$data['weblink']['name']."</strong></a></span>\n";
				echo $new;
				if ($data['weblink_description'] != "") echo "<div class='weblink_text'>".nl2br(stripslashes($data['weblink_description']))."</div>\n";
				echo "<span class='display-inline m-r-20'><strong>".$locale['411']."</strong> ".showdate("shortdate", $data['weblink_datestamp'])."</span>\n";
				echo "<span class='display-inline'><strong>".$locale['412']."</strong> ".$data['weblink_count']."</span>\n";
				echo "</aside>\n";
			}
		} else {
			echo "<div class='well text-center'>".$locale['431']."</div>\n";
		}
		closetable();
	}
}

if (!function_exists('render_weblinks')) {
	function render_weblinks($info) {
		global $locale;
		echo render_breadcrumbs();
		opentable($locale['400']);
		if ($info['weblink_cat_rows'] != 0) {
			$counter = 0;
			$columns = 2;
			echo "<div class='row m-0'>\n";
			if (!empty($info['item'])) {
				foreach($info['item'] as $weblink_cat_id => $data) {
					if ($counter != 0 && ($counter%$columns == 0)) {
						echo "</div>\n<div class='row m-0'>\n";
					}
					echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6 p-t-20'>\n";

					echo "<div class='media'>\n";
					echo "<div class='pull-left'><i class='entypo folder mid-opacity icon-sm'></i></div>\n";
					echo "<div class='media-body overflow-hide'>\n";
					echo "<div class='media-heading strong'><a href='".$data['weblink_item']['link']."'>".$data['weblink_item']['name']."</a> <span class='small'>".$data['weblink_count']."</span></div>\n";
					if ($data['weblink_cat_description'] != "") {
						echo "<span>".$data['weblink_cat_description']."</span>";
					}
					echo "</div>\n</div>\n";
					echo "</div>\n";
					$counter++;
				}
			}
			echo "</div>\n";
		} else {
			echo "<div style='text-align:center'><br />\n".$locale['430']."<br /><br />\n</div>\n";
		}
		closetable();
	}
}
