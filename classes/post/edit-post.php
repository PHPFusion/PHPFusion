<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: post/edit-post.php
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
namespace PHPFusion\Infusions\Forum\Classes\Post;

use PHPFusion\Infusions\Forum\Classes\Threads\Thread_Logs;
use PHPFusion\Infusions\Forum\Classes\Threads\View_Thread;

class Edit_Post {

    private $viewer = NULL;

    /**
     * Edit_Post constructor.
     *
     * @param View_Thread $obj
     */
    public function __construct(View_Thread $obj) {
        $this->viewer = $obj;
    }

    /*
     * Displays Edit a Thread Post Form
     * Template: function display_forum_postform
     */
    public function render_edit_form() {

        $locale = fusion_get_locale();

        $userdata = fusion_get_userdata();

        $settings = fusion_get_settings();

        $thread = $this->viewer::thread();

        $thread_info = $thread->getInfo();

        $forum_settings = $this->viewer->get_forum_settings();

        $thread_data = $thread_info['thread'];

        $thread_data['thread_author'] = $thread_data['thread_author']['user_id'];

        // Fix tags info
        if (!empty($thread_data['thread_tags'])) {
            $thread_tags = array_keys($thread_data['thread_tags']);
            $thread_data['thread_tags'] = implode('.', $thread_tags);
        }

        // Get error redirect uri
        $default_redirect = FORUM."index.php";
        if ($settings['site_seo']) {
            $default_redirect = $settings['siteurl']."infusions/forum/index.php";
        }

        if ((!iMOD or !iSUPERADMIN) && $thread_data['thread_locked'])
            redirect(INFUSIONS.'forum/index.php');

        $post_id = get('post_id', FILTER_VALIDATE_INT);

        if ($post_id) {

            add_to_title($locale['global_201'].$locale['forum_0360']);

            add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $locale['forum_0360']]);

