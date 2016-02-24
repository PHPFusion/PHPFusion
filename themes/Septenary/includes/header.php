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
| Developer & Designer: Craig, Chan
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

$default_locale_file = THEME."locale/".LANGUAGE.".php";

if (file_exists($default_locale_file)) {
    include THEME."locale/".LANGUAGE.".php";
} else {
    include THEME."locale/English.php";
}


echo "<header id='top'>";
echo "<div class='overlay'>\n";
open_grid('section-1', 1);
echo "<div class='row hidden-xs'>\n";
echo "<div id='logo' class='hidden-xs hidden-md col-lg-3 p-t-5 text-smaller'>\n</div>\n";
echo "<div class='col-xs-12 col-md-9 col-lg-9 text-right clearfix'>\n";

echo "<div class='display-inline-block' style='width:30%; float:right;'>\n";
echo openform('searchform', 'post', BASEDIR.'search.php?stype=all',
              array('class' => 'm-b-10'));
echo form_text('stext', '', '', array(
    'placeholder' => $locale['sept_006'],
    'append_button' => TRUE,
    'append_type' => "submit",
    "append_form_value" => $locale['sept_006'],
    "append_value" => "<i class='fa fa-search'></i> ".$locale['sept_006'],
    "append_button_name" => "search",
    'class' =>'no-border m-b-0',
));
echo closeform();
echo "</div>\n";

echo "<ul id='head_nav' class='display-inline-block'>\n";

$language_opts = '';
if (count(fusion_get_enabled_languages()) > 1) {
    $languages = fusion_get_enabled_languages();
    $language_opts = "<li class='dropdown'>\n";
    $language_opts .= "<a class='dropdown-toggle pointer' data-toggle='dropdown' title='".$locale['UM101']."'><i class='fa fa-globe fa-lg'></i> ".translate_lang_names(LANGUAGE)." <span class='caret'></span></a>\n";
    $language_opts .= "<ul class='dropdown-menu' role='menu'>\n";

    $language_switch = fusion_get_language_switch();

    if (!empty($language_switch)) {
        $row = 0;
        foreach ($language_switch as $folder => $langData) {
            $language_opts .= "<li class='text-left'><a href='".$langData['language_link']."'>\n";
            $language_opts .= "<img alt='".$langData['language_name']."' class='m-r-5' src='".$langData['language_icon_s']."'/>\n";
            $language_opts .= $langData['language_name'];
            $language_opts .= "</a></li>\n";
        }
    }
    $language_opts .= "</ul>\n";
    $language_opts .= "</li>\n";
}



if (!iMEMBER) {
    echo "<li><a href='".BASEDIR."login.php'>".$locale['sept_001']."</a></li>\n";
    if (fusion_get_settings("enable_registration")) {
        echo "<li><a href='".BASEDIR."register.php'>".$locale['sept_002']."</a></li>\n";
    }
    echo $language_opts;
} else {
    if (iADMIN) {
        echo "<li><a href='".ADMIN.$aidlink."&amp;pagenum=0'>".$locale['sept_003']."</a></li>\n";
    }
    echo "<li><a href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'>".$locale['sept_004']."</a></li>\n";
    echo $language_opts;
    echo "<li><a href='".BASEDIR."index.php?logout=yes'>".$locale['sept_005']."</a></li>\n";
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
	echo "<div class='text-center logo'>\n";
	if ($settings['sitebanner']) {
		echo "<a href='".BASEDIR."'><img class='img-responsive' src='".BASEDIR.$settings['sitebanner']."' alt='".$settings['sitename']."' style='border: 0;' /></a>\n";
	} else {
		echo "<a href='".BASEDIR."'>".$settings['sitename']."</a>\n";
	}
	echo "</div>\n";
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
			echo "<a href='".INFUSIONS."news/news.php'>\n";
			echo "<i class='entypo pencil'></i>\n";
			echo "<h4>".$locale['sept_007']."</h4>";
			echo "</a>\n";
			echo "</div>\n";
		}
		if ($modules[DB_PHOTO_ALBUMS]) {
			echo "<div class='$sizeClasses section-2-tab text-center'>\n";
			echo "<a href='".INFUSIONS."gallery/gallery.php'>\n";
			echo "<i class='entypo camera'></i>\n";
			echo "<h4>".$locale['sept_008']."</h4>";
			echo "</a>\n";
			echo "</div>\n";
		}
		if ($modules[DB_FORUMS]) {
			echo "<div class='$sizeClasses section-2-tab text-center'>\n";
			echo "<a href='".INFUSIONS."forum/index.php'>\n";
			echo "<i class='entypo icomment'></i>\n";
			echo "<h4>".$locale['sept_009']."</h4>";
			echo "</a>\n";
			echo "</div>\n";
		}
		if ($modules[DB_DOWNLOADS]) {
			echo "<div class='$sizeClasses section-2-tab text-center'>\n";
			echo "<a href='".INFUSIONS."downloads/downloads.php'>\n";
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

