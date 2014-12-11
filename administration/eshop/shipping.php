<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: shipping.php
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

         if (!isset($_GET['s_page'])){
            $_GET['s_page'] = "shipping";
                }

if ($_GET['s_page'] == "shipping"){
$tbl0 = "tbl1";
}else{
$tbl0 = "tbl2";
}
if ($_GET['s_page'] == "shippingcats"){
$tbl1 = "tbl1";
}else{
$tbl1 = "tbl2";
}
echo "<table align='center' cellspacing='0' cellpadding='0' class='tbl-border' width='100%' border='0'><tr>
<td align='center' class='".$tbl0."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shipping'>".$locale['ESHPSHPMTS100']."</a></td>
<td align='center' class='".$tbl1."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shippingcats'>".$locale['ESHPSHPMTS101']."</a></td>
</tr><tr><td align='left' class='tbl' colspan='2'>";
if ($_GET['s_page'] == "shipping") {
include "shippingitems.php";
}
elseif ($_GET['s_page'] == "shippingcats") {
include "shippingcats.php";
}
echo "</td></tr></table>";
?>