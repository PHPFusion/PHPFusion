<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: products.php
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
if (!defined("IN_FUSION")) die("Access Denied");

$item = new \PHPFusion\Eshop\Admin\Products();
$category_count = $item->category_check();
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit' && $category_count) ? $item->verify_product_edit($_GET['id']) : 0;
$tab_title['title'][] = $locale['ESHPPRO097'];
$tab_title['id'][] = 'product';
$tab_title['icon'][] = '';
if ($category_count) {
	$tab_title['title'][] = $edit ? $locale['ESHPPRO098'] : $locale['ESHPPRO099'];
	$tab_title['id'][] = 'itemform';
	$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
}
$tab_active = tab_active($tab_title, ($edit ? 'itemform' : 'product'), 1);
$item->getMessage();
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=main");
echo opentabbody($tab_title['title'][0], 'product', $tab_active, 1);
$item->product_listing();
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'itemform') {
	echo opentabbody($tab_title['title'][1], 'itemform', $tab_active, 1);
	$item->product_form();
	echo closetabbody();
}
closetable();