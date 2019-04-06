<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: threads/view.php
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

namespace PHPFusion\Infusions\Forum\Classes\Threads;

use PHPFusion\BreadCrumbs;
use PHPFusion\httpdownload;
use PHPFusion\Infusions\Forum\Classes\Forum_Server;

class View_Thread extends Forum_Server {

    public function __construct() {
        require_once THEMES."templates/header.php";
        require_once INCLUDES."infusions_include.php";
        require_once INFUSIONS."forum/forum_include.php";
        include INFUSIONS."forum/templates.php";
    }

    private $thread_data = [];

    public function display_thread() {

        $info = self::thread()->get_threadInfo();

        $this->set_ThreadJs();

        if (isset($_GET['action'])) {

            switch ($_GET['action']) {
                case 'editpoll':
                    // Template
                    $poll = new Forum_Poll($info);

                    return $poll::render_poll_form(TRUE);
                    break;
                case 'deletepoll':
                    // Action
                    $poll = new Forum_Poll($info);
                    $poll->delete_poll();
                    break;
                case 'newpoll':
                    // Template
                    $poll = new Forum_Poll($info);

                    return $poll::render_poll_form();
                    break;
                case 'edit':
                    // Template
                    return $this->render_edit_form();
                    break;
                case 'reply':
                    // Template
                    return $this->render_reply_form();
                    break;
                case 'award':
                    $bounty = new Forum_Bounty($info);
                    $bounty->award_bounty();
                    break;
                case 'newbounty':
                    // Template
                    $bounty = new Forum_Bounty($info);

                    return $bounty->render_bounty_form();
                    break;
                case 'editbounty':
                    // Template
                    $bounty = new Forum_Bounty($info);

                    return $bounty->render_bounty_form(TRUE);
                    break;
                default:
                    redirect(clean_request("", ['action'], FALSE));
            }
        } else {

            self::check_download_request();
            // +1 threadviews
            self::increment_thread_views($info['thread']['thread_id']);
            // +1 see who is viewing thread
            self::thread()->set_thread_visitor();

            if ($info['thread']['forum_users'] == TRUE) {
                $info['thread_users'] = $this->get_participated_users($info);
            }

            // the upvote changes
            // everyone can upvote and downvote -- doesn't matter.
            // the author must select which is answered.
            // vote get points as usual. if voted up, and change voted down, all logic remains same.

            return render_thread($info);
        }

        return NULL;
    }

