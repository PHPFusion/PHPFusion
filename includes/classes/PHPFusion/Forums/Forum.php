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

	public function set_ForumInfo() {
		global $settings, $userdata, $locale;

		if (stristr($_SERVER['PHP_SELF'], 'forum_id')) {
			if ($_GET['section'] == 'latest') redirect(FORUM.'index.php?section=latest');
			if ($_GET['section'] == 'mypost') redirect(FORUM.'index.php?section=mypost');
			if ($_GET['section'] == 'tracked') redirect(FORUM.'index.php?section=tracked');
		}

		$this->forum_info = array(
			'forum_id' => (isset($_GET['forum_id']) && verify_forum($_GET['forum_id'])) ? $_GET['forum_id'] : 0,
			'parent_id' => (isset($_GET['parent_id']) && verify_forum($_GET['parent_id'])) ? $_GET['parent_id'] : 0,
			'forum_branch' => (isset($_GET['forum_branch']) && verify_forum($_GET['forum_branch'])) ? $_GET['forum_branch'] : 0,
			'new_thread_link' => '',
			'lastvisited' => (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time(),
			'posts_per_page' => $settings['posts_per_page'],
			'threads_per_page' => $settings['threads_per_page'],
			'forum_index' => dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'),
			'permissions' => array(
			'can_post' => 0
			),
			'threads' => array(),
			'section' => isset($_GET['section']) ? $_GET['section'] : 'thread',
		);
		
		$this->forum_info['max_rows'] = dbcount("('forum_id')", DB_FORUMS, (multilang_table("FO") ? "forum_language='".LANGUAGE."' AND" : '')." forum_cat='".$this->forum_info['parent_id']."'");
		add_to_title($locale['global_200'].$locale['forum_0000']);
		add_breadcrumb(array('link'=>FORUM.'index.php', 'title'=>$locale['forum_0010']));
		$_GET['forum_id'] = $this->forum_info['forum_id'];
		forum_breadcrumbs($this->forum_info['forum_index']);
		$_GET['rowstart'] = (isset($_GET['rowstart']) && $_GET['rowstart'] <= $this->forum_info['max_rows']) ? $_GET['rowstart'] : '0';
		$this->ext = isset($this->forum_info['parent_id']) && isnum($this->forum_info['parent_id']) ? "&amp;parent_id=".$this->forum_info['parent_id'] : '';

		if (isset($_GET['section'])) {
			switch($_GET['section']) {
				case 'mypost':
					include FORUM."sections/my_posts.php";
					add_to_title($locale['global_201'].$locale['forum_0011']);
					add_breadcrumb(array('link'=>FORUM."index.php?section=mypost", 'title'=>$locale['forum_0011']));
					set_meta("description", $locale['forum_0011']);
					break;
				case 'latest': // Let´s just take this section out(?)
					include FORUM."sections/laft.php";
					add_to_title($locale['global_201'].$locale['global_021']);
					add_breadcrumb(array('link'=>FORUM."index.php?section=latest", 'title'=>$locale['global_021']));
					set_meta("description", $locale['global_021']);
					break;
				case 'tracked':
					include FORUM."sections/tracked.php";
					add_to_title($locale['global_201'].$locale['global_056']);
					add_breadcrumb(array('link'=>FORUM."index.php?section=tracked", 'title'=>$locale['global_056']));
					set_meta("description", $locale['global_056']);
					break;
			}
		}
		$this->view_forum();
	}

	// Main view forum structure.
	private function view_forum() {
		global $locale, $userdata, $settings;

		if ($this->forum_info['forum_id'] && isset($this->forum_info['parent_id']) && isset($_GET['viewforum'])) {
			/**
			 * View Forum
			 * @todo: This part needs to merge with get_forum() , extend params with `get_thread` to get threads of current forum.
			 */

			// Filter core
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
			$baseLink =	FORUM.'index.php?viewforum&amp;forum_id='.$_GET['forum_id'].''.(isset($_GET['parent_id']) ? '&amp;parent_id='.$_GET['parent_id'].'' : '');
			$timeLink = $baseLink.$typeExt.$sortExt.$orderExt;
			$this->forum_info['filter']['time'] = array(
				'All Time' => FORUM.'index.php?viewforum&amp;forum_id='.$_GET['forum_id'].''.(isset($_GET['parent_id']) ? '&amp;parent_id='.$_GET['parent_id'].'' : ''),
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

			$this->forum_info['filter']['type'] = array(
				'All Topics' => $typeLink.'&amp;type=all',
				'Discussions' => $typeLink.'&amp;type=discussions',
				'Attachments' => $typeLink.'&amp;type=attachments',
				'Polls' => $typeLink.'&amp;type=poll',
				'Solved' => $typeLink.'&amp;type=solved',
				'Unsolved' => $typeLink.'&amp;type=unsolved',
			);

			$sortLink = $baseLink.$timeExt.$typeExt.$orderExt;

			$this->forum_info['filter']['sort'] = array(
				'Author' => $sortLink.'&amp;sort=author',
				'Post time' => $sortLink.'&amp;sort=time',
				'Subject' => $sortLink.'&amp;sort=subject',
				'Replies' => $sortLink.'&amp;sort=reply',
				'Views' => $sortLink.'&amp;sort=view',
			);

			$orderLink = $baseLink.$timeExt.$typeExt.$sortExt;

			$this->forum_info['filter']['order'] = array(
				'Descending' => $orderLink.'&amp;order=descending',
				'Ascending' => $orderLink.'&amp;order=ascending'
			);

			 // Load forum
				$result = dbquery("SELECT f.*, f2.forum_name AS forum_cat_name,
				t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject,
				u.user_id, u.user_name, u.user_status, u.user_avatar
				FROM ".DB_FORUMS." f
				LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat = f2.forum_id
				LEFT JOIN ".DB_FORUM_THREADS." t ON f.forum_lastpostid = t.thread_lastpostid
				LEFT JOIN ".DB_USERS." u ON f.forum_lastuser = u.user_id
				".(multilang_table("FO") ? "WHERE f.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('f.forum_access')."
				AND f.forum_id='".intval($this->forum_info['forum_id'])."' OR f.forum_cat='".intval($this->forum_info['forum_id'])."' OR f.forum_branch='".intval($this->forum_info['forum_branch'])."'
				ORDER BY forum_cat ASC");
				
			$refs = array();
			if (dbrows($result)>0) {
				while ($row = dbarray($result)) {

					$this->forum_info['forum_moderators'] = parse_forumMods($row['forum_mods']);
					$row['forum_moderators'] = Functions::parse_forumMods($row['forum_mods']);

					$row['forum_new_status'] = '';
					$forum_match = "\|".$row['forum_lastpost']."\|".$row['forum_id'];
					$last_visited = (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time();
					if ($row['forum_lastpost'] > $last_visited) {
						if (iMEMBER && ($row['forum_lastuser'] !== $userdata['user_id'] || !preg_match("({$forum_match}\.|{$forum_match}$)", $userdata['user_threads']))) {
							$row['forum_new_status'] = "<span class='forum-new-icon'><i title='".$locale['forum_0260']."' class='".Functions::get_forumIcons('new')."'></i></span>";
						}
					}
					$row['forum_link'] = FORUM."index.php?viewforum&amp;forum_id=".$row['forum_id']."&amp;parent_id=".$row['forum_cat']."&amp;forum_branch=".$row['forum_branch'];
					$row['forum_description'] = nl2br(parseubb($row['forum_description']));
					$row['forum_postcount'] = format_word($row['forum_postcount'], $locale['fmt_post']);
					$row['forum_threadcount'] = format_word($row['forum_threadcount'], $locale['fmt_thread']);
					$row['forum_threadcounter'] = $row['forum_threadcount']+1;
					
					 // Last posts section
					if ($row['forum_lastpostid']) {
						if ($settings['forum_last_post_avatar']) {
							$row['forum_last_post_avatar'] = display_avatar($row, '30px', '', '', 'img-rounded');
						}
						$row['forum_last_post_thread_link'] = FORUM."viewthread.php?thread_id=".$row['thread_id'];
						$row['forum_last_post_link'] = FORUM."viewthread.php?thread_id=".$row['thread_id']."&amp;pid=".$row['thread_lastpostid']."#post_".$row['thread_lastpostid'];
						$row['forum_last_post_profile_link'] = $locale['by']." ".profile_link($row['forum_lastuser'], $row['user_name'], $row['user_status']);
						$row['forum_last_post_date'] = showdate("forumdate", $row['forum_lastpost']);
						}
					
					 // Icons
					switch($row['forum_type']) {
						case '1':
							$row['forum_icon'] = "<i class='".Functions::get_forumIcons('forum')." fa-fw m-r-10'></i>";
							$row['forum_icon_lg'] = "<i class='".Functions::get_forumIcons('forum')." fa-3x fa-fw m-r-10'></i>";
							break;
						case '2':
							$row['forum_icon'] = "<i class='".Functions::get_forumIcons('thread')." fa-fw m-r-10'></i>";
							$row['forum_icon_lg'] = "<i class='".Functions::get_forumIcons('thread')." fa-3x fa-fw m-r-10'></i>";
							break;
						case '3':
							$row['forum_icon'] = "<i class='".Functions::get_forumIcons('link')." fa-fw m-r-10'></i>";
							$row['forum_icon_lg'] = "<i class='".Functions::get_forumIcons('link')." fa-3x fa-fw m-r-10'></i>";
							break;
						case '4':
							$row['forum_icon'] = "<i class='".Functions::get_forumIcons('question')." fa-fw m-r-10'></i>";
							$row['forum_icon_lg'] = "<i class='".Functions::get_forumIcons('question')." fa-3x fa-fw m-r-10'></i>";
							break;
					}

					$thisref = &$refs[$row['forum_id']];
					$thisref = $row;

					if ($row['forum_cat'] == $this->forum_info['parent_id']) {
						$this->forum_info['item'][$row['forum_id']] = &$thisref;
					} else {
						$refs[$row['forum_cat']]['child'][$row['forum_id']] = &$thisref;
					}

					// get current forum threads
					if ($row['forum_id'] == $this->forum_info['forum_id'] && $row['forum_type'] !=='1') {
						define_forum_mods($row);
						if (iMOD || iSUPERADMIN || ($row['forum_post'] && checkgroup($row['forum_post']) && !$row['forum_lock'])) {
							$this->forum_info['permissions']['can_post'] = 1;
							$this->forum_info['new_thread_link'] = FORUM."newthread.php?forum_id=".$row['forum_id'];
						}
						
						 // Second query to get all threads of this forum. SQL filter conditions override applicable.
						$this->forum_info['thread_max_rows'] = dbcount("('t.thread_id')",
														   	DB_FORUM_THREADS." t
															LEFT JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
															LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id
															LEFT JOIN ".DB_FORUM_POSTS." p1 ON p1.thread_id = t.thread_id
															LEFT JOIN ".DB_FORUM_ATTACHMENTS." a ON a.thread_id = t.thread_id
															LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id",
														   "t.forum_id='".$this->forum_info['forum_id']."' AND thread_hidden='0' $sql_condition
															");
						if ($this->forum_info['thread_max_rows'] > 0) {
							// anti-XSS filtered rowstart
							$_GET['rowstart_thread'] = isset($_GET['rowstart_thread']) && isnum($_GET['rowstart_thread']) && $_GET['rowstart_thread'] <= $this->forum_info['thread_item_rows'] ? $_GET['rowstart_thread'] : 0;
							$t_result = dbquery("SELECT t.*, tu1.user_name AS author_name, tu1.user_status AS author_status, tu1.user_avatar as author_avatar,
								tu2.user_name AS last_user_name, tu2.user_status AS last_user_status, tu2.user_avatar AS last_user_avatar,
								p1.post_datestamp,
								p.forum_poll_title,
								count(v.post_id) AS vote_count
								FROM ".DB_FORUM_THREADS." t
								LEFT JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
								LEFT JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
								LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id
								LEFT JOIN ".DB_FORUM_POSTS." p1 ON p1.thread_id = t.thread_id
								LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id
								LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = t.thread_id AND p1.post_id = v.post_id
								WHERE t.forum_id='".$this->forum_info['forum_id']."' AND t.thread_hidden='0' AND ".groupaccess('tf.forum_access')." $sql_condition
								GROUP BY t.thread_id $sql_order LIMIT ".$_GET['rowstart'].", ".$this->forum_info['threads_per_page']."");

							if (dbrows($t_result)>0) {
								while ($threads = dbarray($t_result)) {
									$match_regex = $threads['thread_id']."\|".$threads['thread_lastpost']."\|".$threads['forum_id'];

									 // Threads Customized Output
									$threads['thread_link'] = FORUM."viewthread.php?thread_id=".$threads['thread_id'];
									$threads['thread_pages'] = '';

									$reps = ($this->forum_info['thread_max_rows'] > $this->forum_info['threads_per_page']) ? ceil($threads['thread_max_rows']/$this->forum_info['threads_per_page']) : 0;
									if ($reps > 1) {
										$ctr = 0;
										$ctr2 = 1;
										$pages = '';
										$middle = FALSE;
										while ($ctr2 <= $reps) {
											if ($reps < 5 || ($reps > 4 && ($ctr2 == 1 || $ctr2 > ($reps-3)))) {
												$pnum = "<a href='".FORUM."viewthread.php?thread_id=".$threads['thread_id']."&amp;rowstart=$ctr'>$ctr2</a> ";
											} else {
												if ($middle == FALSE) {
													$middle = TRUE;
													$pnum = "... ";
												} else {
													$pnum = "";
												}
											}
											$pages .= $pnum;
											$ctr = $ctr+$this->forum_info['threads_per_page'];
											$ctr2++;
										}
										$threads['thread_pages'] = "<span class='forum-pages'><small>(".$locale['forum_0055'].trim($pages).")</small></span>\n";
									}

									// Set up icons
									$attach_image = 0; $attach_file = 0;
									$a_result = dbquery("SELECT attach_id, attach_mime FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id ='".$threads['thread_id']."'");
									if (dbrows($a_result)>0) {
										require_once INCLUDES."mimetypes_include.php";
										while($adata = dbarray($a_result)) {
											if (in_array($adata['attach_mime'], img_mimeTypes())) {
												$attach_image = $attach_image+1;
											} else {
												$attach_file = $attach_file+1;
											}
										}
									}
									$threads['thread_status'] = array(
										'lock' => $threads['thread_locked'] ? "<i class='".get_forumIcons('lock')."' title='".$locale['forum_0263']."'></i>" : '',
										'sticky' => $threads['thread_sticky'] ? "<i class='".get_forumIcons('sticky')."' title='".$locale['forum_0103']."'></i>" : '',
										'poll' => $threads['thread_poll'] ? "<i class='".get_forumIcons('poll')."' title='".$locale['forum_0314']."'></i>" : '',
										'hot' => $threads['thread_postcount'] >= 20 ? "<i class='".get_forumIcons('hot')."' title='".$locale['forum_0311']."'></i>" : '',
										'reads' => $threads['thread_views'] >= 20 ? "<i class='".get_forumIcons('reads')."' title='".$locale['forum_0311']."'></i>" : '',
										'image' => $attach_image ? "<i class='".get_forumIcons('image')."' title='".$locale['forum_0313']."'></i>" : '',
										'file' => $attach_file ? "<i class='".get_forumIcons('file')."' title='".$locale['forum_0312']."'></i>" : '',
										'icon' => "<i class='".get_forumIcons('thread')." low-opacity' title='".$locale['forum_0261']."'></i>",
									);

									if ($threads['thread_lastpost'] > $this->forum_info['lastvisited']) {
										if (iMEMBER && ($threads['thread_lastuser'] == $userdata['user_id'] || preg_match("(^\.{$match_regex}$|\.{$match_regex}\.|\.{$match_regex}$)", $userdata['user_threads']))) {
											$threads['thread_status']['icon'] = "<i class='".get_forumIcons('thread')."' title='".$locale['forum_0261']."'></i>";
										} else {
											$threads['thread_status']['icon'] = "<i class='".get_forumIcons('new')."' title='".$locale['forum_0260']."'></i>";
										}
									}

									if ($threads['thread_status']['reads']) $threads['thread_status']['icon'] = $threads['thread_status']['reads'];
									if ($threads['thread_status']['hot']) $threads['thread_status']['icon'] = $threads['thread_status']['hot'];
									if ($threads['thread_status']['sticky']) $threads['thread_status']['icon'] = $threads['thread_status']['sticky'];
									if ($threads['thread_status']['lock']) $threads['thread_status']['icon'] = $threads['thread_status']['lock'];

									$threads['forum_type'] = $row['forum_type'];

									$author = array(
										'user_id' => $threads['thread_author'],
										'user_name' => $threads['author_name'],
										'user_status'=> $threads['author_status'],
										'user_avatar' => $threads['author_avatar']
									);
									$threads['thread_starter'] = $locale['forum_0006'].display_avatar($author, '20px', '', '', 'img-rounded')." ".profile_link($author['user_id'], $author['user_name'], $author['user_status'])."</span> ".$locale['on']." ".showdate('forumdate', $threads['post_datestamp']);

									$lastuser = array(
										'user_id' => $threads['thread_lastuser'],
										'user_name' => $threads['last_user_name'],
										'user_status' => $threads['last_user_status'],
										'user_avatar' => $threads['last_user_avatar']
									);

									$threads['thread_lastuser'] = "
									<div class='pull-left m-r-10'>".display_avatar($lastuser, '30px', '', '', 'img-rounded')."</div>
									<div class='overflow-hide'>".$locale['forum_0373']." <span class='forum_profile_link'>".profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status'])."</span><br/>
									".timer($threads['post_datestamp'])."
									</div>";

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
				redirect(FORUM.'index.php');
			}
		} else {
			$this->forum_info['forums'] = Functions::get_forum();
		}
	}
}
