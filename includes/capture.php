<?php

function capturePaypalOrder($access_token) {
    global $REST_API_URL, $eh_paypal;

    $params = array(
        'method' => 'POST',
        'timeout' => 60,
        'user-agent' => 'EH_PAYPAL_EXPRESS_CHECKOUT',
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token
        )
    );
    
    $response = wp_safe_remote_request($REST_API_URL . "/v2/checkout/orders/" . $_GET['payment_id'] . '/capture/', $params);

    return $response;
}

$access_token = get_access_token();

$ppOrder = capturePaypalOrder($access_token);
$res = array(
    'success' => $ppOrder['response']['code'] < 204 ? true: false,
    'message' => $ppOrder['response']['message'],
    'full' => $ppOrder
);

if ($res['success'] === true) {
    $res['transaction_id'] = $_GET['payment_id'];
    $body = json_decode($ppOrder['body']);
    $res['seller_receivable_breakdown'] = $body->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown;
}

// header('Content-Type: application/json');
echo json_encode($res);