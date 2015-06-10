<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop_featitem.php
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
$q = isset($_POST['q']) && isnum($_POST['q']) ? $_POST['q'] : 0;

if (checkrights("ESHP") && defined("iAUTH") && $aid == iAUTH) {
	$result = dbquery("SELECT featitem_id, featitem_title, featitem_description, featitem_cid
	 FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_id='".intval($_POST['q'])."'");
	if (dbrows($result)>0) {
		$data = dbarray($result);
		echo json_encode($data);
	}
}
