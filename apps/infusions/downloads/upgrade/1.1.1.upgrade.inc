<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 1.1.1.upgrade.inc
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
$inf_version = '1.1.1';

$inf_altertable[] = DB_DOWNLOADS." ADD INDEX download_user (download_user), ADD INDEX download_cat (download_cat), ADD INDEX download_count (download_count);";
$inf_altertable[] = DB_DOWNLOAD_CATS." ADD INDEX download_cat_parent (download_cat_parent);";
