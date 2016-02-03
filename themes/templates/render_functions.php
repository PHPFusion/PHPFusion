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
}

if (!function_exists("render_comments_form")) {
	function render_comments_form($comment_type, $clink, $comment_item_id, $_CAPTCHA_HIDE_INPUT) {
		global $locale, $settings, $userdata;

		opentable($locale['c102']);
		$comment_message = "";
		if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit") && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
			$eresult = dbquery("SELECT tcm.comment_id, tcm.comment_name, tcm.comment_message, tcu.user_name
				FROM ".DB_COMMENTS." tcm
				LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
				WHERE comment_id='".$_GET['comment_id']."' AND comment_item_id='".$comment_item_id."'
				AND comment_type='".$comment_type."' AND comment_hidden='0'");
			if (dbrows($eresult)>0) {
				$edata = dbarray($eresult);
				if ((iADMIN && checkrights("C")) || (iMEMBER && $edata['comment_name'] == $userdata['user_id'] && isset($edata['user_name']))) {
					$clink .= "&amp;c_action=edit&amp;comment_id=".$edata['comment_id'];
					$comment_message = $edata['comment_message'];
				}
			} else {
				$comment_message = "";
			}
		}

		if (iMEMBER || $settings['guestposts'] == "1") {
			require_once INCLUDES."bbcode_include.php";
			echo "<a id='edit_comment' name='edit_comment'></a>\n";
			echo openform('inputform', 'post', $clink, array('class' => 'm-b-20', 'max_tokens' => 1));
			if (iGUEST) {
				echo form_text('comment_name', $locale['c104'], '', array('max_length'=>30));
			}
			echo form_textarea('comment_message', '', $comment_message, array('required' => 1, 'autosize'=>1, 'form_name'=>'inputform', 'bbcode'=>1));

			if (iGUEST && (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT))) {
				$_CAPTCHA_HIDE_INPUT = FALSE;
				echo "<div style='width:360px; margin:10px auto;'>";
				echo $locale['global_150']."<br />\n";
				include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
				if (!$_CAPTCHA_HIDE_INPUT) {
					echo "<br />\n<label for='captcha_code'>".$locale['global_151']."</label>";
					echo "<br />\n<input type='text' id='captcha_code' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' />\n";
				}
				echo "</div>\n";
			}
			echo form_button('post_comment', $comment_message ? $locale['c103'] : $locale['c102'], $comment_message ? $locale['c103'] : $locale['c102'], array('class' => 'btn-success m-t-10'));
			echo closeform();
		} else {
			echo "<div class='well'>\n";
			echo $locale['c105']."\n";
			echo "</div>\n";
		}
		closetable();
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


