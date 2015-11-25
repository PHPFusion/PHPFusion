<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news.php
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
if (!function_exists('render_main_news')) {
	/**
	 * News Page Template
	 * @param $info
	 */
	function render_main_news($info) {
		global $userdata, $settings, $news_settings, $locale;
        add_to_head("<link href='".INFUSIONS."news/templates/css/news.css' rel='stylesheet'/>\n");
		add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>");
		if (isset($_POST['switchview'])) {
			add_to_jquery("$.cookie('fusion_news_view', '".$_POST['switchview']."', {expires: 7});");
			$_COOKIE['fusion_news_view'] = $_POST['switchview'];
		}
		opentable($locale['global_077']);
		echo render_breadcrumbs();
		/* Slideshow */
		$carousel_indicators = '';
		$carousel_item = '';
		$res = 0;
		$carousel_height = "300";
		if (!empty($info['news_items'])) {
			$i = 0;
			foreach ($info['news_items'] as $news_item) {
				if ($news_item['news_image_src'] && file_exists($news_item['news_image_src'])) {
					$carousel_active = $res == 0 ? 'active' : '';
					$res++;
					$carousel_indicators .= "<li data-target='#news-carousel' data-slide-to='$i' class='".$carousel_active."'></li>\n";
					$carousel_item .= "<div class='item ".$carousel_active."'>\n";
					$carousel_item .= "<img class='img-responsive' style='position:absolute; width:100%; margin-top:-25%' src='".$news_item['news_image_src']."' alt='".$news_item['news_subject']."'>\n";
					$carousel_item .= "
					<div class='carousel-caption'>
						<div class='overflow-hide'>
						<a class='text-white' href='".INFUSIONS."news/news.php?readmore=".$news_item['news_id']."'><h4 class='text-white m-t-10'>".$news_item['news_subject']."</h4></a>\n
						<span class='news-carousel-action m-r-10'><i class='fa fa-eye fa-fw'></i>".$news_item['news_reads']."</span>
						".($news_item['news_allow_comments'] ? "<span class='m-r-10'>".display_comments($news_item['news_comments'], INFUSIONS."news/news.php?readmore=".$news_item['news_id']."#comments")."</span>" : '')."
						".($news_item['news_allow_ratings'] ? "<span class='m-r-10'>".display_ratings($news_item['news_sum_rating'], $news_item['news_count_votes'], INFUSIONS."news/news.php?readmore=".$news_item['news_id']."#postrating")." </span>" : '')."
						</div>\n
					</div>\n</div>\n
					";
					$i++;
				}
			}
		}
		/* Title Panel */
		if (!isset($_GET['readmore'])) {
			if ($res) {
				echo "<div id='news-carousel' class='carousel slide'  data-interval='20000' data-ride='carousel'>\n";
				if ($res > 1) {
					echo "<ol class='carousel-indicators'>\n";
					echo $carousel_indicators;
					echo "</ol>";
				}
				echo "<div class='carousel-inner' style='height:".$carousel_height."px' role='listbox'>\n";
				echo $carousel_item;
				echo "</div>\n";
				echo "
				<a class='left carousel-control' href='#news-carousel' role='button' data-slide='prev'>
					<span class='glyphicon glyphicon-chevron-left' aria-hidden='true'></span>
					<span class='sr-only'>".$locale['previous']."</span>
			  	</a>
			  	<a class='right carousel-control' href='#news-carousel' role='button' data-slide='next'>
					<span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span>
					<span class='sr-only'>".$locale['next']."</span>
			  	</a>\n
				";
				echo "</div>\n";
			}
			echo "<div class='panel panel-default panel-news-header'>\n";
			echo "<div class='panel-body'>\n";
			echo "<div class='pull-right'>\n";
			echo "<a class='btn btn-sm btn-default text-dark' href='".INFUSIONS."news/news.php'><i class='entypo newspaper'></i>".$locale['global_082']."</a>\n";
			echo "<button type='button' class='btn btn-sm btn-primary' data-toggle='collapse' data-target='#newscat' aria-expanded='true' aria-controls='newscat'><i class='entypo book open'></i>".$locale['global_084']."</button>\n";
			echo "</div>\n";
			echo "<div class='pull-left m-r-10' style='position:relative; margin-top:-30px;'>\n";
			echo "<div style='max-width:80px;'>\n";
			echo $info['news_cat_image'];
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo "<h3 class='display-inline text-dark'>".$info['news_cat_name']."</h3><br/><span class='strong'>".$locale['global_083'].":</span> <span class='text-dark'>\n
			".($info['news_last_updated'] > 0 ? showdate('newsdate', $info['news_last_updated']) : $locale['na'])."</span>";
			echo "</div>\n";
			echo "</div>\n";
			echo "<div id='newscat' class='panel-collapse collapse m-b-10'>\n";
			echo "<!--pre_news_cat_idx-->";
			echo "<ul class='list-group'>\n";
			echo "<li class='list-group-item'><hr class='m-t-0 m-b-5'>\n";
			echo "<span class='display-inline-block m-b-10 strong text-smaller text-uppercase'> ".$locale['global_085']."</span><br/>\n";
			foreach ($info['news_categories'] as $cat_id => $cat_data) {
				echo isset($_GET['cat_id']) && $_GET['cat_id'] == $cat_id ? '' : "<a href='".INFUSIONS."news/news.php?cat_id=".$cat_id."' class='btn btn-sm btn-default'>".$cat_data['name']."</a>";
			}
			echo "</li>";
			echo "</ul>\n";
			echo "<!--sub_news_cat_idx-->\n";
			echo "</div>\n</div>\n";
			echo "<div class='row m-b-20 m-t-20'>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
			echo openform('viewform', 'post', FUSION_REQUEST, array(
				'max_tokens' => 1,
				'class' => 'pull-right display-inline-block m-l-10'
			));
			echo "<div class='btn-group'>\n";
			$active = isset($_COOKIE['fusion_news_view']) ? $_COOKIE['fusion_news_view'] : '';
			echo form_button('switchview', '', '1', array(
				'class' => "btn-sm btn-default nsv ".($active == 1 ? 'active' : '')." ",
				'icon' => 'entypo layout',
				'alt' => $locale['global_089b']
			));
			echo form_button('switchview', '', '2', array(
				'class' => "btn-sm btn-default nsv ".($active == 2 ? 'active' : '')."",
				'icon' => 'entypo menu',
				'alt' => $locale['global_089c']
			));
			echo "</div>\n";
			echo closeform();
			// Filters
			echo "<div class='display-inline-block'>\n";
			echo "<span class='text-dark strong m-r-10'>".$locale['show']." :</span>";
			$i = 0;
			foreach ($info['news_filter'] as $link => $title) {
				$filter_active = (!isset($_GET['type']) && $i == '0') || isset($_GET['type']) && stristr($link, $_GET['type']) ? 'text-dark strong' : '';
				echo "<a href='".$link."' class='display-inline $filter_active m-r-10'>".$title."</a>";
				$i++;
			}
			echo "</div>\n";
			// end filter.
			echo "</div>\n</div>\n";
			$news_span = $active == 2 ? 12 : 4;
			if (!empty($info['news_items'])) {
				echo "<div class='row'>\n";
				foreach ($info['news_items'] as $i => $news_info) {
					echo "<div class='col-xs-12 col-sm-$news_span col-md-$news_span col-lg-$news_span'>\n";
					echo (isset($_GET['cat_id'])) ? "<!--pre_news_cat_idx-->\n" : "<!--news_prepost_".$i."-->\n";
					render_news($news_info['news_subject'], $news_info['news_news'], $news_info, $active == 2);
					echo (isset($_GET['cat_id'])) ? "<!--sub_news_cat_idx-->" : "<!--sub_news_idx-->\n";
					echo "</div>\n";
				}
				echo "</div>\n";
				if ($info['news_item_rows'] > $news_settings['news_pagination']) {
					$type_start = isset($_GET['type']) ? "type=".$_GET['type']."&amp;" : '';
					$cat_start = isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '';
					echo "<div class='text-center m-t-10 m-b-10'>".makepagenav($_GET['rowstart'], $news_settings['news_pagination'], $info['news_item_rows'], 3, INFUSIONS."news/news.php?".$cat_start.$type_start)."</div>\n";
				}
			} else {
				echo "<div class='well text-center'>".$locale['global_078']."</div>\n";
			}
		} else {
			render_news_item($info);
		}
		closetable();
	}
}
if (!function_exists('render_news')) {
	/**
	 * News Item Container
	 * @param      $info
	 * @param bool $list_view
	 */
	function render_news($subject, $news, $info, $list_view = FALSE) {
		global $locale, $settings, $news_settings, $aidlink;

		$parameter = $settings['siteurl']."infusions/news/news.php?readmore=".$info['news_id'];
		$title = $settings['sitename'].$locale['global_200'].$locale['global_077'].$locale['global_201'].$info['news_subject']."".$locale['global_200'];

		if ($list_view) {
			echo "<article class='panel panel-default clearfix' style='height:300px;'>\n";
			echo ($info['news_sticky']) ? "<i class='pull-right entypo ialert icon-sm'></i>\n" : '';
			if ($info['news_image']) {
				echo "<div class='pull-left' style='display:inline-block; height: 100%; width:300px;'>\n";
				echo $info['news_image'];
				echo "</div>\n";
				echo "<div class='overflow-hide' style='padding:25px;'>\n";
			}
			echo "<h4 class='news-title panel-title'><a class='strong text-dark' href='".INFUSIONS."news/news.php?readmore=".$info['news_id']."' >".$info['news_subject']."</a></h4>\n";
			echo "<div class='m-t-10'>\n";
			echo "<span class='news-text m-t-10'>".$info['news_news']."</span>\n";
			echo "<div class='m-t-10'><span class='news-date'>".showdate($settings['newsdate'], $info['news_date'])." -- </span></div>\n";
			echo "<div class='news-category m-t-10'><span class='text-dark strong'>\n".ucwords($locale['in'])."</span> : ";
			echo $info['cat_name'] ? "<a href='".INFUSIONS."news/news.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>" : "<a href='".INFUSIONS."news/news.php?cat_id=0'>".$locale['global_080']."</a>&nbsp;";
			echo "</div>\n";
			if ($info['news_image']) {
				echo "</div>\n";
			}
			echo "<div class='news-footer ".($info['news_image'] ? "m-t-20" : '')." p-15 p-l-0'>\n";
			echo "<span><i class='entypo eye'></i> ".number_format($info['news_reads'])."</span>";
			echo $info['news_allow_comments'] ? display_comments($info['news_comments'], INFUSIONS."news/news.php?readmore=".$info['news_id']."#comments") : '';
			echo $info['news_allow_ratings'] ? display_ratings($info['news_sum_rating'], $info['news_count_votes'], INFUSIONS."news/news.php?readmore=".$info['news_id']."#postrating") : '';
			echo "<a class='m-r-10' title='".$locale['global_075']."' href='".$info['print_link']."'><i class='entypo print'></i></a>";
			if (!empty($info['admin_actions'])) {
				$admin_actions = $info['admin_actions'];
				echo "<a title='".$locale['global_076']."' href='".$admin_actions['edit']."' title='".$locale['global_076']."' />".$locale['global_076']."</a>\n";
			}
			echo "</div>\n";
			echo "</article>\n";
		} else {
			echo "<!--news_prepost_".$info['news_id']."-->\n";
			echo "<article class='panel panel-default' style='min-height:290px'>\n";
			echo "<div class='overflow-hide news-img-header' style='height: ".$news_settings['news_thumb_h']."px'>\n";
			add_to_jquery("
			$('.news-img-header').hover(
				function() { $(this).closest('.panel').find('.news-snippet').css({'opacity': 1, 'height': ".$news_settings['news_thumb_h']." }); },
				function() { $(this).closest('.panel').find('.news-snippet').css({'opacity': 0}); }
			);
			");
			echo $info['news_image'];
			echo "<a class='opacity-none transition news-snippet'
			href='".($news_settings['news_image_link'] == 0 ? INFUSIONS."news/news.php?cat_id=".$info['cat_id'] : INFUSIONS."news/news.php?readmore=".$info['news_id'])."'>
			".trim_text(strip_tags($info['news_news']), 120)."</a>\n";
			echo "</div>\n";
			echo "<div class='panel-body' ".(empty($info['news_image']) ? "style='min-height:221px;'" : "style='min-height:133px;'")." >\n";
			echo ($info['news_sticky']) ? "<i class='pull-right entypo ialert icon-sm'></i>\n" : '';
			echo "<h4 class='news-title panel-title'><a class='strong text-dark' href='".INFUSIONS."news/news.php?readmore=".$info['news_id']."' >".$info['news_subject']."</a></h4>\n";
			echo "<div class='news-text m-t-5' style='height:200px;'>".trim_text(strip_tags($info['news_news']), 250)."</div>\n";
			echo "<div class='news-date m-t-5'>".showdate("newsdate", $info['news_date'])."</div>\n";
			echo "<div class='news-category m-t-5'><span class='text-dark strong'>\n".ucwords($locale['in'])."</span> : ";
			echo $info['cat_name'] ? "<a href='".INFUSIONS."news/news.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>" : "<a href='".INFUSIONS."news/news.php?cat_id=0'>".$locale['global_080']."</a>&nbsp;";
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='news-footer panel-footer'>\n";
			echo "<span class='m-r-10'><i class='entypo eye'></i> ".number_format($info['news_reads'])."</span>";
			echo $info['news_allow_comments'] ? display_comments($info['news_comments'], INFUSIONS."news/news.php?readmore=".$info['news_id']."#comments", '', 2) : '';
			echo $info['news_allow_ratings'] ? "".display_ratings($info['news_sum_rating'], $info['news_count_votes'], INFUSIONS."news/news.php?readmore=".$info['news_id']."#postrating", '', 2)."" : '';
			echo "<a title='".$locale['global_075']."' href='".$info['print_link']."'><i class='entypo print'></i></a>";
			if (!empty($info['admin_actions'])) {
				$admin_actions = $info['admin_actions'];
				echo "<a class='pull-right' title='".$locale['global_076']."' href='".$admin_actions['edit']."' title='".$locale['global_076']."' /><i class='entypo pencil'></i></a>\n";
			}
			echo "</div>\n";
			echo "</article>\n";
		}
	}
}
if (!function_exists('render_news_item')) {
	/**
	 * News Item Page Template
	 * @param $info
	 */
	function render_news_item($info) {
		global $locale, $news_settings, $aidlink;
		$data = $info['news_item'];
        add_to_head("<link rel='stylesheet' href='".INFUSIONS."news/templates/css/news.css' type='text/css'>");
        add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
		add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
		add_to_footer('<script type="text/javascript">
			$(document).ready(function() {
				$(".news-image-overlay").colorbox({
					transition: "elasic",
					height:"100%",
					width:"100%",
					maxWidth:"98%",
					maxHeight:"98%",
					scrolling:false,
					overlayClose:true,
					close:false,
					photo:true,
					onComplete: function(result) {
						$("#colorbox").live("click", function(){
						$(this).unbind("click");
						$.fn.colorbox.close();
						});
					},
					onLoad: function () {
					}
			   });
			});
			</script>');
		echo "<!--news_pre_readmore-->";
		echo "<article class='news-item' style='display:block; width:100%; overflow:hidden;'>\n";
		echo "<h2 class='text-center'>".$data['news_subject']."</h2>\n";
		echo "<div class='news_news text-dark m-t-20 m-b-20'>\n";
		if ($data['news_image']) {
			echo "<a class='".$data['news_ialign']." news-image-overlay' href='".IMAGES_N.$data['news_image']."'><img class='img-responsive' src='".IMAGES_N.$data['news_image']."' alt='".$data['news_subject']."' style='padding:5px; max-height:".$news_settings['news_photo_h']."; overflow:hidden;' /></a>";
		} elseif ($data['cat_name']) {
			echo "<a class='".$data['news_ialign']."' href='".INFUSIONS."news/news.php?cat_id=".$data['cat_id']."'><img class='img-responsive' src='".IMAGES_NC.$data['cat_image']."' style='padding:5px; max-height:".$news_settings['news_photo_h']."; alt='".$data['cat_name']."' /></a>";
		}
		echo $data['news_news'];
		echo "</div>\n";
		echo "<div style='clear:both;'></div>\n";
		echo "<div class='well m-t-5 text-center'>\n";
		echo "<span class='news-action m-r-10'><i class='entypo user'></i>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</span>\n";
		echo "<span class='news-action m-r-10'><i class='entypo calendar'></i>".showdate("newsdate", $data['news_date'])."</span>\n";
		echo "<span class='news-action'><i class='entypo eye'></i><span class='text-dark m-r-10'>".number_format($data['news_reads'])."</span>\n</span>";
		echo $data['news_allow_comments'] ? display_comments($data['news_comments'], INFUSIONS."news/news.php?readmore=".$data['news_id']."#comments") : '';
        echo $data['news_allow_ratings'] ? "<span class='m-r-10'>".display_ratings($data['news_sum_rating'], $data['news_count_votes'], INFUSIONS."news/news.php?readmore=".$data['news_id']."#ratings")." </span>" : '';
		echo "<a class='m-r-10' title='".$locale['global_075']."' href='".BASEDIR."print.php?type=N&amp;item_id=".$data['news_id']."'><i class='entypo print'></i></a>";
		echo iADMIN && checkrights("N") ? "<a title='".$locale['global_076']."' href='".INFUSIONS."news/news_admin.php".$aidlink."&amp;action=edit&amp;section=news_form&amp;news_id=".$data['news_id']."' title='".$locale['global_076']."' />".$locale['global_076']."</a>\n" : "";
		echo "</div>";
		echo "<!--news_sub_readmore-->";
		echo !isset($_GET['readmore']) && $data['news_ext'] == "y" ? "<div class='m-t-20'>\n<a href='".INFUSIONS."news/news.php?readmore=".$data['news_id']."' class='button'>".$locale['global_072']."</a>\n</div>\n" : "";
		if ($data['page_count'] > 0) {
			echo "<div class='text-center m-t-10'>\n".makepagenav($_GET['rowstart'], 1, $data['page_count'], 3, INFUSIONS."news/news.php?readmore=".$_GET['readmore']."&amp;")."\n</div>\n";
		}
		if ($data['news_allow_comments']) {
			showcomments("N", DB_NEWS, "news_id", $_GET['readmore'], INFUSIONS."news/news.php?readmore=".$_GET['readmore']);
		}
		if ($data['news_allow_ratings']) {
			showratings("N", $_GET['readmore'], INFUSIONS."news/news.php?readmore=".$_GET['readmore']);
		}
		echo "</article>\n";
	}
}
