<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: htaccess_include.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

/**
 * Generate .htaccess file
 */
function write_htaccess() {
    $site_path = fusion_get_settings('site_path');
    if (empty($site_path)) {
        $site_path = '/';
    }

    $settings_seo = dbresult(dbquery("SELECT settings_value FROM ".DB_PREFIX."settings WHERE settings_name=:settings_name", [':settings_name' => 'site_seo']), 0);
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

    $htc .= "# Protect .htaccess file".PHP_EOL;
    $htc .= "<Files .htaccess>".PHP_EOL;
    $htc .= "    Require all denied".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;

    $htc .= "# Protect config.php".PHP_EOL;
    $htc .= "<Files config.php>".PHP_EOL;
    $htc .= "    Require all denied".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;

    $htc .= "# Protect fusion_error_log.log".PHP_EOL;
    $htc .= "<Files fusion_error_log.log>".PHP_EOL;
    $htc .= "    Require all denied".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;

    $htc .= "# Protect .cache files".PHP_EOL;
    $htc .= "<Files *.cache>".PHP_EOL;
    $htc .= "    Order allow,deny".PHP_EOL;
    $htc .= "    deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;

    $htc .= "<ifModule mod_headers.c>".PHP_EOL;
    $htc .= "    Header set X-Content-Type-Options \"nosniff\"".PHP_EOL;
    $htc .= "</ifModule>".PHP_EOL.PHP_EOL;

    // Error pages
    $htc .= "ErrorDocument 401 ".$site_path."error.php?code=401".PHP_EOL;
    $htc .= "ErrorDocument 403 ".$site_path."error.php?code=403".PHP_EOL;
    $htc .= "ErrorDocument 404 ".$site_path."error.php?code=404".PHP_EOL;

    if ($settings_seo == 1) {
        // Rewrite settings
        $htc .= "Options +SymLinksIfOwnerMatch".PHP_EOL;
        $htc .= "<ifModule mod_rewrite.c>".PHP_EOL;
        $htc .= "    # Let PHP know mod_rewrite is enabled".PHP_EOL;
        $htc .= "    <ifModule mod_env.c>".PHP_EOL;
        $htc .= "        SetEnv MOD_REWRITE On".PHP_EOL;
        $htc .= "    </ifModule>".PHP_EOL;
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
        $htc .= "</ifModule>".PHP_EOL;
    }

    // Extended feature for auto_file function
    //$htc .= "<ifModule mod_rewrite.c>".PHP_EOL;
    //$htc .= "    RewriteEngine on".PHP_EOL;
    //$htc .= "    RewriteRule ^(.*)\.[\d]{10}\.(css|js)$ $1.$2 [L]".PHP_EOL;
    //$htc .= "</ifModule>".PHP_EOL;

    write_file(BASEDIR.".htaccess", $htc);
}
