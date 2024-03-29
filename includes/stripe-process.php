<?php
require __DIR__ . '/stripe-php/init.php';

$content = file_get_contents('php://input');
$postData = json_decode($content, true);
$setting_key = 'woocommerce_eh_stripe_pay_settings';
$settings = get_option($setting_key, false);

$sk = ($settings['eh_stripe_mode'] === 'test') ? $settings['eh_stripe_test_secret_key'] : $settings['eh_stripe_live_secret_key'];

$prepareData = array(
	'amount' => round(floatval($postData['amount']) * 100),
	'currency' => $postData['currency'],
);

if (!empty($postData['payment_method_id'])) {
	$prepareData['payment_method'] = $postData['payment_method_id'];
}

$refer_url = wp_parse_url($_SERVER['HTTP_REFERER']);
$prepareData['metadata'] = array(
	'order_id' => $postData['order_id'],
	'Customer IP' =>  $_SERVER['REMOTE_ADDR'],
	'Agent' => $postData['user_agent'],
	'Referer' => $refer_url['scheme'] . '://' . $refer_url['host'] . $refer_url['path'],
	'Billing Email' => $postData['billing_email']
);
//$prepareData['description'] = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . ' Order #' . $postData['order_invoice'];
$prepareData['description'] = '-';
if (!empty($postData['statement_descriptor'])) {
	$prepareData['statement_descriptor'] = $postData['statement_descriptor'];
}

$orderItems = $postData['order_items'];
$product_name = array();
foreach ($orderItems as $item) {
	array_push($product_name, $item['name']);
}
$product_list = implode(' | ', $product_name);
//$prepareData['metadata']['Products'] = substr($product_list, 0, 499);
$prepareData['capture_method'] = 'automatic';
$prepareData['confirm'] =  true ;

$prepareData['shipping'] = $postData['shipping'];

$response = new stdClass();
\DRStripe\Stripe::setApiKey( $sk );
\DRStripe\Stripe::setAppInfo( 'WordPress payment-gateway-stripe-and-woocommerce-integration', '3.0.6', 'https://wordpress.org/plugins/payment-gateway-stripe-and-woocommerce-integration/', 'pp_partner_KHip9dhhenLx0S' );

if (!empty($postData['payment_method_id'])) {
	try {
		$params = array(
			'name' => $postData['shipping']['name'],
			'description' => "Customer for Order #" . $postData['order_invoice'],
			'email' => $postData['billing_email'],
			'payment_method' => $postData['payment_method_id'],
			'address' => $postData['shipping']['address'],
			
		);
	
		$customer = \DRStripe\Customer::create($params);
		if (!empty($customer->id)) {
			$prepareData['customer'] = $customer;
		}
	}  catch (Exception $error) {
		$error_message = $error->getMessage();
		$response->error_message = $error_message;
	}
}

if (!empty($settings['eh_stripe_statement_descriptor'])) {
	$prepareData['statement_descriptor'] = $settings['eh_stripe_statement_descriptor'];
}

// $prepareData['payment_method_types'] = array('card');
try {
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $guidv4_data = $guidv4_data ?? random_bytes(16);
    assert(strlen($guidv4_data) == 16);
    
    // Set version to 0100
    $guidv4_data[6] = chr(ord($guidv4_data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $guidv4_data[8] = chr(ord($guidv4_data[8]) & 0x3f | 0x80);
    
    // Output the 36 character UUID.
    $myuuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($guidv4_data), 4));
    
	$intent = \DRStripe\PaymentIntent::create( $prepareData , array(
	    'idempotency_key' => md5($postData['merchant_site'] . '-' . $myuuid) ,
	));

	$response->status = $intent->status;
	$response->payment_intent = $intent;
	
	// If the payment requires additional actions, such as authenticating with 3D Secure
	if($intent->status == 'requires_action'){
	    $response->error_message = '3D Secure Check';
	}
	
} catch (Exception $error) {
	if (method_exists($error, 'getJsonBody')) {
		$oops = $error->getJsonBody();
		$error_message = $oops['error']['message'];
	} else {
		$oops = array('message' => $error->getMessage());
		$error_message = $error->getMessage();
	}
	$response->status = 'error';
	$response->error_message = $error_message;
}

echo json_encode($response);
die();