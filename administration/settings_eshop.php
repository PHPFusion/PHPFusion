<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop_settings.php
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
if (!checkrights("ESHP") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { die("Acces Denied"); }
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."eshop.php";
add_to_head("<link rel='stylesheet' type='text/css' href='".THEMES."templates/global/css/eshop.css' />");

echo '<script type="text/javascript">

$(function() {
    $("#colorselector").change(function(){
      $(".colors").hide();
      $("#" + $(this).val()).show();
   });
});

$(function() {
    $("#colorselector2").change(function(){
      $(".colors2").hide();
      $("#2" + $(this).val()).show();
   });
});

$(function() {
    $("#colorselector3").change(function(){
      $(".colors3").hide();
      $("#3" + $(this).val()).show();
   });
});

$(function() {
    $("#colorselector4").change(function(){
      $(".colors4").hide();
      $("#4" + $(this).val()).show();
   });
});

$(function() {
    $("#colorselector5").change(function(){
      $(".colors5").hide();
      $("#5" + $(this).val()).show();
   });
});

$(function() {
    $("#colorselector6").change(function(){
      $(".colors6").hide();
      $("#6" + $(this).val()).show();
   });
});


$(document).ready(function() {
var showcolorbutton = $( "#colorselector option:selected" ).val();
$("#" + showcolorbutton).show();

var showcolorbutton2 = $( "#colorselector2 option:selected" ).val();
$("#2" + showcolorbutton2).show();

var showcolorbutton3 = $( "#colorselector3 option:selected" ).val();
$("#3" + showcolorbutton3).show();

var showcolorbutton4 = $( "#colorselector4 option:selected" ).val();
$("#4" + showcolorbutton4).show();

var showcolorbutton5 = $( "#colorselector5 option:selected" ).val();
$("#5" + showcolorbutton5).show();

var showcolorbutton6 = $( "#colorselector6 option:selected" ).val();
$("#6" + showcolorbutton6).show();
      
});
</script>';
opentable("settings");
if (isset($_POST['update_settings'])) {
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['cats'])."' WHERE settings_name='eshop_cats'");

