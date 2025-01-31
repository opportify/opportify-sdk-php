<?php

namespace Opportify\Sdk;

use GuzzleHttp\Client;
use OpenAPI\Client\Api\IpInsightsApi;
use OpenAPI\Client\Configuration as ApiConfiguration;
use OpenAPI\Client\Model\AnalyzeIpRequest;

/**
 * Class IpInsights
 */
class IpInsights
{
    private ApiConfiguration $config;

    private IpInsightsApi $apiInstance;

    private bool $debugMode = false;

    protected string $host = 'https://api.opportify.com';

    protected string $prefix = 'insights';

    protected string $version = 'v1';

    protected string $finalUrl;

    /**
     * IpInsights constructor.
     */
    public function __construct(string $apiKey)
    {
        $this->config = ApiConfiguration::getDefaultConfiguration();
        $this->config->setApiKey('x-opportify-token', $apiKey);
    }

    /**
     * Analyzes the IP based on the provided parameters.
     *
     * @throws \Exception
     */
    public function analyze(array $params): object
    {
        $params = $this->normalizeRequest($params);

        $this->finalUrl = $this->host.'/'.$this->prefix.'/'.$this->version;

        $this->config->setHost($this->finalUrl);

        $this->apiInstance = new IpInsightsApi(
            new Client(['debug' => $this->debugMode]),
            $this->config
        );

        $analyzeIpRequest = new AnalyzeIpRequest($params);

        $result = $this->apiInstance->analyzeIp($analyzeIpRequest);

        return $result->jsonSerialize();

    }

    /**
     * Sets the host.
     *
     * @return $this
     */
    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Sets the version.
     *
     * @return $this
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Sets the debug mode.
     *
     * @return $this
     */
    public function setDebugMode(bool $debugMode): self
    {
        $this->debugMode = $debugMode;

        return $this;
    }

    /**
     * Normalizes the request parameters.
     */
    private function normalizeRequest(array $params): array
    {
        $normalized = [];
        $normalized['ip'] = (string) $params['ip'];

        if (isset($params['enableAi'])) {
            $params['enable_ai'] = $params['enableAi'];
            unset($params['enableAi']);
        }

        $normalized['enable_ai'] = filter_var($params['enable_ai'], FILTER_VALIDATE_BOOLEAN);

        return $normalized;
    }
}
