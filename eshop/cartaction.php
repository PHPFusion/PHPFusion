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
header("Cache-Control: no-cache");
header("Pragma: nocache");
header("Content-type: text/html; charset=UTF-8");

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
if (isset($_GET['tid']) && !isnum($_GET['tid'])) die("Denied");
if (isset($_GET['id']) && !isnum($_GET['id'])) die("Denied");
if (isset($_GET['qty']) && !isnum($_GET['qty'])) die("Denied");
if (isset($_GET['color']) && !isnum($_GET['color'])) die("Denied");
if (isset($_GET['cupon']) && !isnum($_GET['cupon'])) die("Denied");
if (isset($_GET['delete']) && !isnum($_GET['delete'])) die("Denied");
if (isset($_GET['plusone']) && !isnum($_GET['plusone'])) die("Denied");
if (isset($_GET['minusone']) && !isnum($_GET['minusone'])) die("Denied");
$username = "";

include LOCALE.LOCALESET."eshop.php";
include LOCALE.LOCALESET."colors.php";

if (iMEMBER) { $username = $userdata['user_id']; } else { $username = $_SERVER['REMOTE_ADDR']; }
$product = "";
$qty = "";
$color = "";
$dync = "";
$citem = "";
$cimage = "";
$cdynct = "";
$cprice = "";
$weight = "";
$artno = "";
$cupon = "";

if (isset($_GET['id'])) { $product = stripinput($_GET['id']); }
if (isset($_GET['qty'])) { $qty = stripinput($_GET['qty']); }
if (isset($_GET['color'])) { $color = stripinput($_GET['color']); }
if (isset($_GET['dync'])) { if (!preg_match("/^[\w-0-9A-Z\xe6\xc6\xf8\xd8\xe5\xc5\xf6\xd6\xe4\xc4\/._\´\"\'\-@\s]+$/i",  $_GET['dync'])) { die("Denied dynamic"); exit; } $dync = stripinput($_GET['dync']); }
if (isset($_GET['prod'])) { if (!preg_match("/^[\w\W-0-9A-Z\xe6\xc6\xf8\xd8\xe5\xc5\xf6\xd6\xe4\xc4\/. _\´\"\'\-@\s\S]+$/i", $_GET['prod'])) { die("Denied product"); exit; } $citem = stripinput($_GET['prod']); }
if (isset($_GET['image'])) { if (!preg_match("/^[\w-0-9A-Z\xe6\xc6\xf8\xd8\xe5\xc5\xf6\xd6\xe4\xc4\/._@\s]+$/i", $_GET['image'])) { die("Denied image"); exit; } $cimage = stripinput($_GET['image']); }
if (isset($_GET['dynct'])) { if (!preg_match("/^[\w-0-9A-Z\xe6\xc6\xf8\xd8\xe5\xc5\xf6\xd6\xe4\xc4\/._\´\"\'\-@\s]+$/i",  $_GET['dynct'])) { die("Denied dynamic"); exit; } $cdynct = stripinput($_GET['dynct']); }
if (isset($_GET['cprice'])) { if (!preg_match("/^[0-9.@\s]+$/i", $_GET['cprice'])) { die("Denied price"); exit; } $cprice = stripinput($_GET['cprice']); }
if (isset($_GET['weight'])) { if (!preg_match("/^[0-9.@\s]+$/i", $_GET['weight'])) { die("Denied weight"); exit; } $weight = stripinput($_GET['weight']); }
if (isset($_GET['artno'])) { if (!preg_match("/^[\w-0-9A-Z\xe6\xc6\xf8\xd8\xe5\xc5\xf6\xd6\xe4\xc4\/._@\s]+$/i", $_GET['artno'])) { die("Denied Artno"); exit; } $artno = stripinput($_GET['artno']); }
if (isset($_GET['cupon'])) { $cupon = stripinput($_GET['cupon']); }

