<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Moderator.php
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

include LOCALE.LOCALESET."forum.php";

class Moderator {

	private $allowed_actions = array(
		'renew',
		'delete',
		'nonsticky',
		'sticky',
		'lock',
		'unlock',
		'move'
	);
	private $thread_id = 0;
	private $post_id = 0;
	private $forum_id = 0;
	private $parent_id = 0;
	private $branch_id = 0;
	private $form_action = '';

	/**
	 * @param int $this->forum_id
	 */
	public function setForumId($forum_id) {
		$this->forum_id = $forum_id;
	}

	/**
	 * @param int $post_id
	 */
	public function setPostId($post_id) {
		$this->post_id = $post_id;
	}

	/**
	 * @param int $this->thread_id
	 */
	public function setThreadId($thread_id) {
		$this->thread_id = $thread_id;
	}

	/**
	 * SQL action remove thread
	 * @param $this->thread_id
	 * @return array of affected rows
	 *               - post deleted
	 *               - attachment deleted
	 *               - user thread tracking deleted.
	 */
	private function remove_thread() {
		$data = array();
		if (self::verify_thread($this->thread_id) && self::verify_forum($this->forum_id)) {

			dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($this->thread_id)."'");
			$data['post_deleted'] = mysql_affected_rows();
			dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id='".intval($this->thread_id)."'");
			$data['track_deleted'] = mysql_affected_rows();
			$result = dbquery("SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".intval($this->thread_id)."'");
			if (dbrows($result) != 0) {
				while ($attach = dbarray($result)) {
					@unlink(FORUM."attachments/".$attach['attach_name']);
				}
			}
			dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".intval($this->thread_id)."'");
			$data['attachment_deleted'] = mysql_affected_rows();

			dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".intval($this->thread_id)."'");
			$data['votes_deleted'] = mysql_affected_rows();

			dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".intval($this->thread_id)."'");
			$data['poll_options_deleted'] = mysql_affected_rows();

			dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".intval($this->thread_id)."'");
			$data['polls_deleted'] = mysql_affected_rows();

			dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id='".intval($this->thread_id)."'");
			$data['thread_deleted'] = mysql_affected_rows();
		}
		return $data;
	}

	/**
	 * Unset User Post based on Thread id
	 * This function assumes as if user have never posted before
	 * @param $this->thread_id
	 * @return int - number of posts that user have made in this thread
	 */
	private function unset_userpost() {
		$post_count = 0;
		if (self::verify_thread($this->thread_id)) {
			$result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_id."' GROUP BY post_author");
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
	 * Refresh db_forum forum's stats
	 * @param      $this->forum_id
	 * @param bool $delete_thread true if thread deletion
	 * @return int
	 */
	private function refresh_forum($delete_thread = false) {
		if (self::verify_forum($this->forum_id)) {
			$remaining_threads_count = dbcount("(forum_id)", DB_FORUM_THREADS, "forum_id='$this->forum_id'");
			// last post id from a given forum id.
			if ($remaining_threads_count) {
				$result = dbquery("SELECT p.forum_id, p.post_id, p.post_author, p.post_datestamp,
							COUNT(p.post_id) AS post_count FROM ".DB_FORUM_POSTS." p
							INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
							WHERE p.forum_id='".$this->forum_id."' AND t.thread_hidden='0' AND p.post_hidden='0'
							ORDER BY p.post_datestamp DESC LIMIT 1");
				if (dbrows($result)>0) {
					$pdata = dbarray($result); // yielded LAST post
					$result = dbquery("UPDATE ".DB_FORUMS." SET
							forum_lastpostid = '".$pdata['post_id']."',
							forum_lastpost = '".$pdata['post_datestamp']."',
							forum_postcount = '".$pdata['post_count']."',
							".($delete_thread ? "forum_threadcount = forum_threadcount-1," : '')."
							forum_lastuser = '".$pdata['post_author']."'
							WHERE forum_id = '".$this->forum_id."'
							");
					if ($result) return mysql_affected_rows();
				}
			} else {
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpostid = '0', forum_lastpost='0', forum_postcount=0, forum_threadcount=0, forum_lastuser='0' WHERE forum_id='".intval($this->forum_id)."'");
				if ($result) return mysql_affected_rows();
			}
		}
	}

	public function set_modActions() {
		global $locale;
		if (!isset($_GET['rowstart'])) $_GET['rowstart'] = 0;
		if (isset($_POST['step']) && $_POST['step'] != "") { $_GET['step'] = $_POST['step']; }
		$_GET['step'] = isset($_GET['step']) && in_array($_GET['step'], $this->allowed_actions) ? $_GET['step'] : '';
		$_GET['error'] = isset($_GET['error']) ? $_GET['error'] : '';
		$this->form_action = FORUM."viewthread.php?forum_id=".$this->forum_id."&amp;thread_id=".$this->thread_id."&amp;rowstart=".$_GET['rowstart'];
		// get forum parents
		$branch_data = dbarray(dbquery("SELECT forum_cat, forum_branch FROM ".DB_FORUMS." WHERE forum_id='".$this->forum_id."'"));
		$this->parent_id = $branch_data['forum_cat'];
		$this->branch_id = $branch_data['forum_branch'];

		// at any time when cancel is clicked, redirect to forum id.
		if (isset($_POST['cancelDelete'])) redirect("viewthread.php?forum_id=".$this->forum_id."&amp;thread_id=".intval($this->thread_id));

		/**
		 * Thread actions
		 */
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
			echo "<a href='".FORUM."viewthread.php?thread_id=".intval($this->thread_id)."&amp;rowstart=".$_GET['rowstart']."'>".$locale['forum_0309']."</a><br />";
			echo "</div></div>\n";
			closetable();
		}

		// Delete Posts
		self::mod_delete_posts();
		// Move Posts
		self::mod_move_posts();
	}

	/* Authenticate thread exist */
	public static function verify_thread($thread_id) {
		if (isnum($thread_id)) {
			return dbcount("('thread_id')", DB_FORUM_THREADS, "thread_id = '".intval($thread_id)."'");
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
	private function mod_renew_thread() {
		global $locale;
		$result = dbquery("SELECT p.post_id, p.post_author, p.post_datestamp, f.forum_id, f.forum_cat
					FROM ".DB_FORUM_POSTS." p
					INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
					INNER JOIN ".DB_FORUMS." f on f.forum_id = t.forum_id
					WHERE p.thread_id='".intval($this->thread_id)."' AND t.thread_hidden='0' AND p.post_hidden='0'
					ORDER BY p.post_datestamp DESC LIMIT 1
					");
		if (dbrows($result)) {
			$data = dbarray($result);
			$result = dbquery("UPDATE ".DB_FORUM_POSTS." SET post_datestamp='".time()."' WHERE post_id='".$data['post_id']."'");
			$result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$data['post_id']."', thread_lastuser='".$data['post_author']."' WHERE thread_id='".intval($this->thread_id)."'");
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_lastuser='".$data['post_author']."' WHERE forum_id='".$this->forum_id."'");
			echo openmodal('renew', $locale['forum_0758'], array('class'=>'modal-center', 'static'=>1));
			echo "<div style='text-align:center'><br />\n".$locale['forum_0759']."<br /><br />\n";
			echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."&amp;branch_id=".$this->branch_id."'>".$locale['forum_0702']."</a><br /><br />\n";
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
	private function mod_delete_thread() {
		global $locale;
		echo openmodal('deletethread',$locale['forum_0700'], array('class'=>'modal-center'));
		echo "<div style='text-align:center'><br />\n";
		if (!isset($_POST['deletethread'])) {
			echo "<form name='delform' method='post' action='".FUSION_SELF."?step=delete&amp;forum_id=".intval($this->forum_id)."&amp;thread_id=".intval($this->thread_id)."'>\n";
			echo $locale['forum_0704']."<br /><br />\n";
			echo form_button('deletethread', $locale['yes'], $locale['yes'], array('class'=>'m-r-10 btn-danger'));
			echo form_button('cancelDelete', $locale['no'], $locale['no'], array('class'=>'m-r-10 btn-default'));
			echo "</form>\n";
			echo closeform();
		} else {
			// reset every user post count as if they never posted before
			self::unset_userpost();
			// then we remove thread. outputs information what have been deleted
			$data = self::remove_thread();
			// refresh forum information as if thread never existed
			self::refresh_forum(true);
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
				echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."&amp;branch_id=".$this->branch_id."'>".$locale['forum_0702']."</a><br /><br />\n";
				echo "<a href='index.php'>".$locale['forum_0703']."</a><br /><br />\n";
			} else {
				echo "Unable to remove thread because thread does not exist";
			}
		}
		echo "</div>\n";
		echo closemodal();
	}

	/**
	 * Moderator Action - Lock Thread
	 * Modal pop up confirmation of thread being `locked`
	 */
	private function mod_lock_thread() {
		global $locale;
		dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_locked='1' WHERE thread_id='".intval($this->thread_id)."' AND thread_hidden='0'");
		echo openmodal('lockthread',$locale['forum_0710'], array('class'=>'modal-center'));
		echo "<div style='text-align:center'><br />\n";
		echo "<strong>".$locale['forum_0711']."</strong><br /><br />\n";
		echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."&amp;branch_id=".$this->branch_id."'>".$locale['forum_0702']."</a><br /><br />\n";
		echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br />\n</div>\n";
		echo closemodal();
	}

	/**
	 * Moderator Action - Unlock Thread
	 * Modal pop up confirmation of thread being `unlocked`
	 */
	protected function mod_unlock_thread() {
		global $locale;
		dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_locked='0' WHERE thread_id='".intval($this->thread_id)."' AND thread_hidden='0'");
		echo openmodal('lockthread',$locale['forum_0720'], array('class'=>'modal-center'));
		echo "<div style='text-align:center'><br />\n";
		echo "<strong>".$locale['forum_0721']."</strong><br /><br />\n";
		echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."&amp;branch_id=".$this->branch_id."'>".$locale['forum_0702']."</a><br /><br />\n";
		echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br />\n</div>\n";
		echo closemodal();
	}


	/**
	 * Moderator Action - Non Sticky Thread
	 * Modal pop up confirmation of thread being `un-sticky`
	 */
	protected function mod_nonsticky_thread() {
		global $locale;
		dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_sticky='0' WHERE thread_id='".intval($this->thread_id)."' AND thread_hidden='0'");
		echo openmodal('lockthread',$locale['forum_0740'], array('class'=>'modal-center'));
		echo "<div style='text-align:center'><br />\n";
		echo "<strong>".$locale['forum_0741']."</strong><br /><br />\n";
		echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."&amp;branch_id=".$this->branch_id."'>".$locale['forum_0702']."</a><br /><br />\n";
		echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br /></div>\n";
		echo closemodal();
	}


	/**
	 * Moderator Action - Sticky Thread
	 * Modal pop up confirmation of thread being `sticky`
	 */
	protected function mod_sticky_thread() {
		global $locale;
		$result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_sticky='1' WHERE thread_id='".intval($this->thread_id)."' AND thread_hidden='0'");
		echo openmodal('lockthread',$locale['forum_0730'], array('class'=>'modal-center'));
		echo "<div style='text-align:center'><br />\n";
		echo "<strong>".$locale['forum_0731']."</strong><br /><br />\n";
		echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."&amp;branch_id=".$this->branch_id."'>".$locale['forum_0702']."</a><br /><br />\n";
		echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br />\n</div>\n";
		echo closemodal();
	}

	/**
	 * Moderator Action - Move Thread
	 */
	private function mod_move_thread() {
		global $locale;
		echo openmodal('movethread',$locale['forum_0750'], array('class'=>'modal-center'));
		if (isset($_POST['move_thread'])) {
			$new_forum_id = filter_input(INPUT_POST, 'new_forum_id', FILTER_VALIDATE_INT);
			$forum_id = intval($this->forum_id);
			$thread_id = intval($this->thread_id);
			if (!$new_forum_id || !self::verify_forum($new_forum_id)) {
				redirect("index.php");
			}
			if (!dbcount("(forum_id)", DB_FORUMS, "forum_id=".$new_forum_id)) {
				redirect("../index.php");
			}
			if (!dbcount("(thread_id)", DB_FORUM_THREADS, "thread_id=".$thread_id." AND thread_hidden='0'")) {
				redirect("../index.php");
			}
			$post_count = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=".$thread_id); // total post in this thread
			dbquery("UPDATE ".DB_FORUM_THREADS." SET forum_id=".$new_forum_id." WHERE thread_id=".$thread_id);
			dbquery("UPDATE ".DB_FORUM_POSTS." SET forum_id=".$new_forum_id." WHERE thread_id=".$thread_id);
			$ex_rsc = dbquery("SELECT thread_lastpost, thread_lastpostid, thread_lastuser FROM ".DB_FORUM_THREADS." WHERE forum_id=".$forum_id." AND thread_hidden='0' ORDER BY thread_lastpost DESC LIMIT 1");
			// update current forum.
			if (dbrows($ex_rsc)>0) {
				$old_data = dbarray($ex_rsc);
				dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$old_data['thread_lastpost']."', forum_postcount = forum_postcount-".$post_count.", forum_threadcount=forum_threadcount-1, forum_lastuser='".$old_data['thread_lastuser']."' WHERE forum_id=".$forum_id);
			} else {
				dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_lastpostid = '0', forum_postcount=forum_postcount-".$post_count.", forum_threadcount=forum_threadcount-1, forum_lastuser='0' WHERE forum_id=".$forum_id);
			}
			$new_rsc = dbquery("SELECT thread_lastpost, thread_lastpostid, thread_lastuser FROM ".DB_FORUM_THREADS." WHERE forum_id=".$new_forum_id." AND thread_hidden='0' ORDER BY thread_lastpost DESC LIMIT 1");
			if (dbrows($new_rsc)) {
				$new_data = dbarray($new_rsc);
				dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$new_data['thread_lastpost']."', forum_lastpostid = '".$new_data['thread_lastpostid']."', forum_postcount=forum_postcount+".$post_count.", forum_threadcount=forum_threadcount+1, forum_lastuser='".$new_data['thread_lastuser']."' WHERE forum_id=".$new_forum_id, 1);
			} else {
				dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_lastpostid ='0', forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+".$post_count.", forum_lastuser='0' WHERE forum_id=".$new_forum_id);
			}
			addNotice('success', $locale['forum_0752']);
			redirect(FORUM."viewthread.php?forum_id=".$_POST['new_forum_id']."&amp;thread_id=".$this->thread_id);
		} else {
			echo openform('moveform', 'post', FORUM."viewthread.php?forum_id=".$this->forum_id."&amp;thread_id=".$this->thread_id."&amp;step=move", array('downtime' => 1));
			echo form_select_tree('new_forum_id', $locale['forum_0751'], '', array('input_id'=>"newfrmid", 'no_root'=>1, 'inline'=>1, 'disable_opts' => $this->forum_id),  DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');
			echo form_button('move_thread', $locale['forum_0206'], $locale['forum_0206'], array('class'=>'btn-primary'));
			echo closeform();
		}
		echo closemodal();
	}

	/**
	 * Moderator Action - Remove only selected post in a thread
	 * Requires $_POST['delete_posts']
	 * refer to - viewthread_options.php
	 */
	private function mod_delete_posts() {
		global $locale;
		if (isset($_POST['delete_posts'])) {
			if (isset($_POST['delete_post']) && !empty($_POST['delete_post'])) {
				$del_posts = ""; $i = 0;
				$thread_count = false;
				foreach ($_POST['delete_post'] as $del_post_id) {
					if (isnum($del_post_id)) { $del_posts .= ($del_posts ? "," : "").$del_post_id; $i++; }
				}
				if (!empty($del_posts)) {
					$result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." WHERE post_id IN (".$del_posts.") GROUP BY post_author");
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
					dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".intval($this->thread_id)."' AND post_id IN(".$del_posts.")");
					$attachment_count = mysql_affected_rows();
					dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($this->thread_id)."' AND post_id IN(".$del_posts.")");
					$post_count = mysql_affected_rows();
				}
				if (!dbcount("(post_id)", DB_FORUM_POSTS, "thread_id='".intval($this->thread_id)."'")) {
					dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id='".intval($this->thread_id)."'");
				} else {
					$pdata = dbarray(dbquery("SELECT post_datestamp, post_author, post_id FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($this->thread_id)."' ORDER BY post_datestamp DESC LIMIT 1"));
					dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".$pdata['post_datestamp']."', thread_lastpostid='".$pdata['post_id']."', thread_postcount = thread_postcount-1, thread_lastuser='".$pdata['post_author']."' WHERE thread_id='".intval($this->thread_id)."'");
					$thread_count = TRUE;
				}
				$delete_thread = $thread_count ? false : true;
				self::refresh_forum($this->forum_id, $delete_thread);
				addNotice('success', $locale['success-DP001']);
				if (!$thread_count) { // no remaining thread
					addNotice('success', $locale['success-DP002']);
					redirect(FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."&amp;branch_id=".$this->branch_id);
				}
			} else {
				addNotice('danger', $locale['error-DP001']);
				redirect($this->form_action);
			}
		}
	}

	/**
	 * Moving Posts
	 */
	private function mod_move_posts() {
		global $locale;
		if (isset($_POST['move_posts'])) {
			$remove_first_post = FALSE;
			$f_post_blo = FALSE;
			if (isset($_POST['delete_post']) && !empty($_POST['delete_post'])) {
				$first_post = dbarray(dbquery("SELECT post_id FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($this->thread_id)."' ORDER BY post_datestamp ASC LIMIT 1"));
				/**
				 * Scan for Posts
				 */
				$move_posts = "";
				$array_post = array();
				$first_post_found = FALSE;
				foreach ($_POST['delete_post'] as $move_post_id) {
					if (isnum($move_post_id)) {
						$move_posts .= ($move_posts ? "," : "").$move_post_id;
						$array_post[] = $move_post_id;
						if ($move_post_id == $first_post['post_id']) {
							$first_post_found = TRUE;
						}
					}
				}
				// triggered move post
				if ($move_posts) {
					// validate whether the selected post exists
					$move_result = dbquery("SELECT forum_id, thread_id, COUNT(post_id) as num_posts
									FROM ".DB_FORUM_POSTS."
									WHERE post_id IN (".$move_posts.") AND forum_id='".intval($this->forum_id)."'
									AND thread_id='".intval($this->thread_id)."'
									GROUP BY thread_id");
					if (dbrows($move_result) > 0) {
						$pdata = dbarray($move_result);
						$post_count = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id='".intval($pdata['thread_id'])."'");
						echo openmodal('forum0300', $locale['forum_0300'], array('class' =>'modal-md'));

						if ($first_post_found) {
							// there is a first post.
							echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>";
							if ($pdata['num_posts'] != $post_count) {
								$remove_first_post = TRUE;
								echo $locale['forum_0305']."<br />\n"; // trying to remove first post with other post in the thread
							} else {
								echo $locale['forum_0306']."<br />\n"; // confirm ok to remove first post.
							}
							if ($remove_first_post && count($array_post) == 1) {
								echo "<br /><strong>".$locale['forum_0307']."</strong><br /><br />\n"; // no post to move.
								echo "<a href='".FORUM."viewthread.php?forum_id=".$this->forum_id."&amp;thread_id=".$pdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>".$locale['forum_0309']."</a>";
								$f_post_blo = TRUE;
							}
							echo "</div></div>\n";
						}

						if (!isset($_POST['new_forum_id']) && !$f_post_blo) {
							$fl_result = dbquery("
										SELECT f.forum_id, f.forum_name, f2.forum_name AS forum_cat_name,
										(	SELECT COUNT(thread_id) FROM ".DB_FORUM_THREADS." th WHERE f.forum_id=th.forum_id AND th.thread_id !='".$this->thread_id."'
											GROUP BY th.forum_id
										) AS threadcount
											FROM ".DB_FORUMS." f
											LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
										WHERE ".groupaccess('f.forum_access')." AND f.forum_type !='1'
										HAVING threadcount != 0
										ORDER BY f2.forum_order ASC, f.forum_order ASC
										");
							if (dbrows($fl_result) > 0) {
								// To exclude all type 1 forum.
								$find_category = dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_type = '1'");
								$category_excluded = array();
								if (dbrows($find_category) > 0) {
									while ($cf_data = dbarray($find_category)) {
										$category_excluded[] = $cf_data['forum_id'];
									}
								}
								echo openform('modopts', 'modopts', 'post', $this->form_action, array('downtime' => 1));
								echo form_select_tree('new_forum_id', $locale['forum_0301'], '', array('disable_opts' => $category_excluded,
									'no_root' => 1,
									'inline' => 1), DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');
								foreach ($array_post as $value) {
									echo form_hidden('', "delete_post[]", "delete_post[$value]", $value);
								}
								echo form_hidden('', 'move_posts', 'move_posts', 1);
								echo "<div class='clearfix'>\n<div class='col-xs-12 col-md-offset-3 col-lg-offset-3'>\n";
								echo form_button($locale['forum_0302'], $locale['forum_0208'] , $locale['forum_0208'] , array('inline'=>1, 'class'=>'btn-primary'));
								echo "</div>\n</div>\n";
								echo closeform();
							} else {
								echo "<div class='well'>\n";
								echo "<strong>".$locale['forum_0310']."</strong><br /><br />\n";
								echo "<a href='".FORUM."viewthread.php?forum_id=".$this->forum_id."&amp;thread_id=".$pdata['thread_id']."&amp;rowstart=".$_GET['rowstart']."'>".$locale['forum_0309']."</a><br /><br />\n";
								echo "</div>\n";
							}
						}
						elseif (isset($_POST['new_forum_id']) && isnum($_POST['new_forum_id']) && !isset($_POST['new_thread_id']) && !$f_post_blo) {
							// Select Threads in Selected Forum.
							// build the list.
							$tl_result = dbquery("
							SELECT thread_id, thread_subject
							FROM ".DB_FORUM_THREADS."
							WHERE forum_id='".$_POST['new_forum_id']."' AND thread_id!='".$pdata['thread_id']."' AND thread_hidden='0'
							ORDER BY thread_subject ASC
							");
							if (dbrows($tl_result) > 0) {
								$forum_list = array();
								while ($tl_data = dbarray($tl_result)) {
									$forum_list[$tl_data['thread_id']] = $tl_data['thread_subject'];
								}
								echo openform('modopts', 'modopts', 'post', $this->form_action."&amp;sv", array('downtime' => 1));
								echo form_hidden('', 'new_forum_id', 'new_forum_id', $_POST['new_forum_id']);
								echo form_select('new_thread_id', $locale['forum_0303'], $forum_list, '', array('inline' => 1));
								foreach ($array_post as $value) {
									echo form_hidden('', "delete_post[]", "delete_post[$value]", $value);
								}
								echo form_hidden('', 'move_posts', 'move_posts', 1);
								echo form_button($locale['forum_0304'], $locale['forum_0208'] , $locale['forum_0208'] , array('class'=>'btn-primary btn-sm'));
								} else {
								echo "<div id='close-message'><div class='admin-message'>".$locale['forum_0308']."<br /><br />\n";
								echo "<a href='".FORUM."viewthread.php?forum_id=".$this->forum_id."&amp;thread_id=".$pdata['thread_id']."'>".$locale['forum_0309']."</a>\n";
								echo "</div></div><br />\n";
							}
						} elseif (isset($_GET['sv']) && isset($_POST['new_forum_id']) && isnum($_POST['new_forum_id']) && isset($_POST['new_thread_id']) && isnum($_POST['new_thread_id'])) {
							// Execute move and redirect after
							$move_posts_add = "";
							if (!dbcount("(thread_id)", DB_FORUM_THREADS, "thread_id='".$_POST['new_thread_id']."' AND forum_id='".$_POST['new_forum_id']."'")) {
								redirect($this->form_action."&amp;error=1");
							}
							foreach ($array_post as $move_post_id) {
								if (isnum($move_post_id)) {
									if ($first_post_found && $remove_first_post) {
										if ($move_post_id != $first_post['post_id']) {
											$move_posts_add .= ($move_posts_add ? "," : "").$move_post_id;
										}
										$pdata['num_posts'] = $pdata['num_posts']-1;
									} else {
										$move_posts_add = $move_post_id.($move_posts_add ? "," : "").$move_posts_add;
									}
								}
							}
							if ($move_posts_add) {
								$posts_ex = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id='".$pdata['thread_id']."' AND post_id IN (".$move_posts_add.")");
								if ($posts_ex) {
									$result = dbquery("UPDATE ".DB_FORUM_POSTS." SET forum_id='".$_POST['new_forum_id']."', thread_id='".$_POST['new_thread_id']."' WHERE post_id IN (".$move_posts_add.")");
									$result = dbquery("UPDATE ".DB_FORUM_ATTACHMENTS." SET thread_id='".$_POST['new_thread_id']."' WHERE post_id IN(".$move_posts_add.")");
									$new_thread = dbarray(dbquery("
													SELECT forum_id, thread_id, post_id, post_author, post_datestamp
													FROM ".DB_FORUM_POSTS."
													WHERE thread_id='".$_POST['new_thread_id']."'
													ORDER BY post_datestamp DESC
													LIMIT 1
													")
												);
									$result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".$new_thread['post_datestamp']."', thread_lastpostid='".$new_thread['post_id']."', thread_postcount=thread_postcount+".intval($pdata['num_posts']).", thread_lastuser='".$new_thread['post_author']."' WHERE thread_id='".$_POST['new_thread_id']."'");
									$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$new_thread['post_datestamp']."', forum_postcount=forum_postcount+".intval($pdata['num_posts']).", forum_lastuser='".$new_thread['post_author']."' WHERE forum_id='".$_POST['new_forum_id']."'");

									$old_thread = dbarray(dbquery("
									SELECT forum_id, thread_id, post_id, post_author, post_datestamp
									FROM ".DB_FORUM_POSTS."
									WHERE thread_id='".$pdata['thread_id']."'
									ORDER BY post_datestamp DESC
									LIMIT 1
									"));
									if (!dbcount("(post_id)", DB_FORUM_POSTS, "thread_id='".$pdata['thread_id']."'")) {
										$new_last_post = dbarray(dbquery("SELECT post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE forum_id='".$pdata['forum_id']."' ORDER BY post_datestamp DESC LIMIT 1 "));
										$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$new_last_post['post_datestamp']."', forum_postcount=forum_postcount-".intval($pdata['num_posts']).", forum_threadcount=forum_threadcount-1, forum_lastuser='".$new_last_post['post_author']."' WHERE forum_id='".$pdata['forum_id']."'");
										$result = dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id='".$pdata['thread_id']."'");
										$result = dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id='".$pdata['thread_id']."'");
										$result = dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".$pdata['thread_id']."'");
										$result = dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$pdata['thread_id']."'");
										$result = dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".$pdata['thread_id']."'");
									} else {
										$result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".$old_thread['post_datestamp']."', thread_lastpostid='".$old_thread['post_id']."', thread_postcount=thread_postcount-".intval($pdata['num_posts']).", thread_lastuser='".$old_thread['post_author']."' WHERE thread_id='".$pdata['thread_id']."'");
										$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$old_thread['post_datestamp']."', forum_postcount=forum_postcount-".intval($pdata['num_posts']).", forum_lastuser='".$old_thread['post_author']."' WHERE forum_id='".$pdata['forum_id']."'");
									}
									$pid = count($array_post)-1;
									redirect($this->form_action."&amp;pid=".$array_post[$pid]."#post_".$array_post[$pid]);
								} else {
									addNotice('danger', $locale['error-MP002']);
									redirect($this->form_action); // or here.
								}
							} else {
								addNotice('danger', $locale['error-MP003']);
								redirect($this->form_action);
							}
						} else {
							echo closemodal();
						}
					} else {
						addNotice('danger', $locale['error-MP002']);
						redirect($this->form_action);
					}
				} else {
					addNotice('danger', $locale['error-MP003']);
					redirect($this->form_action);
				}
				echo closemodal();
			} else {
				addNotice('danger', $locale['error-MP003']);
				redirect($this->form_action);
			}
		}
	}
}
?>