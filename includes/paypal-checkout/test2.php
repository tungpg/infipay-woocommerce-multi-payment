<?php
$version = "124";
$user = "pgtung99-facilitator_api1.gmail.com";
$pwd = "7U4FTXT3UPGR5JAT";
$signature = "AfBL1sC11JGbyJqZi0KShe4BJYn0AOaxFgPNHY2gSVJZbF8-cN16UAQB";
$API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";

$resArray = CallGetBalance ( $API_Endpoint, $version, $user, $pwd, $signature );
$ack = strtoupper ( $resArray ["ACK"] );
if ($ack == "SUCCESS") {
    $balance = urldecode ( $resArray ["L_AMT0"] );
    $currency = urldecode ( $resArray ["L_CURRENCYCODE0"] );
    echo "Account Balance: " . $balance . " " . $currency;
}


function CallGetBalance($API_Endpoint, $version, $user, $pwd, $signature) {
    // setting the curl parameters.
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $API_Endpoint );
    curl_setopt ( $ch, CURLOPT_VERBOSE, 1 );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    
    // NVPRequest for submitting to server
    $nvpreq = "METHOD=GetBalance" . "&RETURNALLCURRENCIES=1" . "&VERSION=" . $version . "&PWD=" . $pwd . "&USER=" . $user . "&SIGNATURE=" . $signature;
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