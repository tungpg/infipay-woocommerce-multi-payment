<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


/**
 * TungPG Mod - Script for Multi Paypal Payment Gateway plugin
 */

require __DIR__  . '/PayPal-PHP-SDK/autoload.php';

use PayPal\Api\FlowConfig;
use PayPal\Api\InputFields;
use PayPal\Api\Presentation;
use PayPal\Api\WebProfile;

//=============================
$ppaccid = $_POST["ppaccid"];
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

//=============================
// TODO Get order information

//=============================
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

// Set default landing page to billing
$flow = new FlowConfig();
$flow->setLandingPageType('Billing');

$presentation = new Presentation();

$inputFields = new InputFields();
$inputFields->setAddressOverride(1);

$webProfile = new WebProfile();
$webProfile->setName(uniqid())
    ->setFlowConfig($flow)
    ->setPresentation($presentation)
    ->setInputFields($inputFields);

try {
    $webProfile->create($apiContext);
} catch (\PayPal\Exception\PayPalConnectionException $ex) {
    echo json_encode([
        'error' => $ex->getData(),
    ]);
    exit(1);
} catch (Exception $ex) {
    echo json_encode([
        'error' => $ex->getMessage(),
    ]);
    exit(1);
}

echo json_encode([
    'success' => 1,
]);