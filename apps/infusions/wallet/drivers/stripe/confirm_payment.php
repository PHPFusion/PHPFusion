<?php

use PHPFusion\Infusions\Wallet\Classes\Wallet;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Transaction;
use PHPFusion\Infusions\Wallet\Drivers\Stripe\Stripe_Driver;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

define('FUSION_ALLOW_REMOTE', TRUE);
if (!defined("STOP_REDIRECT")) {
    define("STOP_REDIRECT", true);
}


require_once __DIR__.'/../../../../maincore.php';

header('Content-Type: application/json');

$wallet = new Wallet();

$stripe = new Stripe_Driver();

// ============================================================
// confirm_payment.php -- confirm a PaymentIntent sent by the
// browser. handleAction() in the frontend Javascript makes an
// AJAX request to this file if the user completed an SCA action
// such as 3D Secure.
// ============================================================
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
//   payment_intent_id: "pi_123…",
// }
//
// Let's verify that all of that data is present before continuing.
//
if (!isset($json_obj->payment_intent_id)) {
    die(json_error("No Stripe payment_intent_id provided"));
}

if ($_SESSION["payment_intent_id"] !== $json_obj->payment_intent_id) {
    die(json_error("Payment intent ID passed doesn't match session."));
}

// ====================================================================
// Interjection: Check if transaction is paid from secureCardAction, skip the bottom
// ====================================================================
$transaction = new Wallet_Transaction();

if ($transaction->get($json_obj->transaction_ref)) {

    $tdata = $transaction->transactionData();

    if ($tdata['transaction_status'] === TRANSACTION_PAID) {
        echo json_encode([
            'success'         => TRUE,
            'transaction_url' => fusion_get_settings('siteurl').str_replace('../', '', $json_obj->transaction_url)
        ]);
        die();
    }
}

// ====================================================================
// Step 2: Confirm the PaymentIntent
// ====================================================================
//
// At this point, we already created the customer and calculated the order
// total in create_payment.php. Now, we just need to retrieve the PaymentIntent
// and confirm() it again.

// on normal card, we cannot confirm.
try {

    $intent = PaymentIntent::retrieve($json_obj->payment_intent_id);

    try {

        $intent->confirm();

    } catch (InvalidRequestException $err) {
        // if already being confirmed.
        //print_P($intent);
        if ($intent->status !== 'succeeded') {
            die(json_error($err->getMessage()));
        }

    } catch (CardException $err) {
        die(json_error($err->getMessage()));

    } catch (ApiErrorException $err) {
        die(json_error($err->getMessage()));

    }
} catch (ApiErrorException $err) {
    die(json_error($err->getMessage()));

}

if ($intent->status == 'requires_action' && $intent->next_action->type == 'use_stripe_sdk') {
    # Tell the client to handle the action
    $response = [
        'requires_action'              => TRUE,
        'payment_intent_client_secret' => $intent->client_secret,
        'token'                        => $json_obj->token,
        'transaction_ref'              => $json_obj->transaction_ref,
        'payment_intent_id'            => $json_obj->payment_intent_id,
        'transaction_url'              => $json_obj->transaction_url,
    ];


    echo json_encode($response);

    $transaction = new Wallet_Transaction();
    $transaction->getRef($json_obj->transaction_ref);
    $transaction->logTransactionResponse($response);
    $transaction->setPayment(TRANSACTION_PENDING, time(), fusion_get_userdata("user_id"));

} else if ($intent->status == 'succeeded') {
    # The payment didn’t need any additional actions and completed!
    # Handle post-payment fulfillment
    $response = [
        'success'           => TRUE,
        'token'             => $json_obj->token,
        'transaction_ref'   => $json_obj->transaction_ref,
        'payment_intent_id' => $json_obj->payment_intent_id,
        'transaction_url'   => $json_obj->transaction_url,
    ];
    echo json_encode($response);

    $status = TRANSACTION_PAID;
    $transaction = new Wallet_Transaction();
    $transaction->getRef($json_obj->transaction_ref);
    $transaction->logTransactionResponse($response);
    $transaction->setPayment(TRANSACTION_PAID, time(), fusion_get_userdata("user_id"));

} else {
    # Invalid status
    http_response_code(500);
    $response = [
        'error' => 'Invalid PaymentIntent status'
    ];
    echo json_encode($response);
    $transaction = new Wallet_Transaction();
    $transaction->getRef($json_obj->transaction_ref);
    $transaction->logTransactionResponse($response);
    $transaction->setPayment(TRANSACTION_PENDING, time(), fusion_get_userdata("user_id"));
    die(json_error("Invalid Payment Intent"));
}
