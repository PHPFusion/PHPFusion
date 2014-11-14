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
if (!function_exists('render_main_news')) {

	function render_main_news($info) {
		global $userdata, $settings, $locale;
		add_to_head("<link href='".THEMES."templates/global/css/news.css' rel='stylesheet'/>\n");
		add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>");
		if (isset($_POST['switchview'])) {
			add_to_jquery("$.cookie('fusion_news_view', '".$_POST['switchview']."', {expires: 7});");
		}
		opentable($locale['global_077']);

		/* Title Panel */
		if (!isset($_GET['readmore'])) {

			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-body'>\n";
			echo "<div class='pull-right'>\n";
			echo "<a class='btn btn-sm btn-default text-dark' href='".BASEDIR."news.php'><i class='entypo newspaper'></i> ".$locale['global_082']."</a>\n";
			echo "<button type='button' class='btn btn-sm btn-primary' data-toggle='collapse' data-target='#newscat' aria-expanded='true' aria-controls='newscat'><i class='entypo book open'></i> ".$locale['global_084']."</button>\n";
			echo "</div>\n";
			echo "<div class='pull-left m-r-10' style='position:relative; margin-top:-30px;'>\n";
			echo "<div style='max-width:80px;'>\n";
			echo $info['news_cat_image'];
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo "<h3 class='display-inline text-dark'>".$info['news_cat_name']."</h3><br/><span class='strong'>".$locale['global_083'].":</span> <span class='text-dark'>".showdate('newsdate', $info['news_last_updated'])."</span>";
			echo "</div>\n";
			echo "</div>\n";
			echo "<div id='newscat' class='panel-collapse collapse m-b-10'>\n";
			echo "<ul class='list-group'>\n";
			echo "<li class='list-group-item'><hr class='m-t-0 m-b-5'>\n";
			echo "<span class='display-inline-block m-b-10 strong text-smaller text-uppercase'> ".$locale['global_085']."</span><br/>\n";
			foreach ($info['news_categories'] as $cat_id => $cat_name) {
				echo isset($_GET['cat_id']) && $_GET['cat_id'] == $cat_id ? '' : "<a href='".BASEDIR."news.php?cat_id=".$cat_id."' class='btn btn-sm btn-default'>".$cat_name."</a>";
			}
			echo "</li>";
			echo "</ul>\n";
			echo "</div>\n</div>\n";
		}

		echo render_breadcrumbs();

		$carousel_indicators = '';
		$carousel_item = '';
		$res = 0;

		if (!empty($info['news_items'])) {
			$i = 0;
			foreach ($info['news_items'] as $news_item) {
				if ($news_item['news_image_src']) {
					$carousel_active = $res == 0 ? 'active' : ''; // defunct
					$res++;
					$carousel_indicators .= "<li data-target='#news-slideshow' data-slide-to='$i' class='".$carousel_active."'></li>\n";
					$carousel_item .= "
					<div class='item ".$carousel_active."'>
						<img src='".$news_item['news_image_src']."' alt='".$news_item['news_subject']."'>
						<div class='carousel-caption clearfix'>
							<div class='pull-left m-r-10'>".display_avatar($news_item, '50px', '', '', '')."</div>
							<div class='overflow-hide'>".profile_link($news_item['user_id'], $news_item['user_name'], $news_item['user_status'])." - ".showdate('newsdate', $news_item['news_date'])."<br/>
							<a class='text-white' href='".BASEDIR."news.php?readmore=".$news_item['news_id']."'><h4 class='text-white m-t-10'>".$news_item['news_subject']."</h4></a>\n
							<span class='news-carousel-action m-r-10'><i class='entypo eye'></i> ".$news_item['news_reads']."</span>
							".($news_item['news_allow_comments'] ? "<span class='m-r-10'>".display_comments($news_item['news_comments'], BASEDIR."news.php?readmore=".$news_item['news_id']."#comments")."</span>" : '')."
							".($news_item['news_allow_ratings'] ? "<span class='m-r-10'>".display_ratings($news_item['news_sum_rating'], $news_item['news_count_votes'], BASEDIR."news.php?readmore=".$news_item['news_id']."#postrating")." </span>" : '')."
							</div>\n
					</div>\n</div>\n
					";
				}
				$i++;
			}
		}
		/* Slideshow */
		if (!isset($_GET['readmore']) && ($res)) {
			echo "<div id='news-slideshow' class='m-t-20 carousel slide'  data-interval='20000' data-ride='carousel'>\n";
			if ($res > 1) {
				echo "<ol class='carousel-indicators'>\n";
				echo $carousel_indicators;
				echo "</ol>";
			}
			echo "<div class='carousel-inner' role='listbox'>\n";
			echo $carousel_item;
			echo "</div>\n";
			echo "</div>\n";
		}
		if (!isset($_GET['readmore'])) {
			echo "<div class='row m-b-20 m-t-20'>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
			echo openform('viewform', 'viewform', 'post', FUSION_REQUEST, array('downtime' => 0,
				'class' => 'pull-right display-inline-block m-l-10'));
			echo "<div class='btn-group'>\n";
			$active = (isset($_POST['switchview'])) ? $_POST['switchview'] : $_COOKIE['fusion_news_view'];
			echo form_button('', 'switchview', 'switch-vw1', '1', array('class' => "btn-sm btn-default nsv ".($active == 1 ? 'active' : '')." ",
				'icon' => 'entypo layout',
				'alt' => 'Thumb View'));
			echo form_button('', 'switchview', 'switch-vw2', '2', array('class' => "btn-sm btn-default nsv ".($active == 2 ? 'active' : '')."",
				'icon' => 'entypo menu',
				'alt' => 'List View'));
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
			echo "</div>\n"; // end filter.
			echo "</div>\n</div>\n";
			$news_span = $active == 2 ? 12 : 4;
			if (!empty($info['news_items'])) {
				echo "<div class='row'>\n";
				foreach ($info['news_items'] as $i => $news_info) {
					echo "<div class='col-xs-12 col-sm-$news_span col-md-$news_span col-lg-$news_span'>\n";
					echo "<!--news_prepost_".$i."-->\n";
					$subject_news = $news_info['news_subject'];
					$news = $news_info['news_news'];
					render_news($subject_news, $news, $news_info, $active == 2 ? 1 : 0);
					echo "<!--sub_news_idx-->\n";
					echo "</div>\n";
				}
				echo "</div>\n";
				if ($info['news_item_rows'] > $settings['newsperpage']) {
					$type_start = isset($_GET['type']) ? "type=".$_GET['type']."&amp;" : '';
					$cat_start = isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '';
					echo "<div class='text-center m-t-10 m-b-10'>".makepagenav($_GET['rowstart'], $settings['newsperpage'], $info['news_item_rows'], 3, BASEDIR."news.php?".$cat_start.$type_start)."</div>\n";
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
	function render_news($subject, $news, $info, $list_view = FALSE) {
		global $locale, $settings, $aidlink;
		$parameter = $settings['siteurl']."news.php?readmore=".$info['news_id'];
		$title = $settings['sitename'].$locale['global_200'].$locale['global_077'].$locale['global_201'].$info['news_subject']."".$locale['global_200'];
		if ($list_view) {
			echo "<article class='panel panel-default'>\n";
			echo "<div class='pull-left m-r-10'>\n";
			echo display_avatar($info, '70px', '', '', '');
			echo "</div>\n";
			echo "<div class='overflow-hide news-profile-link'>\n";
			echo "<div class='m-t-10 m-b-10'>\n".profile_link($info['user_id'], $info['user_name'], $info['user_status'], 'strong')." ".getuserlevel($info['user_level'])." </div>\n";
			echo ($info['news_sticky']) ? "<i class='pull-right entypo ialert icon-sm'></i>\n" : '';
			if ($info['news_image']) {
				echo "<div class='pull-left m-r-10' style='width:100px;'>\n";
				echo $info['news_image'];
				echo "</div>\n";
				echo "<div class='overflow-hide'>\n";
			}
			echo "<h4 class='news-title panel-title'><a class='strong text-dark' href='".BASEDIR."news.php?readmore=".$info['news_id']."' >".$info['news_subject']."</a></h4>\n";
			echo "<div class='m-t-10'><span class='news-date'>".showdate($settings['newsdate'], $info['news_date'])." -- </span>\n";
			echo "<span class='news-text m-t-10'>".$info['news_news']."</span>\n</div>";
			echo "<div class='news-category m-t-10'><span class='text-dark strong'>\n".ucwords($locale['in'])."</span> : ";
			echo $info['cat_id'] ? "<a href='".BASEDIR."news.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>" : "<a href='".BASEDIR."news.php?cat_id=0'>".$locale['global_080']."</a>&nbsp;";
			echo "</div>\n";
			if ($info['news_image']) {
				echo "</div>\n";
			}
			echo "<div class='news-footer ".($info['news_image'] ? "m-t-20" : '')." p-15 p-l-0'>\n";
			echo "<span class='m-r-10'><i class='entypo eye'></i> ".number_format($info['news_reads'])."</span>";
			echo $info['news_allow_comments'] ? display_comments($info['news_comments'], BASEDIR."news.php?readmore=".$info['news_id']."#comments") : '';
			echo $info['news_allow_ratings'] ? display_ratings($info['news_sum_rating'], $info['news_count_votes'], BASEDIR."news.php?readmore=".$info['news_id']."#postrating") : '';
			echo "<a class='m-l-10 m-r-10' title='".$locale['global_075']."' href='".BASEDIR."print.php?type=N&amp;item_id=".$info['news_id']."'><i class='entypo print'></i></a>";
			echo iADMIN && checkrights("N") ? "<a title='".$locale['global_076']."' href='".ADMIN."news.php".$aidlink."&amp;action=edit&amp;news_id=".$info['news_id']."' title='".$locale['global_076']."' />".$locale['global_076']."</a>\n" : "";
			echo "</div>\n";
			echo "</article>\n";
		} else {
			echo "<article class='panel panel-default' style='min-height:370px'>\n";
			if ($info['news_image']) {
				echo "<div class='overflow-hide news-img-header'>\n";
				echo $info['news_image'];
				echo "<a class='opacity-none transition news-snippet' href='".($settings['news_image_link'] == 0 ? "news.php?cat_id=".$info['cat_id'] : FUSION_SELF."?readmore=".$info['news_id'])."'>
				".trim_word($info['news_news'], 20)."</a>\n";
				add_to_jquery("
				$('.news-img-header').hover(
					function() { $(this).closest('.panel').find('.news-snippet').css({'opacity': 1}); },
					function() { $(this).closest('.panel').find('.news-snippet').css({'opacity': 0}); }
				);
				");
				echo "</div>\n";
				echo "<div class='m-b-10'>\n";
				echo "<div class='pull-left m-r-10 news-avatar'>\n";
				echo display_avatar($info, '50px', '', '', '');
				echo "</div>\n";
				echo "<div class='overflow-hide'>\n";
				echo "<div class='m-t-5'>\n".profile_link($info['user_id'], $info['user_name'], $info['user_status'], 'strong')."</div>\n";
				echo getuserlevel($info['user_level']);
				echo "</div>\n";
				echo "</div>\n";
			} else {
				echo "<div>\n";
				echo "<div class='pull-left m-r-10'>\n";
				echo display_avatar($info, '70px', '', '', '');
				echo "</div>\n";
				echo "<div class='overflow-hide news-profile-link'>\n";
				echo "<div class='m-t-10'>\n".profile_link($info['user_id'], $info['user_name'], $info['user_status'], 'strong')."</div>\n";
				echo getuserlevel($info['user_level']);
				echo "</div>\n";
				echo "</div>\n";
			}
			echo "<div class='panel-body' ".(empty($info['news_image']) ? "style='min-height:255px;'" : "style='min-height:120px;'")." >\n";
			echo ($info['news_sticky']) ? "<i class='pull-right entypo ialert icon-sm'></i>\n" : '';
			echo "<h4 class='news-title panel-title'><a class='strong text-dark' href='".BASEDIR."news.php?readmore=".$info['news_id']."' >".$info['news_subject']."</a></h4>\n";
			echo "<div class='news-date m-t-10'>".showdate($settings['newsdate'], $info['news_date'])."</div>\n";
			echo empty($info['news_image']) ? "<div class='news-text m-t-10'>".$info['news_news']."</div>\n" : '';
			echo "<div class='news-category m-t-10'><span class='text-dark strong'>\n".ucwords($locale['in'])."</span> : ";
			echo $info['cat_id'] ? "<a href='".BASEDIR."news.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>" : "<a href='".BASEDIR."news.php?cat_id=0'>".$locale['global_080']."</a>&nbsp;";
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='news-footer panel-footer'>\n";
			echo "<span class='m-r-10'><i class='entypo eye'></i> ".number_format($info['news_reads'])."</span>";
			echo $info['news_allow_comments'] ? display_comments($info['news_comments'], BASEDIR."news.php?readmore=".$info['news_id']."#comments", '', 2) : '';
			echo $info['news_allow_ratings'] ? "".display_ratings($info['news_sum_rating'], $info['news_count_votes'], BASEDIR."news.php?readmore=".$info['news_id']."#postrating", '', 2)."" : '';
			echo "<a class='m-l-10' title='".$locale['global_075']."' href='".BASEDIR."print.php?type=N&amp;item_id=".$info['news_id']."'><i class='entypo print'></i></a>";
			echo iADMIN && checkrights("N") ? "<a class='pull-right' title='".$locale['global_076']."' href='".ADMIN."news.php".$aidlink."&amp;action=edit&amp;news_id=".$info['news_id']."' title='".$locale['global_076']."' />".$locale['global_076']."</a>\n" : "";
			echo "</div>\n";
			echo "</article>\n";
		}
	}
}
if (!function_exists('render_news_item')) {
	function render_news_item($info) {
		global $locale, $settings;
		$data = $info['news_item'];
		echo "<!--news_pre_readmore-->";
		echo "<article class='news-item'>\n";
		echo "<div class='display-block text-center'  style='margin-top:10px; max-height:".$settings['news_photo_h']."; display:block; width:100%; overflow:hidden;'>\n";
		echo $data['news_image'] ? $data['news_image'] : $data['cat_image'];
		echo "</div>\n";
		echo "<div class='news-subheader' style='margin-top:-40px; position:relative; z-index:1;'>\n";
		echo "<div class='text-center'>\n";
		echo display_avatar($data, '80px');
		echo "<h4 class='text-dark text-normal news-poster-name m-b-0'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</h4>\n";
		echo getuserlevel($data['user_level']);
		echo "</div>\n";
		echo "</div>\n";
		echo "<h2 class='text-center'>".$data['news_subject']."</h2>\n";
		echo "<div class='m-t-10 text-center'>\n";
		echo "<span class='news-action m-r-10'>".showdate($settings['newsdate'], $data['news_date'])."</span>\n";
		echo "<span class='news-action'> <i class='entypo eye'></i> <span class='text-dark m-r-10'>".number_format($data['news_reads'])."</span>\n</span>";
		echo $data['news_allow_comments'] ? display_comments($data['news_comments'], BASEDIR."news.php?readmore=".$data['news_id']."#comments") : '';
		echo $data['news_allow_ratings'] ? "<span class='m-r-10'>".display_ratings($data['news_sum_rating'], $data['news_count_votes'], BASEDIR."news.php?readmore=".$data['news_id']."#postrating")." </span>" : '';
		echo "</div>";
		echo "<div class='news_news text-dark m-t-20 m-b-20'>\n";
		echo $data['news_news'];
		echo "</div>\n";
		echo "<!--news_sub_readmore-->";
		echo !isset($_GET['readmore']) && $data['news_ext'] == "y" ? "<div class='m-t-20'>\n<a href='".BASEDIR."news.php?readmore=".$data['news_id']."' class='button'>".$locale['global_072']."</a>\n</div>\n" : "";
		if ($data['page_count'] > 1) {
			echo "<div class='text-center m-t-10'>\n".makepagenav($_GET['rowstart'], 1, $data['page_count'], 3, BASEDIR."news.php?readmore=".$_GET['readmore']."&amp;")."\n</div>\n";
		}
		if ($data['news_allow_comments']) {
			showcomments("N", DB_NEWS, "news_id", $_GET['readmore'], BASEDIR."news.php?readmore=".$_GET['readmore']);
		}
		if ($data['news_allow_ratings']) {
			showratings("N", $_GET['readmore'], BASEDIR."news.php?readmore=".$_GET['readmore']);
		}
		echo "</article>\n";
	}
}



?>