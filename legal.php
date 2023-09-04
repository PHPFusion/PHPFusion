<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: legal.php
| Author: meangczac (Chan)
| PHPFusion Lead Developer, PHPFusion Core Developer
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\LegalDocs;

require_once __DIR__ . '/maincore.php';
require_once THEMES . 'templates/header.php';

$locale = fusion_get_locale( '', LOCALE . LOCALESET . 'policies.php' );

LegalDocs::getInstance()->view();

require_once THEMES . 'templates/footer.php';
