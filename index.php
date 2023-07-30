<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: index.php
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
require_once __DIR__ . '/maincore.php';
$settings = fusion_get_settings();

if ($settings['site_seo'] && !get( 'aid' )) {
    define( 'IN_PERMALINK', TRUE );
    $router = PHPFusion\Rewrite\Router::getRouterInstance();
    $router->rewritePage();
    $filepath = $router->getFilePath();
    if (empty( $filepath ) && filter_var( PERMALINK_CURRENT_PATH, FILTER_VALIDATE_URL )) {
        redirect( PERMALINK_CURRENT_PATH, FALSE, FALSE, 301 );
    } else {
        if (check_get( 'lang' ) && valid_language( get( 'lang' ) )) {
            $lang = stripinput( get( 'lang' ) );
            set_language( $lang );
            redirect( BASEDIR . $settings['opening_page'], FALSE, FALSE, 301 );
        } else {
            if (check_get( 'logout' ) && get( 'logout' ) == 'yes') {
                $userdata = Authenticate::logOut();
                redirect( BASEDIR . $settings['opening_page'], FALSE, FALSE, 301 );
            } else {
                if (!empty( $filepath )) {
                    if ($filepath == 'index.php') {
                        redirect( BASEDIR . $settings['opening_page'], FALSE, FALSE, 301 );
                    } else {
                        if (file_exists( $filepath )) {
                            require_once $filepath;
                        } else {
                            redirect( BASEDIR . 'index.php' );
                        }
                    }
                } else {
                    if (server( 'REQUEST_URI' ) == $settings['site_path'] . $settings['opening_page']
                        or server( 'REQUEST_URI' ) == $settings['site_path'] . 'index.php'
                        or $router->removeParam( server( 'REQUEST_URI' ) ) == '/'
                        or server( 'REQUEST_URI' ) == $settings['site_path']
                    ) {
                        if ($settings['opening_page'] == 'index.php') {
                            require_once THEMES . 'templates/header.php';
                            PHPFusion\HomePage::displayHome();
                            require_once THEMES . 'templates/footer.php';
                        } else {
                            redirect( BASEDIR . $settings['opening_page'], FALSE, FALSE, 301 );
                        }
                    } else {
                        $router->setPathtofile( 'error.php' );
                        $router->setGetParameters( ['code' => '404'] );
                        $router->setServerVars();
                        $router->setQueryString();
                        require_once BASEDIR . 'error.php';
                    }
                }
            }
        }
    }
} else {
    if ($settings['opening_page'] == 'index.php') {

        require_once THEMES . 'templates/header.php';

        PHPFusion\HomePage::displayHome();

        require_once THEMES . 'templates/footer.php';
    } else {
        redirect( BASEDIR . $settings['opening_page'], FALSE, FALSE, 301 );
    }
}
