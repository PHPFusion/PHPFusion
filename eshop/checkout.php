<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: checkout.php
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

include LOCALE.LOCALESET."eshop.php";

add_to_title($locale['ESHPCHK100']);
opentable($locale['ESHPCHK100']);

if (iMEMBER) { $username = $userdata['user_id']; } else { $username = $_SERVER['REMOTE_ADDR']; }
//convert guest shopping to member if they login.
if (iMEMBER) {
	$cart_check = dbarray(dbquery("SELECT puid FROM ".DB_ESHOP_CART." WHERE puid = '".$_SERVER['REMOTE_ADDR']."' LIMIT 0,1"));
	if ($cart_check['puid']) {
		dbquery("UPDATE ".DB_ESHOP_CART." SET puid = '".$userdata['user_id']."' WHERE puid = '".$_SERVER['REMOTE_ADDR']."'");
 	}
}

$firstname = "";
$lastname = "";
$dob = "";
$country_code = "";
$region = "";
$city = "";
$address = "";
$address2 = "";
$postcode = "";
$phone = "";
$fax = "";
$email = "";

if (isset($_POST['checkout'])) {
$result = dbquery("SELECT * FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC");
if (dbrows($result) != 0) {
	saveorder();
	redirect(SHOP."checkedout.php");
} else {
	redirect(SHOP."eshop.php");
 }


} else {

$weight = dbarray(dbquery("SELECT sum(cweight*cqty) as weight FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC"));



if (iMEMBER) {
$cdata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_CUSTOMERS." WHERE cuid = '".$userdata['user_id']."'"));
if ($cdata) {
	$firstname = $cdata['cfirstname'];
	$lastname = $cdata['clastname'];
	$dob = $cdata['cdob'];
	$country_code = $cdata['ccountry_code'];
	$region = $cdata['cregion'];
	$city = $cdata['ccity'];
	$address = $cdata['caddress'];
	$address2 = $cdata['caddress2'];
	$postcode = $cdata['cpostcode'];
	$phone = $cdata['cphone'];
	$fax = $cdata['cfax'];
	$email = $cdata['cemail'];
 }
} else { 
echo "<table width='100%' align='center' cellspacing='0' cellpadding='0' border='0'><tr>
<td class='tbl2' align='center' colspan='2' style='width:100%;padding-top:10px;font-size:15px;'>".$locale['ESHPCHK101']."</td>
</tr></table>";
}

echo "<div style='width:685px !important;' class='center'><form name='inputform' method='post' action='".($settings['site_seo'] ? FUSION_ROOT : '').SHOP."checkout.php' enctype='multipart/form-data' onSubmit='return ValidateForm(this);'>";
echo "<div style='float:left;margin-top:5px;padding:1px;'>";
echo"<fieldset style='width:100%;padding:2px;'><legend style='width:90% !important;'>&nbsp;".$locale['ESHPCHK102']."</legend>";
echo "<table width='290' align='center' cellspacing='2' cellpadding='2' border='0'>";
echo "<tr>";

//All ordered items list
echo "<div id='ordertab'>";
echo "<div class='tbl' align='left' style='margin:10px;padding:5px;'><b>".$locale['ESHPCHK181']."</b></div>\n";
echo "<div class='clear'></div>";

$result = dbquery("SELECT * FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC");

if (dbrows($result) != 0) {
$counter = 0; 
echo "<table align='center' width='100%' cellpadding='2' cellspacing='0' class='eshptable'><tr>
	<td class='tbl2' width='1%' align='center' colspan='2'><b>".$locale['ESHPC102']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC103']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC104']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC105']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC106']."</b></td>
</tr><tr>\n";

while ($data = dbarray($result)) {
if ($counter != 0 && ($counter % 1 == 0)) echo "</tr>\n<tr>\n";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>&nbsp;&nbsp;<a href='".SHOP."eshop.php?product=".$data['prid']."'><img src='".($data['cimage'] ? SHOP."pictures/".$data['cimage'] : '')."' alt='' width='40' border='0' /></a></td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>".$data['citem']."</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>".getcolorname($data['cclr'])."</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>"; if ($data['cdynt'] || $data['cdyn']) { echo "".$data['cdynt']." : ".$data['cdyn'].""; } echo "</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>".$data['cqty']."</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>".$data['cprice']."</td>";
	$counter++;
}
echo "</tr>\n</table>\n";
}
echo "<div style='float:left;margin-top:5px;padding:5px;'>&nbsp; <a href='".SHOP."cart.php' title='cart' class='".($settings['eshop_cart_color'] =="default" ? "button" : "eshpbutton ".$settings['eshop_cart_color']."")."'>".$locale['ESHPF105']."</a></div>";
echo "<div style='float:right;margin-top:5px;padding:5px;'><a class='".($settings['eshop_return_color'] =="default" ? "button" : "eshpbutton ".$settings['eshop_return_color']."")."' href='javascript:;' onclick='showordertab(); return false;'>".$locale['ESHPCHK180']."</a> &nbsp; </div>";
echo "</div>";
echo "<div class='clear'></div>";

