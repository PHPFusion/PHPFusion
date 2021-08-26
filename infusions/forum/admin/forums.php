<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: forums.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__."/../../../maincore.php";
if (!defined('FORUM_EXISTS')) {
    redirect(BASEDIR."error.php?code=404");
}
require_once THEMES.'templates/admin_header.php';
require_once FORUM_CLASSES."autoloader.php";
require_once INCLUDES.'infusions_include.php';
PHPFusion\Forums\Admin\ForumAdminInterface::view()->displayForumAdmin();
require_once THEMES.'templates/footer.php';
