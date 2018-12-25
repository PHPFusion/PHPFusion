<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin_layout.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
include LOCALE.LOCALESET."admin/main.php";
header("Content-Type: text/html; charset=".$locale['charset']."");

echo "<!DOCTYPE html>";
echo "<html lang='".$locale['xml_lang']."' dir='".$locale['text-direction']."'>";
echo "<head>";
echo "<title>".$settings['sitename']."</title>";
echo "<meta charset='".$locale['charset']."'/>";
echo "<meta name='robots' content='none'/>";
echo "<meta name='googlebot' content='noarchive'/>";

if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
    echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'/>\n";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'/>\n";
    echo "<link href='".INCLUDES."bootstrap/bootstrap.min.css' rel='stylesheet' media='screen'/>";

    if ($locale['text-direction'] == 'rtl') {
        echo "<link href='".INCLUDES."bootstrap/bootstrap-rtl.min.css' rel='stylesheet' media='screen'/>";
    }
}

// Global CSS, Resets etc.
if (!defined('NO_GLOBAL_CSS')) {
	echo "<link rel='stylesheet' href='".THEMES."templates/global.css' type='text/css' media='screen' />\n";
}

// Default CSS styling which applies to all themes but can be overriden
if (!defined('NO_DEFAULT_CSS')) {
    echo "<link rel='stylesheet' href='".THEMES."templates/default.css' type='text/css' media='screen' />\n";
}

// Site Theme CSS
if (!defined('NO_THEME_CSS')) {
	echo "<link rel='stylesheet' href='".THEME."styles.css' type='text/css' media='screen' />\n";
}

// Entypo
if ($settings['entypo'] || defined('ENTYPO')) {
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.css' type='text/css' />\n";
}

// Font Awesome 4
if (defined('FONTAWESOME-V4')) {
    if ($settings['fontawesome'] || defined('FONTAWESOME')) {
        echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome/css/font-awesome.min.css' type='text/css'/>\n";
    }
}

// Font Awesome 5
if (!defined('FONTAWESOME-V4')) {
    if ($settings['fontawesome'] || defined('FONTAWESOME')) {
        echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/all.min.css' type='text/css'/>\n";
        echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/v4-shims.min.css' type='text/css'/>\n";
    }
}

// Admin Panel Theme CSS
$admin_theme_css = file_exists(THEMES.'admin_themes/'.$settings['admin_theme'].'/acp_styles.min.css') ? THEMES.'admin_themes/'.$settings['admin_theme'].'/acp_styles.min.css' : THEMES.'admin_themes/'.$settings['admin_theme'].'/acp_styles.css';
echo "<link href='".$admin_theme_css."' rel='stylesheet' type='text/css' media='screen'/>\n";

echo render_favicons(defined('THEME_ICON') ? THEME_ICON : IMAGES.'favicons/');

if (function_exists("get_head_tags")) {
    echo get_head_tags();
}

echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.min.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jscripts/jscript.js'></script>\n";

