<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: view.php
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

class ForumAdminView extends ForumAdminInterface {

    /**
     * todo: forum answering via ranks.. assign groups points.
     * */
    private $ext;
    private $forum_index;
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
        'forum_icon'               => '',
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
        // sanitize all $_GET
        $_GET['forum_id'] = (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) ? $_GET['forum_id'] : 0;
        $_GET['forum_cat'] = (isset($_GET['forum_cat']) && isnum($_GET['forum_cat'])) ? $_GET['forum_cat'] : 0;
        $_GET['forum_branch'] = (isset($_GET['forum_branch']) && isnum($_GET['forum_branch'])) ? $_GET['forum_branch'] : 0;
        $_GET['parent_id'] = (isset($_GET['parent_id']) && isnum($_GET['parent_id'])) ? $_GET['parent_id'] : 0;
        $_GET['action'] = (isset($_GET['action'])) && $_GET['action'] ? $_GET['action'] : '';
        $_GET['status'] = (isset($_GET['status'])) && $_GET['status'] ? $_GET['status'] : '';
        $this->ext = isset($_GET['parent_id']) && isnum($_GET['parent_id']) ? "&parent_id=".$_GET['parent_id'] : '';
        $this->ext .= isset($_GET['branch']) && isnum($_GET['branch']) ? "&branch=".$_GET['branch'] : '';

        // indexing hierarchy data
        $this->forum_index = self::getForumIndex();
        if (!empty($this->forum_index)) {
            $this->level = self::makeForumBreadcrumbs();
        }

        /**
         * List of actions available in this admin
         */
        self::forumJump();

