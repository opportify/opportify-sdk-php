<?php

namespace Opportify\Sdk;

use OpenAPI\Client\Configuration as ApiConfiguration;
use OpenAPI\Client\Api\EmailInsightsApi;
use OpenAPI\Client\Model\AnalyzeEmailRequest;
use GuzzleHttp\Client;

class EmailInsights
{
    private ApiConfiguration $config;
    private ?EmailInsightsApi $apiInstance = null;
    private bool $debugMode = false;
    protected string $host = 'https://api.opportify.ai';
    protected string $prefix = 'insights';
    protected string $version = 'v1';
    protected string $finalUrl;
    private bool $configChanged = false; // Tracks if configuration was modified

    /**
     * EmailInsights constructor.
     *
     * @param string $apiKey
     * @param EmailInsightsApi|null $apiInstance (Optional for testing)
     */
    public function __construct(string $apiKey, ?EmailInsightsApi $apiInstance = null)
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
     * 
     * @param bool $firstRun
     * @return void
     */
    private function refreshApiInstance(bool $firstRun = false): void
    {
        if (!$this->configChanged && !$firstRun) {
            return; 
        }

        $this->updateFinalUrl();
        $this->config->setHost($this->finalUrl);
        $this->apiInstance = new EmailInsightsApi(
            new Client(["debug" => $this->debugMode]),
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
     * Analyze the email with given parameters.
     *
     * @param array $params
     * @return object
     * @throws \Exception
     */
    public function analyze(array $params): object
    {
        // Ensure latest config before API call
        $this->refreshApiInstance();

        $params = $this->normalizeRequest($params);
        $analyzeEmailRequest = new AnalyzeEmailRequest($params);

        $result = $this->apiInstance->analyzeEmail($analyzeEmailRequest);
        return $result->jsonSerialize();
    }

    /**
     * Set the host.
     *
     * @param string $host
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
     * Set the version.
     *
     * @param string $version
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
     * Set the debug mode.
     *
     * @param bool $debugMode
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
     * Normalize the request parameters.
     *
     * @param array $params
     * @return array
     */
    private function normalizeRequest(array $params): array
    {
        $normalized = [];
        $normalized['email'] = (string) $params['email'];

        if (isset($params['enableAi'])) {
            $params['enable_ai'] = $params['enableAi'];
            unset($params['enableAi']);
        }

        if (isset($params['enableAutoCorrection'])) {
            $params['enable_auto_correction'] = filter_var($params['enableAutoCorrection'], FILTER_VALIDATE_BOOLEAN);
            unset($params['enableAutoCorrection']);
        }

        $normalized['enable_ai'] = filter_var($params['enable_ai'], FILTER_VALIDATE_BOOLEAN);
        $normalized['enable_auto_correction'] = filter_var($params['enable_auto_correction'], FILTER_VALIDATE_BOOLEAN);

        return $normalized;
    }
}
