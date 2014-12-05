<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: index.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

require_once dirname(__FILE__)."../../maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."forum.php";
require_once INCLUDES."forum_include.php";
include THEMES."templates/global/forum.index.php";
$info = array();
$info['lastvisited'] = (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time();

$_GET['forum_id'] =  (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) ? $_GET['forum_id'] : 0;
$_GET['forum_cat'] =  (isset($_GET['forum_cat']) && isnum($_GET['forum_cat'])) ? $_GET['forum_cat'] : 0;
$_GET['forum_branch'] =  (isset($_GET['forum_branch']) && isnum($_GET['forum_branch'])) ? $_GET['forum_branch'] : 0;
$_GET['parent_id'] =  (isset($_GET['parent_id']) && isnum($_GET['parent_id'])) ? $_GET['parent_id'] : 0;
$ext = isset($_GET['parent_id']) && isnum($_GET['parent_id']) ? "&amp;parent_id=".$_GET['parent_id'] : '';
/* Lets do a new forum */
// start via templating now.
// todo: Your post, New post, unread post, unanswered post, active topics, search, members, the team.
$forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
/* Push breadcrumb */
add_to_title($locale['global_200'].$locale['forum_0000']);

/* Sanitize Globals */
$_GET['forum_id'] =  (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) ? $_GET['forum_id'] : 0;
$_GET['forum_cat'] =  (isset($_GET['forum_cat']) && isnum($_GET['forum_cat'])) ? $_GET['forum_cat'] : 0;
$_GET['forum_branch'] =  (isset($_GET['forum_branch']) && isnum($_GET['forum_branch'])) ? $_GET['forum_branch'] : 0;
$_GET['parent_id'] =  (isset($_GET['parent_id']) && isnum($_GET['parent_id'])) ? $_GET['parent_id'] : 0;
$ext = isset($_GET['parent_id']) && isnum($_GET['parent_id']) ? "&amp;parent_id=".$_GET['parent_id'] : '';
/* Page navigation */
$info['max_rows'] = dbcount("('forum_id')", DB_FORUMS, (multilang_table("FO") ? "forum_language='".LANGUAGE."' AND" : '')." forum_cat='".$_GET['parent_id']."'"); // need max rows
$_GET['rowstart'] = (isset($_GET['rowstart']) && $_GET['rowstart'] <= $info['max_rows']) ? $_GET['rowstart'] : '0';

$info['posts_per_page'] = $settings['posts_per_page'];
$info['threads_per_page'] = $settings['threads_per_page'];

$forum_list = "";
$current_cat = "";
$forumCollapsed = FALSE;
$forumCollapse = TRUE;
/*
 * 	add_to_title($locale['global_201'].$data['forum_name']);
	set_meta("description", $data['forum_name']);
 */
if (isset($_GET['forum_id']) && isnum($_GET['forum_id']) && isset($_GET['parent_id']) && isnum($_GET['parent_id']) && isset($_GET['viewforum'])) {
	// view forum.
	//add_to_title($locale['global_201'].$fdata['forum_name']);
	/* Filter Core */
	//$col_time = 't.thread_lastpost';
	// init
	$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
	$time = isset($_GET['time']) ? $_GET['time'] : '';
	$type = isset($_GET['type']) ? $_GET['type'] : '';
	$order = isset($_GET['order']) ? $_GET['order'] : '';
	$timeCol = '';
	$typeCol = '';
	if ($time) {
		$time_array = array(
			'today' => strtotime('today'),
			'2days' => strtotime('-2 day'),
			'1week' => strtotime('-1 week'),
			'2week' => strtotime('-2 week'),
			'1month' => strtotime('-2 month'),
			'2month' => strtotime('-2 month'),
			'3month' => strtotime('-2 month'),
			'6month' => strtotime('-6 month'),
			'1year' => strtotime('-1 year'),
		);

		$time_stop = '';
		foreach($time_array as $key =>$value) {
			if ($time == $key) {
				$time_stop = prev($time_array);
				$time_stop = prev($time_array);
				break;
			}
		}
		// Debug
		//print_p($time_array);
		//print_p($time_array[$time]);
		//print_p($time_stop);

		if ($time !=='today') {
			$timeCol = "AND ((post_datestamp >= '".$time_array[$time]."' OR t.thread_lastpost >= '".$time_array[$time]."') AND (post_datestamp <= '".$time_stop."' OR t.thread_lastpost <= '".$time_stop."')) ";
		} else {
			$timeCol = "AND (post_datestamp >= '".$time_array[$time]."' OR t.thread_lastpost >= '".$time_array[$time]."') ";
		}

	}
	if ($type) {
		$type_array = array(
			'all' => '',
			'discussions' => "AND (attach_name IS NULL or attach_name='') AND (forum_poll_title IS NULL or forum_poll_title='')",
			'attachments' => "AND attach_name !='' AND (forum_poll_title IS NULL or forum_poll_title='')",
			'poll' => "AND (attach_name IS NULL or attach_name='')  AND forum_poll_title !=''",
			'solved' => "AND t.thread_answered = '1'",
			'unsolved' => "AND t.thread_answered = '0'",
		);
		$typeCol = $type_array[$type];
	}
	// sort
	$sortCol = "ORDER BY t.thread_lastpost ";
	$orderCol = 'ASC';
	if ($sort) {
		$sort_array = array(
			'author'=>'t.thread_author',
			'time'=> 't.thread_lastpost',
			'subject'=>'t.thread_subject',
			'reply' => 't.thread_postcount',
			'view' => 't.thread_views'
		);
		$sortCol = "ORDER BY ".$sort_array[$sort]." ";
	}
	if ($order) {
		$order_array = array(
			'ascending' => 'ASC',
			'descending' => 'DESC'
		);
		$orderCol = $order_array[$order];
	}

	$sql_condition = $timeCol.$typeCol;
	$sql_order = $sortCol.$orderCol;

	// Filter Links
	$timeExt = isset($_GET['time']) ? "&amp;time=".$_GET['time'] : '';
	$typeExt = isset($_GET['type']) ? "&amp;type=".$_GET['type'] : '';
	$sortExt = isset($_GET['sort']) ? "&amp;sort=".$_GET['sort'] : '';
	$orderExt = isset($_GET['order']) ? "&amp;order=".$_GET['order'] : '';
	$baseLink = FORUM.'index.php?viewforum&amp;forum_id='.$_GET['forum_id'].'&amp;parent_id='.$_GET['parent_id'].'&amp;';
	$timeLink = $baseLink.$typeExt.$sortExt.$orderExt;
	$info['filter']['time'] = array(
		'All Time' => FORUM.'index.php?viewforum&amp;forum_id='.$_GET['forum_id'].'&amp;parent_id='.$_GET['parent_id'],
		'Today' => $timeLink.'&amp;time=today', // must be static.
		'2 Days' => $timeLink.'&amp;time=2days',
		'1 Week'=> $timeLink.'&amp;time=1week',
		'2 Weeks' => $timeLink.'&amp;time=2week',
		'1 Month' => $timeLink.'&amp;time=1month',
		'2 Months' => $timeLink.'&amp;time=2month',
		'3 Months' => $timeLink.'&amp;time=3month',
		'6 Months' => $timeLink.'&amp;time=6month',
		'1 Year' => $timeLink.'&amp;time=1year'
	);
	$typeLink = $baseLink.$timeExt.$sortExt.$orderExt;
	$info['filter']['type'] = array(
		'All Topics' => $typeLink.'&amp;type=all',
		'Discussions' => $typeLink.'&amp;type=discussions',
		'Attachments' => $typeLink.'&amp;type=attachments',
		'Polls' => $typeLink.'&amp;type=poll',
		'Solved' => $typeLink.'&amp;type=solved',
		'Unsolved' => $typeLink.'&amp;type=unsolved',
	);
	$sortLink = $baseLink.$timeExt.$typeExt.$orderExt;
	$info['filter']['sort'] = array(
		'Author' => $sortLink.'&amp;sort=author',
		'Post time' => $sortLink.'&amp;sort=time',
		'Subject' => $sortLink.'&amp;sort=subject',
		'Replies' => $sortLink.'&amp;sort=reply',
		'Views' => $sortLink.'&amp;sort=view',
	);
	$orderLink = $baseLink.$timeExt.$typeExt.$sortExt;
	$info['filter']['order'] = array(
		'Descending' => $orderLink.'&amp;order=descending',
		'Ascending' => $orderLink.'&amp;order=ascending'
	);

	$result = dbquery("SELECT f.*, f2.forum_name AS forum_cat_name,
				t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject,
				u.user_id, u.user_name, u.user_status, u.user_avatar
				FROM ".DB_FORUMS." f
				LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
				LEFT JOIN ".DB_THREADS." t ON f.forum_lastpostid=t.thread_lastpostid
				LEFT JOIN ".DB_USERS." u ON f.forum_lastuser=u.user_id
				".(multilang_table("FO") ? "WHERE f.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('forum_access')."
				AND f.forum_id='".$_GET['forum_id']."' OR f.forum_cat='".$_GET['forum_id']."' OR f.forum_branch='".$_GET['forum_branch']."'
				ORDER BY forum_cat ASC
				");
	$refs = array();
	if (dbrows($result)>0) {
		while ($data = dbarray($result)) {
			$thisref = &$refs[$data['forum_id']];
			$thisref = $data;
			// do a last post here. keep compare and replace until the end of loop.
			if ($data['forum_cat'] == $_GET['parent_id']) {
				$info['item'][$data['forum_id']] = &$thisref;
			} else {
				$refs[$data['forum_cat']]['child'][$data['forum_id']] = &$thisref;
			}

			// post permission of the current forum view.
			if ($data['forum_id'] == $_GET['forum_id'] && $data['forum_type'] !=='1') {
				add_to_title($locale['global_201'].$data['forum_name']);
				$info['post_access'] = ($data['forum_post']) ? checkgroup($data['forum_post']) : 0;
				// define mods
				define_forum_mods($data);
				// get thread and apply filter
				$info['thread_item_rows'] = dbcount("('t.thread_id')",
												DB_THREADS." t
												LEFT JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
												LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id
												LEFT JOIN ".DB_POSTS." p1 ON p1.thread_id = t.thread_id
												LEFT JOIN ".DB_FORUM_ATTACHMENTS." a ON a.thread_id = t.thread_id
												LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id",
												"t.forum_id='".$_GET['forum_id']."' AND thread_hidden='0' $sql_condition
												");

				$_GET['rowstart_thread'] = isset($_GET['rowstart_thread']) && isnum($_GET['rowstart_thread']) && $_GET['rowstart_thread'] <= $info['thread_item_rows'] ? $_GET['rowstart_thread'] : 0;

				$t_result = dbquery("SELECT t.*, tu1.user_name AS author_name, tu1.user_status AS author_status, tu1.user_avatar as author_avatar,
                tu2.user_name AS last_user_name, tu2.user_status AS last_user_status, tu2.user_avatar AS last_user_avatar,
                p1.post_datestamp,
                a.attach_name, p.forum_poll_title,
                count(v.post_id) AS vote_count
                FROM ".DB_THREADS." t
                LEFT JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
                LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id
                LEFT JOIN ".DB_POSTS." p1 ON p1.thread_id = t.thread_id
                LEFT JOIN ".DB_FORUM_ATTACHMENTS." a ON a.thread_id = t.thread_id
                LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id
                LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = t.thread_id AND p1.post_id = v.post_id
                WHERE t.forum_id='".$_GET['forum_id']."' AND thread_hidden='0' $sql_condition
                GROUP BY t.thread_id $sql_order LIMIT ".$_GET['rowstart'].", ".$info['threads_per_page']."
                ");

				if (dbrows($t_result)>0) {
					while ($tdata = dbarray($t_result)) {
						//print_p($tdata);
						$tdata['forum_type'] = $data['forum_type'];
						if ($tdata['thread_sticky']) {
							$info['threads']['sticky'][$tdata['thread_id']] = $tdata;
						} else {
							$info['threads']['item'][$tdata['thread_id']] = $tdata;
						}
					}
				}
			}
		}
	} else {
		//echo "lang fail";
		redirect("index.php");
	}


}
elseif (isset($_GET['section']) && $_GET['section'] == 'mypost') {
	include FORUM."sections/my_posts.php";
}
elseif (isset($_GET['section']) && $_GET['section'] == 'latest') {
	include FORUM."sections/laft.php";
}
elseif (isset($_GET['section']) && $_GET['section'] == 'tracked') {
	include FORUM."sections/tracked.php";
}
else {
	// index
	// this might fetch category which is not output --- still can be optimized by 1 level?
	$result = dbquery("SELECT tf.forum_id, tf.forum_cat, tf.forum_branch, tf.forum_name, tf.forum_description, tf.forum_image,
			tf.forum_type, tf.forum_mods, tf.forum_threadcount, tf.forum_postcount, tf.forum_order, tf.forum_lastuser, tf.forum_access, tf.forum_lastpost,
			t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject,
        	u.user_id, u.user_name, u.user_status, u.user_avatar
			FROM ".DB_FORUMS." tf
			LEFT JOIN ".DB_THREADS." t ON tf.forum_id = t.forum_id AND tf.forum_lastpost = t.thread_lastpost
        	LEFT JOIN ".DB_USERS." u ON tf.forum_lastuser = u.user_id
	 		".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('tf.forum_access')." AND tf.forum_cat='".$_GET['parent_id']."'
	 		GROUP BY tf.forum_id ORDER BY tf.forum_order ASC, t.thread_lastpost DESC
	 		");
	if (dbrows($result)>0) {
		while ($data = dbarray($result)) {

			/* Show moderators */
			$moderators = '';
			if ($data['forum_mods']) {
				$_mgroup = explode('.', $data['forum_mods']);
				if (!empty($_mgroup)) {
					foreach ($_mgroup as $mod_group) {
						if ($moderators) $moderators .= ", ";
						$moderators .= $mod_group < 101 ? "<a href='".BASEDIR."profile.php?group_id=".$mod_group."'>".getgroupname($mod_group)."</a>" : getgroupname($mod_group);
					}
				}
			}
			$data['moderators'] = $moderators;
			// push
			$info['item'][$data['forum_id']] = $data;

			$check_child = dbcount("('forum_id')" , DB_FORUMS, "".(multilang_table("FO") ? "forum_language='".LANGUAGE."' AND" : '')." ".groupaccess('forum_access')." AND forum_cat='".$data['forum_id']."'");

			if ($check_child !=0) {
				if ($data['forum_type'] == '1') {
					// Max info to show sub-forum data when the parent is a category.
					$c_result = dbquery("SELECT tf.forum_id, tf.forum_cat, tf.forum_branch, tf.forum_name, tf.forum_description, tf.forum_image,
								tf.forum_type, tf.forum_mods, tf.forum_threadcount, tf.forum_postcount, tf.forum_order, tf.forum_lastuser, tf.forum_access, tf.forum_lastpost, tf.forum_lastpostid,
								t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject,
								u.user_id, u.user_name, u.user_status, u.user_avatar
								FROM ".DB_FORUMS." tf
								LEFT JOIN ".DB_THREADS." t ON tf.forum_lastpostid = t.thread_lastpostid
								LEFT JOIN ".DB_USERS." u ON tf.forum_lastuser = u.user_id
								".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('tf.forum_access')." AND tf.forum_cat='".$data['forum_id']."'
								GROUP BY tf.forum_id ORDER BY tf.forum_order ASC, t.thread_lastpost DESC
							");
				} else {
					$c_result = dbquery("SELECT tf.forum_id, tf.forum_cat, tf.forum_branch, tf.forum_name, tf.forum_description, tf.forum_image,
								tf.forum_type, tf.forum_mods, tf.forum_threadcount, tf.forum_postcount, tf.forum_order, tf.forum_lastuser, tf.forum_access, tf.forum_lastpost
								FROM ".DB_FORUMS." tf
								".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('tf.forum_access')." AND tf.forum_cat='".$data['forum_id']."'
								GROUP BY tf.forum_id ORDER BY tf.forum_order ASC
								");
				}
				if (dbrows($c_result)>0) {
					while ($cdata = dbarray($c_result)) {
						$info['item'][$data['forum_id']]['child'][$cdata['forum_id']] = $cdata;
						// another level down if it is a category
						if ($data['forum_type'] == '1') {
							$check_subforums = dbcount("('forum_id')", DB_FORUMS, "".(multilang_table("FO") ? "forum_language='".LANGUAGE."' AND" : '')." ".groupaccess('forum_access')." AND forum_cat='".$cdata['forum_id']."'");
							if ($check_subforums) {
								$d_result = dbquery("SELECT forum_id, forum_cat, forum_branch, forum_name FROM ".DB_FORUMS."
											".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('forum_access')." AND forum_cat='".$cdata['forum_id']."'
											");
								if (dbrows($d_result)>0) {
									while ($sub = dbarray($d_result)) {
										$info['item'][$data['forum_id']]['child'][$cdata['forum_id']]['child'][] = $sub;
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
forum_breadcrumbs($forum_index);
render_forum($info);



/* 	Autopush Breadcrumb (better to Core)
| 	Note that this function is not the same as the admin one
|	The back end do not require current directory detail.
|	Hence, the parent_id was actual forum_id in admin,
|	but here the parent_id is forum_cat
*/

function forum_breadcrumbs() {
	global $aidlink, $forum_index;
	/* Make an infinity traverse */
	function breadcrumb_arrays($index, $id) {
		global $aidlink;
		$crumb = &$crumb;
		//$crumb += $crumb;
		if (isset($index[get_parent($index, $id)])) {
			$_name = dbarray(dbquery("SELECT forum_id, forum_name, forum_cat, forum_branch FROM ".DB_FORUMS." WHERE forum_id='".$id."'"));
			$crumb = array('link'=>FORUM."index.php?viewforum&amp;forum_id=".$_name['forum_id']."&amp;parent_id=".$_name['forum_cat'], 'title'=>$_name['forum_name']);
			if (isset($index[get_parent($index, $id)])) {
				if (get_parent($index, $id) == 0) {
					return $crumb;
				}
				$crumb_1 = breadcrumb_arrays($index, get_parent($index, $id));
				$crumb = array_merge_recursive($crumb, $crumb_1); // convert so can comply to Fusion Tab API.
			}
		}
		return $crumb;
	}
	// then we make a infinity recursive function to loop/break it out.
	$crumb = breadcrumb_arrays($forum_index, $_GET['forum_id']);
	// then we sort in reverse.
	if (count($crumb['title']) > 1)  { krsort($crumb['title']); krsort($crumb['link']); }
	// then we loop it out using Dan's breadcrumb.
	add_to_breadcrumbs(array('link'=>FORUM.'index.php', 'title'=>'Forum Board Index'));
	if (count($crumb['title']) > 1) {
		foreach($crumb['title'] as $i => $value) {
			add_to_breadcrumbs(array('link'=>$crumb['link'][$i], 'title'=>$value));
		}
	} elseif (isset($crumb['title'])) {
		add_to_breadcrumbs(array('link'=>$crumb['link'], 'title'=>$crumb['title']));
	}
	// hola!
}

require_once THEMES."templates/footer.php";

?>