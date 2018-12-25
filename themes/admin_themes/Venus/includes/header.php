<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: header.php
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
echo "<section id='acp-header' class='clearfix'>\n";
echo "<div class='brand pull-left'>\n";
echo "<img src='".IMAGES."php-fusion-icon.png' style='width:100px;'>\n";
echo "<h4 class='brand-text'>PHP-Fusion ".$settings['version']."</h4>\n";
echo "</div>\n";
echo "<nav>\n";
echo "<ul class='venus-toggler'>\n";
echo "<li><a id='toggle-canvas' style='border-left:none;'><i class='entypo code'></i></a></li>\n";
echo "</ul>\n";
echo admin_nav(); 	// Can also use in other implementations based on the admin class, echo \PHPFusion\Admins::getInstance()->horizontal_admin_nav(TRUE); 

add_to_jquery("
$('#toggle-canvas').bind('click', function(e) {
	$('#acp-left').toggleClass('in');
	setTimeout(function() {
		$('#acp-main').toggleClass('in');
		$('#admin-panel').toggleClass('in');
	}, 30);
	panel_state = $('#acp-left').hasClass('in');
	if (panel_state) {
		$.cookie('Venus', '1', {expires: 7});
	} else {
		$.cookie('Venus', '0', {expires: 7});
	 }
});
");

echo "<ul class='hidden-xs pull-right m-r-15'>\n";
$languages = fusion_get_enabled_languages();
if (sizeof($enabled_languages) > 1) {
	echo "<li class='dropdown'>";
		echo "<a class='dropdown-toggle pointer' data-toggle='dropdown' title='".$locale['282']."'><i class='entypo flag'></i> <span class='hidden-xs hidden-sm hidden-md'>".translate_lang_names(LANGUAGE)."</span><span class='caret'></span></a>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach ($languages as $language_folder => $language_name) {
			echo "<li><a class='display-block' href='".clean_request("lang=".$language_folder, array("lang"), FALSE)."'><img class='m-r-5' src='".BASEDIR."locale/$language_folder/$language_folder-s.png'> $language_name</a></li>\n";
		}
		echo "</ul>\n";
	echo "</li>\n";
}


echo "<li><a title='".$locale['view']." ".$settings['sitename']."' href='".BASEDIR."'><i class='entypo home'></i></a></li>\n";
echo "<li><a title='".$locale['message']."' href='".BASEDIR."messages.php'><i class='entypo mail'></i></a></li>\n";
echo "<li><a title='".$locale['settings']."' href='".ADMIN."settings_main.php".$aidlink."'><i class='entypo cog'></i></a></li>\n";
echo "<li class='dropdown'><a class='dropdown-toggle' data-toggle='dropdown'>".$locale['logged'].$userdata['user_name']." <span class='caret'></span></a>\n";
echo "<ul class='dropdown-menu' role='menu'>\n";
echo "<li style='width:100%;'><a class='display-block' style='width:100%' href='".BASEDIR."edit_profile.php'>".$locale['edit']." ".$locale['profile']."</a></li>\n";
echo "<li style='width:100%;'><a class='display-block' style='width:100%' href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'>".$locale['view']." ".$locale['profile']."</a></li>\n";
echo "<li class='divider' class='display-block' style='width:100%'>\n</li>\n";
echo "<li><a class='display-block' href='".FUSION_SELF."?admin_logout'>".$locale['admin-logout']."</a></li>\n";
echo "<li style='width:100%;'><a class='display-block' style='width:100%' href='".BASEDIR."index.php?logout=yes'>".$locale['logout']."</a></li>\n";
echo "</ul>\n";
echo "</li>\n";
echo "</ul>\n";
echo "</nav>\n";
echo "</section>\n";
?>