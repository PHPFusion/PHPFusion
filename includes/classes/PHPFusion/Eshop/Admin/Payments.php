<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: Admin/Payments.php
| Author: Frederick MC Chan (hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Eshop\Admin;
if (!defined("IN_FUSION")) { die("Access Denied"); }

class Payments {
	private $data = array(
		'pid' => 0,
		'method'=>'',
		'image' => '',
		'active' => 1,
		'surcharge'=>'',
		'cfile'=>'',
		'description' => '',
		'code' => '',
	);
	private $formaction = '';
	private $max_rowstart = 0;

	public function __construct() {
		global $aidlink;
		define("PAYMENT_DIR", BASEDIR."eshop/paymentimgs/");
		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		$this->max_rowstart = dbcount("(pid)", DB_ESHOP_PAYMENTS);
		switch($_GET['action']) {
			case 'edit':
				$this->formaction = FUSION_SELF.$aidlink."&amp;a_page=payments&amp;action=edit&amp;section=paymentform&amp;pid=".$_GET['pid'];
				$this->data = self::get_payment($_GET['pid']);
				break;
			case 'delete':
				self::delete_payment($_GET['pid']);
				break;
			default:
				$this->formaction = FUSION_SELF.$aidlink."&amp;a_page=payments&amp;section=paymentform";
		}
		self::set_paymentdb();

	}

	static function get_paymentOpts() {
		$payment_files = makefilelist(PAYMENT_DIR, ".|..|index.php", true);
		$payment_list = array();
		foreach($payment_files as $file) {
			$payment_list[$file] = $file;
		}
		return $payment_list;
	}

	static function get_activeOpts() {
		global $locale;
		return array(
			'1'=> $locale['yes'],
			'0'=> $locale['no']
		);
	}

	static function get_paymentFile() {
		$payment_file = array();
		if (file_exists(BASEDIR.'eshop/paymentscripts')) {
			$payment_dir = makefilelist(BASEDIR."eshop/paymentscripts/",  ".|..|index.php", true, "files");
			foreach($payment_dir as $paymentfile) {
				$payment_file[$paymentfile] = $paymentfile;
			}
		}
		return $payment_file;
	}

	public static function get_payment($pid) {
		if (isnum($pid)) {
			$result = dbquery("SELECT * FROM ".DB_ESHOP_PAYMENTS." WHERE pid='".intval($pid)."'");
			if (dbrows($result)>0) {
				return dbarray($result);
			}
		}
		return array();
	}

	static function delete_payment($pid) {
		global $aidlink;
		if (isnum($pid)) {
			if (self::verify_payment($pid)) {
				dbquery("DELETE FROM ".DB_ESHOP_PAYMENTS." WHERE pid='".intval($pid)."'");
				redirect(FUSION_SELF.$aidlink."&amp;a_page=payments");
			}
		}
	}

	static function verify_payment($id) {
		if (isnum($id)) {
			return dbcount("(pid)", DB_ESHOP_PAYMENTS, "pid='".intval($id)."'");
		}
		return false;
	}

	private function set_paymentdb() {
		global $aidlink;
		if (isset($_POST['save_payment'])) {
			$this->data['pid'] = isset($_POST['pid']) ? form_sanitizer($_POST['pid'], '0', 'pid') : 0;
			$this->data['method'] = isset($_POST['method']) ? form_sanitizer($_POST['method'], '', 'method') : '';
			$this->data['image'] = isset($_POST['image']) ? form_sanitizer($_POST['image'], '', 'image') : '';
			$this->data['active'] = isset($_POST['active']) ? form_sanitizer($_POST['active'], '0', 'active') : 0;
			$this->data['surcharge'] = isset($_POST['surcharge']) ? form_sanitizer($_POST['surcharge'], '', 'surcharge') : '';
			$this->data['cfile'] = isset($_POST['cfile']) ? form_sanitizer($_POST['cfile'], '', 'cfile') : '';
			$this->data['description'] = isset($_POST['description']) ? form_sanitizer($_POST['description'], '') : '';
			$this->data['code'] = isset($_POST['code']) ? form_sanitizer($_POST['code'], '', 'code') : '';
			if (self::verify_payment($this->data['pid'])) { // this is update
				// find the category
				dbquery_insert(DB_ESHOP_PAYMENTS, $this->data, 'update');
				if (!defined("FUSION_NULL")) redirect("".FUSION_SELF.$aidlink."&amp;a_page=payments&status=su");
			} else { // this is save
				dbquery_insert(DB_ESHOP_PAYMENTS, $this->data, 'save');
				if (!defined("FUSION_NULL")) redirect("".FUSION_SELF.$aidlink."&amp;a_page=payments&status=sn");
			}
		}
	}

	public function payment_listing() {
		global $locale, $aidlink;
		echo "<div class='m-t-20'>\n";
		echo "<table class='table table-striped table-responsive'><tr>
		<th>".$locale['ESHPPMTS116']."</th>\n
		<th>".$locale['ESHPPMTS117']."</th>\n
		</tr>\n";
		$result = dbquery("SELECT * FROM ".DB_ESHOP_PAYMENTS." ORDER BY method ASC, method LIMIT ".$_GET['rowstart'].",25");
		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				echo "<tr>";
				echo "<td><a href='".FUSION_SELF.$aidlink."&amp;a_page=payments&amp;section=paymentform&amp;action=edit&amp;pid=".$data['pid']."'><b>".$data['method']."</b></a></td>\n";
				echo "<td><a href='".FUSION_SELF.$aidlink."&amp;a_page=payments&amp;section=paymentform&amp;action=delete&amp;pid=".$data['pid']."' onClick='return confirmdelete();'>".$locale['delete']."</a></td>\n";
				echo "</tr>";
			}
		} else {
			echo "<tr><td colspan='2' class='text-center'><div class='alert alert-warning m-t-10'>".$locale['ESHPPMTS118']."</div></td></tr>\n";
		}
		echo "</table>\n";
		echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'],25,$this->max_rowstart,3,FUSION_SELF.$aidlink."&amp;a_page=payments&amp;")."\n</div>\n";
		echo "</div>\n";
	}

	public function add_payment_form() {
		global $locale;
		echo "<div class='m-t-20'>\n";
		echo openform('paymentform', 'post', $this->formaction, array('max_tokens' => 1));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8'>\n";
		openside('');
		echo thumbnail(PAYMENT_DIR.$this->data['image'], '70px');
		echo "<div class='overflow-hide p-l-15'>\n";
		echo form_select('image', 'Payment Type', self::get_paymentOpts(), $this->data['image']);
		add_to_jquery("
		$('#image').bind('change', function(e) {
			$('.thumb > img').prop('src', '".PAYMENT_DIR."'+ $(this).val());
		});
		");
		echo "</div>\n";
		closeside();
		openside('');
		echo form_hidden('', 'pid', 'pid', $this->data['pid']);
		echo form_text('method', $locale['ESHPPMTS100'], $this->data['method'], array('inline'=>1, 'required'=>1, 'tip'=>$locale['ESHPPMTS101']));
		echo form_text('surcharge', $locale['ESHPPMTS102'], $this->data['surcharge'], array('inline'=>1, 'required'=>1, 'tip'=>$locale['ESHPPMTS103']));
		echo form_select('cfile', $locale['ESHPPMTS104'], self::get_paymentFile(), $this->data['cfile'], array('inline'=>1, 'required'=>1, 'tip'=>$locale['ESHPPMTS106']));
		closeside();
		openside('');
		echo form_textarea('description', $locale['ESHPPMTS107'], $this->data['description'], array('inline'=>1, 'autosize'=>1, 'tip'=>$locale['ESHPPMTS108']));
		closeside();
		openside('');
		echo form_textarea('code', $locale['ESHPPMTS113'], $this->data['code'], array('tip'=>$locale['ESHPPMTS114'], 'placeholder'=>'<?php .... ?>', 'html'=>1, 'preview'=>1, 'form_name'=>'paymentform', 'autosize'=>1));
		closeside();
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4'>\n";
		openside('');
		echo form_select('active', $locale['ESHPPMTS109'], self::get_activeOpts(), $this->data['active'], array('tip'=>$locale['ESHPPMTS112']));
		echo form_button('save_payment', $locale['save'], $locale['save'], array('class'=>'btn-success', 'icon'=>'fa fa-check-square-o'));
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo form_button('save_payment', $locale['save'], $locale['save'], array('class'=>'btn-success', 'icon'=>'fa fa-check-square-o'));
		echo closeform();
		echo "</div>\n";
	}
}
