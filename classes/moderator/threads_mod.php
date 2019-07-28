<?php
namespace PHPFusion\Infusions\Forum\Classes\Moderator;

use Administration\Members\Users\Actions;
use PHPFusion\Infusions\Forum\Classes\Forum_Moderator;

class Threads_Mod {

    private $class = NULL;
    private $thread_id = 0;
    private $form_action = '';
    private $forum_parent_id = 0;
    private $forum_id = 0;
    private $registered_actions = [
        'renew',
        'delete',
        'nonsticky',
        'sticky',
        'lock',
        'unlock',
        'move',
        'ban_user',
        'delete_user'
    ];

    public function __construct(Forum_Moderator $obj) {
        $this->class = $obj;
        $this->thread_id = $obj->getThreadID();
        $this->forum_parent_id = $obj->getForumParentID();
        $this->forum_id = $obj->getForumID();
        $this->form_action = FORM_REQUEST;
        $step = $this->sanitizeStep();
        if (iMOD && $step) {
            switch ($step) {
                case 'renew':
                    $this->renewThread();
                    break;
                case 'delete':
                    $this->deleteThread();
                    break;
                case 'lock':
                    $this->lockThread();
                    break;
                case 'unlock':
                    $this->unlockThread();
                    break;
                case 'sticky':
                    $this->stickyThread();
                    break;
                case 'nonsticky':
                    $this->unstickyThread();
                    break;
                case 'move':
                    $this->moveThread();
                    break;
                case 'ban_user':
                    $this->banUser();
                    break;
                case 'delete_user':
                    $this->deleteUser();
                    break;
            }
        }
    }

    private function sanitizeStep() {
        $step_post = post('step');
        if (!$step_post) {
            $step_post = get('step');
        }
        return ($step_post && in_array($step_post, $this->registered_actions) ? $step_post : '');
    }

    private function deleteUser() {
        if (checkrights('M') && iMOD) {
            if (post('cancel')) {
                redirect(FORUM.'viewthread.php?thread_id='.$this->thread_id);
            }
            // need to confirm first. because this is very dangerous.
            $user_id = get('user_id', FILTER_VALIDATE_INT);
            if ($user_name = fusion_get_user($user_id, 'user_name')) {
                // requirement to confirm, post confirm
                if (post('delete_confirm')) {
                    dbquery("DELETE FROM ".DB_USERS."   WHERE user_id=:uid", [':uid'=>(int)$user_id]);
                    $user_data = fusion_get_user($user_id);
                    fusion_filter_current_hook('admin_user_delete', $user_data);
                    addNotice('success', $user_name.' has been deleted');
                } else {
                    $modal = openmodal('confirmDelete', 'User delete confirmation', ['static'=>TRUE]);
                    $modal .= "<div class='alert alert-danger'><strong><i class='fas fa-exclamation-triangle m-r-10'></i>You are about to delete user:".$user_name." on this site.</strong><br/>";
                    $modal .= "This action is irreversible and may not be restored. All posts and any information submitted by this user will be removed from this site.</div><hr/>";
                    $modal .= openform('cdelete', 'post').form_button('delete_confirm', 'Delete User', 'delete_confirm', ['class'=>'btn-danger']).form_button('cancel', 'Cancel', 'cancel').closeform();
                    $modal .= closemodal();
                    add_to_footer($modal);
                }
            }
        }else {
            addNotice('danger', 'You do not have permission to moderate users.');
        }
    }

    private function banUser() {
        if (checkrights('M') && iMOD) {
            $user_id = get('user_id', FILTER_VALIDATE_INT);
            if ($user_name = fusion_get_user($user_id, 'user_name')) {
                $user_class = new Actions();
                $user_class->set_userID([$user_id]);
                $user_class->set_action(1);
                $user_class->setCancelLink(FORUM.'viewthread.php?thread_id='.get('thread_id', FILTER_VALIDATE_INT));
                $user_class->execute();
            }
        } else {
            addNotice('danger', 'You do not have permission to moderate users.');
        }
    }