    /**
     * Delete a post when editing a post
     *
     * @param $post_id
     * @param $thread_id
     * @param $forum_id
     * @param $is_first_post
     */
    private function delete_post($post_id, $thread_id, $forum_id, $is_first_post) {

        if (isset($_POST['delete']) && isnum($post_id) && isnum($thread_id) && isnum($forum_id)) {
            $result = dbquery("SELECT post_author ':aid' FROM ".DB_FORUM_POSTS." WHERE post_id=:pid AND thread_id=:tid AND forum_id=:fid", [':pid' => intval($post_id), ':tid' => intval($thread_id), ':fid' => intval($forum_id)]);
            if (dbrows($result)) {
                $result_arr = dbarray($result);

                $post_id_param = [':pid' => $post_id];
                $thread_id_param = [':tid' => $thread_id];
                $forum_id_param = [':fid' => $forum_id];

                if ($is_first_post) {
                    $first_post_param = [
                            ":message" => "",
                            ":aid"     => -1,
                        ] + $post_id_param;
                    dbquery("UPDATE ".DB_FORUM_POSTS." SET post_message=:message, post_author=:aid WHERE post_id=:pid", $first_post_param);
                } else {
                    // Delete the current post
                    dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE post_id=:pid", $post_id_param);
                }

                // Minus -1 post count on user
                dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-1 WHERE user_id=:aid", $result_arr);

                // Update forum post count -1 on forum
                dbquery("UPDATE ".DB_FORUMS." SET forum_postcount=forum_postcount-1 WHERE forum_id=:fid", [':fid' => intval($forum_id)]);

                // Delete all post attachment
                $result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id=:pid", $post_id_param);
                if (dbrows($result)) {
                    while ($attach = dbarray($result)) {
                        if (file_exists(FORUM.'attachments/'.$attach['attach_name'])) {
                            unlink(FORUM."attachments/".$attach['attach_name']);
                        }
                        dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id=:pid", $post_id_param);
                    }
                }

                // Check if there are any remaining post in the thread
                $posts = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=:tid", $thread_id_param);
                if (!$posts) {
                    // Delete the thread
                    dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id=:tid", $thread_id_param);
                    // Delete all tracked status for this thread
                    dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id=:tid", $thread_id_param);
                    // Update forum threadcount -1 on forum
                    // @todo: Check if a post count need to be deducted or not in the integral action
                    dbquery("UPDATE ".DB_FORUMS." SET forum_threadcount=forum_threadcount-1 WHERE forum_id=:fid", $forum_id_param);
                } else {
                    // Check if this is the last post of the thread and update the thread with latest post in the entire thread result
                    $is_last_post = dbcount("(thread_id)", DB_FORUM_THREADS, "thread_id=:tid AND thread_lastpostid=:pid AND thread_lastuser=:aid", [":tid" => $thread_id, ":pid" => $post_id] + $result_arr);
                    if ($is_last_post) {
                        $thread_last_result = dbquery("SELECT thread_id ':last_tid', post_id ':last_pid', post_author ':last_aid', post_datestamp ':last_time'
                        FROM ".DB_FORUM_POSTS." WHERE thread_id=:tid AND post_hidden='0' ORDER BY post_datestamp DESC LIMIT 1", [":tid" => $thread_id]);
                        $last_thread_data = dbarray($thread_last_result);
                        dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:last_time, thread_lastpostid=:last_pid, thread_postcount=thread_postcount-1, thread_lastuser=:last_aid WHERE thread_id=:last_tid", $last_thread_data);
                    }
                }

                // Check if this is the last post of the forum and update the forum with latest post in the entire forum threads's post
                $result = dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_id=:fid AND forum_lastpostid=:pid", $forum_id_param + $post_id_param);
                if (dbrows($result)) {
                    $forum_last_result = dbquery("SELECT p.post_id ':last_pid', p.forum_id ':last_fid', p.post_author ':last_aid', p.post_datestamp ':last_datestamp'
                                    FROM ".DB_FORUM_POSTS." p
                                    LEFT JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
                                    WHERE p.forum_id=:fid AND thread_hidden='0' AND post_hidden='0'
                                    ORDER BY post_datestamp DESC LIMIT 1", $forum_id_param);
                    if (dbrows($forum_last_result)) {
                        $last_forum_data = dbarray($forum_last_result);
                        dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost=:last_datestamp, forum_lastpostid=:last_pid, forum_lastuser=:last_aid WHERE forum_id=:last_fid", $last_forum_data);
                    } else {
                        dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_lastpostid='0' , forum_lastuser='0' WHERE forum_id=:fid", $forum_id_param);
                    }
                }

                redirect(FORUM."postify.php?post=edit&amp;thread_id=$thread_id&forum_id=$forum_id&post_count=$posts");
            }
        }
    }

    /**
     * Displays Reply To A Thread Form
     * Template: function display_form_postform
     */
    public function render_reply_form() {

        $thread = self::thread();
        $thread_info = $thread->get_threadInfo();
        $thread_data = $thread_info['thread'];
        $forum_settings = parent::get_forum_settings();
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();

        $this->thread_data = $thread_info['thread'];

        if ((!iMOD or !iSUPERADMIN) && $thread_data['thread_locked']) {
            addNotice("danger", $locale['forum_0277']);
            redirect(FORUM."index.php");
        }

        // Make a JS version
        if (isset($_POST['cancel']) && !empty($thread_data['thread_id'])) {
            if (fusion_get_settings("site_seo")) {
                redirect(fusion_get_settings("siteurl")."infusions/forum/viewthread.php?thread_id=".$thread_data['thread_id']);
            }
            redirect(FORUM.'viewthread.php?thread_id='.$thread_data['thread_id']);

        }

        if ($thread->getThreadPermission("can_reply") && !empty($thread_data['thread_id'])) {

            add_to_title($locale['global_201'].$locale['forum_0360']);
            //add_to_footer("<script src='".FORUM."templates/ajax/post_preview.js'></script>");
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['forum_0360']]);

            // field data
            $post_data = [
                'post_id'         => 0,
                'post_cat'        => isset($_GET['post_id']) && isnum($_GET['post_id']) && $this->thread_data['thread_firstpostid'] !== $_GET['post_id'] ? intval($_GET['post_id']) : 0,
                'forum_id'        => $thread_info['thread']['forum_id'],
                'thread_id'       => $thread_info['thread']['thread_id'],
                'post_message'    => isset($_POST['post_message']) ? form_sanitizer($_POST['post_message'], "", 'post_message') : "",
                'post_showsig'    => isset($_POST['post_showsig']) ? 1 : 0,
                'post_smileys'    => isset($_POST['post_smileys']) || isset($_POST['post_message']) && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? 1 : 0,
                'post_author'     => $userdata['user_id'],
                'post_datestamp'  => time(),
                'post_ip'         => USER_IP,
                'post_ip_type'    => USER_IP_TYPE,
                'post_edituser'   => 0,
                'post_edittime'   => 0,
                'post_editreason' => "",
                'post_hidden'     => 0,
                'notify_me'       => 0,
                'post_locked'     => $forum_settings['forum_edit_lock'] || isset($_POST['thread_locked']) ? 1 : 0,
            ];

            // execute form post actions
            if (isset($_POST['post_reply'])) {

                require_once INCLUDES."flood_include.php";

                if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice

                    // If you merge, the datestamp on all forum, threads, post will not be updated.
                    $update_forum_lastpost = FALSE;

                    if (\Defender::safe()) {
                        // Prepare forum merging action
                        $last_post_author = dbarray(dbquery("
                        SELECT post_author FROM ".DB_FORUM_POSTS."
                        WHERE thread_id='".intval($thread_data['thread_id'])."'
                        ORDER BY post_id DESC LIMIT 1
                        "));

                        // delete post checkbox...
                        // if is lastpost, update thread on the last.

                        if ($last_post_author['post_author'] == $post_data['post_author'] && $thread_data['forum_merge'] == TRUE) {

                            $last_message = dbarray(dbquery("SELECT post_id, post_message, post_datestamp FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($thread_data['thread_id'])."' ORDER BY post_id DESC"));
                            $post_data['post_id'] = $last_message['post_id'];
                            $post_data['post_message'] = $last_message['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate", time()).":\n".$post_data['post_message'];
                            $post_data['post_datestamp'] = $last_message['post_datestamp'];
                            dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', ['primary_key' => 'post_id', 'keep_session' => TRUE]);

                        } else {

                            $update_forum_lastpost = TRUE;
                            dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', ['primary_key' => 'post_id', 'keep_session' => TRUE]);
                            $post_data['post_id'] = dblastid();
                            dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".intval($post_data['post_author'])."'");

                        }

                        // Attach files if permitted
                        if (!empty($_FILES)
                            && is_uploaded_file($_FILES['file_attachments']['tmp_name'][0])
                            && $thread->getThreadPermission("can_upload_attach")
                        ) {

                            $upload = form_sanitizer($_FILES['file_attachments'], "", 'file_attachments');
                            if ($upload['error'] == 0) {

                                foreach ($upload['target_file'] as $arr => $file_name) {
                                    $attach_data = ['thread_id'    => intval($thread_data['thread_id']),
                                                    'post_id'      => $post_data['post_id'],
                                                    'attach_name'  => $file_name,
                                                    'attach_mime'  => $upload['type'][$arr],
                                                    'attach_size'  => $upload['source_size'][$arr],
                                                    'attach_count' => 0, // downloaded times
                                    ];
                                    dbquery_insert(DB_FORUM_ATTACHMENTS, $attach_data, "save", ['keep_session' => TRUE]);
                                }

                            }
                        }

                        // Update stats in forum and threads
                        if ($update_forum_lastpost == TRUE) {

                            // find all parents and update them
                            $list_of_forums = get_all_parent(dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), intval($thread_data['forum_id']));
                            if (!empty($list_of_forums)) {
                                foreach ($list_of_forums as $forumID) {
                                    dbquery("
                                    UPDATE ".DB_FORUMS." SET
                                    forum_lastpost = '".time()."',
                                    forum_postcount=forum_postcount+1,
                                    forum_lastpostid='".intval($post_data['post_id'])."',
                                    forum_lastuser='".intval($post_data['post_author'])."'
                                    WHERE forum_id='".intval($forumID)."'
                                    ");
                                }
                            }

                            // update current forum
                            dbquery("
                            UPDATE ".DB_FORUMS." SET
                            forum_lastpost='".time()."',
                            forum_postcount=forum_postcount+1,
                            forum_lastpostid='".intval($post_data['post_id'])."',
                            forum_lastuser='".intval($post_data['post_author'])."'
                            WHERE forum_id='".intval($thread_data['forum_id'])."'
                            ");

                            // update current thread
                            dbquery("
                            UPDATE ".DB_FORUM_THREADS." SET
                            thread_lastpost='".time()."',
                            thread_lastpostid='".intval($post_data['post_id'])."',
                            thread_postcount=thread_postcount+1,
                            thread_lastuser='".intval($post_data['post_author'])."',
                            thread_lastpost= '".time()."'
                            WHERE thread_id='".intval($thread_data['thread_id'])."'
                            ");
                        }

                        if ($forum_settings['thread_notify'] && isset($_POST['notify_me']) && $_POST['notify_me'] == TRUE) {
                            if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".intval($thread_data['thread_id'])."' AND notify_user='".intval($post_data['post_author'])."'")) {
                                dbquery("
                                INSERT INTO ".DB_FORUM_THREAD_NOTIFY."
                                (thread_id, notify_datestamp, notify_user, notify_status)
                                VALUES ('".intval($thread_data['thread_id'])."', '".TIME."', '".intval($post_data['post_author'])."', 1)
                                ");

                            }
                        }

                        if (\Defender::safe()) {
                            redirect(FORUM."postify.php?post=reply&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;thread_id=".intval($post_data['thread_id'])."&amp;post_id=".intval($post_data['post_id']));
                        }

                    }
                }
            }

            // template data
            $form_action = FORUM."viewthread.php?action=reply&amp;forum_id=".$thread_data['forum_id']."&amp;thread_id=".$thread_data['thread_id'];
            if (isset($_GET['post_id'])) {
                $form_action = FORUM."viewthread.php?action=reply&amp;forum_id=".$thread_data['forum_id']."&amp;thread_id=".$thread_data['thread_id']."&amp;post_id=".intval($_GET['post_id']);
            }

            // Quote Get
            if (isset($_GET['quote']) && isnum($_GET['quote'])) {
                $quote_result = dbquery("SELECT a.post_message, b.user_name
                                        FROM ".DB_FORUM_POSTS." a
                                        INNER JOIN ".DB_USERS." b ON a.post_author=b.user_id
                                        WHERE thread_id='".intval($thread_data['thread_id'])."' AND post_id='".intval($_GET['quote'])."'");

                if (dbrows($quote_result) > 0) {
                    require_once INCLUDES.'bbcode_include.php';
                    $quote_data = dbarray($quote_result);
                    $post_data['post_message'] = "[quote name=".$quote_data['user_name']." post=".$_GET['quote']."]@".$quote_data['user_name']." - ".\strip_bbcodes($quote_data['post_message'])."[/quote]".$post_data['post_message'];
                    $form_action .= "&amp;post_id=".$_GET['quote']."&amp;quote=".$_GET['quote'];
                } else {
                    redirect(INFUSIONS."forum/index.php");
                }
            }

            $info = [
                "title"             => $locale['forum_0360'],
                "description"       => $locale['forum_2000'].$thread_data['thread_subject'],
                "openform"          => openform("input_form", "post", $form_action, ["enctype" => $thread->getThreadPermission("can_upload_attach")]).form_hidden("preview_src_file", "", INCLUDES."dynamics/assets/preview/preview.ajax.php"),
                "closeform"         => closeform(),
                "preview_box"       => "<div id='preview_box'></div>",
                "forum_id_field"    => form_hidden('forum_id', "", $post_data['forum_id']),
                "thread_id_field"   => form_hidden('thread_id', "", $post_data['thread_id']),
                "forum_field"       => "",
                "tags_field"        => "",
                "subject_field"     => form_hidden('thread_subject', "", $thread_data['thread_subject']),
                "message_field"     => form_textarea('post_message', $locale['forum_0601'], $post_data['post_message'],
                    [
                        "required"  => TRUE,
                        "preview"   => TRUE,
                        "form_name" => "input_form",
                        "bbcode"    => TRUE,
                        "height"    => "500px",
                    ]),
                // happens only in EDIT
                "delete_field"      => "",
                "edit_reason_field" => "",
                "attachment_field"  => $thread->getThreadPermission("can_upload_attach") ?
                    form_fileinput("file_attachments[]", $locale['forum_0557'], "",
                        ["input_id"    => "file_attachments",
                         "upload_path" => INFUSIONS.'forum/attachments/',
                         "type"        => "object",
                         "template"    => "modern",
                         "multiple"    => TRUE,
                         "inline"      => FALSE,
                         "max_count"   => $forum_settings['forum_attachmax_count'],
                         "valid_ext"   => $forum_settings['forum_attachtypes'],
                         "max_byte"    => $forum_settings['forum_attachmax'],
                         "class"       => "m-b-0",
                        ])."
                        <div class='m-b-20'>\n<small>".sprintf($locale['forum_0559'], parsebytesize($forum_settings['forum_attachmax']), str_replace('|', ', ', $forum_settings['forum_attachtypes']), $forum_settings['forum_attachmax_count'])."</small>\n</div>\n"
                    : "",
                "poll_form"         => "",
                "smileys_field"     => form_checkbox('post_smileys', $locale['forum_0169'], $post_data['post_smileys'], ['class' => 'm-b-0', 'type' => 'button', 'ext_tip' => $locale['forum_0622']]),
                "signature_field"   => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', $locale['forum_0264'], $post_data['post_showsig'], ['class' => 'm-b-0', 'type' => 'button', 'ext_tip' => $locale['forum_0170']]) : "",
                "sticky_field"      => "",
                "lock_field"        => "",
                "hide_edit_field"   => "",
                "post_locked_field" => "",
                // not available in edit mode.
                "notify_field"      => $forum_settings['thread_notify'] ? form_checkbox('notify_me', $locale['forum_0552'], $post_data['notify_me'], ['class' => 'm-b-0', 'type' => 'button', 'ext_tip' => $locale['forum_0171']]) : "",
                "post_buttons"      => form_button('post_reply', $locale['forum_0172'], $locale['forum_0172'], ['class' => 'btn-primary']).form_button('cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-default m-l-10']),
                "last_posts_reply"  => ""
            ];

            // only in reply
            if ($forum_settings['forum_last_posts_reply']) {
                $last_post_query = "
                SELECT p.thread_id, p.post_message, p.post_smileys, p.post_author, p.post_datestamp, p.post_hidden,
                u.user_id, u.user_name, u.user_status, u.user_avatar
                FROM ".DB_FORUM_POSTS." p
                LEFT JOIN ".DB_USERS." u ON p.post_author = u.user_id
                WHERE p.thread_id='".$thread_data['thread_id']."' AND p.post_hidden='0'
                GROUP BY p.post_id
                ORDER BY p.post_datestamp DESC LIMIT 0, ".$forum_settings['posts_per_page'];

                $last_post_result = dbquery($last_post_query);

                if (dbrows($last_post_result) > 0) {
                    $title = sprintf($locale['forum_0526'], $forum_settings['forum_last_posts_reply']);
                    if ($forum_settings['forum_last_posts_reply'] == "1") {
                        $title = $locale['forum_0525'];
                    }
                    ob_start();
                    echo "<p><strong>".$title."</strong>\n</p>\n";
                    echo "<div class='table-responsive'><table class='table'>\n";
                    $i = $forum_settings['posts_per_page'];

                    while ($data = dbarray($last_post_result)) {
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
                        echo "</tr>\n<tr>\n<td class='vatop tbl2 forum_thread_user_info' style='width:10%'>\n";
                        echo display_avatar($data, '50px');
                        echo "</td>\n<td class='vatop tbl1 forum_thread_user_post'>\n";
                        echo nl2br($message);
                        echo "</td>\n</tr>\n";
                        $i--;
                    }

                    echo "</table></div>\n";
                    $info['last_posts_reply'] = ob_get_contents();
                    ob_end_clean();
                }
            }

            return display_forum_postform($info);
        } else {
            if (fusion_get_settings("site_seo")) {
                redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
            }
            redirect(INFUSIONS.'forum/index.php');
        }

        return NULL;
    }

    /*
     * Displays Edit a Thread Post Form
     * Template: function display_forum_postform
     */
    public function render_edit_form() {

        $thread = self::thread();
        $thread_info = $thread->get_threadInfo();
        $thread_data = $thread_info['thread'];
        $forum_settings = self::get_forum_settings();
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        $settings = fusion_get_settings();

        // Get error redirect uri
        $default_redirect = FORUM."index.php";
        if ($settings['site_seo']) {
            $default_redirect = $settings['siteurl']."infusions/forum/index.php";
        }

        if ((!iMOD or !iSUPERADMIN) && $thread_data['thread_locked'])
            redirect(INFUSIONS.'forum/index.php');

        if (isset($_GET['post_id']) && isnum($_GET['post_id'])) {

            add_to_title($locale['global_201'].$locale['forum_0360']);
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['forum_0360']]);

            $result = dbquery("SELECT tp.*, tt.thread_subject, tt.thread_poll, tt.thread_author, tt.thread_locked, MIN(tp2.post_id) AS first_post
                FROM ".DB_FORUM_POSTS." tp
                INNER JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id=tt.thread_id
                INNER JOIN ".DB_FORUM_POSTS." tp2 ON tp.thread_id=tp2.thread_id
                WHERE tp.post_id='".intval($_GET['post_id'])."' AND tp.thread_id='".intval($thread_data['thread_id'])."' AND tp.forum_id='".intval($thread_data['forum_id'])."'
                GROUP BY tp2.post_id
                ");

            // Permission to edit
            if (dbrows($result) > 0) {

                $post_data = dbarray($result);

                if ((iMOD or iSUPERADMIN) || ($thread->getThreadPermission("can_reply") && $post_data['post_author'] == $userdata['user_id'])) {

                    $is_first_post = ($post_data['post_id'] == $thread_info['post_firstpost']) ? TRUE : FALSE;

                    // no edit if locked
                    if ($post_data['post_locked'] && !iMOD) {
                        redirect(FORUM."postify.php?post=edit&error=5&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&post_id=".$post_data['post_id']);
                    }

                    // no edit if time limit reached
                    if (!iMOD && ($forum_settings['forum_edit_timelimit'] > 0 && (TIME - $forum_settings['forum_edit_timelimit'] * 60) > $post_data['post_datestamp'])) {
                        redirect(FORUM."postify.php?post=edit&error=6&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&post_id=".$post_data['post_id']);
                    }

                    if (isset($_POST['cancel']) && !empty($thread_data['thread_id'])) {

                        if (fusion_get_settings("site_seo")) {
                            redirect(fusion_get_settings("siteurl")."infusions/forum/viewthread.php?thread_id=".$thread_data['thread_id']);
                        }
                        redirect(FORUM.'viewthread.php?thread_id='.$thread_data['thread_id']);

                    } else if (isset($_POST['post_edit'])) {

                        require_once INCLUDES."flood_include.php";

                        if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice

                            $post_data = [
                                'forum_id'        => $thread_data['forum_id'],
                                'thread_id'       => $thread_data['thread_id'],
                                'post_id'         => $post_data['post_id'],
                                'thread_subject'  => "",
                                'post_message'    => form_sanitizer($_POST['post_message'], "", 'post_message'),
                                'post_showsig'    => isset($_POST['post_showsig']) ? 1 : 0,
                                'post_smileys'    => isset($_POST['post_smileys']) || isset($_POST['post_message']) && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? 1 : 0,
                                'post_author'     => $post_data['post_author'],
                                'post_datestamp'  => $post_data['post_datestamp'], // update on datestamp or not?
                                'post_ip'         => USER_IP,
                                'post_ip_type'    => USER_IP_TYPE,
                                'post_edituser'   => $userdata['user_id'],
                                'post_edittime'   => isset($_POST['hide_edit']) ? 0 : time(),
                                'post_editreason' => form_sanitizer($_POST['post_editreason'], "", 'post_editreason'),
                                'post_hidden'     => 0,
                                'notify_me'       => 0,
                                'post_locked'     => $forum_settings['forum_edit_lock'] || isset($_POST['thread_locked']) ? 1 : 0
                            ];

                            // require thread_subject if first post
                            if ($is_first_post) {
                                $post_data['thread_subject'] = form_sanitizer($_POST['thread_subject'], "", 'thread_subject');
                                $_POST['thread_tags'] = isset($_POST['thread_tags']) ? $_POST['thread_tags'] : "";
                                $current_thread_tags = form_sanitizer($_POST['thread_tags'], "", 'thread_tags');
                                if ($thread_data['thread_tags'] !== $current_thread_tags) {
                                    // Assign the old ones into history
                                    $thread_data['thread_tags_old'] = $thread_data['thread_tags'];
                                    $thread_data['thread_tags_change'] = time();
                                }

                                $thread_data['thread_tags'] = $current_thread_tags;
                                $thread_data['thread_subject'] = $post_data['thread_subject'];
                                $thread_data['thread_locked'] = $post_data['post_locked'];
                            }

                            $thread_data['thread_sticky'] = isset($_POST['thread_sticky']) ? 1 : 0;

                            if (\Defender::safe()) {

                                // If post delete is checked
                                $this->delete_post($post_data['post_id'], $post_data['thread_id'], $post_data['forum_id'], $is_first_post);

                                // Update thread subject
                                if ($is_first_post) {
                                    dbquery_insert(DB_FORUM_THREADS, $thread_data, "update", ["keep_session" => TRUE]);
                                }

                                // Prepare forum merging action
                                $last_post_author = dbarray(dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread_data['thread_id']."' ORDER BY post_id DESC LIMIT 1"));
                                if ($last_post_author == $post_data['post_author'] && $thread_data['forum_merge']) {
                                    $last_message = dbarray(dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread_data['thread_id']."' ORDER BY post_id DESC"));
                                    $post_data['post_id'] = $last_message['post_id'];
                                    $post_data['post_message'] = $last_message['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate", time()).":\n".$post_data['post_message'];
                                    dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', ['primary_key' => 'post_id', 'keep_session' => TRUE]);
                                } else {
                                    dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', ['primary_key' => 'post_id', 'keep_session' => TRUE]);
                                }

                                // Delete attachments if there is any
                                foreach ($_POST as $key => $value) {
                                    if (!strstr($key, "delete_attach"))
                                        continue;
                                    $key = str_replace("delete_attach_", "", $key);
                                    $result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$post_data['post_id']."' AND attach_id='".(isnum($key) ? $key : 0)."'");
                                    if (dbrows($result) != 0 && $value) {
                                        $adata = dbarray($result);
                                        unlink(FORUM."attachments/".$adata['attach_name']);
                                        dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$post_data['post_id']."' AND attach_id='".(isnum($key) ? $key : 0)."'");
                                    }
                                }

                                if (!empty($_FILES) && is_uploaded_file($_FILES['file_attachments']['tmp_name'][0]) && $thread->getThreadPermission("can_upload_attach")) {
                                    $upload = form_sanitizer($_FILES['file_attachments'], "", 'file_attachments');
                                    if ($upload['error'] == 0) {
                                        foreach ($upload['target_file'] as $arr => $file_name) {
                                            $attachment = ['thread_id'    => $thread_data['thread_id'],
                                                           'post_id'      => $post_data['post_id'],
                                                           'attach_name'  => $file_name,
                                                           'attach_mime'  => $upload['type'][$arr],
                                                           'attach_size'  => $upload['source_size'][$arr],
                                                           'attach_count' => '0', // downloaded times?
                                            ];
                                            dbquery_insert(DB_FORUM_ATTACHMENTS, $attachment, 'save', ['keep_session' => TRUE]);
                                        }
                                    }
                                }

                                if (\Defender::safe()) {
                                    redirect(FORUM."postify.php?post=edit&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;thread_id=".intval($post_data['thread_id'])."&amp;post_id=".intval($post_data['post_id']));
                                }
                            }
                        }
                    }

                    // template data
                    $form_action = FORUM."viewthread.php?action=edit&amp;forum_id=".$thread_data['forum_id']."&amp;thread_id=".$thread_data['thread_id']."&amp;post_id=".$_GET['post_id'];

                    // get attachment.
                    $attachments = [];
                    $attach_rows = 0;
                    if ($thread->getThreadPermission("can_upload_attach") && !empty($thread_info['post_items'][$post_data['post_id']]['post_attachments'])) { // need id
                        $a_result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".intval($post_data['post_id'])."' AND thread_id='".intval($thread_data['thread_id'])."'");
                        $attach_rows = dbrows($a_result);
                        if ($attach_rows > 0) {
                            while ($a_data = dbarray($a_result)) {
                                $attachments[] = $a_data;
                            }
                        }
                    }

                    $info = [
                        'title'             => $locale['forum_0507'],
                        'description'       => $locale['forum_2000'].$thread_data['thread_subject'],
                        'openform'          => openform('input_form', 'post', $form_action, ['enctype' => $thread->getThreadPermission("can_upload_attach") ? TRUE : FALSE]),
                        'closeform'         => closeform(),
                        'forum_id_field'    => form_hidden('forum_id', "", $post_data['forum_id']),
                        'thread_id_field'   => form_hidden('thread_id', "", $post_data['thread_id']),
                        'tags_field'        => $is_first_post ? form_select('thread_tags[]', $locale['forum_tag_0100'], $thread_data['thread_tags'],
                            [
                                'options'     => $this->tag()->get_TagOpts(),
                                'inner_width' => '100%',
                                'multiple'    => TRUE,
                                'delimiter'   => '.',
                                'max_select'  => 3, // to do settings on this
                            ]) : "",
                        "forum_field"       => "",
                        'subject_field'     => $thread_info['post_firstpost'] == $_GET['post_id'] ?
                            form_text('thread_subject', $locale['forum_0051'], $thread_data['thread_subject'],
                                ['required'    => TRUE,
                                 'placeholder' => $locale['forum_2001'],
                                 "class"       => 'm-t-20 m-b-20'])
                            : form_hidden("thread_subject", "", $thread_data['thread_subject']),
                        'message_field'     => form_textarea('post_message', $locale['forum_0601'], $post_data['post_message'],
                            ['required'  => TRUE,
                             'height'    => '300px',
                             'preview'   => TRUE,
                             'no_resize' => FALSE,
                             'form_name' => 'input_form',
                             'bbcode'    => TRUE
                            ]),
                        // happens only in EDIT
                        'delete_field'      => form_checkbox('delete', $locale['forum_0624'], "", ['class' => 'm-b-10', 'type' => 'button', 'button_class' => 'btn-danger']),
                        'edit_reason_field' => form_text('post_editreason', $locale['forum_0611'], $post_data['post_editreason'], ['placeholder' => "", 'class' => 'm-t-20 m-b-20']),
                        'attachment_field'  => $thread->getThreadPermission("can_upload_attach") ?
                            form_fileinput('file_attachments[]', $locale['forum_0557'], "",
                                ['input_id'    => 'file_attachments',
                                 'upload_path' => FORUM.'attachments/',
                                 'type'        => 'object',
                                 'template'    => 'modern',
                                 'multiple'    => TRUE,
                                 'inline'      => FALSE,
                                 'max_count'   => $attach_rows > 0 ? $forum_settings['forum_attachmax_count'] - $attach_rows : $forum_settings['forum_attachmax_count'],
                                 'max_byte'    => $forum_settings['forum_attachmax'],
                                 'valid_ext'   => $forum_settings['forum_attachtypes']])."
                                                         <div class='m-b-20'>\n<small>".sprintf($locale['forum_0559'], parsebytesize($forum_settings['forum_attachmax']), str_replace('|', ', ', $forum_settings['forum_attachtypes']), $forum_settings['forum_attachmax_count'])."</small>\n</div>\n"
                            : "",
                        // only happens during edit on first post or new thread AND has poll -- info['forum_poll'] && checkgroup($info['forum_poll']) && ($data['edit'] or $data['new']
                        "poll_form"         => "",
                        'notify_field'      => "",
                        'last_posts_reply'  => "",

                        'smileys_field'     => form_checkbox('post_smileys', $locale['forum_0169'], $post_data['post_smileys'], ['class' => 'm-b-10', 'type' => 'button', 'ext_tip' => $locale['forum_0622']]),
                        'signature_field'   => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', $locale['forum_0264'], $post_data['post_showsig'], ['class' => 'm-b-10', 'type' => 'button', 'ext_tip' => $locale['forum_0170']]) : "",
                        //sticky only in new thread or edit first post
                        'sticky_field'      => ((iMOD || iSUPERADMIN) && $is_first_post) ? form_checkbox('thread_sticky', $locale['forum_0262'], $thread_data['thread_sticky'], ['class' => 'm-b-10', 'type' => 'button', 'ext_tip' => $locale['forum_0620']]) : "",
                        'lock_field'        => (iMOD || iSUPERADMIN) ? form_checkbox('thread_locked', $locale['forum_0621'], $thread_data['thread_locked'], ['class' => 'm-b-10', 'type' => 'button', 'button_class' => 'btn-warning']) : "",
                        'hide_edit_field'   => form_checkbox('hide_edit', $locale['forum_0627'], (!empty($post_data['post_editreason']) && empty($post_data['post_edittime']) ? 1 : 0), ['class' => 'm-b-10', 'type' => 'button']),
                        // available in edit mode
                        'post_locked_field' => (iMOD || iSUPERADMIN) ? form_checkbox('post_locked', $locale['forum_0628'], $post_data['post_locked'], ['class' => 'm-b-10', 'reverse_label' => TRUE]) : "",
                        // not available in edit mode.
                        'post_buttons'      => form_button('post_edit', $locale['forum_0507'], $locale['forum_0507'], ['class' => 'btn-warning m-b-10']).form_button('cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-default m-l-10 m-b-10']),
                    ];
                    $a_info = "";
                    if (!empty($attachments)) {
                        foreach ($attachments as $a_data) {
                            $a_info .= form_checkbox("delete_attach_".$a_data['attach_id'],
                                $locale['forum_0625'], 0,
                                [
                                    "reverse_label" => TRUE,
                                    "ext_tip"       => "<a href='".FORUM."attachments/".$a_data['attach_name']."'>".$a_data['attach_name']."</a> [".parsebytesize($a_data['attach_size'])."]"
                                ]);
                        }

                        $info['attachment_field'] = $a_info.$info['attachment_field'];
                    }

                    return display_forum_postform($info);

                } else {
                    redirect($default_redirect); // no access
                }
            } else {
                redirect(FORUM."postify.php?post=edit&error=4&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&post_id=".$_GET['post_id']);
            }
        } else {
            redirect($default_redirect);
        }

        return NULL;
    }

