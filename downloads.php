<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: downloads.php
| Author: Nick Jones (Digitanium)
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
include LOCALE.LOCALESET."downloads.php";
include THEMES."templates/global/downloads.php";
add_to_title($locale['global_200'].$locale['400']);
// download the file
if (isset($_GET['file_id']) && isnum($_GET['file_id'])) {
	$download_id = stripinput($_GET['file_id']);
	$res = 0;
	$data = dbarray(dbquery("SELECT download_url, download_file, download_cat, download_visibility FROM ".DB_DOWNLOADS." WHERE download_id='".$download_id."'"));
	if (checkgroup($data['download_visibility'])) {
		$result = dbquery("UPDATE ".DB_DOWNLOADS." SET download_count=download_count+1 WHERE download_id='".$download_id."'");
		if (!empty($data['download_file']) && file_exists(DOWNLOADS.$data['download_file'])) {
			$res = 1;
			require_once INCLUDES."class.httpdownload.php";
			ob_end_clean();
			$object = new httpdownload;
			$object->set_byfile(DOWNLOADS.$data['download_file']);
			$object->use_resume = TRUE;
			$object->download();
			exit;
		} elseif (!empty($data['download_url'])) {
			$res = 1;
			redirect($data['download_url']);
		}
	}
	if ($res == 0) {
		redirect("downloads.php");
	}
}
$info = array();
$filter = ''; // you can make static custom filter here
$info['allowed_filters'] = array('comments', 'recent', 'download', 'title', 'ratings');
$info['filters'] = array($locale['444'] => BASEDIR."downloads.php?filter=ratings", $locale['441'] => BASEDIR."downloads.php?filter=download", $locale['443'] => BASEDIR."downloads.php?filter=recent", $locale['452'] => BASEDIR."downloads.php?filter=comments");
$info['global_item_rows'] = dbcount("(download_cat)", DB_DOWNLOADS);
$info['global_download_count'] = dbresult(dbquery("SELECT SUM(download_count) FROM ".DB_DOWNLOADS), 0) ? : '0';
if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart']) || $_GET['rowstart'] > $info['global_item_rows']) {
	$_GET['rowstart'] = 0;
}

if (!isset($_GET['cat_id'])) {
	$columns = '
		td.download_id, td.download_title, td.download_description_short, td.download_image_thumb, td.download_url,
		td.download_count, td.download_cat, tc.download_cat_id, td.download_visibility
		';
	// No Pagenavigation.
	if (!isset($_GET['filter'])) {
		// Most Downloaded
		$result = dbquery("SELECT ".$columns."
            FROM ".DB_DOWNLOADS." td
            LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
            ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('download_visibility')."
            ORDER BY download_count DESC LIMIT 0, ".$settings['downloads_per_page']."
            ");
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				$info['most_downloaded'][] = $data;
			}
		}
		// Most Recently Added
		$result = dbquery("SELECT ".$columns."
		FROM ".DB_DOWNLOADS." td
		LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
		".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('download_visibility')."
		ORDER BY download_datestamp DESC LIMIT 0, ".$settings['downloads_per_page']." ");
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				$info['most_recent'][] = $data;
			}
		}
	}

	if (isset($_GET['filter']) && in_array($_GET['filter'], $info['allowed_filters'])) {
		$current_filter = $_GET['filter'];
		$info['filter-title'] = $locale[$_GET['filter']];
		// supports pagination
		$row_start = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;

		if ($current_filter == 'ratings') {
			$result = dbquery("SELECT ".$columns.", SUM(tr.rating_vote) AS sum_rating,  COUNT(tr.rating_item_id) AS count_votes
			FROM ".DB_DOWNLOADS." td
			LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
			LEFT JOIN ".DB_RATINGS." tr ON (tr.rating_item_id=td.download_id AND tr.rating_type='D')
			".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")."
			".groupaccess('download_visibility')."
			GROUP BY td.download_id
			ORDER BY sum_rating DESC LIMIT ".$_GET['rowstart'].", ".$settings['downloads_per_page']."
			");
		} elseif ($current_filter == 'download') {
			$result = dbquery("SELECT ".$columns."
			FROM ".DB_DOWNLOADS." td
			LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
			".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('download_visibility')."
			ORDER BY download_count DESC LIMIT ".$_GET['rowstart'].", ".$settings['downloads_per_page']." ");
		} elseif ($current_filter == 'recent') {
			$result = dbquery("SELECT ".$columns."
			FROM ".DB_DOWNLOADS." td
			LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
			".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('download_visibility')."
			ORDER BY download_datestamp DESC LIMIT ".$_GET['rowstart'].", ".$settings['downloads_per_page']." ");
		} elseif ($current_filter == 'comments') {
			$result = dbquery("SELECT ".$columns.", COUNT(cc.comment_id) AS total_comments
			FROM ".DB_DOWNLOADS." td
			LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
			LEFT JOIN ".DB_COMMENTS." cc ON (cc.comment_item_id=td.download_id AND comment_type='d')
			".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")."
			".groupaccess('download_visibility')."
			GROUP BY td.download_id
			ORDER BY total_comments DESC LIMIT ".$_GET['rowstart'].", ".$settings['downloads_per_page']." ");
		}

		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				$info['filter-results'][] = $data;
			}
		}
	}
}

