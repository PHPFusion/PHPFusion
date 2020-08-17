<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: lostpassword.php
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
require_once __DIR__.'/maincore.php';
require_once THEMES.'templates/header.php';
require_once TEMPLATES."global/lost-password.php";

if (iMEMBER) {
    redirect(BASEDIR.'index.php');
}
require_once INCLUDES."sendmail_include.php";
$locale = fusion_get_locale('', LOCALE.LOCALESET."lostpassword.php");
add_to_title($locale['global_200'].$locale['400']);

ob_start();
$obj = new PHPFusion\LostPassword();
if (check_get("user_email") && check_get("account")) {
    $email = get("user_email");
    $account = get("account");
    $obj->checkPasswordRequest($email, $account);
} else if ($send_pass = post("send_password")) {
    $obj->sendPasswordRequest($send_pass);
} else {
    $obj->renderInputForm();
}

$obj->displayOutput();
$content = ob_get_contents();
ob_end_clean();

display_lostpassword($content);
require_once THEMES.'templates/footer.php';
