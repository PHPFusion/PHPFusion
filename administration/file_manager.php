<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: file_manager.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';

pageAccess('FM');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/image_uploads.php');

add_to_title($locale['100']);

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'file_manager.php'.fusion_get_aidlink(), 'title' => $locale['100']]);
opentable($locale['100']);
echo '<div class="embed-responsive embed-responsive-16by9">';
echo '<iframe class="embed-responsive-item" src="'.INCLUDES.'filemanager/dialog.php"></iframe>';
echo '</div>';
closetable();

require_once THEMES.'templates/footer.php';