$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['cat_disp'])."' WHERE settings_name='eshop_cat_disp'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['nopp'])."' WHERE settings_name='eshop_nopp'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['noppf'])."' WHERE settings_name='eshop_noppf'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['shopmode'])."' WHERE settings_name='eshop_shopmode'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['returnpage'])."' WHERE settings_name='eshop_returnpage'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['target'])."' WHERE settings_name='eshop_target'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['folderlink'])."' WHERE settings_name='eshop_folderlink'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['selection'])."' WHERE settings_name='eshop_selection'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['cookies'])."' WHERE settings_name='eshop_cookies'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['bclines'])."' WHERE settings_name='eshop_bclines'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['icons'])."' WHERE settings_name='eshop_icons'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['statustext'])."' WHERE settings_name='eshop_statustext'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['closesamelevel'])."' WHERE settings_name='eshop_closesamelevel'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['inorder'])."' WHERE settings_name='eshop_inorder'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['ratios'])."' WHERE settings_name='eshop_ratios'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['idisp_h'])."' WHERE settings_name='eshop_idisp_h'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['idisp_w'])."' WHERE settings_name='eshop_idisp_w'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['idisp_h2'])."' WHERE settings_name='eshop_idisp_h2'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['idisp_w2'])."' WHERE settings_name='eshop_idisp_w2'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['ipr'])."' WHERE settings_name='eshop_ipr'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['ppmail'])."' WHERE settings_name='eshop_ppmail'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['catimg_w'])."' WHERE settings_name='eshop_catimg_w'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['catimg_h'])."' WHERE settings_name='eshop_catimg_h'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['image_w'])."' WHERE settings_name='eshop_image_w'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['image_h'])."' WHERE settings_name='eshop_image_h'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['image_b'])."' WHERE settings_name='eshop_image_b'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['image_tw'])."' WHERE settings_name='eshop_image_tw'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['image_th'])."' WHERE settings_name='eshop_image_th'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['image_t2w'])."' WHERE settings_name='eshop_image_t2w'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['image_t2h'])."' WHERE settings_name='eshop_image_t2h'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['buynow_color'])."' WHERE settings_name='eshop_buynow_color'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['checkout_color'])."' WHERE settings_name='eshop_checkout_color'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['cart_color'])."' WHERE settings_name='eshop_cart_color'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['addtocart_color'])."' WHERE settings_name='eshop_addtocart_color'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['info_color'])."' WHERE settings_name='eshop_info_color'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['return_color'])."' WHERE settings_name='eshop_return_color'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['pretext'])."' WHERE settings_name='eshop_pretext'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['pretext_w'])."' WHERE settings_name='eshop_pretext_w'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['listprice'])."' WHERE settings_name='eshop_listprice'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['currency'])."' WHERE settings_name='eshop_currency'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['shareing'])."' WHERE settings_name='eshop_shareing'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['weightscale'])."' WHERE settings_name='eshop_weightscale'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['vat'])."' WHERE settings_name='eshop_vat'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['vat_default'])."' WHERE settings_name='eshop_vat_default'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['itembox_w'])."' WHERE settings_name='eshop_itembox_w'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['itembox_h'])."' WHERE settings_name='eshop_itembox_h'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['cipr'])."' WHERE settings_name='eshop_cipr'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['newtime'])."' WHERE settings_name='eshop_newtime'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['coupons'])."' WHERE settings_name='eshop_coupons'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['freeshipsum'])."' WHERE settings_name='eshop_freeshipsum'");
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['terms']))."' WHERE settings_name='eshop_terms'");
echo "<div class='admin-message'>".$locale['ESHP500']."</div>";
}

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}
if (dbrows($result) != 0) {
echo "<form name='optionsform' method='post' action='".FUSION_SELF.$aidlink."&amp;a_page=settings'>

<fieldset style='align:left;width:99%;display:block;float:left;margin-left:2px;margin-right:2px;margin-top:2px;margin-bottom:2px;'>
<legend>&nbsp; <b> ".$locale['ESHP502']." </b> &nbsp;</legend>
<table border='0' cellpadding='0' cellspacing='0' width='100%' class='tbl'>

		<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP551']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='ppmail' value='".$settings2['eshop_ppmail']."' class='textbox' style='width:150px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP552']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP553']."</td>
            <td class='tbl1' width='20%'  align='left'>
	    <input type='text' name='returnpage' value='".$settings2['eshop_returnpage']."' class='textbox' style='width:150px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP554']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP555']."</td>
            <td class='tbl1' width='20%'  align='left'>
	    <input type='text' name='vat' value='".$settings2['eshop_vat']."' class='textbox' style='width:40px;'>%</td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP556']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP557']."</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='vat_default' class='textbox'>
                <option value='1'".($settings2['eshop_vat_default'] == "1" ? " selected" : "").">".$locale['ESHP564']."</option>
                <option value='0'".($settings2['eshop_vat_default'] == "0" ? " selected" : "").">".$locale['ESHP565']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP558']."</span></td>
	</tr>
	
		<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP559']."</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='currency' class='textbox' style='width:150px;'>
    <option value='EUR' ".($settings2['eshop_currency'] == "EUR" ? " selected" : "").">Euro (€)</option>
	<option value='USD' ".($settings2['eshop_currency'] == "USD" ? " selected" : "").">U.S. Dollar ($)</option>
	<option value='GBP' ".($settings2['eshop_currency'] == "GBP" ? " selected" : "").">British Pound (£)</option>
	<option value='CAD' ".($settings2['eshop_currency'] == "CAD" ? " selected" : "").">Canadian Dollar (C $)</option>
	<option value='AUD' ".($settings2['eshop_currency'] == "AUD" ? " selected" : "").">Australian Dollar (A $)</option>
	<option value='JPY' ".($settings2['eshop_currency'] == "JPY" ? " selected" : "").">Japanese Yen (¥)</option>
	<option value='NZD' ".($settings2['eshop_currency'] == "NZD" ? " selected" : "").">New Zealand Dollar ($)</option>
	<option value='CHF' ".($settings2['eshop_currency'] == "CHF" ? " selected" : "").">Swiss Franc</option>
	<option value='HKD' ".($settings2['eshop_currency'] == "HKD" ? " selected" : "").">Hong Kong Dollar ($)</option>
	<option value='SGD' ".($settings2['eshop_currency'] == "SGD" ? " selected" : "").">Singapore Dollar ($)</option>
	<option value='SEK' ".($settings2['eshop_currency'] == "SEK" ? " selected" : "").">Swedish Krona</option>
	<option value='DKK' ".($settings2['eshop_currency'] == "DKK" ? " selected" : "").">Danish Krone</option>
	<option value='PLN' ".($settings2['eshop_currency'] == "PLN" ? " selected" : "").">Polish Zloty</option>
	<option value='NOK' ".($settings2['eshop_currency'] == "NOK" ? " selected" : "").">Norwegian Krone</option>
	<option value='HUF' ".($settings2['eshop_currency'] == "HUF" ? " selected" : "").">Hungarian Forint</option>
	<option value='CZK' ".($settings2['eshop_currency'] == "CZK" ? " selected" : "").">Czech Koruna</option>
	<option value='ILS' ".($settings2['eshop_currency'] == "ILS" ? " selected" : "").">Israeli New Shekel</option>
	<option value='MXN' ".($settings2['eshop_currency'] == "MXN" ? " selected" : "").">Mexican Peso</option>
	<option value='BRL' ".($settings2['eshop_currency'] == "BRL" ? " selected" : "").">Brazilian Real</option>
	<option value='MYR' ".($settings2['eshop_currency'] == "MYR" ? " selected" : "").">Malaysian Ringgit </option>
	<option value='PHP' ".($settings2['eshop_currency'] == "PHP" ? " selected" : "").">Philippine Peso</option>
	<option value='TWD' ".($settings2['eshop_currency'] == "TWD" ? " selected" : "").">New Taiwan Dollar</option>
	<option value='THB' ".($settings2['eshop_currency'] == "THB" ? " selected" : "").">Thai Baht</option>
	<option value='TRY' ".($settings2['eshop_currency'] == "TRY" ? " selected" : "").">Turkish Lira</option>
	</select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP560']."</span></td>
	</tr>
	
	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP837']."</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='newtime' class='textbox'>
                <option value='0'".($settings2['eshop_newtime'] == "0" ? " selected" : "").">".$locale['ESHP839']."</option>
				<option value='86400'".($settings2['eshop_newtime'] == "86400" ? " selected" : "").">".$locale['ESHP840']."</option>
                <option value='259200'".($settings2['eshop_newtime'] == "259200" ? " selected" : "").">".$locale['ESHP841']."</option>
				<option value='432000'".($settings2['eshop_newtime'] == "432000" ? " selected" : "").">".$locale['ESHP842']."</option>
				<option value='604800'".($settings2['eshop_newtime'] == "604800" ? " selected" : "").">".$locale['ESHP843']."</option>
				<option value='1209600'".($settings2['eshop_newtime'] == "1209600" ? " selected" : "").">".$locale['ESHP844']."</option>
				<option value='2419200'".($settings2['eshop_newtime'] == "2419200" ? " selected" : "").">".$locale['ESHP845']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP838']."</span></td>
	</tr>
	
		<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP848']."</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='coupons' class='textbox'>
                <option value='1'".($settings2['eshop_coupons'] == "1" ? " selected" : "").">".$locale['ESHP564']."</option>
                <option value='0'".($settings2['eshop_coupons'] == "0" ? " selected" : "").">".$locale['ESHP565']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP849']."</span></td>
	</tr>
	
	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP846']."</td>
            <td class='tbl1' width='20%'  align='left'>
			<input type='text' name='freeshipsum' value='".$settings2['eshop_freeshipsum']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP847']."</span></td>
	</tr>
	
	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP561']."</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='weightscale' class='textbox'>
                <option value='KG'".($settings2['eshop_weightscale'] == "KG" ? " selected" : "").">".$locale['ESHP566']."</option>
                <option value='LBS'".($settings2['eshop_weightscale'] == "LBS" ? " selected" : "").">".$locale['ESHP567']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP562']."</span></td>
	</tr>
		
	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP563']."</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='shareing' class='textbox'>
                <option value='1'".($settings2['eshop_shareing'] == "1" ? " selected" : "").">".$locale['ESHP504']."</option>
                <option value='0'".($settings2['eshop_shareing'] == "0" ? " selected" : "").">".$locale['ESHP505']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP568']."</span></td>
	</tr>
	
	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP569']."</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='shopmode' class='textbox'>
                <option value='1'".($settings2['eshop_shopmode'] == "1" ? " selected" : "").">".$locale['ESHP504']."</option>
                <option value='0'".($settings2['eshop_shopmode'] == "0" ? " selected" : "").">".$locale['ESHP505']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP570']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP598']."</td>
            <td class='tbl1' width='20%'  align='left'>
			<input type='text' name='itembox_w' value='".$settings2['eshop_itembox_w']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP599']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP600']."</td>
            <td class='tbl1' width='20%'  align='left'>
			<input type='text' name='itembox_h' value='".$settings2['eshop_itembox_h']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP601']."</span></td>
	</tr>

	
    <tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP571']."</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='pretext' class='textbox'>
                <option value='1'".($settings2['eshop_pretext'] == "1" ? " selected" : "").">".$locale['ESHP504']."</option>
                <option value='0'".($settings2['eshop_pretext'] == "0" ? " selected" : "").">".$locale['ESHP505']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP572']."</span></td>
	</tr>
	
	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP573']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='pretext_w' value='".$settings2['eshop_pretext_w']."' class='textbox' style='width:60px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP574']."</span></td>
	</tr>

