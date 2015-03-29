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

if (!function_exists('filter_item_list')) {
	function filter_item_list($info) {
		global $locale, $settings;
		$i = $_GET['rowstart']+1;
		echo "<h3 class='m-t-0'>".$locale[$_GET['filter']]."</h3>\n";
		echo "<div class='list-group m-t-20'>\n";
		if (!empty($info['filter-results'])) {
			foreach($info['filter-results'] as $data) {
				$download_title = $data['download_title'];
				echo "<div class='list-group-item clearfix'>\n";
				echo "<span class='badge m-t-10'>Downloaded ".$data['download_count']." </span>\n";
				echo "<div class='pull-left m-r-10'>\n";
				$img_thumb = ($data['download_image_thumb']) ? DOWNLOADS."images/".$data['download_image_thumb'] : IMAGES."imagenotfound70.jpg";
				echo thumbnail($img_thumb, '70px');
				echo "</div>\n";
				echo "<div class='overflow-hide'>\n";
				echo "<h4 class='m-0 display-inline-block'><span class='text-bigger'>".$i.".</span> <a class='text-dark' href='".BASEDIR."downloads.php?cat_id=".$data['download_cat']."&amp;download_id=".$data['download_id']."' title='".$download_title."'>".trimlink($data['download_title'], 100)."</a></h4><br/>";
				echo $data['download_description_short'];
				echo "</div>\n";
				echo "</div>\n";
				$i++;
			}
		} else {
			echo "<div class='text-center well m-t-20'>\n";
			echo $locale['432'];
			echo "</div>\n";
		}

		if ($info['global_item_rows'] > $settings['downloads_per_page']) {
			$append_filter = (isset($_GET['filter'])) ? "&amp;filter=".$_GET['filter']."&amp;" : '';
			echo "<div class='list-group-item text-right'>\n";
			echo makepagenav($_GET['rowstart'], $settings['downloads_per_page'], $info['global_item_rows'], 3, BASEDIR."downloads.php?".$append_filter, "rowstart")."\n";
			echo "</div>\n";
		}
		echo "</div>\n";
	}
}

if (!function_exists('most_downloaded')) {
	function most_downloaded($info) {
		global $locale;
		echo "<div class='list-group m-t-20'>\n";
		if (!empty($info['most_downloaded'])) {
			foreach($info['most_downloaded'] as $data) {
				$download_title = $data['download_title'];
				echo "<div class='list-group-item clearfix'>\n";
				echo "<span class='badge m-t-10 m-l-20'><i class='entypo down-circled'></i> ".$data['download_count']." </span>\n";
				echo "<div class='pull-left m-r-10'>\n";
				$img_thumb = ($data['download_image_thumb']) ? DOWNLOADS."images/".$data['download_image_thumb'] : IMAGES."imagenotfound70.jpg";
				echo thumbnail($img_thumb, '70px');
				echo "</div>\n";
				echo "<div class='overflow-hide'>\n";
				echo "<h4 class='m-0 display-inline-block'><a class='text-dark' href='".BASEDIR."downloads.php?cat_id=".$data['download_cat']."&amp;download_id=".$data['download_id']."' title='".$download_title."'>".trimlink($data['download_title'], 100)."</a></h4><br/>";
				echo $data['download_description_short'];
				echo "</div>\n";
				echo "</div>\n";
			}
		} else {
			echo "<div class='text-center well m-t-20'>\n";
			echo $locale['432'];
			echo "</div>\n";
		}
		echo "</div>\n";
	}
}

if (!function_exists('most_recent_download')) {
	function most_recent_download($info) {
		global $locale;
		echo "<div class='list-group m-t-20'>\n";
		if (!empty($info['most_recent'])) {
			foreach($info['most_recent'] as $data) {
				$download_title = $data['download_title'];
				echo "<div class='list-group-item clearfix'>\n";
				echo "<span class='badge m-t-10 m-l-20'><i class='entypo down-circled'></i> ".$data['download_count']." </span>\n";
				echo "<div class='pull-left m-r-10'>\n";
				$img_thumb = ($data['download_image_thumb']) ? DOWNLOADS."images/".$data['download_image_thumb'] : IMAGES."imagenotfound70.jpg";
				echo thumbnail($img_thumb, '70px');
				echo "</div>\n";
				echo "<div class='overflow-hide'>\n";
				echo "<h4 class='m-0 display-inline-block'><a class='text-dark' href='".BASEDIR."downloads.php?cat_id=".$data['download_cat']."&amp;download_id=".$data['download_id']."' title='".$download_title."'>".trimlink($data['download_title'], 100)."</a></h4><br/>";
				echo $data['download_description_short'];
				echo "</div>\n";
				echo "</div>\n";
			}
		} else {
			echo "<div class='text-center well m-t-20'>\n";
			echo $locale['432'];
			echo "</div>\n";
		}
		echo "</div>\n";
	}
}

