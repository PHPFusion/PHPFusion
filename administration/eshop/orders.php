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
$tab_active = tab_active($tab_title, $_GET['o_page'], 1);
echo opentab($tab_title, $tab_active, 'pageorders', 1);
echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active, 1);
$orders->list_order();
echo closetabbody();
echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active, 1);
//include "orderhistory.php";
echo closetabbody();
echo closetab();


/*
if (isset($_POST['osrchtext'])) {
	$searchtext = stripinput($_POST['osrchtext']);
} else {
	$searchtext = $locale['SRCH161'];
}

echo "<div style='float:right;margin-top:5px;'><form id='search_form'  name='inputform' method='post' action='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;osearch'>
<span style='vertical-align:middle;font-size:14px;'>".$locale['ESHP328']."</span>";
echo "<input type='text' name='osrchtext' class='textbox' style='margin-left:1px; margin-right:1px; margin-bottom:5px; width:160px;'  value='".$searchtext."' onblur=\"if(this.value=='') this.value='".$searchtext."';\" onfocus=\"if(this.value=='".$searchtext."') this.value='';\" />";
echo "<input type='image' id='search_image' src='".BASEDIR."eshop/img/search_icon.png' alt='".$locale['SRCH161']."' />";
echo "</form></div>";
echo "</td></tr></table>";
*/