<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP575']."</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='listprice' class='textbox'>
                <option value='1'".($settings2['eshop_listprice'] == "1" ? " selected" : "").">".$locale['ESHP504']."</option>
                <option value='0'".($settings2['eshop_listprice'] == "0" ? " selected" : "").">".$locale['ESHP505']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP576']."</span></td>
	</tr>
	
	
	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP577']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='noppf' value='".$settings2['eshop_noppf']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP578']."</span></td>
	</tr>

	
	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP592']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='ipr' value='".$settings2['eshop_ipr']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP593']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP515']."</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='ratios' class='textbox'>
                <option value='1'".($settings2['eshop_ratios'] == "1" ? " selected" : "").">".$locale['ESHP504']."</option>
                <option value='0'".($settings2['eshop_ratios'] == "0" ? " selected" : "").">".$locale['ESHP505']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP516']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP519']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='idisp_w' value='".$settings2['eshop_idisp_w']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP520']."</span></td>
	</tr>

	<tr>
	    <td class='tbl1' width='30%' align='left'>".$locale['ESHP517']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='idisp_h' value='".$settings2['eshop_idisp_h']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP518']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP594']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='idisp_w2' value='".$settings2['eshop_idisp_w2']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP595']."</span></td>
	</tr>

	<tr>
	    <td class='tbl1' width='30%' align='left'>".$locale['ESHP596']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='idisp_h2' value='".$settings2['eshop_idisp_h2']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP597'].")</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP537']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='image_w' value='".$settings2['eshop_image_w']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP538']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP539']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='image_h' value='".$settings2['eshop_image_h']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP540']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP541']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='image_b' value='".$settings2['eshop_image_b']."' class='textbox' style='width:50px;'> [".parseByteSize($settings2['eshop_image_b'])."]</td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP542']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP543']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='image_tw' value='".$settings2['eshop_image_tw']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP544']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP545']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='image_th' value='".$settings2['eshop_image_th']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP546']."</span></td>
	</tr>


	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP547']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='image_t2w' value='".$settings2['eshop_image_t2w']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP548']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP549']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='image_t2h' value='".$settings2['eshop_image_t2h']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP550']."</span></td>
	</tr>
	
	<tr>
        <td class='tbl1' width='30%' align='left'>".$locale['ESHP579']."</td>
        <td class='tbl1' width='20%'  align='left'>
	    <select name='buynow_color' id='colorselector' class='textbox'>
	    <option value='default'".($settings2['eshop_buynow_color'] == "default" ? " selected" : "").">".$locale['ESHP580']."</option> 
		<option value='blue'".($settings2['eshop_buynow_color'] == "blue" ? " selected" : "").">".$locale['ESHP581']."</option> 
	    <option value='green'".($settings2['eshop_buynow_color'] == "green" ? " selected" : "").">".$locale['ESHP582']."</option> 
	    <option value='red'".($settings2['eshop_buynow_color'] == "red" ? " selected" : "").">".$locale['ESHP583']."</option> 
	    <option value='magenta'".($settings2['eshop_buynow_color'] == "magenta" ? " selected" : "").">".$locale['ESHP584']."</option> 
	    <option value='orange'".($settings2['eshop_buynow_color'] == "orange" ? " selected" : "").">".$locale['ESHP585']."</option> 
	    <option value='yellow'".($settings2['eshop_buynow_color'] == "yellow" ? " selected" : "").">".$locale['ESHP586']."</option> 
	    </select>
	    </td>
        <td class='tbl1' width='50%' align='left'>
   	    <div id='default' class='colors hide'><a class='button' href='javascript:;'>".$locale['ESHP580']."</a></div>
		<div id='blue' class='colors hide'><a class='eshpbutton blue' href='javascript:;'>".$locale['ESHP581']."</a></div>
  	    <div id='green' class='colors hide'><a class='eshpbutton green' href='javascript:;'>".$locale['ESHP582']."</a></div>
 	    <div id='red' class='colors hide'><a class='eshpbutton red' href='javascript:;'>".$locale['ESHP583']."</a></p></div>
 	    <div id='magenta' class='colors hide'><a class='eshpbutton magenta' href='javascript:;'>".$locale['ESHP584']."</a></div>
 	    <div id='orange' class='colors hide'><a class='eshpbutton orange' href='javascript:;'>".$locale['ESHP585']."</a></div>
 	    <div id='yellow' class='colors hide'><a class='eshpbutton yellow' href='javascript:;'>".$locale['ESHP586']."</a></div>
	    </td></tr>

	<tr>
        <td class='tbl1' width='30%' align='left'>".$locale['ESHP587']."</td>
        <td class='tbl1' width='20%'  align='left'>
	    <select name='checkout_color' id='colorselector2' class='textbox'>
		<option value='default'".($settings2['eshop_checkout_color'] == "default" ? " selected" : "").">".$locale['ESHP580']."</option> 
	    <option value='blue'".($settings2['eshop_checkout_color'] == "blue" ? " selected" : "").">".$locale['ESHP581']."</option> 
	    <option value='green'".($settings2['eshop_checkout_color'] == "green" ? " selected" : "").">".$locale['ESHP582']."</option> 
	    <option value='red'".($settings2['eshop_checkout_color'] == "red" ? " selected" : "").">".$locale['ESHP583']."</option> 
	    <option value='magenta'".($settings2['eshop_checkout_color'] == "magenta" ? " selected" : "").">".$locale['ESHP584']."</option> 
	    <option value='orange'".($settings2['eshop_checkout_color'] == "orange" ? " selected" : "").">".$locale['ESHP585']."</option> 
	    <option value='yellow'".($settings2['eshop_checkout_color'] == "yellow" ? " selected" : "").">".$locale['ESHP586']."</option> 
	    </select>
	    </td>
        <td class='tbl1' width='50%' align='left'>
		<div id='2default' class='colors2 hide'><a class='button' href='javascript:;'>".$locale['ESHP580']."</a></div>
   	    <div id='2blue' class='colors2 hide'><a class='eshpbutton blue' href='javascript:;'>".$locale['ESHP581']."</a></div>
  	    <div id='2green' class='colors2 hide'><a class='eshpbutton green' href='javascript:;'>".$locale['ESHP582']."</a></div>
 	    <div id='2red' class='colors2 hide'><a class='eshpbutton red' href='javascript:;'>".$locale['ESHP583']."</a></p></div>
 	    <div id='2magenta' class='colors2 hide'><a class='eshpbutton magenta' href='javascript:;'>".$locale['ESHP584']."</a></div>
 	    <div id='2orange' class='colors2 hide'><a class='eshpbutton orange' href='javascript:;'>".$locale['ESHP585']."</a></div>
 	    <div id='2yellow' class='colors2 hide'><a class='eshpbutton yellow' href='javascript:;'>".$locale['ESHP586']."</a></div>
	</td></tr>

	<tr>
        <td class='tbl1' width='30%' align='left'>".$locale['ESHP588']."</td>
        <td class='tbl1' width='20%'  align='left'>
	    <select name='cart_color' id='colorselector3' class='textbox'>
	    <option value='default'".($settings2['eshop_cart_color'] == "default" ? " selected" : "").">".$locale['ESHP580']."</option> 
		<option value='blue'".($settings2['eshop_cart_color'] == "blue" ? " selected" : "").">".$locale['ESHP581']."</option> 
	    <option value='green'".($settings2['eshop_cart_color'] == "green" ? " selected" : "").">".$locale['ESHP582']."</option> 
	    <option value='red'".($settings2['eshop_cart_color'] == "red" ? " selected" : "").">".$locale['ESHP583']."</option> 
	    <option value='magenta'".($settings2['eshop_cart_color'] == "magenta" ? " selected" : "").">".$locale['ESHP584']."</option> 
	    <option value='orange'".($settings2['eshop_cart_color'] == "orange" ? " selected" : "").">".$locale['ESHP585']."</option> 
	    <option value='yellow'".($settings2['eshop_cart_color'] == "yellow" ? " selected" : "").">".$locale['ESHP586']."</option> 
	    </select>
	    </td>
        <td class='tbl1' width='50%' align='left'>
		<div id='3default' class='colors3 hide'><a class='button' href='javascript:;'>".$locale['ESHP580']."</a></div>
	    <div id='3blue' class='colors3 hide'><a class='eshpbutton blue' href='javascript:;'>".$locale['ESHP581']."</a></div>
  	    <div id='3green' class='colors3 hide'><a class='eshpbutton green' href='javascript:;'>".$locale['ESHP582']."</a></div>
 	    <div id='3red' class='colors3 hide'><a class='eshpbutton red' href='javascript:;'>".$locale['ESHP583']."</a></p></div>
 	    <div id='3magenta' class='colors3 hide'><a class='eshpbutton magenta' href='javascript:;'>".$locale['ESHP584']."</a></div>
 	    <div id='3orange' class='colors3 hide'><a class='eshpbutton orange' href='javascript:;'>".$locale['ESHP585']."</a></div>
 	    <div id='3yellow' class='colors3 hide'><a class='eshpbutton yellow' href='javascript:;'>".$locale['ESHP586']."</a></div>
	</td></tr>

	<tr>
        <td class='tbl1' width='30%' align='left'>".$locale['ESHP589']."</td>
        <td class='tbl1' width='20%'  align='left'>
		<select name='addtocart_color' id='colorselector4' class='textbox'>
		<option value='default'".($settings2['eshop_addtocart_color'] == "default" ? " selected" : "").">".$locale['ESHP580']."</option> 
	    <option value='blue'".($settings2['eshop_addtocart_color'] == "blue" ? " selected" : "").">".$locale['ESHP581']."</option> 
	    <option value='green'".($settings2['eshop_addtocart_color'] == "green" ? " selected" : "").">".$locale['ESHP582']."</option> 
	    <option value='red'".($settings2['eshop_addtocart_color'] == "red" ? " selected" : "").">".$locale['ESHP583']."</option> 
	    <option value='magenta'".($settings2['eshop_addtocart_color'] == "magenta" ? " selected" : "").">".$locale['ESHP584']."</option> 
	    <option value='orange'".($settings2['eshop_addtocart_color'] == "orange" ? " selected" : "").">".$locale['ESHP585']."</option> 
	    <option value='yellow'".($settings2['eshop_addtocart_color'] == "yellow" ? " selected" : "").">".$locale['ESHP586']."</option> 
	    </select>
	    </td>
        <td class='tbl1' width='50%' align='left'>
		<div id='4default' class='colors4 hide'><a class='button' href='javascript:;'>".$locale['ESHP580']."</a></div>
	    <div id='4blue' class='colors4 hide'><a class='eshpbutton blue' href='javascript:;'>".$locale['ESHP581']."</a></div>
  	    <div id='4green' class='colors4 hide'><a class='eshpbutton green' href='javascript:;'>".$locale['ESHP582']."</a></div>
 	    <div id='4red' class='colors4 hide'><a class='eshpbutton red' href='javascript:;'>".$locale['ESHP583']."</a></p></div>
 	    <div id='4magenta' class='colors4 hide'><a class='eshpbutton magenta' href='javascript:;'>".$locale['ESHP584']."</a></div>
 	    <div id='4orange' class='colors4 hide'><a class='eshpbutton orange' href='javascript:;'>".$locale['ESHP585']."</a></div>
 	    <div id='4yellow' class='colors4 hide'><a class='eshpbutton yellow' href='javascript:;'>".$locale['ESHP586']."</a></div>
	</td></tr>


	<tr>
        <td class='tbl1' width='30%' align='left'>".$locale['ESHP590']."</td>
        <td class='tbl1' width='20%'  align='left'>
	    <select name='info_color' id='colorselector5' class='textbox'>
		<option value='default'".($settings2['eshop_info_color'] == "default" ? " selected" : "").">".$locale['ESHP580']."</option> 
	    <option value='blue'".($settings2['eshop_info_color'] == "blue" ? " selected" : "").">".$locale['ESHP581']."</option> 
	    <option value='green'".($settings2['eshop_info_color'] == "green" ? " selected" : "").">".$locale['ESHP582']."</option> 
	    <option value='red'".($settings2['eshop_info_color'] == "red" ? " selected" : "").">".$locale['ESHP583']."</option> 
	    <option value='magenta'".($settings2['eshop_info_color'] == "magenta" ? " selected" : "").">".$locale['ESHP584']."</option> 
	    <option value='orange'".($settings2['eshop_info_color'] == "orange" ? " selected" : "").">".$locale['ESHP585']."</option> 
	    <option value='yellow'".($settings2['eshop_info_color'] == "yellow" ? " selected" : "").">".$locale['ESHP586']."</option> 
	    </select>
	    </td>
        <td class='tbl1' width='50%' align='left'>   	    
		<div id='5default' class='colors5 hide'><a class='button' href='javascript:;'>".$locale['ESHP580']."</a></div>
	    <div id='5blue' class='colors5 hide'><a class='eshpbutton blue' href='javascript:;'>".$locale['ESHP581']."</a></div>
  	    <div id='5green' class='colors5 hide'><a class='eshpbutton green' href='javascript:;'>".$locale['ESHP582']."</a></div>
 	    <div id='5red' class='colors5 hide'><a class='eshpbutton red' href='javascript:;'>".$locale['ESHP583']."</a></p></div>
 	    <div id='5magenta' class='colors5 hide'><a class='eshpbutton magenta' href='javascript:;'>".$locale['ESHP584']."</a></div>
 	    <div id='5orange' class='colors5 hide'><a class='eshpbutton orange' href='javascript:;'>".$locale['ESHP585']."</a></div>
 	    <div id='5yellow' class='colors5 hide'><a class='eshpbutton yellow' href='javascript:;'>".$locale['ESHP586']."</a></div>
	</td></tr>

