<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: mods.php
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

/**
 * Class Moderator
 * Forum Moderation Controller
 *
 * @package PHPFusion\Forums
 */
class Moderator {

    private static $instance = NULL;
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

    public static function __getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new Static();
        }

        return self::$instance;
    }


    /**
     * Verify a single thread ID is a genuine and valid thread
     * - does not check forum access
     *
     * @param $thread_id
     *
     * @return bool
     */
    public static function verify_thread($thread_id) {
        if (isnum($thread_id)) {
            return dbcount("('thread_id')", DB_FORUM_THREADS, "thread_id = '".intval($thread_id)."'");
        }

        return FALSE;
    }

    /**
     * Verify a single forum ID is a genuine and valid
     * - does not check forum access
     *
     * @param $forum_id
     *
     * @return bool
     */
    public static function verify_forum($forum_id) {
        if (isnum($forum_id)) {
            return dbcount("('forum_id')", DB_FORUMS, "forum_id = '".intval($forum_id)."'");
        }

        return FALSE;
    }

    /**
     * Generate iMOD const
     * @param $info
     */
    public static function define_forum_mods($info) {
        $imod = FALSE;
        if (!defined("iMOD")) {
            if (iMEMBER && $info['forum_mods']) {
                $mod_groups = explode(".", $info['forum_mods']);
                foreach ($mod_groups as $mod_group) {
                    if (checkgroup($mod_group)) {
                        $imod = TRUE;
                    }
                }
            }
            if (iADMIN && checkrights("FO")) {
                $imod = TRUE;
            }
            if (iSUPERADMIN) {
                $imod = TRUE;
            }
            define("iMOD", $imod);
        }
    }

    /**
     * Parse Forum Group Moderators Links
     * @param $forum_mods
     * @return string
     */
    public static function parse_forum_mods($forum_mods) {
        $moderators = '';
        if ($forum_mods) {
            $_mgroup = explode('.', $forum_mods);
            if (!empty($_mgroup)) {
                foreach ($_mgroup as $mod_group) {
                    if ($moderators) {
                        $moderators .= ", ";
                    }
                    $moderators .= $mod_group < -USER_LEVEL_MEMBER ? "<a href='".BASEDIR."profile.php?group_id=".$mod_group."'>".getgroupname($mod_group)."</a>" : getgroupname($mod_group);
                }
            }
        }

        return (string)$moderators;
    }

    /**
     * Set a post id
     * @param $value
     */
    public function setPostId($value) {
        $this->post_id = $value;
    }

    /**
     * Set a thread id
     * @param $value
     */
    public function setThreadId($value) {
        $this->thread_id = $value;
    }

    /**
     * Set a forum id
     *
     * @param $value
     */
    public function setForumID($value) {
        $this->forum_id = $value;
    }

    public function set_modActions() {

        $locale = fusion_get_locale();

        $this->form_action = FORUM.'viewthread.php?thread_id='.$this->thread_id.(isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? "&amp;rowstart=".$_GET['rowstart'] : '');

        if (!isset($_GET['rowstart'])) {
            $_GET['rowstart'] = 0;
        }

        if (isset($_POST['step']) && $_POST['step'] != "") {
            $_GET['step'] = $_POST['step'];
        }

        $_GET['step'] = isset($_GET['step']) && in_array($_GET['step'], $this->allowed_actions) ? $_GET['step'] : '';

        $_GET['error'] = isset($_GET['error']) ? $_GET['error'] : '';

        if ($this->thread_id && !$this->forum_id) {
            $forum_id_data = dbarray(dbquery("SELECT forum_id FROM ".DB_FORUM_THREADS." WHERE thread_id='".$this->thread_id."'"));
            $this->forum_id = $forum_id_data['forum_id'];
        }

        // get forum parents
        $branch_data = dbarray(dbquery("SELECT forum_cat, forum_branch FROM ".DB_FORUMS." WHERE forum_id='".$this->forum_id."'"));
        $this->parent_id = $branch_data['forum_cat'];
        $this->branch_id = $branch_data['forum_branch'];
        // at any time when cancel is clicked, redirect to forum id.
        if (isset($_POST['cancelDelete'])) {
            redirect(FORUM."viewthread.php?thread_id=".intval($this->thread_id));
        }
        /**
         * Thread actions
         */
        switch ($_GET['step']) {
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

        switch ($_GET['error']) {
            case '1':
                $message = $locale['error-MP001'];
                break;
            case '2':
                $message = $locale['error-MP002'];
                break;
            case '3':
                $message = $locale['forum_0307'];
                break;
        }

        if ($message != "") {
            opentable($locale['error-MP000']);
            echo "<div id='close-message'><div class='admin-message'>".$message."<br /><br />\n";
            echo "<a href='".$this->form_action."'>".$locale['forum_0309']."</a><br />";
            echo "</div></div>\n";
            closetable();
        }

        // Delete Posts
        self::mod_delete_posts();

        // Move Posts
        self::mod_move_posts();
    }

    /**
     * Moderator Action - Renew Thread Action
     * Modal pop up confirmation of thread being `renewed`
     */
    private function mod_renew_thread() {

        $locale = fusion_get_locale();

        if (iMOD) {
            $result = dbquery("SELECT p.post_id, p.post_author, p.post_datestamp, f.forum_id, f.forum_cat
					FROM ".DB_FORUM_POSTS." p
					INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
					INNER JOIN ".DB_FORUMS." f on f.forum_id = t.forum_id
					WHERE p.thread_id='".intval($this->thread_id)."' AND t.thread_hidden=0 AND p.post_hidden=0
					ORDER BY p.post_id DESC LIMIT 1
					");

            if (dbrows($result)) {
                $data = dbarray($result);

                // update the last post timestamp
                dbquery("UPDATE ".DB_FORUM_POSTS." SET post_datestamp=:time WHERE post_id=:post_id", [':time' => TIME, ':post_id' => $data['post_id']]);

                // update the thread last post timestamp
                dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:time, thread_lastpostid=:post_id, thread_lastuser=post_author WHERE thread_id=:thread_id",
                    [
                        ':time'        => TIME,
                        ':post_id'     => $data['post_id'],
                        ':post_author' => $data['post_author'],
                        ':thread_id'   => intval($this->thread_id)
                    ]);

                // update forum lastpost timestamp
                dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost=:time, forum_lastuser=:post_author WHERE forum_id=:forum_id",
                    [
                        ':time'        => TIME,
                        ':post_author' => $data['post_author'],
                        ':forum_id'    => $data['forum_id']
                    ]);

                ob_start();
                echo openmodal('renew', $locale['forum_0207'], array('class' => 'modal-center', 'static' => 1));
                echo "<div style='text-align:center'><br />\n".$locale['forum_0759']."<br /><br />\n";
                echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."'>".$locale['forum_0549']."</a><br /><br />\n";
                echo "<a href='".FORUM."index.php'>".$locale['forum_0550']."</a><br /><br /></div>\n";
                echo closemodal();

                add_to_footer(ob_get_clean());

            } else {
                redirect(FORUM.'index.php');
            }
        }
    }

    /**
     * Moderator Action - Delete Thread
     * Modal pop up confirmation of thread being `removed`
     */
    private function mod_delete_thread() {
        $locale = fusion_get_locale();

        if (iMOD) {
            ob_start();
            echo openmodal('deletethread', $locale['forum_0201'], array('class' => 'modal-center'));
            echo "<div class='text-center'><br />\n";
            if (!isset($_POST['deletethread'])) {
                echo openform('delform', 'post', $this->form_action."&amp;step=delete");
                echo $locale['forum_0704']."<br /><br />\n";
                echo form_button('deletethread', $locale['yes'], $locale['yes'], array('class' => 'm-r-10 btn-danger'));
                echo form_button('cancelDelete', $locale['no'], $locale['no'], array('class' => 'm-r-10 btn-default'));
                echo "</form>\n";
                echo closeform();
            } else {

                // reset every user post count as if they never posted before
                self::unset_userpost();

                // then we remove thread. outputs information what have been deleted
                $response = self::remove_thread();

                // refresh forum information as if thread never existed
                self::refresh_forum(TRUE);

                if ($response == TRUE) {
                    echo $locale['forum_0701']."<br /><br />\n";
                    echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."'>".$locale['forum_0549']."</a><br /><br />\n";
                    echo "<a href='index.php'>".$locale['forum_0550']."</a><br /><br />\n";
                } else {
                    echo $locale['forum_0705'];
                }
            }
            echo "</div>\n";
            echo closemodal();
            add_to_footer(ob_get_clean());
        }
    }

    /**
     * Unset User Post based on Thread id
     * This function assumes as if user have never posted before
     * @return int - number of posts that user have made in this thread
     */
    private function unset_userpost() {
        $post_count = 0;
        if (self::verify_thread($this->thread_id)) {
            $result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_id."' GROUP BY post_author");
            $rows = dbrows($result);
            if ($rows > 0) {
                while ($pdata = dbarray($result)) {
                    dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-".$pdata['num_posts']." WHERE user_id='".$pdata['post_author']."'");
                    $post_count = $pdata['num_posts'] + $post_count;
                }
            }
        }

        return (int)$post_count;
    }


    /**
     * SQL action remove thread
     * @return array of affected rows
     *               - post deleted
     *               - attachment deleted
     *               - user thread tracking deleted.
     */
    private function remove_thread() {

        $response = FALSE;

        if (self::verify_thread($this->thread_id) && self::verify_forum($this->forum_id)) {

            $param = [':thread_id' => intval($this->thread_id)];

            // Delete all thread posts
            dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE thread_id=:thread_id", $param);

            // Delete all thread notifications
            dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id=:thread_id", $param);

            // Delete all attachment files
            $result = dbquery("SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id=:thread_id", $param);
            if (dbrows($result)) {
                while ($attach = dbarray($result)) {
                    if (file_exists(INFUSIONS."forum/attachments/".$attach['attach_name'])) {
                        @unlink(INFUSIONS."forum/attachments/".$attach['attach_name']);
                    }
                }
            }

            // Delete all attachments
            dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id=:thread_id", $param);

            // Delete all poll voters
            dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id=:thread_id", $param);

            // Delete all Poll Options
            dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id=:thread_id", $param);

            // Delete Thread Poll
            dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id=:thread_id", $param);

            // Delete The Thread
            dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id=:thread_id", $param);

            $response = TRUE;
        }

        return (boolean)$response;
    }

    /**
     * Refresh db_forum forum's stats
     * @param bool $delete_thread true if thread deletion
     * @return int
     */
    private function refresh_forum($delete_thread = FALSE) {
        if (self::verify_forum($this->forum_id)) {
            $remaining_threads_count = dbcount("(forum_id)", DB_FORUM_THREADS, "forum_id='$this->forum_id'");
            // last post id from a given forum id.
            if ($remaining_threads_count) {
                $result = dbquery("SELECT p.forum_id, p.post_id, p.post_author, p.post_datestamp,
							COUNT(p.post_id) AS post_count FROM ".DB_FORUM_POSTS." p
							INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
							WHERE p.forum_id='".$this->forum_id."' AND t.thread_hidden='0' AND p.post_hidden='0'
							ORDER BY p.post_datestamp DESC LIMIT 1");
                if (dbrows($result) > 0) {
                    $pdata = dbarray($result); // yielded LAST post
                    $result = dbquery("UPDATE ".DB_FORUMS." SET
							forum_lastpostid = '".$pdata['post_id']."',
							forum_lastpost = '".$pdata['post_datestamp']."',
							forum_postcount = '".$pdata['post_count']."',
							".($delete_thread ? "forum_threadcount = '".(dbcount("(thread_id)", DB_FORUM_THREADS,
                                                                                 "forum_id='".$this->forum_id."'"))."'," : '')."
							forum_lastuser = '".$pdata['post_author']."'
							WHERE forum_id = '".$this->forum_id."'
							");
                }
            } else {
                dbquery("UPDATE ".DB_FORUMS." SET forum_lastpostid = '0', forum_lastpost='0', forum_postcount=0, forum_threadcount=0, forum_lastuser='0' WHERE forum_id='".intval($this->forum_id)."'");
            }
        }
    }

    /**
     * Moderator Action - Lock Thread
     * Modal pop up confirmation of thread being `locked`
     */
    private function mod_lock_thread() {
        global $locale;
        if (iMOD) {
            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_locked='1' WHERE thread_id='".intval($this->thread_id)."' AND thread_hidden='0'");
            ob_start();
            echo openmodal('lockthread', $locale['forum_0202']);
            echo "<div style='text-align:center'><br />\n";
            echo "<strong>".$locale['forum_0711']."</strong><br /><br />\n";
            echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."'>".$locale['forum_0549']."</a><br /><br />\n";
            echo "<a href='".FORUM."index.php'>".$locale['forum_0550']."</a><br /><br />\n</div>\n";
            echo closemodal();
            add_to_footer(ob_get_contents());
            ob_end_clean();
        }
    }

    /**
     * Moderator Action - Unlock Thread
     * Modal pop up confirmation of thread being `unlocked`
     */
    protected function mod_unlock_thread() {
        global $locale;
        if (iMOD) {
            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_locked='0' WHERE thread_id='".intval($this->thread_id)."' AND thread_hidden='0'");
            ob_start();
            echo openmodal('lockthread', $locale['forum_0720'], array('class' => 'modal-center'));
            echo "<div style='text-align:center'><br />\n";
            echo "<strong>".$locale['forum_0721']."</strong><br /><br />\n";
            echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."'>".$locale['forum_0549']."</a><br /><br />\n";
            echo "<a href='".FORUM."index.php'>".$locale['forum_0550']."</a><br /><br />\n</div>\n";
            echo closemodal();
            add_to_footer(ob_get_contents());
            ob_end_clean();
        }
    }

    /**
     * Moderator Action - Sticky Thread
     * Modal pop up confirmation of thread being `sticky`
     */
    protected function mod_sticky_thread() {
        global $locale;
        if (iMOD) {
            $result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_sticky='1' WHERE thread_id='".intval($this->thread_id)."' AND thread_hidden='0'");
            ob_start();
            echo openmodal('lockthread', $locale['forum_0204'], array('class' => 'modal-center'));
            echo "<div style='text-align:center'><br />\n";
            echo "<strong>".$locale['forum_0731']."</strong><br /><br />\n";
            echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."'>".$locale['forum_0549']."</a><br /><br />\n";
            echo "<a href='".FORUM."index.php'>".$locale['forum_0550']."</a><br /><br />\n</div>\n";
            echo closemodal();
            add_to_footer(ob_get_contents());
            ob_end_clean();
        }
    }

    /**
     * Moderator Action - Non Sticky Thread
     * Modal pop up confirmation of thread being `un-sticky`
     */
    protected function mod_nonsticky_thread() {
        global $locale;
        if (iMOD) {
            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_sticky='0' WHERE thread_id='".intval($this->thread_id)."' AND thread_hidden='0'");
            ob_start();
            echo openmodal('lockthread', $locale['forum_0205'], array('class' => 'modal-center'));
            echo "<div style='text-align:center'><br />\n";
            echo "<strong>".$locale['forum_0741']."</strong><br /><br />\n";
            echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id."'>".$locale['forum_0549']."</a><br /><br />\n";
            echo "<a href='".FORUM."index.php'>".$locale['forum_0550']."</a><br /><br /></div>\n";
            echo closemodal();
            add_to_footer(ob_get_contents());
            ob_end_clean();
        }
    }

    /**
     * Moderator Action - Move Thread
     */
    private function mod_move_thread() {
        global $locale;
        if (iMOD) {

            ob_start();
            echo openmodal('movethread', $locale['forum_0206'], array('class' => 'modal-lg'));

            if (isset($_POST['move_thread'])) {

                $new_forum_id = filter_input(INPUT_POST, 'new_forum_id', FILTER_VALIDATE_INT);

                $forum_id = intval($this->forum_id);

                $thread_id = intval($this->thread_id);

                // new forum does not exist.

                if (!$new_forum_id || !self::verify_forum($new_forum_id)) {
                    redirect(INFUSIONS."forum/index.php");
                }

                // thread id is hidden, and thread does not exist.
                if (!dbcount("(thread_id)", DB_FORUM_THREADS, "thread_id=".$thread_id." AND thread_hidden='0'")) {
                    redirect(INFUSIONS."forum/index.php");
                }

                $currentThreadPostCount = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=".$thread_id); // total post in current thread

                $currentThreadArray = dbarray(
                    dbquery("SELECT thread_lastpost, thread_lastpostid, thread_lastuser
                        FROM ".DB_FORUM_THREADS." WHERE thread_id=".$thread_id."
						AND thread_hidden='0'")
                );

                if ($currentThreadPostCount == 0 || empty($currentThreadArray)) {
                    redirect(INFUSIONS."forum/index.php");
                }

                $newForumSql = "SELECT forum_lastpostid, forum_lastpost, forum_lastuser FROM ".DB_FORUMS." WHERE forum_id = '".$new_forum_id."' AND forum_type !='1'";

                $newForumResult = dbquery($newForumSql);

                if (dbrows($newForumResult) > 0) {

                    $newForumArray = dbarray($newForumResult);

                    if ($currentThreadArray['thread_lastpost'] > $newForumArray['forum_lastpost']) {
                        // As the current thread has a later datestamp than the target forum, copy current thread stats to the target forum.
                        dbquery("UPDATE ".DB_FORUMS." SET
                            forum_lastpost='".$currentThreadArray['thread_lastpost']."',
                            forum_lastpostid = '".$currentThreadArray['thread_lastpostid']."',
                            forum_postcount=forum_postcount+".$currentThreadPostCount.",
                            forum_threadcount=forum_threadcount+1,
                            forum_lastuser='".$currentThreadArray['thread_lastuser']."'
                            WHERE forum_id=".$new_forum_id
                        );

                    } else {
                        // update add the postcount with the total postcount of current thread, and up +1 threadcount on the target forum
                        dbquery("UPDATE ".DB_FORUMS." SET
                            forum_postcount=forum_postcount+".$currentThreadPostCount.",
                            forum_threadcount=forum_threadcount+1
                            WHERE forum_id=".$new_forum_id
                        );

                    }

                    dbquery("UPDATE ".DB_FORUM_THREADS." SET forum_id=".$new_forum_id." WHERE thread_id=".$thread_id);

                    dbquery("UPDATE ".DB_FORUM_POSTS." SET forum_id=".$new_forum_id." WHERE thread_id=".$thread_id);

                    $bestForumLastThread = dbarray(dbquery("select * from ".DB_FORUM_THREADS." where forum_id='".$forum_id."' order by thread_lastpost desc limit 1"));

                    dbquery("UPDATE ".DB_FORUMS." SET
                            forum_postcount=forum_postcount-".$currentThreadPostCount.",
                            forum_threadcount=forum_threadcount-1,
                            forum_lastpost='".$bestForumLastThread['thread_lastpost']."',
                            forum_lastpostid = '".$bestForumLastThread['thread_lastpostid']."',
                            forum_lastuser='".$bestForumLastThread['thread_lastuser']."'
                            WHERE forum_id=".$forum_id
                    );

                    addNotice('success', $locale['forum_0752']);

                }

                redirect(INFUSIONS."forum/viewthread.php?thread_id=".$this->thread_id);


            } else {

                echo openform('moveform', 'post',
                              INFUSIONS."forum/viewthread.php?forum_id=".$this->forum_id."&amp;thread_id=".$this->thread_id."&amp;step=move");

                // disable all forum that is type 1.
                $disabled_opts[] = $this->forum_id;
                $disabled_sql = "SELECT forum_id FROM ".DB_FORUMS." WHERE forum_type='1'";
                $forum_search = dbquery($disabled_sql);
                if (dbrows($forum_search) > 0) {
                    while ($disabled_data = dbarray($forum_search)) {
                        $disabled_opts[] = $disabled_data['forum_id'];
                    }
                }

                echo form_select_tree('new_forum_id', $locale['forum_0751'], '',
                                      array(
                                          'input_id' => "new_forum_id",
                                          'no_root' => TRUE,
                                          'inline' => TRUE,
                                          'disable_opts' => $disabled_opts
                                      ),
                                      DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat').

                    form_button('move_thread', $locale['forum_0206'], $locale['forum_0206'], array('class' => 'btn-primary')).

                    closeform();

            }
            echo closemodal();
            add_to_footer(ob_get_contents());
            ob_end_clean();
        }
    }

    /**
     * Moderator Action - Remove only selected post in a thread
     * Requires $_POST['delete_posts']
     * refer to - viewthread_options.php
     */
    private function mod_delete_posts() {

        $locale = fusion_get_locale();
        $del_posts = '';
        if (isset($_POST['delete_posts']) && iMOD) {

            $post_items = form_sanitizer($_POST['delete_item_post'], '', 'delete_item_post');

            $post_items = explode(',', $post_items);
            $post_items = array_filter($post_items);
            if (!empty($post_items)) { // the checkboxes
                $thread_count = FALSE;
                $i = 0;
                foreach ($post_items as $del_post_id) {
                    if (isnum($del_post_id)) {
                        $del_posts .= ($del_posts ? "," : "").$del_post_id;
                        $i++;
                    }
                }
                if (!empty($del_posts)) {
                    // Update User Posts
                    $calculate_post = "SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." WHERE post_id IN (".$del_posts.") GROUP BY post_author";
                    $find_attachments = "SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id IN (".$del_posts.")";
                    $delete_attachments = "DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".intval($this->thread_id)."' AND post_id IN(".$del_posts.")";
                    $delete_forum_posts = "DELETE FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($this->thread_id)."' AND post_id IN(".$del_posts.")";
                    $find_lastpost = "SELECT post_datestamp, post_author, post_id FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($this->thread_id)."' ORDER BY post_datestamp DESC LIMIT 1";
                    // also need to delete post_mood
                    $result = dbquery($calculate_post);
                    if (dbrows($result) > 0) {
                        while ($pdata = dbarray($result)) {
                            dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-".intval($pdata['num_posts'])." WHERE user_id='".intval($pdata['post_author'])."'");
                        }
                    }
                    // Delete attachments
                    $result = dbquery($find_attachments);
                    if (dbrows($result)) {
                        while ($adata = dbarray($result)) {
                            $file_path = INFUSIONS."forum/attachments/".$adata['attach_name'];
                            if (file_exists($file_path) && !is_dir($file_path)) {
                                @unlink($file_path);
                            }
                        }
                    }
                    dbquery($delete_attachments);
                    dbquery($delete_forum_posts);
                    if (!dbcount("(post_id)", DB_FORUM_POSTS, "thread_id='".intval($this->thread_id)."'")) {
                        dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id='".intval($this->thread_id)."'");
                    } else {
                        // Find last post
                        $pdata = dbarray(dbquery($find_lastpost));
                        dbquery("
                        UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".$pdata['post_datestamp']."',
                        thread_lastpostid='".$pdata['post_id']."',
                        thread_postcount = '".dbcount("(post_id)", DB_FORUM_POSTS, "thread_id='".$this->thread_id."'")."',
                        thread_lastuser='".$pdata['post_author']."'
                        WHERE thread_id='".intval($this->thread_id)."'
                        ");
                        $thread_count = TRUE;
                    }
                    $delete_thread = $thread_count ? FALSE : TRUE;
                    self::refresh_forum($this->forum_id, $delete_thread);
                    addNotice('success', $locale['success-DP001']);
                    if ($thread_count === FALSE) { // no remaining thread
                        addNotice('success', $locale['success-DP002']);
                        redirect(INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id);
                    }
                } else {
                    addNotice('danger', $locale['error-DP001']);
                    redirect($this->form_action);
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

        $locale = fusion_get_locale();

        if (isset($_POST['move_posts']) && iMOD) {

            $remove_first_post = FALSE;
            $f_post_blo = FALSE;

            $post_items = form_sanitizer($_POST['delete_item_post'], '', 'delete_item_post'); // The selected checkbox of post to move.
            $post_items = explode(',', $post_items);
            $post_items = array_filter($post_items);

            if (!empty($post_items)) {
                //define('STOP_REDIRECT', true);

                $first_post = dbarray(dbquery("SELECT post_id FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($this->thread_id)."' ORDER BY post_datestamp ASC LIMIT 1"));
                /**
                 * Scan for Posts
                 */
                $move_posts = "";
                $array_post = array();
                $first_post_found = FALSE;
                foreach ($post_items as $move_post_id) {
                    if (isnum($move_post_id)) {
                        $move_posts .= ($move_posts ? "," : "").$move_post_id;
                        $array_post[] = $move_post_id;
                        if ($move_post_id == $first_post['post_id']) {
                            $first_post_found = TRUE;
                        }
                    }
                }

                // found post items.
                if (!empty($move_posts)) {

                    // Current Status Before Move.
                    $move_result = dbquery("SELECT forum_id, thread_id, COUNT(post_id) 'num_posts'
									FROM ".DB_FORUM_POSTS."
									WHERE post_id IN (".$move_posts.")
									AND thread_id='".intval($this->thread_id)."'
									GROUP BY thread_id");

                    if (dbrows($move_result)) {

                        $pdata = dbarray($move_result);

                        $post_count = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id='".intval($pdata['thread_id'])."'");

                        ob_start();
                        echo openmodal('forum0300', $locale['forum_0176'], array('class' => 'modal-md'));
                        if ($first_post_found) {
                            // there is a first post.
                            echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>";
                            if ($pdata['num_posts'] != $post_count) {
                                $remove_first_post = TRUE;
                                echo str_replace(['[STRONG]', '[/STRONG]'], ['<strong>', '</strong>'], $locale['forum_0305'])."<br />\n"; // trying to remove first post with other post in the thread
                            } else {
                                echo str_replace(['[STRONG]', '[/STRONG]'], ['<strong>', '</strong>'], $locale['forum_0306'])."<br />\n"; // confirm ok to remove first post.
                            }
                            if ($remove_first_post && count($array_post) == 1) {
                                echo "<br /><strong>".$locale['forum_0307']."</strong><br /><br />\n"; // no post to move.
                                echo "<a href='".$this->form_action."'>".$locale['forum_0309']."</a>";
                                $f_post_blo = TRUE;
                            }
                            echo "</div></div>\n";
                        }
                        if (!isset($_POST['new_forum_id']) && !$f_post_blo) {

                            $fl_result = dbquery("
										SELECT f.forum_id, f.forum_name, f.forum_type, f2.forum_name 'forum_cat_name',
										(	SELECT COUNT(thread_id) FROM ".DB_FORUM_THREADS." th WHERE f.forum_id=th.forum_id AND th.thread_id !='".intval($this->thread_id)."'
											GROUP BY th.forum_id
										) AS threadcount
                                        FROM ".DB_FORUMS." f
                                        LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
										WHERE ".groupaccess('f.forum_access')."
										ORDER BY f2.forum_order ASC, f.forum_order ASC
										");

                            if (dbrows($fl_result)) {
                                $exclude_opts = array();
                                while ($data = dbarray($fl_result)) {
                                    if (empty($data['threadcount']) || $data['forum_type'] == '1') {
                                        $exclude_opts[] = $data['forum_id'];
                                    }
                                }
                                echo openform('modopts', 'post', $this->form_action);
                                echo form_select_tree('new_forum_id', $locale['forum_0301'], '', array(
                                    'disable_opts' => $exclude_opts,
                                    'no_root'      => 1,
                                    'inline'       => FALSE,
                                    'inner_width'  => '100%'
                                ), DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');

                                foreach ($array_post as $value) {
                                    echo form_hidden("delete_item_post[]", "", $value, array("input_id" => "delete_post[$value]"));
                                }
                                echo form_hidden('move_posts', '', 1);
                                echo modalfooter(
                                    form_button($locale['forum_0302'], $locale['forum_0208'], $locale['forum_0208'], array('class' => 'btn-primary'))
                                );
                                echo closeform();
                            } else {
                                echo "<strong>".$locale['forum_0310']."</strong><br /><br />\n";
                                echo "<a href='".$this->form_action."'>".$locale['forum_0309']."</a><br /><br />\n";
                            }
                        } elseif (isset($_POST['new_forum_id']) && isnum($_POST['new_forum_id']) && !isset($_POST['new_thread_id']) && !isset($_POST['new_thread_subject']) && !$f_post_blo) {
                            // Select Threads in Selected Forum.
                            // build the list.
                            $tl_result = dbquery("
							SELECT thread_id, thread_subject
							FROM ".DB_FORUM_THREADS."
							WHERE forum_id='".intval($_POST['new_forum_id'])."' AND thread_id !='".intval($pdata['thread_id'])."' AND thread_hidden='0'
							ORDER BY thread_subject ASC
							");

                            if (dbrows($tl_result)) {
                                $thread_list = array();
                                while ($tl_data = dbarray($tl_result)) {
                                    $thread_list[$tl_data['thread_id']] = $tl_data['thread_subject'];
                                }
                                echo openform('modopts', 'post', $this->form_action."&amp;sv");
                                echo form_hidden('new_forum_id', '', $_POST['new_forum_id']); // has val
                                if (!isset($_POST['new_thread_select'])) {
                                    echo form_checkbox('new_thread_select', '', '', array(
                                        'type'     => 'radio',
                                        'options'  => [
                                            0 => $locale['forum_0300'],
                                            1 => $locale['forum_0303'],
                                        ],
                                        'inline'   => TRUE,
                                        'required' => TRUE,
                                    ));
                                } else {
                                    $thread_type = stripinput($_POST['new_thread_select']);
                                    if (!empty($thread_type)) {
                                        echo form_select('new_thread_id', $locale['forum_0303'], '', array(
                                            'options'     => $thread_list,
                                            'inline'      => FALSE,
                                            'inner_width' => '100%',
                                        ));
                                        echo form_hidden('new_thread_select', '', 0);
                                    } else {
                                        echo form_text('new_thread_subject', $locale['forum_2000'], '', ['required' => TRUE, 'max_length' => 250, 'inline' => FALSE]);
                                        echo form_hidden('new_thread_select', '', 1);
                                    }
                                }
                                foreach ($array_post as $value) {
                                    echo form_hidden("delete_item_post[]", "", $value, array("input_id" => "delete_post[$value]"));
                                }
                                echo form_hidden('move_posts', '', 1);

                                echo modalfooter(
                                    form_button($locale['forum_0176'], $locale['forum_0208'], $locale['forum_0208'], array('class' => 'btn-primary'))
                                );
                            } else {
                                echo $locale['forum_0308']."<br /><br />\n";
                                echo "<a href='".$this->form_action."'>".$locale['forum_0309']."</a>\n";
                            }

                        } elseif (isset($_GET['sv']) && isset($_POST['new_forum_id']) && isnum($_POST['new_forum_id']) && isset($_POST['new_thread_id']) && isnum($_POST['new_thread_id']) || isset($_POST['new_thread_subject'])) {

                            $_POST['new_thread_id'] = isset($_POST['new_thread_id']) ? $_POST['new_thread_id'] : 0;

                            /**
                             * Execute move posts
                             */
                            $move_posts_add = '';
                            $param = [
                                ':new_thread_id' => intval($_POST['new_thread_id']),
                                ':new_forum_id'  => intval($_POST['new_forum_id'])
                            ];

                            // Redirect if there is no thread count
                            if (!dbcount("(thread_id)", DB_FORUM_THREADS, "thread_id=:new_thread_id AND forum_id=:new_forum_id", $param)) {
                                if (!empty($_POST['new_thread_id'])) {
                                    addNotice('danger', $locale['error-MP001']);
                                    redirect($this->form_action);
                                }
                            }

                            // Selects all current selected posts
                            foreach ($array_post as $move_post_id) {
                                if (isnum($move_post_id)) {
                                    if ($first_post_found && $remove_first_post) {
                                        if ($move_post_id != $first_post['post_id']) {
                                            $move_posts_add .= ($move_posts_add ? "," : "").$move_post_id;
                                        }
                                        $pdata['num_posts'] = $pdata['num_posts'] - 1;
                                    } else {
                                        $move_posts_add = $move_post_id.($move_posts_add ? "," : "").$move_posts_add;
                                    }
                                }
                            }

                            if (!empty($move_posts_add)) {
                                // Validate if all the post belongs to the thread?
                                if ($pdata['num_posts'] == count($array_post)) {

                                    // Create a new thread
                                    if (!empty($_POST['new_thread_subject'])) {
                                        $thread_subject = form_sanitizer($_POST['new_thread_subject']);

                                        $author_result = dbarray(dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE post_id IN ($move_posts_add) ORDER BY post_datestamp ASC LIMIT 1"));

                                        // Create a Thread
                                        $new_thread_data = [
                                            'thread_id'      => 0,
                                            'forum_id'       => intval($_POST['new_forum_id']),
                                            'thread_subject' => $thread_subject,
                                            'thread_author'  => $author_result['post_author'],
                                        ];
                                        $param[':new_thread_id'] = dbquery_insert(DB_FORUM_THREADS, $new_thread_data, 'save', ['keep_session' => TRUE]);
                                    }

                                    // Update all selected posts with new thread and forum ID
                                    dbquery("UPDATE ".DB_FORUM_POSTS." SET forum_id=:new_forum_id, thread_id=:new_thread_id, post_datestamp='".TIME."' WHERE post_id IN (".$move_posts_add.")", $param);
                                    // Update all thread attachments with new thread ID
                                    dbquery("UPDATE ".DB_FORUM_ATTACHMENTS." SET thread_id=:new_thread_id WHERE post_id IN(".$move_posts_add.")", [':new_thread_id' => $param[':new_thread_id']]);

                                    // Get the latest post
                                    $new_thread = dbarray(dbquery("
													SELECT post_id, post_author, post_datestamp
													FROM ".DB_FORUM_POSTS."
													WHERE thread_id=:new_thread_id
													ORDER BY post_datestamp DESC
													LIMIT 1
													", [':new_thread_id' => $param[':new_thread_id']]
                                    ));
                                    $param[':thread_lastpost'] = $new_thread['post_datestamp'];
                                    $param[':thread_lastpostid'] = $new_thread['post_id'];
                                    $param[':thread_lastuser'] = $new_thread['post_author'];

                                    // ReUpdate the target thread
                                    dbquery("
                                    UPDATE ".DB_FORUM_THREADS." SET 
                                    thread_lastpost=:thread_lastpost, 
                                    thread_lastpostid=:thread_lastpostid, 
                                    thread_postcount=thread_postcount+".intval($pdata['num_posts']).", 
                                    thread_lastuser=:thread_lastuser                                    
                                    WHERE thread_id=:new_thread_id", [
                                            ':thread_lastpost'   => $param[':thread_lastpost'],
                                            ':thread_lastpostid' => $param[':thread_lastpostid'],
                                            ':thread_lastuser'   => $param[':thread_lastuser'],
                                            ':new_thread_id'     => $param[':new_thread_id']
                                        ]
                                    );

                                    // Re update the target forum
                                    dbquery("UPDATE ".DB_FORUMS." SET 
                                    forum_lastpost=:thread_lastpost, 
                                    forum_postcount=forum_postcount+".intval($pdata['num_posts']).", 
                                    forum_lastuser=:thread_lastuser WHERE forum_id=:new_forum_id", [
                                        ':thread_lastpost' => $param[':thread_lastpost'],
                                        ':thread_lastuser' => $param[':thread_lastuser'],
                                        ':new_forum_id'    => $param[':new_forum_id']
                                    ]);

                                    // If Current Thread has no more post
                                    if (!dbcount("(post_id)", DB_FORUM_POSTS, "thread_id='".intval($pdata['thread_id'])."'")) {
                                        // Select

                                        $forum_lastpost_res = dbarray(dbquery("
                                        SELECT post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE forum_id='".intval($pdata['forum_id'])."' ORDER BY post_datestamp DESC 
                                        LIMIT 1
                                        "));

                                        dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".intval($forum_lastpost_res['post_datestamp'])."', 
                                        forum_postcount=forum_postcount-".intval($pdata['num_posts']).", 
                                        forum_threadcount='".(dbcount("(thread_id)", DB_FORUM_THREADS, "forum_id='".intval($pdata['forum_id'])."'") - 1)."', 
                                        forum_lastuser='".intval($forum_lastpost_res['post_author'])."' 
                                        WHERE forum_id='".intval($pdata['forum_id'])."'");

                                        dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id='".intval($pdata['thread_id'])."'");
                                        dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id='".intval($pdata['thread_id'])."'");
                                        dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".intval($pdata['thread_id'])."'");
                                        dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".intval($pdata['thread_id'])."'");
                                        dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".intval($pdata['thread_id'])."'");

                                    } else {

                                        $thread_lastpost_res = dbarray(dbquery("
                                        SELECT forum_id, thread_id, post_id, post_author, post_datestamp
                                        FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($pdata['thread_id'])."' ORDER BY post_datestamp DESC
                                        LIMIT 1
                                        "));

                                        dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".intval($thread_lastpost_res['post_datestamp'])."',
										thread_lastpostid='".intval($thread_lastpost_res['post_id'])."', thread_postcount=thread_postcount-".intval($pdata['num_posts']).", thread_lastuser='".intval($thread_lastpost_res['post_author'])."' WHERE thread_id='".intval($pdata['thread_id'])."'");

                                        dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".intval($thread_lastpost_res['post_datestamp'])."', forum_postcount=forum_postcount-".intval($pdata['num_posts']).", forum_lastuser='".intval($thread_lastpost_res['post_author'])."' WHERE forum_id='".intval($pdata['forum_id'])."'");
                                    }

                                    $pid = count($array_post) - 1;
                                    addNotice('success', 'Posts have been moved');
                                    redirect(FORUM."viewthread.php?thread_id=".$param[':new_thread_id']."&amp;pid=".$array_post[$pid]."#post_".$array_post[$pid]);

                                } else {

                                    addNotice('danger', $locale['error-MP002']);
                                    redirect($this->form_action);

                                }
                            } else {
                                addNotice('danger', $locale['forum_0307']);
                                redirect($this->form_action);
                            }
                        }
                        echo closemodal();
                        add_to_footer(ob_get_contents());
                        ob_end_clean();

                    } else {
                        addNotice('danger', $locale['error-MP002']);
                        redirect($this->form_action);
                    }
                } else {
                    addNotice('danger', $locale['forum_0307']); // No post to move
                    //redirect($this->form_action);
                }

            } else {
                addNotice('danger', $locale['forum_0307']); // No post to move
                //redirect($this->form_action);
            }
        }
    }


}
