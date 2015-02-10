<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: payments.php
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
if (isset($_GET['payid']) && !isnum($_GET['payid'])) die("Denied");


$payment = new \PHPFusion\Eshop\Admin\Payments();
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $payment->verify_payment($_GET['pid']) : 0;
$tab_title['title'][] = 'Current Payment Method'; //$locale['ESHPCUPNS100'];
$tab_title['id'][] = 'payment';
$tab_title['icon'][] = '';
$tab_title['title'][] =  $edit ? 'Edit Payment Method' : 'Add Payment Method'; // $locale['ESHPCUPNS115'] : $locale['ESHPCUPNS114'];
$tab_title['id'][] = 'paymentform';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
$tab_active = tab_active($tab_title, $edit ? 1 : 0 , 1);
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=payments");
echo opentabbody($tab_title['title'][0], 'payment', $tab_active, 1);
$payment->payment_listing();
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'paymentform') {
	echo opentabbody($tab_title['title'][1], 'paymentform', $tab_active, 1);
	$payment->add_payment_form();
	echo closetabbody();
}


