<?php
if (!defined("IN_FUSION")) { die("Access Denied"); }

add_to_head("<meta name='viewport' content='width=device-width, initial-scale=1.0' />");
add_to_head("<link href='".THEME."bootstrap/css/bootstrap.min.css' rel='stylesheet' media='screen' />");
add_to_head("<link href='".THEME."bootstrap/css/bootstrap-responsive.min.css' rel='stylesheet' media='screen' />");
add_to_footer("<script type='text/javascript' src='".THEME."bootstrap/js/bootstrap.min.js'></script>");

define("SHAREING", true);
define("HSDESCRIPTION", true);

set_image("reply", "reply");
set_image("newthread", "newthread");
set_image("web", "web");
set_image("pm", "pm");
set_image("quote", "quote");
set_image("forum_edit", "forum_edit");


function theme_output($output) {
	global $main_style;
	$search = array(
		"@><img src='reply' alt='(.*?)' style='border:0px' />@si",
		"@><img src='newthread' alt='(.*?)' style='border:0px;?' />@si",
		"@><img src='web' alt='(.*?)' style='border:0;vertical-align:middle' />@si",
		"@><img src='pm' alt='(.*?)' style='border:0;vertical-align:middle' />@si",
		"@><img src='quote' alt='(.*?)' style='border:0px;vertical-align:middle' />@si",
		"@><img src='forum_edit' alt='(.*?)' style='border:0px;vertical-align:middle' />@si"
		);
	
	$replace = array(
		' class="button"><span>$1</span>',
		' class="button"><span>$1</span>',
		' class="button blue" rel="nofollow"><span>Web</span>',
		' class="button blue"><span>PM</span>',
		' class="button blue"><span>$1</span>',
		' class="button blue"><span>$1</span>'
		);
	$output = preg_replace($search, $replace, $output);
	return $output;
}


function navigation() {
	$result = dbquery(
		"SELECT link_name, link_url, link_window, link_visibility FROM ".DB_SITE_LINKS."
		 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_position='3' OR link_position='2' ".(multilang_table("SL") ? "AND link_language='".LANGUAGE."'" : "")." ORDER BY link_order");
		 
	$link = array();
	
	while ($data = dbarray($result)) {
		$link[] = $data;
	}

foreach($link as $data) {
		if (checkgroup($data['link_visibility'])) {
			$link_target = $data['link_window'] == "1" ? " target='_blank'" : "";
				if (!strstr($data['link_url'], "http://") && !strstr($data['link_url'], "https://")) {
				$data['link_url'] = BASEDIR.$data['link_url'];
			}
	 echo "<li class='main'><a class='main' href='".$data['link_url']."' $link_target><strong>".parseubb($data['link_name'], "b|i|u|color")."</strong></a></li>";
		}
	}
}


?>