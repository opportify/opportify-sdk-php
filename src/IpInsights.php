<?php

namespace Opportify\Sdk;

use GuzzleHttp\Client;
use OpenAPI\Client\Api\IPInsightsApi as IpInsightsApi;
use OpenAPI\Client\Configuration as ApiConfiguration;
use OpenAPI\Client\Model\AnalyzeIpRequest;
use OpenAPI\Client\Model\BatchAnalyzeIpsRequest;
use OpenAPI\Client\Model\ExportRequest;

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
        $base = rtrim($this->host, '/');
        $segments = [];

        $prefix = trim($this->prefix, '/');
        if ($prefix !== '') {
            $segments[] = $prefix;
        }

        $version = trim($this->version, '/');
        if ($version !== '') {
            $segments[] = $version;
        }

        $this->finalUrl = $base.(count($segments) ? '/'.implode('/', $segments) : '');
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
     * Sets the prefix.
     *
     * @return $this
     */
    public function setPrefix(string $prefix): self
    {
        if ($this->prefix !== $prefix) {
            $this->prefix = trim($prefix, '/');
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
            if (!isset($params['file']) || !file_exists($params['file'])) {
                throw new \InvalidArgumentException('File parameter is required and must be a valid file path');
            }

            // Open file handle and check for errors
            $fileHandle = fopen($params['file'], 'r');
            if ($fileHandle === false) {
                throw new \InvalidArgumentException('Unable to open file for reading: '.$params['file']);
            }

            try {
                // Create multipart contents array in the format expected by Guzzle MultipartStream
                $multipartContents = [
                    [
                        'name' => 'file',
                        'contents' => $fileHandle,
                        'filename' => basename($params['file']),
                    ],
                ];

                // Add optional parameters as separate parts
                $enableAi = $this->resolveBoolean($params, ['enable_ai', 'enableAi']);
                if ($enableAi !== null) {
                    $multipartContents[] = [
                        'name' => 'enable_ai',
                        'contents' => $enableAi ? 'true' : 'false',
                    ];
                }

                // Add name parameter if provided
                if (isset($params['name'])) {
                    $multipartContents[] = [
                        'name' => 'name',
                        'contents' => (string) $params['name'],
                    ];
                }

                $multipartStream = new \GuzzleHttp\Psr7\MultipartStream($multipartContents);

                $result = $this->apiInstance->batchAnalyzeIps($multipartStream, $contentType);
            } finally {
                // Always close the file handle to prevent resource leaks
                fclose($fileHandle);
            }
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
        return $this->batchAnalyze(['file' => $filePath] + $options, 'multipart/form-data');
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
     * Request a custom export for a completed IP batch job.
     *
     * @throws \Exception
     */
    public function createBatchExport(string $jobId, array $payload = []): object
    {
        $this->refreshApiInstance();

        $jobId = trim($jobId);
        if ($jobId === '') {
            throw new \InvalidArgumentException('Job ID cannot be empty when creating an export.');
        }

        $normalizedPayload = $this->normalizeExportRequest($payload);
        $exportRequest = empty($normalizedPayload) ? null : new ExportRequest($normalizedPayload);

        $result = $this->apiInstance->createIpBatchExport($jobId, $exportRequest);

        return $result->jsonSerialize();
    }

    /**
     * Retrieve the status of a previously requested IP batch export.
     *
     * @throws \Exception
     */
    public function getBatchExportStatus(string $jobId, string $exportId): object
    {
        $this->refreshApiInstance();

        $jobId = trim($jobId);
        $exportId = trim($exportId);

        if ($jobId === '' || $exportId === '') {
            throw new \InvalidArgumentException('Job ID and export ID are required to fetch export status.');
        }

        $result = $this->apiInstance->getIpBatchExportStatus($jobId, $exportId);

        return $result->jsonSerialize();
    }

    /**
     * Normalizes the request parameters.
     */
    private function normalizeRequest(array $params): array
    {
        if (!array_key_exists('ip', $params)) {
            throw new \InvalidArgumentException('The ip parameter is required for analysis.');
        }

        $normalized = [];
        $normalized['ip'] = (string) $params['ip'];
        $normalized['enable_ai'] = $this->resolveBoolean($params, ['enable_ai', 'enableAi'], true);

        return $normalized;
    }

    /**
     * Normalizes the batch request parameters.
     */
    private function normalizeBatchRequest(array $params): array
    {
        $normalized = [];
        $ips = $params['ips'] ?? [];
        if (!is_array($ips)) {
            throw new \InvalidArgumentException('The ips parameter must be provided as an array.');
        }

        $normalized['ips'] = array_map(static fn ($ip) => (string) $ip, $ips);

        $enableAi = $this->resolveBoolean($params, ['enable_ai', 'enableAi']);
        if ($enableAi !== null) {
            $normalized['enable_ai'] = $enableAi;
        }

        // Add name parameter if provided
        if (isset($params['name'])) {
            $normalized['name'] = (string) $params['name'];
        }

        return $normalized;
    }

    /**
     * Normalize export payload for batch exports.
     */
    private function normalizeExportRequest(array $params): array
    {
        $normalized = [];

        if ($this->hasAnyKey($params, ['export_type', 'exportType'])) {
            $value = $params['export_type'] ?? $params['exportType'];
            $normalized['export_type'] = strtolower((string) $value);
        }

        if (array_key_exists('filters', $params) && $params['filters'] !== null) {
            if (!is_array($params['filters'])) {
                throw new \InvalidArgumentException('Filters must be provided as an array.');
            }

            $normalized['filters'] = $params['filters'];
        }

        if (array_key_exists('columns', $params) && $params['columns'] !== null) {
            if (!is_array($params['columns'])) {
                throw new \InvalidArgumentException('Columns must be provided as an array.');
            }

            $normalized['columns'] = array_map(static fn ($column) => (string) $column, $params['columns']);
        }

        return $normalized;
    }

    private function hasAnyKey(array $params, array $keys): bool
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $params)) {
                return true;
            }
        }

        return false;
    }

    private function resolveBoolean(array $params, array $keys, ?bool $default = null): ?bool
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $params)) {
                return $this->toBoolean($params[$key], $key);
            }
        }

        return $default;
    }

    private function toBoolean(mixed $value, string $parameterName): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === 1 || $value === 0 || $value === '1' || $value === '0') {
            return (bool) $value;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($filtered === null) {
            throw new \InvalidArgumentException(sprintf('Invalid boolean value provided for %s', $parameterName));
        }

        return $filtered;
    }
}
