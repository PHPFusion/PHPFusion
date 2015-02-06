<?php

namespace PHPFusion\Eshop;

use PHPFusion\Eshop\Admin\Customers;

class Eshop {

	public $customer_info = array(
		'cuid'=> '',
		'cfirstname' => '',
		'clastname' => '',
		'cdob' => '',
		'ccountry' => '',
		'cregion' => '',
		'ccity' => '',
		'caddress' => '',
		'caddress2' => '',
		'cphone' => '',
		'cfax' => '',
		'cemail' => '',
		'cpostcode' => '',
	);

	private $max_rows = 0;
	private $info = array();
	private $banner_path = '';

	public function __construct() {
		$this->banner_path = BASEDIR."eshop/pictures/banners/";
		$_GET['category'] = isset($_GET['category']) && isnum($_GET['category']) ?  $_GET['category'] : 0;
		$_GET['product'] = isset($_GET['product']) && isnum($_GET['product']) ? $_GET['product'] : 0;
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $this->max_rows ? : 0;
		$_GET['FilterSelect'] = isset($_POST['FilterSelect']) && isnum($_POST['FilterSelect']) ? $_POST['FilterSelect'] : 0;

		$this->info['category_index'] = dbquery_tree(DB_ESHOP_CATS, 'cid', 'parentid');
		$this->info['category'] = dbquery_tree_full(DB_ESHOP_CATS, 'cid', 'parentid');

		self::update_cart();
		// filter the rubbish each run
		dbquery("DELETE FROM ".DB_ESHOP_CART." WHERE cadded < ".time()."-2592180");
	}

	/* Customer form fields */
	public function display_customer_form($cuid = '') {
		global $locale;
		// load the customer data
		$this->customer_info['cuid'] = (isnum($cuid)) ? $cuid : \defender::set_sessionUserID(); // override default 0
		$customer_info = Customers::get_customerData($this->customer_info['cuid']); // binds the above
		// and append data if exist.
		if (!empty($customer_info)) {
			$this->customer_info = $customer_info;
		}

		// Post Action
		if (isset($_POST['save_customer'])) {
			$this->customer_info['cuid'] = isset($_POST['cuid']) ? form_sanitizer($_POST['cuid'], '0', 'cuid') : 0; // user select
			$this->customer_info['cemail'] = isset($_POST['cemail']) ? form_sanitizer($_POST['cemail'], '', 'cemail') : '';
			$this->customer_info['cdob'] = isset($_POST['cdob']) ? form_sanitizer($_POST['cdob'], '', 'cdob') : '';
			$this->customer_info['cname'] = implode('|', $_POST['cname']); // backdoor to traverse back to dynamic
			$name = isset($_POST['cname']) ? form_sanitizer($_POST['cname'], '', 'cname') : '';
			if (!empty($name)) {
				$name = explode('|', $name);
				$this->customer_info['cfirstname'] = $name[0];
				$this->customer_info['clastname'] = $name[1];
			}
			// this goes back to form.
			$this->customer_info['caddress'] = implode('|', $_POST['caddress']);
			$address = isset($_POST['caddress']) ? form_sanitizer($_POST['caddress'], '', 'caddress') : '';
			if (!empty($address)) {
				$address = explode('|', $address);
				// this go into sql only
				$this->customer_info['caddress'] = $address[0];
				$this->customer_info['caddress2'] = $address[1];
				$this->customer_info['ccountry'] = $address[2];
				$this->customer_info['cregion'] = $address[3];
				$this->customer_info['ccity'] = $address[4];
				$this->customer_info['cpostcode'] = $address[5];
			}
			$this->customer_info['cphone'] = isset($_POST['cphone']) ? form_sanitizer($_POST['cphone'], '', 'cphone') : '';
			$this->customer_info['cfax'] = isset($_POST['cfax']) ? form_sanitizer($_POST['cfax'], '', 'cfax') : '';
			$this->customer_info['ccupons'] = isset($_POST['ccupons']) ? form_sanitizer($_POST['ccupons'], '', 'ccupons') : ''; // why is cupons available in customer db????
			if (Customers::verify_customer($this->customer_info['cuid'])) {
				dbquery_insert(DB_ESHOP_CUSTOMERS, $this->customer_info, 'update', array('no_unique'=>1, 'primary_key'=>'cuid'));
				if (!defined('FUSION_NULL')) redirect(BASEDIR."eshop.php?checkout");
			} else {
				dbquery_insert(DB_ESHOP_CUSTOMERS, $this->customer_info, 'save',  array('no_unique'=>1, 'primary_key'=>'cuid'));
				if (!defined('FUSION_NULL')) redirect(BASEDIR."eshop.php?checkout");
			}
		}

		$html = "<div class='m-t-20'>\n";
		$html .= openform('customerform', 'customerform', 'post', BASEDIR."eshop.php?checkout", array('downtime'=>0, 'notice'=>0));
		$customer_name[] = $this->customer_info['cfirstname'];
		$customer_name[] = $this->customer_info['clastname'];
		$customer_name = implode('|', $customer_name);
		$html .= form_name('Customer Name', 'cname', 'cname', $customer_name, array('required'=>1, 'inline'=>1));
		$html .= form_text($locale['ESHPCHK115'], 'cemail', 'cemail', $this->customer_info['cemail'], array('inline'=>1, 'required'=>1, 'email'=>1));
		$html .= form_datepicker($locale['ESHPCHK105'], 'cdob', 'cdob', $this->customer_info['cdob'], array('inline'=>1, 'required'=>1));
		$customer_address[] = $this->customer_info['caddress']; // use this as backdoor.
		$customer_address[] = $this->customer_info['caddress2'];
		$customer_address[] = $this->customer_info['ccountry'];
		$customer_address[] = $this->customer_info['cregion'];
		$customer_address[] = $this->customer_info['ccity'];
		$customer_address[] = $this->customer_info['cpostcode'];
		$customer_address = implode('|', $customer_address);
		$html .= form_address($locale['ESHPCHK106'], 'caddress', 'caddress', $customer_address, array('required'=>1, 'inline'=>1));
		$html .= form_text($locale['ESHPCHK113'], 'cphone', 'cphone', $this->customer_info['cphone'], array('required'=>1, 'inline'=>1, 'number'=>1));
		$html .= form_text($locale['ESHPCHK114'], 'cfax', 'cfax', $this->customer_info['cfax'], array('inline'=>1, 'number'=>1)); // this not compulsory
		$html .= form_hidden('', 'cuid', 'cuid', $this->customer_info['cuid']);
		$html .= form_button($locale['save'], 'save_customer', 'save_customer', $locale['save'], array('class'=>'btn-primary'));
		$html .= closeform();
		$html .= "</div>\n";
		$info['customer_form'] = $html;
		return $info;
	}

