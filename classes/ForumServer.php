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

namespace PHPFusion\Infusions\Forum\Classes;

use Exception;
use PHPFusion\Infusions\Forum\Classes\Forum\Forum;
use PHPFusion\Infusions\Forum\Classes\Forum\ForumTag;
use PHPFusion\Infusions\Forum\Classes\Post\NewThread;
use PHPFusion\Infusions\Forum\Classes\Threads\ForumMood;
use PHPFusion\Infusions\Forum\Classes\Threads\ForumThreadFilter;
use PHPFusion\Infusions\Forum\Classes\Threads\ForumThreads;

//use PHPFusion\Infusions\Forum\Classes\Post\NewThread;

/**
 * Class Forum_Server
 *
 * @package PHPFusion\Infusions\Forum\Classes
 */
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
        'reads'    => 'fa fa-fire fa-fw',
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
     *
     */
    public static function getForumIcons($type = '') {
        if (isset(self::$forum_icons[$type])) {
            return self::$forum_icons[$type];
        }
        redirect(FUSION_SELF);
        return self::$forum_icons;
    }

    /**
     * Set and Modify Forum Icons
     *
     * @param array $icons
     */
    public static function setForumIcons(array $icons = []) {
        self::$forum_icons = $icons + self::$forum_icons;
    }

    /**
     * Verify Forum ID
     *
     * @param $forum_id
     *
     * @return bool|string
     */
    public static function verify_forum($forum_id) {
        if (isnum($forum_id)) {
            return (bool)dbcount("('forum_id')", DB_FORUMS, "forum_id='".$forum_id."' AND ".groupaccess('forum_access')." ") ? TRUE : FALSE;
        }

        return FALSE;
    }

    /**
     * Get HTML source of forum rank images of a member
     *
     * @param int   $posts  The number of posts of the member
     * @param int   $level  The level of the member
     * @param string $groups The groups of the member ".{ID}.{ID}.{ID}"
     *
     * @return string HTML source of forum rank images
     */
    public static function get_forum_rank($posts, $level, $groups) {

        $ranks = [];

        $forum_rank_cache = self::forumRankCache();

        $forum_rank_css_class = [
            USER_LEVEL_MEMBER      => 'label-member',
            USER_LEVEL_ADMIN       => 'label-admin',
            USER_LEVEL_SUPER_ADMIN => 'label-super-admin',
            '-104'                 => 'label-mod'
        ];

        $forum_rank_icon_class = [
            USER_LEVEL_MEMBER      => 'fas fa-user fa-fw',
            USER_LEVEL_ADMIN       => 'fas fa-user-user-secret fa-fw',
            USER_LEVEL_SUPER_ADMIN => 'fas fa-user-astronaut fa-fw',
            '-104'                 => 'fas fa-user-secret fa-fw',
        ];

        // Moderator ranks
        if (!empty($forum_rank_cache['mod'])) {
            if ($level < USER_LEVEL_MEMBER) {
                foreach ( $forum_rank_cache['mod'] as $rank) {
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
                    $groups = explode(".", (string)$groups);
                }

                foreach ( $forum_rank_cache['special'] as $rank) {
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
                foreach ( $forum_rank_cache['post'] as $rank) {
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

        if (!empty($ranks['post_rank'])) {
            $ranks['post_rank']['rank_image_src'] = RANKS.$ranks['post_rank']['rank_image'];
            $ranks['post_rank']['rank_class'] = (isset($forum_rank_css_class[$ranks['post_rank']['rank_apply']]) ? $forum_rank_css_class[$ranks['post_rank']['rank_apply']] : "label-default");
            $ranks['post_rank']['rank_icon'] = (isset($forum_rank_icon_class[$ranks['post_rank']['rank_apply']]) ? $forum_rank_icon_class[$ranks['post_rank']['rank_apply']] : "fa fa-user");
        } else if (!empty($ranks[0])) {
            $ranks['post_rank']['rank_image_src'] = RANKS.$ranks[0]['rank_image'];
            $ranks['post_rank']['rank_class'] = (isset($forum_rank_css_class[$ranks[0]['rank_apply']]) ? $forum_rank_css_class[$ranks[0]['rank_apply']] : "label-default");
            $ranks['post_rank']['rank_icon'] = (isset($forum_rank_icon_class[$ranks[0]['rank_apply']]) ? $forum_rank_icon_class[$ranks[0]['rank_apply']] : "fa fa-user");
        }

        // end of cached.
        $ranks['post_rank']['rank_user_level'] = getuserlevel($level);

        return $ranks['post_rank'];
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
     *
     * @return array
     */
    public static function forumRankCache() {

        $forum_settings = self::get_forum_settings();

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
            SELECT rank_title, rank_image, rank_type, rank_posts, rank_apply, rank_language
            FROM ".DB_FORUM_RANKS." ".(multilang_table("FR") ? "WHERE ".in_group('rank_language', LANGUAGE) : "")."
            ORDER BY rank_apply DESC, rank_posts ASC
            ";

            $result = dbquery($cache_query);

            while ($data = dbarray($result)) {
                $type = isset($known_types[$data['rank_type']]) ? $known_types[$data['rank_type']] : 'special';
                self::$forum_rank_cache[ $type][] = $data;
            }
        }

        return (array)self::$forum_rank_cache;
    }

    /**
     * Verify Thread ID
     *
     * @param $thread_id
     *
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
     *
     * @param $post_id
     *
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
     *
     * @param int $forum_id - all if 0.
     *
     * @return mixed
     */
    public static function getRecentTopics($forum_id = 0) {
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
            ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND" : "WHERE")."
            ".groupaccess('tf.forum_access')." AND tt.thread_hidden='0'
            ".($forum_id ? "AND forum_id='".intval($forum_id)."'" : '')."
            GROUP BY thread_id ORDER BY tt.thread_lastpost LIMIT 0, ".$forum_settings['threads_per_page']."");
        $info['rows'] = dbrows($result);
        if ( $info['rows'] > 0) {
            // need to throw moderator as an object
            while ($data = dbarray($result)) {
                $data['moderators'] = ForumModerator::displayForumMods($data['forum_mods']);
                $info['item'][ $data['thread_id']] = $data;
            }
        }

        return $info;
    }

    /**
     * Thread Filter Instance
     *
     * @param bool $set_info
     *
     * @return null|ForumThreadFilter
     */
    public static function filter( $set_info = TRUE) {
        if (self::$filter_instance === NULL) {
            self::$filter_instance = new ForumThreadFilter();
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
     * @return Forum|null
     */
    public static function forum( $set_info = TRUE) {
        if (self::$forum_instance === NULL) {
            self::$forum_instance = new Forum();
            if ($set_info == TRUE ) {
                self::$forum_instance->setForumInfo();
            }
        }

        return self::$forum_instance;
    }

    /**
     * Tag Instance
     * @param bool $set_info
     * @param bool $set_title
     *
     * @return ForumTag|null
     */
    public static function tag( $set_info = TRUE, $set_title = FALSE) {
        if (empty(self::$tag_instance)) {
            self::$tag_instance = new ForumTag();
            if ($set_info == TRUE) {
                require_once INCLUDES."mimetypes_include.php";
                self::$tag_instance->setTagInfo($set_title);
            }
        }

        return self::$tag_instance;
    }

    /**
     * Thread Instance
     * @param bool $set_info
     *
     * @return ForumThreads|null
     */
    public static function thread( $set_info = TRUE) {
        if (empty(self::$thread_instance)) {
            self::$thread_instance = new ForumThreads();
            if ($set_info == TRUE) {
                require_once INCLUDES."mimetypes_include.php";
                self::$thread_instance->setThreadInfo();
            }
        }

        return self::$thread_instance;
    }

    /**
     * New Thread Instance
     * @param bool $set_info
     *
     * @return NewThread|null
     */
    public static function newThread( $set_info = TRUE) {
        if (empty(self::$new_thread_instance)) {
            self::$new_thread_instance = new NewThread();
            if ($set_info == TRUE) {
                self::$new_thread_instance->set_newThreadInfo();
            }
        }

        return self::$new_thread_instance;
    }

    /**
     * Mood Instance
     * @return ForumMood|null
     */
    public static function mood() {
        if (empty(self::$forum_mood_instance)) {
            self::$forum_mood_instance = new ForumMood();
        }
        return self::$forum_mood_instance;
    }

    /**
     * Load and do postify
     * @return ForumPostify|null
     * @throws Exception
     */
    public static function postify() {
        if (empty(self::$postify_instance)) {
            self::$postify_instance = new ForumPostify();
        }

        return self::$postify_instance;
    }

    /**
     * Method to change template path
     *
     * @param string|array $key instance key
     *
     * @return mixed
     */
    public static function getForumTemplate( $key) {
        $default_paths = [
            'forum'             => FORUM.'templates/index/forum_index.html',
            'forum_section'     => FORUM.'templates/forum_section.html',
            'forum_postify'     => FORUM.'templates/forum_postify.html',
            'forum_postform'    => FORUM.'templates/forms/post.html',
            'forum_pollform'    => FORUM.'templates/forms/poll.html',
            'forum_bountyform'  => FORUM.'templates/forms/bounty.html',
            'forum_qrform'      => FORUM.'templates/forms/quick_reply.html',
            'forum_qr_attach'   => FORUM.'templates/forms/qr_attachments.html',
            'tags_thread'       => FORUM.'templates/tags/tag_threads.html',
            'tags'              => FORUM.'templates/tags/tag.html',
            'viewthreads'       => FORUM.'templates/forum_threads.html',
            'viewforum'         => FORUM.'templates/forum_viewforum.html',
            'forums'            => FORUM.'templates/index/forum_item.html',
            'forum_post'        => FORUM.'templates/forum_post_item.html',
            'forum_thread'      => FORUM.'templates/viewforum/forum_thread_item.html',
            'forum_lastpost'    => FORUM.'templates/index/forum_item_lastpost.html',
            'forum_tag_threads' => FORUM.'templates/forum_tag_threads.html',
            'forum_tags'        => FORUM.'templates/forum_tags.html'

        ];

        return (isset(self::$forum_template_paths[$key]) ? self::$forum_template_paths[$key] : $default_paths[$key]);
    }

    /**
     * Method to set new template path
     *
     * @param string $key       instance key
     * @param string $file_path path relative to basedir
     */
    public static function set_template($key, $file_path) {
        self::$forum_template_paths[ $key] = $file_path;
    }

    /* Make an infinity traverse */
    private function getForumBreadcrumbs(array $index, $id) {
        $crumb = [];

        if (isset($index[get_parent($index, $id)])) {
            $_name = dbarray(dbquery("SELECT forum_id, forum_name, forum_cat, forum_branch FROM ".DB_FORUMS." WHERE forum_id=:fid", [':fid' => $id]));
            $crumb = [
                'link'  => FORUM.'index.php?viewforum&amp;forum_id='.$_name['forum_id'],
                'title' => $_name['forum_name']
            ];

            if (isset($index[get_parent($index, $id)])) {

                if (get_parent($index, $id) == 0) {
                    return $crumb;
                }

                $crumb_1 = $this->getForumBreadcrumbs($index, get_parent($index, $id));

                if (is_array($crumb_1)) {
                    $crumb = array_merge_recursive($crumb, $crumb_1); // convert so can comply to Fusion Tab API.
                }

            }
        }

        return (array)$crumb;
    }

    /**
     * Forum Breadcrumbs Generator
     *
     * @param array $forum_index - requires a dbquery_tree() output
     * @param int   $forum_id
     */
    function addForumBreadcrumb(array $forum_index, $forum_id = 0) {
        $locale = fusion_get_locale('', FORUM_LOCALE);

        if (empty($forum_id)) {
            $forum_id = get('forum_id', FILTER_VALIDATE_INT) ?: 0;
        }


        // then we make a infinity recursive function to loop/break it out.
        $crumb = $this->getForumBreadcrumbs($forum_index, $forum_id);

        $title_count = !empty($crumb['title']) && is_array($crumb['title']) ? count($crumb['title']) > 1 : 0;
        // then we sort in reverse.
        if ($title_count) {
            krsort($crumb['title']);
            krsort($crumb['link']);
        }
        if ($title_count) {
            foreach ( $crumb['title'] as $i => $value) {
                add_breadcrumb(['link' => $crumb['link'][$i], 'title' => $value]);
                if ($i == count($crumb['title']) - 1) {
                    add_to_title($locale['global_201'].$value);
                }
            }
        } else if (isset($crumb['title'])) {
            add_to_title($locale['global_201'].$crumb['title']);
            add_breadcrumb([ 'link' => $crumb['link'], 'title' => $crumb['title']]);
        }
    }

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
     * @throws Exception
     */
    protected function checkForumAccess( $forum_index, $forum_id = 0, $thread_id = 0, $user_id = 0) {
        if (iSUPERADMIN) {
            $this->forum_access = TRUE;

            return $this->forum_access;
        }
        if (!$forum_id or isnum($forum_id)) {
            if ($thread_id && isnum($thread_id)) {
                $forum_id = dbresult(dbquery("SELECT forum_id FROM ".DB_FORUM_THREADS." WHERE thread_id=:thread_id", [ ':thread_id' => $thread_id]), 0);
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
                                $this->forum_access = checkusergroup( $data['forum_access'], $user['user_level'], $user['user_groups']) ? TRUE : FALSE;
                            } else {
                                $this->forum_access = checkgroup( $data['forum_access']) ? TRUE : FALSE;
                            }
                            if ($this->forum_access === FALSE) {
                                break;
                            }
                        }
                    }
                }
            } else {
                throw new Exception( fusion_get_locale( 'forum_4120'));
            }
        }

        return (bool)$this->forum_access;
    }

    /**
     * Moderator Instance
     * @return ForumModerator|null
     */
    protected function moderator() {
        if (empty(self::$moderator_instance)) {
            self::$moderator_instance = new ForumModerator();
        }

        return self::$moderator_instance;
    }

}
