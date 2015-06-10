<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop_coupon.php
| Author: Joakim Falk (Domi)
| Co-Author: Frederick MC Chan (hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../../../../maincore.php";
$aid = isset($_POST['token']) ? explode('=', $_POST['token']) : '';
if (!empty($aid)) {
	$aid = $aid[1];
}
$q = isset($_POST['q']) && $_POST['q'] ? form_sanitizer($_POST['q'], '') : 0;
if (checkrights("ESHP") && defined("iAUTH") && $aid == iAUTH) {
	$result = dbquery("SELECT *	 FROM ".DB_ESHOP_COUPONS." WHERE cuid='".$q."'");
	if (dbrows($result)>0) {
		$data = dbarray($result);
		echo json_encode($data);
	}
}
