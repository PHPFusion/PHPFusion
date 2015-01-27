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

if (!isset($_GET['s_page'])){
	$_GET['s_page'] = "shipping";
}

if ($_GET['s_page'] == "shipping"){
$tbl0 = "tbl1";
}else{
$tbl0 = "tbl2";
}
if ($_GET['s_page'] == "shippingcats"){
$tbl1 = "tbl1";
}else{
$tbl1 = "tbl2";
}
echo "<table align='center' cellspacing='0' cellpadding='0' class='tbl-border' width='100%' border='0'><tr>
<td align='center' class='".$tbl0."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shipping'>".$locale['ESHPSHPMTS100']."</a></td>
<td align='center' class='".$tbl1."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shippingcats'>".$locale['ESHPSHPMTS101']."</a></td>
</tr><tr><td align='left' class='tbl' colspan='2'>";
if ($_GET['s_page'] == "shipping") {
include "shippingitems.php";
}
elseif ($_GET['s_page'] == "shippingcats") {
include "shippingcats.php";
}
echo "</td></tr></table>";


class eShop_shipping {
	public $cdata = array(
		'cid'=>0,
		'title'=>'',
		'image'=>'generic.png',
	);
	public $data = array(

	);

	private $cformaction = '';
	private $max_rowstart = 0;

	public function __construct() {
		global $aidlink;
		define("SHIP_DIR", BASEDIR."eshop/shippingimgs/");
		$this->max_rowstart = dbcount("(cid)", DB_ESHOP_SHIPPINGCATS);
		$this->max_crowstart = dbcount("(sid)", DB_ESHOP_SHIPPINGITEMS);
		$_GET['cid'] = isset($_GET['cid']) && isnum($_GET['cid']) ? $_GET['cid'] : 0;
		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		switch($_GET['action']) {
			case 'edit':
				$this->cdata = self::load_shippingco($_GET['cid']);
				break;
			case 'view':
				$this->data = self::load_itenary($_GET['cid']);
			case 'delete':
				break;
			default :
				$this->cformaction = FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shippingcats";
		}
		self::set_shippingco();

	}
	private function set_shippingco() {
		global $aidlink;
		if (isset($_POST['save_shipping'])) {
			$this->cdata['cid'] = isset($_POST['cid']) ? form_sanitizer($_POST['cid'], 0, 'cid') : 0;
			$this->cdata['title'] = isset($_POST['title']) ? form_sanitizer($_POST['title'], '', 'title') : '';
			$this->cdata['image'] = isset($_POST['image']) ? form_sanitizer($_POST['image'], '', 'image') : '';
			if (self::verify_shippingCats($this->cdata['cid'])) {
				dbquery_insert(DB_ESHOP_SHIPPINGCATS, $this->cdata['cid'], 'update');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;status=su");
			} else {
				dbquery_insert(DB_ESHOP_SHIPPINGCATS, $this->cdata['cid'], 'save');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;status=sn");
			}
		}

	}

