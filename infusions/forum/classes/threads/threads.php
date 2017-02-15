<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: threads/threads.php
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
namespace PHPFusion\Forums\Threads;

use PHPFusion\BreadCrumbs;
use PHPFusion\Forums\ForumServer;
use PHPFusion\Forums\Moderator;
use PHPFusion\Forums\Post\QuickReply;

/**
 * Class ForumThreads
 * Forum threads functions
 *
 * @package PHPFusion\Forums\Threads
 */
class ForumThreads extends ForumServer {

    protected $thread_info = []; // make a default

    protected $thread_data = [];

    /**
     * Get thread structure on specific forum id.
     *
     * @param            $forum_id
     * @param bool|FALSE $filter
     *
     * @return array
     */
    public static function get_forum_thread($forum_id, $filter = FALSE) {

        $info = [];

        $locale = fusion_get_locale("", FORUM_LOCALE);

        $forum_settings = ForumServer::get_forum_settings();

        $userdata = fusion_get_userdata();
        $userdata['user_id'] = !empty($userdata['user_id']) ? (int)intval($userdata['user_id']) : 0;

        $lastVisited = (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time();
        /**
         * Get threads with filter conditions (XSS prevention)
         */
        $thread_query = "
        SELECT
        count(t.thread_id) 'thread_max_rows',
        count(a1.attach_id) 'attach_image',
        count(a2.attach_id) 'attach_files'
        FROM ".DB_FORUM_THREADS." t
        LEFT JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
        INNER JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
        #LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id
        LEFT JOIN ".DB_FORUM_POSTS." p1 ON p1.thread_id = t.thread_id and p1.post_id = t.thread_lastpostid
        LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id
        LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = t.thread_id AND p1.post_id = v.post_id
        LEFT JOIN ".DB_FORUM_ATTACHMENTS." a1 on a1.thread_id = t.thread_id AND a1.attach_mime IN ('".implode(",", img_mimeTypes())."')
        LEFT JOIN ".DB_FORUM_ATTACHMENTS." a2 on a2.thread_id = t.thread_id AND a2.attach_mime NOT IN ('".implode(",", img_mimeTypes())."')
        WHERE t.forum_id='".intval($forum_id)."' AND t.thread_hidden='0' AND ".groupaccess('tf.forum_access')."
        ".(isset($filter['condition']) ? $filter['condition'] : '')."
        GROUP BY tf.forum_id
        ";

        $thread_result = dbquery($thread_query);

        $thread_rows = dbrows($thread_result);

        $count = array(
            "thread_max_rows" => 0,
            "attach_image"    => 0,
            "attach_files"    => 0,
        );

        $info['item'][$forum_id]['forum_threadcount'] = 0;
        $info['item'][$forum_id]['forum_threadcount_word'] = format_word($count['thread_max_rows'], $locale['fmt_thread']);

        if ($thread_rows > 0) {
            $count = dbarray($thread_result);
            $info['item'][$forum_id]['forum_threadcount'] = 0;
            $info['item'][$forum_id]['forum_threadcount_word'] = format_word($count['thread_max_rows'], $locale['fmt_thread']);
        }

        $info['thread_max_rows'] = $count['thread_max_rows'];

        if ($info['thread_max_rows'] > 0) {

            $info['threads']['pagenav'] = "";
            $info['threads']['pagenav2'] = "";

            // anti-XSS filtered rowstart
            $_GET['thread_rowstart'] = isset($_GET['thread_rowstart'])
            && isnum($_GET['thread_rowstart']) && $_GET['thread_rowstart'] <= $count['thread_max_rows'] ?
                $_GET['thread_rowstart'] : 0;

            $thread_query = "
            SELECT t.*, tf.forum_type, tf.forum_name, tf.forum_cat,
            tu1.user_name ' author_name', tu1.user_status 'author_status', tu1.user_avatar 'author_avatar',
            tu2.user_name 'last_user_name', tu2.user_status 'last_user_status', tu2.user_avatar 'last_user_avatar',
            p1.post_datestamp, p1.post_message,
            IF (n.thread_id > 0, 1 , 0) 'user_tracked',
            count(v.vote_user) 'thread_rated',
            count(pv.forum_vote_user_id) 'poll_voted',
            p.forum_poll_title,
            count(v.post_id) AS vote_count,
            a1.attach_name, a1.attach_id,
            a2.attach_name, a2.attach_id,
            count(a1.attach_mime) 'attach_image',
            count(a2.attach_mime) 'attach_files',
            min(p2.post_datestamp) 'first_post_datestamp'
            FROM ".DB_FORUM_THREADS." t
            LEFT JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
            INNER JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
            LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id
            LEFT JOIN ".DB_FORUM_POSTS." p1 ON p1.thread_id = t.thread_id and p1.post_id = t.thread_lastpostid
            LEFT JOIN ".DB_FORUM_POSTS." p2 ON p2.thread_id = t.thread_id
            LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id
            #LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = t.thread_id AND p1.post_id = v.post_id
            LEFT JOIN ".DB_FORUM_VOTES." v on v.thread_id = t.thread_id AND v.vote_user='".$userdata['user_id']."' AND v.forum_id = t.forum_id AND tf.forum_type='4'
            LEFT JOIN ".DB_FORUM_POLL_VOTERS." pv on pv.thread_id = t.thread_id AND pv.forum_vote_user_id='".$userdata['user_id']."' AND t.thread_poll=1
            LEFT JOIN ".DB_FORUM_ATTACHMENTS." a1 on a1.thread_id = t.thread_id AND a1.attach_mime IN ('".implode(",", img_mimeTypes())."')
            LEFT JOIN ".DB_FORUM_ATTACHMENTS." a2 on a2.thread_id = t.thread_id AND a2.attach_mime NOT IN ('".implode(",", img_mimeTypes())."')
            LEFT JOIN ".DB_FORUM_THREAD_NOTIFY." n on n.thread_id = t.thread_id and n.notify_user = '".$userdata['user_id']."'
            WHERE t.forum_id='".$forum_id."' AND t.thread_hidden='0' AND ".groupaccess('tf.forum_access')."
            ".(isset($filter['condition']) ? $filter['condition'] : '')."
            ".(multilang_table("FO") ? "AND tf.forum_language='".LANGUAGE."'" : '')."
            GROUP BY t.thread_id
            ".(isset($filter['order']) ? $filter['order'] : '')."
            LIMIT ".intval($_GET['thread_rowstart']).", ".$forum_settings['threads_per_page'];

            $cthread_result = dbquery($thread_query);

            if (dbrows($cthread_result) > 0) {

                while ($threads = dbarray($cthread_result)) {

                    $icon = "";
                    $match_regex = $threads['thread_id']."\|".$threads['thread_lastpost']."\|".$threads['forum_id'];
                    if ($threads['thread_lastpost'] > $lastVisited) {
                        if (iMEMBER && ($threads['thread_lastuser'] == $userdata['user_id'] ||
                                preg_match("(^\.{$match_regex}$|\.{$match_regex}\.|\.{$match_regex}$)", $userdata['user_threads']))
                        ) {
                            $icon = "<i class='".get_forumIcons('thread')."' title='".$locale['forum_0261']."'></i>";
                        } else {
                            $icon = "<i class='".get_forumIcons('new')."' title='".$locale['forum_0260']."'></i>";
                        }
                    }

                    $author = array(
                        'user_id'     => $threads['thread_author'],
                        'user_name'   => $threads['author_name'],
                        'user_status' => $threads['author_status'],
                        'user_avatar' => $threads['author_avatar']
                    );

                    $lastuser = array(
                        'user_id'     => $threads['thread_lastuser'],
                        'user_name'   => $threads['last_user_name'],
                        'user_status' => $threads['last_user_status'],
                        'user_avatar' => $threads['last_user_avatar']
                    );

                    $threads += array(
                        "thread_link"    => array(
                            "link"  => FORUM."viewthread.php?thread_id=".$threads['thread_id'],
                            "title" => $threads['thread_subject']
                        ),
                        "forum_type"     => $threads['forum_type'],
                        "thread_pages"   => makepagenav(0, $forum_settings['posts_per_page'], $threads['thread_postcount'], 3,
                            FORUM."viewthread.php?thread_id=".$threads['thread_id']."&amp;"),
                        "thread_icons"   => array(
                            'lock'   => $threads['thread_locked'] ? "<i class='".self::get_forumIcons('lock')."' title='".$locale['forum_0263']."'></i>" : '',
                            'sticky' => $threads['thread_sticky'] ? "<i class='".self::get_forumIcons('sticky')."' title='".$locale['forum_0103']."'></i>" : '',
                            'poll'   => $threads['thread_poll'] ? "<i class='".self::get_forumIcons('poll')."' title='".$locale['forum_0314']."'></i>" : '',
                            'hot'    => $threads['thread_postcount'] >= 20 ? "<i class='".self::get_forumIcons('hot')."' title='".$locale['forum_0311']."'></i>" : '',
                            'reads'  => $threads['thread_views'] >= 20 ? "<i class='".self::get_forumIcons('reads')."' title='".$locale['forum_0311']."'></i>" : '',
                            'image'  => $threads['attach_image'] > 0 ? "<i class='".self::get_forumIcons('image')."' title='".$locale['forum_0313']."'></i>" : '',
                            'file'   => $threads['attach_files'] > 0 ? "<i class='".self::get_forumIcons('file')."' title='".$locale['forum_0312']."'></i>" : '',
                            'icon'   => $icon,
                        ),
                        "thread_starter" => $locale['forum_0006'].timer($threads['first_post_datestamp'])." ".$locale['by']." ".profile_link($author['user_id'],
                                $author['user_name'],
                                $author['user_status'])."</span>",
                        "thread_author"  => $author,
                        "thread_last"    => array(
                            'user'         => $lastuser,
                            'avatar'       => display_avatar($lastuser, '35px', '', FALSE, 'img-rounded'),
                            'profile_link' => profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status']),
                            'time'         => $threads['post_datestamp'],
                            'post_message' => parseubb(parsesmileys($threads['post_message'])),
                            "formatted"    => "<div class='pull-left'>".display_avatar($lastuser, '30px', '', '', '')."</div>
																				<div class='overflow-hide'>".$locale['forum_0373']." <span class='forum_profile_link'>".profile_link($lastuser['user_id'],
                                    $lastuser['user_name'],
                                    $lastuser['user_status'])."</span><br/>
																				".timer($threads['post_datestamp'])."
																				</div>"
                        ),
                    );

                    if ($threads['thread_sticky']) {
                        $info['threads']['sticky'][$threads['thread_id']] = $threads;
                    } else {
                        $info['threads']['item'][$threads['thread_id']] = $threads;
                    }
                }
            }

            if ($info['thread_max_rows'] > $forum_settings['threads_per_page']) {

                $info['threads']['pagenav'] = makepagenav($_GET['thread_rowstart'],
                    $forum_settings['threads_per_page'],
                    $info['thread_max_rows'],
                    3,
                    clean_request("", array("thread_rowstart"), FALSE)."&amp;",
                    "thread_rowstart"
                );

                $info['threads']['pagenav2'] = makepagenav($_GET['thread_rowstart'],
                    $forum_settings['threads_per_page'],
                    $info['thread_max_rows'],
                    3,
                    clean_request("", array("thread_rowstart"), FALSE)."&amp;",
                    "thread_rowstart",
                    TRUE
                );

            }
        }

        return (array)$info;
    }

