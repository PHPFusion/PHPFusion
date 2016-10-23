<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: render_functions.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
use PHPFusion\BreadCrumbs;

if (!defined("IN_FUSION")) {
    die("Access Denied");
}

// Render comments template
if (!function_exists("render_comments")) {
    /**
     * Show comments
     * @param       $c_data
     * @param       $c_info
     * @param array $options
     * @return string
     */
    function render_comments($c_data, $c_info, array $options = array()) {

        $locale = fusion_get_locale('', LOCALE.LOCALESET."comments.php");
        $locale += fusion_get_locale('', LOCALE.LOCALESET."ratings.php");
        /*
         * Get ratings information
         */
        $ratings_html = '';
        if (!empty($c_info['ratings_count'])) {
            $ratings_html = "<ul class='well clearfix p-0' style='height:190px'>\n";
            $ratings_html .= "<li class='col-xs-12 col-sm-6'>\n";
            for ($i = 1; $i <= $c_info['ratings_count']['avg']; $i++) {
                $ratings_html .= "<i class='fa fa-star text-warning fa-lg'></i>\n";
            }
            $ratings_html .= "<span class='text-lighter m-l-5'>".format_word($c_info['ratings_count']['total'], "review|reviews")."</span>\n";
            $ratings_html .= "</li>\n";
            $ratings_html .= "<li class='col-xs-12 col-sm-6'>\n";
            for ($i = 5; $i >= 1; $i--) {
                $bal = 5 - $i;
                $ratings_html .= "<div class='row'>\n";
                $ratings_html .= "<div class='display-inline-block m-r-5'>\n";
                for ($x = 1; $x <= $i; $x++) {
                    $ratings_html .= "<i class='fa fa-star text-warning'></i>\n";
                }
                for ($b = 1; $b <= $bal; $b++) {
                    $ratings_html .= "<i class='fa fa-star-o text-lighter'></i>\n";
                }
                $ratings_html .= "<span class='text-lighter m-l-5 m-r-5'>(".($c_info['ratings_count'][$i] ?: 0).")</span>";
                $ratings_html .= "</div>\n<div class='display-inline-block m-l-5' style='width:50%;'>\n";
                $progress_num = $c_info['ratings_count'][$i] > 0 ? floor($c_info['ratings_count'][$i] / $c_info['ratings_count']['total']) * 100 : 0;
                $ratings_html .= progress_bar($progress_num, '', '', '10px', FALSE, TRUE, FALSE, TRUE);
                $ratings_html .= "</div>\n";
                $ratings_html .= "</div>\n";
            }
            $ratings_html .= "</li>\n";
            $ratings_html .= "</ul>\n";
        }

        /*
         * Get comments
         */
        $comments_html = "";
        if (!empty($c_data)) {
            /*
             * Declare function for recursively calling comments
             */
            if (!function_exists("display_all_comments")) {

                function display_all_comments($c_data, $index = 0, $options) { //&$comments_html = FALSE

                    $comments_html = &$comments_html;

                    $locale = fusion_get_locale('', LOCALE.LOCALESET."comments.php");

                    foreach ($c_data[$index] as $comments_id => $data) {
                        $comments_html .= "<!---comment-".$data['comment_id']."--->\n<li id='c".$data['comment_id']."' class='m-b-15'>\n";
                        $comments_html .= "<div class='pull-left text-center m-r-15'>\n";
                        $comments_html .= $data['user_avatar'];
                        $comments_html .= "</div>\n";
                        $comments_html .= "<div class='overflow-hide'>\n";

                        $comments_html .= "<div class='comment_name display-inline-block m-r-10'>\n";
                        $comments_html .= $data['comment_name'];
                        $comments_html .= "<span class='comment_status text-lighter'>".$data['user']['groups']."</span> <small class='comment_date'>".$data['comment_datestamp']."</small>";
                        $comments_html .= "</div>\n";
                        $comments_html .= "<p class='ratings'>\n";
                        $remainder = 5 - $data['ratings'];
                        for ($i = 1; $i <= $data['ratings']; $i++) {
                            $comments_html .= "<i class='fa fa-star text-warning'></i>\n";
                        }
                        if ($remainder) {
                            for ($i = 1; $i <= $remainder; $i++) {
                                $comments_html .= "<i class='fa fa-star-o text-lighter'></i>\n";
                            }
                        }
                        $comments_html .= "</p>\n";
                        $comments_html .= "<p class='comment_title'>".$data['comment_subject']."</p>\n";
                        $comments_html .= "<p class='comment_message'>".$data['comment_message']."</p>\n";
                        if ($options['comment_allow_reply']) {
                            $comments_html .= "<a href='".$data['reply_link']."' class='comments-reply display-inline m-5 m-l-0' data-id='$comments_id'>".$locale['c112']."</a>\n&middot;";
                        }
                        $comments_html .= ($data['edit_link'] ? "<a href='".$data['edit_link']['link']."' class='edit-comment display-inline m-5' data-id='".$data['comment_id']."'>".$data['edit_link']['name']."</a>&middot;" : "");
                        $comments_html .= ($data['delete_link'] ? "<a href='".$data['delete_link']['link']."' class='comments-reply display-inline m-5'>".$data['delete_link']['name']."</a>" : "");
                        $comments_html .= "</div>\n";

                        if (!empty($data['reply_form'])) {
                            $comments_html .= $data['reply_form'];
                        }
                        // Replies is here
                        if (isset($c_data[$data['comment_id']])) {
                            $comments_html .= "<ul class='sub-comments'>\n";
                            $comments_html .= display_all_comments($c_data, $data['comment_id'], $options);
                            $comments_html .= "</ul>\n";
                        }
                        $comments_html .= "</li><!---//comment-".$data['comment_id']."--->";
                    }
                    return $comments_html;
                }
            }

            $c_makepagenav = ($c_info['c_makepagenav'] !== FALSE) ? "<div class=\"text-center m-b-5\">".$c_info['c_makepagenav']."</div>\n" : "";
            $comments_html .= "<ul class='comments clearfix'>\n";
            $comments_html .= display_all_comments($c_data, 0, $options);
            $comments_html .= $c_makepagenav;
            if ($c_info['admin_link'] !== FALSE) {
                $comments_html .= "<div style='float:right' class='comment_admin'>".$c_info['admin_link']."</div>\n";
            }
            $comments_html .= "</ul>\n";
        } else {
            $comments_html .= "<div class='well text-center'>\n";
            $comments_html .= $locale['c101']."\n";
            $comments_html .= "</div>\n";
        }

		// Comments form
        $html = "<div class='comments-panel'>\n";
        $html .= "<div class='comments-header'>\n";
        $html .= $options['comment_title'].($options['comment_count'] ? $c_info['comments_count'] : '');
        $html .= "</div>\n";
        $html .= "<div class='ratings overflow-hide m-b-20'>\n";
        $html .= $ratings_html;
        $html .= "</div>\n";
        $html .= "<div class='comments overflow-hide'>\n";
        $html .= $comments_html;
        $html .= "</div>\n";
        $html .= "</div>\n";

        return $html;
    }
}

