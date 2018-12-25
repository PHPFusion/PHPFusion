<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: functions.php
| Author: Hien (Frederick MC Chan)
| Author: Falk (Jocke Falk)
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

require_once THEME."components.php";
$license = showcopyright();

// set_image("pollbar", THEME."images/blank.gif");
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
set_image("reply", THEME."forum/reply.gif");
set_image("newthread", THEME."forum/newthread.gif");
set_image("web", THEME."forum/web.gif");
set_image("pm", THEME."forum/pm.gif");
set_image("quote", THEME."forum/quote.gif");
set_image("forum_edit", THEME."forum/edit.gif");


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

// Atom X Navigation
function horizontalnav() {
	global $settings, $userdata, $locale, $aidlink, $menu_item;

	$action_url = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");

	if (isset($_GET['redirect']) && strstr($_GET['redirect'], "/")) {
		$action_url = cleanurl(urldecode($_GET['redirect']));
	}

	$html = "";
	$html .= "<div class='navbar-atom m-t-15'>";
	$html .= "<div id='navbar-atom' class='navbar-collapse collapse'>\n";

	$result = dbquery(
		"SELECT link_name, link_url, link_window, link_visibility FROM ".DB_SITE_LINKS."
		".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")."
		 link_position='3' ".(SUBNAV ? "" : " OR link_position='2' ".(multilang_table("SL") ? "AND link_language='".LANGUAGE."'" : "")."")." ORDER BY link_order"
	 );

    $result = dbquery("SELECT * FROM ".DB_SITE_LINKS." WHERE ".groupaccess('link_visibility')." ".(multilang_table("SL") ? "AND link_language='".LANGUAGE."' AND" : "AND")." link_position='3' OR link_position='2' ".(multilang_table("SL") ? "AND link_language='".LANGUAGE."'" : "")."  ORDER BY link_order ASC");
	 
	$html .= "<ul class='nav navbar-nav text-left' >\n";
	if (dbrows($result)>0) {
		$i = 0;
		while ($data = dbarray($result)) {
			$link_id = $data['link_id'];
			$link_target = ($data['link_window'] == "1" ? " target='_blank'" : "");
			if (!strstr($data['link_url'], "http://") && !strstr($data['link_url'], "https://")) {
				$data['link_url'] = BASEDIR.$data['link_url'];
			}
			if ($data['link_url'] != "---" && checkgroup($data['link_visibility'])) {

			$li_class = preg_match("/^".preg_quote(START_PAGE, '/')."/i", $data['link_url']) ? "current_page_item" : "";
				
			}
			if (strstr($data['link_name'], "%submenu%") && SUBNAV) { 
				$html .= "<li class='$li_class dropdown' >\n<a href='".$data['link_url']."' class='dropdown-toggle' data-toggle='dropdown'><span>".parseubb(str_replace("%submenu% ", "",$data['link_name']), "b|i|u|color")."</span> <b class='caret'></b></a>\n<ul class='dropdown-menu' role='menu' aria-labelledby='".$data['link_name']."' >\n";
			} elseif (strstr($data['link_name'], "%endmenu% ") && SUBNAV) {
				$html .= "<li class='$li_class'><a href='".$data['link_url']."' $link_target><span>".parseubb(str_replace("%endmenu% ", "",$data['link_name']), "b|i|u|color")."</span></a></li>\n</ul>\n</li>\n";
			} elseif (strstr($data['link_name'], "%ssmenu%") && SUBNAV) { 
				$html .= "<li class='$li_class'><a href='".$data['link_url']."' class='dropdown-toggle' data-toggle='dropdown' ><span >".parseubb(str_replace("%ssmenu% ", "",$data['link_name']), "b|i|u|color")."</span> <b class='caret'></b></a>\n<ul class='dropdown-menu sub-menu' >\n";
			} elseif (strstr($data['link_name'], "%endssmenu% ") && SUBNAV) {
				$html .= "<li class='$li_class' ><a href='".$data['link_url']."' $link_target><span>".parseubb(str_replace("%endssmenu% ", "",$data['link_name']), "b|i|u|color")."</span></a>\n</li>\n</ul>\n</li>\n";
			} else {
				if (strstr($data['link_name'], "---")) {
					$html .= "<li class='divider' ></li>\n";
				} elseif (strstr($data['link_name'], "%head%")) {
					$html .= "<li class='dropdown-header' role='presentation' >\n<span>".parseubb(str_replace("%head%", "", $data['link_name']), "b|i|u|color")."</span>\n</li>\n";
				} else {
					$html .= "<li class='$li_class' >\n<a href='".$data['link_url']."' $link_target><span>".parseubb($data['link_name'], "b|i|u|color")."</span></a>\n</li>\n";
				}
			}
			$i++;
		}
	} else {
		$html .= "<li>No menu items</li>\n";
	}
	$html .= "</ul>\n";
	$html .= "</div></div></div>";
	return $html;
}

