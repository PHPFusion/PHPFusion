<?php
add_to_head("<link href='".THEMES."templates/global/css/photos.css' rel='stylesheet'/>\n");

/* Main Index Photogallery */
if (!function_exists('render_photo_main')) {
	function render_photo_main($info) {
		global $locale, $settings;
		echo render_breadcrumbs();
		opentable($locale['400']);
		$counter = 0;
		if (isset($info['item'])) {
			echo "<div class='row' style='padding:15px;'>\n";
			foreach($info['item'] as $data) {
				if ($counter != 0 && ($counter%$settings['thumbs_per_row'] == 0)) {
					echo "</div>\n<div class='row'>\n";
				}
				echo "<div class='col-xs-12 col-sm-".(floor(12/$settings['thumbs_per_row']))." col-md-".(floor(12/$settings['thumbs_per_row']))." col-lg-".(floor(12/$settings['thumbs_per_row']))."'>\n";
				render_photo_item($data);
				echo "</div>\n";
				$counter++;
			}
			echo "</div>\n";
		} else {
			echo "<div class='well m-t-20 m-b-20 text-center'>".$locale['406']."</div>\n";
		}
		closetable();
	}
}

/* Photo Category Page */
if (!function_exists('render_photo_category')) {
	function render_photo_category($info) {
		global $locale, $settings;
		echo render_breadcrumbs();
		opentable($locale['430']);
		echo "<!--pre_album_info-->";
		echo "<div class='panel panel-default m-t-20'>\n";
		echo "<div class='panel-body'>\n";
		echo "<div class='pull-left m-r-10'>\n";
		echo "<a title='".$info['album_link']['name']."' href='".$info['album_link']['link']."'>\n";
		if ($info['album_thumb']) {
			echo thumbnail($info['album_thumb'], '150');
		} else {
			// album title.
			echo "<i title='".$info['album_title']."' class='display-block entypo picture icon-md mid-opacity'></i>\n";
		}
		echo "</a>\n";
		echo "</div>\n";
		echo "<div class='overflow-hide'>\n";
		echo "<div class='album_title'>".$info['album_title']."</div>\n";
		echo "<span class='album_stats'>\n".$info['album_stats']."</span>\n";
		echo "</div>\n";
		echo "</div>\n</div>\n";

		echo "<div class='list-group'>\n";
		echo "<div class='list-group-item'>\n";
		echo "<!--photogallery_album_desc-->\n";
		echo "<span class='album_description'>\n".nl2br(parseubb($info['album_description']))."</span><br/>\n";
		echo "</div>\n";
		echo "</div>\n";

		if (isset($info['page_nav'])) echo "<div class='text-right'>".$info['page_nav']."</div>\n";

		echo "<!--sub_album_info-->";
		$counter = 0;
		if (isset($info['item'])) {
			echo "<div class='row' style='padding:15px;'>\n";
			foreach($info['item'] as $data) {
				if ($counter != 0 && ($counter%$settings['thumbs_per_row'] == 0)) {
					echo "</div>\n<div class='row'>\n";
				}
				echo "<div class='col-xs-12 col-sm-".(floor(12/$settings['thumbs_per_row']))." col-md-".(floor(12/$settings['thumbs_per_row']))." col-lg-".(floor(12/$settings['thumbs_per_row']))."'>\n";
				render_photo_item($data);
				echo "</div>\n";
				$counter++;
			}
			echo "</div>\n";
		} else {
			echo "<div class='well m-t-20 m-b-20 text-center'>".$locale['425']."</div>\n"; //$info['album_stats'] = $locale['425']."\n"; // no photo added
		}
		if (isset($info['page_nav'])) echo "<div class='text-right'>".$info['page_nav']."</div>\n";
		closetable();
	}
}

