<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: customers.php
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

class eShop_customer {

	private $data = array(
		'cuid' => 0,
		'cfirstname' => '',
		'clastname' => '',
		'cdob' => '',
		'ccountry' => '',
		'cregion' => '',
		'ccity' => '',
		'caddress' => '',
		'caddress2' => '',
		'cpostcode' => '',
		'cphone' => '',
		'cfax' => '',
		'cemail' => '',
		'ccupons' => '',
	);
	private $formaction = '';

	public function __construct() {
		global $aidlink;
		$_GET['cuid'] = isset($_GET['cuid']) && isnum($_GET['cuid']) ? $_GET['cuid'] : 0;
		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		switch($_GET['action']) {
			case 'edit':
				$this->formaction = FUSION_SELF.$aidlink."&amp;a_page=customers&amp;step=edit&amp;cuid=".$_GET['cuid'];
				$this->data = self::customer_data();
				break;
			default:
				$this->formaction = FUSION_SELF.$aidlink."&amp;a_page=customers";
		}

		self::set_customerdb();

	}

	static function delete_customer($cuid) {
		if (isnum($cuid)) {
			if (self::verify_customer($cuid)) {
				dbquery("DELETE FROM ".DB_ESHOP_CUSTOMERS." WHERE cuid='".intval($cuid)."'");
			}
		}
	}

	static function verify_customer($id) {
		if (isnum($id)) {
			return dbcount("(cuid)", DB_ESHOP_CUSTOMERS." c INNER JOIN ".DB_USERS." u on c.cuid=u.user_id", "cuid='".intval($id)."'");
		}
		return false;
	}

	private function customer_data() {
		$result = dbquery("SELECT * FROM ".DB_ESHOP_CUSTOMERS." WHERE cuid='".$_GET['cuid']."'");
		if (dbrows($result)>0) {
			return dbarray($result);
		}
		return array();
	}

	private function set_customerdb() {
		global $aidlink;
		if (isset($_POST['save'])) {
			var_dump($_POST);
			$this->data['cuid'] = isset($_POST['cuid']) ? form_sanitizer($_POST['cuid'], '', 'cuid') : 0;

			$this->data['cfirstname'] = isset($_POST['cfirstname']) ? form_sanitizer($_POST['cfirstname'], '', 'cfirstname') : '';
			$this->data['clastname'] = isset($_POST['clastname']) ? form_sanitizer($_POST['clastname'], '', 'clastname') : '';

			$this->data['cdob'] = isset($_POST['cdob']) ? form_sanitizer($_POST['cdob'], '', 'cdob') : '';

			$this->data['caddress'] = isset($_POST['caddress']) ? form_sanitizer($_POST['caddress'], '', 'caddress') : '';
			$this->data['caddress2'] = isset($_POST['caddress2']) ? form_sanitizer($_POST['caddress2'], '', 'caddress2') : '';
			$this->data['cregion'] = isset($_POST['cregion']) ? form_sanitizer($_POST['cregion'], '', 'cregion') : ''; // country
			$this->data['cstate'] = isset($_POST['cstate']) ? form_sanitizer($_POST['cstate'], '', 'cstate') : '';
			$this->data['ccity'] = isset($_POST['ccity']) ? form_sanitizer($_POST['ccity'], '', 'ccity') : '';
			$this->data['cpostcode'] = isset($_POST['cpostcode']) ? form_sanitizer($_POST['cpostcode'], '', 'cpostcode') : '';

			$this->data['ccountry_code'] = isset($_POST['ccountry_code']) ? form_sanitizer($_POST['ccountry_code'], '', 'ccountry_code') : '';

			$this->data['cphone'] = isset($_POST['cphone']) ? form_sanitizer($_POST['cphone'], '', 'cphone') : '';
			$this->data['cfax'] = isset($_POST['cfax']) ? form_sanitizer($_POST['cfax'], '', 'cfax') : '';
			$this->data['cemail'] = isset($_POST['cemail']) ? form_sanitizer($_POST['cemail'], '', 'cemail') : '';
			$this->data['ccupons'] = isset($_POST['ccupons']) ? form_sanitizer($_POST['ccupons'], '', 'ccupons') : '';

			if (self::verify_customer($this->data['cuid'])) {
				//dbquery_insert(DB_ESHOP_CUSTOMERS, $this->data, 'update');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=customers&amp;status=su");
			} else {
				print_p($this->data);
				//dbquery_insert(DB_ESHOP_CUSTOMERS, $this->data, 'save');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=customers&amp;status=sn");
			}
		}
	}

