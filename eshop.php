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
$eShop = new PHPFusion\Eshop\Eshop();
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
	$data = $eShop->get_checkoutData();
	$data += $eShop->display_customer_form();
	$data += $eShop->display_coupon_form();
	render_checkout($data);
} else {
	render_eshop_featured_url($info);
	render_eshop_featured_product($info);
	render_eshop_page_content($info);
	render_eshop_featured_category($info);
}


// port to E-shop function when done.



function render_checkout(array $info) {
	echo "<h4>Checkout - ".number_format($info['total_weight'], 2)." ".fusion_get_settings('eshop_weightscale')."</h4>\n";
	echo "<table class='table table-responsive'>";
	echo "<tr>\n";
	echo "<th class='col-xs-5 col-sm-5'>Product</th>\n";
	echo "<th class='col-xs-2 col-sm-2'>Quantity</th>\n";
	echo "<th>Unit Price</th>\n";
	echo "<th>Total</th>\n";
	echo "<th>Options</th>\n";
	echo "</tr>\n";
	foreach($info['item'] as $tid => $data) {
		$specs = \PHPFusion\Eshop\Eshop::get_productSpecs($data['dync'], $data['cdyn']);
		$color = \PHPFusion\Eshop\Eshop::get_productColor($data['cclr']);
		echo openform('updateqty', "updateqty-".$data['tid'], 'post', BASEDIR."eshop.php?checkout", array('downtime'=>0, 'notice'=>0));
		echo "<tr>\n";
		echo "<td class='col-xs-5 col-sm-5'>\n";
		echo "<div class='pull-left m-r-10' style='width:70px'>\n";
		echo "<img class='img-responsive' src='".$data['cimage']."' />";
		echo "</div>\n";
		echo "<div class='overflow-hide'>\n";
		echo "<a href='".BASEDIR."eshop.php?product=".$data['prid']."'>".$data['citem']."</a>\n";
		if ($specs) echo "<div class='display-block text-smaller'><span class='strong'>".$data['cdynt']."</span> - $specs</div>\n";
		if ($color) echo "<div class='display-block text-smaller'><span class='strong'>Color</span> - $color</span>\n";
		echo "</div>\n";
		echo "</td>\n";
		echo "<td>\n";
		echo form_text('', 'qty', 'qty', $data['cqty'], array('append_button'=>1, 'append_value'=>"<i class='fa fa-repeat m-t-5 m-b-0'></i>", 'append_type'=>'submit'));
		echo form_hidden('', 'utid', 'utid', $data['tid']);
		echo "</td>\n";
		echo "<td>".fusion_get_settings('eshop_currency').number_format($data['cprice'], 2)."</td>\n";
		echo "<td>".fusion_get_settings('eshop_currency').number_format($data['totalprice'], 2)."</td>\n";
		echo "<td>".form_button('Remove', 'remove', 'remove', 'remove', array('class'=>'btn-danger btn-sm'))."</td>\n";
		echo "</tr>\n";
		echo closeform();
	}
	echo "</table>\n";
	// list accordion item
	echo opencollapse('cart-list');
	// customer info
	echo opencollapsebody('Your Information', 'cif', 'cart-list', 0);
	echo "<div class='p-15'>\n";
	echo $info['customer_form'];
	echo "</div>\n";
	echo closecollapsebody();
	// Coupon code
	echo opencollapsebody('Use Coupon Codes', 'cpn', 'cart-list', 0);
	echo "<div class='p-15'>\n";
	echo $info['coupon_form'];
	echo "</div>\n";
	echo closecollapsebody();
	// Estimate shipping rates
	echo opencollapsebody('Shipping Rates', 'ship', 'cart-list', 0);
	echo "html";
	echo closecollapsebody();
	echo closecollapse();

	echo "<div class='col-xs-12 col-sm-6 p-r-0 pull-right'>\n";
		echo "<div class='list-group-item'>\n";
		echo "<span class='display-inline-block strong m-r-10'>Sub-Total: </span><span class='pull-right'>".fusion_get_settings('eshop_currency').number_format($info['gross_total'],2)."</span>\n";
		echo "</div>\n";
		echo "<div class='list-group-item'>\n";
		echo "<span class='display-inline-block strong m-r-10'>VAT (".fusion_get_settings('eshop_vat').")%:</span><span class='pull-right'>".fusion_get_settings('eshop_currency').$info['vat']."</span>\n";
		echo "</div>\n";
		echo "<div class='list-group-item'>\n";
		echo "<span class='display-inline-block strong m-r-10'>Sub-Total:</span><span class='pull-right'>".fusion_get_settings('eshop_currency').number_format($info['vat_gross'],2)."</div>\n";
	echo "</div>\n";

	echo "<div class='display-block  p-l-0 p-r-0 m-t-20 col-xs-12'>\n";
	echo "<a class='btn btn-primary pull-right' href=''>Checkout</a>\n";
	echo "<a class='btn btn-default pull-left' href=''>Continue Shopping</a>\n";

	echo "</div>\n";

}






require_once THEMES."templates/footer.php";
