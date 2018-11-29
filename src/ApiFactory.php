<?php
declare(strict_types=1);

namespace Nopolabs\EBay;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Nopolabs\EBay\Commerce\Catalog\Api\ChangeRequestApi;
use Nopolabs\EBay\Commerce\Catalog\Api\ProductApi;
use Nopolabs\EBay\Commerce\Catalog\Api\ProductMetadataApi;
use Nopolabs\EBay\Commerce\Catalog\Api\ProductSummaryApi;
use Nopolabs\EBay\Commerce\Taxonomy\Api\CategoryTreeApi;
use Nopolabs\EBay\Commerce\Translation\Api\LanguageApi;
use Nopolabs\EBay\Developer\Analytics\Api\RateLimitApi;
use Nopolabs\EBay\Developer\Analytics\Api\UserRateLimitApi;
use Nopolabs\EBay\Sell\Account\Api\FulfillmentPolicyApi;
use Nopolabs\EBay\Sell\Account\Api\PaymentPolicyApi;
use Nopolabs\EBay\Sell\Account\Api\PaymentsProgramApi;
use Nopolabs\EBay\Sell\Account\Api\PrivilegeApi;
use Nopolabs\EBay\Sell\Account\Api\ProgramApi;
use Nopolabs\EBay\Sell\Account\Api\RateTableApi;
use Nopolabs\EBay\Sell\Account\Api\ReturnPolicyApi;
use Nopolabs\EBay\Sell\Account\Api\SalesTaxApi;
use Nopolabs\EBay\Sell\Analytics\Api\SellerStandardsProfileApi;
use Nopolabs\EBay\Sell\Analytics\Api\TrafficReportApi;
use Nopolabs\EBay\Sell\Compliance\Api\ListingViolationApi;
use Nopolabs\EBay\Sell\Compliance\Api\ListingViolationSummaryApi;
use Nopolabs\EBay\Sell\Fulfillment\Api\OrderApi;
use Nopolabs\EBay\Sell\Fulfillment\Api\ShippingFulfillmentApi;
use Nopolabs\EBay\Sell\Inventory\Api\InventoryItemApi;
use Nopolabs\EBay\Sell\Inventory\Api\InventoryItemGroupApi;
use Nopolabs\EBay\Sell\Inventory\Api\ListingApi;
use Nopolabs\EBay\Sell\Inventory\Api\LocationApi;
use Nopolabs\EBay\Sell\Inventory\Api\OfferApi;
use Nopolabs\EBay\Sell\Inventory\Api\ProductCompatibilityApi;
use Nopolabs\EBay\Sell\Logistics\Api\ShipmentApi;
use Nopolabs\EBay\Sell\Logistics\Api\ShippingQuoteApi;
use Nopolabs\EBay\Sell\Marketing\Api\AdApi;
use Nopolabs\EBay\Sell\Marketing\Api\AdReportApi;
use Nopolabs\EBay\Sell\Marketing\Api\AdReportMetadataApi;
use Nopolabs\EBay\Sell\Marketing\Api\AdReportTaskApi;
use Nopolabs\EBay\Sell\Marketing\Api\CampaignApi;
use Nopolabs\EBay\Sell\Marketing\Api\ItemPriceMarkdownApi;
use Nopolabs\EBay\Sell\Marketing\Api\ItemPromotionApi;
use Nopolabs\EBay\Sell\Marketing\Api\PromotionApi;
use Nopolabs\EBay\Sell\Marketing\Api\PromotionReportApi;
use Nopolabs\EBay\Sell\Marketing\Api\PromotionSummaryReportApi;
use Nopolabs\EBay\Sell\Metadata\Api\CountryApi;
use Nopolabs\EBay\Sell\Metadata\Api\MarketplaceApi;
use Sainsburys\Guzzle\Oauth2\GrantType\ClientCredentials;
use Sainsburys\Guzzle\Oauth2\GrantType\RefreshToken;
use Sainsburys\Guzzle\Oauth2\Middleware\OAuthMiddleware;

class ApiFactory
{
    public const API_EBAY_COM = 'https://api.ebay.com';
    public const DEFAULT_SCOPE = 'https://api.ebay.com/oauth/api_scope';

    private $baseUri;
    private $scope;
    private $clientId;
    private $clientSecret;

