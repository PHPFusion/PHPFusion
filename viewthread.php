<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: forum/viewthread.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/


use PHPFusion\Infusions\Forum\Classes\Threads\ViewThread;

require_once __DIR__.'/../../maincore.php';
if (!defined('FORUM_EXIST')) {
    redirect(BASEDIR."error.php?code=404");
}

$thread = new ViewThread();
echo $thread->display_thread();

require_once THEMES."templates/footer.php";
