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

namespace PHPFusion\Infusions\Forum\Classes\Post;

use PHPFusion\Infusions\Forum\Classes\ForumServer;

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
        require_once INFUSIONS."forum/forum_include.php";
        require_once INCLUDES."infusions_include.php";
        require_once INFUSIONS."forum/templates.php";
    }

    /**
     * New thread
     */
    public function set_newThreadInfo() {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();

        $forum_settings = parent::get_forum_settings();

        if (iMEMBER) {

            $_forum_id = get('forum_id', FILTER_VALIDATE_INT);

            // New thread directly to a specified forum
            if ($_forum_id && parent::verify_forum($_forum_id)) {

                // URL: newthread.php?forum_id=int
                add_to_title($locale['forum_0000'].$locale['global_201'].$locale['forum_0057']);
                add_to_meta("description", $locale['forum_0000']);
                add_breadcrumb(['link' => FORUM.'index.php', 'title' => $locale['forum_0000']]);

                $forum_data = dbarray(dbquery("SELECT f.*, f2.forum_name AS forum_cat_name
                FROM ".DB_FORUMS." f
                LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
                WHERE f.forum_id=:fid AND ".groupaccess('f.forum_access'), [
                    ':fid' => (int)$_forum_id
                ]));

                if ($forum_data['forum_type'] == 1 or $forum_data['forum_lock']) {
                    redirect(INFUSIONS.'forum/index.php');
                }

                if (post('cancel')) {
                    redirect(INFUSIONS.'forum/index.php?viewforum&amp;forum_id='.$forum_data['forum_id']);
                }

                $forum_data['lock_edit'] = $forum_settings['forum_edit_lock'];

                self::setPermission($forum_data);

                if (self::getPermission('can_post') && self::getPermission('can_access')) {
                    add_breadcrumb([
                        'link'  => INFUSIONS.'forum/index.php?viewforum&amp;forum_id='.$forum_data['forum_id'],
                        'title' => $forum_data['forum_name']
                    ]);
                    add_breadcrumb([
                        'link'  => INFUSIONS.'forum/index.php?viewforum&amp;forum_id='.$forum_data['forum_id'],
                        'title' => $locale['forum_0057']
                    ]);

                    /**
                     * Generate a poll form
                     */
                    $poll_form = '';
                    if (self::getPermission('can_create_poll')) {

                        // initial data to push downwards
                        $pollData = [
                            'thread_id'         => 0,
                            'forum_poll_title'  => (post('forum_poll_title') ? sanitizer('forum_poll_title', '', 'forum_poll_title') : ''),
                            'forum_poll_start'  => TIME, // time poll started
                            'forum_poll_length' => 2, // how many poll options we have
                            'forum_poll_votes'  => 0, // how many vote this poll has
                        ];
                        // counter of lengths
                        $option_data[1] = '';
                        $option_data[2] = '';
                        // Do a validation if checked add_poll
                        if (post('add_poll') or post('add_poll_option')) {
                            $pollData['forum_poll_length'] = count($option_data);
                            // calculate poll lengths
                            $poll_options = (array)\Defender::getInstance()->filterPostArray(['poll_options']);
                            if (!empty($poll_options)) {
                                foreach ($poll_options as $i => $value) {
                                    $option_data[$i] = form_sanitizer($value, '', "poll_options[$i]");
                                }
                            }
                            // filter blank poll entries
                            $option_data = array_filter($option_data);
                        }
                        if (post('add_poll_option')) {
                            // reindex the whole array with blank values.
                            if (fusion_safe()) {
                                $option_data = array_values(array_filter($option_data));
                                array_unshift($option_data, NULL);
                                unset($option_data[0]);
                                $pollData['forum_poll_length'] = count($option_data);
                            }
                            array_push($option_data, '');
                        }

                        $poll_field = [];
                        $poll_field['poll_field'] = form_text('forum_poll_title', $locale['forum_0604'], $pollData['forum_poll_title'], [
                            'max_length'  => 255,
                            'placeholder' => $locale['forum_0604a'],
                            'inline'      => TRUE,
                            //'required'    => TRUE
                        ]);
                        for ($i = 1; $i <= count($option_data); $i++) {
                            $poll_field['poll_field'] .= form_text("poll_options[$i]", sprintf($locale['forum_0606'], $i),
                                $option_data[$i], [
                                    'max_length'  => 255,
                                    'placeholder' => $locale['forum_0605'],
                                    'inline'      => TRUE,
                                    //'required'    => $i <= 2 ? TRUE : FALSE
                                ]);
                        }
                        $poll_field['poll_field'] .= "<div class='col-xs-12 col-sm-offset-3'>\n";
                        $poll_field['poll_field'] .= form_button('add_poll_option', $locale['forum_0608'],
                            $locale['forum_0608'], ['class' => 'btn-primary']);
                        $poll_field['poll_field'] .= "</div>\n";
                        $info = [
                            'title'       => $locale['forum_0366'],
                            'description' => $locale['forum_0630'],
                            'field'       => $poll_field
                        ];
                        $poll_form .= "<div id='poll_form' class='poll-form' style='display:none;'>\n";
                        $poll_form .= "<div class='clearfix'>\n";
                        $poll_form .= "<!--pre_form-->\n";
                        $poll_form .= $info['field']['poll_field'];
                        $poll_form .= "</div>\n";
                        $poll_form .= "</div>\n";
                        $poll_form .= form_checkbox("add_poll", $locale['forum_0366'], isset($_POST['add_poll']) ? TRUE : FALSE, ['class' => 'm-0', 'reverse_label' => TRUE]);
                    }

                    // For new thread
                    $thread_data = [
                        'forum_id'          => $forum_data['forum_id'],
                        'thread_id'         => 0,
                        'thread_subject'    => (post('thread_subject') ? sanitizer('thread_subject', '', 'thread_subject') : ''),
                        'thread_tags'       => (post('thread_tags') ? sanitizer('thread_tags', '', 'thread_tags') : ''),
                        'thread_author'     => $userdata['user_id'],
                        'thread_views'      => 0,
                        'thread_lastpost'   => TIME,
                        'thread_lastpostid' => 0, // need to run update
                        'thread_lastuser'   => $userdata['user_id'],
                        'thread_postcount'  => 1, // already insert 1 postcount.
                        'thread_poll'       => 0,
                        'thread_sticky'     => post('thread_sticky') ? 1 : 0,
                        'thread_locked'     => post('thread_locked') ? 1 : 0,
                        'thread_hidden'     => 0,
                    ];

                    $post_data = [
                        'forum_id'        => $forum_data['forum_id'],
                        'forum_cat'       => $forum_data['forum_cat'],
                        'thread_id'       => 0,
                        'post_id'         => 0,
                        'post_message'    => (post('post_message') ? sanitizer('post_message', '', 'post_message') : ''),
                        'post_showsig'    => (post('post_showsig') ? 1 : 0),
                        'post_smileys'    => '',
                        'post_author'     => $userdata['user_id'],
                        'post_datestamp'  => TIME,
                        'post_ip'         => USER_IP,
                        'post_ip_type'    => USER_IP_TYPE,
                        'post_edituser'   => 0,
                        'post_edittime'   => 0,
                        'post_editreason' => '',
                        'post_hidden'     => 0,
                        'notify_me'       => (post('notify_me') ? (post('notify_me') ? 1 : 0) : 0),
                        'post_locked'     => 0,
                    ];

                    $post_data['post_smileys'] = (!post('post_smileys') || $post_data['post_message'] && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $post_data['post_message']) ? 0 : 1);
                    // Execute post new thread
                    if (post('post_newthread')) {
                        // all data is sanitized here.
                        if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) {

                            if (fusion_safe()) {

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

                                dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id=:uid", [':uid' => intval($post_data['post_author'])]);

                                $this->updateThreadStat($post_data['forum_id'], $last_thread_id, $post_data['post_id'], $post_data['post_author'], $thread_data['thread_subject'], $post_data['post_message'], TIME);

                                // set notify
                                if ($forum_settings['thread_notify'] && $post_data['notify_me'] && $post_data['thread_id']) {

                                    $this->addTracking($post_data['thread_id'], $post_data['post_author']);
                                }

                                // Add poll if exist
                                if (!empty($option_data) && post('add_poll')) {
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
                            if (fusion_safe()) {
                                redirect(INFUSIONS."forum/postify.php?post=new&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;thread_id=".intval($post_data['thread_id'].""));
                            }
                        }
                    }

                    $this->info = [
                        'title'             => $locale['forum_0057'],
                        'description'       => '',
                        'openform'          => openform('input_form', 'post', clean_request('forum_id='.$post_data['forum_id'], ['forum_id'], FALSE),
                                ['enctype' => self::getPermission("can_upload_attach")]).form_hidden("preview_src_file", "", INCLUDES."dynamics/assets/preview/preview.ajax.php"),
                        'closeform'         => closeform(),
                        "preview_box"       => "<div id='preview_box'></div>",
                        'forum_id_field'    => '',
                        'thread_id_field'   => '',
                        "forum_field"       => form_select('forum_id', '', $thread_data['forum_id'],
                            [
                                "required"     => TRUE,
                                "width"        => "100%",
                                "inner_width"  => "100%",
                                "no_root"      => TRUE,
                                "placeholder"  => $locale['forum_0395'],
                                'db'           => DB_FORUMS,
                                'title_col'    => 'forum_name',
                                'id_col'       => 'forum_id',
                                'cat_col'      => 'forum_cat',
                                'custom_query' => "SELECT forum_id, forum_cat, forum_name FROM ".DB_FORUMS.(multilang_table('FO') ? ' WHERE '.in_group('forum_language', LANGUAGE) : ''),
                            ]),
                        'subject_field'     => form_text('thread_subject', $locale['forum_0051'], $thread_data['thread_subject'],
                            [
                                'required'    => 1,
                                'placeholder' => $locale['forum_2001'],
                                'error_text'  => '',
                                'class'       => 'm-t-20 m-b-20 form-group-lg',
                            ]),
                        'tags_field'        => form_select('thread_tags[]', "", $thread_data['thread_tags'],
                            [
                                'options'     => parent::tag()->get_TagOpts(TRUE),
                                'inner_width' => '100%',
                                'multiple'    => TRUE,
                                'delimiter'   => '.',
                                'placeholder' => $locale['forum_tag_0100'],
                                'max_select'  => 3, // to do settings on this
                            ]),
                        "message_field"     => form_textarea("post_message", "", $post_data['post_message'], [
                            'required'    => TRUE,
                            'preview'     => FALSE,
                            'form_name'   => 'input_form',
                            'type'        => 'bbcode',
                            'height'      => '300px',
                            'placeholder' => $locale['forum_0601'],
                            'bbcode'      => TRUE,
                            'grippie'     => TRUE,
                            'tab'         => TRUE,
                        ]),
                        'attachment_field'  => self::getPermission("can_upload_attach") ?
                            form_fileinput('file_attachments[]',
                                $locale['forum_0557'],
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
                            ".sprintf($locale['forum_0559'], parsebytesize($forum_settings['forum_attachmax']), str_replace('|', ', ', $forum_settings['forum_attachtypes']), $forum_settings['forum_attachmax_count'])."</small>\n</div>\n" : '',
                        'poll_form'         => $poll_form,
                        'smileys_field'     => form_checkbox('post_smileys', $locale['forum_0169'], $post_data['post_smileys'], ['type' => 'button', 'ext_tip' => $locale['forum_0622'], "class" => "m-b-10"]),
                        "signature_field"   => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', $locale['forum_0264'], $post_data['post_showsig'], ["type" => "button", "ext_tip" => $locale['forum_0170'], "class" => "m-b-10"]) : '',
                        'sticky_field'      => (iMOD || iSUPERADMIN) ? form_checkbox('thread_sticky', $locale['forum_0262'], $thread_data['thread_sticky'], ['type' => 'button', 'ext_tip' => $locale['forum_0620'], "class" => "m-b-10"]) : '',
                        'lock_field'        => (iMOD || iSUPERADMIN) ? form_checkbox('thread_locked', $locale['forum_0621'], $thread_data['thread_locked'], ['type' => 'button', 'button_class' => 'btn-warning', "class" => "m-b-10"]) : '',
                        'edit_reason_field' => '',
                        'delete_field'      => '',
                        'hide_edit_field'   => '',
                        'post_locked_field' => '',
                        'notify_field'      => $forum_settings['thread_notify'] ? form_checkbox('notify_me', $locale['forum_0175'], $post_data['notify_me'], ['ext_tip' => $locale['forum_0171'], 'type' => 'button', "class" => "m-b-10"]) : '',
                        'post_buttons'      => form_button('post_newthread', $locale['forum_0057'], $locale['forum_0057'], ['class' => 'btn-primary ']).form_button('cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-default  m-l-10']),
                        'last_posts_reply'  => '',
                    ];
                    // add a jquery to toggle the poll form
                    add_to_jquery("
                    $('#poll_form').hide();
                    if ($('#add_poll').is(':checked')) {
                    $('#poll_form').show();
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

                // Create a general new thread
                if (post('cancel')) {
                    redirect(INFUSIONS.'forum/index.php');
                }

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

                add_breadcrumb(["link" => FORUM."newthread.php?forum_id=0", "title" => $locale['forum_0057']]);

                $forum_id = (post('forum_id') ? sanitizer('forum_id', 0, 'forum_id') : 0);

                $thread_data = [
                    'forum_id'          => $forum_id,
                    'thread_id'         => 0,
                    'thread_subject'    => (post('thread_subject') ? sanitizer('thread_subject', '', 'thread_subject') : ''),
                    'thread_tags'       => (post('thread_tags') ? sanitizer('thread_tags', '', 'thread_tags') : ''),
                    'thread_author'     => $userdata['user_id'],
                    'thread_views'      => 0,
                    'thread_lastpost'   => time(),
                    'thread_lastpostid' => 0, // need to run update
                    'thread_lastuser'   => $userdata['user_id'],
                    'thread_postcount'  => 1, // already insert 1 postcount.
                    'thread_poll'       => 0,
                    'thread_sticky'     => (post('thread_sticky') ? 1 : 0),
                    'thread_locked'     => (post('thread_locked') ? 1 : 0),
                    'thread_hidden'     => 0,
                ];

                $post_data = [
                    'forum_id'        => $forum_id,
                    "forum_cat"       => 0, // for redirect
                    'thread_id'       => 0, // required lastid
                    'post_id'         => 0, // auto insertion
                    'post_message'    => (post('post_message') ? sanitizer('post_message', '', 'post_message') : ''),
                    'post_showsig'    => (post('post_showsig') ? 1 : 0),
                    'post_smileys'    => 1,
                    'post_author'     => $userdata['user_id'],
                    'post_datestamp'  => TIME,
                    'post_ip'         => USER_IP,
                    'post_ip_type'    => USER_IP_TYPE,
                    'post_edituser'   => 0,
                    'post_edittime'   => 0,
                    'post_editreason' => '',
                    'post_hidden'     => 0,
                    'notify_me'       => (post('notify_me') ? (post('notify_me') ? 1 : 0) : 0),
                    'post_locked'     => 0,
                ];


                $post_data['post_smileys'] = (!post('post_smileys') || $post_data['post_message'] && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $post_data['post_message']) ? 0 : 1);

                if (post('post_newthread') && fusion_safe()) {

                    require_once INCLUDES.'flood_include.php';

                    if (!flood_control('post_datestamp', DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) {

                        if (ForumServer::verify_forum($thread_data['forum_id'])) {

                            $forum_data = dbarray(dbquery("SELECT f.*, f2.forum_name AS forum_cat_name
                            FROM ".DB_FORUMS." f
                            LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
                            WHERE f.forum_id=:fid AND ".groupaccess('f.forum_access'), [
                                ":fid" => intval($thread_data['forum_id'])
                            ]));

                            if ($forum_data['forum_type'] == 1) {
                                redirect(FORUM.'index.php');
                            }

                            // Use the new permission settings
                            self::setPermission($forum_data);

                            $forum_data['lock_edit'] = $forum_settings['forum_edit_lock'];

                            if (self::getPermission('can_post') && self::getPermission('can_access')) {

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
                                if (!empty($_FILES) && is_uploaded_file($_FILES['file_attachments']['tmp_name'][0]) && self::getPermission('can_upload_attach')) {
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

                                // update the user statistic
                                dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".intval($post_data['post_author'])."'");

                                // update current thread
                                $this->updateThreadStat($post_data['forum_id'], $last_thread_id, $post_data['post_id'], $post_data['post_author'], $thread_data['thread_subject'], $post_data['post_message'], TIME);

                                if (!empty($option_data) && post('add_poll')) {

                                    dbquery_insert(DB_FORUM_POLLS, $pollData, 'save', ['keep_session' => TRUE]);

                                    $poll_option_data['thread_id'] = $pollData['thread_id'];
                                    $i = 1;

                                    foreach ($option_data as $option_text) {
                                        if ($option_text) {
                                            $poll_option_data['forum_poll_option_id'] = $i;
                                            $poll_option_data['forum_poll_option_text'] = $option_text;
                                            $poll_option_data['forum_poll_option_votes'] = 0;
                                            dbquery_insert(DB_FORUM_POLL_OPTIONS, $poll_option_data, 'save', ['keep_session' => TRUE]);
                                            $i++;
                                        }
                                    }
                                    dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_poll='1' WHERE thread_id='".$pollData['thread_id']."'");
                                }

                                // set notify
                                if ($forum_settings['thread_notify'] && post('notify_me') && $post_data['thread_id']) {
                                    $this->addTracking($post_data['thread_id'], $post_data['post_author']);
                                }

                                if (fusion_safe()) {
                                    redirect(FORUM."postify.php?post=new&error=0&amp;forum_id=".$post_data['forum_id']."&amp;thread_id=".$post_data['thread_id']);
                                }
                            } else {
                                addNotice("danger", $locale['forum_0186']);
                            }
                        } else {

                            addNotice("danger", $locale['forum_0187']);

                            redirect(FORUM."index.php");
                        }
                    }
                }

                //Disable all parents
                $disabled_opts = [];
                $disable_query = "SELECT forum_id FROM ".DB_FORUMS." WHERE forum_type=1 ".(multilang_table("FO") ? "AND ".in_group('forum_language', LANGUAGE) : '');
                $disable_query = dbquery($disable_query);
                if (dbrows($disable_query) > 0) {
                    while ($d_forum = dbarray($disable_query)) {
                        $disabled_opts[] = $d_forum['forum_id'];
                    }
                }

                // Have 3 types of forum configurations
                // print_P($disabled_opts);
                $this->info = [
                    'title'             => $locale['forum_0057'],
                    'description'       => '',
                    'openform'          => openform('input_form', 'post', FORUM.'newthread.php').form_hidden('preview_src_file', '', INCLUDES.'dynamics/assets/preview/preview.ajax.php'),
                    'closeform'         => closeform(),
                    'forum_id_field'    => '',
                    'thread_id_field'   => '',
                    'preview_box'       => '<div id=\'preview_box\'></div>',
                    // need to disable all parents
                    'forum_field'       => form_select('forum_id', '', $thread_data['forum_id'],
                        [
                            'required'     => TRUE,
                            'width'        => '100%',
                            'inner_width'  => '100%',
                            'no_root'      => TRUE,
                            'optgroup'     => TRUE,
                            'disable_opts' => $disabled_opts,
                            'hide_disabled' => TRUE,
                            'placeholder'  => $locale['forum_0395'],
                            'db'           => DB_FORUMS,
                            'id_col'       => 'forum_id',
                            'cat_col'      => 'forum_cat',
                            'title_col'    => 'forum_name',
                            'select_alt'   => TRUE,
                            'custom_query' => "SELECT forum_id, forum_cat, forum_name FROM ".DB_FORUMS.(multilang_table('FO') ? ' WHERE '.in_group('forum_language', LANGUAGE) : ''),
                        ]),
                    'subject_field'     => form_text('thread_subject', $locale['forum_0051'], $thread_data['thread_subject'], [
                        'required'    => TRUE,
                        'placeholder' => $locale['forum_2001'],
                        'class'       => 'form-group-lg',
                    ]),
                    'tags_field'        => form_select('thread_tags[]', '', $thread_data['thread_tags'],
                        [
                            'options'     => parent::tag()->get_TagOpts(TRUE),
                            'placeholder' => $locale['forum_tag_0100'],
                            'inner_width' => '100%',
                            'multiple'    => TRUE,
                            'delimiter'   => '.',
                            'max_select'  => 3, // to do settings on this
                        ]),
                    'message_field'     => form_textarea('post_message', '', $post_data['post_message'], [
                        'required'    => TRUE,
                        'preview'     => FALSE,
                        'form_name'   => 'input_form',
                        'type'        => 'bbcode',
                        'height'      => '300px',
                        'placeholder' => $locale['forum_0601'],
                        'bbcode'      => TRUE,
                        'grippie'     => TRUE,
                        'tab'         => TRUE,
                    ]),
                    'attachment_field'  => '',
                    'poll_form'         => '',
                    'smileys_field'     => form_checkbox('post_smileys', $locale['forum_0169'], $post_data['post_smileys'], ['type' => 'button', 'ext_tip' => $locale['forum_0622']]),
                    'signature_field'   => (array_key_exists('user_sig', $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', $locale['forum_0264'], $post_data['post_showsig'], ['type' => 'button', 'ext_tip' => $locale['forum_0170']]) : '',
                    'sticky_field'      => (iSUPERADMIN) ? form_checkbox('thread_sticky', $locale['forum_0262'], $thread_data['thread_sticky'], ['type' => 'button', 'ext_tip' => $locale['forum_0620']]) : '',
                    'lock_field'        => (iSUPERADMIN) ? form_checkbox('thread_locked', $locale['forum_0263'], $thread_data['thread_locked'], ['type' => 'button', 'ext_tip' => $locale['forum_0263']]) : '',
                    'edit_reason_field' => '',
                    'delete_field'      => '',
                    'hide_edit_field'   => '',
                    'post_locked_field' => '',
                    'notify_field'      => $forum_settings['thread_notify'] ? form_checkbox('notify_me', $locale['forum_0175'], $post_data['notify_me'], ['type' => 'button', 'ext_tip' => $locale['forum_0171']]) : '',
                    'post_buttons'      => form_button('post_newthread', $locale['forum_0057'], $locale['forum_0057'], ['class' => 'btn-primary m-r-5']).form_button('cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-default']),
                    'last_posts_reply'  => '',
                ];
            }

        } else {
            redirect(FORUM."index.php");
        }
    }

    /**
     * Track a thread
     *
     * @param $thread_id
     * @param $user_id
     *
     * @throws \Exception
     */
    public function addTracking($thread_id, $user_id) {
        if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id=:tid AND notify_user=:uid", [':tid' => $thread_id, ':uid' => $user_id])) {
            $notice_array = [
                'thread_id'        => $thread_id,
                'notify_datestamp' => TIME,
                'notify_user'      => $user_id,
                'notify_status'    => 1,
            ];
            dbquery_insert(DB_FORUM_THREAD_NOTIFY, $notice_array, 'save', ['keep_session' => TRUE]);
        }
    }

    /**
     * @param $forum_id
     * @param $thread_id
     * @param $post_id
     * @param $post_author
     * @param $thread_subject
     * @param $post_message
     * @param $time
     *
     * @throws \Exception
     */
    public function updateThreadStat($forum_id, $thread_id, $post_id, $post_author, $thread_subject, $post_message = '', $time) {
        // find all parents and update them
        $forum_sql = /** @lang MySQL */
            "UPDATE ".DB_FORUMS." SET forum_lastpost=:time, forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+1, forum_lastpostid=:pid, forum_lastuser=:uid WHERE forum_id=:fid";
        $list_of_forums = get_all_parent(dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), $forum_id);
        if (!empty($list_of_forums)) {
            foreach ($list_of_forums as $fid) {
                $param = [
                    ':time' => $time,
                    ':pid'  => $post_id,
                    ':uid'  => $post_author,
                    ':fid'  => $fid
                ];
                dbquery($forum_sql, $param);
            }
        }
        $param = [
            ':time' => $time,
            ':pid'  => $post_id,
            ':uid'  => $post_author,
            ':fid'  => $forum_id
        ];
        dbquery($forum_sql, $param);

        // update current thread stats
        $sql = "UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:time, thread_lastpostid=:pid, thread_lastuser=:uid WHERE thread_id=:tid";
        $param = [
            ':time' => TIME,
            ':pid'  => $post_id,
            ':uid'  => $post_author,
            ':tid'  => $thread_id
        ];
        dbquery($sql, $param);

        // Update Activity for User
        fusion_add_activity(
            $post_author,
            USER_LEVEL_PUBLIC,
            'forum',
            'thread_new',
            $thread_id,
            "<a href='".fusion_get_settings('siteurl')."infusions/forum/viewthread.php?thread_id=$thread_id'>$thread_subject</a>",
            $post_message,
            TIME
        );
    }

    private function setPermission($forum_data) {
        // Generate iMOD Constant
        set_forum_mods($forum_data);

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
    public function getInfo() {
        return $this->info;
    }
}

require_once INCLUDES."flood_include.php";
