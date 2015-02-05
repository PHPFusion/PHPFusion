<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

if (!function_exists('render_eshop_nav')) {
	/**
	 * Shop Navigation
	 * @param array $info
	 */
	function render_eshop_nav(array $info) {
		$res = "<div class='navbar navbar-default' role='navigation'>\n";
		$res .= "<div class='navbar-collapse collapse'>\n";
		$res .= "<ul class='nav navbar-nav'>\n";
		if ($_GET['category']) {
			if (!empty($info['previous_category'])) {
				$res .= "<li><a href='".$info['previous_category']['link']."'>Back to ".$info['previous_category']['title']."</a></li>\n";
			} else {
				$res .= "<li><a href='".BASEDIR."eshop.php'>Store Front</a></li>\n";
			}
			if (!empty($info['current_category'])) {
				$res .= "<li class='active'><a href='".BASEDIR."eshop.php?category=".$info['current_category']['cid']."'>".$info['current_category']['title']."</a></li>\n";
			}
		}
		if (!empty($info['category'][$_GET['category']])) {
			foreach ($info['category'][$_GET['category']] as $data) {
				$res .= "<li><a href='".$data['link']."'>".$data['title']."</a></li>\n";
			}
		}
		$res .= "</ul>\n";
		$res .= "</div>\n</div>\n";
		echo $res;
	}
}

if (!function_exists('render_eshop_featured_product')) {
	/**
	 * Product Slideshow (Canvas)
	 * @param array $info
	 */
	function render_eshop_featured_product(array $info) {
		//go for carousel
		$i = 0;
		$indicator = '';
		$slides = '';
		if (!empty($info['featured'])) {
			foreach ($info['featured'] as $id => $banner) {
				if ($banner['featbanner_id']) {
					$indicator .= "<li data-target='#carousel-example-generic' ".($i == 0 ? "class='active'" : '')." data-slide-to='".$i."'></li>\n";
					$slides .= "
			<div style='max-height:280px; overflow:hidden;' class='item ".($i == 0 ? "active" : '')."'>
			<a href='".BASEDIR."eshop.php?product=".$banner['featbanner_id']."'>
			<img class='img-responsive' style='width:100%' src='".$banner['featbanner_banner']."' />
			<div class='carousel-caption'>".$banner['featbanner_title']."</div>
			</a>
			</div>
			";
					$i++;
				}
			}
		}
		if ($indicator) {
			?>
			<div class='panel panel-default m-t-20'>
				<div class='panel-body'>
					<div id="carousel-example-generic" class="carousel slide" style='max-height:400px;'
						 data-ride="carousel">
						<!-- Indicators -->
						<ol class="carousel-indicators">
							<?php echo $indicator ?>
						</ol>
						<!-- Wrapper for slides -->
						<div class="carousel-inner" role="listbox">
							<?php echo $slides ?>
						</div>

						<!-- Controls -->
						<a class="left carousel-control" href="#carousel-example-generic" role="button"
						   data-slide="prev">
							<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
							<span class="sr-only">Previous</span>
						</a>
						<a class="right carousel-control" href="#carousel-example-generic" role="button"
						   data-slide="next">
							<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
							<span class="sr-only">Next</span>
						</a>
					</div>
				</div>
			</div>
		<?php
		}
	}
}

if (!function_exists('render_eshop_featured_category')) {
	/**
	 * Category Thumbnails
	 * @param array $info
	 */
	function render_eshop_featured_category(array $info) {
		$cat = '';
		if (!empty($info['featured'])) {
			foreach ($info['featured'] as $id => $banner) {
				if ($banner['featbanner_cat']) {
					$cat .= "<a href='".BASEDIR."eshop.php?category=".$banner['featbanner_cat']."'>";
					$cat .= thumbnail($banner['featbanner_banner'], "23%");
					$cat .= "</a>\n";
				}
			}
		}
		if ($cat) {
			?>
			<h3>Featured Category</h3>
			<?php echo $cat; ?>
		<?php
		}
	}
}

if (!function_exists('render_eshop_featured_url')) {
	/**
	 * URL Thumbnails
	 * @param array $info
	 */
	function render_eshop_featured_url(array $info) {
		$cat = '';
		if (!empty($info['featured'])) {
			foreach ($info['featured'] as $id => $banner) {
				if ($banner['featbanner_url']) {
					$cat .= "<a href='".BASEDIR.$banner['featbanner_url']."'>";
					$cat .= thumbnail($banner['featbanner_banner'], "23%");
					$cat .= "</a>\n";
				}
			}
		}
		if ($cat) {
			?>
			<h3>Featured Sections</h3>
			<?php echo $cat; ?>
		<?php
		}
	}
}

