<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: coupons.php
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

$coupon = new \PHPFusion\Eshop\Admin\Coupons();
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $coupon->verify_coupon($_GET['cuid']) : 0;
$tab_title['title'][] = $locale['ESHPCUPNS100'];
$tab_title['id'][] = 'coupon';
$tab_title['icon'][] = '';
$tab_title['title'][] =  $edit ? $locale['ESHPCUPNS115'] : $locale['ESHPCUPNS114'];
$tab_title['id'][] = 'couponform';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1, 1);
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=coupons");
echo opentabbody($tab_title['title'][0], 'coupon', $tab_active, 1);
$coupon->coupon_listing();
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'couponform') {
	echo opentabbody($tab_title['title'][1], 'couponform', $tab_active, 1);
	$coupon->add_coupon_form();
	echo closetabbody();
}