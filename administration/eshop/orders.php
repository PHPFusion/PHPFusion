<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: orders.php
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

$orders = new \PHPFusion\Eshop\Admin\Orders();
$tab_title['title'][] = $locale['ESHP301'];
$tab_title['id'][] = 'orders';
$tab_title['icon'][] = '';
$tab_title['title'][] = $locale['ESHP302'];
$tab_title['id'][] = 'history';
$tab_title['icon'][] = '';
$tab_active = tab_active($tab_title, $_GET['section'], 1);
echo opentab($tab_title, $tab_active, 'pageorders', 1);
echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active, 1);
$orders->list_order();
echo closetabbody();
if ($_GET['section'] == 'history') {
	echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active, 1);
	$orders->list_history();
	echo closetabbody();
}

echo closetab();