    /**
     * Moderator Action - Renew Thread Action
     * Modal pop up confirmation of thread being `renewed`
     *
     * Refactor completed, checked
     */
    private function renewThread() {
        $locale = fusion_get_locale();
        $thread_id = (int)$this->class->getThreadID();
        $forum_id = (int)$this->class->getForumID();
        $parent_id = (int)$this->class->getForumParentID();

        $result = dbquery("SELECT p.post_id, p.post_author, p.post_datestamp, f.forum_id, f.forum_cat
                    FROM ".DB_FORUM_POSTS." p
                    INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
                    INNER JOIN ".DB_FORUMS." f ON f.forum_id = t.forum_id
                    WHERE p.thread_id=:tid AND t.thread_hidden=0 AND p.post_hidden=0
                    ORDER BY p.post_id DESC LIMIT 1
                    ", [':tid' => $thread_id]);

        if (dbrows($result)) {
            $data = dbarray($result);

            // update the last post timestamp
            dbquery("UPDATE ".DB_FORUM_POSTS." SET post_datestamp=:time WHERE post_id=:pid", [':time' => TIME, ':pid' => $data['post_id']]);

            // update the thread last post timestamp
            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:time, thread_lastpostid=:post_id, thread_lastuser=:post_author WHERE thread_id=:thread_id",
                [
                    ':time'        => TIME,
                    ':post_id'     => $data['post_id'],
                    ':post_author' => $data['post_author'],
                    ':thread_id'   => $thread_id
                ]);

            // update forum lastpost timestamp
            dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost=:time, forum_lastuser=:puid WHERE forum_id=:fid",
                [
                    ':time' => TIME,
                    ':puid' => $data['post_author'],
                    ':fid'  => $data['forum_id']
                ]);


            $modal = openmodal('renew', $locale['forum_0207'], ['class_dialog' => 'modal-center', 'static' => 1]);
            $modal .= "<div style='text-align:center'><br />\n".$locale['forum_0759']."<br /><br />\n";
            $modal .= "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$forum_id."&amp;parent_id=".$parent_id."'>".$locale['forum_0549']."</a><br /><br />\n";
            $modal .= "<a href='".FORUM."index.php'>".$locale['forum_0550']."</a><br /><br /></div>\n";
            $modal .= closemodal();

            add_to_footer($modal);

        } else {
            redirect(FORUM.'index.php');
        }

    }

    /**
     * Moderator Action - Delete Thread
     * Modal pop up confirmation of thread being `removed`
     *
     * Refactor completed
     */
    private function deleteThread() {
        $locale = fusion_get_locale();
        $modal = openmodal('deletethread', $locale['forum_0201'], ['class_dialog' => 'modal-center']);
        $modal .= "<div class='text-center'><br />\n";

        if (post('deletethread')) {

            $this->reduceUserPost();

            $response = $this->removeThread();

            $forum_mods = new Forums_Mod($this->class);

            $forum_mods->refreshForums();

            if ($response == TRUE) {

                addNotice('success', $locale['forum_0701'], 'all');

                redirect(FORUM."index.php?viewforum&amp;forum_id=".$this->class->getForumID()."&amp;parent_id=".$this->class->getForumParentID());

            } else {
                $modal .= $locale['forum_0705'];
            }
        } else {

            // reset every user post count as if they never posted before
            $modal .= openform('delform', 'post', FORM_REQUEST."&amp;step=delete");
            $modal .= $locale['forum_0704']."<br /><br />\n";
            $modal .= form_button('deletethread', $locale['yes'], $locale['yes'], ['class' => 'm-r-10 btn-danger']);
            $modal .= form_button('cancelDelete', $locale['no'], $locale['no'], ['class' => 'm-r-10 btn-default']);
            $modal .= closeform();
        }
        $modal .= "</div>\n";
        $modal .= closemodal();
        add_to_footer($modal);

    }

    /**
     * Unset User Post based on Thread id
     * This function assumes as if user have never posted before
     *
     * @return int - number of posts that user have made in this thread
     */
    public function reduceUserPost() {
        $post_count = 0;
        $thread_id = (int)$this->class->getThreadID();

        if ($this->class->verifyThreadID($thread_id)) {

            $result = dbquery("SELECT post_author, COUNT(post_id) 'num_posts'  FROM ".DB_FORUM_POSTS."  WHERE thread_id=:tid    GROUP BY post_author", [':tid' => $thread_id]);
            if (dbrows($result)) {
                while ($pdata = dbarray($result)) {
                    $num_post = (int)$pdata['num_posts'];
                    dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-$num_post WHERE user_id=:uid", [':uid' => (int)$pdata['post_author']]);
                    $post_count = $pdata['num_posts'] + $post_count;
                }
            }
        }

        return (int)$post_count;
    }

    /**
     * Moderator Action - Lock Thread
     * Modal pop up confirmation of thread being `locked`
     *
     * Refactor completed, checked
     */
    private function lockThread() {
        $locale = fusion_get_locale();
        $thread_id = (int)$this->class->getThreadID();
        $forum_id = (int)$this->class->getForumID();
        $forum_parent_id = (int)$this->class->getForumParentID();
        dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_locked='1' WHERE thread_id=:tid AND thread_hidden=0", [':tid' => $thread_id]);

        $modal = openmodal('lockthread', $locale['forum_0202'], ['class_dialog' => 'modal-center']);
        $modal .= "<div style='text-align:center'><br />\n";
        $modal .= "<strong>".$locale['forum_0711']."</strong><br /><br />\n";
        $modal .= "<a href='".FORUM."index.php?viewforum&amp;forum_id=$forum_id&amp;parent_id=$forum_parent_id'>".$locale['forum_0549']."</a><br /><br />\n";
        $modal .= "<a href='".FORUM."index.php'>".$locale['forum_0550']."</a><br /><br />\n</div>\n";
        $modal .= closemodal();
        add_to_footer($modal);

    }

    /**
     * Moderator Action - Unlock Thread
     * Modal pop up confirmation of thread being `unlocked`
     *
     * Refactor completed, checked
     */
    private function unlockThread() {
        $locale = fusion_get_locale();
        $thread_id = (int)$this->class->getThreadID();
        $forum_id = (int)$this->class->getForumID();
        $parent_id = (int)$this->class->getForumParentID();

        dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_locked='0' WHERE thread_id=:tid AND thread_hidden=0", [':tid' => $thread_id]);

        $modal = openmodal('lockthread', $locale['forum_0720'], ['class_dialog' => 'modal-center']);
        $modal .= "<div style='text-align:center'><br />\n";
        $modal .= "<strong>".$locale['forum_0721']."</strong><br /><br />\n";
        $modal .= "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$forum_id."&amp;parent_id=".$parent_id."'>".$locale['forum_0549']."</a><br /><br />\n";
        $modal .= "<a href='".FORUM."index.php'>".$locale['forum_0550']."</a><br /><br />\n</div>\n";
        $modal .= closemodal();
        add_to_footer($modal);
    }

    /**
     * Moderator Action - Sticky Thread
     * Modal pop up confirmation of thread being `sticky`
     *
     * Refactor completed, checked
     */
    private function stickyThread() {
        $locale = fusion_get_locale();
        $thread_id = (int)$this->class->getThreadID();
        $forum_id = (int)$this->class->getForumID();
        $parent_id = (int)$this->class->getForumParentID();

        dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_sticky='1' WHERE thread_id=:tid AND thread_hidden='0'", [':tid' => $thread_id]);
        $modal = openmodal('lockthread', $locale['forum_0204'], ['class_dialog' => 'modal-center']);
        $modal .= "<div style='text-align:center'><br />\n";
        $modal .= "<strong>".$locale['forum_0731']."</strong><br /><br />\n";
        $modal .= "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$forum_id."&amp;parent_id=".$parent_id."'>".$locale['forum_0549']."</a><br /><br />\n";
        $modal .= "<a href='".FORUM."index.php'>".$locale['forum_0550']."</a><br /><br />\n</div>\n";
        $modal .= closemodal();
        add_to_footer($modal);
    }

    /**
     * Moderator Action - Non Sticky Thread
     * Modal pop up confirmation of thread being `un-sticky`
     *
     * Refactor completed, checked
     */
    private function unstickyThread() {
        $locale = fusion_get_locale();
        $thread_id = (int)$this->class->getThreadID();
        $forum_id = (int)$this->class->getForumID();
        $parent_id = (int)$this->class->getForumParentID();

        dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_sticky='0' WHERE thread_id=:tid AND thread_hidden='0'", [':tid' => $thread_id]);

        $modal = openmodal('lockthread', $locale['forum_0205'], ['class_dialog' => 'modal-center']);
        $modal .= "<div style='text-align:center'><br />\n";
        $modal .= "<strong>".$locale['forum_0741']."</strong><br /><br />\n";
        $modal .= "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$forum_id."&amp;parent_id=".$parent_id."'>".$locale['forum_0549']."</a><br /><br />\n";
        $modal .= "<a href='".FORUM."index.php'>".$locale['forum_0550']."</a><br /><br /></div>\n";
        $modal .= closemodal();
        add_to_footer($modal);

    }


    /**
     * Moderator Action - Move Thread
     *
     * Refactor completed, checked
     */
    private function moveThread() {
        $locale = fusion_get_locale();
        $forum_id = (int)$this->class->getForumID();
        $thread_id = (int)$this->class->getThreadID();

        if (post('move_thread')) {

            $new_forum_id = post('new_forum_id', FILTER_VALIDATE_INT);

            // validate new forum id to move the thread to and validate current thread id
            if ($new_forum_id && !$this->class->verifyForumID($new_forum_id) || !$this->class->verifyThreadID($thread_id)) {
                redirect(FORUM."index.php");
            }

            $cur_thread_posts = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=:tid", [':tid' => $thread_id]); // total post in current thread

            $cur_thread_arr = dbarray(dbquery("SELECT thread_lastpost, thread_lastpostid, thread_lastuser  FROM ".DB_FORUM_THREADS." WHERE thread_id=:tid AND thread_hidden='0'", [':tid' => $thread_id]));
            if ($cur_thread_posts == 0 || empty($cur_thread_arr)) {
                redirect(FORUM.'index.php');
            }

            list($forum_lastpostid, $forum_lastpost) = dbarraynum(dbquery("SELECT forum_lastpostid, forum_lastpost FROM ".DB_FORUMS." WHERE forum_id=:nfid AND forum_type !=1", [':nfid' => $new_forum_id]));

            if ($forum_lastpostid && $forum_lastpost) {

                if ($cur_thread_arr['thread_lastpost'] > $forum_lastpost) {
                    // As the current thread has a later datestamp than the target forum, copy current thread stats to the target forum.
                    dbquery("UPDATE ".DB_FORUMS." SET
                            forum_lastpost='".$cur_thread_arr['thread_lastpost']."',
                            forum_lastpostid = '".$cur_thread_arr['thread_lastpostid']."',
                            forum_postcount=forum_postcount+".$cur_thread_posts.",
                            forum_threadcount=forum_threadcount+1,
                            forum_lastuser='".$cur_thread_arr['thread_lastuser']."'
                            WHERE forum_id=".$new_forum_id
                    );

                } else {
                    // update add the postcount with the total postcount of current thread, and up +1 threadcount on the target forum
                    dbquery("UPDATE ".DB_FORUMS." SET
                            forum_postcount=forum_postcount+".$cur_thread_posts.",
                            forum_threadcount=forum_threadcount+1
                            WHERE forum_id=".$new_forum_id
                    );

                }

                // change current thread forum
                dbquery("UPDATE ".DB_FORUM_THREADS."    SET forum_id=:nfid WHERE thread_id=:tid", [':nfid' => $new_forum_id, ':tid' => $thread_id]);

                // change current thread post
                dbquery("UPDATE ".DB_FORUM_POSTS."  SET forum_id=:nfid WHERE thread_id=:tid", [':nfid' => $new_forum_id, ':tid' => $thread_id]);

                $bestForumLastThread = dbarray(dbquery("SELECT * FROM ".DB_FORUM_THREADS." WHERE forum_id='".$forum_id."' ORDER BY thread_lastpost DESC LIMIT 1"));
                dbquery("UPDATE ".DB_FORUMS." SET
                            forum_postcount=forum_postcount-".$cur_thread_posts.",
                            forum_threadcount=forum_threadcount-1,
                            forum_lastpost='".$bestForumLastThread['thread_lastpost']."',
                            forum_lastpostid = '".$bestForumLastThread['thread_lastpostid']."',
                            forum_lastuser='".$bestForumLastThread['thread_lastuser']."'
                            WHERE forum_id=".$forum_id
                );

                addNotice('success', $locale['forum_0752']);
            }

            redirect(FORUM."viewthread.php?thread_id=".$thread_id);

        } else {

            $modal = openmodal('movethread', $locale['forum_0206'], ['class_dialog' => 'modal-center']);
            $modal .= openform('moveform', 'post', FORUM."viewthread.php?forum_id=".$forum_id."&amp;thread_id=".$thread_id."&amp;step=move");
            // disable all forum that is type 1.
            $disabled_opts[] = $forum_id;
            $disabled_sql = "SELECT forum_id FROM ".DB_FORUMS." WHERE forum_type='1'";
            $forum_search = dbquery($disabled_sql);
            if (dbrows($forum_search) > 0) {
                while ($disabled_data = dbarray($forum_search)) {
                    $disabled_opts[] = $disabled_data['forum_id'];
                }
            }
            $modal .= form_select_tree('new_forum_id', $locale['forum_0751'], '',
                [
                    'input_id'     => "new_forum_id",
                    'no_root'      => TRUE,
                    'inline'       => TRUE,
                    'disable_opts' => $disabled_opts
                ],
                DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');
            $modal .= form_button('move_thread', $locale['forum_0206'], $locale['forum_0206'], ['class' => 'btn-primary']);
            $modal .= closeform();
            $modal .= closemodal();
            add_to_footer($modal);
        }
    }

    /**
     * SQL action remove thread
     *               - post deleted
     *               - attachment deleted
     *               - user thread tracking deleted.
     *
     * Refactor completed
     */
    private function removeThread() {
        $response = FALSE;
        $thread_id = (int)$this->class->getThreadID();
        $forum_id = (int)$this->class->getForumID();

        if ($this->class->verifyThreadID($thread_id) && $this->class->verifyForumID($forum_id)) {

            $param = [':tid' => (int)$thread_id];

            // Delete all thread posts
            dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE thread_id=:tid", $param);

            // Delete all thread notifications
            dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id=:tid", $param);

            // Delete all attachment files
            $result = dbquery("SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id=:tid", $param);
            if (dbrows($result)) {
                while ($attach = dbarray($result)) {
                    if (file_exists(INFUSIONS."forum/attachments/".$attach['attach_name'])) {
                        @unlink(INFUSIONS."forum/attachments/".$attach['attach_name']);
                    }
                }
            }

            // Delete all attachments
            dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id=:tid", $param);

            // Delete all poll voters
            dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id=:tid", $param);

            // Delete all Poll Options
            dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id=:tid", $param);

            // Delete Thread Poll
            dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id=:tid", $param);

            // Delete The Thread
            dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id=:tid", $param);

            $response = TRUE;
        }

        return (boolean)$response;
    }

    // /**
    //  * Refresh db_threads thread's stats
    //  *
    //  * @param $thread_id
    //  */
    // private function refreshThread($thread_id) {
    //     $query = "SELECT post_datestamp ':time', post_author ':aid', post_id ':pid' FROM ".DB_FORUM_POSTS." WHERE thread_id=:tid ORDER BY post_datestamp DESC LIMIT 1";
    //     $param[':tid'] = intval($thread_id);
    //     $param[':post_count'] = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=:tid", $param);
    //     $result = dbarray(dbquery($query, $param));
    //     if (dbrows($result)) {
    //         $pdata = dbarray($result) + $param;
    //         dbquery("
    //         UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:time,
    //         thread_lastpostid=:pid,
    //         thread_postcount=:post_count,
    //         thread_lastuser=:aid", $pdata);
    //     } else {
    //         // delete the thread because there are no post count in this thread.
    //         dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id=:tid", [':tid' => $param[':tid']]);
    //     }
    // }
}

require_once ADMIN.'members/members.class.php';
require_once ADMIN.'members/users/actions.class.php';