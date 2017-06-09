<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/admin/admin.php
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
namespace PHPFusion\Forums\Admin;

use PHPFusion\Forums\ForumServer;

// This is being extended by viewer
// A model file
abstract class ForumAdminInterface extends ForumServer {

    public static $admin_instance = NULL;
    public static $admin_rank_instance = NULL;
    public static $admin_tag_instance = NULL;
    public static $admin_settings_instance = NULL;
    public static $mood_instance = NULL;

    protected static $locale = array();

    public static function view() {
        if (empty(self::$admin_instance)) {
            self::setLocale();
            self::$admin_instance = new ForumAdminView();
        }

        return self::$admin_instance;
    }

    private static function setLocale() {
        self::$locale = fusion_get_locale("", [FORUM_ADMIN_LOCALE,
                                               SETTINGS_LOCALE,
                                               FORUM_TAGS_LOCALE,
                                               FORUM_RANKS_LOCALE
                                               ]);

    }

    public static function viewRank() {
        if (empty(self::$admin_rank_instance)) {
            self::setLocale();
            self::$admin_rank_instance = new ForumAdminRanks();
        }

        return self::$admin_rank_instance;
    }


    public static function viewTags() {
        if (empty(self::$admin_tag_instance)) {
            self::setLocale();
            self::$admin_tag_instance = new ForumAdminTags();
        }

        return self::$admin_tag_instance;
    }

    public static function viewMood() {
        if (empty(self::$mood_instance)) {
            self::setLocale();
            self::$mood_instance = new ForumAdminMood();
        }

        return self::$mood_instance;
    }


    public static function viewSettings() {
        if (empty(self::$admin_settings_instance)) {
            self::setLocale();
            self::$admin_settings_instance = new ForumAdminSettings();
        }

        return self::$admin_settings_instance;
    }

    /**
     * Delete all forum posts
     * @param      $forum_id
     * @param bool $time
     * @return string
     */
    public static function prune_posts($forum_id, $time = FALSE) {

        dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE forum_id='".$forum_id."' ".($time ? "AND post_datestamp < '".$time."'" : '')."");

    }

    /**
     * Get forum rank images
     * @return array
     */
    protected static function get_rank_images() {
        $opts = array();
        $image_files = makefilelist(RANKS."", ".|..|index.php|.svn|.DS_Store", TRUE);
        if (!empty($image_files)) {
            foreach ($image_files as $value) {
                $opts[$value] = $value;
            }
        }
        return $opts;
    }

    /**
     * Get a forum full data
     * @param $forum_id
     * @return array|bool
     */
    protected static function get_forum($forum_id) {
        if (self::verify_forum($forum_id)) {
            return dbarray(dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_id='".intval($forum_id)."' AND ".groupaccess('forum_access')." "));
        }
        return array();
    }

    /**
     * Return a valid forum name without duplicate
     * @param     $forum_name
     * @param int $forum_id
     * @return mixed
     */
    protected static function check_validForumName($forum_name, $forum_id = 0) {
        if ($forum_name) {
            if ($forum_id) {
                $name_check = dbcount("('forum_name')", DB_FORUMS, "forum_name='".$forum_name."' AND forum_id !='".$forum_id."'");
            } else {
                $name_check = dbcount("('forum_name')", DB_FORUMS, "forum_name='".$forum_name."'");
            }
            if ($name_check) {
                \defender::stop();
                addNotice('danger', self::$locale['forum_error_7']);
            } else {
                return $forum_name;
            }
        }
        return FALSE;
    }

    /**
     * Delete all forum attachments
     * @param      $forum_id
     * @param bool $time
     * @return string
     */
    protected static function prune_attachment($forum_id, $time = FALSE) {

        // delete attachments.
        $result    = dbquery("
                    SELECT post_id, post_datestamp FROM ".DB_FORUM_POSTS."
                    WHERE forum_id='".$forum_id."' ".($time ? "AND post_datestamp < '".$time."'" : '')."
                    ");
        $delattach = 0;
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                // delete all attachments
                $result2 = dbquery("SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$data['post_id']."'");
                if (dbrows($result2) != 0) {
                    $delattach++;
                    $attach = dbarray($result2);
                    @unlink(FORUM."attachments/".$attach['attach_name']);
                    dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$data['post_id']."'");
                }
            }
        }

    }

