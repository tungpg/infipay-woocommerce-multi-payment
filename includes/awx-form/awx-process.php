<?php
require(dirname(__FILE__) . "/InfipayAirwallexCardClient.php");
header('Content-Type: application/json');
http_response_code(200);

function awxCreatePaymentIntent($dataPayment){
    try {
        
        $apiClient = new \InfipayAirwallexCardClient();
        
        $payment_id = $paymentIntent->getId();
        $payment_id = str_replace("int_", "od", $payment_id);
        $dataPayment['payment_id'] = $payment_id;
        
        $gateway = new Airwallex\Gateways\Card();
        $orderService = new Airwallex\Services\OrderService();
        $airwallexCustomerId = null;
        $paymentIntent = $apiClient->createPaymentIntentExt($dataPayment);
        
        
        WC()->session->set('airwallex_payment_intent_id', $paymentIntent->getId());
        
        $response = [
            'paymentIntent' => $paymentIntent->getId(),
            'orderId' => $dataPayment['payment_id'],
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
        return json_encode([
            'error' => $e->getMessage(),
        ]);
    }
}
