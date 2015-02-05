<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: navigation_panel.php
| Author: Nick Jones (Digitanium)
| Co-author: Chubatyj Vitalij (Rizado)
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
openside($locale['global_001']);

function showsidelinks(array $options = array(), $id = 0) {
	global $userdata;
	static $data = array();
	$settings = fusion_get_settings();
	$acclevel = isset($userdata['user_level']) ? $userdata['user_level'] : 0;
	$res = &$res;
	if (empty($data)) {
		$data = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat", "WHERE link_position <= 2".(multilang_table("SL") ? " AND link_language='".LANGUAGE."'" : "")." AND link_visibility <= '$acclevel' ORDER BY link_cat, link_order");
	}

	$res .= "<ul>\n";

	foreach($data[$id] as $link_id => $link_data) {
		$li_class = "";
		if ($link_data['link_name'] != "---" && $link_data['link_name'] != "===") {
			$link_target = ($link_data['link_window'] == "1" ? " target='_blank'" : "");
			if (START_PAGE == $link_data['link_url']) {
				$li_class .= ($li_class ? " " : "")."current-link";
			}
			if (preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url'])) {
				$itemlink = $link_data['link_url'];
			} else {
				$itemlink = BASEDIR.$link_data['link_url'];
			}
			$res .= "<li".($li_class ? " class='".$li_class."'" : "")."><a href='".$itemlink."'".$link_target.">".$link_data['link_name']."</a>";
			if (isset($data[$link_id])) {
				$res .= showsidelinks($options, $link_data['link_id']);
			}
			$res .= "</li>\n";
		} elseif ($link_data['link_cat'] > 0) {
			echo "<li class='divider'></li>";
		}
	}

	$res .= "</ul>\n";

	return $res;
}

echo "<table width='100%' cellpadding='0' cellspacing='0'>\n<tr>\n<td>\n";
echo "<div class='sidecssmenu'>\n";

echo showsidelinks();

echo "</div>\n";
echo "</td>\n</tr>\n</table>\n";

echo "<script type='text/javascript'>
	$(document).ready(function(){
    $('.sidecssmenu ul li').hover(
        function() {
            $(this).find('ul:first').slideDown();
        },
        function() {            
            $(this).find('ul:first').slideUp('fast'); 
        }
    );
    $('.sidecssmenu li:has(ul)').find('a:first').append(' »');
	});
	</script>\n";

closeside();
?>