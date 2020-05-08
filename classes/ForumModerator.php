<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: mods.php
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

use PHPFusion\Infusions\Forum\Classes\Moderator\PostMod;
use PHPFusion\Infusions\Forum\Classes\Moderator\ThreadMod;

/**
 * Class Moderator
 * Forum Moderation Controller
 *
 * @package PHPFusion\Forums
 */
class ForumModerator {

    private static $instance = NULL;

    private $thread_id = 0;

    private $post_id = 0;

    private $forum_id = 0;

    private $forum_parent_id = 0;

    private $forum_branch_id = 0;

    private $form_action = '';

    private $locale = [];

    /**
     * Get Moderator Instance
     * @return object
     */
    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }
        return (object) self::$instance;
    }

    /**
     * Verify a single thread ID is a genuine and valid thread
     * - does not check forum access
     *
     * @param $thread_id
     *
     * @return bool
     */
    public function verifyThreadID(int $thread_id) {
        if (isnum($thread_id)) {
            return dbcount("(thread_id)", DB_FORUM_THREADS, "thread_id=:tid AND thread_hidden=0", [':tid' =>(int)$thread_id]);
        }
        return 0;
    }

    /**
     * Verify a single forum ID is a genuine and valid
     * - does not check forum access
     *
     * @param $forum_id
     *
     * @return bool
     */
    public function verifyForumID(int $forum_id) {
        if (isnum($forum_id)) {
            return dbcount("(forum_id)", DB_FORUMS, "forum_id=:fid", [':fid'=>(int)$forum_id]);
        }

        return 0;
    }

    /**
     * Generate iMOD const
     *
     * @param $info // need forum_mods key
     */
    public static function setForumMods($info) {
        $imod = FALSE;
        if (!defined("iMOD")) {
            if (iMEMBER && $info['forum_mods']) {
                $mod_groups = explode(".", $info['forum_mods']);
                foreach ($mod_groups as $mod_group) {
                    if (checkgroup($mod_group)) {
                        $imod = TRUE;
                    }
                }
            }
            if (iADMIN && checkrights("FO")) {
                $imod = TRUE;
            }
            if (iSUPERADMIN) {
                $imod = TRUE;
            }
            define("iMOD", $imod);
        }
    }

    /**
     * Check if the user is the forum moderator
     *
     * @param $forum_mods - $forum_data['forum_mods']
     *
     * @return bool
     */
    public static function check_forum_mods($forum_mods) {
        if (iMEMBER && $forum_mods) {
            $mod_groups = explode(".", $forum_mods);
            foreach ($mod_groups as $mod_group) {
                if (checkgroup($mod_group)) {
                    return TRUE;
                }
            }
        }
        if (iADMIN && checkrights("FO")) {
            return TRUE;
        }
        if (iSUPERADMIN) {
            return TRUE;
        }

        return FALSE;
    }

    public static function get_forum_moderators($forum_id) {
        static $users = [];
        if (empty($users)) {
            $mod_groups = [];
            $forum_mods = dbresult(dbquery("SELECT forum_mods FROM ".DB_FORUMS." WHERE forum_id=:fid", [":fid" => intval($forum_id)]), 0);
            if (!empty($forum_mods)) {
                $mod_groups = explode(".", $forum_mods);
            }
            $result = dbquery("SELECT user_id, user_name, user_level, user_groups
            FROM ".DB_USERS." WHERE user_level <= :admin ORDER BY user_name ASC", [":admin" => USER_LEVEL_MEMBER]);
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    if ($data['user_level'] === USER_LEVEL_ADMIN && in_array($data['user_rights'], explode(".", iUSER_RIGHTS))
                        || ($data['user_level'] === USER_LEVEL_SUPER_ADMIN)
                        || (array_intersect(explode(".", $data['user_groups']), $mod_groups) === TRUE)
                    ) {
                        $users[$data['user_id']] = $data['user_name'];
                    }
                }
            }
        }

        return $users;
    }

    /**
     * Parse Forum Group Moderators Links
     *
     * @param $forum_mods
     *
     * @return string
     */
    public static function displayForumMods($forum_mods) {
        $moderators = '';
        if ($forum_mods) {
            $_mgroup = explode('.', $forum_mods);
            if (!empty($_mgroup)) {
                foreach ($_mgroup as $mod_group) {
                    if ($moderators) {
                        $moderators .= ", ";
                    }
                    $moderators .= $mod_group < -USER_LEVEL_MEMBER ? "<a href='".BASEDIR."profile.php?group_id=".$mod_group."'>".getgroupname($mod_group)."</a>" : getgroupname($mod_group);
                }
            }
        }

        return (string)$moderators;
    }

    /**
     * Set a post id
     *
     * @param $value
     */
    public function setPostId($value) {
        $this->post_id = $value;
    }

    public function getPostId() {
        return (int) $this->post_id;
    }

    /**
     * Set a thread id
     *
     * @param $value
     */
    public function setThreadId($value) {
        $this->thread_id = $value;
    }

    public function getThreadID() {
        return (int)$this->thread_id;
    }

    /**
     * Set a forum id
     *
     * @param $value
     */
    public function setForumID($value) {
        $this->forum_id = (int)$value;
    }

    public function getForumID() {
        return (int)$this->forum_id;
    }

    public function getForumParentID() {
        return (int)$this->forum_parent_id;
    }

    public function getForumBranchID() {
        return (int)$this->forum_branch_id;
    }

    public function doModActions() {

        $this->locale = fusion_get_locale('', FORUM_LOCALE);

        $this->form_action = FORM_REQUEST;//FORUM.'viewthread.php?thread_id='.$this->thread_id.(isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? "&amp;rowstart=".$_GET['rowstart'] : '');

        //$rowstart = get('rowstart', FILTER_VALIDATE_INT);
        if ($this->thread_id && !$this->forum_id) {
            $forum_id_data = dbarray(dbquery("SELECT forum_id FROM ".DB_FORUM_THREADS." WHERE thread_id='".$this->thread_id."'"));
            $this->forum_id = $forum_id_data['forum_id'];
        }

        // get forum parents and branch
        list($this->forum_parent_id, $this->forum_branch_id) = dbarraynum(dbquery("SELECT forum_cat, forum_branch FROM ".DB_FORUMS." WHERE forum_id=:fid", [':fid'=>$this->forum_id]));

        // at any time when cancel is clicked, redirect to forum id.
        if (post('cancelDelete')) {
            redirect(FORUM."viewthread.php?thread_id=".intval($this->thread_id));
        }

        new ThreadMod($this);
        new PostMod($this);

        $this->showModError();
    }

    private function showModError() {
        $locale = fusion_get_locale();
        $error = get('error');
        $message = '';
        if ($error) {
            switch ($error) {
                case '1':
                    $message = $locale['error-MP001'];
                    break;
                case '2':
                    $message = $locale['error-MP002'];
                    break;
                case '3':
                    $message = $locale['forum_0307'];
                    break;
            }
        }
        if ($message) {
            opentable($locale['error-MP000']);
            echo "<div id='close-message'><div class='admin-message'>".$message."<br /><br />\n";
            echo "<a href='".FORM_REQUEST."'>".$locale['forum_0309']."</a><br />";
            echo "</div></div>\n";
            closetable();
        }
    }

}
