<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: index.php
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
require_once "maincore.php";
$settings = fusion_get_settings();
if ($settings['site_seo'] == "1") {
    define("IN_PERMALINK", TRUE);
    // Starting Rewrite Object
    $seo_rewrite = new PHPFusion\Rewrite();
    $seo_rewrite->rewritePage();
    $filepath = $seo_rewrite->getFilePath();
    if (!empty($filepath)) {
        require_once $filepath;
    } else {
        if ($_SERVER['REQUEST_URI'] == $settings['site_path'].$settings['opening_page']
            or $_SERVER['REQUEST_URI'] == $settings['site_path']."index.php"
            or $_SERVER['REQUEST_URI'] == $settings['site_path']
        ) {
            require_once $settings['opening_page'];
        } else {
            if (!$settings['debug_seo']) {
                redirect($settings['siteurl']."error.php?code=404");
            }
        }
    }
} else if (empty($settings['opening_page']) || $settings['opening_page'] == "index.php" || $settings['opening_page'] == "/") {
	redirect("home.php");
} else {
	redirect($settings['opening_page']);
}
