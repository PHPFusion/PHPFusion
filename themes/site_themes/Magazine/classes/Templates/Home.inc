<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: Magazine/classes/Templates/Home.inc
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Magazine\Templates;

use Magazine\Core;

class Home extends Core {
    public static function displayHome($info) {
        $twig = twig_init(THEME.'twig');

        foreach ($info as $db_id => $content) {
            if (!empty($content['data'])) {
                foreach ($content['data'] as $id => $data) {
                    $info[$db_id]['data'][$id]['content'] = nl2br(trim_text(strip_tags($data['content']), 200));
                }
            }
        }

        echo $twig->render('bootstrap3/home.twig', ['info' => $info]);
    }
}
