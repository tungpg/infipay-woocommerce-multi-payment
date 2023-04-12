<?php
require(dirname(__FILE__) . "/InfipayAirwallexCardClient.php");
// $content = file_get_contents('php://input');
// $postData = json_decode($content, true);

function awxProcess($postData){
    try {
        
        $data = array(
            '2payment_order_id'    => isset($postData['payment_order_id']) ? $postData['payment_order_id'] : null,
            'clientname'    => isset($postData['clientname']) ? $postData['clientname'] : null,
            'payment_code' => isset($postData['payment_code']) ? $postData['payment_code'] : null,
            
            'fname' => isset($postData['first_name']) ? $postData['first_name'] : null,
            'lname' => isset($postData['last_name']) ? $postData['last_name'] : null,
            'country' => isset($postData['country']) ? $postData['country'] : null,
            'address' => isset($postData['line1']) ? $postData['line1'] : null,
            'city' => isset($postData['city']) ? $postData['city'] : null,
            'state' => isset($postData['state']) ? $postData['state'] : null,
            'zipcode' => isset($postData['postal_code']) ? $postData['postal_code'] : null,
            'phone' => isset($postData['phone']) ? $postData['phone'] : null,
            'email' => isset($postData['email']) ? $postData['email'] : null,
            
            'totalprice' => isset($postData['totalprice']) ? $postData['totalprice'] : null,
            'pagecheckout' => isset($postData['pagecheckout']) ? $postData['pagecheckout'] : null,
            'pagethankyou' => isset($postData['pagethankyou']) ? $postData['pagethankyou'] : null,
            
            'airwallex_consent_id' => null,
            'airwallex_customer_id' => null,
            'noteorder' => null,
            'statuspayment' => isset($postData['statuspayment']) ? $postData['statuspayment'] : null,
        );
        
        
        header('Content-Type: application/json');
        http_response_code(200);
        return json_encode($data);
        
        $apiClient = new \InfipayAirwallexCardClient();
        
        $paymentProcess = $dataPayment;
        $paymentProcess['payment_id'] = time();
        
        $gateway = new Airwallex\Gateways\Card();
        $orderService = new Airwallex\Services\OrderService();
        $airwallexCustomerId = null;
        $paymentIntent = $apiClient->createPaymentIntentExt($paymentProcess);
        
        
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
        
        return json_encode($response);
    } catch (Exception $e) {
        // $logService->error('async intent controller action failed', $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(200);
        return json_encode([
            'error' => $e->getMessage(),
        ]);
    }
}
