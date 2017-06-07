<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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

use PHPFusion\BreadCrumbs;
use PHPFusion\Forums\Post\NewThread;
use PHPFusion\Forums\Postify\Forum_Postify;
use PHPFusion\Forums\Threads\Forum_Mood;
use PHPFusion\Forums\Threads\ForumMood;
use PHPFusion\Forums\Threads\ThreadFilter;

abstract class ForumServer {

    /**
     * Moderator object
     * @return object
     */
    public static $moderator_instance = NULL;

    /* Forum icons */
    /**
     * Thread filter object
     * @return object
     */
    public static $filter_instance = NULL;
    /**
     * Forum object
     * @return object
     */
    public static $forum_instance = NULL;
    /**
     * Tag object
     * @return object
     */
    public static $tag_instance = NULL;
    /**
     * Thread object
     * @return object
     */
    public static $thread_instance = NULL;
    /**
     * Post object
     * @var null
     */
    public static $new_thread_instance = NULL;
    /**
     * Mood object
     * @return object
     */
    public static $forum_mood_instance = NULL;

    public static $postify_instance = NULL;

    protected static $forum_settings = array();

    static private $forum_icons = array(
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
    );
    /**
     * Get records of cached forum ranks
     * @staticvar array $forum_rank_cache
     * @return array Cached forum ranks
     */
    private static $forum_rank_cache = NULL;

    /**
     * @param string $type
     * @return array
     */
    public static function get_ForumIcons($type = '') {
        if (isset(self::$forum_icons[$type])) {
            return self::$forum_icons[$type];
        }

        return self::$forum_icons;
    }

    /**
     * Set and Modify Forum Icons
     * @param array $icons
     * @return array
     */
    public static function set_forumIcons(array $icons = array()) {
        self::$forum_icons = array(
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
        );
    }

    /**
     * Verify Forum ID
     * @param $forum_id
     * @return bool|string
     */
    public static function verify_forum($forum_id) {
        if (isnum($forum_id)) {
            return (bool)dbcount("('forum_id')", DB_FORUMS, "forum_id='".$forum_id."' AND ".groupaccess('forum_access')." ") ? TRUE : FALSE;
        }

        return FALSE;
    }

    private $forum_access = FALSE;

