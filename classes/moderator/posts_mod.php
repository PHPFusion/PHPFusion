<?php
namespace PHPFusion\Infusions\Forum\Classes\Moderator;

use PHPFusion\Infusions\Forum\Classes\Forum_Moderator;

class Posts_Mod {

    private $class = NULL;

    public function __construct(Forum_Moderator $obj) {
        $this->class = $obj;
        if (iMOD) {
            $this->deletePosts();
            $this->movePosts();
        }
    }

    /**
     * Moderator Action - Remove only selected post in a thread
     * Requires $_POST['delete_posts']
     * refer to - viewthread_options.php
     */
    private function deletePosts() {
        $locale = fusion_get_locale();

        $thread_id = $this->class->getThreadID();
        //$forum_id = $this->class->getForumID();
        $thread_param[':tid'] = (int)$thread_id;
//        $forum_param[':fid'] = (int)$forum_id;

        if (post('delete_posts')) {

            $post_items = sanitizer('delete_item_post', '', 'delete_item_post');

            $post_items = explode(',', $post_items);

            $post_items = array_filter($post_items);

            if (!empty($post_items)) { // the checkboxes
                // get the thread post item.
                $thread_count = FALSE;
                $count = 0;
                $fpost_id = 0;

                $thread_data = get_thread_stats($thread_id);

                $sanitized_post_id = [];

                foreach ($post_items as $del_post_id) {
                    if (isnum($del_post_id)) {
                        if ($del_post_id == $thread_data['first_post_id']) {
                            // this is the first post
                            $fpost_id = $del_post_id;
                        }
                        $sanitized_post_id[] = $del_post_id;
                        $count++;
                    }
                }
                if (!empty($sanitized_post_id)) {

                    $rm_pid = implode(',', $sanitized_post_id);
                    // also need to delete post_mood
                    $remove_mood = "DELETE FROM ".DB_FORUM_POST_NOTIFY." WHERE post_id IN ($rm_pid)";
                    // Delete attachment records
                    // Find and delete physical attachment files
                    $delete_attachments = "DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id=:tid AND post_id IN($rm_pid)";

                    $del_attachment = "SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id IN ($rm_pid)";

                    // Delete any reports existed
                    $del_reports = "DELETE FROM ".DB_FORUM_REPORTS." WHERE post_id IN($rm_pid)";

                    // First post to be deleted
                    $result = dbquery($del_attachment);
                    if (dbrows($result)) {
                        while ($adata = dbarray($result)) {
                            $file_path = FORUM."attachments/".$adata['attach_name'];
                            if (file_exists($file_path) && !is_dir($file_path)) {
                                @unlink($file_path);
                            }
                        }
                    }

                    // Delete posts
                    $delete_forum_posts = "DELETE FROM ".DB_FORUM_POSTS." WHERE thread_id=:tid AND post_id IN($rm_pid)";
                    if (!empty($fpost_id)) {
                        // Just do reset instead of removing.
                        $amend_query = "UPDATE ".DB_FORUM_POSTS." SET post_message=:message, post_author=:new_aid WHERE post_id=:pid";
                        $fpost_param = [
                            ':pid'     => (int)$fpost_id,
                            ':message' => '',
                            ':new_aid' => '-1'
                        ];

                        dbquery($amend_query, $fpost_param);

                        // Remove just the first post as undeletable.
                        if (($fpost_key = array_search($fpost_id, $sanitized_post_id)) !== FALSE) {
                            unset($sanitized_post_id[$fpost_key]);
                        }

                        $frm_pid = implode(',', $sanitized_post_id);
                        $delete_forum_posts = "DELETE FROM ".DB_FORUM_POSTS." WHERE thread_id=:tid AND post_id IN ($frm_pid)";
                    }

                    dbquery($remove_mood);

                    dbquery($delete_attachments, $thread_param);

                    dbquery($del_reports);

                    if (!empty($delete_forum_posts)) {
                        dbquery($delete_forum_posts, $thread_param);
                    }

                    // Recalculate Authors Post .. this one is mistaken, because all must also delete.
                    $calculate_post = "SELECT post_author, COUNT(post_id) 'num_posts' FROM ".DB_FORUM_POSTS." WHERE post_id IN ($rm_pid) GROUP BY post_author";
                    $result = dbquery($calculate_post);
                    if (dbrows($result) > 0) {
                        while ($pdata = dbarray($result)) {
                            $num_posts = (int)$pdata['num_posts'];
                            dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-$num_posts WHERE user_id=:uid", [':uid' => (int)$pdata['post_author']]);
                        }
                    }

                    // Update Thread
                    if (!dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=:tid", $thread_param)) {

                        dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id=:tid", $thread_param); // you will not get this with the new patch, leave here until further examination.

                    } else {

                        // Find last post
                        $find_lastpost = "SELECT post_datestamp, post_author, post_id FROM ".DB_FORUM_POSTS." WHERE thread_id=:tid ORDER BY post_datestamp DESC LIMIT 1";

                        $pdata = dbarray(dbquery($find_lastpost, $thread_param));

                        dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:time, thread_lastpostid=:pid, thread_postcount=:count, thread_lastuser=:auid WHERE thread_id=:tid", [
                            ":time"  => (int)$pdata['post_datestamp'],
                            ":pid"   => (int)$pdata['post_id'],
                            ":count" => dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=:tid", [":tid" => (int)$thread_id]),
                            ":auid"  => (int)$pdata['post_author'],
                            ":tid"   => (int)$thread_id,
                        ]);

                        $thread_count = TRUE;
                    }

                    // Update Forum
                    $forum_mods = new Forums_Mod($this->class);
                    $forum_mods->refreshForums();

                    addNotice('success', sprintf($locale['success-DP001'], count($sanitized_post_id)));

                    if ($thread_count === FALSE) { // no remaining thread
                        addNotice('success', $locale['success-DP002']);
                        redirect(FORUM."index.php?viewforum&amp;forum_id=".$this->forum_id."&amp;parent_id=".$this->parent_id);
                    }
                    redirect(FORM_REQUEST);
                } else {
                    addNotice('danger', $locale['error-DP001']);
                    redirect(FORM_REQUEST);
                }
            } else {
                addNotice('danger', $locale['error-DP001']);
                redirect(FORM_REQUEST);
            }
        }
    }

    private $post_id_array = [];
    private $first_post_found = FALSE;
    private $first_post_id = 0;
    private $remove_first_post = FALSE;
    private $f_post_blo = FALSE;
    private $current_num_post = 0;

    /**
     * Moving Posts
     */
    private function movePosts() {
        $locale = fusion_get_locale();

        if (post('move_posts')) {

            $thread_id = $this->class->getThreadID();
            $thread_param[':tid'] = (int)$thread_id;
            // define('STOP_REDIRECT', true);
            // print_P($_POST);

            $post_items = form_sanitizer($_POST['delete_item_post'], '', 'delete_item_post'); // The selected checkbox of post to move.
            $post_items = explode(',', $post_items);
            $this->post_id_array = array_filter($post_items);

            if (!empty($this->post_id_array)) {

                list($this->first_post_id) = dbarraynum(dbquery("SELECT post_id FROM ".DB_FORUM_POSTS." WHERE thread_id=:tid ORDER BY post_datestamp ASC LIMIT 1", $thread_param));
                /**
                 * Scan for Posts
                 */
                $post_id_array = [];
                foreach ($post_items as $move_post_id) {
                    if (isnum($move_post_id)) {
                        $post_id_array[] = $move_post_id;
                        if ($move_post_id == $this->first_post_id) {
                            $this->first_post_found = TRUE;
                        }
                    }
                }
                $move_posts = implode($post_id_array);
                $this->post_id_array = $post_id_array;

                // found post items.
                if (!empty($move_posts)) {

                    // Current Status Before Move.
                    // this one is redundant.
                    // it just a matter for validation.
                    $move_result = dbquery("SELECT COUNT(post_id) 'num_posts' FROM ".DB_FORUM_POSTS." WHERE post_id IN ($move_posts) AND thread_id=:tid GROUP BY thread_id", $thread_param);

                    if (dbrows($move_result)) {

                        $pdata = dbarray($move_result);

                        $this->current_num_post = $pdata['num_posts'];

                        $post_count = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=:tid", $thread_param);

                        $modal = openmodal('forum0300', $locale['forum_0176'], ['class_dialog' => 'modal-center']);

                        if ($this->first_post_found) {
                            // there is a first post.
                            $modal .= "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>";
                            if ($pdata['num_posts'] != $post_count) {
                                $this->remove_first_post = TRUE;
                                $modal .= str_replace(['[STRONG]', '[/STRONG]'], ['<strong>', '</strong>'], $locale['forum_0305'])."<br />\n"; // trying to remove first post with other post in the thread
                            } else {
                                $modal .= str_replace(['[STRONG]', '[/STRONG]'], ['<strong>', '</strong>'], $locale['forum_0306'])."<br />\n"; // confirm ok to remove first post.
                            }

                            if ($this->remove_first_post && count($this->post_id_array) == 1) {
                                $modal .= "<br /><strong>".$locale['forum_0307']."</strong><br /><br />\n"; // no post to move.
                                $modal .= "<a href='".FORM_REQUEST."'>".$locale['forum_0309']."</a>";
                                $this->f_post_blo = TRUE;
                            }
                            $modal .= "</div></div>\n";
                        }

                        // just move any post within thread, without moving first post or move to any other forum.
                        if (!post('new_forum_id') && !$this->f_post_blo) {

                            $modal .= $this->moveSimilarPost();

                        } else if (post('new_forum_id', FILTER_VALIDATE_INT) && !post('new_thread_id', FILTER_VALIDATE_INT) && !post('new_thread_subject') && !$this->f_post_blo) {
                            // move post to another forum thread

                            $modal .= $this->moveNewForumPost();

                        } else if (get('sv', FILTER_VALIDATE_INT) && post('new_forum_id', FILTER_VALIDATE_INT) && post('new_thread_id', FILTER_VALIDATE_INT) || post('new_thread_subject')) {

                            $modal .= $this->executeMovePost();
                        }

                        $modal .= closemodal();
                        add_to_footer($modal);

                    } else {
                        addNotice('danger', $locale['error-MP002']);
                        redirect(FORM_REQUEST);
                    }
                } else {
                    addNotice('danger', $locale['forum_0307']); // No post to move
                    redirect(FORM_REQUEST);
                }
            } else {
                addNotice('danger', $locale['forum_0307']); // No post to move
                redirect(FORM_REQUEST);
            }
        }
    }

    public function moveSimilarPost() {
        $locale = fusion_get_locale();
        $fl_result = dbquery("
        SELECT f.forum_id, f.forum_name, f.forum_type, f2.forum_name 'forum_cat_name',
        (	SELECT COUNT(thread_id) FROM ".DB_FORUM_THREADS." th WHERE f.forum_id=th.forum_id AND th.thread_id !='".$this->class->getThreadID()."'
            GROUP BY th.forum_id
        ) AS threadcount
        FROM ".DB_FORUMS." f
        LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
        WHERE ".groupaccess('f.forum_access')."
        ORDER BY f2.forum_order ASC, f.forum_order ASC
        ");

        $html = '';
        if (dbrows($fl_result)) {
            $exclude_opts = [];
            while ($data = dbarray($fl_result)) {
                if (empty($data['threadcount']) || $data['forum_type'] == '1') {
                    $exclude_opts[] = $data['forum_id'];
                }
            }

            $html .= openform('modopts', 'post');
            $html .= form_select_tree('new_forum_id', $locale['forum_0301'], '', [
                'disable_opts' => $exclude_opts,
                'no_root'      => 1,
                'inline'       => FALSE,
                'inner_width'  => '100%'
            ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');

            foreach ($this->post_id_array as $value) {
                $html .= form_text("delete_item_post[]", '', $value, ["input_id" => "delete_post[$value]"]);
            }
            $html .= form_hidden('move_posts', '', 1);
            $html .= modalfooter(form_button($locale['forum_0302'], $locale['forum_0208'], $locale['forum_0208'], ['class' => 'btn-primary']));
            $html .= closeform();

        } else {
            $html .= "<strong>".$locale['forum_0310']."</strong><br /><br />\n";
            $html .= "<a href='".FORM_REQUEST."'>".$locale['forum_0309']."</a><br /><br />\n";
        }

        return (string)$html;
    }

    private function moveNewForumPost() {
        $locale = fusion_get_locale();
        // Select Threads in Selected Forum.
        $html = '';
        $new_forum_id = sanitizer('new_forum_id', 0, 'new_forum_id');
        if ($new_forum_id) {

            $tl_result = dbquery("
                            SELECT thread_id, thread_subject
                            FROM ".DB_FORUM_THREADS."
                            WHERE forum_id=:nfid AND thread_id !=:tid AND thread_hidden=0
                            ORDER BY thread_subject ASC
                            ", [':nfid' => (int)$new_forum_id,
                                ':tid'  => (int)$this->class->getThreadID()
            ]);
            if (dbrows($tl_result)) {

                $thread_list = [];
                while ($tl_data = dbarray($tl_result)) {
                    $thread_list[$tl_data['thread_id']] = $tl_data['thread_subject'];
                }
                $html .= openform('thread_select_frm', 'post');
                $html .= form_hidden('new_forum_id', '', $new_forum_id); // has val

                if (!post('new_thread_select', FILTER_VALIDATE_INT)) {

                    $html .= form_checkbox('new_thread_select', '', '', [
                        'type'     => 'radio',
                        'options'  => [
                            1 => $locale['forum_0300'],
                            2 => $locale['forum_0303'],
                        ],
                        'inline'   => TRUE,
                        'required' => TRUE,
                    ]);

                } else {

                    $ehtml = form_text('new_thread_subject', $locale['forum_2000'], '', ['required' => TRUE, 'max_length' => 250, 'inline' => FALSE]).
                        form_hidden('new_thread_select', '', 2);

                    if (post('new_thread_select', FILTER_VALIDATE_INT) == 2) {
                        $ehtml = form_select('new_thread_id', $locale['forum_0303'], '', [
                                'options'     => $thread_list,
                                'inline'      => FALSE,
                                'inner_width' => '100%',
                            ]).form_hidden('new_thread_select', '', 1);
                    }

                    $html .= $ehtml;
                }
                foreach ($this->post_id_array as $value) {
                    $html .= form_hidden("delete_item_post[]", "", $value, ["input_id" => "delete_post[$value]"]);
                }
                $html .= form_hidden('move_posts', '', 1);
                $html .= modalfooter(form_button($locale['forum_0176'], $locale['forum_0208'], $locale['forum_0208'], ['class' => 'btn-primary']));

            } else {
                $html .= $locale['forum_0308']."<br /><br />\n";
                $html .= "<a href='".FORM_REQUEST."'>".$locale['forum_0309']."</a>\n";
            }
        }

        return (string)$html;
    }

    private function executeMovePost() {
        $locale = fusion_get_locale();
        $new_thread_id = sanitizer('new_thread_id', 0, 'new_thread_id');
        $new_forum_id = sanitizer('new_forum_id', 0, 'new_forum_id');

        // define('STOP_REDIRECT', true);
        // print_p($_POST);
        /**
         * Execute move posts
         */
        $param = [
            ':ntid' => (int)$new_thread_id,
            ':nfid' => (int)$new_forum_id
        ];
        // $ntid_param[':ntid'] = (int)$new_thread_id;
        // $nfid_param[':nfid'] = (int)$new_forum_id;

        // Redirect if there is no thread count
        $new_thread_subject = post('new_thread_subject');
        $new_thread_selected = post('new_thread_select', FILTER_VALIDATE_INT);
        if (!dbcount("(thread_id)", DB_FORUM_THREADS, "thread_id=:ntid AND forum_id=:nfid", $param) && !$new_thread_subject && $new_thread_selected !=2) {
            if (!$new_thread_id) {
                addNotice('danger', $locale['error-MP001']);
                redirect(FORM_REQUEST);
            }
        }

        // Selects all current selected posts
        $move_posts_add = '';
        foreach ($this->post_id_array as $move_post_id) {
            if (isnum($move_post_id)) {
                if ($this->first_post_found && $this->remove_first_post) {
                    if ($move_post_id != $this->first_post_id) {
                        $move_posts_add .= ($move_posts_add ? ',' : '').$move_post_id;
                    }
                    $this->current_num_post = $this->current_num_post - 1;
                } else {
                    $move_posts_add = $move_post_id.($move_posts_add ? ',' : '').$move_posts_add;
                }
            }
        }

        if (!empty($move_posts_add)) {

            // Validate if all the post belongs to the thread?
            if ($this->current_num_post == count($this->post_id_array)) {

                $num_posts = (int)$this->current_num_post;
                // $current_thread_id = (int)$this->class->getThreadID();
                // $current_forum_id = (int)$this->class->getForumID();

                // Create a new thread - ok
                if (post('new_thread_subject')) {
                    $thread_subject = sanitizer('new_thread_subject', '', 'new_thread_subject');
                    $author_result = dbarray(dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE post_id IN ($move_posts_add) ORDER BY post_datestamp ASC LIMIT 1"));
                    list($lastpost_user, $lastpost_datestamp, $lastpost_id) = dbarraynum(dbquery("SELECT post_author, post_datestamp, post_id FROM ".DB_FORUM_POSTS." WHERE post_id IN ($move_posts_add) ORDER BY post_datestamp DESC LIMIT 1"));

                    // Create a Thread
                    $new_thread_data = [
                        'thread_id'      => 0,
                        'forum_id'       => $new_forum_id,
                        'thread_subject' => $thread_subject,
                        'thread_author'  => $author_result['post_author'],
                        // more
                        'thread_lastpost' => $lastpost_datestamp,
                        'thread_lastpostid' => $lastpost_id,
                        'thread_lastuser' => $lastpost_user,
                        'thread_postcount' => $num_posts,
                    ];
                    // print_P($new_thread_data);
                    // override the new thread id
                    $param[':ntid'] = dbquery_insert(DB_FORUM_THREADS, $new_thread_data, 'save', ['keep_session' => TRUE]);
                }

                // Update all selected posts with new thread and forum ID
                dbquery("UPDATE ".DB_FORUM_POSTS." SET forum_id=:nfid, thread_id=:ntid, post_datestamp='".TIME."' WHERE post_id IN (".$move_posts_add.")", $param);

                // Update all thread attachments with new thread ID
                dbquery("UPDATE ".DB_FORUM_ATTACHMENTS." SET thread_id=:ntid WHERE post_id IN(".$move_posts_add.")", [':ntid' => $param[':ntid']]);

                // Get the latest post
                list($lastpost_id, $lastpost_author, $lastpost) = dbarraynum(dbquery("
                                                    SELECT post_id, post_author, post_datestamp
                                                    FROM ".DB_FORUM_POSTS."
                                                    WHERE thread_id=:ntid
                                                    ORDER BY post_datestamp DESC
                                                    LIMIT 1
                                                    ", [':ntid' => $param[':ntid']]
                ));

                // ReUpdate the target thread
                dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:thread_lastpost, thread_lastpostid=:thread_lastpostid, thread_postcount=thread_postcount+$num_posts, thread_lastuser=:thread_lastuser WHERE thread_id=:new_thread_id", [
                        ':thread_lastpost'   => $lastpost,
                        ':thread_lastpostid' => $lastpost_id,
                        ':thread_lastuser'   => $lastpost_author,
                        ':new_thread_id'     => $param[':ntid']
                    ]
                );

                // Re update the target forum
                dbquery("UPDATE ".DB_FORUMS."   SET forum_lastpost=:t_last_post, forum_postcount=forum_postcount+$num_posts, forum_lastuser=:t_last_user   
                WHERE forum_id=:nfid", [
                    ':t_last_post' => $lastpost,
                    ':t_last_user' => $lastpost_author,
                    ':nfid'        => $param[':nfid']
                ]);

                // update thread and forum lastpost info
                $current_forum_id = (int) $this->class->getForumID();
                $current_thread_id = (int) $this->class->getThreadID();
                // If current thread has no more post
                if (!dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=:ctid", [':ctid' => $this->class->getPostId() ])) {
                    // Select last post information from post db
                    list($last_post, $last_user) = dbarraynum(dbquery("SELECT post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE forum_id=:cfid ORDER BY post_datestamp DESC  LIMIT 1", [':cfid' => $this->class->getForumID() ]));

                    // update the forum to deduct a thread and update last post info
                    dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost=:flp, forum_postcount=forum_postcount-$num_posts, forum_threadcount=:tcount, forum_lastuser=:fla   WHERE forum_id=:cfid", [
                        ':flp'    => $last_post,
                        ':fla'    => $last_user,
                        ':tcount' => (dbcount("(thread_id)", DB_FORUM_THREADS, "forum_id=:cfid", [':cfid' => $current_forum_id ]) - 1),
                        ':cfid'   => $current_forum_id
                    ]);

                    // delete current thread
                    dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id=:ctid", [':ctid'=>$current_thread_id]);
                    // delete all current thread notify for all users
                    dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id=:ctid", [':ctid'=>$current_thread_id]);
                    // delete all current thread poll votes
                    dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id=:ctid", [':ctid'=>$current_thread_id]);
                    // delete current thread poll options
                    dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id=:ctid", [':ctid'=>$current_thread_id]);
                    // delete current thread poll
                    dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id=:ctid", [':ctid'=>$current_thread_id]);

                } else {

                   // find current thread info
                    list($thread_lastpostid, $thread_lastpost_user, $thread_lastpost) = dbarraynum(dbquery("
                                        SELECT post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." 
                                        WHERE thread_id=:ctid ORDER BY post_datestamp DESC
                                        LIMIT 1", [':ctid' => $current_thread_id]));
                    dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:tlp, thread_lastpostid=:tpi, thread_postcount=thread_postcount-$num_posts, thread_lastuser=:tlu WHERE thread_id=:tid", [
                        ':tlp' => $thread_lastpost,
                        ':tpi' => $thread_lastpostid,
                        ':tlu' => $thread_lastpost_user,
                        ':tid' => $current_thread_id,
                    ]);
                    dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost=:tlp, forum_postcount=forum_postcount-$num_posts, forum_lastuser=:tlu WHERE forum_id=:fid", [
                        ':tlp' => $thread_lastpost,
                        ':tlu' => $thread_lastpost_user,
                        ':fid' => $current_forum_id
                    ]);
                }

                $pid = count($this->post_id_array) - 1;
                addNotice('success', 'Posts have been moved', 'all');
                redirect(FORUM."viewthread.php?thread_id=".$new_thread_id."&amp;pid=".$this->post_id_array[$pid]."#post_".$this->post_id_array[$pid]);

            } else {

                addNotice('danger', $locale['error-MP002']);
                redirect(FORM_REQUEST);

            }
        } else {
            addNotice('danger', $locale['forum_0307']);
            redirect(FORM_REQUEST);
        }
    }

}