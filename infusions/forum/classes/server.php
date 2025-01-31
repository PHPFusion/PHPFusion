<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: server.php
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

namespace PHPFusion\Forums;

use PHPFusion\Forums\Post\NewThread;
use PHPFusion\Forums\Postify\Forum_Postify;
use PHPFusion\Forums\Threads\Forum_Mood;
use PHPFusion\Forums\Threads\ThreadFilter;

abstract class ForumServer {

    /**
     * Moderator object
     *
     * @return object
     */
    public static $moderator_instance = NULL;

    /* Forum icons */
    /**
     * Thread filter object
     *
     * @return object
     */
    public static $filter_instance = NULL;
    /**
     * Forum object
     *
     * @return object
     */
    public static $forum_instance = NULL;
    /**
     * Tag object
     *
     * @return object
     */
    public static $tag_instance = NULL;
    /**
     * Thread object
     *
     * @return object
     */
    public static $thread_instance = NULL;
    /**
     * Post object
     *
     * @var null
     */
    public static $new_thread_instance = NULL;
    /**
     * Mood object
     *
     * @return object
     */
    public static $forum_mood_instance = NULL;

    public static $postify_instance = NULL;

    protected static $forum_settings = [];

    protected static $forum_template_paths = [];

    static private $forum_icons = [
        'forum'    => 'fa fa-folder fa-fw',
        'thread'   => 'fa fa-comments-o fa-fw',
        'link'     => 'fa fa-link fa-fw',
        'question' => 'fa fa-mortar-board fa-fw',
        'new'      => 'fa fa-lightbulb-o fa-fw',
        'poll'     => 'fa fa-pie-chart fa-fw',
        'lock'     => 'fa fa-lock fa-fw',
        'image'    => 'fa fa-file-picture-o fa-fw',
        'file'     => 'fa fa-file-zip-o fa-fw',
        'tracked'  => 'fa fa-bell-o fa-fw',
        'hot'      => 'fa fa-heartbeat fa-fw',
        'sticky'   => 'fa fa-thumb-tack fa-fw',
        'reads'    => 'fa fa-ticket fa-fw',
    ];
    /**
     * Get records of cached forum ranks
     *
     * @staticvar array $forum_rank_cache
     * @return array Cached forum ranks
     */
    private static $forum_rank_cache = NULL;

    private $forum_access = FALSE;

    /**
     * @param string $type
     */
    public static function getForumIcons($type = '') {
        if (isset(self::$forum_icons[$type])) {
            return self::$forum_icons[$type];
        }

        return self::$forum_icons;
    }

    /**
     * Set and Modify Forum Icons
     *
     * @param array $icons
     */
    public static function setForumIcons(array $icons = []) {
        self::$forum_icons = [
            'forum'    => !empty($icons['main']) ? $icons['main'] : 'fa fa-folder fa-fw',
            'thread'   => !empty($icons['thread']) ? $icons['thread'] : 'fa fa-chat-o fa-fw',
            'link'     => !empty($icons['link']) ? $icons['link'] : 'fa fa-link fa-fw',
            'question' => !empty($icons['question']) ? $icons['question'] : 'fa fa-mortar-board fa-fw',
            'new'      => !empty($icons['new']) ? $icons['new'] : 'fa fa-lightbulb-o fa-fw',
            'poll'     => !empty($icons['poll']) ? $icons['poll'] : 'fa fa-pie-chart fa-fw',
            'lock'     => !empty($icons['lock']) ? $icons['lock'] : 'fa fa-lock fa-fw',
            'image'    => !empty($icons['image']) ? $icons['image'] : 'fa fa-file-picture-o fa-fw',
            'file'     => !empty($icons['file']) ? $icons['file'] : 'fa fa-file-zip-o fa-fw',
            'tracked'  => !empty($icons['tracked']) ? $icons['tracked'] : 'fa fa-bell-o fa-fw',
            'hot'      => !empty($icons['hot']) ? $icons['hot'] : 'fa fa-heartbeat fa-fw',
            'sticky'   => !empty($icons['sticky']) ? $icons['sticky'] : 'fa fa-thumb-tack fa-fw',
            'reads'    => !empty($icons['reads']) ? $icons['reads'] : 'fa fa-ticket fa-fw',
        ];
    }

