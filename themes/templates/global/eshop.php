<?php

if (!function_exists('render_eshop_nav')) {
	/**
	 * Shop Navigation
	 * @param array $info
	 */
	function render_eshop_nav(array $info) {
		echo "<nav class='eshop-nav navbar-default nav'>\n";
		echo "<ul class='navbar navbar-nav'>\n";
		if ($_GET['category']) {
			if (!empty($info['previous_category'])) {
				echo "<li><a href='".$info['previous_category']['link']."'>Back to ".$info['previous_category']['title']."</a></li>\n";
			} else {
				echo "<li><a href='".BASEDIR."eshop.php'>Store Front</a></li>\n";
			}
			if (!empty($info['current_category'])) {
				echo "<li class='active'><a href='".BASEDIR."eshop.php?category=".$info['current_category']['cid']."'>".$info['current_category']['title']."</a></li>\n";
			}
		}
		if (!empty($info['category'][$_GET['category']])) {
			foreach($info['category'][$_GET['category']] as $data) {
				echo "<li><a href='".$data['link']."'>".$data['title']."</a></li>\n";
			}
		}
		echo "</ul>\n";
		echo "</nav>\n";
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
			foreach($info['featured'] as $id => $banner) {
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
					<div id="carousel-example-generic" class="carousel slide" style='max-height:400px;' data-ride="carousel">
						<!-- Indicators -->
						<ol class="carousel-indicators">
							<?php echo $indicator ?>
						</ol>
						<!-- Wrapper for slides -->
						<div class="carousel-inner" role="listbox">
							<?php echo $slides ?>
						</div>

						<!-- Controls -->
						<a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
							<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
							<span class="sr-only">Previous</span>
						</a>
						<a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
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
			foreach($info['featured'] as $id => $banner) {
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
			<div class='list-group-item p-0'>
				<?php echo $cat; ?>
			</div>
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
			foreach($info['featured'] as $id => $banner) {
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
			<div class='list-group-item p-0'>
				<?php echo $cat; ?>
			</div>
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
		echo "<h3>New Arrivals</h3>\n";
		if (!empty($info['item'])) {
			$calculated_bs = col_span(fusion_get_settings('eshop_ipr'), 1);
			echo "<div class='row eshop-rows'>\n";
			$i = 1;
			foreach($info['item'] as $product_id => $item_data) {
				echo "<div class='col-xs-12 eshop-column col-sm-".$calculated_bs." text-center m-t-20 m-b-20'>\n
					<a class='display-inline-block' style='margin:0px auto; min-height:".(fusion_get_settings('eshop_image_th')*1.1)."px;' href='".$item_data['link']."'>\n
						<img class='img-responsive' src='".$item_data['picture']."' style='width:".fusion_get_settings('eshop_image_tw')."px; max-height:".fusion_get_settings('eshop_image_th')."px;'/>
					</a>
					<div class='panel-body' style='min-height:100px;'>

							<div class='text-left'>
							<a href='".$item_data['link']."'><span class='eshop-product-title'>".$item_data['title']."</span></a><br/>";
				if ($item_data['xprice']) {
					echo "
							<span class='eshop-price'>
							<small>".fusion_get_settings('eshop_currency')."</small>".number_format($item_data['xprice'])."</span>
							<span class='eshop-discount label label-danger'>".number_format(100-($item_data['xprice']/$item_data['price']*100))."% ".$locale['off']."</span>
							<br/>
							<span class='eshop-xprice'><small>".fusion_get_settings('eshop_currency')."</small>".number_format($item_data['price'])."</span>\n";
				} else {
					echo "
							<span class='eshop-price'><small>".fusion_get_settings('eshop_currency')."</small>".number_format($item_data['price'])."</span>\n";
				}
				echo "<div>\n";
				echo "</div>\n";
				echo "</div>\n";
				echo "</div>\n";

				echo "<div class='panel-footer text-smaller text-left'>\n";
				echo "<a class='text-lighter text-dark strong' href=''><i class='fa fa-shopping-cart m-r-10 m-t-5'></i>BUY NOW</a>";
				echo "<a class='text-lighter text-dark strong pull-right' href=''><i class='m-t-5 fa fa-heart m-r-10'></i></a>";
				echo "</div>\n";

				echo "</div>\n";
				$i++;
			}
			echo "</div>\n";
		}
	}
}