$info['download_category'] = array();

// Category Listing for Browse by Category
$result = dbquery("
		SELECT download_cat_id, download_cat_name, download_cat_description, download_cat_sorting
		FROM ".DB_DOWNLOAD_CATS."
		".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "").$filter." 
		ORDER BY download_cat_name
		");

if (dbrows($result) > 0) {
	while ($data = dbarray($result)) {
		$info['download_category'][$data['download_cat_id']] = $data;
	}
}

// Category Callback Information
if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
	$result = dbquery("
		SELECT download_cat_id, download_cat_name, download_cat_description, download_cat_sorting
		FROM ".DB_DOWNLOAD_CATS."
		".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." download_cat_id='".$_GET['cat_id']."' ".$filter." 
		ORDER BY download_cat_name
		");
	if (dbrows($result) > 0) {
		$data = dbarray($result);
		list($rows, $max_rows) = dbarraynum(dbquery("SELECT COUNT(b.download_id), MAX(b.download_id)
													FROM ".DB_DOWNLOAD_CATS." a
													LEFT JOIN ".DB_DOWNLOADS." b ON b.download_cat=a.download_cat_id
													WHERE a.download_cat_id='".$data['download_cat_id']."'
													"));
		$info['download_category_id'] = $data['download_cat_id'];
		$info['download_items'] = array();
		$info['download_item_rows'] = $rows; // total possible rows in the whole category - use against rowstart.
		$info['download_item_max_id'] = $max_rows; // last id of the download id inside the category.
		// Prepare vars for incoming sqlquery
		// Limit
		if (!isset($_GET['rowstart'.$data['download_cat_id']]) || !isnum($_GET['rowstart'.$data['download_cat_id']]) || $_GET['rowstart'.$data['download_cat_id']] > $rows) {
			$_GET['rowstart'.$data['download_cat_id']] = 0;
		}
		// filters
		$order_by = '';
		if ($rows > 0) {
			// to filter in category listing result.
			$info['filters-1'] = array(
				$locale['451'] => BASEDIR."downloads.php?cat_id=".$_GET['cat_id'].(isset($_GET['show']) && isnum($_GET['show']) ? "&amp;show=".$_GET['show'] : '')."",
				$locale['452'] => BASEDIR."downloads.php?cat_id=".$_GET['cat_id']."&amp;type=comments".(isset($_GET['show']) && isnum($_GET['show']) ? "&amp;show=".$_GET['show'] : ''),
				$locale['453'] => BASEDIR."downloads.php?cat_id=".$_GET['cat_id']."&amp;type=recent".(isset($_GET['show']) && isnum($_GET['show']) ? "&amp;show=".$_GET['show'] : ''),
				$locale['454'] => BASEDIR."downloads.php?cat_id=".$_GET['cat_id']."&amp;type=title".(isset($_GET['show']) && isnum($_GET['show']) ? "&amp;show=".$_GET['show'] : ''),
				$locale['455'] => BASEDIR."downloads.php?cat_id=".$_GET['cat_id']."&amp;type=ratings".(isset($_GET['show']) && isnum($_GET['show']) ? "&amp;show=".$_GET['show'] : '')
			);
			// to extend number of downloads_per_page up to maximum 3 times the number of $settings['downloads_per_page'];
			for ($i = 1; $i <= 3; $i++) {
				$info['filters-2'][$settings['downloads_per_page']*$i] =
					BASEDIR."downloads.php?cat_id=".$_GET['cat_id'].(isset($_GET['type']) && in_array($_GET['type'], $info['allowed_filters']) ?
					"&amp;type=".$_GET['type'] : '')."&amp;show=".$settings['downloads_per_page']*$i;
			}
			// construct filter here
			$columns = '';
			if (isset($_GET['type']) && in_array($_GET['type'], $info['allowed_filters'])) {
				$current_filter = $_GET['type'];
				$cat_filter = '';
				if ($current_filter == 'comments') {
				// order by comment_count.
					$cat_filter = 'count_comment DESC';
				} elseif ($current_filter == 'recent') {
				// order by datestamp
					$cat_filter = 'download_datestamp DESC';
				} elseif ($current_filter == 'title') {
				// order by download_title
					$cat_filter = 'download_title ASC';
				} elseif ($current_filter == 'ratings') {
				// order by rating_count.
					$cat_filter = 'sum_rating DESC';
				}
			} else {
				$cat_filter = $data['download_cat_sorting'];
				// go default here.
			}
			$info['category_show'] = isset($_GET['show']) && isnum($_GET['show']) ? $_GET['show'] : $settings['downloads_per_page'];
			$cresult = dbquery("SELECT td.download_id, td.download_user, td.download_datestamp, td.download_image_thumb, td.download_cat,
								td.download_title, td.download_version, td.download_count, td.download_description_short, td.download_file,
								td.download_url,
								tu.user_id, tu.user_name, tu.user_status,
								SUM(tr.rating_vote) AS sum_rating,
								COUNT(tr.rating_item_id) AS count_votes,
								COUNT(tc.comment_item_id) AS count_comment
								FROM ".DB_DOWNLOADS." td
								LEFT JOIN ".DB_USERS." tu ON td.download_user=tu.user_id
								LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = td.download_id AND tr.rating_type='D'
								LEFT JOIN ".DB_COMMENTS." tc ON tc.comment_item_id = td.download_id AND tc.comment_type='d'
								WHERE download_cat='".$data['download_cat_id']."'
								GROUP BY download_id
								ORDER BY ".$cat_filter."
								LIMIT ".$_GET['rowstart'.$data['download_cat_id']].",".$info['category_show']);
			$numrows = dbrows($cresult);
			while ($download_list = dbarray($cresult)) {
				$info['download_items'][$download_list['download_id']] = $download_list; // results
				$info['download_items'][$download_list['download_id']]['comments_count'] = dbcount("(comment_id)", DB_COMMENTS, "comment_type='D' AND comment_item_id='".$download_list['download_id']."'");
			}
		}
	}
	// Download details
	if (isset($_GET['download_id']) && isnum($_GET['download_id'])) {
		$result = dbquery("SELECT td.*,
				tc.download_cat_id, tc.download_cat_access, tc.download_cat_name,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_level
                FROM ".DB_DOWNLOADS." td
                LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
                LEFT JOIN ".DB_USERS." tu ON td.download_user=tu.user_id
                ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." download_id='".$_GET['download_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$info['data'] = $data;
			if (!checkgroup($data['download_cat_access'])) {
				redirect(FUSION_SELF);
			}
		}
	}
}

// Breadcrumbs
add_to_breadcrumbs(array('link' => BASEDIR."downloads.php", 'title' => $locale['417']));
if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
	add_to_breadcrumbs(array('link' => BASEDIR."downloads.php?cat_id=".$_GET['cat_id'], 'title' => $info['download_category'][$_GET['cat_id']]['download_cat_name']));
	if (isset($_GET['download_id']) && isnum($_GET['download_id'])) {
		add_to_breadcrumbs(array('link' => BASEDIR."downloads.php?cat_id=".$_GET['cat_id']."&amp;download_id=".$_GET['download_id'], 'title' => $info['data']['download_title']));
		// download id name.
	}
}

render_downloads($info);
require_once THEMES."templates/footer.php";
?>