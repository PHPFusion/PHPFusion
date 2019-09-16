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
use PHPFusion\Infusions\Forum\Classes\Post\Edit_Post;
require_once THEMES."templates/header.php";
require_once INCLUDES."infusions_include.php";
require_once INFUSIONS."forum/forum_include.php";
include INFUSIONS."forum/templates.php";

class View_Thread extends Forum_Server {

    private $thread_data = [];

    public function __construct() {}

    public function display_thread() {

        $info = self::thread()->getInfo();

        $this->set_ThreadJs();

        $action = get('action');
        $all_actions = ['editpoll', 'deletepoll', 'newpoll', 'edit', 'reply', 'award', 'newbounty', 'editbounty'];

        if (in_array($action, $all_actions)) {
            switch ($action) {
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
                    $edit_form = new Edit_Post($this);
                    return $edit_form->render_edit_form();
                    break;
                case 'reply':
                    // Template
                    return $this->threadReplyForm();
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
            $this->addViewCount($info['thread']['thread_id']);
            // +1 see who is viewing thread
            self::thread()->addThreadVisit();

            if ($info['thread']['forum_users'] == TRUE) {

                $info['thread_users'] = $this->get_participated_users($info);

            }

            return render_thread($info);

        }

        return NULL;
    }

    /**
     * Displays Reply To A Thread Form
     * Template: function display_form_postform
     */
    public function threadReplyForm() {

        $thread = self::thread();
        $thread_info = $thread->getInfo();
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
        if (post('cancel') && !empty($thread_data['thread_id'])) {
            if (fusion_get_settings("site_seo")) {
                redirect(fusion_get_settings("siteurl")."infusions/forum/viewthread.php?thread_id=".$thread_data['thread_id']);
            }
            redirect(FORUM.'viewthread.php?thread_id='.$thread_data['thread_id']);
        }

        if ($thread->getThreadPermission('can_reply') && !empty($thread_data['thread_id'])) {

            add_to_title($locale['global_201'].$locale['forum_0360']);
            add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $locale['forum_0360']]);
            //add_to_footer("<script src='".FORUM."templates/ajax/post_preview.js'></script>");

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
            if (post('post_reply')) {

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
            $quote = get('quote', FILTER_VALIDATE_INT);
            if ($quote) {
                $quote_result = dbquery("SELECT a.post_message, b.user_name
                                        FROM ".DB_FORUM_POSTS." a
                                        INNER JOIN ".DB_USERS." b ON a.post_author=b.user_id
                                        WHERE thread_id=:tid AND post_id=:quote", [':quote'=>(int)$quote, ':tid'=>(int) $thread_data['thread_id'] ]);

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
                'title'             => $locale['forum_0360'],
                'description'       => $locale['forum_2000'].$thread_data['thread_subject'],
                'openform'          => openform('input_form', 'post', $form_action, ['enctype' => $thread->getThreadPermission('can_upload_attach')]).form_hidden('preview_src_file', '', INCLUDES.'dynamics/assets/preview/preview.ajax.php'),
                'closeform'         => closeform(),
                'preview_box'       => '<div id="preview_box"></div>',
                'forum_id_field'    => form_hidden('forum_id', '', $post_data['forum_id']),
                'thread_id_field'   => form_hidden('thread_id', '', $post_data['thread_id']),
                'forum_field'       => '',
                'tags_field'        => '',
                "subject_field"     => form_hidden('thread_subject', $locale['forum_0051'], $thread_data['thread_subject'], [
                    'required'    => 1,
                    'placeholder' => $locale['forum_2001'],
                    'error_text'  => '',
                    'class'       => 'm-t-20 m-b-20',
                    'class'       => 'form-group-lg',
                ]),
                "message_field"     => form_textarea('post_message', $locale['forum_0601'], $post_data['post_message'],
                    [
                        'required'  => TRUE,
                        'preview'   => TRUE,
                        'form_name' => 'input_form',
                        'bbcode'    => TRUE,
                        'height'    => '500px',
                        'grippie'   => TRUE,
                        'tab'       => TRUE,
                    ]),
                // happens only in EDIT
                "delete_field"      => "",
                "edit_reason_field" => "",
                "attachment_field"  => $thread->getThreadPermission("can_upload_attach") ?
                    form_fileinput('file_attachments[]', $locale['forum_0557'], '',
                        ['input_id'    => 'file_attachments',
                         'upload_path' => INFUSIONS.'forum/attachments/',
                         'type'        => 'object',
                         'template'    => 'modern',
                         'multiple'    => TRUE,
                         'inline'      => FALSE,
                         'max_count'   => $forum_settings['forum_attachmax_count'],
                         'valid_ext'   => $forum_settings['forum_attachtypes'],
                         'max_byte'    => $forum_settings['forum_attachmax'],
                         'class'       => 'm-b-0',
                        ])."
                        <div class='m-b-20'>\n<small>".sprintf($locale['forum_0559'], parsebytesize($forum_settings['forum_attachmax']), str_replace('|', ', ', $forum_settings['forum_attachtypes']), $forum_settings['forum_attachmax_count'])."</small>\n</div>\n"
                    : "",
                'poll_form'         => '',
                "smileys_field"     => form_checkbox('post_smileys', $locale['forum_0169'], $post_data['post_smileys'], ['class' => 'm-b-0', 'type' => 'button', 'ext_tip' => $locale['forum_0622']]),
                "signature_field"   => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', $locale['forum_0264'], $post_data['post_showsig'], ['class' => 'm-b-0', 'type' => 'button', 'ext_tip' => $locale['forum_0170']]) : "",
                'sticky_field'      => '',
                'lock_field'        => '',
                'hide_edit_field'   => '',
                'post_locked_field' => '',
                // not available in edit mode.
                "notify_field"      => $forum_settings['thread_notify'] ? form_checkbox('notify_me', $locale['forum_0552'], $post_data['notify_me'], ['class' => 'm-b-0', 'type' => 'button', 'ext_tip' => $locale['forum_0171']]) : "",
                "post_buttons"      => form_button('post_reply', $locale['forum_0172'], $locale['forum_0172'], ['class' => 'btn-primary']).form_button('cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-default m-l-10']),
                'last_posts_reply'  => ''
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
    private function addViewCount($thread_id) {
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
