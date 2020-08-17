<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: index.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * @todo:
 * In Babylon, everything must be in SEF in administration panel.
 * SEF URL here, and we will post and do everything in core here... to remove all other files.
 * The dependencies must load and we will use API instead.
 */
use PHPFusion\Administration\Admin;

require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';

$admin_images = TRUE;

$admin = new Admin();
list( $members, $articles, $blog, $download, $forum, $photos, $news, $weblinks, $comments_type, $submit_type, $submit_link, $submit_data, $link_type, $global_infusions, $global_comments, $global_ratings, $global_submissions, $admin_icons, $upgrade_info ) = $admin->getAdminGlobals();

// Update checker
if ($settings['update_checker'] == 1) {
    $url = 'https://www.phpfusion.com/updates/10.txt';
    if (get_http_response_code($url) == 200) {
        $file = @file_get_contents($url);
        $array = explode("\n", $file);
        $version = $array[0];

        if (version_compare($version, $settings['version'], '>')) {
            add_notice('info', str_replace(['[LINK]', '[/LINK]', '[VERSION]'], ['<a href="'.$array[1].'" target="_blank">', '</a>', $version], $locale['new_update_avalaible']));
        }
    }
}

render_admin_dashboard();

require_once THEMES.'templates/footer.php';