	public static function get_productSpecs($serial_value, $key_num) {
		$_str = '';
		if (!empty($serial_value)) {
			$var = str_replace("&quot;", "", $serial_value);
			$_array = array_filter(explode('.', $var));
			if (isset($_array[$key_num])) {
				$_str = $_array[$key_num];
			}
		}
		return (string) $_str;
	}

	public static  function get_productColor($key_num) {
		$color = '';
		if (self::get_iColor($key_num)) {
			$color = self::get_iColor($key_num);
			if (isset($color['title'])) {
				return (string) $color['title'];
			} else {
				return '';
			}
		}
		return (string) $color;
	}

	protected function update_cart() {
		if (isset($_POST['utid']) && isnum($_POST['utid'])) {
			$quantity = form_sanitizer($_POST['qty'], '', 'qty');
			$tid = intval($_POST['utid']);
			if (dbcount('(tid)', DB_ESHOP_CART, "tid = '".$tid."'")) {
				dbquery("UPDATE ".DB_ESHOP_CART." SET cqty='".intval($quantity)."' WHERE tid='".$tid."'");
				redirect(BASEDIR."eshop.php?checkout");
			}
		}
	}

	/**
	 * Get Featured Items
	 * @return array
	 */
	public function get_featured() {
		$info = array();
		$result = dbquery("select * FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_cid = '".$_GET['category']."' ORDER BY featbanner_order");
		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				$data['featbanner_banner'] = file_exists($this->banner_path.$data['featbanner_banner']) ? $this->banner_path.$data['featbanner_banner'] : '';
				$info['featured'][$data['featbanner_aid']] = $data;
			}
		}
		return (array) $info;
	}

	/**
	 * Hardcoded Chart Array for Products
	 * @return array
	 */
	static function get_iColor($key= 0) {
		global $ESHPCLRS;
		$ESHOPCOLOURS[1] = array('hex'=>'#F0F8FF', 'title'=>$ESHPCLRS['1']);
		$ESHOPCOLOURS[2] = array('hex'=>'#FAEBD7', 'title'=>$ESHPCLRS['2']);
		$ESHOPCOLOURS[3] = array('hex'=>'#00FFFF', 'title'=>$ESHPCLRS['3']);
		$ESHOPCOLOURS[4] = array('hex'=>'#7FFFD4', 'title'=>$ESHPCLRS['4']);
		$ESHOPCOLOURS[5] = array('hex'=>'#F0FFFF', 'title'=>$ESHPCLRS['5']);
		$ESHOPCOLOURS[6] = array('hex'=>'#F5F5DC', 'title'=>$ESHPCLRS['6']);
		$ESHOPCOLOURS[7] = array('hex'=>'#FFE4C4', 'title'=>$ESHPCLRS['7']);
		$ESHOPCOLOURS[8] = array('hex'=>'#000000', 'title'=>$ESHPCLRS['8']);
		$ESHOPCOLOURS[9] = array('hex'=>'#FFEBCD', 'title'=>$ESHPCLRS['9']);
		$ESHOPCOLOURS[10] = array('hex'=>'#0000FF', 'title'=>$ESHPCLRS['10']);
		$ESHOPCOLOURS[11] = array('hex'=>'#8A2BE2', 'title'=>$ESHPCLRS['11']);
		$ESHOPCOLOURS[12] = array('hex'=>'#A52A2A', 'title'=>$ESHPCLRS['12']);
		$ESHOPCOLOURS[13] = array('hex'=>'#DEB887', 'title'=>$ESHPCLRS['13']);
		$ESHOPCOLOURS[14] = array('hex'=>'#5F9EA0', 'title'=>$ESHPCLRS['14']);
		$ESHOPCOLOURS[15] = array('hex'=>'#7FFF00', 'title'=>$ESHPCLRS['15']);
		$ESHOPCOLOURS[16] = array('hex'=>'#D2691E', 'title'=>$ESHPCLRS['16']);
		$ESHOPCOLOURS[17] = array('hex'=>'#FF7F50', 'title'=>$ESHPCLRS['17']);
		$ESHOPCOLOURS[18] = array('hex'=>'#6495ED', 'title'=>$ESHPCLRS['18']);
		$ESHOPCOLOURS[19] = array('hex'=>'#FFF8DC', 'title'=>$ESHPCLRS['19']);
		$ESHOPCOLOURS[20] = array('hex'=>'#DC143C', 'title'=>$ESHPCLRS['20']);
		$ESHOPCOLOURS[21] = array('hex'=>'#00FFFF', 'title'=>$ESHPCLRS['21']);
		$ESHOPCOLOURS[22] = array('hex'=>'#00008B', 'title'=>$ESHPCLRS['22']);
		$ESHOPCOLOURS[23] = array('hex'=>'#008B8B', 'title'=>$ESHPCLRS['23']);
		$ESHOPCOLOURS[24] = array('hex'=>'#B8860B', 'title'=>$ESHPCLRS['24']);
		$ESHOPCOLOURS[25] = array('hex'=>'#A9A9A9', 'title'=>$ESHPCLRS['25']);
		$ESHOPCOLOURS[26] = array('hex'=>'#BDB76B', 'title'=>$ESHPCLRS['26']);
		$ESHOPCOLOURS[27] = array('hex'=>'#8B008B', 'title'=>$ESHPCLRS['27']);
		$ESHOPCOLOURS[28] = array('hex'=>'#556B2F', 'title'=>$ESHPCLRS['28']);
		$ESHOPCOLOURS[29] = array('hex'=>'#FF8C00', 'title'=>$ESHPCLRS['29']);
		$ESHOPCOLOURS[30] = array('hex'=>'#9932CC', 'title'=>$ESHPCLRS['30']);
		$ESHOPCOLOURS[31] = array('hex'=>'#8B0000', 'title'=>$ESHPCLRS['31']);
		$ESHOPCOLOURS[32] = array('hex'=>'#E9967A', 'title'=>$ESHPCLRS['32']);
		$ESHOPCOLOURS[33] = array('hex'=>'#8FBC8F', 'title'=>$ESHPCLRS['33']);
		$ESHOPCOLOURS[34] = array('hex'=>'#483D8B', 'title'=>$ESHPCLRS['34']);
		$ESHOPCOLOURS[35] = array('hex'=>'#2F4F4F', 'title'=>$ESHPCLRS['35']);
		$ESHOPCOLOURS[36] = array('hex'=>'#00CED1', 'title'=>$ESHPCLRS['36']);
		$ESHOPCOLOURS[37] = array('hex'=>'#9400D3', 'title'=>$ESHPCLRS['37']);
		$ESHOPCOLOURS[38] = array('hex'=>'#FF1493', 'title'=>$ESHPCLRS['38']);
		$ESHOPCOLOURS[39] = array('hex'=>'#00BFFF', 'title'=>$ESHPCLRS['39']);
		$ESHOPCOLOURS[40] = array('hex'=>'#696969', 'title'=>$ESHPCLRS['40']);
		$ESHOPCOLOURS[41] = array('hex'=>'#1E90FF', 'title'=>$ESHPCLRS['41']);
		$ESHOPCOLOURS[42] = array('hex'=>'#B22222', 'title'=>$ESHPCLRS['42']);
		$ESHOPCOLOURS[43] = array('hex'=>'#FFFAF0', 'title'=>$ESHPCLRS['43']);
		$ESHOPCOLOURS[44] = array('hex'=>'#228B22', 'title'=>$ESHPCLRS['44']);
		$ESHOPCOLOURS[45] = array('hex'=>'#FF00FF', 'title'=>$ESHPCLRS['45']);
		$ESHOPCOLOURS[46] = array('hex'=>'#DCDCDC', 'title'=>$ESHPCLRS['46']);
		$ESHOPCOLOURS[47] = array('hex'=>'#F8F8FF', 'title'=>$ESHPCLRS['47']);
		$ESHOPCOLOURS[48] = array('hex'=>'#FFD700', 'title'=>$ESHPCLRS['48']);
		$ESHOPCOLOURS[49] = array('hex'=>'#DAA520', 'title'=>$ESHPCLRS['49']);
		$ESHOPCOLOURS[50] = array('hex'=>'#808080', 'title'=>$ESHPCLRS['50']);
		$ESHOPCOLOURS[51] = array('hex'=>'#008000', 'title'=>$ESHPCLRS['51']);
		$ESHOPCOLOURS[52] = array('hex'=>'#ADFF2F', 'title'=>$ESHPCLRS['52']);
		$ESHOPCOLOURS[53] = array('hex'=>'#F0FFF0', 'title'=>$ESHPCLRS['53']);
		$ESHOPCOLOURS[54] = array('hex'=>'#FF69B4', 'title'=>$ESHPCLRS['54']);
		$ESHOPCOLOURS[55] = array('hex'=>'#CD5C5C', 'title'=>$ESHPCLRS['55']);
		$ESHOPCOLOURS[56] = array('hex'=>'#4B0082', 'title'=>$ESHPCLRS['56']);
		$ESHOPCOLOURS[57] = array('hex'=>'#F0E68C', 'title'=>$ESHPCLRS['57']);
		$ESHOPCOLOURS[58] = array('hex'=>'#E6E6FA', 'title'=>$ESHPCLRS['58']);
		$ESHOPCOLOURS[59] = array('hex'=>'#FFF0F5', 'title'=>$ESHPCLRS['59']);
		$ESHOPCOLOURS[60] = array('hex'=>'#7CFC00', 'title'=>$ESHPCLRS['60']);
		$ESHOPCOLOURS[61] = array('hex'=>'#FFFACD', 'title'=>$ESHPCLRS['61']);
		$ESHOPCOLOURS[62] = array('hex'=>'#ADD8E6', 'title'=>$ESHPCLRS['62']);
		$ESHOPCOLOURS[63] = array('hex'=>'#F08080', 'title'=>$ESHPCLRS['63']);
		$ESHOPCOLOURS[64] = array('hex'=>'#E0FFFF', 'title'=>$ESHPCLRS['64']);
		$ESHOPCOLOURS[65] = array('hex'=>'#FAFAD2', 'title'=>$ESHPCLRS['65']);
		$ESHOPCOLOURS[66] = array('hex'=>'#D3D3D3', 'title'=>$ESHPCLRS['66']);
		$ESHOPCOLOURS[67] = array('hex'=>'#90EE90', 'title'=>$ESHPCLRS['67']);
		$ESHOPCOLOURS[68] = array('hex'=>'#FFB6C1', 'title'=>$ESHPCLRS['68']);
		$ESHOPCOLOURS[69] = array('hex'=>'#FFA07A', 'title'=>$ESHPCLRS['69']);
		$ESHOPCOLOURS[70] = array('hex'=>'#20B2AA', 'title'=>$ESHPCLRS['70']);
		$ESHOPCOLOURS[71] = array('hex'=>'#87CEFA', 'title'=>$ESHPCLRS['71']);
		$ESHOPCOLOURS[72] = array('hex'=>'#778899', 'title'=>$ESHPCLRS['72']);
		$ESHOPCOLOURS[73] = array('hex'=>'#B0C4DE', 'title'=>$ESHPCLRS['73']);
		$ESHOPCOLOURS[74] = array('hex'=>'#FFFFE0', 'title'=>$ESHPCLRS['74']);
		$ESHOPCOLOURS[75] = array('hex'=>'#00FF00', 'title'=>$ESHPCLRS['75']);
		$ESHOPCOLOURS[76] = array('hex'=>'#FF00FF', 'title'=>$ESHPCLRS['76']);
		$ESHOPCOLOURS[77] = array('hex'=>'#800000', 'title'=>$ESHPCLRS['77']);
		$ESHOPCOLOURS[78] = array('hex'=>'#66CDAA', 'title'=>$ESHPCLRS['78']);
		$ESHOPCOLOURS[79] = array('hex'=>'#0000CD', 'title'=>$ESHPCLRS['79']);
		$ESHOPCOLOURS[80] = array('hex'=>'#BA55D3', 'title'=>$ESHPCLRS['80']);
		$ESHOPCOLOURS[81] = array('hex'=>'#9370DB', 'title'=>$ESHPCLRS['81']);
		$ESHOPCOLOURS[82] = array('hex'=>'#3CB371', 'title'=>$ESHPCLRS['82']);
		$ESHOPCOLOURS[83] = array('hex'=>'#7B68EE', 'title'=>$ESHPCLRS['83']);
		$ESHOPCOLOURS[84] = array('hex'=>'#00FA9A', 'title'=>$ESHPCLRS['84']);
		$ESHOPCOLOURS[85] = array('hex'=>'#48D1CC', 'title'=>$ESHPCLRS['85']);
		$ESHOPCOLOURS[86] = array('hex'=>'#C71585', 'title'=>$ESHPCLRS['86']);
		$ESHOPCOLOURS[87] = array('hex'=>'#191970', 'title'=>$ESHPCLRS['87']);
		$ESHOPCOLOURS[88] = array('hex'=>'#F5FFFA', 'title'=>$ESHPCLRS['88']);
		$ESHOPCOLOURS[89] = array('hex'=>'#FFE4E1', 'title'=>$ESHPCLRS['89']);
		$ESHOPCOLOURS[90] = array('hex'=>'#FFE4B5', 'title'=>$ESHPCLRS['90']);
		$ESHOPCOLOURS[91] = array('hex'=>'#FFDEAD', 'title'=>$ESHPCLRS['91']);
		$ESHOPCOLOURS[92] = array('hex'=>'#000080', 'title'=>$ESHPCLRS['92']);
		$ESHOPCOLOURS[93] = array('hex'=>'#FDF5E6', 'title'=>$ESHPCLRS['93']);
		$ESHOPCOLOURS[94] = array('hex'=>'#808000', 'title'=>$ESHPCLRS['94']);
		$ESHOPCOLOURS[95] = array('hex'=>'#6B8E23', 'title'=>$ESHPCLRS['95']);
		$ESHOPCOLOURS[96] = array('hex'=>'#FFA500', 'title'=>$ESHPCLRS['96']);
		$ESHOPCOLOURS[97] = array('hex'=>'#FF4500', 'title'=>$ESHPCLRS['97']);
		$ESHOPCOLOURS[98] = array('hex'=>'#DA70D6', 'title'=>$ESHPCLRS['98']);
		$ESHOPCOLOURS[99] = array('hex'=>'#EEE8AA', 'title'=>$ESHPCLRS['99']);
		$ESHOPCOLOURS[100] = array('hex'=>'#98FB98', 'title'=>$ESHPCLRS['100']);
		$ESHOPCOLOURS[101] = array('hex'=>'#AFEEEE', 'title'=>$ESHPCLRS['101']);
		$ESHOPCOLOURS[102] = array('hex'=>'#DB7093', 'title'=>$ESHPCLRS['102']);
		$ESHOPCOLOURS[103] = array('hex'=>'#FFEFD5', 'title'=>$ESHPCLRS['103']);
		$ESHOPCOLOURS[104] = array('hex'=>'#FFDAB9', 'title'=>$ESHPCLRS['104']);
		$ESHOPCOLOURS[105] = array('hex'=>'#CD853F', 'title'=>$ESHPCLRS['105']);
		$ESHOPCOLOURS[106] = array('hex'=>'#FFC0CB', 'title'=>$ESHPCLRS['106']);
		$ESHOPCOLOURS[107] = array('hex'=>'#DDA0DD', 'title'=>$ESHPCLRS['107']);
		$ESHOPCOLOURS[108] = array('hex'=>'#B0E0E6', 'title'=>$ESHPCLRS['108']);
		$ESHOPCOLOURS[109] = array('hex'=>'#800080', 'title'=>$ESHPCLRS['109']);
		$ESHOPCOLOURS[110] = array('hex'=>'#FF0000', 'title'=>$ESHPCLRS['110']);
		$ESHOPCOLOURS[111] = array('hex'=>'#BC8F8F', 'title'=>$ESHPCLRS['111']);
		$ESHOPCOLOURS[112] = array('hex'=>'#8B4513', 'title'=>$ESHPCLRS['112']);
		$ESHOPCOLOURS[113] = array('hex'=>'#FA8072', 'title'=>$ESHPCLRS['113']);
		$ESHOPCOLOURS[114] = array('hex'=>'#F4A460', 'title'=>$ESHPCLRS['114']);
		$ESHOPCOLOURS[115] = array('hex'=>'#2E8B57', 'title'=>$ESHPCLRS['115']);
		$ESHOPCOLOURS[116] = array('hex'=>'#FFF5EE', 'title'=>$ESHPCLRS['116']);
		$ESHOPCOLOURS[117] = array('hex'=>'#A0522D', 'title'=>$ESHPCLRS['117']);
		$ESHOPCOLOURS[118] = array('hex'=>'#C0C0C0', 'title'=>$ESHPCLRS['118']);
		$ESHOPCOLOURS[119] = array('hex'=>'#87CEEB', 'title'=>$ESHPCLRS['119']);
		$ESHOPCOLOURS[120] = array('hex'=>'#6A5ACD', 'title'=>$ESHPCLRS['120']);
		$ESHOPCOLOURS[121] = array('hex'=>'#708090', 'title'=>$ESHPCLRS['121']);
		$ESHOPCOLOURS[122] = array('hex'=>'#FFFAFA', 'title'=>$ESHPCLRS['122']);
		$ESHOPCOLOURS[123] = array('hex'=>'#00FF7F', 'title'=>$ESHPCLRS['123']);
		$ESHOPCOLOURS[124] = array('hex'=>'#4682B4', 'title'=>$ESHPCLRS['124']);
		$ESHOPCOLOURS[125] = array('hex'=>'#D2B48C', 'title'=>$ESHPCLRS['125']);
		$ESHOPCOLOURS[126] = array('hex'=>'#008080', 'title'=>$ESHPCLRS['126']);
		$ESHOPCOLOURS[127] = array('hex'=>'#D8BFD8', 'title'=>$ESHPCLRS['127']);
		$ESHOPCOLOURS[128] = array('hex'=>'#FF6347', 'title'=>$ESHPCLRS['128']);
		$ESHOPCOLOURS[129] = array('hex'=>'#40E0D0', 'title'=>$ESHPCLRS['129']);
		$ESHOPCOLOURS[130] = array('hex'=>'#EE82EE', 'title'=>$ESHPCLRS['130']);
		$ESHOPCOLOURS[131] = array('hex'=>'#F5DEB3', 'title'=>$ESHPCLRS['131']);
		$ESHOPCOLOURS[132] = array('hex'=>'#FFFFFF', 'title'=>$ESHPCLRS['132']);
		$ESHOPCOLOURS[133] = array('hex'=>'#F5F5F5', 'title'=>$ESHPCLRS['133']);
		$ESHOPCOLOURS[134] = array('hex'=>'#FFFF00', 'title'=>$ESHPCLRS['134']);
		$ESHOPCOLOURS[135] = array('hex'=>'#9ACD32', 'title'=>$ESHPCLRS['135']);
		if ($key && isset($ESHOPCOLOURS[$key])) {
			return (array) $ESHOPCOLOURS[$key];
		} else {
			return (array) $ESHOPCOLOURS;
		}
	}

	// clear cart actions
	static function clear_cart() {
		global $userdata, $locale;
		$id = iMEMBER ? $userdata['user_id'] : $_SERVER['REMOTE_ADDR'];
		if (isset($_GET['clearcart']) && isnum($id)) {
			dbquery("DELETE FROM ".DB_ESHOP_CART." WHERE puid ='".$id."'");
			echo admin_message($locale['ESHPC100']);
		}
	}

	/**
	 * Get Product Data by using an product ID
	 * @param $id - product ID
	 * @return array
	 */
	static function get_productData($id) {
		$result = array();
		if (isnum($id)) {
			$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE id='".intval($id)."'");
			if (dbrows($result)) {
				return (array) dbarray($result);
			}
		}
		return (array) $result;
	}

	/**
	 * Get current category in relation to $_GET['category']
	 * @return array
	 */
	public function get_current_category() {
		$folder = get_parent($this->info['category_index'], $_GET['category']);
		if ($_GET['category']) {
			return (array) isset($this->info['category'][$folder][$_GET['category']]) ? $this->info['category'][$folder][$_GET['category']] : array();
		}
		return array();
	}

	/**
	 * Get Previous category in relation to current $_GET['category']
	 * @return array
	 */
	public function get_previous_category() {
		if ($_GET['category']) {
			$parent_id = get_parent($this->info['category_index'], $_GET['category']);
			$folder = get_parent($this->info['category_index'], $parent_id) ? get_parent($this->info['category_index'], $parent_id) : '0';
			if (isset($this->info['category'][$folder][$parent_id])) {
				return (array) $this->info['category'][$folder][$parent_id];
			} else {
				return array();
			}
		}
		return array();
	}

	/**
	 * Dynamically Automatic Set Breadcrumbs, Meta, Title on Eshop Pages
	 * @return array
	 */
	public function get_title() {
		global $locale;
		$info = array();
		add_to_title($locale['ESHP031']);
		if ($_GET['category']) {
			$current_category = self::get_current_category();
			$info['title'] = $current_category['title'];
			add_to_title($locale['global_201'].$current_category['title']);
			add_to_breadcrumbs(array('link'=>BASEDIR."eshop.php?category=".$current_category['cid']."", 'title'=>$info['title']));
		} elseif ($_GET['product']) {
			add_to_head("<link rel='canonical' href='".fusion_get_settings('siteurl')."eshop.php?product=".$_GET['product']."'/>");
			add_to_title($locale['global_201'].$this->info['title']);
			add_to_title($locale['global_201'].$this->info['category_title']);
			if ($this->info['keywords']) { set_meta("keywords", $this->info['keywords']); }
			if (fusion_get_settings('eshop_folderlink') == 1 && fusion_get_settings('eshop_cats') == 1) {
				add_to_breadcrumbs(array('link'=>$this->info['category_link'], 'title'=>$this->info['category_title']));
				add_to_breadcrumbs(array('link'=>$this->info['product_link'], 'title'=>$this->info['product_title']));
			}
		} else {
			$info['title'] = $locale['ESHP001'];
		}
		return (array) $info;
	}

	/**
	 * Display Social Buttons
	 * Disable the shareing during SEO, it crash with SEO atm for some reason.
	 * wierd height behavior on g+1 button
	 * @param $product_id
	 * @param $product_picture
	 * @param $product_title
	 */
	static function display_social_buttons($product_id, $product_picture, $product_title) {
		if (!fusion_get_settings('site_seo') && fusion_get_settings('eshop_shareing') == 1) {
			//Load scripts to enable share buttons
			$meta = "<meta property='og:image' content='".fusion_get_settings('siteurl')."eshop/img/nopic.gif' />\n";
			if (file_exists(BASEDIR."eshop/pictures/".$product_picture)) {
				$meta = "<meta property='og:image' content='".fusion_get_settings('siteurl')."eshop/pictures/".$product_picture."' />\n";
			}
			add_to_head("".$meta."<meta property='og:title' content='".$product_title."' />");
			add_to_footer("
			<script type='text/javascript' src='https://connect.facebook.net/en_US/all.js#xfbml=1'></script>\n
			<script type='text/javascript' src='https://platform.twitter.com/widgets.js'></script>\n
			<script type='text/javascript' src='https://apis.google.com/js/plusone.js'>{ lang: 'en-GB' } </script>
			");

			$html = "<div class='display-block clearfix m-b-20'>";
			//FB Like button
			$html .="<div class='pull-left m-r-10'>";
			$html .="<div id='FbCont".$product_id."'>
			<script type='text/javascript'>
				<!--//--><![CDATA[//><!--
				var fb = document.createElement('fb:like');
				fb.setAttribute('href','".fusion_get_settings('siteurl')."eshop.php?product=".$product_id."');
				fb.setAttribute('layout','button_count');
				fb.setAttribute('show_faces','true');
				fb.setAttribute('width','1');
				document.getElementById('FbCont".$product_id."').appendChild(fb);
				//--><!]]>
				</script>
			</div>";
			$html .="</div>";
			//Google+
			$html .="<div class='pull-left' style='width:70px; overflow:hidden; overflow: hidden;
					height: 40px;
					margin-top:-14px;
					display: inline-block;
					'>";
			$html .="<div class='g-plusone pull-left' id='gplusone".$product_id."'></div>
			<script type='text/javascript'>
			var Validplus=document.getElementById('gplusone".$product_id."');
			Validplus.setAttribute('data-size','medium');
			Validplus.setAttribute('data-count','true');
			Validplus.setAttribute('data-href','".fusion_get_settings('siteurl')."eshop.php?product=".$product_id."');
			</script>";
			$html .="</div>";
			//Twitter
			$html .="<div class='pull-left'>";
			$html .="<script type='text/javascript'>
			//<![CDATA[
			(function() {
    		document.write('<a href=\"http://twitter.com/share\" class=\"twitter-share-button\" data-count=\"horizontal\" data-url=\"".fusion_get_settings('siteurl')."eshop.php?product=".$product_id."\" data-text=\"".$product_title."\" data-via=\"eShop\">Tweet</a>');
    		var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
    		s.type = 'text/javascript';
    		s.async = true;
    		s1.parentNode.insertBefore(s, s1);
			})();
			//]]>
			</script>";
			$html .="</div>";
			//End share buttons
			$html .="</div>";
			return $html;
		}
	}

	/**
	 * Return the image source path
	 * @param $image_file
	 * @return string
	 */
	static function picExist($image_file) {
		if (file_exists($image_file)) {
			return $image_file;
		} else {
			return SHOP."img/nopic_thumb.gif";
		}
	}

	// special components ??
	static function makeeshoppagenav($start, $count, $total, $range = 0, $link = "") {
		global $locale;
		if ($link == "") $link = FUSION_SELF."?";
		$res = "";
		$pg_cnt = ceil($total/$count);
		if ($pg_cnt > 1) {
			$idx_back = $start-$count;
			$idx_next = $start+$count;
			$cur_page = ceil(($start+1)/$count);
			$res .= "<table style='width:500px' class='text-center tbl-border'><tr>\n";
			if ($idx_back >= 0) {
				$res .= "<td width='20%' align='center' class='tbl2'><span class='small'><a href='$link"."rowstart=$idx_back'>".$locale['ESHP002']."</a></span></td>\n";
			}
			$idx_fst = max($cur_page-$range, 1);
			$idx_lst = min($cur_page+$range, $pg_cnt);
			if ($range == 0) {
				$idx_fst = 1;
				$idx_lst = $pg_cnt;
			} else {
				$res .= "<td width='20%' align='center' class='tbl1'><span class='small'>".$locale['ESHP003']." $cur_page/$pg_cnt</span></td>\n";
			}
			if ($idx_next < $total) {
				$res .= "<td width='20%' align='center' class='tbl2'><span class='small'><a href='$link"."rowstart=$idx_next'>".$locale['ESHP004']."</a></span></td>\n";
			}
			$res .= "</tr>\n</table>\n";
		}
		return $res;
	}

	static function buildfilters() {
		global $data, $locale, $settings, $rowstart, $filter, $category;
		$filter = "";
		echo '<script type="text/javascript">
		<!--
		var saveclass = null;
		function saveFilter(cookieValue) {
			var sel = document.getElementById("FilterSelect");
			saveclass = saveclass ? saveclass : document.body.className;
			document.body.className = saveclass + " " + sel.value;
			setCookie("Filter", cookieValue, 365);
		}
		function setCookie(cookieName, cookieValue, nDays) {
			var today = new Date();
			var expire = new Date();
			if (nDays==null || nDays==0)
				nDays=1;
			expire.setTime(today.getTime() + 3600000*24*nDays);
			document.cookie = cookieName+"="+escape(cookieValue) + ";expires="+expire.toGMTString();
			$("#filters").submit();
		}
		function readCookie(name) {
		  var nameEQ = name + "=";
		  var ca = document.cookie.split(";");
		  for(var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == " ") c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
		  }
		  return null;
		}
		function readCookie(name) {
		  var nameEQ = name + "=";
		  var ca = document.cookie.split(";");
		  for(var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == " ") c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
		  }
		  return null;
		}
		document.addEventListener("DOMContentLoaded", function() {
			var FilterSelect = document.getElementById("FilterSelect");
			var selectedFilter = readCookie("Filter");
			FilterSelect.value = selectedFilter;
			saveclass = saveclass ? saveclass : document.body.className;
			document.body.className = saveclass + " " + selectedFilter;
		});
		-->
		</script>';

		echo "<div style='float:right;margin-top:5px;margin-left:5px;'>";
		echo "<form name='filters' id='filters' action='".FUSION_SELF."".(isset($_GET['rowstart']) ? "?rowstart=".$_GET['rowstart']."" : "")."".(isset($_GET['category']) ? "&amp;category=".$_GET['category']."" : "")."".(isset($_REQUEST['esrchtext']) ? "&amp;esrchtext=".$_REQUEST['esrchtext']."" : "")."' method='post'>
		<div style='font-size:16px;display:inline;vertical-align:middle;'> ".$locale['ESHPF207']." </div> <select class='eshptextbox' style='height:23px !important;width:140px !important;' name='FilterSelect' id='FilterSelect' onchange='saveFilter(this.value);'>
		<option value='1'>".$locale['ESHPF200']."</option>
		<option value='2'>".$locale['ESHPF201']."</option>
		<option value='3'>".$locale['ESHPF202']."</option>
		<option value='4'>".$locale['ESHPF203']."</option>
		<option value='5'>".$locale['ESHPF204']."</option>
		<option value='6'>".$locale['ESHPF205']."</option>
		<option value='7'>".$locale['ESHPF206']."</option>
		</select></form></div>";
		if (!isset($_COOKIE['Filter'])) {
			$filter = "iorder ASC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "1") {
			$filter = "iorder ASC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "2") {
			$filter = "sellcount DESC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "3") {
			$filter = "id DESC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "4") {
			$filter = "price ASC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "5") {
			$filter = "price DESC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "6") {
			$filter = "title ASC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "7") {
			$filter = "title DESC";
		}
	}

	/**
	 * Get Category Information Array
	 * @return array
	 */
	public function get_category() {
		if (!empty($this->info['category'])) {
			foreach($this->info['category'] as $branch_id => $branch) {
				foreach($branch as $id => $node) {
					$this->info['category'][$branch_id][$id]['link'] = BASEDIR."eshop.php?category=".$node['cid'];
				}
			}
		}
		$info['category_index'] = $this->info['category_index'];
		$info['current_category'] = self::get_current_category();
		$info['previous_category'] = self::get_previous_category();
		$info['category'] = $this->info['category'];
		return (array) $info;
	}

	// Temporary Store this here for panels while I delete old codes
	protected function total_basket() {
		$username  ='';
		$settings = '';
		$items = "";
		$sum = "";
		$items = dbarray(dbquery("SELECT sum(cqty) as count FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC"));
		$sum = dbarray(dbquery("SELECT sum(cprice*cqty) as totals FROM ".DB_ESHOP_CART." WHERE puid = '".$username."'"));
		$vat = $settings['eshop_vat'];
		$price = $sum['totals'];
		$vat = ($price/100)*$vat;
		if ($settings['eshop_vat_default'] == "0") {
			$totalincvat = $price+$vat;
		} else {
			$totalincvat = $price;
		}
	}

	/**
	 * Fetches Product Photos when $_GET['product'] is available
	 * @return array
	 */
	static function get_product_photos() {
		$info = array();
		$result = dbquery("SELECT * FROM ".DB_ESHOP_PHOTOS." WHERE album_id='".intval($_GET['product'])."' ORDER BY photo_order");
		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				$data['photo_filename'] = self::picExist(SHOP."pictures/album_".$data['album_id']."/".$data['photo_filename']);
				$data['photo_thumb1'] = self::picExist(SHOP."pictures/album_".$data['album_id']."/".$data['photo_thumb1']);
				$info['photos'][] = $data;
			}
		}
		return (array)$info;
	}


	/**
	 * Get Product Data from Database
	 * If ($_GET['category']) is available, will return info on the category and its child only
	 * If ($_GET['product']) is available, will return full product info
	 * @return array
	 */
	public function get_product() {
		global $locale;
		$result = null;
		$info = array();
		// set max rows
		$max_result = dbquery("SELECT id FROM ".DB_ESHOP." WHERE active = '1' AND ".groupaccess('access')."");
		$this->max_rows = dbrows($max_result);
		$info['max_rows'] = $this->max_rows;
		if ($_GET['product']) {
			$result = dbquery("SELECT i.*, if(i.cid >0, cat.title, 0) as category_title
			FROM ".DB_ESHOP." i
			LEFT JOIN ".DB_ESHOP_CATS." cat on (i.cid=cat.cid)
			WHERE active = '1' AND id='".intval($_GET['product'])."' AND ".groupaccess('i.access')." LIMIT ".$_GET['rowstart'].", ".fusion_get_settings('eshop_noppf')."");
			if (!dbrows($result)) {
				redirect(BASEDIR."eshop.php");
			} else {
				$data = dbarray($result);
				$es_langs = explode('.', $data['product_languages']);
				if (in_array(LANGUAGE, $es_langs)) {
					$data['net_price'] = $data['price'] * ((fusion_get_settings('eshop_vat')/100)+1); // 40% increase is 1.(40/100) = 1.4 * price = total
					$data['shipping'] = '';
					if (fusion_get_settings('eshop_freeshipsum')>0) {
						$data['shipping'] = ($data['net_price'] > fusion_get_settings('eshop_freeshipsum')) ? $locale['ESHP027']." ".$locale['ESHP028'] : $locale['ESHP025']."  ".$locale['ESHP026']." ".fusion_get_settings('eshop_freeshipsum')." ".fusion_get_settings('eshop_currency');
					}
					$data['version'] = $data['version'] ? $locale['ESHP007']." ".$data['version'] : '';
					$data['delivery'] = $data['delivery'] && $data['instock'] <=0 ?  $locale['ESHP012']." ".nl2br($data['delivery']) : '';
					$data['stock_status'] = '';
					if ($data['stock'] == 1) {
						$data['stock_status'] .= $locale['ESHP008'].": ";
						if ($data['instock'] >= 1) {
							$data['stock_status'] .= ($data['instock'] >= 10) ? $locale['ESHP009'] : $locale['ESHP010'];
							$data['stock_status'] .= " ".number_format($data['instock']);
						} else {
							$data['stock_status'] .= $locale['ESHP011'];
						}
					}

					$data['category_title'] = isnum($data['category_title']) ? "Front Page" : $data['category_title'];
					$data['category_link'] = isnum($data['category_title']) ? BASEDIR."eshop.php" : BASEDIR."category=".$data['cid'];
					$data['link'] = BASEDIR."eshop.php?product=".$data['id'];
					if ($data['thumb']) $data['thumb'] = self::picExist(BASEDIR."eshop/pictures/thumb/".$data['thumb']);
					if ($data['picture']) $data['picture'] = self::picExist(BASEDIR."eshop/pictures/".$data['picture']);

					$info['item'][$data['id']] = $data;
					$this->info['title'] = $data['title'];
					// push for title and meta
					$this->info['category_title'] = $data['category_title'];
					$this->info['category_link'] = BASEDIR."eshop.php?category=".$data['cid'];
					$this->info['product_title'] = $data['title'];
					$this->info['product_link'] = BASEDIR."eshop.php?product=".$data['id'];
					$this->info['keywords'] = $data['keywords'];

					return $info;
				}
			}
		} elseif ($_GET['category']) {
			// on category page
			$sql = "i.cid='".intval($_GET['category'])."'";
			if (isset($this->info['category'][$_GET['category']])) {
				// extract the keys of child from hierarchy tree
				$child_id = array_keys($this->info['category'][$_GET['category']]);
				$sql = "i.cid in (".intval($_GET['category']).implode(',',$child_id).")";
			}
			$result = dbquery("SELECT i.id, i.cid, i.title, i.thumb, i.price, i.picture, i.xprice, i.keywords, i.product_languages, cat.title as category_title
			FROM ".DB_ESHOP." i
			INNER JOIN ".DB_ESHOP_CATS." cat on i.cid = cat.cid
			WHERE ".$sql." AND active = '1' AND ".groupaccess('i.access')."
			ORDER BY dateadded DESC LIMIT ".$_GET['rowstart'].", ".fusion_get_settings('eshop_noppf')."
			");
		} else {
			// on main page
			$result = dbquery("SELECT id, cid, title, thumb, price, picture, xprice, keywords, product_languages, if(cid=0, 0, 1) as category_title FROM ".DB_ESHOP." WHERE active = '1' AND ".groupaccess('access')." ORDER BY dateadded DESC LIMIT ".$_GET['rowstart'].", ".fusion_get_settings('eshop_noppf')."");
		}
		if (dbrows($result)>0) {
			if (multilang_table("ES")) {
				while ($data = dbarray($result)) {
					$es_langs = explode('.', $data['product_languages']);
					if (in_array(LANGUAGE, $es_langs)) {
						$data['category_title'] = isnum($data['category_title']) ? "Front Page" : $data['category_title'];
						$data['category_link'] = isnum($data['category_title']) ? BASEDIR."eshop.php" : BASEDIR."category=".$data['cid'];
						$data['link'] = BASEDIR."eshop.php?product=".$data['id'];
						if ($data['thumb']) $data['thumb'] = BASEDIR."eshop/pictures/thumb/".$data['thumb'];
						if ($data['picture']) $data['picture'] = BASEDIR."eshop/pictures/".$data['picture'];
						$info['item'][$data['id']] = $data;
					}
				}
			} else {
				while ($data = dbarray($result)) {
					$info['item'][$data['id']] = $data;
				}
			}
		} else {
			$info['error'] = 'No products added'; //$locale[''];
		}

		$info['pagenav'] = ($this->max_rows > fusion_get_settings('eshop_noppf')) ? self::makeeshoppagenav($_GET['rowstart'],fusion_get_settings('eshop_noppf'),$this->max_rows,3,FUSION_SELF."?".(isset($_COOKIE['Filter']) ? "FilterSelect=".$_COOKIE['Filter']."&amp;" : "" )."") : '';
		return $info;
	}

	static function get_featureds() {

		$result= dbquery("SELECT ter.* FROM
		".DB_ESHOP." ter
		LEFT JOIN ".DB_ESHOP_FEATITEMS." titm ON ter.id=titm.featitem_item
		WHERE featitem_cid = '".(isset($_REQUEST['category']) ? $_REQUEST['category'] : "0")."' ORDER BY featitem_order");
		$rows = dbrows($result);

	}

}

