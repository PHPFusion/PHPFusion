<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog.php
| Author: Frederick MC Chan (Hien)
| Version: 9.00
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "maincore.php";
require_once THEMES."templates/header.php";
require_once THEMES."templates/global/blog.php";
include LOCALE.LOCALESET."blog/blog.php";

add_to_title($locale['blog_1000']);
add_to_breadcrumbs(array('link' => BASEDIR.'blog.php', 'title' => $locale['blog_1001'])); // blog needs to be localised
$_GET['cat_id'] = isset($_GET['cat_id']) && isnum($_GET['cat_id']) ? $_GET['cat_id'] : '';
$settings = fusion_get_settings();
$result = null;
/** @var $info */
$info = array(
	'blog_title' => $locale['blog_1000'],
	'blog_updated' => '',
	'blog_image' => '',
	'blog_language' => LANGUAGE,
	'blog_categories' => get_blogCatsData(),
	'blog_categories_index' => get_blogCatsIndex(),
	'allowed_filters' => array(
		'recent' => $locale['blog_2001'],
		'comment' => $locale['blog_2002'],
		'rating' => $locale['blog_2003']
	),
	'blog_last_updated' => 0,
	'blog_max_rows' => 0,
	'blog_rows' => 0,
	'blog_nav' => '',
);

/* Filter Construct */
$filter = array_keys($info['allowed_filters']);
$_GET['type'] = isset($_GET['type']) && in_array($_GET['type'], array_keys($info['allowed_filters'])) ? $_GET['type'] : '';

foreach ($info['allowed_filters'] as $type => $filter_name) {
	$filter_link = BASEDIR."blog.php?".($_GET['cat_id'] !=='' ? "cat_id=".$_GET['cat_id']."&amp;" : '').(isset($_GET['archive']) ? "archive=".$_GET['archive']."&amp;" : '')."type=".$type;
	$active = isset($_GET['type']) && $_GET['type'] == $type ? 1 : 0;
	$info['blog_filter'][$type] = array('title'=>$filter_name, 'link'=>$filter_link, 'active' => $active);
	unset($filter_link);
}

switch($_GET['type']) {
	case 'recent':
		$filter_condition = 'blog_datestamp DESC';
		break;
	case 'comment':
		$filter_condition = 'count_comment DESC';
		break;
	case 'rating':
		$filter_condition = 'sum_rating DESC';
		break;
	default:
		$filter_condition = 'blog_datestamp DESC';
}