            $result = dbquery("SELECT tp.*, tt.thread_subject, tt.thread_poll, tt.thread_author, tt.thread_locked, MIN(tp2.post_id) AS first_post
                FROM ".DB_FORUM_POSTS." tp
                INNER JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id=tt.thread_id
                INNER JOIN ".DB_FORUM_POSTS." tp2 ON tp.thread_id=tp2.thread_id
                WHERE tp.post_id=:pid AND tp.thread_id=:tid AND tp.forum_id=:fid
                GROUP BY tp2.post_id
                ", [
                    ':pid' => $post_id,
                    ':tid' => $thread_data['thread_id'],
                    ':fid' => $thread_data['forum_id']
                ]
            );

            // Permission to edit
            if (dbrows($result) > 0) {

                $post_data = dbarray($result);

                if ((iMOD or iSUPERADMIN) || ($thread->getThreadPermission('can_reply') && $post_data['post_author'] == $userdata['user_id'])) {

                    $is_first_post = ($post_data['post_id'] == $thread_info['post_firstpost']) ? TRUE : FALSE;

                    // no edit if locked
                    if ($post_data['post_locked'] && !iMOD) {
                        redirect(FORUM."postify.php?post=edit&error=5&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&post_id=".$post_data['post_id']);
                    }

                    // no edit if time limit reached
                    if (!iMOD && ($forum_settings['forum_edit_timelimit'] > 0 && (TIME - $forum_settings['forum_edit_timelimit'] * 60) > $post_data['post_datestamp'])) {
                        redirect(FORUM."postify.php?post=edit&error=6&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&post_id=".$post_data['post_id']);
                    }

                    // Edit $_POST
                    $post_cancel = post('cancel');
                    $post_edit = post('post_edit');

                    if ($post_cancel) {

                        if (empty($thread_data['thread_id'])) {
                            redirect(FORUM.'index.php');
                        }

                        if (fusion_get_settings("site_seo")) {
                            redirect(fusion_get_settings("siteurl")."infusions/forum/viewthread.php?thread_id=".$thread_data['thread_id']);
                        }

                        redirect(FORUM.'viewthread.php?thread_id='.$thread_data['thread_id']);

                    } else if ($post_edit) {

                        require_once INCLUDES."flood_include.php";

                        if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice

                            $post_message = post('post_message');

                            $post_data = [
                                'forum_id'        => $thread_data['forum_id'],
                                'thread_id'       => $thread_data['thread_id'],
                                'post_id'         => $post_data['post_id'],
                                'post_message'    => censorwords(sanitizer('post_message', '', 'post_message')),
                                'post_showsig'    => post('post_showsig', FILTER_VALIDATE_INT) ? 1 : 0,
                                'post_smileys'    => post('post_smileys') || $post_message && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $post_message) ? 1 : 0,
                                'post_author'     => $post_data['post_author'],
                                'post_datestamp'  => $post_data['post_datestamp'], // update on datestamp or not?
                                'post_ip'         => USER_IP,
                                'post_ip_type'    => USER_IP_TYPE,
                                'post_edituser'   => $userdata['user_id'],
                                'post_edittime'   => post('hide_edit') ? 0 : TIME,
                                'post_editreason' => censorwords(sanitizer('post_editreason', '', 'post_editreason')),
                                'post_hidden'     => 0,
                                'notify_me'       => 0,
                                'post_locked'     => $forum_settings['forum_edit_lock'] || post('thread_locked', FILTER_VALIDATE_INT) ? 1 : 0
                            ];

                            $thread_subject = sanitizer('thread_subject', '', 'thread_subject');

                            $thread_tags = sanitizer(['thread_tags'], '', 'thread_tags[]');

                            // require thread_subject if first post
                            if ($is_first_post) {

                                // Log thread actions
                                $thread_logs = new Thread_Logs($thread_data['thread_id']);

                                $thread_logs->doLogAction('subject', $thread_data['thread_subject'], $thread_subject);

                                $thread_logs->doLogAction('tags', $thread_data['thread_tags'], $thread_tags);

                                $thread_logs->doLogAction('lock', $thread_data['thread_locked'], $post_data['post_locked']);

                                $thread_logs->doLogAction('sticky', $thread_data['thread_locked'], $post_data['post_locked']);

                                $thread_data['thread_tags'] = $thread_tags;

                                $thread_data['thread_subject'] = $thread_subject;

                                $thread_data['thread_locked'] = $post_data['post_locked'];

                                $thread_data['thread_sticky'] = isset($_POST['thread_sticky']) ? 1 : 0;

                                // Update thread
                                dbquery_insert(DB_FORUM_THREADS, $thread_data, "update", ["keep_session" => TRUE]);
                            }

                            // If post delete is checked
                            if (post('delete')) {
                                $this->deletePostEdit($post_data['post_id'], $post_data['thread_id'], $post_data['forum_id'], $is_first_post);
                            }

                            if (fusion_safe()) {

                                // Handle file attachments
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

                                if (fusion_safe()) {

                                    // Do forum post merging actions
                                    $last_post_author = dbarray(dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread_data['thread_id']."' ORDER BY post_id DESC LIMIT 1"));

                                    if ($last_post_author == $post_data['post_author'] && $thread_data['forum_merge']) {

                                        $last_message = dbarray(dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread_data['thread_id']."' ORDER BY post_id DESC"));

                                        $post_data['post_id'] = $last_message['post_id'];

                                        $post_data['post_message'] = $last_message['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate", time()).":\n".$post_data['post_message'];

                                        dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', ['primary_key' => 'post_id', 'keep_session' => TRUE]);

                                    } else {

                                        dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', ['primary_key' => 'post_id', 'keep_session' => TRUE]);

                                    }

                                    redirect(FORUM."postify.php?post=edit&error=0&amp;forum_id=".$post_data['forum_id']."&amp;thread_id=".$post_data['thread_id']."&amp;post_id=".$post_data['post_id']);
                                }
                            }
                        } // end of flood control
                    }

                    // template data
                    $form_action = FORUM."viewthread.php?action=edit&amp;forum_id=".$thread_data['forum_id']."&amp;thread_id=".$thread_data['thread_id']."&amp;post_id=".$_GET['post_id'];

                    // get attachment.
                    $attachments = [];
                    $attach_rows = 0;

                    // Check attachments in the form
                    if ($thread->getThreadPermission('can_upload_attach') && !empty($thread_info['post_items'][$post_data['post_id']]['post_attachments'])) { // need id

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
                                'options'     => $this->viewer->tag()->get_TagOpts(),
                                'inner_width' => '100%',
                                'multiple'    => TRUE,
                                'delimiter'   => '.',
                                'max_select'  => 3, // to do settings on this
                            ]) : "",
                        "forum_field"       => '',
                        'subject_field'     => $thread_info['post_firstpost'] == $_GET['post_id'] ?
                            form_text('thread_subject', $locale['forum_0051'], $thread_data['thread_subject'],
                                ['required'    => TRUE,
                                 'placeholder' => $locale['forum_2001'],
                                 "class"       => 'm-t-20 m-b-20'])
                            : form_hidden("thread_subject", "", $thread_data['thread_subject']),
                        'message_field'     => form_textarea('post_message', $locale['forum_0601'], $post_data['post_message'],
                            [
                                'required'  => TRUE,
                                'preview'   => TRUE,
                                'form_name' => 'input_form',
                                'tab'       => TRUE,
                                'input_id'  => 'thread-edit-form',
                                'bbcode'    => TRUE,
                                'height'    => '500px',
                                'grippie'   => TRUE,
                                'tab' => TRUE,
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
                                 'valid_ext'   => $forum_settings['forum_attachtypes']])."<div class='m-b-20'>\n<small>".sprintf($locale['forum_0559'], parsebytesize($forum_settings['forum_attachmax']), str_replace('|', ', ', $forum_settings['forum_attachtypes']), $forum_settings['forum_attachmax_count'])."</small>\n</div>\n"
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

                    $a_info = '';
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
     * Delete a post when editing a post
     *
     * @param $post_id
     * @param $thread_id
     * @param $forum_id
     * @param $is_first_post
     */
    private function deletePostEdit($post_id, $thread_id, $forum_id, $is_first_post) {

        if (isnum($post_id) && isnum($thread_id) && isnum($forum_id)) {

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

}