<tr>
        <td class='tbl1' width='30%' align='left'>".$locale['ESHP591']."</td>
        <td class='tbl1' width='20%'  align='left'>
	    <select name='return_color' id='colorselector6' class='textbox'>
		<option value='default'".($settings2['eshop_return_color'] == "default" ? " selected" : "").">".$locale['ESHP580']."</option> 
	    <option value='blue'".($settings2['eshop_return_color'] == "blue" ? " selected" : "").">".$locale['ESHP581']."</option> 
	    <option value='green'".($settings2['eshop_return_color'] == "green" ? " selected" : "").">".$locale['ESHP582']."</option> 
	    <option value='red'".($settings2['eshop_return_color'] == "red" ? " selected" : "").">".$locale['ESHP583']."</option> 
	    <option value='magenta'".($settings2['eshop_return_color'] == "magenta" ? " selected" : "").">".$locale['ESHP584']."</option> 
	    <option value='orange'".($settings2['eshop_return_color'] == "orange" ? " selected" : "").">".$locale['ESHP585']."</option> 
	    <option value='yellow'".($settings2['eshop_return_color'] == "yellow" ? " selected" : "").">".$locale['ESHP586']."</option> 
	    </select>
	    </td>
        <td class='tbl1' width='50%' align='left'>   	    
		<div id='6default' class='colors6 hide'><a class='button' href='javascript:;'>".$locale['ESHP580']."</a></div>
	    <div id='6blue' class='colors6 hide'><a class='eshpbutton blue' href='javascript:;'>".$locale['ESHP581']."</a></div>
  	    <div id='6green' class='colors6 hide'><a class='eshpbutton green' href='javascript:;'>".$locale['ESHP582']."</a></div>
 	    <div id='6red' class='colors6 hide'><a class='eshpbutton red' href='javascript:;'>".$locale['ESHP583']."</a></p></div>
 	    <div id='6magenta' class='colors6 hide'><a class='eshpbutton magenta' href='javascript:;'>".$locale['ESHP584']."</a></div>
 	    <div id='6orange' class='colors6 hide'><a class='eshpbutton orange' href='javascript:;'>".$locale['ESHP585']."</a></div>
 	    <div id='6yellow' class='colors6 hide'><a class='eshpbutton yellow' href='javascript:;'>".$locale['ESHP586']."</a></div>
	</td></tr>";

