<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Forum.php
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

class ThreadTags extends ForumServer {

    public $tag_info = array();

    public function get_TagInfo() {
        return (array) $this->tag_info;
    }

    public function set_TagInfo() {

        $forum_settings = $this->get_forum_settings();

        $locale = fusion_get_locale("", FORUM_LOCALE);
        $locale += fusion_get_locale("", FORUM_TAGS_LOCALE);

        add_to_title($locale['forum_0000']);
        add_to_title($locale['global_201'].$locale['forum_tag_0100']);
        add_breadcrumb(array(
            'link' => FORUM."index.php",
            'title' => $locale['forum_0000']
                       )
        );
        add_breadcrumb(array(
            'link' => FORUM."tags.php",
            'title' => $locale['forum_tag_0100']
                       )
        );
        $thread_result = NULL;

        if (isset($_GET['tag_id']) && isnum($_GET['tag_id'])) {

            $tag_query = "SELECT * FROM ".DB_FORUM_TAGS." WHERE tag_status=1 AND tag_id='".intval($_GET['tag_id'])."'
            ".(multilang_table("FO") ? "AND tag_language='".LANGUAGE."'" : "")."
            ";
            $tag_result = dbquery( $tag_query );
            if (dbrows( $tag_result ) > 0 ) {
                $data = dbarray($tag_result);

                add_to_title($locale['global_201'].$data['tag_title']);
                add_breadcrumb(array(
                                   'link' => FORUM."tags.php?tag_id=".$data['tag_id'],
                                   'title' => $data['tag_title']
                               )
                );
                if (!empty($data['tag_description'])) set_meta('description', $data['tag_description']);

                $data['tag_link'] = FORUM."tags.php?tag_id=".$data['tag_id'];
                $data['tag_active'] = (isset($_GET['viewtags']) && isset($_GET['tag_id']) && $_GET['tag_id'] == $data['tag_id'] ? TRUE : FALSE);
                $this->tag_info[$data['tag_id']] = $data;

                // get forum threads.

//                if ($thread_rows > 0) {
  //                  while ($tData = dbarray ( $thread_result )) {
    //                    $this->tag_info[ $_GET['tag_id'] ]['threads'][ $tData['thread_id'] ] = $tData;
      //              }

                //}

            } else {
                redirect(FORUM."index.php");
            }

        } else {

            // Full View

            $tag_query = "SELECT * FROM ".DB_FORUM_TAGS." WHERE tag_status=1
            ".(multilang_table("FO") ? "AND tag_language='".LANGUAGE."'" : "")."
            ORDER BY tag_title ASC";
            $tag_result = dbquery( $tag_query );

            if (dbrows( $tag_result ) > 0 ) {

                while ($data = dbarray($tag_result)) {

                    $data['tag_link'] = FORUM."tags.php?tag_id=".$data['tag_id'];
                    $data['tag_active'] = (isset($_GET['viewtags']) && isset($_GET['tag_id']) && $_GET['tag_id'] == $data['tag_id'] ? TRUE : FALSE);
                    $this->tag_info[$data['tag_id']] = $data;

                    $thread_query = "SELECT * FROM ".DB_FORUM_THREADS." WHERE ".in_group('thread_tags', $data['tag_id'])." ORDER BY thread_lastpost DESC LIMIT 1";
                    $thread_result = dbquery($thread_query);
                    $thread_rows = dbrows($thread_result);
                    if ($thread_rows > 0) {
                        $tData = dbarray($thread_result);
                        $this->tag_info[$data['tag_id']]['threads'] = $tData;
                    }
                }

                // More
                $this->tag_info[0] = array(
                    'tag_id' => 0,
                    'tag_link' => FORUM."tags.php",
                    'tag_title' => fusion_get_locale("global_700")."&hellip;",
                    'tag_active' => '',
                    'tag_color' => ''
                );

            }

        }

    }

    public function get_tagOpts() {
        $tag_opts = array();
        if (!empty($this->tag_info)) {
            if (isset($this->tag_info[0])) unset($this->tag_info[0]);
            foreach($this->tag_info as $tag_data) {
                $tag_opts[$tag_data['tag_id']] = $tag_data['tag_title'];
            }
        }
        return (array) $tag_opts;
    }


}