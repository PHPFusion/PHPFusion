<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop.php
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
require_once dirname(__FILE__)."/maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."eshop.php";
require_once THEMES."templates/global/eshop.php";
//include INCLUDES."eshop_functions_include.php";

//Close the tree when eShop home have been clicked... where is the tree?
/*
if ($settings['eshop_cats'] == "1") {
echo '<script type="text/javascript"> 
	d.closeAll();
</script>';
}
*/
$eShop = new PHPFusion\Eshop();
$info = $eShop->get_category();
$info += $eShop->get_product();
$info += $eShop->get_featured();
$info += $eShop->get_title();
$info += $eShop->get_product_photos();
render_eshop_nav($info);
if ($_GET['category']) {
	// view category page
	render_eshop_featured_product($info);
	render_eshop_page_content($info);
	render_eshop_featured_category($info);
} elseif ($_GET['product']) {
	// view product page
	render_eshop_product($info);
} elseif (isset($_GET['checkout'])) {
	// checkout page
	render_checkout();

} else {
	render_eshop_featured_url($info);
	render_eshop_featured_product($info);
	render_eshop_page_content($info);
	render_eshop_featured_category($info);
}

function render_checkout() {


	echo "<h4>Checkout (Total Weight)</h4>\n";
	echo "<table class='table table-responsive'>";
	echo "<tr>\n";
	echo "<th class='col-xs-5 col-sm-5'>Product</th>\n";
	echo "<th class='col-xs-2 col-sm-2'>Quantity</th>\n";
	echo "<th>Unit Price</th>\n";
	echo "<th>Total</th>\n";
	echo "<th>Options</th>\n";
	echo "</tr>\n";

	echo "<tr>\n";

	echo "<td>\n";
	echo "<div class='pull-left m-r-10'>\n";
	echo thumbnail('fake.png', '70px');
	echo "</div>\n";
	echo "<div class='overflow-hide'>\n";
	echo "<a href=''>Product Name</a>\n";
	echo "<span class='display-block'>Product Specifications</span>\n";
	echo "<span class='display-block'>Product Color</span>\n";
	echo "</div>\n";
	echo "</td>\n";

	echo "<td>\n";
	echo form_text('', 'qty', 'qty', 1, array('append_button'=>1, 'append_value'=>"<i class='fa fa-repeat m-t-5 m-b-0'></i>", 'append_type'=>'button'));
	echo "</td>\n";

	echo "<td>\n";
	echo number_format('30', 2);
	echo "</td>\n";

	echo "<td>\n";
	echo number_format('130', 2);
	echo "</td>\n";

	echo "<td>\n";
	echo form_button('Remove', 'remove', 'remove', 'remove', array('class'=>'btn-danger btn-sm'));
	echo "</td>\n";


	echo "</tr>\n";
	echo "</table>\n";




	// list accordion item
	echo opencollapse('cart-list');
	// customer info
	echo opencollapsebody('Customer Info', 'cif', 'cart-list', 0);
	echo "html";
	echo closecollapsebody();
	// Coupon code
	echo opencollapsebody('Coupon Codes', 'cpn', 'cart-list', 0);
	echo "html";
	echo closecollapsebody();
	// Estimate shipping rates
	echo opencollapsebody('Shipping Rates', 'ship', 'cart-list', 0);
	echo "html";
	echo closecollapsebody();
	echo closecollapse();

	echo "<div class='col-xs-12 col-sm-6 p-r-0 pull-right'>\n";
		echo "<div class='list-group-item'>\n";
		echo "<span class='display-inline-block strong m-r-10'>Sub-Total:</span> ".number_format(30,2);
		echo "</div>\n";
		echo "<div class='list-group-item'>\n";
		echo "<span class='display-inline-block strong m-r-10'>Sub-Total:</span> ".number_format(30,2);
		echo "</div>\n";
	echo "</div>\n";

	echo "<div class='display-block  p-l-0 p-r-0 m-t-20 col-xs-12'>\n";
	echo "<a class='btn btn-primary pull-right' href=''>Checkout</a>\n";
	echo "<a class='btn btn-default pull-left' href=''>Continue Shopping</a>\n";

	echo "</div>\n";

}






require_once THEMES."templates/footer.php";
