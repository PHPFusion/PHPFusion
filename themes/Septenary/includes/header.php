<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: header.php
| Version: 1.00
| Author: PHP-Fusion Mods UK, PHP-Fusion Development Team.
| Developer & Designer: Craig, Hien
| Site: http://www.phpfusionmods.co.uk
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
echo "<header id='top' style='background-image:url(".THEME."images/bg_main.jpg)'>";
echo "<div class='overlay'>\n";
open_grid('section-1', 1);
echo "<div class='row hidden-xs'>\n";
echo "<div id='logo' class='col-xs-12 col-md-3 col-lg-3 p-t-5 text-smaller'>\n";
echo "&nbsp;";
echo "</div>\n";
echo "<div class='col-xs-9 col-md-9 col-lg-9 text-right clearfix p-t-5'>\n";
echo openform('searchform', 'post', $settings['site_seo'] ? FUSION_ROOT : ''.BASEDIR.'search.php?stype=all', array('max_tokens' => 1, 'class'=>'display-inline-block pull-right m-r-10', 'notice'=>0));
echo form_text('stext', '', '', array('append_button' => 1, 'placeholder' => $locale['sept_006'], 'class' =>'no-border m-r-20', 'width'=>'250px'));
echo closeform();
echo "<ul id='head_nav' class='display-inline-block m-t-5 m-r-10 pull-right'>\n";
$language_opts = '';
if (count(fusion_get_enabled_languages()) > 1) {
	$language_opts = "<li class='dropdown pull-left'><a class='dropdown-toggle pointer' data-toggle='dropdown' title='".$locale['UM101']."'><i class='fa fa-flag fa-lg'></i><span class='caret'></span></a>\n";
	$language_opts .= "<ul class='dropdown-menu' role='lang-menu'>\n";
	foreach(fusion_get_enabled_languages() as $languages) {
	$link_prefix = clean_request('lang='.$languages, array('lang'), false, '&amp;');
	$language_opts .= "<li class='display-block text-left'><a class='display-block' style='width:100%' href='".$link_prefix."'> <img alt='".$languages."' class='m-r-5' src='".BASEDIR."locale/$languages/$languages-s.png'> $languages</a></li>\n";
		
	}
	$language_opts .= "</ul>\n";
	$language_opts .= "</li>\n";
}

if (!iMEMBER) {
	echo "<li class='pull-left m-r-10'><a href='".BASEDIR."login.php'>".$locale['sept_001']."</a></li>\n";
	echo "<li class='pull-left m-r-10 m-l-10'><a href='".BASEDIR."register.php'>".$locale['sept_002']."</a></li>\n";
	echo $language_opts;
} else {
	if (iADMIN) {
		echo "<li class='pull-left m-r-10'><a href='".ADMIN.$aidlink."&amp;pagenum=0'>".$locale['sept_003']."</a></li>\n";
	}
	echo "<li class='pull-left m-r-10'><a href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'>".$locale['sept_004']."</a></li>\n";
	echo $language_opts;
	echo "<li class='pull-left'><a href='".BASEDIR."index.php?logout=yes'>".$locale['sept_005']."</a></li>\n";
}
echo "</ul>\n";
echo "</div>\n";
echo "</div>\n";
close_grid(1);
open_grid('section-2', 1);
echo "<div class='header-nav'>\n";
echo showsublinks('')."\n";
echo "</div>\n";

close_grid();
echo "</div>\n";
open_grid('section-showcase', 1);
if ($settings['opening_page'] == FUSION_SELF) {
	echo "<div class='text-center logo'> ".showbanners()." </div>\n";
	echo "<h2 class='text-center text-uppercase' style='letter-spacing:10px; font-weight:300; font-size:36px;'>".$settings['sitename']."</h2>\n";
	echo "<div class='text-center' style='font-size:19.5px; line-height:35px; font-weight:300; color:rgba(255,255,255,0.8'>".stripslashes($settings['siteintro'])."</div>\n";
	$modules = array(
		DB_NEWS => db_exists(DB_NEWS),
		DB_PHOTO_ALBUMS => db_exists(DB_PHOTO_ALBUMS),
		DB_FORUMS => db_exists(DB_FORUMS),
		DB_DOWNLOADS => db_exists(DB_DOWNLOADS)
	);
	$sum = array_sum($modules);
	if ($sum) {
		$size = 12 / $sum;
		$sizeClasses = 'col-sm-'.$size.' col-md-'.$size.' col-lg-'.$size;
		echo "<div class='section-2-row row'>\n";
		if ($modules[DB_NEWS]) {
			echo "<div class='$sizeClasses section-2-tab text-center'>\n";
			echo "<a href='".BASEDIR."news.php'>\n";
			echo "<i class='entypo pencil'></i>\n";
			echo "<h4>".$locale['sept_007']."</h4>";
			echo "</a>\n";
			echo "</div>\n";
		}
		if ($modules[DB_PHOTO_ALBUMS]) {
			echo "<div class='$sizeClasses section-2-tab text-center'>\n";
			echo "<a href='".BASEDIR."photogallery.php'>\n";
			echo "<i class='entypo camera'></i>\n";
			echo "<h4>".$locale['sept_008']."</h4>";
			echo "</a>\n";
			echo "</div>\n";
		}
		if ($modules[DB_FORUMS]) {
			echo "<div class='$sizeClasses section-2-tab text-center'>\n";
			echo "<a href='".BASEDIR."forum/index.php'>\n";
			echo "<i class='entypo icomment'></i>\n";
			echo "<h4>".$locale['sept_009']."</h4>";
			echo "</a>\n";
			echo "</div>\n";
		}
		if ($modules[DB_DOWNLOADS]) {
			echo "<div class='$sizeClasses section-2-tab text-center'>\n";
			echo "<a href='".BASEDIR."downloads.php'>\n";
			echo "<i class='entypo window'></i>\n";
			echo "<h4>".$locale['sept_010']."</h4>";
			echo "</a>\n";
			echo "</div>\n";
		}
		echo "</div>\n";
	}
} else {
	// use SQL search for page title.
	$result = dbquery("SELECT link_name FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")."  link_url='".FUSION_SELF."'");
	if (dbrows($result) > 0) {
		$data = dbarray($result);
		$link_name = $data['link_name'];
	} else {
		$link_name = $settings['sitename'];
	}
	add_to_head('<style>.heading h2 { display:none !important; } .footer {margin-top:0px;} .section-showcase { height:150px; }</style>');
	echo "<h2 class='text-center text-uppercase' style='letter-spacing:10px; font-weight:300; font-size:36px;'>".$link_name."</h2>\n";
}
if (FUSION_SELF == 'login.php') {
	/* Custom Overrides CSS just for login */
	add_to_head('<style>.heading h2 { display:none !important; } .footer {margin-top:0px;} .section-showcase { height:594px; }</style>');
	echo CONTENT;
}
close_grid(1);
echo "</div>\n"; // .overlay
echo "</header>\n";
?>
