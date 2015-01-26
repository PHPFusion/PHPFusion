<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: payments.php
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
if (isset($_GET['payid']) && !isnum($_GET['payid'])) die("Denied");


class eShop_payment {
	private $data = array();

	public function __construct() {
		define("PAYMENT_DIR", BASEDIR."eshop/paymentimgs/");

	}

	static function get_paymentOpts() {
		$payment_files = makefilelist(PAYMENT_DIR, ".|..|index.php", true);
		$payment_list = array();
		foreach($payment_files as $file) {
			$payment_list[$file] = $file;
		}
		return $payment_list;
	}


	private function payment_form() {

		echo "<div class='m-t-20'>\n";

		echo "</div>\n";
		/*
		 * echo "<form name='inputform' method='post' action='$formaction'>
<div style='float:left;width:50%'>
<table align='left' cellspacing='0' width='100%' cellpadding='0' class='tbl'>
<tr><td width='1%' align='left'><img src='".PAYMENT_DIR.($payment_image!=''?$payment_image:"")."' width='70' name='payment_image_preview' alt='' valign='middle' /></td>
<td width='1%' align='left'><select name='payment_image' class='textbox' style='width:200px;' onChange=\"document.payment_image_preview.src = '".PAYMENT_DIR."' + document.inputform.payment_image.options[document.inputform.payment_image.selectedIndex].value;\">
<option value='".$payment_image."' ".($payment_image == "$payment_image" ? " selected" : "").">".$payment_image."</option>
$payment_list</select></td></tr>
<tr><td width='1%' align='left'>".$locale['ESHPPMTS100']."</td><td width='1%' align='left'><input type='text' name='method' value='$method' class='textbox' style='width:190px;'>
	<a href='javascript:;' class='info'><span>
	".$locale['ESHPPMTS101']."
	</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";

echo "<tr><td width='1%' align='left'>".$locale['ESHPPMTS102']."</td><td width='1%' align='left'><input type='text' name='surcharge' value='$surcharge' class='textbox' style='width:50px;'> ".$settings['eshop_currency']."
	<a href='javascript:;' class='info'><span>
	".$locale['ESHPPMTS103']."
	</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";

echo "<tr><td align='left'>".$locale['ESHPPMTS104']."</td><td>";
    $payment_dir = makefilelist("../paymentscripts/",  ".|..|index.php", true, "files");
  echo "<select name='cfile' class='textbox' style='width:180px;' >";
  echo "<option value=''>".$locale['ESHPPMTS105']."</option>";
     foreach($payment_dir as $paymentfile){
  echo "<option value='$paymentfile' ".($paymentfile == "$cfile" ? " selected" : "").">$paymentfile</option>";
 }
echo "</select>

<a href='javascript:;' class='info'><span>
".$locale['ESHPPMTS106']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";

echo "<tr><td align='left' valign='top'>".$locale['ESHPPMTS107']."</td><td align='left'><textarea name='description' cols='30' rows='4' class='textbox'>$description</textarea>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPMTS108']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:top;' /></a>
</td></tr>";

echo "<tr><td align='left'>".$locale['ESHPPMTS109']."</td><td align='left'><select name='active' class='textbox'>
    <option value='1'".($active == "1" ? " selected" : "").">".$locale['ESHPPMTS110']."</option>
    <option value='0'".($active == "0" ? " selected" : "").">".$locale['ESHPPMTS111']."</option>
    </select>
	<a href='javascript:;' class='info'><span>
	".$locale['ESHPPMTS112']."
	</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
	</td></tr>";
echo "</table></div>";

    require_once INCLUDES."html_buttons_include.php";
    echo "<div style='float:right;width:50%;'><table align='left' cellspacing='0' width='100%' cellpadding='0' class='tbl'>";
	echo "<tr><td width='100%' class='tbl'>".$locale['ESHPPMTS113']."
	<a href='javascript:;' class='info'><span>
	".$locale['ESHPPMTS114']."
	</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
	<br /><textarea name='code' cols='45' rows='15' class='textbox' style='width:98%'>".$code."</textarea></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'>\n";
	echo "<input type='button' value='&lt;?php?&gt;' class='button' onclick=\"addText('code', '&lt;?php\\n', '\\n?&gt;');\" />\n";
	echo "<input type='button' value='&lt;p&gt;' class='button' onclick=\"addText('code', '&lt;p&gt;', '&lt;/p&gt;');\" />\n";
	echo "<input type='button' value='&lt;br /&gt;' class='button' onclick=\"insertText('code', '&lt;br /&gt;');\" />\n";
	    echo display_html("inputform", "code", true)."</td>\n";
	echo "</tr>\n";
echo "</table></div>";
echo "<div class='clear'></div>";
echo "<center><input type='submit'name='save_payment' value='".$locale['ESHPPMTS115']."' class='button'></center></form>\n";
		 */
	}

}


