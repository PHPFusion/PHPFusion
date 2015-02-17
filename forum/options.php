<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: options.php
| Author: Nick Jones (Digitanium)
| Co-author: Grzegorz Lipok (Grzes)
| Rewritten: Frederick MC Chan
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

include LOCALE.LOCALESET."forum.php";

/* @todo: I have already done most of the actions here, except move_posts() */
class moderator {

	private $allowed_actions = array(
		'renew',
		'delete',
		'unsticky',
		'sticky',
		'lock',
		'unlock',
		'move'
	);

	protected $forum_id = 0;

	/**
	 * SQL action remove thread
	 * @param $thread_id
	 * @return array of affected rows
	 *               - post deleted
	 *               - attachment deleted
	 *               - user thread tracking deleted.
	 */
	static function remove_thread($thread_id) {
		$data = array();
		if (self::verify_thread($thread_id)) {
			dbquery("DELETE FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."'");
			$data['post_deleted'] = mysql_affected_rows();
			dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE thread_id='".$_GET['thread_id']."'");
			$data['track_deleted'] = mysql_affected_rows();
			$result = dbquery("SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".$_GET['thread_id']."'");
			if (dbrows($result) != 0) {
				while ($attach = dbarray($result)) {
					@unlink(FORUM."attachments/".$attach['attach_name']);
				}
			}
			dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".$_GET['thread_id']."'");
			$data['attachment_deleted'] = mysql_affected_rows();

			dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".$_GET['thread_id']."'");
			$data['votes_deleted'] = mysql_affected_rows();

			dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."'");
			$data['poll_options_deleted'] = mysql_affected_rows();

			dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".$_GET['thread_id']."'");
			$data['polls_deleted'] = mysql_affected_rows();

			dbquery("DELETE FROM ".DB_THREADS." WHERE thread_id='".$_GET['thread_id']."'");
			$data['thread_deleted'] = mysql_affected_rows();
		}
		return $data;
	}

