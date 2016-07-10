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

if (!defined("IN_FUSION")) { die("Access Denied"); }
// Render comments template
if (!function_exists("render_comments")) {

    function render_comments($c_data, $c_info, $index = 0) {

        $locale = fusion_get_locale();

        $comments_html = "";

        if ($index == 0) {
            $c_data[$index] = (!empty($c_data[0][$index]) ? $c_data[0][$index] : array());
        }

        if (!empty($c_data[$index])) {


            $c_makepagenav = ($c_info['c_makepagenav'] !== FALSE) ? "<div class=\"text-center m-b-5\">".$c_info['c_makepagenav']."</div>\n" : "";

            $comments_html .= "<ul class='comments clearfix'>\n";

            if (!function_exists("display_all_comments")) {
                function display_all_comments($c_data, $index = 0, &$comments_html = false) {

                    foreach ($c_data[$index] as $comments_id => $data) {

                        $comments_html .= "<!---comment-".$data['comment_id']."---><li class='m-b-15'>\n";
                        $comments_html .= "<div class='pull-left m-r-10'>";
                        $comments_html .= $data['user_avatar'];
                        $comments_html .= "<a href='".$data['reply_link']."' class='btn btn-sm btn-default comments-reply'>Reply</a>";
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
                        $comments_html .= "<span class='text-smaller mid-opacity m-l-10'>".$data['comment_datestamp']."</span>\n";
                        $comments_html .= "<div class='comment_message'>".$data['comment_message']."</div>\n";
                        $comments_html .= "</div>\n";

                        if (!empty($data['reply_form'])) {
                            $comments_html .= $data['reply_form'];
                        }

                        // replies is here
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
        ?>

        <!---comments form--->
        <div class="comments-form-panel">
            <!---comments header-->
            <div class="comments-form-header">
                <i class="pngicon png-comments m-r-10"></i>
                <?php echo $c_info['comments_count'] ?>
            </div>
            <!---//comments header-->
            <div class="comments-form overflow-hide">
                <?php echo $comments_html ?>
            </div>
        </div>
        <!---//comments form--->
        <?php
    }





    /*
    function render_comments($c_data, $c_info) {
		global $locale;
		opentable(format_word(number_format(count($c_data)), $locale['fmt_comment']));
		if (!empty($c_data)) {
			echo "<div class='comments floatfix'>\n";
			$c_makepagenav = '';
			if ($c_info['c_makepagenav'] !== FALSE) {
				echo $c_makepagenav = "<div style='text-align:center;margin-bottom:5px;'>".$c_info['c_makepagenav']."</div>\n";
			}
			foreach ($c_data as $data) {
				echo "<div class='comments_container m-b-15'><div class='pull-left m-r-10'>";
				echo $data['user_avatar'];
				echo "</div>\n";
				echo "<div class='overflow-hide'>\n";
				if ($data['edit_dell'] !== FALSE) {
					echo "
					<div class='pull-right text-smaller comment_actions'>
					".$data['edit_dell']."
					</div>\n";
				}
				echo "<div class='comment_name'>\n";
				echo "<a href='".FUSION_REQUEST."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a> ";
				echo $data['comment_name'];
				echo "<span class='text-smaller mid-opacity m-l-10'>".$data['comment_datestamp']."</span>\n";
				echo "</div>\n";
				echo "<div class='comment_message'>".$data['comment_message']."</div>\n";
				echo "</div>\n</div>\n";

			}
			echo $c_makepagenav;
			if ($c_info['admin_link'] !== FALSE) {
				echo "<div style='float:right' class='comment_admin'>".$c_info['admin_link']."</div>\n";
			}
			echo "</div>\n";
		} else {
			echo "<div class='no_comment'>\n";
			echo $locale['c101']."\n";
			echo "</div>\n";
		}
		closetable();
	}
    */
}

if (!function_exists("render_comments_form")) {


    function render_comments_form($comment_type, $clink, $comment_item_id, $_CAPTCHA_HIDE_INPUT) {
        global $locale, $settings, $userdata;

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
            require_once INCLUDES."bbcode_include.php";
            $comments_form = openform('inputform', 'post', $clink);
            $comments_form .= form_hidden("comment_cat", "", $comment_cat);
            if (iGUEST) {
                $comments_form .= form_text('comment_name', $locale['c104'], '', array('max_length' => 30));
            }
            $comments_form .= form_textarea('comment_message', '', $comment_message,
                                            array('required' => 1,
                                                  'autosize' => 1,
                                                  'form_name' => 'inputform',
                                                  "tinymce" => "simple",
                                                  'type' => fusion_get_settings("tinymce_enabled") ? "tinymce" : "bbcode"
                                            )
            );

            if (iGUEST && (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT))) {
                $_CAPTCHA_HIDE_INPUT = FALSE;
                $comments_form .= "<div style='width:360px; margin:10px auto;'>";
                $comments_form .= $locale['global_150']."<br />\n";
                include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
                if (!$_CAPTCHA_HIDE_INPUT) {
                    $comments_form .= "<br />\n<label for='captcha_code'>".$locale['global_151']."</label>";
                    $comments_form .= "<br />\n<input type='text' id='captcha_code' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' />\n";
                }
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

        ?>
        <!---comments form--->
        <div class="comments-form-panel">
            <!---comments header-->
            <div class="comments-form-header">
                <?php echo $locale['c102'] ?>
            </div>
            <!---//comments header-->
            <div class="comments-form">
                <div class="pull-left">
                    <?php echo display_avatar(fusion_get_userdata(), "50px", "", FALSE, "img-rounded")?>
                </div>
                <div class="overflow-hide">
                    <a id="edit_comment" name="edit_comment"></a>
                    <?php echo $comments_form ?>
                </div>
            </div>
        </div>
        <!---//comments form--->
        <?php
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
			return "
			<link rel='apple-touch-icon' sizes='57x57' href='".$folder."favicons/apple-touch-icon-57x57.png'/>
			<link rel='apple-touch-icon' sizes='114x114' href='".$folder."favicons/apple-touch-icon-114x114.png'/>
			<link rel='apple-touch-icon' sizes='72x72' href='".$folder."favicons/apple-touch-icon-72x72.png'/>
			<link rel='apple-touch-icon' sizes='144x144' href='".$folder."favicons/apple-touch-icon-144x144.png'/>
			<link rel='apple-touch-icon' sizes='60x60' href='".$folder."favicons/apple-touch-icon-60x60.png'/>
			<link rel='apple-touch-icon' sizes='120x120' href='".$folder."favicons/apple-touch-icon-120x120.png'/>
			<link rel='apple-touch-icon' sizes='76x76' href='".$folder."favicons/apple-touch-icon-76x76.png'/>
			<link rel='shortcut icon' href='".$folder."favicons/favicon.ico'/>
			<link rel='icon' type='image/png' href='".$folder."favicons/favicon-96x96.png' sizes='96x96'/>
			<link rel='icon' type='image/png' href='".$folder."favicons/favicon-16x16.png' sizes='16x16'/>
			<link rel='icon' type='image/png' href='".$folder."favicons/favicon-32x32.png' sizes='32x32'/>
			<meta name='msapplication-TileColor' content='#2d7793'/>
			<meta name='msapplication-TileImage' content='".$folder."favicons/mstile-144x144.png'/>
			<meta name='msapplication-config' content='".$folder."favicons/browserconfig.xml'/>
			";
		}
	}
}


