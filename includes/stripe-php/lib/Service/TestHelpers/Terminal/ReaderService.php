<?php

// File generated from our OpenAPI spec

namespace DRStripe\Service\TestHelpers\Terminal;

class ReaderService extends \DRStripe\Service\AbstractService
{
    /**
     * Presents a payment method on a simulated reader. Can be used to simulate
     * accepting a payment, saving a card or refunding a transaction.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\DRStripe\Util\RequestOptions $opts
     *
     * @throws \DRStripe\Exception\ApiErrorException if the request fails
     *
     * @return \DRStripe\Terminal\Reader
     */
    public function presentPaymentMethod($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/terminal/readers/%s/present_payment_method', $id), $params, $opts);
    }
}
