<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: latest_comments_panel/templates.php
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

if (!function_exists('render_latest_comments')) {
    function render_latest_comments($infom) {

        $html = \PHPFusion\Template::getInstance('latest_comments');
        $html->set_template(INFUSIONS."latest_comments_panel/templates/latest_comments.html");
        $html->set_tag('openside', fusion_get_function('openside', $infom['opentable']));
        $html->set_tag('closeside', fusion_get_function('closeside'));
        if (!empty($infom['item'])) {
                foreach ($infom['item'] as $cdatm) {
                    $html->set_block('normal_comments', [
                        'comments_subject'     => "<div id='text_id' data-comments-text='23'>".$cdatm['subject']."</div>",
                        'comments_link_url'    => $cdatm['link_url'],
                        'comments_link_title'  => "<div id='text_id' data-comments-text='23'>".$cdatm['link_title']."</div>",
                        'comments_user'        => $cdatm['user'],
                    ]);
                }
        } else {
            $html->set_block('no_item', ['message' => $infom['no_rows']]);
        }
        echo $html->get_output();
    }
}