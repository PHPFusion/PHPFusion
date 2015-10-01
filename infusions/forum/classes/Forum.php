<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Forum.php
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

class Forum {
	private $forum_info = array();
	private $ext = '';

	/**
	 * @return array
	 */
	public function getForumInfo() {
		return $this->forum_info;
	}

	/**
	 * Set user permission based on current forum configuration
	 * @param $forum_data
	 */
	public function setForumPermission($forum_data) {
		// Access the forum
		$this->forum_info['permissions']['can_access'] = (iMOD || checkgroup($forum_data['forum_access'])) ? TRUE : FALSE;
		// Create new thread -- whether user has permission to create a thread
		$this->forum_info['permissions']['can_post'] = (iMOD || (checkgroup($forum_data['forum_post']) && $forum_data['forum_lock'] == FALSE)) ? TRUE : FALSE;
		// Poll creation -- thread has not exist, therefore cannot be locked.
		$this->forum_info['permissions']['can_create_poll'] = $forum_data['forum_allow_poll'] == TRUE && (iMOD || (checkgroup($forum_data['forum_poll']) && $forum_data['forum_lock'] == FALSE)) ? TRUE : FALSE;
		$this->forum_info['permissions']['can_upload_attach'] = $forum_data['forum_allow_attach'] == TRUE && (iMOD || checkgroup($forum_data['forum_attach'])) ? TRUE : FALSE;
		$this->forum_info['permissions']['can_download_attach'] = iMOD || ($forum_data['forum_allow_attach'] == TRUE && checkgroup($forum_data['forum_attach_download'])) ? TRUE : FALSE;
	}

	/**
	 * Get the relevant permissions of the current forum permission configuration
	 * @param null $key
	 * @return null
	 */
	public function getForumPermission($key = NULL) {
		if (!empty($this->forum_info['permissions'])) {
			if (isset($this->forum_info['permissions'][$key])) {
				return $this->forum_info['permissions'][$key];
			}
			return $this->forum_info['permissions'];
		}
		return NULL;
	}

	public function set_ForumInfo() {
		global $forum_settings, $userdata, $locale;

		if (stristr($_SERVER['PHP_SELF'], 'forum_id')) {
			if ($_GET['section'] == 'latest') redirect(INFUSIONS.'forum/index.php?section=latest');
			if ($_GET['section'] == 'mypost') redirect(INFUSIONS.'forum/index.php?section=mypost');
			if ($_GET['section'] == 'tracked') redirect(INFUSIONS.'forum/index.php?section=tracked');
		}

		// security boot due to insufficient access level
		if (isset($_GET['viewforum']) && !verify_forum($_GET['forum_id'])) {
			redirect(INFUSIONS.'forum/index.php');
		}
		// Xss sanitization
		$this->forum_info = array(
			'forum_id' => isset($_GET['forum_id']) ? $_GET['forum_id'] : 0,
			'parent_id' => isset($_GET['parent_id']) && verify_forum($_GET['parent_id']) ? $_GET['parent_id'] : 0,
			'forum_branch' => isset($_GET['forum_branch']) && verify_forum($_GET['forum_branch']) ? $_GET['forum_branch'] : 0,
			'new_thread_link' => '',
			'lastvisited' => isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit']) ? $userdata['user_lastvisit'] : time(),
			'posts_per_page' => $forum_settings['posts_per_page'],
			'threads_per_page' => $forum_settings['threads_per_page'],
			'forum_index' => dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), // waste resources here.
			'threads' => array(),
			'section' => isset($_GET['section']) ? $_GET['section'] : 'thread',
		);

