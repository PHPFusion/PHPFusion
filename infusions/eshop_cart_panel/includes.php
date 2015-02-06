<?php
// functions for cart

class Cart {

	public function __construct() {
		global $locale;
		add_to_head("<link href='".INFUSIONS."eshop_cart_panel/css/styles.css' media='all' rel='stylesheet' type='text/css' />\n");
		add_to_jquery("
   		$('.cart-tab').bind('click', function(e) {
        $('#cart').toggleClass('open');
    	});
		function deleteItem() {
			$('.remove-cart-item').bind('click', function() {
				var Data = {
				'usr': '".defender::set_sessionUserID()."',
				'val' : $(this).data('value'),
				'clr' : $(this).data('color'),
				'prid' : $(this).data('product'),
				'cdyn' : $(this).data('cdyn'),
				'time' : $(this).data('added')
				};
				var sendData = $.param(Data);
				$.ajax({
					url: '".INFUSIONS."eshop_cart_panel/cart_remove.ajax.php',
					type: 'POST',
					dataType: 'json',
					data : sendData,
					success: function(result){
						$('#cart').addClass('open');
						if (result.response == 1) {
							$('#product-'+Data['prid']+'-'+Data['cdyn']+'-'+Data['clr']+'-'+Data['time']).remove();
							new PNotify({
							title: 'Item Removed',
							text: 'You have removed item to your cart.',
							icon : 'notify_icon n-gift',
							animation: 'fade',
							width: 'auto',
							delay: '2500',
							});
						} else {
							new PNotify({
							title: 'Product Cannot be Removed (Code 2)',
							text: 'There are error in processing your request. Please contact the Site Admin.',
							icon: 'notify_icon n-attention',
							animation: 'fade',
							width: 'auto',
							delay: '3000'
						});
						}

					},
					error: function(result) {
						new PNotify({
							title: 'Error File',
							text: 'There are error in processing your request. Please contact the Site Admin.',
							icon: 'notify_icon n-attention',
							animation: 'fade',
							width: 'auto',
							delay: '3000'
						});
					}
					});
				});
			}

    	// add action
    	$('#add_cart').bind('click', function() {
		var sendData = $('#productfrm').serialize();
		$.ajax({
			url: '".INFUSIONS."eshop_cart_panel/cart.ajax.php',
			type: 'POST',
			dataType: 'html',
			data : sendData,
			success: function(result){
				$('#cart').addClass('open');
				$('#cart-list').append(result);
				deleteItem();
				new PNotify({
				title: 'Item Added',
				text: 'You have added item to your cart.',
				icon : 'notify_icon n-gift',
				animation: 'fade',
				width: 'auto',
				delay: '2500',
				});
			},
			error: function(result) {
				new PNotify({
					title: 'Error File',
					text: 'There are error in processing your request. Please contact the Site Admin.',
					icon: 'notify_icon n-attention',
					animation: 'fade',
					width: 'auto',
					delay: '3000'
				});
			}
		});
		});
		deleteItem();
		");
	}

	static function add_to_cart($data) {
		// when $data is inserted
		if ($data) {
			$product_in_session = dbquery("SELECT * FROM ".DB_ESHOP_CART." WHERE
			puid='".defender::set_sessionUserID()."'
			AND prid='".intval($data['prid'])."'
			AND cdyn='".$data['cdyn']."'
			AND cclr = '".$data['cclr']."'
			");
			if (dbrows($product_in_session)>0) {
				// there is product in current session so just update the same cart record
				$_sdata = dbarray($product_in_session);
				$old_time = $_sdata['cadded'];
				$_sdata['cqty'] = $_sdata['cqty']+$data['cqty']; // update the quantity
				$_sdata['cadded'] = time(); // update the time
				dbquery_insert(DB_ESHOP_CART, $_sdata, 'update', array('keep_session'=>1));
				$data = $_sdata; // override entire data str.
				echo "<script>$('#product-".$data['prid']."-".$data['cdyn']."-".$data['cclr']."-".$old_time."').remove(); </script>\n";
			} else {
				dbquery_insert(DB_ESHOP_CART, $data, 'save', array('keep_session'=>1));
				$data['tid'] = dblastid();
			}
			self::cart_list_item($data);
			$subtotal = self::get_cart_total($data['puid']);
			echo "<script>
			$('#subtotal_price').text(parseFloat('".$subtotal."'));
			$('.cart-blank').remove();
			</script>\n";
		}
	}

	static function cart_list_item(array $cart_data) {
		global $locale;
		echo "<li id='product-".$cart_data['prid']."-".$cart_data['cdyn']."-".$cart_data['cclr']."-".$cart_data['cadded']."'>\n";
		echo "<div class='pull-left m-r-10'>\n";
		$path = "./eshop/pictures/".$cart_data['cimage'];
		echo "<img class='img-responsive' src='$path' />\n";
		echo "</div>\n";
		echo "<div class='overflow-hide'>\n";
		echo "<button title='".$locale['delete']."' data-product='".$cart_data['prid']."' data-cdyn='".$cart_data['cdyn']."' data-color='".$cart_data['cclr']."' data-added='".$cart_data['cadded']."' data-value='".$cart_data['tid']."' type='button' class='remove-cart-item pull-right'><i class='fa fa-remove'></i></button>\n";
		echo "<a class='display-block product-title' href='".BASEDIR."eshop.php?product=".$cart_data['prid']."'>".$cart_data['citem']."</a>";
		echo "<div class='display-block text-smaller'><span id='qty'>".$cart_data['cqty']."</span> x ".fusion_get_settings('eshop_currency')." <span id='unit-price'>".number_format($cart_data['cprice'], 2)."</span></div>\n";
		echo "</div>\n";
		echo "</li>\n";
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

	static function render_cart() {
		global $locale;
		$puid = defender::set_sessionUserID();
		$result = dbquery("SELECT * FROM ".DB_ESHOP_CART." WHERE puid='$puid'");
		$cart_total = number_format(0, 2);
		if (dbrows($result)>0) {
			$cart_total = self::get_cart_total($puid);
		}
		echo "<div id='cart' class='cart-bar'>\n";
		echo "<a class='cart-tab pointer' title='Your cart is current empty' class='display-inline-block'><i class='fa fa-shopping-cart fa-lg m-r-10 m-t-5'></i></a>\n";
		echo "<h4><i class='fa fa-shopping-cart m-r-10'></i> My Cart</h4>";
		echo "<div class='m-b-20'>\n";
		echo "<div class='heading'><span>Cart Subtotal:</span> ".fusion_get_settings('eshop_currency')." <span id='subtotal_price'>".$cart_total."</span></span>\n</div>\n";
		echo "<a class='btn btn-sm m-t-10 ".fusion_get_settings('eshop_cart_color')."' href='".BASEDIR."eshop.php?checkout'>Checkout</a>\n";
		echo "</div>\n";
		echo "<h4>Recently added item</h4>\n";
		// ok now load the cart as final step. and show rows.
		echo "<ul id='cart-list'>\n";
		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				self::cart_list_item($data);
			}
		} else {
			echo "<li class='text-smaller cart-blank'>There are no items in your cart</li>\n";
		}
		echo "</ul>\n";
		echo "</div>\n";
	}
}




?>