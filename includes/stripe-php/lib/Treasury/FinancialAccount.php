<?php

// File generated from our OpenAPI spec

namespace DRStripe\Treasury;

/**
 * Stripe Treasury provides users with a container for money called a
 * FinancialAccount that is separate from their Payments balance. FinancialAccounts
 * serve as the source and destination of Treasury’s money movement APIs.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property string[] $active_features The array of paths to active Features in the Features hash.
 * @property \DRStripe\StripeObject $balance Balance information for the FinancialAccount
 * @property string $country Two-letter country code (<a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2">ISO 3166-1 alpha-2</a>).
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property \DRStripe\Treasury\FinancialAccountFeatures $features Encodes whether a FinancialAccount has access to a particular Feature, with a <code>status</code> enum and associated <code>status_details</code>. Stripe or the platform can control Features via the requested field.
 * @property \DRStripe\StripeObject[] $financial_addresses The set of credentials that resolve to a FinancialAccount.
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\DRStripe\StripeObject $metadata Set of <a href="https://stripe.com/docs/api/metadata">key-value pairs</a> that you can attach to an object. This can be useful for storing additional information about the object in a structured format.
 * @property string[] $pending_features The array of paths to pending Features in the Features hash.
 * @property null|\DRStripe\StripeObject $platform_restrictions The set of functionalities that the platform can restrict on the FinancialAccount.
 * @property string[] $restricted_features The array of paths to restricted Features in the Features hash.
 * @property string $status The enum specifying what state the account is in.
 * @property \DRStripe\StripeObject $status_details
 * @property string[] $supported_currencies The currencies the FinancialAccount can hold a balance in. Three-letter <a href="https://www.iso.org/iso-4217-currency-codes.html">ISO currency code</a>, in lowercase.
 */
class FinancialAccount extends \DRStripe\ApiResource
{
    const OBJECT_NAME = 'treasury.financial_account';

    use \DRStripe\ApiOperations\All;
    use \DRStripe\ApiOperations\Create;
    use \DRStripe\ApiOperations\Retrieve;
    use \DRStripe\ApiOperations\Update;

    const STATUS_CLOSED = 'closed';
    const STATUS_OPEN = 'open';

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \DRStripe\Exception\ApiErrorException if the request fails
     *
     * @return \DRStripe\Treasury\FinancialAccount the retrieved financial account
     */
    public function retrieveFeatures($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/features';
        list($response, $opts) = $this->_request('get', $url, $params, $opts);
        $obj = \DRStripe\Util\Util::convertToStripeObject($response, $opts);
        $obj->setLastResponse($response);

        return $obj;
    }

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \DRStripe\Exception\ApiErrorException if the request fails
     *
     * @return \DRStripe\Treasury\FinancialAccount the updated financial account
     */
    public function updateFeatures($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/features';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);

        return $this;
    }
}
