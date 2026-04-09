<?php
// Stripe autoloads when referenced
use PHPFusion\Infusions\Wallet\Classes\Wallet;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Transaction;
use PHPFusion\Infusions\Wallet\Drivers\Stripe\Stripe_Driver;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

require_once __DIR__.'/../../../../maincore.php';
require_once INCLUDES."ajax_include.php";
header_content_type("json");
if (!defined("STOP_REDIRECT")) {
    define("STOP_REDIRECT", true);
}

// now we need to get the stripe driver config
$wallet = new Wallet();
$stripe = new Stripe_Driver();

$config = $stripe->get_config();
Stripe::setApiKey($config['stripe_api_secret']);

// ============================================================
// Utility functions
// ============================================================
function json_error($msg) {
    return json_encode([
        "error" => [
            "message" => $msg,
        ],
    ]);
}

// ====================================================================
// Step 1: Make sure we received correct parameters from the frontend
// ====================================================================
if (isset($_SERVER['CONTENT_TYPE']) || isset($_SERVER['HTTP_CONTENT_TYPE'])) {
    if (isset($_SERVER['CONTENT_TYPE'])) {
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            header(400);
            //die(json_error($_SERVER));
            die(json_error("Only JSON requests allowed,  currently is ".$_SERVER['CONTENT_TYPE']));
        }
    }
    if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
        if ($_SERVER['HTTP_CONTENT_TYPE'] !== 'application/json') {
            header(400);
            //die(json_error($_SERVER));
            die(json_error("Only JSON requests allowed, currently is ".$_SERVER['HTTP_CONTENT_TYPE']));
        }
    }
} else {
    die(json_error('Header Request Error'));
}

$json_obj = json_decode(file_get_contents('php://input'));
if (!$json_obj) {
    die(json_error("Could not parse JSON request"));
}

// We expect the frontend to send an object like this:
// {
//   payment_method_id: "pm_123…",
//   firstname: "customer first name",
//   lastname: "customer last name",
//   email: "customer email"
// }
//
// Let's verify that all of that data is present before continuing.
//
if (!isset($json_obj->payment_method_id)) {
    die(json_error("No payment_method_id provided"));
} else if (!isset($json_obj->transaction->items)) {
    die(json_error("No Items provided"));
} else if (!isset($json_obj->transaction->user)) {
    die(json_error("No User provided"));
} else if (!isset($json_obj->transaction->payment_id)) {
    die(json_error("No payment id provided"));
}

// TODO: here you want to check that the user is logged in, etc
if (!iMEMBER) {
    die(json_error('User must be login.'));
}
//
//if (!isset($_SESSION["cart_total"])) {
//    die(json_error("Please add something to your cart."));
//}

// ====================================================================
// Step 2: Create or update customer
// ====================================================================
// might have more than 1 number.. we need to create an extra database in driver.
$userdata = $wallet->getUserWallet(fusion_get_userdata('user_id'), TRUE);
$customer = NULL;
if (!empty($userdata['stripe_cid'])) {
    // Customer already exists, update
    try {
        $customer = Customer::update(
            $userdata['stripe_cid'],
            [
                'name'     => $userdata['user_firstname'].' '.$userdata['user_lastname'],
                'email'    => $userdata['user_email'],
                'address'  => [
                    'line1'       => $userdata["street"],
                    'line2'       => $userdata["street2"],
                    'city'        => $userdata["city"],
                    'state'       => $userdata["region"],
                    'country'     => $userdata["country"],
                    'postal_code' => $userdata["postcode"],
                ],
                'phone'    => '+('.$userdata["mobile_prefix"].') '.$userdata["mobile"],
                'metadata' => [
                    'firstname' => $userdata['user_firstname'],
                    'lastname'  => $userdata['user_lastname'],
                    'user_id'   => $userdata['user_id'],
                ],
            ]
        );
    } catch (ApiErrorException $e) {
        set_error(E_CORE_ERROR, $e->getMessage(), $e->getFile(), $e->getLine());
        die(json_error($e->getMessage()));
    }
    $customer_id = $userdata["stripe_cid"];
    //$customer_id = $user_wallet['stripe_cid'];
} else {
    // Customer doesn't exist yet, create
    try {
        $customer = Customer::create([
            'id'       => $userdata['user_id'],
            'name'     => $userdata['user_firstname'].' '.$userdata['user_lastname'],
            'email'    => $userdata['user_email'],
            'address'  => [
                'line1'       => $userdata["street"],
                'line2'       => $userdata["street2"],
                'city'        => $userdata["city"],
                'state'       => $userdata["region"],
                'country'     => $userdata["country"],
                'postal_code' => $userdata["postcode"],
            ],
            'phone'    => '+('.$userdata["mobile_prefix"].') '.$userdata["mobile"],
            'metadata' => [
                'firstname' => $userdata['user_firstname'],
                'lastname'  => $userdata['user_lastname'],
                'user_id'   => $userdata['user_id'],
            ],
        ]);

        $customer_id = $customer->id;
        dbquery("UPDATE ".DB_USER_WALLET." SET stripe_cid=:cid WHERE wallet_id=:wid", [':cid' => (int)$customer_id, ':wid' => (int)$userdata['wallet_id']]);

    } catch (ApiErrorException $e) {
        set_error(E_CORE_ERROR, $e->getMessage(), $e->getFile(), $e->getLine());
    }
}