    /**
     * Returns thread variables
     *
     * @return array
     */
    public function get_threadInfo() {
        return (array)$this->thread_info;
    }

    /**
     * @param $query
     */
    public static function set_thread_query($query) {
        self::$custom_query = $query;
    }

    private static $custom_query = '';


    /**
     * Thread Class constructor - This builds all essential data on load.
     */
    public function set_threadInfo() {

        // Sanitize forum_id, thread_id, parent_id
        if ((!isset($_GET['thread_id']) && !isnum($_GET['thread_id'])) || (isset($_GET['forum_id']) && !isnum($_GET['forum_id'])) || (isset($_GET['parent_id']) && !isnum($_GET['parent_id']))) {
            redirect(FORUM.'index.php');
        }

        if (isset($_GET['forum_id'])) {
            if (!dbcount('(forum_id)', DB_FORUM_THREADS, "forum_id='".$_GET['forum_id']."' AND thread_id='".$_GET['thread_id']."'")) {
                redirect(FORUM.'index.php');
            }
        }

        $forum_settings = $this->get_forum_settings();
        $locale = fusion_get_locale('', [FORUM_LOCALE, FORUM_TAGS_LOCALE]);
        $userdata = fusion_get_userdata();
        $forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');

        $this->thread_data = self::get_thread($_GET['thread_id']); // fetch query and define iMOD

        if (!empty($this->thread_data) && $this->check_forum_access($forum_index, 0, $_GET['thread_id'])) {

            // get post_count, lastpost_id, first_post_id.
            $thread_stat = self::get_thread_stats($_GET['thread_id']);

            // Set the thread permissions
            $this->setThreadPermission();

            if ($this->thread_data['forum_type'] == 1) {
                if (fusion_get_settings("site_seo")) {
                    redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
                }
                redirect(FORUM.'index.php');
            }

            if ($thread_stat['post_count'] < 1) {
                if (fusion_get_settings("site_seo")) {
                    redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
                }
                redirect(FORUM.'index.php');
            }

            // Set meta
            add_to_title($this->thread_data['thread_subject']);
            add_to_meta($locale['forum_0000']);
            if ($this->thread_data['forum_description'] !== '') {
                add_to_meta('description', $this->thread_data['forum_description']);
            }
            if ($this->thread_data['forum_meta'] !== '') {
                add_to_meta('keywords', $this->thread_data['forum_meta']);
            }

            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FORUM.'index.php', 'title' => $locale['forum_0000']]);
            $this->forum_breadcrumbs($forum_index, $this->thread_data['forum_id']);
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FORUM.'viewthread.php?forum_id='.$this->thread_data['forum_id'].'&amp;thread_id='.$this->thread_data['thread_id'], 'title' => $this->thread_data['thread_subject']]);

            // Override $_GET['forum_id'] against tampering
            $_GET['forum_id'] = intval($this->thread_data['forum_id']);

            /**
             * Generate User Tracked Buttons
             */

            $this->thread_info['buttons']['notify'] = [];

            if ($this->getThreadPermission("can_access")) {
                // only member can track the thread
                if ($this->thread_data['user_tracked']) {
                    $this->thread_info['buttons']['notify'] = array(
                        'link'  => INFUSIONS."forum/postify.php?post=off&amp;forum_id=".$this->thread_data['forum_id']."&amp;thread_id=".$this->thread_data['thread_id'],
                        'title' => $locale['forum_0174']
                    );
                } else {
                    $this->thread_info['buttons']['notify'] = array(
                        'link'  => INFUSIONS."forum/postify.php?post=on&amp;forum_id=".$this->thread_data['forum_id']."&amp;thread_id=".$this->thread_data['thread_id'],
                        'title' => $locale['forum_0175']
                    );
                }
            }

            $this->thread_info['thread'] = $this->thread_data;

            /**
             * Generate Quick Reply Form
             */
            $qr_form = ($this->getThreadPermission("can_reply") == TRUE && $this->thread_data['forum_quick_edit'] == TRUE ? QuickReply::display_quickReply($this->thread_data) : '');

            /**
             * Generate Poll Form
             */
            $poll = new Poll($this->thread_info);
            $poll_form = $poll->generate_poll($this->thread_data);

            /**
             * Generate Attachment
             */
            $attach = new Attachment($this->thread_info);
            $attachments = $attach::get_attachments($this->thread_data);

            /**
             * Display thread bounty
             */
            $bounty = new Forum_Bounty($this->thread_info);
            $bounty_display = $bounty->display_bounty();

            /**
             * Generate Mod Form
             */
            if (iMOD) {

                $this->moderator()->setForumId($this->thread_data['forum_id']);
                $this->moderator()->setThreadId($this->thread_data['thread_id']);
                $this->moderator()->set_modActions();
                /**
                 * Thread moderation form template
                 */
                $addition = isset($_GET['rowstart']) ? "&amp;rowstart=".intval($_GET['rowstart']) : "";
                $this->thread_info['form_action'] = FUSION_SELF."?thread_id=".intval($this->thread_data['thread_id']).$addition;

                $this->thread_info['mod_options'] = array(
                    'renew'                                                      => $locale['forum_0207'],
                    'delete'                                                     => $locale['forum_0201'],
                    $this->thread_data['thread_locked'] ? "unlock" : "lock"      => $this->thread_data['thread_locked'] ? $locale['forum_0203'] : $locale['forum_0202'],
                    $this->thread_data['thread_sticky'] ? "nonsticky" : "sticky" => $this->thread_data['thread_sticky'] ? $locale['forum_0205'] : $locale['forum_0204'],
                    'move'                                                       => $locale['forum_0206']
                );

                $this->thread_info['mod_form'] = openform('moderator_menu', 'post', $this->thread_info['form_action']);
                $this->thread_info['mod_form'] .= form_hidden('delete_item_post', '', '');
                $this->thread_info['mod_form'] .= "<div class='btn-group m-r-10'>\n
						".form_button("check_all", $locale['forum_0080'], $locale['forum_0080'], array('class' => 'btn-default', "type" => "button"))."
						".form_button("check_none", $locale['forum_0081'], $locale['forum_0080'], array('class' => 'btn-default', "type" => "button"))."
					</div>\n
					".form_button('move_posts', $locale['forum_0176'], $locale['forum_0176'], array('class' => 'btn-default m-r-10'))."
					".form_button('delete_posts', $locale['delete'], $locale['forum_0177'], array('class' => 'btn-default'))."
					<div class='pull-right'>
						".form_button('go', $locale['forum_0208'], $locale['forum_0208'],
                        array('class' => 'btn-default pull-right m-l-10'))."
						".form_select('step', '', '',
                        array(
                            'options'     => $this->thread_info['mod_options'],
                            'placeholder' => $locale['forum_0200'],
                            'width'       => '250px',
                            'allowclear'  => TRUE,
                            'class'       => 'm-b-0 m-t-5',
                            'inline'      => TRUE
                        )
                    )."
					</div>\n";
                $this->thread_info['mod_form'] .= closeform();
                add_to_jquery("
				$('#check_all').bind('click', function() {
				    var allVal = [];
				    var thread_posts = $('input[name^=delete_post]:checkbox').prop('checked', true);
				    $('input[name^=delete_post]:checked').each(function(e) {
                        var val = $(this).val();
                        allVal.push($(this).val());
				    });
				    $('#delete_item_post').val(allVal);
				});
				$('#check_none').bind('click', function() {
				    $('#delete_item_post').val('');
				    var thread_posts = $('input[name^=delete_post]:checkbox').prop('checked', false); });
				");
            }

            $this->thread_info += array(
                'thread'               => $this->thread_data,
                'thread_id'            => $this->thread_data['thread_id'],
                'forum_id'             => $this->thread_data['forum_id'],
                'thread_tags'          => $this->thread_data['thread_tags'],
                'thread_tags_display'  => '',
                'forum_cat'            => isset($_GET['forum_cat']) && self::verify_forum($_GET['forum_cat']) ? $_GET['forum_cat'] : 0,
                'forum_branch'         => isset($_GET['forum_branch']) && self::verify_forum($_GET['forum_branch']) ? $_GET['forum_branch'] : 0,
                'forum_link'           => array(
                    'link'  => FORUM.'index.php?viewforum&amp;forum_id='.$this->thread_data['forum_id'].'&amp;forum_cat='.$this->thread_data['forum_cat'].'&amp;forum_branch='.$this->thread_data['forum_branch'],
                    'title' => $this->thread_data['forum_name']
                ),
                'thread_attachments'   => $attachments,
                'post_id'              => isset($_GET['post_id']) && self::verify_post($_GET['post_id']) ? $_GET['post_id'] : 0,
                'pid'                  => isset($_GET['pid']) && isnum($_GET['pid']) ? $_GET['pid'] : 0,
                'section'              => isset($_GET['section']) ? $_GET['section'] : '',
                'sort_post'            => isset($_GET['sort_post']) ? $_GET['sort_post'] : '',
                'forum_moderators'     => $this->moderator()->parse_forum_mods($this->thread_data['forum_mods']),
                'max_post_items'       => $thread_stat['post_count'],
                'post_firstpost'       => $thread_stat['first_post_id'],
                'post_lastpost'        => $thread_stat['last_post_id'],
                'posts_per_page'       => $forum_settings['posts_per_page'],
                'threads_per_page'     => $forum_settings['threads_per_page'],
                'lastvisited'          => (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time(),
                'allowed_post_filters' => array('oldest', 'latest', 'high'),
                'attachtypes'          => explode(',', $forum_settings['forum_attachtypes']),
                'quick_reply_form'     => $qr_form,
                'thread_bounty'        => $bounty_display,
                'poll_form'            => $poll_form,
                'post-filters'         => array(),
                'mod_options'          => [],
                'form_action'          => '',
                'open_post_form'       => '',
                'close_post_form'      => '',
                'mod_form'             => ''
            );

            if (!empty($this->thread_info['thread_tags'])) {
                $this->thread_info['thread_tags_display'] = $this->tag(FALSE)->display_thread_tags($this->thread_info['thread_tags']);
            }

            /**
             * Generate All Thread Buttons
             */
            $this->thread_info['buttons'] += array(
                'print'     => array(
                    'link'  => BASEDIR.'print.php?type=F&amp;item_id='.$this->thread_data['thread_id'].'&amp;rowstart='.$_GET['rowstart'],
                    'title' => $locale['forum_0178']
                ),
                'newthread' => $this->getThreadPermission('can_post') == TRUE ?
                    array(
                        'link'  => FORUM.'newthread.php?forum_id='.$this->thread_data['forum_id'],
                        'title' => $locale['forum_0264']
                    ) : [],
                'reply'     => $this->getThreadPermission('can_reply') == TRUE ?
                    array(
                        'link'  => FORUM.'viewthread.php?action=reply&amp;forum_id='.$this->thread_data['forum_id'].'&amp;thread_id='.$this->thread_data['thread_id'],
                        'title' => $locale['forum_0360']
                    ) : [],
                'poll'      => $this->getThreadPermission('can_create_poll') == TRUE ?
                    array(
                        'link'  => FORUM.'viewthread.php?action=newpoll&amp;forum_id='.$this->thread_data['forum_id'].'&amp;thread_id='.$this->thread_data['thread_id'],
                        'title' => $locale['forum_0366']
                    ) : [],
                'bounty'    => $this->getThreadPermission('can_start_bounty') == TRUE ? array(
                    'link'  => FORUM.'viewthread.php?action=newbounty&amp;forum_id='.$this->thread_data['forum_id'].'&amp;thread_id='.$this->thread_data['thread_id'],
                    'title' => $locale['forum_0399'],
                ) : [],
            );

            /**
             * Generate Post Filters
             */
            $this->thread_info['post-filters'][0] = array(
                'value'  => FORUM.'viewthread.php?thread_id='.$this->thread_data['thread_id'].'&amp;sort_post=oldest',
                'locale' => $locale['forum_0180']
            );
            $this->thread_info['post-filters'][1] = array(
                'value'  => FORUM.'viewthread.php?thread_id='.$this->thread_data['thread_id'].'&amp;sort_post=latest',
                'locale' => $locale['forum_0181']
            );
            if ($this->getThreadPermission("can_rate") == TRUE) {
                $this->thread_info['allowed-post-filters'][2] = 'high';
                $this->thread_info['post-filters'][2] = array(
                    'value'  => FORUM.'viewthread.php?thread_id='.$this->thread_info['thread_id'].'&amp;sort_post=high',
                    'locale' => $locale['forum_0182']
                );
            }

            $this->handle_quick_reply();

            $this->get_thread_post();

            //self::set_ThreadJs();
            // execute in the end.

        } else {
            redirect(FORUM.'index.php');
        }
    }

    /**
     * Get the entire thread structure on specific thread id.
     *
     * @param int $thread_id
     *
     * @return array
     */
    public static function get_thread($thread_id = 0) {
        $userdata = fusion_get_userdata();
        $userid = !empty($userdata['user_id']) ? (int)$userdata['user_id'] : 0;
        $data = [];
        $query = !empty(self::$custom_query) ? self::$custom_query : "SELECT t.*, f.*,
				#f2.forum_name 'forum_cat_name', f2.forum_access 'parent_access',
				u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_joined,
				IF (n.thread_id > 0, 1 , 0) as user_tracked,
				count(v.vote_user) 'thread_rated',
				count(p.forum_vote_user_id) 'poll_voted'
				FROM ".DB_FORUM_THREADS." t
				INNER JOIN ".DB_USERS." u on t.thread_author = u.user_id
				INNER JOIN ".DB_FORUMS." f ON t.forum_id=f.forum_id
				#LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id ##--------------this is wrong, as it only checks its own parent. it does not check ancestor root.
				LEFT JOIN ".DB_FORUM_VOTES." v on v.thread_id = t.thread_id AND v.vote_user='".intval($userid)."' AND v.forum_id=f.forum_id AND f.forum_type='4'				
				LEFT JOIN ".DB_FORUM_POLL_VOTERS." p on p.thread_id = t.thread_id AND p.forum_vote_user_id='".intval($userid)."' AND t.thread_poll='1'				
				LEFT JOIN ".DB_FORUM_THREAD_NOTIFY." n on n.thread_id = t.thread_id and n.notify_user = '".intval($userid)."'
				".(multilang_table('FO') ? " WHERE f.forum_language='".LANGUAGE."' AND " : " WHERE ")."
				".groupaccess('f.forum_access')." AND t.thread_id='".intval($thread_id)."' AND t.thread_hidden='0'";
        $result = dbquery($query);
        if (dbrows($result) > 0) {
            $data = dbarray($result);
            Moderator::define_forum_mods($data);
        }

        return (array)$data;
    }

    /**
     * Get post count, lastpost_id and first_post_id
     *
     * @param $thread_id
     *
     * @return array
     */
    private static function get_thread_stats($thread_id) {
        list($array['post_count'], $array['last_post_id'], $array['first_post_id']) = dbarraynum(dbquery("SELECT COUNT(post_id), MAX(post_id), MIN(post_id) FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($thread_id)."' AND post_hidden='0' GROUP BY thread_id"));
        if (!$array['post_count']) {
            redirect(FORUM.'index.php');
        } // exit no.2
        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $array['last_post_id'] ? $_GET['rowstart'] : 0; // secure against XSS
        return (array)$array;
    }

    /**
     * Set in full extent of forum permissions and current user thread permissions
     */
    private function setThreadPermission() {
        // Access the forum
        $this->thread_info['permissions']['can_access'] = (iMOD || checkgroup($this->thread_data['forum_access'])) ? TRUE : FALSE;
        // Create another thread under the same forum
        $this->thread_info['permissions']['can_post'] = (iMOD || (checkgroup($this->thread_data['forum_post']) && $this->thread_data['forum_lock'] == FALSE)) ? TRUE : FALSE;
        // Upload an attachment in this thread
        $this->thread_info['permissions']['can_upload_attach'] = $this->thread_data['forum_allow_attach'] == TRUE && (iMOD || (checkgroup($this->thread_data['forum_attach']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE)) ? TRUE : FALSE;
        // Download an attachment in this thread
        $this->thread_info['permissions']['can_download_attach'] = iMOD || ($this->thread_data['forum_allow_attach'] == TRUE && checkgroup($this->thread_data['forum_attach_download'])) ? TRUE : FALSE;
        // Post a reply in this thread
        $this->thread_info['permissions']['can_reply'] = $this->thread_data['thread_postcount'] > 0 && (iMOD || (checkgroup($this->thread_data['forum_reply']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE)) ? TRUE : FALSE;
        // Create a poll
        $this->thread_info['permissions']['can_create_poll'] = $this->thread_data['thread_poll'] == FALSE && $this->thread_data['forum_allow_poll'] == TRUE && (iMOD || (checkgroup($this->thread_data['forum_poll']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE)) ? TRUE : FALSE;
        // Edit a poll (modify the poll)
        $this->thread_info['permissions']['can_edit_poll'] = $this->thread_data['thread_poll'] == TRUE && (iMOD || (checkgroup($this->thread_data['forum_poll']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE && $this->thread_data['thread_author'] == fusion_get_userdata('user_id'))) ? TRUE : FALSE;
        // Can vote a poll
        $this->thread_info['permissions']['can_vote_poll'] = $this->thread_data['poll_voted'] == FALSE && (iMOD || (checkgroup($this->thread_data['forum_vote']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE)) ? TRUE : FALSE;
        // Can vote in this thread
        $this->thread_info['permissions']['can_rate'] = $this->thread_data['forum_type'] == 4 && (iMOD || (checkgroup($this->thread_data['forum_post_ratings']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE)) ? TRUE : FALSE;
        // Can accept an answer
        $this->thread_info['permissions']['can_answer'] = $this->thread_data['forum_type'] == 4 && $this->thread_data['thread_answered'] == FALSE && $this->thread_data['thread_locked'] == FALSE && ($this->thread_data['thread_author'] == fusion_get_userdata('user_id') || iMOD) ? TRUE : FALSE;
        // Can start a bounty
        $this->thread_info['permissions']['can_start_bounty'] = $this->thread_data['forum_type'] == 4 && !$this->thread_data['thread_bounty'] && $this->thread_data['thread_locked'] == FALSE && fusion_get_userdata('user_reputation') >= 50 ? TRUE : FALSE;
        // Can edit a bounty
        $this->thread_info['permissions']['can_edit_bounty'] = $this->thread_data['forum_type'] == 4 && $this->thread_data['thread_bounty'] && $this->thread_data['thread_locked'] == FALSE && ($this->thread_data['thread_bounty_user'] == fusion_get_userdata('user_id') || iMOD) ? TRUE : FALSE;
        // Can award bounty
        $this->thread_info['permissions']['can_award_bounty'] = $this->thread_data['forum_type'] == 4 && $this->thread_data['thread_bounty'] && ($this->thread_data['thread_bounty_user'] == fusion_get_userdata('user_id')) ? TRUE : FALSE;
    }

    /**
     * Get the relevant permissions of the current thread permission configuration
     * @param null $key
     *
     * @return null
     */
    public function getThreadPermission($key = NULL) {
        if (!empty($this->thread_info['permissions'])) {
            if (isset($this->thread_info['permissions'][$key])) {
                return $this->thread_info['permissions'][$key];
            }

            return $this->thread_info['permissions'];
        }

        return NULL;
    }

    /**
     * Handle post of Quick Reply Form
     */
    private function handle_quick_reply() {

        $forum_settings = $this->get_forum_settings();

        $locale = fusion_get_locale();

        $userdata = fusion_get_userdata();

        if (isset($_POST['post_quick_reply'])) {

            if ($this->getThreadPermission("can_reply") && \defender::safe()) {
                $this->thread_data = $this->thread_info['thread'];
                require_once INCLUDES."flood_include.php";
                if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice
                    $post_data = array(
                        'post_id'         => 0,
                        'forum_id'        => $this->thread_data['forum_id'],
                        'thread_id'       => $this->thread_data['thread_id'],
                        'post_message'    => form_sanitizer($_POST['post_message'], '', 'post_message'),
                        'post_showsig'    => isset($_POST['post_showsig']) ? 1 : 0,
                        'post_smileys'    => isset($_POST['post_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si",
                            $_POST['post_message']) ? 1 : 0,
                        'post_author'     => $userdata['user_id'],
                        'post_datestamp'  => time(),
                        'post_ip'         => USER_IP,
                        'post_ip_type'    => USER_IP_TYPE,
                        'post_edituser'   => 0,
                        'post_edittime'   => 0,
                        'post_editreason' => '',
                        'post_hidden'     => 0,
                        'post_locked'     => $forum_settings['forum_edit_lock'] || isset($_POST['post_locked']) ? 1 : 0
                    );

                    if (\defender::safe()) { // post message is invalid or whatever is invalid

                        $update_forum_lastpost = FALSE;

                        // Prepare forum merging action
                        $last_post_author = dbarray(dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_data['thread_id']."' ORDER BY post_id DESC LIMIT 1"));
                        if ($last_post_author['post_author'] == $post_data['post_author'] && $this->thread_data['forum_merge']) {
                            $last_message = dbarray(dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_data['thread_id']."' ORDER BY post_id DESC"));
                            $post_data['post_id'] = $last_message['post_id'];
                            $post_data['post_message'] = $last_message['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate",
                                    time()).":\n".$post_data['post_message'];
                            dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', array('primary_key' => 'post_id'));
                        } else {
                            $update_forum_lastpost = TRUE;
                            dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', array('primary_key' => 'post_id'));
                            $post_data['post_id'] = dblastid();
                            dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$post_data['post_author']."'");
                        }

                        // Update stats in forum and threads
                        if ($update_forum_lastpost) {
                            // find all parents and update them
                            $list_of_forums = get_all_parent(dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), $this->thread_data['forum_id']);
                            if (!empty($list_of_forums)) {
                                foreach ($list_of_forums as $fid) {
                                    dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$fid."'");
                                }
                            }
                            // update current forum
                            dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$this->thread_data['forum_id']."'");
                            // update current thread
                            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$post_data['post_id']."', thread_postcount=thread_postcount+1, thread_lastuser='".$post_data['post_author']."' WHERE thread_id='".$this->thread_data['thread_id']."'");
                        }
                        // set notify
                        if ($forum_settings['thread_notify'] == TRUE && isset($_POST['notify_me']) && $this->thread_data['thread_id']) {
                            if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY,
                                "thread_id='".$this->thread_data['thread_id']."' AND notify_user='".$post_data['post_author']."'")
                            ) {
                                dbquery("INSERT INTO ".DB_FORUM_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$this->thread_data['thread_id']."', '".time()."', '".$post_data['post_author']."', '1')");
                            }
                        }
                    }

                    redirect(INFUSIONS."forum/postify.php?post=reply&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;thread_id=".intval($post_data['thread_id'])."&amp;post_id=".intval($post_data['post_id']));
                }
            }
        }
    }

    /**
     * Get thread posts info
     *
     * @todo: optimize post reply with a subnested query to reduce post^n queries.
     */
    private function get_thread_post() {

        $forum_settings = $this->get_forum_settings();

        $userdata = fusion_get_userdata();

        $locale = fusion_get_locale();

        $user_sig_module = column_exists(DB_USERS, 'user_sig');

        $user_web_module = column_exists(DB_USERS, 'user_web');

        $userid = isset($userdata['user_id']) ? (int)$userdata['user_id'] : 0;
        switch ($this->thread_info['sort_post']) {
            case 'oldest':
                $sortCol = 'post_datestamp ASC';
                break;
            case 'latest':
                $sortCol = 'post_datestamp DESC';
                break;
            case 'high':
                $sortCol = 'vote_points DESC';
                break;
            default:
                $sortCol = 'post_datestamp ASC';
        }

        // Thread id and forum id mismatch bug

        // @todo: where to calculate has voted without doing it in while loop?

        require_once INCLUDES."mimetypes_include.php";
        // post query
        $result = dbquery("
					SELECT p.*, t.thread_id,
					u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_posts, u.user_groups,
					u.user_joined, u.user_lastvisit, u.user_ip, ".($user_sig_module ? " u.user_sig," : "").($user_web_module ? " u.user_web," : "")."
					u2.user_name AS edit_name, u2.user_status AS edit_status,
					count(a1.attach_id) 'attach_image_count',
					count(a2.attach_id) 'attach_files_count',
					SUM(v.vote_points) 'vote_points', 
					IF(v2.vote_id, 1, 0) 'has_voted', v2.vote_points 'has_voted_points'  					
					FROM ".DB_FORUM_POSTS." p					
					INNER JOIN ".DB_FORUM_THREADS." t ON t.thread_id = p.thread_id
					LEFT JOIN ".DB_FORUM_VOTES." v ON v.post_id=p.post_id 					
					LEFT JOIN ".DB_FORUM_VOTES." v2 ON v2.post_id=p.post_id AND v2.vote_user='".fusion_get_userdata('user_id')."'					
					LEFT JOIN ".DB_USERS." u ON p.post_author = u.user_id
					LEFT JOIN ".DB_USERS." u2 ON p.post_edituser = u2.user_id AND post_edituser > '0'
					LEFT JOIN ".DB_FORUM_ATTACHMENTS." a1 on a1.post_id = p.post_id AND a1.attach_mime IN ('".implode(",", img_mimeTypes())."')
					LEFT JOIN ".DB_FORUM_ATTACHMENTS." a2 on a2.post_id = p.post_id AND a2.attach_mime NOT IN ('".implode(",", img_mimeTypes())."')
					WHERE p.thread_id='".intval($_GET['thread_id'])."' AND post_hidden='0'
					".($this->thread_info['thread']['forum_type'] == '4' ? "OR p.post_id='".intval($this->thread_info['post_firstpost'])."'" : '')."
					GROUP by p.post_id
					ORDER BY $sortCol LIMIT ".intval($_GET['rowstart']).", ".intval($forum_settings['posts_per_page'])
        );

        $this->thread_info['post_rows'] = dbrows($result);

        if ($this->thread_info['post_rows'] > 0) {

            $response = $this->mood()->post_mood();
            if ($response) {
                redirect(FUSION_REQUEST);
            }

            /* Set Threads Navigation */
            $this->thread_info['thread_posts'] = format_word($this->thread_info['post_rows'], $locale['fmt_post']);
            $this->thread_info['page_nav'] = '';
            if ($this->thread_info['max_post_items'] > $this->thread_info['posts_per_page']) {
                $this->thread_info['page_nav'] = "<div class='pull-right'>".makepagenav($_GET['rowstart'],
                        $this->thread_info['posts_per_page'],
                        $this->thread_info['max_post_items'],
                        3,
                        FORUM."viewthread.php?thread_id=".$this->thread_info['thread']['thread_id'].(isset($_GET['highlight']) ? "&amp;highlight=".urlencode($_GET['highlight']) : '')."&amp;")."</div>";
            }

            add_to_jquery("
            $('.reason_button').bind('click', function(e) {
                var reason_div = $(this).data('target');
                console.log(reason_div);
                if ( $('#'+reason_div).is(':visible') ) {
                     $('#'+reason_div).slideUp();
                } else {
                     $('#'+reason_div).slideDown();
                }
            });
            ");

            $i = 1;

            while ($pdata = dbarray($result)) {

                // Format Post Message
                $post_message = empty($pdata['post_smileys']) ? parsesmileys($pdata['post_message']) : $pdata['post_message'];
                $post_message = nl2br(parseubb($post_message));
                if (isset($_GET['highlight'])) {
                    $post_message = "<div class='search_result'>".$post_message."</div>\n";
                }

                // Marker
                $marker = array(
                    'link'  => "#post_".$pdata['post_id'],
                    "title" => "#".($i + $_GET['rowstart']),
                    'id'    => "post_".$pdata['post_id']
                );
                $post_marker = "<a class='marker' href='".$marker['link']."' id='".$marker['id']."'>".$marker['title']."</a>";
                $post_marker .= "<a title='".$locale['forum_0241']."' href='#top'><i class='entypo up-open'></i></a>\n";

                // Post Attachments
                $post_attachments = '';
                if ($pdata['attach_files_count'] || $pdata['attach_image_count']) {
                    if ($this->getThreadPermission("can_download_attach")) {
                        $attachResult = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".intval($pdata['post_id'])."'");
                        if (dbrows($attachResult) > 0) {
                            $aImage = "";
                            $aFiles = "";
                            while ($attachData = dbarray($attachResult)) {
                                if (in_array($attachData['attach_mime'], img_mimeTypes())) {
                                    $aImage .= display_image_attach($attachData['attach_name'], "50", "50", $pdata['post_id'])."\n";
                                } else {
                                    $aFiles .= "<div class='display-inline-block'><i class='entypo attach'></i><a href='".INFUSIONS."forum/viewthread.php?thread_id=".$pdata['thread_id']."&amp;getfile=".$attachData['attach_id']."'>".$attachData['attach_name']."</a>&nbsp;";
                                    $aFiles .= "[<span class='small'>".parsebytesize(filesize(INFUSIONS."forum/attachments/".$attachData['attach_name']))." / ".$attachData['attach_count'].$locale['forum_0162']."</span>]</div>\n";
                                }
                            }
                            if (!empty($aFiles)) {
                                $post_attachments .= "<div class='emulated-fieldset'>\n";
                                $post_attachments .= "<span class='emulated-legend'>".profile_link($pdata['user_id'], $pdata['user_name'],
                                        $pdata['user_status']).$locale['forum_0154'].($pdata['attach_files_count'] > 1 ? $locale['forum_0158'] : $locale['forum_0157'])."</span>\n";
                                $post_attachments .= "<div class='attachments-list m-t-10'>".$aFiles."</div>\n";
                                $post_attachments .= "</div>\n";
                            }

                            if (!empty($aImage)) {
                                $post_attachments .= "<div class='emulated-fieldset'>\n";
                                $post_attachments .= "<span class='emulated-legend'>".profile_link($pdata['user_id'], $pdata['user_name'],
                                        $pdata['user_status']).$locale['forum_0154'].($pdata['attach_image_count'] > 1 ? $locale['forum_0156'] : $locale['forum_0155'])."</span>\n";
                                $post_attachments .= "<div class='attachments-list'>".$aImage."</div>\n";
                                $post_attachments .= "</div>\n";
                                if (!defined('COLORBOX')) {
                                    define('COLORBOX', TRUE);
                                    add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
                                    add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
                                    add_to_jquery("$('a[rel^=\"attach\"]').colorbox({ current: '".$locale['forum_0159']." {current} ".$locale['forum_0160']." {total}',width:'80%',height:'80%'});");
                                }
                            }
                        } else {
                            $post_attachments = "Failed to fetch the attachment";
                        }
                    } else {
                        $post_attachments = "<small><i class='fa fa-clipboard'></i> ".$locale['forum_0184']."</small>\n";
                    }
                }

                $pdata['user_ip'] = ($forum_settings['forum_ips'] && iMOD) ? $locale['forum_0268'].' '.$pdata['post_ip'] : '';

                $pdata += array(
                    'user_online'        => $pdata['user_lastvisit'] >= time() - 3600 ? TRUE : FALSE,
                    'is_first_post'      => $pdata['post_id'] == $this->thread_info['post_firstpost'] ? TRUE : FALSE,
                    'is_last_post'       => $pdata['post_id'] == $this->thread_info['post_lastpost'] ? TRUE : FALSE,
                    'user_profile_link'  => profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']),
                    'user_avatar_image'  => display_avatar($pdata, '50px', FALSE, FALSE, 'img-rounded'),
                    'user_post_count'    => format_word($pdata['user_posts'], $locale['fmt_post']),
                    'print'              => array(
                        'link'  => BASEDIR.'print.php?type=F&amp;item_id='.$_GET['thread_id'].'&amp;post='.$pdata['post_id'].'&amp;nr='.($i + $_GET['rowstart']),
                        'title' => $locale['forum_0179']
                    ),
                    'post_marker'        => $post_marker,
                    'marker'             => $marker,
                    'post_attachments'   => $post_attachments,
                    'post_reply_message' => '',
                    'post_bounty'        => [],
                );

                $pdata['post_message'] = $post_message;

                // Who has replied to this post.
                /**
                 * This will drag the entire forum down with +1 query per forum post.
                 */
                $reply_result = dbquery("
                SELECT p.post_id, p.post_datestamp, u.user_id, u.user_name, u.user_status
                FROM ".DB_FORUM_POSTS." p
                INNER JOIN ".DB_USERS." u ON u.user_id = p.post_author
                WHERE p.post_cat= ".intval($pdata['post_id'])."
                GROUP BY u.user_id ORDER BY p.post_datestamp DESC
                ");
                if (dbrows($reply_result)) {
                    // who has replied
                    $reply_sender = "";
                    $last_datestamp = 0;
                    while ($r_data = dbarray($reply_result)) {
                        $reply_sender[$r_data['post_id']] = "
                        <a class='reply_sender' href='".FUSION_REQUEST."#post_".$r_data['post_id']."'>\n
                        ".profile_link($r_data['user_id'], $r_data['user_name'], $r_data['user_status'], "", FALSE)."
                        </a>
                        ";
                        $last_datestamp = $r_data['post_datestamp'];
                    }
                    $senders = implode(", ", $reply_sender);
                    $pdata['post_reply_message'] = "<i class='fa fa-reply fa-fw'></i>".sprintf($locale['forum_0527'], $senders, timer($last_datestamp));
                }

                // Displays mood buttons
                /**
                 * This will drag the forum down with +1 query per post.
                 */
                $pdata['post_mood'] = $this->mood()->set_PostData($pdata)->display_mood_buttons();

                $pdata['post_mood_message'] = ($pdata['post_mood']) ? $this->mood()->get_mood_message() : '';

                /*
                 * Bounty payment
                 */
                if ($this->getThreadPermission('can_award_bounty') && $pdata['post_author'] !== fusion_get_userdata('user_id')) {
                    $pdata['post_bounty'] = array(
                        'link'  => FORUM.'viewthread.php?action=award&amp;forum_id='.$pdata['forum_id'].'&amp;thread_id='.$pdata['thread_id'].'&amp;post_id='.$pdata['post_id'],
                        'title' => $locale['forum_4107']
                    );
                }

                /**
                 * User Stuffs, Sig, User Message, Web
                 */
                // Quote & Edit Link
                if ($this->getThreadPermission('can_reply')) {
                    if (!$this->thread_info['thread']['thread_locked']) {

                        $pdata['post_quote'] = array(
                            'link'  => INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id']."&amp;quote=".$pdata['post_id'],
                            'title' => $locale['forum_0266']
                        );
                        if (iMOD || (
                                (($forum_settings['forum_edit_lock'] == TRUE && $pdata['is_last_post'] || $forum_settings['forum_edit_lock'] == FALSE))
                                && ($userdata['user_id'] == $pdata['post_author'])
                                && ($forum_settings['forum_edit_timelimit'] <= 0 || time() - $forum_settings['forum_edit_timelimit'] * 60 < $pdata['post_datestamp'])
                            )
                        ) {
                            $pdata['post_edit'] = array(
                                'link'  => INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                                'title' => $locale['forum_0265']
                            );
                        }
                        $pdata['post_reply'] = array(
                            'link'  => INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                            'title' => $locale['forum_0509']
                        );
                    } elseif (iMOD) {
                        $pdata['post_edit'] = array(
                            'link'  => INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                            'title' => $locale['forum_0265']
                        );
                    }
                }

                // rank img
                if ($pdata['user_level'] <= USER_LEVEL_ADMIN) {
                    if ($forum_settings['forum_ranks']) {
                        $pdata['user_rank'] = self::show_forum_rank($pdata['user_posts'], $pdata['user_level'], $pdata['user_groups']);
                    } else {
                        $pdata['user_rank'] = getuserlevel($pdata['user_level']);
                    }
                } else {
                    if ($forum_settings['forum_ranks']) {
                        $pdata['user_rank'] = iMOD ? self::show_forum_rank($pdata['user_posts'], 104, $pdata['user_groups']) : self::show_forum_rank($pdata['user_posts'], $pdata['user_level'], $pdata['user_groups']);
                    } else {
                        $pdata['user_rank'] = iMOD ? $locale['userf1'] : getuserlevel($pdata['user_level']);
                    }
                }

                // Website
                if ($pdata['user_web'] && (iADMIN || $pdata['user_status'] != 6 && $pdata['user_status'] != 5)) {
                    $user_web_url = !preg_match("@^http(s)?\:\/\/@i", $pdata['user_web']) ? "http://".$pdata['user_web'] : $pdata['user_web'];
                    $pdata['user_web'] = array(
                        'link'  => $user_web_url,
                        'title' => $locale['forum_0364']
                    );
                } else {
                    $pdata['user_web'] = array('link' => '', 'title' => '');
                }

                // PM link
                $pdata['user_message'] = array('link' => '', 'title' => '');
                if (iMEMBER && $pdata['user_id'] != $userdata['user_id'] && (iADMIN || $pdata['user_status'] != 6 && $pdata['user_status'] != 5)) {
                    $pdata['user_message'] = array(
                        'link'  => BASEDIR.'messages.php?msg_send='.$pdata['user_id'],
                        "title" => $locale['send_message']
                    );
                }

                // User Sig
                if (isset($pdata['user_sig']) && $pdata['user_sig'] && isset($pdata['post_showsig']) && $pdata['user_status'] != 6 && $pdata['user_status'] != 5) {
                    $pdata['user_sig'] = nl2br(parseubb(parsesmileys(stripslashes($pdata['user_sig'])), "b|i|u||center|small|url|mail|img|color"));
                } else {
                    $pdata['user_sig'] = "";
                }

                // Voting - need up or down link - accessible to author also the vote
                // answered and on going questions.
                // Answer rating
                $pdata['vote_message'] = '';
                //echo $data['forum_type'] == 4 ? "<br/>\n".(number_format($data['thread_postcount']-1)).$locale['forum_0365']."" : ''; // answers
                // form components
                $pdata['post_checkbox'] = iMOD ? "<input type='checkbox' name='delete_post[]' value='".$pdata['post_id']."'/>" : '';
                // Voting up
                $pdata['post_votebox'] = '';
                $pdata['vote_answered'] = '';
                $pdata['post_answer_check'] = '';

                // Support Type
                if ($this->thread_info['thread']['forum_type'] == 4) {
                    // If I am author, I can mark as answered
                    if ($this->thread_info['thread']['thread_author'] == fusion_get_userdata('user_id') or iMOD) {
                        // all post items have checkbox greyed.
                        // if thread is answered, then just this post is answer have checkbox
                        //print_p($this->thread_info);
                        if ($this->thread_info['thread']['thread_answered'] && $pdata['post_answer']) {
                            // Is Answer
                            $pdata['vote_answered'] = [
                                'link'  => FORUM."postify.php?post=answer&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                                'title' => $locale['forum_0513'] //0513
                            ];
                            $pdata['post_answer_check'] = "<a href='".$pdata['vote_answered']['link']."' class='answer_button answer_checked' title='".$pdata['vote_answered']['title']."'><i class='fa fa-check fa-2x'></i></a>";
                        } else {
                            // Is not an answer
                            $pdata['vote_answered'] = [
                                'link'  => FORUM."postify.php?post=answer&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                                'title' => $locale['forum_0512'] //0512
                            ];
                            $pdata['post_answer_check'] = "<a href='".$pdata['vote_answered']['link']."' class='answer_button answer_unchecked' title='".$pdata['vote_answered']['title']."'><i class='fa fa-check fa-2x'></i></a>";
                        }
                    }

                    if ($this->getThreadPermission('can_rate')) { // can vote.
                        $pdata['vote_up'] = array(
                            'link'   => INFUSIONS."forum/postify.php?post=voteup&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                            "title"  => $locale['forum_0510'],
                            'active' => $pdata['has_voted'] && $pdata['has_voted_points'] > 0 ? TRUE : FALSE,
                        );
                        $pdata['vote_down'] = array(
                            'link'   => INFUSIONS."forum/postify.php?post=votedown&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                            "title"  => $locale['forum_0511'],
                            'active' => $pdata['has_voted'] && $pdata['has_voted_points'] < 0 ? TRUE : FALSE,
                        );
                        $pdata['post_votebox'] = "<div class='text-center post_vote_box'>\n";
                        $pdata['post_votebox'] .= "<a href='".$pdata['vote_up']['link']."' class='text-center vote_up".($pdata['vote_up']['active'] ? " text-warning" : '')."' title='".$locale['forum_0510']."'>\n<i class='fa fa-caret-up fa-2x'></i></a>";
                        $pdata['post_votebox'] .= "<h3 class='m-0'>".(!empty($pdata['vote_points']) ? $pdata['vote_points'] : 0)."</h3>\n";
                        $pdata['post_votebox'] .= "<a href='".$pdata['vote_down']['link']."' class='text-center vote_down".($pdata['vote_down']['active'] ? " text-warning" : '')."' title='".$locale['forum_0511']."'>\n<i class='fa fa-caret-down fa-2x'></i></a>";
                        $pdata['post_votebox'] .= "</div>\n";
                    } else {
                        $pdata['post_votebox'] = "<div class='text-center'>\n";
                        $pdata['post_votebox'] .= "<h3 class='m-0'>".(!empty($pdata['vote_points']) ? $pdata['vote_points'] : 0)."</h3>\n";
                        $pdata['post_votebox'] .= "</div>\n";
                    }

                }

                $pdata['post_edit_reason'] = '';
                if ($pdata['post_edittime']) {
                    $edit_reason = "<div class='edit_reason small'>".$locale['forum_0164'].profile_link($pdata['post_edituser'], $pdata['edit_name'], $pdata['edit_status']).$locale['forum_0167'].showdate("forumdate", $pdata['post_edittime'])." - ";
                    if ($pdata['post_editreason'] && iMEMBER) {
                        $edit_reason .= "<a id='reason_pid_".$pdata['post_id']."' rel='".$pdata['post_id']."' class='reason_button pointer' data-target='reason_div_pid_".$pdata['post_id']."'>";
                        $edit_reason .= "<strong>".$locale['forum_0165']."</strong>";
                        $edit_reason .= "</a></div>";
                        $edit_reason .= "<div id='reason_div_pid_".$pdata['post_id']."' class='post_reason' style='display:none;'><span class='text-lighter'>- ".$pdata['post_editreason']."</span></div>\n";
                    } else {
                        $edit_reason .= "</div>";
                    }
                    $pdata['post_edit_reason'] = $edit_reason;
                    //$this->edit_reason = TRUE;
                }

                // Custom Post Message Link/Buttons
                $pdata['post_links'] = '';
                $pdata['post_links'] .= !empty($pdata['post_quote']) ? "<a class='btn btn-xs btn-default' title='".$pdata['post_quote']["title"]."' href='".$pdata['post_quote']['link']."'>".$pdata['post_quote']['title']."</a>\n" : '';
                $pdata['post_links'] .= !empty($pdata['post_edit']) ? "<a class='btn btn-xs btn-default' title='".$pdata['post_edit']["title"]."' href='".$pdata['post_edit']['link']."'>".$pdata['post_edit']['title']."</a>\n" : '';
                $pdata['post_links'] .= !empty($pdata['print']) ? "<a class='btn btn-xs btn-default' title='".$pdata['print']["title"]."' href='".$pdata['print']['link']."'>".$pdata['print']['title']."</a>\n" : '';
                $pdata['post_links'] .= !empty($pdata['user_web']) ? "<a class='btn btn-xs btn-default' class='forum_user_actions' href='".$pdata['user_web']['link']."' target='_blank'>".$pdata['user_web']['title']."</a>\n" : '';
                $pdata['post_links'] .= !empty($pdata['user_message']) ? "<a class='btn btn-xs btn-default' href='".$pdata['user_message']['link']."' target='_blank'>".$pdata['user_message']['title']."</a>\n" : '';
                // Post Date
                $pdata['post_date'] = $locale['forum_0524']." ".timer($pdata['post_datestamp'])." - ".showdate('forumdate', $pdata['post_datestamp']);
                $pdata['post_shortdate'] = $locale['forum_0524']." ".timer($pdata['post_datestamp']);
                $pdata['post_longdate'] = $locale['forum_0524']." ".showdate('forumdate', $pdata['post_datestamp']);

                $this->thread_info['post_items'][$pdata['post_id']] = $pdata;
                $i++;
            }
            if (iMOD) {
                // pass the checkbox value to an input field
                add_to_jquery("
                var checks = $('input[name^=delete_post]:checkbox');
                checks.on('change', function() {
                    var string = checks.filter(':checked').map(function(i,v){
                    return this.value;
                    }).get().join(',');
                    $('#delete_item_post').val(string);
                });
                ");
            }
        }
    }

    /**
     * New Status
     */
    public function set_thread_visitor() {
        if (iMEMBER) {
            $userdata = fusion_get_userdata();
            $thread_match = $this->thread_info['thread_id']."\|".$this->thread_info['thread']['thread_lastpost']."\|".$this->thread_info['thread']['forum_id'];
            if (($this->thread_info['thread']['thread_lastpost'] > $this->thread_info['lastvisited']) && !preg_match("(^\.{$thread_match}$|\.{$thread_match}\.|\.{$thread_match}$)", $userdata['user_threads'])) {
                dbquery("UPDATE ".DB_USERS." SET user_threads='".$userdata['user_threads'].".".stripslashes($thread_match)."' WHERE user_id='".$userdata['user_id']."'");
            }
        }
    }

}