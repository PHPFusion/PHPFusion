<?php
$userdata = array();
header("Content-type: text/html; charset=utf-8");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");        // HTTP/1.0
require_once dirname(__FILE__)."../../../maincore.php";

if ($defender->verify_tokens('productfrm', 0)) {
	// remotely refer form_sanitizer from the referer script treating this form executed from eshop form
	$_SERVER['REQUEST_URI'] = fusion_get_settings('site_path').str_replace(fusion_get_settings('siteurl'), '', $_SERVER['HTTP_REFERER']);
	$data['tid'] = 0; // let it auto increment
	$data['prid'] = form_sanitizer($_POST['id'], '', 'id'); // product id
	$data['puid'] = defender::set_sessionUserID(); // this is the username --- change to user_id and USER_IP? how to get user_name?
	$data['cqty'] = form_sanitizer($_POST['product_quantity'], '', 'product_quantity'); // order quantity
	$data['cclr'] = form_sanitizer($_POST['product_color'], '', 'product_color'); // order color
	$data['cdyn'] = form_sanitizer($_POST['product_type'], '', 'product_type'); // this stores user selection
	$data['cadded'] = time(); // time
	$product = \PHPFusion\Eshop::get_productData($data['prid']);
	if (!empty($product)) { // loaded $data
		$data['artno'] = $product['artno']; // artno
		$data['citem'] = $product['title']; // item name
		$data['cimage'] = $product['picture']; // item image
		$data['cdynt'] = $product['dynf']; // this stores dynf
		$data['cprice'] = $product['xprice'] ? $product['xprice'] : $product['price']; // this is the 1 unit price
		$data['cweight'] = $product['cweight']; // 1 unit weigh t
		$data['ccupons'] = $product['ccupons']; // acept coupons or not
		// now check if order exist.
		include "includes.php";
		Cart::add_to_cart($data);
	} else {
		$defender->stop();
		echo json_encode(array('error_id'=>1, 'code'=>'Product Not Found (Response-1)'));
	}
}