    /**
     * Verify Forum ID
     */
    public static function verifyForum($forum_id) {
        if (isnum($forum_id)) {
            return dbcount("('forum_id')", DB_FORUMS, "forum_id='".$forum_id."' AND ".groupaccess('forum_access')." ");
        }

        return FALSE;
    }

    /**
     * Check all forum access
     *
     * @param mixed $forum_index
     * @param int   $forum_id
     * @param int   $thread_id
     * @param int   $user_id if provided with user_id, to check against that user
     *
     * Breaks the check and returns true for Super Administrator
     * You need to define either forum id or thread id when accessing this function
     * is non-dependent on GET against tampering (bot access)
     *
     * @return bool
     * @throws \Exception
     */
    protected function checkForumAccess($forum_index, $forum_id = 0, $thread_id = 0, $user_id = 0) {
        if (iSUPERADMIN) {
            $this->forum_access = TRUE;

            return $this->forum_access;
        }
        if (!$forum_id or isnum($forum_id)) {
            if ($thread_id && isnum($thread_id)) {
                $forum_id = dbresult(dbquery("SELECT forum_id FROM ".DB_FORUM_THREADS." WHERE thread_id=:thread_id", [':thread_id' => $thread_id]), 0);
                $list[] = $forum_id;
                if ($ancestor = get_all_parent($forum_index, $forum_id)) {
                    $list = array_merge_recursive($list, $ancestor);
                }

                if (!empty($list)) {
                    $list_sql = implode(',', $list);
                    $query = "SELECT forum_access FROM ".DB_FORUMS." WHERE forum_id IN ($list_sql) ORDER BY forum_cat ASC";
                    $result = dbquery($query);
                    if (dbrows($result)) {
                        while ($data = dbarray($result)) {
                            if ($user_id) {
                                $user = fusion_get_user($user_id);
                                $this->forum_access = checkusergroup($data['forum_access'], $user['user_level'], $user['user_groups']);
                            } else {
                                $this->forum_access = checkgroup($data['forum_access']);
                            }
                            if ($this->forum_access === FALSE) {
                                break;
                            }
                        }
                    }
                }
            } else {
                throw new \Exception(fusion_get_locale('forum_4120'));
            }
        }

        return (bool)$this->forum_access;
    }

