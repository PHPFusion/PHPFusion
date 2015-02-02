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

$data = array(
'eshop_ipn'=>1,
	'esho_cats'=>1,
	'eshop_cat_disp'=>1,
	'eshop_nopp'=>6,
	'eshop_noppf'=>9,
	'eshop_target'=> '_self',
	'eshop_folderlink' => 1,
	'eshop_selection'=>1,
	'eshop_cookies'=>1,
	'eshop_bclines'=>1,
	'eshop_icons' => 1,
	'eshop_statustext'=>1,
	'eshop_closesamelevel'=>1,
	'eshop_inorder'=>0,
	'eshop_shopmode'=>1,
	'eshop_returnpage'=> 'ordercompleted.php',
	'eshop_ppmail'=> '',
	'eshop_ipr'=>3,
	'eshop_ratios'=>1,
	'eshop_idisp_h'=>100,
	'eshop_idisp_w'=>100,
	'eshop_idisp_h2'=>180,
	'eshop_idisp_w2'=>180,
	'eshop_catimg_w'=>100,
	'eshop_catimg_h'=>100,
	'eshop_image_w'=>6400,
	'eshop_image_h'=>6400,
	'eshop_image_b'=>9999999,
	'eshop_image_tw'=>150,
	'eshop_image_th'=>150,
	'eshop_image_t2w'=>250,
	'eshop_image_t2h'=>250,
	'eshop_buynow_color'=>'btn-primary',
	'eshop_checkout_color'=>'btn-success',
	'eshop_cart_color'=>'btn-danger',
	'eshop_addtocart_color'=>'btn-success',
	'eshop_info_color'=>'btn-info',
	'eshop_return_color'=>'btn-default',
	'eshop_pretext'=>0,
	'eshop_pretext_w'=>190,
	'eshop_listprice'=>1,
	'eshop_currency'=>'USD',
	'eshop_shareing'=>1,
	'eshop_weightscale'=>'KG',
	'eshop_vat'=>'25',
	'eshop_vat_default'=>'0',
	'eshop_terms'=>'<h2> Ordering </h2><br />\r\nWhilst all efforts are made to ensure accuracy of description, specifications and pricing there may <br />be occasions where errors arise. Should such a situation occur [Company name] cannot accept your order. <br /> In the event of a mistake you will be contacted with a full explanation and a corrected offer. <br />The information displayed is considered as an invitation to treat not as a confirmed offer for sale. \r\nThe contract is confirmed upon supply of goods.\r\n<br /><br /><br />\r\n<h2>Delivery and Returns</h2><br />\r\n[Company name] returns policy has been set up to keep costs down and to make the process as easy for you as possible. You must contact us and be in receipt of a returns authorisation (RA) number before sending any item back. Any product without a RA number will not be refunded. <br /><br /><br />\r\n<h2> Exchange </h2><br />\r\n If when you receive your product(s), you are not completely satisfied you may return the items to us, within seven days of exchange or refund. Returns will take approximately 5 working days for the process once the goods have arrived. Items must be in original packaging, in all original boxes, packaging materials, manuals blank warranty cards and all accessories and documents provided by the manufacturer.<br /><br /><br />\r\n\r\nIf our labels are removed from the product â€“ the warranty becomes void.<br /><br /><br />\r\n\r\nWe strongly recommend that you fully insure your package that you are returning. We suggest the use of a carrier that can provide you with a proof of delivery. [Company name] will not be held responsible for items lost or damaged in transit.<br /><br /><br />\r\n\r\nAll shipping back to [Company name] is paid for by the customer. We are unable to refund you postal fees.<br /><br /><br />\r\n\r\nAny product returned found not to be defective can be refunded within the time stated above and will be subject to a 15% restocking fee to cover our administration costs. Goods found to be tampered with by the customer will not be replaced but returned at the customers expense. <br /><br /><br />\r\n\r\n If you are returning items for exchange please be aware that a second charge may apply. <br /><br /><br />\r\n\r\n<h2>Non-Returnable </h2><br />\r\n For reasons of hygiene and public health, refunds/exchanges are not available for used ......... (this does not apply to faulty goods â€“ faulty products will be exchanged like for like)<br /><br /><br />\r\n\r\nDiscounted or our end of line products can only be returned for repair no refunds of replacements will be made.<br /><br /><br />\r\n\r\n<h2> Incorrect/Damaged Goods </h2><br />\r\n\r\n We try very hard to ensure that you receive your order in pristine condition. If you do not receive your products ordered. Please contract us. In the unlikely event that the product arrives damaged or faulty, please contact [Company name] immediately, this will be given special priority and you can expect to receive the correct item within 72 hours. Any incorrect items received all delivery charges will be refunded back onto you credit/debit card.<br /><br /><br />\r\n\r\n<h2>Delivery service</h2><br />\r\nWe try to make the delivery process as simple as possible and our able to send your order either you home or to your place of work.<br /><br /><br />\r\n\r\nDelivery times are calculated in working days Monday to Friday. If you order after 4 pm the next working day will be considered the first working day for delivery. In case of bank holidays and over the Christmas period, please allow an extra two working days.<br /><br /><br />\r\n\r\nWe aim to deliver within 3 working days but sometimes due to high order volume certain in sales periods please allow 4 days before contacting us. We will attempt to email you if we become aware of an unexpected delay. <br /><br /><br />\r\n\r\nAll small orders are sent out via royal mail 1st packets post service, if your order is over Â£15.00 it will be sent out via royal mails recorded packet service, which will need a signature, if you are not present a card will be left to advise you to pick up your goods from the local sorting office.<br /><br /><br />\r\n\r\nEach item will be attempted to be delivered twice. Failed deliveries after this can be delivered at an extra cost to you or you can collect the package from your local post office collection point.<br /><br /><br />\r\n\r\n<h2>Export restrictions</h2><br /><br /><br />\r\n\r\nAt present [Company name] only sends goods within the [Country]. We plan to add exports to our services in the future. If however you have a special request please contact us your requirements.<br /><br /><br />\r\n\r\n<h2> Privacy Notice </h2><br />\r\n\r\nThis policy covers all users who register to use the website. It is not necessary to purchase anything in order to gain access to the searching facilities of the site.<br /><br /><br />\r\n\r\n<h2> Security </h2><br />\r\nWe have taken the appropriate measures to ensure that your personal information is not unlawfully processed. [Company name] uses industry standard practices to safeguard the confidentiality of your personal identifiable information, including â€˜firewallsâ€™ and secure socket layers. <br /><br /><br />\r\n\r\nDuring the payment process, we ask for personal information that both identifies you and enables us to communicate with you. <br /><br /><br />\r\n\r\nWe will use the information you provide only for the following purposes.<br /><br /><br />\r\n\r\n* To send you newsletters and details of offers and promotions in which we believe you will be interested. <br />\r\n* To improve the content design and layout of the website. <br />\r\n* To understand the interest and buying behavior of our registered users<br />\r\n* To perform other such general marketing and promotional focused on our products and activities. <br />\r\n\r\n<h2> Conditions Of Use </h2><br />\r\n[Company name] and its affiliates provide their services to you subject to the following conditions. If you visit our shop at [Company name] you accept these conditions. Please read them carefully, [Company name] controls and operates this site from its offices within the [Country]. The laws of [Country] relating to including the use of, this site and materials contained. <br /><br /><br />\r\n\r\nIf you choose to access from another country you do so on your own initiave and are responsible for compliance with applicable local lands. <br /><br /><br />\r\n\r\n<h2> Copyrights </h2><br />\r\nAll content includes on the site such as text, graphics logos button icons images audio clips digital downloads and software are all owned by [Company name] and are protected by international copyright laws. <br /><br /><br />\r\n\r\n<h2> License and Site Access </h2><br />\r\n[Company name] grants you a limited license to access and make personal use of this site. This license doses not include any resaleâ€™s of commercial use of this site or its contents any collection and use of any products any collection and use of any product listings descriptions or prices any derivative use of this site or its contents, any downloading or copying of account information. For the benefit of another merchant or any use of data mining, robots or similar data gathering and extraction tools.<br /><br /><br />\r\n\r\nThis site may not be reproduced duplicated copied sold â€“ resold or otherwise exploited for any commercial exploited without written consent of [Company name].<br /><br /><br />\r\n\r\n<h2> Product Descriptions </h2><br />\r\n[Company name] and its affiliates attempt to be as accurate as possible however we do not warrant that product descriptions or other content is accurate complete reliable, or error free.<br /><br /><br />\r\nFrom time to time there may be information on [Company name] that contains typographical errors, inaccuracies or omissions that may relate to product descriptions, pricing and availability.<br /><br /><br />\r\nWe reserve the right to correct ant errors inaccuracies or omissions and to change or update information at any time without prior notice. (Including after you have submitted your order) We apologies for any inconvenience this may cause you. <br /><br /><br />\r\n\r\n<h2> Prices </h2><br />\r\nPrices and availability of items are subject to change without notice the prices advertised on this site are for orders placed and include VAT and delivery.<br /><br /><br />\r\n<br /><br /><br />\r\nPlease review our other policies posted on this site. These policies also govern your visit to [Company name]',
	'eshop_itembox_w'=>'200',
	'eshop_itembox_h'=>'200',
	'eshop_cipr'=>'4',
	'eshop_newtime'=>604800,
	'eshop_freeshipsum'=>0,
	'eshop_coupons'=>0,
);


