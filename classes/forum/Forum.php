<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum.php
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

namespace PHPFusion\Infusions\Forum\Classes\Forum;

use PHPFusion\Infusions\Forum\Classes\ForumModerator;
use PHPFusion\Infusions\Forum\Classes\ForumServer;

/**
 * Class Forum
 *
 * @package PHPFusion\Infusions\Forum
 */
class Forum extends ForumServer {

    /**
     * Forum Data
     *
     * @var array
     */
    private $forum_info = [];
    private $is_viewforum = FALSE;

    /**
     * @param $key
     *
     * @return array
     */
    public static function xxxxxx($key) {
        switch ($key) {
            default:
            case 'latest':
                return [
                    'attachment' => dbresult(dbquery("SELECT t.thread_id FROM ".DB_FORUM_THREADS." t INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
                            INNER JOIN ".DB_FORUM_ATTACHMENTS." a ON a.thread_id=t.thread_id
                            WHERE ".(multilang_table("FO") ? in_group('tf.forum_language', LANGUAGE)." AND " : "").groupaccess('tf.forum_access')." AND t.thread_locked=0 AND t.thread_hidden=0 GROUP BY a.thread_id"), 0),
                    'bounty'     => dbcount("(thread_id)", DB_FORUM_THREADS." t INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id", (multilang_table("FO") ? in_group('tf.forum_language', LANGUAGE)." AND " : "")." t.thread_bounty=1 AND t.thread_locked=0 AND t.thread_hidden=0 AND ".groupaccess('tf.forum_access')),
                    'poll'       => dbcount("(thread_id)", DB_FORUM_THREADS." t INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id", (multilang_table("FO") ? in_group('tf.forum_language', LANGUAGE)." AND " : "")." t.thread_poll=1 AND t.thread_locked=0 AND t.thread_hidden=0 AND ".groupaccess('tf.forum_access')),
                ];
                break;
            case 'participated':
                break;
            case 'unanswered':
                return [
                    'attachment' => dbresult(dbquery("SELECT t.thread_id FROM ".DB_FORUM_THREADS." t INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
                                    INNER JOIN ".DB_FORUM_ATTACHMENTS." a ON a.thread_id=t.thread_id
                                    WHERE ".(multilang_table("FO") ? in_group('tf.forum_language', LANGUAGE)." AND " : "")." t.thread_postcount=1 AND ".groupaccess('tf.forum_access')." AND t.thread_locked=0 AND t.thread_hidden=0
                                    GROUP BY a.thread_id"), 0),
                    'bounty'     => dbcount("(thread_id)", DB_FORUM_THREADS." t INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id", (multilang_table("FO") ? in_group('tf.forum_language', LANGUAGE)." AND " : "")." t.thread_bounty=1 AND t.thread_postcount=1 AND t.thread_locked=0 AND t.thread_hidden=0 AND ".groupaccess('tf.forum_access')),
                    'poll'       => dbcount("(thread_id)", DB_FORUM_THREADS." t INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id", (multilang_table("FO") ? in_group('tf.forum_language', LANGUAGE)." AND " : "")." t.thread_poll=1 AND t.thread_postcount=1  AND t.thread_locked=0 AND t.thread_hidden=0 AND ".groupaccess('tf.forum_access')),
                ];
                break;
            case 'unsolved':
                return [
                    'attachment' => dbresult(dbquery("SELECT t.thread_id FROM ".DB_FORUM_THREADS." t INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
                                    INNER JOIN ".DB_FORUM_ATTACHMENTS." a ON a.thread_id=t.thread_id
                                    WHERE ".(multilang_table("FO") ? in_group('tf.forum_language', LANGUAGE)." AND " : "").groupaccess('tf.forum_access')." AND
                                    tf.forum_type=4 AND t.thread_bounty=1 AND t.thread_answered=0 AND t.thread_locked=0 AND t.thread_hidden=0 GROUP BY a.thread_id"), 0),
                    'bounty'     => dbcount("(thread_id)", DB_FORUM_THREADS." t INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id", (multilang_table("FO") ? in_group('tf.forum_language', LANGUAGE)." AND " : "")." tf.forum_type=4
                    AND t.thread_bounty=1 AND t.thread_answered=0 AND t.thread_locked=0 AND t.thread_hidden=0 AND ".groupaccess("tf.forum_access")),
                    'poll'       => dbcount("(thread_id)", DB_FORUM_THREADS." t INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id", (multilang_table("FO") ? in_group('tf.forum_language', LANGUAGE)." AND " : "")." tf.forum_type=4 AND t.thread_bounty=1 AND t.thread_locked=0 AND t.thread_answered=0 AND t.thread_poll=1 AND t.thread_hidden=0 AND ".groupaccess("tf.forum_access")),
                ];
                break;
        }

        return NULL;
    }

    /**
     * @return array
     */
    public function getForumInfo() {
        return $this->forum_info;
    }

    /**
     * Executes forum
     */
    public function setForumInfo() {

        $forum_settings = self::get_forum_settings();
        $userdata = fusion_get_userdata();
        $locale = fusion_get_locale();

        // security boot due to insufficient access level
        $this->checkViewForum();

        // legacy version panel support
        $this->legacyPanelRedirect();

        $this->forum_info = [
            'link'             => FORUM,
            'forum_id'         => get('forum_id', FILTER_VALIDATE_INT),
            'parent_id'        => 0,
            'forum_page_link'  => [],
            'new_thread_link'  => '',
            'lastvisited'      => (int)(isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit']) ? $userdata['user_lastvisit'] : time()),
            'posts_per_page'   => $forum_settings['posts_per_page'],
            'threads_per_page' => $forum_settings['threads_per_page'],
            'forum_index'      => dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat', (multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('forum_access')), // waste resources here.
            'threads'          => [],
            'section'          => $this->getCurrentSection(),
            'new_topic_link'   => ['link' => FORUM.'newthread.php', 'title' => $locale['forum_0057']],
        ];

        // Rss panel feed support
        $this->loadXML();

        add_to_title($locale['global_200'].$locale['forum_0000']);

        add_breadcrumb(['link' => FORUM."index.php", "title" => $locale['forum_0000']]);

        $forum_sections = $this->getForumSection();
        if (isset($forum_sections[$this->forum_info['section']])) {
            $this->loadSection($forum_sections[$this->forum_info['section']]);
            return $this;
        }

        if ($this->is_viewforum) {

            $viewforum = new ViewForum($this);

            $this->forum_info = $viewforum->getForumViewInfo();

            return $this;

        }

        // Categories
        $this->forum_info['forums'] = self::get_forums();

        return $this;

    }

    private function checkViewForum() {

        $this->is_viewforum = isset($_GET['viewforum']) ? TRUE : FALSE;
        $forum_id = get('forum_id', FILTER_VALIDATE_INT);
        if ($this->is_viewforum && !$forum_id) {
            redirect(FORUM.'index.php');
        }
        return $this->is_viewforum;
    }

    private function legacyPanelRedirect() {
        if (stristr(server('PHP_SELF'), 'forum_id')) {
            switch ($this->getCurrentSection()) {
                case 'latest':
                    redirect(INFUSIONS.'forum/index.php?section=latest');
                    break;
                case 'mypost':
                    redirect(INFUSIONS.'forum/index.php?section=mypost');
                    break;
                case 'tracked':
                    redirect(INFUSIONS.'forum/index.php?section=tracked');
            }
        }
    }

    /**
     * Get section in forum
     *
     * @return mixed|string
     */
    public function getCurrentSection() {
        $section = get('section');
        $section = $section && in_array($section, ['participated', 'latest', 'tracked', 'sticky', 'unanswered', 'unsolved', 'people', 'reports']) ? $section : '';
        return $section;
    }

    private function loadXML() {
        if (file_exists(INFUSIONS.'rss_feeds_panel/feeds/rss_forums.php')) {
            add_to_head('<link rel="alternate" type="application/rss+xml" title="'.fusion_get_locale('forum_0000').' - RSS Feed" href="'.fusion_get_settings('siteurl').'infusions/rss_feeds_panel/feeds/rss_forums.php"/>');
        }
    }

    public function getForumSection() {
        $locale = fusion_get_locale();
        return [
            'participated' => [
                'title'       => $locale['global_024'],
                'description' => $locale['global_024'],
                'breadcrumbs' => [
                    'link'  => FORUM."index.php?section=participated",
                    'title' => $locale['global_024']
                ]
            ],
            'sticky'       => [
                'title'       => 'Sticky Threads',
                'description' => 'Sticky Threads',
                'breadcrumbs' => [
                    'link'  => FORUM."index.php?section=sticky",
                    'title' => 'Sticky Threads',
                ]
            ],
            'latest'       => [
                'title'       => $locale['global_021'],
                'description' => $locale['global_021'],
                'breadcrumbs' => [
                    'link'  => FORUM."index.php?section=latest",
                    'title' => $locale['global_021'],
                ]
            ],
            'tracked'      => [
                'title'       => $locale['global_056'],
                'description' => $locale['global_056'],
                'breadcrumbs' => [
                    'link'  => FORUM."index.php?section=tracked",
                    'title' => $locale['global_056'],
                ]
            ],
            'unanswered'   => [
                'title'       => $locale['global_027'],
                'description' => $locale['global_027'],
                'breadcrumbs' => [
                    'link'  => FORUM."index.php?section=unanswered",
                    'title' => $locale['global_027'],
                ]
            ],
            'unsolved'     => [
                'title'       => $locale['global_028'],
                'description' => $locale['global_028'],
                'breadcrumbs' => [
                    'link'  => FORUM."index.php?section=unsolved",
                    'title' => $locale['global_028'],
                ]
            ],
            'reports'    => [
                'title'       => 'Reports',
                'description' => 'Forum Reports',
                'breadcrumbs' => [
                    'link'  => FORUM."index.php?section=reports",
                    'title' => 'Reports'
                ]
            ],
            // WIP
            'people'       => [
                'title'       => $locale['global_028'],
                'description' => $locale['global_028'],
                'breadcrumbs' => [
                    'link'  => FORUM."index.php?section=people",
                    'title' => $locale['global_028'],
                ]
            ],
        ];

    }

    private function loadSection($value) {
        $locale = fusion_get_locale();
        add_to_title($locale['global_201'].$value['title']);
        add_breadcrumb($value['breadcrumbs']);
        set_meta("description", $value['description']);

        include FORUM_SECTIONS.$this->forum_info['section'].'.php';
    }

    /**
     * Get the forum structure
     *
     * @param bool $forum_id
     *
     * @return array
     */
    public static function get_forums($forum_id = FALSE) {

        $forum_settings = self::get_forum_settings();

        $locale = fusion_get_locale();

        $index = [];

        $row = [
            'forum_new_status'       => '',
            'last_post'              => '',
            'forum_icon'             => '',
            'forum_icon_lg'          => '',
            'forum_moderators'       => '',
            'forum_link'             => [
                'link'  => '',
                'title' => ''
            ],
            'forum_description'      => '',
            'forum_postcount_word'   => '',
            'forum_threadcount_word' => '',
        ];
        $branch_id = get_hkey(DB_FORUMS, 'forum_id', 'forum_cat', $forum_id);
        $query = dbquery("
            SELECT f.forum_id, f.forum_cat, f.forum_name, f.forum_description, f.forum_branch, f.forum_access, f.forum_lock, f.forum_type, f.forum_mods, f.forum_postcount, f.forum_threadcount, f.forum_image, f.forum_lastpost, f.forum_lastpostid, f.forum_language,
            t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_lastuser, t.thread_subject
            FROM ".DB_FORUMS." f
            LEFT JOIN ".DB_FORUM_THREADS." t ON f.forum_lastpostid = t.thread_lastpostid
            ".(multilang_table("FO") ? "WHERE ".in_group('f.forum_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('f.forum_access')."
            ".($forum_id && $branch_id ? "AND f.forum_id=:forum_id or f.forum_cat=:forum_id01 OR f.forum_branch=:branch_id" : '')."
            GROUP BY f.forum_id ORDER BY f.forum_cat ASC, f.forum_order ASC, t.thread_lastpost DESC
        ", [
            ':forum_id'   => intval($forum_id),
            ':forum_id01' => intval($forum_id),
            ':branch_id'  => intval($branch_id)
        ]);

        while ($data = dbarray($query) and checkgroup($data['forum_access'])) {
            $newStatus = '';

            $lastPostInfo = [
                'avatar'       => '',
                'avatar_sm'    => '',
                'avatar_src'   => '',
                'profile_link' => '',
                'time'         => '',
                'date'         => '',
                'thread_link'  => '',
                'post_link'    => ''
            ];

            if ($data['forum_type'] > 1 && $data['forum_lastpost']) {

                // avoid undefined index for deleted user
                $data += array(
                    "user_id"     => "",
                    "user_name"   => "",
                    "user_status" => "",
                    "user_avatar" => "",
                    "user_level"  => ""
                );

                if ($data["thread_lastuser"]) {
                    $user = fusion_get_user($data['thread_lastuser']);
                    if (!empty($user['user_id'])) {
                        $data['user_id'] = $user['user_id'];
                        $data['user_name'] = $user['user_name'];
                        $data['user_status'] = $user['user_status'];
                        $data['user_avatar'] = $user['user_avatar'];
                        $data['user_level'] = $user['user_level'];
                    }
                }

                $lastPostInfo = array(
                    'avatar'         => $forum_settings['forum_last_post_avatar'] ? display_avatar($data, '45px', '', '', 'img-circle') : '',
                    'avatar_sm'      => $forum_settings['forum_last_post_avatar'] ? display_avatar($data, '24px', '', '', 'img-circle') : '',
                    'avatar_src'     => $data['user_avatar'] && file_exists(IMAGES.'avatars/'.$data['user_avatar']) && !is_dir(IMAGES.'avatars/'.$data['user_avatar']) ? IMAGES.'avatars/'.$data['user_avatar'] : '',
                    'profile_link'   => profile_link($data['user_id'], $data['user_name'], $data['user_status']),
                    'time'           => timer($data['thread_lastpost']),
                    'date'           => showdate("forumdate", $data['thread_lastpost']),
                    'thread_link'    => INFUSIONS."forum/viewthread.php?thread_id=".$data['thread_id'],
                    'thread_subject' => $data['thread_subject'],
                    'post_link'      => INFUSIONS."forum/viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['thread_lastpostid']."#post_".$data['thread_lastpostid'],
                    'link_title'     => $data['forum_name'],
                );

                // Calculate Forum New Status
                if (check_thread_new_status($data['forum_id'], $data['thread_lastpost'], $data['thread_lastuser'])) {
                    $newStatus = "<span class='forum-new-icon'><i title='".$locale['forum_0260']."' class='".self::getForumIcons('new')."'></i></span>";
                }

            }

            // Icons
            switch ($data['forum_type']) {
                case '1':
                    $forum_icon = "<i class='".self::getForumIcons('forum')." fa-fw'></i>";
                    $forum_icon_lg = "<i class='".self::getForumIcons('forum')." fa-3x fa-fw'></i>";
                    break;
                case '2':
                    $forum_icon = "<i class='".self::getForumIcons('thread')." fa-fw'></i>";
                    $forum_icon_lg = "<i class='".self::getForumIcons('thread')." fa-3x fa-fw'></i>";
                    break;
                case '3':
                    $forum_icon = "<i class='".self::getForumIcons('link')." fa-fw'></i>";
                    $forum_icon_lg = "<i class='".self::getForumIcons('link')." fa-3x fa-fw'></i>";
                    break;
                case '4':
                    $forum_icon = "<i class='".self::getForumIcons('question')." fa-fw'></i>";
                    $forum_icon_lg = "<i class='".self::getForumIcons('question')." fa-3x fa-fw'></i>";
                    break;
                default:
                    $forum_icon = "";
                    $forum_icon_lg = "";
            }

            $row = array_merge($row, $data, [
                "forum_moderators"       => ForumModerator::displayForumMods($data['forum_mods']),
                "forum_new_status"       => $newStatus,
                "forum_link"             => [
                    "link"  => INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$data['forum_id'],
                    "title" => $data['forum_name']
                ],
                "forum_description"      => nl2br(parseubb($data['forum_description'])),
                "forum_postcount_word"   => format_word($data['forum_postcount'], $locale['fmt_post']),
                "forum_threadcount_word" => format_word($data['forum_threadcount'], $locale['fmt_thread']),
                "last_post"              => $lastPostInfo,
                "forum_icon"             => $forum_icon,
                "forum_icon_lg"          => $forum_icon_lg,
            ]);

            $row["forum_image"] = ($row['forum_image'] && file_exists(FORUM."images/".$row['forum_image'])) ? $row['forum_image'] : '';

            $thisref = &$refs[$data['forum_id']];
            $thisref = $row;
            if ($data['forum_cat'] == 0) {
                $index[0][$data['forum_id']] = &$thisref;
            } else {
                $refs[$data['forum_cat']]['child'][$data['forum_id']] = &$thisref;
            }
        }

        return (array)$index;
    }


    /**
     * @param $forum_data
     *
     * @return array
     */
    public function setForumPermission($forum_data) {
        // Access the forum
        $this->forum_info['permissions']['can_access'] = (iMOD || checkgroup($forum_data['forum_access']) ? TRUE : FALSE);
        // Create new thread -- whether user has permission to create a thread
        $this->forum_info['permissions']['can_post'] = (iMOD || (checkgroup($forum_data['forum_post']) && $forum_data['forum_lock'] == FALSE) ? TRUE : FALSE);
        // Poll creation -- thread has not exist, therefore cannot be locked.
        $this->forum_info['permissions']['can_create_poll'] = ($forum_data['forum_allow_poll'] == TRUE && (iMOD || (checkgroup($forum_data['forum_poll']) && $forum_data['forum_lock'] == FALSE)) ? TRUE : FALSE);
        $this->forum_info['permissions']['can_upload_attach'] = ($forum_data['forum_allow_attach'] == TRUE && (iMOD || checkgroup($forum_data['forum_attach'])) ? TRUE : FALSE);
        $this->forum_info['permissions']['can_download_attach'] = (iMOD || ($forum_data['forum_allow_attach'] == TRUE && checkgroup($forum_data['forum_attach_download'])) ? TRUE : FALSE);


        return $this->forum_info;
    }

    /**
     * Get the relevant permissions of the current forum permission configuration
     *
     * @param null $key
     *
     * @return null
     */
    public function getForumPermission($key = NULL) {
        if (!empty($this->forum_info['permissions'])) {
            if (isset($this->forum_info['permissions'][$key])) {
                return $this->forum_info['permissions'][$key];
            }

            return $this->forum_info['permissions'];
        }

        return NULL;
    }

}
