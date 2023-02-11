<?php
include_once 'config.php';
require __DIR__  . '/stripe-php-master/init.php';

$session_id = $_GET['session_id'];
$staccid = $_GET['staccid'];
$app_order_id = $_GET['app_order_id'];
$testmode_enabled = $_GET['testmode_enabled'];

if(empty($session_id) || empty($staccid) || empty($app_order_id)){
    die();
}

//========================
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
        'error' => 'Could not get PP Credential!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

// TEST ALLOW ON ONE ACC
$server_side_redirect = false;
if($staccid == "60b71dfaae73c91a2f51a465"){
    $server_side_redirect = true;
}

// Get the information value
$publishable_key = $stripe_credential_object->publishable_key;
$use_server_redirect_to_stripe_checkout = $stripe_credential_object->use_server_redirect_to_stripe_checkout;

if($use_server_redirect_to_stripe_checkout == "yes"){
    $server_side_redirect = true;
}

if($server_side_redirect){
    $secret_key = $stripe_credential_object->secret_key;
    
    // Get session
    $stripe = new \Stripe\StripeClient($secret_key);
    $checkout_session = $stripe->checkout->sessions->retrieve($session_id, []);
    
    // Redirect to Stripe Checkout
    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);    
}else{
?>
<head>
    <!-- Stripe JavaScript library -->
    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript">
    //Specify Stripe publishable key to initialize Stripe.js
    var stripe = Stripe('<?php echo $publishable_key; ?>');
    
    stripe.redirectToCheckout({
        sessionId: "<?php echo $session_id; ?>",
    });
    </script>
</head>
<body>

</body>
<?php }?>