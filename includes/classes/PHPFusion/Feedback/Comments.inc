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
/**
 * Class Comments
 * @package PHPFusion\Feedback
 * Rating is not working
 * Edit is not working
 */
class Comments {

    private static $instances = NULL;
    private static $key = 'Default';

    /**
     * @var array
     * comment_item_type -
     * comment_db -
     * comment_item_id -
     * clink -
     * comment_allow_reply - enable or disable reply of others comments
     * comment_allow_post - enable or disable posting of comments
     * comment_allow_ratings - enable or disable ratings
     * comment_allow_vote - enable or disable voting
     * comment_once - each user can only comment once (replying a comment is unaffected)
     * comment_echo - to echo the output if true
     * comment_title - display the comment block title
     * comment_count - display the current comment count
     */
    private static $default_params = array(
        'comment_item_type' => '',
        'comment_db' => '',
        'comment_col' => '',
        'comment_item_id' => '',
        'clink' => '',
        'comment_allow_reply' => TRUE,
        'comment_allow_post' => TRUE,
        'comment_allow_ratings' => TRUE,
        'comment_allow_vote' => TRUE,
        'comment_once' => FALSE,
        'comment_echo' => FALSE,
        'comment_title' => '',
        'comment_form_title' => '',
        'comment_count' => TRUE,
        'comment_template' => 'render_comments',
        'comment_form_template' => 'render_comments_form'
    );

    private $jquery_enabled = FALSE;
    private $locale = array();
    private $userdata = array();
    private $settings = array();
    private $postLink = "";
    private $c_arr = array(
        "c_con" => array(),
        "c_info" => array(
            "c_makepagenav" => FALSE,
            "admin_link" => FALSE
        )
    );
    private $comment_params = array();
    private $comment_data = array();
    private $cpp = 0;

    private function __construct() {

        $this->settings = fusion_get_settings();

        $this->locale = fusion_get_locale("", LOCALE.LOCALESET."comments.php");
        $this->locale += fusion_get_locale('', LOCALE.LOCALESET."user_fields.php");

        $this->userdata = fusion_get_userdata();

        $this->postLink = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
        $this->postLink = preg_replace("^(&amp;|\?)c_action=(edit|delete)&amp;comment_id=\d*^", "", $this->postLink);

        $_GET['comment'] = isset($_GET['comment']) && isnum($_GET['comment']) ? $_GET['comment'] : 0;

        $this->jquery_enabled = fusion_get_settings('comments_jquery') ? TRUE : FALSE;

        $this->cpp = fusion_get_settings('comments_per_page');
    }

    /**
     * Get an instance by key
     * @param array  $params
     * @param string $key
     * @return object
     */
    public static function getInstance(array $params = array(), $key = 'Default') {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
            self::$key = $key;
            $params += self::$default_params;

            self::$instances[$key]->setParams($params);
            self::$instances[$key]->setEmptyCommentData();
            self::$instances[$key]->get_Permissions();
            self::$instances[$key]->execute_CommentUpdate();
            self::$instances[$key]->get_Comments();
        }

