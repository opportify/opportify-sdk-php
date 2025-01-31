<?php

namespace Opportify\Sdk;

use GuzzleHttp\Client;
use OpenAPI\Client\Api\EmailInsightsApi;
use OpenAPI\Client\Configuration as ApiConfiguration;
use OpenAPI\Client\Model\AnalyzeEmailRequest;

class EmailInsights
{
    private ApiConfiguration $config;

    private EmailInsightsApi $apiInstance;

    private bool $debugMode = false;

    protected string $host = 'https://api.opportify.ai';

    protected string $prefix = 'insights';

    protected string $version = 'v1';

    protected string $finalUrl;

    /**
     * EmailInsights constructor.
     */
    public function __construct(string $apiKey)
    {
        $this->config = ApiConfiguration::getDefaultConfiguration();
        $this->config->setApiKey('x-opportify-token', $apiKey);
    }

    /**
     * Analyze the email with given parameters.
     *
     * @throws \Exception
     */
    public function analyze(array $params): object
    {
        $params = $this->normalizeRequest($params);

        $this->finalUrl = $this->host.'/'.$this->prefix.'/'.$this->version;

        $this->config->setHost($this->finalUrl);

        $this->apiInstance = new EmailInsightsApi(
            new Client(['debug' => $this->debugMode]),
            $this->config
        );

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
        $this->host = $host;

        return $this;
    }

    /**
     * Set the version.
     *
     * @return $this
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Set the debug mode.
     */
    public function setDebugMode(bool $debugMode): self
    {
        $this->debugMode = $debugMode;

        return $this;
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
}
