<?php

namespace Opportify\Sdk;

use GuzzleHttp\Client;
use OpenAPI\FraudIntel\Client\Api\FraudProtectionApi as FraudProtectionApiClient;
use OpenAPI\FraudIntel\Client\Configuration as ApiConfiguration;
use OpenAPI\FraudIntel\Client\Model\AnalyzeFraudRequest;

class FraudProtection
{
    private ApiConfiguration $config;

    private ?FraudProtectionApiClient $apiInstance = null;

    private bool $debugMode = false;

    protected string $host = 'https://api.opportify.ai';

    protected string $prefix = 'intel';

    protected string $version = 'v1';

    protected string $finalUrl;

    private bool $configChanged = false;

    /**
     * FraudProtection constructor.
     *
     * @param  FraudProtectionApiClient|null  $apiInstance  (Optional for testing)
     */
    public function __construct(string $apiKey, ?FraudProtectionApiClient $apiInstance = null)
    {
        $this->config = ApiConfiguration::getDefaultConfiguration();
        $this->config->setApiKey('x-opportify-token', $apiKey);

        $this->updateFinalUrl();

        if ($apiInstance) {
            $this->apiInstance = $apiInstance;
        } else {
            $this->refreshApiInstance(true);
        }
    }

    /**
     * Ensures apiInstance is updated only if config has changed.
     */
    private function refreshApiInstance(bool $firstRun = false): void
    {
        if (!$this->configChanged && !$firstRun) {
            return;
        }

        $this->updateFinalUrl();
        $this->config->setHost($this->finalUrl);
        $this->apiInstance = new FraudProtectionApiClient(
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
     * Analyze a form submission for fraud risk.
     *
     * Sends mapped submission fields and receives a complete risk report covering
     * email, IP, content, session, velocity, and geographic cross-referencing
     * signals in a single HTTP call.
     *
     * @param  array  $params  Submission fields to analyze. Supported keys:
     *                         - email (string)
     *                         - phone1, phone2 (string)
     *                         - user_ip / userIp (string)
     *                         - first_name / firstName, last_name / lastName, full_name / fullName (string)
     *                         - username, company_name / companyName, website (string)
     *                         - subject, message (string)
     *                         - address1, address2, city, region, country, postal_code / postalCode (string)
     *                         - origin (string)
     *                         - submission_type / submissionType (string)
     *                         - form_data / formData (array)
     *                         - opportify_token / opportifyToken (string)
     *                         - opportify_form_uuid / opportifyFormUUID (string)
     *                         - enable_ai / enableAi (bool, default: true)
     *
     * @throws \Exception
     */
    public function analyze(array $params): object
    {
        $this->refreshApiInstance();

        $normalized = $this->normalizeRequest($params);
        $request = new AnalyzeFraudRequest($normalized);

        $result = $this->apiInstance->analyzeFraud($request);

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
        if ($this->prefix !== trim($prefix, '/')) {
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
     * Normalize request params: support both snake_case and camelCase keys,
     * apply defaults, and validate required constraints.
     */
    private function normalizeRequest(array $params): array
    {
        $normalized = [];

        // String fields: accept snake_case or camelCase
        $stringFields = [
            'email' => ['email'],
            'phone1' => ['phone1'],
            'phone2' => ['phone2'],
            'user_ip' => ['user_ip', 'userIp'],
            'first_name' => ['first_name', 'firstName'],
            'last_name' => ['last_name', 'lastName'],
            'full_name' => ['full_name', 'fullName'],
            'username' => ['username'],
            'company_name' => ['company_name', 'companyName'],
            'website' => ['website'],
            'subject' => ['subject'],
            'message' => ['message'],
            'address1' => ['address1'],
            'address2' => ['address2'],
            'city' => ['city'],
            'region' => ['region'],
            'country' => ['country'],
            'postal_code' => ['postal_code', 'postalCode'],
            'origin' => ['origin'],
            'submission_type' => ['submission_type', 'submissionType'],
            'opportify_token' => ['opportify_token', 'opportifyToken'],
            'opportify_form_uuid' => ['opportify_form_uuid', 'opportifyFormUUID', 'opportifyFormUuid'],
        ];

        foreach ($stringFields as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $params) && $params[$alias] !== null) {
                    $normalized[$canonical] = (string) $params[$alias];
                    break;
                }
            }
        }

        // form_data: accept snake_case or camelCase, must be array
        foreach (['form_data', 'formData'] as $key) {
            if (array_key_exists($key, $params) && $params[$key] !== null) {
                if (!is_array($params[$key])) {
                    throw new \InvalidArgumentException('form_data must be provided as an array.');
                }
                $normalized['form_data'] = $params[$key];
                break;
            }
        }

        // enable_ai: default true
        $normalized['enable_ai'] = $this->resolveBoolean($params, ['enable_ai', 'enableAi'], true);

        return $normalized;
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