    /**
     * Attachment download request
     */
    public static function check_download_request() {
        $locale = fusion_get_locale("", FORUM_LOCALE);
        if (isset($_GET['getfiles']) && isnum($_GET['getfiles'])) {
            $result = dbquery("SELECT attach_id, attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE attach_id='".$_GET['getfiles']."'");
            if (dbrows($result)) {
                $data = dbarray($result);
                if (file_exists(FORUM."attachments/".$data['attach_name'])) {
                    dbquery("UPDATE ".DB_FORUM_ATTACHMENTS." SET attach_count=attach_count+1 WHERE attach_id='".$data['attach_id']."'");
                    require_once INCLUDES."class.httpdownload.php";
                    $object = new httpdownload();
                    $object->set_byfile(FORUM."attachments/".$data['attach_name']);
                    $object->use_resume = TRUE;
                    $object->download();
                    exit;
                } else {
                    addNotice("warning", $locale['forum_0398']);
                }
            }
        }
    }

    /**
     * Validate whether a specific user has visited the thread.
     * Duration : 7 days
     *
     * @param $thread_id
     */
    private static function increment_thread_views($thread_id) {
        $days_to_keep_session = 7;
        if (!isset($_SESSION['thread'][$thread_id])) {
            $_SESSION['thread'][$thread_id] = time();
            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_views=thread_views+1 WHERE thread_id='".intval($thread_id)."'");
        } else {
            $time = $_SESSION['thread'][$thread_id];
            if ($time <= time() - ($days_to_keep_session * 3600 * 24)) {
                unset($_SESSION['thread'][$thread_id]);
            }
        }
    }