if (isset($_POST['update_settings'])) {
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_ipn'])."' WHERE settings_name='eshop_ipn'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_cats'])."' WHERE settings_name='eshop_cats'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_cat_disp'])."' WHERE settings_name='eshop_cat_disp'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_nopp'])."' WHERE settings_name='eshop_nopp'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_noppf'])."' WHERE settings_name='eshop_noppf'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_shopmode'])."' WHERE settings_name='eshop_shopmode'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_returnpage'])."' WHERE settings_name='eshop_returnpage'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_target'])."' WHERE settings_name='eshop_target'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_folderlink'])."' WHERE settings_name='eshop_folderlink'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_selection'])."' WHERE settings_name='eshop_selection'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_cookies'])."' WHERE settings_name='eshop_cookies'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_bclines'])."' WHERE settings_name='eshop_bclines'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_icons'])."' WHERE settings_name='eshop_icons'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_statustext'])."' WHERE settings_name='eshop_statustext'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_closesamelevel'])."' WHERE settings_name='eshop_closesamelevel'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_inorder'])."' WHERE settings_name='eshop_inorder'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_ratios'])."' WHERE settings_name='eshop_ratios'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_idisp_h'])."' WHERE settings_name='eshop_idisp_h'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_idisp_w'])."' WHERE settings_name='eshop_idisp_w'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_idisp_h2'])."' WHERE settings_name='eshop_idisp_h2'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_idisp_w2'])."' WHERE settings_name='eshop_idisp_w2'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_ipr'])."' WHERE settings_name='eshop_ipr'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_ppmail'])."' WHERE settings_name='eshop_ppmail'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_catimg_w'])."' WHERE settings_name='eshop_catimg_w'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_eshop_catimg_h'])."' WHERE settings_name='eshop_catimg_h'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_image_w'])."' WHERE settings_name='eshop_image_w'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_image_h'])."' WHERE settings_name='eshop_image_h'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_image_b'])."' WHERE settings_name='eshop_image_b'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_image_tw'])."' WHERE settings_name='eshop_image_tw'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_image_th'])."' WHERE settings_name='eshop_image_th'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_image_t2w'])."' WHERE settings_name='eshop_image_t2w'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_image_t2h'])."' WHERE settings_name='eshop_image_t2h'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_buynow_color'])."' WHERE settings_name='eshop_buynow_color'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_checkout_color'])."' WHERE settings_name='eshop_checkout_color'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_cart_color'])."' WHERE settings_name='eshop_cart_color'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_addtocart_color'])."' WHERE settings_name='eshop_addtocart_color'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_info_color'])."' WHERE settings_name='eshop_info_color'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_return_color'])."' WHERE settings_name='eshop_return_color'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_pretext'])."' WHERE settings_name='eshop_pretext'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_pretext_w'])."' WHERE settings_name='eshop_pretext_w'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_listprice'])."' WHERE settings_name='eshop_listprice'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_currency'])."' WHERE settings_name='eshop_currency'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_shareing'])."' WHERE settings_name='eshop_shareing'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_weightscale'])."' WHERE settings_name='eshop_weightscale'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_vat'])."' WHERE settings_name='eshop_vat'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_vat_default'])."' WHERE settings_name='eshop_vat_default'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_itembox_w'])."' WHERE settings_name='eshop_itembox_w'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_itembox_h'])."' WHERE settings_name='eshop_itembox_h'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_cipr'])."' WHERE settings_name='eshop_cipr'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_newtime'])."' WHERE settings_name='eshop_newtime'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_coupons'])."' WHERE settings_name='eshop_coupons'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['eshop_freeshipsum'])."' WHERE settings_name='eshop_freeshipsum'");
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['eshop_terms']))."' WHERE settings_name='eshop_terms'");
	echo "<div class='admin-message'>".$locale['ESHP500']."</div>";
}
$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}
if (dbrows($result) != 0) {

	echo openform('optionsform', 'optionsform', 'post', FUSION_SELF.$aidlink."&amp;a_page=settings");
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
		'btn-default' => $locale['ESHP580'],
		'btn-primary' => $locale['ESHP581'],
		'btn-info' => 'Cyan',
		'btn-success' => $locale['ESHP582'],
		'btn-danger' => $locale['ESHP583'],
		'btn-warning' => $locale['ESHP586'],
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
	echo closeform();
} else {
echo admin_message($locale['ESHP501']);
}
closetable();

require_once THEMES."templates/footer.php";
?>