<?php

namespace PHPFusion\Eshop\Admin;
/**
 * Class eShop
 */
class Main {

	private $pages = array();

	public function __construct() {
		global $locale, $aidlink;
		// sanitized global vars
		if (isset($_GET['category']) && !isnum($_GET['category'])) die("Denied");
		if (isset($_GET['id']) && !isnum($_GET['id'])) die("Denied");
		if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
			$_GET['rowstart'] = 0;
		}
		if (!isset($_GET['errors'])) {
			$_GET['errors'] = "";
		}
		if (!isset($_GET['a_page'])) {
			$_GET['a_page'] = "main";
		}
		$orders_count = dbcount("(oid)", DB_ESHOP_ORDERS, "opaid = '' || ocompleted = ''");
		$product_count = dbcount("(id)", DB_ESHOP, "");
		$this->pages = array(
			'main' => array('title' => $locale['ESHP202']."<span class='badge m-l-10'>".$product_count."</span>", 'file' => ADMIN."eshop/products.php"),
			'categories' => array('title' => $locale['ESHP203'], 'file' => ADMIN."eshop/categories.php"),
			'photos' => array('title' => $locale['ESHP204'], 'file' => ADMIN."eshop/photosadmin.php"),
			'coupons' => array('title' => $locale['ESHP211'], 'file' => ADMIN."eshop/coupons.php"),
			'featured' => array('title' => $locale['ESHP212'], 'file' => ADMIN."eshop/featured.php"),
			'payments' => array('title' => $locale['ESHP206'], 'file' => ADMIN."eshop/payments.php"),
			'shipping' => array('title' => $locale['ESHP207'], 'file' => ADMIN."eshop/shipping.php"),
			'customers' => array('title' => $locale['ESHP208'], 'file' => ADMIN."eshop/customers.php"),
			'orders' => array('title' => $locale['ESHP209']."<span class='badge m-l-10'>".$orders_count."</span>", 'file' => ADMIN."eshop/orders.php")
		);
		add_to_breadcrumbs(array('link'=>ADMIN.'eshop.php'.$aidlink,'title'=>$locale['ESHP201']));
		add_to_jquery("
		function confirmdelete() {
		return confirm(\"".$locale['ESHP210']."\")
		}
		");
	}

	/**
	 * Primary E-shop Admin
	 */
	public function eshopAdmin() {
		global $aidlink, $locale;
		// consistent loading
		ob_start();
		opentable($locale['ESHP201']);
		echo "<!--Start Eshop Admin-->\n";
		echo "<nav class='navbar navbar-default'>\n";
		echo "<ul class='nav navbar-nav'>\n";
		foreach ($this->pages as $page_get => $page) {
			if ($_GET['a_page'] == $page_get) {
				add_to_breadcrumbs(array('link' => FUSION_SELF.$aidlink."&amp;a_page=".$_GET['a_page'], 'title' => $this->pages[$_GET['a_page']]['title']));
			}
			if ($page_get == 'categories') {
				if (\PHPFusion\Eshop\Admin\Products::category_check()) {
					echo "<li ".($_GET['a_page'] == $page_get ? "class='active'" : '')." ><a style='height:50px;' href='".FUSION_SELF.$aidlink."&amp;a_page=".$page_get."'>".$page['title']."</a></li>\n";
				}
			} else {
				echo "<li ".($_GET['a_page'] == $page_get ? "class='active'" : '')." ><a style='height:50px;' href='".FUSION_SELF.$aidlink."&amp;a_page=".$page_get."'>".$page['title']."</a></li>\n";
			}
		}
		echo "</ul>\n";
		echo "</nav>\n";
		self::loadPage();
		echo "<!--End Eshop Admin-->\n";
		closetable();
		$cache = ob_get_contents();
		ob_end_clean();
		echo $cache;
	}

	/**
	 * Page Loader
	 */
	private function loadPage() {
		if ($_GET['a_page'] == 'categories') self::Categories_Admin();
		if ($_GET['a_page'] == 'coupons') self::Coupon_Admin();
		if ($_GET['a_page'] == 'customers') self::Customers_Admin();
		if ($_GET['a_page'] == 'featured') self::Banners_Admin();
		if ($_GET['a_page'] == 'photos') self::Photos_Admin();
		if ($_GET['a_page'] == 'orders') self::Orders_Admin();
		if ($_GET['a_page'] == 'payments') self::Payments_Admin();
		if ($_GET['a_page'] == 'shipping') self::Shipping_Admin();
		if ($_GET['a_page'] == 'main' || !$_GET['a_page'])	self::Products_Admin();
	}

	/**
	 * Products Admin Page
	 */
	private function Products_Admin() {
		global $locale, $aidlink;
		$item = new Products();
		$edit = (isset($_GET['action']) && $_GET['action'] == 'edit' && $item::category_count()) ? $item->verify_product_edit($_GET['id']) : 0;
		$tab_title['title'][] = $locale['ESHPPRO097'];
		$tab_title['id'][] = 'product';
		$tab_title['icon'][] = '';
		if ($item::category_count() && $item::category_check() || !$item::category_check()) {
			$tab_title['title'][] = $edit ? $locale['ESHPPRO098'] : $locale['ESHPPRO099'];
			$tab_title['id'][] = 'itemform';
			$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
		}
		$tab_active = tab_active($tab_title, ($edit ? 'itemform' : 'product'), 1);
		echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=main");
		echo opentabbody($tab_title['title'][0], 'product', $tab_active, 1);
		$item->product_listing();
		echo closetabbody();
		if (isset($_GET['section']) && $_GET['section'] == 'itemform') {
			echo opentabbody($tab_title['title'][1], 'itemform', $tab_active, 1);
			$item->product_form();
			echo closetabbody();
		}
		closetable();
	}

	/**
	 * Categories Admin Page
	 */
	private function Categories_Admin() {
		global $locale, $aidlink;
		$category = new ProductCategories();
		$tab_title['title'][] = $locale['ESHPCATS099'];
		$tab_title['id'][] = 'listcat';
		$tab_title['icon'][] = '';
		$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') && $category->verify_cat_edit($_GET['cid']) ? $_GET['section'] : 'listcat';
		$tab_title['title'][] = $edit ? $locale['ESHPCATS140'] : $locale['ESHPCATS139'];
		$tab_title['id'][] = 'catform';
		$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
		$tab_active = tab_active($tab_title, $edit, 1);
		echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=categories", 1);
		echo opentabbody($tab_title['title'][0], 'listcat', $tab_active, 1);
		$category->category_listing();
		echo closetabbody();
		if (isset($_GET['section']) && $_GET['section'] == 'catform') {
			echo opentabbody($tab_title['title'][1], 'catform', $tab_active, 1);
			$category->add_cat_form();
			echo closetabbody();
		}
		closetable();
	}

	/**
	 * Coupon Admin Page
	 */
	private function Coupon_Admin() {
		global $locale, $aidlink;
		$coupon = new Coupons();
		$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $coupon->verify_coupon($_GET['cuid']) : 0;
		$tab_title['title'][] = $locale['ESHPCUPNS100'];
		$tab_title['id'][] = 'coupon';
		$tab_title['icon'][] = '';
		$tab_title['title'][] =  $edit ? $locale['ESHPCUPNS115'] : $locale['ESHPCUPNS114'];
		$tab_title['id'][] = 'couponform';
		$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
		$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1, 1);
		echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=coupons");
		echo opentabbody($tab_title['title'][0], 'coupon', $tab_active, 1);
		$coupon->coupon_listing();
		echo closetabbody();
		if (isset($_GET['section']) && $_GET['section'] == 'couponform') {
			echo opentabbody($tab_title['title'][1], 'couponform', $tab_active, 1);
			$coupon->add_coupon_form();
			echo closetabbody();
		}
	}

