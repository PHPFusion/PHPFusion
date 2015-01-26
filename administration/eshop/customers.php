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
	private $max_rowstart = 0;

	public function __construct() {
		global $aidlink;
		$_GET['cuid'] = isset($_GET['cuid']) && isnum($_GET['cuid']) ? $_GET['cuid'] : 0;
		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		$this->max_rowstart = dbcount("(cuid)", DB_ESHOP_CUSTOMERS);

		switch($_GET['action']) {
			case 'edit':
				$this->formaction = FUSION_SELF.$aidlink."&amp;a_page=customers&amp;action=edit&amp;section=customerform&amp;cuid=".$_GET['cuid'];
				$this->data = self::customer_data();
				break;
			case 'delete':
				self::delete_customer($_GET['cuid']);
				break;
			default:
				$this->formaction = FUSION_SELF.$aidlink."&amp;a_page=customers&amp;section=customerform";
		}

		self::set_customerdb();
		self::quick_save();
	}

	// quick save
	static function quick_save() {
		global $aidlink, $defender;
		if (isset($_POST['customer_quicksave'])) {
			$quick['cuid'] = isset($_POST['cuid']) ? form_sanitizer($_POST['cuid'], '0', 'cuid') : 0;
			$quick['cphone'] = isset($_POST['cphone']) ? form_sanitizer($_POST['cphone'], '', 'cphone') : '';
			$quick['cfax'] = isset($_POST['cfax']) ? form_sanitizer($_POST['cfax'], '', 'cfax') : 0;
			$address = isset($_POST['caddress']) ? form_sanitizer($_POST['caddress'], '', 'caddress') : '';
			if (!empty($address)) {
				$address = explode('|', $address);
				$quick['caddress'] = $address[0];
				$quick['caddress2'] = $address[1];
				$quick['ccountry'] = $address[2];
				$quick['cregion'] = $address[3];
				$quick['ccity'] = $address[4];
				$quick['cpostcode'] = $address[5];
			}

			if ($quick['cuid'] && $address) {
				$c_result = dbquery("SELECT * FROM ".DB_ESHOP_CUSTOMERS." WHERE cuid='".$quick['cuid']."'");
				if (dbrows($c_result) > 0) {
					$quick += dbarray($c_result);
					dbquery_insert(DB_ESHOP_CUSTOMERS, $quick, 'update', array('no_unique'=>1, 'primary_key'=>'cuid'));
					redirect(FUSION_SELF.$aidlink."&amp;a_page=customers");
				}
			}
		}
	}

	static function delete_customer($cuid) {
		global $aidlink;
		if (isnum($cuid)) {
			if (self::verify_customer($cuid)) {
				dbquery("DELETE FROM ".DB_ESHOP_CUSTOMERS." WHERE cuid='".intval($cuid)."'");
				redirect(FUSION_SELF.$aidlink."&amp;a_page=customers");
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
		if (isset($_POST['save_customer'])) {
			$this->data['cuid'] = isset($_POST['cuid']) ? form_sanitizer($_POST['cuid'], '0', 'cuid') : 0; // user select
			$this->data['cemail'] = isset($_POST['cemail']) ? form_sanitizer($_POST['cemail'], '', 'cemail') : '';
			$this->data['cdob'] = isset($_POST['cdob']) ? form_sanitizer($_POST['cdob'], '', 'cdob') : '';
			$this->data['cname'] = implode('|', $_POST['cname']); // backdoor to traverse back to dynamic
			$name = isset($_POST['cname']) ? form_sanitizer($_POST['cname'], '', 'cname') : '';
			if (!empty($name)) {
				$name = explode('|', $name);
				$this->data['cfirstname'] = $name[0];
				$this->data['clastname'] = $name[1];
			}
			// this goes back to form.
			$this->data['caddress'] = implode('|', $_POST['caddress']);
			$address = isset($_POST['caddress']) ? form_sanitizer($_POST['caddress'], '', 'caddress') : '';
			if (!empty($address)) {
				$address = explode('|', $address);
				// this go into sql only
				$this->data['caddress'] = $address[0];
				$this->data['caddress2'] = $address[1];
				$this->data['ccountry'] = $address[2];
				$this->data['cregion'] = $address[3];
				$this->data['ccity'] = $address[4];
				$this->data['cpostcode'] = $address[5];
			}
			$this->data['cphone'] = isset($_POST['cphone']) ? form_sanitizer($_POST['cphone'], '', 'cphone') : '';
			$this->data['cfax'] = isset($_POST['cfax']) ? form_sanitizer($_POST['cfax'], '', 'cfax') : '';
			$this->data['ccupons'] = isset($_POST['ccupons']) ? form_sanitizer($_POST['ccupons'], '', 'ccupons') : '';
			if (self::verify_customer($this->data['cuid'])) {
				dbquery_insert(DB_ESHOP_CUSTOMERS, $this->data, 'update', array('no_unique'=>1, 'primary_key'=>'cuid'));
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=customers&amp;status=su");
			} else {
				dbquery_insert(DB_ESHOP_CUSTOMERS, $this->data, 'save',  array('no_unique'=>1, 'primary_key'=>'cuid'));
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
		echo openform('customerform', 'customerform', 'post', $this->formaction);
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8'>\n";
		$customer_name[] = $this->data['cfirstname'];
		$customer_name[] = $this->data['clastname'];
		$customer_name = implode('|', $customer_name);
		echo form_name('Customer Name', 'cname', 'cname', $customer_name, array('required'=>1, 'inline'=>1));
		echo form_text($locale['ESHPCHK115'], 'cemail', 'cemail', $this->data['cemail'], array('inline'=>1, 'email'=>1));
		echo form_datepicker($locale['ESHPCHK105'], 'cdob', 'cdob', $this->data['cdob'], array('inline'=>1));
		$customer_address[] = $this->data['caddress']; // use this as backdoor.
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
		echo form_button($locale['save'], 'save_customer', 'save_customer', $locale['save'], array('class'=>'btn-primary'));
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
		echo form_button($locale['save'], 'save_customer', 'save_customer', $locale['save'], array('class'=>'btn-primary'));
		echo closeform();
		echo "</div>\n";
		echo "</div>\n";
	}

	private function customer_view_filters() {
		global $locale, $aidlink;
		echo "<div class='m-t-20 m-b-20 display-block' style='height:40px;'>\n";
		echo "<div class='display-inline-block search-align m-r-10'>\n";
		echo form_text('', 'srch_text', 'srch_cpntext', '', array('placeholder'=>$locale['ESHP214'], 'inline'=>1, 'class'=>'m-b-0 m-r-10', 'width'=>'250px'));
		echo form_button($locale['SRCH164'], 'search', 'search-customer', $locale['SRCH158'], array('class'=>'btn-primary m-b-20 m-t-0'));
		echo "</div>\n";
		echo "</div>\n";
		add_to_jquery("
		$('#search-customer').bind('click', function(e) {
			$.ajax({
				url: '".ADMIN."includes/eshop_customersearch.php',
				dataType: 'html',
				type: 'post',
				beforeSend: function(e) { $('#eshopitem-links').html('<tr><td class=\"text-center\"colspan=\'12\'><img src=\"".IMAGES."loader.gif\"/></td></tr>'); },
				data: { q: $('#srch_cpntext').val(), token: '".$aidlink."' },
				success: function(e) {
					// append html
					$('#eshopitem-links').html(e);
				},
				error : function(e) {
				console.log(e);
				}
			});
		});
		");
	}


	public function customer_listing() {
			global $locale, $aidlink;

			self::customer_view_filters();
			add_to_jquery("
			$('.actionbar').hide();
			$('tr').hover(
				function(e) { $('#customer-'+ $(this).data('id') +'-actions').show(); },
				function(e) { $('#customer-'+ $(this).data('id') +'-actions').hide(); }
			);
			$('.qform').hide();
			$('.qedit').bind('click', function(e) {
				$.ajax({
					url: '".ADMIN."includes/eshop_customers.php',
					dataType: 'json',
					type: 'post',
					data: { q: $(this).data('id'), token: '".$aidlink."' },
					success: function(e) {
						$('#cuids').val(e.cuid);
						$('#cphone').val(e.cphone);
						$('#cfax').val(e.cfax);
						$('#qaddress-street').val(e.caddress);
						$('#qaddress-street2').val(e.caddress2);
						$('#qaddress-country').select2('val', e.ccountry);
						$('#qaddress-state').select2('val', e.cregion);
						$('#qaddress-city').val(e.ccity);
						$('#qaddress-postcode').val(e.cpostcode);
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
			echo "<th class='col-xs-3 col-sm-3'>".$locale['ESHPCHK149']."</th>\n";
			echo "<th>".$locale['ESHPCHK150']."</th>\n";
			echo "<th>".$locale['user_account']."</th>\n";
			echo "<th>".$locale['ESHPCHK151']."</th>\n";
			echo "<th>".$locale['ESHPCHK114']."</th>\n";
			echo "</tr>\n";
			echo "<tr class='qform'>\n";
			echo "<td colspan='6'>\n";
			echo "<div class='list-group-item m-t-20 m-b-20'>\n";
			echo openform('quick_edit', 'quick_edit', 'post', FUSION_SELF.$aidlink."&amp;a_page=customers", array('downtime' => 0, 'notice' => 0));
			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-6'>\n";
			echo form_address($locale['ESHPCHK106'], 'caddress', 'qaddress','', array('required'=>1, 'inline'=>1));
			echo "</div>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-6'>\n";
			echo form_text($locale['ESHPCHK113'], 'cphone', 'cphone', '', array('inline'=>1, 'required'=>1));
			echo form_text($locale['ESHPCHK114'], 'cfax', 'cfax', '', array('inline'=>1));
			echo form_hidden('', 'cuid', 'cuids', '', array('writable' => 1));
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='m-t-10 m-b-10'>\n";
			echo form_button($locale['cancel'], 'cancel', 'cancel', 'cancel', array('class' => 'btn btn-default m-r-10',
				'type' => 'button'));
			echo form_button($locale['update'], 'customer_quicksave', 'customer_quicksave', 'save', array('class' => 'btn btn-primary'));
			echo "</div>\n";
			echo closeform();
			echo "</div>\n";
			echo "</td>\n";
			echo "</tr>\n";

			$result = dbquery("SELECT c.*, u.user_id, u.user_name, u.user_status FROM ".DB_ESHOP_CUSTOMERS." c
			 INNER JOIN ".DB_USERS." u on c.cuid = u.user_id
			 ORDER BY cfirstname ASC LIMIT ".$_GET['rowstart'].",15");
			$rows = dbrows($result);
			if ($rows) {
				$i = 0;
				echo "<tbody id='eshopitem-links' class='connected'>\n";
				while ($data = dbarray($result)) {
					$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
					echo "<tr id='listItem_".$data['cuid']."' data-id='".$data['cuid']."' class='list-result ".$row_color."'>\n";
					echo "<td></td>\n";
					echo "<td>\n";
					echo "<strong>".$data['cfirstname']." ".$data['clastname']."</strong>\n";
					echo "<div class='actionbar text-smaller' id='customer-".$data['cuid']."-actions'>
					<a href='".FUSION_SELF.$aidlink."&amp;a_page=customers&amp;section=customerform&amp;action=edit&amp;cuid=".$data['cuid']."'>".$locale['edit']."</a> |
					<a class='qedit pointer' data-id='".$data['cuid']."'>".$locale['qedit']."</a> |
					<a class='delete' href='".FUSION_SELF.$aidlink."&amp;a_page=customers&amp;action=delete&amp;cuid=".$data['cuid']."' onclick=\"return confirm('".$locale['ESHP213']."');\">".$locale['delete']."</a>
					</div>\n";
					echo "</td>\n";
					echo "<td>".$data['cemail']."</td>";
					echo "<td><a title='Send Private Message' id='send_pm'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</a></td>";
					echo "<td>".$data['cphone']."</td>";
					echo "<td>".$data['cfax']."</td>";
					echo "</tr>";
					$i++;
				}
				echo "</tbody>\n";
			} else {
				echo "<tr><td colspan='6' class='text-center'><div class='alert alert-warning m-t-10'>".$locale['ESHPCHK154']."</div></td></tr>\n";
			}
			echo "</table>\n";
			if ($this->max_rowstart > $rows) {
				echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'],15,$rows,3,FUSION_SELF.$aidlink."&amp;cupons&amp;")."\n</div>\n";
			}
			echo "</div>\n";


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
echo opentabbody($tab_title['title'][0], 'customer', $tab_active, 1);
$customer->customer_listing();
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
