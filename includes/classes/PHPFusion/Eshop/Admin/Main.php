<?php

namespace PHPFusion\Eshop\Admin;
/**
 * Class eShop
 */
class Main {
	/**
	 * @var array
	 */
	private $pages = array(); // secure this so no injection can occur.
	/**
	 * these are the vars we will use only.
	 */
	public function __construct() {
		global $locale;
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
		$countorders = "".dbcount("(oid)", "".DB_ESHOP_ORDERS."", "opaid = '' || ocompleted = ''")."";
		$this->pages = array(
			'main' => array('title' => $locale['ESHP202'], 'file' => ADMIN."eshop/products.php"),
			'photos' => array('title' => $locale['ESHP204'], 'file' => ADMIN."eshop/photosadmin.php"),
			'categories' => array('title' => $locale['ESHP203'], 'file' => ADMIN."eshop/categories.php"),
			'coupons' => array('title' => $locale['ESHP211'], 'file' => ADMIN."eshop/coupons.php"),
			'featured' => array('title' => $locale['ESHP212'], 'file' => ADMIN."eshop/featured.php"),
			'payments' => array('title' => $locale['ESHP206'], 'file' => ADMIN."eshop/payments.php"),
			'shipping' => array('title' => $locale['ESHP207'], 'file' => ADMIN."eshop/shipping.php"),
			'customers' => array('title' => $locale['ESHP208'], 'file' => ADMIN."eshop/customers.php"),
			'orders' => array('title' => $locale['ESHP209']."<span class='badge m-l-10'>".$countorders."</span>", 'file' => ADMIN."eshop/orders.php")
		);
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
		opentable($locale['ESHP201']);
		echo "<!--Start Eshop Admin-->\n";
		echo "<nav class='navbar navbar-default'>\n";
		echo "<ul class='nav navbar-nav'>\n";

		foreach ($this->pages as $page_get => $page) {
			if ($page_get == 'categories') {
				if (\PHPFusion\Eshop\Admin\Products::category_check()) {
					echo "<li ".($_GET['a_page'] == $page_get ? "class='active'" : '')." ><a href='".FUSION_SELF.$aidlink."&amp;a_page=".$page_get."'>".$page['title']."</a></li>\n";
				}
			} else {
				echo "<li ".($_GET['a_page'] == $page_get ? "class='active'" : '')." ><a href='".FUSION_SELF.$aidlink."&amp;a_page=".$page_get."'>".$page['title']."</a></li>\n";
			}
		}
		echo "</ul>\n";
		echo "</nav>\n";
		self::loadPage();
		echo "<!--End Eshop Admin-->\n";
		closetable();
	}
	// Return the included file
	/**
	 * @param $settings
	 */
	private function loadPage() {
		global $aidlink, $locale;
		add_to_breadcrumbs(array('link' => FUSION_SELF.$aidlink."&amp;a_page=".$_GET['a_page'], 'title' => $this->pages[$_GET['a_page']]['title']));
		include $this->pages[$_GET['a_page']]['file'];
	}
}
