<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: tags.php
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

class ThreadTags extends ForumServer {

    public $tag_info = array();

    public function get_TagInfo() {
        return (array)$this->tag_info;
    }

    /**
     * Fetches all Forum Tag Table records
     *
     * @param bool|TRUE $setTitle
     */
    public function set_TagInfo($setTitle = TRUE) {

        $locale = fusion_get_locale("", FORUM_LOCALE);
        $locale += fusion_get_locale("", FORUM_TAGS_LOCALE);

        if ($setTitle == TRUE) {
            set_title($locale['forum_0000']);
            add_to_title($locale['global_201'].$locale['forum_tag_0100']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => FORUM."index.php",
                'title' => $locale['forum_0000']
            ]);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => FORUM."tags.php",
                'title' => $locale['forum_tag_0100']
            ]);
        }

        $thread_result = NULL;

        if (isset($_GET['tag_id']) && isnum($_GET['tag_id'])) {

            $tag_query = "SELECT * FROM ".DB_FORUM_TAGS." WHERE tag_status=1 AND tag_id='".intval($_GET['tag_id'])."'
            ".(multilang_table("FO") ? "AND tag_language='".LANGUAGE."'" : "")."
            ";
            $tag_result = dbquery($tag_query);
            if (dbrows($tag_result) > 0) {
                $data = dbarray($tag_result);

                add_to_title($locale['global_201'].$data['tag_title']);
                BreadCrumbs::getInstance()->addBreadCrumb([
                    'link'  => FORUM."tags.php?tag_id=".$data['tag_id'],
                    'title' => $data['tag_title']
                ]);
                if (!empty($data['tag_description'])) set_meta('description', $data['tag_description']);

                $data['tag_link'] = FORUM."tags.php?tag_id=".$data['tag_id'];
                $data['tag_active'] = (isset($_GET['viewtags']) && isset($_GET['tag_id']) && $_GET['tag_id'] == $data['tag_id'] ? TRUE : FALSE);

                $this->tag_info['tags'][$data['tag_id']] = $data;
                $this->tag_info['tags'][0] = array(
                    'tag_id'     => 0,
                    'tag_link'   => FORUM."tags.php",
                    'tag_title'  => fusion_get_locale("global_700")."&hellip;",
                    'tag_active' => '',
                    'tag_color'  => ''
                );

                $this->tag_info['filter'] = $this->filter()->get_FilterInfo();
                $filter_sql = $this->filter()->get_filterSQL();

                // get forum threads.
                $this->tag_info = array_merge_recursive($this->tag_info, self::get_tag_thread($_GET['tag_id'], array(
                    "condition" => $filter_sql['condition'],
                    "order"     => $filter_sql['order']
                )));

            } else {
                redirect(FORUM."index.php");
            }

        } else {
            $this->cache_tags();
        }
    }

    /**
     * Get thread structure when given specific tag id
     *
     * @param string     $tag_id
     * @param bool|FALSE $filter
     *
     * @return array
     */
    public static function get_tag_thread($tag_id = '0', $filter = FALSE) {

        $info = array();

        $locale = fusion_get_locale("", FORUM_LOCALE);

        $forum_settings = ForumServer::get_forum_settings();

        $userdata = fusion_get_userdata();
        $userdata['user_id'] = !empty($userdata['user_id']) ? (int)intval($userdata['user_id']) : 0;

        $lastVisited = defined('LASTVISITED') ? LASTVISITED : TIME;
        /**
         * Get threads with filter conditions (XSS prevention)
         */
        $thread_query = "
        SELECT
        count(t.thread_id) 'thread_max_rows',
        count(a.attach_id) 'attach_image',
        count(a2.attach_id) 'attach_files',
        count(a.attach_id) 'attach_count'
        FROM ".DB_FORUM_THREADS." t
        LEFT JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
        INNER JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
        #LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id
        LEFT JOIN ".DB_FORUM_POSTS." p1 ON p1.thread_id = t.thread_id and p1.post_id = t.thread_lastpostid
        LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id
        #LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = t.thread_id AND p1.post_id = v.post_id
        LEFT JOIN ".DB_FORUM_ATTACHMENTS." a on a.thread_id = t.thread_id AND a.attach_mime IN ('".implode(",", img_mimeTypes())."')
        LEFT JOIN ".DB_FORUM_ATTACHMENTS." a2 on a2.thread_id = t.thread_id AND a2.attach_mime NOT IN ('".implode(",", img_mimeTypes())."')
        WHERE ".in_group('t.thread_tags', intval($tag_id), '.')."AND t.thread_hidden='0' AND ".groupaccess('tf.forum_access')."
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

        $info['item'][$tag_id]['forum_threadcount'] = 0;
        $info['item'][$tag_id]['forum_threadcount_word'] = format_word($count['thread_max_rows'], $locale['fmt_thread']);

        if ($thread_rows > 0) {
            $count = dbarray($thread_result);
            $info['item'][$tag_id]['forum_threadcount'] = 0;
            $info['item'][$tag_id]['forum_threadcount_word'] = format_word($count['thread_max_rows'], $locale['fmt_thread']);
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
            WHERE ".in_group('t.thread_tags', intval($tag_id), '.')." AND t.thread_hidden='0' AND ".groupaccess('tf.forum_access')."
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
                        "thread_pages"   => makepagenav(0, $forum_settings['posts_per_page'], $threads['thread_postcount'], 3, FORUM."viewthread.php?thread_id=".$threads['thread_id']."&amp;"),
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
                        "thread_starter" => $locale['forum_0006'].' '.timer($threads['first_post_datestamp'])." ".$locale['by']." ".profile_link($author['user_id'], $author['user_name'], $author['user_status'])."</span>",
                        "thread_author"  => $author,
                        "thread_last"    => array(
                            'user'         => $author,
                            'avatar'       => display_avatar($lastuser, '30px', '', '', ''),
                            'profile_link' => profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status']),
                            'time'         => $threads['post_datestamp'],
                            'post_message' => parseubb(parsesmileys($threads['post_message'])),
                            "formatted"    => "<div class='pull-left'>".display_avatar($lastuser, '30px', '', '', '')."</div>
																				<div class='overflow-hide'>".$locale['forum_0373']." <span class='forum_profile_link'>".profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status'])."</span><br/>
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

    public function cache_tags() {

        $tag_query = "SELECT * FROM ".DB_FORUM_TAGS." WHERE tag_status=:tag_status ".(multilang_table("FO") ? "AND tag_language='".LANGUAGE."'" : "")." ORDER BY tag_title ASC";
        $tag_param = [':tag_status' => 1];
        $tag_result = dbquery($tag_query, $tag_param);

        if (dbrows($tag_result)) {

            while ($data = dbarray($tag_result)) {
                $data['tag_link'] = FORUM."tags.php?tag_id=".$data['tag_id'];
                $data['tag_active'] = (isset($_GET['viewtags']) && isset($_GET['tag_id']) && $_GET['tag_id'] == $data['tag_id'] ? TRUE : FALSE);
                $this->tag_info['tags'][$data['tag_id']] = $data;
                $thread_query = "SELECT * FROM ".DB_FORUM_THREADS." WHERE ".in_group('thread_tags', $data['tag_id'])." ORDER BY thread_lastpost DESC LIMIT 1";
                $thread_result = dbquery($thread_query);
                $thread_rows = dbrows($thread_result);
                if ($thread_rows > 0) {
                    $tData = dbarray($thread_result);
                    $this->tag_info['tags'][$data['tag_id']]['threads'] = $tData;
                }
            }

            // More
            $this->tag_info['tags'][0] = array(
                'tag_id'     => 0,
                'tag_link'   => FORUM."tags.php",
                'tag_title'  => fusion_get_locale("global_700")."&hellip;",
                'tag_active' => '',
                'tag_color'  => ''
            );

        }
    }

    /**
     *  Get Tag Options for Dropdown Selector
     *
     * @param bool|FALSE $is_dropdown - is used in dropdown?
     *
     * @return array
     */
    public function get_tagOpts($is_dropdown = FALSE) {
        $tag_opts = array();
        if (!empty($this->tag_info['tags'])) {
            $tag_info = $this->tag_info['tags'];
            if ($is_dropdown) {
                unset($tag_info[0]);
            }
            foreach ($tag_info as $tag_data) {
                $tag_opts[$tag_data['tag_id']] = $tag_data['tag_title'];
            }
        }

        return (array)$tag_opts;
    }

    /**
     * Displays current thread tags
     *
     * @param $thread_tags - tagID (SQL data in DB_FORUM_THREADS `thread_tags`)
     *
     * @return string
     */
    public function display_thread_tags($thread_tags) {
        $html = "";
        $this->cache_tags();
        if (!empty($this->tag_info['tags']) && !empty($thread_tags)) {
            $tags = explode(".", $thread_tags);
            foreach ($tags as $tag_id) {
                if (isset($this->tag_info['tags'][$tag_id])) {
                    $tag_data = $this->tag_info['tags'][$tag_id];
                    $html .= "<div class='tag_info m-r-10'>";
                    $html .= ($tag_data['tag_status']) ? "<a href='".$tag_data['tag_link']."'>\n" : "";
                    $html .= "<i class='fa fa-square fa-lg fa-fw' style='color:".$tag_data['tag_color']."'></i> ";
                    $html .= $tag_data['tag_title'];
                    $html .= ($tag_data['tag_status']) ? "</a>\n" : "";
                    $html .= "</div>\n";
                }
            }
        }

        return (string)$html;
    }

}