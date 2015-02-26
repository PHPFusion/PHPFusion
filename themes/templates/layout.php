<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: layout.php
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
echo "
<!DOCTYPE html>
<head>
	<title>".$settings['sitename']."</title>
	<meta charset='".$locale['charset']."' />
	<meta name='description' content='".$settings['description']."' />
	<meta name='keywords' content='".$settings['keywords']."' />";
	if ($bootstrap_theme_css_src) {
	echo "<meta http-equiv='X-UA-Compatible' content='IE=edge' />
	  	  <meta name='viewport' content='width=device-width, initial-scale=1.0' />
		  <link href='".$bootstrap_theme_css_src."' rel='stylesheet' media='screen' />";
	 }
	echo "
	<!-- Entypo icons -->
	<link href='".INCLUDES."font/entypo/entypo.css' rel='stylesheet' media='screen' />
	<!-- Default CSS styling which applies to all themes but can be overriden -->
	<link href='".THEMES."templates/default.css' rel='stylesheet' type='text/css' media='screen' />
	<!-- Theme CSS -->
	<link href='".THEME."styles.css' rel='stylesheet' type='text/css' media='screen' />";
	echo render_favicons(IMAGES);
	if (function_exists("get_head_tags")) {
		echo get_head_tags();
	}
	echo "
	<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>
	<script type='text/javascript' src='".INCLUDES."jscript.js'></script>
</head>
<body>";
	
	render_page();
	// Output lines added with add_to_footer()
	echo $fusion_page_footer_tags;

	if ($footerError) {
		echo "<div class='admin-message'>".$footerError."</div>";
	}

	if (!empty($fusion_jquery_tags)) {
		echo "<script type='text/javascript'>
			$(function() {
				$fusion_jquery_tags; // Output lines added with add_to_jquery()
			});
			</script>";
}
echo "</body></html>";
?>