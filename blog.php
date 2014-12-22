<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog.php
| Author: Nick Jones (Digitanium)
| Co Author: Frederick MC Chan (Hien)
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

if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {	$_GET['rowstart'] = 0;	$rows = 0; }

// Predefined variables, do not edit these values
$i = 0;
add_to_title($locale['global_200'].$locale['global_077b']);
$info = array();
add_to_breadcrumbs(array('link' => BASEDIR.'blog.php', 'title' => $locale['global_081b'])); // blog needs to be localised

if (isset($_GET['readmore']) && isnum($_GET['readmore'])) {

	// blog items page
	$result = dbquery("SELECT tn.*, tc.*, tu.*,
	 				SUM(tr.rating_vote) AS sum_rating,
					COUNT(tr.rating_item_id) AS count_votes,
					COUNT(td.comment_item_id) AS count_comment
					FROM ".DB_BLOG." tn
					LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
					LEFT JOIN ".DB_BLOG_CATS." tc ON tn.blog_cat=tc.blog_cat_id
					LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.blog_id AND tr.rating_type='B'
					LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.blog_id AND td.comment_type='B' AND td.comment_hidden='0'
					".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')." AND blog_id='".$_GET['readmore']."' AND blog_draft='0'
					LIMIT 1
					");



	if (dbrows($result)>0) {
		include INCLUDES."comments_include.php";
		include INCLUDES."ratings_include.php";
		$data = dbarray($result);
		// protect password
		unset($data['user_password']);
		unset($data['user_algo']);
		unset($data['user_salt']);

		if (!isset($_POST['post_comment']) && !isset($_POST['post_rating'])) {
			$result2 = dbquery("UPDATE ".DB_BLOG." SET blog_reads=blog_reads+1 WHERE blog_id='".$_GET['readmore']."'");
			$data['blog_reads']++;
		}
		$blog_cat_image = "";
		$blog_image = "";
		$blog_subject = $data['blog_subject'];
		$blog_blog = preg_split("/<!?--\s*pagebreak\s*-->/i", $data['blog_breaks'] == "y" ? nl2br(stripslashes($data['blog_extended'] ? $data['blog_extended'] : $data['blog_blog'])) : stripslashes($data['blog_extended'] ? $data['blog_extended'] : $data['blog_blog']));
		$pagecount = count($blog_blog);
		$blog_info = array(
			"blog_id" => $data['blog_id'],
			"user_id" => $data['user_id'],
			"user_name" => $data['user_name'],
			"user_status" => $data['user_status'],
			"user_joined" => $data['user_joined'],
			"user_level" => $data['user_level'],
			"user_avatar" => $data['user_avatar'],
			"blog_date" => $data['blog_datestamp'],
			"blog_ialign" => $data['blog_ialign'],
			"cat_id" => $data['blog_cat'],
			"cat_name" => $data['blog_cat_name'] ? $data['blog_cat_name'] : $locale['global_080'],
			"cat_link" => "<a href='".BASEDIR."blog.php?cat_id=".$data['blog_cat']."'>".($data['blog_cat_name'] ? $data['blog_cat_name'] : $locale['global_080'])."</a>",
			"blog_image" => $data['blog_image'],
			"cat_image" => $data['blog_cat_image'],
			"blog_subject" => $data['blog_subject'],
			'blog_blog' => $blog_blog[$_GET['rowstart']],
			"blog_ext" => "n",
			"blog_keywords" => $data['blog_keywords'],
			"blog_reads" => $data['blog_reads'],
			"blog_comments" => $data['count_comment'],
			'blog_sum_rating' => $data['sum_rating'] ? $data['sum_rating'] : 0,
			'blog_count_votes' => $data['count_votes'],
			"blog_allow_comments" => $data['blog_allow_comments'],
			'blog_allow_ratings' => $data['blog_allow_ratings'],
			"blog_sticky" => $data['blog_sticky']
		);

		// get UF and merge for testimonial

		if ($data['user_email'] && $data['user_hide_email'] == 0) $blog_info['user_email'] = "<a mailto='".$data['user_email']."'>".$data['user_email']."</a>";
		if ($data['user_location']) $blog_info['user_location'] = $data['user_location'];
		if ($data['user_web']) $blog_info['user_web'] = $data['user_web'];

		$blog_info['user_contact'] = '';
		$_fieldquery = dbquery("SELECT field_name FROM ".DB_USER_FIELDS."");
		if (dbrows($_fieldquery)>0) {
			while ($_field = dbarray($_fieldquery)) {
				$field_list[] = $_field['field_name'];
			}
			// i just need these
			if (in_array('user_skype', $field_list) && $data['user_skype']) $blog_info['user_contact'] .= " <strong>Skype:</strong> ".$data['user_skype'];
			if (in_array('user_aim', $field_list) && $data['user_aim']) $blog_info['user_contact'] .= " <strong>AIM:</strong> ".$data['user_aim'];
			if (in_array('user_yahoo', $field_list) && $data['user_yahoo']) $blog_info['user_contact'] .= " <strong>Yahoo:</strong> ".$data['user_yahoo'];
			if (in_array('user_icq', $field_list) && $data['user_icq']) $blog_info['user_contact'] .= " <strong>ICQ:</strong> ".$data['user_icq'];
		}

		add_to_title($locale['global_201'].$blog_subject);
		$cat_name = $data['blog_cat_name'] ? $data['blog_cat_name'] : $locale['global_080'];
		add_to_breadcrumbs(array('link'=>BASEDIR."blog.php?cat_id=".$data['blog_cat'], 'title'=>$cat_name));
		add_to_breadcrumbs(array('link'=>BASEDIR."blog.php?readmore=".$data['blog_id'], 'title'=>$data['blog_subject']));
		$info['blog_item'] = $blog_info;
		$info['blog_item']['page_count'] = $pagecount;
	} else {
		redirect(BASEDIR."blog.php");
	}
} else {
	// Front Page
	/* Init */
	$result = '';
	$info['blog_cat_id'] = '0';
	$info['blog_cat_name'] = $locale['global_082b'];
	$info['blog_cat_image'] = '';
	$info['blog_cat_language'] = LANGUAGE;
	$info['blog_categories'] = '';

	/* blog Category */
	$result = dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."'" : '')." ORDER BY blog_cat_id ASC");
	if (dbrows($result)>0) {
		while ($cdata = dbarray($result)) {
			$info['blog_categories'][$cdata['blog_cat_id']] = $cdata['blog_cat_name'];
		}
		unset($cdata);
	}

	/* Filter Construct */
	$filter = array('recent', 'comment', 'rating');
	$info['allowed_filters'] = array('recent'=>$locale['global_086b'], 'comment'=>$locale['global_087b'], 'rating'=>$locale['global_088b']);
	foreach($info['allowed_filters'] as $type => $filter_name) {
		$filter_link = BASEDIR."blog.php?".(isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '')."type=".$type;
		$info['blog_filter'][$filter_link] = $filter_name;
		unset($filter_link);
	}
	$columns = '';
	if (isset($_GET['type']) && in_array($_GET['type'], $filter)) {
		$current_filter = $_GET['type'];
		$cat_filter = 'blog_datestamp DESC';
		if ($current_filter == 'recent') {
			// order by datestamp.
			$cat_filter = 'blog_datestamp DESC';
		} elseif ($current_filter == 'comment') {
			// order by comment_count
			$cat_filter = 'count_comment DESC';
		} elseif ($current_filter == 'rating') {
			// order by download_title
			$cat_filter = 'sum_rating DESC';
		}
	} else {
		$cat_filter = 'blog_datestamp DESC';
	}

	if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
		// Filtered by Category ID.
		$result = dbquery("SELECT * FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."' AND" : "WHERE")." blog_cat_id='".$_GET['cat_id']."'");
		if (dbrows($result)) {
		
			$data = dbarray($result);
			// build categorial data.
			$info['blog_cat_id'] = $data['blog_cat_id'];
			$info['blog_cat_name'] = $data['blog_cat_name'];
			$info['blog_cat_image'] = $data['blog_cat_image'] && file_exists(IMAGES_BC.$data['blog_cat_image']) ?
				"<img class='img-responsive' src='".IMAGES_BC.$data['blog_cat_image']."' />" :
				"<img class='img-responsive' src='holder.js/80x80/text:".$locale['no_image']."/grey' />";
			$info['blog_cat_language'] = $data['blog_cat_language'];
			$rows = dbcount("(blog_id)", DB_BLOG, "blog_cat='".$data['blog_cat_id']."' AND ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'");
			if ($rows) {
				// apply filter.
				$result = dbquery("SELECT tn.*, tc.*,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
				SUM(tr.rating_vote) AS sum_rating,
				COUNT(tr.rating_item_id) AS count_votes,
				COUNT(td.comment_item_id) AS count_comment
				FROM ".DB_BLOG." tn
				LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
				LEFT JOIN ".DB_BLOG_CATS." tc ON tn.blog_cat=tc.blog_cat_id
				LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.blog_id AND tr.rating_type='B'
				LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.blog_id AND td.comment_type='B' AND td.comment_hidden='0'
				".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')." AND blog_cat='".$data['blog_cat_id']."' AND (blog_start='0'||blog_start<=".time().")
				AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'
				GROUP BY blog_id
				ORDER BY blog_sticky DESC, ".$cat_filter." LIMIT ".$_GET['rowstart'].",".$settings['blogperpage']);
				$info['blog_item_rows'] = $rows;
				add_to_breadcrumbs(array('link'=>BASEDIR."blog.php?cat_id=".$data['blog_cat_id'], 'title'=>$data['blog_cat_name']));
			}
		} elseif ($_GET['cat_id'] == 0) {
			$rows = dbcount("(blog_id)", DB_BLOG, "blog_cat='0' AND ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'");
			if ($rows) {
				// apply filter.
				$result = dbquery("SELECT tn.*, tc.*,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
				SUM(tr.rating_vote) AS sum_rating,
				COUNT(tr.rating_item_id) AS count_votes,
				COUNT(td.comment_item_id) AS count_comment
				FROM ".DB_BLOG." tn
				LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
				LEFT JOIN ".DB_BLOG_CATS." tc ON tn.blog_cat=tc.blog_cat_id
				LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.blog_id AND tr.rating_type='B'
				LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.blog_id AND td.comment_type='B' AND td.comment_hidden='0'
				".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')." AND blog_cat='0' AND (blog_start='0'||blog_start<=".time().")
				AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'
				GROUP BY blog_id
				ORDER BY blog_sticky DESC, ".$cat_filter." LIMIT ".$_GET['rowstart'].",".$settings['blogperpage']);
				$info['blog_item_rows'] = $rows;
				add_to_breadcrumbs(array('link'=>BASEDIR."blog.php?cat_id=".$_GET['cat_id'], 'title'=>$locale['global_080']));
			}
		} else {
		redirect(BASEDIR."blog.php");
		}
	} else {
		// All Results
		$rows = dbcount("(blog_id)", DB_BLOG, groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'");
		if ($rows) {
			$result = dbquery("SELECT tn.*, tc.*,
			tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
			SUM(tr.rating_vote) AS sum_rating,
			COUNT(tr.rating_item_id) AS count_votes,
			COUNT(td.comment_item_id) AS count_comment
			FROM ".DB_BLOG." tn
			LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
			LEFT JOIN ".DB_BLOG_CATS." tc ON tn.blog_cat=tc.blog_cat_id
			LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.blog_id AND tr.rating_type='B'
			LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.blog_id AND td.comment_type='B' AND td.comment_hidden='0'
			".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().")
			AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'
			GROUP BY blog_id
			ORDER BY blog_sticky DESC, ".$cat_filter." LIMIT ".$_GET['rowstart'].",".$settings['blogperpage']);
			$info['blog_item_rows'] = dbrows($result);
		} else {
			$info['blog_item_rows'] = 0;
		}
}
	// end sql
		$info['blog_last_updated'] = 0;
		if (!empty($info['blog_item_rows'])) {
			while ($data = dbarray($result)) {
				$i++;
				if ($i == 1) {
				$info['blog_last_updated'] = $data['blog_datestamp'];
				}
				$blog_cat_image = '';	$blog_image = '';	$blog_img_src = '';
				$blog_subject = stripslashes($data['blog_subject']);
				if ($data['blog_image'] && file_exists(IMAGES_B.$data['blog_image']) && $settings['blog_image_frontpage'] == 0) {
					$blog_image = "<a class='img-link' href='".($settings['blog_image_link'] == 0 ? "blog.php?cat_id=".$data['blog_cat'] : BASEDIR."blog.php?readmore=".$data['blog_id'])."'>";
					$blog_image .= "<img class='img-responsive' src='".IMAGES_B.$data['blog_image']."' alt='".$data['blog_subject']."' /></a>";
					$blog_img_src = "<img src='".IMAGES_B.$data['blog_image']."' alt='".$data['blog_subject']."' />";
				}
				$blog_cat_image = "<a href='".($settings['blog_image_link'] == 0 ? "blog.php?cat_id=".$data['blog_cat'] : BASEDIR."blog.php?readmore=".$data['blog_id'])."'>";
				if ($data['blog_image_t2'] && $settings['blog_image_frontpage'] == 0) {
					$blog_cat_image .= "<img src='".IMAGES_B_T.$data['blog_image_t2']."' alt='".$data['blog_subject']."' class='img-responsive blog-category' /></a>";
				} elseif ($data['blog_cat_image']) {
					$blog_cat_image .= "<img src='".get_image("bl_".$data['blog_cat_name'])."' alt='".$data['blog_cat_name']."' class='img-responsive blog-category' /></a>";
				} else {
					$blog_cat_image = "";
				}
				$blog_blog = preg_replace("/<!?--\s*pagebreak\s*-->/i", "", ($data['blog_breaks'] == "y" ? nl2br(stripslashes($data['blog_blog'])) : stripslashes($data['blog_blog'])));
				$blog_info[$i] = array(
					"blog_id" => $data['blog_id'],
					'blog_subject' => $blog_subject,
					'blog_anchor' => "<a name='blog_".$data['blog_id']."' id='blog_".$data['blog_id']."'></a>",
					'blog_blog' => $blog_blog,
					"blog_keywords" => $data['blog_keywords'],
					"user_id" => $data['user_id'],
					"user_name" => $data['user_name'],
					"user_status" => $data['user_status'],
					"user_avatar" => $data['user_avatar'],
					'user_level' => $data['user_level'],
					"blog_date" => $data['blog_datestamp'],
					"cat_id" => $data['blog_cat'],
					"cat_name" => $data['blog_cat_name'],
					"cat_image" => $blog_cat_image,
					"blog_image" => $blog_image,
					'blog_image_src' => $blog_img_src, // raw rather than preg_replace() usage later.
					"blog_ext" => $data['blog_extended'] ? "y" : "n",
					"blog_reads" => $data['blog_reads'],
					"blog_comments" => $data['count_comment'],
					'blog_sum_rating' => $data['sum_rating'] ? $data['sum_rating'] : 0,
					'blog_count_votes' => $data['count_votes'],
					"blog_allow_comments" => $data['blog_allow_comments'],
					"blog_allow_ratings" => $data['blog_allow_ratings'],
					"blog_sticky" => $data['blog_sticky']
				);
			}
			$info['blog_items'] = $blog_info;
			} else {
		$info['blog_items'] = array();
	}
}
render_main_blog($info);
if ($rows > $settings['blogperpage'] && (!isset($_GET['readmore']) || !isnum($_GET['readmore']))) echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], $settings['blogperpage'], $rows , 3)."\n</div>\n";
require_once THEMES."templates/footer.php";
?>