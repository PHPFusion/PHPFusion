<?php

render_cart();
// now do the cart
add_to_head("
<style>
.cart-bar {
    display: block;
    min-height:50px;
    position: fixed;
    z-index: 5;
    padding: 25px;
    top:0;
    background: rgba(0,0,0,0.85);
    height: 100%;
    width:300px;
    right:0;
    -webkit-transition: all 0.5s ease-in-out ;
    -moz-transition: all 0.5s ease-in-out ;
    -ms-transition: all 0.5s ease-in-out ;
    -o-transition: all 0.5s ease-in-out ;
    transition: all 0.5s ease-in-out ;

}

.cart-bar.open {
    right: -300px;
}
.cart-tab {
    display: inline-block;
    background: rgba(0,0,0,0.85);
    border: 1px solid transparent;
    border-radius: 5px 0 0 5px;
    padding: 15px 20px;
    top: 25%;
    left: -69px;
    position: absolute;
}
</style>
");

add_to_jquery("
    $('.cart-tab').bind('click', function(e) {
        $('#cart').toggleClass('open');
    });
");

/* add to cart form actions */
add_to_jquery("
	$('#add_cart').bind('click', function() {
		var sendData = $('#productfrm').serialize();
		//console.log(sendData);
		$.ajax({
			url: '".INFUSIONS."eshop_cart_panel/cart.ajax.php',
			type: 'POST',
			dataType: 'html',
			data : sendData,
			success: function(result){
				console.log(result);

				//$('#prw-".$input_id."Preview').html(result);
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

");

function render_cart() {
	echo "<div id='cart' class='cart-bar open'>
	<a class='cart-tab pointer' title='Your cart is current empty' class='display-inline-block'>
	<i class='fa fa-shopping-cart fa-lg m-r-10 m-t-5'></i>
    </a>
    aaaaa
	</div>\n";
    //<span class='strong'>MY CART</span>: 0 ITEMS - ".fusion_get_settings('eshop_currency')." 0.00

}

?>