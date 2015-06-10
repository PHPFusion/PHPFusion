<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: Admin/Coupons.php
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

class Coupons {
	private $data = array(
		'cuid' => 0,
		'cuname' => '',
		'cutype' => '',
		'cuvalue' => '',
		'custart' => '',
		'cuend' => '',
		'active' => 1,
	);
	private $form_action = '';
	private $max_rowstart = 0;
	private $filter_Sql = '';

	public function __construct() {
		global $aidlink;
		$_GET['cuid'] = isset($_GET['cuid']) && strlen($_GET['cuid']) == 15 ? stripinput($_GET['cuid']) : 0;
		$_rand = rand(1000000, 9999999);
		$_hash = substr(md5($_rand), 0, 15);
		$this->data['cuid'] = strtoupper($_hash);
		$this->custart = time();
		$this->max_rowstart = dbcount("(cuid)", DB_ESHOP_COUPONS);
		$_GET['rowstart'] = (isset($_GET['cuid']) && isnum($_GET['cuid']) && $_GET['cuid'] <= $this->max_rowstart) ? $_GET['cuid'] : 0;

		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		switch ($_GET['action']) {
			case 'delete' :
				self::remove_coupon();
				break;
			case 'edit' :
				$this->form_action = FUSION_SELF.$aidlink."&amp;a_page=coupons&amp;action=edit&amp;cuid=".$_GET['cuid'];
				$this->data = self::get_couponData($_GET['cuid']);
				break;
			default :
				$this->form_action = FUSION_SELF.$aidlink."&amp;a_page=coupons&amp;section=couponform";
		}

		if (isset($_POST['save_coupons'])) {
			self::set_coupondb();
		}
		self::quick_save();
	}

	/**
	 * Allowed Coupon Type
	 * @return array
	 */
	static function getCouponType() {
		global $locale, $settings, $eshop_settings;
		return array(
			'0'	=> $locale['ESHPCUPNS112'],
			'1' => $locale['ESHPCUPNS113']." (".$eshop_settings['eshop_currency'].")",
		);
	}

	/**
	 * Allowed Coupon Status
	 * @return array
	 */
	static function getCouponStatus() {
		global $locale;
		return array(
			'0' => $locale['no'],
			'1' => $locale['yes']
		);
	}

	/**
	 * Verify the authenticity of the coupon
	 * @param $cuid
	 * @return bool|string
	 */
	static function verify_coupon($cuid) {
		if ($cuid) {
			$cuid = stripinput($cuid);
			return dbcount("(cuid)", DB_ESHOP_COUPONS, "cuid='$cuid'");
		}
		return false;
	}

