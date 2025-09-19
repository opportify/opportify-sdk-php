<?php

namespace Opportify\Sdk;

use GuzzleHttp\Client;
use OpenAPI\Client\Api\EmailInsightsApi;
use OpenAPI\Client\Configuration as ApiConfiguration;
use OpenAPI\Client\Model\AnalyzeEmailRequest;
use OpenAPI\Client\Model\BatchAnalyzeEmailsRequest;

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
        $this->finalUrl = "{$this->host}/{$this->prefix}/{$this->version}";
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

            if (isset($params['enable_auto_correction'])) {
                $multipartParams['enable_auto_correction'] = $params['enable_auto_correction'];
            } elseif (isset($params['enableAutoCorrection'])) {
                $multipartParams['enable_auto_correction'] = $params['enableAutoCorrection'];
            }

            $result = $this->apiInstance->batchAnalyzeEmails($multipartParams, $contentType);
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

        $result = $this->apiInstance->getEmailBatchStatus($jobId);

        return $result->jsonSerialize();
    }

    /**
     * Normalize the request parameters.
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

    /**
     * Normalize the batch request parameters.
     */
    private function normalizeBatchRequest(array $params): array
    {
        $normalized = [];
        $normalized['emails'] = $params['emails'] ?? [];

        if (isset($params['enableAi'])) {
            $params['enable_ai'] = $params['enableAi'];
            unset($params['enableAi']);
        }

        if (isset($params['enableAutoCorrection'])) {
            $params['enable_auto_correction'] = filter_var($params['enableAutoCorrection'], FILTER_VALIDATE_BOOLEAN);
            unset($params['enableAutoCorrection']);
        }

        if (isset($params['enable_ai'])) {
            $normalized['enable_ai'] = filter_var($params['enable_ai'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($params['enable_auto_correction'])) {
            $normalized['enable_auto_correction'] = filter_var($params['enable_auto_correction'], FILTER_VALIDATE_BOOLEAN);
        }

        return $normalized;
    }
}