    /**
     * Check all forum access
     *
     * @param     $forum_index
     * @param int $forum_id
     * @param int $thread_id
     * @param int $user_id - if provided with user_id, to check against that user
     *
     * Breaks the check and returns true for Super Administrator
     * You need to define either forum id or thread id when accessing this function
     * This function is non-dependent on GET against tampering (bot access)
     *
     * @return bool
     * @throws \Exception
     */
    protected function check_forum_access($forum_index, $forum_id = 0, $thread_id = 0, $user_id = 0) {
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
                                $this->forum_access = checkusergroup($data['forum_access'], $user['user_level'], $user['user_groups']) ? TRUE : FALSE;
                            } else {
                                $this->forum_access = checkgroup($data['forum_access']) ? TRUE : FALSE;
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
     * @param int $posts The number of posts of the member
     * @param int $level The level of the member
     * @param array $groups The groups of the member
     * @return string HTML source of forum rank images
     */
    public static function show_forum_rank($posts, $level, $groups) {

        $forum_settings = self::get_forum_settings();

        $ranks = array();

        if (!$forum_settings['forum_ranks']) {
            return '';
        }

        $image = ($forum_settings['forum_rank_style'] == 1);

        $forum_rank_cache = self::forum_rank_cache();

        $forum_rank_css_class = array(
            '-101' => 'label-member',
            '-102' => 'label-mod',
            '-103' => 'label-super-admin',
        );

        $forum_rank_icon_class = array(
            '-101' => 'fa fa-legal fa-fw',
            '-102' => 'fa fa-legal fa-fw',
            '-103' => 'fa fa-legal fa-fw',
            '-104' => 'fa fa-legal fa-fw',
        );

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
                    $res .= "<div class='rank_title'>".$rank['rank_title']."</div>\n<img src='".RANKS.$rank['rank_image']."' alt='' style='border:0' />";
                }
            } else {
                if (isset($rank['rank_apply']) && isset($rank['rank_title'])) {
                    $res .= "<label class='forum label ".(isset($forum_rank_css_class[$rank['rank_apply']]) ? $forum_rank_css_class[$rank['rank_apply']] : "label-default")." '><i class='".(isset($forum_rank_icon_class[$rank['rank_apply']]) ? $forum_rank_icon_class[$rank['rank_apply']] : "fa fa-user fa-fw")."'></i><div class='detail'>".$rank['rank_title']."</div>\n</label>\n";
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
    public static function get_forum_settings($key = NULL) {
        if (empty(self::$forum_settings)) {
            self::$forum_settings = get_settings('forum');
        }

        return $key === NULL ? self::$forum_settings : (isset(self::$forum_settings[$key]) ? self::$forum_settings[$key] : NULL);
    }

    /**
     * Cache Forum Ranks
     * @return array
     */
    public static function forum_rank_cache() {

        $forum_settings = self::get_forum_settings();

        $known_types = array(
            0 => 'post',
            1 => 'mod'
        );

        if (self::$forum_rank_cache === NULL and $forum_settings['forum_ranks']) {

            self::$forum_rank_cache = array(
                'post'    => array(),
                'mod'     => array(),
                'special' => array(),
            );

            $cache_query = "
            SELECT rank_title, rank_image, rank_type, rank_posts, rank_apply, rank_language
            FROM ".DB_FORUM_RANKS." ".(multilang_table("FR") ? "WHERE rank_language='".LANGUAGE."'" : "")."
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
     * @param $thread_id
     * @return bool|string
     */
    public static function verify_thread($thread_id) {
        if (isnum($thread_id)) {
            return (bool)dbcount("('forum_id')", DB_FORUM_THREADS, "thread_id='".$thread_id."'") ? TRUE : FALSE;
        }

        return FALSE;
    }

    /**
     * Verify Post ID
     * @param $post_id
     * @return bool|string
     */
    public static function verify_post($post_id) {
        if (isnum($post_id)) {
            return (bool)dbcount("('post_id')", DB_FORUM_POSTS, "post_id='".$post_id."'") ? TRUE : FALSE;
        }

        return FALSE;
    }

    /**
     * Get Recent Topics per forum.
     * @param int $forum_id - all if 0.
     * @return mixed
     */
    public static function get_recentTopics($forum_id = 0) {
        $forum_settings = self::get_forum_settings();
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
			".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")."
			".groupaccess('tf.forum_access')." AND tt.thread_hidden='0'
			".($forum_id ? "AND forum_id='".intval($forum_id)."'" : '')."
			GROUP BY thread_id ORDER BY tt.thread_lastpost LIMIT 0, ".$forum_settings['threads_per_page']."");
        $info['rows'] = dbrows($result);
        if ($info['rows'] > 0) {
            // need to throw moderator as an object
            while ($data = dbarray($result)) {
                $data['moderators'] = Moderator::parse_forum_mods($data['forum_mods']);
                $info['item'][$data['thread_id']] = $data;
            }
        }

        return $info;
    }

    /**
     * Moderator Instance
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
     * @param bool $set_info
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
     * @param bool $set_info
     * @return null|Forum
     */
    public static function forum($set_info = TRUE) {
        if (self::$forum_instance === NULL) {
            self::$forum_instance = new Forum();
            if ($set_info == TRUE) {
                self::$forum_instance->set_ForumInfo();
            }
        }

        return self::$forum_instance;
    }

    /**
     * Tag Instance
     * @param bool $set_info
     * @param bool $set_title
     * @return null|ThreadTags
     */
    public static function tag($set_info = TRUE, $set_title = FALSE) {
        if (self::$tag_instance === NULL) {
            self::$tag_instance = new ThreadTags();
            if ($set_info == TRUE) {
                require_once INCLUDES."mimetypes_include.php";
                self::$tag_instance->set_TagInfo($set_title);
            }
        }

        return self::$tag_instance;
    }

    /**
     * Thread Instance
     * @param bool $set_info
     * @return null|Threads\ForumThreads
     */
    public static function thread($set_info = TRUE) {
        if (self::$thread_instance === NULL) {
            self::$thread_instance = new Threads\ForumThreads();
            if ($set_info == TRUE) {
                require_once INCLUDES."mimetypes_include.php";
                self::$thread_instance->set_threadInfo();
            }
        }

        return self::$thread_instance;
    }

    /**
     * New Thread Instance
     * @param bool $set_info
     * @return null|NewThread
     */
    public static function new_thread($set_info = TRUE) {
        if (self::$new_thread_instance === NULL) {
            self::$new_thread_instance = new NewThread();
            if ($set_info == TRUE) {
                self::$new_thread_instance->set_newThreadInfo();
            }
        }

        return self::$new_thread_instance;
    }

    /**
     * Mood Instance
     * @return null|ForumMood
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
     * @param array $forum_index - requires a dbquery_tree() output
     * @param int $forum_id
     */
    function forum_breadcrumbs(array $forum_index, $forum_id = 0) {

        $locale = fusion_get_locale('', FORUM_LOCALE);

        if (empty($forum_id)) {
            $forum_id = isset($_GET['forum_id']) && isnum($_GET['forum_id']) ? $_GET['forum_id'] : 0;
        }
        /* Make an infinity traverse */
        function forum_breadcrumb_arrays($index, $id, &$crumb = FALSE) {
            if (isset($index[get_parent($index, $id)])) {
                $_name = dbarray(dbquery("SELECT forum_id, forum_name, forum_cat, forum_branch FROM ".DB_FORUMS." WHERE forum_id='".$id."'"));
                $crumb = array(
                    'link'  => INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$_name['forum_id'],
                    'title' => $_name['forum_name']
                );
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

        // then we make a infinity recursive function to loop/break it out.
        $crumb = forum_breadcrumb_arrays($forum_index, $forum_id);
        // then we sort in reverse.
        if (count($crumb['title']) > 1) {
            krsort($crumb['title']);
            krsort($crumb['link']);
        }
        if (count($crumb['title']) > 1) {
            foreach ($crumb['title'] as $i => $value) {
                BreadCrumbs::getInstance()->addBreadCrumb(['link' => $crumb['link'][$i], 'title' => $value]);
                if ($i == count($crumb['title']) - 1) {
                    add_to_title($locale['global_201'].$value);
                }
            }
        } elseif (isset($crumb['title'])) {
            add_to_title($locale['global_201'].$crumb['title']);
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => $crumb['link'], 'title' => $crumb['title']]);
        }
    }
}