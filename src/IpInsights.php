<?php

namespace Opportify\Sdk;

use OpenAPI\Client\Configuration as ApiConfiguration;
use OpenAPI\Client\ApiException;
use OpenAPI\Client\Api\IpInsightsApi;
use OpenAPI\Client\Model\AnalyzeIpRequest;
use GuzzleHttp\Client;

/**
 * Class IpInsights
 * @package Opportify\Sdk
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
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->config = ApiConfiguration::getDefaultConfiguration();
        $this->config->setApiKey('x-opportify-token', $apiKey);
    }

    /**
     * Analyzes the IP based on the provided parameters.
     *
     * @param array $params
     * @return object
     * @throws \Exception
     */
    public function analyze(array $params): object
    {
        $params = $this->normalizeRequest($params);

        $this->finalUrl = $this->host . '/' . $this->prefix . '/' . $this->version;

        $this->config->setHost($this->finalUrl);

        $this->apiInstance = new IpInsightsApi(
            new Client(["debug" => $this->debugMode]),
            $this->config
        );

        $analyzeIpRequest = new AnalyzeIpRequest($params);

        try {
            $result = $this->apiInstance->analyzeIp($analyzeIpRequest);
            return $result->jsonSerialize();
        } catch (ApiException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Sets the host.
     *
     * @param string $host
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
     * @param string $version
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
     * @param bool $debugMode
     * @return $this
     */
    public function setDebugMode(bool $debugMode): self
    {
        $this->debugMode = $debugMode;
        return $this;
    }

    /**
     * Normalizes the request parameters.
     *
     * @param array $params
     * @return array
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
