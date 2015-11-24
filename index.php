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

    $use_new_version = TRUE;

    if ($use_new_version) {
        /**
         * More optimized, since Permalink and Router are sharing a same Driver bridge
         * Debug driver file change both Permalink and Router
         * Will only use this on public release post RC
         */
        $router = new PHPFusion\Rewrite\Router();
    } else {
        /**
         * Independent Router, same function might not have same codes.
         * i.e. ImportPatterns although same function name but inverts source-target data.
         * Files will be deleted on public release
         */
        $router = new PHPFusion\Rewrite\Old\Router();
    }

    $router->rewritePage();
    $filepath = $router->getFilePath();
    //var_dump($filepath);
    //print_p(PERMALINK_CURRENT_PATH);

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
                //redirect($settings['siteurl']."error.php?code=404");
            }
        }
    }
} else if (empty($settings['opening_page']) || $settings['opening_page'] == "index.php" || $settings['opening_page'] == "/") {
	redirect("home.php");
} else {
	redirect($settings['opening_page']);
}
