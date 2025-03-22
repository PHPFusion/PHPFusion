<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: site_links.php
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

namespace PHPFusion\Administration;

use Exception;

use PHPFusion\Administration\SiteLinks;

require_once __DIR__ . '/../maincore.php';

require_once THEMES . "templates/admin_header.php";

try {
    Sitelinks::Admin()->adminForm();
} catch (Exception $e) {

    die($e->getMessage());
}

require_once THEMES . "templates/footer.php";
