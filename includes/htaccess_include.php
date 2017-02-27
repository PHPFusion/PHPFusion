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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

function write_htaccess() {
    $site_path = fusion_get_settings('site_path');
    $settings_seo = dbresult(dbquery("SELECT settings_value FROM ".DB_PREFIX."settings WHERE settings_name=:settings_name", [':settings_name' => 'site_seo']), 0);
    if (!file_exists(BASEDIR.'.htaccess')) {
        if (file_exists(BASEDIR."_htaccess") && function_exists("rename")) {
            @rename(BASEDIR."_htaccess", ".htaccess");
        } else {
            touch(BASEDIR.".htaccess");
        }
    }

    $htc = "# Force utf-8 charset".PHP_EOL;
    $htc .= "AddDefaultCharset utf-8".PHP_EOL.PHP_EOL;

    $htc .= "# Security".PHP_EOL;
    $htc .= "ServerSignature Off".PHP_EOL.PHP_EOL;

    $htc .= "# Secure htaccess file".PHP_EOL;
    $htc .= "<Files .htaccess>".PHP_EOL;
    $htc .= "   order allow,deny".PHP_EOL;
    $htc .= "   deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;

    $htc .= "# Secure inc file".PHP_EOL;
    $htc .= "<Files *.inc>".PHP_EOL;
    $htc .= "   Order allow,deny".PHP_EOL;
    $htc .= "   deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;

    $htc .= "# Protect config.php".PHP_EOL;
    $htc .= "<Files config.php>".PHP_EOL;
    $htc .= "   order allow,deny".PHP_EOL;
    $htc .= "   deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;

    $htc .= "#Cache images for 7 days to soften network load".PHP_EOL;
    $htc .= "<IfModule mod_headers.c>".PHP_EOL;
    $htc .= '<FilesMatch "\.(ico|pdf|flv|jpg|jpeg|png|gif|js|css|swf)$">'.PHP_EOL;
    $htc .= '   Header set Cache-Control "max-age=290304000, public"'.PHP_EOL;
    $htc .= "</FilesMatch>".PHP_EOL;
    $htc .= "</IfModule>".PHP_EOL.PHP_EOL;

    $htc .= "# Block Nasty Bots".PHP_EOL;
    $htc .= "<IfModule mod_setenvifno.c>".PHP_EOL;
    $htc .= "	SetEnvIfNoCase ^User-Agent$ .*(craftbot|download|extract|stripper|sucker|ninja|clshttp|webspider|leacher|collector|grabber|webpictures) HTTP_SAFE_BADBOT".PHP_EOL;
    $htc .= "	SetEnvIfNoCase ^User-Agent$ .*(libwww-perl|aesop_com_spiderman) HTTP_SAFE_BADBOT".PHP_EOL;
    $htc .= "   SetEnvIfNoCase ^User-Agent$ .*(almaden|Anarchie|ASPSeek|attach|autoemailspider|BackWeb|Bandit|BatchFTP|BlackWidow|Bot\ mailto:craftbot@yahoo.com|Buddy|bumblebee|CherryPicker|ChinaClaw|CICC|Collector|Copier|Crescent|Custo|DA|DIIbot|DISCo|DISCo\ Pump|Download\ Demon|Download\ Wonder|Downloader|Drip|DSurf15a|eCatch|EasyDL/2.99|EirGrabber|EmailCollector|EmailSiphon|EmailWolf|Express\ WebPictures|ExtractorPro|EyeNetIE|FileHound|FlashGet|GetRight|GetSmart|GetWeb!|gigabaz|Go\!Zilla|Go!Zilla|Go-Ahead-Got-It|gotit|Grabber|GrabNet|Grafula|grub-client|HMView|HTTrack|httpdown|ia_archiver|Image\ Stripper|Image\ Sucker|Indy*Library|InterGET|InternetLinkagent|Internet\ Ninja|InternetSeer.com|Iria|JBH*agent|JetCar|JOC\ Web\ Spider|JustView|larbin|LeechFTP|LexiBot|lftp|Link*Sleuth|likse|Link|LinkWalker|Mag-Net|Magnet|Mass\ Downloader|Memo|Microsoft.URL|MIDown\ tool|Mirror|Mister\ PiX|Mozilla.*Indy|Mozilla.*NEWT|Mozilla*MSIECrawler|MS\ FrontPage*|MSFrontPage|MSIECrawler|MSProxy|Navroad|NearSite|NetAnts|NetMechanic|NetSpider|Net\ Vampire|NetZIP|NICErsPRO|Ninja|Octopus|Offline\ Explorer|Offline\ Navigator|Openfind|PageGrabber|Papa\ Foto|pavuk|pcBrowser|Ping|PingALink|Pockey|psbot|Pump|QRVA|RealDownload|Reaper|Recorder|ReGet|Scooter|Seeker|Siphon|sitecheck.internetseer.com|SiteSnagger|SlySearch|SmartDownload|Snake|SpaceBison|sproose|Stripper|Sucker|SuperBot|SuperHTTP|Surfbot|Szukacz|tAkeOut|Teleport\ Pro|URLSpiderPro|Vacuum|VoidEYE|Web\ Image\ Collector|Web\ Sucker|WebAuto|[Ww]eb[Bb]andit|webcollage|WebCopier|Web\ Downloader|WebEMailExtrac.*|WebFetch|WebGo\ IS|WebHook|WebLeacher|WebMiner|WebMirror|WebReaper|WebSauger|Website|Website\ eXtractor|Website\ Quester|Webster|WebStripper|ebWhacker|WebZIP|Wget|Whacker|Widow|WWWOFFLE|x-Tractor|Xaldon\ WebSpider|Xenu|Zeus.*Webster|Zeus) HTTP_SAFE_BADBOT".PHP_EOL;
    $htc .= "	Deny from env=HTTP_SAFE_BADBOT".PHP_EOL;
    $htc .= "</IfModule>".PHP_EOL.PHP_EOL;

    $htc .= "# Disable directory listing".PHP_EOL;
    $htc .= "Options -Indexes".PHP_EOL.PHP_EOL;

    // This force image to not be able to be used as other matter
    $htc .= "<FilesMatch \"(?i).jpe?g$\">".PHP_EOL;
    $htc .= "   ForceType image/jpeg".PHP_EOL;
    $htc .= "</FilesMatch>".PHP_EOL;
    $htc .= "<FilesMatch \"(?i).gif$\">".PHP_EOL;
    $htc .= "   ForceType image/gif".PHP_EOL;
    $htc .= "</FilesMatch>".PHP_EOL;
    $htc .= "<FilesMatch \"(?i).png$\">".PHP_EOL;
    $htc .= "   ForceType image/png".PHP_EOL;
    $htc .= "</FilesMatch>".PHP_EOL.PHP_EOL;

    if ($settings_seo == 1) {
        // Rewrite settings
        $htc .= "Options +SymLinksIfOwnerMatch".PHP_EOL;
        $htc .= "<IfModule mod_rewrite.c>".PHP_EOL;
        $htc .= "	# Let PHP know mod_rewrite is enabled".PHP_EOL;
        $htc .= "	<IfModule mod_env.c>".PHP_EOL;
        $htc .= "		SetEnv MOD_REWRITE On".PHP_EOL;
        $htc .= "	</IfModule>".PHP_EOL;
        $htc .= "	RewriteEngine On".PHP_EOL;
        $htc .= "	RewriteBase ".$site_path.PHP_EOL;
        $htc .= "	# Fix Apache internal dummy connections from breaking [(site_url)] cache".PHP_EOL;
        $htc .= "	RewriteCond %{HTTP_USER_AGENT} ^.*internal\ dummy\ connection.*$ [NC]".PHP_EOL;
        $htc .= "	RewriteRule .* - [F,L]".PHP_EOL;
        $htc .= "	# Exclude /assets and /manager directories and images from rewrite rules".PHP_EOL;
        $htc .= "	RewriteRule ^(administration|themes)/*$ - [L]".PHP_EOL;
        $htc .= "	RewriteCond %{REQUEST_FILENAME} !-f".PHP_EOL;
        $htc .= "	RewriteCond %{REQUEST_FILENAME} !-d".PHP_EOL;
        $htc .= "	RewriteCond %{REQUEST_FILENAME} !-l".PHP_EOL;
        $htc .= "	RewriteCond %{REQUEST_URI} !^/(administration|config|index.php)".PHP_EOL;
        $htc .= "	RewriteRule ^(.*?)$ index.php [L]".PHP_EOL;
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
}