if (isset($_GET['readmore']) && isnum($_GET['readmore'])) {

	if (validate_blog($_GET['readmore'])) {
		$result = dbquery("SELECT tn.*, tc.*, IF(tn.blog_cat = 0, '".$locale['global_080']."', blog_cat_name) as blog_cat_name, tu.*,
	 				SUM(tr.rating_vote) AS sum_rating,
					COUNT(tr.rating_item_id) AS count_votes,
					COUNT(td.comment_item_id) AS count_comment,
					tn.blog_datestamp as last_updated
					FROM ".DB_BLOG." tn
					LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
					LEFT JOIN ".DB_BLOG_CATS." tc ON tn.blog_cat=tc.blog_cat_id
					LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.blog_id AND tr.rating_type='B'
					LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.blog_id AND td.comment_type='B' AND td.comment_hidden='0'
					".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')." AND
					blog_id='".$_GET['readmore']."' AND blog_draft='0'
					GROUP BY blog_id
					");
		$info['blog_rows'] = dbrows($result);
		if ($info['blog_rows'] > 0) {
			include INCLUDES."comments_include.php";
			include INCLUDES."ratings_include.php";
			$item = dbarray($result);
			unset($item['user_password']);
			unset($item['user_algo']);
			unset($item['user_salt']);
			unset($item['user_admin_password']);
			unset($item['user_admin_algo']);
			unset($item['user_admin_salt']);
			/**
			 * Note of Default Behavior:
			 * 1. Blog_blog will not support page-break.
			 * 2. Blog_extended will support page-break.
			 * 3. Image is not available at page 2 and beyond. It must be inserted manually.
			 */
			$item['blog_blog'] = preg_replace("/<!?--\s*pagebreak\s*-->/i", "", ($item['blog_breaks'] ? nl2br(stripslashes($item['blog_blog'])) : stripslashes($item['blog_blog'])));
			$item['blog_extended'] = preg_split("/<!?--\s*pagebreak\s*-->/i", $item['blog_breaks'] ? nl2br(stripslashes($item['blog_extended'])) : stripslashes($item['blog_extended']));
			$item['blog_pagecount'] = 1;
			if (is_array($item['blog_extended'])) {
				$item['blog_pagecount'] = count($item['blog_extended']);
				$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= count($item['blog_extended']) ? $_GET['rowstart'] : 0;
				$item['blog_extended'] = $item['blog_extended'][$_GET['rowstart']];
			} else {
				$_GET['rowstart'] = 0; // validated result
			}

			/**
			 * Pre-parsed section
			 */
			if (!$_GET['rowstart']) {
				$hiRes_image_path = get_blog_image_path($item['blog_image'], $item['blog_image_t1'], $item['blog_image_t2'], true);
				$lowRes_image_path = get_blog_image_path($item['blog_image'], $item['blog_image_t1'], $item['blog_image_t2'], false);
				$item['blog_image'] = "<img class='img-responsive' src='".$hiRes_image_path."' alt='".$item['blog_subject']."' title='".$item['blog_subject']."'>";
				$item['blog_thumb_1'] = thumbnail($lowRes_image_path, '80px', $hiRes_image_path, true);
				$item['blog_thumb_2'] = thumbnail($hiRes_image_path, '100px', $hiRes_image_path, true);
			} else {
				$item['blog_blog'] = '';
				$item['blog_image'] = '';
				$item['blog_image_t1'] = '';
				$item['blog_image_t2'] = '';
			}

			$item['blog_post_author'] = display_avatar($item, '25px', '', true, 'img-rounded').profile_link($item['user_id'], $item['user_name'], $item['user_status']);
			$item['blog_post_cat'] = $locale['in']." <a href='".BASEDIR."blog.php?cat_id=".$item['blog_cat']."'>".$item['blog_cat_name']."</a>";
			$item['blog_post_time'] = $locale['global_049']." ".timer($item['blog_datestamp']);

			$user_contact = '';
			if (isset($item['user_skype']) && $item['user_skype']) {
				$user_contact .= "<strong>Skype:</strong> ".$item['user_skype'];
			}
			if (isset($item['user_aim']) && $item['user_aim']) {
				$user_contact .= "<strong>AIM:</strong> ".$item['user_aim'];
			}
			if (isset($item['user_yahoo']) && $item['user_yahoo']) {
				$user_contact .= "<strong>Yahoo:</strong> ".$item['user_yahoo']." , ";
			}
			if (isset($item['user_yahoo']) && $item['user_yahoo']) {
				$user_contact .= "<strong>YahooIM:</strong> ".$item['user_yahoo']." , ";
			}
			if (isset($item['user_yahoo']) && $item['user_yahoo']) {
				$user_contact .= "<strong>YahooIM:</strong> ".$item['user_yahoo']." , ";
			}
			if (isset($item['user_icq']) && $item['user_icq']) {
				$user_contact .= "<strong>ICQ:</strong> ".$item['user_icq'];
			}

			$item['blog_author_info'] = "<h4 class='blog_author_info'>".$locale['about']." ".profile_link($item['user_id'], $item['user_name'], $item['user_status'])."</h4>";
			$item['blog_author_info'] .= sprintf($locale['testimonial_rank'], getgroupname($item['user_level']));
			$item['blog_author_info'] .= (isset($item['user_location']) && $item['user_location'] !=='') ? sprintf($locale['testimonial_location'], $item['user_location']) : '. ';
			$item['blog_author_info'] .= (isset($item['user_web']) && $item['user_web']) ? sprintf($locale['testimonial_web'], $item['user_web']).". " : '';
			$item['blog_author_info'] .= (isset($item['user_contact']) && $item['user_contact'] !=='') ? sprintf($locale['testimonial_contact'], $user_contact).". " : '';
			$item['blog_author_info'] .= ($item['user_email'] && $item['user_hide_email'] == 0)  ? sprintf($locale['testimonial_email'], "<a href='mailto:".$item['user_email']."'>".$item['user_email']."</a>") : '';
			/* Admin link */
			$item['admin_link'] = '';
			if (iADMIN && checkrights('BLOG')) {
				$item['admin_link'] = array(
					'edit' => ADMIN."blog.php?".$aidlink."&amp;action=edit&amp;section=nform&amp;blog_id=".$item['blog_id'],
					'delete' => ADMIN."blog.php?".$aidlink."&amp;action=delete&amp;section=nform&amp;blog_id=".$item['blog_id'],
				);
			}
			$info['blog_title'] = $item['blog_subject'];
			$info['blog_updated'] = $locale['global_049']." ".timer($item['blog_datestamp']);
			if ($item['blog_pagecount'] > 1) {
				$info['blog_nav'] = makepagenav($_GET['rowstart'], 1, $item['blog_pagecount'], 3, BASEDIR."blog.php?readmore=".$_GET['readmore']."&amp;")."\n";
			}
			add_to_breadcrumbs(array('link' => BASEDIR."blog.php?readmore=".$_GET['readmore'], 'title' => $item['blog_subject']));
			add_to_title($item['blog_subject']);
			add_to_meta($item['blog_subject'].($item['blog_keywords'] ? ",".$item['blog_keywords'] : ''));
			$item['blog_subject'] = "<a class='text-dark' href='".BASEDIR."blog.php?readmore=".$item['blog_id']."'>".$item['blog_subject']."</a>";
			$info['blog_item'] = $item;
			update_blogReads($item['blog_id']);
		}
	} else {
		redirect(BASEDIR."blog.php");
	}
} else {
	// archive filter activation
	$condition = "";
	if (isset($_GET['archive']) && isnum($_GET['archive']) && stristr($_GET['archive'], '/')) {
		$date = explode('/', $_GET['archive']);
		$start_time = mktime('0', '0', '0', $date[1], 1, $date[0]);
		$end_time = mktime('0', '0', '0', $date[1]+1, 1, $date[0])-(3600*24);
		$condition = "AND blog_datestamp >= '".intval($start_time)."' AND blog_datestamp <= '".intval($end_time)."'";
	}

	// There are 3 possible pages here - author view, category view and front page view. I want to do another with date filters
	if (isset($_GET['author']) && isnum($_GET['author'])) {
		/**
		 * Author global_049 Blogs
		 */
		$info['blog_max_rows'] = dbcount("(blog_id)", DB_BLOG, "".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0' AND blog_name='".$_GET['author']."'");
		$_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['blog_max_rows']) ? $_GET['rowstart'] : 0;
		if ($info['blog_max_rows'] > 0) {
			$author_res = dbresult(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id='".intval($_GET['author'])."'"), 0);
			add_to_breadcrumbs(array('link' => BASEDIR."blog.php?author=".$_GET['author'], 'title' =>$locale['global_070'].ucwords($author_res)));
			$result = dbquery("SELECT tn.*, tc.*,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
				SUM(tr.rating_vote) AS sum_rating,
				COUNT(tr.rating_item_id) AS count_votes,
				COUNT(td.comment_item_id) AS count_comment,
				max(tn.blog_datestamp) as last_updated
				FROM ".DB_BLOG." tn
				LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
				LEFT JOIN ".DB_BLOG_CATS." tc ON tn.blog_cat=tc.blog_cat_id
				LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.blog_id AND tr.rating_type='B'
				LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.blog_id AND td.comment_type='B' AND td.comment_hidden='0'
				".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')." AND blog_cat='0' AND (blog_start='0'||blog_start<=".time().")
				AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0' AND blog_name='".$_GET['author']."'
				GROUP BY blog_id
				ORDER BY blog_sticky DESC, ".$filter_condition." LIMIT ".$_GET['rowstart'].",".$settings['blogperpage']
			);
			$info['blog_rows'] = dbrows($result);
		}
	}

	elseif (isset($_GET['cat_id']) && validate_blogCats($_GET['cat_id'])) {
		/**
		 * Blog Category SQL
		 */
		/* given a datestamp, calculate min of the month and max of the month */
		if ($_GET['cat_id'] > 0) {
			$res = dbarray(dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." WHERE blog_cat_id='".intval($_GET['cat_id'])."'"));
			add_to_breadcrumbs(array('link' => BASEDIR."blog.php?cat_id=".$_GET['cat_id'], 'title' => $res['blog_cat_name']));
			add_to_title($res['blog_cat_name']);
			add_to_meta($res['blog_cat_name']);
			$info['blog_title'] = $res['blog_cat_name'];
		}
		elseif ($_GET['cat_id'] == 0) {
			add_to_breadcrumbs(array('link' => BASEDIR."blog.php?cat_id=".$_GET['cat_id'], 'title' => $locale['global_080']));
			add_to_title($locale['global_080']);
			add_to_meta($locale['global_080']);
			$info['blog_title'] = $locale['global_080'];
		}
		$info['blog_max_rows'] = dbcount("('blog_id')", DB_BLOG, "blog_cat='".intval($_GET['cat_id'])."' AND ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'");
		$_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['blog_max_rows']) ? $_GET['rowstart'] : 0;
		if ($info['blog_max_rows']) {
			$result = dbquery("
				SELECT tn.*, tc.*, IF(tn.blog_cat = 0, '".$locale['global_080']."', blog_cat_name) as blog_cat_name,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
				IF(SUM(tr.rating_vote)>0, SUM(tr.rating_vote), 0) AS sum_rating,
				COUNT(tr.rating_item_id) AS count_votes,
				COUNT(td.comment_item_id) AS count_comment,
				max(tn.blog_datestamp) as last_updated
				FROM ".DB_BLOG." tn
				LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
				LEFT JOIN ".DB_BLOG_CATS." tc ON tn.blog_cat=tc.blog_cat_id
				LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.blog_id AND tr.rating_type='B'
				LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.blog_id AND td.comment_type='B' AND td.comment_hidden='0'
				".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')."
				AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().")
				AND blog_draft='0'
				AND blog_cat = '".intval($_GET['cat_id'])."'
				GROUP BY tn.blog_id
				ORDER BY blog_sticky DESC, ".$filter_condition." LIMIT ".intval($_GET['rowstart']).",".intval($settings['blogperpage'])
			);
			$info['blog_rows'] = dbrows($result);
		}
	}
	else {

		/**
		 * Everyone's Blog Posts
		 */
		$info['blog_max_rows'] = dbcount("('blog_id')", DB_BLOG, groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'");
		$_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['blog_max_rows']) ? $_GET['rowstart'] : 0;
		if ($info['blog_max_rows'] > 0) {
			$result = dbquery("
				SELECT tn.*, tc.*, IF(tn.blog_cat = 0, '".$locale['global_080']."', blog_cat_name) as blog_cat_name,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
				IF(SUM(tr.rating_vote)>0, SUM(tr.rating_vote), 0) AS sum_rating,
				COUNT(tr.rating_item_id) AS count_votes,
				COUNT(td.comment_item_id) AS count_comment,
				max(tn.blog_datestamp) as last_updated
				FROM ".DB_BLOG." tn
				LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
				LEFT JOIN ".DB_BLOG_CATS." tc ON tn.blog_cat=tc.blog_cat_id
				LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.blog_id AND tr.rating_type='B'
				LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.blog_id AND td.comment_type='B' AND td.comment_hidden='0'
				".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().")
				AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'
				".$condition."
				GROUP BY tn.blog_id
				ORDER BY blog_sticky DESC, ".$filter_condition." LIMIT ".intval($_GET['rowstart']).",".intval($settings['blogperpage'])
			);
			$info['blog_rows'] = dbrows($result);
		}
	}

	// Set Rows
	// Make Page Navigation
	if (($info['blog_max_rows'] > $settings['blogperpage']) && (!isset($_GET['readmore']) || !isnum($_GET['readmore']))) {
		$info['blog_nav'] = makepagenav($_GET['rowstart'], $settings['blogperpage'], $info['blog_max_rows'], 3);
	}

	if (!empty($info['blog_rows'])) {
		while ($data = dbarray($result)) {
			// Blog Image
			$blog_image = thumbnail('fake', '500px');
			if ($data['blog_image'] && $settings['blog_image_frontpage'] == 0) {
				$hiRes_image_path = get_blog_image_path($data['blog_image'], $data['blog_image_t1'], $data['blog_image_t2'], true);
				$lowRes_image_path = get_blog_image_path($data['blog_image'], $data['blog_image_t1'], $data['blog_image_t2'], false);
				$blog_image = thumbnail($lowRes_image_path, '150px', $hiRes_image_path, true);
			}

			// Category Image
			$blog_cat_image = "<a href='".($settings['blog_image_link'] == 0 ? "blog.php?cat_id=".$data['blog_cat'] : BASEDIR."blog.php?readmore=".$data['blog_id'])."'>";
			if ($settings['blog_image_frontpage'] == 0) {  // do not use category image
				$blog_cat_image .= $blog_image;
			} else { // use category image
				$blog_cat_image .= "<img src='".get_image("bl_".$data['blog_cat_name'])."' alt='".$data['blog_cat_name']."' class='img-responsive blog-category' />";
			}
			$blog_cat_image .= "</a>";

			$cdata = array(
				'blog_ialign'			=> 		$data['blog_ialign'] == 'center' ? 'clearfix' : $data['blog_ialign'],
				'blog_anchor'			=> 		"<a name='blog_".$data['blog_id']."' id='blog_".$data['blog_id']."'></a>",
				'blog_blog' 			=>		preg_replace("/<!?--\s*pagebreak\s*-->/i", "", ($data['blog_breaks'] ? nl2br(stripslashes($data['blog_blog'])) : stripslashes($data['blog_blog']))),
				'blog_extended'			=> 		preg_replace("/<!?--\s*pagebreak\s*-->/i", "", ($data['blog_breaks'] ? nl2br(stripslashes($data['blog_extended'])) : nl2br(stripslashes($data['blog_extended'])))),
				'blog_link'				=>		BASEDIR."blog.php?readmore=".$data['blog_id'],
				'blog_category_link'	=>		"<a href='".BASEDIR."blog.php?cat_id=".$data['blog_cat']."'>".$data['blog_cat_name']."</a>\n",
				'blog_readmore_link'	=> 		"<a href='".BASEDIR."blog.php?readmore=".$data['blog_id']."'>".$locale['blog_1006']."</a>\n",
				'blog_subject' 			=>		stripslashes($data['blog_subject']),
				'blog_image'			=> 		$blog_image,
				'blog_cat_image'		=>		$blog_cat_image,
				'blog_thumb'			=>		get_blog_image_path($data['blog_image'], $data['blog_image_t1'], $data['blog_image_t2'], false),
				"blog_reads" 			=> 		format_word($data['blog_reads'], $locale['fmt_read']),
				"blog_comments" 		=> 		format_word($data['count_comment'], $locale['fmt_comment']) ,
				'blog_sum_rating'		=> 		format_word($data['sum_rating'], $locale['fmt_rating']),
				'blog_count_votes' 		=> 		format_word($data['count_votes'], $locale['fmt_vote']),
				'blog_user_avatar'		=>		display_avatar($data, '35px', '', true, 'img-rounded'),
				'blog_user_link'		=>		profile_link($data['user_id'], $data['user_name'], $data['user_status'], 'strong'),
			);
			$data = array_merge($data, $cdata);
			$info['blog_item'][$data['blog_id']] = $data;
		}
	}
}

