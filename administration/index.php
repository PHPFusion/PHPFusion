<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: index.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";

if (!iADMIN || $userdata['user_rights'] == "" || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}

require_once THEMES."templates/admin_header.php";

if (!isset($_GET['pagenum']) || !isnum($_GET['pagenum'])) $_GET['pagenum'] = 1;

$admin_images = TRUE;

// Work out which tab is the active default (redirect if no tab available)
// These come from Panels.
$default = FALSE;
for ($i = 5; $i > 0; $i--) {
	if ($pages[$i]) {
		$default = $i;
	}
}
if (!$default) {
	//	redirect("../index.php");
}

// Ensure the admin is allowed to access the selected page
$pages['0'] = 'AcpHome';
if (!$pages[$_GET['pagenum']]) {
	redirect("index.php".$aidlink."&pagenum=$default");
}


if ($_GET['pagenum'] == 0) {
	$members_registered = dbcount("(user_id)", DB_USERS, "user_status<='1' OR user_status='3' OR user_status='5'");
	$members_unactivated = dbcount("(user_id)", DB_USERS, "user_status='2'");
	$members_security_ban = dbcount("(user_id)", DB_USERS, "user_status='4'");
	$members_canceled = dbcount("(user_id)", DB_USERS, "user_status='5'");
	// Start Dashboard Widget - Non API in 7.03
	// use php responsive units tweak.
	$mobile = '12';
	$tablet = '12';
	$laptop = '6';
	$desktop = '3';

	opentable($locale['250']);
	echo "<!--Start Members-->\n";
	echo "<div class='row'>\n";
	echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
	openside();
	echo "<img class='pull-left m-r-10' src='".get_image("ac_Members")."'/>\n";
	echo "<h4 class='text-right m-t-0 m-b-0'>\n".number_format($members_registered)."</h4>";
	echo "<span class='m-t-10 text-uppercase text-lighter text-smaller pull-right'><strong>".$locale['251']."</strong></span>\n";
	closeside("".(checkrights("M") ? "<div class='text-right text-uppercase'>\n<a class='text-smaller' href='".ADMIN."members.php".$aidlink."'>".$locale['255']."</a><i class='entypo right-open-mini'></i></div>\n" : '')."");
	echo "</div>\n<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
	openside();
	echo "<img class='pull-left m-r-10' src='".get_image("ac_Members")."'/>\n";
	echo "<h4 class='text-right m-t-0 m-b-0'>\n".number_format($members_canceled)."</h4>";
	echo "<span class='m-t-10 text-uppercase text-lighter text-smaller pull-right'><strong>".$locale['263']."</strong></span>\n";
	closeside("".(checkrights("M") ? "<div class='text-right text-uppercase'>\n<a class='text-smaller' href='".ADMIN."members.php".$aidlink."&amp;status=5'>".$locale['255']."</a> <i class='entypo right-open-mini'></i></div>\n" : '')."");
	echo "</div>\n<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
	openside();
	echo "<img class='pull-left m-r-10' src='".get_image("ac_Members")."'/>\n";
	echo "<h4 class='text-right m-t-0 m-b-0'>\n".number_format($members_unactivated)."</h4>";
	echo "<span class='m-t-10 text-uppercase text-lighter text-smaller pull-right'><strong>".$locale['252']."</strong></span>\n";
	closeside("".(checkrights("M") ? "<div class='text-right text-uppercase'>\n<a class='text-smaller' href='".ADMIN."members.php".$aidlink."&amp;status=2'>".$locale['255']."</a> <i class='entypo right-open-mini'></i></div>\n" : '')."");
	echo "</div>\n<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
	openside();
	echo "<img class='pull-left m-r-10' src='".get_image("ac_Members")."'/>\n";
	echo "<h4 class='text-right m-t-0 m-b-0'>\n".number_format($members_security_ban)."</h4>";
	echo "<span class='m-t-10 text-uppercase text-lighter text-smaller pull-right'><strong>".$locale['253']."</strong></span>\n";
	closeside("".(checkrights("M") ? "<div class='text-right text-uppercase'><a class='text-smaller' href='".ADMIN."members.php".$aidlink."&amp;status=4'>".$locale['255']."</a> <i class='entypo right-open-mini'></i></div>\n" : '')."");
	echo "</div>\n</div>\n";
	echo "<!--End Members-->\n";
	/*
	if ($settings['enable_deactivation'] == "1") {
		$time_overdue = time()-(86400*$settings['deactivation_period']);
		$members_inactive = dbcount("(user_id)", DB_USERS, "user_lastvisit<'$time_overdue' AND user_actiontime='0' AND user_joined<'$time_overdue' AND user_status='0'");
		echo "<a href='".ADMIN."members.php".$aidlink."&amp;status=8'>".$locale['264']."</a> $members_inactive<br />\n";
	}
	*/

	$mobile = '12';
	$tablet = '12';
	$laptop = '6';
	$desktop = '4';

	echo "<div class='row'>\n";
	echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
	// port to functions later
	openside('', 'blank-stats');
	echo "<span class='text-smaller text-uppercase'><strong>".$locale['265']." ".$locale['258']."</strong></span>\n<br/>\n";
	echo "<div class='clearfix m-t-10'>\n";
	echo "<img class='img-responsive pull-right' src='".get_image("ac_Forums")."'/>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['265']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('forum_id')", DB_FORUMS))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['256']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('post_id')", DB_THREADS))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['259']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('post_id')", DB_POSTS))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['260']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".(dbcount("('user_id')", DB_USERS, "user_posts > '0'"))."</h4>\n";
	echo "</div>\n";
	echo "</div>\n";
	closeside();
	echo "</div>\n<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
	openside('', 'green-stats');
	echo "<span class='text-smaller text-uppercase'><strong>".$locale['268']." ".$locale['258']."</strong></span>\n<br/>\n";
	echo "<div class='clearfix m-t-10'>\n";
	echo "<img class='img-responsive pull-right' src='".get_image("ac_Downloads")."'/>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['268']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('download_id')", DB_DOWNLOADS))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('comment_id')", DB_COMMENTS, "comment_type='d'"))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='d'"))."</h4>\n";
	echo "</div>\n";
	echo "</div>\n";
	closeside();
	echo "</div>\n<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
	openside('', 'purple-stats');
	echo "<span class='text-smaller text-uppercase'><strong>".$locale['269']." ".$locale['258']."</strong></span>\n<br/>\n";
	echo "<div class='clearfix m-t-10'>\n";
	echo "<img class='img-responsive pull-right' src='".get_image("ac_News")."'/>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['269']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('news_id')", DB_NEWS))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('comment_id')", DB_COMMENTS, "comment_type='n'"))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='n'"))."</h4>\n";
	echo "</div>\n";
	echo "</div>\n";
	closeside();
	echo "</div>\n<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
	openside('', 'dark-stats');
	echo "<span class='text-smaller text-uppercase'><strong>".$locale['270']." ".$locale['258']."</strong></span>\n<br/>\n";
	echo "<div class='clearfix m-t-10'>\n";
	echo "<img class='img-responsive pull-right' src='".get_image("ac_Articles")."'/>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['270']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('article_id')", DB_ARTICLES))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('comment_id')", DB_COMMENTS, "comment_type='A'"))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='a'"))."</h4>\n";
	echo "</div>\n";
	echo "</div>\n";
	closeside();
	echo "</div>\n<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
	openside('', 'blank-stats');
	echo "<span class='text-smaller text-uppercase'><strong>".$locale['271']." ".$locale['258']."</strong></span>\n<br/>\n";
	echo "<div class='clearfix m-t-10'>\n";
	echo "<img class='img-responsive pull-right' src='".get_image("ac_Web Links")."'/>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['271']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('weblink_id')", DB_WEBLINKS))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('comment_id')", DB_COMMENTS, "comment_type='L'"))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='l'"))."</h4>\n";
	echo "</div>\n";
	echo "</div>\n";
	closeside();
	echo "</div>\n<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
	openside('', 'flat-stats');
	echo "<span class='text-smaller text-uppercase'><strong>".$locale['272']." ".$locale['258']."</strong></span>\n<br/>\n";
	echo "<div class='clearfix m-t-10'>\n";
	echo "<img class='img-responsive pull-right' src='".get_image("ac_Photo Albums")."'/>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['272']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('photo_id')", DB_PHOTOS))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("('comment_id')", DB_COMMENTS, "comment_type='P'"))."</h4>\n";
	echo "</div>\n";
	echo "<div class='pull-left display-inline-block m-r-10'>\n";
	echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
	echo "<h4 class='m-t-0'>".number_format(dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='p'"))."</h4>\n";
	echo "</div>\n";
	echo "</div>\n";
	closeside();
	echo "</div>\n</div>\n";


	// comments commit, ratings commit, submissions commit bloat up.
	$comments_type = array(
		'N' => $locale['269'],
		'D' => $locale['268'],
		'P' => $locale['272'],
		'A' => $locale['270'],
	);
	$submit_type = array(
		'n' => $locale['269'],
		'd' => $locale['268'],
		'p' => $locale['272'],
		'a' => $locale['270'],
		'l' => $locale['271'],
	);
	$link = array(
		'N' => $settings['siteurl']."news.php?readmore=%s",
		'D' => $settings['siteurl']."downloads.php?download_id=%s",
		'P' => $settings['siteurl']."photogallery.php?photo_id=%s",
		'A' => $settings['siteurl']."articles.php?article_id=%s",
	);

	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 co-sm-6 col-md-6 col-lg-4'>\n";
	$rows = dbcount("('comment_id')", DB_COMMENTS);
	openside("<span class='text-smaller text-uppercase'><strong>".$locale['277']."</strong></span><span class='pull-right label label-warning'>".number_format($rows)."</span>");
	$_GET['c_rowstart'] = isset($_GET['c_rowstart']) && $_GET['c_rowstart'] <= $rows ? $_GET['c_rowstart'] : 0;
	$result = dbquery("SELECT c.*, u.user_id, u.user_name, u.user_status, u.user_avatar
	FROM ".DB_COMMENTS." c LEFT JOIN ".DB_USERS." u on u.user_id=c.comment_name
	ORDER BY comment_datestamp DESC LIMIT ".$_GET['c_rowstart'].", ".$settings['comments_per_page']."
	");
	$nav = '';
	if ($rows > $settings['comments_per_page']) {
		$nav = "<span class='pull-right text-smaller'>".makepagenav($_GET['c_rowstart'], $settings['comments_per_page'], $rows, 2)."</span>\n";
	}
	if (dbrows($result)>0) {
		$i = 0;

		add_to_jquery("
		$('.comment_content').hover(function() {
			$('#comment_action-'+$(this).data('id')).removeClass('display-none');
		},function(){
    		$('#comment_action-'+$(this).data('id')).addClass('display-none');
		});
		");
		while ($data = dbarray($result)) {
			echo "<!--Start Comment Item-->\n";
			echo "<div data-id='$i' class='comment_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
			echo "<div class='pull-left m-r-10 display-inline-block' style='margin-top:0px; margin-bottom:10px;'>".display_avatar($data, '40px')."</div>\n";
			echo "<div id='comment_action-$i' class='btn-group pull-right display-none' style='position:absolute; right: 30px; margin-top:10px;'>\n
			<a class='btn btn-xs btn-default' title='".$locale['274']."' href='".ADMIN."comments.php".$aidlink."&amp;ctype=".$data['comment_type']."&amp;cid=".$data['comment_item_id']."'><i class='entypo eye'></i></a>
			<a class='btn btn-xs btn-default' title='".$locale['275']."' href='".ADMIN."comments.php".$aidlink."&amp;action=edit&amp;comment_id=".$data['comment_id']."&amp;ctype=".$data['comment_type']."&amp;cid=".$data['comment_item_id']."'><i class='entypo pencil'></i></a>
			<a class='btn btn-xs btn-default' title='".$locale['276']."' href='".ADMIN."comments.php".$aidlink."&amp;action=delete&amp;comment_id=".$data['comment_id']."&amp;ctype=".$data['comment_type']."&amp;cid=".$data['comment_item_id']."'><i class='entypo trash'></i></a></div>\n";
			echo "<strong>".profile_link($data['user_id'], ucwords($data['user_name']), $data['user_status'])."</strong>\n";
			echo "<span class='text-smaller text-lighter'>".$locale['273']."</span> <a class='text-smaller' href='".sprintf($link[$data['comment_type']], $data['comment_item_id'])."'><strong>".$comments_type[$data['comment_type']]."</strong></a>";
			echo "&nbsp;<span class='text-smaller'>".timer($data['comment_datestamp'])."</span><br/>\n";
			echo "<span class='text-smaller text-lighter'>".trimlink(parseubb($data['comment_message']), 70)."</span>\n";
			echo "</div>\n";
			echo "<!--End Comment Item-->\n";
			$i++;
		}
		echo "<div class='clearfix'>\n";
		echo $nav;
		echo "</div>\n";
	} else {
		echo "<div class='text-center'>".$locale['254c']."</div>\n";
	}
	closeside();
	echo "</div>\n<div class='col-xs-12 co-sm-6 col-md-6 col-lg-4'>\n";
	// Ratings
	$rows = dbcount("('rating_id')", DB_RATINGS);
	openside("<span class='text-smaller text-uppercase'><strong>".$locale['278']."</strong></span>");
	$_GET['r_rowstart'] = isset($_GET['r_rowstart']) && $_GET['r_rowstart'] <= $rows ? $_GET['r_rowstart'] : 0;
	$result = dbquery("SELECT r.*, u.user_id, u.user_name, u.user_status, u.user_avatar
	FROM ".DB_RATINGS." r LEFT JOIN ".DB_USERS." u on u.user_id=r.rating_user
	ORDER BY rating_datestamp DESC LIMIT ".$_GET['r_rowstart'].", ".$settings['comments_per_page']."
	");
	$nav = '';
	if ($rows > $settings['comments_per_page']) {
		$nav = "<span class='pull-right text-smaller'>".makepagenav($_GET['r_rowstart'], $settings['comments_per_page'], $rows, 2)."</span>\n";
	}
	if (dbrows($result)>0) {
		$i = 0;
		while ($data = dbarray($result)) {
			echo "<!--Start Rating Item-->\n";
			echo "<div class='comment_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
			echo "<div class='pull-left m-r-10 display-inline-block' style='margin-top:0px; margin-bottom:10px;'>".display_avatar($data, '40px')."</div>\n";
			echo "<strong>".profile_link($data['user_id'], ucwords($data['user_name']), $data['user_status'])."</strong>\n";
			echo "<span class='text-smaller text-lighter'>".$locale['273a']."</span>\n";
			echo "<a class='text-smaller' href='".sprintf($link[$data['rating_type']], $data['rating_item_id'])."'><strong>".$comments_type[$data['rating_type']]."</strong></a>";
			echo "<span class='text-smaller text-lighter m-l-10'>".str_repeat("<i class='entypo star'></i>", $data['rating_vote'])."</span>\n";
			echo "&nbsp;<span class='text-smaller'>".timer($data['rating_datestamp'])."</span><br/>\n";
			echo "</div>\n";
			echo "<!--End Rating Item-->\n";
			$i++;
		}
		echo "<div class='clearfix'>\n";
		echo $nav;
		echo "</div>\n";
	} else {
		echo "<div class='text-center'>".$locale['254b']."</div>\n";
	}
	closeside();
	echo "</div>\n<div class='col-xs-12 co-sm-6 col-md-6 col-lg-4'>\n";
	// Submissions
	$rows = dbcount("('submit_id')", DB_SUBMISSIONS);
	openside("<span class='text-smaller text-uppercase'><strong>".$locale['279']."</strong></span><span class='pull-right label label-warning'>".number_format($rows)."</span>");
	$_GET['s_rowstart'] = isset($_GET['s_rowstart']) && $_GET['s_rowstart'] <= $rows ? $_GET['s_rowstart'] : 0;
	$result = dbquery("SELECT s.*, u.user_id, u.user_name, u.user_status, u.user_avatar
	FROM ".DB_SUBMISSIONS." s LEFT JOIN ".DB_USERS." u on u.user_id=s.submit_user
	ORDER BY submit_datestamp DESC LIMIT ".$_GET['s_rowstart'].", ".$settings['comments_per_page']."
	");
	$nav = '';
	if ($rows > $settings['comments_per_page']) {
		$nav = "<span class='pull-right text-smaller'>".makepagenav($_GET['s_rowstart'], $settings['comments_per_page'], $rows, 2)."</span>\n";
	}
	if (dbrows($result)>0) {
		$i = 0;
		add_to_jquery("
		$('.submission_content').hover(function() {
			$('#submission_action-'+$(this).data('id')).removeClass('display-none');
		},function(){
    		$('#submission_action-'+$(this).data('id')).addClass('display-none');
		});
		");

		//echo(checkrights("SU") ? "<a href='".ADMIN."submissions.php".$aidlink."#download_submissions'>".$locale['254']."</a>" : $locale['254']);
		//echo(checkrights("SU") ? "<a href='".ADMIN."submissions.php".$aidlink."#news_submissions'>".$locale['254']."</a>" : $locale['254']);
		// echo(checkrights("SU") ? "<a href='".ADMIN."submissions.php".$aidlink."#article_submissions'>".$locale['254']."</a>" : $locale['254']);
		// echo(checkrights("SU") ? "<a href='".ADMIN."submissions.php".$aidlink."#link_submissions'>".$locale['254']."</a>" : $locale['254']);
		// echo(checkrights("SU") ? "<a href='".ADMIN."submissions.php".$aidlink."#photo_submissions'>".$locale['254']."</a>" : $locale['254']);

		while ($data = dbarray($result)) {

			echo "<!--Start Submissions Item-->\n";
			echo "<div data-id='$i' class='submission_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
			echo "<div class='pull-left m-r-10 display-inline-block' style='margin-top:0px; margin-bottom:10px;'>".display_avatar($data, '40px')."</div>\n";
			echo "<div id='submission_action-$i' class='btn-group pull-right display-none' style='position:absolute; right: 30px; margin-top:10px;'>\n
			<a class='btn btn-xs btn-default' title='".$locale['274']."' href='".ADMIN."submissions.php".$aidlink."&amp;action=2&amp;t=".$data['submit_type']."&amp;submit_id=".$data['submit_id']."'><i class='entypo eye'></i></a>
			<a class='btn btn-xs btn-default' title='".$locale['276']."' href='".ADMIN."submissions.php".$aidlink."&amp;delete=".$data['submit_id']."'><i class='entypo trash'></i></a></div>\n";
			echo "<strong>".profile_link($data['user_id'], ucwords($data['user_name']), $data['user_status'])."</strong>\n";
			echo "<span class='text-smaller text-lighter'>".$locale['273b']." <strong>".$submit_type[$data['submit_type']]."</strong></span>";
			echo "&nbsp;<span class='text-smaller'>".timer($data['comment_datestamp'])."</span><br/>\n";
			echo "<span class='text-smaller text-lighter'>".trimlink(parseubb($data['comment_message']), 70)."</span>\n";
			echo "</div>\n";
			echo "<!--End Submissions Item-->\n";
			$i++;
		}
		echo "<div class='clearfix'>\n";
		echo $nav;
		echo "</div>\n";
	} else {
		echo "<div class='text-center'>".$locale['254a']."</div>\n";
	}
	closeside();

	echo "</div>\n";




	closetable();
} else {

	// Display admin panels & pages
	opentable($locale['200']." - v".$settings['version']);
	echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n<tr>\n";
	for ($i = 1; $i < 6; $i++) {
		$_GET['pagenum'] = ($_GET['pagenum'] == 0) ? 0 : $_GET['pagenum'];
		$class = ($_GET['pagenum'] == $i ? "tbl1" : "tbl2");
		if ($pages[$i]) {
			echo "<td align='center' width='20%' class='$class'><span class='small'>\n";
			echo ($_GET['pagenum'] == $i ? "<strong>".$locale['ac0'.$i]."</strong>" : "<a href='index.php".$aidlink."&amp;pagenum=$i'>".$locale['ac0'.$i]."</a>")."</span></td>\n";
		} else {
			echo "<td align='center' width='20%' class='$class'><span class='small' style='text-decoration:line-through'>\n";
			echo $locale['ac0'.$i]."</span></td>\n";
		}
	}
	echo "</tr>\n<tr>\n<td colspan='5' class='tbl'>\n";
	$result = dbquery("SELECT * FROM ".DB_ADMIN." WHERE admin_page='".$_GET['pagenum']."' ORDER BY admin_title");
	$rows = dbrows($result);
	if ($rows != 0) {
		$counter = 0;
		$columns = 4;
		$align = $admin_images ? "center" : "left";
		echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
		while ($data = dbarray($result)) {
			if (checkrights($data['admin_rights']) && $data['admin_link'] != "reserved") {
				if ($counter != 0 && ($counter%$columns == 0)) {
					echo "</tr>\n<tr>\n";
				}
				echo "<td align='$align' width='20%' class='tbl'>";
				if ($admin_images) {

					echo "<span class='small'><a href='".$data['admin_link'].$aidlink."'><img src='".get_image("ac_".$data['admin_title'])."' alt='".$data['admin_title']."' style='border:0px;' /></a><br />\n".$data['admin_title']."</span>";
				} else {
					echo "<span class='small'>".THEME_BULLET." <a href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a></span>";
				}
				echo "</td>\n";
				$counter++;
			}
		}
		echo "</tr>\n</table>\n";
	}
	echo "</td>\n</tr>\n</table>\n";
	closetable();
}



require_once THEMES."templates/footer.php";
?>
