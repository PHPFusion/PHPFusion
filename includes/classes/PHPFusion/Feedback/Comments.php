<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: /PHPFusion/Feedback/Comments.php
| Author: Frederick MC Chan (Hien)
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

class Comments {

    private static $instances = NULL;

    /**
     * Get an instance by key
     * @return static
     */
    private static $default_params = array(
        'comment_item_type' => '',
        'comment_db' => '',
        'comment_col' => '',
        'comment_item_id' => '',
        'clink' => '',
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

        $this->jquery_enabled = fusion_get_settings('comments_jquery_enabled') ? TRUE : FALSE;
        $this->cpp = fusion_get_settings('comments_per_page');
    }

    public static function getInstance(array $params = array()) {
        if (self::$instances === NULL) {
            self::$instances = new static();
            // set the parameters
            $params += self::$default_params;
            self::$instances->setParams($params);
            self::$instances->setEmptyCommentData();
            self::$instances->execute_CommentUpdate();
            self::$instances->get_Comments();
        }
        return self::$instances;
    }

    private function setParams(array $params = array()) {
        $this->comment_params = $params;
    }

    private function setEmptyCommentData() {
        $this->comment_data = array(
            'comment_id' => isset($_GET['comment_id']) && isnum($_GET['comment_id']) ? $_GET['comment_id'] : 0,
            'comment_name' => '',
            'comment_message' => '',
            'comment_datestamp' => time(),
            'comment_item_id' => $this->comment_params['comment_item_id'],
            'comment_type' => $this->comment_params['comment_item_type'],
            'comment_cat' => 0,
            'comment_ip' => USER_IP,
            'comment_ip_type' => USER_IP_TYPE,
            'comment_hidden' => 0,
        );
    }