    /**
     * Get HTML source of forum rank images of a member
     *
     * @param int    $posts  The number of posts of the member
     * @param int    $level  The level of the member
     * @param string $groups The groups of the member
     *
     * @return string HTML source of forum rank images
     */
    public static function showForumRank($posts, $level, $groups) {

        $forum_settings = self::getForumSettings();

        $ranks = [];

        if (!$forum_settings['forum_ranks']) {
            return '';
        }

        $image = ($forum_settings['forum_rank_style'] == 1);

        $forum_rank_cache = self::forumRankCache();

        $forum_rank_css_class = [
            USER_LEVEL_MEMBER      => 'label-member',
            USER_LEVEL_ADMIN       => 'label-mod',
            USER_LEVEL_SUPER_ADMIN => 'label-super-admin',
        ];

        $forum_rank_icon_class = [
            USER_LEVEL_MEMBER      => 'fa fa-legal fa-fw',
            USER_LEVEL_ADMIN       => 'fa fa-legal fa-fw',
            USER_LEVEL_SUPER_ADMIN => 'fa fa-legal fa-fw',
            '-104'                 => 'fa fa-legal fa-fw',
        ];

        // Moderator ranks
        if (!empty($forum_rank_cache['mod'])) {
            if ($level < USER_LEVEL_MEMBER) {
                foreach ($forum_rank_cache['mod'] as $rank) {
                    if (isset($rank['rank_apply']) && $level == $rank['rank_apply']) {
                        $ranks[] = $rank;
                        break;
                    }
                }
            }
        }

        // Special ranks
        if (!empty($forum_rank_cache['special'])) {
            if (!empty($groups)) {
                if (!is_array($groups)) {
                    $groups = explode(".", $groups);
                }

                foreach ($forum_rank_cache['special'] as $rank) {
                    if (!isset($rank['rank_apply'])) {
                        continue;
                    }

                    if (in_array($rank['rank_apply'], $groups)) {
                        $ranks[] = $rank;
                    }
                }
            }
        }

        // Post count ranks
        if (!empty($forum_rank_cache['post'])) {
            if (!$ranks) {
                foreach ($forum_rank_cache['post'] as $rank) {
                    if (!isset($rank['rank_apply'])) {
                        continue;
                    }

                    if ($posts >= $rank['rank_posts']) {
                        $ranks['post_rank'] = $rank;
                    }
                }

                if (!$ranks && isset($forum_rank_cache['post'][0])) {
                    $ranks['post_rank'] = $forum_rank_cache['post'][0];
                }
            }
        }

        // forum ranks must be the highest
        $res = '';
        foreach ($ranks as $rank) {
            if ($image) {
                if (isset($rank['rank_title']) && isset($rank['rank_image'])) {
                    $res .= "<span class='rank".$rank['rank_id']." rank-image'><span class='rank-title'>".$rank['rank_title']."</span> <img src='".RANKS.$rank['rank_image']."' alt='".$rank['rank_title']."'></span>";
                }
            } else {
                if (isset($rank['rank_apply']) && isset($rank['rank_title'])) {
                    $font_color = get_color_brightness($rank['rank_color']) > 130 ? '#000' : '#fff';
                    $bg = !empty($rank['rank_color']) ? " style='background:".$rank['rank_color'].";color:".$font_color."'" : '';
                    $res .= "<span class='rank".$rank['rank_id']." rank-label label ".(isset($forum_rank_css_class[$rank['rank_apply']]) ? $forum_rank_css_class[$rank['rank_apply']] : "label-default")."'".$bg.">";
                    $icon = !empty($rank['rank_icon']) ? $rank['rank_icon'] : '';
                    $icon = !empty($icon) ? $icon : (isset($forum_rank_icon_class[$rank['rank_apply']]) ? $forum_rank_icon_class[$rank['rank_apply']] : "fa fa-user fa-fw");
                    $res .= "<i class='".$icon."'></i>";
                    $res .= "<span class='detail'>".$rank['rank_title']."</span>";
                    $res .= "</span>";
                }
            }
        }

        return $res;
    }

    /**
     * Fetch Forum Settings
     *
     * @param null $key
     *
     * @return array|bool|mixed|null
     */
    public static function getForumSettings($key = NULL) {
        if (empty(self::$forum_settings)) {
            self::$forum_settings = get_settings('forum');
        }

        return $key === NULL ? self::$forum_settings : (isset(self::$forum_settings[$key]) ? self::$forum_settings[$key] : NULL);
    }

    /**
     * Cache Forum Ranks
     *
     * @return array
     */
    public static function forumRankCache() {

        $forum_settings = self::getForumSettings();

        $known_types = [
            0 => 'post',
            1 => 'mod'
        ];

        if (self::$forum_rank_cache === NULL and $forum_settings['forum_ranks']) {

            self::$forum_rank_cache = [
                'post'    => [],
                'mod'     => [],
                'special' => [],
            ];

            $cache_query = "
            SELECT *
            FROM ".DB_FORUM_RANKS." ".(multilang_table("FR") ? "WHERE ".in_group('rank_language', LANGUAGE) : "")."
            ORDER BY rank_apply DESC, rank_posts ASC
            ";

            $result = dbquery($cache_query);

            while ($data = dbarray($result)) {
                $type = isset($known_types[$data['rank_type']]) ? $known_types[$data['rank_type']] : 'special';
                self::$forum_rank_cache[$type][] = $data;
            }
        }

        return (array)self::$forum_rank_cache;
    }

    /**
     * Verify Thread ID
     *
     * @param $thread_id
     *
     * @return bool|int
     */
    public static function verifyThread($thread_id) {
        if (isnum($thread_id)) {
            return dbcount("('forum_id')", DB_FORUM_THREADS, "thread_id='".$thread_id."'");
        }

        return FALSE;
    }

