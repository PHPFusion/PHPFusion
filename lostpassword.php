<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: lostpassword.php
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
require_once __DIR__.'/maincore.php';
require_once THEMES.'templates/header.php';
$locale = fusion_get_locale("", LOCALE.LOCALESET."lostpassword.php");
require_once INCLUDES."sendmail_include.php";
if (iMEMBER) {
    redirect(BASEDIR.'index.php');
}

add_to_title($locale['global_200'].$locale['400']);

ob_start();
$obj = new PHPFusion\LostPassword();
if (isset($_GET['user_email']) && isset($_GET['account'])) {
    $obj->checkPasswordRequest($_GET['user_email'], $_GET['account']);
    $obj->displayOutput();
} else if (isset($_POST['send_password'])) {
    $obj->sendPasswordRequest($_POST['email']);
    $obj->displayOutput();
} else {
    $obj->renderInputForm();
    $obj->displayOutput();
}
$content = ob_get_contents();
ob_end_clean();

if (!function_exists("display_lostpassword")) {
    function display_lostpassword($content) {
        $locale = fusion_get_locale();

        opentable($locale['400']);
        echo $content;
        closetable();
    }
}


display_lostpassword($content);

require_once THEMES.'templates/footer.php';
