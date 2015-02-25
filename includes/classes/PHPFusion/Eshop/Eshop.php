<?php

namespace PHPFusion\Eshop;

use PHPFusion\Eshop\Admin\Coupons;
use PHPFusion\Eshop\Admin\Customers;
use PHPFusion\Eshop\Admin\Payments;
use PHPFusion\Eshop\Admin\Shipping;

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

	public $coupon_info = array(
		'coupon_code' => ''
		);

	// pricing calculation
	private $total_gross = 0;
	private $total_subtotal = 0;
	private $item = array();
	private $item_count = 0;
	private $total_weight = 0;
	private $max_rows = 0;
	private $banner_path = '';

	public function __construct() {
		$this->banner_path = BASEDIR."eshop/pictures/banners/";
		$_GET['category'] = isset($_GET['category']) && isnum($_GET['category']) ?  $_GET['category'] : 0;
		$_GET['product'] = isset($_GET['product']) && isnum($_GET['product']) ? $_GET['product'] : 0;
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $this->max_rows ? : 0;
		$_GET['FilterSelect'] = isset($_POST['FilterSelect']) && isnum($_POST['FilterSelect']) ? $_POST['FilterSelect'] : 0;
		$this->info['category_index'] = dbquery_tree(DB_ESHOP_CATS, 'cid', 'parentid');
		$this->info['category'] = dbquery_tree_full(DB_ESHOP_CATS, 'cid', 'parentid');
		self::adjust_cart();
		// filter the rubbish each run
		dbquery("DELETE FROM ".DB_ESHOP_CART." WHERE cadded < ".time()."-2592180");
	}

	/* static Session ID generator */
	protected static function get_token() {
		global $userdata; // use phpfusion token.
		//$url = fusion_get_settings('siteurl');
		//return \defender::token($url.'-eshop');
		$user_id = \defender::set_sessionUserID();
		$identifier = 'eshop';
		$algo = fusion_get_settings('password_algorithm');
		$url = fusion_get_settings('siteurl');
		$key = $user_id.$identifier.$url.SECRET_KEY;
		$salt = md5(isset($userdata['user_salt']) ? $userdata['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);
		// generate a new token and store it
		$token = $user_id.".".$identifier.".".hash_hmac($algo, $key, $salt);
		return $token;
		//*/
	}
	/* Sets eShop Session */
	protected static function set_session($field_name, $value) {
		// id only. why need itme to hash.
		$token = self::get_token();
		$_SESSION[fusion_get_settings('siteurl')][$token]['eshop'][$field_name] = $value;
		return true;
	}
	// set system update when things change.
	public static function refresh_session($is_checkout = false) {
		$info = self::get_checkout_info();
		if (!$is_checkout) {
			$excluded_field = array('customer', 'customer_message');
		} else {
			// if this is checkout bill adjustments only.
			$excluded_field = array(
				'customer',
				'customer_message',
				'shipping_message',
				'shipping_method',
				'total_shipping',
				'payment_method',
			);
		}
		foreach($info as $field_name => $value) {
			if (!in_array($field_name, $excluded_field)) {
				self::unset_session($field_name);
			}
		}
	}
	protected static function restart() {
		$token = self::get_token();
		unset($_SESSION[fusion_get_settings('siteurl')][$token]['eshop']);
	}

	/* Returns the current Session Value */
	protected static function get($field_name = false, $admin = FALSE) {
		$value =  null;
		$token = self::get_token();
		if ($admin && isset($_SESSION[fusion_get_settings('siteurl')][$token]['eshop'][$field_name])) {
			return (string) $_SESSION[fusion_get_settings('siteurl')][$token]['eshop'][$field_name];
		}
		if (!isset($_COOKIE[COOKIE_PREFIX.'eshop'])) {
			$settings = fusion_get_settings();
			$fusion_domain = (strstr($settings['site_host'], "www.") ? substr($settings['site_host'], 3) : $settings['site_host']);
			$cookie_domain = $settings['site_host'] != 'localhost' ? $fusion_domain : FALSE;
			$cookie_path = $settings['site_path'];
			$time_expiry = time()+1209600;
			\Authenticate::_setCookie(COOKIE_PREFIX.'eshop', $token, $time_expiry, $cookie_path, $cookie_domain, FALSE, TRUE);
		}

		$token_data = explode(".", stripinput($token));
		$salt = md5(isset($userdata['user_salt']) ? $userdata['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);
		$identifier = 'eshop';
		$algo = fusion_get_settings('password_algorithm');
		list($user_id, $token_time, $hash) = $token_data;
		if ($hash = hash_hmac($algo, $user_id.$identifier.SECRET_KEY, $salt)) {
			if ($field_name && isset($_SESSION[fusion_get_settings('siteurl')][$token]['eshop'][$field_name])) {
				$value = $_SESSION[fusion_get_settings('siteurl')][$token]['eshop'][$field_name];
			} elseif (!$field_name && isset($_SESSION[fusion_get_settings('siteurl')][$token]['eshop'])) {
				$value = $_SESSION[fusion_get_settings('siteurl')][$token]['eshop'];
			}
		}
		return $value;
	}
	protected static function unset_session($field_name) {
		$token = self::get_token();
		unset($_SESSION[fusion_get_settings('siteurl')][$token]['eshop'][$field_name]);
		return true;
	}


	// checkout data
	public function __construct_Checkout() {
		$item = array();
		$result = dbquery("SELECT c.*, e.dync, e.icolor, (c.cprice*c.cqty) as total_price
				FROM ".DB_ESHOP_CART." c
				INNER JOIN ".DB_ESHOP." e on c.prid=e.id
				WHERE puid='".\defender::set_sessionUserID()."' ORDER BY cadded asc");
		if (dbrows($result)>0) {
			$vat_rate = fusion_get_settings('eshop_vat') > 0 ? intval(fusion_get_settings('eshop_vat'))/100 : intval(0);
			while ($data = dbarray($result)) {
				$data['cimage'] = $data['cimage'] ? self::picExist(SHOP."pictures/".$data['cimage']) : self::picExist('fake.png');
				// Commented Change ( SEE ABOVE )
				// this is price inclusive of tax or not for visual purposes only. It is not used against calculations of tax vs coupons.
				$data['item_price'] = fusion_get_settings('eshop_vat_default') ? $data['cprice']+($data['cprice'] * ($vat_rate)) : $data['cprice']; // unit
				$data['item_subtotal'] = $data['item_price'] * $data['cqty'];
				$item[$data['tid']] = $data;
				$this->item_count = $this->item_count+$data['cqty'];
				$this->total_weight = $this->total_weight+$data['cweight'];
				$this->total_gross = $this->total_gross+$data['total_price'];
				$this->total_subtotal = $this->total_subtotal+$data['item_subtotal'];
			}
			$this->item = $item;
		}

		//print_p(self::get());
		self::set_checkout_items(); // build checkout items into session
		self::set_vat_rate(); // set VAT into session
		self::set_shipping_rate();
		self::set_coupon_rate();
		self::set_payment_rate();
		self::set_net_price();
		self::set_customerDB();
		self::set_customer();
		self::set_customer_message();
		//self::restart();
	}
	protected static function adjust_cart() {
		if (isset($_POST['p-submit-qty']) && isset($_POST['utid']) && isnum($_POST['utid']) && isset($_POST['qty']) && isnum($_POST['qty'])) {
			// ok now check product exist.
			$tid = intval($_POST['utid']);
			$qty = intval($_POST['qty']);
			$result = dbcount("(tid)", DB_ESHOP_CART, "tid='".$tid."'");
			if ($result) {
				$result = dbquery("UPDATE ".DB_ESHOP_CART." SET cqty='".$qty."' WHERE tid='".$tid."'");
				if ($result) {
					self::refresh_session(); // clear everything.
					redirect(BASEDIR."eshop.php?checkout");
				} else {
					notify('Cart Not Updated', 'An error is found and the cart is not updated.');
				}
			} else {
				notify('Invalid Product Code. Cart Not Updated', 'An error is found in the product ID and the cart is not updated.');
			}
		}
	}

	// we start here.
	protected function set_customer() {
		$user_id = \defender::set_sessionUserID();
		$customer = Customers::get_customerData($user_id); // binds the above
		self::set_session('customer', $customer);
	}

	// Cart Polling.
	protected function set_checkout_items() {
		global $locale;
		// set to poll on each refresh unless a coupon code have been set.

		self::set_session('items', $this->item);
		self::set_session('item_count', $this->item_count);
		self::set_session('total_weight', $this->total_weight);
		self::set_session('total_gross', $this->total_gross);
		self::set_session('total_gross_taxed', $this->total_subtotal); // data
		// this is for the cashier usage.
		self::set_session('current_subtotal', $this->total_subtotal);

		$subtotal = "<span class='strong'>".$locale['ESHPCHK128']."</span>
		<span class='strong pull-right ".(self::get('coupon_code') ? 'required' : '')."'>".fusion_get_settings('eshop_currency').number_format($this->total_subtotal,2)."</span>";
		self::set_session('subtotal', $subtotal);
		self::update_coupon();
	}

	// Item Form
	public static function display_item_form($display=false) {
		$item = self::get('items');
		// secure form. update the whole thing as MVC.
		$html = "<table class='table table-responsive'>";
		$html .= "<tr>\n";
		$html .= "<th class='col-xs-5 col-sm-5'>Product</th>\n";
		$html .= "<th class='col-xs-2 col-sm-2'>Quantity</th>\n";
		$html .= "<th>Unit Price</th>\n";
		$html .= "<th>Total</th>\n";
		$html .= $display ? '' : "<th>Options</th>\n";
		$html .= "</tr>\n";
		if (!empty($item)) {
			foreach($item as $prid => $data) {
				$specs = \PHPFusion\Eshop\Eshop::get_productSpecs($data['dync'], $data['cdyn']);
				$color = \PHPFusion\Eshop\Eshop::get_productColor($data['cclr']);
				$html .= openform('updateqty', "updateqty-".$data['tid'], 'post', BASEDIR."eshop.php?checkout", array('downtime'=>1, 'notice'=>0));
				$html .= "<tr>\n";
				$html .= "<td class='col-xs-5 col-sm-5'>\n";
				$html .= "<div class='pull-left m-r-10' style='width:70px'>\n";
				$html .= "<img class='img-responsive' src='".$data['cimage']."' />";
				$html .= "</div>\n";
				$html .= "<div class='overflow-hide'>\n";
				$html .= "<a href='".BASEDIR."eshop.php?product=".$data['prid']."'>".$data['citem']."</a>\n";
				if ($specs) $html .= "<div class='display-block text-smaller'><span class='strong'>".$data['cdynt']."</span> - $specs</div>\n";
				if ($color) $html .= "<div class='display-block text-smaller'><span class='strong'>Color</span> - $color</span>\n";
				$html .= "</div>\n";
				$html .= "</td>\n";
				$html .= "<td>\n";
				$html .= $display ? $data['cqty'] : form_text('', 'qty', 'qty', $data['cqty'], array('append_button'=>1, 'append_form_value'=>$data['tid'], 'append_value'=>"<i class='fa fa-repeat m-t-5 m-b-0'></i>", 'append_type'=>'submit'));
				$html .= form_hidden('', 'utid', 'utid', $data['tid']);
				$html .= "</td>\n";
				$html .= "<td>".fusion_get_settings('eshop_currency').number_format($data['item_price'], 2)."</td>\n";
				$html .= "<td>".fusion_get_settings('eshop_currency').number_format($data['item_subtotal'], 2)."</td>\n";
				$html .= $display ? '' : "<td>".form_button('Remove', 'remove', 'remove', 'remove', array('class'=>'btn-danger btn-sm'))."</td>\n";
				$html .= "</tr>\n";
				$html .= closeform();
			}
		} else {
			$html .= "<tr><td colspan='5'><div class='alert alert-info strong text-center m-t-20'>There are no products in your cart.</div></td></tr>\n";
		}
		$html .= "</table>\n";
		return $html;
	}

	/* Calculates Grand Total */
	private function set_net_price() {
		$net_price = self::get('nett_gross') + self::get('total_shipping') + self::get('total_surcharge');
		self::set_session('net_price', $net_price);
		$gt = "<span class='strong'>Grand Total:</span><span class='strong pull-right'>+ ".fusion_get_settings('eshop_currency').number_format($net_price,2)."</span>\n";
		self::set_session('grandtotal', $gt);
		self::set_session('datestamp', time()); // put here to refresh this value so we know when customer was last seen.
	}

	/* VAT */
	private function set_vat_rate() {
		global $locale;
		$price_include_vat = fusion_get_settings('eshop_vat_default');
		$total_subtotal = self::get('current_subtotal');
		$vat = fusion_get_settings('eshop_vat');
		// toggle 0 vat or show vat only
		if ($price_include_vat) {
			$total_vat = 0; // since total gross already include vat calculations
		} else {
			$vat_rate = $vat >0 ? $total_subtotal*($vat/100) : 0;
			$total_vat =  $vat_rate;
		}
		$nett_gross = $total_vat + $total_subtotal;
		$vat_output = "<span class='strong'>".$locale['ESHPCHK130']." (".fusion_get_settings('eshop_vat')."% ".($price_include_vat ? $locale['ESHPCHK160'] : $locale['ESHPCHK161']).") :</span><span class='pull-right'>+ ".fusion_get_settings('eshop_currency').number_format($total_vat,2)."</span>";
		$nett_gross_output = "<span class='strong'>".$locale['ESHPCHK134']."</span><span class='strong pull-right'>".fusion_get_settings('eshop_currency').number_format($nett_gross,2)."</span>";
		self::set_session('total_vat', $total_vat);
		self::set_session('nett_gross', $nett_gross); // inclusive VAT
		self::set_session('vat', $vat_output);
		self::set_session('nett', $nett_gross_output);
	}

	/* Checkout check */
	public function saveorder() {
	global $locale, $settings;
		if ($settings['site_seo'] == "1") $settings['siteurl'] = str_replace("../", "", $settings['siteurl']);
		/**
		 * Required fields errors
		 * Sets validation here and push back a step if error happens
		 */
		$item_count = self::get('item_count');
		$item_form = self::display_item_form(true);
		$customerData = self::get('customer'); // customer information array
		$items = self::get('items'); // customer ordered items
		$payment_method = self::get('payment_method'); // payment method
		$shipping_method = self::get('shipping_method'); // shipping method
		$coupon_value = self::get('coupon_value'); // get the coupon values
		$total_gross = self::get('total_gross');
		$total_vat = self::get('total_vat');
		$total_price = self::get('net_price');
		$customer_message = self::get('customer_message');

		$destination = $locale['na'];
		$initial_shipping_cost = 0;
		$weight_cost = 0;
		$bt_payment_info = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_PAYMENTS." WHERE active='1' AND pid='".$payment_method."' ORDER BY pid ASC"));
		if ($shipping_method) {
			$bt_ship_info = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE active='1' AND sid='".$shipping_method."' ORDER BY cid,sid ASC"));
			$destination_locale = Shipping::get_destOpts();
			$destination_locale = $destination_locale[$bt_ship_info['destination']];
			$destination = $bt_ship_info['method']."<br />".$bt_ship_info['dtime']." - ".$destination_locale;
			$initial_shipping_cost = $bt_ship_info['initialcost'];
			$weight_cost = $bt_ship_info['weightcost'];
		}

		$total_weight = self::get('total_weight');
		$total_shipping = self::get('total_shipping');
		$total_surcharge = self::get('total_surcharge');

		$error = array(
			'customer_id_error' => empty($customerData['cuid']) ? 1 : 0, // no id.
			'customer_name_error' => empty($customerData['cfirstname']) || empty($customerData['clastname']) ? 1 : 0, // no name
			'customer_email_error' => empty($customerData['cemail']) ? 1 : 0, // no email
			'customer_dob_error' => empty($customerData['cdob']) ? 1 : 0, // no dob
			'customer_country_error' => empty($customerData['ccountry']) ? 1 : 0, // no country
			'customer_region_error' => empty($customerData['cregion']) ? 1 : 0, // no region
			'customer_city_error' => empty($customerData['ccity']) ? 1 : 0, // no city
			'customer_street_error' => empty($customerData['caddress']) ? 1 : 0, // no customer address
			'customer_postcode_error' => empty($customerData['cpostcode']) ? 1 : 0, // no postcode
			'customer_contact_error' => empty($customerData['cphone']) ? 1 : 0, // no contact number
			'items_error' => empty($items) ? 1 : 0, // no items or cart empty
			'payment_error' => dbcount("('pid')", DB_ESHOP_PAYMENTS) && empty($payment_method) ? 1 : 0, // have payment options but ommitted
			'shipping_error' => dbcount("('sid')", DB_ESHOP_SHIPPINGITEMS) && empty($shipping_method) ? 1 : 0, // have ship items options but omitted
		);
		foreach($error as $key => $value) {
			if ($value == 1) redirect(BASEDIR."eshop.php?checkout&amp;error=$key");
		}




		$responsive_bill_template = "
		<h2>INVOICE</h2>
			<div class='row'>
				<div class='col-xs-12 col-sm-6'>
					<div class='well'>
						<p class='strong'>".$locale['ESHPF112']."</p>
						<table class='table table-responsive'>
							<tr><td>".$locale['ESHPF113']."</td><td>".$customerData['cfirstname']."</td></tr>
							<tr><td>".$locale['ESHPF114']."</td><td>".$customerData['clastname']."</td></tr>
							<tr><td>".$locale['ESHPF115']."</td><td>".date('%d-%M-%Y', $customerData['cdob'])."</td></tr>
							<tr><td>".$locale['ESHPF116']."</td><td>".$customerData['ccountry']."</td></tr>
							<tr><td>".$locale['ESHPF117']."</td><td>".$customerData['cregion']."</td></tr>
							<tr><td>".$locale['ESHPF118']."</td><td>".$customerData['ccity']."</td></tr>
							<tr><td>".$locale['ESHPF119']."</td><td>".$customerData['caddress']."<br/>".$customerData['caddress2']."</td></tr>
							<tr><td>".$locale['ESHPF121']."</td><td>".$customerData['cpostcode']."</td></tr>
							<tr><td>".$locale['ESHPF122']."</td><td>".$customerData['cphone']."</td></tr>
							<tr><td>".$locale['ESHPF123']."</td><td>".$customerData['cfax']."</td></tr>
							<tr><td>".$locale['ESHPF124']."</td><td>".$customerData['cemail']."</td></tr>
						</table>
						<p class='strong'>".$locale['ESHPF125']."</p>
						<span>".nl2br($customer_message)."</span>
					</div>
				</div>
				<div class='col-xs-12 col-sm-6'>
					<div class='well'>
					<p class='strong'>".$locale['ESHPF126']."</p>
					<table class='table table-responsive table-striped'>
						<tr>
							<td><img style='width:40px; height:40px;' src='".$settings['siteurl']."eshop/paymentimgs/".$bt_payment_info['image']."' border='0' alt='' /></td>
							<td align='left' width='55%'>".$bt_payment_info['method']."</td>
							<td align='left' width='25%'>".$locale['ESHPF127']." <br /> ".$bt_payment_info['surcharge']." ".$settings['eshop_currency']."</td>
						</tr>
					</table>
					<p class='strong'>".$locale['ESHPF128']."</p>
					<table class='table table-responsive table-striped'>
						<tr>
							<td width='60%'>".$destination."</td>
							<td width='20%'>".$locale['ESHPF129']."<br /> ".number_format($initial_shipping_cost,2)." ".$settings['eshop_currency']."</td>
							<td width='20%'>".$locale['ESHPF127']."/".$settings['eshop_weightscale']."<br />".number_format($weight_cost,2)." ".$settings['eshop_currency']."</td>
						</tr>
					</table>

					<p class='strong'>".$locale['ESHPF130']."</p>
					<table class='table table-responsive table-striped'>
						<tr>
							<td>
							".$locale['ESHPF131']." ".$item_count." ".$locale['ESHPF132']."".number_format($total_gross, 2)." ".$settings['eshop_currency']."<br />
							".$locale['ESHPF133']." ".$settings['eshop_vat']."% : ".number_format($total_vat, 2)." ".$settings['eshop_currency']."<br />
							".$locale['ESHPCHK176']." ".$coupon_value." <br />
							".$locale['ESHPF134']." ".number_format($total_weight, 2)." ".$settings['eshop_weightscale']." <br />
							".$locale['ESHPF135']." ".$bt_payment_info['surcharge']." (".$total_surcharge." in total) ".$settings['eshop_currency']." <br />
							".$locale['ESHPCHK133']." ".$total_shipping." ".$settings['eshop_currency']." <br />
							".$locale['ESHPF137']." ".number_format($total_price, 2)." ".$settings['eshop_currency']."<br />
							</td>
						</tr>
					</table>
					</div>
				</div>
			</div>
			<div class='row'>
				<div class='col-xs-12 col-sm-12'>
					$item_form
				</div>
			</div>
		";

		echo $responsive_bill_template;

		//dbquery("UPDATE ".DB_ESHOP." SET sellcount=sellcount+".$data['cqty']." WHERE id = '".$data['prid']."'");
		//dbquery("UPDATE ".DB_ESHOP." SET instock=instock-".$data['cqty']." WHERE id = '".$data['prid']."'");



		// Product Description and Particulars will change overtime, so it is best to store full product information.
		$odata = array(
			'oid' => 0,
			'ouid' => $customerData['cuid'],
			'oname' => $customerData['cfirstname'].' '.$customerData['clastname'],
			'oemail' => $customerData['cemail'],
			'oitems' => serialize($items),
			'oorder' => '', // hmm can neglect
			'opaymethod' => $payment_method,
			'oshipmethod' => $shipping_method,
			'odiscount' => $coupon_value,
			'ovat' => $total_vat,
			'ototal' => $total_price,
			'omessage' => $customer_message,
			'oamessage' => '',
			'ocompleted' => 0,
			'opaid' => 0,
			'odate'=> time()
		);

		// ok. ordering snapshot. lets just do this.
	/* Calculation Codes
	$vat = $settings['eshop_vat'];
	if (isset($_POST['buynow'])) {
		$itemdata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP." WHERE id='".$_POST['id']."'"));
		$price = ($itemdata['xprice'] ? $itemdata['xprice'] : $itemdata['price']);
	} else {
		$price = $sum['totals'];
	}
	$vat = ($price/100)*$vat;
	if ($settings['eshop_vat_default'] == "0") {
		$totalincvat = $price+$vat;
	} else {
		$totalincvat = $price;
	}
	$shippingsurcharge = $shipping['weightcost'];
	$shippinginitial = $shipping['initialcost'];
	$shippingsurcharge = $shippingsurcharge*$weight['weight'];
	$shippingtotal = $shippingsurcharge+$shippinginitial;
	$paymentsurcharge = $payment['surcharge'];
	if (isset($_POST['cupon']) && $_POST['cupon'] !== $locale['ESHPCHK171']) {
		$cupons = stripinput($_POST['cupon']);
		if (iMEMBER) {
			$verifycupon = dbquery("SELECT * FROM ".DB_ESHOP_CUSTOMERS." WHERE ccupons LIKE '%.".$cupons."' LIMIT 0,1");
			if (!dbrows($verifycupon) != 0) {
				$cupon = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_COUPONS." WHERE cuid='".$cupons."' AND active = '1' AND (custart='0'||custart<=".time().") AND (cuend='0'||cuend>=".time().") LIMIT 0,1"));
				$cuponsum = dbarray(dbquery("SELECT sum(cprice*cqty) as totals FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' AND ccupons='1'"));
				$cuponexcluded = dbarray(dbquery("SELECT sum(cqty) as count FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' AND ccupons='0'"));
				$cupons = ".".$cupon['cuid']."";
				if ($cupon['cutype'] == "1") {
					if ($cupon['cuvalue'] > $cuponsum['totals']) {
						$discount = $locale['ESHPCHK177'];
						$cupons = "";
					} else {
						$discvalue = $cupon['cuvalue'];
						$discalc = $discvalue;
						$discount = "".number_format($discvalue)." ".$settings['eshop_currency']."";
					}
				} else if ($cupon['cutype'] == "0") {
					$discount = $cupon['cuvalue'];
					$dvat = $settings['eshop_vat'];
					$itemstocalc = $cuponsum['totals'];
					if ($settings['eshop_vat_default'] == "0") {
						$dvat = ($itemstocalc/100)*$dvat;
						$discalc = $itemstocalc+$dvat;
					} else {
						$discalc = $itemstocalc;
					}
					$discalc = ($discalc/100)*$discount;
					$discount = "".number_format($discalc)." ".$settings['eshop_currency']."";
				} else {
					$discount = $locale['ESHPCHK179'];
					$cupons = "";
				}
			}
		}
	}


	$order .= "<fieldset style='width:100%;padding:2px;'><legend style='width:90% !important;'>&nbsp; ".$locale['ESHPF138']." &nbsp;</legend>";
	if (isset($_POST['buynow'])) {
		$itemdata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP." WHERE id='".$_POST['id']."'"));
		$order .= "<table align='center' width='100%' cellpadding='0' cellspacing='0' class='eshptable' ><tr>
					<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPF139']."</b></td>
					<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPF140']."</b></td>
					<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPF141']."</b></td>
					<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPF142']."</b></td>
					<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPF143']."</b></td>
					<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPF144']."</b></td>
					</tr><tr>";
		$order .= "<td class='tbl' align='center' valign='middle' width='1%'>".($itemdata['artno'] !== '' ? "".$itemdata['artno']."" : "".$itemdata['id']."")."</td>
					<td class='tbl' align='center' valign='middle' width='1%'><a href='".$settings['siteurl']."eshop.php?product=".$itemdata['id']."'>".$itemdata['title']."</a></td>
					<td class='tbl' align='center' valign='middle' width='1%'></td>
					<td class='tbl' align='center' valign='middle' width='1%'>";
		if ($itemdata['dynf'] || $itemdata['dync']) {
			$order .= "".$itemdata['dynf']." : ".$itemdata['dync']."";
		}
		$order .= "</td><td class='tbl' align='center' valign='middle' width='1%'>".$itemdata['qty']."</td><td class='tbl' align='center' valign='middle' width='1%'>".$price."</td>";
		$order .= "</tr>\n</table>";
		dbquery("UPDATE ".DB_ESHOP." SET sellcount=sellcount+1 WHERE id = '".$_POST['id']."'");
	} else {
		$result = dbquery("SELECT * FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC");
		$counter = 0;
		$order .= "<table align='center' width='100%' cellpadding='0' cellspacing='0' class='eshptable' ><tr>
					<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPF139']."</b></td>
					<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPF140']."</b></td>
					<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPF141']."</b></td>
					<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPF142']."</b></td>
					<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPF143']."</b></td>
					<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPF144']."</b></td>
					</tr><tr>";
		while ($data = dbarray($result)) {
			if ($counter != 0 && ($counter%1 == 0)) $order .= "</tr>\n<tr>";
			$order .= "<td class='tbl' align='center' valign='middle' width='1%'>".($data['artno'] !== '' ? "".$data['artno']."" : "".$data['prid']."")."</td>
						<td class='tbl' align='center' valign='middle' width='1%'><a href='".$settings['siteurl']."eshop.php?product=".$data['prid']."'>".$data['citem']."</a></td>
						<td class='tbl' align='center' valign='middle' width='1%'>".getcolorname($data['cclr'])."</td>
						<td class='tbl' align='center' valign='middle' width='1%'>";
			if ($data['cdynt'] || $data['cdyn']) {
				$order .= "".$data['cdynt']." : ".$data['cdyn']."";
			}
			$order .= "</td><td class='tbl' align='center' valign='middle' width='1%'>".$data['cqty']."</td><td class='tbl' align='center' valign='middle' width='1%'>".$data['cprice']."</td>";
			//Let´s make a loop to add an item array for each quantity so we can adjust stock and sellcount accordingly when deleting orders.
			for ($q = 0; $q < $data['cqty']; $q++) {
				$itemlist .= ".".$data['prid']."";
			}

			$counter++;
		}
		$order .= "</tr>\n</table>";
	}
	$order .= "</fieldset></div>";

	//save the order in the orders database
	dbquery("INSERT INTO ".DB_ESHOP_ORDERS." VALUES('', '".$username."','".$firstname." ".$lastname."','".$itemlist."' ,'".addslash($order)."','".$email."','".$paymethod."','".$shipmethod."','".$discount."','".$vat."','".$totalcost."','".$message."','','','','".time()."')");
	//".($pdo_enabled == "1" ? "".$order."" : "".mysql_real_escape_string($order)."")."
	//We only store customer data from registred members.
	//Save and update the customer
	if (iMEMBER) {
		$ccheck = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_CUSTOMERS." WHERE cuid = '".$username."' "));
		//check if the user exist and update with new form values if they do
		if ($ccheck['cuid']) {
			$result = dbquery("UPDATE ".DB_ESHOP_CUSTOMERS." SET
cfirstname = '".$firstname."',
clastname = '".$lastname."',
cdob = '".$dob."',
ccountry_code = '".$country_code."',
cregion = '".$region."',
ccity = '".$city."',
caddress = '".$address."',
caddress2 = '".$address2."',
cpostcode = '".$postcode."',
cphone = '".$phone."',
cfax = '".$fax."',
cemail = '".$email."',
ccupons = '".$ccheck['ccupons']."".$cupons."'
WHERE cuid = '".$username."'");
		} else {
			$result = dbquery("INSERT INTO ".DB_ESHOP_CUSTOMERS." VALUES('".$username."', '".$firstname."', '".$lastname."' ,'".$dob."', '".$country_code."' , '".$region."', '".$city."' , '".$address."' , '".$address2."','".$postcode."','".$phone."','".$fax."','".$email."','.".$cupons."')");
		}
	} */

}

	/* Persistent now since construct function will fill in session values if blank */
	public static function get_checkout_info() {
		global $locale;
		$info = array(
			'items' => self::get('items'), // mvc
			'item_count' => self::get('item_count'), // mvc
			'total_gross_taxed' => self::get('total_gross_taxed'),
			'current_subtotal' => self::get('current_subtotal'),
			'subtotal' => self::get('subtotal'), // mvc
			'total_gross' => self::get('total_gross'),
			'coupon_code' => self::get('coupon_code'),
			'coupon_value' => self::get('coupon_value'),
			'coupon_message' => self::get('coupon_message'),
			'total_vat' => self::get('total_vat'),
			'vat' => self::get('vat'),
			'nett_gross' => self::get('nett_gross'),
			'nett' => self::get('nett'),
			'grandtotal' => self::get('grandtotal'),
			'total_weight' => self::get('total_weight'),
			'shipping' => self::get('shipping'),
			'total_shipping' => self::get('total_shipping'),
			'shipping_method' => self::get('shipping_method'),
			'shipping_message' => self::get('shipping_message'),
			'customer' => self::get('customer'),
			'total_surcharge' => self::get('total_surcharge'),
			'payment' => self::get('payment'),
			'payment_method' => self::get('payment_method'),
			'payment_message' => self::get('payment_message'),
			'customer_message' => self::get('customer_message'),
			'net_price' => self::get('net_price'),
			'item_form' => self::display_item_form(),
			'message_form' => self::display_message_form(),
			'customer_form' => self::display_customer_form(),
			'coupon_form' => self::display_coupon_form(),
			'shipping_form' => self::display_shipping_form(),
			'payment_form' => self::display_payment_form(),
			'agreement' => self::display_agreement(),
		);
		return $info;
	}

	/* Coupon form fields */
	protected static function update_coupon() {
		// get coupon value
		global $locale;
		$coupon_code = self::get('coupon_code');
		if ($coupon_code) {
			$coupon = Coupons::get_couponData($coupon_code);
			$current_subtotal = self::get('total_gross_taxed'); // this cannot be fluctuated. else we will screw ourselves over.
			$total_gross = self::get('total_gross');
			$vat_difference = fusion_get_settings('eshop_vat_default') ? number_format($current_subtotal -$total_gross,2) : 0;

			if ($coupon['cutype'] == 1) { // if price set at sum
				$coupon_value = intval($coupon['cuvalue']);
				$coupon_calculation = $coupon_value;
			} else {
				$coupon_value = intval($coupon['cuvalue']/100*$total_gross); // percent by default.
				$coupon_calculation = "$coupon_value (".$coupon['cuvalue']."% rebate)";
			}
			$coupon_message = "You have applied coupon code <strong>".$coupon_code."</strong> with a rebate value  of <strong>".fusion_get_settings('eshop_currency').$coupon_calculation."</strong>.";
			// calculation difference here
			if ($coupon_value > $total_gross) {
				$current_subtotal = intval(0)+$vat_difference; // there must be a minimum of tax incur here.
			} else {
				$current_subtotal = $total_gross-$coupon_value+$vat_difference;
			}
			// set coupon message
			self::set_session('coupon_message', $coupon_message);
			self::set_session('coupon_value', $coupon_value);
			self::set_session('current_subtotal', $current_subtotal);

			$subtotal_message = "<span class='strong'>".$locale['ESHPCHK128']." (".$locale['ESHPCHK176'].")</span>
			<span class='strong pull-right required'>".fusion_get_settings('eshop_currency').number_format($current_subtotal,2)."</span>";
			self::set_session('subtotal', $subtotal_message);
		}
	}

	private static function set_coupon_rate() {
		global $defender;
		if (isset($_POST['apply_coupon'])) {
			$coupon_code = isset($_POST['coupon_code']) ? form_sanitizer($_POST['coupon_code'], '', 'coupon_code') : '';
			if ($coupon_code && Coupons::verify_coupon($coupon_code)) {
				if (Coupons::verify_coupon_usage(\defender::set_sessionUserID(), $coupon_code)) {
					$defender->stop();
					$defender->addNotice('You have already used this coupon.');
				} else {
					self::set_session('coupon_code', $coupon_code);
					self::update_coupon();
					// will affect gross, and vat and surcharge, grandtotal -  update all.
					self::set_vat_rate();
					self::update_payment();
					redirect(BASEDIR."eshop.php?checkout");
				}
			} else {
				print_p('THe Coupon Code is not valid');
				$defender->stop();
				$defender->addNotice('The coupon code is not valid.');
			}
		}
	}
	public static function display_coupon_form() {
		global $locale;
		$html = '';
		if (fusion_get_settings('eshop_coupons')) {

			if (self::get('coupon_message')) {
				$html .= "<div id='coupon-text' class='text-center'>\n";
				$html .= form_button('Use Another Coupon', 'use_coupon', 'use_coupon', '', array('class'=>'btn-sm btn-info pull-right m-b-10'));
				$html .= "<span>You have applied a coupon on this order.</span>\n";
				$html .= "</div>\n";
				add_to_jquery("
				$('#use_coupon').bind('click', function(e) {
					$('#coupon_container').show();
					$('coupon-text').hide();
				});
				");
			}
			$html .= "<div id='coupon_container' class='m-t-20' ".(self::get('coupon_message') ? 'style="display:none;"' : "")." >\n";
			$html .= openform('coupon_form', 'coupon_form', 'post', BASEDIR."eshop.php?checkout", array('downtime'=>1, 'notice'=>0));
			$html .= form_text($locale['ESHPCHK171'], 'coupon_code', 'coupon_code', '', array('placeholder'=>$locale['ESHPCHK171'], 'inline'=>1));
			$html .= form_button($locale['ESHPCHK172'], 'apply_coupon', 'apply_coupon', $locale['ESHPCHK172'], array('class'=>'btn-primary'));
			$html .= closeform();
			$html .= "</div>\n";
		} else {
			$html = "<div class='alert alert-warning'>Coupon discounts disabled</div>\n";
		}
		return $html;
	}

	/* Customer form fields */
	protected static function set_customerDB() {
		if (isset($_POST['save_customer'])) {
			global $userdata;
			$customer_info['cuid'] = intval($userdata['user_id']);
			$customer_info['cemail'] = isset($_POST['cemail']) ? form_sanitizer($_POST['cemail'], '', 'cemail') : '';
			$customer_info['cdob'] = isset($_POST['cdob']) ? form_sanitizer($_POST['cdob'], '', 'cdob') : '';
			$customer_info['cname'] = implode('|', $_POST['cname']); // backdoor to traverse back to dynamic
			$name = isset($_POST['cname']) ? form_sanitizer($_POST['cname'], '', 'cname') : '';
			if (!empty($name)) {
				$name = explode('|', $name);
				$customer_info['cfirstname'] = $name[0];
				$customer_info['clastname'] = $name[1];
			}
			// this goes back to form.
			$customer_info['caddress'] = implode('|', $_POST['caddress']);
			$address = isset($_POST['caddress']) ? form_sanitizer($_POST['caddress'], '', 'caddress') : '';
			if (!empty($address)) {
				$address = explode('|', $address);
				// this go into sql only
				$customer_info['caddress'] = $address[0];
				$customer_info['caddress2'] = $address[1];
				$customer_info['ccountry'] = $address[2];
				$customer_info['cregion'] = $address[3];
				$customer_info['ccity'] = $address[4];
				$customer_info['cpostcode'] = $address[5];
			}
			$customer_info['cphone'] = isset($_POST['cphone']) ? form_sanitizer($_POST['cphone'], '', 'cphone') : '';
			$customer_info['cfax'] = isset($_POST['cfax']) ? form_sanitizer($_POST['cfax'], '', 'cfax') : '';
			// check this part
			$customer_info['ccupons'] = isset($_POST['ccupons']) ? form_sanitizer($_POST['ccupons'], '', 'ccupons') : '';

			if (Customers::verify_customer($customer_info['cuid'])) {
				dbquery_insert(DB_ESHOP_CUSTOMERS, $customer_info, 'update', array('no_unique'=>1, 'primary_key'=>'cuid'));
				self::set_session('customer', $customer_info);
				if (!defined('FUSION_NULL')) redirect(BASEDIR."eshop.php?checkout");
			} else {
				dbquery_insert(DB_ESHOP_CUSTOMERS, $customer_info, 'save',  array('no_unique'=>1, 'primary_key'=>'cuid'));
				self::set_session('customer', $customer_info);
				if (!defined('FUSION_NULL')) redirect(BASEDIR."eshop.php?checkout");
			}
		}
	}
	public static function display_customer_form() {
		global $locale;
		$customer_info = self::get('customer');
		$html = "<div class='m-t-20'>\n";
		$html .= openform('customerform', 'customerform', 'post', BASEDIR."eshop.php?checkout", array('downtime'=>1, 'notice'=>0));
		$customer_name[] = $customer_info['cfirstname'];
		$customer_name[] = $customer_info['clastname'];
		$customer_name = implode('|', $customer_name);
		$html .= form_name('Customer Name', 'cname', 'cname', $customer_name, array('required'=>1, 'inline'=>1));
		$html .= form_text($locale['ESHPCHK115'], 'cemail', 'cemail', $customer_info['cemail'], array('inline'=>1, 'required'=>1, 'email'=>1));
		$html .= form_datepicker($locale['ESHPCHK105'], 'cdob', 'cdob', $customer_info['cdob'], array('inline'=>1, 'required'=>1));
		$customer_address[] = $customer_info['caddress']; // use this as backdoor.
		$customer_address[] = $customer_info['caddress2'];
		$customer_address[] = $customer_info['ccountry'];
		$customer_address[] = $customer_info['cregion'];
		$customer_address[] = $customer_info['ccity'];
		$customer_address[] = $customer_info['cpostcode'];
		$customer_address = implode('|', $customer_address);
		$html .= form_address($locale['ESHPCHK106'], 'caddress', 'caddress', $customer_address, array('required'=>1, 'inline'=>1));
		$html .= form_text($locale['ESHPCHK113'], 'cphone', 'cphone', $customer_info['cphone'], array('required'=>1, 'inline'=>1, 'number'=>1));
		$html .= form_text($locale['ESHPCHK114'], 'cfax', 'cfax', $customer_info['cfax'], array('inline'=>1, 'number'=>1)); // this not compulsory
		$html .= form_hidden('', 'cuid', 'cuid', $customer_info['cuid']);
		$html .= form_button($locale['save'], 'save_customer', 'save_customer', $locale['save'], array('class'=>'btn-primary'));
		$html .= closeform();
		$html .= "</div>\n";
		return $html;
	}

	/* Shipping form fields */
	protected static function set_shipping_rate() {
		global $locale;
		if (isset($_POST['save_shipping']) && isset($_POST['product_delivery']) && isnum($_POST['product_delivery'])) {
			if (Shipping::verify_itenary($_POST['product_delivery'])) {
				$free_shipping =  (fusion_get_settings('eshop_freeshipsum') > 0 && fusion_get_settings('eshop_freeshipsum') <= self::get('current_subtotal')) ? 1 : 0;
				$si = Shipping::get_itenary($_POST['product_delivery']);
				$ci = Shipping::get_shippingco($si['cid']);
				$ship_cost = intval($si['initialcost']) + ($si['weightcost'] * self::get('total_weight'));
				$s_message = "You have added ".$ci['title']." - ".$si['method']." into this order.";
				$shipping = "<span class='strong'>".$locale['ESHPCHK133']." ".($free_shipping ? "- ".$locale['ESHPCHK188'] : '')."</span>
					<span class='strong pull-right'>+ ".fusion_get_settings('eshop_currency').number_format($ship_cost,2)."</span>";
				$result = self::set_session('shipping_method', $_POST['product_delivery']);
				if ($result) $result = self::set_session('total_shipping', $ship_cost);
				if ($result) $result = self::set_session('shipping_message', $s_message);
				if ($result) $result = self::set_session('shipping', $shipping);
				self::update_payment();
				if ($result) redirect(BASEDIR."eshop.php?checkout");
			}
		}
	}
	public static function display_shipping_form() {
		global $locale;
		$free_shipping =  (fusion_get_settings('eshop_freeshipsum') > 0 && fusion_get_settings('eshop_freeshipsum') <= self::get('current_subtotal')) ? 1 : 0;
		$html = "<div class='display-inline-block text-smaller m-b-10'><span class='required'>**</span> ".$locale['ESHPCHK126']."</div>\n";
		$total_weight = self::get('total_weight');
		$list = array();
		$result = dbquery("SELECT s.*, cat.title, (s.initialcost + (s.weightcost * ".intval($total_weight).")) as delivery_cost
		FROM ".DB_ESHOP_SHIPPINGITEMS." s
		LEFT JOIN ".DB_ESHOP_SHIPPINGCATS." cat on (s.cid=cat.cid)
		WHERE weightmin <='".$total_weight."' AND weightmax >= '".$total_weight."'
		and s.active='1' ORDER BY s.dtime ASC, s.destination ASC, cat.title ASC");
		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				$list[$data['destination']][$data['sid']] = $data;
			}
		}
		if (!empty($list)) {
			$dest_opts = Shipping::get_destOpts();
			$html .= openform('shippingform', 'shippingform', 'post', BASEDIR."eshop.php?checkout", array('downtime'=>1, 'notice'=>0));
			foreach($list as $destination => $data) {
				$html .= "<ul class='list-group'>\n";
				$html .= "<li class='strong m-b-10'>".$dest_opts[$destination]."</li>\n";
				foreach($data as $sid => $_data) {
					$html .= "<li class='list-group-item'>
					<div class='m-r-10 pull-left' style='width:3%;'>
					<input id='".$_data['sid']."-choice' type='radio' name='product_delivery' value='".$_data['sid']."'  ".(self::get('shipping_method') == $sid ? 'checked' : '')." />
					</div>
					<label style='width:97%' class='overflow-hide row text-normal text-smaller' for='".$_data['sid']."-choice'>
					<span class='col-xs-2'>".($free_shipping ? "<span class='required'>".$locale['ESHPCHK188']."</span>" : "+".fusion_get_settings('eshop_currency')." ".$_data['delivery_cost'])."</span>
					<span class='m-r-10 text-bigger strong'>".$_data['method']."</span>
					<span class='text-bigger'>Est. Delivery Time - ".$_data['dtime']." days</span>
					</label>
					</li>";
				}
			}
			$html .= "</ul>\n";
			$html .= form_button($locale['save'], 'save_shipping', 'save_shipping', $locale['save'], array('class'=>'btn-primary'));
			$html .= closeform();
		} else {
			$html .= "<div class='well'>".$locale['ESHPCHK125']."</div>\n";
		}
		return $html;
	}

	/* Payment form fields */
	protected static function update_payment() {
		global $locale;
		$payment_method = self::get('payment_method');
		if ($payment_method) {
			$pay = Payments::get_payment($payment_method);
			$base_cost = intval(self::get('nett_gross')+self::get('total_shipping'));
			$total_surcharge = $base_cost * ($pay['surcharge']/100);
			$s_message = "You have selected ".$pay['method']." as payment method.";
			$payment = "<span class='strong'>".$locale['ESHPPMTS102']."</span>
					<span class='strong pull-right'>".fusion_get_settings('eshop_currency').number_format($total_surcharge,2)."</span>";
			self::set_session('total_surcharge', $total_surcharge);
			self::set_session('payment_message', $s_message);
			self::set_session('payment', $payment);
		}
	}

	protected static function set_payment_rate() {
		if (isset($_POST['save_payment']) && isset($_POST['product_paymethod']) && isnum($_POST['product_paymethod'])) {
			self::set_session('payment_method', $_POST['product_paymethod']);
			if (Payments::verify_payment($_POST['product_paymethod'])) {
				self::update_coupon();
				self::update_payment();
				redirect(BASEDIR."eshop.php?checkout");
			}
		}
	}
	public static function display_payment_form() {
		global $locale;
		$html = '';
		$result = dbquery("SELECT * FROM ".DB_ESHOP_PAYMENTS." WHERE active='1' ORDER BY pid ASC");
		if (dbrows($result)>0) {
			$html .= openform('shippingform', 'shippingform', 'post', BASEDIR."eshop.php?checkout", array('downtime'=>1, 'notice'=>0));
			$html .= "<ul class='list-group'>";
			$html .= "<li class='strong m-b-10'>".$locale['ESHPCHK120']."</li>\n";
			while ($data = dbarray($result)) {
				$html .= "<li class='list-group-item'>
					<div class='m-r-10 pull-left' style='width:3%;'>
					<input id='".$data['pid']."-paychoice' type='radio' name='product_paymethod' value='".$data['pid']."'  ".(self::get('payment_method') == $data['pid'] ? 'checked' : '')." />
					</div>
					<label style='width:97%' class='overflow-hide row text-normal text-smaller' for='".$data['pid']."-paychoice'>
					<span class='pull-left m-r-10 col-xs-3'>".$locale['ESHPCHK121']."  +".fusion_get_settings('eshop_currency')." ".$data['surcharge']."</span>
					<span class='text-bigger pull-right'><img style='width:40px; height:40px;' src='".SHOP."paymentimgs/".$data['image']."' border='0' alt='' /></span>
					<div title='".$data['description']."' class='overflow-hide p-r-20 text-bigger strong'>".$data['method']." <br/><span class='text-normal'>".$data['description']."</span></div>
					</label>
					</li>";
			}
			$html .= "</ul>\n";
			$html .= form_button($locale['save'], 'save_payment', 'save_payment', $locale['save'], array('class'=>'btn-primary'));
			$html .= closeform();
		} else {
			$html .= "<div class='well'>".$locale['ESHPCHK122']."</div>\n";
		}
		$html .= '';
		return $html;
	}

	/* Customer Message fields */
	protected static function set_customer_message() {
		if (isset($_POST['save_message'])) {
			$message = form_sanitizer($_POST['message'], '', 'message');
			self::set_session('customer_message', $message);
			redirect(BASEDIR."eshop.php?checkout");
		}
	}

	public static function display_message_form() {
		global $locale;
		$html = openform('shippingform', 'shippingform', 'post', BASEDIR."eshop.php?checkout", array('downtime'=>1, 'notice'=>0));
		$html .= form_textarea($locale['ESHPCHK116'], 'message', 'message', self::get('customer_message'));
		$html .= form_button($locale['save'], 'save_message', 'save_message', $locale['save'], array('class'=>'btn-primary'));
		$html .= closeform();
		return $html;
	}

	public static function display_agreement() {
		global $locale;
		$html = "<span class='display-block m-b-10 strong'>".$locale['ESHPCHK117']."</span>";
		$html .= form_checkbox($locale['ESHPCHK119'], 'agreement', 'agreement', '', array("required"=>1));
		return $html;
	}

	// calculate the cart total sum
	public static function get_cart_total($puid) {
		if ($puid && dbcount("(puid)", DB_ESHOP_CART, "puid='".$puid."'")) {
			$result = dbquery("SELECT cprice, cqty FROM ".DB_ESHOP_CART." WHERE puid='".$puid."'");
			if (dbrows($result)>0) {
				$subtotal = 0;
				while ($data = dbarray($result)) {
					$subtotal = ($data['cprice'] * $data['cqty']) + $subtotal;
				}
				return number_format($subtotal, 2);
			}
		}
	}

	// calculate the cart total sum
	public static function get_cart_discountable_total($puid) {
		if ($puid && dbcount("(puid)", DB_ESHOP_CART, "puid='".$puid."'")) {
			$result = dbquery("SELECT cprice, cqty FROM ".DB_ESHOP_CART." WHERE puid='".$puid."' AND cupons='1'");
			if (dbrows($result)>0) {
				$subtotal = 0;
				while ($data = dbarray($result)) {
					$subtotal = ($data['cprice'] * $data['cqty']) + $subtotal;
				}
				return number_format($subtotal, 2);
			}
		}
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
			// in product page
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
					// set cupons enabled locale.
					$data['coupon_status'] = $data['cupons'] == 1 ? "Allow Coupons: Yes" : '';
					// set stock status locale.
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