    /**
     * Verify Post ID
     *
     * @param $post_id
     *
     * @return bool|int
     */
    public static function verifyPost($post_id) {
        if (isnum($post_id)) {
            return dbcount("('post_id')", DB_FORUM_POSTS, "post_id='".$post_id."'");
        }

        return FALSE;
    }

    /**
     * Get Recent Topics per forum.
     *
     * @param int $forum_id - all if 0.
     *
     * @return array
     */
    public static function getRecentTopics($forum_id = 0) {
        $forum_settings = self::getForumSettings();
        $result = dbquery("SELECT tt.*, tf.*, tp.post_id, tp.post_datestamp,
            u.user_id, u.user_name as last_user_name, u.user_status as last_user_status, u.user_avatar as last_user_avatar,
            uc.user_id AS s_user_id, uc.user_name AS author_name, uc.user_status AS author_status, uc.user_avatar AS author_avatar,
            count(v.post_id) AS vote_count
            FROM ".DB_FORUM_THREADS." tt
            INNER JOIN ".DB_FORUMS." tf ON (tt.forum_id=tf.forum_id)
            LEFT JOIN ".DB_FORUM_POSTS." tp on (tt.thread_lastpostid = tp.post_id)
            LEFT JOIN ".DB_USERS." u ON u.user_id=tt.thread_lastuser
            LEFT JOIN ".DB_USERS." uc ON uc.user_id=tt.thread_author
            LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = tt.thread_id AND tp.post_id = v.post_id
            ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND" : "WHERE")."
            ".groupaccess('tf.forum_access')." AND tt.thread_hidden='0'
            ".($forum_id ? "AND forum_id='".intval($forum_id)."'" : '')."
            GROUP BY thread_id ORDER BY tt.thread_lastpost LIMIT 0, ".$forum_settings['threads_per_page']."");
        $info['rows'] = dbrows($result);
        if ($info['rows'] > 0) {
            // need to throw moderator as an object
            while ($data = dbarray($result)) {
                $data['moderators'] = Moderator::parseForumMods($data['forum_mods']);
                $info['item'][$data['thread_id']] = $data;
            }
        }

        return $info;
    }

    /**
     * Moderator Instance
     *
     * @return null|Moderator
     */
    protected function moderator() {
        if (self::$moderator_instance === NULL) {
            self::$moderator_instance = new Moderator();
        }

        return self::$moderator_instance;
    }

    /**
     * Thread Filter Instance
     *
     * @param bool $set_info
     *
     * @return null|ThreadFilter
     */
    public static function filter($set_info = TRUE) {
        if (self::$filter_instance === NULL) {
            self::$filter_instance = new ThreadFilter();
            if ($set_info == TRUE) {
                self::$filter_instance->set_FilterInfo();
            }
        }

        return self::$filter_instance;
    }

    /**
     * Forum Instance
     *
     * @param bool $set_info
     *
     * @return null|Forum
     */
    public static function forum($set_info = TRUE) {
        if (self::$forum_instance === NULL) {
            self::$forum_instance = new Forum();
            if ($set_info == TRUE) {
                self::$forum_instance->setForumInfo();
            }
        }

        return self::$forum_instance;
    }

    /**
     * Tag Instance
     *
     * @param bool $set_info
     * @param bool $set_title
     *
     * @return null|ThreadTags
     */
    public static function tag($set_info = TRUE, $set_title = FALSE) {
        if (self::$tag_instance === NULL) {
            self::$tag_instance = new ThreadTags();
            if ($set_info == TRUE) {
                require_once INCLUDES."mimetypes_include.php";
                self::$tag_instance->setTagInfo($set_title);
            }
        }

        return self::$tag_instance;
    }

    /**
     * Thread Instance
     *
     * @param bool $set_info
     *
     * @return null|Threads\ForumThreads
     */
    public static function thread($set_info = TRUE) {
        if (self::$thread_instance === NULL) {
            self::$thread_instance = new Threads\ForumThreads();
            if ($set_info == TRUE) {
                require_once INCLUDES."mimetypes_include.php";
                self::$thread_instance->setThreadInfo();
            }
        }

        return self::$thread_instance;
    }

    /**
     * New Thread Instance
     *
     * @param bool $set_info
     *
     * @return null|NewThread
     */
    public static function newThread($set_info = TRUE) {
        if (self::$new_thread_instance === NULL) {
            self::$new_thread_instance = new NewThread();
            if ($set_info == TRUE) {
                self::$new_thread_instance->setNewThreadInfo();
            }
        }

        return self::$new_thread_instance;
    }

    /**
     * Mood Instance
     *
     * @return null|\PHPFusion\Forums\Threads\Forum_Mood
     */
    public static function mood() {
        if (self::$forum_mood_instance === NULL) {
            self::$forum_mood_instance = new Forum_Mood();
        }

        return self::$forum_mood_instance;
    }

    public static function postify() {
        if (self::$postify_instance === NULL) {
            self::$postify_instance = new Forum_Postify();
        }

        return self::$postify_instance;
    }

    /**
     * Forum Breadcrumbs Generator
     *
     * @param array $forum_index - requires a dbquery_tree() output
     * @param int   $forum_id
     */
    function forumBreadcrumbs(array $forum_index, $forum_id = 0) {
        $locale = fusion_get_locale('', FORUM_LOCALE);

        if (empty($forum_id)) {
            $forum_id = isset($_GET['forum_id']) && isnum($_GET['forum_id']) ? $_GET['forum_id'] : 0;
        }
        /* Make an infinity traverse */
        function forum_breadcrumb_arrays(array $index, $id) {
            $crumb = [];

            if (isset($index[get_parent($index, $id)])) {
                $_name = dbarray(dbquery("SELECT forum_id, forum_name, forum_cat, forum_branch FROM ".DB_FORUMS." WHERE forum_id='".$id."'"));
                $crumb = [
                    'link'  => INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$_name['forum_id'],
                    'title' => $_name['forum_name']
                ];
                if (isset($index[get_parent($index, $id)])) {
                    if (get_parent($index, $id) == 0) {
                        return $crumb;
                    }
                    $crumb_1 = forum_breadcrumb_arrays($index, get_parent($index, $id));
                    if (is_array($crumb_1)) {
                        $crumb = array_merge_recursive($crumb, $crumb_1); // convert so can comply to Fusion Tab API.
                    }
                }
            }

            return $crumb;
        }

        // then we make an infinity recursive function to loop/break it out.
        $crumb = forum_breadcrumb_arrays($forum_index, $forum_id);
        $title_count = !empty($crumb['title']) && is_array($crumb['title']) ? count($crumb['title']) > 1 : 0;
        // then we sort in reverse.
        if ($title_count) {
            krsort($crumb['title']);
            krsort($crumb['link']);
        }
        if ($title_count) {
            foreach ($crumb['title'] as $i => $value) {
                add_breadcrumb(['link' => $crumb['link'][$i], 'title' => $value]);
                if ($i == count($crumb['title']) - 1) {
                    add_to_title($locale['global_201'].$value);
                }
            }
        } else if (isset($crumb['title'])) {
            add_to_title($locale['global_201'].$crumb['title']);
            add_breadcrumb(['link' => $crumb['link'], 'title' => $crumb['title']]);
        }
    }

    static function getEditTimelimit($seconds = TRUE) {
        $seconds = $seconds == TRUE ? 60 : 1;

        switch (self::$forum_settings['forum_edit_timelimit']) {
            case 0: // no limit
                $limit = 0;
                break;
            case 1: // 10 minutes
                $limit = 10 * $seconds;
                break;
            case 2: // 30 minutes
                $limit = 30 * $seconds;
                break;
            case 3: // 45 minutes
                $limit = 45 * $seconds;
                break;
            case 4: // 60 minutes
                $limit = 60 * $seconds;
                break;
            default:
                $limit = 0;
        }

        return $limit;
    }
}
