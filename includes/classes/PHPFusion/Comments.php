<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Comments.php
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

namespace PHPFusion;

/**
 * Class Comments
 *
 * @package PHPFusion
 *          Rating is not working
 *          Edit is not working
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
    private static $params = [
        'comment_user'                     => '',
        'comment_item_type'                => '',
        'comment_db'                       => '',
        'comment_col'                      => '',
        'comment_item_id'                  => '',
        'clink'                            => '',
        'comment_allow_subject'            => TRUE,
        'comment_allow_reply'              => TRUE,
        'comment_allow_post'               => TRUE,
        'comment_allow_ratings'            => FALSE,
        'comment_allow_vote'               => TRUE,
        'comment_once'                     => FALSE,
        'comment_echo'                     => FALSE,
        'comment_title'                    => '',
        'comment_form_title'               => '',
        'comment_count'                    => TRUE,
        'comment_bbcode'                   => TRUE,
        'comment_tinymce'                  => FALSE,
        'comment_tinymce_skin'             => 'lightgray',
        'comment_custom_script'            => FALSE,
        'comment_post_callback_function'   => '', // trigger custom functions during post comment event
        'comment_edit_callback_function'   => '',  // trigger custom functions during reply event
        'comment_delete_callback_function' => '' // trigger custom functions during delete event
    ];

    private $locale;
    private $userdata;
    private $settings;

    private $post_link;

    private $c_arr = [
        'c_con'  => [],
        'c_info' => [
            'c_makepagenav' => FALSE,
            'admin_link'    => FALSE
        ]
    ];
    private $comment_params = [];
    private $comment_data = [];
    private $cpp;

    /**
     * Removes comment reply
     *
     * @param string $clink
     *
     * @return string
     */
    private static $clink = [];

    private static $c_start = 0;

    private function __construct() {
        // Set Settings
        $this->settings = fusion_get_settings();
        // Set Global Locale
        $this->locale = fusion_get_locale('',
            [
                LOCALE.LOCALESET."comments.php",
                LOCALE.LOCALESET."user_fields.php",
                LOCALE.LOCALESET."ratings.php"
            ]
        );

        // Set current userdata
        $this->userdata = fusion_get_userdata();
        // Post link?
        $this->post_link = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
        $this->post_link = preg_replace("^(&|\?)c_action=(edit|delete)&comment_id=\d*^", "", $this->post_link);
        // Comments Per Page
        $this->cpp = fusion_get_settings('comments_per_page');
    }

    /**
     * Get an instance by key
     *
     * @param array  $params
     * @param string $key
     *
     * @return static
     */
    public static function getInstance(array $params = [], $key = 'Default') {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
            self::$key = $key;
            $params['comment_key'] = $key;
            self::$params = $params + self::$params;
            self::setInstance($key);
        }

        return self::$instances[$key];
    }

    /**
     * @param string $key
     */
    private static function setInstance($key) {
        $obj = self::getInstance([], $key);
        $obj->setParams(self::$params);
        $obj->setEmptyCommentData();
        $obj->checkPermissions();
        $obj->executeCommentUpdate();
        $obj->getComments();
    }

    /**
     * Displays Comments
     */
    public function showComments() {

        $comments_form = '';
        $html = '';
        if ($this->settings['comments_enabled'] == TRUE) {
            $settings = fusion_get_settings();

            $clink = $this->getParams('clink');

            /**
             * Forms
             */
            if ($this->getParams('comment_allow_post')) {
                $edata = [
                    'comment_cat'     => 0,
                    'comment_subject' => '',
                    'comment_message' => '',
                ];

                if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit") && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
                    $edit_query = "
                        SELECT tcm.*
                        FROM ".DB_COMMENTS." tcm
                        WHERE comment_id=:comment_id AND comment_item_id=:comment_item_id AND comment_type=:comment_type AND comment_hidden=:comment_hidden";

                    $edit_param = [
                        ':comment_id'      => $_GET['comment_id'],
                        ':comment_item_id' => $this->getParams('comment_item_id'),
                        ':comment_type'    => $this->getParams('comment_item_type'),
                        ':comment_hidden'  => 0,
                    ];
                    $e_result = dbquery($edit_query, $edit_param);

                    if (dbrows($e_result)) {
                        $edata = dbarray($e_result);
                        if ((iADMIN && checkrights("C")) || (iMEMBER && $edata['comment_name'] == fusion_get_userdata('user_id') && isset($edata['user_name']))) {
                            $clink = $this->getParams('clink')."&c_action=edit&comment_id=".$edata['comment_id'];
                        }
                    }
                }

                $can_post = iMEMBER || fusion_get_settings('guestposts');
                // Comments form
                //$form_action = fusion_get_settings('site_path').str_replace('../', '', self::format_clink($clink));
                $form_action = self::formatClink($clink);

                $comments_form = openform('inputform', 'post', $form_action, ['form_id' => $this->getParams('comment_key').'-inputform']);
                $ratings_input = '';
                $_CAPTCHA_FORM_HTML = '';
                $_CAPTCHA_HTML_INPUT = '';

                if ($can_post) {
                    $comments_form .= form_hidden('comment_id', '', '', ['input_id' => $this->getParams('comment_key').'-comment_id']);
                    $comments_form .= form_hidden('comment_cat', '', $edata['comment_cat'], ['input_id' => $this->getParams('comment_key').'-comment_cat']);

                    /*
                     * Ratings Selector
                     */
                    if (fusion_get_settings('ratings_enabled') && $this->getParams('comment_allow_ratings') && $this->getParams('comment_allow_vote')) {
                        $ratings_input .= form_select('comment_rating', $this->locale['r106'], '',
                            [
                                'input_id' => $this->getParams('comment_key').'-comment_rating',
                                'options'  => [
                                    5 => $this->locale['r120'],
                                    4 => $this->locale['r121'],
                                    3 => $this->locale['r122'],
                                    2 => $this->locale['r123'],
                                    1 => $this->locale['r124']
                                ]
                            ]
                        );
                    }
                }
                // Captcha for Guest
                if (!iMEMBER && fusion_get_settings('guestposts') == TRUE && (!isset($_CAPTCHA_HIDE_INPUT) || (!$_CAPTCHA_HIDE_INPUT))) {
                    $_CAPTCHA_HIDE_INPUT = FALSE;
                    include INCLUDES.'captchas/'.$settings['captcha'].'/captcha_display.php';

                    $_CAPTCHA_FORM_HTML = display_captcha([
                        'captcha_id' => 'captcha_'.$this->getParams('comment_key'),
                        'input_id'   => 'captcha_code_'.$this->getParams('comment_key'),
                        'image_id'   => 'captcha_image_'.$this->getParams('comment_key')
                    ]);

                    if (!$_CAPTCHA_HIDE_INPUT) {
                        $_CAPTCHA_HTML_INPUT = form_text('captcha_code', $this->locale['global_151'], '', ['required' => TRUE, 'autocomplete_off' => TRUE, 'input_id' => 'captcha_code_'.$this->getParams('comment_key')]);
                    }
                }

                $avatar = display_avatar(fusion_get_userdata(), "50px", "", FALSE);

                $button = form_button('post_comment', $edata['comment_message'] ? $this->locale['c103'] : $this->locale['c102'], ($edata['comment_message'] ? $this->locale['c103'] : $this->locale['c102']),
                    ['class' => 'btn-primary spacer-sm post_comment', 'input_id' => $this->getParams('comment_key').'-post_comment']
                );

                $comments_form .= display_comments_form([
                    'comment_form_title'     => ($this->getParams('comment_form_title') ?: $this->locale['c111']),
                    'comment_form_id'        => $this->getParams('comment_key').'_edit_comment',
                    'user_avatar'            => $can_post ? $avatar : '',
                    'comment_name_input'     => ($can_post ? (iGUEST ? form_text('comment_name', $this->locale['c104'], '', ['max_length' => 30, 'required' => TRUE, 'input_id' => $this->getParams('comment_key').'-comment_name']) : '') : ''),
                    'comment_subject_input'  => ($can_post ? $this->getParams('comment_allow_subject') ? form_text('comment_subject', $this->locale['c113'], $edata['comment_subject'], ['required' => TRUE, 'input_id' => $this->getParams('comment_key').'-comment_subject']) : '' : ''),
                    'comment_message_input'  => ($can_post ? form_textarea($edata['comment_cat'] ? 'comment_message_reply' : 'comment_message', '', $edata['comment_message'],
                        [
                            'input_id'     => $this->getParams('comment_key')."-comment_message",
                            'required'     => 1,
                            'autosize'     => TRUE,
                            'form_name'    => 'inputform',
                            "tinymce"      => "simple",
                            'wordcount'    => TRUE,
                            'type'         => $this->getParams('comment_bbcode') ? 'bbcode' : ($this->getParams('comment_tinymce') ? 'tinymce' : 'text'),
                            'tinymce_skin' => $this->getParams('comment_tinymce_skin')
                        ]
                    ) : $this->locale['c105']),
                    'comments_ratings_input' => $ratings_input,
                    'comments_captcha'       => ['captcha' => $_CAPTCHA_FORM_HTML, 'input' => $_CAPTCHA_HTML_INPUT],
                    'comment_post'           => $can_post ? $button : '',
                    'comment_type'           => $this->getParams('comment_item_type'),
                    'clink'                  => $this->getParams('clink'),
                    'comment_item_id'        => $this->getParams('comment_item_id'),
                    'options'                => $this->getParams()
                ]);

                $comments_form .= closeform();
            }

            /**
             * Comments
             */
            $ratings_html = '';
            $c_info = $this->c_arr['c_info'];
            if (fusion_get_settings('ratings_enabled') && $this->getParams('comment_allow_ratings')) {
                if (!empty($c_info['ratings_count'])) {
                    $stars = '';
                    $ratings = [];

                    $remainder = 5 - (int)$c_info['ratings_count']['avg'];
                    for ($i = 1; $i <= $c_info['ratings_count']['avg']; $i++) {
                        $stars .= '<i class="fas fa-star text-warning"></i> ';
                    }
                    if ($remainder) {
                        for ($i = 1; $i <= $remainder; $i++) {
                            $stars .= '<i class="far fa-star text-lighter"></i> ';
                        }
                    }

                    for ($i = 5; $i >= 1; $i--) {
                        $bal = 5 - $i;
                        $stars_ = '';
                        for ($x = 1; $x <= $i; $x++) {
                            $stars_ .= '<i class="fas fa-star text-warning"></i> ';
                        }

                        for ($b = 1; $b <= $bal; $b++) {
                            $stars_ .= '<i class="far fa-star text-lighter"></i> ';
                        }

                        $progress_num = $c_info['ratings_count'][$i] == 0 ? 0 : round((($c_info['ratings_count'][$i] / $c_info['ratings_count']['total']) * 100), 1);
                        $progressbar = progress_bar($progress_num, '', ['height' => '10px', 'hide_info' => TRUE, 'progress_class' => 'm-0']);
                        $ratings[] = [
                            'stars'       => $stars_,
                            'stars_count' => $c_info['ratings_count'][$i] ?: 0,
                            'progressbar' => $progressbar
                        ];
                    }

                    $ratings_html = display_comments_ratings([
                        'stars'                 => $stars,
                        'reviews'               => format_word($c_info['ratings_count']['total'], $this->locale['fmt_review']),
                        'ratings'               => $ratings,
                        'ratings_remove_button' => $c_info['ratings_remove_form'] ?: ''
                    ]);
                }
            }

            // @bug: Split the array into page chunks. [0] for page 1, [1] for page 2
            // Display comments
            $no_comments_text = display_no_comments($this->locale['c101']);
            $comments = display_comments_listing([
                'comments_page'       => ($this->c_arr['c_info']['c_makepagenav'] ? "<div class='text-left'>".$this->c_arr['c_info']['c_makepagenav']."</div>\n" : ''),
                'comments_list'       => (!empty($this->c_arr['c_con']) ? $this->displayAllComments($this->c_arr['c_con'], 0, $this->getParams()) : $no_comments_text),
                'comments_admin_link' => $this->c_arr['c_info']['admin_link'],
            ]);

            $comments_listing = display_comments_section([
                'comment_count'   => ($this->getParams('comment_count') ? $this->c_arr['c_info']['comments_count'] : ''),
                'comment_ratings' => $ratings_html,
                'comments'        => $comments,
                'comments_form'   => $comments_form,
                'c_data'          => $this->c_arr['c_con'],
                'c_info'          => $this->c_arr['c_info'],
                'options'         => $this->getParams()
            ]);

            $html .= display_comments_ui([
                'comment_title'             => $this->getParams('comment_title'),
                'comment_count'             => ($this->getParams('comment_count') ? $this->c_arr['c_info']['comments_count'] : ''),
                'comment_container_id'      => $this->getParams('comment_key'),
                'comment_form_container_id' => $this->getParams('comment_key').'-comments_form',
                'comments_listing'          => $comments_listing,
                'comments_form'             => $comments_form
            ]);
        }

        if ($this->getParams('comment_echo')) {
            echo $html;
        }

        return $html;
    }

    /**
     * Comments Listing
     *
     * @param array        $c_data
     * @param int          $index
     * @param string|array $options
     *
     * @return string
     */
    private function displayAllComments($c_data, $index, $options) {
        $comments_html = '';
        foreach ($c_data[$index] as $comments_id => $data) {
            $data['comment_ratings'] = '';
            if (fusion_get_settings('ratings_enabled') && $this->getParams('comment_allow_ratings')) {
                $remainder = 5 - (int)$data['ratings'];
                for ($i = 1; $i <= $data['ratings']; $i++) {
                    $data['comment_ratings'] .= '<i class="fas fa-star text-warning"></i> ';
                }
                if ($remainder) {
                    for ($i = 1; $i <= $remainder; $i++) {
                        $data['comment_ratings'] .= '<i class="far fa-star text-lighter"></i> ';
                    }
                }
            }

            $data_api = \Defender::encode($options);

            $data += [
                'comment_list_id'      => 'c'.$data['comment_id'],
                'user_avatar'          => $data['user_avatar'],
                'user_name'            => $data['comment_name'],
                'comment_date'         => $data['comment_datestamp'],
                'comment_ratings'      => $data['comment_ratings'],
                'comment_subject'      => $data['comment_subject'],
                'comment_message'      => $data['comment_message'],
                'comment_reply_link'   => ($data['reply_link'] ? "<a href='".$data['reply_link']."' class='comments-reply display-inline' data-id='$comments_id'>".$this->locale['c112']."</a>" : ''),
                'comment_edit_link'    => ($data['edit_link'] ? "<a href='".$data['edit_link']['link']."' class='edit-comment display-inline' data-id='".$data['comment_id']."' data-api='$data_api' data-key='".$this->getParams('comment_key')."'>".$data['edit_link']['name']."</a>" : ''),
                'comment_delete_link'  => ($data['delete_link'] ? "<a href='".$data['delete_link']['link']."' class='delete-comment display-inline' data-id='".$data['comment_id']."' data-api='$data_api' data-type='".$options['comment_item_type']."' data-item='".$options['comment_item_id']."' data-key='".$this->getParams('comment_key')."'>".$data['delete_link']['name']."</a>" : ''),
                'comment_reply_form'   => ($data['reply_form'] ?: ''),
                'comment_sub_comments' => (isset($c_data[$data['comment_id']]) ? $this->displayAllComments($c_data, $data['comment_id'], $options) : '')
            ];
            $comments_html .= display_comments_list($data);
        }

        return $comments_html;
    }

    /**
     * Check permissions
     */
    private function checkPermissions() {
        $my_id = fusion_get_userdata('user_id');
        if (dbcount("(rating_id)", DB_RATINGS, "
            rating_user='".$my_id."'
            AND rating_item_id='".$this->getParams('comment_item_id')."'
            AND rating_type='".$this->getParams('comment_item_type')."'
            "
        )
        ) {
            $this->replaceParam('comment_allow_vote', FALSE); // allow ratings
        }
        if (dbcount("(comment_id)", DB_COMMENTS, "
            comment_name='".$my_id."' AND comment_cat='0'
            AND comment_item_id='".$this->getParams('comment_item_id')."'
            AND comment_type='".$this->getParams('comment_item_type')."'
            "
            )
            && $this->getParams('comment_once')
        ) {
            $this->replaceParam('comment_allow_post', FALSE); // allow post
        }
    }

    /**
     * Get Comment Object Parameter
     *
     * @param null $key null for all array
     *
     * @return null
     */
    public function getParams($key = NULL) {
        if ($key !== NULL) {
            return isset($this->comment_params[self::$key][$key]) ? $this->comment_params[self::$key][$key] : NULL;
        }

        return $this->comment_params[self::$key];
    }

    /**
     * Replace Comment Object Parameter
     *
     * @param $param
     * @param $value
     */
    public function replaceParam($param, $value) {
        if (isset($this->comment_params[self::$key][$param])) {
            $this->comment_params[self::$key][$param] = $value;
        }
    }

    /**
     * Set Comment Object Parameters
     *
     * @param array $params
     */
    private function setParams(array $params = []) {
        $this->comment_params[self::$key] = $params;
    }

    /**
     * Set empty comment data
     */
    private function setEmptyCommentData() {
        $this->comment_data = [
            'comment_id'        => isset($_GET['comment_id']) && isnum($_GET['comment_id']) ? $_GET['comment_id'] : 0,
            'comment_name'      => '',
            'comment_subject'   => '',
            'comment_message'   => '',
            'comment_datestamp' => time(),
            'comment_item_id'   => $this->getParams('comment_item_id'),
            'comment_type'      => $this->getParams('comment_item_type'),
            'comment_cat'       => 0,
            'comment_ip'        => USER_IP,
            'comment_ip_type'   => USER_IP_TYPE,
            'comment_hidden'    => 0,
        ];
    }

    /**
     * Execute comment update
     */
    private function executeCommentUpdate() {

        $this->replaceParam('comment_user', $this->userdata['user_id']);

        // Non Jquery Actions
        if (isset($_GET['comment_reply'])) {
            add_to_jquery("scrollTo('comments_reply_form');");
        }

        /** Delete */
        if (isset($_GET['c_action']) && iMEMBER) {
            if ($_GET['c_action'] == 'delete') {
                $delete_query = "
                SELECT tcm.*, tcu.user_name
                FROM ".DB_COMMENTS." tcm
                LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
                WHERE comment_id=:comment_id AND comment_hidden=:comment_hidden
                ";
                $delete_param = [
                    ':comment_id'     => intval(stripinput($_GET['comment_id'])),
                    ':comment_hidden' => 0,
                ];

                $eresult = dbquery($delete_query, $delete_param);
                if (dbrows($eresult)) {
                    $edata = dbarray($eresult);
                    $redirect_link = $this->getParams('clink').($this->settings['comments_sorting'] == "ASC" ? "" : "&c_start=0")."#c".$_GET['comment_id'];
                    $child_query = "SELECT comment_id FROM ".DB_COMMENTS." WHERE comment_cat=:comment_cat_id";
                    $child_param = [':comment_cat_id' => intval($_GET['comment_id'])];
                    $result = dbquery($child_query, $child_param);
                    if (dbrows($result)) {
                        while ($child = dbarray($result)) {
                            dbquery("UPDATE ".DB_COMMENTS." SET comment_cat='".$edata['comment_cat']."' WHERE comment_id='".$child['comment_id']."'");
                        }
                    }
                    dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_id='".$edata['comment_id']."'".(iADMIN ? "" : "AND comment_name='".$this->userdata['user_id']."'"));
                    $func = $this->getParams('comment_delete_callback_function');
                    if (is_callable($func)) {
                        $func($this->getParams());
                    }

                    redirect($redirect_link);
                }
            }
        }

        /** Update & Save */
        // Ratings Removal Update
        // post comment_type, comment_item_id, remove_ratings_vote;
        if (iMEMBER && $this->getParams('comment_allow_ratings') && !$this->getParams('comment_allow_vote')) {
            if (isset($_POST['remove_ratings_vote'])) {
                $my_id = fusion_get_userdata('user_id');
                $delete_ratings = "DELETE FROM ".DB_RATINGS."
                WHERE rating_item_id='".$this->getParams('comment_item_id')."'
                AND rating_type = '".$this->getParams('comment_item_type')."'
                AND rating_user = '$my_id'";
                $result = dbquery($delete_ratings);
                if ($result) {
                    redirect(self::formatClink($this->getParams('clink')));
                }
            }
        }

        /**
         * Post Comment, Reply Comment
         */
        if ((iMEMBER || $this->settings['guestposts']) && isset($_POST['post_comment'])) {

            if (!iMEMBER && $this->settings['guestposts']) {
                // Process Captchas
                $_CAPTCHA_IS_VALID = FALSE;
                include INCLUDES."captchas/".$this->settings['captcha']."/captcha_check.php";
                if (!$_CAPTCHA_IS_VALID) {
                    fusion_stop();
                    addnotice("danger", $this->locale['u194']);
                }
            }

            $default_comment_id = isset($_POST['comment_id']) && isnum($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

            $comment_data = [
                'comment_id'      => isset($_GET['comment_id']) && isnum($_GET['comment_id']) ? $_GET['comment_id'] : $default_comment_id,
                'comment_name'    => iMEMBER ? $this->userdata['user_id'] : form_sanitizer($_POST['comment_name'], '', 'comment_name'),
                'comment_subject' => empty($_POST['comment_cat']) && isset($_POST['comment_subject']) ? form_sanitizer($_POST['comment_subject'], '', 'comment_subject') : '',
                'comment_item_id' => $this->getParams('comment_item_id'),
                'comment_type'    => $this->getParams('comment_item_type'),
                'comment_cat'     => form_sanitizer($_POST['comment_cat'], 0, 'comment_cat'),
                'comment_ip'      => USER_IP,
                'comment_ip_type' => USER_IP_TYPE,
                'comment_hidden'  => 0,
            ];

            // there is a conflict. the form above and the form below is same?
            $comment_data['comment_message'] = $comment_data['comment_cat'] ? form_sanitizer($_POST['comment_message_reply'], '', 'comment_message_reply') : form_sanitizer($_POST['comment_message'], '', 'comment_message');

            $ratings_query = "
            SELECT rating_id FROM ".DB_RATINGS." WHERE rating_item_id='".$comment_data['comment_item_id']."'
            AND rating_type='".$comment_data['comment_type']."' AND rating_user='".$comment_data['comment_name']."'
            ";

            $ratings_data = [];

            $ratings_id = dbresult(dbquery($ratings_query), 0);
            if ($this->getParams('comment_allow_ratings') && $this->getParams('comment_allow_vote') && isset($_POST['comment_rating'])) {
                $ratings_data = [
                    'rating_id'        => $ratings_id,
                    'rating_item_id'   => $this->getParams('comment_item_id'),
                    'rating_type'      => $this->getParams('comment_item_type'),
                    'rating_user'      => $comment_data['comment_name'],
                    'rating_vote'      => form_sanitizer($_POST['comment_rating'], 0, 'comment_rating'),
                    'rating_datestamp' => time(),
                    'rating_ip'        => USER_IP,
                    'rating_ip_type'   => USER_IP_TYPE
                ];
            }

            if (iMEMBER && $comment_data['comment_id']) {
                // Update comment
                if ((iADMIN && checkrights("C")) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS, "comment_id='".$comment_data['comment_id']."'
                        AND comment_item_id='".$this->getParams('comment_item_id')."'
                        AND comment_type='".$this->getParams('comment_item_type')."'
                        AND comment_name='".$this->userdata['user_id']."'
                        AND comment_hidden='0'")) && fusion_safe()
                ) {

                    $c_name_query = "SELECT comment_name FROM ".DB_COMMENTS." WHERE comment_id='".$comment_data['comment_id']."'";
                    $comment_data['comment_name'] = dbresult(dbquery($c_name_query), 0);

                    dbquery_insert(DB_COMMENTS, $comment_data, 'update');
                    $this->comment_params[self::$key]['post_id'] = $comment_data['comment_id'];

                    $func = $this->getParams('comment_edit_callback_function');
                    if (is_callable($func)) {
                        $func($this->getParams());
                    }

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
                    if (fusion_safe()) {
                        addnotice("success", $this->locale['c114']);
                        $_c = (isset($c_start) && isnum($c_start) ? $c_start : "");
                        $c_link = $this->getParams('clink');
                        redirect(self::formatClink("$c_link&c_start=$_c"));
                    }
                }
            } else {

                $comment_data['comment_datestamp'] = time();

                if (fusion_safe()) {

                    $c_start = 0;

                    if ($comment_data['comment_name'] && $comment_data['comment_message']) {

                        require_once INCLUDES."flood_include.php";

                        if (!flood_control("comment_datestamp", DB_COMMENTS, "comment_ip='".USER_IP."'")) {

                            $id = dbquery_insert(DB_COMMENTS, $comment_data, 'save');

                            $this->comment_params[self::$key]['post_id'] = $id;

                            $func = $this->getParams('comment_post_callback_function');
                            if (is_callable($func)) {
                                $func($this->getParams());
                            }

                            if (iMEMBER && fusion_get_settings('ratings_enabled') && $this->getParams('comment_allow_ratings') && $this->getParams('comment_allow_vote')) {
                                dbquery_insert(DB_RATINGS, $ratings_data, ($ratings_data['rating_id'] ? 'update' : 'save'));
                            }

                            if ($this->settings['comments_sorting'] == "ASC") {
                                $c_count = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$this->getParams('comment_item_id')."' AND comment_type='".$this->getParams('comment_item_type')."'");
                                $c_start = (ceil($c_count / $this->settings['comments_per_page']) - 1) * $this->settings['comments_per_page'];
                            }

                            redirect(self::formatClink($this->getParams('clink'))."&c_start=".$c_start."#c".$id);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $clink
     *
     * @return string
     */
    private static function formatClink($clink) {
        if (empty(self::$clink[$clink])) {
            $fusion_query = [];
            $url = ((array)parse_url(htmlspecialchars_decode($clink))) + [
                    'path'  => '',
                    'query' => ''
                ];
            if ($url['query']) {
                parse_str($url['query'], $fusion_query); // this is original.
            }
            $fusion_query = array_diff_key($fusion_query, array_flip(["comment_reply"]));
            $prefix = $fusion_query ? '?' : '';
            self::$clink[$clink] = $url['path'].$prefix.http_build_query($fusion_query);
        }

        return (string)self::$clink[$clink];
    }

    /*
     * Fetches comment data
     */
    private function getComments() {

        if (fusion_get_settings('comments_enabled')) {

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
                WHERE rating_type=:ratings_type AND rating_item_id=:ratings_item_id
                ";
                $ratings_bind = [
                    ':ratings_type'    => $this->getParams('comment_item_type'),
                    ':ratings_item_id' => $this->getParams('comment_item_id')
                ];
                $this->c_arr['c_info']['ratings_count'] = dbarray(dbquery($ratings_query, $ratings_bind));
                $this->c_arr['c_info']['ratings_remove_form'] = '';
                if ($this->getParams('comment_allow_ratings') && !$this->getParams('comment_allow_vote')) {
                    $ratings_html = openform('remove_ratings_frm', 'post', $this->getParams('clink'), [
                            'class'   => 'text-right',
                            'form_id' => $this->getParams('comment_key')."-remove_ratings_frm",
                        ]
                    );
                    $ratings_html .= form_hidden('comment_type', '', $this->getParams('comment_item_type'));
                    $ratings_html .= form_hidden('comment_item_id', '', $this->getParams('comment_item_id'));
                    $ratings_html .= form_button('remove_ratings_vote', $this->locale['r102'], 'remove_ratings_vote', ['input_id' => $this->getParams('comment_key')."-remove_ratings_vote", 'class' => 'btn-default btn-rmRatings']);
                    $ratings_html .= closeform();
                    $this->c_arr['c_info']['ratings_remove_form'] = $ratings_html;
                }
            }

            $this->c_arr['c_info']['comments_count'] = format_word(0, $this->locale['fmt_comment']);
            $this->c_arr['c_info']['total_comments'] = 0;

            $c_rows = dbcount("('comment_id')", DB_COMMENTS, "comment_item_id=:comment_item_id AND comment_type=:comment_item_type AND comment_hidden=:comment_hidden",
                [
                    ':comment_item_id'   => $this->getParams('comment_item_id'),
                    ':comment_item_type' => $this->getParams('comment_item_type'),
                    ':comment_hidden'    => 0
                ]
            );

            $this->c_arr['c_info']['total_comments'] = $c_rows;

            $root_comment_rows = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id=:comment_item_id AND comment_type=:comment_item_type AND comment_cat=:zero AND comment_hidden=:zero2",
                [
                    ':comment_item_type' => $this->getParams('comment_item_type'),
                    ':comment_item_id'   => $this->getParams('comment_item_id'),
                    ':zero'              => 0,
                    ':zero2'             => 0,
                ]);

            if ($root_comment_rows) {

                // Pagination control string
                self::$c_start = isset($_GET['c_start_'.$this->getParams('comment_key')]) && isnum($_GET['c_start_'.$this->getParams('comment_key')]) ? $_GET['c_start_'.$this->getParams('comment_key')] : 0;
                // Only applicable if sorting is Ascending. If descending, the default $c_start is always 0 as latest.
                if (fusion_get_settings('comments_sorting') == 'ASC') {
                    $getname = 'c_start_'.$this->getParams('comment_key');
                    if (!isset($_GET[$getname]) && $root_comment_rows > $this->cpp) {
                        self::$c_start = (ceil($root_comment_rows / $this->cpp) - 1) * $this->cpp;
                    }
                }

                $comment_query = "
                    SELECT tcm.* ".($this->getParams('comment_allow_ratings') && fusion_get_settings('ratings_enabled') ? ", tcr.rating_vote 'ratings'" : '')."
                    FROM ".DB_COMMENTS." tcm
                    ".($this->getParams('comment_allow_ratings') && fusion_get_settings('ratings_enabled') ? "LEFT JOIN ".DB_RATINGS." tcr ON tcr.rating_item_id=tcm.comment_item_id AND tcr.rating_type=tcm.comment_type AND tcr.rating_user=tcm.comment_name" : '')."
                    WHERE comment_item_id=:comment_item_id AND comment_type=:comment_item_type AND comment_hidden=:comment_hidden AND comment_cat = 0
                    ORDER BY comment_datestamp ".$this->settings['comments_sorting'].", comment_id ASC, comment_cat ASC LIMIT ".self::$c_start.", ".$this->cpp."
                ";
                $comment_bind = [
                    ':comment_item_id'   => $this->getParams('comment_item_id'),
                    ':comment_item_type' => $this->getParams('comment_item_type'),
                    ':comment_hidden'    => 0
                ];

                $query = dbquery($comment_query, $comment_bind);

                if (dbrows($query)) {

                    $i = ($this->settings['comments_sorting'] == "ASC" ? self::$c_start + 1 : $root_comment_rows - self::$c_start);

                    if ($root_comment_rows > $this->cpp) {
                        $this->c_arr['c_info']['c_makepagenav'] = makepagenav(self::$c_start, $this->cpp, $root_comment_rows, 3, $this->getParams('clink').(stristr($this->getParams('clink'), '?') ? "&" : '?'), "c_start_".$this->getParams('comment_key'));
                    }

                    if (iADMIN && checkrights('C')) {
                        $this->c_arr['c_info']['admin_link'] = "<!--comment_admin-->\n";
                        $this->c_arr['c_info']['admin_link'] .= "<a href='".ADMIN."comments.php".fusion_get_aidlink()."&ctype=".$this->getParams('comment_item_type')."&comment_item_id=".$this->getParams('comment_item_id')."'>".$this->locale['c106']."</a>";
                    }
                    while ($row = dbarray($query)) {
                        $this->parseCommentsData($row, $i);
                        $this->settings['comments_sorting'] == "ASC" ? $i++ : $i--;
                    }
                    $this->c_arr['c_info']['comments_per_page'] = $this->cpp;
                    $this->c_arr['c_info']['comments_count'] = format_word(number_format($this->c_arr['c_info']['total_comments']), $this->locale['fmt_comment']);
                }
            }
        }
    }

    /*
     * Parse comment results
     */
    private function parseCommentsData($row, $i) {
        $can_reply = iMEMBER || fusion_get_settings('guestposts');
        $garray = [];

        if (!isnum($row['comment_name'])) {
            $garray = [
                'user_id'     => 0,
                'user_name'   => $row['comment_name'],
                'user_avatar' => '',
                'user_status' => 0,
            ];
        }

        $row = array_merge_recursive($row, isnum($row['comment_name']) ? fusion_get_user($row['comment_name']) : $garray);

        $actions = [
            'edit_link'   => '',
            'delete_link' => ''
        ];
        if ((iADMIN && checkrights("C")) || (iMEMBER && $row['comment_name'] == $this->userdata['user_id'] && isset($row['user_name']))) {
            $edit_link = $this->getParams('clink')."&c_action=edit&comment_id=".$row['comment_id']."#edit_comment"; //clean_request('c_action=edit&comment_id='.$row['comment_id'], array('c_action', 'comment_id'),FALSE)."#edit_comment";
            $delete_link = $this->getParams('clink')."&c_action=delete&comment_id=".$row['comment_id']; //clean_request('c_action=delete&comment_id='.$row['comment_id'], array('c_action', 'comment_id'), FALSE);
            $actions = [
                "edit_link"   => ['link' => $edit_link, 'name' => $this->locale['edit']],
                "delete_link" => ['link' => $delete_link, 'name' => $this->locale['delete']]
            ];
        }
        // Reply Form
        $reply_form = '';
        if ($this->getParams('comment_allow_reply') && (isset($_GET['comment_reply']) && $_GET['comment_reply'] == $row['comment_id']) && $can_reply) {

            $this->comment_data['comment_cat'] = $row['comment_id'];

            $reply_form .= openform('comments_reply_frm-'.$row['comment_id'], 'post', self::formatClink($this->getParams('clink')));

            $_CAPTCHA_HTML = '';
            $_CAPTCHA_INPUT = '';

            if (iGUEST && (!isset($_CAPTCHA_HIDE_INPUT) || (!$_CAPTCHA_HIDE_INPUT))) {
                $_CAPTCHA_HIDE_INPUT = FALSE;
                include INCLUDES.'captchas/'.fusion_get_settings('captcha').'/captcha_display.php';

                $_CAPTCHA_HTML = display_captcha([
                    'captcha_id' => 'reply_captcha_'.$this->getParams('comment_key'),
                    'input_id'   => 'reply_captcha_code_'.$this->getParams('comment_key'),
                    'image_id'   => 'reply_captcha_image_'.$this->getParams('comment_key')
                ]);

                if (!$_CAPTCHA_HIDE_INPUT) {
                    $_CAPTCHA_INPUT = form_text('captcha_code', $this->locale['global_151'], '', ['required' => TRUE, 'autocomplete_off' => TRUE, 'input_id' => 'captcha_code_'.$this->getParams('comment_key')]);
                }
            }

            $reply_form .= display_comments_reply_form([
                'comment_name'    => (iGUEST ? form_text('comment_name', fusion_get_locale('c104'), $this->comment_data['comment_name'],
                    [
                        'max_length' => 30,
                        'input_id'   => 'comment_name-'.$row['comment_id'],
                        'form_name'  => 'comments_reply_frm-'.$row['comment_id']
                    ]
                ) : ''),
                'comment_message' => form_textarea("comment_message_reply", "", $this->comment_data['comment_message'],
                    [
                        "tinymce"   => "simple",
                        'autosize'  => TRUE,
                        "type"      => fusion_get_settings("tinymce_enabled") ? "tinymce" : "bbcode",
                        "input_id"  => "comment_message-".$row['comment_id'],
                        'form_name' => 'comments_reply_frm-'.$this->comment_data['comment_cat'],
                        "required"  => TRUE
                    ]),
                'comment_captcha' => ['captcha' => $_CAPTCHA_HTML, 'input' => $_CAPTCHA_INPUT],
                'comment_post'    => form_button('post_comment', fusion_get_locale('c102'), $row['comment_id'], [
                        'class'    => 'post_comment btn-success m-t-10',
                        'input_id' => 'post_comment-'.$row['comment_id']
                    ]
                )
            ]);

            $reply_form .= form_hidden("comment_cat", "", $this->comment_data['comment_cat'], ['input_id' => 'comment_cat-'.$row['comment_id']]);
            $reply_form .= closeform();
        }
        /** formats $row */
        $row = [
                "comment_id"        => $row['comment_id'],
                "comment_cat"       => $row['comment_cat'],
                "i"                 => $i,
                "user_avatar"       => isnum($row['comment_name']) ? display_avatar($row, '50px', '', FALSE, 'm-t-5') : display_avatar([], '50px', '', FALSE, 'm-t-5'),
                "user"              => [
                    "user_id"     => $row['user_id'],
                    "user_name"   => $row['user_name'],
                    "user_avatar" => $row['user_avatar'],
                    "status"      => $row['user_status'],
                ],
                "reply_link"        => $can_reply == TRUE ? self::formatClink($this->getParams('clink')).'&comment_reply='.$row['comment_id'].'#c'.$row['comment_id'] : '',
                "reply_form"        => $reply_form,
                'ratings'           => isset($row['ratings']) ? $row['ratings'] : '',
                'datestamp'         => $row['comment_datestamp'],
                "comment_datestamp" => showdate('longdate', $row['comment_datestamp']),
                "comment_time"      => timer($row['comment_datestamp']),
                "comment_subject"   => $row['comment_subject'],
                "comment_message"   => parse_text($row['comment_message'], ['decode' => FALSE, 'add_line_breaks' => TRUE]),
                "comment_name"      => isnum($row['comment_name']) ? profile_link($row['comment_name'], $row['user_name'], $row['user_status']) : $row['comment_name']
            ] + $actions;


        $comment_query = "
            SELECT tcm.* ".($this->getParams('comment_allow_ratings') && fusion_get_settings('ratings_enabled') ? ", tcr.rating_vote 'ratings'" : '')."
            FROM ".DB_COMMENTS." tcm
            ".($this->getParams('comment_allow_ratings') && fusion_get_settings('ratings_enabled') ? "LEFT JOIN ".DB_RATINGS." tcr ON tcr.rating_item_id=tcm.comment_item_id AND tcr.rating_type=tcm.comment_type AND tcr.rating_user=tcm.comment_name" : '')."
            WHERE comment_item_id=:comment_item_id AND comment_type=:comment_item_type AND comment_hidden=:comment_hidden AND comment_cat=:comment_cat
            ORDER BY comment_datestamp ".$this->settings['comments_sorting'].", comment_id ASC, comment_cat ASC LIMIT ".self::$c_start.", ".$this->cpp."
        ";
        $comment_bind = [
            ':comment_item_id'   => $this->getParams('comment_item_id'),
            ':comment_item_type' => $this->getParams('comment_item_type'),
            ':comment_hidden'    => 0,
            ':comment_cat'       => $row['comment_id']
        ];

        $c_result = dbquery($comment_query, $comment_bind);

        //$c_result = dbquery("SELECT * FROM ".DB_COMMENTS." WHERE comment_cat=:comment_cat", [':comment_cat' => $row['comment_id']]);
        if (dbrows($c_result)) {
            $x = 1;
            while ($c_rows = dbarray($c_result)) {
                $this->parseCommentsData($c_rows, $x);
                $this->settings['comments_sorting'] == "ASC" ? $x++ : $x--;
            }
        }

        $id = $row['comment_id'];
        $parent_id = $row['comment_cat'] === NULL ? "0" : $row['comment_cat'];
        //$data[$id] = $row;
        $this->c_arr['c_con'][$parent_id][$id] = $row;
    }

}

require_once THEMES.'templates/global/comments.tpl.php';