	/* Verify if the customer id has already used the coupon - returns true if used */
	static function verify_coupon_usage($cuid, $coupon_code) {
		if (isnum($cuid)) {
			$verify_result = dbrows(dbquery("SELECT * FROM ".DB_ESHOP_CUSTOMERS." WHERE cuid = '".intval($cuid)."' AND ccupons LIKE '%.".$coupon_code."' LIMIT 0,1"));
			if ($verify_result) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Quick Save Mysql
	 */
	static function quick_save() {
		global $aidlink, $defender;
		if (isset($_POST['coupon_quicksave'])) {
			$quick['cuid'] = isset($_POST['cuid']) ? form_sanitizer($_POST['cuid'], 'void', 'cuid') : 'void';
			$quick['cuname'] = isset($_POST['cuname']) ? form_sanitizer($_POST['cuname'], '', 'cuname') : '';
			$quick['cutype'] = isset($_POST['cutype']) ? form_sanitizer($_POST['cutype'], '0', 'cutype') : 0;
			$quick['cuvalue'] = isset($_POST['cuvalue']) ? form_sanitizer($_POST['cuvalue'], '0', 'cuvalue') : 0;
			$quick['active'] = isset($_POST['active']) ? form_sanitizer($_POST['active'], '0', 'active') : 0;
			if ($quick['cuid']) {
				$c_result = dbquery("SELECT * FROM ".DB_ESHOP_COUPONS." WHERE cuid='".$quick['cuid']."'");
				if (dbrows($c_result) > 0) {
					$quick += dbarray($c_result);
					dbquery_insert(DB_ESHOP_COUPONS, $quick, 'update', array('no_unique'=>1, 'primary_key'=>'cuid'));
					redirect(FUSION_SELF.$aidlink."&amp;a_page=coupons");
				}
			}
		}
	}

	/**
	 * Delete Coupon
	 */
	private function remove_coupon() {
		global $aidlink;
		if (!preg_match("/^[-0-9A-ZÅÄÖ._@\s]+$/i", $_GET['cuid']) && self::verify_coupon($_GET['cuid'])) {
			dbquery("DELETE FROM ".DB_ESHOP_COUPONS." WHERE cuid='".intval($_GET['cuid'])."'");
			redirect(FUSION_SELF.$aidlink."&amp;a_page=coupons&amp;status=del");
		}
	}

	/**
	 * Set Coupon Mysql
	 */
	private function set_coupondb() {
		global $aidlink, $locale, $defender;

		if (!preg_match("/^[-0-9A-ZÅÄÖ._@\s]+$/i", $_POST['cuid'])) {
			$defender->stop();
			$defender->addNotice($locale['ESHPCUPNS_ERROR2']);
		}

		$this->data['cuid'] = isset($_POST['cuid']) ? form_sanitizer($_POST['cuid'], 'void', 'cuid') : 'void';
		$this->data['cuname'] = isset($_POST['cuname']) ? form_sanitizer($_POST['cuname'], '', 'cuname') : '';
		$this->data['cutype'] = isset($_POST['cutype']) ? form_sanitizer($_POST['cutype'], '0', 'cutype') : 0;
		$this->data['cuvalue'] = isset($_POST['cuvalue']) ? form_sanitizer($_POST['cuvalue'], '0', 'cuvalue') : 0;
		$this->data['custart'] = isset($_POST['custart']) ? form_sanitizer($_POST['custart'], '', 'custart') : '';
		$this->data['cuend'] = isset($_POST['cuend']) ? form_sanitizer($_POST['cuend'], '', 'cuend') : '';
		$this->data['active'] = isset($_POST['active']) ? form_sanitizer($_POST['active'], '0', 'active') : 0;

		if ($this->data['cuend'] < $this->data['custart']) {
			$defender->stop();
			$defender->addNotice($locale['ESHPCUPNS_ERROR1']);
		}

		if (self::verify_coupon($this->data['cuid']) && !defined('FUSION_NULL')) {
			// update
			dbquery_insert(DB_ESHOP_COUPONS, $this->data, 'update', array('primary_key'=>'cuid', 'no_unique'=>1));
			redirect(FUSION_SELF.$aidlink."&amp;a_page=coupons&amp;status=su");
		} else {
			//save
			dbquery_insert(DB_ESHOP_COUPONS, $this->data, 'save', array('primary_key'=>'cuid', 'no_unique'=>1));
			redirect(FUSION_SELF.$aidlink."&amp;a_page=coupons&amp;status=sn");
		}
	}

	/**
	 * Data callback
	 * @param $cuid
	 * @return array|bool
	 */
	public static function get_couponData($cuid) {
		$cuid = form_sanitizer($cuid, '');
		$result = dbquery("SELECT * FROM ".DB_ESHOP_COUPONS." WHERE cuid='".$cuid."'");
		if (dbrows($result)>0) {
			return dbarray($result);
		}
		return array();
	}

	/**
	 * Coupon Form Template
	 */
	public function add_coupon_form() {
		global $locale, $defender;

		echo "<div class='m-t-20 inline-block'>\n";
		echo openform('coupon_form', 'post', $this->form_action, array('max_tokens' => 1));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
		openside('');
		echo form_text('cuid', $locale['ESHPCUPNS101'], $this->data['cuid'], array('inline'=>1, 'required'=>1));
		echo form_text('cuname', $locale['ESHPCUPNS102'], $this->data['cuname'], array('inline'=>1, 'required'=>1));
		closeside();
		openside();
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3'>\n";
		echo "<label class='control-label'>".$locale['ESHPCUPNS116']."</label>\n";
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-9'>\n";
		echo "<div class='display-inline-block m-r-10'>\n";
		echo form_datepicker('custart', $locale['ESHPCUPNS105'], $this->data['custart'], array('required'=>1));
		echo "</div>\n";
		echo "<div class='display-inline-block'>\n";
		echo form_datepicker('cuend', $locale['ESHPCUPNS106'], $this->data['cuend'], array('required'=>1));
		echo "</div>\n";
		echo "</div>\n</div>\n";
		closeside();

		openside('');
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3'>\n";
		echo "<label class='control-label'>".$locale['ESHPCUPNS117']."</label>\n";
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-9'>\n";

		echo "<div class='display-inline-block m-r-10 col-xs-12 col-sm-6 p-l-0'>\n";
		echo form_text('cuvalue', $locale['ESHPCUPNS104'], $this->data['cuvalue'], array('number'=>1, 'required'=>1));
		echo "<span class='text-smaller'>".$locale['ESHPCUPNS118']."</span>\n";
		echo "</div>\n";

		echo "<div class='display-inline-block'>\n";
		echo form_select('cutype',$locale['ESHPCUPNS103'], self::getCouponType(), $this->data['cutype']);
		echo "</div>\n";

		echo "</div>\n</div>\n";
		closeside();

		echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";
		openside('');
		echo form_select('active',$locale['ESHPCUPNS107'],  self::getCouponStatus(), $this->data['active'], array('inline'=>1));
		echo form_button('save_coupons', $locale['save'], $locale['ESHPCUPNS111'], array('class'=>'btn-success', 'icon'=>'fa fa-check-square-o'));
		closeside();

		echo "</div>\n";
		echo "</div>\n";
		echo form_button('save_coupons', $locale['save'], $locale['ESHPCUPNS111'], array('class'=>'btn-success', 'icon'=>'fa fa-check-square-o'));
		echo closeform();
		echo "</div>\n";
	}

	/**
	 * Filter
	 */
	private function coupon_view_filters() {
		global $locale, $aidlink;
		$item_status = isset($_GET['status']) && $_GET['status'] == 1 ? 1 : 0;
		$this->filter_Sql = !$item_status ? "(active='1' or active='0')" : "active='0'";
		echo "<div class='m-t-20 m-b-20 display-block' style='height:40px;'>\n";
		echo "<div class='display-inline-block search-align m-r-10'>\n";
		echo form_text('srch_text', '', '', array('placeholder'=>$locale['ESHPCUPNS119'], 'inline'=>1, 'class'=>'m-b-0 m-r-10', 'width'=>'250px'));
		echo form_button('search', $locale['SRCH164'], $locale['SRCH158'], array('class'=>'btn-primary m-b-20 m-t-0'));
		echo "</div>\n";

		echo "<div class='display-inline-block m-r-10'>\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=coupons&amp;status=0' ".(!$item_status ? "class='text-dark'" : '').">All (".number_format(dbcount("(cuid)", DB_ESHOP_COUPONS)).")</a>\n - ";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=coupons&amp;status=1' ".($item_status ? "class='text-dark'" : '').">Inactive (".number_format(dbcount("(cuid)", DB_ESHOP_COUPONS, "active='0'")).")</a>\n";
		echo "</div>\n";

		echo "</div>\n";
		add_to_jquery("
		$('#search-coupon').bind('click', function(e) {
			$.ajax({
				url: '".SHOP."admin/includes/eshop_cpnsearch.php',
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

	/**
	 * Coupon Listing Table
	 */
	public function coupon_listing() {
		global $locale, $aidlink;
		$coupon_status = self::getCouponStatus();
		$coupon_type = self::getCouponType();
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
				url: '".SHOP."admin/includes/eshop_coupon.php',
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
		echo "<table class='table table-striped table-responsive'>\n";
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
		echo openform('quick_edit', 'post', FUSION_SELF.$aidlink."&amp;a_page=coupons", array('max_tokens' => 1, 'notice' => 0));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-5'>\n";
		echo form_text('cuname', $locale['ESHPCUPNS102'], $this->data['cuname'], array('inline'=>1, 'required'=>1));
		echo form_select('active', $locale['ESHPCUPNS107'], self::getCouponStatus(), $this->data['active'], array('inline'=>1));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-7'>\n";
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3'>\n";
		echo "<label class='control-label'>".$locale['ESHPCUPNS117']."</label>\n";
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-9'>\n";
		echo "<div class='display-inline-block m-r-10 col-xs-12 col-sm-6 p-l-0'>\n";
		echo form_text('cuvalue', $locale['ESHPCUPNS104'], $this->data['cuvalue'], array('number'=>1, 'required'=>1));
		echo "<span class='text-smaller'>".$locale['ESHPCUPNS118']."</span>\n";
		echo "</div>\n";
		echo "<div class='display-inline-block'>\n";
		echo form_select('cutype', $locale['ESHPCUPNS103'],  self::getCouponType(), $this->data['cutype']);
		echo "</div>\n";
		echo "</div>\n</div>\n";
		echo form_hidden('', 'cuid', 'cuids', '', array('writable' => 1));
		echo "</div>\n";
		echo "</div>\n";
		echo "<div class='m-t-10 m-b-10'>\n";
		echo form_button('cancel', $locale['cancel'], 'cancel', array('class' => 'btn btn-default m-r-10',
			'type' => 'button'));
		echo form_button('coupon_quicksave', $locale['update'], 'save', array('class' => 'btn btn-primary'));
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
				echo "<tr id='listItem_".$data['cuid']."' data-id='".$data['cuid']."' class='list-result'>\n";
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
				echo "<td>".$data['cuvalue']." ".$coupon_type[$data['cutype']]."</td>";
				echo "<td>".$coupon_status[$data['active']]."</td>";
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
