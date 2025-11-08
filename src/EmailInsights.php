<?php

namespace Opportify\Sdk;

use GuzzleHttp\Client;
use OpenAPI\Client\Api\EmailInsightsApi;
use OpenAPI\Client\Configuration as ApiConfiguration;
use OpenAPI\Client\Model\AnalyzeEmailRequest;
use OpenAPI\Client\Model\BatchAnalyzeEmailsRequest;
use OpenAPI\Client\Model\ExportRequest;

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
     * @param  EmailInsightsApi|null  $apiInstance  (Optional for testing)
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
     */
    private function refreshApiInstance(bool $firstRun = false): void
    {
        if (!$this->configChanged && !$firstRun) {
            return;
        }

        $this->updateFinalUrl();
        $this->config->setHost($this->finalUrl);
        $this->apiInstance = new EmailInsightsApi(
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
     * Analyze the email with given parameters.
     *
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
     * Set the prefix.
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
     * Set the debug mode.
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
     * Submit a batch of emails for analysis.
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
            $batchAnalyzeEmailsRequest = new BatchAnalyzeEmailsRequest($params);
            $result = $this->apiInstance->batchAnalyzeEmails($batchAnalyzeEmailsRequest, $contentType);
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

                $enableAutoCorrection = $this->resolveBoolean($params, ['enable_auto_correction', 'enableAutoCorrection']);
                if ($enableAutoCorrection !== null) {
                    $multipartContents[] = [
                        'name' => 'enable_auto_correction',
                        'contents' => $enableAutoCorrection ? 'true' : 'false',
                    ];
                }

                // Add name parameter if provided
                if (isset($params['name'])) {
                    $multipartContents[] = [
                        'name' => 'name',
                        'contents' => (string) $params['name'],
                    ];
                }

                // Create MultipartStream that OpenAPI client expects
                $multipartStream = new \GuzzleHttp\Psr7\MultipartStream($multipartContents);

                $result = $this->apiInstance->batchAnalyzeEmails($multipartStream, $contentType);
            } finally {
                // Always close the file handle to prevent resource leaks
                fclose($fileHandle);
            }
        } elseif ($contentType === 'text/plain') {
            // For plain text with one email per line
            if (!isset($params['text'])) {
                throw new \InvalidArgumentException('Text parameter is required for text/plain content type');
            }

            $result = $this->apiInstance->batchAnalyzeEmails($params['text'], $contentType);
        } else {
            throw new \InvalidArgumentException('Unsupported content type: '.$contentType);
        }

        return $result->jsonSerialize();
    }

    /**
     * Submit a batch of emails for analysis using a file.
     *
     * @param  string  $filePath  Path to the file containing emails (CSV or text)
     * @param  array  $options  Additional options like enableAi, enableAutoCorrection
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

        $result = $this->apiInstance->getEmailBatchStatus($jobId);

        return $result->jsonSerialize();
    }

    /**
     * Request a custom export for a completed email batch job.
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

        $result = $this->apiInstance->createEmailBatchExport($jobId, $exportRequest);

        return $result->jsonSerialize();
    }

    /**
     * Retrieve the status of a previously requested email batch export.
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

        $result = $this->apiInstance->getEmailBatchExportStatus($jobId, $exportId);

        return $result->jsonSerialize();
    }

    /**
     * Normalize the request parameters.
     */
    private function normalizeRequest(array $params): array
    {
        if (!array_key_exists('email', $params)) {
            throw new \InvalidArgumentException('The email parameter is required for analysis.');
        }

        $normalized = [];
        $normalized['email'] = (string) $params['email'];
        $normalized['enable_ai'] = $this->resolveBoolean($params, ['enable_ai', 'enableAi'], true);
        $normalized['enable_auto_correction'] = $this->resolveBoolean($params, ['enable_auto_correction', 'enableAutoCorrection'], true);
        $normalized['enable_domain_enrichment'] = $this->resolveBoolean($params, ['enable_domain_enrichment', 'enableDomainEnrichment'], true);

        return $normalized;
    }

    /**
     * Normalize the batch request parameters.
     */
    private function normalizeBatchRequest(array $params): array
    {
        $normalized = [];
        $emails = $params['emails'] ?? [];
        if (!is_array($emails)) {
            throw new \InvalidArgumentException('The emails parameter must be provided as an array.');
        }

        $normalized['emails'] = array_map(static fn ($email) => (string) $email, $emails);

        $enableAi = $this->resolveBoolean($params, ['enable_ai', 'enableAi']);
        if ($enableAi !== null) {
            $normalized['enable_ai'] = $enableAi;
        }

        $enableAutoCorrection = $this->resolveBoolean($params, ['enable_auto_correction', 'enableAutoCorrection']);
        if ($enableAutoCorrection !== null) {
            $normalized['enable_auto_correction'] = $enableAutoCorrection;
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
