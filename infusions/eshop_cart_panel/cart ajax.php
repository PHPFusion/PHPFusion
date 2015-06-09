<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: cart ajax.php
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
include SHOP."locale/".LOCALESET."eshop.php";
require_once SHOP."classes/Cart.php";
if (!defined('FUSION_NULL')) {
	// remotely refer form_sanitizer from the referer script treating this form executed from eshop form
	$data['tid'] = 0; // let it auto increment
	$data['prid'] = form_sanitizer($_POST['id'], ''); // product id
	$data['puid'] = defender::set_sessionUserID(); // this is the username --- change to user_id and USER_IP? how to get user_name?
	$data['cqty'] = form_sanitizer($_POST['product_quantity'], ''); // order quantity
	$data['cclr'] = form_sanitizer($_POST['product_color'], ''); // order color
	$data['cdyn'] = form_sanitizer($_POST['product_type'], ''); // this stores user selection
	$data['cadded'] = time(); // time
	$product = \PHPFusion\Eshop\Eshop::get_productData($data['prid']);
	if (!empty($product)) { // loaded $data
		$data['artno'] = $product['artno']; // artno
		$data['citem'] = $product['title']; // item name
		$data['cimage'] = $product['thumb']; // item image
		$data['cdynt'] = $product['dynf']; // this stores dynf
		$data['cprice'] = $product['xprice'] ? $product['xprice'] : $product['price']; // this is the 1 unit price
		$data['cweight'] = $product['cweight']; // 1 unit weigh t
		$data['ccupons'] = $product['cupons']; // accept coupons or not
		// now check if order exist.
		$response = \PHPFusion\Eshop\Cart::add_to_cart($data); // returns json responses
		$response['error_id'] = false;
		$response['title'] = $locale['product_updated'];
		$response['message'] = $locale['product_message'];
		echo json_encode($response);
		\PHPFusion\Eshop\Eshop::refresh_session();
	} else {
		define('FUSION_NULL', true);
		echo json_encode(array('error_id'=>1, 'title'=>$locale['product_error_001'], 'message'=>$locale['product_error_002']));
	}
}
