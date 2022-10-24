<?php

// File generated from our OpenAPI spec

namespace DRStripe;

/**
 * Client used to send requests to Stripe's API.
 *
 * @property \DRStripe\Service\AccountLinkService $accountLinks
 * @property \DRStripe\Service\AccountService $accounts
 * @property \DRStripe\Service\ApplePayDomainService $applePayDomains
 * @property \DRStripe\Service\ApplicationFeeService $applicationFees
 * @property \DRStripe\Service\Apps\AppsServiceFactory $apps
 * @property \DRStripe\Service\BalanceService $balance
 * @property \DRStripe\Service\BalanceTransactionService $balanceTransactions
 * @property \DRStripe\Service\BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property \DRStripe\Service\ChargeService $charges
 * @property \DRStripe\Service\Checkout\CheckoutServiceFactory $checkout
 * @property \DRStripe\Service\CountrySpecService $countrySpecs
 * @property \DRStripe\Service\CouponService $coupons
 * @property \DRStripe\Service\CreditNoteService $creditNotes
 * @property \DRStripe\Service\CustomerService $customers
 * @property \DRStripe\Service\DisputeService $disputes
 * @property \DRStripe\Service\EphemeralKeyService $ephemeralKeys
 * @property \DRStripe\Service\EventService $events
 * @property \DRStripe\Service\ExchangeRateService $exchangeRates
 * @property \DRStripe\Service\FileLinkService $fileLinks
 * @property \DRStripe\Service\FileService $files
 * @property \DRStripe\Service\FinancialConnections\FinancialConnectionsServiceFactory $financialConnections
 * @property \DRStripe\Service\Identity\IdentityServiceFactory $identity
 * @property \DRStripe\Service\InvoiceItemService $invoiceItems
 * @property \DRStripe\Service\InvoiceService $invoices
 * @property \DRStripe\Service\Issuing\IssuingServiceFactory $issuing
 * @property \DRStripe\Service\MandateService $mandates
 * @property \DRStripe\Service\OAuthService $oauth
 * @property \DRStripe\Service\OrderService $orders
 * @property \DRStripe\Service\PaymentIntentService $paymentIntents
 * @property \DRStripe\Service\PaymentLinkService $paymentLinks
 * @property \DRStripe\Service\PaymentMethodService $paymentMethods
 * @property \DRStripe\Service\PayoutService $payouts
 * @property \DRStripe\Service\PlanService $plans
 * @property \DRStripe\Service\PriceService $prices
 * @property \DRStripe\Service\ProductService $products
 * @property \DRStripe\Service\PromotionCodeService $promotionCodes
 * @property \DRStripe\Service\QuoteService $quotes
 * @property \DRStripe\Service\Radar\RadarServiceFactory $radar
 * @property \DRStripe\Service\RefundService $refunds
 * @property \DRStripe\Service\Reporting\ReportingServiceFactory $reporting
 * @property \DRStripe\Service\ReviewService $reviews
 * @property \DRStripe\Service\SetupAttemptService $setupAttempts
 * @property \DRStripe\Service\SetupIntentService $setupIntents
 * @property \DRStripe\Service\ShippingRateService $shippingRates
 * @property \DRStripe\Service\Sigma\SigmaServiceFactory $sigma
 * @property \DRStripe\Service\SkuService $skus
 * @property \DRStripe\Service\SourceService $sources
 * @property \DRStripe\Service\SubscriptionItemService $subscriptionItems
 * @property \DRStripe\Service\SubscriptionScheduleService $subscriptionSchedules
 * @property \DRStripe\Service\SubscriptionService $subscriptions
 * @property \DRStripe\Service\TaxCodeService $taxCodes
 * @property \DRStripe\Service\TaxRateService $taxRates
 * @property \DRStripe\Service\Terminal\TerminalServiceFactory $terminal
 * @property \DRStripe\Service\TestHelpers\TestHelpersServiceFactory $testHelpers
 * @property \DRStripe\Service\TokenService $tokens
 * @property \DRStripe\Service\TopupService $topups
 * @property \DRStripe\Service\TransferService $transfers
 * @property \DRStripe\Service\Treasury\TreasuryServiceFactory $treasury
 * @property \DRStripe\Service\WebhookEndpointService $webhookEndpoints
 */
class StripeClient extends BaseStripeClient
{
    /**
     * @var \DRStripe\Service\CoreServiceFactory
     */
    private $coreServiceFactory;

    public function __get($name)
    {
        if (null === $this->coreServiceFactory) {
            $this->coreServiceFactory = new \DRStripe\Service\CoreServiceFactory($this);
        }

        return $this->coreServiceFactory->__get($name);
    }
}
