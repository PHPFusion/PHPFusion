<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: latest_articles_panel/templates.php
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

if (!function_exists('render_articles')) {
    function render_articles($info) {

        $html = \PHPFusion\Template::getInstance('render_articles');
        $html->set_template(INFUSIONS."latest_articles_panel/templates/latest_articles.html");
        $html->set_tag('openside', fusion_get_function('openside', $info['openside']));
        $html->set_tag('closeside', fusion_get_function('closeside'));
        if (!empty($info['item'])) {
                foreach ($info['item'] as $cdatm) {
                    $html->set_block('articles', [
                        'link_url'    => $cdatm['link_url'],
                        'link_title'  => "<div id='text_id' data-articles-text='21'>".$cdatm['link_title']."</div>",
                        'user'        => $cdatm['user'],
                    ]);
                }
        } else {
            $html->set_block('no_aitem', ['message' => $info['no_item']]);
        }
        echo $html->get_output();
    }
}