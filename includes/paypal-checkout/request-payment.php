<?php 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


/**
 * TungPG Mod - Script for Multi Paypal Payment Gateway plugin
 */

require __DIR__  . '/PayPal-PHP-SDK/autoload.php';

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\FlowConfig;
use PayPal\Api\InputFields;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\Payment;
use PayPal\Api\Presentation;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\WebProfile;

//=============================
// Get the post params
$main_shop_name = $_POST["main_shop_name"];
$main_shop_name = preg_replace("/[^a-zA-Z0-9]+/", "", $main_shop_name);
$main_shop_name = strtolower($main_shop_name);
$main_shop_name = str_replace("shop", "", $main_shop_name);
$main_shop_name = str_replace("store", "", $main_shop_name);
$main_shop_name = trim($main_shop_name);

$ppaccid = $_POST["ppaccid"];
$app_order_id = $_POST["app_order_id"];
$shop_order_id = $_POST["shop_order_id"];
$order_json = $_POST["order_json"];
$invoice_id_prefix = $_POST["invoice_id_prefix"];
$hide_item_title = $_POST["hide_item_title"];
$hide_sku = $_POST["hide_sku"];

if(!isset(
    $main_shop_name) || !isset($ppaccid) || !isset($shop_order_id) || 
    !isset($order_json) || !isset($app_order_id) || !isset($invoice_id_prefix)
    || !isset($hide_item_title)
    ){
    echo json_encode([
        'error' => 'Invalid params!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

if(empty($invoice_id_prefix)) $invoice_id_prefix = "WCO-";

$shop_domain = $_SERVER['HTTP_HOST'];
$shop_order = json_decode(stripslashes($order_json));

//=============================
// Get Paypal Account information
$get_pp_credential_tool_url = "https://" . MULTI_PAYPAL_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-paypal-checkout-payment/get-paypal-credential";

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query([
            'ppaccid' => $ppaccid,
        ])
    )
);
$context  = stream_context_create($options);
$api_response = file_get_contents($get_pp_credential_tool_url, false, $context);

$paypal_credential_object = (object)json_decode( $api_response, true );

if(isset($paypal_credential_object->error)){
    echo json_encode([
        'error' => 'Could not get PP Credential!',
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

// Get the information value
$pp_client_id = $paypal_credential_object->client_id;
$pp_client_secret = $paypal_credential_object->client_secret;
$transaction_prefix = $paypal_credential_object->transaction_prefix;
$transaction_item_name_pattern = $paypal_credential_object->transaction_item_name_pattern;
$pp_invoice_id_prefix = $paypal_credential_object->invoice_id_prefix;
$randomNumber = rand(10000000, 999999999);

$main_shop_name_1 = str_replace(" ", "", $main_shop_name);
$main_shop_name_1 = strtoupper($main_shop_name_1);

if(!empty($pp_invoice_id_prefix)){
    $invoice_id_prefix = $pp_invoice_id_prefix;
    
    $invoice_id_prefix = str_replace("TRANSACTION_PREFIX", $transaction_prefix, $invoice_id_prefix);
    $invoice_id_prefix = str_replace("RANDOM_NUMBER", $randomNumber, $invoice_id_prefix);
    $invoice_id_prefix = str_replace("SHOP_NAME", $main_shop_name_1, $invoice_id_prefix);
    $invoice_id_prefix = str_replace("ORDER_NUMBER", $shop_order->shop_order_number, $invoice_id_prefix);
    $invoice_id_prefix = str_replace("--", "-", $invoice_id_prefix);
}

if(empty($transaction_item_name_pattern)){
    $transaction_item_name_pattern = "TRANSACTION_PREFIX-RANDOM_NUMBER-SHOP_NAME-ORDER_NUMBER";
}

//=============================
// TODO Get order information

//=============================


$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        $pp_client_id,     // ClientID
        $pp_client_secret      // ClientSecret
    )
);

$apiContext->setConfig(
    array(
        'mode' => (bool)$paypal_credential_object->is_sandbox? 'sandbox' : 'live',
        'log.LogEnabled' => true,
        'log.FileName' => './logs/PayPal.log',
        'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
        'cache.enabled' => true,
    )
    );

// Set default landing page to billing
$flow = new FlowConfig();
$flow->setLandingPageType('Billing');

$presentation = new Presentation();

$inputFields = new InputFields();
$inputFields->setAddressOverride(1);

$webProfile = new WebProfile();
$webProfile->setName(md5($shop_domain) . uniqid())
    ->setFlowConfig($flow)
    ->setPresentation($presentation)
    ->setInputFields($inputFields);

try {
    $createProfileResponse = $webProfile->create($apiContext);
} catch (\PayPal\Exception\PayPalConnectionException $ex) {
    echo json_encode([
        'error' => $ex->getData(),
        'show_error_to_buyer' => true,
    ]);
    exit(1);
} catch (Exception $ex) {
    echo json_encode([
        'error' => $ex->getMessage(),
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

$profileId = $createProfileResponse->getId();
// end Set default landing page to billing

// Create a new Payment 
$payment_method = "paypal";
$payer = new Payer();
$payer->setPaymentMethod($payment_method); // Valid Values: ["credit_card", "paypal"]

$billing_address = new PayPal\Api\Address();
$billing_address->setLine1($shop_order->billing->address_1);
$billing_address->setLine2($shop_order->billing->address_2);
$billing_address->setCity($shop_order->billing->city);
$billing_address->setCountryCode($shop_order->billing->country);
$billing_address->setPostalCode($shop_order->billing->postcode);
$billing_address->setState($shop_order->billing->state);

$shipping_address = new PayPal\Api\Address();
$shipping_address->setLine1($shop_order->shipping->address_1);
$shipping_address->setLine2($shop_order->shipping->address_2);
$shipping_address->setCity($shop_order->shipping->city);
$shipping_address->setCountryCode($shop_order->shipping->country);
$shipping_address->setPostalCode($shop_order->shipping->postcode);
$shipping_address->setState($shop_order->shipping->state);

if($payment_method == "credit_card"){
    $payer_info = new PayerInfo();
    $payer_info->setFirstName($shop_order->billing->first_name);
    $payer_info->setLastName($shop_order->billing->last_name);
    $payer_info->setEmail($shop_order->billing->email);
    $payer_info->setBillingAddress($billing_address);
    $payer_info->setCountryCode($shop_order->billing->country);    
    $payer->setPayerInfo($payer_info);
}

if(empty($transaction_prefix)){
    // Gen random string
    $randomString = '';
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    for ($i = 0; $i < 3; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    $randomString = strtoupper($randomString);
    
    $transaction_prefix = $randomString;
}

// Item list
$itemList = new ItemList();
$item_count = 0;
$items = [];
foreach($shop_order->line_items as $line_item){
    $item_count++;
    $item1 = new Item();
    
    // Build item name
    // TRANSACTION_PREFIX, RANDOM_NUMBER, SHOP_NAME, ORDER_NUMBER
    $item_name = $transaction_item_name_pattern;
    $item_name = str_replace("TRANSACTION_PREFIX", $transaction_prefix, $item_name);
    $item_name = str_replace("RANDOM_NUMBER", $randomNumber, $item_name);
    $item_name = str_replace("SHOP_NAME", $main_shop_name_1, $item_name);
    $item_name = str_replace("ORDER_NUMBER", $shop_order->shop_order_number, $item_name);
    $item_name = str_replace("--", "-", $item_name);
    $item_name = str_replace("--", "-", $item_name);
    
    $item_name = str_replace("GENERAL_PRODUCT_TITLE", "ITEM $item_count", $item_name);
    
    $item1
        //->setSku($line_item->sku)
        ->setCurrency($shop_order->currency)
        ->setQuantity($line_item->quantity)
        ->setPrice($line_item->price);
        
        if($hide_sku != 'yes'){
            $item1->setSku($line_item->sku);
        }
        
        if($hide_item_title == "yes"){
            // $shop_order->shop_order_number . ": Item#" . $item_count
            $item1->setName($item_name);
        }else{
            $item1->setName($line_item->name);
        }
        
    $items[] = $item1;
}

$itemList->setItems($items);
$itemList->setShippingAddress($shipping_address);

$details = new Details();
$details->setShipping($shop_order->shipping_total)
    ->setTax($shop_order->total_tax)
    ->setSubtotal($shop_order->order_total - $shop_order->shipping_total - $shop_order->total_tax);

$amount = new Amount();
$amount->setCurrency($shop_order->currency)->setTotal($shop_order->order_total)->setDetails($details);

$transaction = new Transaction();
$transaction->setAmount($amount)
    ->setItemList($itemList)
    //->setDescription($main_shop_name . " : Order#" . $shop_order->shop_order_number)
    //->setInvoiceNumber($invoice_id_prefix . $shop_order->shop_order_number);
    ->setInvoiceNumber($invoice_id_prefix);

$baseUrl = "https://$shop_domain/icheckout";
$redirectUrls = new RedirectUrls();
$redirectUrls->setReturnUrl("$baseUrl/?paypal-checkout=accept-payment&appoid=$app_order_id&ppaccid=$ppaccid")
    ->setCancelUrl("$baseUrl/?paypal-checkout=cancel-payment&sbm=$paypal_credential_object->is_sandbox&app_order_id=$app_order_id");

$payment = new Payment();

$payment->setIntent("sale")
    ->setPayer($payer)
    ->setRedirectUrls($redirectUrls)
    ->setTransactions(array($transaction))
    ->setExperienceProfileId($profileId);

try {
    $payment->create($apiContext);
} catch (PayPal\Exception\PayPalConnectionException $ex) {
    $error_message = "10736 - A match of the Shipping Address City, State and Postal Code failed.";
    $error_message .= " " . $ex->getMessage();
    
    echo json_encode([
        'error' => $error_message,
        'show_error_to_buyer' => true,
    ]);
    exit(1);
} catch (Exception $ex) {
    echo json_encode([
        'error' => $ex->getMessage(),
        'show_error_to_buyer' => false,
    ]);
    exit(1);
}

$approvalUrl = $payment->getApprovalLink();

echo json_encode([
    'approval_url' => $approvalUrl
]);