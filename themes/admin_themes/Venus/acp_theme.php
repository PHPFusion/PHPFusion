<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Venus/acp_theme.php
| Author: PHP-Fusion Inc.
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
require_once INCLUDES."theme_functions_include.php";
require_once ADMIN."navigation.php";
require_once THEMES."admin_themes/Venus/includes/functions.php";
$settings['bootstrap'] = 1;
add_to_footer("<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>");
function render_admin_login() {
	global $locale, $aidlink, $userdata, $defender;
	// TODO: Remove this, add the required styling to acp_styles.css
	add_to_head("<link rel='stylesheet' href='".THEMES."templates/setup_styles.css' type='text/css' />");
	echo "<aside class='block-container'>\n";
	echo "<div class='block'>\n";
	echo "<div class='block-content clearfix' style='font-size:13px;'>\n";
	echo "<h6><strong>".$locale['280']."</strong></h6>\n";
	echo "<img src='".IMAGES."php-fusion-icon.png' class='pf-logo position-absolute' alt='PHP-Fusion'/>";
	echo "<p class='fusion-version text-right mid-opacity text-smaller'>".$locale['version'].fusion_get_settings('version')."</p>";
	echo "<div class='row m-0'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>";
	$form_action = FUSION_SELF.$aidlink == ADMIN."index.php".$aidlink ? FUSION_SELF.$aidlink."&amp;pagenum=0" : FUSION_SELF."?".FUSION_QUERY;
	echo openform('admin-login-form', 'post', $form_action, array('max_tokens' => 1));
	openside('');
	if (!$defender->safe()) setNotice('danger', $locale['global_182']);
	// Get all notices
	$notices = getNotices();
	echo renderNotices($notices);
	echo "<div class='m-t-10 clearfix row'>\n";
	echo "<div class='col-xs-3 col-sm-3 col-md-3 col-lg-3'>\n";
	echo "<div class='pull-right'>\n";
	echo display_avatar($userdata, '90px');
	echo "</div>\n";
	echo "</div>\n<div class='col-xs-9 col-sm-9 col-md-8 col-lg-7'>\n";
	echo "<div class='clearfix'>\n";
	$label = "<span class='h5 display-inline' style='color: #222'><strong>".$locale['welcome'].", ".$userdata['user_name']."</strong><br/>".getuserlevel($userdata['user_level'])."</span>";
	add_to_head('<style>#admin_password-field .required {display:none}</style>');
	echo form_text('admin_password', $label, '', array(
		'callback_check' => 'check_admin_pass',
		'placeholder' => $locale['281'],
		'autocomplete_off' => 1,
		'type' => 'password',
		'required' => 1
	));
	echo "</div>\n";
	echo "</div>\n";
	echo "</div>\n";
	closeside();
	echo form_button('admin_login', $locale['login'], 'Sign in', array('class' => 'btn-primary btn-block'));
	echo closeform();
	echo "</div>\n</div>\n"; // .col-*, .row
	echo "</div>\n"; // .block-content
	echo "</div>\n"; // .block
	echo "<div class='copyright-note clearfix m-t-10'>".showcopyright()."</div>\n";
	echo "</aside>\n";
}