        return (object) self::$instances[$key];
    }

    /**
     * Displays Comments
     */
    public function showComments() {
        $html = '';
        if ($this->settings['comments_enabled'] == "1") {
            $comments_template = $this->getParams('comment_template');
            $comments_form_template = $this->getParams('comment_form_template');
            $html = "<div id='".$this->getParams('comment_item_type')."-".$this->getParams('comment_item_id')."-fusion_comments'  name='comments'>\n";
            if (is_callable($comments_template)) {
                $html .= $comments_template($this->c_arr['c_con'], $this->c_arr['c_info'], $this->getParams());
            } else {
                trigger_error('function '.$this->getParams('comment_template').' not found or declared too late. Is defined in your theme?');
            }
            if ($this->getParams('comment_allow_post')) {
                $html .= "<a id='".$this->getParams('comment_item_type')."-".$this->getParams('comment_item_id')."-comments_form' name='comments_form'></a>\n";
                if (is_callable($comments_form_template)) {
                    $html .= $comments_form_template($this->getParams('comment_item_type'), $this->getParams('clink'), $this->getParams('comment_item_id'), isset($_CAPTCHA_HIDE_INPUT) ? $_CAPTCHA_HIDE_INPUT : FALSE, $this->getParams());
                } else {
                    trigger_error('function '.$this->getParams('comment_form_template').' not found or declared too late. Is defined in your theme?');
                }

            }
            $html .= "</div>\n";
        }
        if ($this->getParams('comment_echo')) {
            echo $html;
        }

        return $html;
    }

    /**
     * Set Comment Object Parameters
     * @param array $params
     */
    private function setParams(array $params = array()) {
        $this->comment_params[self::$key] = $params;
    }

    private function setEmptyCommentData() {
        $this->comment_data = array(
            'comment_id' => isset($_GET['comment_id']) && isnum($_GET['comment_id']) ? $_GET['comment_id'] : 0,
            'comment_name' => '',
            'comment_subject' => '',
            'comment_message' => '',
            'comment_datestamp' => TIME,
            'comment_item_id' => $this->getParams('comment_item_id'),
            'comment_type' => $this->getParams('comment_item_type'),
            'comment_cat' => 0,
            'comment_ip' => USER_IP,
            'comment_ip_type' => USER_IP_TYPE,
            'comment_hidden' => 0,
        );
    }

    /**
     * Get Comment Object Parameter
     * @param null $key - null for all array
     * @return null
     */
    private function getParams($key = NULL) {
        if ($key !== NULL) {
            return isset($this->comment_params[self::$key][$key]) ? $this->comment_params[self::$key][$key] : NULL;
        }

        return $this->comment_params[self::$key];
    }

    private function execute_CommentUpdate() {

        /**
         * Documentation for Ajax Token and Fields.
         * To do remote calls for token,
         * 1. the openform shall register the remote url full path
         * 2. in your jquery - POST the `form id`
         */
        if ($this->jquery_enabled === TRUE) {

            $comment_js = str_replace(array("<script>", "</script>"), array('', ''), $this->getJs());
            add_to_jquery($comment_js);

            if (isset($_GET['c_action']) && $_GET['c_action'] == 'edit') {
                redirect(self::format_clink($this->comment_params['clink']));
            }
        }

        if (isset($_GET['comment_reply'])) {
            add_to_jquery("scrollTo('comments_reply_form');");
        }

        /** Delete */
        $has_delete_access = (iADMIN && checkrights("C")) ? TRUE : FALSE;
        if (!$has_delete_access) {
            $has_delete_access = (iMEMBER && dbcount("(comment_id)", DB_COMMENTS,
                                                     "comment_id='".$_GET['comment_id']."' AND comment_name='".$this->userdata['user_id']."'") ? TRUE : FALSE);
        }

        if ($has_delete_access && (isset($_GET['c_action']) && $_GET['c_action'] == "delete") && (!empty($_GET['comment_id']) && isnum($_GET['comment_id']))) {
            $redirect_link = $this->getParams('clink').($this->settings['comments_sorting'] == "ASC" ? "" : "&amp;c_start=0");
            // Find immediate child. Push to root
            $child_query = "SELECT comment_id FROM ".DB_COMMENTS." WHERE comment_cat='".$_GET['comment_id']."'";
            $result = dbquery($child_query);
            if (dbrows($result)) {
                while ($child = dbarray($result)) {
                    dbquery("UPDATE ".DB_COMMENTS." SET comment_cat=0 WHERE comment_id='".$child['comment_id']."'");
                }
            }

            dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_id='".$_GET['comment_id']."'".(iADMIN ? "" : "AND comment_name='".$this->userdata['user_id']."'"));
            redirect($redirect_link);
        }

        /** Update & Save */
        if ((iMEMBER || $this->settings['guestposts']) && isset($_POST['post_comment'])) {

            if (!iMEMBER && $this->settings['guestposts']) {
                // Process Captchas
                $_CAPTCHA_IS_VALID = FALSE;
                include INCLUDES."captchas/".$this->settings['captcha']."/captcha_check.php";
                if (!isset($_POST['captcha_code']) && $_CAPTCHA_IS_VALID == FALSE) {
                    \defender::stop();
                    addNotice("danger", $this->locale['u194']);
                }
            }

            $default_comment_id = isset($_POST['comment_id']) && isnum($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

            $comment_data = array(
                'comment_id' => isset($_GET['comment_id']) && isnum($_GET['comment_id']) ? $_GET['comment_id'] : $default_comment_id,
                'comment_name' => iMEMBER ? $this->userdata['user_id'] : form_sanitizer($_POST['comment_name'], '', 'comment_name'),
                'comment_subject' => empty($_POST['comment_cat']) ? form_sanitizer($_POST['comment_subject'], '', 'comment_subject') : '',
                'comment_message' => form_sanitizer($_POST['comment_message'], '', 'comment_message'),
                'comment_item_id' => $this->getParams('comment_item_id'),
                'comment_type' => $this->getParams('comment_item_type'),
                'comment_cat' => form_sanitizer($_POST['comment_cat'], 0, 'comment_cat'),
                'comment_ip' => USER_IP,
                'comment_ip_type' => USER_IP_TYPE,
                'comment_hidden' => 0,
            );
            if (!$default_comment_id) {
                $comment_data['comment_datestamp'] = TIME;
            }

            $ratings_query = "
            SELECT rating_id FROM ".DB_RATINGS." WHERE rating_item_id='".$comment_data['comment_item_id']."'
            AND rating_type='".$comment_data['comment_type']."' AND rating_user='".$comment_data['comment_name']."'
            ";
            $ratings_id = dbresult(dbquery($ratings_query), 0);

            if ($this->getParams('comment_allow_ratings') && $this->getParams('comment_allow_vote') && isset($_POST['comment_rating'])) {
                $ratings_data = [
                    'rating_id' => $ratings_id,
                    'rating_item_id' => $this->getParams('comment_item_id'),
                    'rating_type' => $this->getParams('comment_item_type'),
                    'rating_user' => $comment_data['comment_name'],
                    'rating_vote' => form_sanitizer($_POST['comment_rating'], 0, 'comment_rating'),
                    'rating_datestamp' => TIME,
                    'rating_ip' => USER_IP,
                    'rating_ip_type' => USER_IP_TYPE
                ];
            }

            if (iMEMBER && $comment_data['comment_id']) {

                // Update comment
                if ((iADMIN && checkrights("C")) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS, "comment_id='".$comment_data['comment_id']."'
                        AND comment_item_id='".$this->getParams('comment_item_id')."'
                        AND comment_type='".$this->getParams('comment_item_type')."'
                        AND comment_name='".$this->userdata['user_id']."'
                        AND comment_hidden='0'")) && \defender::safe()
                ) {

                    $c_name_query = "SELECT comment_name FROM ".DB_COMMENTS." WHERE comment_id='".$comment_data['comment_id']."'";
                    $comment_data['comment_name'] = dbresult(dbquery($c_name_query), 0);

                    dbquery_insert(DB_COMMENTS, $comment_data, 'update');

                    if (iMEMBER && $this->getParams('comment_allow_ratings') && $this->getParams('comment_allow_vote')) {
                        dbquery_insert(DB_RATINGS, $ratings_data, ($ratings_data['rating_id'] ? 'update' : 'save'));
                    }

                    if ($this->settings['comments_sorting'] == "ASC") {
                        $c_operator = "<=";
                    } else {
                        $c_operator = ">=";
                    }
                    $c_count = dbcount("(comment_id)", DB_COMMENTS, "comment_id".$c_operator."'".$comment_data['comment_id']."'
                            AND comment_item_id='".$this->getParams('comment_item_id')."'
                            AND comment_type='".$this->getParams('comment_item_type')."'");

                    $c_start = (ceil($c_count / $this->settings['comments_per_page']) - 1) * $this->settings['comments_per_page'];

                    if ($_POST['post_comment'] !== 'ajax' && \defender::safe()) {
                        addNotice("success", $this->locale['global_027']);
                        $_c = (isset($c_start) && isnum($c_start) ? $c_start : "");
                        $c_link = $this->getParams('clink');
                        redirect(self::format_clink("$c_link&amp;c_start=$_c"));
                    }

                }

            } else {

                // Save New comment
                if ($_POST['post_comment'] !== 'ajax') {

                    if (!dbcount("(".$this->getParams('comment_col').")", $this->getParams('comment_db'),
                                 $this->getParams('comment_col')."='".$this->getParams('comment_item_id')."'")
                    ) {
                        redirect(BASEDIR."index.php");
                    }
                }

                if (\defender::safe()) {
                    $c_start = 0;

                    if ($comment_data['comment_name'] && $comment_data['comment_message']) {

                        require_once INCLUDES."flood_include.php";

                        if (!flood_control("comment_datestamp", DB_COMMENTS, "comment_ip='".USER_IP."'")) {

                            $id = dbquery_insert(DB_COMMENTS, $comment_data, 'save');

                            if (iMEMBER && $this->getParams('comment_allow_ratings') && $this->getParams('comment_allow_vote')) {
                                dbquery_insert(DB_RATINGS, $ratings_data, ($ratings_data['rating_id'] ? 'update' : 'save'));
                            }

                            if ($this->settings['comments_sorting'] == "ASC") {
                                $c_count = dbcount("(comment_id)", DB_COMMENTS,
                                                   "comment_item_id='".$this->getParams('comment_item_id')."' AND comment_type='".$this->getParams('comment_item_type')."'");
                                $c_start = (ceil($c_count / $this->settings['comments_per_page']) - 1) * $this->settings['comments_per_page'];
                            }

                            if ($_POST['post_comment'] !== 'ajax' && \defender::safe()) {
                                redirect(self::format_clink($this->getParams('clink'))."&amp;c_start=".$c_start."#c".$id);
                            }
                        }


                    }
                }
            }
        }
    }

    // Fire a global.
    public function getJs() {
        return "
        <script>
        PostComments();
        PostCommentsReply();
        EditComments();
        function EditComments() {
            $('.edit-comment').bind('click', function(e) {
                e.preventDefault();
                var prefix = '".$this->getParams('comment_item_type')."-".$this->getParams('comment_item_id')."-';
                var formData = {
                    'comment_id' : $(this).data('id'),
                    'comment_item_type' : '".$this->getParams('comment_item_type')."',
                    'comment_db' : '".$this->getParams('comment_db')."',
                    'comment_col' : '".$this->getParams('comment_col')."',
                    'comment_item_id' : '".$this->getParams('comment_item_id')."',
                    'clink' : '".$this->getParams('clink')."',
                    'comment_allow_reply' : Boolean('".$this->getParams('comment_allow_reply')."'),
                    'comment_allow_post'  : Boolean('".$this->getParams('comment_allow_post')."'),
                    'comment_allow_ratings'  : Boolean('".$this->getParams('comment_allow_ratings')."'),
                    'comment_allow_vote'  : Boolean('".$this->getParams('comment_allow_vote')."'),
                    'comment_once'  : Boolean('".$this->getParams('comment_once')."'),
                    'comment_echo'  : Boolean('".$this->getParams('comment_echo')."'),
                    'comment_title'  : '".$this->getParams('comment_title')."',
                    'comment_form_title'  : '".$this->getParams('comment_form_title')."',
                    'comment_count'  : Boolean('".$this->getParams('comment_count')."'),
                    'post_comment' : 'ajax'
                };
                var sendData = $.param(formData);
                $.ajax({
                    url: '".FUSION_ROOT.CLASSES."PHPFusion/Feedback/EditComments.ajax.php',
                    type: 'POST',
                    dataType: 'json',
                    async: false,
                    data : sendData,
                    success: function(e){
                        if (e) {
                            $('#'+prefix+'comment_cat').val(e.comment_cat);
                            $('#'+prefix+'comment_title').val(e.comment_title);
                            $('#'+prefix+'comment_ratings').val(e.comment_ratings);
                            $('#'+prefix+'comment_name').val(e.comment_name);
                            $('#'+prefix+'comment_subject').val(e.comment_subject);
                            $('#'+prefix+'comment_message').val(e.comment_message);
                            $('#'+prefix+'comment_id').val(e.comment_id);
                            PostComments();
                            PostCommentsReply();
                            scrollTo('comments_form');
                        }
                    },
                    error: function(result) {
                        console.log(result);
                    }
                });
            });
        }
        function PostCommentsReply() {
            // Show comments form with spinner making it like we're loading it remotely... more high-tech.
            $('.comments-reply').bind('click', function(e) {
                e.preventDefault();
                $('.comments_reply_container').hide();
                var comment_id = $(this).data('id');
                // If this screws up over a live server, we'll just ditch the whole spinner idea.
                setTimeout(function() {
                    $('#comments_reply_spinner-'+comment_id).show();
                    setTimeout(function() { $('#comments_reply_spinner-'+comment_id).fadeOut();
                        setTimeout(function() { $('#comments_reply_container-'+comment_id).fadeIn(); }, 350);
                    }, 450);
                }, 100);
            });

            $('.post_comment_reply').bind('click', function(e) {
                var ID = $(this).val();
                e.preventDefault();
                var formData = {
                    'form_id' : 'comments_reply_form-'+ID,
                    'comment_name' : $('#comment_name-'+ID).val() ? $('#comment_name-'+ID).val() : '',
                    'comment_cat' : $('#comment_cat-'+ID).val() ? $('#comment_cat-'+ID).val() : '0',
                    'comment_subject' : $('#comment_subject-'+ID).val() ? $('#comment_subject-'+ID).val() : '',
                    'comment_ratings' : $('#comment_ratings-'+ID).val() ? $('#comment_ratings-'+ID).val() : '',
                    'comment_message' : $('#comment_message-'+ID).val() ? $('#comment_message-'+ID).val() : '',
                    'captcha_code' : $('#captcha_code-'+ID).val() ? $('#captcha_code-'+ID).val() : '0',
                    'comment_item_type' : '".$this->getParams('comment_item_type')."',
                    'comment_db' : '".$this->getParams('comment_db')."',
                    'comment_col' : '".$this->getParams('comment_col')."',
                    'comment_item_id' : '".$this->getParams('comment_item_id')."',
                    'clink' : '".$this->getParams('clink')."',
                    'comment_allow_reply' : Boolean('".$this->getParams('comment_allow_reply')."'),
                    'comment_allow_post'  : Boolean('".$this->getParams('comment_allow_post')."'),
                    'comment_allow_ratings'  : Boolean('".$this->getParams('comment_allow_ratings')."'),
                    'comment_allow_vote'  : Boolean('".$this->getParams('comment_allow_vote')."'),
                    'comment_once'  : Boolean('".$this->getParams('comment_once')."'),
                    'comment_echo'  : Boolean('".$this->getParams('comment_echo')."'),
                    'comment_title'  : '".$this->getParams('comment_title')."',
                    'comment_form_title'  : '".$this->getParams('comment_form_title')."',
                    'comment_count'  : Boolean('".$this->getParams('comment_count')."'),
                    'post_comment' : 'ajax'
                };
                var sendData = $('#comments_reply_form-'+ ID).serialize() + '&' + $.param(formData);
                $.ajax({
                    url: '".FUSION_ROOT.CLASSES."PHPFusion/Feedback/Comments.ajax.php',
                    type: 'POST',
                    dataType: 'html',
                    async: true,
                    data : sendData,
                    success: function(result){
                        $('#".$this->getParams('comment_item_type')."-".$this->getParams('comment_item_id')."-fusion_comments').html(result);
                        PostComments();
                        PostCommentsReply();
                        EditComments();
                    },
                    error: function() {
                    }
                });
            });
        }
        function PostComments() {
            // need to run data-id and data-type if to support multiple instance.
            $('.post_comment').bind('click', function(e) {
                e.preventDefault();
                var prefix = '".$this->getParams('comment_item_type')."-".$this->getParams('comment_item_id')."';
                var formData = {
                    'comment_item_type' : '".$this->getParams('comment_item_type')."',
                    'comment_db' : '".$this->getParams('comment_db')."',
                    'comment_col' : '".$this->getParams('comment_col')."',
                    'comment_item_id' : '".$this->getParams('comment_item_id')."',
                    'clink' : '".$this->getParams('clink')."',
                    'comment_allow_reply' : Boolean('".$this->getParams('comment_allow_reply')."'),
                    'comment_allow_post'  : Boolean('".$this->getParams('comment_allow_post')."'),
                    'comment_allow_ratings'  : Boolean('".$this->getParams('comment_allow_ratings')."'),
                    'comment_allow_vote'  : Boolean('".$this->getParams('comment_allow_vote')."'),
                    'comment_once'  : Boolean('".$this->getParams('comment_once')."'),
                    'comment_echo'  : Boolean('".$this->getParams('comment_echo')."'),
                    'comment_title'  : '".$this->getParams('comment_title')."',
                    'comment_form_title'  : '".$this->getParams('comment_form_title')."',
                    'comment_count'  : Boolean('".$this->getParams('comment_count')."'),
                    'post_comment' : 'ajax'
                };
                var sendData = $('#'+prefix+'-inputform').serialize() + '&' + $.param(formData);
                $.ajax({
                    url: '".FUSION_ROOT.CLASSES."PHPFusion/Feedback/Comments.ajax.php',
                    type: 'POST',
                    dataType: 'html',
                    async: true,
                    data : sendData,
                    success: function(result){
                        $('#'+prefix+'-fusion_comments').html(result);
                         PostComments();
                         PostCommentsReply();
                         EditComments();
                    },
                    error: function(result) {
                    }
                });
            });
        }
        </script>
        ";
    }

    /**
     * Removes comment reply
     * @param $clink
     * @return string
     */
    private static function format_clink($clink) {
        $fusion_query = array();
        $url = $url = ((array)parse_url(htmlspecialchars_decode($clink))) + array(
                'path' => '',
                'query' => ''
            );
        if ($url['query']) {
            parse_str($url['query'], $fusion_query); // this is original.
        }
        $fusion_query = array_diff_key($fusion_query, array_flip(array("comment_reply")));
        $prefix = $fusion_query ? '?' : '';
        $query = $url['path'].$prefix.http_build_query($fusion_query, NULL, '&amp;');

        return (string)$query;
    }

    private function get_Permissions() {

        $has_voted = dbcount("(rating_id)", DB_RATINGS, "rating_user='".fusion_get_userdata('user_id')."'
            AND rating_item_id='".$this->getParams('comment_item_id')."' AND rating_type='".$this->getParams('comment_item_type')."'");

        $has_commented = dbcount("(comment_id)", DB_COMMENTS, "comment_name='".fusion_get_userdata('user_id')."' AND comment_cat='0'
        AND comment_item_id='".$this->getParams('comment_item_id')."' AND comment_type='".$this->getParams('comment_item_type')."'");

        if (!isset($_GET['c_action'])) {
            if ($has_voted) {
                $this->replaceParam('comment_allow_vote', FALSE); // allow ratings
            }
            if ($has_commented && $this->getParams('comment_once')) {
                $this->replaceParam('comment_allow_post', FALSE); // allow post
            }
        }

    }

    /**
     * Replace Comment Object Parameter
     * @param $param
     * @param $value
     */
    public function replaceParam($param, $value) {
        if (isset($this->comment_params[self::$key][$param])) {
            $this->comment_params[self::$key][$param] = $value;
        }
    }

    /**
     * Fetches Comments Data
     */
    private function get_Comments() {

        $this->c_arr['c_info']['comments_count'] = format_word(0, $this->locale['fmt_comment']);

        if ($this->getParams('comment_allow_ratings')) {
            $ratings_query = "
            SELECT
            COUNT(rating_id) 'total',
            IF(avg(rating_vote), avg(rating_vote), 0) 'avg',
            SUM(IF(rating_vote='5', 1, 0)) '5',
            SUM(IF(rating_vote='4', 1, 0)) '4',
            SUM(IF(rating_vote='3', 1, 0)) '3',
            SUM(IF(rating_vote='2', 1, 0)) '2',
            SUM(IF(rating_vote='1', 1, 0)) '1'
            FROM ".DB_RATINGS."
            WHERE rating_type='".$this->getParams('comment_item_type')."' AND rating_item_id='".intval($this->getParams('comment_item_id'))."'
            ";
            $this->c_arr['c_info']['ratings_count'] = dbarray(dbquery($ratings_query));
        }

        // Handle Comment Posts
        $c_rows = dbcount("('comment_id')", DB_COMMENTS,
                          "comment_item_id='".$this->getParams('comment_item_id')."' AND comment_type='".$this->getParams('comment_item_type')."' AND comment_hidden='0' AND comment_cat='0'");

        if (!isset($_GET['c_start']) && $c_rows > $this->cpp) {
            $_GET['c_start'] = (ceil($c_rows / $this->cpp) - 1) * $this->cpp;
        }

        if (!isset($_GET['c_start']) || !isnum($_GET['c_start'])) {
            $_GET['c_start'] = 0;
        }

        $comment_query = "
            SELECT tcm.*, tcu.user_id, tcu.user_name, tcu.user_avatar, tcu.user_status, tcu.user_groups, tcr.rating_vote 'ratings'
            FROM ".DB_COMMENTS." tcm
            LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
            LEFT JOIN ".DB_RATINGS." tcr ON tcr.rating_item_id=tcm.comment_item_id AND tcr.rating_type=tcm.comment_type AND tcr.rating_user=tcu.user_id
            WHERE comment_item_id='".$this->getParams('comment_item_id')."' AND
            comment_type='".$this->getParams('comment_item_type')."' AND comment_hidden='0'
            ORDER BY comment_datestamp ".$this->settings['comments_sorting'].", comment_cat ASC";

        $query = dbquery($comment_query);

        $total_comments = dbrows($query);
        $this->c_arr['c_info']['total_comments'] = $total_comments;

        if (dbrows($query) > 0) :

            $i = ($this->settings['comments_sorting'] == "ASC" ? $_GET['c_start'] + 1 : $c_rows - $_GET['c_start']);

            if ($c_rows > $this->cpp) {
                $this->c_arr['c_info']['c_makepagenav'] = makepagenav($_GET['c_start'], $this->cpp, $c_rows, 3,
                                                                      $this->getParams('clink')."&amp;", "c_start");
            }

            if (iADMIN && checkrights("C")) {
                $this->c_arr['c_info']['admin_link'] = "<!--comment_admin-->\n";
                $this->c_arr['c_info']['admin_link'] .= "<a href='".ADMIN."comments.php".fusion_get_aidlink()."&amp;ctype=".$this->getParams('comment_item_type')."&amp;comment_item_id=".$this->getParams('comment_item_id')."'>".$this->locale['c106']."</a>";
            }

            while ($row = dbarray($query)) :

                $actions = array(
                    "edit_dell" => "",
                    "edit_link" => "",
                    "delete_link" => "",
                );

                if ((iADMIN && checkrights("C"))
                    || (iMEMBER && $row['comment_name'] == $this->userdata['user_id'] && isset($row['user_name']))
                ) {
                    $edit_link = $this->getParams('clink')."&amp;c_action=edit&amp;comment_id=".$row['comment_id']."#edit_comment"; //clean_request('c_action=edit&comment_id='.$row['comment_id'], array('c_action', 'comment_id'),FALSE)."#edit_comment";
                    $delete_link = $this->getParams('clink')."&amp;c_action=delete&amp;comment_id=".$row['comment_id']; //clean_request('c_action=delete&comment_id='.$row['comment_id'], array('c_action', 'comment_id'), FALSE);
                    $comment_actions = "
                    <!---comment_actions-->
                    <div class='btn-group'>
                        <a class='btn btn-xs btn-default edit-comment' data-id='".$row['comment_id']."' data-type='".$this->getParams('comment_item_type')."' data-item='".$this->getParams('comment_item_id')."' href='$edit_link'>".$this->locale['c108']."</a>
                        <a class='btn btn-xs btn-danger delete-comment' data-id='".$row['comment_id']."' href='$delete_link' onclick=\"return confirm('".$this->locale['c110']."');\"><i class='fa fa-trash'></i> ".$this->locale['c109']."</a>
                    </div>
                    <!---//comment_actions-->
                    ";

                    $actions = array(
                        "edit_link" => array('link' => $edit_link, 'name' => $this->locale['c108']),
                        "delete_link" => array('link' => $delete_link, 'name' => $this->locale['c109']),
                        "edit_dell" => $comment_actions
                    );

                }

                // Reply Form
                $reply_form = "";

                if ($this->getParams('comment_allow_reply') && (isset($_GET['comment_reply']) && $_GET['comment_reply'] == $row['comment_id']) || $this->jquery_enabled === TRUE) {

                    if ($this->jquery_enabled === TRUE) {
                        $reply_form .= "<div id='comments_reply_spinner-".$row['comment_id']."' class='spinner text-center m-b-20' style='display:none'><i class='fa fa-circle-o-notch fa-spin fa-3x'></i></div>";
                        $reply_form .= "<div id='comments_reply_container-".$row['comment_id']."' class='comments_reply_container' ".(isset($_GET['comment_reply']) && $_GET['comment_reply'] == $row['comment_id'] ? "" : "style='display:none;'").">";
                    }

                    $locale = fusion_get_locale();

                    $reply_form .= openform("comments_reply_form-".$row['comment_id'], "post", FUSION_REQUEST,
                                            array(
                                                "class" => "comments_reply_form m-t-20 m-b-20",
                                                "remote_url" => $this->jquery_enabled === TRUE ? fusion_get_settings("site_path")."includes/classes/PHPFusion/Feedback/Comments.ajax.php" : ""
                                            )
                    );

                    if (iGUEST) {
                        $reply_form .= form_text('comment_name', fusion_get_locale('c104'), $this->comment_data['comment_name'],
                                                 array(
                                                     'max_length' => 30,
                                                     'input_id' => 'comment_name-'.$row['comment_id']
                                                 )
                        );
                    }

                    $this->comment_data['comment_cat'] = $row['comment_id'];

                    $reply_form .= form_hidden("comment_cat", "", $this->comment_data['comment_cat'],
                                               array('input_id' => 'comment_cat-'.$row['comment_id']));
                    $reply_form .= form_textarea("comment_message", "", $this->comment_data['comment_message'],
                                                 array(
                                                     "tinymce" => "simple",
                                                     'autosize' => TRUE,
                                                     "type" => fusion_get_settings("tinymce_enabled") ? "tinymce" : "bbcode",
                                                     "input_id" => "comment_message-".$row['comment_id'],
                                                     "required" => TRUE
                                                 )
                    );

                    if (iGUEST && (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT))) {
                        $_CAPTCHA_HIDE_INPUT = FALSE;
                        $reply_form .= "<div class='m-t-10 m-b-10'>";
                        $reply_form .= "<label class='col-xs-12 col-sm-3'>".$locale['global_150']."</label><div class='col-xs-12 col-sm-9'>\n";
                        ob_start();
                        include INCLUDES."captchas/".$this->settings['captcha']."/captcha_display.php";
                        $reply_form .= ob_get_contents();
                        ob_end_clean();
                        if (!$_CAPTCHA_HIDE_INPUT) {
                            $reply_form .= "<br />\n<label for='captcha_code'>".$locale['global_151']."</label>";
                            $reply_form .= "<br />\n<input type='text' id='captcha_code-".$row['comment_id']."' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' />\n";
                        }
                        $reply_form .= "</div>\n";
                        $reply_form .= "</div>\n";
                    }

                    $reply_form .= form_button('post_comment', $locale['c102'], $row['comment_id'],
                                               array(
                                                   'class' => 'post_comment_reply btn-success m-t-10',
                                                   'input_id' => 'post_comment-'.$row['comment_id']
                                               )
                    );
                    $reply_form .= closeform();

                    if ($this->jquery_enabled === TRUE) {
                        $reply_form .= "</div>";
                    }
                }

                /** formats $row */
                $row = array(
                    "comment_id" => $row['comment_id'],
                    "comment_cat" => $row['comment_cat'],
                    "i" => $i,
                    "user_avatar" => display_avatar($row, '50px', '', FALSE, 'img-rounded'),
                    "user" => array(
                        "user_id" => $row['user_id'],
                        "user_name" => $row['user_name'],
                        "user_avatar" => $row['user_avatar'],
                        "status" => $row['user_status'],
                        "groups" => getgroupname($row['user_groups']),
                    ),
                    "reply_link" => $this->jquery_enabled === TRUE ? "" : clean_request("comment_reply=".$row['comment_id'], array("comment_reply"),
                                                                                        FALSE),
                    "reply_form" => $reply_form,
                    'ratings' => $row['ratings'],
                    "comment_datestamp" => showdate('shortdate', $row['comment_datestamp']),
                    "comment_time" => timer($row['comment_datestamp']),
                    "comment_subject" => "<!--comment_subject-->\n".$row['comment_subject']."\n<!--//comment_subject-->\n",
                    "comment_message" => "<!--comment_message-->\n".nl2br(parseubb(parsesmileys($row['comment_message'])))."<!--//comment_message-->\n",
                    "comment_name" => $row['user_name'] ? profile_link($row['comment_name'], $row['user_name'],
                                                                       $row['user_status']) : $row['comment_name'],
                );
                $row += $actions;

                $id = $row['comment_id'];

                $parent_id = $row['comment_cat'] === NULL ? "0" : $row['comment_cat'];

                $data[$id] = $row;

                $this->c_arr['c_con'][$parent_id][$id] = $row;

                $this->settings['comments_sorting'] == "ASC" ? $i++ : $i--;

            endwhile;

            // Paginate the base array
            $arrays = array_chunk($this->c_arr['c_con'][0], $this->cpp);
            $indexed_arrays = array();
            $max_page = 0;
            if (!empty($arrays)) {
                foreach ($arrays as $index => $array_items) {
                    $page = $index * $this->cpp; // if 0, is 0, //3 if 1 is 3,//6 if 2 is 6
                    foreach ($array_items as $comment) {
                        $indexed_arrays[$page][$comment['comment_id']] = $comment;
                    }
                    $max_page = $page;
                }
            }

            $this->c_arr['c_con'][0] = $indexed_arrays[(isset($_GET['c_start']) && $_GET['c_start'] <= $max_page ? $_GET['c_start'] : 0)];
            $this->c_arr['c_info']['comments_per_page'] = $this->cpp;
            $this->c_arr['c_info']['comments_count'] = format_word(number_format($total_comments, 0), $this->locale['fmt_comment']);

        endif;
    }

}