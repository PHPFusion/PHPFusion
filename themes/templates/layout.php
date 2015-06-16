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
echo "<!DOCTYPE html>\n";
echo "<head>\n";
	echo "<title>".$settings['sitename']."</title>\n";
	echo "<meta charset='".$locale['charset']."' />\n";
	echo "<meta name='description' content='".$settings['description']."' />\n";
	echo "<meta name='keywords' content='".$settings['keywords']."' />\n";
	$bootstrap_theme_css_src = '';
	if ($bootstrap_theme_css_src) {
		echo "<meta http-equiv='X-UA-Compatible' content='IE=edge' />\n";
	  	echo "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
		echo "<link href='".$bootstrap_theme_css_src."' rel='stylesheet' media='screen' />\n";
	}

	echo "<link rel='stylesheet' href='".INCLUDES."font/font-awesome/css/font-awesome.min.css' type='text/css' />\n";

	// Load bootstrap stylesheets
	if ($settings['bootstrap']) {
		define('BOOTSTRAPPED', TRUE);
		// ok now there is a theme at play here.
		// at maincore, lets load atom.
		$theme_name = isset($userdata['user_theme']) && $userdata['user_theme'] !== 'Default' ? $userdata['user_theme'] : fusion_get_settings('theme');
		$theme_data = dbarray(dbquery("SELECT theme_file FROM ".DB_THEME." WHERE theme_name='".$theme_name."' AND theme_active='1'"));
		$theme_css = INCLUDES.'bootstrap/bootstrap.min.css';
		if (!empty($theme_data)) {
			$theme_css = THEMES.$theme_data['theme_file'];
		}
		echo "<link rel='stylesheet' href='".$theme_css."' type='text/css' />\n";
		echo "<link rel='stylesheet' href='".INCLUDES."jquery/smartmenus/jquery.smartmenus.bootstrap.css' type='text/css' />\n";
	} else {
		echo "<link rel='stylesheet' href='".INCLUDES."jquery/smartmenus/sm-core-css.css' type='text/css' />\n";
		echo "<link rel='stylesheet' href='".INCLUDES."jquery/smartmenus/sm-simple.css' type='text/css' />\n";
	}

	// Entypo icons
	//echo "<link href='".INCLUDES."font/entypo/entypo.css' rel='stylesheet' media='screen' />\n";
	// Default CSS styling which applies to all themes but can be overriden
	echo "<link href='".THEMES."templates/default.css' rel='stylesheet' type='text/css' media='screen' />\n";
	// Theme CSS
	echo "<link href='".THEME."styles.css' rel='stylesheet' type='text/css' media='screen' />\n";

	echo render_favicons(IMAGES);
	if (function_exists("get_head_tags")) {
		echo get_head_tags();
	}
	echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
	echo "<script type='text/javascript' src='".INCLUDES."jscript.js'></script>\n";
echo "</head>\n";
echo "<body>\n";
	if (iADMIN) {
		if (iSUPERADMIN && file_exists(BASEDIR."install/")) addNotice("danger", $locale['global_198'], 'all');
		if ($settings['maintenance']) addNotice("warning", $locale['global_190'], 'all');
		if (!$userdata['user_admin_password']) addNotice("warning", $locale['global_199'], 'all');
	}
	render_page();
	// Output lines added with add_to_footer()
	echo $fusion_page_footer_tags;

	if ($footerError) {
		echo "<div class='admin-message'>".$footerError."</div>";
	}

	// Output lines added with add_to_jquery()
	if (!empty($fusion_jquery_tags)) {
		$fusion_jquery_tags = \PHPFusion\Minifier::minify($fusion_jquery_tags, array('flaggedComments' => false));
		echo "<script type='text/javascript'>
		$(function() { $fusion_jquery_tags; });
		</script>\n";
	}

	// Load bootstrap javascript
	if ($settings['bootstrap']) {
		echo "<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>\n";
		echo "<script type='text/javascript' src='".INCLUDES."bootstrap/holder.js'></script>\n";
		echo "<script type='text/javascript' src='".INCLUDES."jquery/smartmenus/jquery.smartmenus.bootstrap.min.js'></script>\n";
	} else {
		echo "<script type='text/javascript'>
			$(function() {
				$('#main-menu').smartmenus({
					subMenusSubOffsetX: 1,
					subMenusSubOffsetY: -8
				});
			});
			</script>\n";
	}
	echo "<script type='text/javascript' src='".INCLUDES."jquery/smartmenus/jquery.smartmenus.min.js'></script>\n";
	echo "<script src='".INCLUDES."jscripts/html-inspector.js'></script>\n<script> HTMLInspector.inspect() </script>\n";

echo "</body>";
echo "</html>";
