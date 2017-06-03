<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: submissions.php
| Author: Frederick MC Chan
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
require_once THEMES."templates/header.php";
add_to_title(str_replace('...', '', fusion_get_locale('UM089', LOCALE.LOCALESET."global.php")));

$modules = \PHPFusion\Admins::getInstance()->getSubmitData();
if (empty($modules)) {
    redirect("index.php");
}
foreach ($modules as $db => $submit) {
        opentable(sprintf($submit['title'], ''));
        echo "<a href='".$submit['submit_link']."'>".sprintf($submit['title'], str_replace('...', '', fusion_get_locale('UM089', LOCALE.LOCALESET."global.php")))."</a>";
        closetable();
}
require_once THEMES."templates/footer.php";
