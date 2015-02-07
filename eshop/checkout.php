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

//buildeshopheader();

//convert guest shopping to member if they login.
if (iMEMBER) {
$usercartchk = dbarray(dbquery("SELECT puid FROM ".DB_ESHOP_CART." WHERE puid = '".$_SERVER['REMOTE_ADDR']."' LIMIT 0,1"));
if ($usercartchk['puid']) {
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

echo "<tr>
<td align='left' style='width:100px;'> ".$locale['ESHPCHK116']."</td>
<td align='left' style='padding-top:10px;'><textarea name='message' cols='23' rows='4' class='textbox span2'></textarea></td>
</tr>";

echo "<tr>
<td align='center' colspan='2'><br /> ".$locale['ESHPCHK117']." <label><span style='color:#ff0000;display:inline !important;'>*</span> ";
echo "<input type='checkbox' name='agreement' value='1' /> <a class='terms' href='#terms_content' style='font-size:12px !important; vertical-align:middle !important;'> ".$locale['ESHPCHK119']."</a></label></td>";
echo "</tr>";

echo "<tr><td class='tbl' align='center' colspan='2' style='padding-top:20px;'><span style='color:#ff0000;'>*</span> ".$locale['ESHPCHK118']."</td>";
echo "</tr>";
echo "</table></fieldset>";


//Coupon system
if ($settings['eshop_coupons'] == "1") {
echo "<div style='float:left;margin-top:5px;padding:3px;'>";
echo"<fieldset style='width:100%;padding:2px;'><legend style='width:90% !important;'>&nbsp; ".$locale['ESHPCHK170']." &nbsp;</legend>";
echo "<table width='290' height='100%' align='center' cellspacing='0' cellpadding='2' border='0'>";
echo "<tr><td class='tbl' align='center'><input type='text' name='cupon' id='cupon' value='".$locale['ESHPCHK171']."' onblur=\"if(this.value=='') this.value='".$locale['ESHPCHK171']."';\" onfocus=\"if(this.value=='".$locale['ESHPCHK171']."') this.value='';\" class='textbox' style='width:150px;' onKeyDown=\"textCounter(document.inputform.cupon,document.inputform.remLen1,15)\" onKeyUp=\"textCounter(document.inputform.cupon,document.inputform.remLen1,15)\" /> ".$locale['ESHPCHK175']." <input readonly type='text' class='textbox' name='remLen1' style='width:30px;' value='15' /> <br /><a class='button' href='javascript:;' onclick='javascript:cuponcheck(); return false;'>".$locale['ESHPCHK172']."</a></td></tr>";
echo "</table></fieldset></div>";
}

echo "</div>";

echo "<div style='display:none'><div id='terms_content' style='padding:10px;text-align:left'>";
echo stripslashes(nl2br($settings['eshop_terms']));
echo "</div></div>";

//Each payment option
echo "<div style='float:right;margin-top:5px;padding:1px;'>";
echo"<fieldset style='width:100%;padding:2px;'><legend style='width:90% !important;'>&nbsp; ".$locale['ESHPCHK120']." &nbsp;</legend>";
echo "<table width='350' height='100%' align='center' cellspacing='0' cellpadding='2' border='0'>";
  
$result = dbquery("SELECT * FROM ".DB_ESHOP_PAYMENTS."");
$rows = dbrows($result);

if ($rows != 0) {
$result = dbquery("SELECT * FROM ".DB_ESHOP_PAYMENTS." WHERE active='1' ORDER BY pid ASC");
while ($data = dbarray($result)) {
      echo "<tr><td class='tbl' align='left'  valign='middle' width='5%'><input type='radio' name='paymethod' id='payment_".$data['pid']."' value='".$data['pid']."'  onclick='javascript:payment(".$data['pid'].");' /></td>
      <td class='tbl' align='left' width='20%'><img style='width:40px; height:40px;' src='".SHOP."paymentimgs/".$data['image']."' border='0' alt='' /></td>
	  <td class='tbl' align='left' width='50%'>".$data['method']." <a href='javascript:;' class='info'><span>".nl2br($data['description'])."</span><img src='".SHOP."img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a></td>
	  <td class='tbl' align='left' width='30%'>".$locale['ESHPCHK121']." <br /> ".$data['surcharge']." ".$settings['eshop_currency']."</td>
	  </tr>";
echo "<tr><td><div style='margin-top:5px;'></div></td></tr>";
 }

} else { 
	echo $locale['ESHPCHK122']; 
}
echo "</table></fieldset></div>";


echo "<div style='float:right;margin-top:5px;padding:3px;'>";
echo"<fieldset style='width:100%;padding:2px;'><legend style='width:90% !important;'>&nbsp; ".$locale['ESHPCHK127']." &nbsp;</legend>";
echo "<table width='350' height='100%' align='center' cellspacing='0' cellpadding='2' border='0'>";
echo "<tr><td class='tbl' align='left'>
".$locale['ESHPCHK128']." <a href='javascript:;' onclick='showordertab(); return false;'>".$items['count']." ".$locale['ESHPCHK129']."</a>".($settings['eshop_vat_default'] =="1" ? "".number_format($totalincvat, 2)."" : "".number_format($sum['totals'], 2)."")."  ".$settings['eshop_currency']."<br />
".$locale['ESHPCHK130']."".$settings['eshop_vat']."% : ( ".number_format($vat, 2)."  ".($settings['eshop_vat_default'] == "1" ? $locale['ESHPCHK160'] : $locale['ESHPCHK161'])." )<br />";
if ($weight['weight']) {
echo "".$locale['ESHPCHK131']."".number_format($weight['weight'], 2)." ".$settings['eshop_weightscale']." <br />";
}
echo "<div id='subtotal'>";
echo "".$locale['ESHPCHK176']." 0<br />";
echo "".$locale['ESHPCHK132']." 0<br />";
echo "".$locale['ESHPCHK133']." ".($settings['eshop_freeshipsum'] > 0 ? $locale['ESHPCHK188'] : 0)."<br />";
echo "".$locale['ESHPCHK134']."".number_format($totalincvat, 2)." ".$settings['eshop_currency']."";
echo "</div></td></tr>";
echo "</table></fieldset></div>";
echo "<div class='clear'></div>";
echo "<br /><center><input type='submit' name='checkout' value='".$locale['ESHPCHK135']."' class='button' /></center></form></div>";

echo '<script type="text/javascript">
//<![CDATA[
function showexltab(){
$("#exltab").animate({"height": "toggle"}, { duration: 500 });
}
function showordertab(){
$("#ordertab").animate({"height": "toggle"}, { duration: 500 });
}
//]]>
</script>';

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