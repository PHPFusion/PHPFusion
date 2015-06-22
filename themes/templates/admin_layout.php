<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin_layout.php
| Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
header("Content-Type: text/html; charset=".$locale['charset']."");

echo "<!DOCTYPE html><head>";
echo "<title>".$settings['sitename']."</title>";
echo "<meta charset='".$locale['charset']."' />";

if ($bootstrap_theme_css_src) {
	echo "<meta http-equiv='X-UA-Compatible' content='IE=edge' />";
	echo "<meta name='viewport' content='width=device-width, initial-scale=1.0' />";
	echo "<link href='".$bootstrap_theme_css_src."' rel='stylesheet' media='screen' />";
}
if ($settings['entypo']) {
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-codes.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-embedded.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-ie7.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-ie7-codes.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/animation.css' type='text/css' />\n";
}

if ($settings['fontawesome']) {
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome/css/font-awesome.min.css' type='text/css' />\n";
}

// Default CSS styling which applies to all themes but can be overriden
echo "<link href='".THEMES."templates/default.css' rel='stylesheet' type='text/css' media='screen' />";

// Admin Panel Theme CSS
echo "<link href='".THEMES."admin_templates/".$settings['admin_theme']."/acp_styles.css' rel='stylesheet' type='text/css' media='screen' />";

// jQuery related includes
echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>";
echo "<script type='text/javascript' src='".INCLUDES."jscript.js'></script>";

echo render_favicons(IMAGES);

if (function_exists("get_head_tags")) {
	echo get_head_tags();
}

if (fusion_get_settings('tinymce_enabled')) { 
echo "<style type='text/css'>
.mceIframeContainer iframe {
	width: 100% !important;
}
</style>";

echo "<script type='text/javascript' src='".INCLUDES."jscripts/tinymce/tinymce.min.js'></script>";
echo "<script type='text/javascript'>
function advanced() {
	tinymce.init({
		selector: 'textarea',
		theme: 'modern',
		entity_encoding: 'raw',
		language: '".$locale['tinymce']."',
		width: '100%',
		height: 300,
		plugins: [
			'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
			'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
			'save table contextmenu directionality emoticons template paste textcolor'
		],
		image_list: ".$tinymce_list.",
		document_base_url: '".fusion_get_settings('site_path')."',
		content_css: '".THEME."styles.css',
		toolbar1: 'insertfile undo redo | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | newdocument fullscreen preview cut copy paste pastetext spellchecker searchreplace code',
		toolbar2: 'styleselect formatselect removeformat | fontselect fontsizeselect bold italic underline strikethrough subscript superscript blockquote | forecolor backcolor',
		toolbar3: 'hr pagebreak insertdatetime | link unlink anchor | image media | table charmap visualchars visualblocks emoticons',
		image_advtab: true,
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
		entity_encoding: 'raw',
		relative_urls: false,
		language: '".$locale['tinymce']."'
	});
}

function toggleEditor(id) {
	if (!tinyMCE.get(id)) {
		tinyMCE.execCommand('mceAddControl', false, id);
	} else {
		tinyMCE.execCommand('mceRemoveControl', false, id);
	}
}
</script>";
}

echo "</head><body>";

// Check if the user is logged in
if (!check_admin_pass('')) {
	render_admin_login();
	} else {
	render_admin_panel();
}

if ($footerError) {
	echo "<div class='alert alert-warning m-t-10 error-message'>".$footerError."</div>";
}

echo "<script type='text/javascript' src='".INCLUDES."jquery/admin-msg.js'></script>\n";
echo "<script src='".INCLUDES."jscripts/html-inspector.js'></script>\n<script> HTMLInspector.inspect() </script>\n";

// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;

// Output lines added with add_to_jquery()
if (!empty($fusion_jquery_tags)) {
	$fusion_jquery_tags = \PHPFusion\Minifier::minify($fusion_jquery_tags, array('flaggedComments' => false));
	echo "<script type='text/javascript'>
		$(function() { $fusion_jquery_tags; });
		</script>\n";
}
echo "</body></html>";
