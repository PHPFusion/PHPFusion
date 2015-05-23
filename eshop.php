<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop.php
| Author: Joakim Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__)."/maincore.php";
if (!db_exists(DB_ESHOP)) {
	$_GET['code'] = 404;
	require_once __DIR__.'/error.php';
	exit;
}
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."eshop.php";
require_once THEMES."templates/global/eshop.php";
//include INCLUDES."eshop_functions_include.php";
//Close the tree when eShop home have been clicked... where is the tree?
/*
if ($settings['eshop_cats'] == "1") {
echo '<script type="text/javascript"> 
	d.closeAll();
</script>';
}
*/
$eShop = new PHPFusion\Eshop\Eshop();
$eShop->__construct_checkout();
$info = $eShop->get_category();
$info += $eShop->get_product();
$info += $eShop->get_featured();
$info += $eShop->get_title();
if ($_GET['product']) {
	$info += $eShop->get_product_photos();
}
render_eshop_nav($info);
if ($_GET['category']) {
	// view category page
	render_eshop_featured_product($info);
	render_eshop_page_content($info);
	render_eshop_featured_category($info);
} elseif ($_GET['product']) {
	// view product page
	render_eshop_product($info);

} elseif (isset($_GET['checkout'])) {
	print_p($_POST);
	$info = $eShop->get_checkout_info();
	if (isset($_POST['confirm_payout'])) {
		$eShop->handle_payments();
	}
	elseif (isset($_POST['agreement_checked'])) {
		// validate the form.
		$validate_success = $eShop->validate_order();
		if ($validate_success) {
			print_p('is_successful');
		} else {
			render_checkout($info);
		}
	}
	else {
		// do checkout form
		render_checkout($info);
	}
} else {
	render_eshop_featured_url($info);
	render_eshop_featured_product($info);
	render_eshop_page_content($info);
	render_eshop_featured_category($info);
}

require_once THEMES."templates/footer.php";