echo "</table></fieldset>";

echo "<fieldset style='align:left;width:99%;display:block;float:left;margin-left:2px;margin-right:2px;margin-top:2px;margin-bottom:2px;'>
<legend>&nbsp; <b> ".$locale['ESHP503']." </b> &nbsp;</legend>
<table border='0' cellpadding='0' cellspacing='0' width='100%' class='tbl'>
	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP503']."</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='cats' class='textbox'>
                <option value='1'".($settings2['eshop_cats'] == "1" ? " selected" : "").">".$locale['ESHP504']."</option>
                <option value='0'".($settings2['eshop_cats'] == "0" ? " selected" : "").">".$locale['ESHP505']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP506']."</span></td>
	</tr>
	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP823']."</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='cat_disp' class='textbox'>
                <option value='1'".($settings2['eshop_cat_disp'] == "1" ? " selected" : "").">".$locale['ESHP825']."</option>
                <option value='0'".($settings2['eshop_cat_disp'] == "0" ? " selected" : "").">".$locale['ESHP826']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP824']."</span></td>
	</tr>
	
	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP513']."</td>
            <td class='tbl1' width='20%'  align='left'>
			<input type='text' name='nopp' value='".$settings2['eshop_nopp']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP514']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP834']."</td>
            <td class='tbl1' width='20%'  align='left'>
			<input type='text' name='cipr' value='".$settings2['eshop_cipr']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP835']."</span></td>
	</tr>

	
	<tr>
	    <td class='tbl1' width='30%' align='left'>".$locale['ESHP509']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='catimg_h' value='".$settings2['eshop_catimg_h']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP510']."</span></td>
	</tr>
	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP511']."</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='catimg_w' value='".$settings2['eshop_catimg_w']."' class='textbox' style='width:40px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP512']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP805'].":</td>
            <td class='tbl1' width='20%'  align='left'>
	   <input type='text' name='target' value='".$settings2['eshop_target']."' class='textbox' style='width:60px;'></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP806']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP807'].":</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='folderlink' class='textbox'>
                <option value='1'".($settings2['eshop_folderlink'] == "1" ? " selected" : "").">".$locale['ESHP828']."</option>
                <option value='0'".($settings2['eshop_folderlink'] == "0" ? " selected" : "").">".$locale['ESHP829']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP808']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP809'].":</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='selection' class='textbox'>
                <option value='1'".($settings2['eshop_selection'] == "1" ? " selected" : "").">".$locale['ESHP828']."</option>
                <option value='0'".($settings2['eshop_selection'] == "0" ? " selected" : "").">".$locale['ESHP829']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP810']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP811'].":</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='cookies' class='textbox'>
                <option value='1'".($settings2['eshop_cookies'] == "1" ? " selected" : "").">".$locale['ESHP828']."</option>
                <option value='0'".($settings2['eshop_cookies'] == "0" ? " selected" : "").">".$locale['ESHP829']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP812']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP813'].":</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='bclines' class='textbox'>
                <option value='1'".($settings2['eshop_bclines'] == "1" ? " selected" : "").">".$locale['ESHP828']."</option>
                <option value='0'".($settings2['eshop_bclines'] == "0" ? " selected" : "").">".$locale['ESHP829']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP814']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP815'].":</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='icons' class='textbox'>
                <option value='1'".($settings2['eshop_icons'] == "1" ? " selected" : "").">".$locale['ESHP828']."</option>
                <option value='0'".($settings2['eshop_icons'] == "0" ? " selected" : "").">".$locale['ESHP829']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP816']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP817'].":</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='statustext' class='textbox'>
                <option value='1'".($settings2['eshop_statustext'] == "1" ? " selected" : "").">".$locale['ESHP828']."</option>
                <option value='0'".($settings2['eshop_statustext'] == "0" ? " selected" : "").">".$locale['ESHP829']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP818']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP819'].":</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='closesamelevel' class='textbox'>
                <option value='1'".($settings2['eshop_closesamelevel'] == "1" ? " selected" : "").">".$locale['ESHP828']."</option>
                <option value='0'".($settings2['eshop_closesamelevel'] == "0" ? " selected" : "").">".$locale['ESHP829']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP820']."</span></td>
	</tr>

	<tr>
            <td class='tbl1' width='30%' align='left'>".$locale['ESHP821'].":</td>
            <td class='tbl1' width='20%'  align='left'>
            <select name='inorder' class='textbox'>
                <option value='1'".($settings2['eshop_inorder'] == "1" ? " selected" : "").">".$locale['ESHP828']."</option>
                <option value='0'".($settings2['eshop_inorder'] == "0" ? " selected" : "").">".$locale['ESHP829']."</option>
            </select></td>
            <td class='tbl1' width='50%' align='left'><span style='small'>".$locale['ESHP822']."</span></td>
	</tr></table></fieldset>";
	
echo "<fieldset style='align:left;width:99%;display:block;float:left;margin-left:2px;margin-right:2px;margin-top:2px;margin-bottom:2px;'>
<legend>&nbsp; <b> ".$locale['ESHP830']." </b> &nbsp;</legend><table border='0' cellpadding='0' cellspacing='0' width='100%' class='tbl'>";
	
	echo "<tr><td valign='top' class='tbl'>".$locale['ESHP831']."</td>\n";
		echo "<td class='tbl'><textarea name='terms' cols='100' rows='5' class='textbox span7' >".stripslashes($settings2['eshop_terms'])."</textarea></td>\n";
		echo "</tr>\n";
		echo "</table></fieldset>";


echo "<center><input type='submit' name='update_settings' value='".$locale['ESHP700']."' class='button'></center>";
echo "</form>";
} else {
echo "<br /><div class='admin-message' style='width: 100%;'>".$locale['ESHP501']."</div><br />";
}
closetable();
require_once THEMES."templates/footer.php";
?>