if ($settings['tinymce_enabled'] == 1) {
		
	echo "<style type='text/css'>.mceIframeContainer iframe{width:100%!important;background-color: #00000;}</style>\n";
	echo "<script language='javascript' type='text/javascript' src='".INCLUDES."jscripts/tiny_mce/tinymce.min.js'></script>\n
	<script type='text/javascript'>
	
	function advanced() {
		tinymce.init({
			selector: 'textarea',
			resize: 'both',
			height: 300,
			theme: 'modern',
			branding: false,
			language:'".$locale['tinymce']."',
			plugins: [
				'advlist autolink lists link image charmap print preview hr anchor pagebreak',
				'searchreplace wordcount visualblocks visualchars code fullscreen',
				'insertdatetime media nonbreaking save table contextmenu directionality',
				'emoticons template paste textcolor colorpicker textpattern imagetools codesample toc help importcss'
			],
			toolbar1: 'undo redo | styleselect formatselect fontselect fontsizeselect removeformat',
			toolbar2: 'cut copy paste | bold underline italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
			toolbar3: 'link unlink anchor | hr | image media | forecolor backcolor charmap emoticons | codesample | code | preview fullpage | fullscreen',
			menubar: 'edit insert view format table',
			file_browser_callback: RoxyFileBrowser,
			image_advtab: true,
			relative_urls : false,
			remove_script_host : false,
			document_base_url : '".$settings['siteurl']."',
			content_css: [
				'".(file_exists(THEME."editor.css") ? 
					$settings['siteurl']."themes/".$settings['theme']."/editor.css":
					$settings['siteurl']."themes/".$settings['theme']."/styles.css"
				   )."',
			],
			content_style: 'body.mceDefBody {background:#".(IsSet($settings['tinymce_bgcolor']) ? $settings['tinymce_bgcolor'] : "FFFFFF").";}',
			body_class: 'mceDefBody'
		});
	}

	function simple() {
		tinymce.init({
			selector: 'textarea',
			height: 200,
			menubar: false,
			branding: false,
			plugins: [
				'advlist autolink lists link image charmap print preview anchor',
				'searchreplace visualblocks code fullscreen',
				'insertdatetime media table contextmenu paste code'
			],
			toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
			content_css: [
				'".(file_exists(THEME."editor.css") ? 
					$settings['siteurl']."themes/".$settings['theme']."/editor.css":
					$settings['siteurl']."themes/".$settings['theme']."/styles.css"
				   )."',
			],
			content_style: 'body.mceDefBody {background:#".(IsSet($settings['tinymce_bgcolor']) ? $settings['tinymce_bgcolor'] : "FFFFFF").";}',
			body_class: 'mceDefBody'
		});
	}

	function toggleEditor(id) {
		if (!tinyMCE.get(id))
			tinyMCE.execCommand('mceAddControl', false, id);
		else
			tinyMCE.execCommand('mceRemoveControl', false, id);
	}

	function RoxyFileBrowser(field_name, url, type, win) {
	  var roxyFileman = '".INCLUDES."filemanager_mce/index.php';
	  if (roxyFileman.indexOf(\"?\") < 0) {     
		roxyFileman += \"?type=\" + type;   
	  }
	  else {
		roxyFileman += \"&type=\" + type;
	  }
	  roxyFileman += '&input=' + field_name + '&value=' + win.document.getElementById(field_name).value;
	  if(tinyMCE.activeEditor.settings.language){
		roxyFileman += '&langCode=' + tinyMCE.activeEditor.settings.language;
	  }
	  tinyMCE.activeEditor.windowManager.open({
		 file: roxyFileman,
		 title: 'File Manager',
		 width: 850, 
		 height: 550,
		 resizable: \"yes\",
		 plugins: \"media\",
		 inline: \"yes\",
		 close_previous: \"no\"  
	  }, {     window: win,     input: field_name    });
	  return false; 
	}
	</script>\n";
}
echo "</head>";

/**
 * Constant - THEME_BODY;
 * replace <body> tags with your own theme definition body tags. Some body tags require additional params
 * for the theme purposes.
 */

if (!defined("THEME_BODY")) {
    echo "<body>\n";
} else {
    echo THEME_BODY;
}

// Check login, skip infusions.
if (!check_admin_pass('') && !stristr($_SERVER['PHP_SELF'], $settings['site_path']."infusions")) {
		if (empty($userdata['user_admin_password'])) {
        redirect(BASEDIR."edit_profile.php");
    } else {
        render_admin_login();
    }
} else {
    render_admin_panel();
}

// Load Bootstrap javascript
if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
    echo "<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>\n";
}

echo "<script type='text/javascript' src='".INCLUDES."jquery/admin-scripts.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jquery/holder/holder.min.js'></script>\n";

// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;

// Output lines added with add_to_jquery()
if (!empty($fusion_jquery_tags)) {
	echo push_jquery(); // output jquery.
}

// Uncomment to guide your theme development
//echo "<script src='".INCLUDES."jscripts/html-inspector.js'></script>\n<script> HTMLInspector.inspect() </script>\n";
echo "</body>\n";
echo "</html>";
