<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: threads.php
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
    private static $custom_query = '';

    /**
     * Get thread structure on specific forum id.
     *
     * @param int  $forum_id
     * @param bool $filter
     *
     * @return array
     */
    public static function getForumThread($forum_id = 0, $filter = FALSE) {

        $default_filter = [
            'count_query' => '',
            'query'       => '',
        ];
        $filter += $default_filter;

        /* Redo and remove all joins */

        $info = [];
        $locale = fusion_get_locale();
        $forum_settings = ForumServer::getForumSettings();
        $userdata = fusion_get_userdata();
        $userdata['user_id'] = !empty($userdata['user_id']) ? intval($userdata['user_id']) : 0;
        $lastVisited = defined('LASTVISITED') ? LASTVISITED : time();

        $join = '';
        if (isset($_GET['type']) && $_GET['type'] == 'poll') {
            $join = "LEFT JOIN ".DB_FORUM_POSTS." p1 ON p1.thread_id=t.thread_id AND tf.forum_id=p1.forum_id
            LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id
            LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = t.thread_id AND p1.post_id = v.post_id";
        }

        //print_p($filter);
        /**
         * Get threads with filter conditions (XSS prevention)
         * The latest Post requires a simple query replacement.
         * #AND tf.forum_lastpost = t.thread_lastpost AND tf.forum_lastpostid = t.thread_lastpostid
         * The rows is thread that has the last post.... \\\\\\
         *
         */
        $thread_query = $filter['count_query'] ?: "
        SELECT count(t.thread_id) 'thread_count',
        t.thread_id
        FROM ".DB_FORUM_THREADS." t
        INNER JOIN ".DB_FORUMS." tf ON t.forum_id=tf.forum_id
        LEFT JOIN ".DB_FORUM_ATTACHMENTS." a on a.thread_id = t.thread_id
        ".$join."
        WHERE ".($forum_id ? " t.forum_id='".intval($forum_id)."' AND " : "")." t.thread_hidden='0' AND ".groupaccess('tf.forum_access')."
        ".(isset($filter['condition']) ? $filter['condition'] : '')." GROUP BY t.thread_id";

        if (!empty($filter['debug'])) {
            print_p($thread_query);
        }

        $thread_result = dbquery($thread_query);
        $info['thread_max_rows'] = dbrows($thread_result);

        $info['item'][$forum_id]['forum_threadcount'] = 0;
        $info['item'][$forum_id]['forum_threadcount_word'] = format_word($info['thread_max_rows'], $locale['fmt_thread']);

        if ($info['thread_max_rows']) {

            $info['threads']['pagenav'] = '';
            $info['threads']['pagenav2'] = '';

            // anti-XSS filtered rowstart
            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['thread_max_rows'] ? $_GET['rowstart'] : 0;

            $thread_query = $filter['query'] ?: "
            SELECT t.*, tf.forum_type, tf.forum_name, tf.forum_cat,
            IF (n.thread_id > 0, 1 , 0) 'user_tracked',
            count(v.vote_user) 'thread_rated',
            count(pv.forum_vote_user_id) 'poll_voted',
            count(v.post_id) AS vote_count,
            count(a.attach_id) AS attach_count, a.attach_id
            FROM ".DB_FORUM_THREADS." t
            INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
            LEFT JOIN ".DB_FORUM_VOTES." v on v.thread_id = t.thread_id AND v.vote_user='".$userdata['user_id']."' AND v.forum_id = t.forum_id AND tf.forum_type='4'
            LEFT JOIN ".DB_FORUM_POLL_VOTERS." pv on pv.thread_id = t.thread_id AND pv.forum_vote_user_id='".$userdata['user_id']."' AND t.thread_poll=1
            LEFT JOIN ".DB_FORUM_ATTACHMENTS." a on a.thread_id = t.thread_id
            LEFT JOIN ".DB_FORUM_THREAD_NOTIFY." n on n.thread_id = t.thread_id and n.notify_user = '".$userdata['user_id']."'
            WHERE ".($forum_id ? "t.forum_id='".$forum_id."' AND " : "")."t.thread_hidden='0' AND ".groupaccess('tf.forum_access')."
            ".(isset($filter['condition']) ? $filter['condition'] : '')." ".(multilang_table("FO") ? "AND ".in_group('tf.forum_language', LANGUAGE) : '')."
            GROUP BY t.thread_id
            ".(isset($filter['order']) ? $filter['order'] : '');

            $thread_query .= " LIMIT ".intval($_GET['rowstart']).", ".$forum_settings['threads_per_page'];

            if (!empty($filter['debug']))
                print_p($thread_query);

            $cthread_result = dbquery($thread_query);
            $rows = dbrows($cthread_result);

            if ($rows) {

                while ($threads = dbarray($cthread_result)) {
                    if (iMEMBER) {
                        $threads['track_button'] = [
                            "link"    => FORUM."postify.php?post=on&forum_id=".$threads['forum_id']."&thread_id=".$threads['thread_id'],
                            "title"   => $locale['forum_0175'],
                            "onclick" => "",
                        ];
                        if (isset($threads['user_tracked']) && $threads['user_tracked']) {
                            $threads['track_button'] = [
                                "link"    => FORUM."postify.php?post=off&forum_id=".$threads['forum_id']."&thread_id=".$threads['thread_id'],
                                "title"   => $locale['forum_0174'],
                                "onclick" => "onclick=\"return confirm('".$locale['global_060']."');\"",
                            ];
                        }
                    }

                    if (!isset($threads['attach_count'])) {
                        $threads['attach_count'] = dbcount("(attach_id)", DB_FORUM_ATTACHMENTS, "thread_id=:current_thread", [':current_thread' => $threads['thread_id']]);
                    }
                    if (!isset($threads['vote_count'])) {
                        $threads['vote_count'] = dbcount("(post_id)", DB_FORUM_VOTES, "thread_id=:current_thread", [':current_thread' => $threads['thread_id']]);
                    }

                    $threads += [
                        'author_name'      => '',
                        'author_status'    => '',
                        'author_avatar'    => '',
                        'last_user_name'   => '',
                        'last_user_status' => '',
                        'last_user_avatar' => '',
                    ];

                    $user1 = fusion_get_user($threads['thread_author']);
                    if (!empty($user1['user_id'])) {
                        $threads['author_name'] = $user1['user_name'];
                        $threads['author_status'] = $user1['user_status'];
                        $threads['author_avatar'] = $user1['user_avatar'];
                    }

                    $user2 = fusion_get_user($threads['thread_lastuser']);
                    if (!empty($user2['user_id'])) {
                        $threads['last_user_name'] = $user2['user_name'];
                        $threads['last_user_status'] = $user2['user_status'];
                        $threads['last_user_avatar'] = $user2['user_avatar'];
                    }

                    $icon = "";
                    $match_regex = $threads['thread_id']."\|".$threads['thread_lastpost']."\|".$threads['forum_id'];
                    if ($threads['thread_lastpost'] > $lastVisited) {
                        if (iMEMBER && ($threads['thread_lastuser'] == $userdata['user_id'] ||
                                preg_match("(^\.$match_regex$|\.$match_regex\.|\.$match_regex$)", $userdata['user_threads']))
                        ) {
                            $icon = "<i class='".self::getForumIcons('thread')."' title='".$locale['forum_0261']."'></i>";
                        } else {
                            $icon = "<i class='".self::getForumIcons('new')."' title='".$locale['forum_0260']."'></i>";
                        }
                    }

                    $author = [
                        'user_id'     => $threads['thread_author'],
                        'user_name'   => $threads['author_name'],
                        'user_status' => $threads['author_status'],
                        'user_avatar' => $threads['author_avatar']
                    ];

                    $lastuser = [
                        'user_id'     => $threads['thread_lastuser'],
                        'user_name'   => $threads['last_user_name'],
                        'user_status' => $threads['last_user_status'],
                        'user_avatar' => $threads['last_user_avatar']
                    ];

                    // Automatic link to the latest post
                    $thread_rowstart = '';
                    if (!empty($threads['thread_postcount']) && !empty($forum_settings['posts_per_page'])) {
                        if ($threads['thread_postcount'] > $forum_settings['posts_per_page']) {
                            $thread_rowstart = $forum_settings['posts_per_page'] * floor(($threads['thread_postcount'] - 1) / $forum_settings['posts_per_page']);
                            $thread_rowstart = "&rowstart=".$thread_rowstart;
                        }
                    }

                    $threads += [
                        "thread_link"         => [
                            "link"  => FORUM."viewthread.php?thread_id=".$threads['thread_id'].$thread_rowstart."&pid=".$threads['thread_lastpostid']."#post_".$threads['thread_lastpostid'],
                            "title" => $threads['thread_subject']
                        ],
                        "forum_type"          => $threads['forum_type'],
                        "thread_pages"        => makepagenav(0, $forum_settings['posts_per_page'], $threads['thread_postcount'], 3, FORUM."viewthread.php?thread_id=".$threads['thread_id']."&"),
                        "thread_icons"        => [
                            'lock'   => $threads['thread_locked'] ? "<i class='".self::getForumIcons('lock')."' title='".$locale['forum_0263']."'></i>" : '',
                            'sticky' => $threads['thread_sticky'] ? "<i class='".self::getForumIcons('sticky')."' title='".$locale['forum_0103']."'></i>" : '',
                            'poll'   => $threads['thread_poll'] ? "<i class='".self::getForumIcons('poll')."' title='".$locale['forum_0314']."'></i>" : '',
                            'hot'    => $threads['thread_postcount'] >= 20 ? "<i class='".self::getForumIcons('hot')."' title='".$locale['forum_0311']."'></i>" : '',
                            'reads'  => $threads['thread_views'] >= 100 ? "<i class='".self::getForumIcons('reads')."' title='".$locale['forum_0311']."'></i>" : '',
                            'attach' => $threads['attach_count'] > 0 ? "<i class='".self::getForumIcons('image')."' title='".$locale['forum_0312']."'></i>" : '',
                            'icon'   => $icon,
                        ],
                        "thread_starter_text" => $locale['forum_0006'].' '.$locale['by']." ".profile_link($author['user_id'], $author['user_name'], $author['user_status'])."</span>",
                        //"thread_starter"      => $locale['forum_0006'].' '.timer($threads['first_post_datestamp'])." ".$locale['by']." ".profile_link($author['user_id'], $author['user_name'], $author['user_status'])."</span>", // Very slow
                        "thread_starter"      => [
                            'author'       => $author,
                            'profile_link' => profile_link($author['user_id'], $author['user_name'], $author['user_status']),
                            'avatar'       => display_avatar($author, '20px', '', FALSE, 'img-rounded'),
                        ],
                        "thread_last"         => [
                            'user'         => $lastuser,
                            'avatar'       => display_avatar($lastuser, '35px', '', FALSE),
                            'profile_link' => profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status']),
                            'time'         => $threads['thread_lastpost'],
                            "formatted"    => "<div class='pull-left'>".display_avatar($lastuser, '30px', '', '')."</div>
                                                                                <div class='overflow-hide'>".$locale['forum_0373']." <span class='forum_profile_link'>".profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status'])."</span><br/>
                                                                                ".timer($threads['thread_lastpost'])."
                                                                                </div>"
                        ],
                    ];

                    if ($threads['thread_sticky']) {
                        $info['threads']['sticky'][$threads['thread_id']] = $threads;
                    } else {
                        $info['threads']['item'][$threads['thread_id']] = $threads;
                    }
                }
            }

            if ($info['thread_max_rows'] > $rows) {

                $info['threads']['pagenav'] = makepagenav($_GET['rowstart'],
                    $forum_settings['threads_per_page'],
                    $info['thread_max_rows'],
                    3,
                    clean_request("", ["rowstart"], FALSE)."&"
                );

                $info['threads']['pagenav2'] = makepagenav($_GET['rowstart'],
                    $forum_settings['threads_per_page'],
                    $info['thread_max_rows'],
                    3,
                    clean_request("", ["rowstart"], FALSE)."&",
                    'rowstart',
                    TRUE
                );

            }
        }

        return $info;
    }

    /**
     * Returns thread variables
     *
     * @return array
     */
    public function getThreadInfo() {
        return $this->thread_info;
    }

    /**
     * @param string $query
     */
    public static function setThreadQuery($query) {
        self::$custom_query = $query;
    }

    /**
     * Thread Class constructor - This builds all essential data on load.
     */
    public function setThreadInfo() {
        if (!isset($_GET['thread_id']) or !isnum($_GET['thread_id'])) {
            redirect(FORUM.'index.php');
        }

        if (isset($_GET['forum_id'])) {
            if (isnum($_GET['forum_id'])) {
                if (!dbcount('(forum_id)', DB_FORUM_THREADS, "forum_id=:forum_id AND thread_id=:thread_id",
                    [
                        ':forum_id'  => $_GET['forum_id'],
                        ':thread_id' => $_GET['thread_id']]
                )
                ) {
                    redirect(FORUM.'index.php');
                }
            } else {
                redirect(FORUM.'index.php');
            }
        }

        $forum_settings = self::getForumSettings();
        $locale = fusion_get_locale('', [FORUM_LOCALE, FORUM_TAGS_LOCALE]);
        $userdata = fusion_get_userdata();
        $forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');

        $this->thread_data = self::getThread($_GET['thread_id']); // fetch query and define iMOD

        if (!empty($this->thread_data) && !empty($_GET['thread_id']) && isnum($_GET['thread_id']) && $this->checkForumAccess($forum_index, 0, $_GET['thread_id'])) {

            // get post_count, lastpost_id, first_post_id.
            $thread_stat = self::getThreadStats($_GET['thread_id']);

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

            add_breadcrumb(['link' => FORUM.'index.php', 'title' => $locale['forum_0000']]);
            $this->forumBreadcrumbs($forum_index, $this->thread_data['forum_id']);
            add_breadcrumb(['link' => FORUM.'viewthread.php?forum_id='.$this->thread_data['forum_id'].'&thread_id='.$this->thread_data['thread_id'], 'title' => $this->thread_data['thread_subject']]);

            // Override $_GET['forum_id'] against tampering
            $_GET['forum_id'] = intval($this->thread_data['forum_id']);

            /**
             * Generate User Tracked Buttons
             */
            $this->thread_info['buttons']['notify'] = [];

            if ($this->getThreadPermission("can_access")) {
                // only member can track the thread
                if ($this->thread_data['user_tracked']) {
                    $this->thread_info['buttons']['notify'] = [
                        'link'  => INFUSIONS."forum/postify.php?post=off&forum_id=".$this->thread_data['forum_id']."&thread_id=".$this->thread_data['thread_id'],
                        'title' => $locale['forum_0174']
                    ];
                } else {
                    $this->thread_info['buttons']['notify'] = [
                        'link'  => INFUSIONS."forum/postify.php?post=on&forum_id=".$this->thread_data['forum_id']."&thread_id=".$this->thread_data['thread_id'],
                        'title' => $locale['forum_0175']
                    ];
                }
            }

            $this->thread_info['thread'] = $this->thread_data;

            /**
             * Generate Quick Reply Form
             */
            $qr_form = ($this->getThreadPermission("can_reply") == TRUE && $this->thread_data['forum_quick_edit'] == TRUE ? QuickReply::displayQuickReply($this->thread_data) : '');

            /**
             * Generate Poll Form
             */
            $poll = new Poll($this->thread_info);
            $poll_form = $poll->generatePoll($this->thread_data);
            $poll_info = $poll->getPollInfo();

            /**
             * Generate Attachment
             */
            $attach = new Attachment($this->thread_info);
            $attachments = $attach::getAttachments($this->thread_data);

            /**
             * Display thread bounty
             */
            $bounty = new Forum_Bounty($this->thread_info);
            $bounty_display = $bounty->displayBounty();

            /**
             * Generate Mod Form
             */
            if (iMOD) {

                $this->moderator()->setForumID($this->thread_data['forum_id']);
                $this->moderator()->setThreadId($this->thread_data['thread_id']);
                $this->moderator()->setModActions();

                /**
                 * Thread moderation form template
                 */
                $addition = isset($_GET['rowstart']) ? "&rowstart=".intval($_GET['rowstart']) : "";
                $this->thread_info['form_action'] = fusion_get_settings('siteurl')."infusions/forum/viewthread.php?thread_id=".intval($this->thread_data['thread_id']).$addition;

                $this->thread_info['mod_options'] = [
                    'renew'                                                      => $locale['forum_0207'],
                    'delete'                                                     => $locale['forum_0201'],
                    $this->thread_data['thread_locked'] ? "unlock" : "lock"      => $this->thread_data['thread_locked'] ? $locale['forum_0203'] : $locale['forum_0202'],
                    $this->thread_data['thread_sticky'] ? "nonsticky" : "sticky" => $this->thread_data['thread_sticky'] ? $locale['forum_0205'] : $locale['forum_0204'],
                    'move'                                                       => $locale['forum_0206']
                ];

                $this->thread_info['mod_form'] = openform('moderator_menu', 'post', $this->thread_info['form_action']);
                $this->thread_info['mod_form'] .= form_hidden('delete_item_post');
                $this->thread_info['mod_form'] .= "<div class='btn-group m-r-10'>\n
                        ".form_button("check_all", $locale['forum_0080'], $locale['forum_0080'], ['class' => 'btn-default btn-sm', "type" => "button"])."
                        ".form_button("check_none", $locale['forum_0081'], $locale['forum_0080'], ['class' => 'btn-default btn-sm', "type" => "button"])."
                    </div>\n
                    ".form_button('move_posts', $locale['forum_0176'], $locale['forum_0176'], ['class' => 'btn-default btn-sm m-r-10'])."
                    ".form_button('delete_posts', $locale['delete'], $locale['forum_0177'], ['class' => 'btn-default btn-sm'])."
                    <div class='pull-right'>
                        ".form_button('go', $locale['forum_0208'], $locale['forum_0208'], ['class' => 'btn-default btn-sm pull-right m-l-10'])
                    .form_select('step', '', '', [
                        'options'     => $this->thread_info['mod_options'],
                        'placeholder' => $locale['forum_0200'],
                        'width'       => '250px',
                        'allowclear'  => TRUE,
                        'class'       => 'pull-right m-b-0 m-t-5',
                        'inline'      => TRUE
                    ])."
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
            $this->thread_info += [
                'thread'               => $this->thread_data,
                'thread_id'            => $this->thread_data['thread_id'],
                'forum_id'             => $this->thread_data['forum_id'],
                'thread_tags'          => $this->thread_data['thread_tags'],
                'thread_tags_display'  => '',
                'buttons'              => [],
                'forum_cat'            => isset($_GET['forum_cat']) && self::verifyForum($_GET['forum_cat']) ? $_GET['forum_cat'] : 0,
                'forum_branch'         => isset($_GET['forum_branch']) && self::verifyForum($_GET['forum_branch']) ? $_GET['forum_branch'] : 0,
                'forum_link'           => [
                    'link'  => FORUM.'index.php?viewforum&forum_id='.$this->thread_data['forum_id'].'&forum_cat='.$this->thread_data['forum_cat'].'&forum_branch='.$this->thread_data['forum_branch'],
                    'title' => $this->thread_data['forum_name']
                ],
                'thread_attachments'   => $attachments,
                'post_id'              => isset($_GET['post_id']) && self::verifyPost($_GET['post_id']) ? $_GET['post_id'] : 0,
                'pid'                  => isset($_GET['pid']) && isnum($_GET['pid']) ? $_GET['pid'] : 0,
                'section'              => isset($_GET['section']) ? $_GET['section'] : '',
                'sort_post'            => isset($_GET['sort_post']) ? $_GET['sort_post'] : '',
                'forum_moderators'     => $this->moderator()->parseForumMods($this->thread_data['forum_mods']),
                'max_post_items'       => $thread_stat['post_count'],
                'post_firstpost'       => $thread_stat['first_post_id'],
                'post_lastpost'        => $thread_stat['last_post_id'],
                'posts_per_page'       => $forum_settings['posts_per_page'],
                'threads_per_page'     => $forum_settings['threads_per_page'],
                'lastvisited'          => (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time(),
                'allowed_post_filters' => ['oldest', 'latest', 'high'],
                'attachtypes'          => explode(',', $forum_settings['forum_attachtypes']),
                'quick_reply_form'     => $qr_form,
                'thread_bounty'        => $bounty_display,
                'poll_form'            => $poll_form,
                'poll_info'            => $poll_info,
                'post-filters'         => [],
                'mod_options'          => [],
                'form_action'          => '',
                'open_post_form'       => '',
                'close_post_form'      => '',
                'mod_form'             => '',
                'permissions'          => $this->getThreadPermission()
            ];

            if (!empty($this->thread_info['thread_tags'])) {
                $this->thread_info['thread_tags_display'] = $this->tag(FALSE)->displayThreadTags($this->thread_info['thread_tags']);
            }

            /**
             * Generate All Thread Buttons
             */
            $this->thread_info['buttons'] += [
                'print'     => [
                    'link'  => BASEDIR.'print.php?type=F&item_id='.$this->thread_data['thread_id'].'&rowstart='.$_GET['rowstart'],
                    'title' => $locale['forum_0178']
                ],
                'newthread' => $this->getThreadPermission('can_post') == TRUE ?
                    [
                        'link'  => FORUM.'newthread.php?forum_id='.$this->thread_data['forum_id'],
                        'title' => $this->thread_data['forum_type'] == 4 ? $locale['forum_0058'] : $locale['forum_0057']
                    ] : [],
                'reply'     => $this->getThreadPermission('can_reply') == TRUE ?
                    [
                        'link'  => FORUM.'viewthread.php?action=reply&forum_id='.$this->thread_data['forum_id'].'&thread_id='.$this->thread_data['thread_id'],
                        'title' => $locale['forum_0360']
                    ] : [],
                'poll'      => $this->getThreadPermission('can_create_poll') == TRUE ?
                    [
                        'link'  => FORUM.'viewthread.php?action=newpoll&forum_id='.$this->thread_data['forum_id'].'&thread_id='.$this->thread_data['thread_id'],
                        'title' => $locale['forum_0366']
                    ] : [],
                'bounty'    => $this->getThreadPermission('can_start_bounty') == TRUE ? [
                    'link'  => FORUM.'viewthread.php?action=newbounty&forum_id='.$this->thread_data['forum_id'].'&thread_id='.$this->thread_data['thread_id'],
                    'title' => $locale['forum_0399'],
                ] : [],
            ];

            /**
             * Generate Post Filters
             */
            $this->thread_info['post-filters'][0] = [
                'value'  => FORUM.'viewthread.php?thread_id='.$this->thread_data['thread_id'].'&sort_post=oldest',
                'locale' => $locale['forum_0180']
            ];
            $this->thread_info['post-filters'][1] = [
                'value'  => FORUM.'viewthread.php?thread_id='.$this->thread_data['thread_id'].'&sort_post=latest',
                'locale' => $locale['forum_0181']
            ];
            if ($this->getThreadPermission("can_rate") == TRUE) {
                $this->thread_info['allowed-post-filters'][2] = 'high';
                $this->thread_info['post-filters'][2] = [
                    'value'  => FORUM.'viewthread.php?thread_id='.$this->thread_info['thread_id'].'&sort_post=high',
                    'locale' => $locale['forum_0182']
                ];
            }
            $this->handleQuickReply();
            $this->getThreadPost();
            //showBenchmark(TRUE);
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
    public static function getThread($thread_id = 0) {
        $userdata = fusion_get_userdata();
        $userid = !empty($userdata['user_id']) ? (int)$userdata['user_id'] : 0;
        $query = !empty(self::$custom_query) ? self::$custom_query : "SELECT t.*, f.*,
                u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_joined,
                IF (n.thread_id > 0, 1 , 0) 'user_tracked',
                count(v.vote_user) 'thread_rated',
                count(p.forum_vote_user_id) 'poll_voted'
                FROM ".DB_FORUM_THREADS." t
                LEFT JOIN ".DB_USERS." u on t.thread_author = u.user_id
                INNER JOIN ".DB_FORUMS." f ON t.forum_id=f.forum_id
                LEFT JOIN ".DB_FORUM_VOTES." v on v.thread_id = t.thread_id AND v.vote_user='".$userid."' AND v.forum_id=f.forum_id AND f.forum_type='4'
                LEFT JOIN ".DB_FORUM_POLL_VOTERS." p on p.thread_id = t.thread_id AND p.forum_vote_user_id='".$userid."' AND t.thread_poll='1'
                LEFT JOIN ".DB_FORUM_THREAD_NOTIFY." n on n.thread_id = t.thread_id and n.notify_user = '".$userid."'
                ".(multilang_table('FO') ? " WHERE ".in_group('f.forum_language', LANGUAGE)." AND " : " WHERE ")."
                ".groupaccess('f.forum_access')." AND t.thread_id='".intval($thread_id)."' AND t.thread_hidden='0'";
        $result = dbquery($query);
        if (dbrows($result)) {
            $data = dbarray($result);
            if ($data['forum_id']) {
                Moderator::defineForumMods($data);

                return $data;
            } else {
                redirect(FORUM.'index.php');
            }
        } else {
            redirect(FORUM.'index.php');
        }

        return NULL;
    }

    /**
     * Get post count, lastpost_id and first_post_id
     *
     * @param int $thread_id
     *
     * @return array
     */
    private static function getThreadStats($thread_id) {
        list($array['post_count'], $array['last_post_id'], $array['first_post_id']) = dbarraynum(dbquery("SELECT COUNT(post_id), MAX(post_id), MIN(post_id) FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($thread_id)."' AND post_hidden='0' GROUP BY thread_id"));
        if (!$array['post_count']) {
            redirect(FORUM.'index.php');
        } // exit no.2
        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $array['last_post_id'] ? $_GET['rowstart'] : 0; // secure against XSS

        return $array;
    }

    /**
     * Set in full extent of forum permissions and current user thread permissions
     */
    private function setThreadPermission() {
        // Access the forum
        $this->thread_info['permissions']['can_access'] = (iMOD || checkgroup($this->thread_data['forum_access']));
        // Create another thread under the same forum
        $this->thread_info['permissions']['can_post'] = $this->thread_info['permissions']['can_access'] && (iMOD || (checkgroup($this->thread_data['forum_post']) && $this->thread_data['forum_lock'] == FALSE));
        // Upload an attachment in this thread
        $this->thread_info['permissions']['can_upload_attach'] = $this->thread_data['forum_allow_attach'] == TRUE && (iMOD || (checkgroup($this->thread_data['forum_attach']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE));
        // Download an attachment in this thread
        $this->thread_info['permissions']['can_download_attach'] = iMOD || ($this->thread_data['forum_allow_attach'] == TRUE && checkgroup($this->thread_data['forum_attach_download']));
        // Post a reply in this thread
        $this->thread_info['permissions']['can_reply'] = $this->thread_data['thread_postcount'] > 0 && (iMOD || (checkgroup($this->thread_data['forum_reply']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE));
        // Create a poll
        $this->thread_info['permissions']['can_create_poll'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['thread_poll'] == FALSE && $this->thread_data['forum_allow_poll'] == TRUE && (iMOD || (checkgroup($this->thread_data['forum_poll']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE));
        // Edit a poll (modify the poll)
        $this->thread_info['permissions']['can_edit_poll'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['thread_poll'] == TRUE && (iMOD || (checkgroup($this->thread_data['forum_poll']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE && $this->thread_data['thread_author'] == fusion_get_userdata('user_id')));
        // Can vote a poll
        $this->thread_info['permissions']['can_vote_poll'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['poll_voted'] == FALSE && (iMOD || (checkgroup($this->thread_data['forum_vote']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE));
        // Can vote in this thread
        $this->thread_info['permissions']['can_rate'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['forum_type'] == 4 && (iMOD || (checkgroup($this->thread_data['forum_post_ratings']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE));
        // Can accept an answer
        $this->thread_info['permissions']['can_answer'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['forum_type'] == 4 && $this->thread_data['thread_answered'] == FALSE && $this->thread_data['thread_locked'] == FALSE && ($this->thread_data['thread_author'] == fusion_get_userdata('user_id') || iMOD);
        // Can start a bounty
        $this->thread_info['permissions']['can_start_bounty'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['forum_type'] == 4 && iMEMBER && !$this->thread_data['thread_bounty'] && $this->thread_data['thread_locked'] == FALSE && fusion_get_userdata('user_reputation') >= self::getForumSettings('min_rep_points');
        // Can edit a bounty
        $this->thread_info['permissions']['can_edit_bounty'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['forum_type'] == 4 && iMEMBER && $this->thread_data['thread_bounty'] && $this->thread_data['thread_locked'] == FALSE && ($this->thread_data['thread_bounty_user'] == fusion_get_userdata('user_id') || iMOD);
        // Can award bounty
        $this->thread_info['permissions']['can_award_bounty'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['forum_type'] == 4 && iMEMBER && $this->thread_data['thread_bounty'] && ($this->thread_data['thread_bounty_user'] == fusion_get_userdata('user_id'));
    }

    /**
     * Get the relevant permissions of the current thread permission configuration
     *
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
    private function handleQuickReply() {
        $forum_settings = self::getForumSettings();
        $locale = fusion_get_locale();
        $thread = self::thread();
        $userdata = fusion_get_userdata();

        if (isset($_POST['post_quick_reply'])) {

            if ($this->getThreadPermission("can_reply") && fusion_safe()) {
                $this->thread_data = $this->thread_info['thread'];
                require_once INCLUDES."flood_include.php";
                if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice
                    $post_data = [
                        'post_id'         => 0,
                        'forum_id'        => $this->thread_data['forum_id'],
                        'thread_id'       => $this->thread_data['thread_id'],
                        'post_message'    => form_sanitizer($_POST['post_message'], '', 'post_message'),
                        'post_showsig'    => isset($_POST['post_showsig']) ? 1 : 0,
                        'post_smileys'    => isset($_POST['post_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? 1 : 0,
                        'post_author'     => $userdata['user_id'],
                        'post_datestamp'  => time(),
                        'post_ip'         => USER_IP,
                        'post_ip_type'    => USER_IP_TYPE,
                        'post_edituser'   => 0,
                        'post_edittime'   => 0,
                        'post_editreason' => '',
                        'post_hidden'     => 0,
                        'post_locked'     => $forum_settings['forum_edit_lock'] || isset($_POST['post_locked']) ? 1 : 0
                    ];

                    if (fusion_safe()) { // post message is invalid or whatever is invalid
                        $update_forum_lastpost = FALSE;

                        // Prepare forum merging action
                        $last_post_author = dbarray(dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_data['thread_id']."' ORDER BY post_id DESC LIMIT 1"));
                        if ($last_post_author['post_author'] == $post_data['post_author'] && $this->thread_data['forum_merge']) {
                            $last_message = dbarray(dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_data['thread_id']."' ORDER BY post_id DESC"));
                            $post_data['post_id'] = $last_message['post_id'];
                            $post_data['post_message'] = $last_message['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate", time()).":\n".$post_data['post_message'];
                            dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', ['primary_key' => 'post_id', 'keep_session' => TRUE]);

                            dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$this->thread_data['forum_id']."'");
                            // update current thread
                            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$post_data['post_id']."', thread_lastuser='".$post_data['post_author']."' WHERE thread_id='".$this->thread_data['thread_id']."'");
                        } else {
                            $update_forum_lastpost = TRUE;
                            dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', ['primary_key' => 'post_id', 'keep_session' => TRUE]);
                            $post_data['post_id'] = dblastid();
                            dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$post_data['post_author']."'");
                        }

                        if (!empty($_FILES)
                            && is_uploaded_file($_FILES['file_attachments']['tmp_name'][0])
                            && $thread->getThreadPermission("can_upload_attach")
                        ) {
                            $upload = form_sanitizer($_FILES['file_attachments'], '', 'file_attachments');
                            if ($upload['error'] == 0) {
                                foreach ($upload['target_file'] as $arr => $file_name) {
                                    $attach_data = [
                                        'thread_id'    => intval($this->thread_data['thread_id']),
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

                    if (fusion_safe()) {
                        redirect(INFUSIONS."forum/postify.php?post=reply&error=0&forum_id=".intval($post_data['forum_id'])."&thread_id=".intval($post_data['thread_id'])."&post_id=".intval($post_data['post_id']));
                    }
                }
            }
        }
    }

    /**
     * Get thread posts info
     *
     * @todo: optimize post reply with a subnested query to reduce post^n queries.
     */
    private function getThreadPost() {
        global $pid;
        $forum_settings = self::getForumSettings();
        $userdata = fusion_get_userdata();
        $locale = fusion_get_locale();

        switch ($this->thread_info['sort_post']) {
            case 'oldest':
                $sortCol = 'p.post_datestamp ASC';
                break;
            case 'latest':
                $sortCol = 'p.post_datestamp DESC';
                break;
            case 'high':
                $sortCol = 'v.vote_points DESC';
                break;
            default:
                $sortCol = 'p.post_datestamp ASC';
        }

        require_once INCLUDES."mimetypes_include.php";
        // post query
        $result = dbquery("
                    SELECT p.*,
                    SUM(v.vote_points) 'vote_points',
                    IF(v2.vote_id, 1, 0) 'has_voted',
                    v2.vote_points 'has_voted_points',
                    COUNT(a.attach_id) 'attach_count'
                    FROM ".DB_FORUM_POSTS." p
                    LEFT JOIN ".DB_FORUM_VOTES." v ON v.post_id=p.post_id
                    LEFT JOIN ".DB_FORUM_VOTES." v2 ON v2.post_id=p.post_id AND v2.vote_user='".fusion_get_userdata('user_id')."'
                    LEFT JOIN ".DB_FORUM_ATTACHMENTS." a ON p.thread_id=a.thread_id AND a.post_id=p.post_id
                    WHERE p.thread_id='".intval($_GET['thread_id'])."' AND p.post_hidden='0'
                    ".($this->thread_info['thread']['forum_type'] == '4' ? "OR p.post_id='".intval($this->thread_info['post_firstpost'])."'" : '')."
                    GROUP by p.post_id
                    ORDER BY $sortCol LIMIT ".intval($_GET['rowstart']).", ".intval($forum_settings['posts_per_page'])
        );

        $this->thread_info['post_rows'] = dbrows($result);

        if ($this->thread_info['post_rows'] > 0) {
            $mood = self::mood();
            $response = $mood->postMood();

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
                        FORUM."viewthread.php?thread_id=".$this->thread_info['thread']['thread_id'].(isset($_GET['highlight']) ? "&highlight=".urlencode($_GET['highlight']) : '')."&")."</div>";
            }

            add_to_jquery("
            $('.reason_button').on('click', function(e) {
                var reason_div = $(this).data('target');
                if ($('#'+reason_div).is(':visible')) {
                     $('#'+reason_div).show();
                } else {
                     $('#'+reason_div).hide();
                }
            });
            ");
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

            $i = 1;
            // Cache the user fields in the system
            $enabled_uf_fields = [];
            $module = [];
            $sql_condition = "";
            if (!empty($forum_settings['forum_enabled_userfields'])) {
                $enabled_uf_fields = explode(',', $forum_settings['forum_enabled_userfields']);
                foreach ($enabled_uf_fields as $key => $values) {
                    if ($sql_condition)
                        $sql_condition .= " OR ";
                    $sql_condition .= "fd.field_name='".$values."'";
                }
                $uf_result = dbquery("
                  SELECT fd.*, ufc.*
                  FROM ".DB_USER_FIELDS." fd
                  INNER JOIN ".DB_USER_FIELD_CATS." ufc ON fd.field_cat=ufc.field_cat_id
                  WHERE $sql_condition
                  ORDER BY field_name ASC
                ");
                if (dbrows($uf_result)) {
                    while ($ufData = dbarray($uf_result)) {
                        $module[$ufData['field_name']] = $ufData;
                    }
                }
            }

            while ($pdata = dbarray($result)) {

                $user = fusion_get_user($pdata['post_author']);

                $default_author = [
                    'user_id'        => '',
                    'user_name'      => '',
                    'user_status'    => '',
                    'user_avatar'    => '',
                    'user_level'     => '',
                    'user_posts'     => '',
                    'user_groups'    => '',
                    'user_joined'    => '',
                    'user_lastvisit' => 0,
                    'user_ip'        => '0.0.0.0',
                    'user_sig'       => ''
                ];

                if (!empty($user)) {
                    $author = [
                        'user_id'        => $user['user_id'],
                        'user_name'      => $user['user_name'],
                        'user_status'    => $user['user_status'],
                        'user_avatar'    => $user['user_avatar'],
                        'user_level'     => $user['user_level'],
                        'user_posts'     => $user['user_posts'],
                        'user_groups'    => $user['user_groups'],
                        'user_joined'    => $user['user_joined'],
                        'user_lastvisit' => $user['user_lastvisit'],
                        'user_ip'        => $user['user_ip'],
                        'user_sig'       => !empty($user['user_sig']) ? $user['user_sig'] : ''
                    ];

                    if (!$pdata['post_showsig']) {
                        unset($module['user_sig']);
                    }
                    /*
                     * Build ['user_profiles'] info
                     */
                    if (!empty($enabled_uf_fields)) {
                        foreach ($module as $field_name => $fieldAttr) {
                            if (!empty($user[$field_name])) {
                                $field_value = $user[$field_name];
                                if ($fieldAttr['field_type'] == 'file') {
                                    $module_file_path = INCLUDES.'user_fields/'.$fieldAttr['field_name'].'_include.php';
                                    $module_locale_file_path = LOCALE.LOCALESET.'user_fields/'.$fieldAttr['field_name'].'.php';
                                    if (file_exists($module_file_path) && file_exists($module_locale_file_path)) {
                                        $profile_method = 'display';
                                        $user_fields = [];
                                        include($module_locale_file_path);
                                        include($module_file_path);
                                        if (!empty($user_fields) && is_array($user_fields)) {
                                            $user_fields['field_cat_name'] = $fieldAttr['field_cat_name'];
                                            $author['user_profiles'][$field_name] = $user_fields;
                                        }
                                    }
                                } else {
                                    // this is just normal type
                                    $author['user_profiles'][$field_name] = [
                                        'field_cat_name' => $fieldAttr['field_cat_name'],
                                        'title'          => $field_name['field_name'],
                                        'value'          => $field_value
                                    ];
                                }
                            }
                        }
                    }
                    // add author pdata in.
                    $pdata += $author;
                }

                $pdata += $default_author;

                $pid = $pdata['post_id'];
                // Format Post Message
                $post_message = empty($pdata['post_smileys']) ? parsesmileys($pdata['post_message']) : $pdata['post_message'];
                $post_message = nl2br(parseubb($post_message, '', FALSE));
                $post_message = fusion_parse_user($post_message);
                if (isset($_GET['highlight'])) {
                    $post_message = "<div class='search_result'>".$post_message."</div>\n";
                }

                // Marker
                $marker = [
                    'link'  => "#post_".$pdata['post_id'],
                    "title" => "#".($i + $_GET['rowstart']),
                    'id'    => "post_".$pdata['post_id']
                ];

                $post_marker = "<a class='marker' href='".$marker['link']."' id='".$marker['id']."'>".$marker['title']."</a>";
                $post_marker .= "<a title='".$locale['forum_0241']."' href='#top'><i class='fa fa-angle-up'></i></a>\n";
                // Post Attachments
                $post_attachments = '';
                if ($pdata['attach_count']) {
                    if ($this->getThreadPermission("can_download_attach")) {
                        $attachResult = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".intval($pdata['post_id'])."'");
                        if (dbrows($attachResult) > 0) {
                            $aImage = "";
                            $aFiles = "";
                            $aFiles_Count = 0;
                            $aImage_Count = 0;
                            while ($attachData = dbarray($attachResult)) {
                                if (in_array($attachData['attach_mime'], img_mimetypes())) {
                                    $aImage .= display_image_attach($attachData['attach_name'], "70", "70", $pdata['post_id'])."\n";
                                    $aFiles_Count++;
                                } else {
                                    $current_file = FORUM.'attachments/'.$attachData['attach_name'];
                                    $aFiles .= "<div class='display-inline-block'><i class='fa fa-paperclip'></i><a href='".INFUSIONS."forum/viewthread.php?thread_id=".$pdata['thread_id']."&getfiles=".$attachData['attach_id']."'>".$attachData['attach_name']."</a>&nbsp;";
                                    $aFiles .= "[<span class='small'>".(file_exists($current_file) ? parsebytesize(filesize($current_file)) : $locale['na'])." / ".$attachData['attach_count'].$locale['forum_0162']."</span>]</div>\n";
                                    $aImage_Count++;
                                }
                            }
                            if (!empty($aFiles)) {
                                $post_attachments .= "<div class='emulated-fieldset'>\n";
                                $post_attachments .= "<span class='emulated-legend'>".profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']).' '.$locale['forum_0154'].($aFiles_Count > 1 ? $locale['forum_0158'] : $locale['forum_0157'])."</span>\n";
                                $post_attachments .= "<div class='attachments-list m-t-10'>".$aFiles."</div>\n";
                                $post_attachments .= "</div>\n";
                            }
                            if (!empty($aImage)) {
                                $post_attachments .= "<div class='emulated-fieldset'>\n";
                                $post_attachments .= "<span class='emulated-legend'>".profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']).' '.$locale['forum_0154'].($aImage_Count > 1 ? $locale['forum_0156'] : $locale['forum_0155'])."</span>\n";
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
                            $post_attachments = $locale['forum_0163a'];
                        }
                    } else {
                        $post_attachments = "<small><i class='fa fa-clipboard'></i> ".$locale['forum_0184']."</small>\n";
                    }
                }
                $pdata['user_ip'] = ($forum_settings['forum_ips'] && iMOD) ? $locale['forum_0268'].' '.$pdata['post_ip'] : '';

                $pdata += [
                    'user_online'        => $pdata['user_lastvisit'] >= time() - 300,
                    'is_first_post'      => $pdata['post_id'] == $this->thread_info['post_firstpost'],
                    'is_last_post'       => $pdata['post_id'] == $this->thread_info['post_lastpost'],
                    'user_profile_link'  => profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']),
                    'user_avatar_image'  => display_avatar($pdata, '50px', FALSE, FALSE, 'img-rounded'),
                    'user_post_count'    => format_word($pdata['user_posts'], $locale['fmt_post']),
                    'print'              => [
                        'link'  => BASEDIR.'print.php?type=F&item_id='.$_GET['thread_id'].'&post='.$pdata['post_id'].'&nr='.($i + $_GET['rowstart']),
                        'title' => $locale['forum_0179']
                    ],
                    'post_marker'        => $post_marker,
                    'marker'             => $marker,
                    'post_attachments'   => $post_attachments,
                    'post_reply_message' => '',
                    'post_bounty'        => [],
                ];
                $pdata['post_message'] = $post_message;

                /**
                 * Who has replied to this post?
                 * This will drag the entire forum down with +1 query per forum post. Each is 0.04s
                 *
                 * @todo:
                 * Many to many search very slow (TURN OFF to implement it in next release)
                 * Increment DB_FORUM_POSTS with 'post_replied' column and have postify set it.
                 */

                $replies_sql = "SELECT post_id FROM ".DB_FORUM_POSTS." WHERE post_cat=:post_id AND thread_id=:thread_id AND forum_id=:forum_id LIMIT 1";
                $replies_param = [
                    ':post_id'   => $pdata['post_id'],
                    ':thread_id' => $pdata['thread_id'],
                    ':forum_id'  => $pdata['forum_id']
                ];
                if (dbrows(dbquery($replies_sql, $replies_param))) {
                    $replies_sql = "SELECT post_id, post_datestamp, post_author FROM ".DB_FORUM_POSTS." WHERE post_cat=:post_id AND thread_id=:thread_id AND forum_id=:forum_id GROUP BY post_author ORDER BY post_datestamp DESC";
                    $reply_result = dbquery($replies_sql, $replies_param);
                    if (dbrows($reply_result)) {
                        // who has replied
                        $reply_sender = [];
                        $last_datestamp = 0;
                        while ($r_data = dbarray($reply_result)) {
                            $user_replied = fusion_get_user($r_data['post_author']);
                            $r_data += [
                                'user_id'     => $user_replied['user_id'],
                                'user_name'   => $user_replied['user_name'],
                                'user_status' => $user_replied['user_status']
                            ];
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
                }

                /**
                 * Displays mood buttons
                 * This will drag the forum down with +1 query per post.
                 */
                $pdata['post_mood_buttons'] = $this->mood()->setPostData($pdata)->displayMoodButtons();
                $pdata['post_moods'] = $this->mood()->getMoodMessage();
                /*
                 * Bounty payment
                 */
                if ($this->getThreadPermission('can_award_bounty') && $pdata['post_author'] !== fusion_get_userdata('user_id')) {
                    $pdata['post_bounty'] = [
                        'link'  => FORUM.'viewthread.php?action=award&forum_id='.$pdata['forum_id'].'&thread_id='.$pdata['thread_id'].'&post_id='.$pdata['post_id'],
                        'title' => $locale['forum_4107']
                    ];
                }
                /**
                 * User Stuffs, Sig, User Message, Web
                 */
                // Quote & Edit Link
                if ($this->getThreadPermission('can_reply')) {
                    if (!$this->thread_info['thread']['thread_locked']) {

                        $pdata['post_quote'] = [
                            'link'  => INFUSIONS."forum/viewthread.php?action=reply&forum_id=".$pdata['forum_id']."&thread_id=".$pdata['thread_id']."&post_id=".$pdata['post_id']."&quote=".$pdata['post_id'],
                            'title' => $locale['forum_0266']
                        ];
                        if (iMOD || (
                                (($forum_settings['forum_edit_lock'] == TRUE && $pdata['is_last_post'] || $forum_settings['forum_edit_lock'] == FALSE))
                                && ($userdata['user_id'] == $pdata['post_author'])
                                && (self::getEditTimelimit() <= 0 || (time() - self::getEditTimelimit()) < $pdata['post_datestamp'])
                            )
                        ) {
                            $pdata['post_edit'] = [
                                'link'  => INFUSIONS."forum/viewthread.php?action=edit&forum_id=".$pdata['forum_id']."&thread_id=".$pdata['thread_id']."&post_id=".$pdata['post_id'],
                                'title' => $locale['edit']
                            ];
                        }
                        $pdata['post_reply'] = [
                            'link'  => INFUSIONS."forum/viewthread.php?action=reply&forum_id=".$pdata['forum_id']."&thread_id=".$pdata['thread_id']."&post_id=".$pdata['post_id'],
                            'title' => $locale['forum_0509']
                        ];
                    } else if (iMOD) {
                        $pdata['post_edit'] = [
                            'link'  => INFUSIONS."forum/viewthread.php?action=edit&forum_id=".$pdata['forum_id']."&thread_id=".$pdata['thread_id']."&post_id=".$pdata['post_id'],
                            'title' => $locale['edit']
                        ];
                    }
                }

                // rank img
                if ($pdata['user_level'] <= USER_LEVEL_ADMIN) {
                    if ($forum_settings['forum_ranks']) {
                        $pdata['user_rank'] = self::showForumRank($pdata['user_posts'], $pdata['user_level'], $pdata['user_groups']);
                    } else {
                        $pdata['user_rank'] = getuserlevel($pdata['user_level']);
                    }
                } else {
                    if ($forum_settings['forum_ranks']) {
                        $pdata['user_rank'] = self::showForumRank($pdata['user_posts'], iMOD ? 104 : $pdata['user_level'], $pdata['user_groups']);
                    } else {
                        $pdata['user_rank'] = getuserlevel($pdata['user_level']);
                    }
                }

                // Website
                if (!empty($pdata['user_web']) && (iADMIN || $pdata['user_status'] != 6 && $pdata['user_status'] != 5)) {
                    $user_web_url = !preg_match("@^http(s)?\:\/\/@i", $pdata['user_web']) ? "http://".$pdata['user_web'] : $pdata['user_web'];
                    $pdata['user_web'] = [
                        'link'  => $user_web_url,
                        'title' => $locale['forum_0364']
                    ];
                } else {
                    $pdata['user_web'] = ['link' => '', 'title' => ''];
                }

                // PM link
                $pdata['user_message'] = ['link' => '', 'title' => ''];
                if (iMEMBER && $pdata['user_id'] != $userdata['user_id'] && (iADMIN || $pdata['user_status'] != 6 && $pdata['user_status'] != 5)) {
                    $pdata['user_message'] = [
                        'link'  => BASEDIR.'messages.php?msg_send='.$pdata['user_id'],
                        "title" => $locale['send_message']
                    ];
                }

                // User Sig
                if (!empty($pdata['user_sig']) && $pdata['user_sig'] && isset($pdata['post_showsig']) && $pdata['post_showsig'] == 1 && $pdata['user_status'] != 6 && $pdata['user_status'] != 5) {
                    $pdata['user_sig'] = nl2br(parsesmileys(parseubb(stripslashes($pdata['user_sig']))));
                } else {
                    $pdata['user_sig'] = "";
                }

                // Voting - need up or down link - accessible to author also the vote
                // answered and ongoing questions.
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
                                'link'  => FORUM."postify.php?post=answer&forum_id=".$pdata['forum_id']."&thread_id=".$pdata['thread_id']."&post_id=".$pdata['post_id'],
                                'title' => $locale['forum_0513'] //0513
                            ];
                            $pdata['post_answer_check'] = "<a href='".$pdata['vote_answered']['link']."' class='answer_button answer_checked' title='".$pdata['vote_answered']['title']."'><i class='fa fa-check fa-2x'></i></a>";
                        } else {
                            // Is not an answer
                            $pdata['vote_answered'] = [
                                'link'  => FORUM."postify.php?post=answer&forum_id=".$pdata['forum_id']."&thread_id=".$pdata['thread_id']."&post_id=".$pdata['post_id'],
                                'title' => $locale['forum_0512'] //0512
                            ];
                            $pdata['post_answer_check'] = "<a href='".$pdata['vote_answered']['link']."' class='answer_button answer_unchecked' title='".$pdata['vote_answered']['title']."'><i class='fa fa-check fa-2x'></i></a>";
                        }
                    }

                    if ($this->getThreadPermission('can_rate')) { // can vote.
                        $pdata['vote_up'] = [
                            'link'   => INFUSIONS."forum/postify.php?post=voteup&forum_id=".$pdata['forum_id']."&thread_id=".$pdata['thread_id']."&post_id=".$pdata['post_id'],
                            "title"  => $locale['forum_0510'],
                            'active' => $pdata['has_voted'] && $pdata['has_voted_points'] > 0,
                        ];
                        $pdata['vote_down'] = [
                            'link'   => INFUSIONS."forum/postify.php?post=votedown&forum_id=".$pdata['forum_id']."&thread_id=".$pdata['thread_id']."&post_id=".$pdata['post_id'],
                            "title"  => $locale['forum_0511'],
                            'active' => $pdata['has_voted'] && $pdata['has_voted_points'] < 0,
                        ];
                    }
                    $pdata['post_votebox'] = "<div class='text-center post_vote_box'>\n";
                    $pdata['post_votebox'] .= "<a ".(!empty($pdata['vote_up']['link']) ? "href='".$pdata['vote_up']['link']."'" : 'disabled')." class='text-center vote_up".(!empty($pdata['vote_up']['active']) && $pdata['vote_up']['active'] ? " text-warning" : '').(!empty($pdata['vote_up']['link']) ? '' : ' disabled')."' title='".$locale['forum_0510']."'>\n<i class='fa fa-caret-up fa-2x'></i></a>";
                    $pdata['post_votebox'] .= "<h3 class='m-0'>".(!empty($pdata['vote_points']) ? $pdata['vote_points'] : 0)."</h3>\n";
                    $pdata['post_votebox'] .= "<a ".(!empty($pdata['vote_down']['link']) ? "href='".$pdata['vote_down']['link']."'" : 'disabled')." class='text-center vote_down".(!empty($pdata['vote_down']['active']) && $pdata['vote_down']['active'] ? " text-warning" : '').(!empty($pdata['vote_down']['link']) ? '' : ' disabled')."' title='".$locale['forum_0511']."'>\n<i class='fa fa-caret-down fa-2x'></i></a>";
                    $pdata['post_votebox'] .= "</div>\n";
                }

                $pdata['post_edit_reason'] = '';
                if ($pdata['post_edittime']) {
                    $e_user = fusion_get_user($pdata['post_edituser']);
                    $edit_reason = "<div class='edit_reason small'>".$locale['forum_0164']." ";
                    if (!empty($e_user)) {
                        $edit_user = [
                            'edit_userid'     => $e_user['user_id'],
                            'edit_username'   => $e_user['user_name'],
                            'edit_userstatus' => $e_user['user_status'],
                        ];
                        $pdata += $edit_user;
                        $edit_reason .= profile_link($edit_user['edit_userid'], $edit_user['edit_username'], $edit_user['edit_userstatus']);
                    } else {
                        $edit_reason .= $locale['user_na'];
                    }
                    $edit_reason .= " ".$locale['forum_0167']." ".showdate("forumdate", $pdata['post_edittime']).", ".timer($pdata['post_edittime']);
                    if ($pdata['post_editreason'] && iMEMBER) {
                        $edit_reason .= " - <a id='reason_pid_".$pdata['post_id']."' rel='".$pdata['post_id']."' class='reason_button pointer' data-target='reason_div_pid_".$pdata['post_id']."'>";
                        $edit_reason .= "<strong>".$locale['forum_0165']."</strong>";
                        $edit_reason .= "</a></div>";
                        $edit_reason .= "<div id='reason_div_pid_".$pdata['post_id']."' class='post_reason' style='display:none;'><span class='text-lighter'>- ".$pdata['post_editreason']."</span></div>\n";
                    } else {
                        $edit_reason .= "</div>";
                    }
                    $pdata['post_edit_reason'] = $edit_reason;
                }

                // Custom Post Message Link/Buttons
                $pdata['post_links'] = !empty($pdata['post_quote']) ? "<a class='btn btn-xs btn-default' title='".$pdata['post_quote']["title"]."' href='".$pdata['post_quote']['link']."'>".$pdata['post_quote']['title']."</a>\n" : '';
                $pdata['post_links'] .= !empty($pdata['post_edit']) ? "<a class='btn btn-xs btn-default' title='".$pdata['post_edit']["title"]."' href='".$pdata['post_edit']['link']."'>".$pdata['post_edit']['title']."</a>\n" : '';
                $pdata['post_links'] .= !empty($pdata['print']) ? "<a class='btn btn-xs btn-default' title='".$pdata['print']["title"]."' href='".$pdata['print']['link']."'>".$pdata['print']['title']."</a>\n" : '';
                $pdata['post_links'] .= !empty($pdata['user_web']) ? "<a class='btn btn-xs btn-default forum_user_actions' href='".$pdata['user_web']['link']."' target='_blank'>".$pdata['user_web']['title']."</a>\n" : '';
                $pdata['post_links'] .= !empty($pdata['user_message']) ? "<a class='btn btn-xs btn-default' href='".$pdata['user_message']['link']."' target='_blank'>".$pdata['user_message']['title']."</a>\n" : '';
                // Post Date
                $pdata['post_date'] = $locale['forum_0524']." ".timer($pdata['post_datestamp'])." - ".showdate('forumdate', $pdata['post_datestamp']);
                $pdata['post_shortdate'] = $locale['forum_0524']." ".timer($pdata['post_datestamp']);
                $pdata['post_longdate'] = $locale['forum_0524']." ".showdate('forumdate', $pdata['post_datestamp']);

                $this->thread_info['post_items'][$pdata['post_id']] = $pdata;
                $i++;
            }
        }
    }

    /**
     * New Status
     */
    public function setThreadVisitor() {
        if (iMEMBER) {
            $userdata = fusion_get_userdata();
            $thread_match = $this->thread_info['thread_id']."\|".$this->thread_info['thread']['thread_lastpost']."\|".$this->thread_info['thread']['forum_id'];
            if (($this->thread_info['thread']['thread_lastpost'] < $this->thread_info['lastvisited']) && !preg_match("(^\.$thread_match$|\.$thread_match\.|\.$thread_match$)", $userdata['user_threads'])) {
                dbquery("UPDATE ".DB_USERS." SET user_threads='".$userdata['user_threads'].".".stripslashes($thread_match)."' WHERE user_id='".$userdata['user_id']."'");
            }
        }
    }

}
