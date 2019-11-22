<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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

use PHPFusion\Administration\Classes\AdminIndex;

require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
require_once ADMIN.'/dashboard/AdminDashboard.php';

$admin_images = TRUE;

$admin = new AdminIndex();
list( $members, $articles, $blog, $download, $forum, $photos, $news, $weblinks, $comments_type, $submit_type, $submit_link, $submit_data, $link_type, $global_infusions, $global_comments, $global_ratings, $global_submissions, $admin_icons, $upgrade_info ) = $admin->getAdminGlobals();

render_admin_dashboard();

require_once THEMES.'templates/footer.php';
