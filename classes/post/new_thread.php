<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: new_thread.php
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
namespace PHPFusion\Forums\Post;

use PHPFusion\BreadCrumbs;
use PHPFusion\Forums\ForumServer;

class NewThread extends ForumServer {

    /**
     * Set user permission based on current forum configuration
     *
     * @param $forum_data
     */
    private static $permissions = [];
    private static $locale = [];
    public $info = [];

    public function __construct() {
        self::$locale = fusion_get_locale('', [FORUM_LOCALE, FORUM_TAGS_LOCALE]);
    }

    /**
     * New thread
     */
    public function set_newThreadInfo() {

        $userdata = fusion_get_userdata();
        $forum_settings = self::get_forum_settings();

        // @todo: Reduce lines and optimize further
        if (iMEMBER) {
            // New thread directly to a specified forum
            if (!empty($_GET['forum_id']) && isnum($_GET['forum_id']) && ForumServer::verify_forum($_GET['forum_id'])) {

                add_to_title(self::$locale['forum_0000'].self::$locale['global_201'].self::$locale['forum_0057']);
                add_to_meta("description", self::$locale['forum_0000']);
                BreadCrumbs::getInstance()->addBreadCrumb(["link" => FORUM."index.php", "title" => self::$locale['forum_0000']]);

                $forum_data = dbarray(dbquery("SELECT f.*, f2.forum_name AS forum_cat_name
                FROM ".DB_FORUMS." f
                LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
                WHERE f.forum_id='".intval($_GET['forum_id'])."'
                AND ".groupaccess('f.forum_access')."
                "));

                if ($forum_data['forum_type'] == 1 or $forum_data['forum_lock']) {
                    redirect(INFUSIONS.'forum/index.php');
                }

                $forum_data['lock_edit'] = $forum_settings['forum_edit_lock'];

                self::setPermission($forum_data);

                if (self::getPermission('can_post') && self::getPermission('can_access')) {
                    BreadCrumbs::getInstance()->addBreadCrumb([
                        'link'  => INFUSIONS.'forum/index.php?viewforum&amp;forum_id='.$forum_data['forum_id'],
                        'title' => $forum_data['forum_name']
                    ]);
                    BreadCrumbs::getInstance()->addBreadCrumb([
                        'link'  => INFUSIONS.'forum/index.php?viewforum&amp;forum_id='.$forum_data['forum_id'],
                        'title' => self::$locale['forum_0057']
                    ]);

                    /**
                     * Generate a poll form
                     */
                    $poll_form = '';
                    if (self::getPermission('can_create_poll')) {
                        // initial data to push downwards
                        $pollData = [
                            'thread_id'         => 0,
                            'forum_poll_title'  => (!empty($_POST['forum_poll_title']) ? form_sanitizer($_POST['forum_poll_title'], '', 'forum_poll_title') : ''),
                            'forum_poll_start'  => TIME, // time poll started
                            'forum_poll_length' => 2, // how many poll options we have
                            'forum_poll_votes'  => 0, // how many vote this poll has
                        ];
                        // counter of lengths
                        $option_data[1] = '';
                        $option_data[2] = '';
                        // Do a validation if checked add_poll
                        if (isset($_POST['add_poll'])) {
                            $pollData = [
                                'thread_id'         => 0,
                                'forum_poll_title'  => isset($_POST['forum_poll_title']) ? form_sanitizer($_POST['forum_poll_title'], '', 'forum_poll_title') : '',
                                'forum_poll_start'  => TIME, // time poll started
                                'forum_poll_length' => count($option_data), // how many poll options we have
                                'forum_poll_votes'  => 0, // how many vote this poll has
                            ];
                            // calculate poll lengths
                            if (!empty($_POST['poll_options']) && is_array($_POST['poll_options'])) {
                                foreach ($_POST['poll_options'] as $i => $value) {
                                    $option_data[$i] = form_sanitizer($value, '', "poll_options[$i]");
                                }
                            }
                        }

                        if (isset($_POST['add_poll_option']) && isset($_POST['poll_options'])) {
                            // reindex the whole array with blank values.
                            foreach ($_POST['poll_options'] as $i => $value) {
                                $option_data[$i] = form_sanitizer($value, '', "poll_options[$i]");
                            }

                            if (\defender::safe()) {
                                $option_data = array_values(array_filter($option_data));
                                array_unshift($option_data, NULL);
                                unset($option_data[0]);
                                $pollData['forum_poll_length'] = count($option_data);
                            }
                            array_push($option_data, '');
                        }
                        $poll_field = [];
                        $poll_field['poll_field'] = form_text('forum_poll_title', self::$locale['forum_0604'],
                            $pollData['forum_poll_title'], [
                                'max_length'  => 255,
                                'placeholder' => self::$locale['forum_0604a'],
                                'inline'      => TRUE,
                                //'required'    => TRUE
                            ]);
                        for ($i = 1; $i <= count($option_data); $i++) {
                            $poll_field['poll_field'] .= form_text("poll_options[$i]", sprintf(self::$locale['forum_0606'], $i),
                                $option_data[$i], [
                                    'max_length'  => 255,
                                    'placeholder' => self::$locale['forum_0605'],
                                    'inline'      => TRUE,
                                    //'required'    => $i <= 2 ? TRUE : FALSE
                                ]);
                        }
                        $poll_field['poll_field'] .= "<div class='col-xs-12 col-sm-offset-3'>\n";
                        $poll_field['poll_field'] .= form_button('add_poll_option', self::$locale['forum_0608'],
                            self::$locale['forum_0608'], ['class' => 'btn-primary']);
                        $poll_field['poll_field'] .= "</div>\n";
                        $info = [
                            'title'       => self::$locale['forum_0366'],
                            'description' => self::$locale['forum_0630'],
                            'field'       => $poll_field
                        ];
                        $poll_form = form_checkbox("add_poll", self::$locale['forum_0366'], isset($_POST['add_poll']) ? TRUE : FALSE, ['reverse_label' => TRUE]);
                        $poll_form .= "<div id='poll_form' class='poll-form' style='display:none;'>\n";
                        $poll_form .= "<div class='well clearfix'>\n";
                        $poll_form .= "<!--pre_form-->\n";
                        $poll_form .= $info['field']['poll_field'];
                        $poll_form .= "</div>\n";
                        $poll_form .= "</div>\n";
                    }

                    $thread_data = [
                        'forum_id'          => $forum_data['forum_id'],
                        'thread_id'         => 0,
                        'thread_subject'    => isset($_POST['thread_subject']) ? form_sanitizer($_POST['thread_subject'], '', 'thread_subject') : '',
                        'thread_tags'       => isset($_POST['thread_tags']) ? form_sanitizer($_POST['thread_tags'], '', 'thread_tags') : '',
                        'thread_author'     => $userdata['user_id'],
                        'thread_views'      => 0,
                        'thread_lastpost'   => time(),
                        'thread_lastpostid' => 0, // need to run update
                        'thread_lastuser'   => $userdata['user_id'],
                        'thread_postcount'  => 1, // already insert 1 postcount.
                        'thread_poll'       => 0,
                        'thread_sticky'     => isset($_POST['thread_sticky']) ? 1 : 0,
                        'thread_locked'     => isset($_POST['thread_sticky']) ? 1 : 0,
                        'thread_hidden'     => 0,
                    ];

                    $post_data = [
                        'forum_id'        => $forum_data['forum_id'],
                        'forum_cat'       => $forum_data['forum_cat'],
                        'thread_id'       => 0,
                        'post_id'         => 0,
                        'post_message'    => isset($_POST['post_message']) ? form_sanitizer($_POST['post_message'], '', 'post_message') : '',
                        'post_showsig'    => isset($_POST['post_showsig']) ? 1 : 0,
                        'post_smileys'    => !isset($_POST['post_smileys']) || isset($_POST['post_message']) && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? 0 : 1,
                        'post_author'     => $userdata['user_id'],
                        'post_datestamp'  => time(),
                        'post_ip'         => USER_IP,
                        'post_ip_type'    => USER_IP_TYPE,
                        'post_edituser'   => 0,
                        'post_edittime'   => 0,
                        'post_editreason' => '',
                        'post_hidden'     => 0,
                        'notify_me'       => isset($_POST['notify_me']) ? 1 : 0,
                        'post_locked'     => 0,
                    ];

                    // Execute post new thread
                    if (isset($_POST['post_newthread']) && \defender::safe()) {

                        require_once INCLUDES."flood_include.php";

                        // all data is sanitized here.
                        if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) {

                            if (\defender::safe()) {

                                // create a new thread.
                                $last_thread_id = dbquery_insert(DB_FORUM_THREADS, $thread_data, 'save', [
                                    'primary_key'  => 'thread_id',
                                    'keep_session' => TRUE
                                ]);

                                $post_data['thread_id'] = $last_thread_id;
                                $pollData['thread_id'] = $last_thread_id;

                                $post_data['post_id'] = dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', [
                                    'primary_key'  => 'post_id',
                                    'keep_session' => TRUE
                                ]);

                                // Attach files if permitted
                                if (!empty($_FILES) && is_uploaded_file($_FILES['file_attachments']['tmp_name'][0]) && self::getPermission("can_upload_attach")) {
                                    $upload = form_sanitizer($_FILES['file_attachments'], '', 'file_attachments');
                                    if ($upload['error'] == 0) {
                                        foreach ($upload['target_file'] as $arr => $file_name) {
                                            $attach_data = [
                                                'thread_id'    => $post_data['thread_id'],
                                                'post_id'      => $post_data['post_id'],
                                                'attach_name'  => $file_name,
                                                'attach_mime'  => $upload['type'][$arr],
                                                'attach_size'  => $upload['source_size'][$arr],
                                                'attach_count' => '0', // downloaded times
                                            ];
                                            dbquery_insert(DB_FORUM_ATTACHMENTS, $attach_data, "save", ['keep_session' => TRUE]);
                                        }
                                    }
                                }

                                dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".intval($post_data['post_author'])."'");

                                // update current thread
                                dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=".intval($post_data['post_datestamp']).", thread_lastpostid=".intval($post_data['post_id']).", thread_lastuser=".intval($post_data['post_author'])." WHERE thread_id=".intval($post_data['thread_id']));

                                // this is a new forum threadcount
                                // find all parents and update them
                                $list_of_forums = get_all_parent(dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), $post_data['forum_id']);

                                if (!empty($list_of_forums)) {
                                    foreach ($list_of_forums as $forum_id) {
                                        list($forum_postcount, $forum_threadcount) = dbarraynum(
                                            dbquery("SELECT forum_postcount, forum_threadcount FROM ".DB_FORUMS." WHERE forum_id='".$forum_id."'")
                                        );
                                        $forum_postcount++;
                                        $forum_threadcount++;
                                        $update_forum_sql = "
                                        UPDATE ".DB_FORUMS." SET
                                        forum_lastpost='".intval($post_data['post_datestamp'])."',
                                        forum_postcount = '$forum_postcount',
                                        forum_threadcount = '$forum_threadcount',
                                        forum_lastpostid = '".intval($post_data['post_id'])."',
                                        forum_lastuser='".intval($post_data['post_author'])."'
                                        WHERE forum_id='$forum_id'
                                        ";
                                        dbquery($update_forum_sql);
                                    }
                                }

                                $forum_postcount = $forum_data['forum_postcount'] + 1;
                                $forum_threadcount = $forum_data['forum_threadcount'] + 1;
                                $update_forum_sql = "
                                        UPDATE ".DB_FORUMS." SET
                                        forum_lastpost='".intval($post_data['post_datestamp'])."',
                                        forum_postcount = '$forum_postcount',
                                        forum_threadcount = '$forum_threadcount',
                                        forum_lastpostid = '".intval($post_data['post_id'])."',
                                        forum_lastuser ='".intval($post_data['post_author'])."'
                                        WHERE forum_id='".intval($post_data['forum_id'])."'
                                        ";
                                dbquery($update_forum_sql);

                                // set notify
                                if ($forum_settings['thread_notify'] && isset($_POST['notify_me']) && $post_data['thread_id']) {
                                    if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$post_data['thread_id']."' AND notify_user='".$post_data['post_author']."'")
                                    ) {
                                        $notice_array = [
                                            'thread_id'        => $post_data['thread_id'],
                                            'notify_datestamp' => $post_data['post_datestamp'],
                                            'notify_user'      => $post_data['post_author'],
                                            'notify_status'    => 1,
                                        ];
                                        dbquery_insert(DB_FORUM_THREAD_NOTIFY, $notice_array, 'save');
                                    }
                                }

                                // Add poll if exist
                                if (!empty($option_data) && isset($_POST['add_poll'])) {
                                    dbquery_insert(DB_FORUM_POLLS, $pollData, 'save');
                                    $poll_option_data['thread_id'] = $pollData['thread_id'];
                                    $i = 1;
                                    foreach ($option_data as $option_text) {
                                        if ($option_text) {
                                            $poll_option_data['forum_poll_option_id'] = $i;
                                            $poll_option_data['forum_poll_option_text'] = $option_text;
                                            $poll_option_data['forum_poll_option_votes'] = 0;
                                            dbquery_insert(DB_FORUM_POLL_OPTIONS, $poll_option_data, 'save');
                                            $i++;
                                        }
                                    }
                                    dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_poll='1' WHERE thread_id='".$pollData['thread_id']."'");
                                }
                            }
                            if (\defender::safe()) {
                                redirect(INFUSIONS."forum/postify.php?post=new&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;thread_id=".intval($post_data['thread_id'].""));
                            }
                        }
                    }

