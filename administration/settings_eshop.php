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
opentable("Eshop Settings");
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
	openside('');
	echo form_text($locale['ESHP551'], 'eshop_ppmail', 'eshop_ppmail', $settings2['eshop_ppmail'], array('tip'=>$locale['ESHP552'], 'inline'=>1));
	echo form_text($locale['ESHP553'], 'eshop_returnpage', 'eshop_returnpage', $settings2['eshop_returnpage'], array('tip'=>$locale['ESHP554'], 'inline'=>1));
	echo form_select($locale['ESHP850'], 'eshop_ipn', 'eshop_ipn',array('0'=>$locale['no'], '1'=>$locale['yes']), $settings2['eshop_ipn'], array('tip'=>$locale['ESHP851'], 'inline'=>1));
	echo form_text($locale['ESHP555'], 'eshop_vat', 'eshop_vat', $settings2['eshop_vat'], array('tip'=>$locale['ESHP556'], 'inline'=>1, 'placeholder'=>'%'));
	echo form_select($locale['ESHP557'], 'eshop_vat_default', 'eshop_vat_default',array('0'=>$locale['no'], '1'=>$locale['yes']), $settings2['eshop_vat_default'], array('tip'=>$locale['ESHP558'], 'inline'=>1));
	echo form_select($locale['ESHP559'], 'eshop_currency', 'eshop_currency', \PHPFusion\Geomap::get_Currency(), $settings2['eshop_currency'], array('tip'=>$locale['ESHP560'], 'width'=>'350px', 'inline'=>1));
	closeside();
	openside('');
	echo form_select($locale['ESHP575'], 'eshop_listprice', 'eshop_listprice', array('1'=>$locale['on'], '0'=>$locale['off']), $settings2['eshop_listprice'], array('tip'=>$locale['ESHP576'], 'inline'=>1));
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
	echo form_text($locale['ESHP846'], 'eshop_freeshipsum', 'eshop_freeshipsum', $settings2['eshop_freeshipsum'], array('tip'=>$locale['ESHP847'], 'width'=>'350px', 'inline'=>1));
	echo form_select($locale['ESHP561'], 'eshop_weightscale', 'eshop_weightscale', array('KG'=>$locale['ESHP566'], 'LBS'=>$locale['ESHP567']), $settings2['eshop_weightscale'], array('tip'=>$locale['ESHP562'], 'inline'=>1));
	closeside();

	openside('');
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
	closeside();


	openside('');

	echo form_select($locale['ESHP503'], 'eshop_cats', 'eshop_cats', array('0'=>$locale['off'], '1'=>$locale['on']), $settings2['eshop_cats'], array('tip'=>$locale['ESHP506'], 'inline'=>1));
	echo form_select($locale['ESHP823'], 'eshop_cat_disp', 'eshop_cat_disp', array('0'=>$locale['off'], '1'=>$locale['on']), $settings2['eshop_cat_disp'], array('tip'=>$locale['ESHP824'], 'inline'=>1));
	echo form_text($locale['ESHP513'], 'eshop_nopp', 'eshop_nopp', $settings2['eshop_nopp'], array('tip'=>$locale['ESHP514'], 'inline'=>1));
	echo form_text($locale['ESHP834'], 'eshop_cipr', 'eshop_cipr', $settings2['eshop_cipr'], array('tip'=>$locale['ESHP835'], 'inline'=>1));
	echo form_text($locale['ESHP834'], 'eshop_cipr', 'eshop_cipr', $settings2['eshop_cipr'], array('tip'=>$locale['ESHP835'], 'inline'=>1));
	echo form_text($locale['ESHP509'], 'eshop_catimg_h', 'eshop_catimg_h', $settings2['eshop_catimg_h'], array('tip'=>$locale['ESHP510'], 'inline'=>1));
	echo form_text($locale['ESHP511'], 'eshop_catimg_w', 'eshop_catimg_w', $settings2['eshop_catimg_w'], array('tip'=>$locale['ESHP512'], 'inline'=>1));
	echo form_text($locale['ESHP805'], 'eshop_target', 'eshop_target', $settings2['eshop_target'], array('tip'=>$locale['ESHP806'], 'inline'=>1));
	echo form_select($locale['ESHP807'], 'eshop_folderlink', 'eshop_folderlink', array('1'=>$locale['ESHP828'], '0'=>$locale['ESHP829']), $settings2['eshop_folderlink'], array('tip'=>$locale['ESHP808'], 'inline'=>1));
	echo form_select($locale['ESHP809'], 'eshop_selection', 'eshop_selection', array('1'=>$locale['ESHP828'], '0'=>$locale['ESHP829']), $settings2['eshop_selection'], array('tip'=>$locale['ESHP810'], 'inline'=>1));
	echo form_select($locale['ESHP811'], 'eshop_cookies', 'eshop_cookies', array('1'=>$locale['ESHP828'], '0'=>$locale['ESHP829']), $settings2['eshop_cookies'], array('tip'=>$locale['ESHP812'], 'inline'=>1));
	echo form_select($locale['ESHP813'], 'eshop_bclines', 'eshop_bclines', array('1'=>$locale['ESHP828'], '0'=>$locale['ESHP829']), $settings2['eshop_bclines'], array('tip'=>$locale['ESHP814'], 'inline'=>1));
	echo form_select($locale['ESHP815'], 'eshop_icons', 'eshop_icons', array('1'=>$locale['ESHP828'], '0'=>$locale['ESHP829']), $settings2['eshop_icons'], array('tip'=>$locale['ESHP816'], 'inline'=>1));
	echo form_select($locale['ESHP817'], 'eshop_statustext', 'eshop_statustext', array('1'=>$locale['ESHP828'], '0'=>$locale['ESHP829']), $settings2['eshop_statustext'], array('tip'=>$locale['ESHP818'], 'inline'=>1));
	echo form_select($locale['ESHP819'], 'eshop_closesamelevel', 'eshop_closesamelevel', array('1'=>$locale['ESHP828'], '0'=>$locale['ESHP829']), $settings2['eshop_closesamelevel'], array('tip'=>$locale['ESHP820'], 'inline'=>1));
	echo form_select($locale['ESHP821'], 'eshop_inorder', 'eshop_inorder', array('1'=>$locale['ESHP828'], '0'=>$locale['ESHP829']), $settings2['eshop_inorder'], array('tip'=>$locale['ESHP822'], 'inline'=>1));
	closeside();


	echo form_textarea($locale['ESHP831'], 'eshop_terms', 'eshop_terms', $data['eshop_terms'], array('autosize'=>1));


	echo "</div>\n";
	echo "<div class='col-xs-12 col-sm-4'>\n";

	echo form_button($locale['ESHP700'], 'update_settings', 'update_settings1', $locale['ESHP700'], array('class'=>'btn-primary m-b-10'));

	openside('');
	echo form_text($locale['ESHP577'], 'eshop_noppf', 'eshop_noppf', $settings2['eshop_noppf'], array('tip'=>$locale['ESHP578']));
	echo form_text($locale['ESHP592'], 'eshop_ipr', 'eshop_ipr', $settings2['eshop_ipr'], array('tip'=>$locale['ESHP593']));
	closeside();
	openside('');
	echo form_select($locale['ESHP563'], 'eshop_shareing', 'eshop_shareing', array('1'=>$locale['on'], '0'=>$locale['off']), $settings2['eshop_shareing'], array('tip'=>$locale['ESHP568']));
	echo form_select($locale['ESHP569'], 'eshop_shopmode', 'eshop_shopmode', array('1'=>$locale['on'], '0'=>$locale['off']), $settings2['eshop_shopmode'], array('tip'=>$locale['ESHP570']));
	closeside();
	openside('');
	echo form_text($locale['ESHP598'], 'eshop_itembox_w', 'eshop_itembox_w', $settings2['eshop_itembox_w'], array('tip'=>$locale['ESHP599']));
	echo form_text($locale['ESHP600'], 'eshop_itembox_h', 'eshop_itembox_h', $settings2['eshop_itembox_h'], array('tip'=>$locale['ESHP601']));
	closeside();
	openside('');
	echo form_select($locale['ESHP571'], 'eshop_pretext', 'eshop_pretext', array('1'=>$locale['on'], '0'=>$locale['off']), $settings2['eshop_pretext'], array('tip'=>$locale['ESHP572']));
	echo form_text($locale['ESHP573'], 'eshop_pretext_w', 'eshop_pretext_w', $settings2['eshop_pretext_w'], array('tip'=>$locale['ESHP574']));
	closeside();

	openside('');
	$color_options = array(
		'default' => $locale['ESHP580'],
		'blue' => $locale['ESHP581'],
		'green' => $locale['ESHP582'],
		'red' => $locale['ESHP583'],
		'magenta' => $locale['ESHP584'],
		'orange' => $locale['ESHP585'],
		'yellow' => $locale['ESHP586'],
	);
	echo form_select($locale['ESHP579'], 'eshop_buynow_color', 'eshop_buynow_color', $color_options, $settings2['eshop_buynow_color']);
	echo form_select($locale['ESHP587'], 'eshop_checkout_color', 'eshop_checkout_color', $color_options, $settings2['eshop_checkout_color']);
	echo form_select($locale['ESHP588'], 'eshop_cart_color', 'eshop_cart_color', $color_options, $settings2['eshop_cart_color']);
	echo form_select($locale['ESHP589'], 'eshop_addtocart_color', 'eshop_addtocart_color', $color_options, $settings2['eshop_addtocart_color']);
	echo form_select($locale['ESHP590'], 'eshop_info_color', 'eshop_info_color', $color_options, $settings2['eshop_info_color']);
	echo form_select($locale['ESHP591'], 'eshop_return_color', 'eshop_return_color', $color_options, $settings2['eshop_return_color']);
	closeside();
	echo "</div>\n";
	echo "</div>\n";
	echo form_button($locale['ESHP700'], 'update_settings', 'update_settings', $locale['ESHP700'], array('class'=>'btn-primary'));
	closeform();
} else {
echo admin_message($locale['ESHP501']);
}
closetable();

require_once THEMES."templates/footer.php";
?>