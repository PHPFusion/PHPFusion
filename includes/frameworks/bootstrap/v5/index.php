<?php
/*
 * -------------------------------------------------------+
 * | PHPFusion Content Management System
 * | Copyright (C) PHP Fusion Inc
 * | https://phpfusion.com/
 * +--------------------------------------------------------+
 * | Filename: index.php
 * | Author:  meangczac (Chan)
 * +--------------------------------------------------------+
 * | This program is released as free software under the
 * | Affero GPL license. You can redistribute it and/or
 * | modify it under the terms of this license which you
 * | can read by viewing the included agpl.txt or online
 * | at www.gnu.org/licenses/agpl.html. Removal of this
 * | copyright header is strictly prohibited without
 * | written permission from the original author(s).
 * +--------------------------------------------------------
 */

/**
 * Bootstrap v5 header hooks
 * @param string $custom_file
 */
function bootstrap_header(string $context = 'site') {

    echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';

    if (bootstrap_framework_variant() === 'tabler') {
        echo '<link rel="stylesheet" href="' . INCLUDES . 'frameworks/bootstrap/tabler/tabler.min.css">';
        echo '<link rel="stylesheet" href="' . INCLUDES . 'frameworks/bootstrap/tabler/icons-webfont/tabler-icons.min.css">';
        echo '<link rel="stylesheet" href="' . INCLUDES . 'frameworks/bootstrap/tabler/tabler-adapter.css">';
        return;
    }

    echo '<link rel="stylesheet" href="' . INCLUDES . 'frameworks/bootstrap/v5/css/bootstrap.min.css">';
//    if (file_exists( $custom_file )) {
//        echo '<link rel="stylesheet" href="' . $custom_file . '">';
//    } else {
//        echo '<link rel="stylesheet" href="' . INCLUDES . 'frameworks/bootstrap/v3/css/bootstrap-submenu.min.css" defer>';
//    }
    if (fusion_get_locale( 'text-direction' ) === 'rtl') {
        echo '<link rel="stylesheet" href="' . INCLUDES . 'frameworks/bootstrap/v5/css/bootstrap.rtl.min.css">';
    }
}

/**
 * Bootstrap v5 footer hooks
 */
function bootstrap_footer(string $context = 'site') {
    echo '<script src="' . INCLUDES . 'frameworks/bootstrap/v5/js/dynamics.min.js" defer></script>';

    if (bootstrap_framework_variant() === 'tabler') {
        echo '<script src="' . INCLUDES . 'frameworks/bootstrap/tabler/tabler.min.js" defer></script>';
        echo '<script src="' . INCLUDES . 'frameworks/bootstrap/tabler/tabler-adapter.js" defer></script>';
        return;
    }

    echo '<script src="' . INCLUDES . 'frameworks/bootstrap/v5/js/popper.min.js" defer></script>';
    echo '<script src="' . INCLUDES . 'frameworks/bootstrap/v5/js/bootstrap.bundle.min.js" defer></script>';
//    echo '<script src="' . INCLUDES . 'frameworks/bootstrap/v5/js/bootstrap-submenu.min.js" defer></script>';
}
