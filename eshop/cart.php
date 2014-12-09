<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: cart.php
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
require_once dirname(__FILE__)."../../maincore.php";
require_once THEMES."templates/header.php";
include INCLUDES."eshop_functions_include.php";

add_to_title($locale['ESHPC101']);
opentable($locale['ESHPC101']);

echo "<div class='notify-bar'></div>";

if (iMEMBER) { $username = $userdata['user_id']; } else { $username = $_SERVER['REMOTE_ADDR']; }

buildeshopheader();
echo "<div id ='incart'>";

$result = dbquery("SELECT * FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC");

if (dbrows($result) != 0) {
$counter = 0; 

echo "<table align='center' width='100%' cellpadding='2' cellspacing='0' class='eshptable'><tr>
	<td class='tbl2' width='1%' align='center' colspan='2'><b>".$locale['ESHPC102']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC103']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC104']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC105']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC106']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC107']."</b></td>
</tr><tr>\n";

while ($data = dbarray($result)) {

if ($counter != 0 && ($counter % 1 == 0)) echo "</tr>\n<tr>\n";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>&nbsp;&nbsp;<a href='".BASEDIR."eshop.php?details=".$data['prid']."'><img src='".($data['cimage'] ? "".checkeShpImageExists(BASEDIR."eshop/pictures/".$data['cimage']."")."" : "".BASEDIR."eshop/img/nopic_thumb.gif")."' alt='' width='40' border='0' /></a></td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'><input type='hidden' value='".$data['citem']."' id='prod_".$data['tid']."' name='prod_".$data['tid']."' />".$data['citem']."</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>".getcolorname($data['cclr'])."</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>"; if ($data['cdynt'] || $data['cdyn']) { echo "".$data['cdynt']." : ".$data['cdyn'].""; } echo "</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'><a href='javascript:;' onclick='javascript:minusonecart(".$data['tid']."); return false;'><img src='".BASEDIR."eshop/img/minus.png' border='0' alt='' style='vertical-align:middle;' /></a><input type='text' name='quantity_".$data['tid']."' id='quantity_".$data['tid']."' value='".$data['cqty']."' class='textbox' readonly style='width:25px;' /><a href='javascript:;' onclick='javascript:plusonecart(".$data['tid']."); return false;'><img src='".BASEDIR."eshop/img/plus.png' border='0' alt='' style='vertical-align:middle;' /></a></div></td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>".$data['cprice']."</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'><a href='javascript:;' onclick='javascript:delcitem(".$data['tid']."); return false;' title='".$locale['ESHPC107']."'><img src='".BASEDIR."eshop/img/remove.png' border='0' alt='".$locale['ESHPC107']."' /> </a>";
	$counter++;
	}
echo "</tr>\n</table>\n";

$weight = dbarray(dbquery("SELECT sum(cweight*cqty) as weight FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC"));
$items = dbarray(dbquery("SELECT sum(cqty) as count FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC"));
$sum = dbarray(dbquery("SELECT sum(cprice*cqty) as totals FROM ".DB_ESHOP_CART." WHERE puid = '".$username."'"));

echo "<div style='float:left;margin-top:10px;'><a class='eshpbutton red' href='".BASEDIR."eshop.php?clearcart'>".$locale['ESHPC119']."</a></div>";
$vat = $settings['eshop_vat']; 
$price = $sum['totals'];
$vat = ($price / 100) * $vat;
if ($settings['eshop_vat_default'] == "0") {
$totalincvat = $price + $vat;
} else {
$totalincvat = $price;
}

echo "<div style='float:right;margin-top:8px;'>".$locale['ESHPC108']." ".$items['count']." ".$locale['ESHPC109']." ".($settings['eshop_vat_default'] =="1" ? "".number_format($totalincvat, 2)."" : "".number_format($sum['totals'], 2)."")."  ".$settings['eshop_currency']." <br />";

if ($settings['eshop_vat_default'] == "1") {
	echo "".$settings['eshop_vat']."% VAT (".number_format($vat, 2)." ".$settings['eshop_currency'].") ".$locale['ESHPC110']."<br />";
} else { 
	echo "".$settings['eshop_vat']."% VAT (".number_format($vat, 2)." ".$settings['eshop_currency'].") ".$locale['ESHPC111']."<br />";
	echo "".$locale['ESHPC112']."  ".number_format($totalincvat, 2)." ".$settings['eshop_currency']."<br />";
}

if ($weight['weight']) {
	echo "<div style='float:right;margin-top:0px;'>".$locale['ESHPC113']."".number_format($weight['weight'], 2)." ".$settings['eshop_weightscale']."</div>";
}

echo "</div><div style='clear:both;'></div>";

} else {
	echo "<br /><div class='admin-message'>".$locale['ESHPC114']."</div>";
}

echo "</div>"; //incart div end.

echo "<div style='clear:both;'></div>";
echo "<div style='float:right;margin-top 15px;padding:10px;'><a class='".($settings['eshop_checkout_color'] =="default" ? "button" : "eshpbutton ".$settings['eshop_checkout_color']."")."' href='".BASEDIR."eshop/checkout.php'>".$locale['ESHPC115']." &raquo;</a></div>";
echo "<div style='float:left;margin-top 15px;padding:10px;'><a class='".($settings['eshop_return_color'] =="default" ? "button" : "eshpbutton ".$settings['eshop_return_color']."")."' href='javascript:;' onclick='javascript:history.back(-1); return false;'>&laquo; ".$locale['ESHP030']."</a></div>";
closetable();

require_once THEMES."templates/footer.php";
?>