// ====================================================================
// Step 3: Determine final price
// ====================================================================
$order_names = [];
// Just create transaction here.
// set the transaction as per cron.
// if transaction given ref.
$transaction = new Wallet_Transaction();
$transaction_ref = "";
$transaction_number = "";
if (!empty($json_obj->transaction_ref)) {
    $_transaction = $transaction->getRef($json_obj->transaction_ref);
    $tdata = $transaction->transactionData();
    // update the transaction method
    $transaction_id = $tdata['transaction_id'];
    $transaction_ref = $tdata["transaction_ref"];
    $transaction_number = $tdata["transaction_number"];
}

//else {
//    // Save transaction if it doesn't exist.
//    $transaction->datestamp = time();
//    $transaction->user = $userdata["user_id"];
//    $transaction->checkout_url = $json_obj->transaction->return_url;
//    $transaction->custom_amount = (float)sanitizer($json_obj->transaction->custom_amount);
//    foreach ($json_obj->transaction->items as $item) {
//
//        if ($item->price === "custom") {
//            $item->price = $json_obj->transaction->custom_amount;
//        }
//
//        $transaction->order_data[] = [
//            'order_item_id'       => $item->id,
//            'order_item_type'     => $item->type,
//            'order_item_value'    => $item->price,
//            'order_item_quantity' => $item->quantity,
//            'order_title'         => $item->title,
//            'order_description'   => $item->description,
//            "order_currency"      => $item->currency,
//            "order_item_cycle"    => $item->cycle,
//            "order_item_interval" => $item->interval,
//            'order_options'       => fusion_encode($item)
//        ];
//    }
//    // Save transaction
//    $transaction_id = $transaction->save(FALSE);
//    $_transaction = $transaction->get($transaction_id);
//    $tdata = $transaction->transactionData();
//    // update the transaction method
//    $transaction_id = $tdata['transaction_id'];
//    $transaction_ref = $tdata["transaction_ref"];
//    $transaction_number = $tdata["transaction_number"];
//}

// fetch the transaction data
if (!$transaction_ref || !$transaction_number) {
    die(json_error("Error creating or updating transaction - $transaction_ref, $transaction_number"));
}

$item_meta = [];
$tdata = [];
if ($transaction->getRef($transaction_ref)) {
    $tdata = $transaction->transactionData();
    //        echo json_encode($tdata);
    $item_meta = [
        'Transaction ID'   => $tdata['transaction_id'],
        'Transaction Ref'  => $tdata['transaction_ref'],
        'Transaction Hash' => $tdata['transaction_number']
    ];
    //$orders = $transaction->getOrders();
    foreach ($transaction->getOrders() as $order_id => $item) {
        $item_meta['Order ID #'.$order_id] = $item['order_title'].' - $'.$item['order_total'];
        $order_names[] = $item['order_title'];
    }
} else {
    die(json_error('Transaction is not found.'));
}

// ====================================================================
// Step 4: Create PaymentIntent and confirm
// ====================================================================

$metadata = [
        "first_name"        => $json_obj->transaction->user->user_firstname,
        "last_name"         => $json_obj->transaction->user->user_lastname,
        'integration_check' => 'accept_a_payment',
    ] + $item_meta;

$payment_intent = [
    'description'         => implode(', ', $order_names),
    'payment_method'      => $json_obj->payment_method_id,
    'amount'              => (int)(number_format($tdata['transaction_amount'], 0, '', '') * 100),
    'currency'            => strtolower($tdata['transaction_currency']),
    'customer'            => $customer,
    'metadata'            => $metadata,
    'receipt_email'       => $json_obj->transaction->user->user_email,
    'confirmation_method' => 'manual',
    'save_payment_method' => TRUE,
    'setup_future_usage'  => 'off_session',
    'confirm'             => TRUE,
];

try {

    $intent = PaymentIntent::create($payment_intent);

    $_SESSION['payment_intent_id'] = $intent->id;

    if ($intent->status === 'requires_action' && $intent->next_action->type === 'use_stripe_sdk') {
        # Tell the client to handle the action
        echo json_encode([
            'payment_intent_id'            => $intent->id,
            'requires_action'              => TRUE,
            'payment_intent_client_secret' => $intent->client_secret,
            "transaction_ref"              => $transaction_ref,
            "token"                        => $transaction_number,
            'transaction_url'              => WALLET.'confirmation.php?payment_method=stripe&token='.$tdata['transaction_number'].'&transaction_ref='.$tdata['transaction_ref']
        ], JSON_PRETTY_PRINT);


    } else if ($intent->status == 'succeeded') {
        # The payment didn’t need any additional actions and completed!
        # Handle post-payment fulfillment
        // here we need to log the request to db transaction for the intent id.
        // store the payment intent to transaction response
        echo json_encode([
            'payment_intent_id' => $intent->id,
            'success'           => TRUE,
            "transaction_ref"   => $transaction_ref,
            "token"             => $transaction_number,
            'transaction_url'   => WALLET.'confirmation.php?payment_method=stripe&token='.$tdata['transaction_number'].'&transaction_ref='.$tdata['transaction_ref']
        ], JSON_PRETTY_PRINT);


    } else {
        # Invalid status
        http_response_code(500);
        echo json_encode([
            'payment_intent_id' => $intent->id,
            'error'             => 'Invalid PaymentIntent status',
            "transaction_ref"   => $transaction_ref,
            "token"             => $transaction_number,
            'transaction_url'   => WALLET.'confirmation.php?payment_method=stripe&token='.$tdata['transaction_number'].'&transaction_ref='.$tdata['transaction_ref']
        ]);
    }

    $_SESSION['transaction_ref'] = $transaction_ref;

} catch (ApiErrorException $e) {
    set_error(E_CORE_ERROR, $e->getMessage(), $e->getFile(), $e->getLine());
}
