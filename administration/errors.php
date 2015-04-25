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
pageAccess('ERRO');
require_once THEMES."templates/admin_header.php";

use PHPFusion\ErrorLogs;
$fusion_errors = new ErrorLogs();

add_breadcrumb(array('link'=>ADMIN."errors.php".$aidlink, 'title'=>$locale['400']));

opentable($locale['400']);
$fusion_errors->show_error_log();
closetable();

require_once THEMES."templates/footer.php";