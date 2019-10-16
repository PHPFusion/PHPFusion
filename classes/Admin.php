<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Admin.php
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

use \PHPFusion\BreadCrumbs;

/**
 * Forum Administration Console and functions
 * Class Admin
 *
 * @package PHPFusion\Forums
 */
class Admin extends ForumServer {
    /**
     * todo: forum answering via ranks.. assign groups points.
     * */
    private $ext = '';
    private $forum_index = [];
    private $level = [];
    private $data = [
        'forum_id'                 => 0,
        'forum_cat'                => 0,
        'forum_branch'             => 0,
        'forum_name'               => '',
        'forum_type'               => '2',
        'forum_answer_threshold'   => 0,
        'forum_lock'               => 0,
        'forum_order'              => 0,
        'forum_description'        => '',
        'forum_rules'              => '',
        'forum_mods'               => '',
        'forum_access'             => USER_LEVEL_PUBLIC,
        'forum_post'               => USER_LEVEL_MEMBER,
        'forum_reply'              => USER_LEVEL_MEMBER,
        'forum_allow_poll'         => 0,
        'forum_poll'               => USER_LEVEL_MEMBER,
        'forum_vote'               => USER_LEVEL_MEMBER,
        'forum_image'              => '',
        'forum_allow_post_ratings' => 0,
        'forum_post_ratings'       => USER_LEVEL_MEMBER,
        'forum_users'              => 0,
        'forum_allow_attach'       => USER_LEVEL_MEMBER,
        'forum_attach'             => USER_LEVEL_MEMBER,
        'forum_attach_download'    => USER_LEVEL_MEMBER,
        'forum_quick_edit'         => 1,
        'forum_laspostid'          => 0,
        'forum_postcount'          => 0,
        'forum_threadcount'        => 0,
        'forum_lastuser'           => 0,
        'forum_merge'              => 0,
        'forum_language'           => LANGUAGE,
        'forum_meta'               => '',
        'forum_alias'              => ''
    ];

    public function __construct() {
        global $aidlink;

        $locale = fusion_get_locale();
        // sanitize all $_GET
        $_GET['forum_id'] = (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) ? $_GET['forum_id'] : 0;
        $_GET['forum_cat'] = (isset($_GET['forum_cat']) && isnum($_GET['forum_cat'])) ? $_GET['forum_cat'] : 0;
        $_GET['forum_branch'] = (isset($_GET['forum_branch']) && isnum($_GET['forum_branch'])) ? $_GET['forum_branch'] : 0;
        $_GET['parent_id'] = (isset($_GET['parent_id']) && isnum($_GET['parent_id'])) ? $_GET['parent_id'] : 0;
        $_GET['action'] = (isset($_GET['action'])) && $_GET['action'] ? $_GET['action'] : '';
        $_GET['status'] = (isset($_GET['status'])) && $_GET['status'] ? $_GET['status'] : '';
        $this->ext = isset($_GET['parent_id']) && isnum($_GET['parent_id']) ? "&amp;parent_id=".$_GET['parent_id'] : '';
        $this->ext .= isset($_GET['branch']) && isnum($_GET['branch']) ? "&amp;branch=".$_GET['branch'] : '';
        // indexing hierarchy data
        $this->forum_index = self::get_forum_index();
        if (!empty($this->forum_index)) {
            $this->level = self::make_forum_breadcrumbs();
        }

        /**
         * List of actions available in this admin
         */
        self::forum_jump();

        // Save_permission
        if (isset($_POST['save_permission'])) {

            $this->data['forum_id'] = form_sanitizer($_POST['forum_id'], '', 'forum_id');

            $this->data = self::get_forum($this->data['forum_id']);

            if (!empty($this->data)) {

                $this->data['forum_access'] = form_sanitizer($_POST['forum_access'], USER_LEVEL_PUBLIC, 'forum_access');
                $this->data['forum_post'] = form_sanitizer($_POST['forum_post'], USER_LEVEL_MEMBER, 'forum_post');
                $this->data['forum_reply'] = form_sanitizer($_POST['forum_reply'], USER_LEVEL_MEMBER, 'forum_reply');
                $this->data['forum_post_ratings'] = form_sanitizer($_POST['forum_post_ratings'], USER_LEVEL_MEMBER, 'forum_post_ratings');
                $this->data['forum_poll'] = form_sanitizer($_POST['forum_poll'], USER_LEVEL_MEMBER, 'forum_poll');
                $this->data['forum_vote'] = form_sanitizer($_POST['forum_vote'], USER_LEVEL_MEMBER, 'forum_vote');
                $this->data['forum_answer_threshold'] = form_sanitizer($_POST['forum_answer_threshold'], 0, 'forum_answer_threshold');
                $this->data['forum_attach'] = form_sanitizer($_POST['forum_attach'], USER_LEVEL_MEMBER, 'forum_attach');
                $this->data['forum_attach_download'] = form_sanitizer($_POST['forum_attach_download'], USER_LEVEL_PUBLIC, 'forum_attach_download');
                $this->data['forum_mods'] = isset($_POST['forum_mods']) ? form_sanitizer($_POST['forum_mods'], '', 'forum_mods') : "";

                dbquery_insert(DB_FORUMS, $this->data, 'update');

                addnotice('success', $locale['forum_notice_10']);

                if (\defender::safe()) {
                    redirect(FUSION_SELF.$aidlink.$this->ext);
                }

            }
        }

        self::set_forumDB();
        /**
         * Ordering actions
         */
        switch ($_GET['action']) {
            case 'mu':
                self::move_up();
                break;
            case 'md':
                self::move_down();
                break;
            case 'delete':
                self::validate_forum_removal();
                break;
            case 'prune':
                require_once "forums_prune.php";
                break;
            case 'edit':
                $this->data = self::get_forum($_GET['forum_id']);
                break;
            case 'p_edit':
                $this->data = self::get_forum($_GET['forum_id']);
                break;
        }
    }

    /**
     * Get forum index for hierarchy traversal
     *
     * @return array
     */
    private function get_forum_index() {
        return dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
    }

