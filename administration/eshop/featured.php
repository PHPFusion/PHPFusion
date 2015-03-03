<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: featured.php
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

$banner = new \PHPFusion\Eshop\Admin\Banners();
$banner->getMessage();
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $banner->verify_banner($_GET['b_id']) : 0;
$cedit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $banner->verify_item($_GET['i_id']) : 0;

$tab_title['title'][] = $locale['ESHFEAT108b'];
$tab_title['id'][] = 'items';
$tab_title['icon'][] = '';
$tab_title['title'][] = $locale['ESHFEAT108'];
$tab_title['id'][] = 'banner';
$tab_title['icon'][] = '';

$tab_title['title'][] =  $edit ? $locale['ESHFEAT109a'] : $locale['ESHFEAT109'];
$tab_title['id'][] = 'bannerform';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';

$tab_title['title'][] =  $edit ? $locale['ESHFEAT108d'] : $locale['ESHFEAT108c'];
$tab_title['id'][] = 'itemform';
$tab_title['icon'][] = $cedit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';


$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1);

echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=featured");
echo opentabbody($tab_title['title'][0], 'items', $tab_active, 1);
$banner->item_listing();
echo closetabbody();

echo opentabbody($tab_title['title'][1], 'banner', $tab_active, 1);
$banner->banner_listing();
echo closetabbody();

switch($_GET['section']) {
	case 'bannerform':
		echo opentabbody($tab_title['title'][2], 'bannerform', $tab_active, 1);
		$banner->add_banner_form();
		echo closetabbody();
		break;
	case 'itemform':
		echo opentabbody($tab_title['title'][2], 'itemform', $tab_active, 1);
		$banner->add_item_form();
		echo closetabbody();
		break;
}
closetable();