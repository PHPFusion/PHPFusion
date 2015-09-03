<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: global/photos.php
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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
if (!function_exists("render_gallery")) {
	function render_gallery($info) {
		global $locale;
		echo render_breadcrumbs();
		opentable($locale['400']);
		echo $info['page_nav'];
		if (isset($info['item'])) {
			function render_photoAlbum(array $info = array()) {
				// add admin edit.
				global $locale, $gallery_settings;
				echo "<div class='panel panel-default'>\n";
				echo "<div class='panel-heading'>\n";
				echo "<a title='".$locale['430']."' href='".$info['album_link']['link']."'>\n<strong>".trimlink($info['album_link']['name'], 10)."</strong>\n</a>\n";
				echo "</div>\n";
				echo "<div class='overflow-hide' style='background: #ccc; height: ".($gallery_settings['thumb_h']-15)."px'>\n";
				echo $info['image'];
				echo "</div>\n";
				echo "<div class='panel-body'>\n";
				echo "<span class='album_count'>".$info['photo_rows']." ".($info['photo_rows'] > 1 ? $locale['462'] : $locale['461'])."</span>";
				echo "</div>\n";
				echo "<div class='panel-footer'>\n";
				echo "<abbr title='".$locale['464'].showdate("shortdate", $info['album_datestamp'])."'><i class='entypo calendar text-lighter'></i></abbr> ".timer($info['album_datestamp'])."";
				if (!empty($info['album_edit']) && !empty($info['album_delete'])) {
					echo "</div>\n<div class='panel-footer'>\n";
					echo "<a class='btn btn-default' href='".$info['album_edit']['link']."' title='".$info['album_edit']['name']."'><i class='fa fa-edit fa-lg'></i></a>\n";
					echo "<a class='btn btn-danger' href='".$info['album_delete']['link']."' title='".$info['album_delete']['name']."'><i class='fa fa-trash fa-lg'></i></a>\n";
				}
				echo "</div></div>\n";
			}

			echo "<div class='row'>\n";
			foreach ($info['item'] as $data) {
				echo "<div class='col-xs-12 col-sm-3'>\n";
				render_photoAlbum($data);
				echo "</div>\n";
			}
		} else {
			echo "<div class='well m-t-20 m-b-20 text-center'>".$locale['406']."</div>\n";
		}
		echo $info['page_nav'];
		echo "</div>\n";
		closetable();
	}
}
/* Photo Category Page */
if (!function_exists('render_photo_album')) {
	function render_photo_album($info) {
		global $locale;
		echo render_breadcrumbs();
		opentable($locale['430']);
		echo "<!--pre_album_info-->";
		echo "<div class='clearfix well'>\n";
		echo "<h4 class='album_title m-t-0'>".$info['album_title']."</h4>\n";
		if (isset($info['album_stats'])) {
			echo "<span class='album_stats'>\n".$info['album_stats']."</span>\n";
		}
		if ($info['album_description']) {
			echo "<div class='m-t-20'>\n";
			echo "<!--photogallery_album_desc-->\n";
			echo "<span class='album_description'>\n".nl2br(parseubb($info['album_description']))."</span><br/>\n";
			echo "</div>\n";
		}
		echo "</div>\n";
		echo "<hr/>\n";
		if (isset($info['page_nav'])) echo "<div class='text-right'>".$info['page_nav']."</div>\n";
		echo "<!--sub_album_info-->";
		$counter = 0;
		function render_photo_items(array $info = array()) {
			global $locale, $gallery_settings;
			echo "<div class='panel panel-default'>\n";
			echo "<div class='overflow-hide' style='background: #ccc; height: ".($gallery_settings['thumb_h']-15)."px'>\n";
			echo $info['image'];
			echo "</div>\n";
			echo "<div class='panel-body'>\n";
			echo "<a class='word-break' href='".$info['photo_link']['link']."'><strong>".trimlink($info['photo_link']['name'], 15)."</strong></a>\n<br/>";
			echo "</div>\n";
			echo "<div class='panel-footer'>\n";
			echo "<span><i class='fa fa-eye fa-fw'></i>".$info['photo_views']."</span></br>\n";
			if (isset($info['photo_comments'])) echo "<span><i class='fa fa-comment-o fa-fw'></i><a href='".$info['photo_comments']['link']."'>".$info['photo_comments']['word']."</a>\n</span></br>\n";
			if (isset($info['photo_ratings'])) echo "<span><i class='fa fa-star-o fa-fw'></i><a href='".$info['photo_ratings']['link']."'>".$info['photo_ratings']['word']."</a>\n</span></br>\n";
			echo "</div>\n";
			echo "<div class='panel-footer'>\n";
			echo "<small><strong>".$locale['434']."</strong></small>\n<br/>\n";
			echo "<div class='pull-left'>\n".display_avatar($info, "15px", "", "", "")."</div>";
			echo "<div class='overflow-hide'>\n".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</div>\n";
			echo "</div>\n";
			echo "<div class='panel-footer'>\n";
			echo "<abbr title='".$locale['464'].showdate("shortdate", $info['photo_datestamp'])."'>
			<i class='entypo calendar text-lighter'></i></abbr> ".timer($info['photo_datestamp'])."";
			if (!empty($info['photo_edit']) && !empty($info['photo_delete'])) {
				echo "</div>\n<div class='panel-footer'>\n";
				echo "<a class='btn btn-default' href='".$info['photo_edit']['link']."' title='".$info['photo_edit']['name']."'><i class='fa fa-edit fa-lg'></i></a>\n";
				echo "<a class='btn btn-danger' href='".$info['photo_delete']['link']."' title='".$info['photo_delete']['name']."'><i class='fa fa-trash fa-lg'></i></a>\n";
			}
			echo "</div></div>\n";
		}

		if (isset($info['item'])) {
			echo "<div class='row m-0' style='position:relative;'>\n";
			global $gallery_settings;
			// theme compat solutions
			$theme = fusion_get_settings("theme");
			switch ($theme) {
				case "Septenary":
					$grid_offset = -10;
					break;
				case "Bootstrap":
					$grid_offset = 13;
					break;
				case "debonair":
					$grid_offset = 26;
					break;
				default :
					$grid_offset = 13;
			}
			foreach ($info['item'] as $data) {
				echo "<div style='margin:0 auto; width: ".($gallery_settings['thumb_w']-$grid_offset)."px; float:left; padding-left:5px; padding-right:5px;'>\n";
				render_photo_items($data);
				echo "</div>\n";
				$counter++;
			}
			echo "</div>\n";
		} else {
			echo "<div class='well m-t-20 m-b-20 text-center'>".$locale['425']."</div>\n";
		}
		if (isset($info['page_nav'])) echo "<div class='text-right'>".$info['page_nav']."</div>\n";
		closetable();
	}
}
if (!function_exists('render_photo')) {
	function render_photo($info) {
		global $locale, $userdata;
		opentable($locale['450']);
		echo render_breadcrumbs();
		echo "<!--pre_photo-->";
		echo "<a target='_blank' href='".$info['photo_filename']."' class='photogallery_photo_link' title='".(!empty($info['photo_title']) ? $info['photo_title'] : $info['photo_filename'])."'><!--photogallery_photo_".$_GET['photo_id']."-->";
		echo "<img class='img-responsive' style='margin:0 auto;' src='".$info['photo_filename']."' alt='".(!empty($info['photo_title']) ? $info['photo_title'] : $info['photo_filename'])."' style='border:0px' class='photogallery_photo' />";
		echo "</a>\n";
		echo "<div class='clearfix'>\n";
		echo "<div class='btn-group pull-right m-t-20'>\n";
		echo isset($info['nav']['first']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['first']['link']."' title='".$info['nav']['first']['name']."'><i class='entypo to-start'></i></a>\n" : '';
		echo isset($info['nav']['prev']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['prev']['link']."' title='".$info['nav']['prev']['name']."'><i class='entypo left-dir'></i></a>\n" : '';
		echo isset($info['nav']['next']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['next']['link']."' title='".$info['nav']['next']['name']."'><i class='entypo right-dir'></i></a>\n" : '';
		echo isset($info['nav']['last']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['last']['link']."' title='".$info['nav']['last']['name']."'><i class='entypo to-end'></i></a>\n" : '';
		echo "</div>\n";
		echo "<div class='overflow-hide m-b-20'>\n";
		echo "<h2 class='photo_title m-b-0'>".$info['photo_title']."</span>\n</h2>\n";
		if ($info['photo_description']) {
			echo "<span class='photo_description list-group-item'>".$info['photo_description']."</span>";
		}
		echo "</div>\n";
		echo "<div class='list-group-item m-b-20'>\n";
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
		echo "<strong>".$locale['434']."</strong>".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."<br/>\n";
		echo "<strong>".$locale['433']."</strong><abbr title='".showdate("shortdate", $info['photo_datestamp'])."'>".timer(time())."</abbr><br/>";
		echo "<strong>".$locale['454']."</strong>".$info['photo_size'][0]." x ".$info['photo_size'][1]." ".$locale['455']."<br/>\n";
		echo "<strong>".$locale['456']."</strong>".$info['photo_byte'];
		echo "</div><div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
		echo "<strong>".$locale['457']."</strong>".number_format($info['photo_views'])."<br/>\n";
		echo "<strong>".$locale['437']."</strong>".$info['photo_ratings']."<br/>\n";
		echo "<strong>".$locale['436']."</strong>".$info['photo_comment']."<br/>\n";
		echo "</div>\n</div>\n";
		echo "</div>\n</div>\n";
		echo "<!--sub_photo-->";
		if ($info['photo_allow_comments']) {
			showcomments("P", DB_PHOTOS, "photo_id", $_GET['photo_id'], INFUSIONS."gallery/gallery.php?photo_id=".$_GET['photo_id']);
		}
		if ($info['photo_allow_ratings']) {
			showratings("P", $_GET['photo_id'], INFUSIONS."gallery/gallery.php?photo_id=".$_GET['photo_id']);
		}
		closetable();
	}
}