    /**
     * Delete all forum threads
     * @param      $forum_id
     * @param bool $time
     */
    protected static function prune_threads($forum_id, $time = FALSE) {
        // delete follows on threads
        $result = dbquery("SELECT thread_id, thread_lastpost FROM ".DB_FORUM_THREADS." WHERE forum_id='".$forum_id."' ".($time ? "AND thread_lastpost < '".$time."'" : '')." ");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id='".$data['thread_id']."'");
            }
        }
        // delete threads
        dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE forum_id='$forum_id' ".($time ? "AND thread_lastpost < '".$time."'" : '')." ");
    }

    /**
     * Recalculate a forum post count
     * @param $forum_id
     * @return string
     */
    protected static function recalculate_post($forum_id) {

        // update last post
        $result = dbquery("SELECT thread_lastpost, thread_lastuser FROM ".DB_FORUM_THREADS." WHERE forum_id='".$forum_id."' ORDER BY thread_lastpost DESC LIMIT 0,1"); // get last thread_lastpost.
        if (dbrows($result)) {
            $data   = dbarray($result);
            dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$data['thread_lastpost']."', forum_lastuser='".$data['thread_lastuser']."' WHERE forum_id='".$forum_id."'");
        } else {
            dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_lastuser='0' WHERE forum_id='".$forum_id."'");
        }
        // update postcount on each threads -  this is the remaining.
        $result = dbquery("SELECT COUNT(post_id) AS postcount, thread_id FROM ".DB_FORUM_POSTS." WHERE forum_id='".$forum_id."' GROUP BY thread_id");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_postcount='".$data['postcount']."' WHERE thread_id='".$data['thread_id']."'");
            }
        }
        // calculate and update total combined postcount on all threads to forum
        $result = dbquery("SELECT SUM(thread_postcount) AS postcount, forum_id FROM ".DB_FORUM_THREADS."
		WHERE forum_id='".$forum_id."' GROUP BY forum_id");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                dbquery("UPDATE ".DB_FORUMS." SET forum_postcount='".$data['postcount']."' WHERE forum_id='".$data['forum_id']."'");
            }
        }
        // calculate and update total threads to forum
        $result = dbquery("SELECT COUNT(thread_id) AS threadcount, forum_id FROM ".DB_FORUM_THREADS." WHERE forum_id='".$forum_id."' GROUP BY forum_id");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                dbquery("UPDATE ".DB_FORUMS." SET forum_threadcount='".$data['threadcount']."' WHERE forum_id='".$data['forum_id']."'");
            }
        }
    }

    /**
     * Remove the entire forum branch, image and order updated
     * @param bool $branch_data -- now as entire $this->index
     * @param bool $index
     * @param bool $time
     */
    protected function prune_forums($index = FALSE, $time = FALSE) {

        // delete forums - wipeout branch, image, order updated.
        $index = $index ? $index : 0;

        // need to refetch a new index after moving, else the id will be targetted
        $branch_data = $this->get_forum_index();
        //print_p($branch_data[$index]);
        //print_p("Index is $index");

        $index_data = dbarray(dbquery("SELECT forum_id, forum_image, forum_order FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$index."'"));

        // check if there is a sub for this node.

        if (isset($branch_data[$index])) {

            foreach ($branch_data[$index] as $forum_id) {

                $data = dbarray(dbquery("SELECT forum_id, forum_image, forum_order FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$forum_id."'"));

                if ($data['forum_image'] && file_exists(IMAGES."forum/".$data['forum_image'])) {
                    unlink(IMAGES."forum/".$data['forum_image']);
                }

                dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$forum_id."' AND forum_order>'".$data['forum_order']."'");

                dbquery("DELETE FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='$forum_id' ".($time ? "AND forum_lastpost < '".$time."'" : '')." ");

                if (isset($branch_data[$data['forum_id']])) {
                    self::prune_forums($branch_data, $data['forum_id'], $time);
                }
                // end foreach
            }
            // finally remove itself.
            if ($index_data['forum_image'] && file_exists(IMAGES."forum/".$index_data['forum_image'])) {
                unlink(IMAGES."forum/".$data['forum_image']);
                //print_p("unlinked ".$index_data['forum_image']."");
            }
            dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$index."' AND forum_order>'".$index_data['forum_order']."'");
            //print_p("deleted ".$index."");
            dbquery("DELETE FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$index."' ".($time ? "AND forum_lastpost < '".$time."'" : '')." ");
        } else {
            if ($index_data['forum_image'] && file_exists(IMAGES."forum/".$index_data['forum_image'])) {
                unlink(IMAGES."forum/".$index_data['forum_image']);
                //print_p("unlinked ".$index_data['forum_image']."");
            }
            dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$index."' AND forum_order>'".$index_data['forum_order']."'");
            //print_p("deleted ".$index."");
            dbquery("DELETE FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_id='".$index."' ".($time ? "AND forum_lastpost < '".$time."'" : '')." ");
        }
    }

    /**
     * Get forum index for hierarchy traversal
     * @return array
     */
    protected static function get_forum_index() {
        return dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
    }


}
