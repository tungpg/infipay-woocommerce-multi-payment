<?php

namespace PayPal\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\ArgumentValidator;
use PayPal\Rest\ApiContext;

/**
 * Class ShipmentTracking (created by TungPG)
 *
 * A capture transaction.
 *
 * @package PayPal\Api
 *
 */
class ShipmentTracking extends PayPalResourceModel
{

    /**
     * Adds tracking information with tracking numbers for multiple PayPal transactions.
     *
     * @param array $trackings
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return ShipmentTracking
     */
    public static function addTrackings($trackings, $apiContext = null, $restCall = null)
    {
        $payLoad = ["trackers" => $trackings];
        $payLoad = json_encode($payLoad);
        $json = self::executeCall(
            "/v1/shipping/trackers-batch",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
            );
        $ret = new ShipmentTracking();
        $ret->fromJson($json);
        return $ret;
    }
}
