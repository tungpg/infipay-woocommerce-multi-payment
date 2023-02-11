<?php 
use Stripe\Exception\ApiErrorException;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


/**
 * TungPG Mod - Script for Multi Stripe Payment Gateway plugin
 */

require __DIR__  . '/stripe-php-master/init.php';

//=============================
// Get the post params
$main_shop_name = $_POST["main_shop_name"];
$main_shop_name = preg_replace("/[^a-zA-Z0-9]+/", "", $main_shop_name);
$main_shop_name = strtolower($main_shop_name);
$main_shop_name = str_replace("shop", "", $main_shop_name);
$main_shop_name = str_replace("store", "", $main_shop_name);
$main_shop_name = trim($main_shop_name);

$staccid = $_POST["staccid"];
$app_order_id = $_POST["app_order_id"];
$shop_order_id = $_POST["shop_order_id"];
$order_json = $_POST["order_json"];
$testmode_enabled = $_POST["testmode_enabled"];

if(!isset(
    $main_shop_name) || !isset($staccid) || !isset($shop_order_id) || 
    !isset($order_json) || !isset($app_order_id)
    ){
    echo json_encode([
        'error' => 'Invalid params!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

$shop_domain = $_SERVER['HTTP_HOST'];
$shop_order = json_decode(stripslashes($order_json));

//=============================
// Get Stripe Account information
$get_pp_credential_tool_url = "https://" . MULTI_STRIPE_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-stripe-checkout-payment/get-stripe-credential";

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
// $api_response = file_get_contents($get_pp_credential_tool_url, false, $context);

//================
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $get_pp_credential_tool_url);
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
// $stripe_publishable_key = $stripe_credential_object->publishable_key;
$stripe_secret_key = $stripe_credential_object->secret_key;
$transaction_prefix = $stripe_credential_object->transaction_prefix;
$transaction_item_name_pattern = $stripe_credential_object->transaction_item_name_pattern;
$transaction_description_pattern = $stripe_credential_object->transaction_description_pattern;

if(empty($transaction_item_name_pattern)){
    $transaction_item_name_pattern = "SKU#TRANSACTION_PREFIX-RANDOM_NUMBER-SHOP_NAME-ORDER_NUMBER";
}

if(empty($transaction_description_pattern)){
    $transaction_description_pattern = "TRANSACTION_PREFIXORDER_NUMBER";
}
//=============================
// Set API key
\Stripe\Stripe::setApiKey($stripe_secret_key);

// Item list
$line_items = [];
// $item_count = 0;
// foreach($shop_order->line_items as $line_item){
//     $item_count++;    
    
// //     if($hide_item_title == "yes"){
// //         // $shop_order->shop_order_number . ": Item#" . $item_count
// //         $item_name = "$main_shop_name-Order#$shop_order->shop_order_number#$item_count";
// //     }else{
// //         $item_name = $line_item->name;
// //     }
    
//     $item = [
//         'price_data' => [
//             'product_data' => [
//                 'name' => "$main_shop_name - Order $shop_order->shop_order_number",
//             ],
//             'unit_amount' => round($shop_order->order_total*100, 2),
//             'currency' => $shop_order->currency,
//         ],
//         'quantity' => 1,
//         //         'quantity' => $line_item->quantity,
//     ];
    
//     $line_items[] = $item;    
// }

// Shipping
// $line_items[] = [
//     'price_data' => [
//         'product_data' => [
//             'name' => "Shipping",
//         ],
//         'unit_amount' => round($shop_order->shipping_total*100, 2),
//         'currency' => $shop_order->currency,
//     ],
//     'quantity' => 1,
// ];

$randomNumber = rand(10000000, 999999999);

if(empty($transaction_prefix)){
    // Gen random string
    $randomString = '';
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    for ($i = 0; $i < 3; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    $randomString = strtoupper($randomString);
    
    $transaction_prefix = $randomString;
}

$main_shop_name_1 = str_replace(" ", "", $main_shop_name);
$main_shop_name_1 = strtoupper($main_shop_name_1);    

//TRANSACTION_PREFIX, RANDOM_NUMBER, SHOP_NAME, ORDER_NUMBER
$item_name = $transaction_item_name_pattern;
$item_name = str_replace("TRANSACTION_PREFIX", $transaction_prefix, $item_name);
$item_name = str_replace("RANDOM_NUMBER", $randomNumber, $item_name);
$item_name = str_replace("SHOP_NAME", $main_shop_name_1, $item_name);
$item_name = str_replace("ORDER_NUMBER", $shop_order->shop_order_number, $item_name);
$item_name = str_replace("--", "-", $item_name);

$statement_descriptor = substr($main_shop_name, 0, 21);
$statement_descriptor = str_replace(" ", "", $statement_descriptor);
$statement_descriptor = strtoupper($statement_descriptor);

//TRANSACTION_PREFIX, RANDOM_NUMBER, SHOP_NAME, ORDER_NUMBER
$description = $transaction_description_pattern;
$description = str_replace("TRANSACTION_PREFIX", $transaction_prefix, $description);
$description = str_replace("RANDOM_NUMBER", $randomNumber, $description);
$description = str_replace("SHOP_NAME", $main_shop_name_1, $description);
$description = str_replace("ORDER_NUMBER", $shop_order->shop_order_number, $description);
$description = str_replace("--", "-", $description);

$item = [
    'price_data' => [
        'product_data' => [
            'name' => $item_name,
        ],
        'unit_amount' => round($shop_order->order_total*100, 2),
        'currency' => $shop_order->currency,
    ],
    'quantity' => 1,
    //         'quantity' => $line_item->quantity,
];

$line_items[] = $item;

// Create new Checkout Session for the order
$baseUrl = "https://$shop_domain/stripe-payment";
$success_url = "$baseUrl/accept-payment.php?appoid=$app_order_id&staccid=$staccid";
$cancel_url = "$baseUrl/cancel-payment.php?app_order_id=$app_order_id";

$session = null;
$api_error = null;

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'customer_email' => $shop_order->billing->email,
        'payment_intent_data' => [
            //'description' => "$main_shop_name - Order $shop_order->shop_order_number",
            'description' => $description,
            
            'statement_descriptor' => $statement_descriptor,
            'shipping' => [
                'name' => $shop_order->billing->first_name . ' ' . $shop_order->billing->last_name,
                'phone' => $shop_order->billing->phone,
                'address' => [
                    'city' => $shop_order->shipping->city,
                    'country' => $shop_order->shipping->country,
                    'line1' => $shop_order->shipping->address_1,
                    'line2' => $shop_order->shipping->address_2,
                    'postal_code' => $shop_order->shipping->postcode,
                    'state' => $shop_order->shipping->state,
                ],
            ],
        ],
        'metadata' => [
            "customer_name" => $shop_order->billing->first_name . ' ' . $shop_order->billing->last_name,
            "customer_email" => $shop_order->billing->email,
            "order_id" => $shop_order->shop_order_number,
        ],
        //         'shipping' => [
//             'name' => $shop_order->billing->first_name . ' ' . $shop_order->billing->last_name,
//             'address' => [
//                 'city' => $shop_order->shipping->city,
//                 'country' => $shop_order->shipping->country,
//                 'line1' => $shop_order->shipping->address_1,
//                 'line2' => $shop_order->shipping->address_2,
//                 'postal_code' => $shop_order->shipping->postcode,
//                 'state' => $shop_order->shipping->state,
//             ]
//         ],
        'success_url' => $success_url.'&session_id={CHECKOUT_SESSION_ID}&testmode_enabled=' . $testmode_enabled,
        'cancel_url' => $cancel_url,
    ]);
} catch (Stripe\Exception\AuthenticationException | \Stripe\Exception\PermissionException $e) {
    // AuthenticationException: Authentication with Stripe's API failed
    // (maybe you changed API keys recently)
    $api_error = $e->getMessage();    
    
    // disable this Stripe account to avoid payment blocking error
    $disable_payment_account_tool_url = "https://" . MULTI_STRIPE_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-stripe-checkout-payment/deactivate-stripe-account";
    
//     $options = array(
//         'http' => array(
//             'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//             'method'  => 'POST',
//             'content' => http_build_query([
//                 'staccid' => $staccid,
//                 'error_message' => $api_error,
//             ])
//         )
//     );
//     $context  = stream_context_create($options);
//     $api_response = file_get_contents($disable_payment_account_tool_url, false, $context);

    //================
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $disable_payment_account_tool_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS,
        http_build_query(array('staccid' => $staccid, "error_message" => $api_error)));
    
    // Receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $api_response = curl_exec($ch);
    
    curl_close ($ch);
    //================
    
    echo json_encode([
        'error' => $api_error,
        'show_error_to_buyer' => false,
    ]);
    exit(1);
} catch(ApiErrorException $e) {
    // Display a very generic error to the user, and maybe send yourself an email
    $api_error = $e->getMessage();
    
    echo json_encode([
        'error' => $api_error,
        'show_error_to_buyer' => true,
    ]);
    exit(1);
} catch (Exception $e) {
    // Something else happened, completely unrelated to Stripe
    echo json_encode([
        'error' => "Not Stripe Error!",
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

if(empty($api_error) && $session){
    echo json_encode([
        'session_id' => $session['id']
    ]);
}else{
    // disable this Stripe account to avoid payment blocking error
    $disable_payment_account_tool_url = "https://" . MULTI_STRIPE_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-stripe-checkout-payment/deactivate-stripe-account";
    
//     $options = array(
//         'http' => array(
//             'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//             'method'  => 'POST',
//             'content' => http_build_query([
//                 'staccid' => $staccid,
//                 'error_message' => 'Checkout Session creation failed! ' . $api_error,
//             ])
//         )
//     );
//     $context  = stream_context_create($options);
//     $api_response = file_get_contents($disable_payment_account_tool_url, false, $context);    
    
    //================
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $disable_payment_account_tool_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS,
        http_build_query(array('staccid' => $staccid, "error_message" => 'Checkout Session creation failed! ' . $api_error)));
    
    // Receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $api_response = curl_exec($ch);
    
    curl_close ($ch);
    //================
    
    echo json_encode([
        'error' => 'Checkout Session creation failed! '.$api_error,
        'show_error_to_buyer' => false,
    ]);
}