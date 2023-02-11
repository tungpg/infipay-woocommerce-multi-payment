<?php 
use PayPal\Api\Amount;
use PayPal\Api\RefundRequest;
use PayPal\Api\Sale;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


/**
 * TungPG Mod - Script for Multi Paypal Payment Gateway plugin
 */

require __DIR__  . '/PayPal-PHP-SDK/autoload.php';


// Get the post params
$client_id = $_POST["client_id"];
$client_secret = $_POST["client_secret"];
$is_sandbox = $_POST["is_sandbox"];

$amount = $_POST["amount"];
$reason = $_POST["reason"];
$transaction_id = $_POST["transaction_id"];
$currency = $_POST["currency"];

// Process the refund
// ### Refund amount
// Includes both the refunded amount (to Payer)
// and refunded fee (to Payee). Use the $amt->details
// field to mention fees refund details.
$amt = new Amount();
$amt->setCurrency($currency)
->setTotal($amount);

// ### Refund object
$refundRequest = new RefundRequest();
$refundRequest->setAmount($amt);
$refundRequest->setReason($reason);

// ###Sale
// A sale transaction.
// Create a Sale object with the
// given sale transaction id.
$sale = new Sale();
$sale->setId($transaction_id);
try {
    // Create a new apiContext object so we send a new
    // PayPal-Request-Id (idempotency) header for this resource
    $apiContext = new \PayPal\Rest\ApiContext(
        new \PayPal\Auth\OAuthTokenCredential(
            $client_id,     // ClientID
            $client_secret      // ClientSecret
        )
    );
    
    $apiContext->setConfig(
        array(
            'mode' => (bool)$is_sandbox? 'sandbox' : 'live',
            'log.LogEnabled' => true,
            'log.FileName' => './logs/PayPal.log',
            'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
            'cache.enabled' => true,
        )
    );
    
    // Refund the sale
    // (See bootstrap.php for more on `ApiContext`)
    $refundedSale = $sale->refundSale($refundRequest, $apiContext);
    
    echo json_encode([
        'success' => '1',
    ]);
    exit(1);
} catch (Exception $ex) {
    echo json_encode([
        'error' => $ex->getMessage(),
    ]);
    exit(1);
}