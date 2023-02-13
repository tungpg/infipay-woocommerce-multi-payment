<?php 

/**
 * TungPG Mod - Script for Multi Paypal Payment Gateway plugin
 */

$app_order_id = $_GET['app_order_id'];

$redirect_url = "";

// Redirect back to main shop checkout page
// Get order information
$get_stripe_credential_tool_url = "https://" . MULTI_STRIPE_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-stripe-checkout-payment/get-order-info";

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
// $api_response = file_get_contents($get_stripe_credential_tool_url, false, $context);

//================
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $get_stripe_credential_tool_url);
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
        'error' => 'Could not get PP Credential!'
    ]);
    exit(1);
}

// Get the information value
$shop_domain = $result_object->shop_domain;

$redirect_url = "https://$shop_domain/checkout/";

// Redirect user back to stripe page
header('Location: ' . $redirect_url);
