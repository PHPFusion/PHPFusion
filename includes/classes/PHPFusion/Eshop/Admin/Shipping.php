<?php

namespace PHPFusion\Eshop\Admin;

class Shipping {
	/**
	 * @var array|bool
	 */
	public $cdata = array(
		'cid'=>0,
		'title'=>'',
		'image'=>'generic.png',
	);
	/**
	 * @var array|bool
	 */
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

	/**
	 * the constructor
	 */
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
				$this->cdata = self::get_shippingco($_GET['cid']);
				$this->cformaction = FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shippingcat&action=edit&amp;cid=".$_GET['cid'];
				break;
			case 'view':
				$this->data = self::get_itenary($_GET['cid']);
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
				break;
			case 'delete_details':
				self::delete_itenary($_GET['sid']);
				break;
		}
		self::set_shippingcodb();
		self::set_itenarydb();
	}

	/**
	 * MYSQL delete shipping co
	 * @param $cid
	 */
	private function delete_shippingco($cid) {
		global $aidlink;
		if (isnum($cid)) {
			if (self::verify_shippingCats($cid)) {
				dbquery("DELETE FROM ".DB_ESHOP_SHIPPINGCATS." WHERE cid='".intval($cid)."'");
				redirect(FUSION_SELF.$aidlink."&amp;a_page=shipping");
			}
		}
	}

	/**
	 * MySQL delete itenary details off shipping co
	 * @param $sid
	 */
	private function delete_itenary($sid) {
		global $aidlink;
		if (isnum($sid)) {
			if (self::verify_itenary($sid)) {
				dbquery("DELETE FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE sid='".intval($sid)."'");
				redirect(FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;section=shipping&amp;action=view&amp;cid=".$_GET['cid']);
			}
		}
	}

	/**
	 * MYSQL actions on shipping co - update or insert
	 */
	private function set_shippingcodb() {
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

	/**
	 * MYSQL actions on itenary - update or insert
	 */
	private function set_itenarydb() {
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
	/**
	 * @param $id
	 * @return bool|string
	 */
	public static function verify_itenary($id) {
		if (isnum($id)) {
			return dbcount("(sid)", DB_ESHOP_SHIPPINGITEMS, "sid='".intval($id)."'");
		}
		return false;
	}

	/**
	 * @param $id
	 * @return bool|string
	 */
	public static function verify_shippingCats($id) {
		if (isnum($id)) {
			return dbcount("(cid)", DB_ESHOP_SHIPPINGCATS, "cid='".intval($id)."'");
		}
		return false;
	}

	// Opts Functions
	/**
	 * @return array
	 */
	static function get_ImageOpts() {
		$image_list = array();
		$cat_files = makefilelist(SHIP_DIR, ".|..|index.php", true);
		foreach($cat_files as $file) {
			$image_list[$file] = $file;
		}
		return $image_list;
	}

	/**
	 * @return array
	 */
	static function get_destOpts() {
		global $locale;
		return array(
			'0' => 'No Delivery',
			'1' => $locale['D101'],
			'2' => $locale['D102'],
			'3' => $locale['D103']
		);
	}

	/**
	 * @return array
	 */
	static function get_activeOpts() {
		global $locale;
		return array(
			'1' => $locale['yes'],
			'0' => $locale['no'],
		);
	}

	/**
	 * @return array
	 */
	static function get_dTimeOpts() {
		global $locale;
		return array(
			'0' => $locale['ESHPSS111'],
			'1' => $locale['ESHPSS112'],
			'2' => $locale['ESHPSS113'],
			'3' => $locale['ESHPSS114'],
			'4' => $locale['ESHPSS115'],
			'5' => $locale['ESHPSS116'],
			'6' => $locale['ESHPSS117'],
			'7' => $locale['ESHPSS118'],
			'8' => $locale['ESHPSS119']
		);
	}

	// Data loaders
	/**
	 * @param $id
	 * @return array|bool
	 */
	static function get_shippingco($id) {
		if (isnum($id)) {
			$result = dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGCATS." WHERE cid='".intval($id)."'");
			if (dbrows($result)>0) {
				return dbarray($result);
			}
			return array();
		}
	}

	/* Returns shipping cats */
	public static function get_shipCats() {
		$result = dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGCATS);
		if (dbrows($result)>0) {
			$array = array();
			while ($data = dbarray($result)) {
				$array[$data['cid']] = $data['title'];
			}
			return (array) $array;
		}
		return array();
	}

	/**
	 * @param $id
	 * @return array|bool
	 */
	static function get_itenary($id) {
		if (isnum($id)) {
			$result = dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE sid='".intval($id)."'");
			if (dbrows($result)>0) {
				return dbarray($result);
			}
			return array();
		}
	}

	/**
	 * The shipping form
	 */
	public function add_shippingco_form() {
		global $locale;
		echo "<div class='m-t-10'>\n";
		echo openform('addcat', 'post', $this->cformaction, array('max_tokens' => 1));
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
		echo form_text('title', $locale['ESHPSHPMTS102'], $this->cdata['title']);
		echo form_button('save_shipping', $locale['save'], $locale['save'], array('class'=>'btn-success', 'icon'=>'fa fa-check-square-o'));
		echo closeform();
		echo "</div>\n";
	}

	/**
	 * The listing for shipping co
	 */
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
		echo "<table class='table table-striped table-responsive'>\n";
		echo "<tr>\n";
		echo "<th></th>\n";
		echo "<th class='col-xs-3 col-sm-3'>".$locale['ESHPCHK149']."</th>\n";
		echo "<th>".$locale['ESHPSS100']."</th>\n";
		echo "<th>".$locale['ESHPSS101']."</th>\n";
		echo "<th>".$locale['ESHPSS102']."</th>\n";
		echo "<th>".$locale['ESHPSS103']." ".fusion_get_settings('eshop_weightscale')."</th>\n";
		echo "<th>".$locale['ESHPSS104']." ".fusion_get_settings('eshop_weightscale')."</th>\n";
		echo "<th>".$locale['ESHPSS105']."</th>\n";
		echo "<th>".$locale['ESHPSS106']."</th>\n";
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
				echo "<tr id='listItem_".$data['cid']."' data-id='".$data['cid']."' class='list-result'>\n";
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

	/**
	 * The listing for itenary
	 */
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
			echo openform('add_detail', 'post', $this->sformaction, array('max_tokens' => 1, 'notice' => 0));
			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-6'>\n";
			echo form_text('method', $locale['ESHPSHPMTS107'],$this->sdata['method'], array('required'=>1, 'inline'=>1));
			echo form_select($locale['ESHPSHPMTS108'], 'dtime', 'dtime', self::get_dTimeOpts(), $this->sdata['dtime'], array('required'=>1, 'inline'=>1));
			echo form_select($locale['ESHPSHPMTS109'], 'destination', 'destination', self::get_destOpts(), $this->sdata['destination'], array('inline'=>1));
			echo form_select($locale['ESHPSHPMTS114'], 'active', 'active', self::get_activeOpts(), $this->sdata['active'], array('inline'=>1));
			echo "</div>\n";
			echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-6'>\n";
			echo form_text('weightmin', $locale['ESHPSHPMTS110']." (".fusion_get_settings('eshop_weightscale').")", $this->sdata['weightmin'], array('number'=>1, 'inline'=>1));
			echo form_text('weightmax', $locale['ESHPSHPMTS111']." (".fusion_get_settings('eshop_weightscale').")", $this->sdata['weightmax'], array('number'=>1, 'inline'=>1));
			echo form_text('initialcost', $locale['ESHPSHPMTS112']." (".fusion_get_settings('eshop_currency').")", $this->sdata['initialcost'], array('number'=>1, 'inline'=>1));
			echo form_text('weightcost', $locale['ESHPSHPMTS113']." (".fusion_get_settings('eshop_weightscale').")", $this->sdata['weightcost'], array('number'=>1, 'inline'=>1));
			echo form_hidden('', 'sid', 'sid', $this->sdata['sid'], array('writable' => 1));
			echo form_hidden('', 'cid', 'cid', $_GET['cid'], array('writable' => 1));
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='m-t-10 m-b-10'>\n";
			echo form_button('cancel', $locale['cancel'], 'cancel', array('class' => 'btn btn-default m-r-10',
				'type' => 'button'));
			echo form_button('save_item', $locale['save'], 'save', array('class' => 'btn btn-success', 'icon'=>'fa fa-check-square-o'));
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
