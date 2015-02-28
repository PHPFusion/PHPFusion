<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_include.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

$imagetypes = array(
	".bmp",
	".gif",
	".iff",
	".jpg",
	".jpeg",
	".png",
	".psd",
	".tiff",
	".wbmp"
);

function attach_exists($file) {
	$dir = FORUM."attachments/";
	$i = 1;
	$file_name = substr($file, 0, strrpos($file, "."));
	$file_ext = strrchr($file, ".");
	while (file_exists($dir.$file)) {
		$file = $file_name."_".$i.$file_ext;
		$i++;
	}
	return $file;
}

function forum_rank_cache() {
	global $settings, $forum_mod_rank_cache, $forum_post_rank_cache, $forum_special_rank_cache;
	$forum_post_rank_cache = array();
	$forum_mod_rank_cache = array();
	$forum_special_rank_cache = array();
	if ($settings['forum_ranks']) {
		$result = dbquery("SELECT rank_title, rank_image, rank_type, rank_posts, rank_apply FROM ".DB_FORUM_RANKS." ORDER BY rank_apply DESC, rank_posts ASC");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				if ($data['rank_type'] == 0) {
					$forum_post_rank_cache[] = $data;
				} elseif ($data['rank_type'] == 1) {
					$forum_mod_rank_cache[] = $data;
				} else {
					$forum_special_rank_cache[] = $data;
				}
			}
		}
	}
}

function show_forum_rank($posts, $level, $groups) {
	global $locale, $settings, $forum_mod_rank_cache, $forum_post_rank_cache, $forum_special_rank_cache;
	$res = "";
	if ($settings['forum_ranks']) {
		if (!$forum_post_rank_cache) { forum_rank_cache(); }
		// Moderator ranks
		if ($level > 101 && is_array($forum_mod_rank_cache) && count($forum_mod_rank_cache)) {
			for ($i = 0; $i < count($forum_mod_rank_cache) && !$res; $i++) {
				if ($level == $forum_mod_rank_cache[$i]['rank_apply']) {
					$res = $forum_mod_rank_cache[$i]['rank_title']."<br />\n<img src='".RANKS.$forum_mod_rank_cache[$i]['rank_image']."' alt='' style='border:0' /><br />";
				}
			}
		}
		// Special ranks
		if ($groups != "" && is_array($forum_special_rank_cache) && count($forum_special_rank_cache)) {
			for ($i = 0; $i < count($forum_special_rank_cache); $i++) {
				if (in_array($forum_special_rank_cache[$i]['rank_apply'], explode(".", $groups))) {
					$res .= $forum_special_rank_cache[$i]['rank_title']."<br />\n<img src='".RANKS.$forum_special_rank_cache[$i]['rank_image']."' alt='' style='border:0' /><br />";
				}
			}
		}
		// Post count ranks
		if (!$res && is_array($forum_post_rank_cache) && count($forum_post_rank_cache)) {
			for ($i = 0; $i < count($forum_post_rank_cache); $i++) {
				if ($posts >= $forum_post_rank_cache[$i]['rank_posts']) {
					$res = $forum_post_rank_cache[$i]['rank_title']."<br />\n<img src='".RANKS.$forum_post_rank_cache[$i]['rank_image']."' alt='' style='border:0' /><br />";
				}
			}
			if (!$res) {
				$res .= $forum_post_rank_cache[0]['rank_title']."<br />\n<img src='".RANKS.$forum_post_rank_cache[0]['rank_image']."' alt='' style='border:0' /><br />";
			}
		}
	}
	return $res;
}

function display_image($file) {
	$size = @getimagesize(FORUM."attachments/".$file);
	
	if ($size[0] > 300 || $size[1] > 200) {
		if ($size[0] <= $size[1]) {
			$img_w = round(($size[0] * 200) / $size[1]);
			$img_h = 200;
		} elseif ($size[0] > $size[1]) {
			$img_w = 300;
			$img_h = round(($size[1] * 300) / $size[0]);
		} else {
			$img_w = 300;
			$img_h = 200;
		}
	} else {
		$img_w = $size[0];
		$img_h = $size[1];
	}
	
	if ($size[0] != $img_w || $size[1] != $img_h) {
		$res = "<a href='".FORUM."attachments/".$file."'><img src='".FORUM."attachments/".$file."' width='".$img_w."' height='".$img_h."' style='border:0;' alt='".$file."' /></a>";
	} else {
		$res = "<img src='".FORUM."attachments/".$file."' width='".$img_w."' height='".$img_h."' style='border:0;' alt='".$file."' />";
	}
	
	return $res;
}

function display_image_attach($file, $width = 50, $height = 50, $rel = "") {
	$size = @getimagesize(FORUM."attachments/".$file);
		
	if ($size [0] > $height || $size [1] > $width) {
		if ($size [0] < $size [1]) {
			$img_w = round ( ($size [0] * $width) / $size [1] );
			$img_h = $width;
		} elseif ($size [0] > $size [1]) {
			$img_w = $height;
			$img_h = round ( ($size [1] * $height) / $size [0] );
		} else {
			$img_w = $height;
			$img_h = $width;
		}
	} else {
		$img_w = $size [0];
		$img_h = $size [1];
	}
	
	
	$res = "<a target='_blank' href='".FORUM."attachments/".$file."' rel='attach_".$rel."' title='".$file."'><img src='".FORUM."attachments/".$file."' alt='".$file."' style='border:0px; width:".$img_w."px; height:".$img_h."px;' /></a>\n";
	
	return $res;
}

if (isset($_GET['getfile']) && isnum($_GET['getfile'])) {
	$result = dbquery("SELECT attach_id, attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE attach_id='".$_GET['getfile']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		if (file_exists(FORUM."attachments/".$data['attach_name'])) {
			$attach_count = dbquery("UPDATE ".DB_FORUM_ATTACHMENTS." SET attach_count=attach_count+1 WHERE attach_id='".$data['attach_id']."'");
			require_once INCLUDES."class.httpdownload.php";
			ob_end_clean();
			$object = new httpdownload;
			$object->set_byfile(FORUM."attachments/".$data['attach_name']);
			$object->use_resume = true;
			$object->download();
		} else {
			redirect("index.php");
		}
	}
	exit;
}
?>