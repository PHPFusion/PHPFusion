<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Functions.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Forums;

class Functions {


	/**
	 * Appends increment integer on multiple files on same post
	 * @param $file
	 * @return string
	 */
	public static function attach_exists($file) {
		$dir = INFUSIONS."forum/attachments/";
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
	 * @staticvar array $forum_rank_cache
	 * @return array Cached forum ranks
	 */
	public static function forum_rank_cache() {
		global $forum_settings;
		static $forum_rank_cache = NULL;
		$known_types = array(
			0 => 'post',
			1 => 'mod'
		);
		if ($forum_rank_cache === NULL and $forum_settings['forum_ranks']) {
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
	 * @param int   $posts  The number of posts of the member
	 * @param int   $level  The level of the member
	 * @param array $groups The groups of the member
	 * @return string HTML source of forum rank images
	 */
	public static function show_forum_rank($posts, $level, $groups) {
		global $forum_settings;

		$ranks = array();

		if (!$forum_settings['forum_ranks'])
			return '';

		$image = ($forum_settings['forum_rank_style'] == 1);

		$forum_rank_cache = forum_rank_cache();

		$forum_rank_css_class  = array(
			'-101' => 'label-member',
			'-102' => 'label-mod',
			'-103' => 'label-super-admin',
		);

		$forum_rank_icon_class = array(
			'-101' => 'fa fa-user fa-fw',
			'-102' => 'fa fa-shield fa-fw',
			'-103' => 'fa fa-shield fa-fw',
		);

		// Moderator ranks
		if (!empty($forum_rank_cache['mod'])) {
			if ($level < USER_LEVEL_MEMBER) {
				foreach ($forum_rank_cache['mod'] as $rank) {
					if (isset($rank['rank_apply']) && $level == $rank['rank_apply']) {
						$ranks[] = $rank;
						break;
					}
				}
			}
		}

		// Special ranks
		if (!empty($forum_rank_cache['special'])) {
			if (!empty($groups)) {
				if (!is_array($groups)) {
					$groups = explode(".", $groups);
				}

				foreach ($forum_rank_cache['special'] as $rank) {
					if (!isset($rank['rank_apply'])) continue;

					if (in_array($rank['rank_apply'], $groups)) {
						$ranks[] = $rank;
					}
				}
			}
		}

		// Post count ranks
		if (!empty($forum_rank_cache['post'])) {
			if (!$ranks) {
				foreach ($forum_rank_cache['post'] as $rank) {
					if (!isset($rank['rank_apply'])) continue;

					if ($posts >= $rank['rank_posts']) {
						$ranks['post_rank'] = $rank;
					}
				}

				if (!$ranks && isset($forum_rank_cache['post'][0])) {
					$ranks['post_rank'] = $forum_rank_cache['post'][0];
				}
			}
		}

		// forum ranks must be the highest
		$res = '';
		foreach ($ranks as $rank) {
			if ($image) {
				if(isset($rank['rank_title']) && isset($rank['rank_image']))
					$res .= $rank['rank_title']."<br />\n<img src='".RANKS.$rank['rank_image']."' alt='' style='border:0' /><br />";
			} else {
				if(isset($rank['rank_apply']) && isset($rank['rank_title']))
					$res .= "<label class='label ".(isset($forum_rank_css_class[$rank['rank_apply']]) ? $forum_rank_css_class[$rank['rank_apply']] : "label-default")." '><i class='".(isset($forum_rank_icon_class[$rank['rank_apply']]) ? $forum_rank_icon_class[$rank['rank_apply']] : "fa fa-user fa-fw")."'></i> ".$rank['rank_title']."</label>\n";
			}
		}

		return $res;
	}

	/**
	 * Display an image
	 * @param $file
	 * @return string
	 */
	public static function display_image($file) {
		$size = @getimagesize(INFUSIONS."forum/attachments/".$file);
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
			$res = "<a href='".INFUSIONS."forum/attachments/".$file."'><img src='".INFUSIONS."forum/attachments/".$file."' width='".$img_w."' height='".$img_h."' style='border:0;' alt='".$file."' /></a>";
		} else {
			$res = "<img src='".INFUSIONS."forum/attachments/".$file."' width='".$img_w."' height='".$img_h."' style='border:0;' alt='".$file."' />";
		}
		return $res;
	}

	/**
	 * Display attached image with a certain given width and height.
	 * @param        $file
	 * @param int    $width
	 * @param int    $height
	 * @param string $rel
	 * @return string
	 */
	public static function display_image_attach($file, $width = 50, $height = 50, $rel = "") {
		$size = @getimagesize(INFUSIONS."forum/attachments/".$file);
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
		$res = "<a target='_blank' href='".INFUSIONS."forum/attachments/".$file."' rel='attach_".$rel."' title='".$file."'><img class='img-thumbnail' src='".INFUSIONS."forum/attachments/".$file."' alt='".$file."' style='border:0px; width:".$img_w."px; height:".$img_h."px;' /></a>\n";
		return $res;
	}

}