        self::setForumDB();
        /**
         * Ordering actions
         */
        switch ($_GET['action']) {
            case 'mu':
                self::moveUp();
                break;
            case 'md':
                self::moveDown();
                break;
            case 'delete':
                self::validateForumRemoval();
                break;
            case 'prune':
                self::pruneForumView();
                break;
            case 'p_edit':
            case 'edit':
                $this->data = self::getForum($_GET['forum_id']);
                break;
        }
    }

    /**
     * Breadcrumb and Directory Output Handler
     *
     * @return array
     */
    private function makeForumBreadcrumbs() {
        global $aidlink;

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
                    'link'  => [FUSION_SELF.$aidlink."&parent_id=".$_name['forum_id']],
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

        // then we make an infinity recursive function to loop/break it out.
        $crumb = breadcrumb_arrays($this->forum_index, $_GET['parent_id']);
        add_breadcrumb(['link' => FUSION_SELF.$aidlink, 'title' => self::$locale['forum_root']]);
        for ($i = count($crumb['title']) - 1; $i >= 0; $i--) {
            add_breadcrumb(['link' => $crumb['link'][$i], 'title' => $crumb['title'][$i]]);
        }

        return $crumb;
    }

    /**
     * Quick navigation jump.
     */
    private function forumJump() {
        global $aidlink;
        if (isset($_POST['jp_forum'])) {
            $data['forum_id'] = form_sanitizer($_POST['forum_id'], 0, 'forum_id');
            redirect(FUSION_SELF.$aidlink."&action=p_edit&forum_id=".$data['forum_id']."&parent_id=".$_GET['parent_id']);
        }
    }

    /**
     * MYSQL update and save forum
     */
    private function setForumDB() {
        global $aidlink;

        // Save_permission
        if (isset($_POST['save_permission'])) {

            $this->data['forum_id'] = form_sanitizer($_POST['forum_id'], 0, 'forum_id');

            $this->data = self::getForum($this->data['forum_id']);

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

                addnotice('success', self::$locale['forum_notice_10']);

                if (fusion_safe()) {
                    redirect(FUSION_SELF.$aidlink.$this->ext);
                }

            }
        }

        if (isset($_POST['save_forum'])) {
            $this->data = [
                'forum_id'           => form_sanitizer($_POST['forum_id'], 0, 'forum_id'),
                'forum_name'         => form_sanitizer($_POST['forum_name'], '', 'forum_name'),
                'forum_description'  => form_sanitizer($_POST['forum_description'], '', 'forum_description'),
                'forum_cat'          => form_sanitizer($_POST['forum_cat'], 0, 'forum_cat'),
                'forum_type'         => form_sanitizer($_POST['forum_type'], '', 'forum_type'),
                'forum_language'     => form_sanitizer($_POST['forum_language'], LANGUAGE, 'forum_language'),
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
                'forum_icon'         => form_sanitizer($_POST['forum_icon'], '', 'forum_icon'),
                'forum_image'        => '',
                'forum_mods'         => "",
            ];
            $this->data['forum_alias'] = $this->data['forum_alias'] ? str_replace(' ', '-',
                $this->data['forum_alias']) : '';
            // Checks for unique forum alias
            if ($this->data['forum_alias']) {
                if ($this->data['forum_id']) {
                    $alias_check = dbcount("('alias_id')", DB_PERMALINK_ALIAS,
                        "alias_url='".$this->data['forum_alias']."' AND alias_item_id !='".$this->data['forum_id']."'");
                } else {
                    $alias_check = dbcount("('alias_id')", DB_PERMALINK_ALIAS,
                        "alias_url='".$this->data['forum_alias']."'");
                }
                if ($alias_check) {

                    fusion_stop();
                    addnotice('warning', self::$locale['forum_error_6']);

                }
            }
            // check forum name unique
            $this->data['forum_name'] = $this->checkValidForumName($this->data['forum_name'], $this->data['forum_id']);

            // Uploads or copy forum image or use back the forum image existing
            if (!empty($_FILES) && is_uploaded_file($_FILES['forum_image']['tmp_name'])) {
                $upload = form_sanitizer($_FILES['forum_image'], '', 'forum_image');
                if (!empty($upload) && $upload['error'] === UPLOAD_ERR_OK) {
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
                $this->data['forum_image'] = $type_opts[intval($_POST['forum_image_header'])].form_sanitizer($_POST['forum_image_url'], '', 'forum_image_url');
                $upload = copy_file($this->data['forum_image'], FORUM."images/");
                if ($upload['error'] == TRUE) {
                    fusion_stop();
                    addnotice('danger', self::$locale['forum_error_9']);

                } else {
                    $this->data['forum_image'] = $upload['name'];
                }
            } else {
                $this->data['forum_image'] = isset($_POST['forum_image']) ? form_sanitizer($_POST['forum_image'], '',
                    'forum_image') : "";
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
                $this->data['forum_order'] = dbresult(dbquery("SELECT MAX(forum_order) FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_cat='".$this->data['forum_cat']."'"),
                        0) + 1;
            }

            if (fusion_safe()) {

                if ($this->verifyForum($this->data['forum_id'])) {

                    $result = dbquery_order(DB_FORUMS, $this->data['forum_order'], 'forum_order',
                        $this->data['forum_id'], 'forum_id', $this->data['forum_cat'], 'forum_cat',
                        1, 'forum_language');

                    if ($result) {
                        dbquery_insert(DB_FORUMS, $this->data, 'update');
                    }

                    addnotice('success', self::$locale['forum_notice_9']);

                    redirect(FUSION_SELF.$aidlink.$this->ext);

                } else {

                    $new_forum_id = 0;

                    $result = dbquery_order(DB_FORUMS, $this->data['forum_order'], 'forum_order', FALSE, FALSE, $this->data['forum_cat'], 'forum_cat', 1, 'forum_language', 'save');

                    if ($result) {
                        dbquery_insert(DB_FORUMS, $this->data, 'save');
                        $new_forum_id = dblastid();
                    }

                    if ($this->data['forum_cat'] == 0) {

                        redirect(FUSION_SELF.$aidlink."&action=p_edit&forum_id=".$new_forum_id."&parent_id=0");

                    } else {

                        switch ($this->data['forum_type']) {
                            case '1':
                                addnotice('success', self::$locale['forum_notice_1']);
                                break;
                            case '2':
                                addnotice('success', self::$locale['forum_notice_2']);
                                break;
                            case '3':
                                addnotice('success', self::$locale['forum_notice_3']);
                                break;
                            case '4':
                                addnotice('success', self::$locale['forum_notice_4']);
                                break;
                        }

                        redirect(FUSION_SELF.$aidlink.$this->ext);

                    }
                }
            }

        }
    }

    /**
     * Move forum order up a number
     */
    private function moveUp() {
        global $aidlink;

        if (isset($_GET['forum_id']) && isnum($_GET['forum_id'])
            && isset($_GET['parent_id']) && isnum($_GET['parent_id'])
            && isset($_GET['order']) && isnum($_GET['order'])
        ) {

            $data = dbarray(dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_cat='".intval($_GET['parent_id'])."' AND forum_order='".intval($_GET['order'])."'"));

            dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".intval($data['forum_id'])."'");

            dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".intval($_GET['forum_id'])."'");

            addnotice('success', self::$locale['forum_notice_6']." ".sprintf(self::$locale['forum_notice_13'], $_GET['forum_id'], $_GET['order']));

            redirect(FUSION_SELF.$aidlink.$this->ext);
        }
    }

    /**
     * Move forum order down a number
     */
    private function moveDown() {
        global $aidlink;
        if (isset($_GET['forum_id']) && isnum($_GET['forum_id']) && isset($_GET['order']) && isnum($_GET['order'])) {
            // fetches the id of the last forum.
            $data = dbarray(dbquery("SELECT forum_id FROM ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_cat='".$_GET['parent_id']."' AND forum_order='".$_GET['order']."'"));
            $result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order-1 ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".$data['forum_id']."'");
            if ($result) {
                $result = dbquery("UPDATE ".DB_FORUMS." SET forum_order=forum_order+1 ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_id='".$_GET['forum_id']."'");
            }
            if ($result) {
                addnotice('success', self::$locale['forum_notice_7']." ".sprintf(self::$locale['forum_notice_13'],
                        $_GET['forum_id'], $_GET['order']));
                redirect(FUSION_SELF.$aidlink.$this->ext);
            }
        }
    }

    /**
     * Delete Forum.
     * If Forum has Sub Forum, deletion will give you a move form.
     * If Forum has no Sub Forum, it will prune itself and delete itself.
     */
    private function validateForumRemoval() {
        global $aidlink;

        if (isset($_GET['forum_id']) && isnum($_GET['forum_id']) && isset($_GET['forum_cat']) && isnum($_GET['forum_cat'])) {

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

                    if (self::verifyForum($action_data['forum_id'])) {

                        // Threads and Posts action
                        if (!$action_data['delete_threads'] && $action_data['threads_to_forum']) {
                            //dbquery("UPDATE ".DB_FORUM_THREADS." SET forum_id='".$action_data['threads_to_forum']."' WHERE forum_id='".$action_data['forum_id']."'");
                            dbquery("UPDATE ".DB_FORUM_POSTS." SET forum_id='".$action_data['threads_to_forum']."' WHERE forum_id='".$action_data['forum_id']."'");
                        } // wipe current forum and all threads
                        else if ($action_data['delete_threads']) {
                            // remove all threads and all posts in this forum.
                            self::pruneAttachment($action_data['forum_id']); // wipe
                            self::prunePosts($action_data['forum_id']); // wipe
                            self::pruneThreads($action_data['forum_id']); // wipe
                            self::recalculatePosts($action_data['forum_id']); // wipe

                        } else {
                            fusion_stop();
                            addnotice('danger', self::$locale['forum_notice_na']);
                        }

                        // Subforum action
                        if (!$action_data['delete_forums'] && $action_data['subforums_to_forum']) {
                            dbquery("UPDATE ".DB_FORUMS." SET forum_cat='".$action_data['subforums_to_forum']."', forum_branch='".get_hkey(DB_FORUMS,
                                    'forum_id',
                                    'forum_cat',
                                    $action_data['subforums_to_forum'])."'
                ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_cat='".$action_data['forum_id']."'");
                        } else if (!$action_data['delete_forums']) {
                            fusion_stop();
                            addnotice('danger', self::$locale['forum_notice_na']);
                        }
                    } else {
                        fusion_stop();
                        addnotice('error', self::$locale['forum_notice_na']);
                    }

                    self::pruneForums($action_data['forum_id']);

                    addnotice('info', self::$locale['forum_notice_5']);
                    redirect(FUSION_SELF.$aidlink);
                }

                self::displayForumMoveForm();

            } else {

                self::pruneAttachment($_GET['forum_id']);

                self::prunePosts($_GET['forum_id']);

                self::pruneThreads($_GET['forum_id']);

                self::recalculatePosts($_GET['forum_id']);

                dbquery("DELETE FROM ".DB_FORUMS." WHERE forum_id='".intval($_GET['forum_id'])."'");

                addnotice('info', self::$locale['forum_notice_5']);

                redirect(FUSION_SELF.$aidlink);
            }
        }
    }

    /**
     * HTML template for forum move
     */
    private function displayForumMoveForm() {
        ob_start();

        echo openmodal('move', self::$locale['forum_060'], ['static' => 1, 'class' => 'modal-md']);
        echo openform('moveform', 'post', FUSION_REQUEST);
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5'>\n";
        echo "<span class='text-dark strong'>".self::$locale['forum_052']."</span><br/>\n";
        echo "</div><div class='col-xs-12 col-sm-7 col-md-7 col-lg-7'>\n";
        echo form_select_tree('move_threads', '', $_GET['forum_id'], [
            'width'         => '100%',
            'inline'        => TRUE,
            'disable_opts'  => $_GET['forum_id'],
            'hide_disabled' => 1,
            'no_root'       => 1
        ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat', $_GET['forum_id']);
        echo form_checkbox('delete_threads', self::$locale['forum_053'], '');
        echo "</div>\n</div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5'>\n";
        echo "<span class='text-dark strong'>".self::$locale['forum_054']."</span><br/>\n"; // if you move, then need new hcat_key
        echo "</div><div class='col-xs-12 col-sm-7 col-md-7 col-lg-7'>\n";
        echo form_select_tree('move_forums', '', $_GET['forum_id'], [
            'width'         => '100%',
            'inline'        => TRUE,
            'disable_opts'  => $_GET['forum_id'],
            'hide_disabled' => 1,
            'no_root'       => 1
        ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat', $_GET['forum_id']);
        echo form_checkbox('delete_forums', self::$locale['forum_055'], '');
        echo "</div>\n</div>\n";
        echo "<div class='clearfix'>\n";
        echo form_hidden('forum_id', '', $_GET['forum_id']);
        echo form_hidden('forum_branch', '', $_GET['forum_branch']);
        echo form_button('forum_remove', self::$locale['forum_049'], 'forum_remove', [
            'class' => 'btn-sm btn-danger m-r-10',
            'icon'  => 'fa fa-trash'
        ]);
        echo "<button type='button' class='btn btn-sm btn-default' data-dismiss='modal'>".self::$locale['close']."</button>\n";
        echo "</div>\n";
        echo closeform();
        echo closemodal();
        add_to_footer(ob_get_contents());
        ob_end_clean();
    }

    private function pruneForumView() {
        $aidlink = fusion_get_aidlink();

        if ((!isset($_POST['prune_forum'])) && (isset($_GET['action']) && $_GET['action'] == "prune") && (isset($_GET['forum_id']) && isnum($_GET['forum_id']))) {
            $result = dbquery("SELECT forum_name FROM ".DB_FORUMS." WHERE forum_id='".$_GET['forum_id']."' AND forum_cat!='0'");
            if (dbrows($result) > 0) {
                $data = dbarray($result);
                opentable(self::$locale['600'].": ".$data['forum_name']);
                echo "<form name='prune_form' method='post' action='".FUSION_SELF.$aidlink."&action=prune&forum_id=".$_GET['forum_id']."'>\n";
                echo "<div style='text-align:center'>\n";
                echo self::$locale['601']."<br />\n".self::$locale['602']."<br /><br />\n";
                echo self::$locale['603']."<select name='prune_time' class='textbox'>\n";
                echo "<option value='7'>1 ".self::$locale['604']."</option>\n";
                echo "<option value='14'>2 ".self::$locale['605']."</option>\n";
                echo "<option value='30'>1 ".self::$locale['606']."</option>\n";
                echo "<option value='60'>2 ".self::$locale['607']."</option>\n";
                echo "<option value='90'>3 ".self::$locale['607']."</option>\n";
                echo "<option value='120'>4 ".self::$locale['607']."</option>\n";
                echo "<option value='150'>5 ".self::$locale['607']."</option>\n";
                echo "<option value='180' selected>6 ".self::$locale['607']."</option>\n";
                echo "</select><br /><br />\n";
                echo "<input type='submit' name='prune_forum' value='".self::$locale['600']."' class='button' / onclick=\"return confirm('".self::$locale['612']."');\">\n";
                echo "</div>\n</form>\n";
                closetable();
            }
        } else if ((isset($_POST['prune_forum'])) && (isset($_GET['action']) && $_GET['action'] == "prune") && (isset($_GET['forum_id']) && isnum($_GET['forum_id'])) && (isset($_POST['prune_time']) && isnum($_POST['prune_time']))) {
            $result = dbquery("SELECT forum_name FROM ".DB_FORUMS." WHERE forum_id='".$_GET['forum_id']."' AND forum_cat!='0'");
            if (dbrows($result)) {
                $data = dbarray($result);
                opentable(self::$locale['600'].": ".$data['forum_name']);
                echo "<div style='text-align:center'>\n<strong>".self::$locale['608']."</strong></br /></br />\n";
                $prune_time = (time() - (86400 * $_POST['prune_time']));
                // delete attachments.
                $result = dbquery("SELECT post_id, post_datestamp FROM ".DB_FORUM_POSTS." WHERE forum_id='".$_GET['forum_id']."' AND post_datestamp < '".$prune_time."'");
                $delattach = 0;
                if (dbrows($result)) {
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

                // delete posts.
                $result = dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE forum_id='".$_GET['forum_id']."' AND post_datestamp < '".$prune_time."'");
                echo self::$locale['609'].$result->affected_rows."<br />";
                echo self::$locale['610'].$delattach."<br />";

                // delete follows on threads
                $result = dbquery("SELECT thread_id,thread_lastpost FROM ".DB_FORUM_THREADS." WHERE  forum_id='".$_GET['forum_id']."' AND thread_lastpost < '".$prune_time."'");
                if (dbrows($result)) {
                    while ($data = dbarray($result)) {
                        dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id='".$data['thread_id']."'");
                    }
                }
                // delete threads
                dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE forum_id='".$_GET['forum_id']."' AND  thread_lastpost < '".$prune_time."'");

                // update last post on forum
                $result = dbquery("SELECT thread_lastpost, thread_lastuser FROM ".DB_FORUM_THREADS." WHERE forum_id='".$_GET['forum_id']."' ORDER BY thread_lastpost DESC LIMIT 0,1"); // get last thread_lastpost.
                if (dbrows($result)) {
                    $data = dbarray($result);
                    $result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$data['thread_lastpost']."', forum_lastuser='".$data['thread_lastuser']."' WHERE forum_id='".$_GET['forum_id']."'");
                } else {
                    $result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_lastuser='0' WHERE forum_id='".$_GET['forum_id']."'");
                }
                echo self::$locale['611'].$result->affected_rows."\n</div>";

                // calculate and update postcount on each specific threads -  this is the remaining.
                $result = dbquery("SELECT COUNT(post_id) AS postcount, thread_id FROM ".DB_FORUM_POSTS." WHERE forum_id='".$_GET['forum_id']."' GROUP BY thread_id");
                if (dbrows($result)) {
                    while ($data = dbarray($result)) {
                        dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_postcount='".$data['postcount']."' WHERE thread_id='".$data['thread_id']."'");
                    }
                }
                // calculate and update total combined postcount on all threads to forum
                $result = dbquery("SELECT SUM(thread_postcount) AS postcount, forum_id FROM ".DB_FORUM_THREADS." WHERE forum_id='".$_GET['forum_id']."' GROUP BY forum_id");
                if (dbrows($result)) {
                    while ($data = dbarray($result)) {
                        dbquery("UPDATE ".DB_FORUMS." SET forum_postcount='".$data['postcount']."' WHERE forum_id='".$data['forum_id']."'");
                    }
                }
                // calculate and update total threads to forum
                $result = dbquery("SELECT COUNT(thread_id) AS threadcount, forum_id FROM ".DB_FORUM_THREADS." WHERE forum_id='".$_GET['forum_id']."' GROUP BY forum_id");
                if (dbrows($result)) {
                    while ($data = dbarray($result)) {
                        dbquery("UPDATE ".DB_FORUMS." SET forum_threadcount='".$data['threadcount']."' WHERE forum_id='".$data['forum_id']."'");
                    }
                }
                // but users posts...?
                closetable();
            }
        }
    }

    /**
     * Recalculate users post count
     *
     * @param int $forum_id
     */
    public static function pruneUsersPosts($forum_id) {
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

    public function displayForumAdmin() {
        $aidlink = fusion_get_aidlink();

        if (isset($_GET['section'])) {

            switch ($_GET['section']) {
                case 'fr':
                    add_breadcrumb([
                        'link'  => INFUSIONS.'forum/admin/forums.php'.$aidlink.'&section=fr',
                        'title' => self::$locale['forum_rank_404']
                    ]);
                    break;
                case 'ft':
                    add_breadcrumb([
                        'link'  => INFUSIONS.'forum/admin/forums.php'.$aidlink.'&section=ft',
                        'title' => self::$locale['forum_tag_0100']
                    ]);
                    break;
                case 'fmd':
                    add_breadcrumb([
                        'link'  => INFUSIONS.'forum/admin/forums.php'.$aidlink.'&section=fmd',
                        'title' => self::$locale['forum_admin_004']
                    ]);
                    break;
                case 'fs':
                    add_breadcrumb([
                        'link'  => ADMIN.'settings_forum.php'.$aidlink,
                        'title' => self::$locale['admins_forum_settings']
                    ]);
                    break;
                default :
            }

        }

        opentable(self::$locale['forum_root']);

        $tab_title['title'][] = self::$locale['forum_admin_000'];
        $tab_title['id'][] = 'fm';
        $tab_title['icon'][] = 'fa fa-comment-o';
        $tab_title['title'][] = self::$locale['forum_admin_001'];
        $tab_title['id'][] = 'fr';
        $tab_title['icon'][] = 'fa fa-star';
        $tab_title['title'][] = self::$locale['forum_admin_002'];
        $tab_title['id'][] = 'ft';
        $tab_title['icon'][] = 'fa fa-tags';
        $tab_title['title'][] = self::$locale['forum_admin_004'];
        $tab_title['id'][] = 'fmd';
        $tab_title['icon'][] = 'fa fa-thumbs-up';
        $tab_title['title'][] = self::$locale['forum_admin_003'];
        $tab_title['id'][] = 'fs';
        $tab_title['icon'][] = 'fa fa-cogs';

        echo opentab($tab_title, (isset($_GET['section']) ? $_GET['section'] : 'fm'), 'forum-admin-tabs', TRUE, 'nav-tabs', 'section', ['action', 'ref', 'mood_id', 'forum_id']);
        if (isset($_GET['section'])) {

            switch ($_GET['section']) {
                case 'fr':
                    add_to_title(self::$locale['forum_admin_001']);
                    $this->viewRank()->viewRanksAdmin();
                    break;
                case 'ft':
                    add_to_title(self::$locale['forum_admin_002']);
                    $this->viewTags()->viewTagsAdmin();
                    break;
                case 'fmd':
                    add_to_title(self::$locale['forum_admin_004']);
                    $this->viewMood()->viewMoodAdmin();
                    break;
                case 'fs':
                    add_to_title(self::$locale['forum_admin_003']);
                    $this->viewSettings()->viewSettingsAdmin();
                    break;
                default:
                    redirect(INFUSIONS.'forum/admin/forums.php'.$aidlink);
            }

        } else {
            pageaccess('F');
            $this->displayForumIndex();
        }
        echo closetab();
        closetable();
    }

    /**
     * Forum Admin Main Template Output
     */
    public function displayForumIndex() {
        $res = FALSE;
        if (isset($_POST['init_forum'])) {
            $this->data['forum_name'] = self::checkValidForumName(form_sanitizer($_POST['forum_name'], '', 'forum_name'));
            if ($this->data['forum_name']) {
                $this->data['forum_cat'] = isset($_GET['parent_id']) && isnum($_GET['parent_id']) ? $_GET['parent_id'] : 0;
                $res = TRUE;
            }
        }
        if ($res == TRUE or (isset($_POST['save_forum']) && !fusion_safe()) or
            isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['forum_id']) && isnum($_GET['forum_id'])
        ) {
            $this->displayForumForm();
        } else if (isset($_GET['action']) && $_GET['action'] == 'p_edit' && isset($_GET['forum_id']) && isnum($_GET['forum_id'])) {
            self::displayForumPermissionsForm();
        } else {
            self::displayForumList();
            self::quickCreateForum();
        }
    }

    /**
     * Display Forum Form
     */
    public function displayForumForm() {

        require_once INCLUDES.'photo_functions_include.php';
        require_once INCLUDES.'infusions_include.php';

        $forum_settings = self::getForumSettings();
        $language_opts = fusion_get_enabled_languages();
        $admin_title = ($this->data['forum_id'] ? self::$locale['forum_002'] : self::$locale['forum_001']);
        add_to_title($admin_title);
        add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $admin_title]);

        if (!isset($_GET['action']) && $_GET['parent_id']) {
            $data['forum_cat'] = $_GET['parent_id'];
        }

        $type_opts = [
            '1' => self::$locale['forum_opts_001'],
            '2' => self::$locale['forum_opts_002'],
            //'3' => self::$locale['forum_opts_003'],
            '4' => self::$locale['forum_opts_004']
        ];

        $forum_image_path = FORUM."images/";

        if (isset($_POST['remove_image']) && isset($_POST['forum_id'])) {

            $data['forum_id'] = form_sanitizer($_POST['forum_id'], 0, 'forum_id');

            if ($data['forum_id']) {
                $data = self::getForum($data['forum_id']);
                if (!empty($data)) {
                    $forum_image = $forum_image_path.$data['forum_image'];

                    if (!empty($data['forum_image']) && file_exists($forum_image) && !is_dir($forum_image)) {
                        @unlink($forum_image);
                        $data['forum_image'] = '';
                    }

                    dbquery_insert(DB_FORUMS, $data, 'update');
                    addnotice('success', self::$locale['forum_notice_8']);
                    redirect(FUSION_REQUEST);
                }
            }
        }

        echo '<h4>'.$admin_title.'</h4>';

        echo openform('inputform', 'post', FUSION_REQUEST, ['enctype' => 1]);

        echo "<div class='row'>\n<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n";

        echo form_text('forum_name', self::$locale['forum_006'], $this->data['forum_name'], [
                'required'   => TRUE,
                'class'      => 'form-group-lg',
                'inline'     => FALSE,
                'error_text' => self::$locale['forum_error_1']
            ]).
            form_textarea(
                'forum_description', self::$locale['forum_007'], $this->data['forum_description'], [
                'autosize'  => TRUE,
                'type'      => 'bbcode',
                'form_name' => 'inputform',
                'preview'   => TRUE
            ]).
            form_text('forum_alias', self::$locale['forum_011'], $this->data['forum_alias']);
        echo form_select('forum_meta', self::$locale['forum_012'], $this->data['forum_meta'], [
            'tags'        => 1,
            'multiple'    => 1,
            'inner_width' => '100%',
            'width'       => '100%'
        ]);
        echo "</div><div class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>\n";

        echo "<div class='well'>\n";
        $self_id = !empty($this->data['forum_id']) ? $this->data['forum_id'] : '';

        echo form_select_tree('forum_cat', self::$locale['forum_008'], $this->data['forum_cat'], [
                'add_parent_opts' => 1,
                'disable_opts'    => $self_id,
                'hide_disabled'   => 1
            ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat', $self_id).
            form_select('forum_type', self::$locale['forum_009'], $this->data['forum_type'], ["options" => $type_opts]).
            form_select('forum_language[]', self::$locale['forum_010'], $this->data['forum_language'], [
                "options"  => $language_opts,
                'multiple' => TRUE
            ]).
            form_text('forum_order', self::$locale['forum_043'], $this->data['forum_order'], ['number' => 1]).
            form_button('save_forum', $this->data['forum_id'] ? self::$locale['forum_000a'] : self::$locale['forum_000'], self::$locale['forum_000'], ['class' => 'btn btn-sm btn-success', 'input_id' => 'save_forum2']);
        echo "</div>\n";
        echo "</div>\n</div>\n";

        echo "<div class='row'>\n<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n";
        echo form_textarea('forum_rules', self::$locale['forum_017'], $this->data['forum_rules'], [
            'autosize'  => TRUE,
            'type'      => 'bbcode',
            'form_name' => 'inputform'
        ]);

        echo form_text('forum_icon', self::$locale['forum_011a'], $this->data['forum_icon'], ['placeholder' => 'fa fa-folder']);

        if ($this->data['forum_image'] && file_exists(FORUM."images/".$this->data['forum_image'])) {

            openside('');
            echo "<div class='pull-left m-r-10'>\n";
            echo thumbnail(FORUM."images/".$this->data['forum_image'], '80px');
            echo "</div>\n<div class='overflow-hide'>\n";
            echo "<span class='strong'>".self::$locale['forum_013']."</span><br/>\n";
            $image_size = @getimagesize(FORUM."images/".$this->data['forum_image']);
            echo "<span class='text-smaller'>".sprintf(self::$locale['forum_027'], $image_size[0],
                    $image_size[1])."</span><br/>";
            echo form_hidden('forum_image', '', $this->data['forum_image']);
            echo form_button('remove_image', self::$locale['forum_028'], self::$locale['forum_028'], [
                'class' => 'btn-danger btn-sm m-t-10',
                'icon'  => 'fa fa-trash'
            ]);
            echo "</div>\n";
            closeside();
        } else {

            openside(self::$locale['forum_028a']);
            echo "<div class='pull-left m-r-15 p-r-15'>\n";
            echo form_fileinput('forum_image', '', '', [
                "upload_path"      => $forum_image_path,
                "thumbnail"        => TRUE,
                "thumbnail_folder" => $forum_image_path,
                "type"             => "image",
                "delete_original"  => TRUE,
                'inline'           => FALSE,
                "max_count"        => $forum_settings['forum_attachmax'],
                'template'         => 'thumbnail',
                'ext_tip'          => sprintf(self::$locale['forum_015'], parsebytesize($forum_settings['forum_attachmax'])),
            ]);
            echo "</div><div class='pull-left'>\n";
            echo form_select('forum_image_header', self::$locale['forum_056'], '', [
                'inline'  => FALSE,
                'options' => [
                    '0' => 'Local Server',
                    '1' => 'URL',
                ],
            ]);
            echo form_text('forum_image_url', self::$locale['forum_014'], '', [
                'placeholder' => 'images/forum/',
                'inline'      => FALSE,
                'ext_tip'     => self::$locale['forum_016']
            ]);
            echo "</div>\n";
            closeside();
        }
        echo "</div><div class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>\n";
        echo "<div class='well'>\n";
        // need to get parent category
        echo form_select_tree('forum_permissions', self::$locale['forum_025'], $this->data['forum_branch'],
            ['no_root' => TRUE, 'deactivate' => $this->data['forum_id']],
            DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');
        if ($this->data['forum_id']) {
            echo form_button('jp_forum', self::$locale['forum_029'], self::$locale['forum_029'],
                ['class' => 'btn-sm btn-default m-r-10']);
        }
        echo "</div>\n";
        echo "<div class='well'>\n";
        echo form_checkbox('forum_lock', self::$locale['forum_026'], $this->data['forum_lock'], [
                "reverse_label" => TRUE
            ]).
            form_checkbox('forum_users', self::$locale['forum_024'], $this->data['forum_users'], [
                "reverse_label" => TRUE,
            ]).
            form_checkbox('forum_quick_edit', self::$locale['forum_021'], $this->data['forum_quick_edit'], [
                "reverse_label" => TRUE,
            ]).
            form_checkbox('forum_merge', self::$locale['forum_019'], $this->data['forum_merge'], [
                "reverse_label" => TRUE,
            ]).
            form_checkbox('forum_allow_attach', self::$locale['forum_020'], $this->data['forum_allow_attach'], [
                "reverse_label" => TRUE,
            ]).
            form_checkbox('forum_allow_poll', self::$locale['forum_022'], $this->data['forum_allow_poll'], [
                "reverse_label" => TRUE,
            ]).
            form_hidden('forum_id', '', $this->data['forum_id']).
            form_hidden('forum_branch', '', $this->data['forum_branch']);
        echo "</div>\n";
        echo "</div>\n</div>\n";
        echo form_button('save_forum', $this->data['forum_id'] ? self::$locale['forum_000a'] : self::$locale['forum_000'], self::$locale['forum_000'], ['class' => 'btn-sm btn-success']);
        echo closeform();
    }

    /**
     * Permissions Form
     */
    private function displayForumPermissionsForm() {
        $data = $this->data;

        $data += [
            'forum_id'   => !empty($data['forum_id']) && isnum($data['forum_id']) ? $data['forum_id'] : 0,
            'forum_type' => !empty($data['forum_type']) ? $data['forum_type'] : '', // redirect if not exist? no.
        ];

        $_access = getusergroups();
        $access_opts['0'] = self::$locale['531'];
        foreach ($_access as $option) {
            $access_opts[$option['0']] = $option['1'];
        }
        $public_access_opts = $access_opts;
        unset($access_opts[0]); // remove public away.

        $selection = [
            self::$locale['forum_041'],
            "10 ".self::$locale['forum_points'],
            "20 ".self::$locale['forum_points'],
            "30 ".self::$locale['forum_points'],
            "40 ".self::$locale['forum_points'],
            "50 ".self::$locale['forum_points'],
            "60 ".self::$locale['forum_points'],
            "70 ".self::$locale['forum_points'],
            "80 ".self::$locale['forum_points'],
            "90 ".self::$locale['forum_points'],
            "100 ".self::$locale['forum_points']
        ];

        $options = fusion_get_groups();
        unset($options[0]); //  no public to moderate, unset
        unset($options[-101]); // no member group to moderate, unset.

        add_breadcrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_030']]);
        add_to_title(self::$locale['forum_030']);
        echo '<h4>'.self::$locale['forum_030'].'</h4>';
        echo openform('permissionsForm', 'post', FUSION_REQUEST);
        echo "<span class='strong display-inline-block m-b-20'>".self::$locale['forum_006'].": ".$data['forum_name']."</span>\n";
        openside('');
        echo "<span class='text-dark strong display-inline-block m-b-20'>".self::$locale['forum_desc_000']."</span><br/>\n";
        echo form_select('forum_access[]', self::$locale['forum_031'], $data['forum_access'], [
            'inline'   => TRUE,
            'options'  => $public_access_opts,
            'multiple' => TRUE,
        ]);
        $optionArray = ["inline" => TRUE, "options" => $access_opts, 'multiple' => TRUE,];
        echo form_select('forum_post[]', self::$locale['forum_032'], $data['forum_post'], $optionArray);
        echo form_select('forum_reply[]', self::$locale['forum_033'], $data['forum_reply'], $optionArray);
        echo form_select('forum_post_ratings[]', self::$locale['forum_039'], $data['forum_post_ratings'], $optionArray);
        closeside();
        openside('');
        echo "<span class='text-dark strong display-inline-block m-b-20'>".self::$locale['forum_desc_001']."</span><br/>\n";
        echo form_select('forum_poll[]', self::$locale['forum_036'], $data['forum_poll'], $optionArray);
        echo form_select('forum_vote[]', self::$locale['forum_037'], $data['forum_vote'], $optionArray);
        closeside();
        openside('');
        echo "<span class='text-dark strong display-inline-block m-b-20'>".self::$locale['forum_desc_004']."</span><br/>\n";
        echo form_select('forum_answer_threshold', self::$locale['forum_040'], $data['forum_answer_threshold'], [
            'options' => $selection,
            'inline'  => TRUE
        ]);
        closeside();
        openside('');
        echo "<span class='text-dark strong display-inline-block m-b-20'>".self::$locale['forum_desc_002']."</span><br/>\n";
        echo form_select('forum_attach[]', self::$locale['forum_034'], $data['forum_attach'], [
            'options'  => $access_opts,
            'inline'   => TRUE,
            'multiple' => TRUE,
        ]);
        echo form_select('forum_attach_download[]', self::$locale['forum_035'], $data['forum_attach_download'], [
            'options'  => $public_access_opts,
            'inline'   => TRUE,
            'multiple' => TRUE,
        ]);
        closeside();
        openside('');
        echo form_hidden('forum_id', '', $data['forum_id']);
        echo form_select("forum_mods[]", self::$locale['forum_desc_003'], $data['forum_mods'], [
            "multiple"  => TRUE,
            "width"     => "100%",
            "options"   => $options,
            "delimiter" => ".",
            "inline"    => TRUE
        ]);

        closeside();
        echo form_button('save_permission', self::$locale['forum_042'], self::$locale['forum_042'],
            ['class' => 'btn-primary']);

    }

    /**
     * Js menu jumper
     */
    private function displayForumJumper() {
        /* JS Menu Jumper */
        echo "<div class='pull-right'>\n";
        echo form_select_tree('forum_jump', self::$locale['forum_044'], $_GET['parent_id'], [
            'inline'       => FALSE,
            'parent_value' => self::$locale['forum_root']
        ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat');
        echo "</div>\n";
        add_to_jquery("
        $('#forum_jump').change(function() {
        location = '".FUSION_SELF.fusion_get_aidlink()."&parent_id='+$(this).val();
        });
        ");
    }

    /**
     * Forum Listing
     */
    private function displayForumList() {
        $aidlink = fusion_get_aidlink();

        $title = !empty($this->level['title']) ? sprintf(self::$locale['forum_000b'], $this->level['title'][0]) : self::$locale['forum_root'];
        add_to_title(" ".$title);

        $forum_settings = self::getForumSettings();
        $threads_per_page = $forum_settings['threads_per_page'];
        $max_rows = dbcount("('forum_id')", DB_FORUMS,
            (multilang_table("FO") ? in_group('forum_language', LANGUAGE)." AND" : '')." forum_cat='".$_GET['parent_id']."'"); // need max rows
        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_rows) ? intval($_GET['rowstart']) : 0;

        echo '<div class="clearfix">';
        self::displayForumJumper();
        echo '<h4>'.$title.'</h4>';
        echo '</div>';

        echo '<div class="m-t-25">';

        $result = dbquery("SELECT forum_id, forum_cat, forum_branch, forum_name, forum_description, forum_icon, forum_image, forum_alias, forum_type, forum_threadcount, forum_postcount, forum_order FROM
            ".DB_FORUMS." ".(multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." forum_cat='".intval($_GET['parent_id'])."'
             ORDER BY forum_order ASC LIMIT ".$_GET['rowstart'].", $threads_per_page
             ");

        $rows = dbrows($result);
        if ($rows > 0) {

            // To support entypo and font-awesome icon switching
            $has_entypo = defined('ENTYPO') && ENTYPO == TRUE;
            $has_fa = defined('FONTAWESOME') && FONTAWESOME == TRUE;

            $type_icon = [
                '1' => ($has_entypo ? 'entypo entypo-folder' : $has_fa) ? 'fa fa-folder fa-fw fa-2x' : "",
                '2' => ($has_entypo ? 'entypo entypo-message' : $has_fa) ? 'fa fa-comment-o fa-fw fa-2x' : "",
                '3' => ($has_entypo ? 'entypo entypo-link' : $has_fa) ? 'fa fa-external-link fa-fw fa-2x' : "",
                '4' => ($has_entypo ? 'entypo entypo-info-circled' : $has_fa) ? 'fa fa-lightbulb-o fa-fw fa-2x' : ""
            ];

            $ui_label = [
                "move_up"         => ($has_entypo ? "<i class='entypo entypo-arrow-up'></i>" : $has_fa) ? "<i class='fa fa-arrow-up fa-lg'></i>" : self::$locale['forum_046'],
                "move_down"       => ($has_entypo ? "<i class='entypo entypo-arrow-down'></i>" : $has_fa) ? "<i class='fa fa-arrow-down fa-lg'></i>" : self::$locale['forum_045'],
                "edit_permission" => ($has_entypo ? "<i class='entypo entypo-eye'></i>" : $has_fa) ? "<i class='fa fa-eye fa-lg'></i>" : self::$locale['forum_029'],
                "edit"            => ($has_entypo ? "<i class='entypo entypo-cog'></i>" : $has_fa) ? "<i class='fa fa-cog fa-lg'></i>" : self::$locale['forum_002'],
                "delete"          => ($has_entypo ? "<i class='entypo entypo-trash'></i>" : $has_fa) ? "<i class='fa fa-trash-o fa-lg'></i>" : self::$locale['forum_049'],
            ];

            $i = 1;
            while ($data = dbarray($result)) {
                $up = $data['forum_order'] - 1;
                $down = $data['forum_order'] + 1;

                $subforums = get_child($this->forum_index, $data['forum_id']);
                $subforums = !empty($subforums) ? count($subforums) : 0;

                echo "<div class='panel panel-default'>\n";
                echo "<div class='panel-body'>\n";
                echo "<div class='pull-left m-r-10'>\n";

                if ($forum_settings['picture_style'] == 'image' && ($data['forum_image'] && file_exists(INFUSIONS."forum/images/".$data['forum_image']))) {
                    echo thumbnail(INFUSIONS."forum/images/".$data['forum_image'], '50px');
                } else if ($forum_settings['picture_style'] == 'icon' && !empty($data['forum_icon'])) {
                    echo "<i style='font-size: 50px' class='display-inline-block ".$data['forum_icon']."'></i>\n";
                } else {
                    echo "<i style='font-size: 50px' class='display-inline-block ".$type_icon[$data['forum_type']]."'></i>\n";
                }

                echo "</div>\n";
                echo "<div class='overflow-hide'>\n";
                echo "<div class='row'>\n";
                echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
                echo "<span class='strong text-bigger'><a href='".FUSION_SELF.$aidlink."&parent_id=".$data['forum_id']."&branch=".$data['forum_branch']."'>".$data['forum_name']."</a></span><br/>".nl2br(parseubb($data['forum_description']));
                echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
                echo "<div class='pull-right'>\n";
                $upLink = FUSION_SELF.$aidlink.$this->ext."&action=mu&order=$up&forum_id=".$data['forum_id'];
                $downLink = FUSION_SELF.$aidlink.$this->ext."&action=md&order=$down&forum_id=".$data['forum_id'];

                echo ($i == 1) ? '' : "<a class='m-r-10' title='".self::$locale['forum_046']."' href='".$upLink."'>".$ui_label['move_up']."</a>";
                echo ($i == $rows) ? '' : "<a class='m-r-10' title='".self::$locale['forum_045']."' href='".$downLink."'>".$ui_label['move_down']."</a>";
                echo "<a class='m-r-10' title='".self::$locale['forum_029']."' href='".FUSION_SELF.$aidlink."&action=p_edit&forum_id=".$data['forum_id']."&parent_id=".$_GET['parent_id']."'>".$ui_label['edit_permission']."</a>"; // edit
                echo "<a class='m-r-10' title='".self::$locale['forum_002']."' href='".FUSION_SELF.$aidlink."&action=edit&forum_id=".$data['forum_id']."&parent_id=".$_GET['parent_id']."'>".$ui_label['edit']."</a>"; // edit
                echo "<a title='".self::$locale['forum_049']."' href='".FUSION_SELF.$aidlink."&action=delete&forum_id=".$data['forum_id']."&forum_cat=".$data['forum_cat']."&forum_branch=".$data['forum_branch'].$this->ext."' onclick=\"return confirm('".self::$locale['delete_notice']."');\">".$ui_label['delete']."</a>"; // delete
                echo "</div>\n";
                echo "<span class='text-dark text-smaller strong'>".self::$locale['forum_057']." ".number_format($data['forum_threadcount'])." / ".self::$locale['forum_059']." ".number_format($data['forum_postcount'])." </span>\n<br/>";

                echo "<span class='text-dark text-smaller strong'>".self::$locale['forum_058']." ".number_format($subforums)."</span>\n<br/>";
                echo "<span class='text-smaller text-dark strong'>".self::$locale['forum_051']." </span> <span class='text-smaller'>".$data['forum_alias']." </span>\n";
                echo "</div></div>\n"; // end row
                echo "</div>\n";
                echo "</div>\n</div>\n";
                $i++;
            }
            if ($max_rows > $threads_per_page) {
                $ext = (isset($_GET['parent_id'])) ? "&parent_id=".$_GET['parent_id']."&" : '';
                echo makepagenav($_GET['rowstart'], $threads_per_page, $max_rows, 3, FUSION_SELF.$aidlink.$ext);
            }
        } else {
            echo "<div class='well text-center'>".self::$locale['560']."</div>\n";
        }

        echo '</div>';
    }

    /**
     * Quick create
     */
    private function quickCreateForum() {
        echo "<hr/>\n";
        echo openform('forum_create_form', 'post', FUSION_REQUEST);
        echo "<h4>".self::$locale['forum_001']."</h4>";
        echo form_text('forum_name', self::$locale['forum_006'], '', [
            'class'       => 'form-group-lg',
            'required'    => 1,
            'inline'      => FALSE,
            'placeholder' => self::$locale['forum_018']
        ]);
        echo form_button('init_forum', self::$locale['forum_001'], 'init_forum',
            ['class' => 'btn btn-sm btn-primary']);
        echo closeform();
    }
}
