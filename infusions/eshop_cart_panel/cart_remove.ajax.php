<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: cart_remove_ajax.php
| Author: Frederick MC Chan (hien)
| Co-Author: J.Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
$userdata = array();
header("Content-type: text/html; charset=utf-8");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");        // HTTP/1.0
require_once dirname(__FILE__)."../../../maincore.php";
require_once SHOP."classes/Cart.php";
$data['usr'] = isset($_POST['usr']) ? stripinput($_POST['usr']) : 0;
$data['value'] = isset($_POST['val']) && isnum($_POST['val']) ? $_POST['val'] : 0;
$data['prid'] = isset($_POST['prid']) && isnum($_POST['prid']) ? $_POST['prid'] : 0;
$data['color'] = isset($_POST['clr']) && isnum($_POST['clr']) ? $_POST['clr'] : '';
$data['cdyn'] = isset($_POST['cdyn']) && isnum($_POST['cdyn']) ? $_POST['cdyn'] : '';
$data['time'] = isset($_POST['time']) && isnum($_POST['time']) ? $_POST['time'] : 0;

$check = dbcount("(tid)", DB_ESHOP_CART, "tid='".$data['value']."' AND puid='".$data['usr']."' AND prid='".$data['prid']."' AND cdyn='".$data['cdyn']."' AND cclr='".$data['color']."'");
if ($check) {
	require_once 'includes.php';
	dbquery("DELETE FROM ".DB_ESHOP_CART." WHERE tid='".$data['value']."'");
	$data['response'] = 1;
	$data['subtotal'] = \PHPFusion\Eshop\Eshop::get_cart_total(defender::set_sessionUserID());
	if ($data['subtotal'] == 0 ) {
		$data['subtotal'] = intval(0);
	}
	echo json_encode($data);
	\PHPFusion\Eshop\Eshop::refresh_session();
} else {
	echo json_encode(array('response'=>2, 'data'=>$data));
}
