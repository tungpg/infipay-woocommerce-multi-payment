<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

include_once 'config.php';
require __DIR__  . '/PayPal-PHP-SDK/autoload.php';

$ppaccid = $_GET["ppaccid"];
$app_order_id = $_GET['appoid'];
$paymentId = $_GET['paymentId'];
$payerId = $_GET['PayerID'];

if(!isset($ppaccid)  || !isset($app_order_id) || !isset($paymentId) || !isset($payerId)){
    echo json_encode([
        'error' => 'Invalid params!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

// Get order information
$get_pp_credential_tool_url = "https://" . MULTI_PAYPAL_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-paypal-payment/get-order-info";

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query([
            'app_order_id' => $app_order_id,
        ])
    )
);
$context  = stream_context_create($options);
$api_response = file_get_contents($get_pp_credential_tool_url, false, $context);

$result_object = (object)json_decode( $api_response, true );

if(isset($result_object->error)){
    echo json_encode([
        'error' => 'Could not get App Order!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

// Get the information value
$shop_domain = $result_object->shop_domain;
$order_json = $result_object->order_json;

$app_order = json_decode($order_json);

// Accept payment
//=============================
// Get Paypal Account information
$get_pp_credential_tool_url = "https://" . MULTI_PAYPAL_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-paypal-payment/get-paypal-credential";

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query([
            'ppaccid' => $ppaccid,
        ])
    )
);
$context  = stream_context_create($options);
$api_response = file_get_contents($get_pp_credential_tool_url, false, $context);

$paypal_credential_object = (object)json_decode( $api_response, true );

if(isset($paypal_credential_object->error)){
    echo json_encode([
        'error' => 'Could not get PP Credential!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

// Get the information value
$pp_client_id = $paypal_credential_object->client_id;
$pp_client_secret = $paypal_credential_object->client_secret;

$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        $pp_client_id,     // ClientID
        $pp_client_secret      // ClientSecret
        )
    );

$apiContext->setConfig(
    array(
        'mode' => (bool)$paypal_credential_object->is_sandbox? 'sandbox' : 'live',
        'log.LogEnabled' => true,
        'log.FileName' => './logs/PayPal.log',
        'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
        'cache.enabled' => true,
    )
    );
//=============================

// Get payment object by passing paymentId
$payment = Payment::get($paymentId, $apiContext);

// Execute payment with payer ID
$execution = new PaymentExecution();
$execution->setPayerId($payerId);

$transaction_id = null;

try {
    // Execute payment
    $result = $payment->execute($execution, $apiContext);
    
    
    // Get transaction id
    $transactions = $payment->getTransactions();
    $relatedResources = $transactions[0]->getRelatedResources();
    $sale = $relatedResources[0]->getSale();
    $transaction_id = $sale->getId();
    
} catch (PayPal\Exception\PayPalConnectionException $ex) {
    die($ex);
} catch (Exception $ex) {
    die($ex);
}

// Catch empty transaction id
if(empty($transaction_id)){
    echo json_encode([
        'error' => 'Transaction Id not found!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

// Change the status to processing
$confirm_order_tool_url = "https://" . MULTI_PAYPAL_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-paypal-payment/confirm-order";

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query([
            'transaction_id' => $transaction_id,
            'ppaccid' => $ppaccid,
            'app_order_id' => $app_order_id,
        ])
    )
);
$context  = stream_context_create($options);
$api_response = file_get_contents($confirm_order_tool_url, false, $context);

$result_object = (object)json_decode( $api_response, true );

if(isset($result_object->error)){
    echo json_encode([
        'error' => 'Could not confirm order!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}


// TODO Take note to order

// Redirect buyer back to main shop thank you page
$order_received_url = "https://$shop_domain/checkout/order-received/" . $app_order->shop_order_id . "/?key=" . $app_order->order_key;
header('Location: ' . $order_received_url);
