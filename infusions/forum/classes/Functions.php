<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Functions.php
| Author: Hien (Frederick MC Chan)
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
/* Authentication */

/**
 * Verify Forum ID
 * @param $forum_id
 * @return bool|string
 */
public static function verify_forum($forum_id) {
	if (isnum($forum_id)) {
		return (int) dbcount("('forum_id')", DB_FORUMS, "forum_id='".$forum_id."' AND ".groupaccess('forum_access')." ");
	}

	return false;
}

/**
 * Verify Thread ID
 * @param $thread_id
 * @return bool|string
 */
public static function verify_thread($thread_id) {
	if (isnum($thread_id)) {
		return dbcount("('forum_id')", DB_FORUM_THREADS, "thread_id='".$thread_id."'");
	}
	return false;
}

/**
 * Verify Post ID
 * @param $post_id
 * @return bool|string
 */
public static function verify_post($post_id) {
	if (isnum($post_id)) {
		return dbcount("('post_id')", DB_FORUM_POSTS, "post_id='".$post_id."'");
	}
	return false;
}

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
 *
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
 *
 * @param int $posts The number of posts of the member
 * @param int $level The level of the member
 * @param array $groups The groups of the member
 * @return string HTML source of forum rank images
 */
public static function show_forum_rank($posts, $level, $groups) {
	global $forum_settings;
	$ranks = array();
	if (!$forum_settings['forum_ranks']) {
		return '';
	}
	$image = 0;
	if ($forum_settings['forum_rank_style'] == 1) {
		$image = 1;
	}
	$forum_rank_cache = forum_rank_cache();
	$forum_rank_css_class = array(
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
	if ($level < USER_LEVEL_MEMBER) {
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
		if ($image) {
			$res .= $rank['rank_title']."<br />\n<img src='".RANKS.$rank['rank_image']."' alt='' style='border:0' /><br />";
		} else {
			$res .= "<label class='label ".(isset($forum_rank_css_class[$rank['rank_apply']]) ? $forum_rank_css_class[$rank['rank_apply']] : "label-default")." '><i class='".$forum_rank_icon_class[$rank['rank_apply']]."'></i> ".$rank['rank_title']."</label>\n";
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

/**
 * Generate iMOD const
 * @param $info
 */
public static function define_forum_mods($info) {
	if (iSUPERADMIN && !defined('iMOD')) { define("iMOD", TRUE); }
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

/**
 * Parse Forum Moderators Links
 * @param $forum_mods
 * @return string
 */
public static function parse_forumMods($forum_mods) {
	$moderators = '';
	if ($forum_mods) {
		$_mgroup = explode('.', $forum_mods);
		if (!empty($_mgroup)) {
			foreach ($_mgroup as $mod_group) {
				if ($moderators) $moderators .= ", ";
				$moderators .= $mod_group < -101 ? "<a href='".BASEDIR."profile.php?group_id=".$mod_group."'>".getgroupname($mod_group)."</a>" : getgroupname($mod_group);
			}
		}
	}
	return $moderators;
}

/**
 * Get Recent Topics per forum.
 * @param int $forum_id - all if 0.
 * @return mixed
 */
public static function get_recentTopics($forum_id = 0) {
	global $forum_settings;
	$result = dbquery("SELECT tt.*, tf.*, tp.post_id, tp.post_datestamp,
			u.user_id, u.user_name as last_user_name, u.user_status as last_user_status, u.user_avatar as last_user_avatar,
			uc.user_id AS s_user_id, uc.user_name AS author_name, uc.user_status AS author_status, uc.user_avatar AS author_avatar,
			count(v.post_id) AS vote_count
			FROM ".DB_FORUM_THREADS." tt
			INNER JOIN ".DB_FORUMS." tf ON (tt.forum_id=tf.forum_id)
			LEFT JOIN ".DB_FORUM_POSTS." tp on (tt.thread_lastpostid = tp.post_id)
			LEFT JOIN ".DB_USERS." u ON u.user_id=tt.thread_lastuser
			LEFT JOIN ".DB_USERS." uc ON uc.user_id=tt.thread_author
			LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = tt.thread_id AND tp.post_id = v.post_id
			".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")."
			".groupaccess('tf.forum_access')." AND tt.thread_hidden='0'
			".($forum_id ? "AND forum_id='".intval($forum_id)."'" : '')."
			GROUP BY thread_id ORDER BY tt.thread_lastpost LIMIT 0, ".$forum_settings['threads_per_page']."");
			
	$info['rows'] = dbrows($result);
	if ($info['rows'] > 0) {
		while ($data = dbarray($result)) {
			$data['moderators'] = self::parse_forumMods($data['forum_mods']);
			$info['item'][$data['thread_id']] = $data;
		}
	}
	return $info;
}

/**
 * Get the forum structure
 * @return array
 */
public static function get_forum($forum_id = false, $branch_id = false) { // only need to fetch child.
	global $locale, $userdata, $forum_settings;
	$data = array();
	$index = array();
	$query = dbquery("SELECT tf.forum_id, tf.forum_cat, tf.forum_branch, tf.forum_name, tf.forum_description, tf.forum_image,
	tf.forum_type, tf.forum_mods, tf.forum_threadcount, tf.forum_postcount, tf.forum_order, tf.forum_lastuser, tf.forum_access, tf.forum_lastpost, tf.forum_lastpostid,
	t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject, p.post_message,
	u.user_id, u.user_name, u.user_status, u.user_avatar
	FROM ".DB_FORUMS." tf
	LEFT JOIN ".DB_FORUM_THREADS." t ON tf.forum_lastpostid = t.thread_lastpostid
	LEFT JOIN ".DB_FORUM_POSTS." p on p.thread_id = t.thread_id and p.post_id = t.thread_lastpostid
	LEFT JOIN ".DB_USERS." u ON tf.forum_lastuser = u.user_id
	".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('tf.forum_access')."
	".($forum_id && $branch_id ? "AND tf.forum_id = '".intval($forum_id)."' or tf.forum_cat = '".intval($forum_id)."' OR tf.forum_branch = '".intval($branch_id)."'" : '')."
	GROUP BY tf.forum_id ORDER BY tf.forum_cat ASC, tf.forum_order ASC, t.thread_lastpost DESC");
	while ($row = dbarray($query) and checkgroup($row['forum_access'])) {
		// add custom data here.
		$row['forum_moderators'] = self::parse_forumMods($row['forum_mods']);
		// get new status
		$row['forum_new_status'] = '';
		$forum_match = "\|".$row['forum_lastpost']."\|".$row['forum_id'];
		$last_visited = (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time();
		if ($row['forum_lastpost'] > $last_visited) {
			if (iMEMBER && ($row['forum_lastuser'] !== $userdata['user_id'] || !preg_match("({$forum_match}\.|{$forum_match}$)", $userdata['user_threads']))) {
				$row['forum_new_status'] = "<span class='forum-new-icon'><i title='".$locale['forum_0260']."' class='".self::get_forumIcons('new')."'></i></span>";
			}
		}
		$row['forum_link'] = INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$row['forum_id']."&amp;parent_id=".$row['forum_cat']."&amp;forum_branch=".$row['forum_branch'];
		$row['forum_description'] = nl2br(parseubb($row['forum_description']));
		$row['forum_postcount'] = format_word($row['forum_postcount'], $locale['fmt_post']);
		$row['forum_threadcount'] = format_word($row['forum_threadcount'], $locale['fmt_thread']);
		/**
		 * Last post section
		 */
		if ($row['forum_lastpostid']) {
			$last_post = array(
				'avatar' => '',
				'avatar_src' => $row['user_avatar'] && file_exists(IMAGES.'avatars/'.$row['user_avatar']) && !is_dir(IMAGES.'avatars/'.$row['user_avatar']) ? IMAGES.'avatars/'.$row['user_avatar'] : '',
				'message' => fusion_first_words(parseubb(parsesmileys($row['post_message'])), 10),
				'profile_link' => profile_link($row['forum_lastuser'], $row['user_name'], $row['user_status']),
				'time' => timer($row['forum_lastpost']),
				'date' => showdate("forumdate", $row['forum_lastpost']),
				'thread_link' => INFUSIONS."forum/viewthread.php?forum_id=".$row['forum_id']."&amp;thread_id=".$row['thread_id'],
				'post_link' => INFUSIONS."forum/viewthread.php?forum_id=".$row['forum_id']."&amp;thread_id=".$row['thread_id']."&amp;pid=".$row['thread_lastpostid']."#post_".$row['thread_lastpostid'],
			);
			if ($forum_settings['forum_last_post_avatar']) {
				$last_post['avatar'] = display_avatar($row, '30px', '', '', 'img-rounded');
			}
			$row['last_post'] = $last_post;
		}
		/**
		 * Icons
		 */
		switch($row['forum_type']) {
			case '1':
				$row['forum_icon'] = "<i class='".self::get_forumIcons('forum')." fa-fw m-r-10'></i>";
				$row['forum_icon_lg'] = "<i class='".self::get_forumIcons('forum')." fa-3x fa-fw m-r-10'></i>";
				break;
			case '2':
				$row['forum_icon'] = "<i class='".self::get_forumIcons('thread')." fa-fw m-r-10'></i>";
				$row['forum_icon_lg'] = "<i class='".self::get_forumIcons('thread')." fa-3x fa-fw m-r-10'></i>";
				break;
			case '3':
				$row['forum_icon'] = "<i class='".self::get_forumIcons('link')." fa-fw m-r-10'></i>";
				$row['forum_icon_lg'] = "<i class='".self::get_forumIcons('link')." fa-3x fa-fw m-r-10'></i>";
				break;
			case '4':
				$row['forum_icon'] = "<i class='".self::get_forumIcons('question')." fa-fw m-r-10'></i>";
				$row['forum_icon_lg'] = "<i class='".self::get_forumIcons('question')." fa-3x fa-fw m-r-10'></i>";
				break;
		}


		$thisref = &$refs[$row['forum_id']];
		$thisref = $row;
		if ($row['forum_cat'] == 0) {
			$index[0][$row['forum_id']] = &$thisref;
		} else {
			$refs[$row['forum_cat']]['child'][$row['forum_id']] = &$thisref;
		}
		//print_p($index);
/*
		$id = $row['forum_id'];
		$parent_id = $row['forum_cat'] === NULL ? "NULL" : $row['forum_cat'];
		$index[$parent_id][$id] = $row; */

	}
	return (array) $index;
}

/**
 * Get thread structure on specific id.
 * @param int $thread_id
 */
public static function get_thread($thread_id = 0) {
	global $userdata;
	$data = array();
	// where parent access is set to admin only and child access is set to public. follow child access.
	// where child access is set to admin only and parent is set to public, follow child access.
	// child to inherit parents access.
	$result = dbquery("SELECT t.*, f.*, f2.forum_name AS forum_cat_name, f2.forum_access as parent_access,
				u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_joined,
				IF (n.thread_id > 0, 1 , 0) as user_tracked
				FROM ".DB_FORUM_THREADS." t
				INNER JOIN ".DB_USERS." u on t.thread_author = u.user_id
				INNER JOIN ".DB_FORUMS." f ON t.forum_id=f.forum_id
				LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
				LEFT JOIN ".DB_FORUM_THREAD_NOTIFY." n on n.thread_id = t.thread_id and n.notify_user = '".intval($userdata['user_id'])."'
				".(multilang_table("FO") ? "WHERE f.forum_language='".LANGUAGE."' AND" : "WHERE")."
				".groupaccess('f.forum_access')." AND t.thread_id='".intval($thread_id)."' AND t.thread_hidden='0'");

		if (dbrows($result) > 0) {
			$data = dbarray($result);
			define_forum_mods($data);
		}
	return (array) $data;
}


static private $forum_icons = array(
	'forum' 	=> 'fa fa-folder fa-fw',
	'thread' 	=> 'fa fa-comments-o fa-fw',
	'link' 		=> 'fa fa-link fa-fw',
	'question' 	=> 'fa fa-mortar-board fa-fw',
	'new'		=> 'fa fa-lightbulb-o fa-fw',
	'poll'		=> 'fa fa-pie-chart fa-fw',
	'lock' 		=> 'fa fa-lock fa-fw',
	'image'		=> 'fa fa-file-picture-o fa-fw',
	'file'		=> 'fa fa-file-zip-o fa-fw',
	'tracked'	=> 'fa fa-bell-o fa-fw',
	'hot'		=> 'fa fa-heartbeat fa-fw',
	'sticky'	=> 'fa fa-thumb-tack fa-fw',
	'reads' 	=> 'fa fa-ticket fa-fw',
);

/**
 * Return array of icons or all icons
 * @return array
 */
public static function get_ForumIcons($type = '') {
	if (isset(self::$forum_icons[$type])) {
		return self::$forum_icons[$type];
	}
	return self::$forum_icons;
}

/**
 * Set and Modify Forum Icons
 * @param array $icons
 * @return array
 */
public static function set_forumIcons(array $icons = array()) {
	self::$forum_icons = array(
		'forum' 	=> !empty($icons['main']) ? $icons['main'] : 'fa fa-folder fa-fw',
		'thread' 	=> !empty($icons['thread']) ? $icons['thread'] : 'fa fa-chat-o fa-fw',
		'link' 		=> !empty($icons['link']) ? $icons['link'] : 'fa fa-link fa-fw',
		'question' 	=> !empty($icons['question']) ? $icons['question'] : 'fa fa-mortar-board fa-fw',
		'new' 	=> !empty($icons['new']) ? $icons['new'] : 'fa fa-lightbulb-o fa-fw',
		'poll' 	=> !empty($icons['poll']) ? $icons['poll'] : 'fa fa-pie-chart fa-fw',
		'lock' 	=> !empty($icons['lock']) ? $icons['lock'] : 'fa fa-lock fa-fw',
		'image' 	=> !empty($icons['image']) ? $icons['image'] : 'fa fa-file-picture-o fa-fw',
		'file' 	=> !empty($icons['file']) ? $icons['file'] : 'fa fa-file-zip-o fa-fw',
		'tracked' 	=> !empty($icons['tracked']) ? $icons['tracked'] : 'fa fa-bell-o fa-fw',
		'hot' 	=> !empty($icons['hot']) ? $icons['hot'] : 'fa fa-heartbeat fa-fw',
		'sticky' 	=> !empty($icons['sticky']) ? $icons['sticky'] : 'fa fa-thumb-tack fa-fw',
		'reads' => !empty($icons['reads']) ? $icons['reads'] : 'fa fa-ticket fa-fw',
	);
}
}
