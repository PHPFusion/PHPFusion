<?php
header("Cache-Control: no-cache");
header("Pragma: nocache");
header('Content-type: application/json');
require_once "../../../maincore.php";

//print_r($_POST);

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

$order_total = ""; // just re-calc all the way down.
$token = "";
$product_type = "";
$product = "";
$payment_method = "";
$order_id = "";

if (isset($_POST['wallet'])) {
	$payment_method = "Wallet";
} else {
	$payment_method = "Credit Card";
}

/* Reserved Item types */ 
// Item type 1 = SSL
// Item type 2 = Domain Transfer
// Item type 3 = Domain
// Item type 4 = Domain ID Protection
// Item type 5 = Addon
// Item type 6 = Hosting
// Item type 7 = License
// Item type 8 = Roadmap
// Item type 99 = Manual Invoice


if (isset($_POST['order_item_type'])) {
	$product_type = stripinput($_POST['order_item_type']);
}

if (isset($_POST['product'])) {
	$product = stripinput($_POST['product']);
}

if (isset($_POST['order_id'])) {
	$order_id = stripinput($_POST['order_id']);
}

if (iMEMBER) {
	// For manually added invoices
	if ($product_type == "99") {
		$order_total = stripinput($_POST['order_total']);
	}
	// Required for transaction tracking
	include INFUSIONS."wallet/wallet_functions.php";
	
	if ($product_type == "3") {
		$odata = dbarray(dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_user='".fusion_get_userdata('user_id')."' AND order_id='".intval($_POST['order_id'])."' LIMIT 0,1"));
		$order_total = $odata['order_total'];
		$years = $odata['order_item_quantity'];
		
		// Get domain expire date
		include INFUSIONS."pf_domains/infusion_db.php";
		$domainData = dbarray(dbquery("SELECT * FROM ".DB_PFD." WHERE pfd_uid='".fusion_get_userdata('user_id')."' AND pfd_oid='".intval($_POST['order_info'])."'"));
		$expire = $domainData['pfd_end_datestamp'];
	}
	
	if ($product_type == "1") {
		$odata = dbarray(dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_user='".fusion_get_userdata('user_id')."' AND order_id='".intval($_POST['order_id'])."' LIMIT 0,1"));
		$order_total = $odata['order_total'];
	}

// Initiate user requested Domain restoration new price calcs are required
	if (isset($_POST['restore_domain'])) {
	
		//Include the TLD database, get prices, allowed years etc..
		include INFUSIONS."pf_tlds/infusion_db.php";
		$tld_data = dbarray(dbquery("SELECT * FROM ".DB_PFT." WHERE pft_active = '1' AND pft_tld='".stripinput($_POST['tld'])."'"));
		
	   if ($tld_data) {
			$domain_price = str_replace(',', '.', $tld_data['pft_restore_price']);
			$order_total = $domain_price;
			$years = stripinput($_POST['years']);
		
			if ($years == "1") {
				$order_total = $domain_price;
			} elseif ($years == "2") {
				$order_total = $domain_price * 2;
			} elseif ($years == "3") {
				$order_total = $domain_price * 3;
			} elseif ($years == "4") {
				$order_total = $domain_price * 4;
			} elseif ($years == "5") {
				$order_total = $domain_price * 5;
			} elseif ($years == "6") {
				$order_total = $domain_price * 6;
			} elseif ($years == "7") {
				$order_total = $domain_price * 7;
			} elseif ($years == "8") {
				$order_total = $domain_price * 8;
			} elseif ($years == "9") {
				$order_total = $domain_price * 9;
				// Set a silly high default if any form of manipulations sneaks in.
			} else {
				$order_total = "10000";
			}
		}	
	}

// Initiate user requested Domain rejuvenation new price calcs are required
	if (isset($_POST['rejuvenate'])) {
	
		//Include the TLD database, get prices, allowed years etc..
		include INFUSIONS."pf_tlds/infusion_db.php";
		$tld_data = dbarray(dbquery("SELECT * FROM ".DB_PFT." WHERE pft_active = '1' AND pft_tld='".stripinput($_POST['tld'])."'"));
		
		if ($tld_data) {
			$domain_price = str_replace(',', '.', $tld_data['pft_renew_price']);
			$years = stripinput($_POST['years']);
		
			if ($years == "1") {
				$order_total = $domain_price;
			} elseif ($years == "2") {
				$order_total = $domain_price * 2;
			} elseif ($years == "3") {
				$order_total = $domain_price * 3;
			} elseif ($years == "4") {
				$order_total = $domain_price * 4;
			} elseif ($years == "5") {
				$order_total = $domain_price * 5;
			} elseif ($years == "6") {
				$order_total = $domain_price * 6;
			} elseif ($years == "7") {
				$order_total = $domain_price * 7;
			} elseif ($years == "8") {
				$order_total = $domain_price * 8;
			} elseif ($years == "9") {
				$order_total = $domain_price * 9;
				// Set a silly high default if any form of manipulations sneaks in.
			} else {
				$order_total = "10000";
			}
		}
	}

	// Initiate user requested Domain rejuvenation new price calcs are required
	if (isset($_POST['newidprotection'])) {
	
		$years = stripinput($_POST['years']);
		$price = 10; // static
			
		if ($years == "1") {
			$order_total = $price;
		} elseif ($years == "2") {
			$order_total = $price * 2;
		} elseif ($years == "3") {
			$order_total = $price * 3;
		} elseif ($years == "4") {
			$order_total = $price * 4;
		} elseif ($years == "5") {
			$order_total = $price * 5;
		} elseif ($years == "6") {
			$order_total = $price * 6;
		} elseif ($years == "7") {
			$order_total = $price * 7;
		} elseif ($years == "8") {
			$order_total = $price * 8;
		} elseif ($years == "9") {
			$order_total = $price * 9;
			// Set a silly high default if any form of manipulations sneaks in.
		} else {
			$order_total = "10000";
		}
	}

	
function IDprotectDomain($orderid,$years,$order_total) {
global $payment_method;

	include BASEDIR."hosting/functions.php";
	include INFUSIONS."pf_domains/infusion_db.php";
	
	// Invoke LogicBoxes
	$Invoke_LB = LB_Call();

	// Load the command class we want to use
	$Invoke_LB->loadCommand("logicboxes_domains");
	$manage_domain = new LogicboxesDomains($Invoke_LB);

	$params = array(
		'order-id' => $orderid,
		'invoice-option' => 'NoInvoice'
	);

	// Enable the ID Protection
	$idprotection = $manage_domain->purchaseprivacy($params)->response();
	
	// It glithed on some domains
	$idPinvoiceid = "";
	
	if (isset($idprotection->eaqid) && $idprotection->eaqid !="") {
		$idPinvoiceid = $idprotection->eaqid;
	}

	 // print_p($idprotection);
	
	$params = array(
				'order-id' => $orderid,
				'protect-privacy' => "true",
				'reason' => "Requested by Domain owner",
			);
			
	$enable_id_protection = new LogicboxesDomains($Invoke_LB);
	$LB_Response = $enable_id_protection->modifyPrivacyProtection($params)->response();
	
	// print_r($LB_Response);
	
	// Update the domain database
	dbquery("UPDATE ".DB_PFD." SET pfd_privacy_prot='1', pfd_purchased_privacy='1' WHERE pfd_oid ='".intval($orderid)."' AND pfd_uid='".fusion_get_userdata('user_id')."'");

	// Insert protect data to domain IDP database
	dbquery("INSERT INTO ".DB_PFD_IDP." VALUES('$orderid','".time()."','".strtotime(+"".format_word($years, 'year|years'))."','".time()."','".intval($idPinvoiceid)."')");

	// Get domain name.
	include INFUSIONS."pf_domains/infusion_db.php";
	$domainData = dbarray(dbquery("SELECT * FROM ".DB_PFD." WHERE pfd_uid='".fusion_get_userdata('user_id')."' AND pfd_oid='".intval($orderid)."'"));

	// Insert ID Protection order to the database
	dbquery("INSERT INTO ".DB_WALLET_ORDERS." VALUES 
	('', 
	0, 
	'$payment_method', 
	'', 
	'".fusion_get_userdata('user_id')."', 
	'Domain ID Protection', 
	'".$domainData['pfd_domain']." - ".format_word($years, 'year|years')."', 
	'".TIME()."', 
	0, 
	0, 
	1, 
	'".TIME()."', 
	'".fusion_get_userdata('user_id')."', 
	0, 
	1, 
	'".fusion_get_userdata('user_id')."', 
	'".TIME()."', 
	1, 
	'4',
	0, $years, 'N', 0, 'N', 0, 0,
	$order_total,'".intval($idPinvoiceid)."',
	0)");
	
	// Track the transaction
	walletTransaction(fusion_get_userdata('user_id'), "Domain ID Protection", $order_total, "Insert", "Wallet", intval($idPinvoiceid), fusion_get_userdata('user_ip'), "1");

}

function renewDomain($orderid,$years,$expdate) {
	include BASEDIR."hosting/functions.php";

	// Invoke LogicBoxes
	$Invoke_LB = LB_Call();

	// Load the command class we want to use
	$Invoke_LB->loadCommand("logicboxes_domains");
	
	//Get details of LB epochtime
	$domaindetails = new LogicboxesDomains($Invoke_LB);

	$params = array(
		'order-id' => $orderid,
		'options' => 'OrderDetails',
	);

	$expire = $domaindetails->details($params)->response();
		
	$registered_expire = $expire->endtime;
	
	$manage_domain = new LogicboxesDomains($Invoke_LB);

	$params = array(
		'order-id' => $orderid,
		'years' => $years,
		'exp-date' => $registered_expire,
		'invoice-option' => 'NoInvoice',
	);

	$rejuvenate = $manage_domain->renew($params)->response();

	$pluralism = format_word($years, 'Year|Years');
	$nextduedate = strtotime("+$years $pluralism", $registered_expire); 
	
	dbquery("UPDATE ".DB_PFD." SET pfd_end_datestamp = '".$nextduedate."', pfd_updated_datestamp = '".TIME()."'  WHERE pfd_oid ='".intval($_POST['order_info'])."' AND pfd_uid='".fusion_get_userdata('user_id')."'");

	// print_r($rejuvenate);
}

function restoreDomain($orderid,$years,$expdate) {
	include BASEDIR."hosting/functions.php";

	// Invoke LogicBoxes
	$Invoke_LB = LB_Call();

	// Load the command class we want to use
	$Invoke_LB->loadCommand("logicboxes_domains");
	
	$manage_domain = new LogicboxesDomains($Invoke_LB);

	$params = array(
		'order-id' => $orderid,
		'invoice-option' => 'NoInvoice',
	);

	$restoreDomain = $manage_domain->restore($params)->response();

	$pluralism = format_word($years, 'Year|Years');
	$nextduedate = strtotime("+$years $pluralism",$expdate); 
	
	dbquery("UPDATE ".DB_PFD." SET pfd_end_datestamp = '".$nextduedate."', pfd_updated_datestamp = '".TIME()."' WHERE pfd_oid ='".intval($_POST['order_info'])."' AND pfd_uid='".fusion_get_userdata('user_id')."'");

	// print_r($restoreDomain);
}

function RenewSSL($orderid,$order_total) {
global $payment_method;

	include BASEDIR."hosting/functions.php";
	include INFUSIONS."pf_ssl/infusion_db.php";
	

	// Create a new LogicBox instance 
		$Invoke_LB = LB_Call();

	// Load new set of commands to use
		$Invoke_LB->loadCommand("logicboxes_ssl");
		
		$renew_ssl = new LogicboxesSSL($Invoke_LB);		
		$params = array(
			'order-id' => $orderid,
			'months' => '12',
			'invoice-option' => 'NoInvoice'
		);

	$LB_Response = $renew_ssl->renew($params)->response();

	// print_p($LB_Response); Need to get back to this one on sharp mode.

	// Get ssl expire date
	include INFUSIONS."pf_ssl/infusion_db.php";
	$sslData = dbarray(dbquery("SELECT * FROM ".DB_PF_SSL." WHERE pfssl_uid='".fusion_get_userdata('user_id')."' AND pfssl_invoiceid='".$orderid."'"));
	$ssl_expire_date= strtotime('+12 months',$sslData['pfssl_end_datestamp']); 
	
	// Update the SSL database
	dbquery("UPDATE ".DB_PF_SSL." SET pfssl_end_datestamp='".$ssl_expire_date."',pfssl_updated_datestamp = '".TIME()."' WHERE pfssl_invoiceid='".intval($orderid)."' AND pfssl_uid='".fusion_get_userdata('user_id')."'");

	// Track the domain price transaction
	walletTransaction(fusion_get_userdata('user_id'),"SSL Purchase","$order_total","Insert",$payment_method,$orderid,fusion_get_userdata('user_ip'),"1");
}
		
	if (isset($_POST['wallet'])) {
		//check if the user exist and verify it first of all
		if (fusion_get_userdata('user_id') && dbcount('(wallet_id)', DB_USER_WALLET, "user_id='".fusion_get_userdata('user_id')."'")) {
			
			$walletdata =  dbarray(dbquery("SELECT balance FROM ".DB_USER_WALLET." WHERE user_id = '".fusion_get_userdata('user_id')."'"));
			$wallet_amount = $walletdata['balance'];
			
			if ($wallet_amount >= $order_total) {
				$new_balance = $wallet_amount-$order_total;
				dbquery("UPDATE ".DB_USER_WALLET." SET balance = '".$new_balance."' WHERE user_id='".fusion_get_userdata('user_id')."'");
				$data['status'] = 1;
								
				// Initiate user requested Domain rejuvenation
				if (isset($_POST['rejuvenate'])) {
					renewDomain(intval($_POST['order_info']),intval($years),intval($_POST['domain_expire']));
					
				// Restore domain function
				} else if (isset($_POST['restore_domain'])) {
				
					restoreDomain(intval($_POST['order_info']),intval($years),intval($_POST['domain_expire']));
					dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_paid = '1', order_paid_datestamp = '".time()."' WHERE order_id='".intval($_POST['order_id'])."'");
			
				// Pay a Domain invoice
				} else if ($product_type == "3") {
					// Update the order to paid
					dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_paid = '1', order_paid_datestamp = '".time()."' WHERE order_id='".intval($_POST['order_id'])."'");

					// Fire renewal function
					renewDomain(intval($_POST['order_info']),intval($years),intval($expire));
				
				} else if (isset($_POST['newidprotection'])) {
					IDprotectDomain(intval($_POST['order_info']),$years,$order_total);
				// Pay SSL Renewal
				} else if ($product_type == "1") {
				RenewSSL(intval($_POST['order_info']),$order_total);
				dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_paid = '1', order_paid_datestamp = '".time()."' WHERE order_id='".intval($_POST['order_id'])."'");
		
				} else if ($product_type == "99") {
					dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_paid = '1', order_paid_datestamp = '".time()."' WHERE order_id='".intval($_POST['order_id'])."'");
				}

				// Only Track the transaction if not purchasing new ID Protection, we need the new invoice ID from Logicboxes
				if (!isset($_POST['newidprotection'])) {
					walletTransaction(fusion_get_userdata('user_id'), $product, $order_total, "Insert", "Wallet", intval($_POST['order_info']), fusion_get_userdata('user_ip'), "1");
				}
			} else {
				$data['status'] = 0;
			}
		} else {
			$data['status'] = 0;
		}
			
	} else {

		$token = stripinput($_POST['token']);
		
		// include INFUSIONS."wallet/infusion_db.php";
		$cdata = dbarray(dbquery("SELECT * FROM ".DB_USER_WALLET." WHERE user_id='".fusion_get_userdata('user_id')."'"));
		
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
				"addrLine1" => $cdata['address'],
				"city" => $cdata['city'],
				"state" => $cdata['region'],
				"zipCode" => $cdata['postcode'],
				"country" => $cdata['country'],
				"email" => $cdata['email'],
				"phoneNumber" => $cdata['phone']
			),
			"shippingAddr" => array(
				"name" => stripinput($_POST['name_on_card']),
				"addrLine1" => $cdata['address'],
				"city" => $cdata['city'],
				"state" => $cdata['region'],
				"zipCode" => $cdata['postcode'],
				"country" => $cdata['country'],
				"email" => $cdata['email'],
				"phoneNumber" => $cdata['phone']
			)
		), 'array');
			if ($charge['response']['responseCode'] == 'APPROVED') {
				$data['status'] = 1;
				$data['total'] = $charge['response']['total'];
				$data['token'] = $token;
				$data['response'] = $charge['response'];
				
				// Initiate user requested Domain rejuvenation
				if (isset($_POST['rejuvenate'])) {
					renewDomain(intval($_POST['order_info']),intval($years),intval($_POST['domain_expire']));
					
				// Restore domain function
				} else if (isset($_POST['restore_domain'])) {
				
					restoreDomain(intval($_POST['order_info']),intval($years),intval($_POST['domain_expire']));
					dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_paid = '1', order_paid_datestamp = '".time()."' WHERE order_id='".intval($_POST['order_id'])."'");
			
				// Pay a Domain invoice
				} else if ($product_type == "3") {
					// Update the order to paid
					dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_paid = '1', order_paid_datestamp = '".time()."' WHERE order_id='".intval($_POST['order_id'])."'");

					// Fire renewal function
					renewDomain(intval($_POST['order_info']),intval($years),intval($expire));
				
				} else if (isset($_POST['newidprotection'])) {
					IDprotectDomain(intval($_POST['order_info']),$years,$order_total);
				// Pay SSL Renewal
				} else if ($product_type == "1") {
				RenewSSL(intval($_POST['order_info']),$order_total);
				dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_paid = '1', order_paid_datestamp = '".time()."' WHERE order_id='".intval($_POST['order_id'])."'");
				} else if ($product_type == "99") {
					dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_paid = '1', order_paid_datestamp = '".time()."' WHERE order_id='".intval($_POST['order_id'])."'");
				}
				
				// Only Track the transaction if not purchasing new ID Protection, we need the new invoice ID from Logicboxes
				if (!isset($_POST['newidprotection'])) {
					walletTransaction(fusion_get_userdata('user_id'), $product, $order_total, "Insert", "Credit Card", intval($_POST['order_info']), fusion_get_userdata('user_ip'), "1");
				}
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