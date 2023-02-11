<?php
include_once 'config.php';

/**
 * TungPG Mod - Script for Multi Paypal Payment Gateway plugin
 */

require __DIR__  . '/PayPal-PHP-SDK/autoload.php';


//=============================
$ppaccid = $_POST["ppaccid"];
//=============================
// Get Paypal Account information
$get_pp_credential_tool_url = "https://" . MULTI_PAYPAL_PAYMENT_SERVER_DOMAIN . "/index.php?r=multi-paypal-payment/get-paypal-credential";

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
$live_api_user_name = $paypal_credential_object->live_api_user_name;
$live_api_password = $paypal_credential_object->live_api_password;
$live_api_signature = $paypal_credential_object->live_api_signature;

$version = "124";

if((bool)$paypal_credential_object->is_sandbox){
    $API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
}else{
    $API_Endpoint = "https://api-3t.paypal.com/nvp";
}

$resArray = CallGetBalance ( $API_Endpoint, $version, $live_api_user_name, $live_api_password, $live_api_signature );
$ack = strtoupper ( $resArray ["ACK"] );
if ($ack == "SUCCESS") {
    $balance = urldecode ( $resArray ["L_AMT0"] );
    $currency = urldecode ( $resArray ["L_CURRENCYCODE0"] );
    
    $balances = [$currency => $balance];
    
    if(isset($resArray ["L_AMT1"])){
        $balance1 = urldecode ( $resArray ["L_AMT1"] );
        $currency1 = urldecode ( $resArray ["L_CURRENCYCODE1"] );
        
        $balances[$currency1] = $balance1;
    }
    
    echo json_encode([
        'balances' => $balances,
    ]);
}else{
    echo json_encode([
        'error' => $ack,
    ]);
}

function CallGetBalance($API_Endpoint, $version, $live_api_user_name, $live_api_password, $live_api_signature) {
    // setting the curl parameters.
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $API_Endpoint );
    curl_setopt ( $ch, CURLOPT_VERBOSE, 1 );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    
    // NVPRequest for submitting to server
    $nvpreq = "METHOD=GetBalance" . "&RETURNALLCURRENCIES=1" . "&VERSION=" . $version . "&PWD=" . $live_api_password . "&USER=" . $live_api_user_name . "&SIGNATURE=" . $live_api_signature;
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $nvpreq );
    $response = curl_exec ( $ch );
    
    $nvpResArray = deformatNVP ( $response );
    
    curl_close ( $ch );
    
    return $nvpResArray;
}

/*
 * This function will take NVPString and convert it to an Associative Array and it will decode the response. It is usefull to search for a particular key and displaying arrays. @nvpstr is NVPString. @nvpArray is Associative Array.
 */
function deformatNVP($nvpstr) {
    $intial = 0;
    $nvpArray = array ();
    
    while ( strlen ( $nvpstr ) ) {
        // postion of Key
        $keypos = strpos ( $nvpstr, '=' );
        // position of value
        $valuepos = strpos ( $nvpstr, '&' ) ? strpos ( $nvpstr, '&' ) : strlen ( $nvpstr );
        
        /* getting the Key and Value values and storing in a Associative Array */
        $keyval = substr ( $nvpstr, $intial, $keypos );
        $valval = substr ( $nvpstr, $keypos + 1, $valuepos - $keypos - 1 );
        // decoding the respose
        $nvpArray [urldecode ( $keyval )] = urldecode ( $valval );
        $nvpstr = substr ( $nvpstr, $valuepos + 1, strlen ( $nvpstr ) );
    }
    return $nvpArray;
}

?>