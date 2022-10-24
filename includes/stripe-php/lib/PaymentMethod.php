<?php

// File generated from our OpenAPI spec

namespace DRStripe;

/**
 * PaymentMethod objects represent your customer's payment instruments. You can use
 * them with <a
 * href="https://stripe.com/docs/payments/payment-intents">PaymentIntents</a> to
 * collect payments or save them to Customer objects to store instrument details
 * for future payments.
 *
 * Related guides: <a
 * href="https://stripe.com/docs/payments/payment-methods">Payment Methods</a> and
 * <a href="https://stripe.com/docs/payments/more-payment-scenarios">More Payment
 * Scenarios</a>.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property \DRStripe\StripeObject $acss_debit
 * @property \DRStripe\StripeObject $affirm
 * @property \DRStripe\StripeObject $afterpay_clearpay
 * @property \DRStripe\StripeObject $alipay
 * @property \DRStripe\StripeObject $au_becs_debit
 * @property \DRStripe\StripeObject $bacs_debit
 * @property \DRStripe\StripeObject $bancontact
 * @property \DRStripe\StripeObject $billing_details
 * @property \DRStripe\StripeObject $boleto
 * @property \DRStripe\StripeObject $card
 * @property \DRStripe\StripeObject $card_present
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property null|string|\DRStripe\Customer $customer The ID of the Customer to which this PaymentMethod is saved. This will not be set when the PaymentMethod has not been saved to a Customer.
 * @property \DRStripe\StripeObject $customer_balance
 * @property \DRStripe\StripeObject $eps
 * @property \DRStripe\StripeObject $fpx
 * @property \DRStripe\StripeObject $giropay
 * @property \DRStripe\StripeObject $grabpay
 * @property \DRStripe\StripeObject $ideal
 * @property \DRStripe\StripeObject $interac_present
 * @property \DRStripe\StripeObject $klarna
 * @property \DRStripe\StripeObject $konbini
 * @property \DRStripe\StripeObject $link
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\DRStripe\StripeObject $metadata Set of <a href="https://stripe.com/docs/api/metadata">key-value pairs</a> that you can attach to an object. This can be useful for storing additional information about the object in a structured format.
 * @property \DRStripe\StripeObject $oxxo
 * @property \DRStripe\StripeObject $p24
 * @property \DRStripe\StripeObject $paynow
 * @property \DRStripe\StripeObject $promptpay
 * @property \DRStripe\StripeObject $radar_options Options to configure Radar. See <a href="https://stripe.com/docs/radar/radar-session">Radar Session</a> for more information.
 * @property \DRStripe\StripeObject $sepa_debit
 * @property \DRStripe\StripeObject $sofort
 * @property string $type The type of the PaymentMethod. An additional hash is included on the PaymentMethod with a name matching this value. It contains additional information specific to the PaymentMethod type.
 * @property \DRStripe\StripeObject $us_bank_account
 * @property \DRStripe\StripeObject $wechat_pay
 */
class PaymentMethod extends ApiResource
{
    const OBJECT_NAME = 'payment_method';

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \DRStripe\Exception\ApiErrorException if the request fails
     *
     * @return \DRStripe\PaymentMethod the attached payment method
     */
    public function attach($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/attach';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);

        return $this;
    }

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \DRStripe\Exception\ApiErrorException if the request fails
     *
     * @return \DRStripe\PaymentMethod the detached payment method
     */
    public function detach($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/detach';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);

        return $this;
    }
}