function user_login() {
	global $locale, $userdata, $aidlink, $settings;

	if (iMEMBER) {
		$class = 'avatar-member';
		$name = "".$locale['welcome'].", ".ucfirst($userdata['user_name']);
		$avatar = ($userdata['user_avatar']) ? IMAGES."avatars/".$userdata['user_avatar'] :  IMAGES."avatars/noavatar.gif";
	} else {
		$class = 'avatar-guest';
		$name = $locale['login']." / ".$locale['register'];
		$avatar = IMAGES."avatars/noavatar.gif";
	}

	$html = "<ul class='nav navbar-nav pull-right m-r-20'>";

		//Search bar (Courtesy iTheme II)
	$locale['search'] = str_replace($locale['global_200'], "", $locale['global_202']);
	
	$html .= "<li id='user-info' class='dropdown' >\n";
	$html .= "<button type='button' class='btn btn-primary btn-sm dropdown-toggle' data-toggle='dropdown' style='margin-top: 8px;' >$name <span class='caret'></span></button>";
	if (iMEMBER) {
		$html .= "<ul class='dropdown-menu text-left'>";
		$html .= "<li><a href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'>".$locale['view']." ".$locale['profile']."</a></li>";
		$html .= "<li><a href='".BASEDIR."messages.php'>".$locale['global_121']."</a></li>";
		$html .= "<li class='divider'></li>";
		$html .= "<li class='dropdown-header' role='presentation'>".$locale['settings']."</li>";
		$html .= "<li><a href='".BASEDIR."edit_profile.php'>".$locale['edit']." ".$locale['profile']."</a></li>";
		$html .= (iADMIN) ? "<li><a href='".ADMIN."index.php$aidlink'>".$locale['global_123']."</a></li>\n" : "";
		$html .= "<li class='divider'></li>";
		$html .= "<li><a href='".BASEDIR."index.php?logout=yes'>".$locale['global_124']."</a></li>";
		$html .= "</ul>";
		$html .= "</li>";

	} else {

		$action_url = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");

		if (isset($_GET['redirect']) && strstr($_GET['redirect'], "/")) {
			$action_url = cleanurl(urldecode($_GET['redirect']));
		}

		$html .= "<ul class='dropdown-menu login-menu text-left'>\n";
		$html .= "<li class='dropdown-header' role='presentation'>\n </li>\n";
		$html .= "<li>\n";

		$html .= "<form name='loginform' method='post' action='$action_url' >\n";
		$html .= "<input type='text' id='username' name='user_name' class='form-control m-b-10 input-sm' placeholder='".$locale['global_101']."' >\n";
		$html .= "<input type='password' id='user_pass' name='user_pass' class='form-control m-b-10 input-sm' placeholder='".$locale['global_102']."' >\n";
		$html .= "<label><input type='checkbox' name='remember_me' value='y' title='".$locale['global_103']."' style='vertical-align:top; margin-right:5px;' > ".$locale['global_103']."</label>\n";
		$html .= "<button type='submit' name='login' class='m-t-10 m-b-10 btn btn-primary btn-sm' >".$locale['global_104']."</button> <br >\n";
		$html .= "</form>\n";

		if ($settings['enable_registration']) {
			$html .= "<li><a href='".BASEDIR."register.php'>".$locale['register']."</a></li>\n";
		}
		$html .= "<li><a href='".BASEDIR."lostpassword.php'>".$locale['global_108']."</a></li>\n";
		$html .= "</li>\n</ul>\n";
	}
	add_to_footer("<script type='text/javascript'>$('.dropdown-menu input, .dropdown-menu label').click(function(e) {e.stopPropagation();});</script>");
	$html .= "</ul>";
	
	$html .= "<div style='margin-top:7px;' class='pull-right m-r-15'>
	<form action='".BASEDIR."search.php' id='searchform' method='get'>
	<input type='text' class='textbox' onblur='if (this.value == \"\") {this.value = \"".$locale['search']."...\";}' onfocus='if (this.value == \"".$locale['search']."...\") {this.value = \"\";}' id='stext' name='stext' value='".$locale['search']."...' />
	</form></div>";
	return $html;
	
	

	
}

