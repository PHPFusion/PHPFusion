<?php

require_once "../../../maincore.php";

// print_r($_POST);

/* we verify by comparing intial calcs vs return response, this is only needed on form returns, we only return response.
		$hashSecretWord = 'MjAwMjAyMjgtYjI5Yi00MTg3LTg5NjQtNjg4NDY1ZDM0NGNj';
        $hashSid = '901333413';
        $hashTotal = '10.00';
        $hashOrder = '9093733900149';
		$key = '9655B5E4B123D6FCE6B764F9F736FB00';
        $StringToHash = strtoupper(md5($hashSecretWord . $hashSid . $hashOrder . $hashTotal));
        if ($StringToHash != $key) {
            echo "fail";
        } else {
            echo "success";
        }	
*/
$token = "";

if (iMEMBER) {

if (isset($_POST['wallet'])) {
	//check if the user exist and verify it first of all
	if (fusion_get_userdata('user_id') && dbcount('(wallet_id)', DB_USER_WALLET, "user_id='".fusion_get_userdata('user_id')."'")) {
		
		$order_total = stripinput($_POST['order_total']);

		$walletdata =  dbarray(dbquery("SELECT balance FROM ".DB_USER_WALLET." WHERE user_id = '".fusion_get_userdata('user_id')."'"));
		$wallet_amount = $walletdata['balance'];

		if ($wallet_amount >= $order_total) {
			$new_balance = $wallet_amount-$order_total;
			dbquery("UPDATE ".DB_USER_WALLET." SET balance = '".$new_balance."' WHERE user_id='".fusion_get_userdata('user_id')."'");
			$data['status'] = 1;
			$data['total'] = $order_total; 
		} else {
			$data['status'] = 0;
		}
	} else {
		$data['status'] = 0;
	}
		
} else {

	$token = stripinput($_POST['token']);
	$product = stripinput($_POST['product']);
	 
	require_once INFUSIONS."wallet/drivers/Twocheckout/Twocheckout.php";

	Twocheckout::privateKey('C0C598DB-CC04-4988-A720-E7C646CCF0A6');
	Twocheckout::sellerId('901333413'); 
	Twocheckout::sandbox(true); 
	// Twocheckout::verifySSL(false);  // this is set to true by default

	try {
		$charge = Twocheckout_Charge::auth(array(
			"merchantOrderId" => $product,
			"token" => $token,
			"currency" => 'USD',
			"total" => stripinput($_POST['order_total']),
			"billingAddr" => array(
				"name" => stripinput($_POST['name_on_card']),
				"addrLine1" => stripinput($_POST['address']['0'])." ".stripinput($_POST['address']['1']),
				"city" => stripinput($_POST['address']['4']),
				"state" => stripinput($_POST['address']['3']),
				"zipCode" => stripinput($_POST['address']['5']),
				"country" => stripinput($_POST['address']['2']),
				"email" => stripinput($_POST['email']),
				"phoneNumber" => stripinput($_POST['phone'])
			),
			"shippingAddr" => array(
				"name" => stripinput($_POST['name_on_card']),
				"addrLine1" => stripinput($_POST['address']['0'])." ".stripinput($_POST['address']['1']),
				"city" => stripinput($_POST['address']['4']),
				"state" => stripinput($_POST['address']['3']),
				"zipCode" => stripinput($_POST['address']['5']),
				"country" => stripinput($_POST['address']['2']),
				"email" => stripinput($_POST['email']),
				"phoneNumber" => stripinput($_POST['phone'])
			)
		), 'array');
		if ($charge['response']['responseCode'] == 'APPROVED') {
			$data['status'] = 1;
			$data['total'] = $charge['response']['total'];
			$data['token'] = $token;
			$data['response'] = $charge['response'];
		}
	} catch (Twocheckout_Error $e) {

	//	print_r($charge);
		$data['status'] = 0;
		$data['token'] = $token;
		$data['response'] = $e->getMessage();;
	}
}

echo json_encode($data);

}