	/**
	 * Customers Admin Page
	 */
	private function Customers_Admin() {
		global $locale, $aidlink;
		$customer = new Customers();
		$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $customer->verify_customer($_GET['cuid']) : 0;
		$tab_title['title'][] = $locale['ESHPCHK158b'];
		$tab_title['id'][] = 'customer';
		$tab_title['icon'][] = '';
		$tab_title['title'][] =  $edit ? $locale['ESHPCHK158a'] : $locale['ESHPCHK158']; // $locale['ESHPCUPNS115'] : $locale['ESHPCUPNS114'];
		$tab_title['id'][] = 'customerform';
		$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
		$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1);
		echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=customers");
		echo opentabbody($tab_title['title'][0], 'customer', $tab_active, 1);
		$customer->customer_listing();
		echo closetabbody();
		if (isset($_GET['section']) && $_GET['section'] == 'customerform') {
			echo opentabbody($tab_title['title'][1], 'customerform', $tab_active, 1);
			$customer->add_customer_form();
			echo closetabbody();
		}
		// this one has not been deciphered yet.
		if (isset($_GET['step']) && $_GET['step'] == "deletecode") {
			$codetoremove = dbarray(dbquery("SELECT ccupons FROM ".DB_ESHOP_CUSTOMERS." WHERE cuid='".$_GET['cuid']."'"));
			if (!preg_match("/^[-0-9A-ZÅÄÖ._@\s]+$/i", $_GET['cupon'])) { die("Denied"); exit; }
			$cuponcodes = preg_replace(array("(^\.{$_GET['cupon']}$)","(\.{$_GET['cupon']}\.)","(\.{$_GET['cupon']}$)"), array("",".",""), $codetoremove['ccupons']);
			$result = dbquery("UPDATE ".DB_ESHOP_CUSTOMERS." SET ccupons='".$cuponcodes."' WHERE cuid='".$_GET['cuid']."'");
			redirect(FUSION_SELF.$aidlink."&amp;a_page=customers");
		}
	}

	/**
	 * Featured Banner Admin Page
	 */
	private function Banners_Admin() {
		global $locale, $aidlink;
		$banner = new \PHPFusion\Eshop\Admin\Banners();
		$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $banner->verify_banner($_GET['b_id']) : 0;
		$cedit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $banner->verify_item($_GET['i_id']) : 0;

		$tab_title['title'][] = $locale['ESHFEAT108b'];
		$tab_title['id'][] = 'items';
		$tab_title['icon'][] = '';
		$tab_title['title'][] = $locale['ESHFEAT108'];
		$tab_title['id'][] = 'banner';
		$tab_title['icon'][] = '';

		$tab_title['title'][] =  $edit ? $locale['ESHFEAT109a'] : $locale['ESHFEAT109'];
		$tab_title['id'][] = 'bannerform';
		$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';

		$tab_title['title'][] =  $edit ? $locale['ESHFEAT108d'] : $locale['ESHFEAT108c'];
		$tab_title['id'][] = 'itemform';
		$tab_title['icon'][] = $cedit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';


		$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1);

		echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=featured");
		echo opentabbody($tab_title['title'][0], 'items', $tab_active, 1);
		$banner->item_listing();
		echo closetabbody();

		echo opentabbody($tab_title['title'][1], 'banner', $tab_active, 1);
		$banner->banner_listing();
		echo closetabbody();

		switch($_GET['section']) {
			case 'bannerform':
				echo opentabbody($tab_title['title'][2], 'bannerform', $tab_active, 1);
				$banner->add_banner_form();
				echo closetabbody();
				break;
			case 'itemform':
				echo opentabbody($tab_title['title'][2], 'itemform', $tab_active, 1);
				$banner->add_item_form();
				echo closetabbody();
				break;
		}
		closetable();
	}

	/**
	 * Photo Admin Page
	 */
	private function Photos_Admin() {
		global $locale, $aidlink;
		$eshop_gallery = new \PHPFusion\Gallery\Admin();
		$eshop_gallery->setUploadSettings(
			array(
				'thumbnail_folder'=>'thumbs',
				'thumbnail' => 1,
				'thumbnail_w' =>  fusion_get_settings('eshop_image_tw'),
				'thumbnail_h' =>  fusion_get_settings('eshop_image_th'),
				'thumbnail_suffix' =>'_t1',
				'thumbnail2'=>1,
				'thumbnail2_w' 	=>  fusion_get_settings('eshop_image_t2w'),
				'thumbnail2_h' 	=>  fusion_get_settings('eshop_image_t2h'),
				'thumbnail2_suffix' => '_t2',
				'delete_original' => 1,
				'max_width'		=>	fusion_get_settings('eshop_image_w'),
				'max_height'	=>	fusion_get_settings('eshop_image_h'),
				'max_byte'		=>	fusion_get_settings('eshop_image_b'),
				'multiple' => 0,
			)
		);

		$eshop_gallery->setImageUploadDir(BASEDIR."eshop/pictures/");
		$eshop_gallery->setPhotoCatDb(DB_ESHOP_ALBUMS);
		$eshop_gallery->setPhotoDb(DB_ESHOP_PHOTOS);
		$eshop_gallery->setGalleryRights('ESHP');
		$eshop_gallery->setEnableComments(false);
		$eshop_gallery->setAllowComments(false);
		$eshop_gallery->setEnableRatings(false);
		$eshop_gallery->setAllowRatings(false);
		$eshop_gallery->boot();
	}

	/**
	 * Payments Admin Page
	 */
	private function Payments_Admin() {
		global $locale, $aidlink;
		if (isset($_GET['payid']) && !isnum($_GET['payid'])) die("Denied");
		$payment = new \PHPFusion\Eshop\Admin\Payments();
		$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $payment->verify_payment($_GET['pid']) : 0;
		$tab_title['title'][] = $locale['ESHPPMTS119'];
		$tab_title['id'][] = 'payment';
		$tab_title['icon'][] = '';
		$tab_title['title'][] =  $edit ? $locale['ESHPPMTS120'] : $locale['ESHPPMTS121'];
		$tab_title['id'][] = 'paymentform';
		$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
		$tab_active = tab_active($tab_title, $edit ? 1 : 0 , 1);
		echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=payments");
		echo opentabbody($tab_title['title'][0], 'payment', $tab_active, 1);
		$payment->payment_listing();
		echo closetabbody();
		if (isset($_GET['section']) && $_GET['section'] == 'paymentform') {
			echo opentabbody($tab_title['title'][1], 'paymentform', $tab_active, 1);
			$payment->add_payment_form();
			echo closetabbody();
		}
	}

	/**
	 * Orders Admin Page
	 */
	private function Orders_Admin() {
		global $locale, $aidlink;
		$orders = new Orders();
		$tab_title['title'][] = $locale['ESHP301'];
		$tab_title['id'][] = 'orders';
		$tab_title['icon'][] = '';
		$tab_title['title'][] = $locale['ESHP302'];
		$tab_title['id'][] = 'history';
		$tab_title['icon'][] = '';
		$tab_active = tab_active($tab_title, $_GET['section'], 1);
		echo opentab($tab_title, $tab_active, 'pageorders', 1);
		echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active, 1);
		$orders->list_order();
		echo closetabbody();
		if ($_GET['section'] == 'history') {
			echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active, 1);
			$orders->list_history();
			echo closetabbody();
		}
		echo closetab();
	}

	/**
	 * Shipping Admin Page
	 */
	private function Shipping_Admin() {
		global $locale, $aidlink;
		$shipping = new Shipping();
		$cview = (isset($_GET['action']) && $_GET['action'] == 'view') ? $shipping->verify_shippingCats($_GET['cid']) : 0;
		$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $shipping->verify_shippingCats($_GET['cid']) : 0;
		$tab_title['title'][] = $cview ? $locale['ESHPSS107'] : $locale['ESHPSS108'];
		$tab_title['id'][] = 'shipping';
		$tab_title['icon'][] = $cview ? 'fa fa-pencil m-r-10' : '';
		$tab_title['title'][] =  $edit ? $locale['ESHPSS109'] : $locale['ESHPSS110'];
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
	}
}