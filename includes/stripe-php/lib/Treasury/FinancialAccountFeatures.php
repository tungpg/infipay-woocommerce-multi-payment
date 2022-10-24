<?php

// File generated from our OpenAPI spec

namespace DRStripe\Treasury;

/**
 * Encodes whether a FinancialAccount has access to a particular Feature, with a
 * <code>status</code> enum and associated <code>status_details</code>. Stripe or
 * the platform can control Features via the requested field.
 *
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property \DRStripe\StripeObject $card_issuing Toggle settings for enabling/disabling a feature
 * @property \DRStripe\StripeObject $deposit_insurance Toggle settings for enabling/disabling a feature
 * @property \DRStripe\StripeObject $financial_addresses Settings related to Financial Addresses features on a Financial Account
 * @property \DRStripe\StripeObject $inbound_transfers InboundTransfers contains inbound transfers features for a FinancialAccount.
 * @property \DRStripe\StripeObject $intra_stripe_flows Toggle settings for enabling/disabling a feature
 * @property \DRStripe\StripeObject $outbound_payments Settings related to Outbound Payments features on a Financial Account
 * @property \DRStripe\StripeObject $outbound_transfers OutboundTransfers contains outbound transfers features for a FinancialAccount.
 */
class FinancialAccountFeatures extends \DRStripe\ApiResource
{
    const OBJECT_NAME = 'treasury.financial_account_features';
}
