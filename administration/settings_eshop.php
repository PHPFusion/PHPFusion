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
//add_to_head("<link rel='stylesheet' type='text/css' href='".THEMES."templates/global/css/eshop.css' />");

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
$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['ipn'])."' WHERE settings_name='eshop_ipn'");
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

	openform('optionsform', 'optionsform', 'post', FUSION_SELF.$aidlink."&amp;a_page=settings");
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-8'>\n";

	echo "</div>\n";
	echo "<div class='col-xs-12 col-sm-4'>\n";
	echo "</div>\n";
	echo "</div>\n";
	closeform();


	echo form_text($locale['ESHP551'], 'eshop_ppmail', 'eshop_ppmail', $settings2['eshop_ppmail'], array('tip'=>$locale['ESHP552'], 'inline'=>1));
	echo form_text($locale['ESHP553'], 'eshop_returnpage', 'eshop_returnpage', $settings2['eshop_returnpage'], array('tip'=>$locale['ESHP554'], 'inline'=>1));
	echo form_select($locale['ESHP850'], 'eshop_ipn', 'eshop_ipn',array('0'=>$locale['no'], '1'=>$locale['yes']), $settings2['eshop_ipn'], array('tip'=>$locale['ESHP851'], 'inline'=>1));
	echo form_text($locale['ESHP555'], 'eshop_vat', 'eshop_vat', $settings2['eshop_vat'], array('tip'=>$locale['ESHP556'], 'inline'=>1, 'placeholder'=>'%'));
	echo form_select($locale['ESHP557'], 'eshop_vat_default', 'eshop_vat_default',array('0'=>$locale['no'], '1'=>$locale['yes']), $settings2['eshop_vat_default'], array('tip'=>$locale['ESHP558'], 'inline'=>1));
	echo form_select($locale['ESHP559'], 'eshop_currency', 'eshop_currency', \PHPFusion\Geomap::get_Currency(), $settings2['eshop_currency'], array('tip'=>$locale['ESHP560'], 'width'=>'350px', 'inline'=>1));

	// ribbon timers
	$timer_opts = array(
		'0' => $locale['ESHP839'],
		'86400' => $locale['ESHP840'],
		'259200' => $locale['ESHP841'],
		'432000' => $locale['ESHP842'],
		'604800' => $locale['ESHP843'],
		'1209600' => $locale['ESHP844'],
		'2419200' => $locale['ESHP845'],
	);
	echo form_select($locale['ESHP837'], 'eshop_newtime', 'eshop_newtime', $timer_opts, $settings2['eshop_newtime'], array('tip'=>$locale['ESHP838'], 'width'=>'350px', 'inline'=>1));
	echo form_select($locale['ESHP848'], 'eshop_coupons', 'eshop_coupons', array('0'=>$locale['no'], '1'=>$locale['yes']), $settings2['eshop_coupons'], array('tip'=>$locale['ESHP849'], 'width'=>'350px', 'inline'=>1));
	// is this in dollar or in kg?
	echo form_text($locale['ESHP846'], 'eshop_freeshipsum', 'eshop_freeshipsum', $settings2['eshop_freeshipsum'], array('tip'=>$locale['ESHP847'], 'width'=>'350px', 'inline'=>1));
	echo form_select($locale['ESHP561'], 'eshop_weightscale', 'eshop_weightscale', array('KG'=>$locale['ESHP566'], 'LBS'=>$locale['ESHP567']), $settings2['eshop_weightscale'], array('tip'=>$locale['ESHP562'], 'inline'=>1));
	echo form_select($locale['ESHP563'], 'eshop_shareing', 'eshop_shareing', array('1'=>$locale['on'], '0'=>$locale['off']), $settings2['eshop_shareing'], array('tip'=>$locale['ESHP568'], 'inline'=>1));
	echo form_select($locale['ESHP569'], 'eshop_shopmode', 'eshop_shopmode', array('1'=>$locale['on'], '0'=>$locale['off']), $settings2['eshop_shopmode'], array('tip'=>$locale['ESHP570'], 'inline'=>1));
	echo form_text($locale['ESHP598'], 'eshop_itembox_w', 'eshop_itembox_w', $settings2['eshop_itembox_w'], array('tip'=>$locale['ESHP599'], 'inline'=>1));
	echo form_text($locale['ESHP600'], 'eshop_itembox_h', 'eshop_itembox_h', $settings2['eshop_itembox_h'], array('tip'=>$locale['ESHP601'], 'inline'=>1));
	echo form_select($locale['ESHP571'], 'eshop_pretext', 'eshop_pretext', array('1'=>$locale['on'], '0'=>$locale['off']), $settings2['eshop_pretext'], array('tip'=>$locale['ESHP572'], 'inline'=>1));
	echo form_text($locale['ESHP573'], 'eshop_pretext_w', 'eshop_pretext_w', $settings2['eshop_pretext_w'], array('tip'=>$locale['ESHP574'], 'inline'=>1));
	echo form_select($locale['ESHP575'], 'eshop_listprice', 'eshop_listprice', array('1'=>$locale['on'], '0'=>$locale['off']), $settings2['eshop_listprice'], array('tip'=>$locale['ESHP576'], 'inline'=>1));
	echo form_text($locale['ESHP577'], 'eshop_noppf', 'eshop_noppf', $settings2['eshop_noppf'], array('tip'=>$locale['ESHP578'], 'inline'=>1));
	echo form_text($locale['ESHP592'], 'eshop_ipr', 'eshop_ipr', $settings2['eshop_ipr'], array('tip'=>$locale['ESHP593'], 'inline'=>1));
	echo form_select($locale['ESHP515'], 'eshop_ratios', 'eshop_ratios', array('1'=>$locale['on'], '0'=>$locale['off']), $settings2['eshop_ratios'], array('tip'=>$locale['ESHP516'], 'inline'=>1));
	echo form_text($locale['ESHP519'], 'eshop_idisp_w', 'eshop_idisp_w', $settings2['eshop_idisp_w'], array('tip'=>$locale['ESHP520'], 'inline'=>1));
	echo form_text($locale['ESHP517'], 'eshop_idisp_h', 'eshop_idisp_h', $settings2['eshop_idisp_h'], array('tip'=>$locale['ESHP518'], 'inline'=>1));
	echo form_text($locale['ESHP594'], 'eshop_idisp_w2', 'eshop_idisp_w2', $settings2['eshop_idisp_w2'], array('tip'=>$locale['ESHP595'], 'inline'=>1));
	echo form_text($locale['ESHP596'], 'eshop_idisp_h2', 'eshop_idisp_h2', $settings2['eshop_idisp_h2'], array('tip'=>$locale['ESHP597'], 'inline'=>1));
	echo form_text($locale['ESHP537'], 'eshop_image_w', 'eshop_image_w', $settings2['eshop_image_w'], array('tip'=>$locale['ESHP538'], 'inline'=>1));
	echo form_text($locale['ESHP539'], 'eshop_image_h', 'eshop_image_h', $settings2['eshop_image_h'], array('tip'=>$locale['ESHP540'], 'inline'=>1));
	echo form_text($locale['ESHP541'], 'eshop_image_b', 'eshop_image_b', $settings2['eshop_image_b'], array('placeholder'=>parseByteSize($settings2['eshop_image_b']), 'tip'=>$locale['ESHP542'], 'inline'=>1));
	echo form_text($locale['ESHP543'], 'eshop_image_tw', 'eshop_image_tw', $settings2['eshop_image_tw'], array('tip'=>$locale['ESHP544'], 'inline'=>1));
	echo form_text($locale['ESHP545'], 'eshop_image_th', 'eshop_image_th', $settings2['eshop_image_th'], array('tip'=>$locale['ESHP546'], 'inline'=>1));
	echo form_text($locale['ESHP547'], 'eshop_image_t2w', 'eshop_image_t2w', $settings2['eshop_image_t2w'], array('tip'=>$locale['ESHP548'], 'inline'=>1));
	echo form_text($locale['ESHP549'], 'eshop_image_t2h', 'eshop_image_t2h', $settings2['eshop_image_t2h'], array('tip'=>$locale['ESHP550'], 'inline'=>1));
	$color_options = array(
		'default' => $locale['ESHP580'],
		'blue' => $locale['ESHP581'],
		'green' => $locale['ESHP582'],
		'red' => $locale['ESHP583'],
		'magenta' => $locale['ESHP584'],
		'orange' => $locale['ESHP585'],
		'yellow' => $locale['ESHP586'],
	);
	echo form_select($locale['ESHP579'], 'eshop_buynow_color', 'eshop_buynow_color', $color_options, $settings2['eshop_buynow_color'], array('inline'=>1));
	echo form_select($locale['ESHP587'], 'eshop_checkout_color', 'eshop_checkout_color', $color_options, $settings2['eshop_checkout_color'], array('inline'=>1));
	echo form_select($locale['ESHP588'], 'eshop_cart_color', 'eshop_cart_color', $color_options, $settings2['eshop_cart_color'], array('inline'=>1));
	echo form_select($locale['ESHP589'], 'eshop_addtocart_color', 'eshop_addtocart_color', $color_options, $settings2['eshop_addtocart_color'], array('inline'=>1));
	echo form_select($locale['ESHP590'], 'eshop_info_color', 'eshop_info_color', $color_options, $settings2['eshop_info_color'], array('inline'=>1));
	echo form_select($locale['ESHP591'], 'eshop_return_color', 'eshop_return_color', $color_options, $settings2['eshop_return_color'], array('inline'=>1));

	openside('');
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