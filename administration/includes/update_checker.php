<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: update_checker.php
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
require_once __DIR__.'/../../maincore.php';

if (iADMIN) {
    $settings = fusion_get_settings();
    include LOCALE.LOCALESET."admin/main.php";

    if ($settings['update_checker'] == 1) {
        $url = 'https://raw.githubusercontent.com/PHPFusion/Archive/updates/8.txt';
        if (@get_http_response_code($url) == 200) {
            $file = fusion_get_contents($url);
            $array = explode("\n", $file);
            $version = $array[0];

            if (version_compare($version, $settings['version'], '>')) {
                $result = str_replace(['[LINK]', '[/LINK]', '[VERSION]'], ['<a href="'.$array[1].'" target="_blank">', '</a>', $version], $locale['new_update_avalaible']);

                header('Content-Type: application/json');
                echo json_encode(['result' => $result]);
            }
        }
    }
}
