<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gallery.php
| Author: Nick Jones (Digitanium)
| Co-Author: PHP-Fusion Development Team
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
if (!db_exists(DB_PHOTO_ALBUMS)) {
	$_GET['code'] = 404;
	require_once __DIR__.'/error.php';
	exit;
}

require_once THEMES."templates/header.php";
include INFUSIONS."gallery/locale/".LOCALESET."gallery.php";
include INFUSIONS."gallery/templates/gallery.php";

require_once INCLUDES."infusions_include.php";
$gallery_settings = get_settings("gallery");

if (!defined('SAFEMODE')) define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
add_to_title($locale['global_200'].$locale['400']);
add_breadcrumb(array('link'=>INFUSIONS.'gallery/gallery.php', 'title'=>$locale['400']));

/* View Photo */
if (isset($_GET['photo_id']) && isnum($_GET['photo_id'])) {
	include INCLUDES."comments_include.php";
	include INCLUDES."ratings_include.php";
	add_to_jquery("$('a.photogallery_photo_link').colorbox({width:'80%', height:'80%', photo:true});");

	$result = dbquery("SELECT tp.*, ta.album_id, ta.album_title, ta.album_access,
		tu.user_id, tu.user_name, tu.user_status,
		SUM(tr.rating_vote) AS sum_rating, COUNT(tr.rating_item_id) AS count_votes,
		count(tc.comment_id) AS comment_count
		FROM ".DB_PHOTOS." tp
		LEFT JOIN ".DB_PHOTO_ALBUMS." ta USING (album_id)
		LEFT JOIN ".DB_USERS." tu ON tp.photo_user=tu.user_id
		LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tp.photo_id AND tr.rating_type='P'
		LEFT JOIN ".DB_COMMENTS." tc ON tc.comment_item_id=tp.photo_id AND comment_type='P'
		WHERE ".groupaccess('album_access')." AND photo_id='".intval($_GET['photo_id'])."' GROUP BY tp.photo_id");
	$info = array();


	if (dbrows($result)>0) {

		$data = dbarray($result);
		$info = $data;
		/* Declaration */
		define("PHOTODIR", PHOTOS.(!SAFEMODE ? "album_".$data['album_id']."/" : ""));

		$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_views=(photo_views+1) WHERE photo_id='".$_GET['photo_id']."'");
		// order is not working.
		$pres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order='".($data['photo_order']-1)."' AND album_id='".$data['album_id']."'");
		$nres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order='".($data['photo_order']+1)."' AND album_id='".$data['album_id']."'");
		$fres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order='1' AND album_id='".$data['album_id']."'");

		$lastres = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." WHERE album_id='".$data['album_id']."'"), 0);
		$lres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order>='".$lastres."' AND album_id='".$data['album_id']."'");
		if (dbrows($pres)) $prev = dbarray($pres);
		if (dbrows($nres)) $next = dbarray($nres);
		if (dbrows($fres)) $first = dbarray($fres);
		if (dbrows($lres)) $last = dbarray($lres);

		add_to_title($locale['global_201'].$data['photo_title']);
		add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
		add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");

		add_breadcrumb(array('link'=>INFUSIONS."gallery/gallery.php?album_id=".$data['album_id'], 'title'=>$data['album_title']));
		add_breadcrumb(array('link'=>INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id'], 'title'=>$data['photo_title']));

		if ($gallery_settings['photo_watermark']) {
			if ($gallery_settings['photo_watermark_save']) {
				$parts = explode(".", $data['photo_filename']);
				$wm_file1 = $parts[0]."_w1.".$parts[1];
				$wm_file2 = $parts[0]."_w2.".$parts[1];
				if (!file_exists(PHOTODIR."/thumbs/".$wm_file1)) {
					if ($data['photo_thumb2']) {
						$info['photo_thumb'] = INFUSIONS."gallery/photo.php?photo_id=".$_GET['photo_id'];
					}
					$info['photo_file'] = INFUSIONS."gallery/photo.php?photo_id=".$_GET['photo_id']."&amp;full";
				} else {
					if ($data['photo_thumb2']) {
						$info['photo_thumb'] = PHOTODIR."/".$wm_file1;
					}
					$info['photo_file'] = PHOTODIR."/".$wm_file2;
				}
			} else {
				if ($data['photo_thumb2']) {
					$info['photo_thumb'] = INFUSIONS."gallery/photo.php?photo_id=".$_GET['photo_id'];
				}
				$info['photo_file'] = INFUSIONS."gallery/photo.php?photo_id=".$_GET['photo_id']."&amp;full";
			}
			$info['photo_size'] = @getimagesize(PHOTODIR.$data['photo_filename']);
		} else {
			$info['photo_thumb'] = $data['photo_thumb2'] ? PHOTODIR."/thumbs/".$data['photo_thumb2'] : "";
			$info['photo_file'] = PHOTODIR.$data['photo_filename'];
			$info['photo_size'] = @getimagesize($photo_file);
		}
		$info['photo_byte'] = parsebytesize($gallery_settings['photo_watermark'] ? filesize(PHOTODIR.$info['photo_filename']) : filesize($info['photo_file']));
		$info['photo_comment'] = $data['photo_allow_comments'] ? number_format($data['comment_count']) : 0;
		$info['photo_ratings'] = $data['photo_allow_ratings'] ? number_format(ceil($info['sum_rating']/$info['count_votes'])) : '0';
		$info['photo_description'] = $data['photo_description'] ? nl2br(parseubb($info['photo_description'], "b|i|u|center|small|url|mail|img|quote")) : '';

		if ((isset($prev['photo_id']) && isnum($prev['photo_id'])) || (isset($next['photo_id']) && isnum($next['photo_id']))) {
			if (isset($prev) && isset($first)) {
				$info['nav']['first'] = array('link'=>INFUSIONS."gallery/gallery.php?photo_id=".$first['photo_id'], 'name'=>$locale['459']);
			}
			if (isset($prev)) {
				$info['nav']['prev'] = array('link'=>INFUSIONS."gallery/gallery.php?photo_id=".$prev['photo_id'], 'name'=>$locale['451']);
			}
			if (isset($next)) {
				$info['nav']['next'] = array('link'=>INFUSIONS."gallery/gallery.php?photo_id=".$next['photo_id'], 'name'=>$locale['452']);
			}
			if (isset($next) && isset($last)) {
				$info['nav']['last'] = array('link'=>INFUSIONS."gallery/gallery.php?photo_id=".$last['photo_id'], 'name'=>$locale['460']);
			}
		}
		render_photo($info);
	} else {
		redirect(INFUSIONS.'gallery/gallery.php');
	}
}

/* View Album */
elseif (isset($_GET['album_id']) && isnum($_GET['album_id'])) {

	// There are 2 errors here:
	// Notice: Use of undefined constant LANGUAGE - assumed 'LANGUAGE' in /Applications/MAMP/htdocs/PHP-Fusion/includes/core_mlang_hub_include.php on line 139
    // Notice: Undefined variable: userdata in /Applications/MAMP/htdocs/PHP-Fusion/maincore.php on line 166

	define("PHOTODIR", PHOTOS.(!SAFEMODE ? "album_".$_GET['album_id']."/" : ""));

	$result = dbquery("SELECT album_title, album_description, album_thumb, album_access FROM ".DB_PHOTO_ALBUMS." WHERE ".groupaccess('album_access')." AND album_id='".intval($_GET['album_id'])."'");
	if (dbrows($result)>0) {
		$info = dbarray($result);
		add_to_title($locale['global_201'].$info['album_title']);
		add_breadcrumb(array('link'=>INFUSIONS.'gallery/gallery.php?album_id='.$_GET['album_id'], 'title'=>$info['album_title']));
		/* Category Info */
		$info['album_thumb'] = ($info['album_thumb'] && file_exists(PHOTOS."thumbs/".$info['album_thumb'])) ? PHOTOS."thumbs/".$info['album_thumb'] : '';
		$info['album_link'] = array('link'=>INFUSIONS.'gallery/gallery.php?album_id='.$_GET['album_id'], 'name'=>$info['album_title']);
		$info['max_rows'] = dbcount("(photo_id)", DB_PHOTOS, "album_id='".$_GET['album_id']."'");
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['max_rows'] ? $_GET['rowstart'] : 0;
		if ($info['max_rows'] >0 ){
			// Album stats
			$latest_update = dbarray(dbquery("
					SELECT tp.photo_datestamp, tu.user_id, tu.user_name, tu.user_status FROM ".DB_PHOTOS." tp
					LEFT JOIN ".DB_USERS." tu ON tp.photo_user=tu.user_id
					WHERE album_id='".intval($_GET['album_id'])."' ORDER BY photo_datestamp DESC LIMIT 1")); // get photo data?
			$info['album_stats'] = $locale['422'].$info['max_rows']."<br />\n";
			$info['album_stats'] .= $locale['423'].profile_link($latest_update['user_id'], $latest_update['user_name'], $latest_update['user_status'])."".$locale['424'].showdate("longdate", $latest_update['photo_datestamp'])."\n";
			$result = dbquery("SELECT tp.photo_id, tp.photo_title, tp.photo_thumb1, tp.photo_description, tp.photo_views, tp.photo_datestamp, tp.photo_allow_comments, tp.photo_allow_ratings,
					tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, SUM(tr.rating_vote) AS sum_rating, COUNT(tr.rating_item_id) AS count_votes
					FROM ".DB_PHOTOS." tp
					LEFT JOIN ".DB_USERS." tu ON tp.photo_user=tu.user_id
					LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tp.photo_id AND tr.rating_type='P'
					WHERE album_id='".$_GET['album_id']."' GROUP BY photo_id ORDER BY photo_order LIMIT ".intval($_GET['rowstart']).",".$gallery_settings['thumbs_per_page']);
			$info['photo_rows'] = dbrows($result);

			$info['page_nav'] = $info['max_rows'] > $gallery_settings['thumbs_per_page'] ? makepagenav($_GET['rowstart'], $gallery_settings['thumbs_per_page'], $info['max_rows'], 3, INFUSIONS."gallery/gallery.php?album_id=".$_GET['album_id']."&amp;") : '';
			if ($info['photo_rows'] >0) {
				// this is photo
				while ($data = dbarray($result)) {
					// data manipulation
					$data['album_link'] = array('link'=>INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id'], 'name'=>$data['photo_title']);
					$data['image'] = ($data['photo_thumb1'] && file_exists(PHOTODIR."thumbs/".$data['photo_thumb1'])) ? PHOTODIR."thumbs/".$data['photo_thumb1'] : '';

					$data['title'] = ($data['photo_title']) ? $data['photo_title'] : $data['image'];
					$data['description'] = ($data['photo_description']) ? $data['photo_description'] : '';
					if ($data['photo_allow_comments']) {
						$data['photo_comments'] = array('link'=>$data['album_link']['link'].'#comments', 'name'=>$data['count_votes']);
					}
					if ($data['photo_allow_ratings']) {
						$data['photo_ratings'] = array('link'=>$data['album_link']['link'].'#ratings', 'name'=>$data['sum_rating']>0 ? $data['sum_rating'] : '0');
					}
					$info['item'][] = $data;
				}
			}
		}
	} else {
		redirect(INFUSIONS.'gallery/gallery.php');
	}
	render_photo_category($info);
}
/* Main Index */
else {
	$info['max_rows'] = dbcount("(album_id)", DB_PHOTO_ALBUMS, groupaccess('album_access'));
	$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['max_rows'] ? $_GET['rowstart'] : 0;
	if ($info['max_rows'] > 0) {
		$info['page_nav'] = ($info['max_rows'] > $gallery_settings['thumbs_per_page']) ? makepagenav($_GET['rowstart'], $gallery_settings['thumbs_per_page'], $info['max_rows'], 3) : '';
		$result = dbquery("SELECT ta.album_id, ta.album_title, ta.album_description, ta.album_thumb, ta.album_datestamp,
			tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_PHOTO_ALBUMS." ta
			LEFT JOIN ".DB_USERS." tu ON ta.album_user=tu.user_id
			".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('album_access')." ORDER BY album_order
			LIMIT ".$_GET['rowstart'].",".$gallery_settings['thumbs_per_page']);
		while ($data = dbarray($result)) {
			$data['album_link'] = array('link'=>INFUSIONS."gallery/gallery.php?album_id=".$data['album_id'], 'name'=>$data['album_title']);
			$photo_directory = !SAFEMODE ? "album_".$data['album_id'] : '';
			$data['image'] = ($data['album_thumb'] && file_exists(PHOTOS.$photo_directory."/thumbs/".$data['album_thumb'])) ? PHOTOS.$photo_directory."/thumbs/".$data['album_thumb'] : '';
			$data['title'] = $data['album_title'] ? $data['album_title'] : $locale['402'];
			$data['description'] = $data['album_description'] ? $data['album_description'] : '';
			$_photo = dbquery("SELECT pp.photo_user, u.user_id, u.user_name, u.user_status, u.user_avatar
			FROM ".DB_PHOTOS." pp LEFT JOIN ".DB_USERS." u on u.user_id=pp.photo_user WHERE album_id='".$data['album_id']."'
			ORDER BY photo_datestamp DESC LIMIT 0,5
			");
			$data['photo_rows'] = dbrows($_photo);
			$user = array();
			if ($data['photo_rows']>0) {
				while ($_photo_data = dbarray($_photo))
				$user[$_photo_data['user_id']] = $_photo_data; // distinct value.
			}
			$data['photo_user'] = $user;
			$info['item'][] = $data;
		}
	}
	render_photo_main($info);
}

function photo_thumbnail($data) {
	global $locale, $gallery_settings;
	echo "<div class='panel panel-default tbl-border'>\n";
	echo "<div class='p-0'>\n";
	echo "<!--photogallery_album_photo_".$data['photo_id']."-->";
	echo "<a href='".INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id']."' class='photogallery_album_photo_link'>\n";
	$thumb_img = ($data['photo_thumb1'] && file_exists(PHOTODIR.$data['photo_thumb1'])) ? PHOTODIR.$data['photo_thumb1'] : DOWNLOADS."images/no_image.jpg";
	$title = ($data['album_thumb1'] && file_exists(PHOTOS.$data['album_thumb1'])) ? $data['album_thumb1'] : $locale['432'];
	echo "<img class='photogallery_album_photo img-responsive' style='min-width: 100%;' src='".$thumb_img."' title='$title' alt='$title' />\n";
	echo "</a>\n";
	echo "</div>\n<div class='panel-body photogallery_album_photo_info'>\n";
	echo "<a href='".INFUSIONS."gallery/gallery.php?photo_id=".$data['photo_id']."' class='photogallery_album_photo_link'><strong>".$data['photo_title']."</strong></a>\n";
	echo "</div>\n<div class='panel-body photogallery_album_photo_info' style='border-top:1px solid #ddd'>\n";
	echo "<!--photogallery_album_photo_info-->\n";
	echo "<span class='display-inline-block'>\n";
	echo($data['photo_allow_ratings'] ? $locale['437'].($data['count_votes'] > 0 ? str_repeat("<img src='".get_image("star")."' alt='*' style='vertical-align:middle' />", ceil($data['sum_rating']/$data['count_votes'])) : $locale['438'])."<br />\n" : "");
	echo "</span>\n<br/>\n";
	echo "</div>\n<div class='panel-body photogallery_album_photo_info' style='border-top:1px solid #ddd'>\n";
	echo "<span> ".$locale['434'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])." </span>";
	echo "</div>\n<div class='panel-body photogallery_album_photo_info' style='border-top:1px solid #ddd'>\n";
	echo "<span class='m-r-10'><abbr title='".$locale['433'].showdate("shortdate", $data['photo_datestamp'])."'><i title='".$locale['433'].showdate("shortdate", $data['photo_datestamp'])."' class='entypo calendar text-lighter'></i></abbr></span>";
	$photo_comments = dbcount("(comment_id)", DB_COMMENTS, "comment_type='P' AND comment_item_id='".$data['photo_id']."'");
	$comments_text = ($data['photo_allow_comments'] ? ($photo_comments == 1 ? $locale['436b'] : $locale['436']).$photo_comments : "");
	echo "<span class='m-r-10'><abbr title='".$comments_text."'><i class='entypo icomment text-lighter'></i></abbr> $photo_comments</abbr></span>";
	echo "<span class='m-r-10'><abbr title='".$locale['434'].$data['user_name']."'><i class='entypo user text-lighter'></i></span>";
	echo "<span><abbr title='".$locale['435'].$data['photo_views']."'><i class='entypo eye text-lighter'></i></abbr> ".$data['photo_views']."</span>";
	echo "</div></div>\n";
}

require_once THEMES."templates/footer.php";
