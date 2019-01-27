<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_tags.php
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

use PHPFusion\BreadCrumbs;
use PHPFusion\Infusions\Forum\Classes\Forum_Server;

class Forum_Tags extends Forum_Server {

    public $tag_info = [];

    public function get_TagInfo() {
        return (array)$this->tag_info;
    }

    /**
     * Fetches all Forum Tag Table records
     *
     * @param bool|TRUE $setTitle
     */
    public function set_TagInfo($setTitle = TRUE) {

        $locale = fusion_get_locale();

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
                if (!empty($data['tag_description'])) {
                    set_meta('description', $data['tag_description']);
                }
                $data['tag_link'] = FORUM."tags.php?tag_id=".$data['tag_id'];
                $data['tag_active'] = (isset($_GET['viewtags']) && isset($_GET['tag_id']) && $_GET['tag_id'] == $data['tag_id'] ? TRUE : FALSE);

                $this->tag_info['tags'][$data['tag_id']] = $data;
                $this->tag_info['title'] = $data['tag_title'];
                $this->tag_info['description'] = $data['tag_description'];
                $this->tag_info['tags'][0] = [
                    'tag_id'     => 0,
                    'tag_link'   => FORUM."tags.php",
                    'tag_title'  => fusion_get_locale("global_700")."&hellip;",
                    'tag_active' => '',
                    'tag_color'  => ''
                ];
                // get forum threads.
                $this->tag_info['filter'] = $this->filter()->get_FilterInfo();
                $filter = $this->filter()->get_filterSQL();
                $thread_info = Forum_Server::thread(FALSE)->get_forum_thread(0,
                    [
                        "condition" => " AND ".in_group('t.thread_tags', intval($data['tag_id']), '.')." ".$filter['condition'],
                        "order"     => $filter['order'],
                        "debug"     => FALSE,
                    ] + $filter + [
                        'custom_condition' => " AND ".in_group('t.thread_tags', intval($data['tag_id']), '.'),
                    ]
                );

                $this->tag_info = array_merge_recursive($this->tag_info, $thread_info);

            } else {
                redirect(FORUM."index.php");
            }

        } else {
            $this->cache_tags();
        }
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
                    $tData['thread_link'] = FORUM.'viewthread.php?thread_id='.$tData['thread_id'];
                    $tData['user_id'] = 0;
                    $tData['user_name'] = "";
                    $tData['user_status'] = 0;
                    $tData['user_avatar'] = "";
                    if ($author = fusion_get_user($tData['thread_author'])) {
                        $tData += $author;
                    }
                    $tData['thread_profile_link'] = profile_link($tData['user_id'], $tData['user_name'], $tData['user_status']);
                    $tData['thread_avatar'] = display_avatar($tData, '25px');
                    $this->tag_info['tags'][$data['tag_id']]['threads'] = $tData;
                }
            }

            // More
            $this->tag_info['tags'][0] = [
                'tag_id'     => 0,
                'tag_link'   => FORUM."tags.php",
                'tag_title'  => fusion_get_locale("global_700")."&hellip;",
                'tag_active' => '',
                'tag_color'  => '',
                'tag_status' => 1
            ];

        }

        return self::$tag_instance;
    }

    /**
     *  Get Tag Options for Dropdown Selector
     *
     * @param bool|FALSE $is_dropdown - is used in dropdown?
     *
     * @return array
     */
    public function get_tagOpts($is_dropdown = FALSE) {
        $tag_opts = [];
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
