<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: latest_downloads_panel/templates.php
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
defined('IN_FUSION') || exit;

if (!function_exists('render_latest_downloads')) {
    function render_latest_downloads($info) {
        $locale = fusion_get_locale();

        $html = \PHPFusion\Template::getInstance('render_latest_downloads');
        $html->set_template(__DIR__.'/templates/latest_downloads.html');
        $html->set_tag('openside', fusion_get_function('openside', $info['title']));
        $html->set_tag('closeside', fusion_get_function('closeside'));

        add_to_jquery("$('[data-trim-text]').trim_text();");

        if (!empty($info['item'])) {
            foreach ($info['item'] as $data) {
                $html->set_block('download', [
                    'download_url'   => $data['download_url'],
                    'download_title' => $data['download_title'],
                    'author'         => $locale['global_070'].$data['profile_link'],
                    'bullet'         => $info['theme_bullet']
                ]);
            }
        } else {
            $html->set_block('no_item', ['message' => $info['no_item']]);
        }

        echo $html->get_output();
    }
}
