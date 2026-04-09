<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: checkout_include_invoice.php
| Author: J.Falk (Falk)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

// Always set a product type for Credit Card checkout
echo "<input type='hidden' name='product' id='product' value='".$product."' />\n";

// Always pass cart total for Credit Card checkout
echo "<input type='hidden' name='order_total' id='order_total' value='".$cart_total."' />\n";

// Set token placeholder
echo "<input type='hidden' name='token' id='token' value='' />\n";

// Pass to injections based on selection
echo "<input type='hidden' name='payment_method' id='payment_method' value='Wallet' />\n";

echo "<div class='row'>
<div class='col-xs-12'>
<div class='panel panel-default'>
	<div class='panel-heading'>
		<h3 class='panel-title'>Choose Payment Method</h3>
	</div>
		<div class='panel-body'>";

// Block 1
	echo "<div class='row'>";

	echo "<div class='col-xs-12 col-md-12'>";
	
	echo "<div class='well'>\n";
    echo "<input class='payment' type = 'radio' name = 'creditcard' id = 'creditcard' /> Credit Card <img src='".INFUSIONS."wallet/images/cards/Visa.png' style='width:35px; height:20px;' /> <img src='".INFUSIONS."wallet/images/cards/Mastercard.png' style='width:35px; height:20px;' /> <img src='".INFUSIONS."wallet/images/cards/Discover-Network.png' style='width:35px; height:20px;' /> <img src='".INFUSIONS."wallet/images/cards/Diners-Club.png' style='width:35px; height:20px;' /> <img src='".INFUSIONS."wallet/images/cards/JCB.png' style='width:35px; height:20px;' /> <img src='".INFUSIONS."wallet/images/cards/2co.png' style='width:35px; height:20px;' />";
    echo "</div>\n";
	
	echo "</div>\n";

// Block 2
	echo "<div class='col-xs-12 col-md-12'>";
	
	echo "<div class='well'>\n";
    echo "<input class='payment' type = 'radio' name = 'wallet' id = 'wallet' checked='checked' /> Fusion Wallet";
    echo "</div>\n";
	
	echo "</div>\n";
	
	echo "</div>\n";

	echo "
 	<!-- CREDIT CARD FORM STARTS HERE -->
<div class='spacer-xs'></div>

<div id='creditcard_div' class='panel panel-default credit-card-box customwidth center-block' style='display:none;'>
	<div class='panel-heading'>
		<h3 class='panel-title'>Credit Card Details</h3>
	</div>
				
                <div class='panel-body'>
					<div class='row'>
						<div class='col-xs-12 form-group'>
							<label class='control-label'>Name on Card</label>
							<input name='name_on_card' 
							placeholder='The written name on Card'
							class='form-control' size='4' type='text'>
						</div>
						</div>
                        <div class='row'>
                            <div class='col-xs-12'>
                                <div class='form-group'>
									<label class='control-label'>Card Number</label>
                                    <div class='input-group'>
                                        <input type='text' class='form-control' 
										    name='ccNo'
											id='ccNo'
                                            placeholder='Valid Card Number'
                                            autocomplete='off'
                                        />
                                        <span class='input-group-addon'><i class='fa fa-credit-card' id='cardlogo' style='color:purple;font-size:2rem;'></i></span>
                                    </div>
                                </div>                            
                            </div>
                        </div>
                       <div class='control-group'>
                <label label-default='' class='control-label'>Card Expiry</label>
                <div class='controls'>
                    <div class='row'>
                        <div class='col-md-9'>
                            <select class='form-control' id='expMonth' name='expMonth'>
                                <option value='01'>January (01) </option>
                                <option value='02'>February (02) </option>
                                <option value='03'>March (03) </option>
                                <option value='04'>April (04) </option>
                                <option value='05'>May (05) </option>
                                <option value='06'>June (06) </option>
                                <option value='07'>July (07) </option>
                                <option value='08'>August (08) </option>
                                <option value='09'>September (09) </option>
                                <option value='10'>October (10) </option>
                                <option value='11'>November (11) </option>
                                <option value='12'>December (12) </option>
                            </select>
                        </div>
                        <div class='col-md-3'>
                            <select class='form-control' name='expYear' id='expYear'>";
								for ($i=date("Y", strtotime('-10 years'));$i<=date("Y", strtotime('+10 years'));$i++) echo "<option ".(date("Y") == $i ? " selected='selected'" : "").">".$i."</option>\n";
                            echo "</select>
                        </div>
                    </div>
                </div>
            </div>
            <div class='control-group spacer-xs'>
                <label label-default='' class='control-label'>Card CVC/CVV</label>
                <div class='controls'>
                    <div class='row'>
                        <div class='col-md-4'>
                            <input type='text' class='form-control' name='cvv' id='cvv' autocomplete='off' placeholder='CVC/CVV'>
                        </div>
                        <div class='col-md-8'><img src='".INFUSIONS."wallet/images/cvv.png' style='height: 50px;'/></div>
                    </div>
                </div>
            </div>

                </div>
            </div>            
            <!-- CREDIT CARD FORM ENDS HERE -->
