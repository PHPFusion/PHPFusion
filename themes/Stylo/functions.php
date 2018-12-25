<?php
if (!defined("IN_FUSION")) { die("Access Denied"); }

set_image("stickythread", THEME."forum/stickythread.png");
set_image("printer", THEME."images/icons/printer.png");
set_image("up", THEME."images/up.png");
set_image("down", THEME."images/down.png");
set_image("left", THEME."images/left.png");
set_image("right", THEME."images/right.png");

set_image("reply", "reply");
set_image("newthread", "newthread");
set_image("web", "web");
set_image("pm", "pm");
set_image("quote", "quote");
set_image("forum_edit", "forum_edit");

function theme_output($output) {

	$search = array(
		"@><img src='reply' alt='(.*?)' style='border:0px' />@si",
		"@><img src='newthread' alt='(.*?)' style='border:0px;?' />@si",
		"@><img src='web' alt='(.*?)' style='border:0;vertical-align:middle' />@si",
		"@><img src='pm' alt='(.*?)' style='border:0;vertical-align:middle' />@si",
		"@><img src='quote' alt='(.*?)' style='border:0px;vertical-align:middle' />@si",
		"@><img src='forum_edit' alt='(.*?)' style='border:0px;vertical-align:middle' />@si",
		"@<a href='".ADMIN."comments.php(.*?)&amp;ctype=(.*?)&amp;cid=(.*?)'>(.*?)</a>@si"
	);
	$replace = array(
		' class="big button"><span class="reply-button icon"></span>$1',
		' class="big button"><span class="newthread-button icon"></span>$1',
		' class="button" rel="nofollow" title="$1"><span class="web-button icon"></span>Web',
		' class="button" title="$1"><span class="pm-button icon"></span>PM',
		' class="button" title="$1"><span class="quote-button icon"></span>$1',
		' class="negative button" title="$1"><span class="edit-button icon"></span>$1',
		'<a href="'.ADMIN.'comments.php$1&amp;ctype=$2&amp;cid=$3" class="big button"><span class="settings-button icon"></span>$4</a>'
	);
	$output = preg_replace($search, $replace, $output);

	return $output;
}
?>