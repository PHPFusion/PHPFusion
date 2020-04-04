<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: site_links.php
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

use PHPFusion\Administration\SitelinksAdmin;

require_once __DIR__.'/../maincore.php';

require_once THEMES.'templates/admin_header.php';
SitelinksAdmin::getInstance()->admin();

//SiteLinks_Admin::Administration()->display_administration_form();

require_once THEMES.'templates/footer.php';
