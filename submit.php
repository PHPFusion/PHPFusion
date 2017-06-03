<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: submit.php
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
require_once dirname(__FILE__).'/maincore.php';
if (!iMEMBER) {
    redirect("index.php");
}
require_once THEMES."templates/header.php";
$modules = \PHPFusion\Admins::getInstance()->getSubmitData();

$_GET['stype'] = !empty($_GET['stype']) && isset($modules[$_GET['stype']]) ? $_GET['stype'] : "";

if (!empty($modules) && $_GET['stype']) {
   require_once $modules[$_GET['stype']]['link'];
} else {
    redirect('index.php');
}
require_once THEMES."templates/footer.php";