if (!function_exists("render_comments_form")) {
    /**
     * Comment Form
     *
     * @param       $comment_type
     * @param       $clink
     * @param       $comment_item_id
     * @param       $_CAPTCHA_HIDE_INPUT
     * @param array $options
     * @return string
     */
    function render_comments_form($comment_type, $clink, $comment_item_id, $_CAPTCHA_HIDE_INPUT, array $options = array()) {

        $userdata = fusion_get_userdata();
        $settings = fusion_get_settings();
        $locale = fusion_get_locale('', LOCALE.LOCALESET."ratings.php");

        $edata = [
            'comment_cat' => 0,
            'comment_subject' => '',
            'comment_message' => '',
        ];

        if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit") && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
            $eresult = dbquery("SELECT tcm.*, tcu.user_name
				FROM ".DB_COMMENTS." tcm
				LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
				WHERE comment_id='".intval($_GET['comment_id'])."' AND comment_item_id='".intval($comment_item_id)."'
				AND comment_type='".$comment_type."' AND comment_hidden='0'");
            if (dbrows($eresult) > 0) {
                $edata = dbarray($eresult);
                if ((iADMIN && checkrights("C")) || (iMEMBER && $edata['comment_name'] == $userdata['user_id'] && isset($edata['user_name']))) {
                    $clink .= "&amp;c_action=edit&amp;comment_id=".$edata['comment_id'];
                }
            }
        }

        // Comments form
        if (iMEMBER || fusion_get_settings("guestposts") == 1) {
            $comments_form = openform('inputform', 'post', $clink,
                                      array(
                                          'remote_url' => fusion_get_settings('comments_jquery') ? fusion_get_settings("site_path")."includes/classes/PHPFusion/Feedback/Comments.ajax.php" : ""
                                      )
            );
            $comments_form .= form_hidden("comment_id", '', '');
            $comments_form .= form_hidden("comment_cat", '', $edata['comment_cat']);

            if (iGUEST) {
                $comments_form .= form_text('comment_name', $locale['c104'], '', array('max_length' => 30, 'required' => TRUE));
            }

            $comments_form .= form_text('comment_subject', $locale['c113'], $edata['comment_subject'], ['required' => TRUE]);

            if ($options['comment_allow_ratings'] && $options['comment_allow_vote']) {
                $comments_form .= form_select('comment_rating', $locale['r106'], '',
                                              array(
                                                  'input_id' => 'rate_global',
                                                  'options' => [
                                                      5 => $locale['r120'],
                                                      4 => $locale['r121'],
                                                      3 => $locale['r122'],
                                                      2 => $locale['r123'],
                                                      1 => $locale['r124']
                                                  ]
                                              )
                );
            }

            $comments_form .= form_textarea('comment_message', '', $edata['comment_message'],
                                            array(
                                                'required' => 1,
                                                'autosize' => TRUE,
                                                'form_name' => 'inputform',
                                                "tinymce" => "simple",
                                                'wordcount' => TRUE,
                                                'type' => fusion_get_settings("tinymce_enabled") ? "tinymce" : "bbcode"
                                            )
            );

            if (iGUEST && (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT))) {
                $_CAPTCHA_HIDE_INPUT = FALSE;
                $comments_form .= "<div class='m-t-10 m-b-10'>";
                $comments_form .= "<label class='col-xs-12 col-sm-3'>".$locale['global_150']."</label><div class='col-xs-12 col-sm-9'>\n";
                ob_start();
                include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
                $comments_form .= ob_get_contents();
                ob_end_clean();
                if (!$_CAPTCHA_HIDE_INPUT) {
                    $comments_form .= "<br />\n<label for='captcha_code'>".$locale['global_151']."</label>";
                    $comments_form .= "<br />\n<input type='text' id='captcha_code' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' />\n";
                }
                $comments_form .= "</div>\n";
                $comments_form .= "</div>\n";
            }
            $comments_form .= form_button('post_comment', $edata['comment_message'] ? $locale['c103'] : $locale['c102'],
                                          $edata['comment_message'] ? $locale['c103'] : $locale['c102'],
                                          array('class' => 'btn-success m-t-10')
            );
            $comments_form .= closeform();
        } else {
            $comments_form = "<div class='well'>\n";
            $comments_form .= $locale['c105']."\n";
            $comments_form .= "</div>\n";
        }

        // Comments form
        $html = "<div class='comments-form-panel'>\n";
        $html .= "<div class='comments-form-header'>\n";
        $html .= $options['comment_form_title'].$locale['c111'];
        $html .= "</div>\n";
        $html .= "<div class='comments-form'>\n";
        $html .= "<div class='pull-left m-r-15'>\n";
        $html .= display_avatar(fusion_get_userdata(), "50px", "", FALSE, "img-rounded");
        $html .= "</div>\n";
        $html .= "<div class='overflow-hide'>\n";
        $html .= "<a id='edit_comment' name='edit_comment'></a>\n";
        $html .= $comments_form;
        $html .= "</div>\n";
        $html .= "</div>\n";
        $html .= "</div>\n";

        return $html;
    }
}