if (isset($_GET['delete']) || isset($_GET['plusone']) || isset($_GET['minusone'])) {

if (isset($_GET['delete'])) {
	$result = dbquery("DELETE FROM ".DB_ESHOP_CART." WHERE puid ='".$username."' AND tid='".$_GET['delete']."'");
}

if (isset($_GET['plusone'])) {
	$result = dbquery("UPDATE ".DB_ESHOP_CART." SET cqty=cqty+1 WHERE puid ='".$username."' AND tid = '".$_GET['plusone']."'");
}

if (isset($_GET['minusone'])) {
	$result = dbquery("UPDATE ".DB_ESHOP_CART." SET cqty=cqty-1 WHERE puid ='".$username."' AND tid = '".$_GET['minusone']."'");
}

function checkeShpImageExists($image_file) {
if(file_exists($image_file)) {
	return $image_file;
	} else {
	return BASEDIR."eshop/img/nopic_thumb.gif";
  }
}

function getcolorname($id){
global $locale;
$id = "{$locale['color_'.$id]}";
return $id;
}

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

	echo "<td class='tbl' align='center' valign='middle' width='1%'>&nbsp;&nbsp;<a href='".BASEDIR."eshop/eshop.php?product=".$data['prid']."'><img src='".($data['cimage'] ? "".checkeShpImageExists(BASEDIR."eshop/pictures/".$data['cimage']."")."" : "".BASEDIR."eshop/img/nopic_thumb.gif")."' alt='' width='40' border='0' /></a></td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'><input type='hidden' value='".$data['citem']."' id='prod_".$data['tid']."' name='prod_".$data['tid']."' />".$data['citem']."</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>".getcolorname($data['cclr'])."</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>"; if ($data['cdynt'] || $data['cdyn']) { echo "".$data['cdynt']." : ".$data['cdyn'].""; } echo "</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'><a href='javascript:;' onclick='javascript:minusonecart(".$data['tid'].")'><img src='".BASEDIR."eshop/img/minus.png' border='0' alt='' style='vertical-align:middle;' /></a><input type='text' name='quantity_".$data['tid']."' id='quantity_".$data['tid']."' value='".$data['cqty']."' class='textbox' readonly style='width:25px;' /><a href='javascript:;' onclick='javascript:plusonecart(".$data['tid'].")'><img src='".BASEDIR."eshop/img/plus.png' border='0' alt='' style='vertical-align:middle;' /></a></div></td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>".$data['cprice']."</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'><a href='javascript:;' onclick='javascript:delcitem(".$data['tid'].")' title='".$locale['ESHPC107']."'><img src='".BASEDIR."eshop/img/remove.png' border='0' alt='".$locale['ESHPC107']."' /> </a>";
	$counter++;
}

echo "</tr>\n</table>\n";

$weight = dbarray(dbquery("SELECT sum(cweight*cqty) as weight FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC"));
$items = dbarray(dbquery("SELECT sum(cqty) as count FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC"));
$sum = dbarray(dbquery("SELECT sum(cprice*cqty) as totals FROM ".DB_ESHOP_CART." WHERE puid = '".$username."'"));

echo "<div style='float:left;margin-top:10px;'><a class='eshpbutton red' href='".BASEDIR."eshop/eshop.php?clearcart'>".$locale['ESHPC119']."</a></div>";

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
	echo "".$locale['ESHPC112']." ".number_format($totalincvat, 2)." ".$settings['eshop_currency']."<br />";
}

if ($weight['weight']) {
	echo "<div style='float:right;margin-top:0px;'>".$locale['ESHPC113']."".number_format($weight['weight'], 2)." ".$settings['eshop_weightscale']."</div>";
}

echo "</div><div style='clear:both;'></div>";

} else {
	echo "<div class='admin-message'>".$locale['ESHPC114']."</div>";
}

} else  {
//add new items submited.
$dcheck = dbquery("SELECT * FROM ".DB_ESHOP_CART." WHERE 
puid = '".$username."' AND  
artno = '".$artno."' AND
prid = '".$product."' AND
citem = '".$citem."' AND
cclr = '".$color."' AND
cdyn = '".$dync."' AND
cdynt = '".$cdynct."' AND
cprice = '".$cprice."'");
//check for duplicates.

if (dbrows($dcheck) != 0) {
$result = dbquery("UPDATE ".DB_ESHOP_CART." SET cqty=cqty+".$qty." WHERE 
puid ='".$username."' AND
artno = '".$artno."' AND
prid = '".$product."' AND
citem = '".$citem."' AND
cclr = '".$color."' AND
cdyn = '".$dync."' AND
cdynt = '".$cdynct."' AND
cprice = '".$cprice."'");
       
echo '<script type="text/javascript">
$(document).ready(function() {
	$(".notify-bar").html(" '.$citem.' '.$locale['ESHPC116'].' ").slideDown();
    setTimeout(function () {
    $(".notify-bar").slideUp();
  },5000)
});
</script>';

} else {
$result = dbquery("INSERT INTO ".DB_ESHOP_CART." VALUES('', '".$username."', '".$product."' ,'".$artno."', '".$citem."' , '".$cimage."', '".$qty."' , '".$color."' , '".$dync."','".$cdynct."','".$cprice."','".$weight."','".$cupon."','".time()."')");
echo '<script type="text/javascript">
$(document).ready(function() {
	$(".notify-bar").html(" '.$citem.' '.$locale['ESHPC117'].' ").slideDown();
    setTimeout(function () {
    $(".notify-bar").slideUp();
  },5000)
});
</script>';
}
$items = dbarray(dbquery("SELECT sum(cqty) as count FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC"));
$sum = dbarray(dbquery("SELECT sum(cprice*cqty) as totals FROM ".DB_ESHOP_CART." WHERE puid = '".$username."'"));
echo "".($items['count'])." ".$locale['ESHPC118']." ".number_format($sum['totals'], 2)." ".$settings['eshop_currency']."";
} // delete checkpoint

//Clean it up
$result = dbquery ("DELETE FROM ".DB_ESHOP_CART."  WHERE tid = '0' OR prid = '0' OR citem = '';");
}
?>