function render_admin_panel() {
	global $locale, $userdata, $defender, $pages, $aidlink, $admin;
	$languages = fusion_get_enabled_languages();
	//$enabled_languages = array_keys($languages); //remove it if it is not needed
	// Admin panel page
	echo "<div id='admin-panel' class='clearfix in'>\n";
	// Top header section
	echo "<section id='acp-header' class='pull-left affix clearfix' data-offset-top='0' data-offset-bottom='0'>\n";
	// Top left logo
	echo "<div class='brand'>\n";
	echo "<div class='pull-right'>\n";

	echo "</div>\n</div>\n";
	// Top navigation
	echo "<nav>\n";
	// Top side panel toggler
	echo "<ul class='venus-toggler'>\n";
	echo "<li><a id='toggle-canvas' class='pointer' style='border-left:none;'><i class='fa fa-bars fa-lg'></i></a></li>\n";
	echo "</ul>\n";
	echo $admin->horiziontal_admin_nav(true);
	// Top right menu links
	echo "<ul class='top-right-menu pull-right m-r-15'>\n";
	echo "<li class='dropdown'>\n";
	echo "<a class='dropdown-toggle pointer' data-toggle='dropdown'>".display_avatar($userdata, '25px', '', '', '')." ".$locale['logged']."<strong>".$userdata['user_name']."</strong> <span class='caret'></span>\n</a>\n";
	echo "<ul class='dropdown-menu' role='menu'>\n";
	echo "<li><a class='display-block' href='".BASEDIR."edit_profile.php'>".$locale['edit']." ".$locale['profile']."</a></li>\n";
	echo "<li><a class='display-block' href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'>".$locale['view']." ".$locale['profile']."</a></li>\n";
	echo "<li class='divider'> </li>\n";
	echo "<li><a class='display-block' href='".FUSION_REQUEST."&amp;logout'>".$locale['admin-logout']."</a></li>\n";
	echo "<li><a class='display-block' href='".BASEDIR."index.php?logout=yes'>".$locale['logout']."</a></li>\n";
	echo "</ul>\n";
	echo "</li>\n";
	echo "<li><a title='".$locale['settings']."' href='".ADMIN."settings_main.php".$aidlink."'><i class='fa fa-cog fa-lg'></i></a></li>\n";
	echo "<li><a title='".fusion_get_settings('sitename')."' href='".BASEDIR.fusion_get_settings("opening_page")."'><i class='fa fa-home fa-lg'></i></a>\n</li>\n";
	echo "<li><a title='".$locale['message']."' href='".BASEDIR."messages.php'><i class='fa fa-envelope-o fa-lg'></i></a>\n</li>\n";
	if (count($languages) > 1) {
        echo "<li class='dropdown'><a class='dropdown-toggle pointer' data-toggle='dropdown' title='".$locale['282']."'><i class='fa fa-globe fa-lg fa-fw'></i> ".translate_lang_names(LANGUAGE)."<span class='caret'></span></a>\n";
		echo "<ul class='dropdown-menu'>\n";
        foreach ($languages as $language_folder => $language_name) {
            echo "<li><a class='display-block' href='".clean_request("lang=".$language_folder, array("lang"),
                                                                     FALSE)."'><img class='m-r-5' src='".BASEDIR."locale/$language_folder/$language_folder-s.png'> $language_name</a></li>\n";
		}
		echo "</ul>\n";
		echo "</li>\n";
	}
	echo "</ul>\n"; // .top-right-menu
	echo "</nav>\n";
	echo "</section>\n";
	// Content section
	echo "<div class='content-wrapper display-table pull-left'>\n";
	// Left side panel
	echo "<div id='acp-left' class='pull-left affix' data-offset-top='0' data-offset-bottom='0'>\n"; // collapse to top menu on sm and xs
	echo "<div class='panel panel-default admin'><div class='panel-body clearfix'>\n";
	echo "<div class='pull-left m-r-10'>\n".display_avatar($userdata, '50px', '', '', '')."</div>\n";
	echo "<span class='overflow-hide m-t-10'><h4 class='m-b-0 text-stronger'>\n".$userdata['user_name']."</h4>\n".getuserlevel($userdata['user_level'])."</span></div>\n";
	echo "</div>\n";
	echo $admin->vertical_admin_nav();
	echo "</div>\n"; // #acp-left
	// Control panel content wrapper
	echo "<div id='acp-main' class='clearfix' style='vertical-align:top;'>\n";

	// Main content wrapper
	echo "<div id='acp-content' class='m-t-20 col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
	// Render breadcrumbs
	echo render_breadcrumbs();
	// Get and render notices
	$notices = getNotices();
	echo renderNotices($notices);
	// Render the content
	echo CONTENT;
	echo "</div>\n"; // #acp-content
	// Footer section
	echo "<footer class='m-l-20 display-inline-block m-t-20 m-b-20'>\n";
	// Copyright
	echo "Venus Admin &copy; ".date("Y")." created by <a href='https://www.php-fusion.co.uk'><strong>PHP-Fusion Inc.</strong></a>\n";
	echo showcopyright();
	// Render time
	if (fusion_get_settings('rendertime_enabled')) {
		echo "<br /><br />";
		// Make showing of queries and memory usage separate settings
		echo showrendertime();
		echo showMemoryUsage();
	}
	echo "</footer>\n";
	echo "</div>\n"; // .acp-main
	echo "</div>\n"; // .content-wrapper
	echo "</div>\n"; // #admin-panel

    add_to_footer("<script src='".THEMES."admin_themes/Venus/includes/jquery.slimscroll.min.js'></script>");
	if (!isset($_COOKIE['acp_sidemenu'])) {
		setcookie("acp_sidemenu", 1, 64800);
	}
	add_to_jquery("
	// Initialize slimscroll
	$('#adl').slimScroll({
		height: null
	});

	// Function to toggle side menu
	function toggleSideMenu(state) {
		var panel_state = null;
		if (state == 'show') {
			$('#admin-panel').addClass('in');
			var panel_state = 1;
		} else if (state == 'hide') {
			$('#admin-panel').removeClass('in');
			var panel_state = 0;
		} else {
			$('#admin-panel').toggleClass('in');
			var panel_state = $('#admin-panel').hasClass('in');
		}
		if (panel_state) {
			$.cookie('".COOKIE_PREFIX."acp_sidemenu', '1', {expires: 164800});
		} else {
			$.cookie('".COOKIE_PREFIX."acp_sidemenu', '0', {expires: 164800});
		}
	}

	// Adjust side menu height on page load, resize or orientation change
	$(window).on('load resize orientationchange', function(event) {
		var init_hgt = $(window).height();
		var small = $('.brand-text').is(':visible');
		var panel_height = (small ? init_hgt-125 : init_hgt-80);
		var hgt = $(this).height();
		$('#acp-left').css('height', hgt);
		$('.admin-vertical-link').css('height', panel_height);
		// Hide side menu on orientation change
		//if (event.type === 'orientationchange') {
		//	toggleSideMenu('show');
		//}
	});
	// Side menu toggler
	$('#toggle-canvas').on('click', toggleSideMenu);
	");
}