/* The photo container */
if (!function_exists('render_photo_item')) {
	function render_photo_item($data) {
		global $locale;
		echo "<div class='panel panel-default'>\n";
		echo "<a title='".$data['album_link']['name']."' href='".$data['album_link']['link']."'><div class='panel-image'>\n";
		if ($data['image']) {
			$offset = "style='width:100%;'";
			echo "<img src='".$data['image']."' ".$offset." title='".$data['title']."'/>\n";
		} else {
			echo "<i title='".$data['title']."' class='display-block entypo picture icon-lg mid-opacity' style='margin-top:40px;'></i>\n";
		}
		echo "</a></div>\n";
		echo "<div class='panel-body' style='height:100px;'>\n";
		echo "<a class='photo-item-title' title='".$locale['430']."' href='".$data['album_link']['link']."'>".$data['title']."</a><br/>\n";
		echo $data['description'] ? "<span class='photo-item-description'>".fusion_first_words($data['description'],10)."</span><br/>\n" : '';

		if (isset($_GET['album_id'])) {
			// show on viewalbum
			echo "</div>\n";
			echo "<div class='panel-body' style='border-top:1px solid #ddd;'>\n";
			echo "<div class='pull-left'>\n".display_avatar($data, '35px', '', TRUE, 'm-r-10')."</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo profile_link($data['user_id'], $data['user_name'], $data['user_status']);
			echo "<br/><abbr title='".showdate('forumdate', $data['photo_datestamp'])."' class='album_date'>".timer($data['photo_datestamp'])."</span>\n";
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='panel-footer'>\n";
			echo "<span class='album_views'><i class='entypo eye'></i> ".$data['photo_views']."</span>";
			echo isset($data['photo_comments']) ? "<a class='album_comments' href='".$data['photo_comments']['link']."'><i class='entypo thumbs-up'></i>".$data['photo_comments']['name']."</a>" : '';
			echo isset($data['photo_ratings']) ? "<a class='album_ratings' href='".$data['photo_ratings']['link']."'><i class='entypo chat'></i>".$data['photo_ratings']['name']."</a>" : '';
			echo "</div></div>\n";
		} else {
			// show on main index
			echo "<span class='album_count'>".$data['photo_rows']." ".($data['photo_rows']>1 ? $locale['462'] : $locale['461'])."</span>";
			echo "</div>\n";
			if ($data['photo_user']) {
				echo "<div class='panel-body' style='border-top:1px solid #ddd;'>\n";
				echo "<div class='photo_author text-smaller'>".format_word(count($data['photo_user']), $locale['463'])."</div>\n";
				echo "<div class='display-block'>\n";
				foreach($data['photo_user'] as $user_id => $user_data) {
					echo display_avatar($user_data, '25px', '', TRUE, 'm-r-0');
				}
				echo "</div>\n";
				echo "</div>\n";
			}
			echo "<div class='panel-footer'>\n";
			echo "<abbr title='".$locale['464'].showdate("shortdate", $data['album_datestamp'])."'><i class='entypo calendar text-lighter'></i></abbr> ".timer($data['album_datestamp'])."";
			echo "</div></div>\n";
		}
	}
}

if (!function_exists('render_photo')) {
	function render_photo($info) {
		global $locale, $settings, $userdata;

		opentable($locale['450']);
		echo render_breadcrumbs();
		echo "<!--pre_photo-->";

		echo "<div class='photo_cover'>\n";
		echo "<a target='_blank' href='".$info['photo_file']."' class='photogallery_photo_link' title='".(!empty($info['photo_title']) ? $info['photo_title'] : $info['photo_filename'])."'><!--photogallery_photo_".$_GET['photo_id']."-->";
		echo "<img class='img-responsive' src='".$info['photo_file']."' alt='".(!empty($info['photo_title']) ? $info['photo_title'] : $info['photo_filename'])."' style='border:0px' class='photogallery_photo' />";
		echo "</a>\n";
		echo "</div>\n";

		echo "<div class='clearfix'>\n";
		echo "<div class='pull-left'>\n";
		echo display_avatar($userdata, '50px', '', TRUE, '');
		echo "</div>\n";

		echo "<div class='btn-group pull-right'>\n";
		echo isset($info['nav']['first']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['first']['link']."' title='".$info['nav']['first']['name']."'><i class='entypo to-start'></i></a>\n" : '';
		echo isset($info['nav']['prev']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['prev']['link']."' title='".$info['nav']['prev']['name']."'><i class='entypo left-dir'></i></a>\n" : '';
		echo isset($info['nav']['next']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['next']['link']."' title='".$info['nav']['next']['name']."'><i class='entypo right-dir'></i></a>\n" : '';
		echo isset($info['nav']['last']) ? "<a class='btn btn-default btn-sm' href='".$info['nav']['last']['link']."' title='".$info['nav']['first']['last']."'><i class='entypo to-end'></i></a>\n" : '';
		echo "</div>\n";

		echo "<div class='overflow-hide m-b-20'>\n";
		echo "<span class='photo_title'>".$info['photo_title']."</span>\n<br/>";
		echo "<span class='photo_author'>".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</span>\n<br/>";
		echo "</div>\n";

		echo "<span class='photo_description list-group-item'><strong>Description</strong><br/>".$info['photo_description']."</span>";

		echo "<div class='list-group-item m-b-20'>\n";
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
		echo "<strong>".$locale['433']."</strong><abbr title='".showdate("shortdate", $info['photo_datestamp'])."'>".timer(time())."</abbr><br/>";
		echo "<strong>".$locale['454']."</strong>".$info['photo_size'][0]." x ".$info['photo_size'][1]." ".$locale['455']."<br/>\n";
		echo "<strong>".$locale['456']."</strong>".$info['photo_byte'];
		echo "</div><div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
		echo "<strong>".$locale['457']."</strong>".number_format($info['photo_views'])."<br/>\n";
		echo "<strong>".$locale['437']."</strong>".$info['photo_ratings']."<br/>\n";
		echo "<strong>".$locale['436']."</strong>".$info['photo_comment']."<br/>\n";
		echo "</div>\n</div>\n";
		echo "</div>\n";
		echo "<!--sub_photo-->";
		if ($info['photo_allow_comments']) {
			showcomments("P", DB_PHOTOS, "photo_id", $_GET['photo_id'], BASEDIR."photogallery.php?photo_id=".$_GET['photo_id']);
		}
		if ($info['photo_allow_ratings']) {
			showratings("P", $_GET['photo_id'], BASEDIR."photogallery.php?photo_id=".$_GET['photo_id']);
		}
		closetable();
	}
}
?>