";


echo "
 	<!-- WALLET FORM STARTS HERE -->
<div class='spacer-xs'></div>

<div id='wallet_div' class='panel panel-default credit-card-box customwidth center-block'>
	<div class='panel-heading'>
		<h3 class='panel-title'>Wallet</h3>
	</div>";

$walletdata =  dbarray(dbquery("SELECT balance FROM ".DB_USER_WALLET." WHERE user_id = '".fusion_get_userdata('user_id')."'"));

echo "<p class='spacer-xs'><h4 class='p-15 text-center'> You have ".number_format($walletdata['balance'], 2)."$ USD in your Wallet </h4></p>";

echo "<!-- WALLET FORM ENDS HERE -->";
	
echo "</div></div>";

// Payment div header
echo "</div></div></div>";

echo "<div id='payment_status' style='display:none;'>";

echo "<div class='row'>
<div class='col-xs-12'>
<div class='panel panel-default'>
	<div class='panel-heading'>
		<h3 class='panel-title'>Payment Status</h3>
	</div>
		<div class='panel-body'>";
echo "<div class='well'><div id='payment_results'></div></div>";

echo "</div>";
echo "</div>";
echo "</div></div></div>";


echo "<script type='text/javascript'>
		
		$(\"[name='ccNo']\").change(function(){
		var input = $(\"[name='ccNo']\");
		if(input.val().substring(0, 1) == 4) {
			$('#cardlogo').addClass('fa-cc-visa');
		}
		if(input.val().substring(0, 2) == 34 || input.val().substring(0, 2) == 37) {
			$('#cardlogo').addClass('fa-cc-amex');
		}
		if(input.val().substring(0, 2) == 51 || input.val().substring(0, 2) == 52 || input.val().substring(0, 2) == 53 || input.val().substring(0, 2) == 54 || input.val().substring(0, 2) == 55) {
			$('#cardlogo').addClass('fa-cc-mastercard');
		}
		if(input.val().substring(0, 4) == 6011 || input.val().substring(0, 2) == 65) {
			$('#cardlogo').addClass('fa-cc-discover');
		}
		else if(input.val().length === 0) {
			$('#cardlogo').removeClass('fa-cc-visa fa-cc-amex fa-cc-mastercard fa-cc-discover');
		};
});
		
</script>";

echo '<script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.min.js"></script>';

echo '<script type="text/javascript">
	// sleep time expects milliseconds
	function sleep (time) {
	  return new Promise((resolve) => setTimeout(resolve, time));
	}
	
	// Called when token created successfully.
	var successCallback = function(data) {
		$("#token").val(data.response.token.token);
	};

	// Called when token creation fails.
	var errorCallback = function(data) {
		if (data.errorCode === 200) {
			tokenRequest();
		} else {
			alert(data.errorMsg);
		}
	};

	var tokenRequest = function() {
	// Setup token request arguments
		var args = {
			sellerId: "901333413",
			publishableKey: "B4B8467A-F969-48AE-A7F3-7E0BA0950BC2",
			ccNo: $("#ccNo").val(),
			cvv: $("#cvv").val(),
			expMonth: $("#expMonth").val(),
			expYear: $("#expYear").val()
		};

	// Make the token request
		TCO.requestToken(successCallback, errorCallback, args);
	};

	$(function() {
	// Pull in the public encryption key for our environment
		TCO.loadPubKey("sandbox");
	});
	
