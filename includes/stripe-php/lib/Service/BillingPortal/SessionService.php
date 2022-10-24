<?php

// File generated from our OpenAPI spec

namespace DRStripe\Service\BillingPortal;

class SessionService extends \DRStripe\Service\AbstractService
{
    /**
     * Creates a session of the customer portal.
     *
     * @param null|array $params
     * @param null|array|\DRStripe\Util\RequestOptions $opts
     *
     * @throws \DRStripe\Exception\ApiErrorException if the request fails
     *
     * @return \DRStripe\BillingPortal\Session
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/billing_portal/sessions', $params, $opts);
    }
}
