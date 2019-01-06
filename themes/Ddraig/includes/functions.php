<?php
if (!defined("IN_FUSION")) { die("Access Denied"); }

set_image("pollbar", THEME."images/blank.gif");
set_image("edit", THEME."images/edit.png");
set_image("printer", THEME."images/printer.png");
set_image("link", THEME."images/link.png");
//Arrows
set_image("up", THEME."images/up.png");
set_image("down", THEME."images/down.png");
set_image("left", THEME."images/left.png");
set_image("right", THEME."images/right.png");
//Forum folders icons
set_image("folder", THEME."forum/folder.png");
set_image("foldernew", THEME."forum/foldernew.png");
set_image("folderlock", THEME."forum/folderlock.png");
set_image("stickythread", THEME."forum/stickythread.png");
//Forum buttons
set_image("reply", "reply");
set_image("newthread", "newthread");
set_image("web", "web");
set_image("pm", "pm");
set_image("quote", "quote");
set_image("forum_edit", "forum_edit");

// lines by Johan Wilson
function theme_output($output) {
	global $locale;
	$search = array(
		"@><img src='reply' alt='(.*?)' style='border:0px' />@si",								//Reply button
		"@><img src='newthread' alt='(.*?)' style='border:0px;?' />@si",						//New thread button
		"@><img src='web' alt='(.*?)' style='border:0;vertical-align:middle' />@si",			//Website button
		"@><img src='pm' alt='(.*?)' style='border:0;vertical-align:middle' />@si",				//PM button
		"@><img src='quote' alt='(.*?)' style='border:0px;vertical-align:middle' />@si",		//Quote button
		"@><img src='forum_edit' alt='(.*?)' style='border:0px;vertical-align:middle' />@si",	//Edit button
		"@<a href='".ADMIN."comments.php(.*?)&amp;ctype=(.*?)&amp;cid=(.*?)'>(.*?)</a>@si", 	//Manage comments button
		"@forum_thread_user_info' style='width:140px'>\n<img src='(.*?)' alt='(.*?)' />@si",	//User avatar in forum
		"@<td colspan='2' class='tbl1 forum_thread_post_space' style='height:10px'></td>@si",	//Space between forums posts
		"@<div class='quote'><a (.*?)>(.*?)</a>(<br />)?@si",									//Quote
		"@<img src='".THEME."forum/stickythread.png'(.*?)/>@si",								//Sticky thread tag
		"@src='".THEME."forum/folderlock.png'(.*?)<td width='100%' class='(.*?)'>(.*?)<a@si",	//Locked thread tag
		"@<span class='small' style='font-weight:bold'>\[".$locale['global_051']."\]</span>@si",//Poll thread text 
		"@<hr />\n<span class='small'>(.*?)</span>@si"											//Edit note in forums
	);
	$replace = array(
		' class="button big"><img alt="$1" class="reply-button icon" src="'.THEME.'images/blank.gif" />$1',
		' class="button big"><img alt="$1" class="newthread-button icon" src="'.THEME.'images/blank.gif" />$1',
		' class="button" rel="nofollow" title="$1"><img alt="$1" class="web-button icon" src="'.THEME.'images/blank.gif" />Web',
		' class="button" title="$1"><img alt="$1" class="pm-button icon" src="'.THEME.'images/blank.gif" />PM',
		' class="button" title="$1"><img alt="$1" class="quote-button icon" src="'.THEME.'images/blank.gif" />$1',
		' class="negative button" title="$1"><img alt="" class="edit-button icon" src="'.THEME.'images/blank.gif" />$1',
		'<a href="'.ADMIN.'comments.php$1&amp;ctype=$2&amp;cid=$3" class="big button"><img alt="$4" class="settings-button icon" src="'.THEME.'images/blank.gif" />$4</a>',
		'forum_thread_user_info\' style="width:140px"><div class="user-avatar"><img class="avatar" src="$1" alt="$2" /></div>',
		'<td colspan="2" class="tbl1 forum_thread_post_space"></td>',
		'<div class="quote"><p class="citation"><img src="'.THEME.'images/quote_icon.png" alt=">" /><a $1>$2</a></p>',
		'<span class="tag green">'.$locale['sticky'].'</span>',
		'src="'.THEME.'forum/folderlock.png"$1<td width="100%" class="$2">$3<span class="tag red">'.$locale['locked'].'</span> <a',
		'<span class="tag blue small">'.$locale['global_051'].'</span>',
		'<br /><p class="post-edited small">$1</p>'
	);
	$output = preg_replace($search, $replace, $output);
	
	//Forums users last post avatar
	function replace($m) {
		global $locale;
		$r = "<td width='1%' style='white-space:nowrap' class='tbl".$m[1]."'>".$locale['deleted_user']."</td>";
		$src = "";
		$result = dbquery("SELECT user_avatar FROM ".DB_USERS." WHERE user_id='".$m[4]."' LIMIT 1");
		while ($data = dbarray($result)) {
			if ($data['user_avatar'] && file_exists(IMAGES."avatars/".$data['user_avatar'])) {
				$src = IMAGES."avatars/".$data['user_avatar'];
			} else {
				$src = IMAGES."avatars/noavatar50.png";
			}
		$r = "<td width='1%' class='tbl".$m[1]." last-post' style='white-space:nowrap'><a href='".BASEDIR."profile.php?lookup=".$m[4]."' class='profile-link flleft'><span class='user-avatar'><img class='avatar' width='40' src='".$src."' alt='".$src."' /></span></a><span class='last-post-author small'>".$m[3]."<a href='".BASEDIR."profile.php?lookup=".$m[4]."' class='profile-link'>".$m[5]."</a></span><br /><span class='last-post-date'>".$m[2]."</span></td>";
		}
	return $r;
	}
	$search = "#<td width='1%' class='tbl(1|2)' style='white-space:nowrap'>(.*?)<br />\n<span class='small'>(.*?)<a href='".BASEDIR."profile.php\?lookup=(.*?)' class='profile-link'>(.*?)</a></span></td>#i";
	$output = preg_replace_callback($search, 'replace', $output);

	return $output;
}
?>