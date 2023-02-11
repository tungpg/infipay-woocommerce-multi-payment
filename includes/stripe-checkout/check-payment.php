<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'config.php';

/**
 * TungPG Mod - Script for Multi Stripe Payment Gateway plugin
 */

require __DIR__  . '/stripe-php-master/init.php';


//=============================
$staccid = $_POST["staccid"];
//=============================
// Get Stripe Account information
$get_pp_credential_tool_url = "https://" . MULTI_STRIPE_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-stripe-checkout-payment/get-stripe-credential";

// $options = array(
//     'http' => array(
//         'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//         'method'  => 'POST',
//         'content' => http_build_query([
//             'staccid' => $staccid,
//             'testmode_enabled' => 'no',
//         ])
//     )
// );
// $context  = stream_context_create($options);
// $api_response = file_get_contents($get_pp_credential_tool_url, false, $context);

//================
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $get_pp_credential_tool_url);
curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_POSTFIELDS,
    http_build_query(array('staccid' => $staccid, "testmode_enabled" => "no")));

// Receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$api_response = curl_exec($ch);

curl_close ($ch);
//================

$stripe_credential_object = (object)json_decode( $api_response, true );

if(isset($stripe_credential_object->error)){
    echo json_encode([
        'error' => 'Could not get Stripe Credential!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

// Get the information value
// $stripe_publishable_key = $stripe_credential_object->publishable_key;
$stripe_secret_key = $stripe_credential_object->secret_key;

//=============================
// Set API key
try {
    $stripe = new \Stripe\StripeClient(
        $stripe_secret_key
        );
    $stripe->checkout->sessions->create([
        'success_url' => 'https://example.com/success',
        'cancel_url' => 'https://example.com/cancel',
        'payment_method_types' => ['card'],
        'line_items' => [
            [
                'price_data' => [
                    'product_data' => [
                        'name' => "Dress",
                    ],
                    'unit_amount' => '1000',
                    'currency' => 'USD',
                ],
                'quantity' => 2,
            ],
        ],
        'mode' => 'payment',
    ]);
}catch(Exception $e) {
    $api_error = $e->getMessage();
    
    echo json_encode([
        'error' => $api_error,
    ]);
    exit(1);
}

echo json_encode([
    'success' => 1,
]);