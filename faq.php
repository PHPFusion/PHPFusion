<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/faq.php
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
require_once __DIR__.'/../../maincore.php';
if (!defined('FAQ_EXIST')) {
    redirect(BASEDIR.'error.php?code=404');
}
require_once THEMES.'templates/header.php';
require_once FAQ_CLASS.'autoloader.php';
require_once INFUSIONS.'faq/templates.php';
\PHPFusion\FAQ\FaqServer::Faq()->display_faq();
require_once THEMES.'templates/footer.php';
