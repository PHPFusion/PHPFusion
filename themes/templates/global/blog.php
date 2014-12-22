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

		/* Title Panel */
		if (!isset($_GET['readmore'])) {

			echo "<div class='display-inline-block'>\n";
			echo "<span class='text-dark strong m-r-10'>".$locale['show']." :</span>";
			$i = 0;
			foreach ($info['blog_filter'] as $link => $title) {
				$filter_active = (!isset($_GET['type']) && $i == '0') || isset($_GET['type']) && stristr($link, $_GET['type']) ? 'text-dark strong' : '';
				echo "<a href='".$link."' class='display-inline $filter_active m-r-10'>".$title."</a>";
				$i++;
			}
			echo "</div>\n"; // end filter.

			if (isset($info['blog_items'])) {
				$i = 1;
				foreach($info['blog_items'] as $blog) {
					echo "<!--blog_prepost_".$i."-->\n";
					$subject_blog = $blog['blog_subject'];
					$blog_text = $blog['blog_blog'];
					render_blog($subject_blog, $blog_text, $blog);
					echo "<!--sub_blog_idx-->\n";
					$i++;
				}
				if ($info['blog_item_rows'] > $settings['blogperpage']) {
					$type_start = isset($_GET['type']) ? "type=".$_GET['type']."&amp;" : '';
					$cat_start = isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '';
					echo "<div class='text-center m-t-10 m-b-10'>".makepagenav($_GET['rowstart'], $settings['blogperpage'], $info['blog_item_rows'], 3, BASEDIR."blog.php".$cat_start.$type_start)."</div>\n";
				}
			}
			else {
				echo "<div class='well text-center'>".$locale['global_078b']."</div>\n";
			}

			// make a panel for blog and then push to left.
			/*
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
			echo "<h3 class='display-inline text-dark'>".$info['blog_cat_name']."</h3><br/><span class='strong'>".$locale['global_083'].":</span> <span class='text-dark'>".showdate('newsdate', $info['blog_last_updated'])."</span>";
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
			*/
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
		echo "<article class='blog_item'>\n";
		echo "<div class='blog_author pull-left m-t-10'>\n";
		echo display_avatar($info, '70px', '', '', 'img-circle');
		echo "<div class='blog_author_name'><i>".profile_link($info['user_id'], $info['user_name'], $info['user_status'], 'strong')."</i></div>\n";
		echo "</div>\n";
		echo "<div class='overflow-hide'>\n";
		echo "<h1 class='blog_title'><a class='text-dark' href='".BASEDIR."blog.php?readmore=".$info['blog_id']."'>".$subject."</a></h1>\n";
		echo "<div class='blog_info'>\n";
		$category = $info['cat_name'] ? " <a href='".BASEDIR."blog.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>" : " <a href='".BASEDIR."blog.php?cat_id=0'>".$locale['global_080']."</a>";
		echo "<span class='blog_info_item'>".showdate($settings['newsdate'], $info['blog_date'])." ".$locale['in'].$category."</span>\n";
		echo "<span class='blog_info_item'>".number_format($info['blog_reads'])." ".($info['blog_reads'] > 1 ? $locale['global_074'] : $locale['global_074b'])."</span>\n";
		echo $info['blog_allow_comments'] ? "<span class='blog_info_item'>".display_comments($info['blog_comments'], BASEDIR."blog.php?readmore=".$info['blog_id']."#comments")."</span>" : '';
		echo $info['blog_allow_ratings'] ? "<span class='blog_info_item'>".display_ratings($info['blog_sum_rating'], $info['blog_count_votes'], BASEDIR."blog.php?readmore=".$info['blog_id']."#postrating")."</span>" : '';
		echo "<span class='blog_info_item'><a class='m-r-10' title='".$locale['global_075']."' href='".BASEDIR."print.php?type=B&amp;item_id=".$info['blog_id']."'><i class='entypo print'></i></a></span>";
		echo iADMIN && checkrights("B") ? "<a title='".$locale['global_076']."' href='".ADMIN."blog.php".$aidlink."&amp;action=edit&amp;blog_id=".$info['blog_id']."' title='".$locale['global_076']."' />".$locale['global_076']."</a>\n" : "";
		echo "</div>\n";

		echo "<span class='blog_snippet m-t-10'>\n";
		if ($info['blog_image']) {
			echo "<div class='pull-left m-r-10' style='width:100px;'>\n";
			echo $info['blog_image'];
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n".$blog."</div>\n";
		} else {
			echo $blog;
		}
		echo "</span>\n";

		// readmore
		if ($info['blog_ext']) {
			echo "<span class='blog_readmore_link'><a class='text-dark' href='".BASEDIR."blog.php?readmore=".$info['blog_id']."' >".$locale['global_072']." <i class='entypo right-circled'></i> </a></span>\n";
		}
		echo "</div>\n";
		echo "</article>\n";

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
		echo "<div class='text-center'>\n";
		echo "<span class='blog_info_item'>".showdate($settings['newsdate'], $data['blog_date'])." ".$locale['in']." ".$data['cat_link']." </span>";
		echo "<span class='blog_info_item'>".number_format($data['blog_reads'])." ".($data['blog_reads'] >1 ? $locale['global_074'] : $locale['global_074b'])."</span>";
		echo "<span class='blog_info_item'><a title='".$locale['global_075']."' href='".BASEDIR."print.php?type=B&amp;item_id=".$data['blog_id']."'><i class='entypo print'></i> ".$locale['global_075']."</a></span>";
		if (iADMIN && checkrights("B")) echo  "<span class='blog_info_item'><a title='".$locale['global_076']."' href='".ADMIN."blog.php".$aidlink."&amp;action=edit&amp;blog_id=".$data['blog_id']."' title='".$locale['global_076']."' />".$locale['global_076']."</a>\n</span>";
		echo "</div>\n";
		echo "<hr>\n";
		echo "<div class='blog_blog text-dark m-t-20 m-b-20 clearfix'>\n";
		if ($data['blog_image']) {
		echo "<a class='".$data['blog_ialign']." m-r-20 m-b-20 blog-image-overlay' href='".IMAGES_B.$data['blog_image']."'><img class='img-responsive' src='".IMAGES_B.$data['blog_image']."' alt='".$data['blog_subject']."' style='padding:5px; max-height:".$settings['blog_photo_h']."; overflow:hidden;' /></a>";
		} elseif ($data['cat_name'] && $data['cat_image']) {
		echo "<a class='".$data['blog_ialign']." m-r-20 m-b-20' href='blog.php?cat_id=".$data['cat_id']."'><img class='img-responsive' src='".IMAGES_BC.$data['cat_image']."' style='padding:5px; max-height:".$settings['blog_photo_h']."; alt='".$data['cat_name']."' /></a>";
		}
		echo "<div class='overflow-hide'>".$data['blog_blog']."</div>\n";
		echo "</div>\n";

		// this part need some love.
		echo "<div class='blog_author_section clearfix'>\n";
		echo "<div class='pull-right m-t-20 m-l-20'>".display_avatar($data, '70px', '', '', 'img-circle')."</div>\n";
		echo "<div class='overflow-hide'>\n";
		echo "<span class='blog_author_info'>".$locale['global_070']." ".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</span><br/>\n";
		if ($data['user_level']) echo sprintf($locale['testimonial_rank'], $data['user_level']);
		if ($data['user_location']) echo sprintf($locale['testimonial_location'], $data['user_location']);
		if ($data['user_joined']) echo sprintf($locale['testimonial_location'], showdate('newsdate', $data['user_joined']));
		if ($data['user_web']) echo sprintf($locale['testimonial_web'], $data['user_web']);
		if ($data['user_contact']) echo sprintf($locale['testimonial_contact'], $data['user_contact']);
		if ($data['user_email']) echo sprintf($locale['testimonial_email'], $data['user_email']);
		echo "<div><a class='view_author_blog' href='".$data['blog_author_link']['link']."'>".$data['blog_author_link']['name']." <i class='entypo right-thin'></i></a></div>";
		echo "</div>\n";
		echo "</div>\n";
		// sub
		echo "<!--blog_sub_readmore-->";
		echo !isset($_GET['readmore']) && $data['blog_ext'] == "y" ? "<div class='m-t-20'>\n<a href='".BASEDIR."blog.php?readmore=".$data['blog_id']."' class='button'>".$locale['global_072']."</a>\n</div>\n" : "";
		if ($data['page_count'] > 0) {
			echo "<div class='text-center m-t-10'>\n".makepagenav($_GET['rowstart'], 1, $data['page_count'], 3, BASEDIR."blog.php?readmore=".$_GET['readmore']."&amp;")."\n</div>\n";
		}
		if ($data['blog_allow_comments']) {
			showcomments("B", DB_BLOG, "blog_id", $_GET['readmore'], BASEDIR."blog.php?readmore=".$_GET['readmore']);
		}
		if ($data['blog_allow_ratings']) {
			showratings("B", $_GET['readmore'], BASEDIR."blog.php?readmore=".$_GET['readmore']);
		}
		echo "</article>\n";
	}
}



?>