<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}

// Upload acceptable types for Forum
$imagetypes = array(".bmp", ".gif", ".iff", ".jpg", ".jpeg", ".png", ".psd", ".tiff", ".wbmp");
$attachtypes = explode(",", $settings['attachtypes']);

/* deprecate - replicated filename_exists function */
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

/**
 * Get records of cached forum ranks
 * 
 * @staticvar array $forum_rank_cache
 * @return array Cached forum ranks
 */
function forum_rank_cache() {
	static $forum_rank_cache = NULL;
	$settings = fusion_get_settings();
	$known_types = array(
		0 => 'post',
		1 => 'mod'
	);
	if ($forum_rank_cache === NULL and $settings['forum_ranks']) {
		$forum_rank_cache = array(
			'post' => array(),
			'mod' => array(),
			'special' => array(),
		);
		$result = dbquery("SELECT rank_title, rank_image, rank_type, rank_posts, rank_apply, rank_language FROM ".DB_FORUM_RANKS." ".(multilang_table("FR") ? "WHERE rank_language='".LANGUAGE."'" : "")." ORDER BY rank_apply DESC, rank_posts ASC");
		while ($data = dbarray($result)) {
			$type = isset($known_types[$data['rank_type']]) ? $known_types[$data['rank_type']] : 'special';
			$forum_rank_cache[$type][] = $data;
		}
	}
	return $forum_rank_cache;
}

/**
 * Get HTML source of forum rank images of a member
 * 
 * @param int $posts The number of posts of the member
 * @param int $level The level of the member
 * @param array $groups The groups of the member
 * @return string HTML source of forum rank images
 */
function show_forum_rank($posts, $level, $groups) {
	$settings = fusion_get_settings();
	$ranks = array();
	if (!$settings['forum_ranks']) {
		return '';
	}
	$forum_rank_cache = forum_rank_cache();
	// Moderator ranks
	if ($level > 101) {
		foreach ($forum_rank_cache['mod'] as $rank) {
			if ($level == $rank['rank_apply']) {
				$ranks[] = $rank;
				break;
			}
		}
	}
	// Special ranks
	if (!empty($groups)) {
		if (!is_array($groups)) {
			$groups = explode(".", $groups);
		}
		foreach ($forum_rank_cache['special'] as $rank) {
			if (in_array($rank['rank_apply'], $groups)) {
				$ranks[] = $rank;
			}
		}
	}
	// Post count ranks
	if (!$ranks) {
		foreach ($forum_rank_cache['post'] as $rank) {
			if ($posts >= $rank['rank_posts']) {
				$ranks[] = $rank;
			}
		}
		if (!$ranks) {
			$ranks[] = $forum_rank_cache['post'][0];
		}
	}
	$res = '';
	foreach ($ranks as $rank) {
		$res .= $rank['rank_title']."<br />\n<img src='".RANKS.$rank['rank_image']."' alt='' style='border:0' /><br />";
	}
	return $res;
}

function display_image($file) {
	$size = @getimagesize(FORUM."attachments/".$file);
	if ($size[0] > 300 || $size[1] > 200) {
		if ($size[0] <= $size[1]) {
			$img_w = round(($size[0]*200)/$size[1]);
			$img_h = 200;
		} elseif ($size[0] > $size[1]) {
			$img_w = 300;
			$img_h = round(($size[1]*300)/$size[0]);
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
			$img_w = round(($size [0]*$width)/$size [1]);
			$img_h = $width;
		} elseif ($size [0] > $size [1]) {
			$img_w = $height;
			$img_h = round(($size [1]*$height)/$size [0]);
		} else {
			$img_w = $height;
			$img_h = $width;
		}
	} else {
		$img_w = $size [0];
		$img_h = $size [1];
	}
	$res = "<a target='_blank' href='".FORUM."attachments/".$file."' rel='attach_".$rel."' title='".$file."'><img class='img-thumbnail' src='".FORUM."attachments/".$file."' alt='".$file."' style='border:0px; width:".$img_w."px; height:".$img_h."px;' /></a>\n";
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
			$object->use_resume = TRUE;
			$object->download();
		} else {
			redirect("index.php");
		}
	}
	exit;
}

function define_forum_mods($info) {
	if (iSUPERADMIN) { define("iMOD", TRUE); }
	if (!defined("iMOD") && iMEMBER && $info['forum_mods']) {
		$mod_groups = explode(".", $info['forum_mods']);
		foreach ($mod_groups as $mod_group) {
			if (!defined("iMOD") && checkgroup($mod_group)) {
				define("iMOD", TRUE);
			}
		}
	}
	if (!defined("iMOD")) { define("iMOD", FALSE);}
}

?>