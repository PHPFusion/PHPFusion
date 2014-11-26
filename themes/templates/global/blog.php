<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog.php
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
if (!function_exists('render_main_blog')) {

	function render_main_blog($info) {
		global $userdata, $settings, $locale;
		add_to_head("<link href='".THEMES."templates/global/css/blog.css' rel='stylesheet'/>\n");
		add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>");
		if (isset($_POST['switchview'])) {
			add_to_jquery("$.cookie('fusion_blog_view', '".$_POST['switchview']."', {expires: 7});");
			$_COOKIE['fusion_blog_view'] = $_POST['switchview'];
		}
		opentable($locale['global_077b']);
		echo render_breadcrumbs();
		/* Slideshow */
		$carousel_indicators = '';
		$carousel_item = '';
		$res = 0;
		if (!empty($info['blog_items'])) {
			$i = 0;
			foreach ($info['blog_items'] as $blog_item) {
				if ($blog_item['blog_image_src']) {
					$carousel_active = $res == 0 ? 'active' : ''; // defunct
					$res++;
					$carousel_indicators .= "<li data-target='#blog-slideshow' data-slide-to='$i' class='".$carousel_active."'></li>\n";
					$carousel_item .= "
					<div class='item ".$carousel_active."'>
						<img src='".$blog_item['blog_image_src']."' alt='".$blog_item['blog_subject']."'>
						<div class='carousel-caption clearfix'>
							<div class='pull-left m-r-10'>".display_avatar($blog_item, '50px', '', '', '')."</div>
							<div class='overflow-hide'>".profile_link($blog_item['user_id'], $blog_item['user_name'], $blog_item['user_status'])." - ".showdate('blogdate', $blog_item['blog_date'])."<br/>
							<a class='text-white' href='".BASEDIR."blog.php?readmore=".$blog_item['blog_id']."'><h4 class='text-white m-t-10'>".$blog_item['blog_subject']."</h4></a>\n
							<span class='blog-carousel-action m-r-10'><i class='entypo eye'></i> ".$blog_item['blog_reads']."</span>
							".($blog_item['blog_allow_comments'] ? "<span class='m-r-10'>".display_comments($blog_item['blog_comments'], BASEDIR."blog.php?readmore=".$blog_item['blog_id']."#comments")."</span>" : '')."
							".($blog_item['blog_allow_ratings'] ? "<span class='m-r-10'>".display_ratings($blog_item['blog_sum_rating'], $blog_item['blog_count_votes'], BASEDIR."blog.php?readmore=".$blog_item['blog_id']."#postrating")." </span>" : '')."
							</div>\n
					</div>\n</div>\n
					";
				$i++;
			    }
		    }
		}
		if (!isset($_GET['readmore']) && ($res)) {
			echo "<div id='blog-slideshow' class='carousel slide'  data-interval='20000' data-ride='carousel'>\n";
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
		/* Title Panel */
		if (!isset($_GET['readmore'])) {

			echo "<div class='panel panel-default panel-blog-header'>\n";
			echo "<div class='panel-body'>\n";
			echo "<div class='pull-right'>\n";
			echo "<a class='btn btn-sm btn-default text-dark' href='".BASEDIR."blog.php'><i class='entypo newspaper'></i> ".$locale['global_082b']."</a>\n";
			echo "<button type='button' class='btn btn-sm btn-primary' data-toggle='collapse' data-target='#blogcat' aria-expanded='true' aria-controls='blogcat'><i class='entypo book open'></i> ".$locale['global_084b']."</button>\n";
			echo "</div>\n";
			echo "<div class='pull-left m-r-10' style='position:relative; margin-top:-30px;'>\n";
			echo "<div style='max-width:80px;'>\n";
			echo $info['blog_cat_image'];
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo "<h3 class='display-inline text-dark'>".$info['blog_cat_name']."</h3><br/><span class='strong'>".$locale['global_083'].":</span> <span class='text-dark'>".showdate('blogdate', $info['blog_last_updated'])."</span>";
			echo "</div>\n";
			echo "</div>\n";
			echo "<div id='blogcat' class='panel-collapse collapse m-b-10'>\n";
			echo "<ul class='list-group'>\n";
			echo "<li class='list-group-item'><hr class='m-t-0 m-b-5'>\n";
			echo "<span class='display-inline-block m-b-10 strong text-smaller text-uppercase'> ".$locale['global_085']."</span><br/>\n";
			foreach ($info['blog_categories'] as $cat_id => $cat_name) {
				echo isset($_GET['cat_id']) && $_GET['cat_id'] == $cat_id ? '' : "<a href='".BASEDIR."blog.php?cat_id=".$cat_id."' class='btn btn-sm btn-default'>".$cat_name."</a>";
			}
			echo "</li>";
			echo "</ul>\n";
			echo "</div>\n</div>\n";
		}

		if (!isset($_GET['readmore'])) {
			echo "<div class='row m-b-20 m-t-20'>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
			echo openform('viewform', 'viewform', 'post', FUSION_REQUEST, array('downtime' => 0,
				'class' => 'pull-right display-inline-block m-l-10'));
			echo "<div class='btn-group'>\n";
			$active = isset($_COOKIE['fusion_blog_view']) ? $_COOKIE['fusion_blog_view'] : '';
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
			foreach ($info['blog_filter'] as $link => $title) {
				$filter_active = (!isset($_GET['type']) && $i == '0') || isset($_GET['type']) && stristr($link, $_GET['type']) ? 'text-dark strong' : '';
				echo "<a href='".$link."' class='display-inline $filter_active m-r-10'>".$title."</a>";
				$i++;
			}
			echo "</div>\n"; // end filter.
			echo "</div>\n</div>\n";
			$blog_span = $active == 2 ? 12 : 4;
			if (!empty($info['blog_items'])) {
				echo "<div class='row'>\n";
				foreach ($info['blog_items'] as $i => $blog_info) {
					echo "<div class='col-xs-12 col-sm-$blog_span col-md-$blog_span col-lg-$blog_span'>\n";
					echo "<!--blog_prepost_".$i."-->\n";
					$subject_blog = $blog_info['blog_subject'];
					$blog = $blog_info['blog_blog'];
					render_blog($subject_blog, $blog, $blog_info, $active == 2 ? 1 : 0);
					echo "<!--sub_blog_idx-->\n";
					echo "</div>\n";
				}
				echo "</div>\n";

				if ($info['blog_item_rows'] > $settings['blogperpage']) {
					$type_start = isset($_GET['type']) ? "type=".$_GET['type']."&amp;" : '';
					$cat_start = isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '';
					echo "<div class='text-center m-t-10 m-b-10'>".makepagenav($_GET['rowstart'], $settings['blogperpage'], $info['blog_item_rows'], 3, BASEDIR."blog.php".$cat_start.$type_start)."</div>\n";
				}

			} else {
				echo "<div class='well text-center'>".$locale['global_078b']."</div>\n";
			}
		} else {
			render_blog_item($info);
		}

		closetable();
	}
}
if (!function_exists('render_blog')) {
	function render_blog($subject, $blog, $info, $list_view = FALSE) {
		global $locale, $settings, $aidlink;
		$parameter = $settings['siteurl']."blog.php?readmore=".$info['blog_id'];
		$title = $settings['sitename'].$locale['global_200'].$locale['global_077'].$locale['global_201'].$info['blog_subject']."".$locale['global_200'];
		if ($list_view) {
			echo "<article class='panel panel-default'>\n";
			echo "<div class='pull-left m-r-10'>\n";
			echo display_avatar($info, '70px', '', '', '');
			echo "</div>\n";
			echo "<div class='overflow-hide blog-profile-link'>\n";
			echo "<div class='m-t-10 m-b-10'>\n".profile_link($info['user_id'], $info['user_name'], $info['user_status'], 'strong')." ".getuserlevel($info['user_level'])." </div>\n";
			echo ($info['blog_sticky']) ? "<i class='pull-right entypo ialert icon-sm'></i>\n" : '';
			if ($info['blog_image']) {
				echo "<div class='pull-left m-r-10' style='width:100px;'>\n";
				echo $info['blog_image'];
				echo "</div>\n";
				echo "<div class='overflow-hide'>\n";
			}
			echo "<h4 class='blog-title panel-title'><a class='strong text-dark' href='".BASEDIR."blog.php?readmore=".$info['blog_id']."' >".$info['blog_subject']."</a></h4>\n";
			echo "<div class='m-t-10'><span class='blog-date'>".showdate($settings['blogdate'], $info['blog_date'])." -- </span>\n";
			echo "<span class='blog-text m-t-10'>".$info['blog_blog']."</span>\n</div>";
			echo "<div class='blog-category m-t-10'><span class='text-dark strong'>\n".ucwords($locale['in'])."</span> : ";
			echo $info['cat_name'] ? "<a href='".BASEDIR."blog.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>" : "<a href='".BASEDIR."blog.php?cat_id=0'>".$locale['global_080']."</a>&nbsp;";
			echo "</div>\n";
			if ($info['blog_image']) {
				echo "</div>\n";
			}
			echo "<div class='blog-footer ".($info['blog_image'] ? "m-t-20" : '')." p-15 p-l-0'>\n";
			echo "<span><i class='entypo eye'></i> ".number_format($info['blog_reads'])."</span>";
			echo $info['blog_allow_comments'] ? display_comments($info['blog_comments'], BASEDIR."blog.php?readmore=".$info['blog_id']."#comments") : '';
			echo $info['blog_allow_ratings'] ? display_ratings($info['blog_sum_rating'], $info['blog_count_votes'], BASEDIR."blog.php?readmore=".$info['blog_id']."#postrating") : '';
			echo "<a class='m-r-10' title='".$locale['global_075']."' href='".BASEDIR."print.php?type=N&amp;item_id=".$info['blog_id']."'><i class='entypo print'></i></a>";
			echo iADMIN && checkrights("N") ? "<a title='".$locale['global_076']."' href='".ADMIN."blog.php".$aidlink."&amp;action=edit&amp;blog_id=".$info['blog_id']."' title='".$locale['global_076']."' />".$locale['global_076']."</a>\n" : "";
			echo "</div>\n";
			echo "</article>\n";
		} else {
			echo "<article class='panel panel-default' style='min-height:395px'>\n";
			if ($info['blog_image']) {
				echo "<div class='overflow-hide blog-img-header'>\n";
				echo $info['blog_image'];
				echo "<a class='opacity-none transition blog-snippet' href='".($settings['blog_image_link'] == 0 ? "blog.php?cat_id=".$info['cat_id'] : FUSION_SELF."?readmore=".$info['blog_id'])."'>".trim_word($info['blog_blog'], 20)."</a>\n";
				add_to_jquery("
				$('.blog-img-header').hover(
					function() { $(this).closest('.panel').find('.blog-snippet').css({'opacity': 1}); },
					function() { $(this).closest('.panel').find('.blog-snippet').css({'opacity': 0}); }
				);
				");
				echo "</div>\n";
				echo "<div class='m-b-10'>\n";
				echo "<div class='pull-left m-r-10 blog-avatar'>\n";
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
				echo "<div class='overflow-hide blog-profile-link'>\n";
				echo "<div class='m-t-10'>\n".profile_link($info['user_id'], $info['user_name'], $info['user_status'], 'strong')."</div>\n";
				echo getuserlevel($info['user_level']);
				echo "</div>\n";
				echo "</div>\n";
			}
			echo "<div class='panel-body' ".(empty($info['blog_image']) ? "style='min-height:221px;'" : "style='min-height:133px;'")." >\n";
			echo ($info['blog_sticky']) ? "<i class='pull-right entypo ialert icon-sm'></i>\n" : '';
			echo "<h4 class='blog-title panel-title'><a class='strong text-dark' href='".BASEDIR."blog.php?readmore=".$info['blog_id']."' >".$info['blog_subject']."</a></h4>\n";
			echo "<div class='blog-date m-t-5'>".showdate($settings['blogdate'], $info['blog_date'])."</div>\n";
			echo "<div class='blog-text m-t-5'>".trim_word($info['blog_blog'], 10)."</div>\n";
			echo "<div class='blog-category m-t-5'><span class='text-dark strong'>\n".ucwords($locale['in'])."</span> : ";
			echo $info['cat_name'] ? "<a href='".BASEDIR."blog.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>" : "<a href='".BASEDIR."blog.php?cat_id=0'>".$locale['global_080']."</a>&nbsp;";
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='blog-footer panel-footer'>\n";
			echo "<span class='m-r-10'><i class='entypo eye'></i> ".number_format($info['blog_reads'])."</span>";
			echo $info['blog_allow_comments'] ? display_comments($info['blog_comments'], BASEDIR."blog.php?readmore=".$info['blog_id']."#comments", '', 2) : '';
			echo $info['blog_allow_ratings'] ? "".display_ratings($info['blog_sum_rating'], $info['blog_count_votes'], BASEDIR."blog.php?readmore=".$info['blog_id']."#postrating", '', 2)."" : '';
			echo "<a title='".$locale['global_075']."' href='".BASEDIR."print.php?type=N&amp;item_id=".$info['blog_id']."'><i class='entypo print'></i></a>";
			echo iADMIN && checkrights("N") ? "<a class='pull-right' title='".$locale['global_076']."' href='".ADMIN."blog.php".$aidlink."&amp;action=edit&amp;blog_id=".$info['blog_id']."' title='".$locale['global_076']."' /><i class='entypo pencil'></i></a>\n" : "";
			echo "</div>\n";
			echo "</article>\n";
		}
	}
}
if (!function_exists('render_blog_item')) {
	function render_blog_item($info) {
		global $locale, $settings, $aidlink;

    add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
    add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");

      add_to_footer('<script type="text/javascript">
        $(document).ready(function() {

            $(".blog-image-overlay").colorbox({
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

		$data = $info['blog_item'];
		if ($data['blog_keywords'] !=="") { set_meta("keywords", $data['blog_keywords']); }
		echo "<!--blog_pre_readmore-->";
		echo "<article class='blog-item'>\n";
		echo "<h2 class='text-center'>".$data['blog_subject']."</h2>\n";
		echo "<div class='blog_blog text-dark m-t-20 m-b-20'>\n";
		if ($data['blog_image']) {
		echo "<a class='".$data['blog_ialign']." blog-image-overlay' href='".IMAGES_B.$data['blog_image']."'><img class='img-responsive' src='".IMAGES_B.$data['blog_image']."' alt='".$data['blog_subject']."' style='padding:5px; max-height:".$settings['blog_photo_h']."; overflow:hidden;' /></a>";
		} elseif ($data['cat_name']) {
		echo "<a class='".$data['blog_ialign']."' href='blog.php?cat_id=".$data['cat_id']."'><img class='img-responsive' src='".IMAGES_BC.$data['cat_image']."' style='padding:5px; max-height:".$settings['blog_photo_h']."; alt='".$data['cat_name']."' /></a>";
		}
		echo $data['blog_blog'];
		echo "</div>\n";
		echo "<div style='clear:both;'></div>\n";
		echo "<div class='well m-t-5 text-center'>\n";
		echo "<span class='blog-action m-r-10'><i class='entypo user'></i>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</span>\n";
		echo "<span class='blog-action m-r-10'><i class='entypo calendar'></i>".showdate($settings['blogdate'], $data['blog_date'])."</span>\n";
		echo "<span class='blog-action'><i class='entypo eye'></i><span class='text-dark m-r-10'>".number_format($data['blog_reads'])."</span>\n</span>";
		echo $data['blog_allow_comments'] ? display_comments($data['blog_comments'], BASEDIR."blog.php?readmore=".$data['blog_id']."#comments") : '';
		echo $data['blog_allow_ratings'] ? "<span class='m-r-10'>".display_ratings($data['blog_sum_rating'], $data['blog_count_votes'], BASEDIR."blog.php?readmore=".$data['blog_id']."#postrating")." </span>" : '';
		echo "<a class='m-r-10' title='".$locale['global_075']."' href='".BASEDIR."print.php?type=N&amp;item_id=".$data['blog_id']."'><i class='entypo print'></i></a>";
		echo iADMIN && checkrights("N") ? "<a title='".$locale['global_076']."' href='".ADMIN."blog.php".$aidlink."&amp;action=edit&amp;blog_id=".$data['blog_id']."' title='".$locale['global_076']."' />".$locale['global_076']."</a>\n" : "";
		echo "</div>";
		echo "<!--blog_sub_readmore-->";
		echo !isset($_GET['readmore']) && $data['blog_ext'] == "y" ? "<div class='m-t-20'>\n<a href='".BASEDIR."blog.php?readmore=".$data['blog_id']."' class='button'>".$locale['global_072']."</a>\n</div>\n" : "";
		if ($data['page_count'] > 0) {
			echo "<div class='text-center m-t-10'>\n".makepagenav($_GET['rowstart'], 1, $data['page_count'], 3, BASEDIR."blog.php?readmore=".$_GET['readmore']."&amp;")."\n</div>\n";
		}
		if ($data['blog_allow_comments']) {
			showcomments("N", DB_BLOG, "blog_id", $_GET['readmore'], BASEDIR."blog.php?readmore=".$_GET['readmore']);
		}
		if ($data['blog_allow_ratings']) {
			showratings("N", $_GET['readmore'], BASEDIR."blog.php?readmore=".$_GET['readmore']);
		}
		echo "</article>\n";
	}
}



?>