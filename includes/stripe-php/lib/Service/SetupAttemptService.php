<?php

// File generated from our OpenAPI spec

namespace DRStripe\Service;

class SetupAttemptService extends \DRStripe\Service\AbstractService
{
    /**
     * Returns a list of SetupAttempts associated with a provided SetupIntent.
     *
     * @param null|array $params
     * @param null|array|\DRStripe\Util\RequestOptions $opts
     *
     * @throws \DRStripe\Exception\ApiErrorException if the request fails
     *
     * @return \DRStripe\Collection<\DRStripe\SetupAttempt>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/setup_attempts', $params, $opts);
    }
}
