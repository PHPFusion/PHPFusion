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
namespace PHPFusion\Forums;

use PHPFusion\BreadCrumbs;

class Forum extends ForumServer {

    /**
     * Forum Data
     *
     * @var array
     */
    private $forum_info = array();

    /**
     * Extensions of parent_id
     *
     * @var string
     */
    private $ext = '';

    /**
     * @return array
     */
    public function getForumInfo() {
        return $this->forum_info;
    }

    /**
     * Executes forum
     */
    public function set_ForumInfo() {

        $forum_settings = $this->get_forum_settings();

        $userdata = fusion_get_userdata();

        $locale = fusion_get_locale("", FORUM_LOCALE);

        $_GET['forum_id'] = (isset($_GET['forum_id']) && verify_forum($_GET['forum_id'])) ? intval($_GET['forum_id']) : 0;

        // security boot due to insufficient access level
        if (isset($_GET['viewforum']) && (empty($_GET['forum_id']) OR !isnum($_GET['forum_id']))) {
            redirect(INFUSIONS.'forum/index.php');
        }

        if (stristr($_SERVER['PHP_SELF'], 'forum_id')) {
            if ($_GET['section'] == 'latest') redirect(INFUSIONS.'forum/index.php?section=latest');
            if ($_GET['section'] == 'mypost') redirect(INFUSIONS.'forum/index.php?section=mypost');
            if ($_GET['section'] == 'tracked') redirect(INFUSIONS.'forum/index.php?section=tracked');
        }

        // Xss sanitization
        $this->forum_info = [
            'forum_id'         => isset($_GET['forum_id']) ? $_GET['forum_id'] : 0,
            'new_thread_link'  => '',
            'lastvisited'      => isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit']) ? $userdata['user_lastvisit'] : time(),
            'posts_per_page'   => $forum_settings['posts_per_page'],
            'threads_per_page' => $forum_settings['threads_per_page'],
            'forum_index'      => dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat', (multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('forum_access')), // waste resources here.
            'threads'          => array(),
            'section'          => isset($_GET['section']) ? $_GET['section'] : 'thread',
        ];
        $this->forum_info['parent_id'] = dbresult(dbquery("SELECT forum_cat FROM ".DB_FORUMS." WHERE forum_id='".$this->forum_info['forum_id']."'"), 0);
        $this->forum_info['forum_branch'] = dbresult(dbquery("SELECT forum_branch FROM ".DB_FORUMS." WHERE forum_id='".$this->forum_info['forum_id']."'"), 0);

        // Set Max Rows -- XSS
        $this->forum_info['forum_max_rows'] = dbcount("('forum_id')", DB_FORUMS, (multilang_table("FO") ? "forum_language='".LANGUAGE."' AND" : '')."
		forum_cat='".$this->forum_info['parent_id']."' AND ".groupaccess('forum_access')."");

        // Sanitize Globals
        $_GET['rowstart'] = (isset($_GET['rowstart']) && $_GET['rowstart'] <= $this->forum_info['forum_max_rows']) ? $_GET['rowstart'] : 0;

        $this->ext = isset($this->forum_info['parent_id']) && isnum($this->forum_info['parent_id']) ? "&amp;parent_id=".$this->forum_info['parent_id'] : '';

        add_to_title($locale['global_200'].$locale['forum_0000']);

        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FORUM."index.php", "title" => $locale['forum_0000']]);

        $this->forum_breadcrumbs($this->forum_info['forum_index']);

        // Set Meta data
        if ($this->forum_info['forum_id'] > 0) {
            $meta_sql = "SELECT forum_meta, forum_description FROM ".DB_FORUMS."
            WHERE forum_id='".intval($this->forum_info['forum_id'])."'";
            $meta_result = dbquery($meta_sql);
            if (dbrows($meta_result) > 0) {
                $meta_data = dbarray($meta_result);
                if (!empty($meta_data['forum_description'])) set_meta('description', $meta_data['forum_description']);
                if (!empty($meta_data['forum_meta'])) set_meta('keywords', $meta_data['forum_meta']);
            }
        }