    /**
     * Breadcrumb and Directory Output Handler
     *
     * @return array
     */
    private function make_forum_breadcrumbs() {
        global $aidlink, $locale;
        /* Make an infinity traverse */
        function breadcrumb_arrays($index, $id) {
            global $aidlink;
            $crumb = [
                'link'  => [],
                'title' => []
            ];
            if (isset($index[get_parent($index, $id)])) {
                $_name = dbarray(dbquery("SELECT forum_id, forum_name FROM ".DB_FORUMS." WHERE forum_id='".intval($id)."'"));
                $crumb = [
                    'link'  => [FUSION_SELF.$aidlink."&amp;parent_id=".$_name['forum_id']],
                    'title' => [$_name['forum_name']]
                ];
                if (isset($index[get_parent($index, $id)])) {
                    if (get_parent($index, $id) == 0) {
                        return $crumb;
                    }
                    $crumb_1 = breadcrumb_arrays($index, get_parent($index, $id));
                    $crumb = array_merge_recursive($crumb, $crumb_1); // convert so can comply to Fusion Tab API.
                }
            }

            return $crumb;
        }

        // then we make a infinity recursive function to loop/break it out.
        $crumb = breadcrumb_arrays($this->forum_index, $_GET['parent_id']);
        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_SELF.$aidlink, 'title' => $locale['forum_root']]);
        for ($i = count($crumb['title']) - 1; $i >= 0; $i--) {
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => $crumb['link'][$i], 'title' => $crumb['title'][$i]]);
        }

        return $crumb;
    }

    /**
     * Quick navigation jump.
     */
    private function forum_jump() {
        global $aidlink;
        if (isset($_POST['jp_forum'])) {
            $data['forum_id'] = form_sanitizer($_POST['forum_id'], '', 'forum_id');
            redirect(FUSION_SELF.$aidlink."&amp;action=p_edit&amp;forum_id=".$data['forum_id']."&amp;parent_id=".$_GET['parent_id']);
        }
    }

    /**
     * Get a forum full data
     *
     * @param $forum_id
     *
     * @return array|bool
     */
    private function get_forum($forum_id) {
        if ($this->verify_forum($forum_id)) {
            return dbarray(dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_id='".intval($forum_id)."' AND ".groupaccess('forum_access')." "));
        }

        return [];
    }

    /**
     * MYSQL update and save forum
     */
    private function set_forumDB() {
        global $aidlink, $locale;

        if (isset($_POST['save_forum'])) {
            $this->data = [
                'forum_id'           => form_sanitizer($_POST['forum_id'], 0, 'forum_id'),
                'forum_name'         => form_sanitizer($_POST['forum_name'], '', 'forum_name'),
                'forum_description'  => form_sanitizer($_POST['forum_description'], '', 'forum_description'),
                'forum_cat'          => form_sanitizer($_POST['forum_cat'], 0, 'forum_cat'),
                'forum_type'         => form_sanitizer($_POST['forum_type'], '', 'forum_type'),
                'forum_language'     => form_sanitizer($_POST['forum_language'], '', 'forum_language'),
                'forum_alias'        => form_sanitizer($_POST['forum_alias'], '', 'forum_alias'),
                'forum_meta'         => form_sanitizer($_POST['forum_meta'], '', 'forum_meta'),
                'forum_rules'        => form_sanitizer($_POST['forum_rules'], '', 'forum_rules'),
                'forum_image_enable' => isset($_POST['forum_image_enable']) ? 1 : 0,
                'forum_merge'        => isset($_POST['forum_merge']) ? 1 : 0,
                'forum_allow_attach' => isset($_POST['forum_allow_attach']) ? 1 : 0,
                'forum_quick_edit'   => isset($_POST['forum_quick_edit']) ? 1 : 0,
                'forum_allow_poll'   => isset($_POST['forum_allow_poll']) ? 1 : 0,
                'forum_poll'         => USER_LEVEL_MEMBER,
                'forum_users'        => isset($_POST['forum_users']) ? 1 : 0,
                'forum_lock'         => isset($_POST['forum_lock']) ? 1 : 0,
                'forum_permissions'  => isset($_POST['forum_permissions']) ? form_sanitizer($_POST['forum_permissions'], 0, 'forum_permissions') : 0,
                'forum_order'        => isset($_POST['forum_order']) ? form_sanitizer($_POST['forum_order']) : '',
                'forum_branch'       => get_hkey(DB_FORUMS, 'forum_id', 'forum_cat', $this->data['forum_cat']),
                'forum_image'        => '',
                'forum_mods'         => "",
            ];
            $this->data['forum_alias'] = $this->data['forum_alias'] ? str_replace(' ', '-', $this->data['forum_alias']) : '';
            // Checks for unique forum alias
            if ($this->data['forum_alias']) {
                if ($this->data['forum_id']) {
                    $alias_check = dbcount("('alias_id')", DB_PERMALINK_ALIAS,
                        "alias_url='".$this->data['forum_alias']."' AND alias_item_id !='".$this->data['forum_id']."'");
                } else {
                    $alias_check = dbcount("('alias_id')", DB_PERMALINK_ALIAS, "alias_url='".$this->data['forum_alias']."'");
                }
                if ($alias_check) {

                    \defender::stop();
                    addNotice('warning', $locale['forum_error_6']);

                }
            }
            // check forum name unique
            $this->data['forum_name'] = self::check_validForumName($this->data['forum_name'], $this->data['forum_id']);

            // Uploads or copy forum image or use back the forum image existing
            if (!empty($_FILES) && is_uploaded_file($_FILES['forum_image']['tmp_name'])) {
                $upload = form_sanitizer($_FILES['forum_image'], '', 'forum_image');
                if ($upload['error'] == 0) {
                    if (!empty($upload['thumb1_name'])) {
                        $this->data['forum_image'] = $upload['thumb1_name'];
                    } else {
                        $this->data['forum_image'] = $upload['image_name'];
                    }
                }
            } else if (isset($_POST['forum_image_url']) && $_POST['forum_image_url'] != "") {
                require_once INCLUDES."photo_functions_include.php";
                // if forum_image_header is not empty
                $type_opts = ['0' => BASEDIR, '1' => ''];
                // the url
                $this->data['forum_image'] = $type_opts[intval($_POST['forum_image_header'])].form_sanitizer($_POST['forum_image_url'], '',
                        'forum_image_url');
                $upload = copy_file($this->data['forum_image'], FORUM."images/");
                if ($upload['error'] == TRUE) {

                    \defender::stop();
                    addNotice('danger', $locale['forum_error_9']);

                } else {
                    $this->data['forum_image'] = $upload['name'];
                }
            } else {
                $this->data['forum_image'] = isset($_POST['forum_image']) ? form_sanitizer($_POST['forum_image'], '', 'forum_image') : "";
            }

            if (!$this->data['forum_id']) {
                $this->data += [
                    'forum_access'       => USER_LEVEL_PUBLIC,
                    'forum_post'         => USER_LEVEL_MEMBER,
                    'forum_reply'        => USER_LEVEL_MEMBER,
                    'forum_post_ratings' => USER_LEVEL_MEMBER,
                    'forum_poll'         => USER_LEVEL_MEMBER,
                    'forum_vote'         => USER_LEVEL_MEMBER,
                    'forum_mods'         => "",
                ];
            }

            // Set last order
            if (!$this->data['forum_order']) {
                $this->data['forum_order'] = dbresult(dbquery("SELECT MAX(forum_order) FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE forum_language='".LANGUAGE."' AND" : "WHERE")." forum_cat='".$this->data['forum_cat']."'"),
                        0) + 1;
            }

            if (\defender::safe()) {

                if ($this->verify_forum($this->data['forum_id'])) {

                    $result = dbquery_order(DB_FORUMS, $this->data['forum_order'], 'forum_order', $this->data['forum_id'], 'forum_id',
                        $this->data['forum_cat'], 'forum_cat', 1, 'forum_language', 'update');

                    if ($result) {
                        dbquery_insert(DB_FORUMS, $this->data, 'update');
                    }

                    addNotice('success', $locale['forum_notice_9']);

                    redirect(FUSION_SELF.$aidlink.$this->ext);

                } else {

                    $new_forum_id = 0;

                    $result = dbquery_order(DB_FORUMS, $this->data['forum_order'], 'forum_order', FALSE, FALSE, $this->data['forum_cat'], 'forum_cat',
                        1, 'forum_language', 'save');

                    if ($result) {
                        dbquery_insert(DB_FORUMS, $this->data, 'save');
                        $new_forum_id = dblastid();
                    }

                    if ($this->data['forum_cat'] == 0) {

                        redirect(FUSION_SELF.$aidlink."&amp;action=p_edit&amp;forum_id=".$new_forum_id."&amp;parent_id=0");

                    } else {

                        switch ($this->data['forum_type']) {
                            case '1':
                                addNotice('success', $locale['forum_notice_1']);
                                break;
                            case '2':
                                addNotice('success', $locale['forum_notice_2']);
                                break;
                            case '3':
                                addNotice('success', $locale['forum_notice_3']);
                                break;
                            case '4':
                                addNotice('success', $locale['forum_notice_4']);
                                break;
                        }

                        redirect(FUSION_SELF.$aidlink.$this->ext);

                    }
                }
            }

        }
    }

    /**
     * Return a valid forum name without duplicate
     *
     * @param     $forum_name
     * @param int $forum_id
     *
     * @return mixed
     */
    private function check_validForumName($forum_name, $forum_id = 0) {

        $locale = fusion_get_locale();

        if ($forum_name) {
            if ($forum_id) {
                $name_check = dbcount("('forum_name')", DB_FORUMS, "forum_name='".$forum_name."' AND forum_id !='".$forum_id."'");
            } else {
                $name_check = dbcount("('forum_name')", DB_FORUMS, "forum_name='".$forum_name."'");
            }
            if ($name_check) {
                \defender::stop();
                addNotice('danger', $locale['forum_error_7']);
            } else {
                return $forum_name;
            }
        }

        return FALSE;
    }

    /**
     * Move forum order up a number
     */
    private function move_up() {
        global $aidlink, $locale;
        if (isset($_GET['forum_id']) && isnum($_GET['forum_id'])
            && isset($_GET['parent_id']) && isnum($_GET['parent_id'])
            && isset($_GET['order']) && isnum($_GET['order'])
        ) {

            $data = dbarray(dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_cat='".intval($_GET['parent_id'])."' AND forum_order='".intval($_GET['order'])."'"));

            dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".intval($data['forum_id'])."'");

            dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".intval($_GET['forum_id'])."'");

            addNotice('success', $locale['forum_notice_6']." ".sprintf($locale['forum_notice_13'], $_GET['forum_id'], $_GET['order']));

            redirect(FUSION_SELF.$aidlink.$this->ext);
        }
    }

    /**
     * Move forum order down a number
     */
    private function move_down() {
        global $aidlink, $locale;
        if (isset($_GET['forum_id']) && isnum($_GET['forum_id']) && isset($_GET['order']) && isnum($_GET['order'])) {
            // fetches the id of the last forum.
            $data = dbarray(dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_cat='".$_GET['parent_id']."' AND forum_order='".$_GET['order']."'"));
            dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".$data['forum_id']."'");
            dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".$_GET['forum_id']."'");
            addNotice('success', $locale['forum_notice_7']." ".sprintf($locale['forum_notice_13'], $_GET['forum_id'], $_GET['order']));
            redirect(FUSION_SELF.$aidlink.$this->ext);
        }
    }

    /**
     * Delete Forum.
     * If Forum has Sub Forum, deletion will give you a move form.
     * If Forum has no Sub Forum, it will prune itself and delete itself.
     *
     */
    private function validate_forum_removal() {
        global $aidlink;

        $locale = fusion_get_locale();

        if (isset($_GET['forum_id']) && isnum($_GET['forum_id'])
            && isset($_GET['forum_cat']) && isnum($_GET['forum_cat'])
        ) {

            $forum_count = dbcount("('forum_id')", DB_FORUMS, "forum_cat='".$_GET['forum_id']."'");

            if (($forum_count) >= 1) {

                // Delete forum
                /**
                 * $action_data
                 * 'forum_id' - current forum id
                 * 'forum_branch' - the branch id
                 * 'threads_to_forum' - target destination where all threads should move to
                 * 'delete_threads' - if delete threads are checked
                 * 'subforum_to_forum' - target destination where all subforums should move to
                 * 'delete_forum' - if delete all subforums are checked
                 */

                if (isset($_POST['forum_remove'])) {

                    $action_data = [
                        'forum_id'           => isset($_POST['forum_id']) ? form_sanitizer($_POST['forum_id'], 0, 'forum_id') : 0,
                        'forum_branch'       => isset($_POST['forum_branch']) ? form_sanitizer($_POST['forum_branch'], 0, 'forum_branch') : 0,
                        'threads_to_forum'   => isset($_POST['move_threads']) ? form_sanitizer($_POST['move_threads'], 0, 'move_threads') : '',
                        'delete_threads'     => isset($_POST['delete_threads']) ? 1 : 0,
                        'subforums_to_forum' => isset($_POST['move_forums']) ? form_sanitizer($_POST['move_forums'], 0, 'move_forums') : '',
                        'delete_forums'      => isset($_POST['delete_forums']) ? 1 : 0,
                    ];

                    if (self::verify_forum($action_data['forum_id'])) {

                        // Threads and Posts action
                        if (!$action_data['delete_threads'] && $action_data['threads_to_forum']) {
                            //dbquery("UPDATE ".DB_FORUM_THREADS." SET forum_id='".$action_data['threads_to_forum']."' WHERE forum_id='".$action_data['forum_id']."'");
                            dbquery("UPDATE ".DB_FORUM_POSTS." SET forum_id='".$action_data['threads_to_forum']."' WHERE forum_id='".$action_data['forum_id']."'");
                        } // wipe current forum and all threads
                        else if ($action_data['delete_threads']) {
                            // remove all threads and all posts in this forum.
                            self::prune_attachment($action_data['forum_id']); // wipe
                            self::prune_posts($action_data['forum_id']); // wipe
                            self::prune_threads($action_data['forum_id']); // wipe
                            self::recalculate_post($action_data['forum_id']); // wipe

                        } else {
                            \defender::stop();
                            addNotice('danger', $locale['forum_notice_na']);
                        }

                        // Subforum action
                        if (!$action_data['delete_forums'] && $action_data['subforums_to_forum']) {
                            dbquery("UPDATE ".DB_FORUMS." SET forum_cat='".$action_data['subforums_to_forum']."', forum_branch='".get_hkey(DB_FORUMS,
                                    'forum_id',
                                    'forum_cat',
                                    $action_data['subforums_to_forum'])."'
                ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_cat='".$action_data['forum_id']."'");
                        } else if (!$action_data['delete_forums']) {
                            \defender::stop();
                            addNotice('danger', $locale['forum_notice_na']);
                        }
                    } else {
                        \defender::stop();
                        addNotice('error', $locale['forum_notice_na']);
                    }

                    self::prune_forums($action_data['forum_id']);

                    addNotice('info', $locale['forum_notice_5']);
                    redirect(FUSION_SELF.$aidlink);
                }

                self::display_forum_move_form();

            } else {

                self::prune_attachment($_GET['forum_id']);

                self::prune_posts($_GET['forum_id']);

                self::prune_threads($_GET['forum_id']);

                self::recalculate_post($_GET['forum_id']);

                dbquery("DELETE FROM ".DB_FORUMS." WHERE forum_id='".intval($_GET['forum_id'])."'");

                addNotice('info', $locale['forum_notice_5']);

                redirect(FUSION_SELF.$aidlink);
            }
        }
    }

    /**
     * Delete all forum attachments
     *
     * @param      $forum_id
     * @param bool $time
     *
     * @return string
     */
    public static function prune_attachment($forum_id, $time = FALSE) {

        // delete attachments.
        $result = dbquery("
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
     * Delete all forum posts
     *
     * @param      $forum_id
     * @param bool $time
     *
     * @return string
     */
    public static function prune_posts($forum_id, $time = FALSE) {

        dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE forum_id='".$forum_id."' ".($time ? "AND post_datestamp < '".$time."'" : '')."");

    }

    /**
     * Delete all forum threads
     *
     * @param      $forum_id
     * @param bool $time
     */
    public static function prune_threads($forum_id, $time = FALSE) {
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
     *
     * @param $forum_id
     *
     * @return string
     */
    public static function recalculate_post($forum_id) {
        // update last post
        $result = dbquery("SELECT thread_lastpost, thread_lastuser FROM ".DB_FORUM_THREADS." WHERE forum_id='".$forum_id."' ORDER BY thread_lastpost DESC LIMIT 0,1"); // get last thread_lastpost.
        if (dbrows($result)) {
            $data = dbarray($result);
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
     *
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

        $index_data = dbarray(dbquery("SELECT forum_id, forum_image, forum_order FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".$index."'"));

        // check if there is a sub for this node.

        if (isset($branch_data[$index])) {
            $data = [];

            foreach ($branch_data[$index] as $forum_id) {

                $data = dbarray(dbquery("SELECT forum_id, forum_image, forum_order FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".$forum_id."'"));

                if ($data['forum_image'] && file_exists(IMAGES."forum/".$data['forum_image'])) {
                    unlink(IMAGES."forum/".$data['forum_image']);
                }

                dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".$forum_id."' AND forum_order>'".$data['forum_order']."'");

                dbquery("DELETE FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='$forum_id' ".($time ? "AND forum_lastpost < '".$time."'" : '')." ");

                if (isset($branch_data[$data['forum_id']])) {
                    self::prune_forums($branch_data, $time);
                }
                // end foreach
            }
            // finally remove itself.
            if ($index_data['forum_image'] && file_exists(IMAGES."forum/".$index_data['forum_image'])) {
                unlink(IMAGES."forum/".$data['forum_image']);
                //print_p("unlinked ".$index_data['forum_image']."");
            }
            dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".$index."' AND forum_order>'".$index_data['forum_order']."'");
            //print_p("deleted ".$index."");
            dbquery("DELETE FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".$index."' ".($time ? "AND forum_lastpost < '".$time."'" : '')." ");
        } else {
            if ($index_data['forum_image'] && file_exists(IMAGES."forum/".$index_data['forum_image'])) {
                unlink(IMAGES."forum/".$index_data['forum_image']);
                //print_p("unlinked ".$index_data['forum_image']."");
            }
            dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".$index."' AND forum_order>'".$index_data['forum_order']."'");
            //print_p("deleted ".$index."");
            dbquery("DELETE FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".$index."' ".($time ? "AND forum_lastpost < '".$time."'" : '')." ");
        }
    }

    /**
     * HTML template for forum move
     */
    private function display_forum_move_form() {

        ob_start();
        $locale = fusion_get_locale();
        echo openmodal('move', $locale['forum_060'], ['static' => 1, 'class' => 'modal-md']);
        echo openform('moveform', 'post', FUSION_REQUEST);
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5'>\n";
        echo "<span class='text-dark strong'>".$locale['forum_052']."</span><br/>\n";
        echo "</div><div class='col-xs-12 col-sm-7 col-md-7 col-lg-7'>\n";
        echo form_select_tree('move_threads', '', $_GET['forum_id'], [
            'width'         => '100%',
            'inline'        => TRUE,
            'disable_opts'  => $_GET['forum_id'],
            'hide_disabled' => 1,
            'no_root'       => 1
        ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat', $_GET['forum_id']);
        echo form_checkbox('delete_threads', $locale['forum_053'], '');
        echo "</div>\n</div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5'>\n";
        echo "<span class='text-dark strong'>".$locale['forum_054']."</span><br/>\n"; // if you move, then need new hcat_key
        echo "</div><div class='col-xs-12 col-sm-7 col-md-7 col-lg-7'>\n";
        echo form_select_tree('move_forums', '', $_GET['forum_id'], [
            'width'         => '100%',
            'inline'        => TRUE,
            'disable_opts'  => $_GET['forum_id'],
            'hide_disabled' => 1,
            'no_root'       => 1
        ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat', $_GET['forum_id']);
        echo form_checkbox('delete_forums', $locale['forum_055'], '');
        echo "</div>\n</div>\n";
        echo "<div class='clearfix'>\n";
        echo form_hidden('forum_id', '', $_GET['forum_id']);
        echo form_hidden('forum_branch', '', $_GET['forum_branch']);
        echo form_button('forum_remove', $locale['forum_049'], 'forum_remove', [
            'class' => 'btn-sm btn-danger m-r-10',
            'icon'  => 'fa fa-trash'
        ]);
        echo "<button type='button' class='btn btn-sm btn-default' data-dismiss='modal'>".$locale['close']."</button>\n";
        echo "</div>\n";
        echo closeform();
        echo closemodal();
        add_to_footer(ob_get_contents());
        ob_end_clean();
    }

    /**
     * Recalculate users post count
     *
     * @param $forum_id
     */
    public static function prune_users_posts($forum_id) {
        // after clean up.
        $result = dbquery("SELECT post_user FROM ".DB_FORUM_POSTS." WHERE forum_id='".$forum_id."'");
        $user_data = [];
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $user_data[$data['post_user']] = isset($user_data[$data['post_user']]) ? $user_data[$data['post_user']] + 1 : 1;
            }
        }
        if (!empty($user_data)) {
            foreach ($user_data as $user_id => $count) {
                $result = dbquery("SELECT user_post FROM ".DB_USERS." WHERE user_id='".$user_id."'");
                if (dbrows($result) > 0) {
                    $_userdata = dbarray($result);
                    $calculated_post = $_userdata['user_post'] - $count;
                    $calculated_post = $calculated_post > 1 ? $calculated_post : 0;
                    dbquery("UPDATE ".DB_USERS." SET user_post='".$calculated_post."' WHERE user_id='".$user_id."'");
                }
            }
        }
    }

    /** Prune functions */

    /**
     * Forum Admin Main Template Output
     */
    public function display_forum_admin() {
        $res = FALSE;
        if (isset($_POST['init_forum'])) {
            $this->data['forum_name'] = self::check_validForumName(form_sanitizer($_POST['forum_name'], '', 'forum_name'), 0);
            if ($this->data['forum_name']) {
                $this->data['forum_cat'] = isset($_GET['parent_id']) && isnum($_GET['parent_id']) ? $_GET['parent_id'] : 0;
                $res = TRUE;
            }
        }
        if ($res == TRUE or (isset($_POST['save_forum']) && !\defender::safe()) or
            isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['forum_id']) && isnum($_GET['forum_id'])
        ) {

            $this->display_forum_form();

        } else if (isset($_GET['action']) && $_GET['action'] == 'p_edit' && isset($_GET['forum_id']) && isnum($_GET['forum_id'])) {

            self::display_forum_permissions_form();

        } else {
            self::display_forum_jumper();
            self::display_forum_list();
            self::quick_create_forum();
        }
    }

    /**
     * Display Forum Form
     */
    public function display_forum_form() {
        require_once INCLUDES.'photo_functions_include.php';
        include INCLUDES.'infusions_include.php';

        $locale = fusion_get_locale();
        $forum_settings = get_settings('forum');
        $language_opts = fusion_get_enabled_languages();

        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['forum_001']]);
        if (!isset($_GET['action']) && $_GET['parent_id']) {
            $data['forum_cat'] = $_GET['parent_id'];
        }
        $type_opts = [
            '1' => $locale['forum_opts_001'],
            '2' => $locale['forum_opts_002'],
            '3' => $locale['forum_opts_003'],
            '4' => $locale['forum_opts_004']
        ];

        $forum_image_path = FORUM."images/";

        if (isset($_POST['remove_image']) && isset($_POST['forum_id'])) {

            $data['forum_id'] = form_sanitizer($_POST['forum_id'], '', 'forum_id');

            if ($data['forum_id']) {
                $data = self::get_forum($data['forum_id']);
                if (!empty($data)) {
                    $forum_image = $forum_image_path.$data['forum_image'];

                    if (!empty($data['forum_image']) && file_exists($forum_image) && !is_dir($forum_image)) {
                        @unlink($forum_image);
                        $data['forum_image'] = '';
                    }

                    dbquery_insert(DB_FORUMS, $data, 'update');
                    addNotice('success', $locale['forum_notice_8']);
                    redirect(FUSION_REQUEST);
                }
            }
        }

        opentable($locale['forum_001']);

        echo openform('inputform', 'post', FUSION_REQUEST, ['enctype' => 1]);

        echo "<div class='row'>\n<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n";

        echo form_text('forum_name', $locale['forum_006'], $this->data['forum_name'], [
                'required'   => 1,
                'error_text' => $locale['forum_error_1']
            ]).
            form_textarea('forum_description', $locale['forum_007'], $this->data['forum_description'], [
                'autosize'  => 1,
                'bbcode'    => 1,
                'form_name' => 'inputform'
            ]).
            form_text('forum_alias', $locale['forum_011'], $this->data['forum_alias']);

        echo "</div><div class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>\n";

        openside('');

        $self_id = $this->data['forum_id'] ? $this->data['forum_id'] : '';

        echo form_select_tree('forum_cat', $locale['forum_008'], $this->data['forum_cat'], [
                'add_parent_opts' => 1,
                'disable_opts'    => $self_id,
                'hide_disabled'   => 1
            ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat', $self_id).

            form_select('forum_type', $locale['forum_009'], $this->data['forum_type'], ["options" => $type_opts]).

            form_select('forum_language[]', $locale['forum_010'], $this->data['forum_language'], [
                "options"   => $language_opts,
                'multiple'  => TRUE,
                'delimeter' => '.'
            ]).

            form_text('forum_order', $locale['forum_043'], $this->data['forum_order'], ['number' => 1]).

            form_button('save_forum', $this->data['forum_id'] ? $locale['forum_000a'] : $locale['forum_000'], $locale['forum_000'],
                ['class' => 'btn btn-sm btn-success']);

        closeside();
        echo "</div>\n</div>\n";

        echo "<div class='row'>\n<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n";

        echo form_select('forum_meta', $locale['forum_012'], $this->data['forum_meta'], [
            'tags'     => 1,
            'multiple' => 1,
            'width'    => '100%'
        ]);
        if ($this->data['forum_image'] && file_exists(FORUM."images/".$this->data['forum_image'])) {
            openside();
            echo "<div class='pull-left m-r-10'>\n";
            echo thumbnail(FORUM."images/".$this->data['forum_image'], '80px');
            echo "</div>\n<div class='overflow-hide'>\n";
            echo "<span class='strong'>".$locale['forum_013']."</span><br/>\n";
            $image_size = @getimagesize(FORUM."images/".$this->data['forum_image']);
            echo "<span class='text-smaller'>".sprintf($locale['forum_027'], $image_size[0], $image_size[1])."</span><br/>";
            echo form_hidden('forum_image', '', $this->data['forum_image']);
            echo form_button('remove_image', $locale['forum_028'], $locale['forum_028'], [
                'class' => 'btn-danger btn-sm m-t-10',
                'icon'  => 'fa fa-trash'
            ]);
            echo "</div>\n";
            closeside();
        } else {
            $tab_title['title'][] = $locale['forum_013'];
            $tab_title['id'][] = 'fir';
            $tab_title['icon'][] = '';
            $tab_title['title'][] = $locale['forum_014'];
            $tab_title['id'][] = 'ful';
            $tab_title['icon'][] = '';
            $tab_active = tab_active($tab_title, 0);

            echo opentab($tab_title, $tab_active, 'forum-image-tab', FALSE, "m-t-20 m-b-20");
            // Upload Image
            echo opentabbody($tab_title['title'][0], 'fir', $tab_active);
            echo "<span class='display-inline-block m-t-10 m-b-10'>".sprintf($locale['forum_015'],
                    parsebytesize($forum_settings['forum_attachmax']))."</span>\n";

            $fileOptions = [
                "upload_path"      => $forum_image_path,
                "thumbnail"        => TRUE,
                "thumbnail_folder" => $forum_image_path,
                "type"             => "image",
                "delete_original"  => TRUE,
                "max_count"        => $forum_settings['forum_attachmax'],
            ];

            echo form_fileinput('forum_image', "", '', $fileOptions);

            echo closetabbody();

            // Upload image via Web Address
            echo opentabbody($tab_title['title'][1], 'ful', $tab_active);
            echo "<span class='display-inline-block m-t-10 m-b-10'>".$locale['forum_016']."</strong></span>\n";
            $header_opts = [
                '0' => 'Local Server',
                '1' => 'URL',
            ];
            echo form_select('forum_image_header', $locale['forum_056'], '', [
                'inline'  => TRUE,
                'options' => $header_opts
            ]);
            echo form_text('forum_image_url', $locale['forum_014'], '', [
                'placeholder' => 'images/forum/',
                'inline'      => TRUE
            ]);
            echo closetabbody();
            echo closetab();
        }
        echo form_textarea('forum_rules', $locale['forum_017'], $this->data['forum_rules'], [
            'autosize' => 1,
            'bbcode'   => 1
        ]);
        echo "</div><div class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>\n";
        openside('');
        // need to get parent category

        echo form_select_tree('forum_permissions', $locale['forum_025'], $this->data['forum_branch'],
            ['no_root' => 1, 'deactivate' => $this->data['forum_id'] ? TRUE : FALSE],
            DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');

        if ($this->data['forum_id']) {
            echo form_button('jp_forum', $locale['forum_029'], $locale['forum_029'], ['class' => 'btn-sm btn-default m-r-10']);
        }
        closeside();

        openside('');
        echo form_checkbox('forum_lock', $locale['forum_026'], $this->data['forum_lock'], [
                "reverse_label" => TRUE,
            ]).
            form_checkbox('forum_users', $locale['forum_024'], $this->data['forum_users'], [
                "reverse_label" => TRUE,
            ]).
            form_checkbox('forum_quick_edit', $locale['forum_021'], $this->data['forum_quick_edit'], [
                "reverse_label" => TRUE,
            ]).
            form_checkbox('forum_merge', $locale['forum_019'], $this->data['forum_merge'], [
                "reverse_label" => TRUE,
            ]).
            form_checkbox('forum_allow_attach', $locale['forum_020'], $this->data['forum_allow_attach'], [
                "reverse_label" => TRUE,
            ]).
            form_checkbox('forum_allow_poll', $locale['forum_022'], $this->data['forum_allow_poll'], [
                "reverse_label" => TRUE,
            ]).
            form_hidden('forum_id', '', $this->data['forum_id']).
            form_hidden('forum_branch', '', $this->data['forum_branch']);

        closeside();
        echo "</div>\n</div>\n";
        echo form_button('save_forum', $this->data['forum_id'] ? $locale['forum_000a'] : $locale['forum_000'], $locale['forum_000'],
            ['class' => 'btn-sm btn-success']);
        echo closeform();
        closetable();
    }

    /**
     * Permissions Form
     */
    private function display_forum_permissions_form() {
        global $locale;
        $data = $this->data;
        $data += [
            'forum_id'   => !empty($data['forum_id']) && isnum($data['forum_id']) ? $data['forum_id'] : 0,
            'forum_type' => !empty($data['forum_type']) ? $data['forum_type'] : '', // redirect if not exist? no..
        ];
        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['forum_030']]);
        opentable($locale['forum_030'], 'm-t-15');
        $_access = getusergroups();
        $access_opts['0'] = $locale['531'];
        foreach ($_access as $key => $option) {
            $access_opts[$option['0']] = $option['1'];
        }
        $public_access_opts = $access_opts;
        unset($access_opts[0]); // remove public away.
        echo openform('permissionsForm', 'post', FUSION_REQUEST);
        echo "<span class='strong display-inline-block m-b-20'>".$locale['forum_006']." : ".$data['forum_name']."</span>\n";
        openside();
        echo "<span class='text-dark strong display-inline-block m-b-20'>".$locale['forum_desc_000']."</span><br/>\n";
        echo form_select('forum_access', $locale['forum_031'], $data['forum_access'], [
            'inline'  => TRUE,
            'options' => $public_access_opts
        ]);
        $optionArray = ["inline" => TRUE, "options" => $access_opts];
        echo form_select('forum_post', $locale['forum_032'], $data['forum_post'], $optionArray);
        echo form_select('forum_reply', $locale['forum_033'], $data['forum_reply'], $optionArray);
        echo form_select('forum_post_ratings', $locale['forum_039'], $data['forum_post_ratings'], $optionArray);
        closeside();
        openside();
        echo "<span class='text-dark strong display-inline-block m-b-20'>".$locale['forum_desc_001']."</span><br/>\n";
        echo form_select('forum_poll', $locale['forum_036'], $data['forum_poll'], $optionArray);
        echo form_select('forum_vote', $locale['forum_037'], $data['forum_vote'], $optionArray);
        closeside();
        openside();
        echo "<span class='text-dark strong display-inline-block m-b-20'>".$locale['forum_desc_004']."</span><br/>\n";
        $selection = [
            $locale['forum_041'],
            "10 ".$locale['forum_points'],
            "20 ".$locale['forum_points'],
            "30 ".$locale['forum_points'],
            "40 ".$locale['forum_points'],
            "50 ".$locale['forum_points'],
            "60 ".$locale['forum_points'],
            "70 ".$locale['forum_points'],
            "80 ".$locale['forum_points'],
            "90 ".$locale['forum_points'],
            "100 ".$locale['forum_points']
        ];
        echo form_select('forum_answer_threshold', $locale['forum_040'], $data['forum_answer_threshold'], [
            'options' => $selection,
            'inline'  => TRUE
        ]);
        closeside();
        openside();
        echo "<span class='text-dark strong display-inline-block m-b-20'>".$locale['forum_desc_002']."</span><br/>\n";
        echo form_select('forum_attach', $locale['forum_034'], $data['forum_attach'], [
            'options' => $access_opts,
            'inline'  => TRUE
        ]);
        echo form_select('forum_attach_download', $locale['forum_035'], $data['forum_attach_download'], [
            'options' => $public_access_opts,
            'inline'  => TRUE
        ]);
        closeside();
        openside();
        echo form_hidden('forum_id', '', $data['forum_id']);
        $options = fusion_get_groups();
        unset($options[0]); //  no public to moderate, unset
        unset($options[-101]); // no member group to moderate, unset.
        echo form_select("forum_mods[]", $locale['forum_desc_003'], $data['forum_mods'], [
            "multiple"  => TRUE,
            "width"     => "100%",
            "options"   => $options,
            "delimiter" => ".",
            "inline"    => TRUE
        ]);
        /*
        echo "<span class='text-dark strong display-inline-block m-b-20'>".$locale['forum_desc_003']."</span><br/>\n";
        $mod_groups = getusergroups();
        $mods1_user_id = array();
        $mods1_user_name = array();
        while (list($key, $mod_group) = each($mod_groups)) {
            if ($mod_group['0'] != USER_LEVEL_PUBLIC && $mod_group['0'] != USER_LEVEL_MEMBER && $mod_group['0'] != USER_LEVEL_SUPER_ADMIN) {
                if (!preg_match("(^{$mod_group['0']}$|^{$mod_group['0']}\.|\.{$mod_group['0']}\.|\.{$mod_group['0']}$)", $data['forum_mods'])) {
                    $mods1_user_id[] = $mod_group['0'];
                    $mods1_user_name[] = $mod_group['1'];
                } else {
                    $mods2_user_id[] = $mod_group['0'];
                    $mods2_user_name[] = $mod_group['1'];
                }
            }
        }
        echo "<div class='row'>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
        echo "<select multiple='multiple' size='10' name='modlist1' id='modlist1' class='form-control textbox m-r-10' onchange=\"addUser('modlist2','modlist1');\">\n";
        for ($i = 0; $i < count($mods1_user_id); $i++) {
            echo "<option value='".$mods1_user_id[$i]."'>".$mods1_user_name[$i]."</option>\n";
        }
        echo "</select>\n";
        echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
        echo "<select multiple='multiple' size='10' name='modlist2' id='modlist2' class='form-control textbox' onchange=\"addUser('modlist1','modlist2');\">\n";
        if (isset($mods2_user_id) && is_array($mods2_user_id)) {
            for ($i = 0; $i < count($mods2_user_id); $i++) {
                echo "<option value='".$mods2_user_id[$i]."'>".$mods2_user_name[$i]."</option>\n";
            }
        }
        echo "</select>\n";
        //echo form_text('forum_mods', '', $data['forum_mods']);

        echo "</div>\n</div>\n";
        */
        closeside();
        echo form_button('save_permission', $locale['forum_042'], $locale['forum_042'], ['class' => 'btn-primary']);
        /*
        add_to_jquery(" $('#save').bind('click', function() { saveMods(); }); ");
        echo "<script type='text/javascript'>\n"."function addUser(toGroup,fromGroup) {\n";
        echo "var listLength = document.getElementById(toGroup).length;\n";
        echo "var selItem = document.getElementById(fromGroup).selectedIndex;\n";
        echo "var selText = document.getElementById(fromGroup).options[selItem].text;\n";
        echo "var selValue = document.getElementById(fromGroup).options[selItem].value;\n";
        echo "var i; var newItem = true;\n";
        echo "for (i = 0; i < listLength; i++) {\n";
        echo "if (document.getElementById(toGroup).options[i].text == selText) {\n";
        echo "newItem = false; break;\n}\n}\n"."if (newItem) {\n";
        echo "document.getElementById(toGroup).options[listLength] = new Option(selText, selValue);\n";
        echo "document.getElementById(fromGroup).options[selItem] = null;\n}\n}\n";
        echo "function saveMods() {\n"."var strValues = \"\";\n";
        echo "var boxLength = document.getElementById('modlist2').length;\n";
        echo "var count = 0;\n"."	if (boxLength != 0) {\n"."for (i = 0; i < boxLength; i++) {\n";
        echo "if (count == 0) {\n"."strValues = document.getElementById('modlist2').options[i].value;\n";
        echo "} else {\n"."strValues = strValues + \".\" + document.getElementById('modlist2').options[i].value;\n";
        echo "}\n"."count++;\n}\n}\n";
        echo "if (strValues.length == 0) {\n"."document.forms['inputform'].submit();\n";
        echo "} else {\n"."document.forms['inputform'].forum_mods.value = strValues;\n";
        echo "document.forms['inputform'].submit();\n}\n}\n</script>\n";
        */
        closetable();
    }

    /**
     * Js menu jumper
     */
    private function display_forum_jumper() {
        /* JS Menu Jumper */
        global $aidlink, $locale;
        echo "<div class='pull-right m-t-10'>\n";
        echo form_select_tree('forum_jump', '', $_GET['parent_id'], [
            'inline'       => TRUE,
            'class'        => 'pull-right',
            'parent_value' => $locale['forum_root']
        ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');
        echo "<label for='forum_jump' class='text-dark strong pull-right m-r-10 m-t-3'>".$locale['forum_044']."</label>\n";
        add_to_jquery("
    $('#forum_jump').change(function() {
        location = '".FUSION_SELF.$aidlink."&parent_id='+$(this).val();
    });
    ");
        echo "</div>\n";
    }

    /**
     * Forum Listing
     */
    private function display_forum_list() {
        global $locale, $aidlink;

        $forum_settings = get_settings('forum');

        $title = !empty($this->level['title']) ? sprintf($locale['forum_000b'], $this->level['title'][0]) : $locale['forum_root'];
        add_to_title(" ".$title);

        opentable($title);

        $threads_per_page = $forum_settings['threads_per_page'];

        $max_rows = dbcount("('forum_id')", DB_FORUMS,
            (multilang_table("FO") ? in_group('forum_language', LANGUAGE)." AND" : '')." forum_cat='".$_GET['parent_id']."'"); // need max rows

        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_rows) ? intval($_GET['rowstart']) : 0;

        $result = dbquery("SELECT forum_id, forum_cat, forum_branch, forum_name, forum_description, forum_image, forum_alias, forum_type, forum_threadcount, forum_postcount, forum_order FROM
            ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_cat='".intval($_GET['parent_id'])."'
             ORDER BY forum_order ASC LIMIT ".$_GET['rowstart'].", $threads_per_page
             ");

        $rows = dbrows($result);

        if ($rows > 0) {

            // To support entypo and font-awesome icon switching
            $has_entypo = fusion_get_settings("entypo") ? TRUE : FALSE;
            $has_fa = fusion_get_settings("fontawesome") ? TRUE : FALSE;

            $type_icon = [
                '1' => $has_entypo ? 'entypo entypo-folder' : $has_fa ? 'fa fa-folder fa-fw fa-2x' : "",
                '2' => $has_entypo ? 'entypo entypo-message' : $has_fa ? 'fa fa-comment-o fa-fw fa-2x' : "",
                '3' => $has_entypo ? 'entypo entypo-link' : $has_fa ? 'fa fa-external-link fa-fw fa-2x' : "",
                '4' => $has_entypo ? 'entypo entypo-info-circled' : $has_fa ? 'fa fa-lightbulb-o fa-fw fa-2x' : ""
            ];

            $ui_label = [
                "move_up"         => $has_entypo ? "<i class='entypo entypo-arrow-up m-r-10'></i>" : $has_fa ? "<i class='fa fa-arrow-up fa-lg m-r-10'></i>" : $locale['forum_046'],
                "move_down"       => $has_entypo ? "<i class='entypo entypo-arrow-down m-r-10'></i>" : $has_fa ? "<i class='fa fa-arrow-down fa-lg m-r-10'></i>" : $locale['forum_045'],
                "edit_permission" => $has_entypo ? "<i class='entypo entypo-eye m-r-10'></i>" : $has_fa ? "<i class='fa fa-eye fa-lg m-r-10'></i>" : $locale['forum_029'],
                "edit"            => $has_entypo ? "<i class='entypo entypo-cog m-r-10'></i>" : $has_fa ? "<i class='fa fa-cog fa-lg m-r-10'></i>" : $locale['forum_002'],
                "delete"          => $has_entypo ? "<i class='entypo entypo-trash m-r-10'></i>" : $has_fa ? "<i class='fa fa-trash-o fa-lg m-r-10'></i>" : $locale['forum_049'],
            ];

            $i = 1;
            while ($data = dbarray($result)) {
                $up = $data['forum_order'] - 1;
                $down = $data['forum_order'] + 1;
                echo "<div class='panel panel-default'>\n";
                echo "<div class='panel-body'>\n";
                echo "<div class='pull-left m-r-10'>\n";
                echo "<i class='display-inline-block text-lighter ".$type_icon[$data['forum_type']]."'></i>\n";
                echo "</div>\n";
                echo "<div class='overflow-hide'>\n";
                echo "<div class='row'>\n";
                echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
                $html2 = '';
                if ($data['forum_image'] && file_exists(INFUSIONS."forum/images/".$data['forum_image'])) {
                    echo "<div class='pull-left m-r-10'>\n".thumbnail(INFUSIONS."forum/images/".$data['forum_image'], '50px')."</div>\n";
                    echo "<div class='overflow-hide'>\n";
                    $html2 = "</div>\n";
                }
                echo "<span class='strong text-bigger'><a href='".FUSION_SELF.$aidlink."&amp;parent_id=".$data['forum_id']."&amp;branch=".$data['forum_branch']."'>".$data['forum_name']."</a></span><br/>".nl2br(parseubb($data['forum_description'])).$html2;
                echo "</div>\n<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
                echo "<div class='pull-right'>\n";
                $upLink = FUSION_SELF.$aidlink.$this->ext."&amp;action=mu&amp;order=$up&amp;forum_id=".$data['forum_id'];
                $downLink = FUSION_SELF.$aidlink.$this->ext."&amp;action=md&amp;order=$down&amp;forum_id=".$data['forum_id'];

                echo ($i == 1) ? '' : "<a title='".$locale['forum_046']."' href='".$upLink."'>".$ui_label['move_up']."</a>";
                echo ($i == $rows) ? '' : "<a title='".$locale['forum_045']."' href='".$downLink."'>".$ui_label['move_down']."</a>";
                echo "<a title='".$locale['forum_029']."' href='".FUSION_SELF.$aidlink."&amp;action=p_edit&forum_id=".$data['forum_id']."&amp;parent_id=".$_GET['parent_id']."'>".$ui_label['edit_permission']."</a>"; // edit
                echo "<a title='".$locale['forum_002']."' href='".FUSION_SELF.$aidlink."&amp;action=edit&forum_id=".$data['forum_id']."&amp;parent_id=".$_GET['parent_id']."'>".$ui_label['edit']."</a>"; // edit
                echo "<a title='".$locale['forum_049']."' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;forum_id=".$data['forum_id']."&amp;forum_cat=".$data['forum_cat']."&amp;forum_branch=".$data['forum_branch'].$this->ext."' onclick=\"return confirm('".$locale['delete_notice']."');\">".$ui_label['delete']."</a>"; // delete
                echo "</div>\n";
                echo "<span class='text-dark text-smaller strong'>".$locale['forum_057']." ".number_format($data['forum_threadcount'])." / ".$locale['forum_059']." ".number_format($data['forum_postcount'])." </span>\n<br/>";
                $subforums = get_child($this->forum_index, $data['forum_id']);
                $subforums = !empty($subforums) ? count($subforums) : 0;
                echo "<span class='text-dark text-smaller strong'>".$locale['forum_058']." ".number_format($subforums)."</span>\n<br/>";
                echo "<span class='text-smaller text-dark strong'>".$locale['forum_051']." </span> <span class='text-smaller'>".$data['forum_alias']." </span>\n";
                echo "</div></div>\n"; // end row
                echo "</div>\n";
                echo "</div>\n</div>\n";
                $i++;
            }
            if ($max_rows > $threads_per_page) {
                $ext = (isset($_GET['parent_id'])) ? "&amp;parent_id=".$_GET['parent_id']."&amp;" : '';
                echo makepagenav($_GET['rowstart'], $threads_per_page, $max_rows, 3, FUSION_SELF.$aidlink.$ext);
            }
        } else {
            echo "<div class='well text-center'>".$locale['560']."</div>\n";
        }
        closetable();
    }

    /**
     * Quick create
     */
    private function quick_create_forum() {
        global $locale;
        opentable($locale['forum_001']);
        echo openform('forum_create_form', 'post', FUSION_REQUEST);
        echo form_text('forum_name', $locale['forum_006'], '', [
            'required'    => 1,
            'inline'      => TRUE,
            'placeholder' => $locale['forum_018']
        ]);
        echo form_button('init_forum', $locale['forum_001'], 'init_forum', ['class' => 'btn btn-sm btn-primary']);
        echo closeform();
        closetable();
    }
}
