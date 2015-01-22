<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop.php
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
require_once "../maincore.php";
require_once THEMES."templates/admin_header.php";
if (!checkrights("ESHP") || !defined("iAUTH") || $_GET['aid'] != iAUTH) { die("Denied"); }
if (isset($_GET['category']) && !isnum($_GET['category'])) die("Denied");
if (isset($_GET['id']) && !isnum($_GET['id'])) die("Denied");
if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
if (!isset($_GET['errors'])){ $_GET['errors'] = ""; }
include INCLUDES."eshop_functions_include.php";

require_once INCLUDES."photo_functions_include.php";

echo "<SCRIPT LANGUAGE=\"JAVASCRIPT\" TYPE=\"TEXT/JAVASCRIPT\">
<!--Hide script from old browsers
function confirmdelete() {
return confirm(\"".$locale['ESHP210']."\")
}
//Stop hiding script from old browsers -->
</SCRIPT>";

opentable($locale['ESHP201']);
if (!isset($_GET['a_page'])) { $_GET['a_page'] = "Main"; }
$countorders = "".dbcount("(oid)", "".DB_ESHOP_ORDERS."", "opaid = '' || ocompleted = ''")."";

$pages = array(
	'main' => array('title'=>$locale['ESHP202'], 'file'=>ADMIN."eshop/products.php"),
	'photos' => array('title'=>$locale['ESHP204'], 'file'=> ADMIN."eshop/photosadmin.php"),
	'categories' => array('title'=>$locale['ESHP203'], 'file'=> ADMIN."eshop/categories.php"),
	'coupons' => array('title'=>$locale['ESHP211'], 'file' => ADMIN."eshop/coupons.php"),
	'featured' => array('title'=>$locale['ESHP212'], 'file'=> ADMIN."eshop/featured.php"),
	'payments' => array('title'=>$locale['ESHP206'], 'file'=> ADMIN."eshop/payments.php"),
	'shipping' => array('title'=>$locale['ESHP207'], 'file' => ADMIN."eshop/shipping.php"),
	'customers' => array('title'=> $locale['ESHP208'], 'file' =>  ADMIN."eshop/customers.php"),
	'orders' => array('title'=> $locale['ESHP209']."<span class='badge m-l-10'>".$countorders."</span>", 'file' => ADMIN."eshop/orders.php")
);

// @todo : deprecate countbox_bubble
echo "<nav class='navbar navbar-default'>\n";
echo "<ul class='nav navbar-nav'>\n";
foreach($pages as $page_get => $page) {
	echo "<li ".($_GET['a_page'] == $page_get ? "class='active'" : '')." ><a href='".FUSION_SELF.$aidlink."&amp;a_page=".$page_get."'>".$page['title']."</a></li>\n";
}
echo "</ul>\n";
echo "</nav>\n";

echo "<table class='table'><tr><td align='left' colspan='10'>\n";
include $pages[$_GET['a_page']]['file'];
echo "</td></tr></table>";
closetable();

require_once THEMES."templates/footer.php";
?>