        // Additional Sections in Index View
        if (isset($_GET['section'])) {

            switch ($_GET['section']) {
                case 'participated':
                    include FORUM_SECTIONS."participated.php";
                    add_to_title($locale['global_201'].$locale['global_024']);
                    BreadCrumbs::getInstance()->addBreadCrumb([
                        'link'  => FORUM."index.php?section=participated",
                        'title' => $locale['global_024']
                    ]);
                    set_meta("description", $locale['global_024']);
                    break;
                case 'latest':
                    include FORUM_SECTIONS."latest.php";
                    add_to_title($locale['global_201'].$locale['global_021']);
                    BreadCrumbs::getInstance()->addBreadCrumb([
                        'link'  => FORUM."index.php?section=latest",
                        'title' => $locale['global_021']
                    ]);
                    set_meta("description", $locale['global_021']);
                    break;
                case 'tracked':
                    include FORUM_SECTIONS."tracked.php";
                    add_to_title($locale['global_201'].$locale['global_056']);
                    BreadCrumbs::getInstance()->addBreadCrumb([
                        'link'  => FORUM."index.php?section=tracked",
                        'title' => $locale['global_056']
                    ]);
                    set_meta("description", $locale['global_056']);
                    break;
                case "unanswered":
                    include FORUM_SECTIONS."unanswered.php";
                    add_to_title($locale['global_201'].$locale['global_027']);
                    BreadCrumbs::getInstance()->addBreadCrumb([
                        'link'  => INFUSIONS."forum/index.php?section=unanswered",
                        'title' => $locale['global_027']
                    ]);
                    set_meta("description", $locale['global_027']);
                    break;
                case "unsolved":
                    include FORUM_SECTIONS."unsolved.php";
                    add_to_title($locale['global_201'].$locale['global_028']);
                    BreadCrumbs::getInstance()->addBreadCrumb([
                        'link'  => INFUSIONS."forum/index.php?section=unsolved",
                        'title' => $locale['global_028']
                    ]);
                    set_meta("description", $locale['global_028']);
                    break;
                default:
                    redirect(FORUM);
            }

        } else {

            // Viewforum view
            if (!empty($this->forum_info['forum_id']) && isset($_GET['viewforum'])) {

                // @todo: turn this into ajax filtration to cut down SEO design pattern
                $this->forum_info['filter'] = $this->filter()->get_FilterInfo();

                // Forum SQL
                $forum_sql = "
                SELECT f.*,
                f2.forum_name 'forum_cat_name',
				t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject,
				p.post_message,
				u.user_id, u.user_name, u.user_status, u.user_avatar,
				min(p2.post_datestamp) 'first_post_datestamp'
				FROM ".DB_FORUMS." f
				# subforums
				LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat = f2.forum_id
				# thread info
				LEFT JOIN ".DB_FORUM_THREADS." t ON t.forum_id = f.forum_id AND ".groupaccess('f.forum_access')."
				# just last post
				LEFT JOIN ".DB_FORUM_POSTS." p on p.thread_id = t.thread_id and p.post_id = t.thread_lastpostid
				# post info
				LEFT JOIN ".DB_FORUM_POSTS." p2 ON p2.thread_id = t.thread_id
				# just last post user
				LEFT JOIN ".DB_USERS." u ON f.forum_lastuser=u.user_id
				".(multilang_table("FO") ? "WHERE f.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('f.forum_access')."
				AND (f.forum_id='".intval($this->forum_info['forum_id'])."' OR f.forum_cat='".intval($this->forum_info['forum_id'])."'
				OR f.forum_branch='".intval($this->forum_info['forum_branch'])."')
				GROUP BY f.forum_id ORDER BY forum_cat ASC
                ";

                $result = dbquery($forum_sql);

                $refs = array();

                // define what a row is
                $row_array = array(
                    'forum_new_status'       => '',
                    'last_post'              => '',
                    'forum_icon'             => '',
                    'forum_icon_lg'          => '',
                    'forum_moderators'       => '',
                    'forum_link'             => array(
                        'link'  => '',
                        'title' => ''
                    ),
                    'forum_description'      => '',
                    'forum_postcount_word'   => '',
                    'forum_threadcount_word' => '',
                );

                if (dbrows($result) > 0) {

                    while ($row = dbarray($result) and checkgroup($row['forum_access'])) {

                        // Calculate Forum New Status
                        $newStatus = "";
                        $forum_match = "\|".$row['forum_lastpost']."\|".$row['forum_id'];
                        $last_visited = (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time();
                        if ($row['forum_lastpost'] > $last_visited) {
                            if (iMEMBER && ($row['forum_lastuser'] !== $userdata['user_id'] || !preg_match("({$forum_match}\.|{$forum_match}$)", $userdata['user_threads']))) {
                                $newStatus = "<span class='forum-new-icon'><i title='".$locale['forum_0260']."' class='".self::get_forumIcons('new')."'></i></span>";
                            }
                        }

                        // Calculate lastpost information
                        $lastPostInfo = array();
                        if (!empty($row['forum_lastpostid'])) {
                            $last_post = array(
                                'avatar'       => '',
                                'avatar_src'   => $row['user_avatar'] && file_exists(IMAGES.'avatars/'.$row['user_avatar']) && !is_dir(IMAGES.'avatars/'.$row['user_avatar']) ? IMAGES.'avatars/'.$row['user_avatar'] : '',
                                'message'      => fusion_first_words(parseubb(parsesmileys($row['post_message'])), 10),
                                'profile_link' => profile_link($row['forum_lastuser'], $row['user_name'], $row['user_status']),
                                'time'         => timer($row['forum_lastpost']),
                                'date'         => showdate("forumdate", $row['forum_lastpost']),
                                'thread_link'  => INFUSIONS."forum/viewthread.php?forum_id=".$row['forum_id']."&amp;thread_id=".$row['thread_id'],
                                'post_link'    => INFUSIONS."forum/viewthread.php?forum_id=".$row['forum_id']."&amp;thread_id=".$row['thread_id']."&amp;pid=".$row['thread_lastpostid']."#post_".$row['thread_lastpostid'],
                            );
                            if ($forum_settings['forum_last_post_avatar']) {
                                $last_post['avatar'] = display_avatar($row, '30px', '', '', 'img-rounded');
                            }
                            $lastPostInfo = $last_post;
                        }

                        /**
                         * Default system icons - why do i need this? Why not let themers decide?
                         */
                        switch ($row['forum_type']) {
                            case '1':
                                $forum_icon = "<i class='".self::get_forumIcons('forum')." fa-fw m-r-10'></i>";
                                $forum_icon_lg = "<i class='".self::get_forumIcons('forum')." fa-3x fa-fw m-r-10'></i>";
                                break;
                            case '2':
                                $forum_icon = "<i class='".self::get_forumIcons('thread')." fa-fw m-r-10'></i>";
                                $forum_icon_lg = "<i class='".self::get_forumIcons('thread')." fa-3x fa-fw m-r-10'></i>";
                                break;
                            case '3':
                                $forum_icon = "<i class='".self::get_forumIcons('link')." fa-fw m-r-10'></i>";
                                $forum_icon_lg = "<i class='".self::get_forumIcons('link')." fa-3x fa-fw m-r-10'></i>";
                                break;
                            case '4':
                                $forum_icon = "<i class='".self::get_forumIcons('question')." fa-fw m-r-10'></i>";
                                $forum_icon_lg = "<i class='".self::get_forumIcons('question')." fa-3x fa-fw m-r-10'></i>";
                                break;
                            default:
                                $forum_icon = "";
                                $forum_icon_lg = "";
                        }

                        $mods = new Moderator();
                        $_row = array_merge($row_array, $row, array(
                            "forum_type"             => $row['forum_type'],
                            "forum_moderators"       => $mods::parse_forum_mods($row['forum_mods']), //// display forum moderators per forum.
                            "forum_new_status"       => $newStatus,
                            "forum_link"             => array(
                                "link"  => FORUM."index.php?viewforum&amp;forum_id=".$row['forum_id'],
                                "title" => $row['forum_name']
                            ),
                            "forum_description"      => nl2br(parseubb($row['forum_description'])), // current forum description
                            "forum_postcount_word"   => format_word($row['forum_postcount'], $locale['fmt_post']), // current forum post count
                            "forum_threadcount_word" => format_word($row['forum_threadcount'], $locale['fmt_thread']), // thread in the current forum
                            "last_post"              => $lastPostInfo, // last post information
                            "forum_icon"             => $forum_icon, // normal icon
                            "forum_icon_lg"          => $forum_icon_lg, // big icon.
                            "forum_image"            => ($row['forum_image'] && file_exists(FORUM."images/".$row['forum_image'])) ? $row['forum_image'] : '',
                        ));

                        $this->forum_info['forum_moderators'] = $_row['forum_moderators'];

                        // child hierarchy data.
                        $thisref = &$refs[$_row['forum_id']];
                        $thisref = $_row;
                        if ($_row['forum_cat'] == $this->forum_info['parent_id']) {
                            $this->forum_info['item'][$_row['forum_id']] = &$thisref; // will push main item out.
                        } else {
                            $refs[$_row['forum_cat']]['child'][$_row['forum_id']] = &$thisref;
                        }

                        /**
                         * The current forum
                         */
                        if ($row['forum_id'] == $this->forum_info['forum_id']) {

                            require_once INCLUDES."mimetypes_include.php";

                            $this->forum_info['forum_type'] = $row['forum_type'];

                            $mods::define_forum_mods($row);

                            // do the full string of checks for forums access
                            $this->setForumPermission($row);

                            // Generate New thread link
                            if ($this->getForumPermission("can_post") && $row['forum_type'] > 1) {
                                $this->forum_info['new_thread_link'] = FORUM."newthread.php?forum_id=".$row['forum_id'];
                            }

                            // Not a category
                            if ($row['forum_type'] !== '1') {

                                $filter_sql = $this->filter()->get_filterSQL();

                                $thread_info = $this->thread(FALSE)->get_forum_thread($this->forum_info['forum_id'],
                                    array(
                                        'condition' => $filter_sql['condition'],
                                        'order'     => $filter_sql['order']
                                    ));

                                $this->forum_info = array_merge_recursive($this->forum_info, $thread_info);
                            }
                        }
                    }
                } else {
                    redirect(INFUSIONS.'forum/index.php');
                }
            } else {
                $this->forum_info['forums'] = self::get_forum(); //Index view
            }

        }
    }

