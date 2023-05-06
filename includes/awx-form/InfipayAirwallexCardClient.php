<?php
use Airwallex\CardClient;
use Airwallex\Struct\PaymentIntent;

class InfipayAirwallexCardClient extends CardClient
{
    final public function createPaymentIntentExt($dataPayment, $withDetails = false){
        $infipay_checkout_page_url = get_permalink( get_page_by_path( 'icheckout' ) );
        
        $client = $this->getHttpClient();
        // $orderNumber = ($orderNumber = $order->get_meta('_order_number')) ? $orderNumber : $orderId;
        $customerId=null;
        $data = [
            'amount' => $dataPayment['totalprice'],
            'currency' => 'USD',
            'descriptor' => $dataPayment['payment_descriptor'],
            'merchant_order_id' => $dataPayment['payment_id'],
            'return_url' => "$infipay_checkout_page_url?infipay-awx-confirm-payment=1",
            'order' => [
                'type' => 'physical_goods',
            ],
            'request_id' => uniqid(),
        ]
        + ($customerId !== null ? ['customer_id' => $customerId] : []);
        
        if (mb_strlen($data['descriptor']) > 32) {
            $data['descriptor'] = mb_substr($data['descriptor'], 0, 32);
        }
        
        // Set customer detail
        $customerAddress = [
            'city'=> $dataPayment['city'],
            'country_code' => $dataPayment['country'],
            'postcode' => $dataPayment['zipcode'],
            'state' => $dataPayment['state'],
            'street' => $dataPayment['address'],
        ];
        
        $customer = [
            'email' => $dataPayment['email'],
            'first_name' => $dataPayment['fname'],
            'last_name' => $dataPayment['lname'],
            'merchant_customer_id' => $dataPayment['payment_id'] + 1,
            'phone_number' =>$dataPayment['phone'],
            'address' => [
                'city' => $dataPayment['city'],
                'country_code' => $dataPayment['country'],
                'postcode' => $dataPayment['zipcode'],
                'state' => $dataPayment['state'],
                'street' => $dataPayment['address'],
            ],
        ];
        
        $data['customer'] = $customerId === null ? $customer : null;
        
        // Set order details
        $orderData = [
            'type' => 'physical_goods',
            'products' => [],
        ];
        
        $orderData['products'][] = [
            'desc' => $dataPayment['payment_code'],
            'name' => $dataPayment['payment_code'],
            'quantity' => 1,
            'sku' => $dataPayment['payment_code'],
            'type' => 'physical',
            'unit_price' => round($dataPayment['totalprice'], 2),
        ];
        
        $orderData['shipping'] = [
            'address' => [
                'city' => $dataPayment['city'],
                'country_code' => $dataPayment['country'],
                'postcode' => $dataPayment['zipcode'],
                'state' => $dataPayment['state'],
                'street' => $dataPayment['address'],
            ],
            'first_name' => $dataPayment['fname'],
            'last_name' => $dataPayment['lname'],
            'shipping_method' => 'standard',
        ];
        //var_dump($orderData);die;
        $data['order'] = $orderData;
        // echo 'getReferrer:'. json_encode($this->getReferrer()).'<br>';
        // echo 'getToken:'.$this->getToken().'<br>';
        // echo 'getAuthorizationRetryClosure:'.json_encode($this->getAuthorizationRetryClosure()).'<br>';
        // echo json_encode(
        //     $data
        //     + $this->getReferrer()
        // );
        $response = $client->call(
            'POST',
            $this->getPciUrl('pa/payment_intents/create'),
            json_encode(
                $data
                + $this->getReferrer()
                ),
            [
                'Authorization' => 'Bearer ' . $this->getToken(),
            ],
            $this->getAuthorizationRetryClosure()
            );
        
        if (empty($response->data['id'])) {
            throw new Exception('payment intent creation failed: ' . json_encode($response));
        }
        
        return new PaymentIntent($response->data);
    }
}