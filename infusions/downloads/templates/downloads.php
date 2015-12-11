<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: global/downloads.php
| Author: Frederick MC Chan (Hien)
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
if (!function_exists('render_downloads')) {
	function render_downloads($info) {
		global $dl_settings, $locale;
		echo render_breadcrumbs();
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-9'>\n";
		if (isset($_GET['download_id'])) {
			$data = $info['download_item'];
			echo "<h3 class='m-t-0 m-b-0'>".$data['download_title']."</h3>\n";
			echo "<div class='m-b-20'>\n";
			echo $data['download_description_short'];
			echo "</div>\n";
			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-heading clearfix'>\n";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-5 col-sm-5 col-md-5 col-lg-5' style='border-right: 1px solid #ddd;'>\n";
			echo "<a href='".$data['download_file_link']."' class='pull-left m-r-20 btn btn-success btn-xs m-t-5 text-white'>\n";
			echo "<i class='fa fa-download p-5 fa-2x'></i>\n";
			echo "</a>\n";
			echo "<div class='overflow-hide'><h4 class='m-t-5 m-b-0 strong'>".$locale['download_1007']."</h4>\n ".$locale['download_1020'].": ".$data['download_filesize']." </div>\n";
			echo "</div><div class='col-xs-7 col-sm-7 col-md-7 col-lg-7'>\n";
			echo "<div class='pull-left m-b-20'>\n";
			if (!$data['download_allow_ratings']) {
				echo $data['download_post_author'];
			} else {
				echo "<span class='strong'>".$locale['download_1008'].":</span><br/>\n";
				echo "<a id='rateJump'>".$locale['download_3003']."</a>\n";
				add_to_jquery("	$('#rateJump').bind('click', function() { $('html,body').animate({scrollTop: $('#rate').offset().top}, 'slow');	});	");
			}
			echo "</div>\n";
			echo "</div>\n</div>\n";
			echo "</div><div class='panel-body p-b-0'>\n";
			if ($dl_settings['download_screenshot'] && $data['download_image']) {
				echo "<div class='pull-left m-l-0 m-10'>\n";
				echo thumbnail(DOWNLOADS."images/".$data['download_image'], '120px');
				echo "<p class='mid-opacity strong text-smaller m-t-0'>".$locale['download_1009']."</h4>\n";
				echo "</div>\n";
			}
			echo "<div class='overflow-hide m-10'>\n";
			echo "<p class='strong m-0'>".$locale['download_1010']."</p>\n";
			echo "<div class='row m-t-5 m-b-5'>\n";
			echo "<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['download_1011'].":</span><br/>".$data['download_version'];
			echo "</div>\n<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['download_1012'].": </span><br/>".$data['download_count'];
			echo "</div>\n<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['download_1021'].":</span><br/>".$data['download_post_time'];
			echo "</div></div>\n";
			echo "<hr class='m-t-5 m-b-0'>\n";
			echo "<div class='row m-t-5'>\n";
			echo "<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['download_1013'].":</span><br/>".$data['download_license'];
			echo "</div>\n<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['download_1014'].":</span><br/>".$data['download_os'];
			echo "</div>\n<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['download_1015'].":</span><br/>".$data['download_copyright'];
			echo "</div></div>\n";
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='panel-footer'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['download_1017'].":</span><br/>".$data['download_homepage'];
			echo "</div>\n";
			echo "</div>\n";
			if ($data['download_description']) {
				echo "<p class='strong'>".$locale['download_1019']."</p>";
				echo $data['download_description'];
			}
			if ($data['download_allow_comments']) {
				showcomments("D", DB_DOWNLOADS, "download_id", $_GET['download_id'], FUSION_SELF."?cat_id=".$data['download_cat']."&amp;download_id=".$_GET['download_id']);
			}
			if ($data['download_allow_ratings']) {
				echo "<a id='rate'>\n</a>\n"; // jumper target
				showratings("D", $_GET['download_id'], FUSION_SELF."?cat_id=".$data['download_cat']."&amp;download_id=".$_GET['download_id']);
			}
		} else {
			echo "<!--pre_download_cat-->\n";
			echo "<div class='list-group'>\n";
			if (!empty($info['download_item'])) {
				foreach ($info['download_item'] as $download_id => $data) {
					$download_title = $data['download_title'];
					echo "<div class='list-group-item clearfix'>\n";
					echo "<div class='pull-right'>\n";
					echo "<div class='m-t-10 m-r-10'><i class='entypo down-circled'></i> ".$data['download_count']."</div>\n";
					echo "<div class='m-r-10'><i class='fa fa-comments-o fa-fw'></i>".$data['download_comments']."</div>\n";
					echo "<div class='m-r-10'><i class='fa fa-star-o fa-fw'></i>".$data['download_sum_rating']."</div>\n";
					echo "<a class='btn btn-sm btn-primary m-t-10 ".(empty($data['download_file_link']) ? 'disabled' : '')."' href='".$data['download_file_link']."'><i class='fa fa-download fa-fw'></i> ".$locale['download_1007']."</a>\n";
					echo "</div>\n";
					echo "<div class='pull-left m-r-10'>\n";
					echo $data['download_image'];
					echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					echo "<div class='overflow-hide'>\n";
					echo "<h4 class='m-0 display-inline-block'><a class='text-dark' href='".$data['download_link']."' title='".$download_title."'>".trimlink($data['download_title'], 100)."</a></h4>";
					echo "<div class='m-b-10'>".$data['download_category_link']."</div>\n";
					echo "<div class='m-b-5'>".$data['download_description_short']."</div>";
					echo "</div>\n";
					echo "</div>\n";
					echo "</div>\n";
				}
				echo $info['download_nav'];
			} else {
				echo "<div class='text-center well m-t-20'>\n";
				echo $locale['download_3000'];
				echo "</div>\n";
			}
			echo "</div>\n";
			echo "<!--sub_download_cat-->";
		}
		echo "</div><div class='col-xs-12 col-sm-3'>\n";
		echo display_download_menu($info);
		echo "</div>\n</div>\n";
	}
}
if (!function_exists('display_download_menu')) {
	function display_download_menu($info) {
		global $locale;
		// Internal function
		function find_cat_menu($info, $cat_id = 0, $level = 0) {
			$html = '';
			if (!empty($info[$cat_id])) {
				foreach ($info[$cat_id] as $download_cat_id => $cdata) {
					$active = ($download_cat_id == isset($_GET['cat_id']) && $_GET['cat_id'] !== '') ? 1 : 0;
					$html .= "<li ".($active ? "class='active strong'" : '')." >".str_repeat('&nbsp;', $level)." ".$cdata['download_cat_link']."</li>\n";
					if ($active && $download_cat_id != 0) {
						if (!empty($info[$download_cat_id])) {
							$html .= find_cat_menu($info, $download_cat_id, $level++);
						}
					}
				}
			}
			return $html;
		}

		// The layout calling the above function
		ob_start();
		echo "<ul class='m-b-20'>\n";
		echo "<li><a title='".$locale['download_1001']."' href='".DOWNLOADS."downloads.php'>".$locale['download_1001']."</a></li>\n";
		echo "</ul>\n";
		echo "<ul class='m-b-20'>\n";
		foreach ($info['download_filter'] as $filter_key => $filter) {
			echo "<li ".(isset($_GET['type']) && $_GET['type'] == $filter_key ? "class='active strong'" : '')." ><a href='".$filter['link']."'>".$filter['title']."</a></li>\n";
		}
		echo "</ul>\n";
		echo "<div class='text-bigger strong text-dark m-b-20 m-t-20'><i class='fa fa-list m-r-10'></i> ".$locale['download_1003']."</div>\n";
		echo "<ul class='m-b-40'>\n";
		// Call recursive function
		$download_cat_menu = find_cat_menu($info['download_categories']);
		if (!empty($download_cat_menu)) {
			echo $download_cat_menu;
		} else {
			echo "<li>".$locale['download_3001']."</li>\n";
		}
		echo "</ul>\n";
		echo "<div class='text-bigger strong text-dark m-t-20 m-b-20'><i class='fa fa-users m-r-10'></i> ".$locale['download_1004']."</div>\n";
		echo "<ul class='m-b-40'>\n";
		if (!empty($info['download_author'])) {
			foreach ($info['download_author'] as $author_id => $author_info) {
				echo "<li ".($author_info['active'] ? "class='active strong'" : '').">
					<a href='".$author_info['link']."'>".$author_info['title']."</a> <span class='badge m-l-10'>".$author_info['count']."</span>
					</li>\n";
			}
		} else {
			echo "<li>".$locale['download_3002']."</li>\n";
		}
		echo "</ul>\n";
		$str = ob_get_contents();
		ob_end_clean();
		return $str;
	}
}