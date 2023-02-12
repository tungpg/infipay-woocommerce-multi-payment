<?php
use PayPal\Api\ShipmentTracking;

require __DIR__  . '/PayPal-PHP-SDK/autoload.php';


$ppaccid = $_POST["ppaccid"];
$tracking_number = $_POST["tracking_number"];
$transaction_id = $_POST["transaction_id"];
$carrier = $_POST["carrier"];

if(empty($ppaccid) || empty($tracking_number) || empty($transaction_id) || empty($carrier)){
    echo json_encode([
        'error' => 'Invalid params!',
    ]);
    exit(1);
}

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
$shipment_tracking = new ShipmentTracking();

$supported_carriers = [];
$supported_carriers[] = 'dhl';
$supported_carriers[] = 'dhl express';
$supported_carriers[] = 'fedex';
$supported_carriers[] = 'usps';
$supported_carriers[] = 'ups';

$ocarrier = strtolower($carrier);
$ocarrier = trim($ocarrier);
$carrier_name_other = null;

if(!in_array($ocarrier, $supported_carriers)){
    $carrier_name_other = $carrier;
    $carrier = "OTHER";
}

$tracker = array( 'transaction_id' => $transaction_id,
    'tracking_number' => $tracking_number,
    'status' => 'SHIPPED',
    'carrier' => strtoupper($carrier));

if(!empty($carrier_name_other)){
    $tracker['carrier_name_other'] = $carrier_name_other;
}

$trackings = array($tracker);

try{
    $shipment_tracking->addTrackings($trackings, $apiContext);

    echo json_encode([
        'success' => 1,
    ]);
    exit(1);
}catch(Exception $e){
    echo json_encode([
        'error' => 'Send tracking to Paypal failed! ' . $e->getCode() . ": " . $e->getMessage(),
    ]);
    exit(1);
}