$payment = new eShop_payment();






if (isset($_GET['step']) && $_GET['step'] == "delete") {
$result = dbquery("DELETE FROM ".DB_ESHOP_PAYMENTS." WHERE pid='".$_GET['payid']."'");
} 
if (isset($_POST['save_payment'])) {
	$method = stripinput($_POST['method']);
	$description = stripinput($_POST['description']);
	$payment_image = stripinput($_POST['payment_image']);
	$surcharge = stripinput($_POST['surcharge']);
	$code = addslashes($_POST['code']);
	$cfile = stripinput($_POST['cfile']);
	$active = stripinput($_POST['active']);

if (isset($_GET['step']) && $_GET['step'] == "edit") {
$result = dbquery("UPDATE ".DB_ESHOP_PAYMENTS." SET method='$method',description='$description',image='$payment_image',surcharge='$surcharge',code='$code',cfile='$cfile',active='$active' WHERE pid ='".$_GET['payid']."'");
} else {
$result = dbquery("INSERT INTO ".DB_ESHOP_PAYMENTS." (pid,method,description,image,surcharge,code,cfile,active) VALUES('','$method','$description','$payment_image','$surcharge','$code','$cfile','$active')");
}
redirect(FUSION_SELF.$aidlink."&amp;a_page=payments");
}

if (isset($_GET['step']) && $_GET['step'] == "edit") {
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_PAYMENTS." WHERE pid='".$_GET['payid']."'"));
	$pid = $data['pid'];
	$method = $data['method'];
	$description = $data['description'];
	$payment_image = $data['image'];
	$surcharge = $data['surcharge'];
	$code = stripslashes($data['code']);
	$cfile = $data['cfile'];
	$active = $data['active'];
	$formaction = FUSION_SELF.$aidlink."&amp;a_page=payments&amp;step=edit&amp;payid=".$data['pid'];
} else {
	$pid = "";
	$method = "";
	$description = "";
	$payment_image = "";
	$surcharge = "";
	$code = "";
	$cfile = "";
	$active = "";
	$payment_image = "invoice.png";
	$formaction = FUSION_SELF.$aidlink."&amp;a_page=payments";
}


echo "<div class='clear'></div>";
echo "<hr />";

$result = dbquery("SELECT * FROM ".DB_ESHOP_PAYMENTS."");
$rows = dbrows($result);
if ($rows != 0) {

echo "<br /><table align='center' cellspacing='4' cellpadding='0' class='tbl-border' width='99%'><tr>
<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPPMTS116']."</b></td>
<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPPMTS117']."</b></td>
</tr>\n";




$result = dbquery("SELECT * FROM ".DB_ESHOP_PAYMENTS." ORDER BY method ASC, method LIMIT ".$_GET['rowstart'].",25");
while ($data = dbarray($result)) {
echo "<tr style='height:20px;' onMouseOver=\"this.className='tbl2'\" onMouseOut=\"this.className='tbl1'\">";
echo "<td align='center' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=payments&amp;step=edit&amp;payid=".$data['pid']."'><b>".$data['method']."</b></a></td>\n";
echo "<td align='center' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=payments&amp;step=delete&amp;payid=".$data['pid']."' onClick='return confirmdelete();'><img src='".BASEDIR."eshop/img/remove.png' border='0' height='20' style='vertical-align:middle;'  alt='' /></a></td></tr>";
} 
echo "</table>\n";
echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'],25,$rows,3,FUSION_SELF.$aidlink."&amp;payments&amp;")."\n</div>\n";
} else {
echo "<div class='admin-message'>".$locale['ESHPPMTS118']."</div>\n";
}
