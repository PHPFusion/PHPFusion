<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: htaccess_include.php
| Author: Core Development Team (coredevs@phpfusion.com)
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

function write_htaccess() {
    global $settings;

    $seo_settings = [];
    $result = dbquery("SELECT settings_name, settings_value FROM ".DB_SETTINGS." WHERE settings_name='site_seo'");
    while ($data = dbarray($result)) {
        $seo_settings[$data['settings_name']] = $data['settings_value'];
    }

    $site_path = $settings['site_path'];
    if (empty($site_path)) {
        $site_path = '/';
    }

    if (!file_exists(BASEDIR.'.htaccess')) {
        if (file_exists(BASEDIR."_htaccess") && function_exists("rename")) {
            @rename(BASEDIR."_htaccess", ".htaccess");
        } else {
            touch(BASEDIR.".htaccess");
        }
    }

    $htc = "# Disable directory listing".PHP_EOL;
    $htc .= "Options -Indexes".PHP_EOL.PHP_EOL;

    $htc .= "# Force utf-8 charset".PHP_EOL;
    $htc .= "AddDefaultCharset UTF-8".PHP_EOL;
    $htc .= 'AddCharset UTF-8 .html .css .js .svg .woff .woff2'.PHP_EOL.PHP_EOL;

    $htc .= "# Security".PHP_EOL;
    $htc .= "ServerSignature Off".PHP_EOL.PHP_EOL;

    $htc .= "# Secure .htaccess file".PHP_EOL;
    $htc .= "<Files .htaccess>".PHP_EOL;
    $htc .= "    order allow,deny".PHP_EOL;
    $htc .= "    deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;

    $htc .= "# Protect config.php".PHP_EOL;
    $htc .= "<Files config.php>".PHP_EOL;
    $htc .= "    order allow,deny".PHP_EOL;
    $htc .= "    deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;

    if ($seo_settings['site_seo'] == "1") {
        // Rewrite settings
        $htc .= "Options +SymLinksIfOwnerMatch".PHP_EOL;
        $htc .= "<IfModule mod_rewrite.c>".PHP_EOL;
        $htc .= "    # Let PHP know mod_rewrite is enabled".PHP_EOL;
        $htc .= "    <IfModule mod_env.c>".PHP_EOL;
        $htc .= "        SetEnv MOD_REWRITE On".PHP_EOL;
        $htc .= "    </IfModule>".PHP_EOL;
        $htc .= "    RewriteEngine On".PHP_EOL;
        $htc .= "    RewriteBase ".$site_path.PHP_EOL;
        $htc .= "    # Fix Apache internal dummy connections from breaking [(site_url)] cache".PHP_EOL;
        $htc .= "    RewriteCond %{HTTP_USER_AGENT} ^.*internal\ dummy\ connection.*$ [NC]".PHP_EOL;
        $htc .= "    RewriteRule .* - [F,L]".PHP_EOL;
        $htc .= "    # Exclude /administration and /themes directories and images from rewrite rules".PHP_EOL;
        $htc .= "    RewriteRule ^(administration|themes)/*$ - [L]".PHP_EOL;
        $htc .= "    RewriteCond %{REQUEST_FILENAME} !-f".PHP_EOL;
        $htc .= "    RewriteCond %{REQUEST_FILENAME} !-d".PHP_EOL;
        $htc .= "    RewriteCond %{REQUEST_FILENAME} !-l".PHP_EOL;
        $htc .= "    RewriteCond %{REQUEST_URI} !^/(administration|config|index.php)".PHP_EOL;
        $htc .= "    RewriteRule ^(.*?)$ index.php [L]".PHP_EOL;
        $htc .= "</IfModule>".PHP_EOL;
    } else {
        // Error pages
        $htc .= "ErrorDocument 400 ".$site_path."error.php?code=400".PHP_EOL;
        $htc .= "ErrorDocument 401 ".$site_path."error.php?code=401".PHP_EOL;
        $htc .= "ErrorDocument 403 ".$site_path."error.php?code=403".PHP_EOL;
        $htc .= "ErrorDocument 404 ".$site_path."error.php?code=404".PHP_EOL;
        $htc .= "ErrorDocument 500 ".$site_path."error.php?code=500".PHP_EOL;
    }

    write_file(BASEDIR.".htaccess", $htc);

    unset($seo_settings);
}
