<?php
use PayPal\Api\ShipmentTracking;
use PayPal\Api\AccountBalance;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__  . '/PayPal-PHP-SDK/autoload.php';

include_once 'config.php';

$ppaccid = "60dec254ae73c95da63e7cc3";

//=============================
// Get Paypal Account information
$get_pp_credential_tool_url = "https://" . MULTI_PAYPAL_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-paypal-checkout-payment/get-paypal-credential";

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
        'log.FileName' => dirname(__FILE__) . '/logs/PayPal.log',
        'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
        'cache.enabled' => true,
    )
    );
//=============================

// Add Trackings
// $shipment_tracking = new ShipmentTracking();

// $tracker = array( 'transaction_id' => '06T73283FC395731E',
//     'tracking_number' => '280848528311',
//     'status' => 'SHIPPED',
//     'carrier' => 'FEDEX');

// $trackings = array($tracker);

// try{
//     $shipment_tracking->addTrackings($trackings, $apiContext);
    
// }catch(Exception $e){
//     echo $e->getCode() . ": " . $e->getMessage();
// }

// Check balance
$account_balance = new AccountBalance();

try{
    $balance_accounts = $account_balance->getBalanceAccounts($apiContext);
    
    print_r($balance_accounts);
}catch(Exception $e){
    echo $e->getCode() . ": " . $e->getMessage();
}