// Atom X Counter
function atom_counter($number, $offset=false, $speed=false){
	// lets go for 25%;
	if (!$offset) {
		$offset = ($number > 10000) ? floor($number*0.01) : floor($number*0.25);
	}
	if (!$speed) {
		$speed = ($number > 10000) ? 3000 : 2000;
	}

	add_to_head("
	<script src='".ASSETS."flipCounter/jquery.easing.1.3.js' type='text/javascript'></script>\n
	<script src='".ASSETS."flipCounter/jquery.flipCounter.1.2.pack.js' type='text/javascript'></script>\n
	<script src='".ASSETS."flipCounter/jshashtable.js' type='text/javascript'></script>\n
	");

	$html = "<div id='counter'><input type='hidden' name='counter-value'></div>\n";
	// replace the most bottom line for main.uk site
	// NON AJAX Version
	//$('#counter').flipCounter('startAnimation', { number: $number-$offset, end_number:$number, formatNumberOptions: {format:'0###,###,###', locale:'GB'}, easing:jQuery.easing.easeInOutCubic, duration:$speed});


	add_to_footer("
	<script type='text/javascript'>

	jQuery(document).ready(function($) {

			$('#counter').flipCounter();
			$('#counter').flipCounter({imagePath:'".ASSETS."flipCounter/flipCounter-medium.png'});
			$('#counter').flipCounter('startAnimation', { number: $number-$offset, end_number:$number, formatNumberOptions: {format:'0###,###,###', locale:'GB'}, easing:jQuery.easing.easeInOutCubic, duration:$speed, onAnimationStopped:setUpdateTimer});
	});
			function setUpdateTimer() { setInterval(checkMoreDownloads, 6000); }
			function checkMoreDownloads()
			{
				$.ajax({
				  url: '".ASSETS."flipCounter/download.ajax.php?Ajax=get_downloads',
				  success: function(data) {
					if(typeof data !== 'undefined') {
						if($('#counter').flipCounter('getNumber').toString() != data) {
							$('#counter').flipCounter('startAnimation', {end_number:parseInt(data), duration:1000});
						}
					}
				  }
				});
			}

	</script>
	");
	return $html;
}

// header with breadcrumbs minimal
// header with breadcrumbs minimal
function render_header($title, $subtitle=false, $image_url=false, $link=false)
{
	global $locale, $settings, $aidlink;
	$current_url = str_replace($settings['siteurl'], '', $_SERVER['PHP_SELF']);
	$current_url = str_replace($settings['site_path'], '', $current_url);
	/* Calculation of Luminance */
	$contrast = 'light';
	if ($image_url) {
		// validate
		$luminance = get_avg_luminance($image_url,10);
		$contrast = ($luminance > 170) ? 'light' : 'dark';
	}

	if (defined('ADMIN_PANEL')) {
		$current_url = str_replace("administration/", '', $current_url);
		$rows = dbarray(dbquery("SELECT admin_title FROM ".DB_ADMIN." WHERE admin_link='$current_url' LIMIT 1"));
		$current_page_title = (!empty($rows['admin_title'])) ? "<a href='".$current_url.$aidlink."'>".$rows['admin_title']."</a>\n" : "<a href='".$current_url.$aidlink."'>Custom Page</a>\n";
		$indexlink = "<a href='".ADMIN."index.php".$aidlink."'>Home</a>";
	} else {
		$rows = dbarray(dbquery("SELECT link_name FROM ".DB_SITE_LINKS." WHERE link_url='$current_url' LIMIT 1"));
		$current_page_title = (!empty($rows['link_name'])) ? "<a href='".BASEDIR.$current_url."'>".$rows['link_name']."</a>\n" : "<a href='".BASEDIR.$current_url."'>Custom Page</a>\n";
		$indexlink = "<a href='".BASEDIR."home.php'>Home</a>";
	}

	$html = "<section id='banner' ".($image_url ? "class='p-0 ".$contrast."'" : '')." role='banner' style='display: inline-block;'>\n";
	$html .= ($image_url) ? "<img class='img-responsive atom-banner-img' src='$image_url'>" : "";

	$html .= "<div ".($image_url ? "class='atom-banner-section'" : "").">\n";
	$html .= "<h1 ".($image_url ? "class='atom-banner-header'" : "").">$title</h1>\n";
	$html .= ($subtitle) ? "<h4 ".($image_url ? "class='atom-banner-desc'" : "").">$subtitle</h4>\n" : '';
	$html .= ($link) ? "<a class='btn btn-primary btn-sm m-t-10' href='$link'>Find out more</a>" : '';
	$html .= "</div>\n";

	$html .= "</section>\n";
	return $html;
}

// footer
function render_footer() {
	global $settings;

	$html = "<section id='greycontent' role='advert-content' >\n";
	$html .= "<div class='container-fluid'><div class='row'><div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
	$html .= "<h4 class='m-b-20'>About PHP-Fusion</h4>\n";
	$html .= "PHP-Fusion is an all in one integrated and scalable platform that will fit any purpose when it comes to website productions, whether you are creating community portals or personal sites.\n";
	$html .= "Founded as an open source project under the GNU AGPL v3, PHP-Fusion is licensed to be open and free to use. Derivative codes must be shared unless we grant you a <a href='".BASEDIR."license/'>license</a> to waive the AGPL agreement. This is what we believe gives the best possible protection for both PHP-Fusion and all the Developers that creates <a href='".INFUSIONS."addondb/index.php'>Addons</a> for PHP-Fusion.";
	$html .= "</div><div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
	$html .= "<h4 class='m-b-20'>Latest News</h4>\n";

	$result = dbquery("SELECT * FROM ".DB_NEWS." WHERE ".groupaccess('news_visibility')." AND (news_start='0'||news_start<=".time().")
					 AND (news_end='0'||news_end>=".time().") AND news_draft='0'
					ORDER BY news_sticky DESC, news_datestamp DESC LIMIT 0,5");

	$html .= "<ul class='atom-ul'>\n";
	while ($data = dbarray($result)) {
		$count_comment = dbcount("('comment_id')", DB_COMMENTS, "comment_type='N' AND comment_item_id='".$data['news_id']."'");
		$html .= "<li><i class='entypo open-book'></i> <a style='font-size:13px; color: #428bca' href='".BASEDIR."news.php?readmore=".$data['news_id']."'>".trim_text($data['news_subject'],30)."</a><br ><div class='spacer'></div><small><p>\n";
		$html .= "".showdate('shortdate', $data['news_datestamp'])." &middot; \n";
		if ($data['news_allow_comments'] == '1') {
			if ($count_comment < 1) {
				$html .= "<a href='".BASEDIR."news.php?readmore=".$data['news_id']."#comment'>Leave a comment</a> \n";
			} else {
				$html .= "<a href='".BASEDIR."news.php?readmore=".$data['news_id']."#comment'> $count_comment ".(($count_comment > 1) ? "Comments" : "Comment")." </a> \n";
			}
		} else {
			$html .= "Comments Disabled";
		}
		$html .= " &middot; ".$data['news_reads']." ".(($data['news_reads'] > 1) ? "Views" : "View")."\n";
		$html .= "</p></small></li>\n";
	}
	$html .= "</ul>\n";

	$html .= "</div><div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
	$html .= "<h4 class='m-b-20'>Documentations</h4>\n";
	$html .= "<ul class='atom-ul'>\n";
	$html .= "<li><i class='entypo text-doc'></i> <a href='".INFUSIONS."wiki/documentation.php?page=12'>PHP-Fusion Documentation</a><small><p>PHP-Fusion Documentation</p></small></li>";
	$html .= "<li><i class='entypo text-doc'></i> <a href='".INFUSIONS."wiki/documentation.php?page=20'>Getting Started</a><small><p>Installing PHP-Fusion</p></small></li>";
	$html .= "<li><i class='entypo text-doc'></i> <a href='".INFUSIONS."wiki/documentation.php?page=23'>Features List</a><small><p>PHP-Fusion Features</p></small></li>";
	$html .= "<li><i class='entypo text-doc'></i> <a href='".INFUSIONS."wiki/documentation.php?page=1'>PHP-Fusion Development</a><small><p>PHP-Fusion Development</p></small></li>";
	$html .= "<li><i class='entypo text-doc'></i> <a href='".BASEDIR."license/'>Licensing</a><small><p>Information about PHP-Fusion licenses</p></small></li>";
	$html .= "<li><i class='entypo text-doc'></i> <a href='".BASEDIR."coc.php'>Code of Conduct</a><small><p>Our Code of Conduct list</p></small></li>";
	$html .= "</ul>\n";

	$html .= "</div><div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
	$html .= "<h4 class='m-b-20'>Contact Information</h4>\n";
	$html .= "<strong>PHP-Fusion Inc</strong><br />\n";
	$html .= "For contact please send an email to ".hide_email($settings['siteemail'])."<br /><br />";
	$html .= "<h4 class='m-b-20'>Sponsors</h4>\n";
	//$html .= "<a href='https://partners.a2hosting.com/solutions.php?id=5169&url=554' target='_blank' title='A2 Hosting'><img src='".IMAGES."a2/a2125x125.gif' alt='A2 Hosting' style='width:100px; height:100px;' ></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href='http://www.jetbrains.com' target='_blank' title='JetBrains'><img src='".IMAGES."JBlogo.gif' alt='JetBrains' style='width:100px; height:60px;' ></a>";
	$html .= "<a href='https://partners.a2hosting.com/solutions.php?id=5169&url=554' target='_blank' title='A2 Hosting'><img src='".IMAGES."a2/a2125x125.gif' alt='A2 Hosting' style='width:100px; height:100px;' /></a> ";
	$html .= "<a href='http://www.dealslands.co.uk' target='_blank'><img src='".IMAGES."dealsland.png' alt='Dealslands UK' style='width:100px; height:100px;' /></a> ";
	$html .= "</div>\n</div>\n</div>\n";
	$html .= "</section>\n";
	return $html;
}

// comments api
function atom_comment_box($ctype, $cdb, $ccol, $cid, $clink) {
	global $settings, $locale, $userdata, $aidlink;
	include LOCALE.LOCALESET."comments.php";

	$html = '';
	$link = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
	$link = preg_replace("^(&amp;|\?)c_action=(edit|delete)&amp;comment_id=\d*^", "", $link);
	$cpp = $settings['comments_per_page'];

	if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "delete") && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {

		if ((iADMIN && checkrights("C")) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS, "comment_id='".$_GET['comment_id']."' AND comment_name='".$userdata['user_id']."'"))) {
			$result = dbquery(
			"DELETE FROM ".DB_COMMENTS."
			WHERE comment_id='".$_GET['comment_id']."'".(iADMIN ? "" : "
			AND comment_name='".$userdata['user_id']."'")
			);
		}
		redirect($clink.($settings['comments_sorting'] == "ASC" ? "" : "&amp;c_start=0")."#c-1xxx");
	}

	if ($settings['comments_enabled'] == "1") {
		// start post/*
		if ((iMEMBER || $settings['guestposts'] == "1") && isset($_POST['p_comment'])) {
			if (iMEMBER) {
				$comment_name = $userdata['user_id'];
			} elseif ($settings['guestposts'] == "1") {
				if (!isset($_POST['comment_name'])) { redirect($link); }
				$comment_name = trim(stripinput($_POST['comment_name']));
				$comment_name = preg_replace("(^[+0-9\s]*)", "", $comment_name);
				if (isnum($comment_name)) { $comment_name = ""; }
				$_CAPTCHA_IS_VALID = false;
				include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php";
				if (!isset($_POST['captcha_code']) || $_CAPTCHA_IS_VALID == false) {
					redirect($link);
				}
			}
			$comment_message = trim(stripinput(censorwords($_POST['comment_message'])));
			// edit
			if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit") && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
				$comment_updated = false;
				if ((iADMIN && checkrights("C")) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS,
					"comment_id='".$_GET['comment_id']."' AND comment_item_id='".$cid."'
					AND comment_type='".$ctype."' AND comment_name='".$userdata['user_id']."'
					AND comment_hidden='0'"))) {
					if ($comment_message) {
						$result = dbquery("UPDATE ".DB_COMMENTS." SET comment_message='".$comment_message."'
										WHERE comment_id='".$_GET['comment_id']."'".(iADMIN ? "" : "
										AND comment_name='".$userdata['user_id']."'"));
						$comment_updated = true;
					}
				}
				if ($comment_updated) {
					if ($settings['comments_sorting'] == "ASC") {
						$c_operator = "<=";
					} else {
						$c_operator = ">=";
					}

					$c_count = dbcount("(comment_id)", DB_COMMENTS,
								"comment_id".$c_operator."'".$_GET['comment_id']."'
								AND comment_item_id='".$cid."'
								AND comment_type='".$ctype."'");
					$c_start = (ceil($c_count / $cpp) - 1) * $cpp;
				}

				redirect($clink."&amp;c_start=".(isset($c_start) && isnum($c_start) ? $c_start : "")."#c".$_GET['comment_id']."");

			} else {

				if (!dbcount("(".$ccol.")", $cdb, $ccol."='".$cid."'")) { redirect(BASEDIR."index.php"); }

				if ($comment_name && $comment_message) {
					require_once INCLUDES."flood_include.php";
					if (!flood_control("comment_datestamp", DB_COMMENTS, "comment_ip='".USER_IP."'")) {
						$result = dbquery(
							"INSERT INTO ".DB_COMMENTS." (
							comment_item_id, comment_type, comment_name, comment_message, comment_datestamp,
							comment_ip, comment_ip_type, comment_hidden
						) VALUES (
							'".$cid."', '".$ctype."', '".$comment_name."', '".$comment_message."', '".time()."',
							'".USER_IP."', '".USER_IP_TYPE."', '0'
						)"
						);
					}
					$new_post_id = mysql_insert_id();
				}

				if ($settings['comments_sorting'] == "ASC") {
					$c_count = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$cid."'
									AND comment_type='".$ctype."'");
					$c_start = (ceil($c_count / $cpp) - 1) * $cpp;
				} else {
					$c_start = 0;
				}
				redirect($clink."&amp;c_start=".$c_start."#c".$new_post_id);
			}

		} // end of post.

		$c_arr = array(
			"c_con" => array(),
			"c_info" => array(
				"c_makepagenav" => false,
				"admin_link" => false
			)
		);
		$c_rows = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$cid."' AND comment_type='".$ctype."' AND comment_hidden='0'");
		if (!isset($_GET['c_start']) && $c_rows > $cpp) {
			$_GET['c_start'] = (ceil($c_rows / $cpp) - 1) * $cpp;
		}
		if (!isset($_GET['c_start']) || !isnum($_GET['c_start'])) { $_GET['c_start'] = 0; }
		$result = dbquery(
			"SELECT tcm.comment_id, tcm.comment_name, tcm.comment_message, tcm.comment_datestamp, tcu.user_name, tcu.user_avatar, tcu.user_status
			FROM ".DB_COMMENTS." tcm
			LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
			WHERE comment_item_id='".$cid."' AND comment_type='".$ctype."' AND comment_hidden='0'
			ORDER BY comment_datestamp ".$settings['comments_sorting']." LIMIT ".$_GET['c_start'].",".$cpp
			);
		$com_rows = dbrows($result);

		$html .= "<section id='comment' class='m-b-20 p-10'>\n";
		$html .= "<h4><b>Discuss this ($c_rows ".($c_rows > 1 ? 'Comments' : 'Comment').")</b></h4>\n";

		if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit") && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
			$eresult = dbquery(
				"SELECT tcm.comment_id, tcm.comment_name, tcm.comment_message, tcu.user_name
				FROM ".DB_COMMENTS." tcm
				LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
				WHERE comment_id='".$_GET['comment_id']."' AND comment_item_id='".$cid."'
				AND comment_type='".$ctype."' AND comment_hidden='0'"
			);
			$com_rows = dbrows($eresult);
			if (dbrows($eresult)) {
				$edata = dbarray($eresult);
				if ((iADMIN && checkrights("C")) || (iMEMBER && $edata['comment_name'] == $userdata['user_id'] && isset($edata['user_name']))) {
					$comment_message = $edata['comment_message'];
				}
			} else {
				$comment_message = "";
			}
			$ccid = '';
		} else {
			$comment_message = "";
			$last_row = dbarray(dbquery("SELECT comment_id FROM ".DB_COMMENTS." ORDER BY comment_id DESC LIMIT 1"));
			$ccid =  ($last_row) ? "#c".$last_row['comment_id']+1 : '';
		}

		$user_avatar = display_avatar($userdata, '50px');
		if (iMEMBER || $settings['guestposts'] == "1") {

			require_once INCLUDES."bbcode_include.php";
			$html .= "<form id='comment_form' name='inputform' method='post' action='".FUSION_REQUEST."' role='form'>\n";

			if (iGUEST) {
				$html .= "<div align='center' class='tbl'>\n".$locale['c104']."<br >\n";
				$html .= "<input type='text' name='comment_name' maxlength='30' class='textbox' style='width:360px' >\n";
				$html .= "</div>\n";
			}

			$html .= "<div class='row m-t-20 m-b-20'>\n";
			$html .= "<div class='col-xs-1 col-sm-1 col-md-1 col-lg-1'>\n";

			$html .= "<span class='pull-left'>$user_avatar</span>\n";
			$html .= "</div><div class='col-xs-11 col-sm-11 col-md-11 col-lg-11' style='border:1px solid #ccc; padding:10px;'>\n";
			$html .= "<div class='form-group'>\n";
			$html .= display_bbcodes("100%;", "comment_message");
			$html .= "<textarea name='comment_message' rows='4' class='form-control' placeholder='Post your comment...' style='width:100%'>".$comment_message."</textarea><br >\n";

			if (iGUEST && (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT))) {
				$_CAPTCHA_HIDE_INPUT = false;
				//     echo "<div style='width:80%; margin:10px auto;'>";
				//    echo $locale['global_150']."<br >\n";
				//   include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
				if (!$_CAPTCHA_HIDE_INPUT) {
					//   echo "<br >\n<label for='captcha_code'>".$locale['global_151']."</label>";
					//     echo "<br >\n<input type='text' id='captcha_code' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' >\n";
				}
				$html .= "</div>\n";
				$html .= "</div>\n";
			}
			$html .= "<input class='btn btn-primary' type='submit' name='p_comment' value='".($comment_message ? $locale['c103'] : $locale['c102'])."' class='button' >";
			$html .= "</div>\n</form>\n";
			$html .= "</div>\n</div>\n";
		} else {
			$html .= $locale['c105']."\n";
		}
		$html .= "<hr>\n";
		if (dbrows($result)) {
			$i = ($settings['comments_sorting'] == "ASC" ? $_GET['c_start']+1 : $c_rows - $_GET['c_start']);
			if ($c_rows > $cpp) {
				$c_arr['c_info']['c_makepagenav'] = makepagenav($_GET['c_start'], $cpp, $c_rows, 3, $clink."&amp;", "c_start");
			}

			while ($data = dbarray($result)) {
				$c_arr['c_con'][$i]['comment_id'] = $data['comment_id'];
				$c_arr['c_con'][$i]['edit_dell'] = false;
				$c_arr['c_con'][$i]['i'] = $i;
				if ($data['user_name']) {
					$c_arr['c_con'][$i]['comment_name'] = profile_link($data['comment_name'], $data['user_name'], $data['user_status']);
				} else {
					$c_arr['c_con'][$i]['comment_name'] = $data['comment_name'];
				}

				$c_arr['c_con'][$i]['user_avatar'] = display_avatar($data, '50px');
				$c_arr['c_con'][$i]['comment_datestamp'] = $locale['global_071'].showdate("longdate", $data['comment_datestamp']);
				$c_arr['c_con'][$i]['comment_message'] = "<!--comment_message-->\n".nl2br(parseubb(parsesmileys($data['comment_message'])));
				if ((iADMIN && checkrights("C")) || (iMEMBER && $data['comment_name'] == $userdata['user_id'] && isset($data['user_name']))) {
					$c_arr['c_con'][$i]['edit_dell'] = "<!--comment_actions-->\n";
					$c_arr['c_con'][$i]['edit_dell'] .= "<a href='".$clink."&amp;c_action=edit&amp;comment_id=".$data['comment_id']."#comment_form'>";
					$c_arr['c_con'][$i]['edit_dell'] .= $locale['c108']."</a> |\n";
					$c_arr['c_con'][$i]['edit_dell'] .= "<a href='".$clink."&amp;c_action=delete&amp;comment_id=".$data['comment_id']."#comment_form'>";
					$c_arr['c_con'][$i]['edit_dell'] .= $locale['c109']."</a>";
				}
				$settings['comments_sorting'] == "ASC" ? $i++ :	$i--;
			}
			if (iADMIN && checkrights("C")) {
				$c_arr['c_info']['admin_link'] = "<!--comment_admin-->\n";
				$c_arr['c_info']['admin_link'] .= "<a href='".ADMIN."comments.php".$aidlink."&amp;ctype=".$ctype."&amp;cid=".$cid."'>".$locale['c106']."</a>";
			}
		}

		// Render comments
		$html .= "<a id='comments' name='comments'></a>";
		$html .= render_comment($c_arr['c_con'], $c_arr['c_info']);
		$html .= "</section>\n";
	}
	return $html;
}

