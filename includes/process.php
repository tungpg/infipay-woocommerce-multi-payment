<?php
function paypalCreateOrder($access_token) {
	global $REST_API_URL, $eh_paypal, $wp;

	$page_referer = home_url( $wp->request );
	// $page_referer = 'https://deleonsst.com/thank-you';

	$landing_page = ( 'login' === $eh_paypal['smart_button_landing_page'] ) ? 'LOGIN' : 'BILLING';
	$shipping_preference = ($eh_paypal['smart_button_paypal_allow_override'] == 'yes') ? 'SET_PROVIDED_ADDRESS' : 'GET_FROM_FILE';
	$user_action = ($eh_paypal['smart_button_skip_review'] == 'yes') ? 'PAY_NOW' : 'CONTINUE';
	$body = array(
		'intent' => 'CAPTURE',
		'application_context' => array(
			'landing_page' => $landing_page,
			'return_url' => $page_referer . '/?action=create_order',
			'cancel_url' => $page_referer . '/?action=cancel_order',
			'brand_name' => $eh_paypal['smart_button_business_name'],
			'shipping_preference' => $shipping_preference,
			'user_action' => $user_action,
			'locale' => 'en-US'
		)
	);

	if (isset($_GET) && !empty($_GET['items'])) {
		$purchase_units = array();
		foreach($_GET['items'] as $item) {
			if (isset($item['name']) && isset($item['total'])) {
				parse_str($_GET['shipping_info'], $shipping);
				$shipping_address = $shipping['shipping_address'];
				$fullname = $shipping_address['name'];
				unset($shipping_address['name']);
				array_push($purchase_units, array(
					'amount' => array(
						'currency_code' => $_GET['currency'],
						'value' => floatval($item['total']),
						'breakdown' => array(
							'item_total' => array(
								'currency_code' => $_GET['currency'],
								'value' => $item['total']
							),
							'discount' => array(
								'currency_code' => $_GET['currency'],
								'value' => $_GET['discount']
							)
						)
					),
					'items' => array(
						array(
							'name' => $item['name'],
							'quantity' => $item['quantity'],
							'unit_amount' => array(
								'currency_code' => $_GET['currency'],
								'value' => $item['total']
							)
						)
					),
					'shipping' => array(
						'address' => array(
							'country_code' => $shipping_address['address_country'],
							'address_line_1' => $shipping_address['address_line1'],
							'address_line_2' => $shipping_address['address_line2'],
							'admin_area_1' => $shipping_address['address_state'],
							'admin_area_2' => $shipping_address['address_city'],
							'postal_code' => $shipping_address['address_zip'],
						),
						'name' => array(
							'full_name' => $fullname
						),
						'type' => 'SHIPPING'
					)
				));
			}
			
		}

		$body['purchase_units'] = $purchase_units;
	}

	// return json_encode($body);

	$params = array(
		'method' => 'POST',
		'timeout' => 60,
		'user-agent' => 'EH_PAYPAL_EXPRESS_CHECKOUT',
		'headers' => array(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $access_token
		),
		'body' => json_encode($body)
	);

	$response = wp_safe_remote_request($REST_API_URL . "/v2/checkout/orders", $params);

	return json_decode($response['body']);
}


$access_token = get_access_token();
$ppOrder = paypalCreateOrder($access_token);

$redirect_url = '';
if ($ppOrder && isset($ppOrder->id) && isset($ppOrder->links) && is_array($ppOrder->links)) {
	set_transient($ppOrder->id, $_GET, 3600);
    foreach($ppOrder->links as $arr_value){
        if ($arr_value->rel == 'approve') {
            $redirect_url = $arr_value->href;
        }
    }
}

if (!empty($redirect_url)) {
	$redirect_args = wp_parse_url($redirect_url);
	$redirect_args['path'] = '/webapps/hermes';
	$redirect_change = 'https://' . $redirect_args['host'] . $redirect_args['path'] . '?' . $redirect_args['query'];
	wp_redirect($redirect_change);
} else {
	$return_param = $_GET;
	$return_param['woo-infipay-return'] = 1;
	$return_param['pay-error'] = 1;
	if (isset($ppOrder->name)) {
		$return_param['error-name'] = $ppOrder->name;
	}
	$returnUrl = add_query_arg($return_param, $_GET['merchant_site'] );
	// echo $returnUrl;
	wp_redirect($returnUrl);
}

