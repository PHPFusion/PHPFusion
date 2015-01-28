<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: shipping.php
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

class eShop_shipping {
	public $cdata = array(
		'cid'=>0,
		'title'=>'',
		'image'=>'generic.png',
	);
	public $sdata = array(
		'sid' =>0,
		'cid' =>0,
		'method' => '',
		'dtime' => '',
		'destination' => 0,
		'weightmin' => 0,
		'weightmax' => 0,
		'weightcost' => 0,
		'initialcost' => 0,
		'active' => 1
	);
	private $cformaction = '';
	private $sformaction = '';
	private $max_rowstart = 0;
	private $max_srowstart = 0;

	public function __construct() {
		global $aidlink;
		define("SHIP_DIR", BASEDIR."eshop/shippingimgs/");
		$this->max_rowstart = dbcount("(cid)", DB_ESHOP_SHIPPINGCATS);
		$this->max_srowstart = dbcount("(sid)", DB_ESHOP_SHIPPINGITEMS);
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] < $this->max_rowstart ? $_GET['rowstart'] : 0;
		$_GET['srowstart'] = isset($_GET['srowstart']) && isnum($_GET['srowstart']) && $_GET['srowstart'] < $this->max_srowstart ? $_GET['srowstart'] : 0;
		$_GET['cid'] = isset($_GET['cid']) && isnum($_GET['cid']) ? $_GET['cid'] : 0;
		$_GET['sid'] = isset($_GET['sid']) && isnum($_GET['sid']) ? $_GET['sid'] : 0;
		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		$_GET['ref'] = isset($_GET['ref']) ? $_GET['ref'] : '';
		switch($_GET['action']) {
			case 'edit':
				$this->cdata = self::load_shippingco($_GET['cid']);
				$this->cformaction = FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shippingcat&action=edit&amp;cid=".$_GET['cid'];
				break;
			case 'view':
				$this->data = self::load_itenary($_GET['cid']);
				break;
			case 'delete':
				self::delete_shippingco($_GET['cid']);
				break;
			default :
				$this->cformaction = FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shippingcat";
		}
		switch($_GET['ref']) {
			case 'add_details':
				if (self::verify_shippingCats($_GET['cid'])) {
					$this->sformaction = FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shipping&amp;action=view&amp;cid=".$_GET['cid']."&amp;ref=add_details";
				}
				break;
			case 'edit_details':
				if (self::verify_shippingCats($_GET['cid'])) {
					$this->sformaction = FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shipping&amp;action=view&amp;cid=".$_GET['cid']."&amp;sid=".$_GET['sid']."&amp;ref=edit_details";
					$this->sdata = self::load_itenary($_GET['sid']);
				}
			case 'delete_details':
				self::delete_itenary($_GET['sid']);
				break;
		}
		self::set_shippingco();
		self::set_itenary();
	}

	private function delete_shippingco($cid) {
		global $aidlink;
		if (isnum($cid)) {
			if (self::verify_shippingCats($cid)) {
				dbquery("DELETE FROM ".DB_ESHOP_SHIPPINGCATS." WHERE cid='".intval($cid)."'");
				redirect(FUSION_SELF.$aidlink."&amp;a_page=shipping");
			}
		}
	}

	private function delete_itenary($sid) {
		global $aidlink;
		if (isnum($sid)) {
			if (self::verify_itenary($sid)) {
				dbquery("DELETE FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE sid='".intval($sid)."'");
				redirect(FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shipping&amp;action=view&amp;cid=".$_GET['cid']);
			}
		}
	}


	private function set_shippingco() {
		global $aidlink;
		if (isset($_POST['save_shipping'])) {
			$this->cdata['cid'] = isset($_POST['cid']) ? form_sanitizer($_POST['cid'], 0, 'cid') : 0;
			$this->cdata['title'] = isset($_POST['title']) ? form_sanitizer($_POST['title'], '', 'title') : '';
			$this->cdata['image'] = isset($_POST['image']) ? form_sanitizer($_POST['image'], '', 'image') : '';
			if (self::verify_shippingCats($this->cdata['cid'])) {
				dbquery_insert(DB_ESHOP_SHIPPINGCATS, $this->cdata, 'update');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;status=su");
			} else {
				dbquery_insert(DB_ESHOP_SHIPPINGCATS, $this->cdata, 'save');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;status=sn");
			}
		}
	}

	private function set_itenary() {
		global $aidlink;
		if (isset($_POST['save_item'])) {
			$this->sdata['sid'] = isset($_POST['sid']) ? form_sanitizer($_POST['sid'], 0, 'sid') : 0;
			$this->sdata['cid'] = isset($_POST['cid']) && self::verify_shippingCats($_GET['cid']) ? $_GET['cid'] : 0;
			$this->sdata['active'] = isset($_POST['active']) ? form_sanitizer($_POST['active'], 0, 'active') : 0;
			$this->sdata['dtime'] = isset($_POST['dtime']) ? form_sanitizer($_POST['dtime'], '', 'dtime') : '';
			$this->sdata['destination'] = isset($_POST['destination']) ? form_sanitizer($_POST['destination'], '', 'destination') : '';
			$this->sdata['weightmin'] = isset($_POST['weightmin']) ? form_sanitizer($_POST['weightmin'], '', 'weightmin') : '';
			$this->sdata['weightmax'] = isset($_POST['weightmax']) ? form_sanitizer($_POST['weightmax'], '', 'weightmax') : '';
			$this->sdata['initialcost'] = isset($_POST['initialcost']) ? form_sanitizer($_POST['initialcost'], '', 'initialcost') : '';
			if (self::verify_itenary($this->sdata['sid'])) {
				dbquery_insert(DB_ESHOP_SHIPPINGITEMS, $this->sdata, 'update');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;action=view&amp;section=shipping&amp;cid=".$this->sdata['cid']."&amp;status=su");
			} else {
				dbquery_insert(DB_ESHOP_SHIPPINGITEMS, $this->sdata, 'save');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;action=view&amp;section=shipping&amp;cid=".$this->sdata['cid']."&amp;status=sn");
			}
		}
	}

	// Verification Functions
	static function verify_itenary($id) {
		if (isnum($id)) {
			return dbcount("(sid)", DB_ESHOP_SHIPPINGITEMS, "sid='".intval($id)."' AND cid='".intval($_GET['cid'])."'");
		}
		return false;
	}

	static function verify_shippingCats($id) {
		if (isnum($id)) {
			return dbcount("(cid)", DB_ESHOP_SHIPPINGCATS, "cid='".intval($id)."'");
		}
		return false;
	}

	// Opts Functions
	static function get_ImageOpts() {
		$image_list = array();
		$cat_files = makefilelist(SHIP_DIR, ".|..|index.php", true);
		foreach($cat_files as $file) {
			$image_list[$file] = $file;
		}
		return $image_list;
	}
	static function get_destOpts() {
		global $locale;
		return array(
			'1' => $locale['D101'],
			'2' => $locale['D102'],
			'3' => $locale['D103']
		);
	}
	static function get_activeOpts() {
		global $locale;
		return array(
			'1' => $locale['yes'],
			'0' => $locale['no'],
		);
	}

	static function get_dTimeOpts() {
		global $locale;
		return array(
			'0' => 'N/A', // required for null item
			'1' => '1-2 Days',
			'2' => '3-7 Days',
			'3' => '1-2 Weeks',
			'4' => '2-4 Weeks',
			'5' => '1-2 Months',
			'6' => '2-3 Months',
			'7' => '3-6 Months',
			'8' => 'Please enquire',
		);
	}

	// Data loaders
	static function load_shippingco($id) {
		if (isnum($id)) {
			$result = dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGCATS." WHERE cid='".intval($id)."'");
			if (dbrows($result)>0) {
				return dbarray($result);
			}
			return array();
		}
	}
	static function load_itenary($id) {
		if (isnum($id)) {
			$result = dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE sid='".intval($id)."'");
			if (dbrows($result)>0) {
				return dbarray($result);
			}
			return array();
		}
	}

	public function add_shippingco_form() {
		global $locale;
		echo "<div class='m-t-10'>\n";
		echo openform('addcat', 'addcat', 'post', $this->cformaction);
		openside('');
		echo thumbnail(SHIP_DIR.$this->cdata['image'], '70px');
		echo "<div class='overflow-hide p-l-15'>\n";
		echo form_select('Shipping Type', 'image', 'image',  self::get_ImageOpts(), $this->cdata['image']);
		echo form_hidden('', 'cid', 'cid', $_GET['cid']);
		echo "</div>\n";
		add_to_jquery("
		$('#image').bind('change', function(e) {
			$('.thumb > img').prop('src', '".SHIP_DIR."'+ $(this).val());
		});
		");
		closeside();
		echo form_text($locale['ESHPSHPMTS102'], 'title', 'title', $this->cdata['title']);
		echo form_button($locale['save'], 'save_shipping', 'save_shipping', $locale['save'], array('class'=>'btn-primary'));
		echo closeform();
		echo "</div>\n";
	}

	public function shipping_listing() {
		global $locale, $aidlink;
		$delivery_opts = self::get_dTimeOpts();
		add_to_jquery("
			$('.actionbar').hide();
			$('tr').hover(
				function(e) { $('#shipping-'+ $(this).data('id') +'-actions').show(); },
				function(e) { $('#shipping-'+ $(this).data('id') +'-actions').hide(); }
			);
		");
		echo "<div class='m-t-20'>\n";
		echo "<table class='table table-responsive'>\n";
		echo "<tr>\n";
		echo "<th></th>\n";
		echo "<th class='col-xs-3 col-sm-3'>".$locale['ESHPCHK149']."</th>\n";
		echo "<th>Shipping Co. Itenary</th>\n";
		echo "<th>Fastest Delivery Time</th>\n";
		echo "<th>Longest Delivery Time</th>\n";
		echo "<th>Min. Weight ".fusion_get_settings('eshop_weightscale')."</th>\n";
		echo "<th>Max. Weight ".fusion_get_settings('eshop_weightscale')."</th>\n";
		echo "<th>Min. Cost</th>\n";
		echo "<th>Max. Cost</th>\n";
		echo "</tr>\n";

		$result = dbquery("SELECT s.*, count(si.cid) methods,
					IF(si.dtime, min(si.dtime), '0') as min_delivery_time,
					IF(si.dtime, max(si.dtime), '0') as max_delivery_time,
					IF(si.weightmin, min(si.weightmin), 0.00) as min_weight,
					IF (si.weightmax, max(si.weightmax), 0.00) as max_weight,
					IF (si.initialcost, min(si.initialcost), 0.00) as min_cost,
					IF (si.initialcost, max(si.initialcost), 0.00) as max_cost
					FROM ".DB_ESHOP_SHIPPINGCATS." s
					LEFT JOIN ".DB_ESHOP_SHIPPINGITEMS." si on s.cid=si.cid AND si.active='1'
					GROUP BY s.cid
					ORDER BY s.title ASC
					LIMIT ".$_GET['rowstart'].",25");
		$rows = dbrows($result);
		if ($rows) {
			$i = 0;
			echo "<tbody id='eshopitem-links' class='connected'>\n";
			while ($data = dbarray($result)) {
				//print_p($data);
				$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
				echo "<tr id='listItem_".$data['cid']."' data-id='".$data['cid']."' class='list-result ".$row_color."'>\n";
				echo "<td></td>\n";
				echo "<td>\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shipping&amp;action=view&amp;cid=".$data['cid']."' class='text-dark'>".$data['title']."</a>\n";
				echo "<div class='actionbar text-smaller' id='shipping-".$data['cid']."-actions'>
					<a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shippingcat&amp;action=edit&amp;cid=".$data['cid']."'>".$locale['edit']."</a> |
					<a class='delete' href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;action=delete&amp;cid=".$data['cid']."' onclick=\"return confirm('".$locale['ESHP213']."');\">".$locale['delete']."</a>
					</div>\n";
				echo "</td>\n";
				echo "<td>".number_format($data['methods'])."</td>\n";
				echo "<td>".$delivery_opts[$data['min_delivery_time']]."</td>\n";
				echo "<td>".$delivery_opts[$data['max_delivery_time']]."</td>\n";
				echo "<td>".number_format($data['min_weight'], 2)."</td>\n";
				echo "<td>".number_format($data['max_weight'], 2)."</td>\n";
				echo "<td>".number_format($data['min_cost'], 2)." ".fusion_get_settings('eshop_currency')."</td>\n";
				echo "<td>".number_format($data['max_cost'], 2)." ".fusion_get_settings('eshop_currency')."</td>\n";
				echo "</tr>";
				$i++;
			}
			echo "</tbody>\n";
		} else {
			echo "<tr><td colspan='6' class='text-center'><div class='alert alert-warning m-t-10'>".$locale['ESHPSHPMTS106']."</div></td></tr>\n";
		}
		echo "</table>\n";
		if ($this->max_rowstart > $rows) {
			echo "<div class='m-t-20 text-center'>".makePageNav($_GET['rowstart'],25,$this->max_rowstart,3,FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shipping&amp;")."\n</div>\n";
		}
		echo "</div>\n";
	}

	public function itenary_list() {
		global $locale, $aidlink;
		$dest_opts = self::get_destOpts();
		$active_opts = self::get_activeOpts();
		$dtime_opts = self::get_dTimeOpts();
		add_to_jquery("
			$('.actionbar').hide();
			$('tr').hover(
				function(e) { $('#shipping-'+ $(this).data('id') +'-actions').show(); },
				function(e) { $('#shipping-'+ $(this).data('id') +'-actions').hide(); }
			);
			$('#cancel').bind('click', function(e) {
				$('.qform').hide();
			});
		");

		echo "<div class='m-t-20'>\n";

		echo "<table class='table table-responsive'>\n";
		echo "<tr>\n";
		echo "<th></th>\n";
		echo "<th class='col-xs-3 col-sm-3'>".$locale['ESHPSHPMTS107']."</th>\n";
		echo "<th>".$locale['ESHPSHPMTS108']."</th>\n";
		echo "<th>".$locale['ESHPSHPMTS109']."</th>\n";
		echo "<th>".$locale['ESHPSHPMTS110']." (".fusion_get_settings('eshop_weightscale').")</th>\n";
		echo "<th>".$locale['ESHPSHPMTS111']." (".fusion_get_settings('eshop_weightscale').")</th>\n";
		echo "<th>".$locale['ESHPSHPMTS112']."</th>\n";
		echo "<th>".$locale['ESHPSHPMTS113']." (".fusion_get_settings('eshop_weightscale').")</th>\n";
		echo "<th>".$locale['ESHPSHPMTS114']."</th>\n";
		echo "</tr>\n";

		// add quick form.
		if ($this->sformaction) {
			echo "<tr class='qform'>\n";
			echo "<td colspan='9'>\n";
			echo "<div class='list-group-item m-t-20 m-b-20'>\n";
			echo openform('add_detail', 'add_detail', 'post', $this->sformaction, array('downtime' => 0, 'notice' => 0));
			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-6'>\n";
			echo form_text($locale['ESHPSHPMTS107'], 'method', 'method',$this->sdata['method'], array('required'=>1, 'inline'=>1));
			echo form_select($locale['ESHPSHPMTS108'], 'dtime', 'dtime', self::get_dTimeOpts(), $this->sdata['dtime'], array('required'=>1, 'inline'=>1));
			echo form_select($locale['ESHPSHPMTS109'], 'destination', 'destination', self::get_destOpts(), $this->sdata['destination'], array('inline'=>1));
			echo form_select($locale['ESHPSHPMTS114'], 'active', 'active', self::get_activeOpts(), $this->sdata['active'], array('inline'=>1));
			echo "</div>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-6'>\n";
			echo form_text($locale['ESHPSHPMTS110']." (".fusion_get_settings('eshop_weightscale').")", 'weightmin', 'weightmin', $this->sdata['weightmin'], array('number'=>1, 'inline'=>1));
			echo form_text($locale['ESHPSHPMTS111']." (".fusion_get_settings('eshop_weightscale').")", 'weightmax', 'weightmax', $this->sdata['weightmax'], array('number'=>1, 'inline'=>1));
			echo form_text($locale['ESHPSHPMTS112']." (".fusion_get_settings('eshop_currency').")", 'initialcost', 'initialcost', $this->sdata['initialcost'], array('number'=>1, 'inline'=>1));
			echo form_text($locale['ESHPSHPMTS113']." (".fusion_get_settings('eshop_weightscale').")", 'weightcost', 'weightcost', $this->sdata['weightcost'], array('number'=>1, 'inline'=>1));
			echo form_hidden('', 'sid', 'sid', $this->sdata['sid'], array('writable' => 1));
			echo form_hidden('', 'cid', 'cid', $_GET['cid'], array('writable' => 1));
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='m-t-10 m-b-10'>\n";
			echo form_button($locale['cancel'], 'cancel', 'cancel', 'cancel', array('class' => 'btn btn-default m-r-10',
				'type' => 'button'));
			echo form_button($locale['save'], 'save_item', 'save_item', 'save', array('class' => 'btn btn-primary'));
			echo "</div>\n";
			echo closeform();
			echo "</div>\n";
			echo "</td>\n";
			echo "</tr>\n";
		}
		// need to redo query to get float val for 0.00.
		$result = dbquery("SELECT
		sid, cid, method, dtime, destination, active,
		IF (weightmin, weightmin, 0.00) as weightmin,
		IF (weightmax, weightmax, 0.00) as weightmax,
		IF (weightcost, weightcost, 0.00) as weightcost,
		IF (initialcost, initialcost, 0.00) as initialcost
		FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE cid = '".intval($_GET['cid'])."' ORDER BY sid ASC LIMIT ".$_GET['rowstart'].",25");
		$rows = dbrows($result);
		if ($rows>0) {
			$i = 0;
			echo "<tbody id='eshopitem-links' class='connected'>\n";
			while ($data = dbarray($result)) {
				$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
				// missing tr
				echo "<tr id='listItem_".$data['sid']."' data-id='".$data['sid']."' class='list-result ".$row_color."'>\n";
				echo "<td></td>\n";
				echo "<td><a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shipping&amp;action=view&amp;cid=".$data['cid']."&amp;sid=".$data['sid']."&amp;ref=edit_details'>".$data['method']."</a>\n";
				echo "<div class='actionbar text-smaller' id='shipping-".$data['sid']."-actions'>
					<a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shipping&amp;action=view&amp;cid=".$data['cid']."&amp;sid=".$data['sid']."&amp;ref=edit_details'>".$locale['edit']."</a> |
					<a class='delete' href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shipping&amp;action=view&amp;cid=".$data['cid']."&amp;sid=".$data['sid']."&amp;ref=delete_details' onclick=\"return confirm('".$locale['ESHP213']."');\">".$locale['delete']."</a>
					</div>\n";
				echo "</td>\n";
				echo "<td>".$dtime_opts[$data['dtime']]."</td>\n";
				echo "<td>".$dest_opts[$data['destination']]."</td>\n";
				echo "<td>".number_format($data['weightmin'],2)."</td>\n";
				echo "<td>".number_format($data['weightmax'],2)."</td>\n";
				echo "<td>".number_format($data['initialcost'],2)." ".fusion_get_settings('eshop_currency')."</td>\n";
				echo "<td>".number_format($data['weightcost'],2)."  ".fusion_get_settings('eshop_currency')."</td>\n";
				echo "<td>".$active_opts[$data['active']]."</td>\n";
				echo "</tr>\n";
				$i++;
			}
			echo "</tbody>\n";
		} else {
			echo "<tr><td colspan='9' class='text-center'><div class='alert alert-warning m-t-10'>".$locale['ESHPSHPMTS106']."</div></td></tr>\n";
		}
		echo "<tr><td></td><td colspan='9'><a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shipping&amp;action=view&amp;cid=".$_GET['cid']."&amp;ref=add_details'>+ Add New Itenary</a></td></tr>\n";
		echo "</table>\n";
		// rowstarts
		if ($this->max_srowstart > $rows) {
			echo "<div class='m-t-20 text-center'>".makePageNav($_GET['srowstart'], 25, $this->max_srowstart, 3, FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shipping&amp;action=view&amp;cid=".$_GET['cid']."&amp;")."\n</div>\n";
		}
		echo "</div>\n";
	}
}

$shipping = new eShop_shipping();
$cview = (isset($_GET['action']) && $_GET['action'] == 'view') ? $shipping->verify_shippingCats($_GET['cid']) : 0;
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $shipping->verify_shippingCats($_GET['cid']) : 0;
$tab_title['title'][] = $cview ? 'Edit Shipping Company Itenary' : 'Current Shipping Lists'; //$locale['ESHPCUPNS100'];
$tab_title['id'][] = 'shipping';
$tab_title['icon'][] = $cview ? 'fa fa-pencil m-r-10' : '';
$tab_title['title'][] =  $edit ? 'Edit Shipping' : 'Add Shipping'; // $locale['ESHPCUPNS115'] : $locale['ESHPCUPNS114'];
$tab_title['id'][] = 'shippingcat';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1, 1);
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=shipping");
echo opentabbody($tab_title['title'][0], 'shipping', $tab_active, 1);
if (isset($_GET['section']) && $_GET['section'] == 'shipping' && $cview) {
	$shipping->itenary_list();
} else {
	$shipping->shipping_listing();
}
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'shippingcat') {
	echo opentabbody($tab_title['title'][1], 'shippingcat', $tab_active, 1);
	$shipping->add_shippingco_form();
	echo closetabbody();
}