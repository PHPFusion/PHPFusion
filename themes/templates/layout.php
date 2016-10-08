<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
header("Content-Type: text/html; charset=".fusion_get_locale('charset')."");
echo "<!DOCTYPE html>\n";
echo "<html lang='".fusion_get_locale('xml_lang')."'>\n";
echo "<head>\n";
echo "<title>".fusion_get_settings("sitename")."</title>\n";
echo "<meta charset='".fusion_get_locale('charset')."' />\n";
echo "<meta property='og:title' content='".fusion_get_settings("sitename")."' />\n";
echo "<meta name='description' content='".fusion_get_settings("description")."' />\n";
echo "<meta property='og:description' name='description' content='".fusion_get_settings("description")."' />\n";
echo "<meta property='og:url' content='".fusion_get_settings("siteurl")."' />\n";
echo "<meta name='url' content='".fusion_get_settings("siteurl")."' />\n";
echo "<meta property='og:keywords' content='".fusion_get_settings("keywords")."' />\n";
echo "<meta name='keywords' content='".fusion_get_settings("keywords")."' />\n";
echo "<meta property='og:image' content='".fusion_get_settings("sitebanner")."' />\n";
echo "<meta name='image' content='".fusion_get_settings("sitebanner")."' />\n";
echo "<meta http-equiv='Cache-control' content='public'/>\n";
echo "<meta http-equiv='expires' content='".gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60))."'/>\n";
if (fusion_get_settings("bootstrap") == TRUE) {
    echo "<meta http-equiv='X-UA-Compatible' content='IE=edge' />\n";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
}

if (fusion_get_settings("entypo")) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.css' type='text/css' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-codes.css' type='text/css' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-embedded.css' type='text/css' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-ie7.css' type='text/css' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-ie7-codes.css' type='text/css' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/animation.css' type='text/css' />\n";
}

if (fusion_get_settings("fontawesome")) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome/css/font-awesome.min.css' type='text/css' />\n";
}

// Load bootstrap stylesheets
if (fusion_get_settings("bootstrap")) {
    define('BOOTSTRAPPED', TRUE);
    echo "<link rel='stylesheet' href='".INCLUDES."bootstrap/bootstrap.min.css' type='text/css' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."bootstrap/bootstrap-submenu.min.css' type='text/css' />\n";

    $user_theme = fusion_get_userdata("user_theme");
    $theme_name = $user_theme !== 'Default' ? $user_theme : fusion_get_settings('theme');
    $theme_data = dbarray(dbquery("SELECT theme_file FROM ".DB_THEME." WHERE theme_name='".$theme_name."' AND theme_active='1'"));
    if (!empty($theme_data)) {
        $theme_css = THEMES.$theme_data['theme_file'];
        echo "<link href='".$theme_css."' rel='stylesheet' type='text/css' />\n";
    }
}
if (!defined('NO_DEFAULT_CSS')) {
    echo "<link href='".THEMES."templates/default.css' rel='stylesheet' type='text/css' media='screen' />\n";
}
echo "<link href='".THEME."styles.css' rel='stylesheet' type='text/css' media='screen' />\n";
echo render_favicons(IMAGES);
if (function_exists("get_head_tags")) {
    echo get_head_tags();
}
echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jscripts/jscript.min.js'></script>\n";
echo "</head>\n";

// Online users database -- to core level whether panel is on or not
if (dbcount("(online_user)", DB_ONLINE,
        (iMEMBER ? "online_user='".fusion_get_userdata("user_id")."'" : "online_user='0' AND online_ip='".USER_IP."'")) == 1
) {
    $result = dbquery("UPDATE ".DB_ONLINE." SET online_lastactive='".time()."', online_ip='".USER_IP."'
		WHERE ".(iMEMBER ? "online_user='".fusion_get_userdata("user_id")."'" : "online_user='0' AND online_ip='".USER_IP."'"));
} else {
    $result = dbquery("INSERT INTO ".DB_ONLINE." (online_user, online_ip, online_ip_type, online_lastactive)
		VALUES ('".(iMEMBER ? fusion_get_userdata("user_id") : 0)."', '".USER_IP."', '".USER_IP_TYPE."', '".time()."')");
}
$result = dbquery("DELETE FROM ".DB_ONLINE." WHERE online_lastactive<".(time() - 60)."");


/**
 * new constant - THEME_BODY;
 * replace <body> tags with your own theme definition body tags. Some body tags require additional params
 * for the theme purposes.
 */
if (!defined("THEME_BODY")) {
    echo "<body>\n";
} else {
    echo THEME_BODY;
}

if (iADMIN) {
    if (iSUPERADMIN && file_exists(BASEDIR.'install.php')) {
        addNotice("danger", fusion_get_locale("global_198"), 'all');
    }
    if (fusion_get_settings("maintenance")) {
        addNotice("warning", fusion_get_locale("global_190"), 'all');
    }
    if (!fusion_get_userdata('user_admin_password')) {
        addNotice("warning", str_replace(array("[LINK]", "[/LINK]"), array("<a href='edit_profile.php'>", "</a>"), fusion_get_locale("global_199")), 'all');
    }
}

if (function_exists("render_page")) {
    render_page(); // by here, header and footer already closed
}

// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;
if (!empty($footerError)) {
    echo "<div class='admin-message container'>".$footerError."</div>\n";
}

// Output lines added with add_to_jquery()
$jquery_tags = "$('[data-submenu]').submenupicker();";
if (!empty($fusion_jquery_tags)) {
    $jquery_tags .= $fusion_jquery_tags;
}
$jquery_tags = \PHPFusion\Minifier::minify($jquery_tags, array('flaggedComments' => FALSE));
echo "<script type='text/javascript'>$(function() { $jquery_tags });</script>\n";

// Load bootstrap javascript
if (fusion_get_settings("bootstrap")) {
    echo "<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>\n";
    echo "<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap-submenu.min.js'></script>\n";
    echo "<script type='text/javascript' src='".INCLUDES."bootstrap/holder.min.js'></script>\n";
}

//Uncomment to guide your theme development
//echo "<script src='".INCLUDES."jscripts/html-inspector.js'></script>\n<script> HTMLInspector.inspect() </script>\n";

echo "</body>\n";
echo "</html>\n";
