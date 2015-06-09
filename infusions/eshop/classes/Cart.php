<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: Cart.php
| Author: Frederick MC Chan (hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Eshop;
if (!defined("IN_FUSION")) { die("Access Denied"); }
require_once INCLUDES."infusions_include.php";

class Cart {
	public static function loadCart() {
		global $locale;
		add_to_head("<link href='".INFUSIONS."eshop_cart_panel/css/styles.css' media='all' rel='stylesheet' type='text/css' />\n");

		add_to_jquery("
   		$('.cart-tab').bind('click', function(e) {
        $('#cart').toggleClass('open');
    	});

		function deleteItem(tid) {
			$('#remove-'+tid).bind('click', function() {
				var Data = {
				'usr': '".\defender::set_sessionUserID()."',
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
						if (result.response) {
							$('#product-'+Data['prid']+'-'+Data['cdyn']+'-'+Data['clr']+'-'+Data['time']).remove();
							$('#subtotal_price').text(parseFloat(result.subtotal));
							new PNotify({
							title: '".$locale['cart_remove']."',
							text: '".$locale['cart_remove_message']."',
							icon : 'notify_icon n-gift',
							animation: 'fade',
							width: 'auto',
							delay: '2500',
							});
						} else {
							new PNotify({
							title: '".$locale['cart_error_001']."',
							text: '".$locale['cart_error_002']."',
							icon: 'notify_icon n-attention',
							animation: 'fade',
							width: 'auto',
							delay: '3000'
						});
						}
					},
					error: function(result) {
						new PNotify({
							title: '".$locale['cart_error_001']."',
							text: '".$locale['cart_error_002']."',
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
			dataType: 'json',
			data : sendData,
			success: function(data){
				if (!data.error_id) {
					$('#cart').addClass('open');
					$('.cart-blank').remove();
					$('#cart-list').append(data.html);
					$(data.remove).remove();
					if (data.subtotal > 0) {
					$('#subtotal_price').text(parseFloat(data.subtotal));
					} else {
					$('#subtotal_price').text(parseFloat('0'));
					}
					deleteItem(data.tid);
					new PNotify({
						title: data.title,
						text: data.message,
						icon : 'notify_icon n-gift',
						animation: 'fade',
						width: 'auto',
						delay: '2500',
					});
				} else {
					console.log('error happened');
					new PNotify({
						title: data.title,
						text: data.message,
						icon : 'notify_icon n-gift',
						animation: 'fade',
						width: 'auto',
						delay: '2500',
					});
				}
			},
			error: function(result) {
				new PNotify({
					title: '".$locale['cart_error_003']."',
					text: '".$locale['cart_error_002']."',
					icon: 'notify_icon n-attention',
					animation: 'fade',
					width: 'auto',
					delay: '3000'
				});
			}
		});
		});

		// remove cart
		$('.remove-cart-item').on('click', function(e) {
				var Data = {
				'usr': '".\defender::set_sessionUserID()."',
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
							if (result.subtotal > 0) {
							$('#subtotal_price').text(parseFloat(result.subtotal));
							} else {
							$('#subtotal_price').text(parseFloat('0'));
							}
							new PNotify({
							title: '".$locale['cart_remove']."',
							text: '".$locale['cart_remove_message']."',
							icon : 'notify_icon n-gift',
							animation: 'fade',
							width: 'auto',
							delay: '2500',
							});
						} else {
							//console.log(result.data);
							new PNotify({
							title: '".$locale['cart_error_001']."',
							text: '".$locale['cart_error_002']."',
							icon: 'notify_icon n-attention',
							animation: 'fade',
							width: 'auto',
							delay: '3000'
						});
						}
					},
					error: function(result) {
						new PNotify({
							title: '".$locale['cart_error_001']."',
							text: '".$locale['cart_error_002']."',
							icon: 'notify_icon n-attention',
							animation: 'fade',
							width: 'auto',
							delay: '3000'
						});
					}
					});
				});
		");
	}

	/**
	 * Outputs JSON response
	 * @param $data
	 * @return array
	 */
	static function add_to_cart($data) {
		// when $data is inserted
		$json_response = array(
			'remove' => '',
			'tid' => '',
			'html' => '',
			'subtotal' => '',
		);
		if ($data) {
			$product_in_session = dbquery("SELECT * FROM ".DB_ESHOP_CART." WHERE
			puid='".\defender::set_sessionUserID()."'
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
				$data['tid'] = $_sdata['tid'];
				$json_response['remove'] = "#product-".$data['prid']."-".$data['cdyn']."-".$data['cclr']."-".$old_time."";
				$json_response['tid'] = $_sdata['tid'];
			} else {
				dbquery_insert(DB_ESHOP_CART, $data, 'save', array('keep_session'=>1));
				$data['tid'] = dblastid();
				$json_response['tid'] = $data['tid'];
			}
			$subtotal = Eshop::get_cart_total($data['prid']);
			$json_response['html'] = self::cart_list_item($data);
			$json_response['subtotal'] = $subtotal;
			return $json_response;
		}
	}

	static function cart_list_item(array $cart_data) {
		global $locale;
		$html = "<li id='product-".$cart_data['prid']."-".$cart_data['cdyn']."-".$cart_data['cclr']."-".$cart_data['cadded']."'>\n";
		$html .= "<div class='pull-left m-r-10'>\n";
		$image = "./eshop/pictures/album_".$cart_data['prid']."/thumbs/".$cart_data['cimage'];
		$html .= "<img class='img-responsive' src='".$image."' />\n";
		$html .= "</div>\n";
		$html .= "<div class='overflow-hide'>\n";
		$html .= "<button id='remove-".$cart_data['tid']."' title='".$locale['delete']."' data-product='".$cart_data['prid']."' data-cdyn='".$cart_data['cdyn']."' data-color='".$cart_data['cclr']."' data-added='".$cart_data['cadded']."' data-value='".$cart_data['tid']."' type='button' class='remove-cart-item pull-right'><i class='fa fa-remove'></i></button>\n";
		$html .= "<a class='display-block product-title' href='".BASEDIR."eshop.php?product=".$cart_data['prid']."'>".$cart_data['citem']."</a>";
		$html .= "<div class='display-block text-smaller'><span id='qty'>".$cart_data['cqty']."</span> x ".get_settings('eshop_currency')." <span id='unit-price'>".number_format($cart_data['cprice'], 2)."</span></div>\n";
		$html .= "</div>\n";
		$html .= "</li>\n";
		return $html;
	}

	/**
	 * Template for Cart Panel Main HTML Output
	 */
	public static function render_cart() {
		global $locale;
		self::loadCart();
		$puid = \defender::set_sessionUserID();
		$result = dbquery("SELECT * FROM ".DB_ESHOP_CART." WHERE puid='".intval($puid)."'");
		$cart_total = number_format(0, 2);
		if (dbrows($result)>0) {
			$cart_total = Eshop::get_cart_total($puid);
		}
		$color = get_settings('eshop_cart_color');
		switch($color) {
			case 'red':
				$color = 'btn-danger';
				break;
			case 'green':
				$color = 'btn-success';
				break;
			default :
				$color = 'btn-default';
				break;
		}
		echo "<div id='cart' class='cart-bar'>\n";
		echo "<a class='cart-tab pointer' title='".$locale['cart_purchases']."' class='display-inline-block'><i class='fa fa-shopping-cart fa-lg m-r-10 m-t-5'></i></a>\n";
		echo "<h4><i class='fa fa-shopping-cart m-r-10'></i> ".$locale['cart_title']."</h4>";
		echo "<div class='m-b-20'>\n";
		echo "<div class='heading'><span class='display-inline m-r-5'>".$locale['ESHPF131'].":</span>".get_settings('eshop_currency')." <span id='subtotal_price'>".$cart_total."</span></span>\n</div>\n";
		echo "<a class='btn btn-sm button m-t-10 ".($color ? $color : 'btn-success')."' href='".BASEDIR."eshop.php?checkout'>".$locale['check_out']."</a>\n";
		echo "</div>\n";
		echo "<h4></h4>\n";
		// ok now load the cart as final step. and show rows.
		echo "<ul id='cart-list'>\n";
		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				echo self::cart_list_item($data);
			}
		} else {
			echo "<li class='text-smaller cart-blank'>".$locale['cart_empty']."</li>\n";
		}
		echo "</ul>\n";
		echo "</div>\n";
	}
}