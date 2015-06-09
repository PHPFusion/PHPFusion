<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: cartaction.php
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
header("Content-type: text/html; charset=UTF-8");
header("Cache-Control: no-cache");
header("Pragma: nocache");
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
if (isset($_GET['payment']) && !isnum($_GET['payment'])) die("Denied");
if (isset($_GET['shipment']) && !isnum($_GET['shipment'])) die("Denied");

$username = "";

include LOCALE.LOCALESET."eshop.php";

if (iMEMBER) { $username = $userdata['user_id']; } else { $username = $_SERVER['REMOTE_ADDR']; }

$payment = "";
$shipping = "";
$totalincvat = "";
$shippingtotal = "";
$paymentsurcharge = "";
$discount = "0";
$cuponexcluded = "";
$weight = "";
$sum = "";
$discalc = "";

$weight = dbarray(dbquery("SELECT sum(cweight*cqty) as weight FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC"));
$sum = dbarray(dbquery("SELECT sum(cprice*cqty) as totals FROM ".DB_ESHOP_CART." WHERE puid = '".$username."'"));

$vat = $eshop_settings['eshop_vat']; 
$price = $sum['totals'];
$vat = ($price / 100) * $vat;
if ($eshop_settings['eshop_vat_default'] == "0") {
$totalincvat = $price + $vat;
} else {
$totalincvat = $price;
}

if (isset($_GET['payment'])) {
$payment = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_PAYMENTS." WHERE active='1' AND pid='".$_GET['payment']."'"));
$paymentsurcharge = $payment['surcharge'];
}
if (isset($_GET['shipment'])) {
$shipping = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE active='1' AND sid='".$_GET['shipment']."'"));
$shippingsurcharge = $shipping['weightcost'];
$shippinginitial = $shipping['initialcost'];
$shippingsurcharge = $shippingsurcharge*$weight['weight'];
$shippingtotal = $shippingsurcharge+$shippinginitial;
}

if (isset($_GET['cupon']) && $_GET['cupon'] !== $locale['ESHPCHK171']) {
if (!preg_match("/^[\w\W-0-9A-Z@\s]+$/i", $_GET['cupon'])) { die("Denied"); exit; }

if (!iMEMBER) {
	echo $locale['ESHPCHK184'];
} else { 
	$verifycupon = dbquery("SELECT * FROM ".DB_ESHOP_CUSTOMERS." WHERE ccupons LIKE '%.".$_GET['cupon']."' LIMIT 0,1");
if (!dbrows($verifycupon) != 0) {

$cupon = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_COUPONS." WHERE cuid='".$_GET['cupon']."' AND active = '1' AND (custart='0'||custart<=".time().") AND (cuend='0'||cuend>=".time().") LIMIT 0,1"));
$cuponsum = dbarray(dbquery("SELECT sum(cprice*cqty) as totals FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' AND ccupons='1'"));

if ($cupon['cutype'] == "1") {
if ($cupon['cuvalue'] > $cuponsum['totals']) {
$discount = $locale['ESHPCHK177'];
$cuponexcluded = dbarray(dbquery("SELECT sum(cqty) as count FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' AND ccupons='0'"));
} else {
$discvalue = $cupon['cuvalue'];
$discalc = $discvalue;
$discount = "".number_format($discalc)." ".$eshop_settings['eshop_currency']."";
echo '<script type="text/javascript">
$(document).ready(function() {
	$(".notify-bar").html(" '.$cupon['cuname'].' '.$locale['ESHPCHK183'].' ").slideDown();
    setTimeout(function () {
    $(".notify-bar").slideUp();
  },5000)
});
</script>';
}
} else if ($cupon['cutype'] == "0") {
$cuponexcluded = dbarray(dbquery("SELECT sum(cqty) as count FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' AND ccupons='0'"));
$cuponexcluded = $cuponexcluded['count'];
$discount = $cupon['cuvalue']; 
$dvat = $eshop_settings['eshop_vat']; 
$itemstocalc = $cuponsum['totals'];
if ($eshop_settings['eshop_vat_default'] == "0") {
$dvat = ($itemstocalc / 100) * $dvat;
$discalc = $itemstocalc + $dvat;
} else {
$discalc = $itemstocalc;
}
$discalc = ($discalc / 100) * $discount;
$discount = "".$discalc." ".$eshop_settings['eshop_currency']."";

echo '<script type="text/javascript">
$(document).ready(function() {
	$(".notify-bar").html(" '.$cupon['cuname'].' '.$locale['ESHPCHK183'].' ").slideDown();
    setTimeout(function () {
    $(".notify-bar").slideUp();
  },5000)
});
</script>';
} else {
$discount = $locale['ESHPCHK179'];
echo '<script type="text/javascript">
$(document).ready(function() {
	$(".notify-bar").html(" '.$cupon['cuname'].' '.$locale['ESHPCHK179'].' ").slideDown();
    setTimeout(function () {
    $(".notify-bar").slideUp();
  },5000)
});
</script>';
 }
} else {
	echo $locale['ESHPCHK185'];
  }
 }
}

echo "".$locale['ESHPCHK176']." ".$discount." ";  if ($cuponexcluded) { echo "| <a href='javascript:;' onclick='showexltab(); return false;'>".$cuponexcluded." Item(s) </a> ".$locale['ESHPCHK178'].""; } echo "<br />";
echo "".$locale['ESHPCHK132']."".$paymentsurcharge." ".$eshop_settings['eshop_currency']." <br />";
if ($eshop_settings['eshop_freeshipsum'] !=0) { 
echo "".$locale['ESHPCHK133']."".($eshop_settings['eshop_freeshipsum'] <= $totalincvat ? "<s>$shippingtotal</s>" : "$shippingtotal")." ".$eshop_settings['eshop_currency']." <br />";
if ($eshop_settings['eshop_freeshipsum'] <= $totalincvat) { $shippingtotal = ""; } 
} else {
echo "".$locale['ESHPCHK133']." ".$shippingtotal." ".$eshop_settings['eshop_currency']." <br />";
}
$total = $totalincvat-$discalc+$shippingtotal+$paymentsurcharge;
echo "".$locale['ESHPCHK134']."".number_format($total, 2)." ".$eshop_settings['eshop_currency']."";
}
