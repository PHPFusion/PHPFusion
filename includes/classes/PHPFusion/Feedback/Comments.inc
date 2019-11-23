<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: PHPFusion/Feedback/Comments.inc
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Feedback;

use PHPFusion\OutputHandler;

/**
 * Class Comments
 *
 * @package PHPFusion\Feedback
 *          Rating is not working
 *          Edit is not working
 */
class Comments extends CommentsFactory {
    
    private static $key = 'Default';
    
    private $jquery_enabled = FALSE;
    
    private $comment_params = [];
    
    private static $clink = [];
    
    private function __construct( $params ) {
        fusion_get_locale( '', [ LOCALE.LOCALESET."comments.php", LOCALE.LOCALESET."user_fields.php", LOCALE.LOCALESET."ratings.php" ] );
        $this->setParams( $params + $this->params );
        // Check lapsed permissions
        $this->checkPermissions();
    }
    
    public static function getInstance( $params ) {
        if ( empty( self::$instance ) ) {
            self::$instance = new self( $params );
        }
        return self::$instance;
    }
    
    /**
     * Displays Comments
     */
    public function showComments() {
    
    
        if ( fusion_get_settings( 'comments_enabled' ) == TRUE ) {
        
            $this->commentUpdate();
        
            $form = new CommentsForm( $this );
        
            $comment = new CommentListing( $this );
        
            $comment_info = $comment->getInfo();
        
            $info = [
                'comment_title'             => $this->getParams( 'comment_title' ),
                'comment_count'             => $comment_info['comment_count'],
                'comment_container_id'      => $this->getParams( 'comment_item_type' ).$this->getParams( 'comment_item_id' ),
                'comment_form_container_id' => $this->getParams( 'comment_item_type' ).$this->getParams( 'comment_item_id' ).'_commentsfrm',
                'comments_listing'          => display_comments_section( [
                    'comments'        => $comment_info['comments'],
                    'comment_ratings' => $comment_info['comment_ratings']
                ] ),
                'comments_form'             => display_comments_form( $form->getInfo() ),
            ];
        
            return display_comments_ui( $info );
        }
    
        return '';
    }
    
    private function deprecatedJScript() {
        /*
        * Note: Required to output to prevent broken select2.
        * Original ajax script to be removed to prevent event bubbling
        */
        if ( $this->jquery_enabled === TRUE ) {
            if ( !defined( "COMMENTS_JQUERY_SCRIPT" ) ) {
                define( "COMMENTS_JQUERY_SCRIPT", TRUE );
                $jquery_tags = OutputHandler::$jqueryTags;
                $comment_js = str_replace( [ "<script>", "</script>" ], [], $this->buildJavascript() );
                $jquery_tags = str_replace( $comment_js, '', $jquery_tags );
                echo "<script type='text/javascript'>\n";
                echo "$(function() { $jquery_tags });";
                echo "</script>\n";
            }
        }
        
    }
    
    private function checkPermissions() {
        $permission_params = [
            ':uid'       => fusion_get_userdata( 'user_id' ),
            ':item_id'   => $this->getParams( 'comment_item_id' ),
            ':item_type' => $this->getParams( 'comment_item_type' ),
        ];
        if ( dbcount( "(rating_id)", DB_RATINGS, "rating_user=:uid AND rating_item_id=:item_id AND rating_type=:item_type", $permission_params ) ) {
            $this->replaceParam( 'comment_allow_vote', FALSE ); // allow ratings
        }
        
        if ( dbcount( "(comment_id)", DB_COMMENTS, "comment_name=:uid AND comment_cat='0' AND comment_item_id=:item_id AND comment_type=:item_type", $permission_params ) && $this->getParams( 'comment_once' ) ) {
            $this->replaceParam( 'comment_allow_post', FALSE ); // allow post
        }
    }
    
