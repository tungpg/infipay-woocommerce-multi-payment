<?php
require(dirname(__FILE__) . "/InfipayAirwallexCardClient.php");

header('Content-Type: application/json');
http_response_code(200);

try {
    
    $data = array(
        'payment_order_id'    => isset($_POST['payment_order_id']) ? $_POST['payment_order_id'] : null,
        'clientname'    => isset($_POST['clientname']) ? $_POST['clientname'] : null,
        'payment_code' => isset($_POST['payment_code']) ? $_POST['payment_code'] : null,
        
        'fname' => isset($_POST['first_name']) ? $_POST['first_name'] : null,
        'lname' => isset($_POST['last_name']) ? $_POST['last_name'] : null,
        'country' => isset($_POST['country']) ? $_POST['country'] : null,
        'address' => isset($_POST['line1']) ? $_POST['line1'] : null,
        'city' => isset($_POST['city']) ? $_POST['city'] : null,
        'state' => isset($_POST['state']) ? $_POST['state'] : null,
        'zipcode' => isset($_POST['postal_code']) ? $_POST['postal_code'] : null,
        'phone' => isset($_POST['phone']) ? $_POST['phone'] : null,
        'email' => isset($_POST['email']) ? $_POST['email'] : null,
        
        'totalprice' => isset($_POST['totalprice']) ? $_POST['totalprice'] : null,
        'pagecheckout' => isset($_POST['pagecheckout']) ? $_POST['pagecheckout'] : null,
        'pagethankyou' => isset($_POST['pagethankyou']) ? $_POST['pagethankyou'] : null,
        
        'airwallex_consent_id' => null,
        'airwallex_customer_id' => null,
        'noteorder' => null,
        'statuspayment' => isset($_POST['statuspayment']) ? $_POST['statuspayment'] : null,
    );
    
    $apiClient = new \InfipayAirwallexCardClient();
    
    $paymentProcess = $dataPayment;
    $paymentProcess['payment_id'] = time();
    
    $gateway = new Airwallex\Gateways\Card();
    $orderService = new Airwallex\Services\OrderService();
    $airwallexCustomerId = null;
    $paymentIntent = $apiClient->createPaymentIntentExt($paymentProcess);
    
    echo json_encode($paymentIntent);
    die();
    
    WC()->session->set('airwallex_payment_intent_id', $paymentIntent->getId());
    
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
    
    echo json_encode($response);
    die;
} catch (Exception $e) {
    // $logService->error('async intent controller action failed', $e->getMessage());
    echo json_encode([
        'error' => $e->getMessage(),
    ]);
    die;
}