<?php
require_once "../../maincore.php";
$aid = isset($_POST['token']) ? explode('=', $_POST['token']) : '';
if (!empty($aid)) {
	$aid = $aid[1];
}
$q = isset($_POST['q']) && isnum($_POST['q']) ? $_POST['q'] : 0;

if (checkrights("SL") && defined("iAUTH") && $aid == iAUTH) {
	$result = dbquery("SELECT link_id, link_name, link_icon, link_position, link_language, link_visibility, link_window
	 FROM ".DB_SITE_LINKS." WHERE link_id='".intval($_POST['q'])."'");
	if (dbrows($result)>0) {
		$data = dbarray($result);
		echo json_encode($data);
	}
}
