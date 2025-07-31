<?php

namespace Opportify\Sdk;

use GuzzleHttp\Client;
use OpenAPI\Client\Api\IPInsightsApi as IpInsightsApi;
use OpenAPI\Client\Configuration as ApiConfiguration;
use OpenAPI\Client\Model\AnalyzeIpRequest;
use OpenAPI\Client\Model\BatchAnalyzeIpsRequest;

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
     * Submit a batch of IPs for analysis.
     *
     * @param  array  $params  Parameters for the batch analysis
     * @param  string|null  $contentType  Optional content type (defaults to application/json)
     *
     * @throws \Exception
     */
    public function batchAnalyze(array $params, ?string $contentType = null): object
    {
        // Ensure latest config before API call
        $this->refreshApiInstance();

        // Default to application/json if not specified
        $contentType = $contentType ?? 'application/json';

        if ($contentType === 'application/json') {
            $params = $this->normalizeBatchRequest($params);
            $batchAnalyzeIpsRequest = new BatchAnalyzeIpsRequest($params);
            $result = $this->apiInstance->batchAnalyzeIps($batchAnalyzeIpsRequest, $contentType);
        } elseif ($contentType === 'multipart/form-data') {
            // For file uploads
            if (!isset($params['file']) || !file_exists($params['file'])) {
                throw new \InvalidArgumentException('File parameter is required and must be a valid file path');
            }

            // Create a multipart request with the file
            $fileContent = file_get_contents($params['file']);
            if ($fileContent === false) {
                throw new \InvalidArgumentException('Unable to read file content');
            }

            // Create a new request with the file
            $multipartParams = [
                'file' => $fileContent,
            ];

            // Add optional parameters
            if (isset($params['enable_ai'])) {
                $multipartParams['enable_ai'] = $params['enable_ai'];
            } elseif (isset($params['enableAi'])) {
                $multipartParams['enable_ai'] = $params['enableAi'];
            }

            $result = $this->apiInstance->batchAnalyzeIps($multipartParams, $contentType);
        } elseif ($contentType === 'text/plain') {
            // For plain text with one IP per line
            if (!isset($params['text'])) {
                throw new \InvalidArgumentException('Text parameter is required for text/plain content type');
            }

            $result = $this->apiInstance->batchAnalyzeIps($params['text'], $contentType);
        } else {
            throw new \InvalidArgumentException('Unsupported content type: '.$contentType);
        }

        return $result->jsonSerialize();
    }

    /**
     * Submit a batch of IPs for analysis using a file.
     *
     * @param  string  $filePath  Path to the file containing IPs (CSV or text)
     * @param  array  $options  Additional options like enableAi
     *
     * @throws \Exception
     */
    public function batchAnalyzeFile(string $filePath, array $options = []): object
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $contentType = $extension === 'csv' ? 'multipart/form-data' : 'text/plain';

        if ($contentType === 'multipart/form-data') {
            return $this->batchAnalyze(['file' => $filePath] + $options, $contentType);
        } else {
            // For text files, read the content and pass it directly
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new \InvalidArgumentException('Unable to read file content');
            }

            return $this->batchAnalyze(['text' => $content] + $options, $contentType);
        }
    }

    /**
     * Get the status of a batch job.
     *
     * @throws \Exception
     */
    public function getBatchStatus(string $jobId): object
    {
        // Ensure latest config before API call
        $this->refreshApiInstance();

        $result = $this->apiInstance->getIpBatchStatus($jobId);

        return $result->jsonSerialize();
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

    /**
     * Normalizes the batch request parameters.
     */
    private function normalizeBatchRequest(array $params): array
    {
        $normalized = [];
        $normalized['ips'] = $params['ips'] ?? [];

        if (isset($params['enableAi'])) {
            $params['enable_ai'] = $params['enableAi'];
            unset($params['enableAi']);
        }

        if (isset($params['enable_ai'])) {
            $normalized['enable_ai'] = filter_var($params['enable_ai'], FILTER_VALIDATE_BOOLEAN);
        }

        return $normalized;
    }
}
