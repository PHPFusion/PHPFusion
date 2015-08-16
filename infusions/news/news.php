<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news.php
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
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";
if (!db_exists(DB_NEWS)) {
	$_GET['code'] = 404;
	require_once __DIR__.'/error.php';
	exit;
}
require_once THEMES."templates/header.php";
require_once INCLUDES."infusions_include.php";
$news_settings = get_settings("news");
require_once INFUSIONS."news/templates/news.php";
if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
	$_GET['rowstart'] = 0;
	$rows = 0;
}
// Predefined variables, do not edit these values
$i = 0;
add_to_title($locale['global_200'].$locale['global_077']);
$info = array();
add_breadcrumb(array('link' => INFUSIONS.'news/news.php', 'title' => $locale['global_081']));
if (isset($_GET['readmore']) && isnum($_GET['readmore'])) {
	$result = dbquery("SELECT tn.*, tc.*, tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
	 				SUM(tr.rating_vote) AS sum_rating,
					COUNT(tr.rating_item_id) AS count_votes,
					COUNT(td.comment_item_id) AS count_comment
					FROM ".DB_NEWS." tn
					LEFT JOIN ".DB_USERS." tu ON tn.news_name=tu.user_id
					LEFT JOIN ".DB_NEWS_CATS." tc ON tn.news_cat=tc.news_cat_id
					LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.news_id AND tr.rating_type='N'
					LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.news_id AND td.comment_type='N' AND td.comment_hidden='0'
					".(multilang_table("NS") ? "WHERE news_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('news_visibility')."
					AND news_id='".$_GET['readmore']."' AND news_draft='0'
					LIMIT 1
					");
	if (dbrows($result)) {
		include INCLUDES."comments_include.php";
		include INCLUDES."ratings_include.php";
		$data = dbarray($result);
		if ($data['news_keywords'] !== "") {
			set_meta("keywords", $data['news_keywords']);
		}
		if (!isset($_POST['post_comment']) && !isset($_POST['post_rating'])) {
			$result2 = dbquery("UPDATE ".DB_NEWS." SET news_reads=news_reads+1 WHERE news_id='".$_GET['readmore']."'");
			$data['news_reads']++;
		}
		$news_cat_image = "";
		$news_image = "";
		$news_subject = $data['news_subject'];
		$news_news = preg_split("/<!?--\s*pagebreak\s*-->/i", $data['news_breaks'] == "y" ? nl2br(stripslashes($data['news_extended'] ? $data['news_extended'] : $data['news_news'])) : stripslashes($data['news_extended'] ? $data['news_extended'] : $data['news_news']));
		$pagecount = count($news_news);
		$news_info = array(
			"news_id" => $data['news_id'], "user_id" => $data['user_id'], "user_name" => $data['user_name'],
			"user_status" => $data['user_status'], "user_joined" => $data['user_joined'],
			"user_level" => $data['user_level'], "user_avatar" => $data['user_avatar'],
			"news_date" => $data['news_datestamp'], "news_ialign" => $data['news_ialign'],
			"cat_id" => $data['news_cat'], "cat_name" => $data['news_cat_name'], "news_image" => $data['news_image'],
			"cat_image" => $data['news_cat_image'], "news_subject" => $data['news_subject'],
			"news_descr" => $data['news_news'], 'news_url' => INFUSIONS.'news/news.php?readmore='.$data['news_id'],
			'news_news' => $news_news[$_GET['rowstart']], "news_ext" => "n", "news_keywords" => $data['news_keywords'],
			"news_reads" => $data['news_reads'], "news_comments" => $data['count_comment'],
			'news_sum_rating' => $data['sum_rating'] ? $data['sum_rating'] : 0,
			'news_count_votes' => $data['count_votes'], "news_allow_comments" => $data['news_allow_comments'],
			'news_allow_ratings' => $data['news_allow_ratings'], "news_sticky" => $data['news_sticky']
		);
		if (fusion_get_settings("create_og_tags")) {
			add_to_head("<meta property='og:title' content='".$data['news_subject']."' />");
			add_to_head("<meta property='og:description' content='".strip_tags($data['news_news'])."' />");
			add_to_head("<meta property='og:site_name' content='".$settings['sitename']."' />");
			add_to_head("<meta property='og:type' content='article' />");
			add_to_head("<meta property='og:url' content='".$settings['siteurl']."infusions/news.php?readmore=".$_GET['readmore']."' />");
			if ($data['news_image']) {
				$og_image = IMAGES_N.$data['news_image'];
			} else {
				$og_image = IMAGES_NC.$data['news_cat_image'];
			}
			$og_image = str_replace(BASEDIR, $settings['siteurl'], $og_image);
			add_to_head("<meta property='og:image' content='".$og_image."' />");
		}
		set_title($news_subject.$locale['global_200'].$locale['global_077']);
		add_breadcrumb(array(
						   'link' => INFUSIONS."news/news.php?cat_id=".$data['news_cat'],
						   'title' => $data['news_cat_name']
					   ));
		add_breadcrumb(array(
						   'link' => INFUSIONS."news/news.php?readmore=".$data['news_id'],
						   'title' => $data['news_subject']
					   ));
		$info['news_item'] = $news_info;
		$info['news_item']['page_count'] = $pagecount;
	} else {
		redirect(INFUSIONS."news/news.php");
	}
} else {
	// Front Page
	/* Init */
	$result = '';
	$info['news_cat_id'] = '0';
	$info['news_cat_name'] = $locale['global_082'];
	$info['news_cat_image'] = '';
	$info['news_cat_language'] = LANGUAGE;
	$info['news_categories'] = '';
	/* News Category */
	$result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : '')." ORDER BY news_cat_id ASC");
	if (dbrows($result) > 0) {
		while ($cdata = dbarray($result)) {
			$info['news_categories'][$cdata['news_cat_id']] = array(
				'link' => INFUSIONS.'news.php?cat_id='.$cdata['news_cat_id'], 'name' => $cdata['news_cat_name']
			);
		}
		unset($cdata);
	}
	/* Filter Construct */
	$filter = array('recent', 'comment', 'rating');
	$info['allowed_filters'] = array(
		'recent' => $locale['global_086'], 'comment' => $locale['global_087'], 'rating' => $locale['global_088']
	);
	foreach ($info['allowed_filters'] as $type => $filter_name) {
		$filter_link = INFUSIONS."news/news.php?".(isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '')."type=".$type;
		$info['news_filter'][$filter_link] = $filter_name;
		unset($filter_link);
	}
	$columns = '';
	if (isset($_GET['type']) && in_array($_GET['type'], $filter)) {
		$current_filter = $_GET['type'];
		$cat_filter = 'news_datestamp DESC';
		if ($current_filter == 'recent') {
			// order by datestamp.
			$cat_filter = 'news_datestamp DESC';
		} elseif ($current_filter == 'comment') {
			// order by comment_count
			$cat_filter = 'count_comment DESC';
		} elseif ($current_filter == 'rating') {
			// order by download_title
			$cat_filter = 'sum_rating DESC';
		}
	} else {
		$cat_filter = 'news_datestamp DESC';
	}
	if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
		// Filtered by Category ID.
		$result = dbquery("SELECT * FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."' AND" : "WHERE")." news_cat_id='".$_GET['cat_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			// build categorial data.
			$info['news_cat_id'] = $data['news_cat_id'];
			$info['news_cat_name'] = $data['news_cat_name'];
			$info['news_cat_image'] = $data['news_cat_image'] && file_exists(IMAGES_NC.$data['news_cat_image']) ? "<img class='img-responsive' src='".IMAGES_NC.$data['news_cat_image']."' />" : "<img class='img-responsive' src='holder.js/80x80/text:".$locale['no_image']."/grey' />";
			$info['news_cat_language'] = $data['news_cat_language'];
			$rows = dbcount("(news_id)", DB_NEWS, "news_cat='".$data['news_cat_id']."' AND ".groupaccess('news_visibility')." AND (news_start='0'||news_start<=".time().") AND (news_end='0'||news_end>=".time().") AND news_draft='0'");
			if ($rows) {
				// apply filter.
				$result = dbquery("SELECT tn.*, tc.*,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
				SUM(tr.rating_vote) AS sum_rating,
				COUNT(tr.rating_item_id) AS count_votes,
				COUNT(td.comment_item_id) AS count_comment
				FROM ".DB_NEWS." tn
				LEFT JOIN ".DB_USERS." tu ON tn.news_name=tu.user_id
				LEFT JOIN ".DB_NEWS_CATS." tc ON tn.news_cat=tc.news_cat_id
				LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.news_id AND tr.rating_type='N'
				LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.news_id AND td.comment_type='N' AND td.comment_hidden='0'
				".(multilang_table("NS") ? "WHERE news_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('news_visibility')." AND news_cat='".$data['news_cat_id']."' AND (news_start='0'||news_start<=".time().")
				AND (news_end='0'||news_end>=".time().") AND news_draft='0'
				GROUP BY news_id
				ORDER BY news_sticky DESC, ".$cat_filter." LIMIT ".$_GET['rowstart'].",".$news_settings['news_pagination']);
				$info['news_item_rows'] = $rows;
				add_breadcrumb(array(
								   'link' => INFUSIONS."news/news.php?cat_id=".$data['news_cat_id'],
								   'title' => $data['news_cat_name']
							   ));
			}
		} elseif ($_GET['cat_id'] == 0) {
			$rows = dbcount("(news_id)", DB_NEWS, "news_cat='0' AND ".groupaccess('news_visibility')." AND (news_start='0'||news_start<=".time().") AND (news_end='0'||news_end>=".time().") AND news_draft='0'");
			if ($rows) {
				// apply filter.
				$result = dbquery("SELECT tn.*, tc.*,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
				SUM(tr.rating_vote) AS sum_rating,
				COUNT(tr.rating_item_id) AS count_votes,
				COUNT(td.comment_item_id) AS count_comment
				FROM ".DB_NEWS." tn
				LEFT JOIN ".DB_USERS." tu ON tn.news_name=tu.user_id
				LEFT JOIN ".DB_NEWS_CATS." tc ON tn.news_cat=tc.news_cat_id
				LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.news_id AND tr.rating_type='N'
				LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.news_id AND td.comment_type='N' AND td.comment_hidden='0'
				".(multilang_table("NS") ? "WHERE news_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('news_visibility')." AND news_cat='0' AND (news_start='0'||news_start<=".time().")
				AND (news_end='0'||news_end>=".time().") AND news_draft='0'
				GROUP BY news_id
				ORDER BY news_sticky DESC, ".$cat_filter." LIMIT ".$_GET['rowstart'].",".$news_settings['news_pagination']);
				$info['news_item_rows'] = $rows;
				add_breadcrumb(array(
								   'link' => INFUSIONS."news/news.php?cat_id=".$_GET['cat_id'],
								   'title' => $locale['global_080']
							   ));
			}
		} else {
			redirect(INFUSIONS."news/news.php");
		}
	} else {
		// All Results
		$rows = dbcount("(news_id)", DB_NEWS, groupaccess('news_visibility')." AND (news_start='0'||news_start<=".time().") AND (news_end='0'||news_end>=".time().") AND news_draft='0'");
		if ($rows) {
			$result = dbquery("SELECT tn.*, tc.*,
			tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
			SUM(tr.rating_vote) AS sum_rating,
			COUNT(tr.rating_item_id) AS count_votes,
			COUNT(td.comment_item_id) AS count_comment
			FROM ".DB_NEWS." tn
			LEFT JOIN ".DB_USERS." tu ON tn.news_name=tu.user_id
			LEFT JOIN ".DB_NEWS_CATS." tc ON tn.news_cat=tc.news_cat_id
			LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.news_id AND tr.rating_type='N'
			LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.news_id AND td.comment_type='N' AND td.comment_hidden='0'
			".(multilang_table("NS") ? "WHERE news_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('news_visibility')." AND (news_start='0'||news_start<=".time().")
			AND (news_end='0'||news_end>=".time().") AND news_draft='0'
			GROUP BY news_id
			ORDER BY news_sticky DESC, ".$cat_filter." LIMIT ".$_GET['rowstart'].",".$news_settings['news_pagination']);
			$info['news_item_rows'] = dbrows($result);
		} else {
			$info['news_item_rows'] = 0;
		}
	}
	// end sql
	$info['news_last_updated'] = 0;
	if (!empty($info['news_item_rows'])) {
		while ($data = dbarray($result)) {
			$i++;
			if ($i == 1) {
				$info['news_last_updated'] = $data['news_datestamp'];
			}
			$news_cat_image = '';
			$news_image = '';
			$news_subject = stripslashes($data['news_subject']);
			// ok, lets start all over again.
			// the full image
			$_fullResSource = "";
			if ($data['news_image'] && file_exists(IMAGES_N.$data['news_image'])) {
				$_fullResSource = IMAGES_N.$data['news_image'];
			}
			// need to check if want to link.
			$imageSource = IMAGES_N."news_default.jpg";
			if ($data['news_cat_image']) {
				$imageSource = get_image("nc_".$data['news_cat_name']);
			}
			if ($news_settings['news_image_frontpage'] == 0) {
				if ($data['news_image'] && file_exists(IMAGES_N.$data['news_image'])) {
					$imageSource = IMAGES_N.$data['news_image'];
				}
				if ($data['news_image_t2'] && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
					$imageSource = IMAGES_N_T.$data['news_image_t2'];
				}
				if ($data['news_image_t1'] && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
					$imageSource = IMAGES_N_T.$data['news_image_t1'];
				}
			}
			$image = "<img class='img-responsive' src='".$imageSource."' alt='".$data['news_subject']."' />\n";
			$news_image = "<a class='img-link' href='
					".($news_settings['news_image_link'] == 0 ? INFUSIONS."news/news.php?cat_id=".$data['news_cat'] : INFUSIONS."news/news.php?readmore=".$data['news_id'])."
					'>".$image."</a>\n";
			$news_cat_image = "<a href='".($news_settings['news_image_link'] == 0 ? "".INFUSIONS."news/news.php?cat_id=".$data['news_cat'] : INFUSIONS."news/news.php?readmore=".$data['news_id'])."'>";
			if ($data['news_image_t2'] && $news_settings['news_image_frontpage'] == 0) {
				$news_cat_image .= $image."</a>";
			} elseif ($data['news_cat_image']) {
				$news_cat_image .= "<img src='".get_image("nc_".$data['news_cat_name'])."' alt='".$data['news_cat_name']."' class='img-responsive news-category' /></a>";
			}
			$news_news = preg_replace("/<!?--\s*pagebreak\s*-->/i", "", ($data['news_breaks'] == "y" ? nl2br(stripslashes($data['news_news'])) : stripslashes($data['news_news'])));
			$news_info[$i] = array(
				"news_id" => $data['news_id'], 'news_subject' => $news_subject,
				"news_url" => INFUSIONS.'news/news.php?readmore='.$data['news_id'],
				'news_anchor' => "<a name='news_".$data['news_id']."' id='news_".$data['news_id']."'></a>",
				'news_news' => $news_news, "news_keywords" => $data['news_keywords'], "user_id" => $data['user_id'],
				"user_name" => $data['user_name'], "user_status" => $data['user_status'],
				"user_avatar" => $data['user_avatar'], 'user_level' => $data['user_level'],
				"news_date" => $data['news_datestamp'], "cat_id" => $data['news_cat'],
				"cat_name" => $data['news_cat_name'], "cat_image" => $news_cat_image, "news_image" => $news_image,
				'news_image_src' => $_fullResSource, "news_ext" => $data['news_extended'] ? "y" : "n",
				"news_reads" => $data['news_reads'], "news_comments" => $data['count_comment'],
				'news_sum_rating' => $data['sum_rating'] ? $data['sum_rating'] : 0,
				'news_count_votes' => $data['count_votes'], "news_allow_comments" => $data['news_allow_comments'],
				"news_allow_ratings" => $data['news_allow_ratings'], "news_sticky" => $data['news_sticky']
			);
		}
		$info['news_items'] = $news_info;
	} else {
		$info['news_items'] = array();
	}
}
render_main_news($info);
require_once THEMES."templates/footer.php";