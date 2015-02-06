<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: customers.php
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

$customer = new PHPFusion\Eshop\Admin\Customers();
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $customer->verify_customer($_GET['cuid']) : 0;
$tab_title['title'][] = 'Current Customers'; //$locale['ESHPCUPNS100'];
$tab_title['id'][] = 'customer';
$tab_title['icon'][] = '';
$tab_title['title'][] =  $edit ? 'Edit Customer' : 'Add Customer'; // $locale['ESHPCUPNS115'] : $locale['ESHPCUPNS114'];
$tab_title['id'][] = 'customerform';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1);
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=customers");
echo opentabbody($tab_title['title'][0], 'customer', $tab_active, 1);
$customer->customer_listing();
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'customerform') {
	echo opentabbody($tab_title['title'][1], 'customerform', $tab_active, 1);
	$customer->add_customer_form();
	echo closetabbody();
}

// this one has not been deciphered yet.
if (isset($_GET['step']) && $_GET['step'] == "deletecode") {
	$codetoremove = dbarray(dbquery("SELECT ccupons FROM ".DB_ESHOP_CUSTOMERS." WHERE cuid='".$_GET['cuid']."'"));
	if (!preg_match("/^[-0-9A-ZÅÄÖ._@\s]+$/i", $_GET['cupon'])) { die("Denied"); exit; }
	$cuponcodes = preg_replace(array("(^\.{$_GET['cupon']}$)","(\.{$_GET['cupon']}\.)","(\.{$_GET['cupon']}$)"), array("",".",""), $codetoremove['ccupons']);
	$result = dbquery("UPDATE ".DB_ESHOP_CUSTOMERS." SET ccupons='".$cuponcodes."' WHERE cuid='".$_GET['cuid']."'");
	redirect(FUSION_SELF.$aidlink."&amp;a_page=customers");
}