// Render breadcrumbs template
if (!function_exists("render_breadcrumbs")) {
    function render_breadcrumbs() {
        $breadcrumbs = BreadCrumbs::getInstance();
        $html = "<ol class='".$breadcrumbs->getCssClasses()."'>\n";
        foreach ($breadcrumbs->toArray() as $crumb) {
            $html .= "<li class='".$crumb['class']."'>";
            $html .= ($crumb['link']) ? "<a title='".$crumb['title']."' href='".$crumb['link']."'>".$crumb['title']."</a>" : $crumb['title'];
            $html .= "</li>\n";
        }
        $html .= "</ol>\n";
        return $html;
    }
}

if (!function_exists('render_favicons')) {
    function render_favicons($folder = IMAGES) {
        $html = "";
        /* Src: http://realfavicongenerator.net/favicon_result?file_id=p1avd9jap61od55nq1l2e1e2q7q76#.WAbP6I995D8 */
		if (file_exists($folder)) {
            $html .= "<link rel='apple-touch-icon' sizes='144x144' href='".$folder."favicons/apple-touch-icon.png'>\n";
            $html .= "<link rel='icon' type='image/png' href='".$folder."favicons/favicon-32x32.png' sizes='32x32'>\n";
            $html .= "<link rel='icon' type='image/png' href='".$folder."favicons/favicon-16x16.png' sizes='16x16'>\n";
            $html .= "<link rel='manifest' href='".$folder."favicons/manifest.json'>\n";
            $html .= "<link rel='mask-icon' href='".$folder."favicons/safari-pinned-tab.svg' color='#ccc'>\n";
            $html .= "<meta name='theme-color' content='#ffffff'>\n";

        }
        return $html;
    }
}

