<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'config.php';

/**
 * TungPG Mod - Script for Multi Stripe Payment Gateway plugin
 */

require __DIR__  . '/stripe-php-master/init.php';


// Get the post params
$secret_key = $_POST["secret_key"];

$amount = $_POST["amount"];
//$reason = $_POST["reason"];
$payment_id = $_POST["payment_id"];
// $currency = $_POST["currency"];

// Process the refund
try {
    \Stripe\Stripe::setApiKey($secret_key);
    
    $re = \Stripe\Refund::create([
        'amount' => $amount * 100,
        'payment_intent' => $payment_id,
        'reason' => 'requested_by_customer',
    ]);
    
    if($re->status == "succeeded"){
        echo json_encode([
            'success' => '1',
            'refund_id' => $re->id,
        ]);
        exit(1);
    }else{
        echo json_encode([
            'error' => "Refund failed!",
        ]);
        exit(1);
    }
} catch (Exception $ex) {
    echo json_encode([
        'error' => $ex->getMessage(),
    ]);
    exit(1);
}