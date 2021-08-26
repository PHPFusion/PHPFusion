<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: weblink_submit.php
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
require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/header.php';
require_once WEBLINKS_CLASSES."autoloader.php";
require_once INFUSIONS."weblinks/templates/weblinks.tpl.php";
PHPFusion\Weblinks\WeblinksServer::weblinksSubmit()->displayWeblinks();
require_once THEMES.'templates/footer.php';
