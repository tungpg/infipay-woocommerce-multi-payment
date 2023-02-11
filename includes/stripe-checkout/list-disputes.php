<?php
require __DIR__  . '/stripe-php-master/init.php';

$staccid = $_POST["staccid"];
$testmode_enabled = $_POST['testmode_enabled'];

if(!isset($staccid) || !isset($testmode_enabled)){
    echo json_encode([
        'error' => 'Invalid params!',
    ]);
    exit(1);
}

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
    ]);
    exit(1);
}

$stripe_secret_key = $stripe_credential_object->secret_key;

// Get the Payment Id
$stripe = new \Stripe\StripeClient($stripe_secret_key);
$stripe_disputes = $stripe->disputes->all(['limit' => 100]);

if(empty($stripe_disputes->data)){
    $stripe_disputes = json_encode(array());
}

echo json_encode($stripe_disputes->data);