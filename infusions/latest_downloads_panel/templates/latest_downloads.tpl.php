<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: latest_downloads.tpl.php
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
defined('IN_FUSION') || exit;

if (!function_exists('render_latest_downloads')) {
    function render_latest_downloads($info) {
        $locale = fusion_get_locale();

        add_to_jquery("$('[data-trim-text]').trim_text();");

        openside($info['title']);
        if (!empty($info['item'])) {
            echo '<ul style="margin-left: 25px;">';
            foreach ($info['item'] as $data) {
                echo '<li style="list-style-position: inside;text-indent: -1.5em;">
                    <a class="overflow-hide" data-trim-text="30" href="'.$data['download_url'].'">'.$data['download_title'].'</a>
                    <br/><span>'.$locale['global_070'].$data['profile_link'].'</span>
                </li>';
            }
            echo '</ul>';
        } else {
            echo $info['no_item'];
        }
        closeside();
    }
}