	static function get_ImageOpts() {
		$image_list = array();
		$cat_files = makefilelist(SHIP_DIR, ".|..|index.php", true);
		foreach($cat_files as $file) {
			$image_list[$file] = $file;
		}
		return $image_list;
	}

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
			$result = dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE cid='".intval($id)."'");
			if (dbrows($result)>0) {
				return dbarray($result);
			}
			return array();
		}
	}


	public function add_shippingco_form() {
		global $aidlink, $locale;
		echo "<div class='m-t-10'>\n";
		echo openform('addcat', 'addcat', 'post', $this->cformaction);
		openside('');
		echo thumbnail(SHIP_DIR.$this->cdata['image'], '70px');
		echo "<div class='overflow-hide p-l-15'>\n";
		echo form_select('Payment Type', 'image', 'image',  self::get_ImageOpts(), $this->cdata['image']);
		echo "</div>\n";
		add_to_jquery("
		$('#image').bind('change', function(e) {
			$('.thumb > img').prop('src', '".SHIP_DIR."'+ $(this).val());
		});
		");
		closeside();
		echo form_text($locale['ESHPSHPMTS102'], 'title', 'title', $this->cdata['title']);
		echo form_button($locale['ESHPSHPMTS103'], 'save_shipping', 'save_shipping', $locale['save'], array('class'=>'btn-primary'));
		echo closeform();
		echo "</div>\n";
	}

	static function verify_shippingCats($id) {
		if (isnum($id)) {
			return dbcount("(cid)", DB_ESHOP_SHIPPINGCATS, "cid='".intval($id)."'");
		}
		return false;
	}

	static function shipping_view_filters() {
		return '';
	}

	static function get_destOpts() {
		global $locale;
		return array(
			'1' => $locale['D101'],
			'2' => $locale['D101'],
			'3' => $locale['D101']
		);
	}
	static function get_activeOpts() {
		global $locale;
		return array(
			'1' => $locale['yes'],
			'0' => $locale['no'],
		);
	}

	public function itenary_list() {
		global $locale, $aidlink;
		$dest_opts = self::get_destOpts();
		$active_opts = self::get_activeOpts();
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
		echo "<th class='col-xs-3 col-sm-3'>".$locale['ESHPSHPMTS107']."</th>\n";
		echo "<th>".$locale['ESHPSHPMTS108']."</th>\n";
		echo "<th>".$locale['ESHPSHPMTS109']."</th>\n";
		echo "<th>".$locale['ESHPSHPMTS110']." (".fusion_get_settings('eshop_weightscale').")</th>\n";
		echo "<th>".$locale['ESHPSHPMTS111']." (".fusion_get_settings('eshop_weightscale').")</th>\n";
		echo "<th>".$locale['ESHPSHPMTS112']."</th>\n";
		echo "<th>".$locale['ESHPSHPMTS113']." (".fusion_get_settings('eshop_weightscale').")</th>\n";
		echo "<th>".$locale['ESHPSHPMTS114']."</th>\n";
		echo "</tr>\n";




		$result = dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE cid = '".intval($_GET['cid'])."' ORDER BY sid ASC LIMIT ".$_GET['rowstart'].",25");
		$rows = dbrows($result);
		if ($rows>0) {
			$i = 0;
			echo "<tbody id='eshopitem-links' class='connected'>\n";
			while ($data = dbarray($result)) {
				$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
				// missing tr
				echo "<tr id='listItem_".$data['sid']."' data-id='".$data['sid']."' class='list-result ".$row_color."'>\n";
				echo "<td></td>\n";
				echo "<td><a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shippingcat&amp;action=iedit&amp;sid=".$data['sid']."'>".$data['method']."</a>\n";
				echo "<div class='actionbar text-smaller' id='shipping-".$data['sid']."-actions'>
					<a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shippingcat&amp;action=iedit&amp;sid=".$data['sid']."'>".$locale['edit']."</a> |
					<a class='delete' href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;action=idelete&amp;sid=".$data['sid']."' onclick=\"return confirm('".$locale['ESHP213']."');\">".$locale['delete']."</a>
					</div>\n";
				echo "</td>\n";
				echo "<td>".$data['dtime']."</td>\n";
				echo "<td>".$dest_opts[$data['destination']]."</td>\n";
				echo "<td>".$data['weightmin']."</td>\n";
				echo "<td>".$data['weightmax']."</td>\n";
				echo "<td>".$data['initialcost']."</td>\n";
				echo "<td>".$data['weightcost']."</td>\n";
				echo "<td>".$active_opts[$data['active']]."</td>\n";
				echo "</tr>\n";
				$i++;
			}
			echo "</tbody>\n";
		} else {
			echo "<tr><td colspan='9' class='text-center'><div class='alert alert-warning m-t-10'>".$locale['ESHPSHPMTS106']."</div></td></tr>\n";
		}

		echo "<tr><td></td><td colspan='9'><a id='' href=''>+ Add New Itenary</a></td></tr>\n";



		echo "</table>\n";
		echo "</div>\n";


	}

	public function shipping_listing() {
		global $locale, $aidlink;
		self::shipping_view_filters();
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
		echo "<th>Shipping Itenary</th>\n";
		echo "<th>Fastest Delivery Time</th>\n";
		echo "<th>Min. Weight</th>\n";
		echo "<th>Max. Weight</th>\n";
		echo "<th>Max. Cost</th>\n";
		echo "</tr>\n";

		$result = dbquery("SELECT s.*, count(si.cid) methods, IF(si.dtime, min(si.dtime), '--') as min_delivery_time, min(si.weightmin) as min_weight, max(si.weightmax) as max_weight, max(si.initialcost) as cost
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
					<a class='qedit pointer' data-id='".$data['cid']."'>".$locale['qedit']."</a> |
					<a class='delete' href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;action=delete&amp;cuid=".$data['cid']."' onclick=\"return confirm('".$locale['ESHP213']."');\">".$locale['delete']."</a>
					</div>\n";
				echo "</td>\n";
				echo "<td>".number_format($data['methods'])."</td>\n";
				echo "<td>".$data['min_delivery_time']."</td>\n";
				echo "<td>".number_format($data['min_weight'])." kg</td>\n";
				echo "<td>".number_format($data['max_weight'])." kg</td>\n";
				echo "<td>".number_format($data['cost'])." ".fusion_get_settings('eshop_currency')."</td>\n";
				echo "</tr>";
				$i++;
			}
			echo "</tbody>\n";
		} else {
			echo "<tr><td colspan='6' class='text-center'><div class='alert alert-warning m-t-10'>".$locale['ESHPSHPMTS106']."</div></td></tr>\n";
		}
		echo "</table>\n";
		if ($this->max_rowstart > $rows) {
			echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'],25,$this->max_rowstart,3,FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shipping&amp;")."\n</div>\n";
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




?>