<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: categories.php
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

$category = new PHPFusion\Eshop\Admin\ProductCategories(); // load constructs
// build a new interface
$tab_title['title'][] = $locale['ESHPCATS099'];
$tab_title['id'][] = 'listcat';
$tab_title['icon'][] = '';

$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') && $category->verify_cat_edit($_GET['cid']) ? $_GET['section'] : 'listcat';

$tab_title['title'][] = $edit ? $locale['ESHPCATS140'] : $locale['ESHPCATS139'];
$tab_title['id'][] = 'catform';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';

$tab_active = tab_active($tab_title, $edit, 1);
$category->getMessage();
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=categories");
echo opentabbody($tab_title['title'][0], 'listcat', $tab_active, 1);
$category->category_listing();
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'catform') {
	echo opentabbody($tab_title['title'][1], 'catform', $tab_active, 1);
	$category->add_cat_form();
	echo closetabbody();
}
closetable();
