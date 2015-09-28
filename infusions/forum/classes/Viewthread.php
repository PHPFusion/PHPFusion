<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Viewthread.php
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
class Viewthread {
	private $thread_info = array();

	public function get_thread_data() {
		return $this->thread_info;
	}

	static function get_thread_stats($thread_id) {
		list($array['post_count'], $array['last_post_id'], $array['first_post_id']) = dbarraynum(dbquery("SELECT COUNT(post_id), MAX(post_id), MIN(post_id) FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($thread_id)."' AND post_hidden='0' GROUP BY thread_id"));
		if (!$array['post_count']) redirect(INFUSIONS.'forum/index.php'); // exit no.2
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $array['last_post_id'] ? $_GET['rowstart'] : 0; // secure against XSS
		return $array;
	}

	/**
	 * Validate whether a specific user has visited the thread.
	 * Duration : 7 days
	 * @param $thread_id
	 */
	static function increment_thread_views($thread_id) {
		$days_to_keep_session = 7;
		if (!isset($_SESSION['thread'][$thread_id])) {
			$_SESSION['thread'][$thread_id] = time();
			dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_views=thread_views+1 WHERE thread_id='".intval($thread_id)."'");
		} else {
			$time = $_SESSION['thread'][$thread_id];
			if ($time <= time()-($days_to_keep_session*3600*24)) {
				unset($_SESSION['thread'][$thread_id]);
			}
		}
	}

	/* Get participated users - parsing */
	public function get_participated_users($info) {
		$user = array();
		$result = dbquery("SELECT u.user_id, u.user_name, u.user_status, count(p.post_id) as post_count FROM ".DB_FORUM_POSTS." p
				INNER JOIN ".DB_USERS." u on (u.user_id=p.post_author)
				WHERE p.forum_id='".intval($info['thread']['forum_id'])."' AND p.thread_id='".intval($info['thread']['thread_id'])."' group by user_id");
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				$user[$data['user_id']] = profile_link($data['user_id'], $data['user_name'], $data['user_status']);
			}
		}
		return $user;
	}

	/**
	 * New Status
	 */
	public function set_thread_visitor() {
		global $userdata;
		if (iMEMBER) {
			$thread_match = $this->thread_info['thread_id']."\|".$this->thread_info['thread']['thread_lastpost']."\|".$this->thread_info['thread']['forum_id'];
			if (($this->thread_info['thread']['thread_lastpost'] > $this->thread_info['lastvisited']) && !preg_match("(^\.{$thread_match}$|\.{$thread_match}\.|\.{$thread_match}$)", $userdata['user_threads'])) {
				dbquery("UPDATE ".DB_USERS." SET user_threads='".$userdata['user_threads'].".".stripslashes($thread_match)."' WHERE user_id='".$userdata['user_id']."'");
			}
		}
	}

	public function __construct() {
		global $locale, $userdata, $settings, $forum_settings;

		// exit no.1
		if (!isset($_GET['thread_id']) && !isnum($_GET['thread_id'])) redirect(INFUSIONS.'forum/index.php');
		$thread_data = \PHPFusion\Forums\Functions::get_thread($_GET['thread_id']); // fetch query and define iMOD
		if (empty($thread_data)) {
			redirect(FORUM.'index.php');
		}

		$thread_stat = self::get_thread_stats($_GET['thread_id']); // get post_count, lastpost_id, first_post_id.
		// Set meta
		add_to_meta($locale['forum_0000']);
		if ($thread_data['forum_description'] !== '') add_to_meta('description', $thread_data['forum_description']);
		if ($thread_data['forum_meta'] !== '') add_to_meta('keywords', $thread_data['forum_meta']);
		if ($thread_data['forum_type'] == 1) redirect(INFUSIONS.'forum/index.php');
		if ($thread_stat['post_count'] < 1) redirect(INFUSIONS.'forum/index.php');

		$_GET['forum_id'] = $thread_data['forum_id'];

		$this->thread_info += array(
			'thread' => $thread_data,
			'forum_id' => $thread_data['forum_id'],
			'forum_cat' => isset($_GET['forum_cat']) && verify_forum($_GET['forum_cat']) ? $_GET['forum_cat'] : 0,
			'forum_branch' => isset($_GET['forum_branch']) && verify_forum($_GET['forum_branch']) ? $_GET['forum_branch'] : 0,
			'forum_link' => array(
				"link" => INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$thread_data['forum_id']."&amp;forum_cat=".$thread_data['forum_cat']."&amp;forum_branch=".$thread_data['forum_branch'],
				"title" => $thread_data['forum_name']
			),
			'thread_id' => isset($_GET['thread_id']) && verify_thread($_GET['thread_id']) ? $_GET['thread_id'] : 0,
			'post_id' => isset($_GET['post_id']) && verify_post($_GET['post_id']) ? $_GET['post_id'] : 0,
			'pid' => isset($_GET['pid']) && isnum($_GET['pid']) ? $_GET['pid'] : 0,
			'section' => isset($_GET['section']) ? $_GET['section'] : '',
			// logic for viewthread access
			'permissions' => array(
				'can_post' => iMOD || iSUPERADMIN ? TRUE : iMEMBER && checkgroup($thread_data['forum_post']) && !$thread_data['forum_lock'] ? TRUE : FALSE,
				'can_edit' => iMOD || iSUPERADMIN ? TRUE : iMEMBER && checkgroup($thread_data['forum_post']) && $userdata['user_id'] == $thread_data['thread_author'] ? TRUE : FALSE,
				'can_vote_poll' => (iMOD || iSUPERADMIN) && $thread_data['forum_allow_poll'] && $thread_data['thread_poll'] ? TRUE : iMEMBER && checkgroup($thread_data['forum_post'] && checkgroup($thread_data['forum_reply']) && $thread_data['forum_allow_poll'] && $thread_data['thread_poll']) ? TRUE : FALSE,
				'can_poll' => ((iMOD || iSUPERADMIN) && $thread_data['forum_allow_poll']) ? TRUE : iMEMBER && checkgroup($thread_data['forum_post']) && checkgroup($thread_data['forum_reply']) && $thread_data['forum_allow_poll'] ? TRUE : FALSE,
				'can_reply' => iMOD || iSUPERADMIN ? TRUE : (checkgroup($thread_data['forum_reply']) && iMEMBER && checkgroup($thread_data['forum_reply']) && !$thread_data['forum_lock']) ? TRUE : FALSE,
				'can_rate' => ($thread_data['forum_type'] == 4 && ((iMOD || iSUPERADMIN) || ($thread_data['forum_allow_post_ratings'] && iMEMBER && checkgroup($thread_data['forum_post_ratings']) && !$thread_data['forum_lock']))) ? TRUE : FALSE,
				'can_view_poll' => checkgroup($thread_data['forum_poll']) ? TRUE : FALSE,
				'can_modify_poll' => iMOD || iSUPERADMIN ? TRUE : isset($userdata['user_id']) && $userdata['user_id'] == $thread_data['thread_author'] ? TRUE : FALSE,
				'edit_lock' => $forum_settings['forum_edit_lock'] ? TRUE : FALSE,
				'can_attach' => iMOD || iSUPERADMIN ? TRUE : iMEMBER && checkgroup($thread_data['forum_attach']) && $thread_data['forum_allow_attach'] ? TRUE : FALSE,
				'can_download_attach' => iMOD || iSUPERADMIN ? TRUE : $thread_data['forum_allow_attach'] && checkgroup($thread_data['forum_attach_download']) ? TRUE : FALSE,
			),
			'max_post_items' => $thread_stat['post_count'],
			'post_firstpost' => $thread_stat['first_post_id'],
			'post_lastpost' => $thread_stat['last_post_id'],
			'posts_per_page' => $forum_settings['posts_per_page'],
			'threads_per_page' => $forum_settings['threads_per_page'],
			'lastvisited' => (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time(),
			'allowed_post_filters' => array('oldest', 'latest', 'high'),
			'attachtypes' => explode(",", $forum_settings['forum_attachtypes']),
			'quick_reply_form' => '',
			'mod_options' => array(),
			'form_action' => '',
			'open_post_form' => '',
			'close_post_form' => '',
			'mod_form' => ''
		);

		// Thread buttons
		$this->thread_info['buttons'] = array(
			'print' => array('link' => BASEDIR."print.php?type=F&amp;thread=".$this->thread_info['thread_id']."&amp;rowstart=".$_GET['rowstart'],
			'name' => $locale['forum_0178']),
			'newthread' => $this->thread_info['permissions']['can_post'] ? array('link' => INFUSIONS."forum/newthread.php?forum_id=".$this->thread_info['thread']['forum_id'],
					'name' => $locale['forum_0264']) : array(),
			'reply' => $this->thread_info['permissions']['can_reply'] ? array('link' => INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$this->thread_info['thread']['forum_id']."&amp;thread_id=".$this->thread_info['thread']['thread_id'],
					'name' => $locale['forum_0360']) : array(),
			'poll' => $this->thread_info['permissions']['can_poll'] ? array('link' => INFUSIONS."forum/viewthread.php?action=newpoll&amp;forum_id=".$this->thread_info['thread']['forum_id']."&amp;thread_id=".$this->thread_info['thread']['thread_id'],
					'name' => $locale['forum_0366']) : array()
		);

		$this->thread_info['buttons']['notify'] = array();

		if (iMEMBER) {
			// only member can track the thread
			if ($thread_data['user_tracked']) {
				$this->thread_info['buttons']['notify'] = array('link' => INFUSIONS."forum/postify.php?post=off&amp;forum_id=".$this->thread_info['thread']['forum_id']."&amp;thread_id=".$this->thread_info['thread']['thread_id'],
					'name' => $locale['forum_0174']);
			} else {
				$this->thread_info['buttons']['notify'] = array('link' => INFUSIONS."forum/postify.php?post=on&amp;forum_id=".$this->thread_info['thread']['forum_id']."&amp;thread_id=".$this->thread_info['thread']['thread_id'],
					'name' => $locale['forum_0175']);
			}
		}

		// Quick reply form
		if ($this->thread_info['permissions']['can_post'] && $thread_data['forum_quick_edit']) {
			$form_action = ($settings['site_seo'] ? FUSION_ROOT : '').INFUSIONS."forum/viewthread.php?forum_id=".$this->thread_info['thread']['forum_id']."&amp;thread_id=".$this->thread_info['thread']['thread_id'];
			$html = "<!--sub_forum_thread-->\n";
			$html .= openform('quick_reply_form', 'post', $form_action, array('class' => 'm-b-20 m-t-20',
				'downtime' => 1));
			$html .= "<h4 class='m-t-20 pull-left'>".$locale['forum_0168']."</h4>\n";
			$html .= form_textarea('post_message', $locale['forum_0601'], '', array('bbcode' => 1,
													 'required' => 1,
													 'autosize' => 1,
													 'preview' => 1,
													 'form_name' => 'quick_reply_form'));
			$html .= "<div class='m-t-10 pull-right'>\n";
			$html .= form_button('post_quick_reply', $locale['forum_0172'], $locale['forum_0172'], array('class' => 'btn-primary btn-sm m-r-10'));
			$html .= "</div>\n";
			$html .= "<div class='overflow-hide'>\n";
			$html .= form_checkbox('post_smileys', $locale['forum_0169'], '', array('class' => 'm-b-0'));
			if (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) {
				$html .= form_checkbox('post_showsig', $locale['forum_0170'], '1', array('class' => 'm-b-0'));
			}
			if ($forum_settings['thread_notify']) {
				$html .= form_checkbox('notify_me', $locale['forum_0171'], $this->thread_info['thread']['user_tracked'], array('class' => 'm-b-0'));
			}
			$html .= "</div>\n";
			$html .= closeform();
			$this->thread_info['quick_reply_form'] = $html;
		}

		// Build Polls Info.
		if ($this->thread_info['thread']['thread_poll'] && $this->thread_info['permissions']['can_view_poll']) {
			if ($this->thread_info['permissions']['can_vote_poll']) {
				$poll_result = dbquery("SELECT tfp.forum_poll_title, tfp.forum_poll_votes, tfv.forum_vote_user_id
				FROM ".DB_FORUM_POLLS." tfp
				LEFT JOIN ".DB_FORUM_POLL_VOTERS." tfv
				ON tfp.thread_id=tfv.thread_id AND forum_vote_user_id='".$userdata['user_id']."'
				WHERE tfp.thread_id='".$_GET['thread_id']."'");
			} else {
				$poll_result = dbquery("SELECT tfp.forum_poll_title, tfp.forum_poll_votes FROM ".DB_FORUM_POLLS." tfp WHERE tfp.thread_id='".$_GET['thread_id']."'");
			}
			if (dbrows($poll_result) > 0) {
				$this->thread_info['poll'] = dbarray($poll_result);
				$p_options = dbquery("SELECT forum_poll_option_votes, forum_poll_option_text
					FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."'
					ORDER BY forum_poll_option_id ASC
				");
				$poll_option_rows = dbrows($p_options);
				$this->thread_info['poll']['max_option_id'] = $poll_option_rows;
				if ($poll_option_rows > 0) {
					while ($pdata = dbarray($p_options)) {
						$this->thread_info['poll']['poll_opts'][] = $pdata;
					}
				}
				$this->thread_info['permissions']['can_vote_poll'] = isset($this->thread_info['poll']['forum_vote_user_id']) ? FALSE : $this->thread_info['permissions']['can_vote_poll'];
				// Execute cast poll vote
				if ((isset($_POST['poll_option']) && isnum($_POST['poll_option']) && $_POST['poll_option'] <= $this->thread_info['poll']['max_option_id']) && $this->thread_info['permissions']['can_vote_poll'] && !defined('FUSION_NULL')) {
					dbquery("UPDATE ".DB_FORUM_POLL_OPTIONS." SET forum_poll_option_votes=forum_poll_option_votes+1 WHERE thread_id='".$thread_data['thread_id']."' AND forum_poll_option_id='".$_POST['poll_option']."'");
					dbquery("UPDATE ".DB_FORUM_POLLS." SET forum_poll_votes=forum_poll_votes+1 WHERE thread_id='".$thread_data['thread_id']."'");
					dbquery("INSERT INTO ".DB_FORUM_POLL_VOTERS." (thread_id, forum_vote_user_id, forum_vote_user_ip, forum_vote_user_ip_type) VALUES ('".$this->thread_info['thread_id']."', '".$userdata['user_id']."', '".USER_IP."', '".USER_IP_TYPE."')");
					addNotice('success', $locale['forum_0614']);
					redirect(INFUSIONS."forum/viewthread.php?forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']);
				}
				$html = '';
				if ($this->thread_info['permissions']['can_vote_poll']) {
					$html .= openform('voteform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').INFUSIONS."forum/viewthread.php?thread_id=".$this->thread_info['thread_id'], array('notice' => 0,
						'downtime' => 1));
				}
				// need to fix security.
				if ($this->thread_info['permissions']['can_modify_poll']) {
					$html .= "<div class='pull-right btn-group'>\n";
					$html .= "<a class='btn btn-sm btn-default' href='".INFUSIONS."forum/viewthread.php?action=editpoll&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."'>".$locale['forum_0603']."</a>\n";
					$html .= "<a class='btn btn-sm btn-default' href='".INFUSIONS."forum/viewthread.php?action=deletepoll&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."' onclick='confirm('".$locale['forum_0616']."');'>".$locale['delete']."</a>\n";
					$html .= "</div>\n";
				}
				$html .= "<h3 class='strong m-b-10'><i class='fa fa-fw fa-pie-chart fa-lg'></i>".$locale['forum_0377']." : ".$this->thread_info['poll']['forum_poll_title']."</h3>\n";
				$html .= "<ul class='p-l-20 p-t-0'>\n";
				$i = 1;
				if (!empty($this->thread_info['poll']['poll_opts'])) {
					foreach ($this->thread_info['poll']['poll_opts'] as $poll_option) {
						if ($this->thread_info['permissions']['can_vote_poll']) {
							$html .= "<li><label for='opt-".$i."'><input id='opt-".$i."' type='radio' name='poll_option' value='".$i."' class='m-r-20'> <span class='m-l-10'>".$poll_option['forum_poll_option_text']."</span>\n</label></li>\n";
						} else {
							$option_votes = ($this->thread_info['poll']['forum_poll_votes'] ? number_format(100/$this->thread_info['poll']['forum_poll_votes']*$poll_option['forum_poll_option_votes']) : 0);
							$html .= progress_bar($option_votes, $poll_option['forum_poll_option_text'], '', '10px');
						}
						$i++;
					}
				}
				$html .= "</ul>\n";
				if ($this->thread_info['permissions']['can_vote_poll']) {
					$html .= form_button('vote', $locale['forum_2010'], 'vote', array('class' => 'btn btn-sm btn-primary m-l-20 '));
					$html .= closeform();
				}
				$this->thread_info['poll_form'] = $html;
			}
		}
		// Build basic Attachment Info
		if ($this->thread_info['permissions']['can_download_attach']) {
			$a_result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".$this->thread_info['thread_id']."' ORDER BY post_id ASC");
			if (dbrows($a_result) > 0) {
				while ($a_data = dbarray($a_result)) {
					if (file_exists(INFUSIONS."forum/attachments/".$a_data['attach_name'])) {
						$this->thread_info['attachments'][$a_data['post_id']][] = $a_data;
					}
				}
			}
		}
		if (!empty($thread_data)) {
			/* Safe Zone */
			/* Do moderation */
			if (iMOD) {
				// need to wrap with issets?
				$mod = new Moderator();
				$mod->setForumId($thread_data['forum_id']);
				$mod->setThreadId($thread_data['thread_id']);
				$mod->set_modActions();
				/**
				 * Thread moderation form template
				 */
				$this->thread_info['mod_options'] = array('renew' => $locale['forum_0207'],
					'delete' => $locale['forum_0201'],
					$thread_data['thread_locked'] ? "unlock" : "lock" => $thread_data['thread_locked'] ? $locale['forum_0203'] : $locale['forum_0202'],
					$thread_data['thread_sticky'] ? "nonsticky" : "sticky" => $thread_data['thread_sticky'] ? $locale['forum_0205'] : $locale['forum_0204'],
					'move' => $locale['forum_0206']);
				$this->thread_info['form_action'] = $settings['site_seo'] ? FUSION_ROOT : ''.INFUSIONS."forum/viewthread.php?thread_id=".$thread_data['thread_id']."&amp;rowstart=".$_GET['rowstart'];
				$this->thread_info['open_post_form'] = openform('mod_form', 'post', $this->thread_info['form_action'], array('max_tokens' => 1,
					'notice' => 0));
				$this->thread_info['close_post_form'] = closeform();
				$this->thread_info['mod_form'] = "
				<div class='list-group-item'>\n
					<div class='btn-group m-r-10'>\n
						<a id='check' class='btn button btn-sm btn-default text-dark' href='#' onclick=\"javascript:setChecked('mod_form','delete_post[]',1);return false;\">".$locale['forum_0080']."</a>\n
						<a id='uncheck' class='btn button btn-sm btn-default text-dark' href='#' onclick=\"javascript:setChecked('mod_form','delete_post[]',0);return false;\">".$locale['forum_0081']."</a>\n
					</div>\n
					".form_button('move_posts', $locale['forum_0176'], $locale['forum_0176'], array('class' => 'btn-default btn-sm m-r-10'))."
					".form_button('delete_posts', $locale['forum_0177'], $locale['forum_0177'], array('class' => 'btn-default btn-sm'))."
					<div class='pull-right'>
						".form_button('go', $locale['forum_0208'], $locale['forum_0208'], array('class' => 'btn-default pull-right btn-sm m-t-0 m-l-10'))."
						".form_select('step', '', '', array('options' => $this->thread_info['mod_options'],
						'placeholder' => $locale['forum_0200'],
						'width' => '250px',
						'allowclear' => 1,
						'class' => 'm-b-0 m-t-5',
						'inline' => 1))."
					</div>\n
				</div>\n";
			}
			// listen and execute $_POST event
			$this->exec_post_actions();
			add_to_title($thread_data['thread_subject']);
			// generate thread breadcrumbs
			add_breadcrumb(array('link' => INFUSIONS.'forum/index.php', 'title' => $locale['forum_0000']));
			$this->forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
			forum_breadcrumbs($this->forum_index);
			add_breadcrumb(array('link' => INFUSIONS.'forum/viewthread.php?forum_id='.$thread_data['forum_id'].'&amp;thread_id='.$thread_data['thread_id'],
							   'title' => $thread_data['thread_subject']));
			// Make thread filter links
			$this->thread_info['post-filters'][0] = array('value' => INFUSIONS.'forum/viewthread.php?thread_id='.$thread_data['thread_id'].'&amp;section=oldest',
				'locale' => $locale['forum_0180']);
			$this->thread_info['post-filters'][1] = array('value' => INFUSIONS.'forum/viewthread.php?thread_id='.$thread_data['thread_id'].'&amp;section=latest',
				'locale' => $locale['forum_0181']);
			if ($this->thread_info['permissions']['can_rate']) {
				$this->thread_info['allowed-post-filters'][2] = 'high';
				$this->thread_info['post-filters'][2] = array('value' => INFUSIONS.'forum/viewthread.php?thread_id='.$this->thread_info['thread_id'].'&amp;section=high',
					'locale' => $locale['forum_0182']);
			}
			$this->get_thread_post();
			//self::set_ThreadJs();
			// execute in the end.
			//self::set_ForumPostDB();
		}
	}

	/**
	 * Get thread posts info
	 */
	private function get_thread_post() {
		global $settings, $forum_settings, $locale, $userdata;
		$user_sig_module = \PHPFusion\UserFields::check_user_field('user_sig');
		$user_web_module = \PHPFusion\UserFields::check_user_field('user_web');
		$userid = isset($userdata['user_id']) ? (int) $userdata['user_id'] : 0;
		switch ($this->thread_info['section']) {
			case 'oldest':
				$sortCol = 'post_datestamp ASC';
				break;
			case 'latest':
				$sortCol = 'post_datestamp DESC';
				break;
			case 'high':
				$sortCol = 'vote_points DESC';
				break;
			default:
				$sortCol = 'post_datestamp ASC';
		}
		// @todo: where to calculate has voted without doing it in while loop?
		$result = dbquery("
		SELECT p.*,
		t.thread_id,
					u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_posts, u.user_groups, u.user_joined, u.user_lastvisit, u.user_ip,
					".($user_sig_module ? " u.user_sig," : "").($user_web_module ? " u.user_web," : "")."
					u2.user_name AS edit_name, u2.user_status AS edit_status, a.attach_mime,
					SUM(v.vote_points) as vote_points, count(v2.thread_id) as has_voted
					FROM ".DB_FORUM_POSTS." p
					INNER JOIN ".DB_FORUM_THREADS." t ON t.thread_id = p.thread_id
					LEFT JOIN ".DB_FORUM_VOTES." v ON v.post_id = p.post_id
					LEFT JOIN ".DB_FORUM_VOTES." v2 on v2.thread_id = p.thread_id AND v2.vote_user = '".$userid."'
					LEFT JOIN ".DB_USERS." u ON p.post_author = u.user_id
					LEFT JOIN ".DB_USERS." u2 ON p.post_edituser = u2.user_id AND post_edituser > '0'
					LEFT JOIN ".DB_FORUM_ATTACHMENTS." a on a.post_id = p.post_id
					WHERE p.thread_id='".intval($_GET['thread_id'])."' AND post_hidden='0' ".($this->thread_info['thread']['forum_type'] == '4' ? "OR p.post_id='".intval($this->thread_info['post_firstpost'])."'" : '')."
					GROUP by p.post_id ORDER BY $sortCol LIMIT ".intval($_GET['rowstart']).", ".intval($forum_settings['posts_per_page']));
		$this->thread_info['post_rows'] = dbrows($result);
		if ($this->thread_info['post_rows'] > 0) {
			/* Set Threads Navigation */
			$this->thread_info['thread_posts'] = format_word($this->thread_info['post_rows'], $locale['fmt_post']);
			$this->thread_info['page_nav'] = '';
			if ($this->thread_info['max_post_items'] > $this->thread_info['posts_per_page']) {
				$this->thread_info['page_nav'] = "<div class='pull-right'>".makepagenav($_GET['rowstart'], $this->thread_info['posts_per_page'], $this->thread_info['max_post_items'], 3, INFUSIONS."forum/viewthread.php?forum_id=".$this->thread_info['forum_id']."&amp;thread_id=".$this->thread_info['thread']['thread_id'].(isset($_GET['highlight']) ? "&amp;highlight=".urlencode($_GET['highlight']) : '')."&amp;")."</div>";
			}
			$i = 1;
			while ($pdata = dbarray($result)) {
				if (!iMOD) {
					// Fix #184
					unset($pdata['post_ip']);
				}
				$pdata['user_online'] = $pdata['user_lastvisit'] >= time()-3600 ? 1 : 0;
				$pdata['is_first_post'] = $pdata['post_id'] == $this->thread_info['post_firstpost'] ? TRUE : FALSE;
				$pdata['is_last_post'] = $pdata['post_id'] == $this->thread_info['post_lastpost'] ? TRUE : FALSE;
				$pdata['post_message'] = $pdata['post_smileys'] ? parsesmileys($pdata['post_message']) : $pdata['post_message'];
				$pdata['post_message'] = nl2br(parseubb($pdata['post_message']));
				$pdata['post_message'] = (isset($_GET['highlight'])) ? "<div class='search_result'>".$pdata['post_message']."</div>\n" : $pdata['post_message'];
				/**
				 * User Stuffs, Sig, User Message, Web
				 */
				// Quote & Edit Link
				if (iMEMBER && ($this->thread_info['permissions']['can_post'] || $this->thread_info['permissions']['can_reply'])) {
					if (!$this->thread_info['thread']['thread_locked']) {
						$pdata['post_quote'] = array('link' => INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id']."&amp;quote=".$pdata['post_id'],
							'name' => $locale['forum_0266']);
						if (iMOD || (($this->thread_info['permissions']['edit_lock'] && $pdata['is_last_post'] || !$this->thread_info['permissions']['forum_edit_lock'])) && ($userdata['user_id'] == $pdata['post_author']) && ($forum_settings['forum_edit_timelimit'] <= 0 || time()-$forum_settings['forum_edit_timelimit']*60 < $pdata['post_datestamp'])) {
							$pdata['post_edit'] = array('link' => INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
								'name' => $locale['forum_0265']);
						}
						$pdata['post_reply'] = array('link' => INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
							'name' => $locale['forum_0509']);
					} elseif (iMOD) {
						$pdata['post_edit'] = array('link' => INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
							'name' => $locale['forum_0265']);
					}
				}
				$pdata['user_profile_link'] = profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']);
				$pdata['user_avatar'] = display_avatar($pdata, '40px', '', '', ''); // @todo: to go for settings base - class & size.
				// rank img
				if ($pdata['user_level'] <= USER_LEVEL_ADMIN) {
					if ($forum_settings['forum_ranks']) {
						$pdata['user_rank'] = show_forum_rank($pdata['user_posts'], $pdata['user_level'], $pdata['user_groups']); // in fact now is get forum rank
					} else {
						$pdata['user_rank'] = getuserlevel($pdata['user_level']);
					}
				} else {
					if ($forum_settings['forum_ranks']) {
						$pdata['user_rank'] = iMOD ? show_forum_rank($pdata['user_posts'], 104, $pdata['user_groups']) : show_forum_rank($pdata['user_posts'], $pdata['user_level'], $pdata['user_groups']);
					} else {
						$pdata['user_rank'] = iMOD ? $locale['userf1'] : getuserlevel($pdata['user_level']);
					}
				}
				// IP
				$pdata['user_ip'] = (($forum_settings['forum_ips'] && iMEMBER) || iMOD or USER_LEVEL_SUPER_ADMIN) ? $locale['forum_0268'].' '.$pdata['post_ip'] : '';
				// Post count
				$pdata['user_post_count'] = format_word($pdata['user_posts'], $locale['fmt_post']);
				// Print Thread
				$pdata['print'] = array('link' => BASEDIR."print.php?type=F&amp;thread=".$_GET['thread_id']."&amp;post=".$pdata['post_id']."&amp;nr=".($i+$_GET['rowstart']),
					'name' => $locale['forum_0179']);
				// Website
				if ($pdata['user_web'] && (iADMIN || $pdata['user_status'] != 6 && $pdata['user_status'] != 5)) {
					$user_web_url_prefix = !strstr($pdata['user_web'], "http://") ? "http://" : "";
					$pdata['user_web'] = array('link' => $user_web_url_prefix.$pdata['user_web'],
						'name' => $locale['forum_0364']);
				} else {
					$pdata['user_web'] = array('link' => '', 'name' => '');
				}
				// PM link
				$pdata['user_message'] = array('link' => '', 'name' => '');
				if (iMEMBER && $pdata['user_id'] != $userdata['user_id'] && (iADMIN || $pdata['user_status'] != 6 && $pdata['user_status'] != 5)) {
					$pdata['user_message'] = array('link' => BASEDIR.'messages.php?msg_send='.$pdata['user_id'],
						'name' => $locale['send_message']);
				}
				// User Sig
				if ($pdata['user_sig'] && isset($pdata['post_showsig']) && $pdata['user_status'] != 6 && $pdata['user_status'] != 5) {
					$pdata['user_sig'] = nl2br(parseubb(parsesmileys(stripslashes($pdata['user_sig'])), "b|i|u||center|small|url|mail|img|color"));
				}
				// Voting - need up or down link - accessible to author also the vote
				// answered and on going questions.
				$pdata['vote_message'] = '';
				//echo $data['forum_type'] == 4 ? "<br/>\n".(number_format($data['thread_postcount']-1)).$locale['forum_0365']."" : ''; // answers
				// form components
				$pdata['post_checkbox'] = iMOD ? "<input type='checkbox' name='delete_post[]' value='".$pdata['post_id']."'/>" : '';
				$pdata['post_votebox'] = '';
				// Answer rating
				if ($this->thread_info['thread']['forum_type'] == 4) {
					if ($this->thread_info['permissions']['can_rate'] && $pdata['has_voted'] == 0) { // can vote.
						$pdata['vote_up'] = array('link' => INFUSIONS."forum/postify.php?post=voteup&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
							'name' => $locale['forum_0265']);
						$pdata['vote_down'] = array('link' => INFUSIONS."forum/postify.php?post=votedown&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
							'name' => $locale['forum_0265']);
						$pdata['post_votebox'] = "<div class='text-center'>\n";
						$pdata['post_votebox'] .= "<a href='".$pdata['vote_up']['link']."' class='btn btn-default btn-xs m-b-5 p-5' title='".$locale['forum_0265']."'>\n<i class='entypo up-dir icon-xs'></i></a>";
						$pdata['post_votebox'] .= "<h3 class='m-0'>".(!empty($pdata['vote_points']) ? $pdata['vote_points'] : 0)."</h3>\n";
						$pdata['post_votebox'] .= "<a href='".$pdata['vote_down']['link']."' class='btn btn-default btn-xs m-t-5 p-5' title='".$locale['forum_0265']."'>\n<i class='entypo down-dir icon-xs'></i></a>";
						$pdata['post_votebox'] .= "</div>\n";
					} else {
						$pdata['post_votebox'] = "<div class='text-center'>\n";
						$pdata['post_votebox'] .= "<h3 class='m-0'>".(!empty($pdata['vote_points']) ? $pdata['vote_points'] : 0)."</h3>\n";
						$pdata['post_votebox'] .= "</div>\n";
					}
				}
				// Marker
				$pdata['marker'] = array('link' => "#post_".$pdata['post_id'],
					'name' => "#".($i+$_GET['rowstart']),
					'id' => "post_".$pdata['post_id']);
				$pdata['post_marker'] = !empty($pdata['marker']) ? "<a class='marker' href='".$pdata['marker']['link']."' id='".$pdata['marker']['id']."'>".$pdata['marker']['name']."</a>" : '';
				$pdata['post_marker'] .= "<a title='".$locale['forum_0241']."' href='#top'><i class='entypo up-open'></i></a>\n";
				// Edit Reason - NOT WORKING?
				$pdata['post_edit_reason'] = '';
				if ($pdata['post_edittime']) {
					$edit_reason = "<div class='edit_reason m-t-10'><small>".$locale['forum_0164'].profile_link($pdata['post_edituser'], $pdata['edit_name'], $pdata['edit_status']).$locale['forum_0167'].showdate("forumdate", $pdata['post_edittime'])."</small>\n";
					if ($pdata['post_editreason'] && iMEMBER) {
						$edit_reason .= "<br /><a id='reason_pid_".$pdata['post_id']."' rel='".$pdata['post_id']."' class='reason_button small' data-target='reason_div_pid_".$pdata['post_id']."'>";
						$edit_reason .= "<strong>".$locale['forum_0165']."</strong>";
						$edit_reason .= "</a>\n";
						$edit_reason .= "<div id='reason_div_pid_".$pdata['post_id']."' class='reason_div small'>".$pdata['post_editreason']."</div>\n";
					}
					$edit_reason .= "</div>\n";
					$pdata['post_edit_reason'] = $edit_reason;
					$this->edit_reason = TRUE;
				}
				// Attachments reset
				$pdata['attach_files_count'] = 0;
				$pdata['attach_image_count'] = 0;
				$pdata['post_attachments'] = '';
				//print_p($this->thread_info['attachments']);
				//print_p($this->thread_info['permissions']['can_download_attach']);
				if (isset($this->thread_info['attachments'][$pdata['post_id']]) && $this->thread_info['permissions']['can_download_attach']) {
					require_once INCLUDES."mimetypes_include.php";
					$pdata['attach-image'] = '';
					$pdata['attach-files'] = '';
					foreach ($this->thread_info['attachments'][$pdata['post_id']] as $attach) {
						if (in_array($attach['attach_mime'], img_mimeTypes())) {
							$pdata['attach-image'] .= display_image_attach($attach['attach_name'], "50", "50", $pdata['post_id'])."\n";
							$pdata['attach_image_count']++;
						} else {
							$pdata['attach-files'] .= "<div class='display-inline-block'><i class='entypo attach'></i><a href='".FUSION_SELF."?thread_id=".$_GET['thread_id']."&amp;getfile=".$attach['attach_id']."'>".$attach['attach_name']."</a>&nbsp;";
							$pdata['attach-files'] .= "[<span class='small'>".parsebytesize(filesize(INFUSIONS."forum/attachments/".$attach['attach_name']))." / ".$attach['attach_count'].$locale['forum_0162']."</span>]</div>\n";
							$pdata['attach_files_count']++;
						}
					}
					if ($pdata['attach_files_count']) {
						$pdata['post_attachments'] .= "<div class='emulated-fieldset well'>\n";
						$pdata['post_attachments'] .= "<span class='emulated-legend'>".profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']).$locale['forum_0154'].($pdata['attach_files_count'] > 1 ? $locale['forum_0158'] : $locale['forum_0157'])."</span>\n";
						$pdata['post_attachments'] .= "<div class='attachments-list m-t-10'>".$pdata['attach-files']."</div>\n";
						$pdata['post_attachments'] .= "</div>\n";
					}
					if ($pdata['attach_image_count']) {
						$pdata['post_attachments'] .= "<div class='emulated-fieldset well'>\n";
						$pdata['post_attachments'] .= "<span class='emulated-legend'>".profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']).$locale['forum_0154'].($pdata['attach_image_count'] > 1 ? $locale['forum_0156'] : $locale['forum_0155'])."</span>\n";
						$pdata['post_attachments'] .= "<div class='attachments-list'>".$pdata['attach-image']."</div>\n";
						$pdata['post_attachments'] .= "</div>\n";
						if (!defined('COLORBOX')) {
							define('COLORBOX', TRUE);
							add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
							add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
							add_to_jquery("$('a[rel^=\"attach\"]').colorbox({ current: '".$locale['forum_0159']." {current} ".$locale['forum_0160']." {total}',width:'80%',height:'80%'});");
						}
					}
				}
				/* Indicator of current post has attachment */
				if (!$pdata['post_attachments'] && !empty($pdata['attach_mime'])) {
					$pdata['post_attachments'] = "<small><i class='fa fa-clipboard'></i> ".$locale['forum_0184']."</small>\n";
				}
				// Custom Post Message Link/Buttons
				$pdata['post_links'] = '';
				$pdata['post_links'] .= !empty($pdata['post_quote']) ? "<a class='btn btn-xs btn-default' title='".$pdata['post_quote']['name']."' href='".$pdata['post_quote']['link']."'>".$pdata['post_quote']['name']."</a>\n" : '';
				$pdata['post_links'] .= !empty($pdata['post_edit']) ? "<a class='btn btn-xs btn-default' title='".$pdata['post_edit']['name']."' href='".$pdata['post_edit']['link']."'>".$pdata['post_edit']['name']."</a>\n" : '';
				$pdata['post_links'] .= !empty($pdata['print']) ? "<a class='btn btn-xs btn-default' title='".$pdata['print']['name']."' href='".$pdata['print']['link']."'>".$pdata['print']['name']."</a>\n" : '';
				$pdata['post_links'] .= !empty($pdata['user_web']) ? "<a class='btn btn-xs btn-default' class='forum_user_actions' href='".$pdata['user_web']['link']."' target='_blank'>".$pdata['user_web']['name']."</a>\n" : '';
				$pdata['post_links'] .= !empty($pdata['user_message']) ? "<a class='btn btn-xs btn-default' href='".$pdata['user_message']['link']."' target='_blank'>".$pdata['user_message']['name']."</a>\n" : '';
				// Post Date
				$pdata['post_date'] = $locale['forum_0524']." ".timer($pdata['post_datestamp'])." - ".showdate('forumdate', $pdata['post_datestamp']);
				$pdata['post_shortdate'] = $locale['forum_0524']." ".timer($pdata['post_datestamp']);
				$pdata['post_longdate'] = $locale['forum_0524']." ".showdate('forumdate', $pdata['post_datestamp']);
				$this->thread_info['post_items'][$pdata['post_id']] = $pdata;
				$i++;
			}
		}
	}

	private function set_ThreadJs() {
		$viewthread_js = '';
		//javascript to footer
		$highlight_js = "";
		$colorbox_js = "";
		$edit_reason_js = '';
		/** javascript **/
		// highlight jQuery plugin
		if (isset($_GET['highlight'])) {
			$words = explode(" ", urldecode($_GET['highlight']));
			$higlight = "";
			$i = 1;
			$c_words = count($words);
			foreach ($words as $hlight) {
				$hlight = htmlentities($hlight, ENT_QUOTES);
				$higlight .= "'".$hlight."'";
				$higlight .= ($i < $c_words ? "," : "");
				$i++;
			}
			add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.highlight.js'></script>");
			$highlight_js .= "$('.search_result').highlight([".$higlight."],{wordsOnly:true});";
			$highlight_js .= "$('.highlight').css({backgroundColor:'#FFFF88'});"; //better via theme or settings
		}
		$edit_reason_js .= "
			$('.reason_div').hide();
			$('div').find('.reason_button').css({cursor: 'pointer' });
			$('.reason_button').bind('click', function(e) {
				var target = $(this).data('target');
				$('#'+target).stop().slideToggle('fast');
			});
			";
		// viewthread javascript, moved to footer
		if (!empty($highlight_js) || !empty($colorbox_js) || !empty($edit_reason_js)) {
			$viewthread_js .= $highlight_js.$colorbox_js.$edit_reason_js;
		}
		$viewthread_js .= "$('a[href=#top]').click(function(){";
		$viewthread_js .= "$('html, body').animate({scrollTop:0}, 'slow');";
		$viewthread_js .= "return false;";
		$viewthread_js .= "});";
		$viewthread_js .= "});";
		// below functions could be made more unobtrusive thanks to jQuery, giving a more accessible cms
		$viewthread_js .= "function jumpforum(forum_id){";
		$viewthread_js .= "document.location.href='".INFUSIONS."forum/viewforum.php?forum_id='+forum_id;";
		$viewthread_js .= "}";
		if (iMOD) { // only moderators need this javascript
			$viewthread_js .= "function setChecked(frmName,chkName,val){";
			$viewthread_js .= "dml=document.forms[frmName];";
			$viewthread_js .= "len=dml.elements.length;";
			$viewthread_js .= "for(i=0;i<len;i++){";
			$viewthread_js .= "if(dml.elements[i].name==chkName){";
			$viewthread_js .= "dml.elements[i].checked=val;";
			$viewthread_js .= "}";
			$viewthread_js .= "}";
			$viewthread_js .= "}";
		}
		//$viewthread_js .= "/*]]>*/";
		//$viewthread_js .= "</script>";
		add_to_jquery($viewthread_js);
	}

	/**
	 * Handle Forum $_POST and Save/Update on SQL
	 */
	private function exec_post_actions() {
		// Post Quick Reply -- please see the function itself. it's not corelated to any other scripts anymore.
		if (isset($_POST['post_quick_reply'])) $this->handle_quickreply();
	}

	/*
	 * Execute quick reply posts
	 */
	protected function handle_quickreply() {
		global $userdata, $forum_settings, $locale;
		if ($this->thread_info['permissions']['can_reply']) {
			$info = self::get_thread_data();
			$thread_data = $info['thread'];
			require_once INCLUDES."flood_include.php";
			if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice
				$post_data = array('forum_id' => $thread_data['forum_id'],
					'thread_id' => $thread_data['thread_id'],
					'post_id' => 0,
					'post_message' => form_sanitizer($_POST['post_message'], '', 'post_message'),
					'post_showsig' => isset($_POST['post_showsig']) ? 1 : 0,
					'post_smileys' => isset($_POST['post_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? 0 : 1,
					'post_author' => $userdata['user_id'],
					'post_datestamp' => time(),
					'post_ip' => USER_IP,
					'post_ip_type' => USER_IP_TYPE,
					'post_edituser' => 0,
					'post_edittime' => 0,
					'post_editreason' => '',
					'post_hidden' => FALSE,
					'post_locked' => $forum_settings['forum_edit_lock'] || isset($_POST['post_locked']) ? 1 : 0,);
				$update_forum_lastpost = FALSE;
				// Prepare forum merging action
				$last_post_author = dbarray(dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread_data['thread_id']."' ORDER BY post_id DESC LIMIT 1"));
				if ($last_post_author['post_author'] == $post_data['post_author'] && $thread_data['forum_merge']) {
					$last_message = dbarray(dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread_data['thread_id']."' ORDER BY post_id DESC"));
					$post_data['post_id'] = $last_message['post_id'];
					$post_data['post_message'] = $last_message['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate", time()).":\n".$post_data['post_message'];
					dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', array('primary_key' => 'post_id'));
				} else {
					$update_forum_lastpost = TRUE;
					dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', array('primary_key' => 'post_id'));
					$post_data['post_id'] = dblastid();
					if (!defined("FUSION_NULL")) dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$post_data['post_author']."'");
				}
				if (!defined('FUSION_NULL')) { // post message is invalid or whatever is invalid
					// Update stats in forum and threads
					if ($update_forum_lastpost) {
						// find all parents and update them
						$list_of_forums = get_all_parent(dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), $thread_data['forum_id']);
						foreach ($list_of_forums as $fid) {
							dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$fid."'");
						}
						// update current forum
						dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$thread_data['forum_id']."'");
						// update current thread
						dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$post_data['post_id']."', thread_postcount=thread_postcount+1, thread_lastuser='".$post_data['post_author']."' WHERE thread_id='".$thread_data['thread_id']."'");
					}
					// set notify
					if ($forum_settings['thread_notify'] && isset($_POST['notify_me']) && $thread_data['thread_id']) {
						if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$thread_data['thread_id']."' AND notify_user='".$post_data['post_author']."'")) {
							dbquery("INSERT INTO ".DB_FORUM_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$thread_data['thread_id']."', '".time()."', '".$post_data['post_author']."', '1')");
						}
					}
				}
				redirect("postify.php?post=reply&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;thread_id=".intval($post_data['thread_id'])."&amp;post_id=".intval($post_data['post_id']));
			}
		}
	}

	/*
	 * Render full reply form
	 */
	public function render_reply_form() {
		global $locale, $userdata, $forum_settings, $settings;
		$thread_info = $this->thread_info;
		$thread_data = $this->thread_info['thread'];
		if ((!iMOD or !iSUPERADMIN) && $thread_data['thread_locked']) redirect(INFUSIONS.'forum/index.php');
		if ($this->thread_info['permissions']['can_reply']) {
			add_to_title($locale['global_201'].$locale['forum_0503']);
			add_breadcrumb(array('link' => '', 'title' => $locale['forum_0503']));
			// field data
			$post_data = array('forum_id' => $this->thread_info['thread']['forum_id'],
				'thread_id' => $this->thread_info['thread']['thread_id'],
				'post_id' => 0,
				'post_message' => isset($_POST['post_message']) ? form_sanitizer($_POST['post_message'], '', 'post_message') : '',
				'post_showsig' => isset($_POST['post_showsig']) ? 1 : 0,
				'post_smileys' => !isset($_POST['post_smileys']) || isset($_POST['post_message']) && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? FALSE : TRUE,
				'post_author' => $userdata['user_id'],
				'post_datestamp' => time(),
				'post_ip' => USER_IP,
				'post_ip_type' => USER_IP_TYPE,
				'post_edituser' => 0,
				'post_edittime' => 0,
				'post_editreason' => '',
				'post_hidden' => FALSE,
				'notify_me' => FALSE,
				'post_locked' => 0, //$forum_settings['forum_edit_lock'] || isset($_POST['post_locked']) ? 1 : 0,
			);
			// execute form post actions
			if (isset($_POST['post_reply'])) {
				require_once INCLUDES."flood_include.php";
				// all data is sanitized here.
				if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice
					$update_forum_lastpost = FALSE;
					if (!defined('FUSION_NULL')) {
						// Prepare forum merging action
						$last_post_author = dbarray(dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread_data['thread_id']."' ORDER BY post_id DESC LIMIT 1"));
						if ($last_post_author['post_author'] == $post_data['post_author'] && $thread_data['forum_merge']) {
							$last_message = dbarray(dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread_data['thread_id']."' ORDER BY post_id DESC"));
							$post_data['post_id'] = $last_message['post_id'];
							$post_data['post_message'] = $last_message['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate", time()).":\n".$post_data['post_message'];
							dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', array('primary_key' => 'post_id',
								'keep_session' => 1));
						} else {
							$update_forum_lastpost = TRUE;
							dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', array('primary_key' => 'post_id',
								'keep_session' => 1));
							$post_data['post_id'] = dblastid();
							if (!defined("FUSION_NULL")) dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$post_data['post_author']."'");
						}
						// Fix post_id missing on attach upload
						if (!empty($_FILES) && is_uploaded_file($_FILES['file_attachments']['tmp_name'][0])) {
							$upload = form_sanitizer($_FILES['file_attachments'], '', 'file_attachments');
							if ($upload['error'] == 0) {
								foreach ($upload['target_file'] as $arr => $file_name) {
									$adata = array('thread_id' => $thread_data['thread_id'],
										'post_id' => $post_data['post_id'],
										'attach_name' => $file_name,
										'attach_mime' => $upload['type'][$arr],
										'attach_size' => $upload['source_size'][$arr],
										'attach_count' => '0', // downloaded times?
									);
									dbquery_insert(DB_FORUM_ATTACHMENTS, $adata, 'save', array('keep_session' => TRUE));
								}
							}
						}
						// Update stats in forum and threads
						if ($update_forum_lastpost && !defined('FUSION_NULL')) {
							// find all parents and update them
							$list_of_forums = get_all_parent(dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), $thread_data['forum_id']);
							foreach ($list_of_forums as $fid) {
								dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$fid."'");
							}
							// update current forum
							dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$thread_data['forum_id']."'");
							// update current thread
							dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$post_data['post_id']."', thread_postcount=thread_postcount+1, thread_lastuser='".$post_data['post_author']."' WHERE thread_id='".$thread_data['thread_id']."'");
						}
						if ($forum_settings['thread_notify'] && isset($_POST['notify_me']) && $thread_data['thread_id'] && !defined('FUSION_NULL')) {
							if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$thread_data['thread_id']."' AND notify_user='".$post_data['post_author']."'")) {
								dbquery("INSERT INTO ".DB_FORUM_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$thread_data['thread_id']."', '".time()."', '".$post_data['post_author']."', '1')");
							}
						}
					}
					if (!defined('FUSION_NULL')) redirect("postify.php?post=reply&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;thread_id=".intval($post_data['thread_id'])."&amp;post_id=".intval($post_data['post_id']));
				}
			}
			// template data
			$form_action = ($settings['site_seo'] ? FUSION_ROOT : '').INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$thread_data['forum_id']."&amp;thread_id=".$thread_data['thread_id'];
			// Quote Get
			if (isset($_GET['quote']) && isnum($_GET['quote'])) {
				$quote_result = dbquery("SELECT a.post_message, b.user_name
										FROM ".DB_FORUM_POSTS." a
										INNER JOIN ".DB_USERS." b ON a.post_author=b.user_id
										WHERE thread_id='".$thread_data['thread_id']."' and post_id='".$_GET['quote']."'");
				if (dbrows($quote_result) > 0) {
					$quote_data = dbarray($quote_result);
					// do not do this. to silently inject.
					$post_data['post_message'] = "[quote name=".$quote_data['user_name']." post=".$_GET['quote']."]@".$quote_data['user_name']." - ".strip_bbcodes($quote_data['post_message'])."[/quote]".$post_data['post_message'];
					$form_action .= "&amp;post_id=".$_GET['post_id']."&amp;quote=".$_GET['quote'];
				} else {
					redirect(clean_request('', array('thread_id'), TRUE));
				}
			}
			$info = array('title' => $locale['forum_0503'],
				'description' => $locale['forum_2000'].$thread_data['thread_subject'],
				'openform' => openform('input_form', 'post', $form_action, array('enctype' => $thread_data['forum_allow_attach'] ? 1 : 0,
					'max_tokens' => 1)),
				'closeform' => closeform(),
				'forum_id_field' => form_hidden('forum_id', '', $post_data['forum_id']),
				'thread_id_field' => form_hidden('thread_id', '', $post_data['thread_id']),
				'subject_field' => form_hidden('thread_subject', '', $thread_data['thread_subject']),
				'message_field' => form_textarea('post_message', $locale['forum_0601'], $post_data['post_message'], array('required' => 1,
					'error_text' => '',
					'autosize' => 1,
					'no_resize' => 1,
					'preview' => 1,
					'form_name' => 'input_form',
					'bbcode' => 1)),
				// happens only in EDIT
				'delete_field' => '',
				'edit_reason_field' => '',
				'attachment_field' => $this->thread_info['permissions']['can_attach'] && $thread_data['forum_allow_attach'] ? array('title' => $locale['forum_0557'],
						'field' => "<div class='m-b-10'>".sprintf($locale['forum_0559'], parsebytesize($forum_settings['forum_attachmax']), str_replace('|', ', ', $forum_settings['forum_attachtypes']), $forum_settings['forum_attachmax_count'])."</div>\n
					".form_fileinput('file_attachments[]', "", "", array('input_id' => 'file_attachments',
								'upload_path' => INFUSIONS.'forum/attachments/',
								'type' => 'object',
								'preview_off' => TRUE,
								'multiple' => TRUE,
								'max_count' => $forum_settings['forum_attachmax_count'],
								'valid_ext' => $forum_settings['forum_attachtypes']))) : array(),
				'poll' => array(),
				'smileys_field' => form_checkbox('post_smileys', $locale['forum_0622'], $post_data['post_smileys'], array('class' => 'm-b-0')),
				'signature_field' => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', $locale['forum_0623'], $post_data['post_showsig'], array('class' => 'm-b-0')) : '',
				'sticky_field' => '',
				'lock_field' => '',
				'hide_edit_field' => '',
				'post_locked_field' => '',
				// not available in edit mode.
				'notify_field' => $forum_settings['thread_notify'] ? form_checkbox('notify_me', $locale['forum_0626'], $post_data['notify_me'], array('class' => 'm-b-0')) : '',
				'post_buttons' => form_button('post_reply', $locale['forum_0504'], $locale['forum_0504'], array('class' => 'btn-primary btn-sm')).form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default btn-sm m-l-10')),
				'last_posts_reply' => '',);
			// only in reply
			if ($forum_settings['forum_last_posts_reply']) {
				$result = dbquery("SELECT p.thread_id, p.post_message, p.post_smileys, p.post_author, p.post_datestamp, p.post_hidden,
							u.user_id, u.user_name, u.user_status, u.user_avatar
							FROM ".DB_FORUM_POSTS." p
							LEFT JOIN ".DB_USERS." u ON p.post_author = u.user_id
							WHERE p.thread_id='".$thread_data['thread_id']."' AND p.post_hidden='0'
							ORDER BY p.post_datestamp DESC LIMIT 0,".$forum_settings['forum_last_posts_reply']);
				if (dbrows($result)) {
					$title = sprintf($locale['forum_0526'], $forum_settings['forum_last_posts_reply']);
					if ($forum_settings['forum_last_posts_reply'] == "1") {
						$title = $locale['forum_0525'];
					}
					// backdoor to stringify echo'ed opentable.
					ob_start();
					opentable($title);
					echo "<table class='tbl-border forum_thread_table table table-responsive'>\n";
					$i = $forum_settings['forum_last_posts_reply'];
					while ($data = dbarray($result)) {
						$message = $data['post_message'];
						if ($data['post_smileys']) {
							$message = parsesmileys($message);
						}
						$message = parseubb($message);
						echo "<tr>\n<td class='tbl2 forum_thread_user_name' style='width:10%'><!--forum_thread_user_name-->".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
						echo "<td class='tbl2 forum_thread_post_date'>\n";
						echo "<div style='float:right' class='small'>\n";
						echo $i.($i == $forum_settings['forum_last_posts_reply'] ? " (".$locale['forum_0525'].")" : "");
						echo "</div>\n";
						echo "<div class='small'>".$locale['forum_0524'].showdate("forumdate", $data['post_datestamp'])."</div>\n";
						echo "</td>\n";
						echo "</tr>\n<tr>\n<td valign='top' class='tbl2 forum_thread_user_info' style='width:10%'>\n";
						echo display_avatar($data, '50px');
						echo "</td>\n<td valign='top' class='tbl1 forum_thread_user_post'>\n";
						echo nl2br($message);
						echo "</td>\n</tr>\n";
						$i--;
					}
					echo "</table>\n";
					closetable();
					$info['last_posts_reply'] = ob_get_contents();
					ob_end_clean();
				}
			}
			postform($info);
		} else {
			redirect(INFUSIONS.'forum/index.php');
		}
	}

	/*
	 * Render and execute edit form
	 */
	public function render_edit_form() {
		global $locale, $userdata, $forum_settings, $settings;
		$thread_data = $this->thread_info['thread'];
		if ((!iMOD or !iSUPERADMIN) && $thread_data['thread_locked']) redirect(INFUSIONS.'forum/index.php');
		if (isset($_GET['post_id']) && isnum($_GET['post_id'])) {
			add_to_title($locale['global_201'].$locale['forum_0503']);
			add_breadcrumb(array('link' => '', 'title' => $locale['forum_0503']));
			// field data
			// get post data
			$result = dbquery("SELECT tp.*, tt.thread_subject, tt.thread_poll, tt.thread_author, tt.thread_locked, MIN(tp2.post_id) AS first_post
				FROM ".DB_FORUM_POSTS." tp
				INNER JOIN ".DB_FORUM_THREADS." tt on tp.thread_id=tt.thread_id
				INNER JOIN ".DB_FORUM_POSTS." tp2 on tp.thread_id=tp2.thread_id
				WHERE tp.post_id='".intval($_GET['post_id'])."' AND tp.thread_id='".intval($thread_data['thread_id'])."' AND tp.forum_id='".intval($thread_data['forum_id'])."' GROUP BY tp2.post_id
				");
			if (dbrows($result) > 0) {
				$post_data = dbarray($result);
				if ($this->thread_info['permissions']['can_edit'] or $post_data['post_author'] == $userdata['user_id']) {
					$is_first_post = $post_data['post_id'] == $this->thread_info['post_firstpost'] ? TRUE : FALSE;
					// no edit if locked
					if ($post_data['post_locked'] && (!iMOD or !iSUPERADMIN)) {
						redirect("postify.php?post=edit&error=5&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&post_id=".$post_data['post_id']);
					}
					// no edit if time limit reached
					if (!iMOD && ($forum_settings['forum_edit_timelimit'] > 0 && time()-$forum_settings['forum_edit_timelimit']*60 > $post_data['post_datestamp'])) {
						redirect("postify.php?post=edit&error=6&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&post_id=".$post_data['post_id']);
					}
					// execute form post actions
					if (isset($_POST['post_edit'])) {
						require_once INCLUDES."flood_include.php";
						// all data is sanitized here.
						if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice
							$post_data = array('forum_id' => $this->thread_info['thread']['forum_id'],
								'thread_id' => $this->thread_info['thread']['thread_id'],
								'post_id' => $post_data['post_id'],
								'post_message' => form_sanitizer($_POST['post_message'], '', 'post_message'),
								'post_showsig' => isset($_POST['post_showsig']) ? 1 : 0,
								'post_smileys' => isset($_POST['post_smileys']) || isset($_POST['post_message']) && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? TRUE : FALSE,
								'post_author' => $userdata['user_id'],
								'post_datestamp' => $post_data['post_datestamp'], // update on datestamp or not?
								'post_ip' => USER_IP,
								'post_ip_type' => USER_IP_TYPE,
								'post_edituser' => $userdata['user_id'],
								'post_edittime' => time(),
								'post_editreason' => form_sanitizer($_POST['post_editreason'], '', 'post_editreason'),
								'post_hidden' => FALSE,
								'notify_me' => FALSE,
								'post_locked' => $forum_settings['forum_edit_lock'] or isset($_POST['post_locked']) ? TRUE : FALSE);
							// Do attachment first.
							// copied over attachments deletion
							foreach ($_POST as $key => $value) {
								if (!strstr($key, "delete_attach")) continue;
								$key = str_replace("delete_attach_", "", $key);
								$result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$post_data['post_id']."' AND attach_id='".(isnum($key) ? $key : 0)."'");
								if (dbrows($result) != 0 && $value) {
									$adata = dbarray($result);
									unlink(FORUM."attachments/".$adata['attach_name']);
									dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$post_data['post_id']."' AND attach_id='".(isnum($key) ? $key : 0)."'");
								}
							}
							// save all file attachments and get error
							if (!empty($_FILES) && is_uploaded_file($_FILES['file_attachments']['tmp_name'][0])) {
								$upload = form_sanitizer($_FILES['file_attachments'], '', 'file_attachments');
								if ($upload['error'] == 0) {
									foreach ($upload['target_file'] as $arr => $file_name) {
										$attachment = array('thread_id' => $thread_data['thread_id'],
											'post_id' => $post_data['post_id'],
											'attach_name' => $file_name,
											'attach_mime' => $upload['type'][$arr],
											'attach_size' => $upload['source_size'][$arr],
											'attach_count' => '0', // downloaded times?
										);
										dbquery_insert(DB_FORUM_ATTACHMENTS, $attachment, 'save', array('keep_session' => TRUE));
									}
								}
							}
							if (!defined('FUSION_NULL')) { // post message is invalid or whatever is invalid
								if ($is_first_post == TRUE) {
									$thread_subject = form_sanitizer($_POST['thread_subject'], '', 'thread_subject');
									if (!defined('FUSION_NULL')) {
										dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_subject='".$thread_subject."' WHERE thread_id='".intval($thread_data['thread_id'])."'");
									}
								}
								// Prepare forum merging action
								$last_post_author = dbarray(dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread_data['thread_id']."' ORDER BY post_id DESC LIMIT 1"));
								if ($last_post_author == $post_data['post_author'] && $thread_data['forum_merge']) {
									$last_message = dbarray(dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread_data['thread_id']."' ORDER BY post_id DESC"));
									$post_data['post_id'] = $last_message['post_id'];
									$post_data['post_message'] = $last_message['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate", time()).":\n".$post_data['post_message'];
									dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', array('primary_key' => 'post_id',
										'keep_session' => 1));
								} else {
									dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', array('primary_key' => 'post_id',
										'keep_session' => 1));
								}
							}
							redirect("postify.php?post=edit&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;thread_id=".intval($post_data['thread_id'])."&amp;post_id=".intval($post_data['post_id']));
						}
					}
					// template data
					$form_action = ($settings['site_seo'] ? FUSION_ROOT : '').INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$thread_data['forum_id']."&amp;thread_id=".$thread_data['thread_id']."&amp;post_id=".$_GET['post_id'];
					$info = array('title' => $locale['forum_0507'],
						'description' => $locale['forum_2000'].$thread_data['thread_subject'],
						'openform' => openform('input_form', 'post', $form_action, array('enctype' => 1,
							'max_tokens' => 1)),
						'closeform' => closeform(),
						'forum_id_field' => form_hidden('forum_id', '', $post_data['forum_id']),
						'thread_id_field' => form_hidden('thread_id', '', $post_data['thread_id']),
						// in edit or in new thread is a textbox
						// for new thread --
						/* 'subject_field' => form_text('thread_subject', $locale['forum_0600'], $thread_data['thread_subject'], array('required' => 1,
																																		'placeholder' => $locale['forum_2001'],
																																		'error_text' => '',
																																		'class' => 'm-t-20 m-b-20'))*/
						'subject_field' => $this->thread_info['post_firstpost'] == $_GET['post_id'] ? form_text('thread_subject', $locale['forum_0600'], $thread_data['thread_subject'], array('required' => 1,
																																	'placeholder' => $locale['forum_2001'],
																																	'error_text' => '',
																																	'class' => 'm-t-20 m-b-20')) : form_hidden("thread_subject", "", $thread_data['thread_subject']),
						'message_field' => form_textarea('post_message', $locale['forum_0601'], $post_data['post_message'], array('required' => 1,
							'error_text' => '',
							'autosize' => 1,
							'no_resize' => 1,
							'preview' => 1,
							'form_name' => 'input_form',
							'bbcode' => 1)),
						// happens only in EDIT
						'delete_field' => form_checkbox('delete', $locale['forum_0624'], '', array('class' => 'm-b-0')),
						'edit_reason_field' => form_text('post_editreason', $locale['forum_0611'], $post_data['post_editreason'], array('placeholder' => 'Edit reasons',
							'error_text' => '',
							'class' => 'm-t-20 m-b-20')),
						'attachment_field' => $this->thread_info['permissions']['can_attach'] && $thread_data['forum_allow_attach'] ? array('title' => $locale['forum_0557'],
								'field' => "<div class='m-b-10'>".sprintf($locale['forum_0559'], parsebytesize($forum_settings['forum_attachmax']), str_replace(',', ' ', $forum_settings['forum_attachtypes']), $forum_settings['forum_attachmax_count'])."</div>\n
								".form_fileinput('file_attachments[]', "", '', array('input_id' => 'file_attachments',
										'upload_path' => INFUSIONS.'forum/attachments/',
										'type' => 'object',
										'preview_off' => TRUE,
										'multiple' => TRUE,
										'max_count' => $forum_settings['forum_attachmax_count'],
										'valid_ext' => $forum_settings['forum_attachtypes']))) : array(),
						// only happens during edit on first post or new thread AND has poll -- info['forum_poll'] && checkgroup($info['forum_poll']) && ($data['edit'] or $data['new']
						//'poll' => $is_first_post && $thread_data['forum_allow_poll'] ? array('title'=>'Forum Poll', 'field'=>$poll_field) : array(),
						'smileys_field' => form_checkbox('post_smileys', $locale['forum_0622'], $post_data['post_smileys'], array('class' => 'm-b-0')),
						'signature_field' => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', $locale['forum_0623'], $post_data['post_showsig'], array('class' => 'm-b-0')) : '',
						//sticky only in new thread or edit first post
						'sticky_field' => ((iMOD || iSUPERADMIN) && $is_first_post) ? form_checkbox('thread_sticky', $locale['forum_0620'], $thread_data['thread_sticky'], array('class' => 'm-b-0')) : '',
						'lock_field' => (iMOD || iSUPERADMIN) ? form_checkbox('thread_locked', $locale['forum_0621'], $thread_data['thread_locked'], array('class' => 'm-b-0')) : '',
						'hide_edit_field' => form_checkbox('hide_edit', $locale['forum_0627'], '', array('class' => 'm-b-0')),
						// edit mode only
						'post_locked_field' => (iMOD || iSUPERADMIN) ? form_checkbox('post_locked', $locale['forum_0628'], $post_data['post_locked'], array('class' => 'm-b-0')) : '',
						// edit mode only
						// not available in edit mode.
						'notify_field' => '',
						//$forum_settings['thread_notify'] ? form_checkbox('notify_me', $locale['forum_0626'], $post_data['notify_me'], array('class' => 'm-b-0')) : '',
						'post_buttons' => form_button('post_edit', $locale['forum_0504'], $locale['forum_0504'], array('class' => 'btn-warning btn-sm')).form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default btn-sm m-l-10')),
						'last_posts_reply' => '',);
					if (!empty($info['attachment_field']) && isset($this->thread_info['attachments'][$post_data['post_id']])) { // need id
						$a_info = '';
						foreach ($this->thread_info['attachments'][$post_data['post_id']] as $attachment) {
							$a_info .= "<label><input type='checkbox' name='delete_attach_".$attachment['attach_id']."' value='1' /> ".$locale['forum_0625']."</label>\n"."<a href='".INFUSIONS."forum/attachments/".$attachment['attach_name']."'>".$attachment['attach_name']."</a> [".parsebytesize($attachment['attach_size'])."]\n"."<br/>\n";
						}
						$info['attachment_field']['field'] = $a_info.$info['attachment_field']['field'];
					}
					postform($info);
				} else {
					redirect(INFUSIONS.'forum/index.php'); // no access
				}
			} else {
				redirect("postify.php?post=edit&error=4&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&post_id=".$_GET['post_id']);
			}
		} else {
			redirect(INFUSIONS.'forum/index.php');
		}
	}

	/*
	 * Execute delete poll
	 */
	public function delete_poll() {
		if ($this->thread_info['thread']['thread_poll'] && $this->thread_info['permissions']['can_poll']) {
			$thread_data = $this->thread_info['thread'];
			if (!defined('FUSION_NULL')) {
				dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".$thread_data['thread_id']."'");
				dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$thread_data['thread_id']."'");
				dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".$thread_data['thread_id']."'");
				dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_poll='0' WHERE thread_id='".$thread_data['thread_id']."'");
				redirect("postify.php?post=deletepoll&error=4&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&post_id=".$_GET['post_id']);
			} else {
				redirect(INFUSIONS."forum/viewthread.php?forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']);
			}
		}
	}

	// Poll form
	public function render_poll_form($edit = 0) {
		global $locale;
		$poll_field = '';
		// Build Polls Info.
		$thread_data = $this->thread_info['thread'];
		// secure poll form from being edited by others
		$access = $this->thread_info['permissions']['can_poll'];
		if ($edit) {
			$access = $this->thread_info['permissions']['can_modify_poll'] ? TRUE : FALSE;
		}
		if ($access == TRUE && $thread_data['forum_allow_poll']) { // if permitted to create new poll.
			// edit callback
			// add poll without saving to database.
			$data = array('thread_id' => $thread_data['thread_id'],
				'forum_poll_title' => isset($_POST['forum_poll_title']) ? form_sanitizer($_POST['forum_poll_title'], '', 'forum_poll_title') : '',
				'forum_poll_start' => time(), // time poll started
				'forum_poll_length' => 2, // how many poll options we have
				'forum_poll_votes' => 0, // how many vote this poll has
			);
			// counter of lengths
			$option_data[1] = "";
			$option_data[2] = "";
			// calculate poll lengths
			if (isset($_POST['poll_options'])) {
				// callback on post.
				foreach ($_POST['poll_options'] as $i => $value) {
					$option_data[$i] = form_sanitizer($value, '', "poll_options[$i]");
				}
				// reindex the whole array with blank values.
				if (!defined('FUSION_NULL')) {
					$option_data = array_values(array_filter($option_data));
					array_unshift($option_data, NULL);
					unset($option_data[0]);
					$data['forum_poll_length'] = count($option_data);
				}
			}
			// add a Blank Poll option
			if (isset($_POST['add_poll_option']) && !defined('FUSION_NULL')) {
				array_push($option_data, '');
			}
			if ($edit) {
				$result = dbquery("SELECT * FROM ".DB_FORUM_POLLS." WHERE thread_id='".$thread_data['thread_id']."'");
				if (dbrows($result) > 0) {
					if (isset($_POST['update_poll']) || isset($_POST['add_poll_option'])) {
						$load = FALSE;
						$data += dbarray($result); // append if not available.
					} else {
						$load = TRUE;
						$data = dbarray($result); // call
					}
					if (isset($_POST['update_poll'])) {
						$data = array('thread_id' => $thread_data['thread_id'],
							'forum_poll_title' => form_sanitizer($_POST['forum_poll_title'], '', 'forum_poll_title'),
							'forum_poll_start' => $data['forum_poll_start'], // time poll started
							'forum_poll_length' => $data['forum_poll_length'], // how many poll options we have
						);
						dbquery_insert(DB_FORUM_POLLS, $data, 'update', array('primary_key' => 'thread_id',
							'no_unique' => TRUE));
						$i = 1;
						// populate data for matches
						$poll_result = dbquery("SELECT forum_poll_option_id FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$thread_data['thread_id']."'");
						while ($_data = dbarray($poll_result)) {
							$_poll[$_data['forum_poll_option_id']] = $_data;
							// Prune the emptied fields AND field is not required.
							if (empty($option_data[$_data['forum_poll_option_id']]) && !defined('FUSION_NULL')) {
								dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$thread_data['thread_id']."' AND forum_poll_option_id='".$_data['forum_poll_option_id']."'");
							}
						}
						foreach ($option_data as $option_text) {
							if ($option_text) {
								if (!defined('FUSION_NULL')) {
									if (isset($_poll[$i])) { // has record
										dbquery("UPDATE ".DB_FORUM_POLL_OPTIONS." SET forum_poll_option_text='".$option_text."' WHERE thread_id='".$thread_data['thread_id']."' AND forum_poll_option_id='".$i."'");
									} else { // no record - create
										$array = array('thread_id' => $thread_data['thread_id'],
											'forum_poll_option_id' => $i,
											'forum_poll_option_text' => $option_text,
											'forum_poll_option_votes' => 0,);
										dbquery_insert(DB_FORUM_POLL_OPTIONS, $array, 'save');
									}
								}
								$i++;
							}
						}
						if (!defined('FUSION_NULL')) {
							redirect("postify.php?post=editpoll&error=0&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']);
						}
					}
					// how to make sure values containing options votes
					$poll_field['openform'] = openform('pollform', 'post', INFUSIONS.'forum/viewthread.php?action=editpoll&forum_id='.$_GET['forum_id'].'&thread_id='.$_GET['thread_id'], array('max_tokens' => 1));
					$poll_field['openform'] .= "<div class='alert alert-info'>".$locale['forum_0613']."</div>\n";
					$poll_field['poll_field'] = form_text('forum_poll_title', $locale['forum_0604'], $data['forum_poll_title'], array('max_length' => 255,
						'placeholder' => 'Enter a Poll Title',
						'inline' => 1,
						'required' => TRUE));
					if ($load == FALSE) {
						for ($i = 1; $i <= count($option_data); $i++) {
							$poll_field['poll_field'] .= form_text("poll_options[$i]", sprintf($locale['forum_0606'], $i), $option_data[$i], array('max_length' => 255,
								'placeholder' => $locale['forum_0605'],
								'inline' => 1,
								'required' => $i <= 2 ? TRUE : FALSE));
						}
					} else {
						$result = dbquery("SELECT forum_poll_option_text, forum_poll_option_votes FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY forum_poll_option_id ASC");
						$i = 1;
						while ($_pdata = dbarray($result)) {
							$poll_field['poll_field'] .= form_text("poll_options[$i]", $locale['forum_0605'].' '.$i, $_pdata['forum_poll_option_text'], array('max_length' => 255,
								'placeholder' => 'Poll Options',
								'inline' => 1,
								'required' => $i <= 2 or $_pdata['forum_poll_option_votes'] ? TRUE : FALSE));
							$i++;
						}
					}
					$poll_field['poll_field'] .= "<div class='col-xs-12 col-sm-offset-3'>\n";
					$poll_field['poll_field'] .= form_button('add_poll_option', $locale['forum_0608'], $locale['forum_0608'], array('class' => 'btn-primary btn-sm'));
					$poll_field['poll_field'] .= "</div>\n";
					$poll_field['poll_button'] = form_button('update_poll', $locale['forum_2013'], $locale['forum_2013'], array('class' => 'btn-warning btn-md'));
					$poll_field['closeform'] = closeform();
				} else {
					redirect(INFUSIONS.'forum/index.php'); // redirect because the poll id is not available.
				}
			} else {
				// Save New Poll
				if (isset($_POST['add_poll'])) {
					dbquery_insert(DB_FORUM_POLLS, $data, 'save');
					$data['forum_poll_id'] = dblastid();
					$i = 1;
					foreach ($option_data as $option_text) {
						if ($option_text) {
							$data['forum_poll_option_id'] = $i;
							$data['forum_poll_option_text'] = $option_text;
							$data['forum_poll_option_votes'] = 0;
							dbquery_insert(DB_FORUM_POLL_OPTIONS, $data, 'save');
							$i++;
						}
					}
					if (!defined('FUSION_NULL')) {
						dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_poll='1' WHERE thread_id='".$thread_data['thread_id']."'");
						redirect("postify.php?post=newpoll&error=0&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']);
					}
				}
				// blank poll - no poll on edit or new thread
				$poll_field['openform'] = openform('pollform', 'post', INFUSIONS.'forum/viewthread.php?action=newpoll&forum_id='.$_GET['forum_id'].'&thread_id='.$_GET['thread_id'], array('max_tokens' => 1));
				$poll_field['poll_field'] = form_text('forum_poll_title', $locale['forum_0604'], $data['forum_poll_title'], array('max_length' => 255,
					'placeholder' => 'Enter a Poll Title',
					'inline' => 1,
					'required' => TRUE));
				for ($i = 1; $i <= count($option_data); $i++) {
					$poll_field['poll_field'] .= form_text("poll_options[$i]", sprintf($locale['forum_0606'], $i), $option_data[$i], array('max_length' => 255,
						'placeholder' => $locale['forum_0605'],
						'inline' => 1,
						'required' => $i <= 2 ? TRUE : FALSE));
				}
				$poll_field['poll_field'] .= "<div class='col-xs-12 col-sm-offset-3'>\n";
				$poll_field['poll_field'] .= form_button('add_poll_option', $locale['forum_0608'], $locale['forum_0608'], array('class' => 'btn-primary btn-sm'));
				$poll_field['poll_field'] .= "</div>\n";
				$poll_field['poll_button'] = form_button('add_poll', $locale['forum_2011'], $locale['forum_2011'], array('class' => 'btn-success btn-md'));
				$poll_field['closeform'] = closeform();
			}
			$info = array('title' => $locale['forum_0366'],
				'description' => $locale['forum_2000'].$thread_data['thread_subject'],
				'field' => $poll_field,);
			pollform($info);
		} else {
			redirect(FORUM."index.php");
		}
	}
}

