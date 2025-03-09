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
require_once __DIR__.'/../../maincore.php';

if (iADMIN) {
    $endpoints = [
        'sitelinks-list'      => 'sitelinks/sitelinks-list.php',
        'sitelinks-order'     => 'sitelinks/sitelinks-order.php',
        'cache-update'        => 'cache_update.php',
        'error-logs-updater'  => 'error_logs_updater.php',
        'update-checker'      => 'update/update_checker.php',
        'update-core'         => 'update/update_core.php',
        'available-languages' => 'update/available_languages.php',
        'bbcodes-order'       => 'bbcodes-order.php',
    ];

    if ($api = get('api')) {
        if (isset($endpoints[$api])) {

            require __DIR__.DIRECTORY_SEPARATOR.$endpoints[$api];

            fusion_apply_hook('fusion_admin_hooks');

        } else {
            set_error(2, 'End point is faulty', debug_backtrace()[1]['file'], debug_backtrace()[1]['line']);
        }
    } else {
        set_error(2, 'API is not specified', debug_backtrace()[1]['file'], debug_backtrace()[1]['line']);
    }
} else {
    die('You are not authorized to view the data');
}
