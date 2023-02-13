<?php 

/**
 * TungPG Mod - Script for Multi Paypal Payment Gateway plugin
 */

require __DIR__  . '/PayPal-PHP-SDK/autoload.php';

$sbm = $_GET['sbm'];
$app_order_id = $_GET['app_order_id'];

$redirect_url = "";

// Start Redirect Back to Paypal
// $token = $_GET['token'];
// $paypal_domain = "";

// if($sbm){
//     $paypal_domain = "www.sandbox.paypal.com";
// }else{
//     $paypal_domain = "www.paypal.com";
// }

// $redirect_url = 'https://' . $paypal_domain . '/cgi-bin/webscr?cmd=_express-checkout&token=' . $token;
// End Redirect Back to Paypal

//===================

// Redirect back to main shop checkout page
// Get order information
$get_pp_credential_tool_url = "https://" . MULTI_PAYPAL_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-paypal-checkout-payment/get-order-info";

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
        'error' => 'Could not get PP Credential!'
    ]);
    exit(1);
}

// Get the information value
$shop_domain = $result_object->shop_domain;

$redirect_url = "https://$shop_domain/checkout/";

// Redirect user back to paypal page
header('Location: ' . $redirect_url);
