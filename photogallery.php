<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: photogallery.php
| Author: Nick Jones (Digitanium)
| Co-Author: Robert Gaudyn (Wooya)
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
include LOCALE.LOCALESET."photogallery.php";
define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
add_to_title($locale['global_200'].$locale['400']);
if (isset($_GET['photo_id']) && isnum($_GET['photo_id'])) {
	$result = dbquery("SELECT tp.photo_title, tp.photo_description, tp.photo_filename, tp.photo_thumb2, tp.photo_datestamp, tp.photo_views,
		tp.photo_order, tp.photo_allow_comments, tp.photo_allow_ratings, ta.album_id, ta.album_title, ta.album_access,
		tu.user_id, tu.user_name, tu.user_status, SUM(tr.rating_vote) AS sum_rating, COUNT(tr.rating_item_id) AS count_votes
		FROM ".DB_PHOTOS." tp
		LEFT JOIN ".DB_PHOTO_ALBUMS." ta USING (album_id)
		LEFT JOIN ".DB_USERS." tu ON tp.photo_user=tu.user_id
		LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tp.photo_id AND tr.rating_type='P'
		WHERE photo_id='".$_GET['photo_id']."' GROUP BY tp.photo_id");
	$data = dbarray($result);
	if (!checkgroup($data['album_access'])) {
		redirect(FUSION_SELF);
	} else {
		define("PHOTODIR", PHOTOS.(!SAFEMODE ? "album_".$data['album_id']."/" : ""));
		include INCLUDES."comments_include.php";
		include INCLUDES."ratings_include.php";
		$result = dbquery("UPDATE ".DB_PHOTOS." SET photo_views=(photo_views+1) WHERE photo_id='".$_GET['photo_id']."'");
		$pres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order='".($data['photo_order']-1)."' AND album_id='".$data['album_id']."'");
		$nres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order='".($data['photo_order']+1)."' AND album_id='".$data['album_id']."'");
		$fres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order='1' AND album_id='".$data['album_id']."'");
		$lastres = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." WHERE album_id='".$data['album_id']."'"), 0);
		$lres = dbquery("SELECT photo_id FROM ".DB_PHOTOS." WHERE photo_order>='".$lastres."' AND album_id='".$data['album_id']."'");
		if (dbrows($pres)) $prev = dbarray($pres);
		if (dbrows($nres)) $next = dbarray($nres);
		if (dbrows($fres)) $first = dbarray($fres);
		if (dbrows($lres)) $last = dbarray($lres);
		opentable($locale['450']);
		echo "<!--pre_photo-->";
		if ($settings['photo_watermark']) {
			if ($settings['photo_watermark_save']) {
				$parts = explode(".", $data['photo_filename']);
				$wm_file1 = $parts[0]."_w1.".$parts[1];
				$wm_file2 = $parts[0]."_w2.".$parts[1];
				if (!file_exists(PHOTODIR.$wm_file1)) {
					if ($data['photo_thumb2']) {
						$photo_thumb = "photo.php?photo_id=".$_GET['photo_id'];
					}
					$photo_file = "photo.php?photo_id=".$_GET['photo_id']."&amp;full";
				} else {
					if ($data['photo_thumb2']) {
						$photo_thumb = PHOTODIR.$wm_file1;
					}
					$photo_file = PHOTODIR.$wm_file2;
				}
			} else {
				if ($data['photo_thumb2']) {
					$photo_thumb = "photo.php?photo_id=".$_GET['photo_id'];
				}
				$photo_file = "photo.php?photo_id=".$_GET['photo_id']."&amp;full";
			}
			$photo_size = @getimagesize(PHOTODIR.$data['photo_filename']);
		} else {
			$photo_thumb = $data['photo_thumb2'] ? PHOTODIR.$data['photo_thumb2'] : "";
			$photo_file = PHOTODIR.$data['photo_filename'];
			$photo_size = @getimagesize($photo_file);
		}
		add_to_title($locale['global_201'].$data['photo_title']);
		add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
		add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
		add_to_head("<script type='text/javascript'>\n
			/* <![CDATA[ */\n
				jQuery(document).ready(function(){
					jQuery('a.photogallery_photo_link').colorbox({
						width:'80%', height:'80%', photo:true
					});
				});\n
			/* ]]>*/\n
		</script>\n");
		echo "<ol class='breadcrumb'>\n";
		echo "<li><a href='".BASEDIR."photogallery.php'>".$locale['400']."</a></li>\n";
		echo "<li><a href='".BASEDIR."photogallery.php?album_id=".$data['album_id']."'>".$data['album_title']."</a></li>\n";
		echo ($data['photo_title'] ? "<li><strong>".$data['photo_title']."</strong>" : "")."\n</li>\n";
		echo "</ol>\n";
		if ((isset($prev['photo_id']) && isnum($prev['photo_id'])) || (isset($next['photo_id']) && isnum($next['photo_id']))) {
			if (isset($prev) && isset($first)) {
				echo "<td width='1%' class='tbl2'><a href='".BASEDIR."photogallery.php?photo_id=".$first['photo_id']."' title='".$locale['459']."'>".get_image("go_first", $locale['459'], "border:none;", "", "")."</a></td>\n";
			}
			if (isset($prev)) {
				echo "<td width='1%' class='tbl2'><a href='".BASEDIR."photogallery.php?photo_id=".$prev['photo_id']."' title='".$locale['451']."'>".get_image("go_previous", $locale['451'], "border:none;", "", "")."</a></td>\n";
			}
			if (isset($next)) {
				echo "<td width='1%' class='tbl2'><a href='".BASEDIR."photogallery.php?photo_id=".$next['photo_id']."' title='".$locale['452']."'>".get_image("go_next", $locale['452'], "border:none;", "", "")."</a></td>\n";
			}
			if (isset($next) && isset($last)) {
				echo "<td width='1%' class='tbl2'><a href='".BASEDIR."photogallery.php?photo_id=".$last['photo_id']."' title='".$locale['460']."'>".get_image("go_last", $locale['460'], "border:none;", "", "")."</a></td>\n";
			}
		}
		echo "<div id='photogallery' class='panel-default tbl-border'>\n";
		echo "<a target='_blank' href='".$photo_file."' class='photogallery_photo_link' title='".(!empty($data['photo_title']) ? $data['photo_title'] : $data['photo_filename'])."'><!--photogallery_photo_".$_GET['photo_id']."-->";
		echo "<img class='img-responsive' src='".(isset($photo_thumb) && !empty($photo_thumb) ? $photo_thumb : $photo_file)."' alt='".(!empty($data['photo_title']) ? $data['photo_title'] : $data['photo_filename'])."' style='border:0px' class='photogallery_photo' /></a>\n";
		if ($data['photo_description']) {
			echo "<div class='m-l-10 m-t-5'>\n";
			echo nl2br(parseubb($data['photo_description'], "b|i|u|center|small|url|mail|img|quote"))."<br /><br />\n";
			echo "</div>\n";
		}
		echo "</div>\n";
		echo "<!--photogallery_photo_desc-->\n";
		echo "<div class='list-group photogallery_photo_desc m-t-10 m-b-10'>\n";
		echo "<div class='list-group-item tbl-border'>\n";
		echo "<span class='col-xs-12 col-sm-3 col-md-3 col-lg-3'><strong>".$locale['433']."</strong></span>".showdate("shortdate", $data['photo_datestamp'])."\n";
		echo "</div>\n";
		echo "<div class='list-group-item tbl-border'>\n";
		echo "<span class='col-xs-12 col-sm-3 col-md-3 col-lg-3'><strong>".$locale['434']."</strong></span>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."\n";
		echo "</div>\n";
		echo "<div class='list-group-item tbl-border'>\n";
		echo "<span class='col-xs-12 col-sm-3 col-md-3 col-lg-3'><strong>".$locale['454']."</strong></span>".$photo_size[0]." x ".$photo_size[1]." ".$locale['455']."\n";
		echo "</div>\n";
		echo "<div class='list-group-item tbl-border'>\n";
		echo "<span class='col-xs-12 col-sm-3 col-md-3 col-lg-3'><strong>".$locale['456']."</strong></span>".parsebytesize($settings['photo_watermark'] ? filesize(PHOTODIR.$data['photo_filename']) : filesize($photo_file))."<br />\n";
		echo "</div>\n";
		$photo_comments = dbcount("(comment_id)", DB_COMMENTS, "comment_type='P' AND comment_item_id='".$_GET['photo_id']."'");
		echo "<div class='list-group-item tbl-border'>\n";
		echo "<span class='col-xs-12 col-sm-3 col-md-3 col-lg-3'><strong>".($data['photo_allow_comments'] ? ($photo_comments == 1 ? $locale['436b'] : $locale['436'])."<br />\n" : "")."</strong></span>\n".$photo_comments."";
		echo "</div>\n";
		echo "<div class='list-group-item tbl-border'>\n";
		$ratings = ($data['photo_allow_ratings'] ? ($data['count_votes'] > 0 ? str_repeat("<img src='".get_image("star")."' alt='*' style='vertical-align:middle' />", ceil($data['sum_rating']/$data['count_votes'])) : $locale['438'])."\n" : "");
		echo "<span class='col-xs-12 col-sm-3 col-md-3 col-lg-3'><strong>".$locale['437']."</strong></span>\n".$ratings."";
		echo "</div>\n";
		echo "<div class='list-group-item tbl-border'>\n";
		echo "<span class='col-xs-12 col-sm-3 col-md-3 col-lg-3'><strong>".$locale['457']."</strong></span>\n".$data['photo_views']."";
		echo "</div>\n";
		echo "</div>\n";
		echo "<!--sub_photo-->";
		if ($data['photo_allow_comments']) {
			showcomments("P", DB_PHOTOS, "photo_id", $_GET['photo_id'], BASEDIR."photogallery.php?photo_id=".$_GET['photo_id']);
		}
		if ($data['photo_allow_ratings']) {
			showratings("P", $_GET['photo_id'], BASEDIR."photogallery.php?photo_id=".$_GET['photo_id']);
		}
		closetable();
	}
} elseif (isset($_GET['album_id']) && isnum($_GET['album_id'])) {
	define("PHOTODIR", PHOTOS.(!SAFEMODE ? "album_".$_GET['album_id']."/" : ""));
	$result = dbquery("SELECT album_id, album_title, album_description, album_thumb, album_access FROM ".DB_PHOTO_ALBUMS." WHERE album_id='".$_GET['album_id']."'");
	if (!dbrows($result)) {
		redirect(FUSION_SELF);
	} else {
		$data = dbarray($result);
		if (!checkgroup($data['album_access'])) {
			redirect(FUSION_SELF);
		} else {
			add_to_title($locale['global_201'].$data['album_title']);
			opentable($locale['430']);
			$rows = dbcount("(photo_id)", DB_PHOTOS, "album_id='".$_GET['album_id']."'");
			$album_text = $locale['425']."\n";
			if ($rows) {
				$pdata = dbarray(dbquery("
					SELECT tp.photo_datestamp, tu.user_id, tu.user_name, tu.user_status FROM ".DB_PHOTOS." tp
					LEFT JOIN ".DB_USERS." tu ON tp.photo_user=tu.user_id
					WHERE album_id='".$_GET['album_id']."' ORDER BY photo_datestamp DESC LIMIT 1"));
				$album_text = $locale['422']."$rows<br />\n";
				$album_text .= $locale['423'].profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status'])."".$locale['424'].showdate("longdate", $pdata['photo_datestamp'])."\n";
				if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
					$_GET['rowstart'] = 0;
				}
				$result = dbquery("SELECT tp.photo_id, tp.photo_title, tp.photo_thumb1, tp.photo_views, tp.photo_datestamp, tp.photo_allow_comments, tp.photo_allow_ratings,
					tu.user_id, tu.user_name, tu.user_status, SUM(tr.rating_vote) AS sum_rating, COUNT(tr.rating_item_id) AS count_votes
					FROM ".DB_PHOTOS." tp
					LEFT JOIN ".DB_USERS." tu ON tp.photo_user=tu.user_id
					LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tp.photo_id AND tr.rating_type='P'
					WHERE album_id='".$_GET['album_id']."' GROUP BY photo_id ORDER BY photo_order LIMIT ".$_GET['rowstart'].",".$settings['thumbs_per_page']);
				$counter = 0;
				echo "<ol class='breadcrumb'>";
				echo "<li><a href='".BASEDIR."photogallery.php'>".$locale['400']."</a></li>\n";
				echo "<li><a href='".BASEDIR."photogallery.php?album_id=".$_GET['album_id']."'>".$data['album_title']."</a></li>\n";
				echo "</ol>\n";
			}

			echo "<!--pre_album_info-->";
			echo "<div class='panel panel-default tbl-border'>\n";
			echo "<div class='panel-body'>\n";
			echo "<div class='row m-0'>\n";
			echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
			$thumb_img = ($data['album_thumb'] && file_exists(PHOTOS.$data['album_thumb'])) ? PHOTOS.$data['album_thumb'] : DOWNLOADS."images/no_image.jpg";
			$title = ($data['album_thumb'] && file_exists(PHOTOS.$data['album_thumb'])) ? $data['album_thumb'] : $locale['432'];
			echo "<a class='display-inline-block' style='width:100%;' href='".BASEDIR."photogallery.php?album_id=".$data['album_id']."'>\n";
			echo "<img class='img-responsive img-thumbnail' style='min-width:100%;' src='".$thumb_img."' title='$title' alt='$title' />\n";
			echo "</a>\n";
			echo "</div><div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n";
			echo "<h4 class=' photogallery_album_title'><strong>".$data['album_title']."</strong></h4>\n";
			echo "<span class='photogallery_album_desc'>\n";
			echo "<!--photogallery_album_desc-->\n";
			echo "".nl2br(parseubb($data['album_description']))."";
			echo "</span>\n";
			echo $album_text;
			echo "</div>\n</div>\n</div>\n</div>\n";
			echo "<!--sub_album_info-->";
			closetable();
			if ($rows) {
				if ($rows > $settings['thumbs_per_page']) {
					echo "<div class='m-t-5 m-b-10'>\n".makepagenav($_GET['rowstart'], $settings['thumbs_per_page'], $rows, 3, BASEDIR."photogallery.php?album_id=".$_GET['album_id']."&amp;")."\n</div>\n";
				}
				// new responsive template
				echo "<div class='row m-0'>\n";
				while ($data = dbarray($result)) {
					if ($counter != 0 && ($counter%$settings['thumbs_per_row'] == 0)) {
						echo "</div>\n<div class='row m-0'>\n";
					}
					echo "<div class='col-xs-12 col-sm-".(floor(12/$settings['thumbs_per_row']))." col-md-".(floor(12/$settings['thumbs_per_row']))." col-lg-".(floor(12/$settings['thumbs_per_row']))."'>\n";
					echo photo_thumbnail($data);
					echo "</div>\n";
					$counter++;
				}
				echo "</div>\n";
			}
			if ($rows > $settings['thumbs_per_page']) {
				echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], $settings['thumbs_per_page'], $rows, 3, BASEDIR."photogallery.php?album_id=".$_GET['album_id']."&amp;")."\n</div>\n";
			}
		}
	}
} else {
	opentable($locale['400']);
	$rows = dbcount("(album_id)", DB_PHOTO_ALBUMS, groupaccess('album_access'));
	if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
		$_GET['rowstart'] = 0;
	}
	if ($rows) {
		$result = dbquery("SELECT ta.album_id, ta.album_title, ta.album_thumb, ta.album_datestamp,
			tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_PHOTO_ALBUMS." ta
			LEFT JOIN ".DB_USERS." tu ON ta.album_user=tu.user_id
			".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('album_access')." ORDER BY album_order
			LIMIT ".$_GET['rowstart'].",".$settings['thumbs_per_page']);
		$counter = 0;
		$r = 0;
		$k = 1;
		if ($rows > $settings['thumbs_per_page']) {
			echo "<div align='right' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], $settings['thumbs_per_page'], $rows, 3, BASEDIR."photogallery.php?")."\n</div>\n";
		}
		echo "<div class='row'>\n";
		while ($data = dbarray($result)) {
			if ($counter != 0 && ($counter%$settings['thumbs_per_row'] == 0)) {
				echo "</div>\n<div class='row'>\n";
			}
			echo "<div class='col-xs-12 col-sm-".(floor(12/$settings['thumbs_per_row']))." col-md-".(floor(12/$settings['thumbs_per_row']))." col-lg-".(floor(12/$settings['thumbs_per_row']))."'>\n";
			photo_cat_container($data);
			echo "</div>\n";
			$counter++;
			$k++;
		}
		echo "</div>\n";
		closetable();
		if ($rows > $settings['thumbs_per_page']) {
			echo "<div align='right' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], $settings['thumbs_per_page'], $rows, 3, BASEDIR."photogallery.php?")."\n</div>\n";
		}
	} else {
		echo "<div class='well m-t-20 m-b-20' style='text-align:center'>".$locale['406']."</div>\n";
		closetable();
	}
}
// change setup to 155px on thumb_w and thumb_h on default installation;
function photo_cat_container($data) {
	global $locale, $settings;
	echo "<div class='panel panel-default tbl-border'>\n";
	echo "<div class='panel-body'>\n";
	echo "<p><a href='".BASEDIR."photogallery.php?album_id=".$data['album_id']."'><strong>".$data['album_title']."</strong></a></p>\n";
	// get image no photo.
	$thumb_img = ($data['album_thumb'] && file_exists(PHOTOS.$data['album_thumb'])) ? PHOTOS.$data['album_thumb'] : DOWNLOADS."images/no_image.jpg";
	$title = ($data['album_thumb'] && file_exists(PHOTOS.$data['album_thumb'])) ? $data['album_thumb'] : $locale['402'];
	echo "<a class='display-inline-block' style='width:100%;' href='".BASEDIR."photogallery.php?album_id=".$data['album_id']."'>\n";
	echo "<img class='img-responsive img-thumbnail' style='min-width:100%;' src='".$thumb_img."' title='$title' alt='$title' />\n";
	echo "</a>\n";
	echo "<div class='album-details m-t-10'>\n";
	echo "<span><abbr title='".$locale['403'].showdate("shortdate", $data['album_datestamp'])."'><i class='entypo calendar text-lighter'></i></abbr></span>\n";
	echo "<span><i title='".$locale['404'].$data['user_name']."' class='entypo user text-lighter'></i> ".$data['user_name']."</span>\n";
	$pic_count = dbcount("(photo_id)", DB_PHOTOS, "album_id='".$data['album_id']."'");
	echo "<span><i title='".$locale['405'].$pic_count."' class='entypo picture text-lighter'></i> ".$pic_count."</span>\n";
	echo "</div>\n";
	echo "</div>\n<div class='panel-footer' align='center'>\n";
	echo "<a href='".BASEDIR."photogallery.php?album_id=".$data['album_id']."' class='btn btn-block btn-default button'>".$locale['430']."</a>\n";
	echo "</div>\n</div>\n";
}

function photo_thumbnail($data) {
	global $locale, $settings;
	echo "<div class='panel panel-default tbl-border'>\n";
	echo "<div class='p-0'>\n";
	echo "<!--photogallery_album_photo_".$data['photo_id']."-->";
	echo "<a href='".BASEDIR."photogallery.php?photo_id=".$data['photo_id']."' class='photogallery_album_photo_link'>\n";
	$thumb_img = ($data['photo_thumb1'] && file_exists(PHOTODIR.$data['photo_thumb1'])) ? PHOTODIR.$data['photo_thumb1'] : DOWNLOADS."images/no_image.jpg";
	$title = ($data['album_thumb1'] && file_exists(PHOTOS.$data['album_thumb1'])) ? $data['album_thumb1'] : $locale['432'];
	echo "<img class='photogallery_album_photo img-responsive' style='min-width: 100%;' src='".$thumb_img."' title='$title' alt='$title' />\n";
	echo "</a>\n";
	echo "</div>\n<div class='panel-body photogallery_album_photo_info'>\n";
	echo "<a href='".BASEDIR."photogallery.php?photo_id=".$data['photo_id']."' class='photogallery_album_photo_link'><strong>".$data['photo_title']."</strong></a>\n";
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
?>