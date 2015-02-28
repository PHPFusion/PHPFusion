<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: lostpassword.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "maincore.php";
require_once THEMES."templates/header.php";
require_once INCLUDES."sendmail_include.php";
include LOCALE.LOCALESET."lostpassword.php";

if (iMEMBER) redirect("index.php");

function __autoload($class) {
  require CLASSES.$class.".class.php";
  if (!class_exists($class)) { die("Class not found"); }
}

add_to_title($locale['global_200'].$locale['400']);
opentable($locale['400']);

$obj = new LostPassword();
if (isset($_GET['user_email']) && isset($_GET['account'])) {
	$obj->checkPasswordRequest($_GET['user_email'], $_GET['account']);
	$obj->displayOutput();
} elseif (isset($_POST['send_password'])) {
	$obj->sendPasswordRequest($_POST['email']);
	$obj->displayOutput();
} else {
	$obj->renderInputForm();
	$obj->displayOutput();
}

closetable();

require_once THEMES."templates/footer.php";
?>