    /**
     * Set user permission based on current forum configuration
     *
     * @param $forum_data
     */
    public function setForumPermission($forum_data) {
        // Access the forum
        $this->forum_info['permissions']['can_access'] = (iMOD || checkgroup($forum_data['forum_access'])) ? TRUE : FALSE;
        // Create new thread -- whether user has permission to create a thread
        $this->forum_info['permissions']['can_post'] = (iMOD || (checkgroup($forum_data['forum_post']) && $forum_data['forum_lock'] == FALSE)) ? TRUE : FALSE;
        // Poll creation -- thread has not exist, therefore cannot be locked.
        $this->forum_info['permissions']['can_create_poll'] = $forum_data['forum_allow_poll'] == TRUE && (iMOD || (checkgroup($forum_data['forum_poll']) && $forum_data['forum_lock'] == FALSE)) ? TRUE : FALSE;
        $this->forum_info['permissions']['can_upload_attach'] = $forum_data['forum_allow_attach'] == TRUE && (iMOD || checkgroup($forum_data['forum_attach'])) ? TRUE : FALSE;
        $this->forum_info['permissions']['can_download_attach'] = iMOD || ($forum_data['forum_allow_attach'] == TRUE && checkgroup($forum_data['forum_attach_download'])) ? TRUE : FALSE;
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

    /**
     * Get the forum structure
     *
     * @param bool $forum_id
     * @param bool $branch_id
     *
     * @return array
     */
    public static function get_forum($forum_id = FALSE, $branch_id = FALSE) { // only need to fetch child.

        $forum_settings = self::get_forum_settings();

        $userdata = fusion_get_userdata();

        $locale = fusion_get_locale("", FORUM_LOCALE);

        $index = array();

        // define what a row is
        $row = array(
            'forum_new_status'       => '',
            'last_post'              => '',
            'forum_icon'             => '',
            'forum_icon_lg'          => '',
            'forum_moderators'       => '',
            'forum_link'             => array(
                'link'  => '',
                'title' => ''
            ),
            'forum_description'      => '',
            'forum_postcount_word'   => '',
            'forum_threadcount_word' => '',
        );

        $query = dbquery("
				SELECT tf.forum_id, tf.forum_cat, tf.forum_branch, tf.forum_name, tf.forum_description, tf.forum_image,
				tf.forum_type, tf.forum_mods, tf.forum_threadcount, tf.forum_postcount, tf.forum_order, tf.forum_lastuser, tf.forum_access, tf.forum_lastpost, tf.forum_lastpostid,
				t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject, p.post_message,
				u.user_id, u.user_name, u.user_status, u.user_avatar
				FROM ".DB_FORUMS." tf
				LEFT JOIN ".DB_FORUM_THREADS." t ON tf.forum_lastpostid = t.thread_lastpostid
				LEFT JOIN ".DB_FORUM_POSTS." p ON p.thread_id = t.thread_id AND p.post_id = t.thread_lastpostid
				LEFT JOIN ".DB_USERS." u ON tf.forum_lastuser = u.user_id
				".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('tf.forum_access')."
				".($forum_id && $branch_id ? "AND tf.forum_id = '".intval($forum_id)."' or tf.forum_cat = '".intval($forum_id)."' OR tf.forum_branch = '".intval($branch_id)."'" : '')."
				GROUP BY tf.forum_id ORDER BY tf.forum_cat ASC, tf.forum_order ASC, t.thread_lastpost DESC
		");
        while ($data = dbarray($query) and checkgroup($data['forum_access'])) {

            // Calculate Forum New Status
            $newStatus = "";
            $forum_match = "\\|".$data['forum_lastpost']."\\|".$data['forum_id'];
            $last_visited = (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time();
            if ($data['forum_lastpost'] > $last_visited) {
                if (iMEMBER && ($data['forum_lastuser'] !== $userdata['user_id'] || !preg_match("({$forum_match}\\.|{$forum_match}$)",
                            $userdata['user_threads']))
                ) {
                    $newStatus = "<span class='forum-new-icon'><i title='".$locale['forum_0260']."' class='".self::get_forumIcons('new')."'></i></span>";
                }
            }
            // Calculate lastpost information
            $lastPostInfo = array();
            if ($data['forum_lastpostid']) {
                $last_post = array(
                    'avatar'       => '',
                    'avatar_src'   => $data['user_avatar'] && file_exists(IMAGES.'avatars/'.$data['user_avatar']) && !is_dir(IMAGES.'avatars/'.$data['user_avatar']) ? IMAGES.'avatars/'.$data['user_avatar'] : '',
                    'message'      => fusion_first_words(parseubb(parsesmileys($data['post_message'])), 10),
                    'profile_link' => profile_link($data['forum_lastuser'], $data['user_name'], $data['user_status']),
                    'time'         => timer($data['forum_lastpost']),
                    'date'         => showdate("forumdate", $data['forum_lastpost']),
                    'thread_link'  => INFUSIONS."forum/viewthread.php?forum_id=".$data['forum_id']."&amp;thread_id=".$data['thread_id'],
                    'post_link'    => INFUSIONS."forum/viewthread.php?forum_id=".$data['forum_id']."&amp;thread_id=".$data['thread_id']."&amp;pid=".$data['thread_lastpostid']."#post_".$data['thread_lastpostid'],
                );
                if ($forum_settings['forum_last_post_avatar']) {
                    $last_post['avatar'] = display_avatar($data, '30px', '', '', 'img-rounded');
                }
                $lastPostInfo = $last_post;
            }
            /**
             * Default system icons - why do i need this? Why not let themers decide?
             */
            switch ($data['forum_type']) {
                case '1':
                    $forum_icon = "<i class='".self::get_forumIcons('forum')." fa-fw m-r-10'></i>";
                    $forum_icon_lg = "<i class='".self::get_forumIcons('forum')." fa-3x fa-fw m-r-10'></i>";
                    break;
                case '2':
                    $forum_icon = "<i class='".self::get_forumIcons('thread')." fa-fw m-r-10'></i>";
                    $forum_icon_lg = "<i class='".self::get_forumIcons('thread')." fa-3x fa-fw m-r-10'></i>";
                    break;
                case '3':
                    $forum_icon = "<i class='".self::get_forumIcons('link')." fa-fw m-r-10'></i>";
                    $forum_icon_lg = "<i class='".self::get_forumIcons('link')." fa-3x fa-fw m-r-10'></i>";
                    break;
                case '4':
                    $forum_icon = "<i class='".self::get_forumIcons('question')." fa-fw m-r-10'></i>";
                    $forum_icon_lg = "<i class='".self::get_forumIcons('question')." fa-3x fa-fw m-r-10'></i>";
                    break;
                default:
                    $forum_icon = "";
                    $forum_icon_lg = "";
            }
            $mod = new Moderator();
            $row = array_merge($row, $data, array(
                "forum_moderators"       => $mod::parse_forum_mods($data['forum_mods']),
                // display forum moderators per forum.
                "forum_new_status"       => $newStatus,
                "forum_link"             => array(
                    "link"  => INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$data['forum_id'],
                    // uri
                    "title" => $data['forum_name']
                ),
                "forum_description"      => nl2br(parseubb($data['forum_description'])),
                // current forum description
                "forum_postcount_word"   => format_word($data['forum_postcount'], $locale['fmt_post']),
                // current forum post count
                "forum_threadcount_word" => format_word($data['forum_threadcount'], $locale['fmt_thread']),
                // current forum thread count
                "last_post"              => $lastPostInfo,
                // last post information
                "forum_icon"             => $forum_icon,
                // normal icon
                "forum_icon_lg"          => $forum_icon_lg,
                // big icon.
            ));

            $row["forum_image"] = ($row['forum_image'] && file_exists(FORUM."images/".$row['forum_image'])) ? $row['forum_image'] : "";
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
}