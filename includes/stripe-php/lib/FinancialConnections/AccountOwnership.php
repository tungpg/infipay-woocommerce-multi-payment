<?php

// File generated from our OpenAPI spec

namespace DRStripe\FinancialConnections;

/**
 * Describes a snapshot of the owners of an account at a particular point in time.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property \DRStripe\Collection<\DRStripe\FinancialConnections\AccountOwner> $owners A paginated list of owners for this account.
 */
class AccountOwnership extends \DRStripe\ApiResource
{
    const OBJECT_NAME = 'financial_connections.account_ownership';
}
