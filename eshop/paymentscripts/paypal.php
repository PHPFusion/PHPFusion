<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: paypal.php
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

echo "<center><div style = 'font-family : Verdana, Arial, Helvetica, sans-serif;
	text-align: center;
    margin-top : 20px;
	width: 550px;
	font-size: 20px;
	font-weight: bold;
	padding: 5px 0 5px 20px;
	margin-bottom: 5px;
	color: #05B;
	border: 1px solid #9CC0EE;
	-webkit-border-bottom-right-radius: 5px;
	-webkit-border-bottom-left-radius: 5px;
	-moz-border-radius-bottomright: 5px;
	-moz-border-radius-bottomleft: 5px;
	border-bottom-right-radius: 5px;
	border-bottom-left-radius: 5px;
	-webkit-border-top-left-radius: 5px;
	-webkit-border-top-right-radius: 5px;
	-moz-border-radius-topleft: 5px;
	-moz-border-radius-topright: 5px;
	border-top-left-radius: 5px;
	border-top-right-radius: 5px;';> ".$locale['ESHPPP100']." </div></center><br /><br />";


echo "<center><div style = 'font-family : Verdana, Arial, Helvetica, sans-serif;
	text-align: center;
	width: 350px;
	font-size: 11px;
	font-weight: bold;
	padding: 5px 0 5px 20px;
	margin-bottom: 5px;
	color: #05B;
	border: 1px solid #9CC0EE;
	-webkit-border-bottom-right-radius: 5px;
	-webkit-border-bottom-left-radius: 5px;
	-moz-border-radius-bottomright: 5px;
	-moz-border-radius-bottomleft: 5px;
	border-bottom-right-radius: 5px;
	border-bottom-left-radius: 5px;
	-webkit-border-top-left-radius: 5px;
	-webkit-border-top-right-radius: 5px;
	-moz-border-radius-topleft: 5px;
	-moz-border-radius-topright: 5px;
	border-top-left-radius: 5px;
	border-top-right-radius: 5px;';> ".$locale['ESHPPP101']." <span id='counter'>0</span> seconds</div></center>";

echo "
<script type='text/javascript'>
function countdown() {
    var i = document.getElementById('counter');
    i.innerHTML = parseInt(i.innerHTML)+1;
}
setInterval(function(){ countdown(); },1000);
</script>";
//If we do IPN checks, it will be scrapped first version.
//<input type="hidden" name="notify_url" value="'.$settings['siteurl'].'eshop/ipnverify.php" />
echo '
<form name="cart" id="cart" action="https://www.paypal.com/cgi-bin/webscr" method="post" />
<input type="hidden" name="cmd" value="_cart" readonly /> 
<input type="hidden" name="upload" value="1" readonly />
<input type="hidden" name="business" value="'.$settings['eshop_ppmail'].'" readonly />
<input type="hidden" name="page_style" value="PayPal" readonly />
<input type="hidden" name="return" value="'.$settings['siteurl'].'eshop/'.$settings['eshop_returnpage'].'" readonly />
<input type="hidden" name="currency_code" value="'.$settings['eshop_currency'].'" readonly />
<input type="hidden" name="cancel_return" value="'.$settings['siteurl'].'eshop/eshop.php" readonly />';
if ($settings['eshop_ipn']) {
echo '<input type="hidden" name="notify_url" value="' . $settings['siteurl'] . 'eshop/ipn_verify.php" readonly />';
}
$uodata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE ouid = '".$username."' ORDER BY oid DESC LIMIT 0,1"));
$weight = dbarray(dbquery("SELECT sum(cweight*cqty) as weight FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC"));
$shipping = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE active='1' AND sid='".$uodata['oshipmethod']."' ORDER BY cid,sid ASC"));
$shippingsurcharge = $shipping['weightcost'];
$shippinginitial = $shipping['initialcost'];
$shippingsurcharge = $shippingsurcharge*$weight['weight'];
$shippingtotal = $shippingsurcharge+$shippinginitial;

    $result = dbquery("SELECT * FROM ".DB_ESHOP_CART." WHERE puid = '".$username."'");
       $i=1;
       while ($data = dbarray($result)) {
        echo '<input type="hidden" name="item_name_'.$i.'" value="'.$data['citem'].'" readonly />
        <input type="hidden" name="amount_'.$i.'" value="'.$data['cprice'].'" readonly />
		<input type="hidden" name="quantity_'.$i.'" value="'.$data['cqty'].'" readonly />';
		if ($data['cclr']) {
		echo '<input type="hidden" name="on1_'.$i.'" value="'.$locale['ESHPC103'].'" readonly />
		<input type="hidden" name="os1_'.$i.'" value="'.getcolorname($data['cclr']).'" readonly />';
		}
		if ($data['cdynt'] || $data['cdyn']) { 
		echo '<input type="hidden" name="on0_'.$i.'" value="'.$data['cdynt'].'" readonly /> 
		<input type="hidden" name="os0_'.$i.'" value="'.$data['cdyn'].'" readonly />';
		} 
	$i++;
    }
	   
$discount = preg_replace("/[^0-9]/","",''.$uodata['odiscount'].'');
echo '<input type="hidden" name="discount_amount_cart" value="'.$discount.'" readonly />';

if ($settings['eshop_freeshipsum'] !=0) { 
if ($settings['eshop_freeshipsum'] <= $totalincvat) { $shippingtotal = "0"; } 
}
echo '<input type="hidden" name="shipping_1" value="'.$shippingtotal.'" />';
if ($settings['eshop_vat_default'] == "0") {
echo '<input type="hidden" name="tax_cart" value="'.$uodata['ovat'].'" />';	
} else {
echo '<input type="hidden" name="tax_cart" value="0" />';	
}
echo '<br /><center><input name="mySubmit" type="submit" id="mySubmit" value="Click here to continue if you are not automatically redirected" /></center>';
echo "</form>";
echo '<script type="text/javascript">';
echo 'window.onload = function(){document.cart.submit()}';
echo '</script>';

?>