if (!function_exists('render_eshop_page_content')) {
	/**
	 * Main Page Content
	 * @param array $info
	 */
	function render_eshop_page_content(array $info) {
		global $locale;
		echo $_GET['category'] ? "<h3>Latest in ".$info['title']."</h3>" : "<h3>New Arrivals in Store</h3>\n";
		if (!empty($info['item'])) {
			$i = 1;
			$calculated_bs = col_span(fusion_get_settings('eshop_ipr'), 1);
			// Main Products Lineup
			if (!$_GET['category'] || tree_count($info['item'], 'cid', $_GET['category'])) {
				echo "<div class='row eshop-rows'>\n";
				foreach ($info['item'] as $product_id => $item_data) {
					if ($_GET['category'] && $item_data['cid'] == $_GET['category'] || !$_GET['category']) {
						echo "
						<div class='col-xs-12 eshop-column col-sm-$calculated_bs text-center m-t-20 m-b-20'>\n
						<a class='display-inline-block' style='margin:0px auto; min-height: ".(fusion_get_settings('eshop_image_th')*1.1)."px;' href='".$item_data['link']."'>
							<img class='img-responsive' src='".$item_data['picture']."' style='width: ".fusion_get_settings('eshop_image_tw')."px; max-height: ".fusion_get_settings('eshop_image_th')."px;'>\n
						</a>
						<div class='text-left p-l-20 m-b-20' style='min-height: ".(fusion_get_settings('eshop_image_th')*0.5)."px;'>
							<a href='".$item_data['link']."'><span class='eshop-product-title'>".$item_data['title']."</span></a>
						";
						if ($item_data['xprice']) {
							echo "
								<div class='eshop-price'>
									<span><small>".fusion_get_settings('eshop_currency')."</small> ".number_format($item_data['xprice'])."</span>
									<span class='eshop-discount label label-danger'>".number_format(100-($item_data['xprice']/$item_data['price']*100))."% ".$locale['off']."</span>
								</div>
								<span class='eshop-xprice'><small>".fusion_get_settings('eshop_currency')."</small>".number_format($item_data['price'])."</span>\n";
						} else {
							echo "<div class='eshop-price'><small>".fusion_get_settings('eshop_currency')."</small>".number_format($item_data['price'])."</div>\n";
						}
						echo "</div>\n";
						echo "</div>\n";
						$i++;
					}
				}
				echo "</div>\n";
			} else {
				echo "<div class='text-center m-t-20'>
				<span>There are no products found</span><br/>
				</div>\n";
			}
			// Related Products Lineup
			if ($_GET['category']) {
				$i = 1;
				$a_html = "<h3>Related Products</h3>\n<div class='row eshop-rows'>\n";
				$html = "";
				foreach ($info['item'] as $product_id => $item_data) {
					if ($item_data['cid'] !== $_GET['category']) {
						$html .= "
						<div class='col-xs-12 eshop-column col-sm-$calculated_bs text-center m-t-20 m-b-20'>\n
							<a class='display-inline-block' style='margin:0px auto; min-height: ".(fusion_get_settings('eshop_image_th')*1.1)."px;' href='".$item_data['link']."'>
							<img class='img-responsive' src='".$item_data['picture']."' style='width: ".fusion_get_settings('eshop_image_tw')."px; max-height: ".fusion_get_settings('eshop_image_th')."px;'>\n
							</a>
						<div class='text-left p-l-20 m-b-20' style='min-height: ".(fusion_get_settings('eshop_image_th')*0.5)."px;'>
						<a href='".$item_data['link']."'><span class='eshop-product-title'>".$item_data['title']."</span></a>
					";
						if ($item_data['xprice']) {
							$html .= "
							<div class='eshop-price'>
								<span><small>".fusion_get_settings('eshop_currency')."</small> ".number_format($item_data['xprice'])."</span>
								<span class='eshop-discount label label-danger'>".number_format(100-($item_data['xprice']/$item_data['price']*100))."% ".$locale['off']."</span>
							</div>
							<span class='eshop-xprice'><small>".fusion_get_settings('eshop_currency')."</small>".number_format($item_data['price'])."</span>\n";
						} else {
							$html .= "<div class='eshop-price'><small>".fusion_get_settings('eshop_currency')."</small>".number_format($item_data['price'])."</div>\n";
						}
						$html .= "</div>\n";
						$html .= "</div>\n";
						$i++;
					}
				}
				$b_html = "</div>";
				if ($html) {
					echo $a_html.$html.$b_html;
				}
			}
		} else {
			?>
			<div class='text-center m-t-20'>
				<span>There are no products found</span><br/>
				<a href='<?php echo BASEDIR."eshop.php" ?>'>Go back to Store Front</a>
			</div>
		<?php
		}
	}
}