    private $oauth2Client;
    private $sellMetadataConfig;
    private $sellMarketingConfig;
    private $sellLogisticsConfig;
    private $sellInventoryConfig;
    private $sellFulfillmentConfig;
    private $sellComplianceConfig;
    private $sellAnalyticsConfig;
    private $sellAccountConfig;
    private $developerAnalyticsConfig;
    private $commerceTranslationConfig;
    private $commerceTaxonomyConfig;
    private $commerceCatalogConfig;

    public function __construct(ApiConfig $config)
    {
        $this->baseUri = $config->getBaseUri();
        $this->clientId = $config->getClientId();
        $this->clientSecret = $config->getClientSecret();
        $this->scope = $config->getScope();
    }

    public function getChangeRequestApi() : ChangeRequestApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getCommerceCatalogConfiguration();

        return new ChangeRequestApi($client, $config);
    }

    public function getProductApi() : ProductApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getCommerceCatalogConfiguration();

        return new ProductApi($client, $config);
    }

    public function getProductMetadataApi() : ProductMetadataApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getCommerceCatalogConfiguration();

        return new ProductMetadataApi($client, $config);
    }

    public function getProductSummaryApi() : ProductSummaryApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getCommerceCatalogConfiguration();

        return new ProductSummaryApi($client, $config);
    }

    public function getCategoryTreeApi() : CategoryTreeApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getCommerceTaxonomyConfiguration();

        return new CategoryTreeApi($client, $config);
    }

    public function getLanguageApi() : LanguageApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getCommerceTranslationConfiguration();

        return new LanguageApi($client, $config);
    }

    public function getRateLimitApi() : RateLimitApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getDeveloperAnalyticsConfiguration();

        return new RateLimitApi($client, $config);
    }

    public function getUserRateLimitApi() : UserRateLimitApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getDeveloperAnalyticsConfiguration();

        return new UserRateLimitApi($client, $config);
    }

    public function getFulfillmentPolicyApi() : FulfillmentPolicyApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellAccountConfiguration();

        return new FulfillmentPolicyApi($client, $config);
    }

    public function getPaymentPolicyApi() : PaymentPolicyApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellAccountConfiguration();

        return new PaymentPolicyApi($client, $config);
    }

    public function getPaymentsProgramApi() : PaymentsProgramApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellAccountConfiguration();

        return new PaymentsProgramApi($client, $config);
    }

    public function getPrivilegeApi() : PrivilegeApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellAccountConfiguration();

        return new PrivilegeApi($client, $config);
    }

    public function getProgramApi() : ProgramApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellAccountConfiguration();

        return new ProgramApi($client, $config);
    }

    public function getRateTableApi() : RateTableApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellAccountConfiguration();

        return new RateTableApi($client, $config);
    }

    public function getReturnPolicyApi() : ReturnPolicyApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellAccountConfiguration();

        return new ReturnPolicyApi($client, $config);
    }

    public function getSalesTaxApi() : SalesTaxApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellAccountConfiguration();

        return new SalesTaxApi($client, $config);
    }

    public function getSellerStandardsProfileApi() : SellerStandardsProfileApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellAnalyticsConfiguration();

        return new SellerStandardsProfileApi($client, $config);
    }

    public function getTrafficReportApi() : TrafficReportApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellAnalyticsConfiguration();

        return new TrafficReportApi($client, $config);
    }

    public function getListingViolationApi() : ListingViolationApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellComplianceConfiguration();

        return new ListingViolationApi($client, $config);
    }

    public function getListingViolationSummaryApi() : ListingViolationSummaryApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellComplianceConfiguration();

        return new ListingViolationSummaryApi($client, $config);
    }

    public function getOrderApi() : OrderApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellFulfillmentConfiguration();

        return new OrderApi($client, $config);
    }

    public function getShippingFulfillmentApi() : ShippingFulfillmentApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellFulfillmentConfiguration();

        return new ShippingFulfillmentApi($client, $config);
    }

    public function getInventoryItemApi() : InventoryItemApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellInventoryConfiguration();

        return new InventoryItemApi($client, $config);
    }

    public function getInventoryItemGroupApi() : InventoryItemGroupApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellInventoryConfiguration();

        return new InventoryItemGroupApi($client, $config);
    }

    public function getListingApi() : ListingApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellInventoryConfiguration();

        return new ListingApi($client, $config);
    }

    public function getLocationApi() : LocationApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellInventoryConfiguration();

        return new LocationApi($client, $config);
    }

    public function getOfferApi() : OfferApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellInventoryConfiguration();

        return new OfferApi($client, $config);
    }

    public function getProductCompatibilityApi() : ProductCompatibilityApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellInventoryConfiguration();

        return new ProductCompatibilityApi($client, $config);
    }

    public function getShipmentApi() : ShipmentApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellLogisticsConfiguration();

        return new ShipmentApi($client, $config);
    }

    public function getShippingQuoteApi() : ShippingQuoteApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellLogisticsConfiguration();

        return new ShippingQuoteApi($client, $config);
    }

    public function getAdApi() : AdApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellMarketingConfiguration();

        return new AdApi($client, $config);
    }

    public function getAdReportApi() : AdReportApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellMarketingConfiguration();

        return new AdReportApi($client, $config);
    }

    public function getAdReportMetadataApi() : AdReportMetadataApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellMarketingConfiguration();

        return new AdReportMetadataApi($client, $config);
    }

    public function getAdReportTaskApi() : AdReportTaskApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellMarketingConfiguration();

        return new AdReportTaskApi($client, $config);
    }

    public function getCampaignApi() : CampaignApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellMarketingConfiguration();

        return new CampaignApi($client, $config);
    }

    public function getItemPriceMarkdownApi() : ItemPriceMarkdownApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellMarketingConfiguration();

        return new ItemPriceMarkdownApi($client, $config);
    }

    public function getItemPromotionApi() : ItemPromotionApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellMarketingConfiguration();

        return new ItemPromotionApi($client, $config);
    }

    public function getPromotionApi() : PromotionApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellMarketingConfiguration();

        return new PromotionApi($client, $config);
    }

    public function getPromotionReportApi() : PromotionReportApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellMarketingConfiguration();

        return new PromotionReportApi($client, $config);
    }

    public function getPromotionSummaryReportApi() : PromotionSummaryReportApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellMarketingConfiguration();

        return new PromotionSummaryReportApi($client, $config);
    }

    public function getCountryApi() : CountryApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellMetadataConfiguration();

        return new CountryApi($client, $config);
    }

    public function getMarketplaceApi() : MarketplaceApi
    {
        $client = $this->getOauth2Client();
        $config = $this->getSellMetadataConfiguration();

        return new MarketplaceApi($client, $config);
    }

    private function getOauth2Client() : Client
    {
        if ($this->oauth2Client === null) {
            $oauthConfig = [
                ClientCredentials::CONFIG_CLIENT_ID => $this->clientId,
                ClientCredentials::CONFIG_CLIENT_SECRET => $this->clientSecret,
                ClientCredentials::CONFIG_TOKEN_URL => '/identity/v1/oauth2/token',
                'scope' => $this->scope,
            ];

            $oauthClient = new Client(['base_uri' => $this->baseUri]);
            $grantType = new ClientCredentials($oauthClient, $oauthConfig);
            $refreshToken = new RefreshToken($oauthClient, $oauthConfig);
            $middleware = new OAuthMiddleware($oauthClient, $grantType, $refreshToken);

            $handlerStack = HandlerStack::create();
            $handlerStack->push($middleware->onBefore());
            $handlerStack->push($middleware->onFailure(5));

            $this->oauth2Client = new Client([
                'handler' => $handlerStack,
                'base_uri' => $this->baseUri,
                'auth' => 'oauth2',
            ]);
        }

        return $this->oauth2Client;
    }

    private function updateHost(string $host) : string
    {
        return $this->baseUri.substr($host, \strlen(self::API_EBAY_COM));
    }

    private function getCommerceCatalogConfiguration() : Commerce\Catalog\Configuration
    {
        if ($this->commerceCatalogConfig === null) {
            $config = Commerce\Catalog\Configuration::getDefaultConfiguration();

            $host = $this->updateHost($config->getHost());

            $this->commerceCatalogConfig = $config->setHost($host)->setAccessToken(null);
        }

        return $this->commerceCatalogConfig;
    }

    private function getCommerceTaxonomyConfiguration() : Commerce\Taxonomy\Configuration
    {
        if ($this->commerceTaxonomyConfig === null) {
            $config = Commerce\Taxonomy\Configuration::getDefaultConfiguration();

            $host = $this->updateHost($config->getHost());

            $this->commerceTaxonomyConfig = $config->setHost($host)->setAccessToken(null);
        }

        return $this->commerceTaxonomyConfig;
    }

    private function getCommerceTranslationConfiguration() : Commerce\Translation\Configuration
    {
        if ($this->commerceTranslationConfig === null) {
            $config = Commerce\Translation\Configuration::getDefaultConfiguration();

            $host = $this->updateHost($config->getHost());

            $this->commerceTranslationConfig = $config->setHost($host)->setAccessToken(null);
        }

        return $this->commerceTranslationConfig;
    }

    private function getDeveloperAnalyticsConfiguration() : Developer\Analytics\Configuration
    {
        if ($this->developerAnalyticsConfig === null) {
            $config = Developer\Analytics\Configuration::getDefaultConfiguration();

            $host = $this->updateHost($config->getHost());

            $this->developerAnalyticsConfig = $config->setHost($host)->setAccessToken(null);
        }

        return $this->developerAnalyticsConfig;
    }

    private function getSellAccountConfiguration() : Sell\Account\Configuration
    {
        if ($this->sellAccountConfig === null) {
            $config = Sell\Account\Configuration::getDefaultConfiguration();

            $host = $this->updateHost($config->getHost());

            $this->sellAccountConfig = $config->setHost($host)->setAccessToken(null);
        }

        return $this->sellAccountConfig;
    }

    private function getSellAnalyticsConfiguration() : Sell\Analytics\Configuration
    {
        if ($this->sellAnalyticsConfig === null) {
            $config = Sell\Analytics\Configuration::getDefaultConfiguration();

            $host = $this->updateHost($config->getHost());

            $this->sellAnalyticsConfig = $config->setHost($host)->setAccessToken(null);
        }

        return $this->sellAnalyticsConfig;
    }

    private function getSellComplianceConfiguration() : Sell\Compliance\Configuration
    {
        if ($this->sellComplianceConfig === null) {
            $config = Sell\Compliance\Configuration::getDefaultConfiguration();

            $host = $this->updateHost($config->getHost());

            $this->sellComplianceConfig = $config->setHost($host)->setAccessToken(null);
        }

        return $this->sellComplianceConfig;
    }

    private function getSellFulfillmentConfiguration() : Sell\Fulfillment\Configuration
    {
        if ($this->sellFulfillmentConfig === null) {
            $config = Sell\Fulfillment\Configuration::getDefaultConfiguration();

            $host = $this->updateHost($config->getHost());

            $this->sellFulfillmentConfig = $config->setHost($host)->setAccessToken(null);
        }

        return $this->sellFulfillmentConfig;
    }

    private function getSellInventoryConfiguration() : Sell\Inventory\Configuration
    {
        if ($this->sellInventoryConfig === null) {
            $config = Sell\Inventory\Configuration::getDefaultConfiguration();

            $host = $this->updateHost($config->getHost());

            $this->sellInventoryConfig = $config->setHost($host)->setAccessToken(null);
        }

        return $this->sellInventoryConfig;
    }

    private function getSellLogisticsConfiguration() : Sell\Logistics\Configuration
    {
        if ($this->sellLogisticsConfig === null) {
            $config = Sell\Logistics\Configuration::getDefaultConfiguration();

            $host = $this->updateHost($config->getHost());

            $this->sellLogisticsConfig = $config->setHost($host)->setAccessToken(null);
        }

        return $this->sellLogisticsConfig;
    }

    private function getSellMarketingConfiguration() : Sell\Marketing\Configuration
    {
        if ($this->sellMarketingConfig === null) {
            $config = Sell\Marketing\Configuration::getDefaultConfiguration();

            $host = $this->updateHost($config->getHost());

            $this->sellMarketingConfig = $config->setHost($host)->setAccessToken(null);
        }

        return $this->sellMarketingConfig;
    }

    private function getSellMetadataConfiguration() : Sell\Metadata\Configuration
    {
        if ($this->sellMetadataConfig === null) {
            $config = Sell\Metadata\Configuration::getDefaultConfiguration();

            $host = $this->updateHost($config->getHost());

            $this->sellMetadataConfig = $config->setHost($host)->setAccessToken(null);
        }

        return $this->sellMetadataConfig;
    }
}