	/**
	 * Unset User Post based on Thread id
	 * This function assumes as if user have never posted before
	 * @param $thread_id
	 * @return int - number of posts that user have made in this thread
	 */
	static function unset_userpost($thread_id) {
		$post_count = 0;
		if (self::verify_thread($thread_id)) {
			$result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_POSTS." WHERE thread_id='".$thread_id."' GROUP BY post_author");
			$rows = dbrows($result);
			if ($rows >0) {
				while ($pdata = dbarray($result)) {
					dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-".$pdata['num_posts']." WHERE user_id='".$pdata['post_author']."'");
					$post_count = $pdata['num_posts']+$post_count;
				}
			}
		}
		return (int) $post_count;
	}

	/**
	 * Reconsolidate the forum information
	 * It is as good as 'refresh forum info'
	 * @param $forum_id
	 */
	static function refresh_forum($forum_id) {
		if (self::verify_forum($forum_id)) {
			$remaining_threads_count = dbcount("(forum_id)", DB_THREADS, "forum_id='$forum_id'");
			if ($remaining_threads_count) {
				$result = dbquery("SELECT p.forum_id, p.post_author, p.post_datestamp, COUNT(p.post_id) AS post_count FROM ".DB_POSTS." p
							INNER JOIN ".DB_THREADS." t ON p.thread_id=t.thread_id
							WHERE p.forum_id='".$forum_id."' AND t.thread_hidden='0' AND p.post_hidden='0'
							ORDER BY p.post_datestamp DESC LIMIT 1");
				if (dbrows($result)>0) {
					$pdata = dbarray($result); // yielded LAST post
					dbquery("UPDATE ".DB_FORUMS." SET
							forum_lastpostid = '".$pdata['post_id']."',
							forum_lastpost = '".$pdata['post_datestamp']."',
							forum_postcount = '".$pdata['post_count']."',
							forum_threadcount = forum_threadcount-1,
							forum_lastuser = '".$pdata['post_author']."'
							WHERE forum_id = '".$forum_id."'
							");
				}
			} else {
				dbquery("UPDATE ".DB_FORUMS." SET forum_lastpostid = '0', forum_lastpost='0', forum_postcount=0, forum_threadcount=0, forum_lastuser='0' WHERE forum_id='".intval($forum_id)."'");
			}
		}
	}

	/**
	 * @param $thread_id
	 * @return int - a forum id
	 */
	static function get_forum_id($thread_id) {
		$id = 0;
		if (isnum($thread_id) && self::verify_thread($thread_id)) {
			$res = dbarray(dbquery("SELECT forum_id FROM ".DB_THREADS." WHERE thread_id='".intval($thread_id)."'"));
			$id = $res['forum_id'];
		}
		return (int) $id;
	}

	/**
	 * @param int $forum_id
	 */
	public function setForumId($forum_id) {
		$this->forum_id = $forum_id;
	}

	/**
	 * Get the constructor's forum id.
	 * @return int
	 */
	public function getForumId() {
		return $this->forum_id;
	}

	public function __construct() {
		global $locale;
		$_GET['thread_id'] = isset($_GET['thread_id']) && self::verify_thread($_GET['thread_id']) ? $_GET['thread_id'] : 0;
		if (!$_GET['thread_id']) redirect(FORUM."index.php"); // if no thread_id, we cannot do anything. just exit.

		$this->forum_id = self::get_forum_id($_GET['thread_id']); // base on the thread id, we generate a forum id.

		if (isset($_POST['step']) && $_POST['step'] != "") { $_GET['step'] = $_POST['step']; }
		$_GET['step'] = isset($_GET['step']) && in_array($_GET['step'], $this->allowed_actions) ? $_GET['step'] : '';
		$_GET['error'] = isset($_GET['error']) ? $_GET['error'] : '';
		// at any time when cancel is clicked, redirect to forum id.
		if (isset($_POST['canceldelete'])) redirect("viewthread.php?forum_id=".$this->forum_id."&amp;thread_id=".$_GET['thread_id']);

		// moderator actions only consist of the following steps.
		switch($_GET['step']) {
			case 'renew':
				self::mod_renew_thread();
				break;
			case 'delete':
				self::mod_delete_thread();
				break;
			case 'lock':
				self::mod_lock_thread();
				break;
			case 'unlock':
				self::mod_unlock_thread();
				break;
			case 'sticky':
				self::mod_sticky_thread();
				break;
			case 'nonsticky':
				self::mod_nonsticky_thread();
				break;
			case 'move':
				self::mod_move_thread();
				break;
		}


		$message = '';
		switch($_GET['error']) {
			case '1':
				$message = $locale['error-MP001'];
				break;
			case '2':
				$message = $locale['error-MP002'];
				break;
			case '3':
				$message = $locale['error-MP003'];
				break;
		}

		if ($message != "") {
			opentable($locale['error-MP000']);
			echo "<div id='close-message'><div class='admin-message'>".$message."<br /><br />\n";
			echo "<a href='".FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>".$locale['forum_0309']."</a><br />";
			echo "</div></div>\n";
			closetable();
		}

		self::mod_delete_posts();
		self::mod_move_posts();
	}

	/* Authenticate thread exist */
	public static function verify_thread($thread_id) {
		if (isnum($thread_id)) {
			return dbcount("('thread_id')", DB_THREADS, "thread_id = '".intval($thread_id)."'");
		}
		return false;
	}

	/* Authenticate forum exist */
	static function verify_forum($forum_id) {
		if (isnum($forum_id)) {
			return dbcount("('forum_id')", DB_FORUMS, "forum_id = '".intval($forum_id)."'");
		}
		return false;
	}

	/**
	 * Moderator Action - Renew Thread
	 * Modal pop up confirmation of thread being `renewed`
	 */
	protected function mod_renew_thread() {
		global $locale;
		$result = dbquery("SELECT p.post_id, p.post_author, p.post_datestamp, f.forum_id
		 FROM ".DB_POSTS." p
		INNER JOIN ".DB_THREADS." t ON p.thread_id=t.thread_id
		INNER JOIN ".DB_FORUMS." f on f.forum_id = t.forum_id
		WHERE p.thread_id='".$_GET['thread_id']."' AND t.thread_hidden='0' AND p.post_hidden='0'
		ORDER BY p.post_datestamp DESC LIMIT 1");
		if (dbrows($result)) {
			$data = dbarray($result);
			$result = dbquery("UPDATE ".DB_POSTS." SET post_datestamp='".time()."' WHERE post_id='".$data['post_id']."'");
			$result = dbquery("UPDATE ".DB_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$data['post_id']."', thread_lastuser='".$data['post_author']."' WHERE thread_id='".$_GET['thread_id']."'");
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_lastuser='".$data['post_author']."' WHERE forum_id='".$this->forum_id."'");
			echo openmodal('renew', $locale['forum_0758'], array('class'=>'modal-center', 'static'=>1));
			echo "<div style='text-align:center'><br />\n".$locale['forum_0759']."<br /><br />\n";
			echo "<a href='".FORUM."viewforum.php?forum_id=".$this->forum_id."'>".$locale['forum_0702']."</a><br /><br />\n";
			echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br /></div>\n";
			echo closemodal();
		} else {
			redirect("index.php");
		}
	}

	/**
	 * Moderator Action - Delete Thread
	 * Modal pop up confirmation of thread being `removed`
	 */
	protected function mod_delete_thread() {
		global $locale;
		$forum_id = self::getForumId();
		echo openmodal('deletethread',$locale['forum_0700'], array('class'=>'modal-center'));
		echo "<div style='text-align:center'><br />\n";
		if (!isset($_POST['deletethread'])) {
			echo "<form name='delform' method='post' action='".FUSION_SELF."?step=delete&amp;forum_id=".$forum_id."&amp;thread_id=".$_GET['thread_id']."'>\n";
			echo $locale['forum_0704']."<br /><br />\n";
			echo form_button($locale['yes'], 'deletethread', 'deletethread', $locale['yes'], array('class'=>'m-r-10 btn-danger'));
			echo form_button($locale['no'], 'canceldelete', 'canceldelete', $locale['no'], array('class'=>'m-r-10 btn-default'));
			echo "</form>\n";
			echo closeform();
		} else {

			if ($forum_id) {

				// reset every user post count as if they never posted before
				self::unset_userpost($_GET['thread_id']);
				// then we remove thread. outputs information what have been deleted
				$data = self::remove_thread($_GET['thread_id']);
				// refresh forum information as if thread never existed
				self::refresh_forum($forum_id);

				if (!empty($data)) {
					echo $locale['forum_0701']."<br /><br />\n";
					echo "<ul>\n";
					echo "<li><i class='fa fa-check'></i> ".$data['post_deleted']." posts deleted</li>\n";
					echo "<li><i class='fa fa-check'></i> ".$data['track_deleted']." tracks deleted</li>\n";
					echo "<li><i class='fa fa-check'></i> ".$data['attachment_deleted']." attachments deleted</li>\n";
					echo "<li><i class='fa fa-check'></i> ".$data['polls_deleted']." polls deleted</li>\n";
					echo "<li><i class='fa fa-check'></i> ".$data['poll_options_deleted']." poll options deleted</li>\n";
					echo "<li><i class='fa fa-check'></i> ".$data['votes_deleted']." votes deleted</li>\n";
					echo "</ul>\n";
					echo "<a href='viewforum.php?forum_id=".$data['forum_id']."'>".$locale['forum_0702']."</a><br /><br />\n";
					echo "<a href='index.php'>".$locale['forum_0703']."</a><br /><br />\n";
				} else {
					echo "Unable to remove thread because thread does not exist";
				}
			} else {
				echo "Unable to remove thread because there are no forum exist";
			}
		}
		echo "</div>\n";
		echo closemodal();
	}

	/**
	 * Moderator Action - Lock Thread
	 * Modal pop up confirmation of thread being `locked`
	 */
	protected function mod_lock_thread() {
		global $locale;
		dbquery("UPDATE ".DB_THREADS." SET thread_locked='1' WHERE thread_id='".$_GET['thread_id']."' AND thread_hidden='0'");
		echo openmodal('lockthread',$locale['forum_0710'], array('class'=>'modal-center'));
		echo "<div style='text-align:center'><br />\n";
		echo "<strong>".$locale['forum_0711']."</strong><br /><br />\n";
		echo "<a href='".FORUM."viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['forum_0702']."</a><br /><br />\n";
		echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br />\n</div>\n";
		echo closemodal();
	}

	/**
	 * Moderator Action - Unlock Thread
	 * Modal pop up confirmation of thread being `unlocked`
	 */
	protected function mod_unlock_thread() {
		global $locale;
		dbquery("UPDATE ".DB_THREADS." SET thread_locked='0' WHERE thread_id='".$_GET['thread_id']."' AND thread_hidden='0'");
		echo openmodal('lockthread',$locale['forum_0720'], array('class'=>'modal-center'));
		echo "<div style='text-align:center'><br />\n";
		echo "<strong>".$locale['forum_0721']."</strong><br /><br />\n";
		echo "<a href='".FORUM."viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['forum_0702']."</a><br /><br />\n";
		echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br />\n</div>\n";
		echo closemodal();
	}


	/**
	 * Moderator Action - Non Sticky Thread
	 * Modal pop up confirmation of thread being `un-sticky`
	 */
	protected function mod_nonsticky_thread() {
		global $locale;
		dbquery("UPDATE ".DB_THREADS." SET thread_sticky='0' WHERE thread_id='".$_GET['thread_id']."' AND thread_hidden='0'");
		echo openmodal('lockthread',$locale['forum_0740'], array('class'=>'modal-center'));
		echo "<div style='text-align:center'><br />\n";
		echo "<strong>".$locale['forum_0741']."</strong><br /><br />\n";
		echo "<a href='".FORUM."viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['forum_0702']."</a><br /><br />\n";
		echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br /></div>\n";
		echo closemodal();
	}


	/**
	 * Moderator Action - Sticky Thread
	 * Modal pop up confirmation of thread being `sticky`
	 */
	protected function mod_sticky_thread() {
		global $locale;
		$result = dbquery("UPDATE ".DB_THREADS." SET thread_sticky='1' WHERE thread_id='".$_GET['thread_id']."' AND thread_hidden='0'");
		echo openmodal('lockthread',$locale['forum_0730'], array('class'=>'modal-center'));
		echo "<div style='text-align:center'><br />\n";
		echo "<strong>".$locale['forum_0731']."</strong><br /><br />\n";
		echo "<a href='".FORUM."viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['forum_0702']."</a><br /><br />\n";
		echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br />\n</div>\n";
		echo closemodal();
	}

	/* SQL Move Thread -- still unclean */
	protected function mod_move_thread() {
		global $locale;
		$forum_id = self::getForumId();
		echo openmodal('lockthread',$locale['forum_0750'], array('class'=>'modal-center'));
		if (isset($_POST['move_thread'])) {
			echo "<div style='text-align:center'><br />\n";
			if (!isset($_POST['new_forum_id']) || !isnum($_POST['new_forum_id'])) redirect("index.php");
			if (!dbcount("(forum_id)", DB_FORUMS, "forum_id='".$_POST['new_forum_id']."' AND ".groupaccess('forum_access'))) redirect("../index.php");
			if (!dbcount("(thread_id)", DB_THREADS, "thread_id='".$_GET['thread_id']."' AND thread_hidden='0'")) redirect("../index.php");
			$result = dbquery("UPDATE ".DB_THREADS." SET forum_id='".$_POST['new_forum_id']."' WHERE thread_id='".$_GET['thread_id']."'");
			$result = dbquery("UPDATE ".DB_POSTS." SET forum_id='".$_POST['new_forum_id']."' WHERE thread_id='".$_GET['thread_id']."'");
			$post_count = dbcount("(post_id)", DB_POSTS, "thread_id='".$_GET['thread_id']."'");
			$result = dbquery("SELECT thread_lastpost, thread_lastuser FROM ".DB_THREADS." WHERE forum_id='".$_GET['forum_id']."' AND thread_hidden='0' ORDER BY thread_lastpost DESC LIMIT 1");
			if (dbrows($result)) {
				$pdata2 = dbarray($result);
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$pdata2['thread_lastpost']."', forum_postcount=forum_postcount-".$post_count.", forum_threadcount=forum_threadcount-1, forum_lastuser='".$pdata2['thread_lastuser']."' WHERE forum_id='".$_GET['forum_id']."'");
			} else {
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_postcount=forum_postcount-".$post_count.", forum_threadcount=forum_threadcount-1, forum_lastuser='0' WHERE forum_id='".$_GET['forum_id']."'");
			}
			$result = dbquery("SELECT thread_lastpost, thread_lastuser FROM ".DB_THREADS." WHERE forum_id='".$_POST['new_forum_id']."' AND thread_hidden='0' ORDER BY thread_lastpost DESC LIMIT 1");
			if (dbrows($result)) {
				$pdata2 = dbarray($result);
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$pdata2['thread_lastpost']."', forum_postcount=forum_postcount+".$post_count.", forum_threadcount=forum_threadcount+1, forum_lastuser='".$pdata2['thread_lastuser']."' WHERE forum_id='".$_POST['new_forum_id']."'");
			} else {
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+".$post_count.", forum_lastuser='0' WHERE forum_id='".$_POST['new_forum_id']."'");
			}
			echo "<strong>".$locale['forum_0752']."</strong><br /><br />\n";
			echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br />\n</div>\n";
		} else {
			if ($forum_id) {
				echo "<div>";
				echo openform('moveform', 'moveform', 'post', FUSION_SELF."?step=move&forum_id=".$forum_id."&amp;thread_id=".$data['thread_id'], array('downtime' => 1));
				echo form_select_tree($locale['forum_0751'], 'new_forum_id', 'new_forum_id2', '',
									  array(
										  'inline'=>1,
										  'disable_opts' => dbcount("('forum_id')",  DB_FORUMS) > 1 ? $forum_id : 0,
										  'no_root'=>1,
									  ),
									  DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');
				echo form_button($locale['forum_0750'], 'move_thread', 'move_thread', $locale['forum_0750'], array('class'=>'btn-primary'));
				echo closeform();
			} else {
				echo "Cannot move forum because thread_id and forum_id is corrupted.";
			}
		}
		echo "</div>\n";
		echo closemodal();
	}

	/**
	 * Moderator Action - Remove only selected post in a thread
	 * Requires $_POST['delete_posts']
	 * Modal pop up confirmation of thread being removed
	 */
	protected function mod_delete_posts() {
		global $locale;
		// insert code from viewthread_options.php
		if (isset($_POST['delete_posts']) && isset($_POST['delete_post']) && is_array($_POST['delete_post']) && count($_POST['delete_post'])) {

			// manually remove 'X' amount of post from a thread
			$_POST['delete_post'] = sanitize_array($_POST['delete_post']); // verify it.
			$del_posts = "";
			foreach ($_POST['delete_post'] as $del_post_id) {
				if (isnum($del_post_id)) {
					$del_posts .= ($del_posts ? "," : "").$del_post_id;
				}
			}
			if (!empty($del_posts)) {
				$result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_POSTS." WHERE post_id IN (".$del_posts.") GROUP BY post_author");
				if (dbrows($result)) {
					while ($pdata = dbarray($result)) {
						dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-".$pdata['num_posts']." WHERE user_id='".$pdata['post_author']."'");
					}
				}
				$result = dbquery("SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id IN (".$del_posts.")");
				if (dbrows($result)) {
					while ($adata = dbarray($result)) {
						unlink(FORUM."attachments/".$adata['attach_name']);
					}
				}
				dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".$_GET['thread_id']."' AND post_id IN(".$del_posts.")");
				$attachment_count = mysql_affected_rows();
				dbquery("DELETE FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' AND post_id IN(".$del_posts.")");
				$post_count = mysql_affected_rows();
			}

			// check remaining post count
			if (!dbcount("(post_id)", DB_POSTS, "thread_id='".$_GET['thread_id']."'")) {
				self::remove_thread($_GET['thread_id']);
				$thread_count = FALSE;
			} else {
				$pdata = dbarray(dbquery("SELECT post_datestamp, post_author, post_id FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY post_datestamp DESC LIMIT 1"));
				dbquery("UPDATE ".DB_THREADS." SET thread_lastpost='".$pdata['post_datestamp']."', thread_lastpostid='".$pdata['post_id']."', thread_postcount=thread_postcount-1, thread_lastuser='".$pdata['post_author']."' WHERE thread_id='".$_GET['thread_id']."'");
				$thread_count = TRUE;
			}
			self::refresh_forum($this->forum_id);
			if (!$thread_count) redirect(FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id);
		}
	}


	protected function mod_move_posts() {
		global $locale;
		// @todo: ffs...OPTIMIZE THIS......
		if (isset($_POST['move_posts']) && isset($_POST['delete_post']) && is_array($_POST['delete_post']) && count($_POST['delete_post'])) {
			$move_posts = "";
			$array_post = array();
			$f_post = FALSE;
			$dell_f_post = FALSE;
			$f_post_blo = FALSE;
			$first_post = dbarray(dbquery("SELECT post_id FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' ORDER BY post_datestamp ASC LIMIT 1"));
			// negotiate a first post
			foreach ($_POST['delete_post'] as $move_post_id) {
				if (isnum($move_post_id)) {
					$move_posts .= ($move_posts ? "," : "").$move_post_id;
					$array_post[] = $move_post_id;
					if ($move_post_id == $first_post['post_id']) {
						$f_post = TRUE;
					}
				}
			}
			// triggered move post
			if ($move_posts) {
				// validate whether post exists
				$move_result = dbquery("SELECT forum_id, thread_id, COUNT(post_id) as num_posts
				FROM ".DB_POSTS."
				WHERE post_id IN (".$move_posts.") AND forum_id='".$this->forum_id."'
				AND thread_id='".$_GET['thread_id']."'
				GROUP BY thread_id");
				if (dbrows($move_result)) {
					$pdata = dbarray($move_result);
					$num_posts = $pdata['num_posts'];
					echo openmodal('forum0300', $locale['forum_0300'], array('class'=>'modal-md top-30p', 'static'=>1));
					if ($f_post) { // there is a first post.
						echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>";
						if ($num_posts != dbcount("(post_id)", DB_POSTS, "thread_id='".$pdata['thread_id']."'")) {
							$dell_f_post = TRUE;
							echo $locale['forum_0305']."<br />\n"; // trying to remove first post with other post in the thread
						} else {
							echo $locale['forum_0306']."<br />\n"; // confirm ok to remove first post.
						}
						if ($dell_f_post && count($array_post) == 1) {
							echo "<br /><strong>".$locale['forum_0307']."</strong><br /><br />\n"; // no post to move.
							echo "<a href='".FORUM."viewthread.php?thread_id=".$pdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>".$locale['forum_0309']."</a>";
							$f_post_blo = TRUE;
						}
						echo "</div></div>\n";
					}
					if (!isset($_POST['new_forum_id']) && !$f_post_blo) {
						$fl_result = dbquery("
							SELECT f.forum_id, f.forum_name, f2.forum_name AS forum_cat_name,
							(SELECT COUNT(thread_id)
							FROM ".DB_THREADS." th
							WHERE f.forum_id=th.forum_id AND th.thread_id!='".$pdata['thread_id']."'
							GROUP BY th.forum_id) AS threadcount
							FROM ".DB_FORUMS." f
							INNER JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
							WHERE ".groupaccess('f.forum_access')." AND f.forum_type !='1'
							HAVING threadcount != 0
							ORDER BY f2.forum_order ASC, f.forum_order ASC
						");
						if (dbrows($fl_result) > 0) {

							// To exclude all type 1 forum.
							$find_category = dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_type='1'");
							$category_excluded = array();
							if (dbrows($find_category)>0) {
								while ($cf_data = dbarray($find_category)) {
									$category_excluded[] = $cf_data['forum_id'];
								}
							}

							echo openform('modopts', 'modopts', 'post', FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart'], array('downtime' => 1));
							echo form_select_tree($locale['forum_0301'], 'new_forum_id', 'new_forum_id', '', array('disable_opts'=>$category_excluded, 'no_root'=>1, 'inline'=>1), DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');
							foreach ($array_post as $value) {
								echo form_hidden('', "delete_post[]", "delete_post[$value]", $value);
							}
							echo form_hidden('', 'move_posts', 'move_posts', 1);
							echo "<div class='clearfix'>\n<div class='col-xs-12 col-md-offset-3 col-lg-offset-3'>\n";
							echo form_button($locale['forum_0302'], 'go', 'go', $locale['forum_0302'], array('class'=>'btn-primary'));
							echo "</div>\n</div>\n";
							echo closeform();
						} else {
							echo "<div class='well'>\n";
							echo "<strong>".$locale['forum_0310']."</strong><br /><br />\n";
							echo "<a href='".FORUM."viewthread.php?thread_id=".$pdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>".$locale['forum_0309']."</a><br /><br />\n";
							echo "</div>\n";
						}
					}
					// Select Threads in Selected Forum.
					elseif (isset($_POST['new_forum_id']) && isnum($_POST['new_forum_id']) && !isset($_POST['new_thread_id']) && !$f_post_blo) {
						// build the list.
						$tl_result = dbquery("
							SELECT thread_id, thread_subject
							FROM ".DB_THREADS."
							WHERE forum_id='".$_POST['new_forum_id']."' AND thread_id!='".$info['thread_id']."' AND thread_hidden='0'
							ORDER BY thread_subject ASC
						");

						if (dbrows($tl_result) > 0) {
							$forum_list = array();
							while ($tl_data = dbarray($tl_result)) {
								$forum_list[$tl_data['thread_id']] = $tl_data['thread_subject'];
							}
							echo openform('modopts', 'modopts', 'post', FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;sv", array('downtime' => 1));
							echo form_hidden('', 'new_forum_id', 'new_forum_id', $_POST['new_forum_id']);
							echo form_select($locale['forum_0303'], 'new_thread_id', 'new_thread_id', $forum_list, '', array('inline'=>1));
							foreach ($array_post as $value) {
								echo form_hidden('', "delete_post[]", "delete_post[$value]", $value);
							}
							echo form_hidden('', 'move_posts', 'move_posts', 1);
							echo "<div class='clearfix'>\n<div class='col-xs-12 col-md-offset-3 col-lg-offset-3'>\n";
							echo form_button($locale['forum_0304'], 'go', 'go', $locale['forum_0302'], array('class'=>'btn-primary btn-sm'));
							echo "</div>\n</div>\n";
						} else {
							echo "<div id='close-message'><div class='admin-message'>".$locale['forum_0308']."<br /><br />\n";
							echo "<a href='".FORUM."viewthread.php?thread_id=".$pdata['thread_id']."'>".$locale['forum_0309']."</a>\n";
							echo "</div></div><br />\n";
						}
					}

					elseif (isset($_GET['sv']) && isset($_POST['new_forum_id']) && isnum($_POST['new_forum_id']) && isset($_POST['new_thread_id']) && isnum($_POST['new_thread_id'])) {
						// Execute move and redirect after
						$move_posts_add = "";
						if (!dbcount("(thread_id)", DB_THREADS, "thread_id='".$_POST['new_thread_id']."' AND forum_id='".$_POST['new_forum_id']."'")) {
							redirect(FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=1");
						}
						foreach ($array_post as $move_post_id) {
							if (isnum($move_post_id)) {
								if ($f_post && $dell_f_post) {
									if ($move_post_id != $first_post['post_id']) {
										$move_posts_add .= ($move_posts_add ? "," : "").$move_post_id;
									}
									$num_posts = ($num_posts-1);
								} else {
									$move_posts_add = $move_post_id.($move_posts_add ? "," : "").$move_posts_add;
								}
							}
						}
						if ($move_posts_add) {
							$posts_ex = dbcount("(post_id)", DB_POSTS, "thread_id='".$pdata['thread_id']."' AND post_id IN (".$move_posts_add.")");
							if ($posts_ex) {
								$result = dbquery("UPDATE ".DB_POSTS." SET forum_id='".$_POST['new_forum_id']."', thread_id='".$_POST['new_thread_id']."' WHERE post_id IN (".$move_posts_add.")");
								$result = dbquery("UPDATE ".DB_FORUM_ATTACHMENTS." SET thread_id='".$_POST['new_thread_id']."' WHERE post_id IN(".$move_posts_add.")");
								$new_thread = dbarray(dbquery("
									SELECT forum_id, thread_id, post_id, post_author, post_datestamp
									FROM ".DB_POSTS."
									WHERE thread_id='".$_POST['new_thread_id']."'
									ORDER BY post_datestamp DESC
									LIMIT 1
								"));
								$result = dbquery("UPDATE ".DB_THREADS." SET thread_lastpost='".$new_thread['post_datestamp']."', thread_lastpostid='".$new_thread['post_id']."', thread_postcount=thread_postcount+".$num_posts.", thread_lastuser='".$new_thread['post_author']."' WHERE thread_id='".$_POST['new_thread_id']."'");
								$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$new_thread['post_datestamp']."', forum_postcount=forum_postcount+".$num_posts.", forum_lastuser='".$new_thread['post_author']."' WHERE forum_id='".$_POST['new_forum_id']."'");
								$old_thread = dbarray(dbquery("
									SELECT forum_id, thread_id, post_id, post_author, post_datestamp
									FROM ".DB_POSTS."
									WHERE thread_id='".$pdata['thread_id']."'
									ORDER BY post_datestamp DESC
									LIMIT 1
								"));
								if (!dbcount("(post_id)", DB_POSTS, "thread_id='".$pdata['thread_id']."'")) {
									$new_last_post = dbarray(dbquery("SELECT post_author, post_datestamp FROM ".DB_POSTS." WHERE forum_id='".$pdata['forum_id']."' ORDER BY post_datestamp DESC LIMIT 1 "));
									$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$new_last_post['post_datestamp']."', forum_postcount=forum_postcount-".$num_posts.", forum_threadcount=forum_threadcount-1, forum_lastuser='".$new_last_post['post_author']."' WHERE forum_id='".$pdata['forum_id']."'");
									$result = dbquery("DELETE FROM ".DB_THREADS." WHERE thread_id='".$pdata['thread_id']."'");
									$result = dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE thread_id='".$pdata['thread_id']."'");
									$result = dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".$pdata['thread_id']."'");
									$result = dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$pdata['thread_id']."'");
									$result = dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".$pdata['thread_id']."'");
								} else {
									$result = dbquery("UPDATE ".DB_THREADS." SET thread_lastpost='".$old_thread['post_datestamp']."', thread_lastpostid='".$old_thread['post_id']."', thread_postcount=thread_postcount-".$num_posts.", thread_lastuser='".$old_thread['post_author']."' WHERE thread_id='".$pdata['thread_id']."'");
									$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$old_thread['post_datestamp']."', forum_postcount=forum_postcount-".$num_posts.", forum_lastuser='".$old_thread['post_author']."' WHERE forum_id='".$pdata['forum_id']."'");
								}
								$pid = count($array_post)-1;
								redirect(FORUM."viewthread.php?thread_id=".$_POST['new_thread_id']."&amp;pid=".$array_post[$pid]."#post_".$array_post[$pid]);
							} else {
								redirect(FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=2"); // or here.
							}
						} else {
							redirect(FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=3");
						}
					}
					else {
						echo closemodal();
					}
				} else {
					redirect(FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=2"); // always go here.
				}
			} else {
				redirect(FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart']."&amp;error=3");
			}
			echo closemodal();
		}
	}
}