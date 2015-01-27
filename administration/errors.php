<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: errors.php
| Author: Hans Kristian Flaatten (Starefossen)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
require_once THEMES."templates/admin_header.php";
if (!checkrights("ERRO") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) die("Acces Denied");

use PHPFusion\ErrorLogs;

$fusion_errors = new ErrorLogs();
$fusion_errors->add_breadcrumb();
//$fusion_errors->show_error_logs();
opentable($locale['400']);
$fusion_errors->show_error_notice();
closetable();
require_once THEMES."templates/footer.php";