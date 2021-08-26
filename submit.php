<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: submit.php
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

use PHPFusion\Admins;
require_once __DIR__.'/maincore.php';

if (!iMEMBER) {
    redirect(BASEDIR.'index.php');
}
require_once FUSION_HEADER;

if ($type = get('stype')) {

    $modules = Admins::getInstance()->getSubmitData();

    if (isset($modules[$type]['link'])) {

        require_once $modules[$type]['link'];

    } else {

        redirect(BASEDIR.'index.php');
    }
} else {
    redirect(BASEDIR.'index.php');
}

require_once FUSION_FOOTER;
