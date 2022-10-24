<?php

namespace DRStripe;

/**
 * Interface for a Stripe client.
 */
interface StripeClientInterface extends BaseStripeClientInterface
{
    /**
     * Sends a request to Stripe's API.
     *
     * @param string $method the HTTP method
     * @param string $path the path of the request
     * @param array $params the parameters of the request
     * @param array|\DRStripe\Util\RequestOptions $opts the special modifiers of the request
     *
     * @return \DRStripe\StripeObject the object returned by Stripe's API
     */
    public function request($method, $path, $params, $opts);
}