		// Set Max Rows -- XSS
		$this->forum_info['forum_max_rows'] = dbcount("('forum_id')", DB_FORUMS, (multilang_table("FO") ? "forum_language='".LANGUAGE."' AND" : '')."
		forum_cat='".$this->forum_info['parent_id']."' AND ".groupaccess('forum_access')."");

		// Sanitize Globals
		$_GET['forum_id'] = $this->forum_info['forum_id'];
		$_GET['rowstart'] = (isset($_GET['rowstart']) && $_GET['rowstart'] <= $this->forum_info['max_rows']) ? $_GET['rowstart'] : '0';

		$this->ext = isset($this->forum_info['parent_id']) && isnum($this->forum_info['parent_id']) ? "&amp;parent_id=".$this->forum_info['parent_id'] : '';
		add_to_title($locale['global_200'].$locale['forum_0000']);
		add_breadcrumb(array('link' => INFUSIONS.'forum/index.php', 'title' => $locale['forum_0000']));
		forum_breadcrumbs($this->forum_info['forum_index']);
		// Set Meta data
		if ($this->forum_info['forum_id'] > 0) {
			$meta_result = dbquery("SELECT forum_meta, forum_description FROM ".DB_FORUMS." WHERE forum_id='".intval($this->forum_info['forum_id'])."'");
			if (dbrows($meta_result) > 0) {
				$meta_data = dbarray($meta_result);
				if ($meta_data['forum_description'] !== '') {
					set_meta('description', $meta_data['forum_description']);
				}
				if ($meta_data['forum_meta'] !== '') {
					set_meta('keywords', $meta_data['forum_meta']);
				}
			}
		}

		// Additional Sections in Index View
		if (isset($_GET['section'])) {
			switch ($_GET['section']) {
				case 'participated':
					include INFUSIONS."forum/sections/participated.php";
					add_to_title($locale['global_201'].$locale['global_024']);
					add_breadcrumb(array(
									   'link' => INFUSIONS."forum/index.php?section=participated",
									   'title' => $locale['global_024']
								   ));
					set_meta("description", $locale['global_024']);
					break;
				case 'latest':
					include INFUSIONS."forum/sections/latest.php";
					add_to_title($locale['global_201'].$locale['global_021']);
					add_breadcrumb(array(
									   'link' => INFUSIONS."forum/index.php?section=latest",
									   'title' => $locale['global_021']
								   ));
					set_meta("description", $locale['global_021']);
					break;
				case 'tracked':
					include INFUSIONS."forum/sections/tracked.php";
					add_to_title($locale['global_201'].$locale['global_056']);
					add_breadcrumb(array(
									   'link' => INFUSIONS."forum/index.php?section=tracked",
									   'title' => $locale['global_056']
								   ));
					set_meta("description", $locale['global_056']);
					break;
				case "unanswered":
					include INFUSIONS."forum/sections/unanswered.php";
					add_to_title($locale['global_201'].$locale['global_027']);
					add_breadcrumb(array(
									   'link' => INFUSIONS."forum/index.php?section=unanswered",
									   'title' => $locale['global_027']
								   ));
					set_meta("description", $locale['global_027']);
					break;
				case "unsolved":
					include INFUSIONS."forum/sections/unsolved.php";
					add_to_title($locale['global_201'].$locale['global_028']);
					add_breadcrumb(array(
									   'link' => INFUSIONS."forum/index.php?section=unsolved",
									   'title' => $locale['global_028']
								   ));
					set_meta("description", $locale['global_028']);
					break;
				default:
					redirect(FUSION_SELF);
			}
		} else {
			// Switch between view forum or forum index -- required: $_GET['viewforum']
			if ($this->forum_info['forum_id'] && isset($this->forum_info['parent_id']) && isset($_GET['viewforum'])) {

				/**
				 * View Forum Additional Views - add Filter Initialization
				 */

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
					foreach ($time_array as $key => $value) {
						if ($time == $key) {
							$time_stop = prev($time_array);
							break;
						}
					}
					if ($time !== 'today') {
						$timeCol = "AND ((post_datestamp >= '".$time_array[$time]."' OR t.thread_lastpost >= '".$time_array[$time]."') AND (post_datestamp <= '".$time_stop."' OR t.thread_lastpost <= '".$time_stop."')) ";
					} else {
						$timeCol = "AND (post_datestamp >= '".$time_array[$time]."' OR t.thread_lastpost >= '".$time_array[$time]."') ";
					}
				}
				if ($type) {
					$type_array = array(
						'all' => '',
						'discussions' => "AND (a1.attach_name IS NULL or a1.attach_name='') AND (a2.attach_name IS NULL or a2.attach_name='') AND (forum_poll_title IS NULL or forum_poll_title='')",
						'attachments' => "AND a1.attach_name !='' OR a2.attach_name !='' AND (forum_poll_title IS NULL or forum_poll_title='')",
						'poll' => "AND (a1.attach_name IS NULL or a1.attach_name='') AND (a2.attach_name IS NULL or a2.attach_name='') AND forum_poll_title !=''",
						'solved' => "AND t.thread_answered = '1'",
						'unsolved' => "AND t.thread_answered = '0'",
					);
					$typeCol = $type_array[$type];
				}
				$sortCol = "ORDER BY t.thread_lastpost ";
				$orderCol = 'ASC';
				if ($sort) {
					$sort_array = array(
						'author' => 't.thread_author',
						'time' => 't.thread_lastpost',
						'subject' => 't.thread_subject',
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
				$baseLink = INFUSIONS.'forum/index.php?viewforum&amp;forum_id='.$_GET['forum_id'].''.(isset($_GET['parent_id']) ? '&amp;parent_id='.$_GET['parent_id'].'' : '');
				$timeLink = $baseLink.$typeExt.$sortExt.$orderExt;

				$this->forum_info['filter']['time'] = array(
					$locale['forum_3006'] => INFUSIONS.'forum/index.php?viewforum&amp;forum_id='.$_GET['forum_id'].''.(isset($_GET['parent_id']) ? '&amp;parent_id='.$_GET['parent_id'].'' : ''),
					$locale['forum_3007'] => $timeLink.'&amp;time=today', // must be static.
					$locale['forum_3008'] => $timeLink.'&amp;time=2days',
					$locale['forum_3009'] => $timeLink.'&amp;time=1week',
					$locale['forum_3010'] => $timeLink.'&amp;time=2week',
					$locale['forum_3011'] => $timeLink.'&amp;time=1month',
					$locale['forum_3012'] => $timeLink.'&amp;time=2month',
					$locale['forum_3013'] => $timeLink.'&amp;time=3month',
					$locale['forum_3014'] => $timeLink.'&amp;time=6month',
					$locale['forum_3015'] => $timeLink.'&amp;time=1year'
				);
				$typeLink = $baseLink.$timeExt.$sortExt.$orderExt;
				$this->forum_info['filter']['type'] = array(
					$locale['forum_3000'] => $typeLink.'&amp;type=all',
					$locale['forum_3001'] => $typeLink.'&amp;type=discussions',
					$locale['forum_3002'] => $typeLink.'&amp;type=attachments',
					$locale['forum_3003'] => $typeLink.'&amp;type=poll',
					$locale['forum_3004'] => $typeLink.'&amp;type=solved',
					$locale['forum_3005'] => $typeLink.'&amp;type=unsolved',
				);
				$sortLink = $baseLink.$timeExt.$typeExt.$orderExt;
				$this->forum_info['filter']['sort'] = array(
					$locale['forum_3016'] => $sortLink.'&amp;sort=author',
					$locale['forum_3017'] => $sortLink.'&amp;sort=time',
					$locale['forum_3018'] => $sortLink.'&amp;sort=subject',
					$locale['forum_3019'] => $sortLink.'&amp;sort=reply',
					$locale['forum_3020'] => $sortLink.'&amp;sort=view',
				);
				$orderLink = $baseLink.$timeExt.$typeExt.$sortExt;
				$this->forum_info['filter']['order'] = array(
					$locale['forum_3021'] => $orderLink.'&amp;order=descending',
					$locale['forum_3022'] => $orderLink.'&amp;order=ascending'
				);

				// Forum SQL
				$result = dbquery("SELECT f.*, f2.forum_name AS forum_cat_name,
				t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject,
				count(t.thread_id) as forum_threadcount, p.post_message,
				u.user_id, u.user_name, u.user_status, u.user_avatar
				FROM ".DB_FORUMS." f
				LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat = f2.forum_id
				LEFT JOIN ".DB_FORUM_THREADS." t ON t.forum_id = f.forum_id
				LEFT JOIN ".DB_FORUM_POSTS." p on p.thread_id = t.thread_id and p.post_id = t.thread_lastpostid
				LEFT JOIN ".DB_USERS." u ON f.forum_lastuser=u.user_id  ## -- redo this part -- ##
				".(multilang_table("FO") ? "WHERE f.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('f.forum_access')."
				AND f.forum_id='".intval($this->forum_info['forum_id'])."' OR f.forum_cat='".intval($this->forum_info['forum_id'])."' OR f.forum_branch='".intval($this->forum_info['forum_branch'])."'
				group by f.forum_id ORDER BY forum_cat ASC
				");
				$refs = array();
				if (dbrows($result) > 0) {
					while ($row = dbarray($result) and checkgroup($row['forum_access'])) {

						// Calculate Forum New Status
						$newStatus = "";
						$forum_match = "\|".$row['forum_lastpost']."\|".$row['forum_id'];
						$last_visited = (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time();
						if ($row['forum_lastpost'] > $last_visited) {
							if (iMEMBER && ($row['forum_lastuser'] !== $userdata['user_id'] || !preg_match("({$forum_match}\.|{$forum_match}$)", $userdata['user_threads']))) {
								$newStatus = "<span class='forum-new-icon'><i title='".$locale['forum_0260']."' class='".Functions::get_forumIcons('new')."'></i></span>";
							}
						}

						// Calculate lastpost information
						$lastPostInfo = array();
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
							$lastPostInfo = $last_post;
						}
						/**
						 * Default system icons - why do i need this? Why not let themers decide?
						 */
						switch ($row['forum_type']) {
							case '1':
								$forum_icon = "<i class='".Functions::get_forumIcons('forum')." fa-fw m-r-10'></i>";
								$forum_icon_lg = "<i class='".Functions::get_forumIcons('forum')." fa-3x fa-fw m-r-10'></i>";
								break;
							case '2':
								$forum_icon = "<i class='".Functions::get_forumIcons('thread')." fa-fw m-r-10'></i>";
								$forum_icon_lg = "<i class='".Functions::get_forumIcons('thread')." fa-3x fa-fw m-r-10'></i>";
								break;
							case '3':
								$forum_icon = "<i class='".Functions::get_forumIcons('link')." fa-fw m-r-10'></i>";
								$forum_icon_lg = "<i class='".Functions::get_forumIcons('link')." fa-3x fa-fw m-r-10'></i>";
								break;
							case '4':
								$forum_icon = "<i class='".Functions::get_forumIcons('question')." fa-fw m-r-10'></i>";
								$forum_icon_lg = "<i class='".Functions::get_forumIcons('question')." fa-3x fa-fw m-r-10'></i>";
								break;
							default:
								$forum_icon = "";
								$forum_icon_lg = "";
						}
						$row += array(
							"forum_moderators" => Functions::parse_forumMods($row['forum_mods']),
							// display forum moderators per forum.
							"forum_new_status" => $newStatus,
							"forum_link" => array(
								"link" => INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$row['forum_id']."&amp;parent_id=".$row['forum_cat']."&amp;forum_branch=".$row['forum_branch'],
								// uri
								"title" => $row['forum_name']
							),
							"forum_description" => nl2br(parseubb($row['forum_description'])),
							// current forum description
							"forum_postcount_word" => format_word($row['forum_postcount'], $locale['fmt_post']),
							// current forum post count
							"forum_threadcount_word" => format_word($row['forum_threadcount'], $locale['fmt_thread']),
							// current forum thread count
							"last_post" => $lastPostInfo,
							// last post information
							"forum_icon" => $forum_icon,
							// normal icon
							"forum_icon_lg" => $forum_icon_lg,
							// big icon.
							"forum_image" => ($row['forum_image'] && file_exists(FORUM."images/".$row['forum_image'])) ? $row['forum_image'] : "",
						);
						$this->forum_info['forum_moderators'] = $row['forum_moderators'];
						// child hierarchy data.
						$thisref = & $refs[$row['forum_id']];
						$thisref = $row;
						if ($row['forum_cat'] == $this->forum_info['parent_id']) {
							$this->forum_info['item'][$row['forum_id']] = & $thisref; // will push main item out.
						} else {
							$refs[$row['forum_cat']]['child'][$row['forum_id']] = & $thisref;
						}

						/**
						 * The current forum
						 */
						if ($row['forum_id'] == $this->forum_info['forum_id']) {
							require_once INCLUDES."mimetypes_include.php";
							define_forum_mods($row);
							// do the full string of checks for forums access
							$this->setForumPermission($row);
							// Generate Links
							if ($this->getForumPermission("can_post")) {
								$this->forum_info['new_thread_link'] = INFUSIONS."forum/newthread.php?forum_id=".$row['forum_id'];
							}

							/**
							 * Get threads with filter conditions
							 */

							//xss
							$count = dbarray(dbquery("SELECT
								count(t.thread_id) 'thread_max_rows',
								count(a1.attach_id) 'attach_image',
								count(a2.attach_id) 'attach_files'
								FROM ".DB_FORUM_THREADS." t
								LEFT JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
								INNER JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
								LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id ## -- issue 323
								LEFT JOIN ".DB_FORUM_POSTS." p1 ON p1.thread_id = t.thread_id and p1.post_id = t.thread_lastpostid
								LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id
								LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = t.thread_id AND p1.post_id = v.post_id
								LEFT JOIN ".DB_FORUM_ATTACHMENTS." a1 on a1.thread_id = t.thread_id AND a1.attach_mime IN ('".implode(",", img_mimeTypes() )."')
								LEFT JOIN ".DB_FORUM_ATTACHMENTS." a2 on a2.thread_id = t.thread_id AND a2.attach_mime NOT IN ('".implode(",", img_mimeTypes() )."')
								WHERE t.forum_id='".$this->forum_info['forum_id']."' AND t.thread_hidden='0' AND ".groupaccess('tf.forum_access')." $sql_condition
								GROUP BY t.thread_id
						"));

							$this->forum_info['thread_max_rows'] = $count['thread_max_rows'];

							if ($this->forum_info['thread_max_rows'] > 0) {
								// anti-XSS filtered rowstart
								$_GET['rowstart_thread'] = isset($_GET['rowstart_thread']) && isnum($_GET['rowstart_thread']) && $_GET['rowstart_thread'] <= $this->forum_info['thread_item_rows'] ? $_GET['rowstart_thread'] : 0;

								$t_result = dbquery("SELECT t.*, tu1.user_name AS author_name, tu1.user_status AS author_status, tu1.user_avatar as author_avatar,
								tu2.user_name AS last_user_name, tu2.user_status AS last_user_status, tu2.user_avatar AS last_user_avatar,
								p1.post_datestamp, p1.post_message,
								p.forum_poll_title,
								count(v.post_id) AS vote_count,
								a1.attach_name, a1.attach_id,
								a2.attach_name, a2.attach_id,
								count(a1.attach_mime) 'attach_image',
								count(a2.attach_mime) 'attach_files'
								FROM ".DB_FORUM_THREADS." t
								LEFT JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
								INNER JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
								LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id #issue 323
								LEFT JOIN ".DB_FORUM_POSTS." p1 ON p1.thread_id = t.thread_id and p1.post_id = t.thread_lastpostid
								LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id
								LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = t.thread_id AND p1.post_id = v.post_id
								LEFT JOIN ".DB_FORUM_ATTACHMENTS." a1 on a1.thread_id = t.thread_id AND a1.attach_mime IN ('".implode(",", img_mimeTypes() )."')
								LEFT JOIN ".DB_FORUM_ATTACHMENTS." a2 on a2.thread_id = t.thread_id AND a2.attach_mime NOT IN ('".implode(",", img_mimeTypes() )."')
								WHERE t.forum_id='".$this->forum_info['forum_id']."' AND t.thread_hidden='0' AND ".groupaccess('tf.forum_access')." $sql_condition
								GROUP BY t.thread_id $sql_order LIMIT ".intval($_GET['rowstart']).", ".$this->forum_info['threads_per_page']
								);

								if (dbrows($t_result) > 0) {
									while ($threads = dbarray($t_result)) {

										$icon = "";
										$match_regex = $threads['thread_id']."\|".$threads['thread_lastpost']."\|".$threads['forum_id'];
										if ($threads['thread_lastpost'] > $this->forum_info['lastvisited']) {
											if (iMEMBER && ($threads['thread_lastuser'] == $userdata['user_id'] || preg_match("(^\.{$match_regex}$|\.{$match_regex}\.|\.{$match_regex}$)", $userdata['user_threads']))) {
												$icon = "<i class='".get_forumIcons('thread')."' title='".$locale['forum_0261']."'></i>";
											} else {
												$icon = "<i class='".get_forumIcons('new')."' title='".$locale['forum_0260']."'></i>";
											}
										}

										$author = array(
											'user_id' => $threads['thread_author'],
											'user_name' => $threads['author_name'],
											'user_status' => $threads['author_status'],
											'user_avatar' => $threads['author_avatar']
										);
										$lastuser = array(
											'user_id' => $threads['thread_lastuser'],
											'user_name' => $threads['last_user_name'],
											'user_status' => $threads['last_user_status'],
											'user_avatar' => $threads['last_user_avatar']
										);

										$threads += array(
											"thread_link" => array(
												"link"=> INFUSIONS."forum/viewthread.php?thread_id=".$threads['thread_id'],
												"title" => $threads['thread_subject']
											),
											"forum_type" => $row['forum_type'],
											"thread_pages" => makepagenav(0, $forum_settings['posts_per_page'], $threads['thread_postcount'], 3, FORUM."viewthread.php?thread_id=".$threads['thread_id']."&amp;"),
											"thread_icons" => array(
												'lock' => $threads['thread_locked'] ? "<i class='".get_forumIcons('lock')."' title='".$locale['forum_0263']."'></i>" : '',
												'sticky' => $threads['thread_sticky'] ? "<i class='".get_forumIcons('sticky')."' title='".$locale['forum_0103']."'></i>" : '',
												'poll' => $threads['thread_poll'] ? "<i class='".get_forumIcons('poll')."' title='".$locale['forum_0314']."'></i>" : '',
												'hot' => $threads['thread_postcount'] >= 20 ? "<i class='".get_forumIcons('hot')."' title='".$locale['forum_0311']."'></i>" : '',
												'reads' => $threads['thread_views'] >= 20 ? "<i class='".get_forumIcons('reads')."' title='".$locale['forum_0311']."'></i>" : '',
												'image' => $threads['attach_image'] >0 ? "<i class='".get_forumIcons('image')."' title='".$locale['forum_0313']."'></i>" : '',
												'file' => $threads['attach_files'] >0 ? "<i class='".get_forumIcons('file')."' title='".$locale['forum_0312']."'></i>" : '',
												'icon' => $icon,
											),
											"thread_starter" => $locale['forum_0006'].timer($threads['post_datestamp'])." ".$locale['by']." ".profile_link($author['user_id'], $author['user_name'], $author['user_status'])."</span>",
											"thread_author" => $author,
											"thread_last" => array(
												'avatar' => display_avatar($lastuser, '30px', '', '', ''),
												'profile_link' => profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status']),
												'time' => $threads['post_datestamp'],
												'post_message' => parseubb(parsesmileys($threads['post_message'])),
												"formatted" => "<div class='pull-left'>".display_avatar($lastuser, '30px', '', '', '')."</div>
																				<div class='overflow-hide'>".$locale['forum_0373']." <span class='forum_profile_link'>".profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status'])."</span><br/>
																				".timer($threads['post_datestamp'])."
																				</div>"
											),
										);

										//if ($threads['thread_status']['reads']) $threads['thread_status']['icon'] = $threads['thread_status']['reads'];
										//if ($threads['thread_status']['hot']) $threads['thread_status']['icon'] = $threads['thread_status']['hot'];
										//if ($threads['thread_status']['sticky']) $threads['thread_status']['icon'] = $threads['thread_status']['sticky'];
										//if ($threads['thread_status']['lock']) $threads['thread_status']['icon'] = $threads['thread_status']['lock'];

										// Threads Customized Output
										/*
										$attach_image = 0;
										$attach_file = 0;
										$a_result = dbquery("SELECT attach_id, attach_mime FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id ='".$threads['thread_id']."'");
										if (dbrows($a_result) > 0) {
											require_once INCLUDES."mimetypes_include.php";
											while ($adata = dbarray($a_result)) {
												if (in_array($adata['attach_mime'], img_mimeTypes())) {
													$attach_image = $attach_image+1;
												} else {
													$attach_file = $attach_file+1;
												}
											}
										}*/
										if ($threads['thread_sticky']) {
											$this->forum_info['threads']['sticky'][$threads['thread_id']] = $threads;
										} else {
											$this->forum_info['threads']['item'][$threads['thread_id']] = $threads;
										}
									}
								}
							}
						}
					}
				} else {
					redirect(INFUSIONS.'forum/index.php');
				}
			}
			else {
				$this->forum_info['forums'] = Functions::get_forum();
			}
		}
	}
}