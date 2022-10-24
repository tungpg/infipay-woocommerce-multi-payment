<?php

namespace DRStripe\ApiOperations;

/**
 * Trait for listable resources. Adds a `all()` static method to the class.
 *
 * This trait should only be applied to classes that derive from StripeObject.
 */
trait All
{
    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \DRStripe\Exception\ApiErrorException if the request fails
     *
     * @return \DRStripe\Collection of ApiResources
     */
    public static function all($params = null, $opts = null)
    {
        self::_validateParams($params);
        $url = static::classUrl();

        list($response, $opts) = static::_staticRequest('get', $url, $params, $opts);
        $obj = \DRStripe\Util\Util::convertToStripeObject($response->json, $opts);
        if (!($obj instanceof \DRStripe\Collection)) {
            throw new \DRStripe\Exception\UnexpectedValueException(
                'Expected type ' . \DRStripe\Collection::class . ', got "' . \get_class($obj) . '" instead.'
            );
        }
        $obj->setLastResponse($response);
        $obj->setFilters($params);

        return $obj;
    }
}