if (!function_exists('render_eshop_product')) {
	/**
	 * Product Catalog Page
	 * @param $info
	 */
	function render_eshop_product($info) {

	/*
	 * [item] => Array
        (
            [16] => Array
                (
					[qty] - unrestricted quantity buy at a go
                    [id] => 16
                    [title] => Mobile 99
                    [cid] => 16
                    [picture] => ./eshop/pictures/cover_large.jpg
                    [thumb] => ./eshop/pictures/thumb/cover_large_t1.jpg
                    [thumb2] => cover_large_t2.jpg
                    [introtext] =>
                    [description] =>
                    [anything1] =>
                    [anything1n] =>
                    [anything2] =>
                    [anything2n] =>
                    [anything3] =>
                    [anything3n] =>
                    [weight] =>

                    [stock] => 1 // show stock?
                    [version] =>
                    [status] => 1 // in stock?
                    [active] => 1
                    [gallery_on] => 1
                    [delivery] => 0
                    [demo] =>
                    [cart_on] => 1
                    [buynow] => 1

	[rpage] => crl.php
                    [icolor] =>
                    [dynf] =>
                    [dync] =>


                    [dmulti] => 1
                    [cupons] => 1
                    [access] => 0
                    [campaign] => 0


                    [category_title] => Game
                    [category_link] => ./category=16
                    [link] => ./eshop.php?product=16


	 [comments] => 1
                    [ratings] => 1
                    [linebreaks] => 1
                )

        )
	 */
	global $locale, $eShop;
	$data = $info['item'][$_GET['product']];
	echo "<div class='m-t-10'>\n";
	echo render_breadcrumbs();
	echo "</div>\n";
	echo "<div class='row product_slot_".$data['id']."'>\n<div class='col-xs-12 col-sm-5'>\n";
	// Images
	echo "<div class='rib-wrap itembox'>";
	if (!$data['status'] == "1") {
		echo "<div class='ribbon-wrapper-green'><div class='ribbon-green'>".$locale['ESHPF147']."</div></div>";
	} else if ($data['campaign'] == "1") {
		echo "<div class='ribbon-wrapper-red'><div class='ribbon-red'>".$locale['ESHPF146']."</div></div>";
	} else {
		if ($data['dateadded']+fusion_get_settings('eshop_newtime') >= time()) {
			echo "<div class='ribbon-wrapper-blue'><div class='ribbon-blue'>".$locale['ESHPF145']."</div></div>";
		}
	}
	// picture container
	echo "<img title='".$data['title']."' id='photo_container' ".(fusion_get_settings('eshop_ratios') ? "class='img-responsive'" : "style='width:".fusion_get_settings('eshop_idisp_w2')."px; height: ".fusion_get_settings('eshop_idisp_h2')."px;' ")." src='".$data['picture']."'>\n";
	echo "</div>\n";

	if ($data['gallery_on'] == "1") {
		// add a change source of photo_container is sufficient for default template.
		add_to_jquery("
		$('.imgclass').bind('click', function(e) {
			$('#photo_container').prop('src', $(this).data('url'));
		});
		");
		if (!empty($info['photos'])) {
			foreach($info['photos'] as $photos) {
				echo "<div class='pointer display-inline-block m-t-20 m-r-10 imgclass' style='width:20%' data-url='".$photos['photo_filename']."'>\n";
				echo thumbnail($photos['photo_thumb1'], '100%');
				echo "</div>\n";
			}
		}
	}
	echo "</div>\n<div class='col-xs-12 col-sm-7'>\n";
	echo "<h2 class='product-title m-b-0'>".$data['title']."</h2>";
	//echo $eShop->display_social_buttons($data['id'], $data['picture'], $data['title']); // there is a wierd behavior in social buttons i cannot push this array into $info.
	// product basic information
	echo "<div class='text-smaller'>\n";
	echo "<span class='display-block'>Product-Serial: ".$data['artno']."</span>\n";
	echo "<span class='display-block'>".$data['stock_status']."</span>\n";
	echo "<span class='display-block'>".$data['version']."</span>";
	echo "<span class='display-block'>".$data['shipping']."</span>";

	if ($data['demo']) {
		echo "<span class='display-block'>";
		$urlprefix = !strstr($data['demo'], "http://") ? "http://" : "";
		echo $locale['ESHP013'].": <a href='".$urlprefix.$data['demo']."' target='_blank'>".$locale['ESHP015']."</a>";
		echo "</span>\n";
	}


	echo "</div>\n";
	// keywords
	$keywords = $data['keywords'] ? explode(',', $data['keywords']) : '';
	if (!empty($keywords)) {
		echo "<div class='text-smaller'>\n";
		echo "<span>Tags:</span> \n";
		foreach($keywords as $tag) {
			echo "<a class='display-inline m-r-10' href=''>".$tag."</a>";
		}
		echo "</div>\n";
	}
	// price
	if ($data['xprice']) {
		echo "<div class='m-t-20'>\n";
		echo "
		<div class='eshop-price'>
			<span><small>".fusion_get_settings('eshop_currency')."</small> ".number_format($data['xprice'],2)."</span>
			<span class='eshop-discount label label-danger'>".number_format(100-($data['xprice']/$data['price']*100))."% ".$locale['off']."</span>
		</div>
		<span class='eshop-xprice'><small>".fusion_get_settings('eshop_currency')."</small>".number_format($data['price'],2)."</span>\n";
		echo "</div>\n";
	} else {
		echo "<div class='m-t-20'>\n";
		echo "<div class='eshop-price'><small>".fusion_get_settings('eshop_currency')."</small>".number_format($data['price'],2)."</div>\n";
		echo "</div>\n";
	}

	// ok now do the add cart thing. we need a form to push to cart or buynow.
	echo openform('productfrm', 'productfrm','post', BASEDIR."eshop.php?product=".$_GET['product']);
	echo "<div class='m-t-20'>\n";
	if (!empty($data['dync'])) {
		$title = $data['dynf'] ? $data['dynf'] : 'Category';
		$dync = str_replace('&quot;', '', $data['dync']);
		$dync_opts = array_filter(explode('.', $dync));
		echo form_select($title, 'product_type', 'product_type', $dync_opts, 1, array('inline'=>1, 'width'=>'200px', 'class'=>'product-selector m-b-0'));
	}
	if ($data['icolor']) {
		echo "<div class='form-group m-t-10'>\n";
		echo "<label class='col-xs-12 col-sm-3 text-smaller p-l-0'>".$locale['ESHP017']."</label>\n";
		echo "<div class='col-xs-12 col-sm-9'>\n";
		$color = str_replace('&quot;', '', $data['icolor']);
		$full_colors = PHPFusion\Eshop::get_iColor();
		$current_colors = array_filter(explode('.', $color));
		$i = 0;
		foreach($current_colors as $val) {
			$color = $full_colors[$val]['hex'];
			$title = $full_colors[$val]['title'];
			echo "<div><input id='".$color."' type='radio' name='product_color' value='".$val."' ".($i == 0 ? 'checked' : '')." />
			<span class='display-inline-block' style='background: $color; width:15px; height:15px; border-radius:50%; margin-left:5px;'>&nbsp;</span>
			<small class='p-l-10'><label for='".$color."'>$title</label></small>
			</div>";
			$i++;
		}
		defender::add_field_session(array(
					 'input_name' 	=> 	'product_color',
					 'type'			=>	'number',
					 'title'		=>	$locale['ESHP017'],
					 'id' 			=>	'',
					 'required'		=>	1,
					 'safemode' 	=> 	0,
				 ));
		echo "</div>\n";
		echo "</div>\n";
	}
	// qty
	if ($data['qty']) {
		echo form_text($locale['ESHP019'], 'product_quantity', 'product_quantity', '1', array('number'=>1, 'inline'=>1, 'class'=>'product-quantity input-sm', 'width'=>'50px',
			'append_button'=>1,
			'append_value'=> "<i class='fa fa-plus m-t-5'></i>",
			'append_type'=>'button',
			'prepend_button'=>1,
			'prepend_value'=> "<i class='fa fa-minus m-t-5'></i>",
			'prepend_type'=>'button',
		));
		// now add some simple js
		add_to_jquery("
		$('#product_quantity-prepend-btn').bind('click', function(e) {
			var order_qty = $('#product_quantity').val();
			var new_val = --order_qty;
			if (order_qty >=1) { $('#product_quantity').val(new_val); }
		 });
		 $('#product_quantity-append-btn').bind('click', function(e) {
			var order_qty = $('#product_quantity').val();
			var new_val = ++order_qty;
			$('#product_quantity').val(new_val);
		 });
		");
	} else {
		echo form_hidden('', 'product_quantity', 'product_quantity', 1);
	}

	if ($data['status'] == "1") {
		echo "<div class='m-t-20'>\n";
		if ($data['buynow'] == "1") { // use post action instead
			echo form_button($locale['ESHP020'], 'buy_now', 'buy_now', $locale['ESHP020'], array('class'=>'m-r-10 '.fusion_get_settings('eshop_buynow_color')));
			//echo "<a class='btn m-r-10 ".."' href='".BASEDIR."eshop/buynow.php?id=".$data['id']."'>".$locale['ESHP020']."</a>";
		}
		if ($data['cart_on'] == "1") {
			echo form_button($locale['ESHP021'], 'add_cart', 'add_cart', $locale['ESHP021'], array('icon'=>'fa fa-shopping-cart m-r-5 m-t-5', 'class'=>'m-r-10 '.fusion_get_settings('eshop_addtocart_color'), 'type'=>'button'));
			//echo "<a class='btn m-r-10 ".fusion_get_settings('eshop_addtocart_color')."' href='javascript:;' onclick='javascript:cartaction(".$data['id']."); return false;'><i class='fa fa-shopping-cart m-t-5 m-r-10'></i> ".$locale['ESHP021']."</a>";
		}
		echo "</div>\n";
	}
	echo form_hidden('', 'id', 'id', $data['id']);
	// the rest of the fields can load data from class... like this.
	//$product_data = PHPFusion\Eshop::get_productData($_POST['id']);
	echo "</div>\n";
	echo closeform();
	// change buynow color.
	echo "</div>\n</div>\n";
	echo "<hr/>\n";
	$tab_title['title'][] = $locale['ESHP022'];
	$tab_title['id'][] = 'pdesc';
	$tab_title['icon'][] = '';
	$any = array();
	if ($data['anything1'] && $data['anything1n']) {
		$any['a1'] = array('title'=>'anything1n', 'data'=>'anything1');
		$tab_title['title'][] = $data['anything1n'];
		$tab_title['id'][] = 'a1';
		$tab_title['icon'][] = '';
	}
	if ($data['anything2'] && $data['anything2n']) {
		$any['a2'] = array('title'=>'anything2n', 'data'=>'anything2');
		$tab_title['title'][] = $data['anything2n'];
		$tab_title['id'][] = 'a2';
		$tab_title['icon'][] = '';
	}
	if ($data['anything3'] && $data['anything3n']) {
		$any['a3'] = array('title'=>'anything3n', 'data'=>'anything3');
		$tab_title['title'][] = $data['anything3n'];
		$tab_title['id'][] = 'a3';
		$tab_title['icon'][] = '';
	}

	$tab_active = tab_active($tab_title, 0);
	echo opentab($tab_title, $tab_active, 'product-tabs');
	echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);
	echo "<span class='display-block m-t-10'>".stripslashes(nl2br($data['description']))."</span>";
	echo closetabbody();
	if (!empty($any)) {
		foreach($any as $id => $tab_data) {
			echo opentabbody($data[$tab_data['title']], $id, $tab_active);
			echo "<span class='display-block m-t-10'>".stripslashes(nl2br($data[$tab_data['data']]))."</span>";
			echo closetabbody();
		}
	}
	echo closetab();

	echo "<a class='m-t-20 btn ".fusion_get_settings('eshop_return_color')."' href='javascript:;' onclick='javascript:history.back(-1); return false;'><i class='fa fa-reply m-t-5 m-r-5'></i> ".$locale['ESHP030']."</a>";

}
}