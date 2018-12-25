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
require_once CLASSES."PHPFusion/BreadCrumbs.inc";

use PHPFusion\BreadCrumbs;

// Render comments template
if (!function_exists("render_comments")) {
	function render_comments($c_data, $c_info) {
		global $locale;
		opentable($locale['c100']);
		if (!empty($c_data)) {
			echo "<div class='comments floatfix'>\n";
			$c_makepagenav = '';
			if ($c_info['c_makepagenav'] !== FALSE) {
				echo $c_makepagenav = "<div style='text-align:center;margin-bottom:5px;'>".$c_info['c_makepagenav']."</div>\n";
			}
			foreach ($c_data as $data) {
				echo "<div class='tbl2'>\n";
				if ($data['edit_dell'] !== FALSE) {
					echo "<div style='float:right' class='comment_actions'>".$data['edit_dell']."\n</div>\n";
				}
				echo "<a href='".PERMALINK_CURRENT_PATH."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a> |\n";
				echo "<span class='comment-name'>".$data['comment_name']."</span>\n";
				echo "<span class='small'>".$data['comment_datestamp']."</span>\n";
				echo "</div>\n<div class='tbl1 comment_message'>".$data['comment_message']."</div>\n";
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
    function render_breadcrumbs($key = 'default') {
        $breadcrumbs = BreadCrumbs::getInstance($key);
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
    function render_favicons($folder = '') {
        $folder = ($folder == '' ? IMAGES.'favicons/' : $folder);
        $html = '';
        // Generator - https://realfavicongenerator.net/
        if (is_dir($folder)) {
            $html .= '<link rel="apple-touch-icon" sizes="180x180" href="'.$folder.'apple-touch-icon.png">';
            $html .= '<link rel="icon" type="image/png" sizes="32x32" href="'.$folder.'favicon-32x32.png">';
            $html .= '<link rel="icon" type="image/png" sizes="16x16" href="'.$folder.'favicon-16x16.png">';
            $html .= '<link rel="manifest" href="'.$folder.'manifest.json">';
            $html .= '<link rel="mask-icon" href="'.$folder.'safari-pinned-tab.svg" color="#262626">';
            $html .= '<link rel="shortcut icon" href="'.$folder.'favicon.ico">';
            $html .= '<meta name="msapplication-TileColor" content="#262626">';
            $html .= '<meta name="msapplication-config" content="'.$folder.'browserconfig.xml">';
            $html .= '<meta name="theme-color" content="#ffffff">';
        }
        return $html;
    }
}

if (!function_exists('render_blog')) {
	function render_blog($subject, $blog, $info) {
		echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
		echo "<td class='capmain-left'></td>\n";
		echo "<td class='capmain'>".$subject."</td>\n";
		echo "<td class='capmain-right'></td>\n";
		echo "</tr>\n</table>\n";
		echo "<table width='100%' cellpadding='0' cellspacing='0' class='spacer'>\n<tr>\n";
		echo "<td class='main-body middle-border'>".$info['cat_image'].$blog."</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td align='center' class='blog-footer middle-border'>\n";
		echo blogposter($info," &middot;").blogcat($info," &middot;").blogopts($info,"&middot;").itemoptions("B",$info['blog_id']);
		echo "</td>\n";
		echo "</tr><tr>\n";
		echo "<td style='height:5px;background-color:#f6a504;'></td>\n";
		echo "</tr>\n</table>\n";

	}
} 