    private function execute_CommentUpdate() {

        /**
         * Documentation for Ajax Token and Fields.
         * To do remote calls for token,
         * 1. the openform shall register the remote url full path
         * 2. in your jquery - POST the `form id`
         */
        if ($this->jquery_enabled === TRUE) {
            add_to_jquery($this->getJs());
        }

        if (isset($_GET['comment_reply'])) {
            add_to_jquery("scrollTo('comments_reply_form');");
        }

        /** Delete */
        if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "delete")
            && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))
        ) {
            if ((iADMIN && checkrights("C")) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS,
                                                                    "comment_id='".$_GET['comment_id']."' AND comment_name='".$this->userdata['user_id']."'"))
            ) {

                // Find immediate child. Push to root
                $child_query = "SELECT comment_id FROM ".DB_COMMENTS." WHERE comment_cat='".$_GET['comment_id']."'";
                $result = dbquery($child_query);
                if (dbrows($result)) {
                    while ($child = dbarray($result)) {
                        dbquery("UPDATE ".DB_COMMENTS." SET comment_cat=0 WHERE comment_id='".$child['comment_id']."'");
                    }
                }

                dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_id='".$_GET['comment_id']."'".(iADMIN ? "" : "AND comment_name='".$this->userdata['user_id']."'"));
            }
            redirect($this->comment_params['clink'].($this->settings['comments_sorting'] == "ASC" ? "" : "&amp;c_start=0"));
        }

        /** Update */
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

            $comment_data = array(
                'comment_id' => isset($_GET['comment_id']) && isnum($_GET['comment_id']) ? $_GET['comment_id'] : 0,
                'comment_name' => iMEMBER ? $this->userdata['user_id'] : form_sanitizer($_POST['comment_name'], '', 'comment_name'),
                'comment_message' => form_sanitizer($_POST['comment_message'], '', 'comment_message'),
                'comment_datestamp' => time(),
                'comment_item_id' => $this->comment_params['comment_item_id'],
                'comment_type' => $this->comment_params['comment_item_type'],
                'comment_cat' => form_sanitizer($_POST['comment_cat'], 0, 'comment_cat'),
                'comment_ip' => USER_IP,
                'comment_ip_type' => USER_IP_TYPE,
                'comment_hidden' => 0,
            );

            if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit") && $comment_data['comment_id']) {

                // Update comment
                if ((iADMIN && checkrights("C")) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS, "comment_id='".$comment_data['comment_id']."'
                        AND comment_item_id='".$this->comment_params['comment_item_id']."'
                        AND comment_type='".$this->comment_params['comment_type']."'
                        AND comment_name='".$this->userdata['user_id']."'
                        AND comment_hidden='0'")) && \defender::safe()
                ) {

                    $c_name_query = "SELECT comment_name FROM ".DB_COMMENTS." WHERE comment_id='".$comment_data['comment_id']."'";
                    $comment_data['comment_name'] = dbresult(dbquery($c_name_query), 0);

                    dbquery_insert(DB_COMMENTS, $comment_data, 'update');

                    if ($this->settings['comments_sorting'] == "ASC") {
                        $c_operator = "<=";
                    } else {
                        $c_operator = ">=";
                    }
                    $c_count = dbcount("(comment_id)", DB_COMMENTS, "comment_id".$c_operator."'".$comment_data['comment_id']."'
                            AND comment_item_id='".$this->comment_params['comment_item_id']."'
                            AND comment_type='".$this->comment_params['comment_type']."'");

                    $c_start = (ceil($c_count / $this->settings['comments_per_page']) - 1) * $this->settings['comments_per_page'];

                    if ($_POST['post_comment'] !== 'ajax') {
                        addNotice("success", $this->locale['global_027']);
                        redirect(self::format_clink($this->comment_params['clink'])."&amp;c_start=".(isset($c_start) && isnum($c_start) ? $c_start : ""));
                    }

                }

            } else {

                // Save New comment
                if ($_POST['post_comment'] !== 'ajax') {
                    if (!dbcount("(".$this->comment_params['comment_col'].")", $this->comment_params['comment_db'],
                                 $this->comment_params['comment_col']."='".$this->comment_params['comment_item_id']."'")
                    ) {
                        redirect(BASEDIR."index.php");
                    }
                }


                if (\defender::safe()) {
                    $c_start = 0;
                    $id = 0;

                    if ($comment_data['comment_name'] && $comment_data['comment_message']) {

                        require_once INCLUDES."flood_include.php";

                        if (!flood_control("comment_datestamp", DB_COMMENTS, "comment_ip='".USER_IP."'")) {

                            dbquery_insert(DB_COMMENTS, $comment_data, 'save');

                            $id = dblastid();

                            if ($this->settings['comments_sorting'] == "ASC") {
                                $c_count = dbcount("(comment_id)", DB_COMMENTS,
                                                   "comment_item_id='".$this->comment_params['comment_item_id']."' AND comment_type='".$this->comment_params['comment_type']."'");
                                $c_start = (ceil($c_count / $this->settings['comments_per_page']) - 1) * $this->settings['comments_per_page'];
                            }

                        }

                        if ($_POST['post_comment'] !== 'ajax') {
                            redirect(self::format_clink($this->comment_params['clink'])."&amp;c_start=".$c_start."#c".$id);
                        }
                    }
                }
            }
        }

    }

    public function getJs() {
        return "
        var comment_btn_id = 'post_comment';
        var comment_form_id = 'inputform';
        var remote_url = '".FUSION_ROOT.CLASSES."PHPFusion/Feedback/Comments.ajax.php';
        var comment_container_id = '".$this->comment_params['comment_item_type']."-".$this->comment_params['comment_item_id']."-fusion_comments';

        PostComments(comment_btn_id, comment_form_id, remote_url, comment_container_id);

        function PostComments(comment_btn_id, comment_form_id, remote_url, comment_container_id) {
            $('#'+ comment_btn_id).bind('click', function(e) {
            e.preventDefault();
                var formData = {
                    'form_id' : comment_form_id,
                    'comment_name' : $('#comment_name').val() ? $('#comment_name').val() : '',
                    'comment_cat' : $('#comment_cat').val() ? $('#comment_cat').val() : '0',
                    'comment_message' : $('#comment_message').val() ? $('#comment_message').val() : '',
                    'captcha_code' : $('#captcha_code').val() ? $('#captcha_code').val() : '0',
                    'comment_item_type' : '".$this->comment_params['comment_item_type']."',
                    'comment_db' : '".$this->comment_params['comment_db']."',
                    'comment_col' : '".$this->comment_params['comment_col']."',
                    'comment_item_id' : '".$this->comment_params['comment_item_id']."',
                    'clink' : '".$this->comment_params['clink']."',
                    'post_comment' : 'ajax'
                }
                var sendData = $('#'+ comment_form_id).serialize() + '&' + $.param(formData);
                $.ajax({
                    url: remote_url,
                    type: 'POST',
                    dataType: 'html',
                    async: true,
                    data : sendData,
                    success: function(result){
                        //console.log(result);
                        $('#'+comment_container_id).html(result);
                         PostComments(comment_btn_id, comment_form_id, remote_url, comment_container_id);
                    },
                    error: function(result) {
                        console.log(result);
                        new PNotify({
                            title: 'Errors:',
                            text: 'There are errors posting comments. Please contact the administrator',
                            icon: 'notify_icon n-attention',
                            animation: 'fade',
                            width: 'auto',
                            delay: '3000'
                        });
                    }
                });
            });
        }
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

    private function get_Comments() {

        $this->c_arr['c_info']['comments_count'] = format_word(0, $this->locale['fmt_comment']);

        // Handle Comment Posts
        $c_rows = dbcount("('comment_id')", DB_COMMENTS,
                          "comment_item_id='".$this->comment_params['comment_item_id']."' AND comment_type='".$this->comment_params['comment_item_type']."' AND comment_hidden='0' AND comment_cat='0'");

        if (!isset($_GET['c_start']) && $c_rows > $this->cpp) {
            $_GET['c_start'] = (ceil($c_rows / $this->cpp) - 1) * $this->cpp;
        }

        if (!isset($_GET['c_start']) || !isnum($_GET['c_start'])) {
            $_GET['c_start'] = 0;
        }

        $comment_query = "
            SELECT tcm.*, tcu.user_id, tcu.user_name, tcu.user_avatar, tcu.user_status
            FROM ".DB_COMMENTS." tcm
            LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
            WHERE comment_item_id='".$this->comment_params['comment_item_id']."' AND comment_type='".$this->comment_params['comment_item_type']."' AND comment_hidden='0'
            ORDER BY comment_datestamp ".$this->settings['comments_sorting'].", comment_cat ASC";

        $query = dbquery($comment_query);

        $total_comments = dbrows($query);

        if (dbrows($query) > 0) :

            $i = ($this->settings['comments_sorting'] == "ASC" ? $_GET['c_start'] + 1 : $c_rows - $_GET['c_start']);

            if ($c_rows > $this->cpp) {
                $this->c_arr['c_info']['c_makepagenav'] = makepagenav($_GET['c_start'], $this->cpp, $c_rows, 3,
                                                                      $this->comment_params['clink']."&amp;",
                                                                      "c_start");
            }

            if (iADMIN && checkrights("C")) {
                $this->c_arr['c_info']['admin_link'] = "<!--comment_admin-->\n";
                $this->c_arr['c_info']['admin_link'] .= "<a href='".ADMIN."comments.php".fusion_get_aidlink()."&amp;ctype=".$this->comment_params['comment_item_type']."&amp;comment_item_id=".$this->comment_params['comment_item_id']."'>".$this->locale['c106']."</a>";
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
                    $edit_link = clean_request('c_action=edit&comment_id='.$row['comment_id'],
                                               array('c_action', 'comment_id'), FALSE)."#edit_comment";
                    $delete_link = clean_request('c_action=delete&comment_id='.$row['comment_id'],
                                                 array('c_action', 'comment_id'), FALSE);
                    $comment_actions = "<!---comment_actions--><div class='btn-group'>
                        <a class='btn btn-xs btn-default' href='$edit_link'>".$this->locale['c108']."</a>
                        <a class='btn btn-xs btn-default' href='$delete_link' onclick=\"return confirm('".$this->locale['c110']."');\"><i class='fa fa-trash'></i>".$this->locale['c109']."</a>
                        </div><!---//comment_actions-->
                    ";

                    $actions = array(
                        "edit_link" => array('link' => $edit_link, 'name' => $this->locale['c108']),
                        "delete_link" => array('link' => $delete_link, 'name' => $this->locale['c109']),
                        "edit_dell" => $comment_actions
                    );

                }

                $reply_form = "";

                if (isset($_GET['comment_reply']) && $_GET['comment_reply'] == $row['comment_id']) {

                    $locale = fusion_get_locale();
                    $comment_data['comment_cat'] = $row['comment_id'];
                    $reply_form = openform("comments_reply_form", "post", FUSION_REQUEST,
                                           array(
                                               "class" => "comments_reply_form",
                                               "remote_url" => self::$jquery_enabled === TRUE ? fusion_get_settings("site_path")."includes/classes/PHPFusion/Feedback/Comments.ajax.php" : ""
                                           )
                    );

                    if (iGUEST) {
                        $reply_form .= form_text('comment_name', fusion_get_locale('c104'), $comment_data['comment_name'],
                                                 array('max_length' => 30));
                    }

                    $reply_form .= form_hidden("comment_cat", "", $comment_data['comment_cat']);
                    $reply_form .= form_textarea("comment_message", "", $comment_data['comment_message'], array(
                        "tinymce" => "simple",
                        "autogrow" => TRUE,
                        "type" => fusion_get_settings("tinymce_enabled") ? "tinymce" : "bbcode",
                        "input_id" => "comment_message-".$i,
                        "required" => TRUE,
                    ));

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
                            $reply_form .= "<br />\n<input type='text' id='captcha_code' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' />\n";
                        }
                        $reply_form .= "</div>\n";
                        $reply_form .= "</div>\n";
                    }

                    $reply_form .= form_button('post_comment', $locale['c102'], $locale['c102'], array('class' => 'btn-success m-t-10'));
                    $reply_form .= closeform();
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
                        "status" => $row['user_status']
                    ),
                    "reply_link" => clean_request("comment_reply=".$row['comment_id'], array("comment_reply"), FALSE),
                    "reply_form" => $reply_form,
                    "comment_datestamp" => showdate('shortdate', $row['comment_datestamp']),
                    "comment_time" => timer($row['comment_datestamp']),
                    "comment_message" => "<!--comment_message-->\n".nl2br(parseubb(parsesmileys($row['comment_message'])))."<!--//comment_message-->\n",
                    "comment_name" => $row['user_name'] ? profile_link($row['comment_name'], $row['user_name'], $row['user_status'],
                                                                       'strong text-dark') : $row['comment_name'],
                );
                $row += $actions;

                $id = $row['comment_id'];

                $parent_id = $row['comment_cat'] === NULL ? "0" : $row['comment_cat'];

                $data[$id] = $row;

                $this->c_arr['c_con'][$parent_id][$id] = $row;

                $this->settings['comments_sorting'] == "ASC" ? $i++ : $i--;
                //$this->settings['comments_sorting'] == $i++;

            endwhile;

            // Paginate the base array
            $arrays = array_chunk($this->c_arr['c_con'][0], $this->cpp);
            $indexed_arrays = array();
            if (!empty($arrays)) {
                foreach ($arrays as $index => $array_items) {
                    $page = $index * $this->cpp; // if 0, is 0, //3 if 1 is 3,//6 if 2 is 6
                    foreach ($array_items as $comment) {
                        $indexed_arrays[$page][$comment['comment_id']] = $comment;
                    }
                }
            }

            $this->c_arr['c_con'][0] = $indexed_arrays[(isset($_GET['c_start']) ? $_GET['c_start'] : 0)];

            $this->c_arr['c_info']['comments_per_page'] = $this->cpp;

            $this->c_arr['c_info']['comments_count'] = format_word(number_format($total_comments, 0), $this->locale['fmt_comment']);

        endif;
    }

    /**
     * once i click button
     * must update
     * must show latest html results
     *
     * what happen is when i click post button, jquery kicks in.
     * then on the second one. it did not update.
     *
     *
     *
     */

    public function showComments() {

        if ($this->settings['comments_enabled'] == "1") {
            echo "<div id='".$this->comment_params['comment_item_type']."-".$this->comment_params['comment_item_id']."-fusion_comments'>\n";
            echo "<a id='comments' name='comments'></a>";
            render_comments($this->c_arr['c_con'], $this->c_arr['c_info']);
            render_comments_form($this->comment_params['comment_item_type'], $this->comment_params['clink'], $this->comment_params['comment_item_id'],
                                 isset($_CAPTCHA_HIDE_INPUT) ? $_CAPTCHA_HIDE_INPUT : FALSE);
            echo "</div>\n";
        }
    }

}