<?php
require(dirname(__FILE__) . "/InfipayAirwallexCardClient.php");
// $content = file_get_contents('php://input');
// $postData = json_decode($content, true);

function awxProcess($data){
    try {
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
