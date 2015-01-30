<?php
require_once "../../maincore.php";
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
?>