    /**
     * If we have multiple instance, only execute 1 time.
     *
     * @return bool
     * @throws \Exception
     */
    private function commentUpdate() {
        static $update_cache = FALSE;
        
        if ( !$update_cache ) {
            $update_cache = TRUE;
            
            $settings = fusion_get_settings();
            $locale = fusion_get_locale();
            
            $this->replaceParam( 'comment_user', fusion_get_userdata( 'user_id' ) );
            /**
             * Documentation for Ajax Token and Fields.
             * To do remote calls for token,
             * 1. the openform shall register the remote url full path
             * 2. in your jquery - POST the `form id`
             */
            /*
             * if ( $this->jquery_enabled === TRUE ) {
                if ( !defined( "COMMENTS_JQUERY_SCRIPT" ) ) {
                    define( "COMMENTS_JQUERY_SCRIPT", TRUE );
                    if ( !$this->getParams( 'comment_custom_script' ) ) {
                        $comment_js = str_replace( [ "<script>", "</script>" ], [], $this->buildJavascript() );
                        add_to_jquery( $comment_js );
                    }
                }
            }
             */
            // Non Jquery Actions
            if ( get( 'comment_reply' ) ) {
                add_to_jquery( "scrollTo('comments_reply_form');" );
            }
            
            /** Delete */
            if ( $c_action = get( 'c_action' ) && iMEMBER ) {
                if ( $c_action == 'delete' ) {
                    $comment_id = get( 'comment_id', FILTER_VALIDATE_INT );
                    
                    $delete_query = "SELECT tcm.*, tcu.user_name FROM ".DB_COMMENTS." tcm
                LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
                WHERE comment_id=:comment_id AND comment_hidden=:comment_hidden
                ";
                    $delete_param = [
                        ':comment_id'     => (int)$comment_id,
                        ':comment_hidden' => 0,
                    ];
                    
                    $eresult = dbquery( $delete_query, $delete_param );
                    
                    if ( dbrows( $eresult ) ) {
                        
                        $edata = dbarray( $eresult );
                        
                        $redirect_link = $this->getParams( 'clink' ).( $settings['comments_sorting'] == "ASC" ? "" : "&amp;c_start=0" )."#c".$comment_id;
                        
                        $child_query = "SELECT comment_id FROM ".DB_COMMENTS." WHERE comment_cat=:cid";
                        $child_param = [ ':cid' => (int)$comment_id ];
                        
                        $result = dbquery( $child_query, $child_param );
                        
                        if ( dbrows( $result ) ) {
                            
                            while ( $child = dbarray( $result ) ) {
                                dbquery( "UPDATE ".DB_COMMENTS." SET comment_cat=:ccid WHERE comment_id=:cid", [
                                    ':ccid' => (int)$edata['comment_cat'],
                                    ':cid'  => (int)$child['comment_id']
                                ] );
                            }
                        }
                        dbquery( "DELETE FROM ".DB_COMMENTS." WHERE comment_id=:ecid".( iADMIN ? '' : "AND comment_name='".$this->userdata['user_id']."'" ), [
                            ':ecid' => (int)$edata['comment_id']
                        ] );
                        
                        $func = $this->getParams( 'comment_delete_callback_function' );
                        
                        if ( is_callable( $func ) ) {
                            $func( $this->getParams() );
                        }
                        
                        redirect( $redirect_link );
                    }
                }
                return FALSE;
            }
            
            
            /** Update & Save */
            // Ratings Removal Update
            // post comment_type, comment_item_id, remove_ratings_vote;
            if ( iMEMBER && $this->getParams( 'comment_allow_ratings' ) && !$this->getParams( 'comment_allow_vote' ) ) {
                if ( post( 'remove_ratings_vote' ) ) {
                    $delete_ratings = "DELETE FROM ".DB_RATINGS." WHERE rating_item_id=:rid AND rating_type=:rit AND rating_user:ruid";
                    $result = dbquery( $delete_ratings, [
                        ':rid'  => (int)$this->getParams( 'comment_item_id' ),
                        ':rit'  => $this->getParams( 'comment_item_type' ),
                        ':ruid' => (int)fusion_get_userdata( 'user_id' ) ] );
                    if ( $result ) {
                        redirect( $this->format_clink( $this->getParams( 'clink' ) ) );
                    }
                }
                return FALSE;
            }
            
            /**
             * Post Comment, Reply Comment
             */
            if ( ( iMEMBER || $settings['guestposts'] ) && post( 'post_comment' ) && post( 'comment_item_id' ) == $this->getParams( 'comment_item_id' ) && post( 'comment_item_type' ) == $this->getParams( 'comment_item_type' ) ) {
                
                if ( !iMEMBER && $settings['guestposts'] ) {
                    // Process Captchas
                    $_CAPTCHA_IS_VALID = FALSE;
                    include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php";
                    if ( !$_CAPTCHA_IS_VALID ) {
                        \Defender::stop();
                        addNotice( "danger", $locale['u194'] );
                    }
                }
                
                $default_comment_id = sanitizer( 'comment_id', 0, 'comment_id' ); //isset($_POST['comment_id']) && isnum($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
                
                $comment_data = [
                    'comment_id'      => get( 'comment_id', FILTER_VALIDATE_INT ) ?: $default_comment_id, //isset($_GET['comment_id']) && isnum($_GET['comment_id']) ? $_GET['comment_id'] : $default_comment_id,
                    'comment_name'    => iMEMBER ? $this->getParams( 'comment_user' ) : sanitizer( 'comment_name', '', 'comment_name' ), //form_sanitizer($_POST['comment_name'], '', 'comment_name'),
                    'comment_subject' => ( !post( 'comment_cat', FILTER_VALIDATE_INT ) && post( 'comment_subject' ) ? sanitizer( 'comment_subject', '', 'comment_subject' ) : '' ),
                    'comment_item_id' => $this->getParams( 'comment_item_id' ),
                    'comment_type'    => $this->getParams( 'comment_item_type' ),
                    'comment_cat'     => sanitizer( 'comment_cat', 0, 'comment_cat' ), //form_sanitizer($_POST['comment_cat'], 0, 'comment_cat'),
                    'comment_ip'      => USER_IP,
                    'comment_ip_type' => USER_IP_TYPE,
                    'comment_hidden'  => 0,
                ];
                
                // there is a conflict. the form above and the form below is same?
                $comment_data['comment_message'] = $comment_data['comment_cat'] ? sanitizer( 'comment_message_reply', '', 'comment_message_reply' ) : sanitizer( 'comment_message', '', 'comment_message' ); //form_sanitizer($_POST['comment_message'], '', 'comment_message');
                
                $ratings_query = "SELECT rating_id FROM ".DB_RATINGS." WHERE rating_item_id='".$comment_data['comment_item_id']."' AND rating_type='".$comment_data['comment_type']."' AND rating_user='".$comment_data['comment_name']."'";
                $ratings_id = dbresult( dbquery( $ratings_query ), 0 );
                
                $ratings_data = [];
                if ( $this->getParams( 'comment_allow_ratings' ) && $this->getParams( 'comment_allow_vote' ) && post( 'comment_rating' ) ) {
                    $ratings_data = [
                        'rating_id'        => $ratings_id,
                        'rating_item_id'   => $this->getParams( 'comment_item_id' ),
                        'rating_type'      => $this->getParams( 'comment_item_type' ),
                        'rating_user'      => $comment_data['comment_name'],
                        'rating_vote'      => sanitizer( 'comment_rating', 0, 'comment_rating' ),
                        'rating_datestamp' => TIME,
                        'rating_ip'        => USER_IP,
                        'rating_ip_type'   => USER_IP_TYPE
                    ];
                }
                
                if ( iMEMBER && $comment_data['comment_id'] ) {
                    // Update comment
                    if ( ( iADMIN && checkrights( "C" ) ) || ( iMEMBER && dbcount( "(comment_id)", DB_COMMENTS, "comment_id='".$comment_data['comment_id']."'
                        AND comment_item_id='".$this->getParams( 'comment_item_id' )."'
                        AND comment_type='".$this->getParams( 'comment_item_type' )."'
                        AND comment_name='".$this->userdata['user_id']."'
                        AND comment_hidden='0'" ) ) && fusion_safe()
                    ) {
                        
                        $c_name_query = "SELECT comment_name FROM ".DB_COMMENTS." WHERE comment_id='".$comment_data['comment_id']."'";
                        $comment_data['comment_name'] = dbresult( dbquery( $c_name_query ), 0 );
                        
                        dbquery_insert( DB_COMMENTS, $comment_data, 'update' );
                        $this->comment_params[ self::$key ]['post_id'] = $comment_data['comment_id'];
                        
                        $func = $this->getParams( 'comment_edit_callback_function' );
                        if ( is_callable( $func ) ) {
                            $func( $this->getParams() );
                        }
                        
                        if ( iMEMBER && $this->getParams( 'comment_allow_ratings' ) && $this->getParams( 'comment_allow_vote' ) ) {
                            dbquery_insert( DB_RATINGS, $ratings_data, ( $ratings_data['rating_id'] ? 'update' : 'save' ) );
                        }
                        
                        if ( $settings['comments_sorting'] == "ASC" ) {
                            $c_operator = "<=";
                        } else {
                            $c_operator = ">=";
                        }
                        
                        $c_count = dbcount( "(comment_id)", DB_COMMENTS, "comment_id".$c_operator."'".$comment_data['comment_id']."'
                            AND comment_item_id='".$this->getParams( 'comment_item_id' )."'
                            AND comment_type='".$this->getParams( 'comment_item_type' )."'" );
                        
                        $c_start = ( ceil( $c_count / $settings['comments_per_page'] ) - 1 ) * $settings['comments_per_page'];
                        if ( $this->jquery_enabled === FALSE && fusion_safe() ) {
                            addNotice( "success", $locale['c114'] );
                            $_c = ( isset( $c_start ) && isnum( $c_start ) ? $c_start : "" );
                            $c_link = $this->getParams( 'clink' );
                            redirect( $this->format_clink( "$c_link&amp;c_start=$_c" ) );
                        }
                    }
                    
                    return FALSE;
                }
                
                $comment_data['comment_datestamp'] = TIME;
                
                if ( fusion_safe() ) {
                    
                    $c_start = 0;
                    
                    if ( $comment_data['comment_name'] && $comment_data['comment_message'] ) {
                        
                        require_once INCLUDES."flood_include.php";
                        
                        if ( !flood_control( "comment_datestamp", DB_COMMENTS, "comment_ip='".USER_IP."'" ) ) {
                            
                            $comment_id = dbquery_insert( DB_COMMENTS, $comment_data, 'save' );
                            
                            $this->comment_params[ self::$key ]['post_id'] = $comment_id;
                            
                            $func = $this->getParams( 'comment_post_callback_function' );
                            if ( is_callable( $func ) ) {
                                $func( $this->getParams() );
                            }
                            
                            if ( iMEMBER && fusion_get_settings( 'ratings_enabled' ) && $this->getParams( 'comment_allow_ratings' ) && $this->getParams( 'comment_allow_vote' ) ) {
                                dbquery_insert( DB_RATINGS, $ratings_data, ( $ratings_data['rating_id'] ? 'update' : 'save' ) );
                            }
                            
                            if ( $settings['comments_sorting'] == "ASC" ) {
                                $c_count = dbcount( "(comment_id)", DB_COMMENTS, "comment_item_id='".$this->getParams( 'comment_item_id' )."' AND comment_type='".$this->getParams( 'comment_item_type' )."'" );
                                $c_start = ( ceil( $c_count / $settings['comments_per_page'] ) - 1 ) * $settings['comments_per_page'];
                            }
                            
                            if ( !$this->jquery_enabled ) {
                                redirect( self::format_clink( $this->getParams( 'clink' ) )."&amp;c_start=".$c_start."#c".$comment_id );
                            }
                            
                        }
                    }
                }
                
                return FALSE;
            }
        }
        return FALSE;
    }
    
    /**
     * Removes comment reply
     *
     * @param $clink
     *
     * @return string
     */
    public function format_clink( $clink ) {
        //$form_action = fusion_get_settings('site_path').str_replace('../', '', self::format_clink($clink));
        if ( empty( self::$clink[ $clink ] ) ) {
            $fusion_query = [];
            $url = $url = ( (array)parse_url( htmlspecialchars_decode( $clink ) ) ) + [
                    'path'  => '',
                    'query' => ''
                ];
            if ( $url['query'] ) {
                parse_str( $url['query'], $fusion_query ); // this is original.
            }
            $fusion_query = array_diff_key( $fusion_query, array_flip( [ "comment_reply" ] ) );
            $prefix = $fusion_query ? '?' : '';
            self::$clink[ $clink ] = $url['path'].$prefix.http_build_query( $fusion_query, NULL, '&amp;' );
        }
        
        return (string)self::$clink[ $clink ];
    }
    
    // This is singular. We cannot have many multiple ones for every instance.
    // All will post towards the last box on multiple instance.
    private function buildJavascript() {
        return "
        <script>
        InitComment();
        function InitComment() {
            PostComments();
            PostCommentsReply();
            EditComments();
            DeleteComments();
        }
        function DeleteComments() {
            $('body').on('click', '.delete-comment', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (confirm('".$locale['c110']."')) {
                     var formData = {
                    'comment_id' : $(this).data('id'),
                    'comment_options' : $(this).data('api'),
                    'comment_item' : $(this).data('item'),
                    'comment_item_type' : $(this).data('type'),
                    'comment_key' : $(this).data('key'),
                    };
                    console.log(formData);
                    var sendData = $.param(formData);
                    $.ajax({
                        url: '".FUSION_ROOT.CLASSES."PHPFusion/Feedback/Comments.ajax.php?action=delete',
                        type: 'POST',
                        dataType: 'html',
                        data : sendData,
                        success: function(result){
                            if (result) {
                                $('#'+formData.comment_key).html(result);
                            }
                        },
                        error: function(result) {
                            //console.log(result);
                        }
                    });
                }
            });
        }
        function EditComments() {
            $('body').on('click', '.edit-comment', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var formData = {
                    'comment_id' : $(this).data('id'),
                    'comment_options' : $(this).data('api'),
                    'comment_key' : $(this).data('key')
                };
                var sendData = $.param(formData);
                $.ajax({
                    url: '".FUSION_ROOT.CLASSES."PHPFusion/Feedback/Comments.ajax.php?action=edit',
                    type: 'POST',
                    dataType: 'json',
                    data : sendData,
                    success: function(e){
                        if (e) {
                            var containerID = formData.comment_key;
                            $('#'+containerID+'-comment_cat').val(e.comment_cat);
                            $('#'+containerID+'-comment_title').val(e.comment_title);
                            $('#'+containerID+'-comment_ratings').val(e.comment_ratings);
                            $('#'+containerID+'-comment_name').val(decodeEntities(e.comment_name));
                            $('#'+containerID+'-comment_subject').val(decodeEntities(e.comment_subject));
                            $('#'+containerID+'-comment_message').val(decodeEntities(e.comment_message));
                            $('#'+containerID+'-comment_id').val(e.comment_id);
                            scrollTo(containerID+'-comment_subject');
                        }
                    },
                    error: function(result) {
                        //console.log(result);
                    }
                });
            });
        }
        function PostComments() {
            $('body').on('click', '.post_comment', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var formData = {
                'post_comment' : $(this).val()
            }
            var sendData = $(this).closest('form').serialize() + '&' + $.param(formData);
            var values = {};
            $.each($(this).closest('form').serializeArray(), function (i, field) {
                values[field.name] = field.value;
            });
            var url = '".FUSION_ROOT.CLASSES."PHPFusion/Feedback/Comments.ajax.php';

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'html',
                data: sendData,
                success: function (result) {
                    $('#'+values.comment_key).html(result);
                },
                error: function (result) {
                }
            });
        });
        }
        function PostCommentsReply() {
            $('body').on('click', '.comments-reply', function (e) {
                e.preventDefault();
                $('.comments_reply_container').hide();
                var comment_id = $(this).data('id');
                setTimeout(function () {
                    $('#comments_reply_spinner-' + comment_id).show();
                    setTimeout(function () {
                        $('#comments_reply_spinner-' + comment_id).fadeOut();
                        setTimeout(function () {
                            $('#comments_reply_container-' + comment_id).fadeIn();
                        }, 350);
                    }, 450);
                }, 100);
            });
        }
        </script>
        ";
    }
    
}

require_once( __DIR__.'/Comments.view.php' );
