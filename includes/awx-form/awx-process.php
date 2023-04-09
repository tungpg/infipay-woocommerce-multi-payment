<?php
use Airwallex\CardClient;

header('Content-Type: application/json');
http_response_code(200);

try {
    
//     $data = array(
//         'payment_order_id'    => isset($_POST['payment_order_id']) ? $_POST['payment_order_id'] : null,
//         'clientname'    => isset($_POST['clientname']) ? $_POST['clientname'] : null,
//         'payment_code' => isset($_POST['payment_code']) ? $_POST['payment_code'] : null,
        
//         'fname' => isset($_POST['first_name']) ? $_POST['first_name'] : null,
//         'lname' => isset($_POST['last_name']) ? $_POST['last_name'] : null,
//         'country' => isset($_POST['country']) ? $_POST['country'] : null,
//         'address' => isset($_POST['line1']) ? $_POST['line1'] : null,
//         'city' => isset($_POST['city']) ? $_POST['city'] : null,
//         'state' => isset($_POST['state']) ? $_POST['state'] : null,
//         'zipcode' => isset($_POST['postal_code']) ? $_POST['postal_code'] : null,
//         'phone' => isset($_POST['phone']) ? $_POST['phone'] : null,
//         'email' => isset($_POST['email']) ? $_POST['email'] : null,
        
//         'totalprice' => isset($_POST['totalprice']) ? $_POST['totalprice'] : null,
//         'pagecheckout' => isset($_POST['pagecheckout']) ? $_POST['pagecheckout'] : null,
//         'pagethankyou' => isset($_POST['pagethankyou']) ? $_POST['pagethankyou'] : null,
        
//         'airwallex_consent_id' => null,
//         'airwallex_customer_id' => null,
//         'noteorder' => null,
//         'statuspayment' => isset($_POST['statuspayment']) ? $_POST['statuspayment'] : null,
//     );
    
    $apiClient = new Airwallex\CardClient();
    
    http_response_code(200);
    
    print_r($apiClient);
    echo json_encode([
        'error' => 1,
    ]);
    die;
    // if (!empty($_GET['airwallexOrderId'])) {
    //     $payment_id = $_GET['airwallexOrderId'];
    //     WC()->session->set('airwallex_order', $payment_id);
    // }
    // if (empty($payment_id)) {
    //     $payment_id = (int)WC()->session->get('airwallex_order');
    // }
    // if (empty($payment_id)) {
    //     $payment_id = (int)WC()->session->get('order_awaiting_payment');
    // }
    $gateway = new Airwallex\Gateways\Card();
    // $order = wc_get_order($orderId);
    // if (empty($order)) {
    //     throw new Exception('Order not found: ' . $orderId);
    // }
    $orderService = new Airwallex\Services\OrderService();
    $airwallexCustomerId = null;
    // if ($orderService->containsSubscription($order->get_id())) {
    //     $airwallexCustomerId = $orderService->getAirwallexCustomerId($order->get_customer_id(''), $apiClient);
    // }
    // $logService->debug('asyncIntent() before create', ['orderId' => $orderId]);
    $paymentIntent = $apiClient->createPaymentIntentExt($paymentProcess);
    WC()->session->set('airwallex_payment_intent_id', $paymentIntent->getId());
    // $order['airwallex_consent_id'] = $paymentIntent->getPaymentConsentId();
    // $order['airwallex_customer_id'] = $paymentIntent->getCustomerId();
    // update_post_meta($orderId, '_tmp_airwallex_payment_intent', $paymentIntent->getId());
    
    header('Content-Type: application/json');
    http_response_code(200);
    $response = [
        'paymentIntent' => $paymentIntent->getId(),
        'orderId' => $data['payment_code'],
        'createConsent' => !empty($airwallexCustomerId),
        'customerId' => !empty($airwallexCustomerId) ? $airwallexCustomerId : '',
        'currency' => 'USD',
        'airwallex_consent_id'=>$paymentIntent->getPaymentConsentId(),
        'airwallex_customer_id'=>$paymentIntent->getCustomerId(),
        'clientSecret' => $paymentIntent->getClientSecret(),
    ];
    // $logService->debug('asyncIntent() response', [
    //     'response' => $response,
    //     'session' => [
    //         'cookie' => WC()->session->get_session_cookie(),
    //         'data' => WC()->session->get_session_data(),
    //     ],
    // ]);
    echo json_encode($response);
    die;
} catch (Exception $e) {
    // $logService->error('async intent controller action failed', $e->getMessage());
    http_response_code(200);
    echo json_encode([
        'error' => 1,
    ]);
    die;
}