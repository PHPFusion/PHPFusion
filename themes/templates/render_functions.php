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
if (!defined("IN_FUSION")) { die("Access Denied"); }
// Render comments template
if (!function_exists("render_comments")) {
	function render_comments($c_data, $c_info) {
		global $locale;
		opentable($locale['c100'].' : ('.format_word(number_format(count($c_data)), $locale['fmt_comment']).')');
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
					- <a href='".FUSION_REQUEST."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a>
					</div>\n";
				}
				echo "<div class='comment_name'>\n";
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
			echo $locale['c101']."\n";
		}
		closetable();
	}
}

// Render breadcrumbs template
if (!function_exists("render_breadcrumbs")) {
	function render_breadcrumbs() {
		global $locale, $breadcrumbs;

		// Testing
		/*$breadcrumbs->class = 'breadcrumb custom-class';
		$breadcrumbs->show_home = FALSE;
		$breadcrumbs->last_no_link = FALSE;*/

		$crumbs = get_breadcrumbs();

		$html = "<ol class='".$breadcrumbs->class."'>\n";
		foreach ($crumbs as $crumb) {
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


?>