<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: shipping.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

$shipping = new \PHPFusion\Eshop\Admin\Shipping();
$cview = (isset($_GET['action']) && $_GET['action'] == 'view') ? $shipping->verify_shippingCats($_GET['cid']) : 0;
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $shipping->verify_shippingCats($_GET['cid']) : 0;
$tab_title['title'][] = $cview ? $locale['ESHPSS107'] : $locale['ESHPSS108'];
$tab_title['id'][] = 'shipping';
$tab_title['icon'][] = $cview ? 'fa fa-pencil m-r-10' : '';
$tab_title['title'][] =  $edit ? $locale['ESHPSS109'] : $locale['ESHPSS110'];
$tab_title['id'][] = 'shippingcat';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1, 1);
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=shipping");
echo opentabbody($tab_title['title'][0], 'shipping', $tab_active, 1);
if (isset($_GET['section']) && $_GET['section'] == 'shipping' && $cview) {
	$shipping->itenary_list();
} else {
	$shipping->shipping_listing();
}
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'shippingcat') {
	echo opentabbody($tab_title['title'][1], 'shippingcat', $tab_active, 1);
	$shipping->add_shippingco_form();
	echo closetabbody();
}