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

namespace PHPFusion\Infusions\Forum\Classes\Threads;

use PHPFusion\BreadCrumbs;
use PHPFusion\Infusions\Forum\Classes\Forum_Moderator;
use PHPFusion\Infusions\Forum\Classes\Forum_Server;
use PHPFusion\Infusions\Forum\Classes\Post\Quick_Reply;
use PHPFusion\Infusions\Forums\Classes\Moderator;
use PHPFusion\UserFieldsQuantum;

/**
 * Class ForumThreads
 * Forum threads functions
 *
 * @package PHPFusion\Forums\Threads
 */
class Forum_Threads extends Forum_Server {

    protected $thread_info = []; // make a default

    protected $thread_data = [];

    /**
     * Get thread structure on specific forum id.
     *
     * @param           $forum_id
     * @param array     $filter
     *
     * @return array
     */
    public function get_forum_thread($forum_id = 0, array $filter = array()) {

        $info = [];
        $locale = fusion_get_locale();
        $forum_settings = parent::get_forum_settings();
        $userdata = fusion_get_userdata();
        $userdata['user_id'] = !empty($userdata['user_id']) ? (int)intval($userdata['user_id']) : 0;
        $lastVisited = defined('LASTVISITED') ? LASTVISITED : TIME;

        $default_filter = [
            'join'             => "",
            'custom_join'      => "",
            'select'           => "",
            'order'            => "",
            'condition'        => "",
            'custom_condition' => '',
            'time_condition'   => '',
            'type_condition'   => '',
            'count_query'      => "", // using normal dbquery
            'query'            => "", // actual query for more intensive output.
            "item_id"          => "thread_id" // sorting output key

        ];
        $filter += $default_filter;

        // Get threads with filter conditions (XSS prevention)
        $info['count_query'] = $filter['count_query'] ?: "        
        SELECT t.thread_id ".$filter['select']."
        FROM ".DB_FORUMS." tf
        INNER JOIN ".DB_FORUM_THREADS." t ON tf.forum_id=t.forum_id
        LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id        
        ##LEFT JOIN ".DB_FORUM_ATTACHMENTS." a ON a.thread_id = t.thread_id
        ##LEFT JOIN ".DB_FORUM_POSTS." p1 ON p1.thread_id=t.thread_id AND tf.forum_id=p1.forum_id
        ##LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = t.thread_id AND p1.post_id = v.post_id        
        ".$filter['join']."
        WHERE ".($forum_id ? " tf.forum_id='".intval($forum_id)."' AND " : "")."t.thread_hidden='0' AND ".groupaccess('tf.forum_access').$filter['condition'].(multilang_table("FO") ? " AND tf.forum_language='".LANGUAGE."'" : '')." GROUP BY t.thread_id";

        $info['thread_query'] = $filter['query'] ?: "
            SELECT t.*, tf.*, 
            IF (n.thread_id > 0, 1 , 0) 'user_tracked',
            COUNT(pv.forum_vote_user_id) 'poll_voted'            
            ".$filter['select']."
            FROM ".DB_FORUM_THREADS." t            
            INNER JOIN ".DB_FORUMS." tf ON tf.forum_id=t.forum_id
            LEFT JOIN ".DB_FORUM_POLL_VOTERS." pv ON pv.thread_id = t.thread_id AND pv.forum_vote_user_id='".$userdata['user_id']."' AND t.thread_poll=1            
            LEFT JOIN ".DB_FORUM_THREAD_NOTIFY." n ON n.thread_id = t.thread_id AND n.notify_user = '".$userdata['user_id']."'
            ".$filter['join']."
            WHERE ".($forum_id ? "t.forum_id='".intval($forum_id)."' AND " : "")." t.thread_hidden='0' AND ".groupaccess('tf.forum_access').$filter['condition'].(multilang_table("FO") ? " AND tf.forum_language='".LANGUAGE."'" : '')."
            GROUP BY t.thread_id ".$filter['order'];

        $count_result = dbquery($info['count_query']);

        $info['thread_max_rows'] = dbrows($count_result);
        $info['item'][$forum_id]['forum_threadcount'] = $info['thread_max_rows'];
        $info['item'][$forum_id]['forum_threadcount_word'] = format_word($info['thread_max_rows'], $locale['fmt_thread']);
        if ($info['thread_max_rows']) {

            // anti-XSS filtered rowstart
            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['thread_max_rows'] ? $_GET['rowstart'] : 0;
            $info['thread_query'] .= " LIMIT :rowstart, :tpp";

            $cthread_result = dbquery($info['thread_query'], [":rowstart" => intval($_GET['rowstart']), ":tpp" => intval($forum_settings['threads_per_page'])]);
            $info['thread_rows'] = dbrows($cthread_result);

            $info['threads']['pagenav'] = ($info['thread_max_rows'] > $info['thread_rows'] ? makepagenav($_GET['rowstart'], $forum_settings['threads_per_page'], $info['thread_max_rows'], 3, clean_request('', ['rowstart']).'&rowstart=') : '');
            $info['threads']['pagenav2'] = $info['threads']['pagenav'];

            if (!empty($filter['debug'])) {
                print_p($info);
            }

            if ($info['thread_rows']) {

                /*****
                 * NEW SETTINGS
                 */
                $forum_settings['show_thread_attach'] = FALSE;
                $forum_settings['show_last_message'] = FALSE;

                while ($threads = dbarray($cthread_result)) {

                    $threads = $threads + [
                            "post_id"             => 0,
                            "post_message"        => "",
                            "post_time"           => "",
                            "post_date"           => "",
                            "thread_answered"     => ($threads['forum_type'] === 4 ? FALSE : TRUE),
                            "thread_bounty"       => ($threads['forum_type'] === 4 ? FALSE : TRUE),
                            "post_smileys"        => "",
                            "post_attachments"    => "",
                            "author_name"         => "",
                            "author_status"       => "",
                            "author_avatar"       => "",
                            "last_user_name"      => "",
                            "last_user_status"    => "",
                            "last_user_avatar"    => "",
                            'last_post_id'        => 0,
                            'last_post_message'   => "",
                            "thread_user_avatars" => [],
                        ];
                    $this->thread_data = $threads;
                    $this->setThreadPermission(Forum_Moderator::check_forum_mods($threads['forum_mods']));

                    // Find First Post Message
                    $first_post_query = "SELECT post_id, post_message, post_smileys, post_author, post_datestamp 
                    FROM ".DB_FORUM_POSTS." WHERE thread_id=:tid AND forum_id=:fid ORDER BY post_id ASC LIMIT 1";
                    $first_post_param = [':tid' => $threads['thread_id'], ':fid' => $threads['forum_id']];
                    $first_post_result = dbquery($first_post_query, $first_post_param);
                    $first_post_count = FALSE;
                    if (dbrows($first_post_result)) {
                        while ($post_data = dbarray($first_post_result)) {
                            if (empty($first_post_count)) {
                                $threads['post_id'] = $post_data['post_id'];
                                $threads['post_message'] = $post_data['post_message'] ? strip_bbcodes(parsesmileys($post_data['post_message'])) : $locale['forum_0666'];
                                $threads['post_time'] = timer($post_data['post_datestamp']);
                                $threads['post_date'] = showdate("forumdate", $post_data['post_datestamp']);
                                if (!empty($threads['attach_count']) && $forum_settings['show_thread_attach'] === TRUE) {
                                    if ($this->getThreadPermission("can_download_attach")) {
                                        $attachResult = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".intval($post_data['post_id'])."'");
                                        if (dbrows($attachResult) > 0) {
                                            $aImage = "";
                                            $aFiles = "";
                                            $aFiles_Count = 0;
                                            $aImage_Count = 0;
                                            while ($attachData = dbarray($attachResult)) {
                                                if (in_array($attachData['attach_mime'], img_mimeTypes())) {
                                                    // make settings for attachment images
                                                    $aImage .= display_image_attach($attachData['attach_name'], "200", "200", $post_data['post_id'])."\n";
                                                    $aFiles_Count++;
                                                } else {
                                                    $current_file = FORUM.'attachments/'.$attachData['attach_name'];
                                                    $aFiles .= "<div class='display-block text-sm'><i class='fa fa-paperclip m-r-5'></i><a class='strong' href='".INFUSIONS."forum/viewthread.php?thread_id=".$threads['thread_id']."&amp;getfiles=".$attachData['attach_id']."'>".$attachData['attach_name']."</a>&nbsp;";
                                                    $aFiles .= "<div class='pull-right text-lighter'>[".(file_exists($current_file) ? parsebytesize(filesize($current_file)) : $locale['na'])." / ".$attachData['attach_count'].$locale['forum_0162']."]</div></div>\n";
                                                    $aImage_Count++;
                                                }
                                            }
                                            if (!empty($aFiles)) {
                                                $threads['post_attachments'] .= "<div class='emulated-fieldset'>\n";
                                                $threads['post_attachments'] .= "<div class='attachments-list m-t-10'>".$aFiles."</div>\n";
                                                $threads['post_attachments'] .= "</div>\n";
                                            }
                                            if (!empty($aImage)) {
                                                $threads['post_attachments'] .= "<div class='emulated-fieldset'>\n";
                                                $threads['post_attachments'] .= "<div class='attachments-list'>".$aImage."</div>\n";
                                                $threads['post_attachments'] .= "</div>\n";
                                                if (!defined('COLORBOX')) {
                                                    define('COLORBOX', TRUE);
                                                    add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
                                                    add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
                                                    add_to_jquery("$('a[rel^=\"attach\"]').colorbox({ current: '".$locale['forum_0159']." {current} ".$locale['forum_0160']." {total}',width:'80%',height:'80%'});");
                                                }
                                            }
                                        } else {
                                            $threads['post_attachments'] = $locale['forum_0163a'];
                                        }
                                    } else {
                                        $threads['post_attachments'] = "<small><i class='fa fa-clipboard'></i> ".$locale['forum_0184']."</small>\n";
                                    }
                                }
                                $first_post_count = TRUE;
                            }
                            $thread_users = fusion_get_user($post_data['post_author']);
                            if ($thread_users['user_name']) {
                                $threads['thread_user_avatars'][$thread_users['user_id']] = display_avatar($thread_users, '25px');
                            }
                        }
                    }

                    // Find last post
                    $last_post_query = "SELECT post_id, post_message, post_smileys FROM ".DB_FORUM_POSTS." WHERE thread_id=:tid AND forum_id=:fid ORDER BY post_id DESC LIMIT 1";
                    $last_post_param = [':tid' => $threads['thread_id'], ':fid' => $threads['forum_id']];
                    $last_post_result = dbquery($last_post_query, $last_post_param);
                    if (dbrows($last_post_result)) {
                        $post_data = dbarray($last_post_result);
                        $threads['last_post_id'] = $post_data['post_id'];
                        if ($forum_settings['show_last_message']) {
                            $threads['last_post_message'] = $post_data['post_message'] ? parsesmileys(strip_tags(strip_bbcodes($post_data['post_message']))) : $locale['forum_0666'];
                        }
                    }

                    // Thread Item Track Button
                    $threads['track_button'] = [
                        "link"    => FORUM."postify.php?post=on&forum_id=".$threads['forum_id']."&amp;thread_id=".$threads['thread_id'],
                        "title"   => $locale['forum_0175'],
                        "onclick" => "",
                    ];
                    if (isset($threads['user_tracked']) && $threads['user_tracked']) {
                        $threads['track_button'] = [
                            "link"    => FORUM."postify.php?post=off&forum_id=".$threads['forum_id']."&amp;thread_id=".$threads['thread_id'],
                            "title"   => $locale['forum_0174'],
                            "onclick" => "onclick=\"return confirm('".$locale['global_060']."');\"",
                        ];
                    }

                    if (!isset($threads['attach_count'])) {
                        $threads['attach_count'] = dbcount("(attach_id)", DB_FORUM_ATTACHMENTS, "thread_id=:current_thread", [':current_thread' => $threads['thread_id']]);
                    }

                    if (!isset($threads['vote_count'])) {
                        $threads['vote_count'] = dbcount("(post_id)", DB_FORUM_VOTES, "thread_id=:current_thread", [':current_thread' => $threads['thread_id']]);
                    }

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
                                preg_match("(^\.{$match_regex}$|\.{$match_regex}\.|\.{$match_regex}$)", $userdata['user_threads']))
                        ) {
                            $icon = "<i class='".self::get_ForumIcons('thread')."' title='".$locale['forum_0261']."'></i>";
                        } else {
                            $icon = "<i class='".self::get_ForumIcons('new')."' title='".$locale['forum_0260']."'></i>";
                        }
                    }

                    $author = [
                        'user_id'     => $threads['thread_author'],
                        'user_name'   => $threads['author_name'],
                        'user_status' => $threads['author_status'],
                        'user_avatar' => $threads['author_avatar'],
                        'user_rank'   => parent::display_rank($user1['user_posts'], $user1['user_level'], $user1['user_groups']),
                    ];

                    $lastuser = [
                        'user_id'     => $threads['thread_lastuser'],
                        'user_name'   => $threads['last_user_name'],
                        'user_status' => $threads['last_user_status'],
                        'user_avatar' => $threads['last_user_avatar'],
                        'user_rank'   => parent::display_rank($user2['user_posts'], $user2['user_level'], $user2['user_groups']),
                    ];

                    // Automatic link to the latest post
                    $thread_rowstart = '';
                    if (!empty($threads['thread_postcount']) && !empty($forum_settings['posts_per_page'])) {
                        if ($threads['thread_postcount'] > $forum_settings['posts_per_page']) {
                            $thread_rowstart = $forum_settings['posts_per_page'] * floor($threads['thread_postcount'] / $forum_settings['posts_per_page']);
                            $thread_rowstart = "&amp;rowstart=".$thread_rowstart;
                        }
                    }

                    $thread_link = FORUM."viewthread.php?thread_id=".$threads['thread_id'].$thread_rowstart."&amp;pid=".$threads['thread_lastpostid']."#post_".$threads['thread_lastpostid'];
                    if (isset($_GET['section']) && $_GET['section'] == "moderator") {
                        $thread_link = FORUM."index.php?section=moderator&amp;rid=".$threads['report_id'];
                    }

                    $threads += [
                        "thread_link"         => [
                            "link"  => $thread_link,
                            "title" => $threads['thread_subject']
                        ],
                        "forum_type"          => $threads['forum_type'],
                        "thread_pages"        => makepagenav(0, $forum_settings['posts_per_page'], $threads['thread_postcount'], 3, FORUM."viewthread.php?thread_id=".$threads['thread_id']."&amp;"),
                        "thread_icons"        => [
                            'lock'   => $threads['thread_locked'] ? "<i class='".self::get_forumIcons('lock')."' title='".$locale['forum_0263']."'></i>" : '',
                            'sticky' => $threads['thread_sticky'] ? "<i class='".self::get_forumIcons('sticky')."' title='".$locale['forum_0103']."'></i>" : '',
                            'poll'   => $threads['thread_poll'] ? "<i class='".self::get_forumIcons('poll')."' title='".$locale['forum_0314']."'></i>" : '',
                            'hot'    => $threads['thread_postcount'] >= 20 ? "<i class='".self::get_forumIcons('hot')."' title='".$locale['forum_0311']."'></i>" : '',
                            'reads'  => $threads['thread_views'] >= 20 ? "<i class='".self::get_forumIcons('reads')."' title='".$locale['forum_0311']."'></i>" : '',
                            'attach' => $threads['attach_count'] > 0 ? "<i class='".self::get_forumIcons('image')."' title='".$locale['forum_0312']."'></i>" : '',
                            'icon'   => $icon,
                        ],
                        "thread_starter_text" => $locale['forum_0006'].' '.$locale['by']." ".profile_link($author['user_id'], $author['user_name'], $author['user_status'])."</span>",
                        "thread_starter"      => [
                            'author'       => $author,
                            'profile_link' => profile_link($author['user_id'], $author['user_name'], $author['user_status']),
                            'avatar'       => display_avatar($author, '45px', '', FALSE, 'img-circle'), // have settings
                            'avatar_sm'    => display_avatar($author, '25px', '', FALSE, 'img-circle'), // have settings
                        ],
                        "thread_last"         => [
                            'user'         => $lastuser,
                            'avatar'       => display_avatar($lastuser, '45px', '', FALSE, ''), // have settings
                            'avatar_sm'    => display_avatar($lastuser, '25px', '', FALSE, ''), // have settings
                            'profile_link' => profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status']),
                            'date'         => showdate('forumdate', $threads['thread_lastpost']),
                            'time'         => timer($threads['thread_lastpost']),
                        ],
                    ];

                    $info['threads']['item'][$threads[$filter['item_id']]] = $threads;
                }
            }

            if ($info['thread_max_rows'] > $info['thread_rows']) {
                $_page_nav_link = clean_request("", ["rowstart"], FALSE)."&amp;";
                // navigation type
                $info['threads']['pagenav'] = makepagenav($_GET['rowstart'], $forum_settings['threads_per_page'], $info['thread_max_rows'], 3, $_page_nav_link, 'rowstart', FALSE);
                // button type
                $info['threads']['pagenav2'] = makepagenav($_GET['rowstart'], $forum_settings['threads_per_page'], $info['thread_max_rows'], 3, $_page_nav_link, 'rowstart', TRUE);
            }
        }

        // Count for Filters
        $thread_sql = "SELECT t.thread_id FROM ".DB_FORUM_THREADS." t INNER JOIN ".DB_FORUMS." tf ON tf.forum_id=t.forum_id 
        ".$filter['join']." ".$filter['custom_join']." WHERE t.thread_hidden=0 ".$filter['custom_condition'].$filter['time_condition'];

        $thread_count = dbrows(dbquery($thread_sql));
        $attach_sql = "SELECT attach_id, count(a.attach_id) 'attach_count'
                FROM ".DB_FORUM_THREADS." t
                INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
                ".(!stristr($filter['join'], DB_FORUM_ATTACHMENTS) ? "INNER JOIN ".DB_FORUM_ATTACHMENTS." a ON t.thread_id=a.thread_id" : "")."
                ".$filter['join']."
                ".$filter['custom_join']."
                WHERE t.thread_hidden=0 AND (a.attach_id IS NOT NULL OR attach_count > 0) ".$filter['custom_condition'].$filter['time_condition']."
                GROUP BY a.thread_id";
        $attach_count = dbrows(dbquery($attach_sql));

        $poll_sql = "SELECT t.thread_id 
                FROM ".DB_FORUM_THREADS." t
                INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
                ".$filter['join']."
                ".$filter['custom_join']."                
                WHERE t.thread_poll=1 AND t.thread_hidden=0 ".$filter['custom_condition'].$filter['time_condition']."
                GROUP BY thread_id";
        $poll_count = dbrows(dbquery($poll_sql));

        $discuss_only_count = $thread_count - $attach_count - $poll_count; // LOL

        $url = clean_request('', ['type'], FALSE);
        $info['filters']['type'] = [
            'all'         => [
                'link'   => $url."&amp;type=all",
                'title'  => $locale['forum_0374'],
                'icon'   => '',
                'active' => FALSE,
                'count'  => $thread_count
            ],
            'attachments' => [
                'link'   => $url."&amp;type=attachments",
                'title'  => $locale['forum_0223'],
                'icon'   => "<i class='fa fa-file-text-o text-info m-r-5'></i>",
                'active' => FALSE,
                'count'  => $attach_count ?: 0,
            ],
            'poll'        => [
                'link'   => $url."&amp;type=poll",
                'title'  => $locale['forum_0314'],
                'icon'   => "<i class='fa fa-bar-chart text-success m-r-5'></i>",
                'active' => FALSE,
                'count'  => $poll_count ?: 0,
            ],
            'discussions' => [
                'link'   => $url."&amp;type=discussions",
                'title'  => $locale['forum_0222'],
                'icon'   => "<i class='fa fa-comment text-primary m-r-5'></i>",
                'active' => FALSE,
                'count'  => $discuss_only_count
            ]
        ];

        $i = 0;
        foreach (array_keys($info['filters']['type']) as $key) {
            if ((isset($_GET['type']) && $key == $_GET['type']) || ($i == 0 && !isset($_GET['type']))) {
                $info['filters']['type'][$key]['active'] = TRUE;
            }
            $i++;
        }

        if (!empty($filter['debug']) && !$info['thread_max_rows']) print_p($info);

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

        $forum_settings = parent::get_forum_settings();
        $locale = fusion_get_locale("", [FORUM_LOCALE, FORUM_TAGS_LOCALE]);
        $userdata = fusion_get_userdata();
        $forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');

        $this->thread_data = self::get_thread_view($_GET['thread_id']); // fetch query and define iMOD

        if (!empty($this->thread_data) && !empty($_GET['thread_id']) && isnum($_GET['thread_id']) && $this->check_forum_access($forum_index, 0, $_GET['thread_id'])) {

            if ($this->thread_data['forum_type'] == 1) {
                if (fusion_get_settings("site_seo")) {
                    redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
                }
                redirect(FORUM.'index.php');
            }

            // get post_count, lastpost_id, first_post_id.
            $thread_stat = self::get_thread_stats($_GET['thread_id']);

            $this->thread_data['thread_firstpostid'] = $thread_stat['first_post_id'];
            // get first post result
            $first_post_result = dbquery("SELECT post_message, post_datestamp FROM ".DB_FORUM_POSTS." WHERE post_id=:pid", [":pid" => $thread_stat['first_post_id']]);
            if (dbrows($first_post_result)) {
                $this->thread_data += dbarray($first_post_result);
            }

            if (!$thread_stat['post_count']) {
                if (fusion_get_settings("site_seo")) {
                    redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
                }
                redirect(FORUM."index.php");
            }

            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart'])
            && $_GET['rowstart'] <= $thread_stat['last_post_id'] ? $_GET['rowstart'] : 0; // secure against XSS

            // Set the thread permissions
            $this->setThreadPermission(Forum_Moderator::check_forum_mods($this->thread_data['forum_mods']));

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
            $this->add_forum_breadcrumbs($forum_index, $this->thread_data['forum_id']);
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
                    $this->thread_info['buttons']['notify'] = [
                        'link'  => INFUSIONS."forum/postify.php?post=off&amp;forum_id=".$this->thread_data['forum_id']."&amp;thread_id=".$this->thread_data['thread_id'],
                        'title' => $locale['forum_0174']
                    ];
                } else {
                    $this->thread_info['buttons']['notify'] = [
                        'link'  => INFUSIONS."forum/postify.php?post=on&amp;forum_id=".$this->thread_data['forum_id']."&amp;thread_id=".$this->thread_data['thread_id'],
                        'title' => $locale['forum_0175']
                    ];
                }
                // only member can lodge a report
                if (isset($_GET['report']) && isset($_GET['rtid'])) {
                    Forum_Reports::render_report_form();
                }
            }

            $this->thread_info['thread'] = $this->thread_data;
            /*
             * Get the first post author
             */
            $thread_author = [
                "user_id"      => -1,
                "user_name"    => $locale['forum_0667'],
                "user_avatar"  => "",
                "user_level"   => USER_LEVEL_ADMIN,
                "user_joined"  => TIME,
                "user_status"  => 0,
                "profile_link" => "",
            ];

            $author = fusion_get_user($this->thread_data['thread_author']);
            if (!empty($author['user_name'])) {
                $thread_author = [
                    "user_id"      => $author['user_id'],
                    "user_name"    => $author['user_name'],
                    "user_avatar"  => display_avatar($author, "55px"),
                    "user_level"   => $author['user_level'],
                    "user_joined"  => $author['user_joined'],
                    "user_status"  => $author['user_status'],
                    "profile_link" => profile_link($author['user_id'], $author['user_name'], $author['user_status'])
                ];
                unset($author);
            }

            $this->thread_info['thread']['thread_author'] = $thread_author;

            /**
             * Generate Quick Reply Form
             */
            $qr_form = ($this->getThreadPermission("can_reply") == TRUE && $this->thread_data['forum_quick_edit'] == TRUE && iMEMBER ? Quick_Reply::display_quickReply($this->thread_data) : "");

            /**
             * Generate Poll Form
             */
            $poll = new Forum_Poll($this->thread_info);
            $poll_form = $poll->generate_poll($this->thread_data);
            $poll_info = $poll->get_poll_info();

            /**
             * Generate Attachment
             */
            $attach = new Forum_Attachment($this->thread_info);
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

                $this->moderator()->setForumID($this->thread_data['forum_id']);
                $this->moderator()->setThreadId($this->thread_data['thread_id']);
                $this->moderator()->set_modActions();

                /**
                 * Thread moderation form template
                 */
                $addition = isset($_GET['rowstart']) ? "&amp;rowstart=".intval($_GET['rowstart']) : "";
                $this->thread_info['form_action'] = FORUM."viewthread.php?thread_id=".intval($this->thread_data['thread_id']).$addition;

                $this->thread_info['mod_options'] = [
                    'renew'                                                      => $locale['forum_0207'],
                    'delete'                                                     => $locale['forum_0201'],
                    $this->thread_data['thread_locked'] ? "unlock" : "lock"      => $this->thread_data['thread_locked'] ? $locale['forum_0203'] : $locale['forum_0202'],
                    $this->thread_data['thread_sticky'] ? "nonsticky" : "sticky" => $this->thread_data['thread_sticky'] ? $locale['forum_0205'] : $locale['forum_0204'],
                    'move'                                                       => $locale['forum_0206']
                ];

                // New Mod form Parts (Andromeda)
                $this->thread_info['mod_form_parts']['openform'] = openform('moderator_menu', 'post', $this->thread_info['form_action']).form_hidden('delete_item_post', '', '');
                $this->thread_info['mod_form_parts']['button_group'] = "<div class='btn-group m-r-10'>\n
                        ".form_button("check_all", $locale['forum_0080'], $locale['forum_0080'], ['class' => 'btn-default', "type" => "button"])."
                        ".form_button("check_none", $locale['forum_0081'], $locale['forum_0080'], ['class' => 'btn-default', "type" => "button"])."
                    </div>\n";
                $this->thread_info['mod_form_parts']['move_button'] = form_button('move_posts', $locale['forum_0176'], $locale['forum_0176'], ['class' => 'btn-default', 'icon' => 'fas fa-truck-loading']);
                $this->thread_info['mod_form_parts']['delete_button'] = form_button('delete_posts', $locale['delete'], $locale['forum_0177'], ['class' => 'btn-default', 'icon' => 'fas fa-trash']);
                $this->thread_info['mod_form_parts']['dropdown'] = form_select('step', '', '', [
                    'options'     => $this->thread_info['mod_options'],
                    'placeholder' => $locale['forum_0200'],
                    'width'       => '180px',
                    'inner_width' => '180px',
                    'allowclear'  => TRUE,
                    'class'       => 'm-b-0 display-inline-block',
                    'inline'      => TRUE
                ]);
                $this->thread_info['mod_form_parts']['go_button'] = form_button('go', $locale['forum_0208'], $locale['forum_0208'], ['class' => 'btn-default']);
                $this->thread_info['mod_form_parts']['closeform'] = closeform();

                // Reconstruct the mod bar
                $this->thread_info['mod_form'] =
                    $this->thread_info['mod_form_parts']['openform'].
                    $this->thread_info['mod_form_parts']['button_group'].
                    $this->thread_info['mod_form_parts']['move_button'].
                    $this->thread_info['mod_form_parts']['delete_button']."
                    <div class='pull-right'>".$this->thread_info['mod_form_parts']['dropdown']."<div class='pull-right'>".$this->thread_info['mod_form_parts']['go_button']."</div></div>".
                    $this->thread_info['mod_form_parts']['closeform'];

                // Mod form jquery codes
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
                'forum_cat'            => isset($_GET['forum_cat']) && self::verify_forum($_GET['forum_cat']) ? $_GET['forum_cat'] : 0,
                'forum_branch'       => isset($_GET['forum_branch']) && self::verify_forum($_GET['forum_branch']) ? $_GET['forum_branch'] : 0,
                'forum_link'         => [
                    'link'  => FORUM.'index.php?viewforum&amp;forum_id='.$this->thread_data['forum_id'].'&amp;forum_cat='.$this->thread_data['forum_cat'].'&amp;forum_branch='.$this->thread_data['forum_branch'],
                    'title' => $this->thread_data['forum_name']
                ],
                'thread_attachments' => $attachments,
                'post_id'            => isset($_GET['post_id']) && self::verify_post($_GET['post_id']) ? intval($_GET['post_id']) : 0,
                'pid'                => isset($_GET['pid']) && isnum($_GET['pid']) ? $_GET['pid'] : 0,
                'section'            => isset($_GET['section']) ? $_GET['section'] : '',
                'sort_post'          => isset($_GET['sort_post']) ? $_GET['sort_post'] : '',
                'forum_moderators'   => $this->moderator()->parse_forum_mods($this->thread_data['forum_mods']),
                'max_post_items'     => $thread_stat['post_count'],
                'post_firstpost'     => $thread_stat['first_post_id'],
                'post_lastpost'        => $thread_stat['last_post_id'],
                'posts_per_page'       => $forum_settings['posts_per_page'],
                'threads_per_page'     => $forum_settings['threads_per_page'],
                'lastvisited'          => (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time(),
                'allowed_post_filters' => ['oldest', 'latest', 'high'],
                'attachtypes'        => explode(',', $forum_settings['forum_attachtypes']),
                'quick_reply_form'   => $qr_form,
                'thread_bounty'      => $bounty_display,
                'poll_form'          => $poll_form,
                'poll_info'          => $poll_info,
                'post-filters'       => [],
                'mod_options'        => [],
                'form_action'        => '',
                'open_post_form'     => '',
                'close_post_form'    => '',
                'mod_form'           => '',
                'mod_form_parts'     => '',
                'permissions'        => $this->getThreadPermission()
            ];

            if (!empty($this->thread_info['thread_tags'])) {
                $this->thread_info['thread_tags_display'] = $this->tag(FALSE)->display_thread_tags($this->thread_info['thread_tags']);
            }

            /**
             * Generate All Thread Buttons
             */
            $this->thread_info['buttons'] += [
                'print'     => [
                    'link'  => BASEDIR.'print.php?type=F&amp;item_id='.$this->thread_data['thread_id'].'&amp;rowstart='.$_GET['rowstart'],
                    'title' => $locale['forum_0178']
                ],
                'newthread' => $this->getThreadPermission('can_post') == TRUE ?
                    [
                        'link'  => FORUM.'newthread.php?forum_id='.$this->thread_data['forum_id'],
                        'title' => $this->thread_data['forum_type'] == 4 ? $locale['forum_0058'] : $locale['forum_0057']
                    ] : [],
                'reply'     => $this->getThreadPermission('can_reply') == TRUE ?
                    [
                        'link'  => FORUM.'viewthread.php?action=reply&amp;forum_id='.$this->thread_data['forum_id'].'&amp;thread_id='.$this->thread_data['thread_id'],
                        'title' => $locale['forum_0360']
                    ] : [],
                'poll'      => $this->getThreadPermission('can_create_poll') == TRUE ?
                    [
                        'link'  => FORUM.'viewthread.php?action=newpoll&amp;forum_id='.$this->thread_data['forum_id'].'&amp;thread_id='.$this->thread_data['thread_id'],
                        'title' => $locale['forum_0366']
                    ] : [],
                'bounty'    => $this->getThreadPermission('can_start_bounty') == TRUE ? [
                    'link'  => FORUM.'viewthread.php?action=newbounty&amp;forum_id='.$this->thread_data['forum_id'].'&amp;thread_id='.$this->thread_data['thread_id'],
                    'title' => $locale['forum_0399'],
                ] : [],
            ];

            /**
             * Generate Post Filters
             */
            $this->thread_info['post-filters'][0] = [
                'value'  => FORUM.'viewthread.php?thread_id='.$this->thread_data['thread_id'].'&amp;sort_post=oldest',
                'locale' => $locale['forum_0180']
            ];
            $this->thread_info['post-filters'][1] = [
                'value'  => FORUM.'viewthread.php?thread_id='.$this->thread_data['thread_id'].'&amp;sort_post=latest',
                'locale' => $locale['forum_0181']
            ];
            if ($this->getThreadPermission("can_rate") == TRUE) {
                $this->thread_info['allowed-post-filters'][2] = 'high';
                $this->thread_info['post-filters'][2] = [
                    'value'  => FORUM.'viewthread.php?thread_id='.$this->thread_info['thread_id'].'&amp;sort_post=high',
                    'locale' => $locale['forum_0182']
                ];
            }

            $this->handle_quick_reply();

            // Get Threads Post
            $post_info = $this->get_thread_post($this->thread_info['thread_id'], 0, [
                "forum_type"      => $this->thread_info['thread']['forum_type'],
                "post_firstpost"  => $this->thread_info['post_firstpost'],
                "post_lastpost"   => $this->thread_info['post_lastpost'],
                "rowstart"        => $_GET['rowstart'],
                "post_count"      => $thread_stat['post_count'],
                "thread_author"   => $this->thread_info['thread']['thread_author'],
                "thread_answered" => $this->thread_info['thread']['thread_answered'],
                "thread_locked"   => $this->thread_info['thread']['thread_locked'],
            ]);

            $this->thread_info = $this->thread_info + $post_info;

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
    private static function get_thread_view($thread_id = 0) {
        $userdata = fusion_get_userdata();
        $userid = !empty($userdata['user_id']) ? (int)$userdata['user_id'] : 0;
        $query = !empty(self::$custom_query) ? self::$custom_query : "SELECT t.*, f.*,
                u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_joined,
                IF (n.thread_id > 0, 1 , 0) 'user_tracked',
                count(v.vote_user) 'thread_rated',
                count(p.forum_vote_user_id) 'poll_voted'
                FROM ".DB_FORUM_THREADS." t
                LEFT JOIN ".DB_USERS." u ON t.thread_author = u.user_id
                INNER JOIN ".DB_FORUMS." f ON t.forum_id=f.forum_id
                LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = t.thread_id AND v.vote_user='".intval($userid)."' AND v.forum_id=f.forum_id AND f.forum_type='4'
                LEFT JOIN ".DB_FORUM_POLL_VOTERS." p ON p.thread_id = t.thread_id AND p.forum_vote_user_id='".intval($userid)."' AND t.thread_poll='1'
                LEFT JOIN ".DB_FORUM_THREAD_NOTIFY." n ON n.thread_id = t.thread_id AND n.notify_user = '".intval($userid)."'
                ".(multilang_table('FO') ? " WHERE f.forum_language='".LANGUAGE."' AND " : " WHERE ")."
                ".groupaccess('f.forum_access')." AND t.thread_id='".intval($thread_id)."' AND t.thread_hidden='0'";
        $result = dbquery($query);
        if (dbrows($result)) {
            $data = dbarray($result);
            if ($data['forum_id']) {
                Forum_Moderator::define_forum_mods($data);

                return (array)$data;
            } else {
                redirect(FORUM.'index.php');
            }
        } else {
            redirect(FORUM.'index.php');
        }
    }

    /**
     * Get post count, lastpost_id and first_post_id
     *
     * @param $thread_id
     *
     * @return array
     */
    public static function get_thread_stats($thread_id) {
        list($array['post_count'], $array['last_post_id'], $array['first_post_id']) = dbarraynum(dbquery("SELECT COUNT(post_id), MAX(post_id), MIN(post_id) FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($thread_id)."' AND post_hidden='0' GROUP BY thread_id"));

        return (array)$array;
    }

    /**
     * Set in full extent of forum permissions and current user thread permissions
     *
     * @param $iMOD
     *
     * @return array
     */
    private function setThreadPermission($iMOD) {
        // Access the forum
        $this->thread_info['permissions']['can_access'] = ($iMOD || checkgroup($this->thread_data['forum_access'])) ? TRUE : FALSE;
        // Create another thread under the same forum
        $this->thread_info['permissions']['can_post'] = $this->thread_info['permissions']['can_access'] && ($iMOD || (checkgroup($this->thread_data['forum_post']) && $this->thread_data['forum_lock'] == FALSE)) ? TRUE : FALSE;
        // Upload an attachment in this thread
        $this->thread_info['permissions']['can_upload_attach'] = $this->thread_data['forum_allow_attach'] == TRUE && ($iMOD || (checkgroup($this->thread_data['forum_attach']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE)) ? TRUE : FALSE;
        // Download an attachment in this thread
        $this->thread_info['permissions']['can_download_attach'] = $iMOD || ($this->thread_data['forum_allow_attach'] == TRUE && checkgroup($this->thread_data['forum_attach_download'])) ? TRUE : FALSE;
        // Post a reply in this thread
        $this->thread_info['permissions']['can_reply'] = $this->thread_data['thread_postcount'] > 0 && ($iMOD || (checkgroup($this->thread_data['forum_reply']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE)) ? TRUE : FALSE;
        // Create a poll
        $this->thread_info['permissions']['can_create_poll'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['thread_poll'] == FALSE && $this->thread_data['forum_allow_poll'] == TRUE && ($iMOD || (checkgroup($this->thread_data['forum_poll']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE)) ? TRUE : FALSE;
        // Edit a poll (modify the poll)
        $this->thread_info['permissions']['can_edit_poll'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['thread_poll'] == TRUE && ($iMOD || (checkgroup($this->thread_data['forum_poll']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE && $this->thread_data['thread_author'] == fusion_get_userdata('user_id'))) ? TRUE : FALSE;
        // Can vote a poll
        $this->thread_info['permissions']['can_vote_poll'] = $this->thread_info['permissions']['can_post'] && isset($this->thread_data['poll_voted']) && $this->thread_data['poll_voted'] == FALSE && ($iMOD || (checkgroup($this->thread_data['forum_vote']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE)) ? TRUE : FALSE;
        // Can vote in this thread
        $this->thread_info['permissions']['can_rate'] = $this->thread_info['permissions']['can_post'] && ($iMOD || (checkgroup($this->thread_data['forum_post_ratings']) && $this->thread_data['forum_lock'] == FALSE && $this->thread_data['thread_locked'] == FALSE)) ? TRUE : FALSE;
        // Can accept an answer
        $this->thread_info['permissions']['can_answer'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['forum_type'] == 4 && $this->thread_data['thread_answered'] == FALSE && $this->thread_data['thread_locked'] == FALSE && ($this->thread_data['thread_author'] == fusion_get_userdata('user_id') || $iMOD) ? TRUE : FALSE;
        // Can start a bounty
        $this->thread_info['permissions']['can_start_bounty'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['forum_type'] == 4 && iMEMBER && !$this->thread_data['thread_bounty'] && $this->thread_data['thread_locked'] == FALSE && fusion_get_userdata('user_reputation') >= 50 ? TRUE : FALSE;
        // Can edit a bounty
        $this->thread_info['permissions']['can_edit_bounty'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['forum_type'] == 4 && iMEMBER && $this->thread_data['thread_bounty'] && $this->thread_data['thread_locked'] == FALSE && ($this->thread_data['thread_bounty_user'] == fusion_get_userdata('user_id') || $iMOD) ? TRUE : FALSE;
        // Can award bounty
        $this->thread_info['permissions']['can_award_bounty'] = $this->thread_info['permissions']['can_post'] && $this->thread_data['forum_type'] == 4 && iMEMBER && $this->thread_data['thread_bounty'] && ($this->thread_data['thread_bounty_user'] == fusion_get_userdata('user_id')) ? TRUE : FALSE;

        return (array)$this->thread_info;
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
            if ($key !== NULL) {
                return (isset($this->thread_info['permissions'][$key]) ? $this->thread_info['permissions'][$key] : NULL);
            }

            return $this->thread_info['permissions'];
        }

        return NULL;
    }

    /**
     * Handle post of Quick Reply Form
     */
    private function handle_quick_reply() {
        // Also do a handle edit from here.
        $forum_settings = self::get_forum_settings();
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();

        if (isset($_POST['post_quick_reply'])) {
            if ($this->getThreadPermission("can_reply") && \defender::safe()) {
                $this->thread_data = $this->thread_info['thread'];
                require_once INCLUDES."flood_include.php";
                if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice
                    $post_data = [
                        'post_id'         => 0,
                        'forum_id'        => $this->thread_data['forum_id'],
                        'thread_id'       => $this->thread_data['thread_id'],
                        'post_message'    => form_sanitizer($_POST['post_message'], '', 'post_message'),
                        'post_cat'        => form_sanitizer($_POST['post_cat'], "", "post_cat"),
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

                    // Post category must be 0 if reply or quoted on first post.
                    if ($post_data['post_cat'] == $this->thread_data['thread_firstpostid']) {
                        $post_data['post_cat'] = 0;
                    }

                    if (\defender::safe()) { // post message is invalid or whatever is invalid

                        $update_forum_lastpost = FALSE;

                        // Prepare forum merging action
                        $last_post_author = dbarray(dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_data['thread_id']."' ORDER BY post_id DESC LIMIT 1"));
                        if ($last_post_author['post_author'] == $post_data['post_author'] && $this->thread_data['forum_merge']) {
                            $last_message = dbarray(dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_data['thread_id']."' ORDER BY post_id DESC"));
                            $post_data['post_id'] = $last_message['post_id'];
                            $post_data['post_message'] = $last_message['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate", TIME).":\n".$post_data['post_message'];
                            dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', ['primary_key' => 'post_id']);
                        } else {
                            $update_forum_lastpost = TRUE;
                            dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', ['primary_key' => 'post_id']);
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
     * @param int   $thread_id
     * @param int   $post_id
     * @param array $filter
     *
     * @todo: optimize post reply with a subnested query to reduce post^n queries.
     * @return array
     */
    public function get_thread_post($thread_id = 0, $post_id = 0, array $filter = array()) {
        global $pid;

        $forum_settings = self::get_forum_settings();
        $userdata = fusion_get_userdata();
        $locale = fusion_get_locale();

        $default_filter = [
            "forum_type"      => 0,
            "post_firstpost"  => 0,
            "post_lastpost"   => 0,
            "rowstart"        => 0,
            "post_query"      => "",
            "post_count"      => 1,
            "thread_author"   => 0,
            "thread_answered" => FALSE,
            "thread_locked"   => FALSE,
        ];

        $filter += $default_filter;

        $sort_post = isset($_GET['sort_post']) ? $_GET['sort_post'] : '';
        switch ($sort_post) {
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

        $post_query_cond = "";
        if ($thread_id) {
            $post_query_cond = "p.thread_id='".$thread_id."' AND";
        } elseif ($post_id) {
            $post_query_cond = "p.post_id='".$post_id."' AND";
        }

        $info['post_query'] = "SELECT p.*,
                    SUM(v.vote_points) 'vote_points',
                    IF(v2.vote_id, 1, 0) 'has_voted',
                    v2.vote_points 'has_voted_points',
                    COUNT(a.attach_id) 'attach_count'
                    FROM ".DB_FORUM_POSTS." p
                    LEFT JOIN ".DB_FORUM_VOTES." v ON v.post_id=p.post_id
                    LEFT JOIN ".DB_FORUM_VOTES." v2 ON v2.post_id=p.post_id AND v2.vote_user='".$userdata['user_id']."'
                    LEFT JOIN ".DB_FORUM_ATTACHMENTS." a ON p.thread_id=a.thread_id AND a.post_id=p.post_id
                    WHERE $post_query_cond p.post_hidden='0'
                    ".($filter['forum_type'] == '4' && !empty($filter['post_firstpost']) ? "OR p.post_id='".intval($filter['post_firstpost'])."'" : '')."
                    GROUP by p.post_id
                    ORDER BY $sortCol LIMIT ".intval($filter['rowstart']).", ".intval($forum_settings['posts_per_page']);

        require_once INCLUDES."mimetypes_include.php";
        // post query
        $result = dbquery($info['post_query']);
        $info['post_rows'] = dbrows($result);
        if ($info['post_rows'] > 0) {

            // Get all post reported in this thread
            $post_rep = [];
            $post_res = dbresult(dbquery("SELECT GROUP_CONCAT(post_id) 'post_id' FROM ".DB_FORUM_REPORTS." WHERE report_status=0"), 0);
            if (!empty($post_res)) {
                $post_rep = explode(",", $post_res);
            }

            $mood = self::mood();
            $response = $mood->post_mood();
            if ($response) {
                redirect(FUSION_REQUEST);
            }
            /* Set Threads Navigation */
            $info['thread_posts'] = format_word($info['post_rows'], $locale['fmt_post']);
            $info['page_nav'] = '';
            if ($filter['post_count'] > $forum_settings['posts_per_page']) {
                $highlight = (isset($_GET['highlight']) ? "&amp;highlight=".urlencode($_GET['highlight']) : '');
                $info['page_nav'] = makepagenav($filter['rowstart'], $forum_settings['posts_per_page'], $filter['post_count'], 3, FORUM."viewthread.php?thread_id=$thread_id$highlight&amp;");
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
            // Loop all results
            while ($pdata = dbarray($result)) {

                $default_author = [
                    'user_id'        => -1,
                    'user_name'      => $locale['forum_0667'],
                    'user_status'    => 0,
                    'user_avatar'    => "",
                    'user_level'     => USER_LEVEL_ADMIN,
                    'user_posts'     => "",
                    'user_groups'    => "",
                    'user_joined'    => "",
                    'user_lastvisit' => 0,
                    'user_ip'        => '0.0.0.0',
                    'user_sig'       => ""
                ];
                $user = fusion_get_user($pdata['post_author']);
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
                        'user_sig'       => (!empty($user['user_sig']) ? nl2br(parsesmileys(parseubb(stripslashes($user['user_sig'])))) : '')
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
                                    $module_file_path = INCLUDES.'user_fields/public/'.$fieldAttr['field_name'].'/'.$fieldAttr['field_name'].'_include.php';
                                    $module_locale_file_path = LOCALE.LOCALESET.'user_fields/public/'.$fieldAttr['field_name'].'/'.$fieldAttr['field_name'].'.php';
                                    if (file_exists($module_file_path) && file_exists($module_locale_file_path)) {
                                        $profile_method = 'display';
                                        $user_fields = [];
                                        include($module_locale_file_path);
                                        include($module_file_path);
                                        if (!empty($user_fields) && is_array($user_fields)) {
                                            $user_fields['field_cat_name'] = UserFieldsQuantum::parse_label($fieldAttr['field_cat_name']);
                                            $author['user_profiles'][$field_name] = $user_fields;
                                        }
                                    }
                                } else {
                                    // this is just normal type
                                    $author['user_profiles'][$field_name] = [
                                        'field_cat_name' => UserFieldsQuantum::parse_label($fieldAttr['field_cat_name']),
                                        'title'          => UserFieldsQuantum::parse_label($fieldAttr['field_title']),
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
                $pdata['post_reported'] = (in_array($pid, $post_rep) ? 1 : 0);
                if ($pdata['post_reported']) {
                    $pdata['post_report_id'] = dbresult(dbquery("SELECT report_id FROM ".DB_FORUM_REPORTS." WHERE post_id=:pid", [":pid" => intval($pid)]), 0);
                }

                // Format Post Message
                $post_message = empty($pdata['post_smileys']) ? parsesmileys($pdata['post_message']) : $pdata['post_message'];
                $post_message = nl2br(parseubb($post_message));
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
                                if (in_array($attachData['attach_mime'], img_mimeTypes())) {
                                    $aImage .= display_image_attach($attachData['attach_name'], "200", "200", $pdata['post_id'])."\n";
                                    $aFiles_Count++;
                                } else {
                                    $current_file = FORUM.'attachments/'.$attachData['attach_name'];
                                    $aFiles .= "<div class='display-inline-block'><i class='fa fa-paperclip'></i><a href='".INFUSIONS."forum/viewthread.php?thread_id=".$pdata['thread_id']."&amp;getfiles=".$attachData['attach_id']."'>".$attachData['attach_name']."</a>&nbsp;";
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
                $default_blank_arr = ["link" => "", "title" => ""];
                $pdata += [
                    'user_online'        => $pdata['user_lastvisit'] >= time() - 300 ? TRUE : FALSE,
                    'is_first_post'      => $pdata['post_id'] == $filter['post_firstpost'] ? TRUE : FALSE,
                    'is_last_post'       => $pdata['post_id'] == $filter['post_lastpost'] ? TRUE : FALSE,
                    'user_profile_link'  => profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']),
                    'user_avatar_image'  => display_avatar($pdata, '50px', FALSE, FALSE, 'img-rounded'),
                    'user_post_count'    => format_word($pdata['user_posts'], $locale['fmt_post'], ['html' => FALSE]),
                    "user_sig"           => "",
                    "user_message"       => $default_blank_arr,
                    'print'              => [
                        'link'  => BASEDIR.'print.php?type=F&amp;item_id='.$thread_id.'&amp;post='.$pdata['post_id'].'&amp;nr='.($i + $_GET['rowstart']),
                        'title' => $locale['forum_0179']
                    ],
                    'post_marker'        => $post_marker,
                    'marker'             => $marker,
                    'post_attachments'   => $post_attachments,
                    'post_reply_message' => '',
                    'post_bounty'        => [],
                    "post_votes"         => [],
                    "vote_message"       => "",
                    "vote_answered"      => "",
                    "post_answer_check"  => "",
                ];

                $pdata['post_message'] = $post_message;

                /**
                 * Who has replied to this post.
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

                    $replies_sql = "SELECT post_id, post_datestamp, post_author 
                    FROM ".DB_FORUM_POSTS." WHERE post_cat=:post_id AND thread_id=:thread_id AND forum_id=:forum_id 
                    GROUP BY post_author ORDER BY post_datestamp DESC";

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
                                'user_status' => $user_replied['user_status'],
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
                $pdata['post_mood'] = $this->mood()->set_PostData($pdata)->display_mood_buttons();
                $pdata['post_mood_message'] = ($pdata['post_mood']) ? $this->mood()->get_mood_message() : '';
                /*
                 * Bounty payment
                 */
                if ($this->getThreadPermission('can_award_bounty') && $pdata['post_author'] !== fusion_get_userdata('user_id')) {
                    $pdata['post_bounty'] = [
                        'link'  => FORUM.'viewthread.php?action=award&amp;forum_id='.$pdata['forum_id'].'&amp;thread_id='.$pdata['thread_id'].'&amp;post_id='.$pdata['post_id'],
                        'title' => $locale['forum_4107']
                    ];
                }
                /**
                 * User Stuffs, Sig, User Message, Web
                 */
                // Quote & Edit Link
                if ($this->getThreadPermission('can_reply')) {
                    if (!$filter['thread_locked']) {
                        // Check first post.
                        $reply_link = INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'];
                        $quote_link = INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id']."&amp;quote=".$pdata['post_id'];
                        if ($pdata['post_id'] == $filter['post_firstpost']) {
                            $quote_link = INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;quote=".$pdata['post_id'];
                            $reply_link = INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id'];
                        }
                        $pdata['post_quote'] = [
                            'link'  => $quote_link,
                            'title' => $locale['forum_0266']
                        ];
                        if (iMOD || (
                                (($forum_settings['forum_edit_lock'] == TRUE && $pdata['is_last_post'] || $forum_settings['forum_edit_lock'] == FALSE))
                                && ($userdata['user_id'] == $pdata['post_author'])
                                && ($forum_settings['forum_edit_timelimit'] <= 0 || time() - $forum_settings['forum_edit_timelimit'] * 60 < $pdata['post_datestamp'])
                            )
                        ) {
                            $pdata['post_edit'] = [
                                'link'  => INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                                'title' => $locale['forum_0265']
                            ];
                        }
                        // Check first post
                        $pdata['post_reply'] = [
                            'link'  => $reply_link,
                            'title' => $locale['forum_0509']
                        ];
                    } else if (iMOD) {
                        $pdata['post_edit'] = [
                            'link'  => INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                            'title' => $locale['forum_0265']
                        ];
                    }
                }
                if (iMEMBER) {
                    $pdata['post_report'] = [
                        "link"  => clean_request("report=true&rtid=".$pdata['post_id'], ['report', 'rtid'], FALSE),
                        "title" => "Report"
                    ];
                }
                // rank img
                if ($pdata['user_level'] <= USER_LEVEL_ADMIN) {
                    if ($forum_settings['forum_ranks']) {
                        $pdata['user_rank'] = self::display_rank($pdata['user_posts'], $pdata['user_level'], $pdata['user_groups']);
                    } else {
                        $pdata['user_rank'] = getuserlevel($pdata['user_level']);
                    }
                } else {
                    if ($forum_settings['forum_ranks']) {
                        $pdata['user_rank'] = iMOD ? self::display_rank($pdata['user_posts'], 104, $pdata['user_groups']) : self::display_rank($pdata['user_posts'], $pdata['user_level'], $pdata['user_groups']);
                    } else {
                        $pdata['user_rank'] = iMOD ? $locale['userf1'] : getuserlevel($pdata['user_level']);
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
                if (iMEMBER && $pdata['user_id'] != $userdata['user_id'] && (iADMIN || $pdata['user_status'] != 6 && $pdata['user_status'] != 5)) {
                    $pdata['user_message'] = [
                        'link'  => BASEDIR.'messages.php?msg_send='.$pdata['user_id'],
                        "title" => $locale['send_message']
                    ];
                }

                // User Sig
                if (!empty($pdata['user_sig']) && $pdata['user_sig'] && isset($pdata['post_showsig']) && $pdata['post_showsig'] == 1 && $pdata['user_status'] != 6 && $pdata['user_status'] != 5) {
                    $pdata['user_sig'] = nl2br(parsesmileys(parseubb(stripslashes($pdata['user_sig']))));
                }

                // Voting - need up or down link - accessible to author also the vote
                // answered and on going questions.
                // Answer rating

                //echo $data['forum_type'] == 4 ? "<br/>\n".(number_format($data['thread_postcount']-1)).$locale['forum_0365']."" : ''; // answers
                // form components
                $pdata['post_checkbox'] = iMOD ? "<input type='checkbox' name='delete_post[]' value='".$pdata['post_id']."'/>" : '';
                // Voting up
                // Support Type
                if ($filter['forum_type'] == 4) {
                    // If I am author, I can mark as answered
                    if ($filter['thread_author'] == fusion_get_userdata('user_id') or iMOD) {
                        // all post items have checkbox greyed.
                        // if thread is answered, then just this post is answer have checkbox
                        if ($filter['thread_answered'] && $pdata['post_answer']) {
                            // Is Answer
                            $pdata['vote_answered'] = [
                                'link'  => FORUM."postify.php?post=answer&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                                'title' => $locale['forum_0513'] //0513
                            ];
                            $pdata['post_answer_check'] = "<a href='".$pdata['vote_answered']['link']."' class='answer_button answer_checked display-block center-x text-center' title='".$pdata['vote_answered']['title']."'><i class='fa fa-check fa-2x'></i></a>";
                        } else {
                            // Is not an answer
                            $pdata['vote_answered'] = [
                                'link'  => FORUM."postify.php?post=answer&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                                'title' => $locale['forum_0512'] //0512
                            ];
                            $pdata['post_answer_check'] = "<a href='".$pdata['vote_answered']['link']."' class='answer_button answer_unchecked display-block center-x text-center' title='".$pdata['vote_answered']['title']."'><i class='fa fa-check fa-2x'></i></a>";
                        }
                    }
                }

                // this one must remodify
                if ($this->getThreadPermission('can_rate')) { // can vote.
                    $pdata['post_votes']['up'] = [
                        'link'   => INFUSIONS."forum/postify.php?post=voteup&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                        "title"  => $locale['forum_0510'],
                        'active' => $pdata['has_voted'] && $pdata['has_voted_points'] > 0 ? TRUE : FALSE,
                    ];
                    $pdata['post_votes']['down'] = [
                        'link'   => INFUSIONS."forum/postify.php?post=votedown&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
                        "title"  => $locale['forum_0511'],
                        'active' => $pdata['has_voted'] && $pdata['has_voted_points'] < 0 ? TRUE : FALSE,
                    ];

                    /*$pdata['post_votebox'] = "<div class='text-center post_vote_box'>\n";
                    $pdata['post_votebox'] .= "<a href='".$pdata['post_vote_up']['link']."' class='text-center vote_up".($pdata['post_vote_up']['active'] ? " text-warning" : '')."' title='".$locale['forum_0510']."'>\n<i class='fa fa-caret-up fa-2x'></i></a>";
                    $pdata['post_votebox'] .= "<h3 class='m-0'>".(!empty($pdata['vote_points']) ? $pdata['vote_points'] : 0)."</h3>\n";
                    $pdata['post_votebox'] .= "<a href='".$pdata['post_vote_down']['link']."' class='text-center vote_down".($pdata['post_vote_down']['active'] ? " text-warning" : '')."' title='".$locale['forum_0511']."'>\n<i class='fa fa-caret-down fa-2x'></i></a>";
                    $pdata['post_votebox'] .= "</div>\n";*/
                } else {
                    $pdata['post_votebox'] = "<div class='text-center'>\n";
                    $pdata['post_votebox'] .= "<h3 class='m-0'>".(!empty($pdata['vote_points']) ? $pdata['vote_points'] : 0)."</h3>\n";
                    $pdata['post_votebox'] .= "</div>\n";
                }

                $pdata['post_edit_reason'] = '';
                if ($pdata['post_edittime']) {
                    $e_user = fusion_get_user($pdata['post_edituser']);
                    $edit_user = [
                        'edit_userid'     => "0",
                        'edit_username'   => "Moderator",
                        'edit_userstatus' => USER_LEVEL_ADMIN
                    ];
                    if ($e_user) {
                        $edit_user = [
                            'edit_userid'     => $e_user['user_id'],
                            'edit_username'   => $e_user['user_name'],
                            'edit_userstatus' => $e_user['user_status'],
                        ];
                        $pdata += $edit_user;
                    }
                    $edit_reason = "<div class='post-edit-reason'>".$locale['forum_0164']." ".profile_link($edit_user['edit_userid'], $edit_user['edit_username'], $edit_user['edit_userstatus'])." ".$locale['forum_0167']." ".showdate("forumdate", $pdata['post_edittime']).", ".timer($pdata['post_edittime']);
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
                $pdata['post_links'] = '';
                $pdata['post_links'] .= !empty($pdata['post_quote']) ? "<a class='btn btn-xs btn-default' title='".$pdata['post_quote']["title"]."' href='".$pdata['post_quote']['link']."'>".$pdata['post_quote']['title']."</a>\n" : '';
                $pdata['post_links'] .= !empty($pdata['post_edit']) ? "<a class='btn btn-xs btn-default' title='".$pdata['post_edit']["title"]."' href='".$pdata['post_edit']['link']."'>".$pdata['post_edit']['title']."</a>\n" : '';
                $pdata['post_links'] .= !empty($pdata['print']) ? "<a class='btn btn-xs btn-default' title='".$pdata['print']["title"]."' href='".$pdata['print']['link']."'>".$pdata['print']['title']."</a>\n" : '';
                $pdata['post_links'] .= !empty($pdata['user_web']) ? "<a class='btn btn-xs btn-default forum_user_actions' href='".$pdata['user_web']['link']."' target='_blank'>".$pdata['user_web']['title']."</a>\n" : '';
                $pdata['post_links'] .= !empty($pdata['user_message']) ? "<a class='btn btn-xs btn-default' href='".$pdata['user_message']['link']."' target='_blank'>".$pdata['user_message']['title']."</a>\n" : '';
                // Post Date
                $pdata['post_date'] = $locale['forum_0524']." ".timer($pdata['post_datestamp'])." - ".showdate('forumdate', $pdata['post_datestamp']);
                $pdata['post_shortdate'] = $locale['forum_0524']." ".timer($pdata['post_datestamp']);
                $pdata['post_longdate'] = $locale['forum_0524']." ".showdate('forumdate', $pdata['post_datestamp']);

                //$this->thread_info['post_items'][$pdata['post_id']] = $pdata;
                $info['post_items'][$pdata['post_id']] = $pdata;
                $i++;
                //print_p($pdata);
            }
        }

        return (array)$info;

    }

    /**
     * New Status
     */
    public function set_thread_visitor() {
        if (iMEMBER) {
            $userdata = fusion_get_userdata();
            $thread_match = $this->thread_info['thread_id']."\|".$this->thread_info['thread']['thread_lastpost']."\|".$this->thread_info['thread']['forum_id'];
            if (($this->thread_info['thread']['thread_lastpost'] < $this->thread_info['lastvisited']) && !preg_match("(^\.{$thread_match}$|\.{$thread_match}\.|\.{$thread_match}$)", $userdata['user_threads'])) {
                dbquery("UPDATE ".DB_USERS." SET user_threads='".$userdata['user_threads'].".".stripslashes($thread_match)."' WHERE user_id='".$userdata['user_id']."'");
            }
        }
    }

}

require_once INCLUDES."bbcode_include.php";