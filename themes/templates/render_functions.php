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
    function render_comments($c_data, $c_info) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."comments.php");
        $comments_html = "";
        if (!empty($c_data)) {
            $c_makepagenav = ($c_info['c_makepagenav'] !== FALSE) ? "<div class=\"text-center m-b-5\">".$c_info['c_makepagenav']."</div>\n" : "";
            $comments_html .= "<ul class='comments clearfix'>\n";
            if (!function_exists("display_all_comments")) {
                function display_all_comments($c_data, $index = 0, &$comments_html = FALSE) {
                    $locale = fusion_get_locale('', LOCALE.LOCALESET."comments.php");
                    foreach ($c_data[$index] as $comments_id => $data) {
                        $comments_html .= "<!---comment-".$data['comment_id']."---><li class='m-b-15'>\n";
                        $comments_html .= "<div class='pull-left m-r-10'>";
                        $comments_html .= $data['user_avatar'];
                        $comments_html .= "<a href='".$data['reply_link']."' class='btn btn-sm btn-default comments-reply' data-id='$comments_id'>".$locale['c112']."</a>";
                        $comments_html .= "</div>\n";
                        $comments_html .= "<div class='overflow-hide'>\n";
                        $comments_html .= "<div class='arrow_box'>\n";
                        if ($data['edit_dell'] !== FALSE) {
                            $comments_html .= "<div class='pull-right text-smaller comment-actions'>".$data['edit_dell']."</div>\n";
                        }
                        $comments_html .= "<h4 class='comment_name display-inline-block m-r-10'>\n";
                        $comments_html .= "<a href='".FUSION_REQUEST."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a> ";
                        $comments_html .= $data['comment_name'];
                        $comments_html .= "</h4>\n";
                        $comments_html .= "<span class='comment_date m-l-10'>".$data['comment_datestamp']."</span>\n";
                        $comments_html .= "<div class='comment_message'>".$data['comment_message']."</div>\n";
                        $comments_html .= "</div>\n";
                        if (!empty($data['reply_form'])) {
                            $comments_html .= $data['reply_form'];
                        }
                        // Replies is here
                        if (isset($c_data[$data['comment_id']])) {
                            $comments_html .= "<ul class='sub-comments'>\n";
                            $comments_html .= display_all_comments($c_data, $data['comment_id']);
                            $comments_html .= "</ul>\n";
                        }
                        $comments_html .= "</div>\n";
                        $comments_html .= "</li><!---//comment-".$data['comment_id']."--->";
                    }
                    return $comments_html;
                }
            }
            $comments_html .= display_all_comments($c_data);
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
		echo "<div class='comments-panel'>\n";
		echo "<div class='comments-header'>\n";
		echo $c_info['comments_count'];
		echo "</div>\n";
		echo "<div class='comments overflow-hide'>\n";
		echo $comments_html;
		echo "</div>\n";
		echo "</div>\n";
    }
}

if (!function_exists("render_comments_form")) {
    function render_comments_form($comment_type, $clink, $comment_item_id, $_CAPTCHA_HIDE_INPUT) {
        $userdata = fusion_get_userdata();
        $settings = fusion_get_settings();
        $locale = fusion_get_locale();
        $comment_cat = "";
        $comment_message = "";
        if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit") && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
            $eresult = dbquery("SELECT tcm.*, tcu.user_name
				FROM ".DB_COMMENTS." tcm
				LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
				WHERE comment_id='".$_GET['comment_id']."' AND comment_item_id='".$comment_item_id."'
				AND comment_type='".$comment_type."' AND comment_hidden='0'");
            if (dbrows($eresult) > 0) {
                $edata = dbarray($eresult);
                if ((iADMIN && checkrights("C")) || (iMEMBER && $edata['comment_name'] == $userdata['user_id'] && isset($edata['user_name']))) {
                    $clink .= "&amp;c_action=edit&amp;comment_id=".$edata['comment_id'];
                    $comment_message = $edata['comment_message'];
                    $comment_cat = $edata['comment_cat'];
                }
            }
        }
        // Comments form
        if (iMEMBER || fusion_get_settings("guestposts") == 1) {
            $comments_form = openform('inputform', 'post', $clink,
                                      array(
                                          'remote_url' => fusion_get_settings('comments_jquery_enabled') ? fusion_get_settings("site_path")."includes/classes/PHPFusion/Feedback/Comments.ajax.php" : ""
                                      )
            );
            $comments_form .= form_hidden("comment_cat", "", $comment_cat);
            if (iGUEST) {
                $comments_form .= form_text('comment_name', $locale['c104'], '', array('max_length' => 30, 'required' => TRUE));
            }
            $comments_form .= form_textarea('comment_message', '', $comment_message,
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
            $comments_form .= form_button('post_comment', $comment_message ? $locale['c103'] : $locale['c102'],
                                          $comment_message ? $locale['c103'] : $locale['c102'],
                                          array('class' => 'btn-success m-t-10')
            );
            $comments_form .= closeform();
        } else {
            $comments_form = "<div class='well'>\n";
            $comments_form .= $locale['c105']."\n";
            $comments_form .= "</div>\n";
        }
		// Comments form 
		echo "<div class='comments-form-panel'>\n";
		echo "<div class='comments-form-header'>\n";
		echo $locale['c111'];
		echo "</div>\n";
		echo "<div class='comments-form'>\n";
		echo "<div class='pull-left'>\n";
		echo display_avatar(fusion_get_userdata(), "50px", "", FALSE, "img-rounded");
		echo "</div>\n";
		echo "<div class='overflow-hide'>\n";
		echo "<a id='edit_comment' name='edit_comment'></a>\n";
		echo $comments_form;
		echo "</div>\n";
		echo "</div>\n";
		echo "</div>\n";
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
        /* Src: http://realfavicongenerator.net/favicon?file_id=p19b99h3uhe83vcfbraftb1lfe5#.VLDLxaZuTig */
		if (file_exists($folder)) {
			return "<link rel='apple-touch-icon' sizes='57x57' href='".$folder."favicons/apple-touch-icon-57x57.png'/>";
			return "<link rel='apple-touch-icon' sizes='114x114' href='".$folder."favicons/apple-touch-icon-114x114.png'/>";
			return "<link rel='apple-touch-icon' sizes='72x72' href='".$folder."favicons/apple-touch-icon-72x72.png'/>";
			return "<link rel='apple-touch-icon' sizes='144x144' href='".$folder."favicons/apple-touch-icon-144x144.png'/>";
			return "<link rel='apple-touch-icon' sizes='60x60' href='".$folder."favicons/apple-touch-icon-60x60.png'/>";
			return "<link rel='apple-touch-icon' sizes='120x120' href='".$folder."favicons/apple-touch-icon-120x120.png'/>";
			return "<link rel='apple-touch-icon' sizes='76x76' href='".$folder."favicons/apple-touch-icon-76x76.png'/>";
			return "<link rel='shortcut icon' href='".$folder."favicons/favicon.ico'/>";
			return "<link rel='icon' type='image/png' href='".$folder."favicons/favicon-96x96.png' sizes='96x96'/>";
			return "<link rel='icon' type='image/png' href='".$folder."favicons/favicon-16x16.png' sizes='16x16'/>";
			return "<link rel='icon' type='image/png' href='".$folder."favicons/favicon-32x32.png' sizes='32x32'/>";
			return "<meta name='msapplication-TileColor' content='#2d7793'/>";
			return "<meta name='msapplication-TileImage' content='".$folder."favicons/mstile-144x144.png'/>";
        }
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
            $html .= "</a>";
            return $html;
        }
        return $m[0];
    }
}