// make archive calculations
$archive_result = dbquery("
				SELECT  YEAR(from_unixtime(blog_datestamp)) as blog_year, MONTH(from_unixtime(blog_datestamp)) as blog_month, count(blog_id) as blog_count
				FROM ".DB_BLOG." ".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().")
				AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'
				GROUP BY blog_year, blog_month ORDER BY blog_datestamp DESC
				");
if (dbrows($archive_result)) {
	while ($a_data = dbarray($archive_result)) {
		$active = isset($_GET['archive']) && $_GET['archive'] == $a_data['blog_year']."/".$a_data['blog_month'] ? 1 : 0;
		$month_locale = explode('|', $locale['months']);
		$info['blog_archive'][$a_data['blog_year']][$a_data['blog_month']] =  array('title'=>$month_locale[$a_data['blog_month']], 'link'=>BASEDIR."blog.php?archive=".$a_data['blog_year']."/".$a_data['blog_month'], 'count' => $a_data['blog_count'], 'active'=>$active);
	}
}

$author_result = dbquery("SELECT b.blog_name, count(b.blog_id) as blog_count, u.user_id, u.user_name, u.user_status
				FROM ".DB_BLOG." b
				INNER JOIN ".DB_USERS." u on (b.blog_name = u.user_id)
				GROUP BY blog_name ORDER BY blog_name ASC
				");
if (dbrows($author_result)) {
	while ($at_data = dbarray($author_result)) {
		$active = isset($_GET['author']) && $_GET['author'] == $at_data['blog_name'] ? 1 : 0;
		$info['blog_author'][$at_data['blog_name']] =  array('title'=>ucfirst($at_data['user_name']), 'link'=>BASEDIR."blog.php?author=".$at_data['blog_name'], 'count' => $at_data['blog_count'], 'active'=>$active);
	}
}

render_main_blog($info);

require_once THEMES."templates/footer.php";

/**
 * Returns Blog Category Hierarchy Tree Data
 * @return array
 */
function get_blogCatsData() {
	return \PHPFusion\Blog\Functions::get_blogCatsData();
}

/**
 * Get Blog Hierarchy Index
 * @return array
 */
function get_blogCatsIndex() {
	return PHPFusion\Blog\Functions::get_blogCatsIndex();
}

/**
 * Validate Blog ID
 * @param $blog_id
 * @return int
 */
function validate_blog($blog_id) {
	return PHPFusion\Blog\Functions::validate_blog($blog_id);
}

/**
 * Validate Blog Cat Id
 * @param $blog_cat_id
 * @return int
 */
function validate_blogCats($blog_cat_id) {
	return PHPFusion\Blog\Functions::validate_blogCat($blog_cat_id);
}

/**
 * Update Blog Reads
 * @param $blog_id
 */
function update_blogReads($blog_id) {
	PHPFusion\Blog\Functions::update_blogReads($blog_id);
}

/**
 * Get the closest image available
 * @param      $image
 * @param      $thumb1
 * @param      $thumb2
 * @param bool $hires - true for image, false for thumbnail
 * @return bool|string
 */
function get_blog_image_path($image, $thumb1, $thumb2, $hires=false) {
	return \PHPFusion\Blog\Functions::get_blog_image_path($image, $thumb1, $thumb2, $hires);
}