<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require __DIR__  . '/stripe-php-master/init.php';

$staccid = $_GET["staccid"];
$app_order_id = $_GET['appoid'];
$session_id = $_GET['session_id'];
$testmode_enabled = $_GET['testmode_enabled'];

if(!isset($staccid)  || !isset($app_order_id) || !isset($session_id) || !isset($testmode_enabled)){
    echo json_encode([
        'error' => 'Invalid params!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

// Get order information
$get_order_info_tool_url = "https://" . MULTI_STRIPE_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-stripe-checkout-payment/get-order-info";

// $options = array(
//     'http' => array(
//         'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//         'method'  => 'POST',
//         'content' => http_build_query([
//             'app_order_id' => $app_order_id,
//         ])
//     )
// );
// $context  = stream_context_create($options);
// $api_response = file_get_contents($get_order_info_tool_url, false, $context);

//================
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $get_order_info_tool_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");

curl_setopt($ch, CURLOPT_POSTFIELDS,
    http_build_query(array('app_order_id' => $app_order_id)));

// Receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$api_response = curl_exec($ch);

curl_close ($ch);
//================

$result_object = (object)json_decode( $api_response, true );

if(isset($result_object->error)){
    echo json_encode([
        'error' => 'Could not get Stripe Credential!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

// Get the information value
$shop_domain = $result_object->shop_domain;
$order_json = $result_object->order_json;

$app_order = json_decode($order_json);
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
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");

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

$stripe_secret_key = $stripe_credential_object->secret_key;

// Get the Payment Id
$stripe = new \Stripe\StripeClient($stripe_secret_key);
$stripe_session = $stripe->checkout->sessions->retrieve($session_id, []);

if($stripe_session->payment_status != 'paid'){
    echo json_encode([
        'error' => 'This order has not been paid yet!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

// Change the status to processing
$confirm_order_tool_url = "https://" . MULTI_STRIPE_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-stripe-checkout-payment/confirm-order";

// $options = array(
//     'http' => array(
//         'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//         'method'  => 'POST',
//         'content' => http_build_query([
//             'payment_id' => $stripe_session->payment_intent,
//             'staccid' => $staccid,
//             'app_order_id' => $app_order_id,
//         ])
//     )
// );
// $context  = stream_context_create($options);
// $api_response = file_get_contents($confirm_order_tool_url, false, $context);

//================
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $confirm_order_tool_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");

curl_setopt($ch, CURLOPT_POSTFIELDS,
    http_build_query(array('payment_id' => $stripe_session->payment_intent, 'staccid' => $staccid, "app_order_id" => $app_order_id)));

// Receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$api_response = curl_exec($ch);

curl_close ($ch);
//================

$result_object = (object)json_decode( $api_response, true );

if(isset($result_object->error)){
    echo json_encode([
        'error' => 'Could not confirm the order!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

// Redirect buyer back to main shop thank you page
$order_received_url = "https://$shop_domain/checkout/order-received/" . $app_order->shop_order_id . "/?key=" . $app_order->order_key;
header('Location: ' . $order_received_url);