</script>';
	
	
echo "<script type='text/javascript'>

	$(\"[name='ccNo']\").change(function(){
		var input = $(\"[name='ccNo']\");
		if(input.val().substring(0, 1) == 4) {
			$('#cardlogo').addClass('fa-cc-visa');
		}
		if(input.val().substring(0, 2) == 34 || input.val().substring(0, 2) == 37) {
			$('#cardlogo').addClass('fa-cc-amex');
		}
		if(input.val().substring(0, 2) == 51 || input.val().substring(0, 2) == 52 || input.val().substring(0, 2) == 53 || input.val().substring(0, 2) == 54 || input.val().substring(0, 2) == 55) {
			$('#cardlogo').addClass('fa-cc-mastercard');
		}
		if(input.val().substring(0, 4) == 6011 || input.val().substring(0, 2) == 65) {
			$('#cardlogo').addClass('fa-cc-discover');
		}
		else if(input.val().length === 0) {
			$('#cardlogo').removeClass('fa-cc-visa fa-cc-amex fa-cc-mastercard fa-cc-discover');
		};
	});
	
	$('input.payment').on('change', function() {
		$('input.payment').not(this).prop('checked', false);
	});

	$('#creditcard').on('click',function(){
		 $('#wallet_div').hide();
		 $('#creditcard_div').show();
		 $('#payment_method').val('Credit Card');
	});
	
	$('#wallet').on('click',function(){
		 $('#creditcard_div').hide();
		 $('#wallet_div').show();
		 $('#payment_method').val('Wallet');
	});


    $('body').on('click', '#submit-btn', function(e) {
        e.preventDefault();
		
		var validation = ValidateForm(postform);
		
		var ccheck = $('input[name=creditcard]');
		var cccheck = ccheck.filter(':checked').val();
		
		if (validation) {
					
			if (cccheck) {
				tokenRequest();
					

					$('#payment_status').show();
					$('#payment_results').html('<img style=\"padding:4px;height:35px;\" src=\"".INFUSIONS."wallet/images/finalize_loader.gif\"> Proccessing Your Payment Request, please wait ...');
						
					sleep(2000).then(() => {
						
						var formData = $('#1337').serialize();
						
						$.ajax({
							type:'POST',
							url:'".INFUSIONS."wallet/checkout/checkout_proccess_invoice.php',
							dataType: 'json',
							cache: false,
							data:formData,
							success:function(data){
								if(data.status == '1'){
										$('#payment_results').html('<img style=\"padding:4px;height:35px;\" src=\"".INFUSIONS."wallet/images/finalize_loader.gif\"> Payment has been successful, please wait while we redirect you to the order finalize ...');
										$('#1337').submit();
								} else {
									$('#payment_results').html('<h3>Payment failed</h3> Please verify that your Credit Card details are entered correctly and try again or try another payment method.');
								}
							}
						});
					});
			} else {
					var formData = $('#1337').serialize();
					
					$.ajax({
						type:'POST',
						url:'".INFUSIONS."wallet/checkout/checkout_proccess_invoice.php',
						dataType: 'json',
						cache: false,
						data:formData,
						beforeSend: function(){  
							$('#payment_status').show();
							$('#payment_results').html('<img style=\"padding:4px;height:35px;\" src=\"".INFUSIONS."wallet/images/finalize_loader.gif\"> Proccessing Your Payment Request, please wait ...');
						},
						success:function(data){
								if(data.status == '1'){
										$('#payment_results').html('<img style=\"padding:4px;height:35px;\" src=\"".INFUSIONS."wallet/images/finalize_loader.gif\"> Payment has been successful, please wait while we redirect you to the order finalize ...');
										$('#1337').submit();
							} else {
								$('#payment_results').html('<h3>Payment failed</h3> Please verify that you have sufficent funds in your Wallet for your order.');
							}
						}
					});
			}			
		}
      });
</script>";