//Excluded items list
echo "<div id='exltab'>";
echo "<div class='tbl2' align='left' style='margin:10px;padding:5px;'><b>".$locale['ESHPCHK182']."</b></div>\n";
echo "<div class='clear'></div>";

if ($settings['eshop_coupons'] == "1") {
$result = dbquery("SELECT * FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' AND ccupons='0' ORDER BY tid ASC");
if (dbrows($result) != 0) {
$counter = 0; 
echo "<table align='center' width='100%' cellpadding='2' cellspacing='0' class='eshptable'><tr>
	<td class='tbl2' width='1%' align='center' colspan='2'><b>".$locale['ESHPC102']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC103']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC104']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC105']."</b></td>
	<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPC106']."</b></td>
</tr><tr>\n";

while ($data = dbarray($result)) {
if ($counter != 0 && ($counter % 1 == 0)) echo "</tr>\n<tr>\n";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>&nbsp;&nbsp;<a href='".SHOP."eshop.php?product=".$data['prid']."'><img src='".($data['cimage'] ? "".checkeShpImageExists(SHOP."pictures/".$data['cimage']."")."" : "".SHOP."img/nopic_thumb.gif")."' alt='' width='40' border='0' /></a></td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>".$data['citem']."</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>".getcolorname($data['cclr'])."</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>"; if ($data['cdynt'] || $data['cdyn']) { echo "".$data['cdynt']." : ".$data['cdyn'].""; } echo "</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>".$data['cqty']."</td>";
	echo "<td class='tbl' align='center' valign='middle' width='1%'>".$data['cprice']."</td>";
	$counter++;
}
echo "</tr>\n</table>\n";
}
}
echo "<div style='float:left;margin-top:5px;padding:5px;'>&nbsp; <a href='".SHOP."cart.php' title='cart' class='".($settings['eshop_cart_color'] =="default" ? "button" : "eshpbutton ".$settings['eshop_cart_color']."")."'>".$locale['ESHPF105']."</a></div>";
echo "<div style='float:right;margin-top:5px;padding:5px;'><a class='".($settings['eshop_return_color'] =="default" ? "button" : "eshpbutton ".$settings['eshop_return_color']."")."' href='javascript:;' onclick='showexltab(); return false;'>".$locale['ESHPCHK180']."</a> &nbsp; </div>";
echo "</div>";
echo "<div class='clear'></div>";

echo "<script type='text/javascript'>
function textCounter(field,cntfield,maxlimit) {
if (field.value.length > maxlimit) 
field.value = field.value.substring(0, maxlimit);
else
cntfield.value = maxlimit - field.value.length;
}

function ValidateForm(frm) {
var pcheck = $('input[name=paymethod]');
var paycheck = pcheck.filter(':checked').val();

var scheck = $('input[name=shipping]');
var shipcheck = scheck.filter(':checked').val();

	if (!paycheck) {
		alert('".$locale['ESHPCHK136']."');
		return false;
	}

	if (!shipcheck) {
		alert('".$locale['ESHPCHK137']."');
		return false;
	}
	
	if (paycheck == '1') {
		if(frm.dob.value=='') {
			alert('".$locale['ESHPCHK138']."');
			return false;
		}
	}
	
	var acheck = $('input[name=agreement]');
    var agreementcheck = acheck.filter(':checked').val();

	if (!agreementcheck) {
		alert('".$locale['ESHPCHK189']."');
		return false;
	}
	
	if(frm.firstname.value=='') {
		alert('".$locale['ESHPCHK139']."');
		return false;
	}

	if(frm.lastname.value=='') {
		alert('".$locale['ESHPCHK140']."');
		return false;
	}

	if(frm.country_code.value=='') {
		alert('".$locale['ESHPCHK141']."');
		return false;
	}

	if(frm.region.value=='') {
		alert('".$locale['ESHPCHK142']."');
		return false;
	}

	if(frm.city.value=='') {
		alert('".$locale['ESHPCHK143']."');
		return false;
	}

	if(frm.address.value=='') {
		alert('".$locale['ESHPCHK144']."');
		return false;
	}

	if(frm.postcode.value=='') {
		alert('".$locale['ESHPCHK145']."');
		return false;
	}

	if(frm.email.value=='') {
		alert('".$locale['ESHPCHK146']."');
		return false;
	}
}

</script>";

//} else {
//echo "<br /><div class='admin-message'>".$locale['ESHPCHK147']."</div>";
//}

echo "<div class='clear'></div>";
echo "<div style='float:left;margin-top:15px;padding:10px;'><a class='".($settings['eshop_return_color'] =="default" ? "button" : "eshpbutton ".$settings['eshop_return_color']."")."' href='javascript:;' onclick='javascript:history.back(-1); return false;'>&laquo; ".$locale['ESHP030']."</a> &nbsp;&nbsp; </div>";
echo "<div class='clear'></div>";
}

closetable();
require_once THEMES."templates/footer.php";
?>