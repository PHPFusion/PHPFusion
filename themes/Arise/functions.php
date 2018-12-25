<?php
if (!defined("IN_FUSION")) { die("Access Denied"); }

/* Readme 
The base to the menu used here are courtesy of http://themify.me/themes/itheme2
Submenu features are inspired from Bifrost (Designed by Johan Wilson).
To add a dropdown link in header, add or edit existing link and add %submenu% before the Link Name and select sub-header only. 
All links after this will appear in this dropdown menu. To end just add %endmenu% in the last Link Name in the list.
You also need to change (define("SUBNAV", false);) to (define("SUBNAV", true);)
To turn off shareing bars in news and articles change define("SHAREING", true); to define("SHAREING", false);
*/

define("SUBNAV", false);
define("HEADERLINKS", true);
define("HSDESCRIPTION", true);

function navigation() {
	$result = dbquery(
	"SELECT link_name, link_url, link_window, link_visibility FROM ".DB_SITE_LINKS."
	".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")."
	 link_position='3' ".(SUBNAV ? "" : " OR link_position='2' ".(multilang_table("SL") ? "AND link_language='".LANGUAGE."'" : "")."")." ORDER BY link_order"
	);
	$link = array();
	while ($data = dbarray($result)) {
		$link[] = $data;
	}

$lifirstclass= preg_match('/news.php/i', $_SERVER['PHP_SELF']) ? " class='home current_page_item'" : " class='home'";

$res = "<ul class='clearfix' id='main-nav'><li$lifirstclass><a href='".BASEDIR."news.php'><span>Home</span></a></li>\n";

	foreach($link as $data) {
		if (checkgroup($data['link_visibility'])) {
			$link_target = $data['link_window'] == "1" ? " target='_blank'" : "";
		$li_class = preg_match("/^".preg_quote(START_PAGE, '/')."/i", $data['link_url']) ? " class='current_page_item'" : "";

			if (!strstr($data['link_url'], "http://") && !strstr($data['link_url'], "https://")) {
				$data['link_url'] = BASEDIR.$data['link_url'];
			}
			if (strstr($data['link_name'], "%submenu% ") && SUBNAV) {
				$res .= "<li$li_class><a href='".$data['link_url']."'$link_target><span>".parseubb(str_replace("%submenu% ", "",$data['link_name']), "b|i|u|color")."</span></a>\n<ul>\n";
			} elseif (strstr($data['link_name'], "%endmenu% ") && SUBNAV) {
				$res .= "<li$li_class><a href='".$data['link_url']."'$link_target><span>".parseubb(str_replace("%endmenu% ", "",$data['link_name']), "b|i|u|color")."</span></a></li>\n</ul>\n</li>\n";
			} else {
				$res .= "<li$li_class><a href='".$data['link_url']."'$link_target><span>".parseubb($data['link_name'], "b|i|u|color")."</span></a></li>\n";
			}
		}
	}
	$res .= "</ul>\n";
	return $res;
}


?>