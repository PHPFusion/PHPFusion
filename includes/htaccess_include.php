<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: htaccess_include.php
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
defined('IN_FUSION') || exit;

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

    $htc .= "# Secure .htaccess file".PHP_EOL;
    $htc .= "<Files .htaccess>".PHP_EOL;
    $htc .= "    order allow,deny".PHP_EOL;
    $htc .= "    deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;

    $htc .= "# Secure .inc files".PHP_EOL;
    $htc .= "<Files *.inc>".PHP_EOL;
    $htc .= "    Order allow,deny".PHP_EOL;
    $htc .= "    deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;

    $htc .= "# Protect config.php".PHP_EOL;
    $htc .= "<Files config.php>".PHP_EOL;
    $htc .= "    order allow,deny".PHP_EOL;
    $htc .= "    deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;

    // Error pages
    $htc .= "ErrorDocument 400 ".$site_path."error.php?code=400".PHP_EOL;
    $htc .= "ErrorDocument 401 ".$site_path."error.php?code=401".PHP_EOL;
    $htc .= "ErrorDocument 403 ".$site_path."error.php?code=403".PHP_EOL;
    $htc .= "ErrorDocument 404 ".$site_path."error.php?code=404".PHP_EOL;
    $htc .= "ErrorDocument 500 ".$site_path."error.php?code=500".PHP_EOL.PHP_EOL;

    $htc .= "# Cache images for 7 days to soften network load".PHP_EOL;
    $htc .= "<ifModule mod_headers.c>".PHP_EOL;
    $htc .= '    <filesMatch "\\.(ico|pdf|flv|jpg|jpeg|png|gif|swf|ttf|otf|woff|woff2|eot|svg)$">'.PHP_EOL;
    $htc .= '        Header append Vary: Accept-Encoding'.PHP_EOL;
    $htc .= '        Header set Cache-Control "max-age=2592000, public"'.PHP_EOL;
    $htc .= "    </filesMatch>".PHP_EOL;
    $htc .= '    <filesMatch "\\.(css|js)$">'.PHP_EOL;
    $htc .= '        Header set Cache-Control "max-age=604800, public"'.PHP_EOL;
    $htc .= "    </filesMatch>".PHP_EOL;
    $htc .= '    <filesMatch "\\.(html|htm|php)$">'.PHP_EOL;
    $htc .= '        Header set Cache-Control "max-age=1, private, must-revalidate"'.PHP_EOL;
    $htc .= "    </filesMatch>".PHP_EOL;
    $htc .= "</ifModule>".PHP_EOL.PHP_EOL;

    $htc .= '# Compress files'.PHP_EOL;
    $htc .= '<ifModule mod_deflate.c>'.PHP_EOL;
    $htc .= '    <filesMatch "\.(jpg|jpeg|png|gif|ico|svg|css|js|json|x?html?|php)$">'.PHP_EOL;
    $htc .= '        SetOutputFilter DEFLATE'.PHP_EOL;
    $htc .= '    </filesMatch>'.PHP_EOL;
    $htc .= '</ifModule>'.PHP_EOL.PHP_EOL;

    $htc .= "# This force image to not be able to be used as other matter".PHP_EOL;
    $htc .= "<filesMatch \"(?i).jpe?g$\">".PHP_EOL;
    $htc .= "    ForceType image/jpeg".PHP_EOL;
    $htc .= "</filesMatch>".PHP_EOL;
    $htc .= "<filesMatch \"(?i).gif$\">".PHP_EOL;
    $htc .= "    ForceType image/gif".PHP_EOL;
    $htc .= "</filesMatch>".PHP_EOL;
    $htc .= "<filesMatch \"(?i).png$\">".PHP_EOL;
    $htc .= "    ForceType image/png".PHP_EOL;
    $htc .= "</filesMatch>".PHP_EOL.PHP_EOL;

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

    write_file(BASEDIR.".htaccess", $htc);
}
