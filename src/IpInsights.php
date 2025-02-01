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

    private ?IpInsightsApi $apiInstance = null;

    private bool $debugMode = false;

    protected string $host = 'https://api.opportify.ai';

    protected string $prefix = 'insights';

    protected string $version = 'v1';

    protected string $finalUrl;

    private bool $configChanged = false; // Tracks if config was modified

    /**
     * IpInsights constructor.
     */
    public function __construct(string $apiKey, ?IpInsightsApi $apiInstance = null)
    {
        $this->config = ApiConfiguration::getDefaultConfiguration();
        $this->config->setApiKey('x-opportify-token', $apiKey);

        $this->updateFinalUrl();

        // Allow passing a mock API instance for testing
        if ($apiInstance) {
            $this->apiInstance = $apiInstance;
        } else {
            $this->refreshApiInstance(true);
        }
    }

    /**
     * Ensures `apiInstance` is updated only if config has changed.
     */
    private function refreshApiInstance(bool $firstRun = false): void
    {
        if (!$this->configChanged && !$firstRun) {
            return;
        }

        $this->updateFinalUrl();
        $this->config->setHost($this->finalUrl);
        $this->apiInstance = new IpInsightsApi(
            new Client(['debug' => $this->debugMode]),
            $this->config
        );

        $this->configChanged = false;
    }

    /**
     * Updates the final URL used for API requests.
     */
    private function updateFinalUrl(): void
    {
        $this->finalUrl = "{$this->host}/{$this->prefix}/{$this->version}";
    }

    /**
     * Analyzes the IP based on the provided parameters.
     *
     * @throws \Exception
     */
    public function analyze(array $params): object
    {
        // Ensure latest config before API call
        $this->refreshApiInstance();

        $params = $this->normalizeRequest($params);
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
        if ($this->host !== $host) {
            $this->host = $host;
            $this->configChanged = true;
        }

        return $this;
    }

    /**
     * Sets the version.
     *
     * @return $this
     */
    public function setVersion(string $version): self
    {
        if ($this->version !== $version) {
            $this->version = $version;
            $this->configChanged = true;
        }

        return $this;
    }

    /**
     * Sets the debug mode.
     *
     * @return $this
     */
    public function setDebugMode(bool $debugMode): self
    {
        if ($this->debugMode !== $debugMode) {
            $this->debugMode = $debugMode;
            $this->configChanged = true;
        }

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
