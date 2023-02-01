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
	$intent = \DRStripe\PaymentIntent::create( $prepareData , array(
		'idempotency_key' => $postData['order_invoice'] .'-'.$payment_method
	));
	
	// If the payment requires additional actions, such as authenticating with 3D Secure
	if($intent->status == 'succeeded'){
	    $response->payment_intent = $intent;
	    $response->status = 'success';
	}elseif($intent->status == 'requires_action'){
	    $response->status = $intent->status;
	    $response->error_message = '3D Secure';
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