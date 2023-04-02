<?php
global $client_id, $client_secret, $REST_API_URL, $eh_paypal;
$eh_paypal = get_option("woocommerce_eh_paypal_express_settings");
$client_id = $eh_paypal['smart_button_environment'] == 'sandbox'? $eh_paypal['sandbox_client_id'] : $eh_paypal['live_client_id'];
$client_secret = $eh_paypal['smart_button_environment'] == 'sandbox'? $eh_paypal['sandbox_client_secret'] : $eh_paypal['live_client_secret'];
$REST_API_URL = $eh_paypal['smart_button_environment'] == 'sandbox'? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';

$tool_server_url = esc_attr( get_option( 'tool_server_domain' ) );

function get_access_token() {
	global $client_id, $client_secret, $REST_API_URL;
	$access_token = get_transient('eh_access_token');
	if ($access_token === false) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $REST_API_URL . "/v1/oauth2/token");
		/*curl_setopt($ch, CURLOPT_URL, “https://api.paypal.com/v1/oauth2/token”);*/
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $client_id.":".$client_secret);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
		$result = curl_exec($ch);
		
		if(empty($result)) {
			die("Error: No response.");
		} else {
			$json = json_decode($result); 
			set_transient( 'eh_access_token', $json->access_token, $json->expires_in );
			$access_token = $json->access_token;
		}
	}

	return $access_token;
}

if (isset($_GET) ) {
	if (isset($_GET['infipay-process'])) {
		include dirname( __FILE__ ) . '/process.php';
	} else if(isset($_GET['airwallex-checkout'])) {
	    if(empty($tool_server_url)) die();
	    
	    define('MULTI_STRIPE_PAYMENT_SERVER_DOMAIN', $tool_server_url);
	    
	    $infipay_airwallex_checkout = $_GET['airwallex-checkout'];
	    
	    include dirname( __FILE__ ) . '/airwallex-checkout/' . $infipay_airwallex_checkout . '.php';
	    
	} else if(isset($_GET['paypal-checkout'])) {
	    if(empty($tool_server_url)) die();
	    
	    define('MULTI_PAYPAL_PAYMENT_SERVER_DOMAIN', $tool_server_url);
	    
	    $infipay_paypal_checkout = $_GET['paypal-checkout'];
	    
	    include dirname( __FILE__ ) . '/paypal-checkout/' . $infipay_paypal_checkout . '.php';
	    
	} else if(isset($_GET['stripe-checkout'])) {
	    if(empty($tool_server_url)) die();
	    
	    define('MULTI_STRIPE_PAYMENT_SERVER_DOMAIN', $tool_server_url);
	    
	    $infipay_stripe_checkout = $_GET['stripe-checkout'];
	    
	    include dirname( __FILE__ ) . '/stripe-checkout/' . $infipay_stripe_checkout . '.php';
	    
	} else if(isset($_GET['infipay-stripe-get-payment-form'])) {
		include dirname( __FILE__ ) . '/stripe-form.php';
	} elseif (isset($_GET['infipay-stripe-make-payment'])) {
		include dirname( __FILE__ ) . '/stripe-process.php';
	} elseif (isset($_GET['infipay-stripe-refund'])) {
	    include dirname( __FILE__ ) . '/stripe-refund.php';
	} else if(isset($_GET['infipay-awx-get-payment-form'])) {
	    include dirname( __FILE__ ) . '/awx-form.php';
	} elseif(!empty($_GET['action']) && !empty($_GET['token'])) {
		$_ppOrderId = $_GET['token'];
		$mainData = get_transient($_ppOrderId);
		$return_param = array(
			'woo-infipay-return' => 1,
			'order_id' => $mainData['order_id'],
			'paymentId' => $_ppOrderId,
			'token' => $_ppOrderId,
		);
		
		if ($_GET['action'] === 'cancel_order') {
			$return_param['cancel'] = 1;
		} elseif (!empty($_GET['PayerID'])) {
			$return_param['PayerID'] = $_GET['PayerID'];
			$return_param['create-billing-agreement'] = 1;
		}

		// var_dump($return_param);
		$returnUrl = add_query_arg($return_param, $mainData['merchant_site'] );
		// echo $returnUrl;
		wp_redirect($returnUrl);
	} elseif (!empty($_GET['infipay-pp-capture-payment'])) {
		include dirname( __FILE__ ) . '/capture.php';
	}
}

?>