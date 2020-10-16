<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: classes/admin/view.php
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
namespace PHPFusion\Infusions\Forum\Classes\Admin;

use PHPFusion\Admins;
use PHPFusion\Tables;

class ForumAdminView extends AdminInterface {

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
        'forum_allow_comments'     => '',
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
        'forum_alias'              => '',
        'forum_show_postcount'     => 1
    ];

    private $forum_id = 0;
    private $forum_cat = 0;
    private $forum_branch = 0;
    private $parent_id = 0;
    private $action = '';
    private $status = '';
    private $aidlink = '';

    public function __construct() {
        // sanitize all $_GET
        $this->forum_id = get( 'forum_id', FILTER_VALIDATE_INT );

        $this->forum_cat = get( 'forum_cat', FILTER_VALIDATE_INT );

        $this->forum_branch = get( 'forum_branch', FILTER_VALIDATE_INT );

        $this->parent_id = get( 'parent_id', FILTER_VALIDATE_INT );

        $this->action = get( 'action' );

        $this->status = get( 'status' );

        $this->ext = $this->parent_id ? "&amp;parent_id=".$this->parent_id : '';
        $this->ext .= $this->forum_branch ? "&amp;branch=".$this->forum_branch : '';

        // indexing hierarchy data
        $this->forum_index = self::get_forum_index();

        if ( !empty( $this->forum_index ) ) {
            $this->level = self::generateBreadcrumb();
        }

        $this->aidlink = fusion_get_aidlink();
    }

    private function getBreadcrumbs( $index, $id ) {

        $crumb = [
            'link'  => [],
            'title' => []
        ];
        if ( isset( $index[ get_parent( $index, $id ) ] ) ) {
            $_name = dbarray( dbquery( "SELECT forum_id, forum_name FROM ".DB_FORUMS." WHERE forum_id='".intval( $id )."'" ) );
            $crumb = [
                'link'  => [ FUSION_SELF.$this->aidlink."&amp;parent_id=".$_name['forum_id'] ],
                'title' => [ $_name['forum_name'] ]
            ];
            if ( isset( $index[ get_parent( $index, $id ) ] ) ) {
                if ( get_parent( $index, $id ) == 0 ) {
                    return $crumb;
                }
                $crumb_1 = breadcrumb_arrays( $index, get_parent( $index, $id ) );
                $crumb = array_merge_recursive( $crumb, $crumb_1 ); // convert so can comply to Fusion Tab API.
            }
        }

        return $crumb;
    }

    /**
     * Breadcrumb and Directory Output Handler
     *
     * @return array
     */
    private function generateBreadcrumb() {

        /* Make an infinity traverse */
        // then we make a infinity recursive function to loop/break it out.
        $crumb = $this->getBreadcrumbs( $this->forum_index, $this->parent_id );
        add_breadcrumb( [ 'link' => FUSION_SELF.$this->aidlink, 'title' => self::$locale['forum_root'] ] );

        for ( $i = count( $crumb['title'] ) - 1; $i >= 0; $i-- ) {
            add_breadcrumb( [ 'link' => $crumb['link'][ $i ], 'title' => $crumb['title'][ $i ] ] );
        }

        return $crumb;
    }

    /**
     * Quick navigation jump.
     */
    private function forum_jump() {

        if ( post( 'jp_forum' ) ) {
            $data['forum_id'] = sanitizer( 'forum_id', '', 'forum_id' );
            redirect( FUSION_SELF.$this->aidlink."&amp;action=p_edit&amp;forum_id=".(int)$data['forum_id']."&amp;parent_id=$this->parent_id " );
        }
    }

    /**
     * MYSQL update and save forum
     */
    private function set_forumDB() {
        // Save_permission
        if ( post( 'save_permission' ) ) {

            $this->data['forum_id'] = sanitizer( 'forum_id', 0, 'forum_id' );

            $this->data = self::get_forum( $this->data['forum_id'] );

            if ( !empty( $this->data ) ) {

                $this->data['forum_access'] = sanitizer( 'forum_access', USER_LEVEL_PUBLIC, 'forum_access' );
                $this->data['forum_post'] = sanitizer( 'forum_post', USER_LEVEL_MEMBER, 'forum_post' );
                $this->data['forum_reply'] = sanitizer( 'forum_reply', USER_LEVEL_MEMBER, 'forum_reply' );
                $this->data['forum_post_ratings'] = sanitizer( 'forum_post_ratings', USER_LEVEL_MEMBER, 'forum_post_ratings' );
                $this->data['forum_poll'] = sanitizer( 'forum_poll', USER_LEVEL_MEMBER, 'forum_poll' );
                $this->data['forum_vote'] = sanitizer( 'forum_vote', USER_LEVEL_MEMBER, 'forum_vote' );
                $this->data['forum_answer_threshold'] = sanitizer( 'forum_answer_threshold', 0, 'forum_answer_threshold' );
                $this->data['forum_attach'] = sanitizer( 'forum_attach', USER_LEVEL_MEMBER, 'forum_attach' );
                $this->data['forum_attach_download'] = sanitizer( 'forum_attach_download', USER_LEVEL_PUBLIC, 'forum_attach_download' );
                $this->data['forum_mods'] = sanitizer( [ 'forum_mods' ], '', 'forum_mods[]' );

                dbquery_insert( DB_FORUMS, $this->data, 'update' );

                add_notice( 'success', self::$locale['forum_notice_10'] );

                if ( fusion_safe() ) {
                    redirect( FUSION_SELF.$this->aidlink.$this->ext );
                }

            }
        }

        if ( post( 'save_forum' ) ) {
            $this->data = [
                'forum_id'             => sanitizer( 'forum_id', 0, 'forum_id' ),
                'forum_name'           => sanitizer( 'forum_name', '', 'forum_name' ),
                'forum_description'    => sanitizer( 'forum_description', '', 'forum_description' ),
                'forum_cat'            => sanitizer( 'forum_cat', 0, 'forum_cat' ),
                'forum_type'           => sanitizer( 'forum_type', '', 'forum_type' ),
                'forum_language'       => sanitizer( 'forum_language', LANGUAGE, 'forum_language' ),
                'forum_alias'          => sanitizer( 'forum_alias', '', 'forum_alias' ),
                'forum_meta'           => sanitizer( 'forum_meta', '', 'forum_meta' ),
                'forum_rules'          => sanitizer( 'forum_rules', '', 'forum_rules' ),
                'forum_image_enable'   => post( 'forum_image_enable' ) ? 1 : 0,
                'forum_merge'          => post( 'forum_merge' ) ? 1 : 0,
                'forum_allow_comments' => post( 'forum_allow_comments' ) ? 1 : 0,
                'forum_allow_attach'   => post( 'forum_allow_attach' ) ? 1 : 0,
                'forum_quick_edit'     => post( 'forum_quick_edit' ) ? 1 : 0,
                'forum_allow_poll'     => post( 'forum_allow_poll' ) ? 1 : 0,
                'forum_poll'           => USER_LEVEL_MEMBER,
                'forum_users'          => post( 'forum_users' ) ? 1 : 0,
                'forum_lock'           => post( 'forum_lock' ) ? 1 : 0,
                'forum_permissions'    => post( 'forum_permissions' ) ? sanitizer( 'forum_permissions', 0, 'forum_permissions' ) : 0,
                'forum_order'          => sanitizer( 'forum_order', 0, 'forum_order' ),
                'forum_branch'         => get_hkey( DB_FORUMS, 'forum_id', 'forum_cat', $this->data['forum_cat'] ),
                'forum_image'          => '',
                'forum_mods'           => '',
                'forum_show_postcount' => post( 'forum_show_postcount' ) ? 1 : 0,
            ];
            define('STOP_REDIRECT',true);
            //print_p($this->data);

            $this->data['forum_alias'] = $this->data['forum_alias'] ? str_replace( ' ', '-',
                $this->data['forum_alias'] ) : '';
            // Checks for unique forum alias
            if ( $this->data['forum_alias'] ) {
                if ( $this->data['forum_id'] ) {
                    $alias_check = dbcount( "('alias_id')", DB_PERMALINK_ALIAS,
                        "alias_url='".$this->data['forum_alias']."' AND alias_item_id !='".$this->data['forum_id']."'" );
                } else {
                    $alias_check = dbcount( "('alias_id')", DB_PERMALINK_ALIAS,
                        "alias_url='".$this->data['forum_alias']."'" );
                }
                if ( $alias_check ) {
                    fusion_stop();
                    add_notice( 'warning', self::$locale['forum_error_6'] );

                }
            }

            // check forum name unique
            $this->data['forum_name'] = $this->checkForumName( $this->data['forum_name'], $this->data['forum_id'] );

            // Uploads or copy forum image or use back the forum image existing
            if ( !empty( $_FILES ) && is_uploaded_file( $_FILES['forum_image']['tmp_name'] ) ) {
                $upload = form_sanitizer( $_FILES['forum_image'], '', 'forum_image' );
                if ( !empty( $upload ) && $upload['error'] === UPLOAD_ERR_OK ) {
                    if ( !empty( $upload['thumb1_name'] ) ) {
                        $this->data['forum_image'] = $upload['thumb1_name'];
                    } else {
                        $this->data['forum_image'] = $upload['image_name'];
                    }
                }
            } else if ( isset( $_POST['forum_image_url'] ) && $_POST['forum_image_url'] != "" ) {

                require_once INCLUDES."photo_functions_include.php";

                // if forum_image_header is not empty
                $type_opts = [ '0' => BASEDIR, '1' => '' ];
                // the url
                $this->data['forum_image'] = $type_opts[ intval( $_POST['forum_image_header'] ) ].form_sanitizer( $_POST['forum_image_url'], '', 'forum_image_url' );
                $upload = copy_file( $this->data['forum_image'], FORUM."images/" );
                if ( $upload['error'] == TRUE ) {
                    fusion_stop();
                    add_notice( 'danger', self::$locale['forum_error_9'] );

                } else {
                    $this->data['forum_image'] = $upload['name'];
                }
            } else {
                $this->data['forum_image'] = isset( $_POST['forum_image'] ) ? form_sanitizer( $_POST['forum_image'], '',
                    'forum_image' ) : "";
            }
            if ( !$this->data['forum_id'] ) {
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
            if ( !$this->data['forum_order'] ) {
                $this->data['forum_order'] = dbresult( dbquery( "SELECT MAX(forum_order) FROM ".DB_FORUMS." ".( multilang_table( "FO" ) ? "WHERE ".in_group( 'forum_language', LANGUAGE )." AND" : "WHERE" )." forum_cat='".$this->data['forum_cat']."'" ),
                        0 ) + 1;
            }

            if ( fusion_safe() ) {

                if ( $this->verify_forum( $this->data['forum_id'] ) ) {

                    $result = dbquery_order( DB_FORUMS, $this->data['forum_order'], 'forum_order',
                        $this->data['forum_id'], 'forum_id', $this->data['forum_cat'], 'forum_cat',
                        1, 'forum_language', 'update' );

                    if ( $result ) {
                        dbquery_insert( DB_FORUMS, $this->data, 'update' );
                    }

                    add_notice( 'success', self::$locale['forum_notice_9'] );

                    redirect( FUSION_SELF.$this->aidlink.$this->ext );

                } else {

                    $new_forum_id = 0;

                    $result = dbquery_order( DB_FORUMS, $this->data['forum_order'], 'forum_order', FALSE, FALSE, $this->data['forum_cat'], 'forum_cat', 1, 'forum_language', 'save' );

                    if ( $result ) {
                        dbquery_insert( DB_FORUMS, $this->data, 'save' );
                        $new_forum_id = dblastid();
                    }

                    if ( $this->data['forum_cat'] == 0 ) {

                        redirect( FUSION_SELF.$this->aidlink."&amp;action=p_edit&amp;forum_id=".$new_forum_id."&amp;parent_id=0" );

                    } else {

                        switch ( $this->data['forum_type'] ) {
                            case '1':
                                add_notice( 'success', self::$locale['forum_notice_1'] );
                                break;
                            case '2':
                                add_notice( 'success', self::$locale['forum_notice_2'] );
                                break;
                            case '3':
                                add_notice( 'success', self::$locale['forum_notice_3'] );
                                break;
                            case '4':
                                add_notice( 'success', self::$locale['forum_notice_4'] );
                                break;
                        }

                        redirect( FUSION_SELF.$this->aidlink.$this->ext );

                    }
                }
            }

        }
    }

    /**
     * Delete Forum.
     * If Forum has Sub Forum, deletion will give you a move form.
     * If Forum has no Sub Forum, it will prune itself and delete itself.
     *
     */
    private function validate_forum_removal() {

        if ( isset( $this->forum_id ) && isnum( $this->forum_id ) && isset( $_GET['forum_cat'] ) && isnum( $_GET['forum_cat'] ) ) {

            $forum_count = dbcount( "('forum_id')", DB_FORUMS, "forum_cat='".$this->forum_id."'" );

            if ( ( $forum_count ) >= 1 ) {

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

                if ( isset( $_POST['forum_remove'] ) ) {

                    $action_data = [
                        'forum_id'           => isset( $_POST['forum_id'] ) ? form_sanitizer( $_POST['forum_id'], 0, 'forum_id' ) : 0,
                        'forum_branch'       => isset( $_POST['forum_branch'] ) ? form_sanitizer( $_POST['forum_branch'], 0, 'forum_branch' ) : 0,
                        'threads_to_forum'   => isset( $_POST['move_threads'] ) ? form_sanitizer( $_POST['move_threads'], 0, 'move_threads' ) : '',
                        'delete_threads'     => isset( $_POST['delete_threads'] ) ? 1 : 0,
                        'subforums_to_forum' => isset( $_POST['move_forums'] ) ? form_sanitizer( $_POST['move_forums'], 0, 'move_forums' ) : '',
                        'delete_forums'      => isset( $_POST['delete_forums'] ) ? 1 : 0,
                    ];

                    if ( self::verify_forum( $action_data['forum_id'] ) ) {

                        // Threads and Posts action
                        if ( !$action_data['delete_threads'] && $action_data['threads_to_forum'] ) {
                            //dbquery("UPDATE ".DB_FORUM_THREADS." SET forum_id='".$action_data['threads_to_forum']."' WHERE forum_id='".$action_data['forum_id']."'");
                            dbquery( "UPDATE ".DB_FORUM_POSTS." SET forum_id='".$action_data['threads_to_forum']."' WHERE forum_id='".$action_data['forum_id']."'" );
                        } // wipe current forum and all threads
                        else if ( $action_data['delete_threads'] ) {
                            // remove all threads and all posts in this forum.
                            self::prune_attachment( $action_data['forum_id'] ); // wipe
                            self::prune_posts( $action_data['forum_id'] ); // wipe
                            self::prune_threads( $action_data['forum_id'] ); // wipe
                            self::recalculate_post( $action_data['forum_id'] ); // wipe

                        } else {
                            \Defender::stop();
                            add_notice( 'danger', self::$locale['forum_notice_na'] );
                        }

                        // Subforum action
                        if ( !$action_data['delete_forums'] && $action_data['subforums_to_forum'] ) {
                            dbquery( "UPDATE ".DB_FORUMS." SET forum_cat='".$action_data['subforums_to_forum']."', forum_branch='".get_hkey( DB_FORUMS,
                                    'forum_id',
                                    'forum_cat',
                                    $action_data['subforums_to_forum'] )."'
                ".( multilang_table( "FO" ) ? "WHERE ".in_group( 'forum_language', LANGUAGE )." AND" : "WHERE" )." forum_cat='".$action_data['forum_id']."'" );
                        } else if ( !$action_data['delete_forums'] ) {
                            \Defender::stop();
                            add_notice( 'danger', self::$locale['forum_notice_na'] );
                        }
                    } else {
                        \Defender::stop();
                        add_notice( 'error', self::$locale['forum_notice_na'] );
                    }

                    self::prune_forums( $action_data['forum_id'] );

                    add_notice( 'info', self::$locale['forum_notice_5'] );
                    redirect( FUSION_SELF.$this->aidlink );
                }

                self::display_forum_move_form();

            } else {

                self::prune_attachment( $this->forum_id );

                self::prune_posts( $this->forum_id );

                self::prune_threads( $this->forum_id );

                self::recalculate_post( $this->forum_id );

                dbquery( "DELETE FROM ".DB_FORUMS." WHERE forum_id='".intval( $this->forum_id )."'" );

                add_notice( 'info', self::$locale['forum_notice_5'] );

                redirect( FUSION_SELF.$this->aidlink );
            }
        }
    }

    /**
     * HTML template for forum move
     */
    private function display_forum_move_form() {

        ob_start();

        echo openmodal( 'move', self::$locale['forum_060'], [ 'static' => 1, 'class_dialog' => 'modal-md' ] );
        echo openform( 'moveform', 'post', FUSION_REQUEST );
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5'>\n";
        echo "<span class='text-dark strong'>".self::$locale['forum_052']."</span><br/>\n";
        echo "</div><div class='col-xs-12 col-sm-7 col-md-7 col-lg-7'>\n";
        echo form_select_tree( 'move_threads', '', $this->forum_id, [
            'width'         => '100%',
            'inline'        => TRUE,
            'disable_opts'  => $this->forum_id,
            'hide_disabled' => 1,
            'no_root'       => 1
        ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat', $this->forum_id );
        echo form_checkbox( 'delete_threads', self::$locale['forum_053'], '' );
        echo "</div>\n</div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-5 col-md-5 col-lg-5'>\n";
        echo "<span class='text-dark strong'>".self::$locale['forum_054']."</span><br/>\n"; // if you move, then need new hcat_key
        echo "</div><div class='col-xs-12 col-sm-7 col-md-7 col-lg-7'>\n";
        echo form_select_tree( 'move_forums', '', $this->forum_id, [
            'width'         => '100%',
            'inline'        => TRUE,
            'disable_opts'  => $this->forum_id,
            'hide_disabled' => 1,
            'no_root'       => 1
        ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat', $this->forum_id );
        echo form_checkbox( 'delete_forums', self::$locale['forum_055'], '' );
        echo "</div>\n</div>\n";
        echo "<div class='clearfix'>\n";
        echo form_hidden( 'forum_id', '', $this->forum_id );
        echo form_hidden( 'forum_branch', '', $_GET['forum_branch'] );
        echo form_button( 'forum_remove', self::$locale['forum_049'], 'forum_remove', [
            'class' => 'btn-danger m-r-10',
            'icon'  => 'fa fa-trash'
        ] );
        echo "<button type='button' class='btn btn-default' data-dismiss='modal'>".self::$locale['close']."</button>\n";
        echo "</div>\n";
        echo closeform();
        echo closemodal();
        add_to_footer( ob_get_contents() );
        ob_end_clean();
    }

    private function prune_forum_view() {
        if ( ( !isset( $_POST['prune_forum'] ) ) && ( isset( $_GET['action'] ) && $_GET['action'] == "prune" ) && ( isset( $this->forum_id ) && isnum( $this->forum_id ) ) ) {
            $result = dbquery( "SELECT forum_name FROM ".DB_FORUMS." WHERE forum_id='".$this->forum_id."' AND forum_cat!='0'" );
            if ( dbrows( $result ) > 0 ) {
                $data = dbarray( $result );
                opentable( self::$locale['600'].": ".$data['forum_name'] );
                echo "<form name='prune_form' method='post' action='".FUSION_SELF.$this->aidlink."&amp;action=prune&amp;forum_id=".$this->forum_id."'>\n";
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
        } else if ( ( isset( $_POST['prune_forum'] ) ) && ( isset( $_GET['action'] ) && $_GET['action'] == "prune" ) && ( isset( $this->forum_id ) && isnum( $this->forum_id ) ) && ( isset( $_POST['prune_time'] ) && isnum( $_POST['prune_time'] ) ) ) {
            $result = dbquery( "SELECT forum_name FROM ".DB_FORUMS." WHERE forum_id='".$this->forum_id."' AND forum_cat!='0'" );
            if ( dbrows( $result ) ) {
                $data = dbarray( $result );
                opentable( self::$locale['600'].": ".$data['forum_name'] );
                echo "<div style='text-align:center'>\n<strong>".self::$locale['608']."</strong></br /></br />\n";
                $prune_time = ( time() - ( 86400 * $_POST['prune_time'] ) );
                // delete attachments.
                $result = dbquery( "SELECT post_id, post_datestamp FROM ".DB_FORUM_POSTS." WHERE forum_id='".$this->forum_id."' AND post_datestamp < '".$prune_time."'" );
                $delattach = 0;
                if ( dbrows( $result ) ) {
                    while ( $data = dbarray( $result ) ) {
                        // delete all attachments
                        $result2 = dbquery( "SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$data['post_id']."'" );
                        if ( dbrows( $result2 ) != 0 ) {
                            $delattach++;
                            $attach = dbarray( $result2 );
                            @unlink( FORUM."attachments/".$attach['attach_name'] );
                            dbquery( "DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$data['post_id']."'" );
                        }
                    }
                }

                // delete posts.
                $query = "DELETE FROM ".DB_FORUM_POSTS." WHERE forum_id='".$this->forum_id."' AND post_datestamp < '".$prune_time."'";
                dbquery( $query );
                echo self::$locale['609'].dbrows( $query )."<br />";
                echo self::$locale['610'].$delattach."<br />";

                // delete follows on threads
                $result = dbquery( "SELECT thread_id,thread_lastpost FROM ".DB_FORUM_THREADS." WHERE  forum_id='".$this->forum_id."' AND thread_lastpost < '".$prune_time."'" );
                if ( dbrows( $result ) ) {
                    while ( $data = dbarray( $result ) ) {
                        dbquery( "DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id='".$data['thread_id']."'" );
                    }
                }
                // delete threads
                dbquery( "DELETE FROM ".DB_FORUM_THREADS." WHERE forum_id='".$this->forum_id."' AND  thread_lastpost < '".$prune_time."'" );

                // update last post on forum
                $result = dbquery( "SELECT thread_lastpost, thread_lastuser FROM ".DB_FORUM_THREADS." WHERE forum_id='".$this->forum_id."' ORDER BY thread_lastpost DESC LIMIT 0,1" ); // get last thread_lastpost.
                if ( dbrows( $result ) ) {
                    $data = dbarray( $result );
                    dbquery( "UPDATE ".DB_FORUMS." SET forum_lastpost='".$data['thread_lastpost']."', forum_lastuser='".$data['thread_lastuser']."' WHERE forum_id='".$this->forum_id."'" );
                } else {
                    dbquery( "UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_lastuser='0' WHERE forum_id='".$this->forum_id."'" );
                }
                echo self::$locale['611'].dbrows( $result )."\n</div>";

                // calculate and update postcount on each specific threads -  this is the remaining.
                $result = dbquery( "SELECT COUNT(post_id) AS postcount, thread_id FROM ".DB_FORUM_POSTS." WHERE forum_id='".$this->forum_id."' GROUP BY thread_id" );
                if ( dbrows( $result ) ) {
                    while ( $data = dbarray( $result ) ) {
                        dbquery( "UPDATE ".DB_FORUM_THREADS." SET thread_postcount='".$data['postcount']."' WHERE thread_id='".$data['thread_id']."'" );
                    }
                }
                // calculate and update total combined postcount on all threads to forum
                $result = dbquery( "SELECT SUM(thread_postcount) AS postcount, forum_id FROM ".DB_FORUM_THREADS."
            WHERE forum_id='".$this->forum_id."' GROUP BY forum_id" );
                if ( dbrows( $result ) ) {
                    while ( $data = dbarray( $result ) ) {
                        dbquery( "UPDATE ".DB_FORUMS." SET forum_postcount='".$data['postcount']."' WHERE forum_id='".$data['forum_id']."'" );
                    }
                }
                // calculate and update total threads to forum
                $result = dbquery( "SELECT COUNT(thread_id) AS threadcount, forum_id FROM ".DB_FORUM_THREADS."
            WHERE forum_id='".$this->forum_id."' GROUP BY forum_id" );
                if ( dbrows( $result ) ) {
                    while ( $data = dbarray( $result ) ) {
                        dbquery( "UPDATE ".DB_FORUMS." SET forum_threadcount='".$data['threadcount']."' WHERE forum_id='".$data['forum_id']."'" );
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
     * @param $forum_id
     */
    public static function prune_users_posts( $forum_id ) {
        // after clean up.
        $result = dbquery( "SELECT post_user FROM ".DB_FORUM_POSTS." WHERE forum_id='".$forum_id."'" );
        $user_data = [];
        if ( dbrows( $result ) > 0 ) {
            while ( $data = dbarray( $result ) ) {
                $user_data[ $data['post_user'] ] = isset( $user_data[ $data['post_user'] ] ) ? $user_data[ $data['post_user'] ] + 1 : 1;
            }
        }
        if ( !empty( $user_data ) ) {
            foreach ( $user_data as $user_id => $count ) {
                $result = dbquery( "SELECT user_post FROM ".DB_USERS." WHERE user_id='".$user_id."'" );
                if ( dbrows( $result ) > 0 ) {
                    $_userdata = dbarray( $result );
                    $calculated_post = $_userdata['user_post'] - $count;
                    $calculated_post = $calculated_post > 1 ? $calculated_post : 0;
                    dbquery( "UPDATE ".DB_USERS." SET user_post='".$calculated_post."' WHERE user_id='".$user_id."'" );
                }
            }
        }
    }

    public function display_forum_admin() {
        $aidlink = fusion_get_aidlink();
        // Push sections to new admin api
        $admin = Admins::getInstance();
        $admin->addAdminPage( 'F', self::$locale['forum_admin_000'], 'FM', FORUM.'admin/forums.php'.$aidlink.'&amp;section=fm', '<i class="far fa-comment fa-fw m-r-10"></i>' );
        $admin->addAdminPage( 'F', self::$locale['forum_admin_001'], 'FR', FORUM.'admin/forums.php'.$aidlink.'&amp;section=fr', '<i class="far fa-star fa-fw m-r-10"></i>' );
        $admin->addAdminPage( 'F', self::$locale['forum_admin_002'], 'FT', FORUM.'admin/forums.php'.$aidlink.'&amp;section=ft', '<i class="fa fa-tags fa-fw m-r-10"></i>' );
        $admin->addAdminPage( 'F', self::$locale['forum_admin_004'], 'FMD', FORUM.'admin/forums.php'.$aidlink.'&amp;section=fmd', '<i class="fa fa-smile-beam fa-fw m-r-10"></i>' );
        $admin->addAdminPage( 'F', self::$locale['forum_admin_003'], 'FS', FORUM.'admin/forums.php'.$aidlink.'&amp;section=fs', '<i class="fa fa-wrench fa-fw m-r-10"></i>' );

        $section = get( 'section' );
        switch ( $section ) {
            case 'fr':
                // FORUM RANKS ADMIN
                add_breadcrumb( [
                    'link'  => INFUSIONS.'forum/admin/forums.php'.$this->aidlink.'&section=fr',
                    'title' => self::$locale['forum_rank_404']
                ] );
                opentable( self::$locale['forum_rank_404'].'<small class="m-l-15"><i class="fas fa-info-circle" title="'.self::$locale['forum_rank_0100'].'"></i></small>' );
                $this->viewRank()->viewRanksAdmin();
                closetable();
                break;
            case 'ft':
                // FORUM TAGS ADMIN
                add_breadcrumb( [
                    'link'  => INFUSIONS.'forum/admin/forums.php'.$this->aidlink.'&section=ft',
                    'title' => self::$locale['forum_tag_0100']
                ] );
                opentable( self::$locale['forum_tag_0100'].'<small class="m-l-15"><i class="fas fa-info-circle" title="'.self::$locale['forum_tag_0101'].'"></i></small>' );
                $this->viewTags()->viewTagsAdmin();
                closetable();
                break;
            case 'fmd':
                // FORUM MOOD ADMIN
                add_breadcrumb( [
                    'link'  => INFUSIONS.'forum/admin/forums.php'.$this->aidlink.'&section=fmd',
                    'title' => self::$locale['forum_admin_004']
                ] );
                opentable( self::$locale['forum_admin_004'].'<small class="m-l-15"><i class="fas fa-info-circle" title="'.self::$locale['forum_090'].'"></i></small>' );
                $this->viewMood()->viewMoodAdmin();
                closetable();
                break;
            case 'fs':
                // Forum settings admin
                add_breadcrumb( [
                    'link'  => ADMIN.'settings_forum.php'.$this->aidlink,
                    'title' => self::$locale['forum_settings']
                ] );
                opentable( self::$locale['forum_settings'] );
                $this->viewSettings()->viewSettingsAdmin();
                closetable();
                break;
            default:

                /**
                 * List of actions available in this admin
                 */
                self::forum_jump();

                self::set_forumDB();

                /**
                 * Ordering actions
                 */
                switch ( $this->action ) {
                    case 'delete':
                        self::validate_forum_removal();
                        break;
                    case 'prune':
                        self::prune_forum_view();
                        break;
                    case 'edit':
                    case 'p_edit':
                        $this->data = self::get_forum( $this->forum_id );
                        break;
                }

                $append_title = '';
                if ( get( 'action' ) == 'edit' ) {
                    $append_title = self::$locale['global_201'].self::$locale['forum_002'];
                } else {
                    if ( post( 'forum_name' ) ) {
                        $append_title = self::$locale['global_201'].self::$locale['forum_001'];
                    }
                }

                opentable( self::$locale['forum_root'].$append_title );
                $this->forumIndex();
                closetable();
        }
    }

    /**
     * Forum Admin Main Template Output
     */
    public function forumIndex() {
        pageAccess( 'F' );
        $res = FALSE;

        if ( post( 'init_forum' ) ) {
            $this->data['forum_name'] = $this->checkForumName( sanitizer( 'forum_name', '', 'forum_name' ), 0 );
            if ( $this->data['forum_name'] ) {
                $this->data['forum_cat'] = isset( $this->parent_id ) && isnum( $this->parent_id ) ? $this->parent_id : 0;
                $res = TRUE;
            }
        }

        if ( $res == TRUE or ( post( 'save_forum' ) && !fusion_safe() ) or get( 'action' ) == 'edit' && isset( $this->forum_id ) && isnum( $this->forum_id ) ) {

            $this->display_forum_form();

        } else if ( get( 'action' ) == 'p_edit' && isset( $this->forum_id ) && isnum( $this->forum_id ) ) {

            self::display_forum_permissions_form();

        } else {

            self::display_forum_list();

            self::quick_create_forum();
        }
    }

    /**
     * Display Forum Form
     */
    public function display_forum_form() {

        $forum_settings = self::get_forum_settings();

        $language_opts = fusion_get_enabled_languages();

        $admin_title = ( $this->data['forum_id'] ? self::$locale['forum_002'] : self::$locale['forum_001'] );

        add_breadcrumb( [ 'link' => FUSION_REQUEST, 'title' => $admin_title ] );

        if ( !get( 'action' ) && $this->parent_id ) {
            $data['forum_cat'] = $this->parent_id;
        }

        $type_opts = [
            '1' => self::$locale['forum_opts_001'],
            '2' => self::$locale['forum_opts_002'],
            '3' => self::$locale['forum_opts_003'],
            '4' => self::$locale['forum_opts_004']
        ];

        $forum_image_path = FORUM."images/";

        if ( post( 'remove_image' ) && post( 'forum_id', FILTER_VALIDATE_INT ) ) {

            $data['forum_id'] = sanitizer( 'forum_id', '', 'forum_id' );

            if ( $data['forum_id'] ) {
                $data = self::get_forum( $data['forum_id'] );
                if ( !empty( $data ) ) {
                    $forum_image = $forum_image_path.$data['forum_image'];

                    if ( !empty( $data['forum_image'] ) && file_exists( $forum_image ) && !is_dir( $forum_image ) ) {
                        @unlink( $forum_image );
                        $data['forum_image'] = '';
                    }

                    dbquery_insert( DB_FORUMS, $data, 'update' );
                    add_notice( 'success', self::$locale['forum_notice_8'] );
                    redirect( FUSION_REQUEST );
                }
            }
        }

        $tab['title'][] = $admin_title;
        $tab['id'][] = 'forum-form';
        if ( $this->forum_id ) {
            $tab['title'][] = self::$locale['forum_029'];
            $tab['id'][] = 'permissions';
        }

        $tab_active = tab_active( $tab, 'forum-form', 'form' );

        echo opentab( $tab, $tab_active, 'forum-admin', TRUE, '', 'form' );

        if ( get( 'form' ) == 'permissions' ) {

            self::display_forum_permissions_form();

        } else {

            echo openform( 'inputform', 'post', FUSION_REQUEST, [ 'enctype' => 1 ] );
            echo "<div class='".grid_row()."'>\n<div class='".grid_column_size( 100, 70, 70, 80 )."'>\n";
            echo form_text( 'forum_name', self::$locale['forum_006'], $this->data['forum_name'], [
                    'required'   => TRUE,
                    'class'      => 'form-group-lg',
                    'inline'     => FALSE,
                    'error_text' => self::$locale['forum_error_1']
                ] ).
                form_textarea(
                    'forum_description', self::$locale['forum_007'], $this->data['forum_description'], [
                    'autosize'  => TRUE,
                    'type'      => 'bbcode',
                    'form_name' => 'inputform',
                    'preview'   => TRUE
                ] ).
                form_text( 'forum_alias', self::$locale['forum_011'], $this->data['forum_alias'] );
            echo form_select( 'forum_meta', self::$locale['forum_012'], $this->data['forum_meta'], [
                'tags'        => 1,
                'multiple'    => 1,
                'inner_width' => '100%',
                'width'       => '100%'
            ] );
            echo "</div><div class='".grid_column_size( 100, 30, 30, 20 )."'>\n";
            echo "<div class='well'>\n";
            $self_id = $this->data['forum_id'] ? $this->data['forum_id'] : '';
            echo form_select_tree( 'forum_cat', self::$locale['forum_008'], $this->data['forum_cat'], [
                    'add_parent_opts' => 1,
                    'disable_opts'    => $self_id,
                    'hide_disabled'   => 1
                ], DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat', $self_id ).
                form_select( 'forum_type', self::$locale['forum_009'], $this->data['forum_type'], [ "options" => $type_opts ] ).
                form_select( 'forum_language[]', self::$locale['forum_010'], $this->data['forum_language'], [
                    "options"   => $language_opts,
                    'multiple'  => TRUE
                ] ).
                form_text( 'forum_order', self::$locale['forum_043'], $this->data['forum_order'], [ 'number' => 1 ] ).
                form_button( 'save_forum', $this->data['forum_id'] ? self::$locale['forum_000a'] : self::$locale['forum_000'], self::$locale['forum_000'], [ 'class' => 'btn btn-primary' ] );
            echo "</div>\n";
            echo "</div>\n</div>\n";

            echo "<div class='".grid_row()."'>\n<div class='".grid_column_size( 100, 70, 70, 80 )."'>\n";
            echo form_textarea( 'forum_rules', self::$locale['forum_017'], $this->data['forum_rules'], [
                'autosize'  => TRUE,
                'type'      => 'bbcode',
                'form_name' => 'inputform'
            ] );
            if ( $this->data['forum_image'] && file_exists( FORUM."images/".$this->data['forum_image'] ) ) {

                openside();
                echo "<div class='pull-left m-r-10'>\n";
                echo thumbnail( FORUM."images/".$this->data['forum_image'], '80px' );
                echo "</div>\n<div class='overflow-hide'>\n";
                echo "<span class='strong'>".self::$locale['forum_013']."</span><br/>\n";
                $image_size = @getimagesize( FORUM."images/".$this->data['forum_image'] );
                echo "<span class='text-smaller'>".sprintf( self::$locale['forum_027'], $image_size[0],
                        $image_size[1] )."</span><br/>";
                echo form_hidden( 'forum_image', '', $this->data['forum_image'] );
                echo form_button( 'remove_image', self::$locale['forum_028'], self::$locale['forum_028'], [
                    'class' => 'btn-danger m-t-10',
                    'icon'  => 'fa fa-trash'
                ] );
                echo "</div>\n";
                closeside();
            } else {

                openside( self::$locale['forum_028a'] );
                echo "<div class='pull-left m-r-15 p-r-15'>\n";
                echo form_fileinput( 'forum_image', '', '', [
                    "upload_path"      => $forum_image_path,
                    "thumbnail"        => TRUE,
                    "thumbnail_folder" => $forum_image_path,
                    "type"             => "image",
                    "delete_original"  => TRUE,
                    'inline'           => FALSE,
                    "max_count"        => $forum_settings['forum_attachmax'],
                    'template'         => 'thumbnail',
                    'ext_tip'          => sprintf( self::$locale['forum_015'], parsebytesize( $forum_settings['forum_attachmax'] ) ),
                ] );
                echo "</div><div class='pull-left'>\n";
                echo form_select( 'forum_image_header', self::$locale['forum_056'], '', [
                    'inline'  => FALSE,
                    'options' => [
                        '0' => 'Local Server',
                        '1' => 'URL',
                    ],
                ] );
                echo form_text( 'forum_image_url', self::$locale['forum_014'], '', [
                    'placeholder' => 'images/forum/',
                    'inline'      => FALSE,
                    'ext_tip'     => self::$locale['forum_016']
                ] );
                echo "</div>\n";
                closeside();
            }
            echo "</div><div class='".grid_column_size( 100, 30, 30, 20 )."'>\n";
            echo "<div class='well'>\n";
            // need to get parent category
            echo form_select_tree( 'forum_permissions', self::$locale['forum_025'], $this->data['forum_branch'],
                [ 'no_root' => TRUE, 'deactivate' => $this->data['forum_id'] ? TRUE : FALSE ],
                DB_FORUMS, 'forum_name', 'forum_id', 'forum_cat' );
            if ( $this->data['forum_id'] ) {
                echo form_button( 'jp_forum', self::$locale['forum_029'], self::$locale['forum_029'],
                    [ 'class' => 'btn-default m-r-10' ] );
            }
            echo "</div>\n";
            echo "<div class='well'>\n";
            echo form_checkbox( 'forum_lock', self::$locale['forum_026'], $this->data['forum_lock'], [
                    "reverse_label" => TRUE,
                    'class'         => 'm-0'
                ] ).
                form_checkbox( 'forum_users', self::$locale['forum_024'], $this->data['forum_users'], [
                    "reverse_label" => TRUE,
                    'class'         => 'm-0'
                ] ).
                form_checkbox( 'forum_quick_edit', self::$locale['forum_021'], $this->data['forum_quick_edit'], [
                    "reverse_label" => TRUE,
                    'class'         => 'm-0'
                ] ).
                form_checkbox( 'forum_merge', self::$locale['forum_019'], $this->data['forum_merge'], [
                    "reverse_label" => TRUE,
                    'class'         => 'm-0'
                ] ).
                form_checkbox( 'forum_allow_comments', self::$locale['forum_144'], $this->data['forum_allow_comments'], [
                    "reverse_label" => TRUE,
                    'class'         => 'm-0'
                ] ).
                form_checkbox( 'forum_allow_attach', self::$locale['forum_020'], $this->data['forum_allow_attach'], [
                    "reverse_label" => TRUE,
                    'class'         => 'm-0'
                ] ).
                form_checkbox( 'forum_allow_poll', self::$locale['forum_022'], $this->data['forum_allow_poll'], [
                    "reverse_label" => TRUE,
                    'class'         => 'm-0'
                ] ).
                form_checkbox( 'forum_show_postcount', self::$locale['forum_145'], $this->data['forum_show_postcount'], [
                    "reverse_label" => TRUE,
                    'class'         => 'm-0'
                ] ).
                form_hidden( 'forum_id', '', $this->data['forum_id'] ).
                form_hidden( 'forum_branch', '', $this->data['forum_branch'] );
            echo "</div>\n";
            echo "</div>\n</div>\n";
            echo form_button( 'save_forum', $this->data['forum_id'] ? self::$locale['forum_000a'] : self::$locale['forum_000'], self::$locale['forum_000'], [ 'class' => 'btn-primary' ] );
            echo closeform();
        }

        echo closetab();
    }

    /**
     * Permissions Form
     */
    private function display_forum_permissions_form() {

        $data = $this->data;
        $data += [
            'forum_id'   => !empty( $data['forum_id'] ) && isnum( $data['forum_id'] ) ? $data['forum_id'] : 0,
            'forum_type' => !empty( $data['forum_type'] ) ? $data['forum_type'] : '', // redirect if not exist? no..
        ];

        $_access = getusergroups();

        $access_opts['0'] = self::$locale['531'];

        foreach ( $_access as $key => $option ) {
            $access_opts[ $option['0'] ] = $option['1'];
        }

        $public_access_opts = $access_opts;

        unset( $access_opts[0] ); // remove public away.

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
        unset( $options[0] ); //  no public to moderate, unset
        unset( $options[ -101 ] ); // no member group to moderate, unset.

        add_breadcrumb( [ 'link' => FUSION_REQUEST, 'title' => self::$locale['forum_030'] ] );

        if ( !$this->forum_id ) {
            opentable( self::$locale['forum_030'] );
        }


        echo openform( 'permissionsForm', 'post', FUSION_REQUEST );
        //echo "<span class='strong display-inline-block m-b-20'>".self::$locale['forum_006'].": ".$data['forum_name']."</span>\n";
        openside();
        echo "<span class='text-dark strong display-inline-block m-b-20'>".self::$locale['forum_desc_000']."</span><br/>\n";
        echo form_select( 'forum_access', self::$locale['forum_031'], $data['forum_access'], [
            'inline'     => TRUE,
            'options'    => $public_access_opts,
            'select_alt' => TRUE,
        ] );
        $optionArray = [ "inline" => TRUE, "options" => $access_opts, 'select_alt' => TRUE ];
        echo form_select( 'forum_post', self::$locale['forum_032'], $data['forum_post'], $optionArray );
        echo form_select( 'forum_reply', self::$locale['forum_033'], $data['forum_reply'], $optionArray );
        echo form_select( 'forum_post_ratings', self::$locale['forum_039'], $data['forum_post_ratings'], $optionArray );
        closeside();
        openside();
        echo "<span class='text-dark strong display-inline-block m-b-20'>".self::$locale['forum_desc_001']."</span><br/>\n";
        echo form_select( 'forum_poll', self::$locale['forum_036'], $data['forum_poll'], $optionArray );
        echo form_select( 'forum_vote', self::$locale['forum_037'], $data['forum_vote'], $optionArray );
        closeside();
        openside();
        echo "<span class='text-dark strong display-inline-block m-b-20'>".self::$locale['forum_desc_004']."</span><br/>\n";
        echo form_select( 'forum_answer_threshold', self::$locale['forum_040'], $data['forum_answer_threshold'], [
            'options' => $selection,
            'inline'  => TRUE
        ] );
        closeside();
        openside();
        echo "<span class='text-dark strong display-inline-block m-b-20'>".self::$locale['forum_desc_002']."</span><br/>\n";
        echo form_select( 'forum_attach', self::$locale['forum_034'], $data['forum_attach'], [
            'options' => $access_opts,
            'inline'  => TRUE
        ] );
        echo form_select( 'forum_attach_download', self::$locale['forum_035'], $data['forum_attach_download'], [
            'options' => $public_access_opts,
            'inline'  => TRUE
        ] );
        closeside();

        echo form_hidden( 'forum_id', '', $data['forum_id'] );

        echo form_select( "forum_mods[]", self::$locale['forum_desc_003'], $data['forum_mods'], [
            "multiple"   => TRUE,
            "width"      => "100%",
            "options"    => $options,
            "delimiter"  => ".",
            "inline"     => TRUE,
            'select_alt' => TRUE,
        ] );

        echo form_button( 'save_permission', self::$locale['forum_042'], self::$locale['forum_042'],
            [ 'class' => 'btn-primary' ] );
        if ( !$this->forum_id ) {
            closetable();
        }
    }

    public function check_forum_image( $data ) {
        return ( is_file( FORUM.'images/'.$data[':forum_image'] ) ? "<i class='fas fa-check'></i>" : "<i class='fa fa-times'></i>" );
    }

    /**
     * Forum Table Listing
     */
    private function display_forum_list() {

        $locale = fusion_get_locale();

        $title = !empty( $this->level['title'] ) ? sprintf( self::$locale['forum_000b'], $this->level['title'][0] ) : self::$locale['forum_root'];

        add_to_title( $locale['global_201'].$title );

        $forum_settings = self::get_forum_settings();

        $threads_per_page = $forum_settings['threads_per_page'];

        new Tables( new Forum_List( $this->forum_cat, $threads_per_page ) );

    }

    /**
     * Quick create
     */
    private function quick_create_forum() {
        echo "<hr/>\n";
        echo openform( 'forum_create_form', 'post', FUSION_REQUEST, [ 'class' => 'spacer-sm m-t-0 p-15' ] );
        echo "<h4>".self::$locale['forum_001']."</h4>";
        echo form_text( 'forum_name', self::$locale['forum_006'], '', [
            'class'       => 'form-group-lg',
            'required'    => 1,
            'inline'      => FALSE,
            'placeholder' => self::$locale['forum_018']
        ] );
        echo form_button( 'init_forum', self::$locale['forum_001'], 'init_forum', [ 'class' => 'btn btn-primary' ] );
        echo closeform();
    }
}


class Forum_List implements \PHPFusion\Interfaces\TableSDK {

    private $forum_cat_id = 0;
    private $default_limit = 0;

    public function __construct( $forum_cat_id = 0, $threads_per_page ) {
        $this->forum_cat_id = isnum( $forum_cat_id ) ? $forum_cat_id : 0;
        $this->default_limit = isnum( $threads_per_page ) ? $threads_per_page : 0;
    }

    public function data() {

        return [
            'debug'  => FALSE,
            'table'  => DB_FORUMS,
            'id'     => 'forum_id',
            'parent' => 'forum_cat',
            'title'  => 'forum_name',
            'order'  => 'forum_order ASC',
            'limit'  => intval( $this->default_limit ),
        ];
    }

    /**
     * @return array
     */
    private function getForumRoot() {
        $refs = [];
        $forum_result = dbquery( "SELECT forum_id, forum_name FROM ".DB_FORUMS." WHERE
        forum_cat=0 ".( multilang_column( 'FO' ) ? " AND ".in_group( 'forum_language', LANGUAGE ) : '' )." ORDER BY forum_name ASC" );
        if ( dbrows( $forum_result ) ) {
            while ( $forum_data = dbarray( $forum_result ) ) {
                $refs[ $forum_data['forum_id'] ] = $forum_data['forum_name'];
            }
        }
        return $refs;
    }

    /**
     * @return array
     */
    public function properties() {

        $locale = fusion_get_locale();

        $aidlink = fusion_get_aidlink();

        $forum_arr = $this->getForumRoot();

        return [
            'table_id'           => 'forum-admin-list',
            'edit_link_format'   => FORUM.'admin/forums.php'.$aidlink.'&amp;action=edit&amp;forum_id=',
            'delete_link_format' => FORUM.'admin/forums.php'.$aidlink.'&amp;action=delete&amp;forum_id=',
            'search_label'       => $locale['search'],
            'search_col'         => 'forum_name',
            'no_record'          => $locale['560'],
            'dropdown_filters'   => [
                'forum_branch' => [
                    'type'    => 'array',
                    'title'   => 'Filter by:',
                    'options' => $forum_arr
                ]
            ],
            'ordering_col'       => 'forum_order',
            'multilang_col'      => 'forum_language',
            'link_filters'       =>
                [
                    'forum_cat' => [
                        'title'   => 'Display',
                        'options' => [
                            '0'  => 'Root Entries only', // where forum cat = 0
                            NULL => 'All Entries' // where forum cat = 1=1
                        ]
                    ],
                ],
            'order_col'          => [
                'forum_name'        => 'forum-name',
                'forum_description' => 'forum-description',
                'forum_type'        => 'forum-type',
                'forum_image'       => 'forum-image',
                'forum_alias'       => 'forum-alias',
                'forum_order'       => 'forum-order',
            ]
        ];
    }

    /**
     * @return array
     */
    public function column() {

        $locale = fusion_get_locale();

        return [
            'forum_name'        => [
                'title'       => 'Forum',
                'edit_link'   => TRUE,
                'delete_link' => TRUE,
                'title_class' => 'col-xs-4',
                'visibility'  => TRUE,
            ],
            'forum_description' => [
                'title'       => 'Description',
                'title_class' => 'col-xs-3',
                'visibility'  => TRUE,
            ],
            'forum_type'        => [
                'title'      => 'Forum Type',
                'options'    => $this->getTypeOptions(),
                'visibility' => TRUE,
            ],
            'forum_image'       => [
                'title'       => 'Image',
                'callback'    => [ 'PHPFusion\\Infusions\\Forum\\Classes\\Admin\\ForumAdminView', 'check_forum_image' ],
                'title_class' => 'text-center',
                'value_class' => 'text-center',
                'visibility'  => TRUE,
            ],
            'forum_alias'       => [
                'title'       => 'Alias',
                'title_class' => 'col-xs-2',
                'visibility'  => TRUE,
            ],
            'forum_order'       => [
                'title'       => 'Ordering',
                'title_class' => 'Order',
                'visibility'  => TRUE,
            ]
        ];
    }

    /**
     * Every row of the array is a field input.
     *
     * @return array
     */

    public function quickEdit() {

        $lang_field = [];
        if ( multilang_column( 'FO' ) ) {
            $lang_field = [
                'forum_language' => [
                    'function' => 'form_select',
                    'options'  => fusion_get_enabled_languages(),
                    'label'    => 'Forum Language',
                    'inline'   => TRUE,
                ]
            ];
        }

        return [
                'forum_name'  => [
                    'label'    => 'Forum Name',
                    'function' => 'form_text',
                    'inline'   => TRUE,
                    'required' => TRUE,
                ],
                'forum_order' => [
                    'label'    => 'Forum Order',
                    'function' => 'form_text',
                    'inline'   => TRUE,
                ],
                'forum_id'    => [
                    'function' => 'form_hidden'
                ],
                'forum_cat'   => [
                    'function' => 'form_hidden',
                ],
                'forum_type'  => [
                    'label'    => 'Forum Type',
                    'function' => 'form_checkbox',
                    'type'     => 'radio',
                    'options'  => $this->getTypeOptions(),
                    'inline'   => TRUE,
                ]
            ] + $lang_field;
    }


    private function getTypeOptions() {
        $locale = fusion_get_locale();
        return [
            '1' => $locale['forum_opts_001'],
            '2' => $locale['forum_opts_002'],
            '3' => $locale['forum_opts_003'],
            '4' => $locale['forum_opts_004'],
        ];
    }

}
