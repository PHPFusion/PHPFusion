<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin_header.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
define("ADMIN_PANEL", TRUE);
require_once INCLUDES."output_handling_include.php";
require_once INCLUDES."header_includes.php";
if ($settings['maintenance'] == "1" && !iADMIN) {
	redirect(BASEDIR."maintenance.php");
} else {
	if (file_exists(THEMES."admin_templates/".$settings['admin_theme']."/acp_theme.php") && preg_match("/^([a-z0-9_-]){2,50}$/i", $settings['admin_theme'])) {
		require_once THEMES."admin_templates/".$settings['admin_theme']."/acp_theme.php";
	}
}
if (iMEMBER) {
	$result = dbquery("UPDATE ".DB_USERS." SET user_lastvisit='".time()."', user_ip='".USER_IP."', user_ip_type='".USER_IP_TYPE."' WHERE user_id='".$userdata['user_id']."'");
}
echo "<!DOCTYPE html>\n";
echo "<head>\n<title>".$settings['sitename']."</title>\n";
echo "<meta http-equiv='Content-Type' content='text/html; charset=".$locale['charset']."' />\n";
echo "<link rel='stylesheet' href='".THEMES."admin_templates/".$settings['admin_theme']."/acp_styles.css' type='text/css' media='screen' />\n";
if (file_exists(IMAGES."favicon.ico")) {
	echo "<link rel='shortcut icon' href='".IMAGES."favicon.ico' type='image/x-icon' />\n";
}
if (function_exists("get_head_tags")) {
	echo get_head_tags();
}
echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jscript.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jquery/admin-msg.js'></script>\n";
if ($settings['tinymce_enabled'] == 1) {
	$tinymce_list = array();
	$image_list = makefilelist(IMAGES, ".|..|");
	$image_filter = array('png', 'PNG', 'bmp', 'BMP', 'jpg', 'JPG', 'jpeg', 'gif', 'GIF', 'tiff', 'TIFF');
	foreach($image_list as $image_name) {
		$image_1 = explode('.', $image_name);
		$last_str = count($image_1)-1;
		if (in_array($image_1[$last_str], $image_filter)) {
			$tinymce_list[] = array('title'=>$image_name, 'value'=> IMAGES.$image_name);
		}
	}
	$tinymce_list = json_encode($tinymce_list);

	echo "<style type='text/css'>.mceIframeContainer iframe{width:100%!important;}</style>\n";
	echo "<script language='javascript' type='text/javascript' src='".INCLUDES."jscripts/tinymce/tinymce.min.js'></script>\n
	<script type='text/javascript'>
	function advanced() {
	tinymce.init({
    selector: 'textarea',
    theme: 'modern',
    width: '100%',
    height: 300,
    plugins: [
		'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
		'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
		'save table contextmenu directionality emoticons template paste textcolor'
	],
	image_list: $tinymce_list,
   content_css: '".THEME."styles.css',
   toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons',
   style_formats: [
        {title: 'Bold text', inline: 'b'},
        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
        {title: 'Example 1', inline: 'span', classes: 'example1'},
        {title: 'Example 2', inline: 'span', classes: 'example2'},
        {title: 'Table styles'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
    ]
 });
}

function simple() {
tinymce.init({
    selector: 'textarea',
    theme: 'modern',
    language:'".$locale['tinymce']."'
 });
}

function toggleEditor(id) {
	if (!tinyMCE.get(id))
		tinyMCE.execCommand('mceAddControl', false, id);
	else
		tinyMCE.execCommand('mceRemoveControl', false, id);
}
</script>\n";
}
echo "</head>\n<body>\n";
require_once THEMES."templates/panels.php";
ob_start();
?>