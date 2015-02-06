<?php
$userdata = array();
header("Content-type: text/html; charset=utf-8");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");        // HTTP/1.0
require_once dirname(__FILE__)."../../../maincore.php";
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
	$data['subtotal'] = Cart::get_cart_total(defender::set_sessionUserID());
	if ($data['subtotal'] == 0 ) {
		$data['subtotal'] = intval(0);
	}
	echo json_encode($data);
} else {
	echo json_encode(array('response'=>2, 'data'=>$data));
}