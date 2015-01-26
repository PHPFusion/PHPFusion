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
	private $data = array(
		'method'=>'',
		'surcharge'=>'',
		'cfile'=>'',
	);
	private $formaction = '';
	private $max_rowstart = 0;
	private $payment_image = 'paypal.png';

	public function __construct() {
		global $aidlink;
		define("PAYMENT_DIR", BASEDIR."eshop/paymentimgs/");
		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		$this->max_rowstart = dbcount("(cuid)", DB_ESHOP_CUSTOMERS);

		switch($_GET['action']) {
			case 'edit':
				$this->formaction = FUSION_SELF.$aidlink."&amp;a_page=customers&amp;action=edit&amp;section=customerform&amp;cuid=".$_GET['cuid'];
				$this->data = self::payment_data();
				break;
			case 'delete':
				self::delete_payment($_GET['cuid']);
				break;
			default:
				$this->formaction = FUSION_SELF.$aidlink."&amp;a_page=customers&amp;section=customerform";
		}
	}

	static function get_paymentOpts() {
		$payment_files = makefilelist(PAYMENT_DIR, ".|..|index.php", true);
		$payment_list = array();
		foreach($payment_files as $file) {
			$payment_list[$file] = $file;
		}
		return $payment_list;
	}


	static function get_paymentFile() {
		$payment_cfile = array();
		if (file_exists('../paymentscripts')) {
			$payment_dir = makefilelist("../paymentscripts/",  ".|..|index.php", true, "files");
			foreach($payment_dir as $paymentfile) {
				$payment_cfile[$paymentfile] = $paymentfile;
			}
		}
		return $payment_cfile;
	}

	private function payment_data() {
		$result = dbquery("SELECT * FROM ".DB_ESHOP_CUSTOMERS." WHERE cuid='".$_GET['cuid']."'");
		if (dbrows($result)>0) {
			return dbarray($result);
		}
		return array();
	}

	static function delete_payment($cuid) {
		global $aidlink;
		if (isnum($cuid)) {
			if (self::verify_customer($cuid)) {
				dbquery("DELETE FROM ".DB_ESHOP_CUSTOMERS." WHERE cuid='".intval($cuid)."'");
				redirect(FUSION_SELF.$aidlink."&amp;a_page=customers");
			}
		}
	}

	static function verify_payment($id) {
		if (isnum($id)) {
			return dbcount("(cuid)", DB_ESHOP_CUSTOMERS." c INNER JOIN ".DB_USERS." u on c.cuid=u.user_id", "cuid='".intval($id)."'");
		}
		return false;
	}

	public function payment_listing() {

	}


	public function add_payment_form() {
		global $locale;
		echo "<div class='m-t-20'>\n";
		echo openform('paymentform', 'ipaymentform', 'post', $this->formaction);

		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8'>\n";
		openside('');
		echo thumbnail(PAYMENT_DIR.$this->payment_image, '70px');
		echo "<div class='overflow-hide p-l-15'>\n";
		echo form_select('Payment Type', 'payment_image', 'payment_image',  self::get_paymentOpts(), '');
		add_to_jquery("
		$('#payment_image').bind('change', function(e) {
			$('.thumb > img').prop('src', '".PAYMENT_DIR."'+ $(this).val());
		});
		");
		echo "</div>\n";
		closeside();

		openside('');
		echo form_text($locale['ESHPPMTS100'], 'method', 'method', $this->data['method'], array('inline'=>1, 'required'=>1, 'tip'=>$locale['ESHPPMTS101']));
		echo form_text($locale['ESHPPMTS102'], 'surcharge', 'surcharge', $this->data['surcharge'], array('inline'=>1, 'required'=>1, 'tip'=>$locale['ESHPPMTS103']));
		echo form_select($locale['ESHPPMTS104'], 'cfile', 'cfile', self::get_paymentFile(), $this->data['cfile'], array('inline'=>1, 'required'=>1, 'tip'=>$locale['ESHPPMTS106']));
		closeside();

		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4'>\n";
		echo "</div>\n";
		echo closeform();
		echo "</div>\n";



		/*
		 * echo "<form name='inputform' method='post' action='$formaction'>
<div style='float:left;width:50%'>
<table align='left' cellspacing='0' width='100%' cellpadding='0' class='tbl'>


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
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $customer->verify_payment($_GET['cuid']) : 0;
$tab_title['title'][] = 'Current Payment Method'; //$locale['ESHPCUPNS100'];
$tab_title['id'][] = 'payment';
$tab_title['icon'][] = '';
$tab_title['title'][] =  $edit ? 'Edit Payment Method' : 'Add Payment Method'; // $locale['ESHPCUPNS115'] : $locale['ESHPCUPNS114'];
$tab_title['id'][] = 'paymentform';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1, 1);
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=payments");
echo opentabbody($tab_title['title'][0], 'payment', $tab_active, 1);
$payment->payment_listing();
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'paymentform') {
	echo opentabbody($tab_title['title'][1], 'paymentform', $tab_active, 1);
	$payment->add_payment_form();
	echo closetabbody();
}






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
