<?php
require __DIR__ . '/stripe-php/init.php';

$content = file_get_contents('php://input');
$postData = json_decode($content, true);
$setting_key = 'woocommerce_eh_stripe_pay_settings';
$settings = get_option($setting_key, false);

$sk = ($settings['eh_stripe_mode'] === 'test') ? $settings['eh_stripe_test_secret_key'] : $settings['eh_stripe_live_secret_key'];

$prepareData = array(
    'transaction_id' => $postData['transaction_id'],
    'amount' => round(floatval($postData['amount']) * 100),
//    'currency' => $postData['currency'],
    'reason' => $postData['reason'],
//    'merchant_site' => $postData['merchant_site'],
);

$response = new stdClass();
\DRStripe\Stripe::setApiKey( $sk );
\DRStripe\Stripe::setAppInfo( 'WordPress payment-gateway-stripe-and-woocommerce-integration', '3.0.6', 'https://wordpress.org/plugins/payment-gateway-stripe-and-woocommerce-integration/', 'pp_partner_KHip9dhhenLx0S' );

if (!empty($postData['transaction_id'])) {
    try {
        $re = \DRStripe\Refund::create([
            'amount' => $prepareData['amount'],
            'payment_intent' => $prepareData['transaction_id'],
            'reason' => $prepareData['reason'],
        ]);
        
        $response->status = $re->status;
        
        if($re->status == "succeeded"){
            
            $response->refund_obj = $re;
            $response->charge_obj = \DRStripe\Charge::retrieve(
                $re->charge,
                []
                );;
        }else{
            $response->error_message = "Refund failed!";
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
}

echo json_encode($response);
die();