                    $this->info = [
                        'title'             => self::$locale['forum_0057'],
                        'description'       => '',
                        'openform'          => openform('input_form', 'post', clean_request('forum_id='.$post_data['forum_id'], ['forum_id'], FALSE), ['enctype' => self::getPermission("can_upload_attach")]),
                        'closeform'         => closeform(),
                        'forum_id_field'    => '',
                        'thread_id_field'   => '',
                        "forum_field"       => "",
                        'subject_field'     => form_text('thread_subject', self::$locale['forum_0051'], $thread_data['thread_subject'],
                            [
                                'required'    => 1,
                                'placeholder' => self::$locale['forum_2001'],
                                'error_text'  => '',
                                'class'       => 'm-t-20 m-b-20'
                            ]),
                        'tags_field'        => form_select('thread_tags[]', self::$locale['forum_tag_0100'], $thread_data['thread_tags'],
                            [
                                'options'     => parent::tag()->get_TagOpts(TRUE),
                                'inner_width' => '100%',
                                'multiple'    => TRUE,
                                'delimiter'   => '.',
                                'max_select'  => 3, // to do settings on this
                            ]),
                        'message_field'     => form_textarea('post_message', self::$locale['forum_0601'], $post_data['post_message'],
                            [
                                'required'   => 1,
                                'error_text' => '',
                                'preview'    => 1,
                                'form_name'  => 'input_form',
                                'bbcode'     => 1,
                                'height'     => '500px'
                            ]),
                        'attachment_field'  => self::getPermission("can_upload_attach") ?
                            form_fileinput('file_attachments[]',
                                self::$locale['forum_0557'],
                                '', [
                                    'input_id'       => 'file_attachments',
                                    'upload_path'    => INFUSIONS.'forum/attachments/',
                                    'type'           => 'object',
                                    'preview_off'    => TRUE,
                                    'multiple'       => TRUE,
                                    'inline'         => FALSE,
                                    'max_count'      => $forum_settings['forum_attachmax_count'],
                                    'valid_ext'      => $forum_settings['forum_attachtypes'],
                                    'class'          => 'm-b-0',
                                    'replace_upload' => TRUE,
                                    'max_byte'       => $forum_settings['forum_attachmax'],
                                ]
                            )." <div class='m-b-20'>\n<small>
                            ".sprintf(self::$locale['forum_0559'], parsebytesize($forum_settings['forum_attachmax']), str_replace('|', ', ', $forum_settings['forum_attachtypes']), $forum_settings['forum_attachmax_count'])."</small>\n</div>\n" : '',
                        'poll_form'         => $poll_form,
                        'smileys_field'     => form_checkbox('post_smileys', self::$locale['forum_0622'], $post_data['post_smileys'], ['class' => 'm-b-0', 'reverse_label' => TRUE]),
                        'signature_field'   => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', self::$locale['forum_0170'], $post_data['post_showsig'], ['class' => 'm-b-0', 'reverse_label' => TRUE]) : '',
                        'sticky_field'      => (iMOD || iSUPERADMIN) ? form_checkbox('thread_sticky', self::$locale['forum_0620'], $thread_data['thread_sticky'], ['class' => 'm-b-0', 'reverse_label' => TRUE]) : '',
                        'lock_field'        => (iMOD || iSUPERADMIN) ? form_checkbox('thread_locked', self::$locale['forum_0621'], $thread_data['thread_locked'], ['class' => 'm-b-0', 'reverse_label' => TRUE]) : '',
                        'edit_reason_field' => '',
                        'delete_field'      => '',
                        'hide_edit_field'   => '',
                        'post_locked_field' => '',
                        'notify_field'      => $forum_settings['thread_notify'] ? form_checkbox('notify_me', self::$locale['forum_0171'], $post_data['notify_me'], ['class' => 'm-b-0', 'reverse_label' => TRUE]) : '',
                        'post_buttons'      => form_button('post_newthread', self::$locale['forum_0057'], self::$locale['forum_0057'], ['class' => 'btn-primary ']).form_button('cancel', self::$locale['cancel'], self::$locale['cancel'], ['class' => 'btn-default  m-l-10']),
                        'last_posts_reply'  => '',
                    ];
                    // add a jquery to toggle the poll form
                    add_to_jquery("
                        if ($('#add_poll').is(':checked')) {
                            $('#poll_form').show();
                        } else {
                            $('#poll_form').hide();
                        }
                        $('#add_poll').bind('click', function() {
                            if ($(this).is(':checked')) {
                                $('#poll_form').slideDown();
                            } else {
                                $('#poll_form').slideUp();
                            }
                        });
                    ");

                } else {
                    redirect(FORUM.'index.php');
                }

            } else {
                /*
                 * Quick New Forum Posting.
                 * Does not require to run permissions.
                 * Does not contain forum poll.
                 * Does not contain attachment
                 */
                if (!dbcount("(forum_id)", DB_FORUMS, "forum_type !='1'")) {
                    redirect(FORUM.'index.php');
                }
                if (!dbcount("(forum_id)", DB_FORUMS, in_group('forum_language', LANGUAGE))) {
                    redirect(FORUM.'index.php');
                }
				if (isset($_GET['forum_id']) && !isnum($_GET['forum_id'])) {
                    redirect(FORUM.'index.php');
				}

                BreadCrumbs::getInstance()->addBreadCrumb(["link" => FORUM."newthread.php?forum_id=0", "title" => self::$locale['forum_0057']]);
                $thread_data = [
                    'forum_id'          => isset($_POST['forum_id']) ? form_sanitizer($_POST['forum_id'], 0, "forum_id") : 0,
                    'thread_id'         => 0,
                    'thread_subject'    => isset($_POST['thread_subject']) ? form_sanitizer($_POST['thread_subject'], '', 'thread_subject') : '',
                    'thread_tags'       => isset($_POST['thread_tags']) ? form_sanitizer($_POST['thread_tags'], '', 'thread_tags') : '',
                    'thread_author'     => $userdata['user_id'],
                    'thread_views'      => 0,
                    'thread_lastpost'   => time(),
                    'thread_lastpostid' => 0, // need to run update
                    'thread_lastuser'   => $userdata['user_id'],
                    'thread_postcount'  => 1, // already insert 1 postcount.
                    'thread_poll'       => 0,
                    'thread_sticky'     => isset($_POST['thread_sticky']) ? TRUE : FALSE,
                    'thread_locked'     => isset($_POST['thread_sticky']) ? TRUE : FALSE,
                    'thread_hidden'     => 0,
                ];

                $post_data = [
                    'forum_id'        => isset($_POST['forum_id']) ? form_sanitizer($_POST['forum_id'], 0, "forum_id") : 0,
                    "forum_cat"       => 0, // for redirect
                    'thread_id'       => 0, // required lastid
                    'post_id'         => 0, // auto insertion
                    'post_message'    => isset($_POST['post_message']) ? form_sanitizer($_POST['post_message'], '', 'post_message') : '',
                    'post_showsig'    => isset($_POST['post_showsig']) ? TRUE : FALSE,
                    'post_smileys'    => !isset($_POST['post_smileys']) || isset($_POST['post_message']) && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? FALSE : TRUE,
                    'post_author'     => $userdata['user_id'],
                    'post_datestamp'  => time(),
                    'post_ip'         => USER_IP,
                    'post_ip_type'    => USER_IP_TYPE,
                    'post_edituser'   => 0,
                    'post_edittime'   => 0,
                    'post_editreason' => '',
                    'post_hidden'     => 0,
                    'notify_me'       => isset($_POST['notify_me']) ? TRUE : FALSE,
                    'post_locked'     => 0,
                ];

                if (isset($_POST['post_newthread']) && \defender::safe()) {

                    require_once INCLUDES.'flood_include.php';

                    if (!flood_control('post_datestamp', DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) {

                        if (ForumServer::verify_forum($thread_data['forum_id'])) {

                            $forum_data = dbarray(dbquery("SELECT f.*, f2.forum_name AS forum_cat_name
                            FROM ".DB_FORUMS." f
                            LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
                            WHERE f.forum_id=:forum_id
                            AND ".groupaccess('f.forum_access')."
                            ", [':forum_id' => intval($thread_data['forum_id'])]));

                            if ($forum_data['forum_type'] == 1) {
                                redirect(INFUSIONS.'forum/index.php');
                            }

                            // Use the new permission settings
                            self::setPermission($forum_data);

                            $forum_data['lock_edit'] = $forum_settings['forum_edit_lock'];

                            if (self::getPermission("can_post") && self::getPermission("can_access")) {

                                $post_data['forum_cat'] = $forum_data['forum_cat'];

                                $last_thread_id = dbquery_insert(DB_FORUM_THREADS, $thread_data, 'save', [
                                    'primary_key'  => 'thread_id',
                                    'keep_session' => TRUE
                                ]);

                                $post_data['thread_id'] = $last_thread_id;
                                $pollData['thread_id'] = $last_thread_id;

                                $post_data['post_id'] = dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', [
                                    'primary_key'  => 'post_id',
                                    'keep_session' => TRUE
                                ]);

                                // Attach files if permitted
                                if (!empty($_FILES) && is_uploaded_file($_FILES['file_attachments']['tmp_name'][0]) && self::getPermission("can_upload_attach")) {
                                    $upload = form_sanitizer($_FILES['file_attachments'], '', 'file_attachments');
                                    if ($upload['error'] == 0) {
                                        foreach ($upload['target_file'] as $arr => $file_name) {
                                            $attach_data = [
                                                'thread_id'    => $post_data['thread_id'],
                                                'post_id'      => $post_data['post_id'],
                                                'attach_name'  => $file_name,
                                                'attach_mime'  => $upload['type'][$arr],
                                                'attach_size'  => $upload['source_size'][$arr],
                                                'attach_count' => '0', // downloaded times
                                            ];
                                            dbquery_insert(DB_FORUM_ATTACHMENTS, $attach_data, "save", ['keep_session' => TRUE]);
                                        }
                                    }
                                }

                                dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".intval($post_data['post_author'])."'");
                                // update current thread
                                dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=".intval($post_data['post_datestamp']).", thread_lastpostid=".intval($post_data['post_id']).", thread_lastuser=".intval($post_data['post_author'])." WHERE thread_id=".intval($post_data['thread_id']));

                                // Update stats in forum and threads
                                // find all parents and update them
                                $list_of_forums = get_all_parent(dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), $post_data['forum_id']);
                                if (!empty($list_of_forums)) {
                                    foreach ($list_of_forums as $forum_id) {

                                        list($forum_postcount, $forum_threadcount) = dbarraynum(
                                            dbquery("SELECT forum_postcount, forum_threadcount FROM ".DB_FORUMS." WHERE forum_id='".$forum_id."'")
                                        );
                                        $forum_postcount++;
                                        $forum_threadcount++;
                                        $update_forum_sql = "
                                        UPDATE ".DB_FORUMS." SET
                                        forum_lastpost='".intval($post_data['post_datestamp'])."',
                                        forum_postcount = '$forum_postcount',
                                        forum_threadcount = '$forum_threadcount',
                                        forum_lastpostid = '".intval($post_data['post_id'])."',
                                        forum_lastuser='".intval($post_data['post_author'])."'
                                        WHERE forum_id='$forum_id'
                                        ";
                                        dbquery($update_forum_sql);
                                    }
                                }

                                // update current forum
                                list($forum_postcount, $forum_threadcount) = dbarraynum(
                                    dbquery("SELECT forum_postcount, forum_threadcount FROM ".DB_FORUMS." WHERE forum_id='".$post_data['forum_id']."'")
                                );
                                $forum_postcount++;
                                $forum_threadcount++;
                                $update_forum_sql = "
                                        UPDATE ".DB_FORUMS." SET
                                        forum_lastpost='".intval($post_data['post_datestamp'])."',
                                        forum_postcount = '$forum_postcount',
                                        forum_threadcount = '$forum_threadcount',
                                        forum_lastpostid = '".intval($post_data['post_id'])."',
                                        forum_lastuser ='".intval($post_data['post_author'])."'
                                        WHERE forum_id='".intval($post_data['forum_id'])."'
                                        ";
                                dbquery($update_forum_sql);

                                // update current thread
                                dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=".time().", thread_lastpostid='".$post_data['post_id']."', thread_lastuser='".$post_data['post_author']."' WHERE thread_id='".$post_data['thread_id']."'");

                                if (!empty($option_data) && isset($_POST['add_poll'])) {
                                    dbquery_insert(DB_FORUM_POLLS, $pollData, 'save');
                                    $poll_option_data['thread_id'] = $pollData['thread_id'];
                                    $i = 1;
                                    foreach ($option_data as $option_text) {
                                        if ($option_text) {
                                            $poll_option_data['forum_poll_option_id'] = $i;
                                            $poll_option_data['forum_poll_option_text'] = $option_text;
                                            $poll_option_data['forum_poll_option_votes'] = 0;
                                            dbquery_insert(DB_FORUM_POLL_OPTIONS, $poll_option_data, 'save');
                                            $i++;
                                        }
                                    }
                                    dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_poll='1' WHERE thread_id='".$pollData['thread_id']."'");
                                }

                                // set notify
                                if ($forum_settings['thread_notify'] && isset($_POST['notify_me']) && $post_data['thread_id']) {
                                    if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$post_data['thread_id']."' AND notify_user='".$post_data['post_author']."'")
                                    ) {
                                        $notice_array = [
                                            'thread_id'        => $post_data['thread_id'],
                                            'notify_datestamp' => $post_data['post_datestamp'],
                                            'notify_user'      => $post_data['post_author'],
                                            'notify_status'    => 1,
                                        ];
                                        dbquery_insert(DB_FORUM_THREAD_NOTIFY, $notice_array, 'save');
                                    }
                                }

                                if (\defender::safe()) {
                                    redirect(INFUSIONS."forum/postify.php?post=new&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;thread_id=".intval($post_data['thread_id'].""));
                                }

                            } else {
                                addNotice("danger", self::$locale['forum_0186']);
                            }
                        } else {
                            addNotice("danger", self::$locale['forum_0187']);
                            redirect(INFUSIONS."forum/index.php");
                        }
                    }
                }

                //Disable all parents
                $disabled_opts = [];
                $disable_query = "SELECT forum_id FROM ".DB_FORUMS." WHERE forum_type=1 ".(multilang_table("FO") ? "AND ".in_group('forum_language', LANGUAGE) : '');
                $disable_query = dbquery(" $disable_query ");
                if (dbrows($disable_query) > 0) {
                    while ($d_forum = dbarray($disable_query)) {
                        $disabled_opts = $d_forum['forum_id'];
                    }
                }

                $this->info = [
                    'title'             => self::$locale['forum_0057'],
                    'description'       => '',
                    'openform'          => openform('input_form', 'post', FORUM.'newthread.php', ['enctype' => FALSE]),
                    'closeform'         => closeform(),
                    'forum_id_field'    => '',
                    'thread_id_field'   => '',
                    // need to disable all parents
                    'forum_field'       => form_select_tree('forum_id', self::$locale['forum_0395'], $thread_data['forum_id'],
                        [
                            'required'     => TRUE,
                            'width'        => '320px',
                            'no_root'      => TRUE,
                            'disable_opts' => $disabled_opts,
                            'query'        => (multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE) : ''),
                        ],
                        DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat'),
                    'subject_field'     => form_text('thread_subject', self::$locale['forum_0051'], $thread_data['thread_subject'], [
                        'required'    => 1,
                        'placeholder' => self::$locale['forum_2001'],
                        'error_text'  => '',
                        'class'       => 'm-t-20 m-b-20'
                    ]),
                    'tags_field'        => form_select('thread_tags[]', self::$locale['forum_tag_0100'], $thread_data['thread_tags'],
                        [
                            'options'     => parent::tag()->get_TagOpts(),
                            'inner_width' => '100%',
                            'multiple'    => TRUE,
                            'delimiter'   => '.',
                            'max_select'  => 3, // to do settings on this
                        ]),
                    'message_field'     => form_textarea('post_message', self::$locale['forum_0601'], $post_data['post_message'], [
                        'required'  => 1,
                        'autosize'  => 1,
                        'no_resize' => 1,
                        'preview'   => 1,
                        'form_name' => 'input_form',
                        'bbcode'    => 1,
                        'height'    => '300px'
                    ]),
                    'attachment_field'  => "",
                    'poll_form'         => "",
                    'smileys_field'     => form_checkbox('post_smileys', self::$locale['forum_0622'], $post_data['post_smileys'], ['class' => 'm-b-0', 'reverse_label' => TRUE]),
                    'signature_field'   => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', self::$locale['forum_0170'], $post_data['post_showsig'], ['class' => 'm-b-0', 'reverse_label' => TRUE]) : '',
                    'sticky_field'      => (iSUPERADMIN) ? form_checkbox('thread_sticky', self::$locale['forum_0620'], $thread_data['thread_sticky'], ['class' => 'm-b-0', 'reverse_label' => TRUE]) : '',
                    'lock_field'        => (iSUPERADMIN) ? form_checkbox('thread_locked', self::$locale['forum_0621'], $thread_data['thread_locked'], ['class' => 'm-b-0', 'reverse_label' => TRUE]) : '',
                    'edit_reason_field' => '',
                    'delete_field'      => '',
                    'hide_edit_field'   => '',
                    'post_locked_field' => '',
                    'notify_field'      => $forum_settings['thread_notify'] ? form_checkbox('notify_me', self::$locale['forum_0171'], $post_data['notify_me'], ['class' => 'm-b-0', 'reverse_label' => TRUE]) : '',
                    'post_buttons'      => form_button('post_newthread', self::$locale['forum_0057'], self::$locale['forum_0057'], ['class' => 'btn-primary']).form_button('cancel', self::$locale['cancel'], self::$locale['cancel'], ['class' => 'btn-default m-l-10']),
                    'last_posts_reply'  => '',
                ];
            }
        } else {
            redirect(INFUSIONS.'forum/index.php');
        }
    }

    private function setPermission($forum_data) {
        // Generate iMOD Constant
        $mods = $this->moderator();
        $mods::define_forum_mods($forum_data);
        unset($mods);
        // Access the forum
        self::$permissions['permissions']['can_access'] = (iMOD || checkgroup($forum_data['forum_access'])) ? TRUE : FALSE;
        // Create new thread -- whether user has permission to create a thread
        self::$permissions['permissions']['can_post'] = (iMOD || (checkgroup($forum_data['forum_post']) && $forum_data['forum_lock'] == FALSE)) ? TRUE : FALSE;
        // Poll creation -- thread has not exist, therefore cannot be locked.
        self::$permissions['permissions']['can_create_poll'] = $forum_data['forum_allow_poll'] == TRUE && (iMOD || (checkgroup($forum_data['forum_poll']) && $forum_data['forum_lock'] == FALSE)) ? TRUE : FALSE;
        self::$permissions['permissions']['can_upload_attach'] = $forum_data['forum_allow_attach'] == TRUE && (iMOD || checkgroup($forum_data['forum_attach'])) ? TRUE : FALSE;
        self::$permissions['permissions']['can_download_attach'] = iMOD || ($forum_data['forum_allow_attach'] == TRUE && checkgroup($forum_data['forum_attach_download'])) ? TRUE : FALSE;
    }

    private static function getPermission($key) {
        if (!empty(self::$permissions['permissions'])) {
            if (isset(self::$permissions['permissions'][$key])) {
                return self::$permissions['permissions'][$key];
            }

            return self::$permissions['permissions'];
        }

        return NULL;
    }

    /**
     * @return array
     */
    public function get_newThreadInfo() {
        return $this->info;
    }
}
