<?php
header("Cache-Control: no-cache");
header("Pragma: nocache");
header('Content-type: application/json');
require_once "../../../maincore.php";
include "../wallet_functions.php";

// print_r($_POST);

$token = "";
$wallet_amount = "0";

if (iMEMBER) {

	$token = stripinput($_POST['token']);
	$product = stripinput($_POST['product']);
	 
	require_once INFUSIONS."wallet/drivers/Twocheckout/Twocheckout.php";

	Twocheckout::privateKey('C0C598DB-CC04-4988-A720-E7C646CCF0A6');
	Twocheckout::sellerId('901333413'); 
	Twocheckout::sandbox(true); 
	// Twocheckout::verifySSL(false);  // this is set to true by default
	
	
	$company = "";
	$firstname = 
	$lastname = "";
	$job_title = "";
	$phone = "";
	$phone_cc = "";
	$fax = "";
	$email = "";
	$dob = "";
	$country_code = "";
	$region = "";
	$city = "";
	$address = "";
	$address2 = "";
	$postcode = "";
	$dob_m = "";
	$dob_d = "";
	$dob_y = "";
	
   if (isset($_POST['address'])) {
		$address = stripinput($_POST['address']['0']);
		$address2 = stripinput($_POST['address']['1']);
		$country_code = stripinput($_POST['address']['2']);
		$region = stripinput($_POST['address']['3']);
		$city = stripinput($_POST['address']['4']);
		$postcode = stripinput($_POST['address']['5']);
	}

    if (isset($_POST['first_name'])) {
        $firstname = stripinput($_POST['first_name']);
    }
    if (isset($_POST['last_name'])) {
        $lastname = stripinput($_POST['last_name']);
    }
    if (isset($_POST['org_name'])) {
        $company = stripinput($_POST['org_name']);
    }
    if (isset($_POST['job_title'])) {
        $job_title = stripinput($_POST['job_title']);
    }
    if (isset($_POST['phone'])) {
        $phone = stripinput($_POST['phone']);
    }
    if (isset($_POST['phone_cc'])) {
        $phone_cc = stripinput($_POST['phone_cc']);
    }
    if (isset($_POST['fax'])) {
        $fax = stripinput($_POST['fax']);
    }
    if (isset($_POST['email'])) {
        $email = stripinput($_POST['email']);
    }
	
	if (isset($_POST['dob_m'])) {
		$dob_m = stripinput($_POST['dob_m']);
	}

	if (isset($_POST['dob_d'])) {
		$dob_d = stripinput($_POST['dob_d']);
	}

	if (isset($_POST['dob_y'])) {
		$dob_y = stripinput($_POST['dob_y']);
	}
	
	$dob = mktime(0,0,0, intval($dob_m), intval($dob_d), intval($dob_y));
	
        //check if the user exist and update with new form values if they do
        if (fusion_get_userdata('user_id') && dbcount('(wallet_id)', DB_USER_WALLET, "user_id='".fusion_get_userdata('user_id')."'")) {
            dbquery("UPDATE ".DB_USER_WALLET." SET
			company = '".$company."', 
			first_name = '".$firstname."', 
			last_name = '".$lastname."', 
			company = '".$company."', 
			job_title = '".$job_title."', 
			birthdate = '".$dob."', 
			country = '".$country_code."', 
			region = '".$region."', 
			city = '".$city."', 
			address = '".$address."', 
			address_2 = '".$address2."', 
			postcode = '".$postcode."', 
			phone = '".$phone."', 
			phone_cc = '".$phone_cc."', 
			fax = '".$fax."', 
			email = '".$email."'
			WHERE user_id = '".fusion_get_userdata('user_id')."'");
			
        } else {
		
            $result = dbquery("INSERT INTO ".DB_USER_WALLET." VALUES 
			('',
			'".fusion_get_userdata('user_id')."',
			'', 
			'', 
			'', 
			'".$company."', 
			'".$job_title."',
			'".$firstname."', 
			'".$lastname."' ,
			'".$country_code."' , 
			'".$region."', 
			'".$city."', 
			'".$address."', 
			'".$address2."', 
			'', 
			'".$postcode."', 
			'".$phone."', 
			'".$phone_cc."', 
			'".$fax."', 
			'".$email."', 
			'".$dob."', 
			'', 
			'".TIME()."')");
			
			}

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
			$data['total'] = number_format($charge['response']['total'],0);
			$data['token'] = $token;
			$data['response'] = $charge['response'];

			// Add a double check here to match post vs actual values before updaste system
		if ($data['total'] = $_POST['order_total']) {
			// Track the transaction
			echo walletTransaction(fusion_get_userdata('user_id'),"Wallet Charge",$data['total'],"Insert","Credit Card",fusion_get_userdata('user_ip'),"1");
					
			$walletdata =  dbarray(dbquery("SELECT balance FROM ".DB_USER_WALLET." WHERE user_id = '".fusion_get_userdata('user_id')."'"));
			
			if ($walletdata['balance']) {
				$wallet_amount = $walletdata['balance'];
			}
			
			$new_balance = $wallet_amount+$data['total'];

			// Need to check if the receiver have a wallet account.
			if (fusion_get_userdata('user_id') && dbcount('(wallet_id)', DB_USER_WALLET, "user_id='".fusion_get_userdata('user_id')."'")) {
				dbquery("UPDATE ".DB_USER_WALLET." SET balance = '".$new_balance."' WHERE user_id='".fusion_get_userdata('user_id')."'");
			} else {
				
				dbquery("INSERT INTO ".DB_USER_WALLET." VALUES 
				('',
				'".fusion_get_userdata('user_id')."',
				'', 
				'', 
				'', 
				'', 
				'',
				'', 
				'' ,
				'' , 
				'', 
				'', 
				'', 
				'', 
				'', 
				'', 
				'', 
				'', 
				'', 
				'', 
				'', 
				'".$new_balance."', 
				'".TIME()."')");
			}
			
			//send an order confirmation to the buyer
			require_once INCLUDES."sendmail_include.php";

			//Send us an email message about this order
			$subject = "A Wallet have been charged with ".$data['total']."$ USD";
			$toemail = "sales@php-fusion.co.uk";
			$toname = "PHP-Fusion Sales";
			$message = "A Wallet have been charged<br />
			Best regards <br /> PHP-Fusion Script";
			sendemail($toname,$toemail,fusion_get_settings('sitename'),fusion_get_settings('siteemail'),$subject,$message,$type="html");

			$pm_subject = "You have Charged your Wallet";
			$pm_message = "Hi there,<br /><br />
			Your Old Wallet Amount was : ".$wallet_amount."<br />
			Your New Wallet Amount is : ".$new_balance."<br />
			You can not reply to this automated message.<br /><br /> Regards,<br /> PHP-Fusion Messenger";
			dbquery("INSERT INTO ".DB_MESSAGES." (message_id, message_to, message_from, message_user, message_subject, message_message, message_smileys, message_read, message_datestamp, message_folder) VALUES('', '".fusion_get_userdata('user_id')."', '15756', '".fusion_get_userdata('user_id')."', '$pm_subject', '$pm_message', 'n', '0', '".time()."', '0')");
			}
			
		}
	} catch (Twocheckout_Error $e) {
		$pm_subject = "You have failed to Charged your Wallet";
		$pm_message = "Hi there,<br /><br />
		The attempt to charge your Wallet was not successful.<br />
		You can not reply to this automated message.<br /><br /> Regards,<br /> PHP-Fusion Messenger";
		dbquery("INSERT INTO ".DB_MESSAGES." (message_id, message_to, message_from, message_user, message_subject, message_message, message_smileys, message_read, message_datestamp, message_folder) VALUES('', '".fusion_get_userdata('user_id')."', '15756', '".fusion_get_userdata('user_id')."', '$pm_subject', '$pm_message', 'n', '0', '".time()."', '0')");
		$data['status'] = 0;
		$data['token'] = $token;
		$data['response'] = $e->getMessage();;
	}


echo json_encode($data);

}