	static function getcodename($code) {
		$datacatar = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_COUPONS." WHERE cuid='".$code."' LIMIT 0,1"));
		return $datacatar['cuname'];
	}

	public function add_customer_form() {
		global $locale, $aidlink;
		echo "<div class='m-t-20'>\n";
		openform('customerform', 'customerform', 'post', $this->formaction);
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8'>\n";
		$customer_name[] = $this->data['cfirstname'];
		$customer_name[] = $this->data['clastname'];
		$customer_name = implode('|', $customer_name);
		echo form_name('Customer Name', 'cname', 'cname', $customer_name, array('required'=>1, 'inline'=>1));
		echo form_text($locale['ESHPCHK115'], 'cemail', 'cemail', $this->data['cemail'], array('inline'=>1, 'email'=>1));
		echo form_datepicker($locale['ESHPCHK105'], 'cdob', 'cdob', $this->data['cdob'], array('inline'=>1));
		$customer_address[] = $this->data['caddress'];
		$customer_address[] = $this->data['caddress2'];
		$customer_address[] = $this->data['ccountry'];
		$customer_address[] = $this->data['cregion'];
		$customer_address[] = $this->data['ccity'];
		$customer_address[] = $this->data['cpostcode'];
		$customer_address = implode('|', $customer_address);
		echo form_address($locale['ESHPCHK106'], 'caddress', 'caddress', $customer_address, array('required'=>1, 'inline'=>1));
		echo form_text($locale['ESHPCHK113'], 'cphone', 'cphone', $this->data['cphone'], array('inline'=>1, 'number'=>1));
		echo form_text($locale['ESHPCHK114'], 'cfax', 'cfax', $this->data['cfax'], array('inline'=>1, 'number'=>1));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-4'>\n";
		openside('');
		echo form_user_select($locale['ESHPCHK156'], 'cuid', 'cuid', $this->data['cuid'], array('required'=>1));
		// to do an import button here
		echo form_button('Find Records', 'import', 'import', 'find', array('class'=>'btn-default m-r-10', 'type'=>'button'));
		echo form_button($locale['save'], 'save', 'save', $locale['save'], array('class'=>'btn-primary'));
		echo form_hidden('', 'ccupons', 'ccupons', $this->data['ccupons']);
		closeside();
		openside();
		echo "<p class='strong'>".$locale['ESHPCHK187']."</p>\n";
		if (!empty($this->data['ccupons'])) {
			echo "<div class='list-group-item'>\n";
			$cuponcodes = explode(".", substr($this->data['ccupons'], 1));
			for ($i = 0;$i < count($cuponcodes);$i++) {
				echo "".self::getcodename($cuponcodes[$i])." | <a href='".FUSION_SELF.$aidlink."&amp;a_page=customers&amp;step=deletecode&amp;cuid=".$userid."&amp;cupon=".$cuponcodes[$i]."' onClick='return confirmdelete();'> ".$locale['ESHPCHK186']." </a>";
			}
			echo "</div>\n";
		}
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo form_button($locale['save'], 'save', 'save', $locale['save'], array('class'=>'btn-primary'));
		closeform();
		echo "</div>\n";
		echo "</div>\n";
	}

	public function customer_listing() {
		function customer_listing() {
			global $locale, $aidlink;
			/*
			 * $result = dbquery("SELECT * FROM ".DB_ESHOP_CUSTOMERS."");
	$rows = dbrows($result);
	if ($rows != 0) {
	echo "<table align='center' cellspacing='4' cellpadding='0' class='tbl-border' width='99%'><tr>
	<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPCHK149']."</b></td>
	<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPCHK150']."</b></td>
	<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPCHK151']."</b></td>
	<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPCHK152']."</b></td>
	<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPCHK153']."</b></td>
	</tr>\n";

	$result = dbquery("SELECT * FROM ".DB_ESHOP_CUSTOMERS." ORDER BY cfirstname ASC LIMIT ".$_GET['rowstart'].",25");
	while ($data = dbarray($result)) {
	echo "<tr style='height:20px;' onMouseOver=\"this.className='tbl2'\" onMouseOut=\"this.className='tbl1'\">";
	echo "<td align='center' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=customers&amp;step=edit&amp;cuid=".$data['cuid']."'><b>".$data['cfirstname']." ".$data['clastname']."</b></a></td>\n";
	echo "<td align='center' width='1%'><a href='mailto:".$data['cemail']."'>".$data['cemail']."</a></td>\n";
	echo "<td align='center' width='1%'>".$data['cphone']."</td>\n";
	echo "<td align='center' width='1%'><a href='".BASEDIR."profile.php?lookup=".$data['cuid']."'><b>".$data['cuid']."</b></a></td>\n";
	echo "<td align='center' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=customers&amp;step=delete&amp;cuid=".$data['cuid']."' onClick='return confirmdelete();'><img src='".BASEDIR."eshop/img/remove.png' border='0' height='20' style='vertical-align:middle;'  alt='' /></a></td></tr>";
	}
	echo "</table>\n";
	echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'],25,$rows,3,FUSION_SELF.$aidlink."&amp;customers&amp;")."\n</div>\n";
	} else {
	echo "<div class='admin-message'>".$locale['ESHPCHK154']."</div>\n";
	}
			 */
			self::coupon_view_filters();
			add_to_jquery("
		$('.actionbar').hide();
		$('tr').hover(
			function(e) { $('#coupon-'+ $(this).data('id') +'-actions').show(); },
			function(e) { $('#coupon-'+ $(this).data('id') +'-actions').hide(); }
		);
		$('.qform').hide();
		$('.qedit').bind('click', function(e) {
			$.ajax({
				url: '".ADMIN."includes/eshop_coupon.php',
				dataType: 'json',
				type: 'post',
				data: { q: $(this).data('id'), token: '".$aidlink."' },
				success: function(e) {
					$('#cuids').val(e.cuid);
					$('#cunames').val(e.cuname);
					$('#cuvalues').val(e.cuvalue);
					$('#actives').select2('val', e.active);
					$('#cutypes').select2('val', e.cutype);
				},
				error : function(e) {
					console.log(e);
				}
			});
			$('.qform').show();
			$('.list-result').hide();
		});
		$('#cancel').bind('click', function(e) {
			$('.qform').hide();
			$('.list-result').show();
		});
		");
			echo "<div class='m-t-20'>\n";
			echo "<table class='table table-responsive'>\n";
			echo "<tr>\n";
			echo "<th></th>\n";
			echo "<th>".$locale['ESHPCHK170']."</th>\n";
			echo "<th>".$locale['ESHPCUPNS102']."</th>\n";
			echo "<th>".$locale['ESHPCUPNS104']."</th>\n";
			echo "<th>".$locale['ESHPCUPNS107']."</th>\n";
			echo "<th>".$locale['ESHPCUPNS105']."</th>\n";
			echo "<th>".$locale['ESHPCUPNS106']."</th>\n";
			echo "</tr>\n";
			echo "<tr class='qform'>\n";
			echo "<td colspan='6'>\n";
			echo "<div class='list-group-item m-t-20 m-b-20'>\n";
			echo openform('quick_edit', 'quick_edit', 'post', FUSION_SELF.$aidlink."&amp;a_page=coupons", array('downtime' => 0, 'notice' => 0));
			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-5'>\n";
			echo form_text($locale['ESHPCUPNS102'], 'cuname', 'cunames', $this->data['cuname'], array('inline'=>1, 'required'=>1));
			echo form_select($locale['ESHPCUPNS107'], 'active', 'actives', self::getCouponStatus(), $this->data['active'], array('inline'=>1));
			echo "</div>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-7'>\n";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-3'>\n";
			echo "<label class='control-label'>".$locale['ESHPCUPNS117']."</label>\n";
			echo "</div>\n";
			echo "<div class='col-xs-12 col-sm-9'>\n";
			echo "<div class='display-inline-block m-r-10 col-xs-12 col-sm-6 p-l-0'>\n";
			echo form_text($locale['ESHPCUPNS104'], 'cuvalue', 'cuvalues', $this->data['cuvalue'], array('number'=>1, 'required'=>1));
			echo "<span class='text-smaller'>".$locale['ESHPCUPNS118']."</span>\n";
			echo "</div>\n";
			echo "<div class='display-inline-block'>\n";
			echo form_select($locale['ESHPCUPNS103'], 'cutype', 'cutypes', self::getCouponType(), $this->data['cutype']);
			echo "</div>\n";
			echo "</div>\n</div>\n";
			echo form_hidden('', 'cuid', 'cuids', '', array('writable' => 1));
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='m-t-10 m-b-10'>\n";
			echo form_button($locale['cancel'], 'cancel', 'cancel', 'cancel', array('class' => 'btn btn-default m-r-10',
				'type' => 'button'));
			echo form_button($locale['update'], 'coupon_quicksave', 'cats_quicksave', 'save', array('class' => 'btn btn-primary'));
			echo "</div>\n";
			echo closeform();
			echo "</div>\n";
			echo "</td>\n";
			echo "</tr>\n";
			$result = dbquery("SELECT * FROM ".DB_ESHOP_COUPONS." WHERE ".$this->filter_Sql." LIMIT ".$_GET['rowstart'].",15");
			$rows = dbrows($result);
			if ($rows) {
				$i = 0;
				echo "<tbody id='eshopitem-links' class='connected'>\n";
				while ($data = dbarray($result)) {
					$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
					echo "<tr id='listItem_".$data['cuid']."' data-id='".$data['cuid']."' class='list-result ".$row_color."'>\n";
					echo "<td></td>\n";
					echo "<td>\n";
					echo "<strong>".$data['cuid']."</strong>\n";
					echo "<div class='actionbar text-smaller' id='coupon-".$data['cuid']."-actions'>
					<a href='".FUSION_SELF.$aidlink."&amp;a_page=coupons&amp;section=couponform&amp;action=edit&amp;cuid=".$data['cuid']."'>".$locale['edit']."</a> |
					<a class='qedit pointer' data-id='".$data['cuid']."'>".$locale['qedit']."</a> |
					<a class='delete' href='".FUSION_SELF.$aidlink."&amp;a_page=coupons&amp;action=delete&amp;cuid=".$data['cuid']."' onclick=\"return confirm('".$locale['ESHPCATS134']."');\">".$locale['delete']."</a>
					</div>\n";
					echo "</td>\n";
					echo "<td>".$data['cuname']."</td>";
					//echo "<td>".$data['cuvalue']." ".$coupon_type[$data['cutype']]."</td>";
					//echo "<td>".$coupon_status[$data['active']]."</td>";
					echo "<td>".showdate("forumdate", $data['custart'])."</td>";
					echo "<td>".showdate("forumdate", $data['cuend'])."</td>";
					echo "</tr>";
					$i++;
				}
				echo "</tbody>\n";
			} else {
				echo "<tr><td colspan='7' class='text-center'><div class='alert alert-warning m-t-10'>".$locale['ESHPCUPNS110']."</div></td></tr>\n";
			}
			echo "</table>\n";
			if ($this->max_rowstart > $rows) {
				echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'],15,$rows,3,FUSION_SELF.$aidlink."&amp;cupons&amp;")."\n</div>\n";
			}
			echo "</div>\n";
		}
	}
}