if (!function_exists('render_user_tags')) {
    /**
     * The callback function for parseUser()
     * @global array $locale
     * @param string $m The message
     * @return string
     */
    function render_user_tags($m) {
        $locale = fusion_get_locale();
        add_to_jquery("$('[data-toggle=\"user-tooltip\"]').popover();");
        $user = str_replace('@', '', $m[0]);
        $result = dbquery("SELECT user_id, user_name, user_level, user_status, user_avatar FROM ".DB_USERS." WHERE user_name='".$user."' or user_name='".ucwords($user)."' or user_name='".strtolower($user)."' AND user_status='0' LIMIT 1");
        if (dbrows($result) > 0) {
            $data = dbarray($result);
            $src = ($data['user_avatar'] && file_exists(IMAGES."avatars/".$data['user_avatar'])) ? $src = IMAGES."avatars/".$data['user_avatar'] : IMAGES."avatars/no-avatar.jpg";
            $title = '<div class="user-tooltip"><div class="pull-left m-r-10"><img class="img-responsive" style="max-height:40px; max-width:40px;" src="'.$src.'"></div><div class="clearfix"><a title="'.sprintf($locale['go_profile'], $data['user_name']).'" class="strong profile-link m-b-5" href="'.BASEDIR.'profile.php?lookup='.$data['user_id'].'">'.$data['user_name'].'</a><br/><small>'.getuserlevel($data['user_level']).'</small></div>';
            $content = '<a class="btn btn-sm btn-block btn-default strong" href="'.BASEDIR.'messages.php?msg_send='.$data['user_id'].'"><i class="fa fa-envelope fa-fw"></i> '.$locale['send_message'].'</a>';
            $html = "<a class='strong pointer' tabindex='0' role='button' data-html='true' data-trigger='focus' data-placement='top' data-toggle='user-tooltip' title='".$title."' data-content='".$content."'>";
            $html .= "<span class='user-label'>".$m[0]."</span>";
            $html .= "</a>\n";
            return $html;
        }
        return $m[0];
    }
}