    /**
     * Get participated users - parsing
     *
     * @param $info
     *
     * @return array
     */
    public function get_participated_users($info) {
        $user = [];
        $result = dbquery("SELECT u.user_id, u.user_name, u.user_status, u.user_avatar, count(p.post_id) 'post_count'
                FROM ".DB_FORUM_POSTS." p
                INNER JOIN ".DB_USERS." u ON (u.user_id=p.post_author)
                WHERE p.forum_id='".intval($info['thread']['forum_id'])."' AND p.thread_id='".intval($info['thread']['thread_id'])."' GROUP BY user_id");
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $user[$data['user_id']] = $data;
            }
        }

        return $user;
    }

    private function set_ThreadJs() {
        $viewthread_js = "";
        //javascript to footer
        $highlight_js = "";
        $colorbox_js = "";
        $edit_reason_js = "";
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
            add_to_footer("<script type='text/javascript' src='".INCLUDES."jquery/jquery.highlight.js'></script>");
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

        // below functions could be made more unobtrusive thanks to jQuery, giving a more accessible cms
        $viewthread_js .= "function jumpforum(forum_id){";
        $viewthread_js .= "document.location.href='".INFUSIONS."forum/viewforum.php?forum_id='+forum_id;";
        $viewthread_js .= "}";

        add_to_jquery($viewthread_js);
    }
}
