<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: custom_pages.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
pageAccess("CP");
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/custom_pages.php";
include LOCALE.LOCALESET."admin/sitelinks.php";
$customPage = new PHPFusion\CustomPage();
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? 1 : 0;
$allowed_pages = array('cp1', 'cp2');
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : 'cp1';
$tab_title['title'][] = $locale['402'];
$tab_title['id'][] = 'cp1';
$tab_title['icon'][] = '';
$tab_title['title'][] = $edit ? $locale['401'] : $locale['400'];
$tab_title['id'][] = 'cp2';
$tab_title['icon'][] =  $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';

$tab_active = tab_active($tab_title, $_GET['section'], TRUE);

echo opentab($tab_title, $tab_active, 'cpa', TRUE);
if (isset($_GET['section']) && $_GET['section'] == "cp2") {
	add_breadcrumb(array('link'=>ADMIN.'custom_pages.php'.$aidlink, 'title'=>$edit ? $locale['401'] : $locale['400']));
	$data = $customPage->getData();
	$customPage::customPage_form($data);
} else {
	$customPage::listPage();
}
echo closetab();
require_once THEMES."templates/footer.php";