// ratings form
function atom_label($title) {
	return "<h6 class='m-b-20' style='background:#121A23; color:#fff !important; padding:5px 10px; text-transform:uppercase;'><strong>$title</strong></h6>\n";
}

function atom_ratings_box($rating_type, $rating_item_id, $rating_link, $item_name=false)
{
	global $settings, $locale, $userdata;
	$html = '';
	$item_name = (!$item_name) ? 'Post' : $item_name;

	if ($settings['ratings_enabled'] == "1") {
		if (iMEMBER) {
			$d_rating = dbarray(dbquery("SELECT rating_vote,rating_datestamp FROM ".DB_RATINGS." WHERE rating_item_id='".$rating_item_id."' AND rating_type='".$rating_type."' AND rating_user='".$userdata['user_id']."'"));
		}
		if (isset($_POST['post_rating'])) {
			if (isnum($_POST['rating']) && $_POST['rating'] > 0 && $_POST['rating'] < 6 && !isset($d_rating['rating_vote'])) {
				$result = dbquery("INSERT INTO ".DB_RATINGS." (rating_item_id, rating_type, rating_user, rating_vote, rating_datestamp, rating_ip, rating_ip_type) VALUES ('$rating_item_id', '$rating_type', '".$userdata['user_id']."', '".$_POST['rating']."', '".time()."', '".USER_IP."', '".USER_IP_TYPE."')");
			}
			redirect($rating_link);
		} elseif (isset($_POST['remove_rating'])) {
			$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='$rating_item_id' AND rating_type='$rating_type' AND rating_user='".$userdata['user_id']."'");
			redirect($rating_link);
		}

		$ratings = array(5 => $locale['r120'], 4 => $locale['r121'], 3 => $locale['r122'], 2 => $locale['r123'], 1 => $locale['r124']);

		$html .= "<div class='panel-atom panel-default'>\n";
		$html .= "<div class='panel-body'>\n";
		if (!iMEMBER) {
			$html .= "<p><strong>$item_name ".$locale['r104']."</strong></p>\n";
		} elseif (isset($d_rating['rating_vote'])) {
			$html .= "<div>\n";
			$html .= "<form name='removerating' method='post' action='".$rating_link."' role='rating-form'>\n";
			$html .= sprintf($locale['r105'], $ratings[$d_rating['rating_vote']], showdate("longdate", $d_rating['rating_datestamp']))."<br ><br >\n";
			$html .= "<button type='submit' name='remove_rating' class='btn btn-remove'>".$locale['r102']."</button>\n";
			$html .= "</form>\n</div>\n";
		} else {
			$html .= "<div>\n";
			$html .= "<form name='postrating' method='post' action='".$rating_link."' role='rating-form'>\n";
			$html .= "<div class='form-group'>\n";
			$html .= "<label class='sr-only' for='rating'>Rate : </label>\n";
			$html .= "<select id='rating' name='rating' class='input-md form-control' placeholder='' >\n";
			$html .= "<option value='0'>What do you think of this $item_name ?</option>\n";
			foreach($ratings as $rating=>$rating_info) {
				$html .= "<option value='".$rating."'>$rating_info</option>\n";
			}
			$html .= "</select>\n";
			$html .= "</div>\n";
			$html .= "<button type='submit' name='post_rating' class='btn btn-sm btn-block btn-primary' >".$locale['r103']."</button>\n";
			$html .= "</form>\n</div>";
		}
		$html .= "</div>\n</div>";
	   return $html;
	}
}

