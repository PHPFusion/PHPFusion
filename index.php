<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: index.php
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
require_once "maincore.php";
$settings = fusion_get_settings();

if ($settings['site_seo'] && !isset($_GET['aid'])) {
    define("IN_PERMALINK", TRUE);
    $router = PHPFusion\Rewrite\Router::getRouterInstance();
    $router->rewritePage();
    $filepath = $router->getFilePath();
    if (empty($filepath) && filter_var(PERMALINK_CURRENT_PATH, FILTER_VALIDATE_URL)) {
        redirect(PERMALINK_CURRENT_PATH, FALSE, FALSE, 301);
    } else {
        if (isset($_GET['lang']) && valid_language($_GET['lang'])) {
            $lang = stripinput($_GET['lang']);
            set_language($lang);
            $redirectPath = clean_request("", ["lang"], FALSE);
            redirect($redirectPath, FALSE, FALSE, 301);
        } else {
            if (isset($_GET['logout']) && $_GET['logout'] == "yes") {
                $userdata = Authenticate::logOut();
                redirect(BASEDIR.$settings['opening_page'], FALSE, FALSE, 301);
            } else {
                if (!empty($filepath)) {
                    if ($filepath == "index.php") {
                        redirect(BASEDIR.$settings['opening_page'], FALSE, FALSE, 301);
                    } else {
                        require_once $filepath;
                    }
                } else {
                    if ($_SERVER['REQUEST_URI'] == $settings['site_path'].$settings['opening_page']
                        or $_SERVER['REQUEST_URI'] == $settings['site_path']."index.php"
                        or $_SERVER['REQUEST_URI'] == $settings['site_path']
                    ) {
                        redirect(BASEDIR.$settings['opening_page'], FALSE, FALSE, 301);
                    } else {
                        $router->setPathtofile("error.php");
                        $params = [
                            "code" => "404",
                        ];
                        $router->setGetParameters($params);
                        $router->setservervars();
                        $router->setquerystring();
                        require_once BASEDIR."error.php";
                    }
                }
            }
        }
    }
} else {
    redirect(BASEDIR.$settings['opening_page'], FALSE, FALSE, 301);
}