$customer = new eShop_customer();
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $customer->verify_customer($_GET['cuid']) : 0;
$tab_title['title'][] = 'Current Customers'; //$locale['ESHPCUPNS100'];
$tab_title['id'][] = 'customer';
$tab_title['icon'][] = '';
$tab_title['title'][] =  $edit ? 'Edit Customer' : 'Add Customer'; // $locale['ESHPCUPNS115'] : $locale['ESHPCUPNS114'];
$tab_title['id'][] = 'customerform';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1, 1);
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=customers");
echo opentabbody($tab_title['title'][0], 'coupon', $tab_active, 1);
//$customer->customer_listing();
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'customerform') {
	echo opentabbody($tab_title['title'][1], 'customerform', $tab_active, 1);
	$customer->add_customer_form();
	echo closetabbody();
}


if (isset($_GET['step']) && $_GET['step'] == "deletecode") {
	$codetoremove = dbarray(dbquery("SELECT ccupons FROM ".DB_ESHOP_CUSTOMERS." WHERE cuid='".$_GET['cuid']."'"));
	if (!preg_match("/^[-0-9A-ZÅÄÖ._@\s]+$/i", $_GET['cupon'])) { die("Denied"); exit; }
	$cuponcodes = preg_replace(array("(^\.{$_GET['cupon']}$)","(\.{$_GET['cupon']}\.)","(\.{$_GET['cupon']}$)"), array("",".",""), $codetoremove['ccupons']);
	$result = dbquery("UPDATE ".DB_ESHOP_CUSTOMERS." SET ccupons='".$cuponcodes."' WHERE cuid='".$_GET['cuid']."'");
	redirect(FUSION_SELF.$aidlink."&amp;a_page=customers");
}




?>