// ratings display
function display_rating_result($rating_type, $rating_item_id, $rating_link)
{
	global $locale;
	$html = '';
	$tot_votes = dbcount("(rating_item_id)", DB_RATINGS, "rating_item_id='".$rating_item_id."' AND rating_type='".$rating_type."'");
	if (iMEMBER) {
		$d_rating = dbarray(dbquery("SELECT rating_vote,rating_datestamp FROM ".DB_RATINGS." WHERE rating_item_id='".$rating_item_id."' AND rating_type='".$rating_type."'"));
	}
	$all_ratings = dbquery("SELECT rating_vote FROM ".DB_RATINGS." WHERE rating_item_id='$rating_item_id' AND rating_type='$rating_type'");
	if (dbrows($all_ratings)>0) {
		$rate = '0';
		while($rdata = dbarray($all_ratings)) {
			$rate = $rate+$rdata['rating_vote'];
		}
	$rate = $rate/dbrows($all_ratings);
	} else {
		$rate = 0;
	}
	return "<i class='glyphicon glyphicon-heart'></i> ".$locale['r100']." <span class='pull-right'>".($rate ? str_repeat( "<i class='glyphicon glyphicon-star'></i>", $rate) : '-')."</span>";
}

// user info api
function user_info_bar($data)
{
	global $settings, $userdata, $locale;

	add_to_head("<link href='".THEME."tpl/tpl_css/profile.css' rel='stylesheet' media='screen'>");
	require_once LOCALE.LOCALESET."user_fields.php";
	require_once LOCALE.LOCALESET."user_fields/user_location.php";
	require_once LOCALE.LOCALESET."user_fields/user_comments-stat.php";
	require_once LOCALE.LOCALESET."user_fields/user_forum-stat.php";
	require_once LOCALE.LOCALESET."user_fields/user_shouts-stat.php";

	if (iMEMBER) {
		$uf_query = dbquery(
			"SELECT * FROM ".DB_USER_FIELDS." tuf
				INNER JOIN ".DB_USER_FIELD_CATS." tufc ON tuf.field_cat = tufc.field_cat_id
				ORDER BY field_cat_order, field_order"
		);
		$i = 0;
		if (dbrows($uf_query)) {
			while($data = dbarray($uf_query)) {
				if ($i != $data['field_cat']) {
					$i = $data['field_cat'];
					$cats[$i] = array(
						"field_cat_name" => $data['field_cat_name'],
						"field_cat" => $data['field_cat']
					);
				}
				$fields[$i][] = (array_key_exists($data['field_name'], $userdata)) ? array('field_name'=>$data['field_name'], 'value'=>$userdata[$data['field_name']]) : array('field_name'=>$data['field_name'], 'value'=>'N/A');
			}
		}
		$avatar = ($userdata['user_avatar'] && file_exists(IMAGES."avatars/".$userdata['user_avatar'])) ? "<img class='img-rounded' src='".IMAGES."avatars/".$userdata['user_avatar']."' style='max-width:50px;' />" : "<img style='max-width:50px;' src='".IMAGES."avatars/noavatar2.png' />";
	} else {
		$avatar = "<img src='".IMAGES."avatars/noavatar100.png' style='max-width:50px;' />";
	}
	$html = "";
	// user stats and groups.
	if (iMEMBER) {
		$html .= "<div class='profile-center'>\n";
		$html .= "<div class='user-details'>\n";
		$html .= "<div class='pull-left m-l-20 m-r-8' style='margin-top:5px; position:absolute; z-index:9 '>\n $avatar \n</div>\n";
		$html .= "<div class='pull-left' style='margin-left:90px;'>\n";
		$html .= "<ul class='nav user-stats-bar'>\n";
		$html .= "<li class='dropdown'><a class='dropdown-toggle' data-toggle='dropdown' href='#'>\n";
		$html .= "<h4>".$userdata['user_name']." <b class='caret'></b></h4> <span><small>".getuserlevel($userdata['user_level'])."</small></span>\n</a>";
		$html .= "<ul class='dropdown-menu' style='width:400px;'><li>\n";
		$html .= "<p><strong>Fusioneer ".timer($userdata['user_joined'])."</strong>\n</p>\n";
		$html .= "<p class='pull-left' style='width:180px;'>\n";
		$html .= "<small>\n";
		$html .= "<strong>".$locale['u066']."</strong>: ".showdate("shortdate", $userdata['user_joined'])."</strong><br />\n";
		$lastVisit = ($userdata['user_lastvisit']) ? showdate("shortdate", $userdata['user_lastvisit']) : $locale['u042'];
		$html .= "<strong>".$locale['u067']."</strong>: $lastVisit<br />";
		$html .= "<strong>".$locale['uf_location']."</strong>: ".(($userdata['user_location']) ? $userdata['user_location'] : 'No Location')."</strong>";
		$html .= "</small>\n";
		$html .= "</p>\n";
		$html .= "<p class='pull-left' style='width:100px;'>\n";
		$html .= "<small><strong>".$locale['u047']."</strong></small><br />\n";
		$html .= "<small>\n";
		$html .= "<strong>".$locale['uf_comments-stat']."</strong> : ".number_format(dbcount("(comment_id)", DB_COMMENTS, "comment_name='".$userdata['user_id']."'"))."<br />\n";
		$html .= "<strong>".$locale['uf_forum-stat']."</strong> : ".number_format($userdata['user_posts'])."<br />\n";
		$html .= "<strong>".$locale['u049']."</strong> : ".(($userdata['user_ip_type'] == '4') ? $userdata['user_ip'] : 'Local IP')."\n";
		$html .= "</small>\n";
		$html .= "</p>\n";
		$html .= "</li>\n</ul>\n</li></ul>\n";
		$html .= "\n</div>\n";
		$_pull = '';
		$_pull_width = '30%';
		if (iMEMBER) {
			// go to profile page.
			$html .= "<div class='user-fields user-icons hidden-xs hidden-sm hidden-md'><a style='border-left: 1px solid rgba(0,0,0,0.23);' href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'><i class='entypo user'></i></a>\n</div>\n";
			// private message
			// pm
			$message_count = dbcount('("message_id")', DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_read='0'");
			$message_count = ($message_count>0) ? $message_count : '0';
			$html .= "<div class='user-fields hidden-xs hidden-sm hidden-md' style='margin:0px'>
			<ul class='nav user-stats-bar'>
			<li class='dropdown'><a class='icon dropdown-toggle' data-toggle='dropdown' href='#'><i class='entypo mail'></i> ".(($message_count) ? "<span class='label label-danger' style='font-size:11px; padding:0px 10px; color:#fff;'>$message_count</span>" : '')." <b class='caret'></b>\n</a>";
			$html .= "<ul class='dropdown-menu' style='width:280px; padding-top:0px;'>\n";
			$html .= "<li style='padding-bottom:0px; background: url(".$settings['siteurl']."themes/Atom-X2/images/pm_header.jpg); height:45px; padding-left:60px; padding-top:15px; color:#fff'><strong><a href='".BASEDIR."messages.php' style='color:#fff; line-height:5px; margin-bottom:0px;'>".$locale['global_121']."</a></strong>\n";

			

			$html .= "</li>\n";
			$get_latest_mail = dbquery("SELECT * FROM ".DB_MESSAGES." WHERE message_to='".$userdata['user_id']."' AND message_read='0' ORDER BY message_datestamp DESC LIMIT 0,5");
			if (dbrows($get_latest_mail)>0) {
				$i = 0;
				while ($maildata = dbarray($get_latest_mail)) {
					$html .= "<a href='".BASEDIR."messages.php?folder=inbox&msg_read=".$maildata['message_id']."'><li style='padding:3px 10px; ".($i>0 ? 'border-top:1px dashed rgba(0,0,0,0.1);': '')."'><span class='pull-right' style='font-weight:normal;'><small>".timer($maildata['message_datestamp'])."</small></span><br /><span style='color:#222; font-size:12px; font-weight:bold;'>".$maildata['message_subject']."</span></li></a>\n";
				$i++;
				}
			} else {
				$html .= "<li style='padding:10px 10px;' class='text-center'><small>".sprintf($locale['UM085'], $message_count)." ".($message_count > 1 ? $locale['global_126'] : $locale['global_127']).".</small></li>\n";
				$html .= "<a href='".BASEDIR."messages.php'><li style='padding:10px 10px; border-top:1px dashed rgba(0,0,0,0.1);' class='text-left text-center'><span style='font-size:12px; color:#222; font-weight:bold'>".$locale['enter']." ".$locale['message']."</span></li></a>\n";
			}

			$html .= "</ul>\n</li></ul>\n";
			$html .= "\n</div>\n";
			// user groups
			$html .= "<div class='user-fields hidden-xs hidden-sm hidden-md' style='margin:0px'>
				<ul class='nav user-stats-bar'>
				<li class='dropdown'><a class='icon dropdown-toggle' data-toggle='dropdown' href='#' ><i class='entypo users'></i>\n <b class='caret'></b></a>";
			$html .= "<ul class='dropdown-menu'><li>\n";
			$html .= "<p><small><strong>".$locale['u057']."</strong></small>\n</p>\n";
			$user_groups = strpos($userdata['user_groups'], ".") == 0 ? substr($userdata['user_groups'], 1) : $userdata['user_groups'];
			$user_groups = explode(".", $user_groups);
			if (!empty($user_groups['0'])) {
				for ($i = 0; $i < count($user_groups); $i++) {
					$html .= "<p><span><a href='".BASEDIR."profile.php?group_id=".$user_groups[$i]."'>".getgroupname($user_groups[$i])."</a></span> : ".getgroupname($user_groups[$i], true)."</p>\n";
				}
			} else {
				$html .= "<p><span>".$locale['no']." ".$locale['u057']."</p>\n";
			}
			$html .= "</li>\n</ul>\n</li></ul>\n";
			$html .= "\n</div>\n";
		}
		$html .= "</div>\n";
		$html .= "</div>\n";
		} else {
			$_pull = 'pull-right';
			$_pull_width = '65%';
	}
	return $html;
}

function image_overlay($img_src, $link, $overlay_html, $static=false) {
	$html = "<a href='".$link."' class='slideup animate display-block p-0 ".($static ? 'static' : '')."'>\n";
	$html .= "<img src='".$img_src."'>";
	$html .= "<div class='rollover ".($static ? 'static' : '')."'><span class='text-center centered'>$overlay_html</span></div>\n";
	$html .= "</a>";
	return $html;
}