<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
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

if ($_GET['a_page'] == "Main") {
$tbl0 = "tbl1";
} else {
$tbl0 = "tbl2";
}

if ($_GET['a_page'] == "Categories") {
$tbl1 = "tbl1";
} else {
$tbl1 = "tbl2";
}

if ($_GET['a_page'] == "photos") {
$tbl3 = "tbl1";
} else {
$tbl3 = "tbl2";
}

if ($_GET['a_page'] == "payments") {
$tbl4 = "tbl1";
} else {
$tbl4 = "tbl2";
}

if ($_GET['a_page'] == "shipping") {
$tbl5 = "tbl1";
} else {
$tbl5 = "tbl2";
}

if ($_GET['a_page'] == "orders") {
$tbl6 = "tbl1";
} else {
$tbl6 = "tbl2";
}

if ($_GET['a_page'] == "customers") {
$tbl7 = "tbl1";
} else {
$tbl7 = "tbl2";
}

if ($_GET['a_page'] == "cupons") {
$tbl8 = "tbl1";
} else {
$tbl8 = "tbl2";
}

if ($_GET['a_page'] == "featured") {
$tbl9 = "tbl1";
} else {
$tbl9 = "tbl2";
}


$countorders = "".dbcount("(oid)", "".DB_ESHOP_ORDERS."", "opaid = '' || ocompleted = ''")."";

echo "<table cellspacing='1' cellpadding='1' width='100%' ><tr>
<td align='center' class='".$tbl0."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=Main'>".$locale['ESHP202']."</a></td>
<td align='center' class='".$tbl3."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=photos'>".$locale['ESHP204']."</a></td>
<td align='center' class='".$tbl1."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=Categories'>".$locale['ESHP203']."</a></td>
<td align='center' class='".$tbl8."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=cupons'>".$locale['ESHP211']."</a></td>
<td align='center' class='".$tbl9."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=featured'>".$locale['ESHP212']."</a></td></tr><tr>
<td align='center' class='".$tbl4."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=payments'>".$locale['ESHP206']."</a></td>
<td align='center' class='".$tbl5."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping'>".$locale['ESHP207']."</a></td>
<td align='center' class='".$tbl7."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=customers'>".$locale['ESHP208']."</a></td>
<td align='center' colspan='2' class='".$tbl6."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders'>".$locale['ESHP209']."</a> <div class='countbox_bubble'>".$countorders."</div></td>
</tr><tr><td align='left' colspan='10'><div class='spacer'></div>";

if ($_GET['a_page'] == "Main") {
include ADMIN."eshop/products.php";
}
elseif ($_GET['a_page'] == "Categories") {
include ADMIN."eshop/categories.php";
}
elseif ($_GET['a_page'] == "photos") {
include ADMIN."eshop/photosadmin.php";
}
elseif ($_GET['a_page'] == "payments") {
include ADMIN."eshop/payments.php";
}
elseif ($_GET['a_page'] == "shipping") {
include ADMIN."eshop/shipping.php";
}
elseif ($_GET['a_page'] == "orders") {
include ADMIN."eshop/orders.php";
}
elseif ($_GET['a_page'] == "customers") {
include ADMIN."eshop/customers.php";
}
elseif ($_GET['a_page'] == "cupons") {
include ADMIN."eshop/cupons.php";
}
elseif ($_GET['a_page'] == "featured") {
include ADMIN."eshop/featured.php";
}
echo "</td></tr></table>";
closetable();
require_once THEMES."templates/footer.php";
?>
