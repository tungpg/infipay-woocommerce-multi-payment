<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


/**
 * TungPG Mod - Script for Multi Stripe Payment Gateway plugin
 */

require __DIR__  . '/stripe-php-master/init.php';

$staccid = $_POST["staccid"];
//$address = $_POST["address"];
//$name = $_POST["name"];
$tracking_number = $_POST["tracking_number"];
$transaction_id = $_POST["transaction_id"];
$carrier = $_POST["carrier"];

if(isset($_POST["testmode_enabled"])){
    $testmode_enabled = $_POST["testmode_enabled"];
}

if(empty($testmode_enabled)) $testmode_enabled = 'no';

if(empty($staccid) || empty($tracking_number) || empty($transaction_id) || empty($carrier)){
    echo json_encode([
        'error' => 'Invalid params!',
    ]);
    exit(1);
}

//$address = json_decode($address, true);

//=============================
$staccid = $_POST["staccid"];
//=============================
// Get Stripe Account information
$get_stripe_credential_tool_url = "https://" . MULTI_STRIPE_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-stripe-checkout-payment/get-stripe-credential";

// $options = array(
//     'http' => array(
//         'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//         'method'  => 'POST',
//         'content' => http_build_query([
//             'staccid' => $staccid,
//             'testmode_enabled' => $testmode_enabled,
//         ])
//     )
// );
// $context  = stream_context_create($options);
// $api_response = file_get_contents($get_stripe_credential_tool_url, false, $context);

//================
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $get_stripe_credential_tool_url);
curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_POSTFIELDS,
    http_build_query(array('staccid' => $staccid, "testmode_enabled" => $testmode_enabled)));

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
$stripe_secret_key = $stripe_credential_object->secret_key;
//print_r($stripe_credential_object);
//=============================
// Add Trackings
//=============================
// Set API key
try {
    $stripe = new \Stripe\StripeClient(
        $stripe_secret_key
        );
    $payment_intent = $stripe->paymentIntents->retrieve(
        $transaction_id,
        []
    );
    
//     $stripe->paymentIntents->update(
//         $transaction_id,
//         ['shipping' =>
//             [
//                 'address' => json_decode(json_encode($payment_intent->shipping->address), true),
//                 'name' => $payment_intent->shipping->name,
//                 'carrier' => $carrier,
//                 'tracking_number' => $tracking_number,
//             ]
//         ]
//     );
    
    if(count($payment_intent->charges->data) > 0){
        $charge = $payment_intent->charges->data[0];
        $stripe->charges->update(
            $charge->id,
            ['shipping' =>
                [
                    'address' => json_decode(json_encode($payment_intent->shipping->address), true),
                    'name' => $payment_intent->shipping->name,
                    'carrier' => $carrier,
                    'phone' => $payment_intent->shipping->phone,
                    'tracking_number' => $tracking_number,
                ]
            ]
        );
    }
    
//     $stripe->paymentIntents->update(
//             $transaction_id,
//             ['shipping' => 
//                 [
//                     'address' => $address,
//                     'name' => $name,
//                     'carrier' => $carrier,
//                     'tracking_number' => $tracking_number,
//                 ]
//             ]
//         );
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