if (!function_exists('render_downloads')) {

	function render_downloads($info) {
		global $settings, $locale;

		//opentable($locale['400']);

		echo render_breadcrumbs();
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-9'>\n";

		// Main View
		if (isset($_GET['filter']) && in_array($_GET['filter'], $info['allowed_filters'])) {
			filter_item_list($info);
		}
		elseif (!isset($_GET['cat_id'])) {
			$tab_title['title'][] = rtrim($locale['441'], ':');
			$tab_title['id'][] = "lst";
			$tab_title['icon'][] = "";
			$tab_title['title'][] =  rtrim($locale['442'], ':');
			$tab_title['id'][] = "pl";
			$tab_title['icon'][] = "";
			$tab_active = tab_active($tab_title, 0);
			echo opentab($tab_title, $tab_active, 'downloads-panel');
			echo opentabbody($tab_title['title'][0], 'lst', $tab_active);
			most_downloaded($info);
			echo closetabbody();
			echo opentabbody($tab_title['title'][1], 'pl', $tab_active);
			most_recent_download($info);
			echo closetabbody();
			echo closetab();

		}
		elseif (isset($_GET['cat_id']) && !isset($_GET['download_id'])) {
			// category page.
			if (!empty($info['download_items'])) {
				$selector = array(
					''=>$locale['451'],
					'comments'=> $locale['452'],
					'recent'=> $locale['453'],
					'title'=> $locale['454'],
					'ratings'=> $locale['455'],
				);
				for ($i=1; $i<=3; $i++) {
					$selector2[$settings['downloads_per_page']*$i] = $settings['downloads_per_page']*$i;
				}

				echo "<div class='list-group m-0'>\n";
				echo "<div class='list-group-item'>".$locale['sort'];
				echo "<span class='display-inline-block m-l-10 m-r-10' style='position:relative; vertical-align:middle;'>\n";
				echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['type']) && in_array($_GET['type'], $info['allowed_filters']) ? $selector[$_GET['type']] : $locale['451'])." <span class='caret'></span></button>\n";
				echo "<ul class='dropdown-menu'>\n";
				$max_show = $settings['downloads_per_page']*3;
				foreach($info['filters-1'] as $filter_locale => $filter_link) {
					echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
				}
				echo "</ul>\n";
				echo "</span>\n";
				echo $locale['show'];
				echo "<span class='display-inline-block m-l-10 m-r-10' style='position:relative; vertical-align:middle;'>\n";
				echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['show']) && isnum($_GET['show']) && $_GET['show'] <= $max_show ? $selector2[$_GET['show']] : $settings['downloads_per_page'])." <span class='caret'></span></button>\n";
				echo "<ul class='dropdown-menu'>\n";
				foreach($info['filters-2'] as $filter_locale => $filter_link) {
					echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
				}
				echo "</ul>\n";
				echo "</span>\n";
				echo "</div>\n";

				// download listing.

				foreach($info['download_items'] as $download_id => $download) {
					$new = ($download['download_datestamp']+604800 > time()+($settings['timeoffset']*3600)) ? "<small>- ".$locale['410']."</small>" : '';
					echo "<div class='list-group-item'>\n";
					echo "<div class='row'>\n";
					echo "<div class='col-xs-6 col-sm-9 col-md-8 col-lg-7 p-r-0'>\n";
					echo "<div class='pull-left m-r-10'>\n";
					if ($download['download_image_thumb']) {
						$img_thumb = DOWNLOADS."images/".$download['download_image_thumb'];
						echo thumbnail($img_thumb, '70px');
					} else {
						$img_thumb = IMAGES."imagenotfound70.jpg";
						echo thumbnail($img_thumb, '70px');
					}
					echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					echo "<h4 class='m-t-0 m-b-10 strong'><a class='text-dark' href='".BASEDIR."downloads.php?cat_id=".$download['download_cat']."&amp;download_id=".$download['download_id']."'>".$download['download_title']."</a> ".$new."</h4>";
					echo $download['download_description_short'] ? "<span>".$download['download_description_short']."</span><br/>\n" : '';
					echo "<span class='text-smaller'>".$locale['423'].": ".($download['download_version'] ? $download['download_version']." | " : $locale['457'].' | ');
					echo $locale['458'].": ".showdate('shortdate', $download['download_datestamp'])." | ";
					echo $locale['459']."</span>";
					if ($download['download_file']) {
						echo "<a href='".BASEDIR."downloads.php?cat_id=".$download['download_cat']."&amp;file_id=".$download['download_id']."' class='btn btn-sm btn-success hidden-xs visible-sm visible-md hidden-lg m-t-10 text-white'>".$locale['461']."<i class='entypo down-circled m-l-10'></i></a>\n";
					}
					echo "</div>\n";
					echo "</div>\n<div class='col-xs-2 col-sm-2 col-md-4 col-lg-2'>\n";
					echo "<div class='text-smaller'><strong>".$locale['440']."</strong><br/>".number_format($download['download_count'])." </div>";
					echo "<div class='text-smaller'><strong>".$locale['426']."</strong><br/>".($download['count_votes'] > 0 ? ceil($download['sum_rating']/$download['count_votes']) : $locale['429a'])."</div>";
					echo "</div>\n<div class='col-xs-4 hidden-sm hidden-md col-lg-2 p-l-0'>\n";
					if ($download['download_file']) {
						echo "<a href='".BASEDIR."downloads.php?cat_id=".$download['download_cat']."&amp;file_id=".$download['download_id']."' class='btn btn-sm btn-success text-white'>".$locale['461']."<i class='entypo down-circled m-l-10'></i></a>\n";
					} elseif ($download['download_url']) {
						echo "<a target='_blank' href='http://".$download['download_url']."' class='btn btn-sm btn-success text-white'>".$locale['464']."<i class='entypo down-circled m-l-10'></i></a>\n";
					}
					echo "</div>\n</div>\n";
					echo "</div>\n"; // list-group-item
				}
				// need to fix filter to pagenav.
				if ($info['download_item_rows'] > $info['category_show']) {
					$append_filter = (isset($_GET['type'])) ? "type=".$_GET['type']."&amp;" : '';
					$append_show = (isset($_GET['show']) && isnum($_GET['show'])) ? "show=".$_GET['show']."&amp;" : '';
					echo "<div class='list-group-item text-right'>\n";
					echo makepagenav($_GET['rowstart'.$info['download_category_id']], $settings['downloads_per_page'], $info['download_item_rows'], 3, BASEDIR."downloads.php?cat_id=".$info['download_category_id']."&amp;".$append_filter.$append_show, "rowstart".$info['download_category_id'])."\n";
					echo "</div>\n";
				}
				echo "</div>\n"; // list-group
			} else {
				echo "<div class='well text-center m-t-20'>\n";
				echo $locale['431'];
				echo "</div>\n";
			}

		}
		elseif (isset($_GET['download_id'])) {

			$data = $info['data'];

			if ($data['download_keywords'] !=="") { set_meta("keywords", $data['download_keywords']); }

			echo "<h3 class='m-t-0 m-b-0'>".$data['download_title']."</h3>\n";
			echo "<p class='text-lighter'><strong>".$info['download_category'][$_GET['cat_id']]['download_cat_name']."</strong> - ".$info['download_category'][$_GET['cat_id']]['download_cat_description']."</p>";
			echo "<div class='m-b-20'>\n";
			echo nl2br(parseubb(parsesmileys($data['download_description'])));
			echo "</div>\n";

			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-heading clearfix'>\n";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-5 col-sm-5 col-md-5 col-lg-5' style='border-right: 1px solid #ddd;'>\n";
			echo "<a href='".BASEDIR."downloads.php?cat_id=".$data['download_cat_id']."&amp;file_id=".$data['download_id']."' class='pull-left m-r-20 btn btn-success btn-xs m-t-5 text-white'>\n";
			echo "<i class='fa fa-download p-5 fa-2x'></i>\n";
			echo "</a>\n";
			echo "<div class='overflow-hide'><h4 class='m-t-5 m-b-0 strong'>".$locale['416']."</h4>\n ".$locale['420'].": ".$data['download_filesize']." </div>\n";
			echo "</div><div class='col-xs-7 col-sm-7 col-md-7 col-lg-7'>\n";
			echo "<div class='pull-left m-b-20'>\n";
			echo "<div class='pull-left'>".display_avatar($data, '25px', '', '', 'img-rounded')."</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/>";
			echo "</div>\n";
			echo "</div>\n";

			if ($data['download_allow_ratings']) {
				echo "<span class='strong'>".$locale['426a'].":</span><br/>\n";
				echo "<a id='rateJump'>".$locale['463']."</a>\n";
				add_to_jquery("	$('#rateJump').bind('click', function() { $('html,body').animate({scrollTop: $('#rate').offset().top}, 'slow');	});	");
			}
			echo "</div>\n</div>\n";

			echo "</div><div class='panel-body p-b-0'>\n";

			if ($settings['download_screenshot'] && $data['download_image']) {
				echo "<div class='pull-left m-l-0 m-10'>\n";
				echo thumbnail(DOWNLOADS."images/".$data['download_image'],'120px');
				echo "<p class='mid-opacity strong text-smaller m-t-0'>".$locale['419']."</h4>\n";
				echo "</div>\n";
			}

			echo "<div class='overflow-hide m-10'>\n";

			echo "<p class='strong m-0'>".$locale['462']."</p>\n";
			echo "<div class='row m-t-5 m-b-5'>\n";
			echo "<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['423'].":</span><br/>";
			echo $data['download_version'] ? $data['download_version'] : $locale['457'];
			echo "</div>\n<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['456'].": </span><br/>";
			echo number_format($data['download_count']);
			echo "</div>\n<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['453'].":</span><br/>";
			echo showdate("shortdate", $data['download_datestamp']);
			echo "</div></div>\n";
			echo "<hr class='m-t-5 m-b-0'>\n";
			echo "<div class='row m-t-5'>\n";
			echo "<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['411'].":</span><br/>";
			echo $data['download_license'] ? $data['download_license'] : $locale['457'];
			echo "</div>\n<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['412'].":</span><br/>";
			echo $data['download_os'] ? $data['download_os'] : $locale['457'];
			echo "</div>\n<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			echo "<span class='strong text-smaller text-lighter'>".$locale['428'].":</span><br/>";
			echo $data['download_copyright'] ? $data['download_copyright'] : $locale['457'];
			echo "</div></div>\n";
			echo "</div>\n";

			echo "</div>\n";

			if ($data['download_homepage']) {
				echo "<div class='panel-footer'>\n";
				if ($data['download_homepage']) {
					$urlprefix = (!strstr($data['download_homepage'], "http://") && !strstr($data['download_homepage'], "https://")) ? 'http://' : '';
					echo "<a href='".$urlprefix.$data['download_homepage']."' title='".$urlprefix.$data['download_homepage']."' target='_blank'>".$locale['418']."</a>\n";
				}
				echo "</div>\n";
			}
			echo "</div>\n";

			if ($data['download_description']) {
				echo "<p class='strong'>".$locale['427']."</p>";
				echo nl2br(stripslashes($data['download_description']));
			}



			include INCLUDES."comments_include.php";
			include INCLUDES."ratings_include.php";
			if ($data['download_allow_comments']) {
				showcomments("D", DB_DOWNLOADS, "download_id", $_GET['download_id'], FUSION_SELF."?cat_id=".$data['download_cat']."&amp;download_id=".$_GET['download_id']);
			}
			if ($data['download_allow_ratings']) {
				echo "<a id='rate'>\n</a>\n"; // jumper target
				showratings("D", $_GET['download_id'], FUSION_SELF."?cat_id=".$data['download_cat']."&amp;download_id=".$_GET['download_id']);
			}
		}

		echo "</div><div class='col-xs-12 col-sm-3'>\n";

		echo "<div class='text-bigger strong text-dark font-lg m-b-20'>".$locale['400']."</div>";
		echo "<ul class='m-b-20'>\n";
		echo "<li><a title='".$locale['417']."' href='".BASEDIR."downloads.php'>".$locale['417']."</a></li>\n";
		foreach($info['filters'] as $filter_locale => $filter_link) {
			echo "<li><a title='".$filter_locale."' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";

		if (!empty($info['download_category'])) {
			echo "<div class='text-bigger strong text-dark m-b-20'><i class='fa fa-list fa-fw'></i>".$locale['445']."</div>";
			echo "<ul class='m-b-20'>\n";
			foreach($info['download_category'] as $cat_data) {
				echo "<li><a ".($cat_data['download_cat_description'] ? "title='".$cat_data['download_cat_description']."'" : '')." href='".FUSION_SELF."?cat_id=".$cat_data['download_cat_id']."'>".$cat_data['download_cat_name']."</a></li>\n";
			}
			echo "</ul>\n";
		}
		echo "</div>\n</div>\n"; // left right grid.
	}
}
?>