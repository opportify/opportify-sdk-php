[![Tests](https://img.shields.io/github/actions/workflow/status/opportify/opportify-sdk-php/phpunit.yml?label=tests&style=for-the-badge&labelColor=115e5c)](https://github.com/opportify/opportify-sdk-php/actions/workflows/phpunit.yml)
[![Packagist Downloads](https://img.shields.io/packagist/dt/opportify/opportify-sdk-php?style=for-the-badge&labelColor=115e5c)](https://packagist.org/packages/opportify/opportify-sdk-php)
[![Packagist Version](https://img.shields.io/packagist/v/opportify/opportify-sdk-php?style=for-the-badge&labelColor=115e5c)](https://packagist.org/packages/opportify/opportify-sdk-php)
[![License](https://img.shields.io/github/license/opportify/opportify-sdk-php?color=9cf&style=for-the-badge&labelColor=115e5c)](https://github.com/opportify/opportify-sdk-php/blob/main/LICENSE)

# Opportify-SDK-PHP

## Overview

The **Opportify Insights API** provides access to a powerful and up-to-date platform. With advanced data warehousing and AI-driven capabilities, this API is designed to empower your business to make informed, data-driven decisions and effectively assess potential risks.

[Sign Up Free](https://www.opportify.ai)

### Base URL
Use the following base URL for all API requests:

```plaintext
https://api.opportify.ai/insights/v1/<service>/<endpoint>
```

## Requirements

Requires PHP [v8.1 or later](https://www.php.net/releases).

## Getting Started

First, install Opportify via the Composer package manager:

```shell
composer require opportify/opportify-sdk-php
```

### Calling Email Insights

```php
use Opportify\Sdk\EmailInsights;

$emailInsights = new EmailInsights("YOUR-API-KEY-HERE");

$params = [
    "email" => "test@gmial.com", // *gmial* - just an example to be auto-corrected
    "enableAi" => true,
    "enableAutoCorrection" => true
];

$result = $emailInsights->analyze($params);
```

### Calling IP Insights

```php
use Opportify\Sdk\IpInsights;

$ipInsights = new IpInsights("<YOUR-KEY-HERE>");

$params = [
    "ip" => "3.1.122.82",
    "enableAi" => true
];

$result = $ipInsights->analyze($params);
```


### Batch Analysis (Email & IP)

You can submit multiple emails or IPs in a single request. Batch jobs are processed asynchronously; the response returns a job identifier (`jobId`) you can poll for status.

#### 1. Batch Email Analysis (JSON)

```php
use Opportify\Sdk\EmailInsights;

$emailInsights = new EmailInsights("<YOUR-KEY-HERE>");

$params = [
    'emails' => [
        'one@example.com',
        'two@example.org'
    ],
    'name' => 'Customer Email Validation', // Optional: descriptive name for the job
    'enableAi' => true,
    'enableAutoCorrection' => true
];

// Default content type is application/json
$batch = $emailInsights->batchAnalyze($params);

// Optional: poll status later
$status = $emailInsights->getBatchStatus($batch->jobId);
```

#### 2. Batch Email Analysis (Plain Text)
Provide one email per line and set the content type to `text/plain`.

```php
$content = "one@example.com\nTwo.User@example.org"; // newline-delimited emails
$batch = $emailInsights->batchAnalyze(['text' => $content], 'text/plain');
$status = $emailInsights->getBatchStatus($batch->jobId);
```

#### 3. Batch Email Analysis (File Upload)
Supply a `.csv` (one email per row; header optional) via `batchAnalyzeFile()`. A `.csv` triggers `multipart/form-data`; other extensions fall back to `text/plain` (newline-delimited body).

```php
$batch = $emailInsights->batchAnalyzeFile(__DIR__.'/emails.csv', [
    'name' => 'Monthly Email Cleanup', // Optional: descriptive name for the job
    'enableAi' => true,
    'enableAutoCorrection' => true
]);
$status = $emailInsights->getBatchStatus($batch->jobId);
```

#### 4. Batch IP Analysis (JSON)

```php
use Opportify\Sdk\IpInsights;

$ipInsights = new IpInsights("<YOUR-KEY-HERE>");

$params = [
    'ips' => [
        '1.1.1.1',
        '8.8.8.8'
    ],
    'name' => 'Network Security Scan', // Optional: descriptive name for the job
    'enableAi' => true
];

$batch = $ipInsights->batchAnalyze($params); // application/json
$status = $ipInsights->getBatchStatus($batch->jobId);
```

#### 5. Batch IP Analysis (Plain Text)

```php
$content = "1.1.1.1\n8.8.8.8"; // newline-delimited IPs
$batch = $ipInsights->batchAnalyze(['text' => $content], 'text/plain');
$status = $ipInsights->getBatchStatus($batch->jobId);
```

#### 6. Batch IP Analysis (File Upload)

```php
$batch = $ipInsights->batchAnalyzeFile(__DIR__.'/ips.csv', [
    'name' => 'Firewall IP Assessment', // Optional: descriptive name for the job
    'enableAi' => true
]);
$status = $ipInsights->getBatchStatus($batch->jobId);
```

#### Convenience & Notes
- `batchAnalyzeFile()` auto-selects content type: `.csv` -> `multipart/form-data`; otherwise `text/plain`.
- For `text/plain`, pass newline-delimited values via the `text` key.
- For `multipart/form-data`, pass a readable file path via the `file` key (handled internally by `batchAnalyzeFile()`).
- The `name` parameter is optional for all batch operations and helps with job identification and tracking.
- `enableAutoCorrection` applies only to Email Insights.
- Always wrap calls in a try-catch (see Error Handling) to capture API errors.
- Polling cadence depends on payload size; a short delay (1–3s) between status checks is recommended.


### Enabling Debug Mode

```php
$clientInsights->setDebugMode(true);
```

### Handling Error

We strongly recommend that any usage of this SDK happens within a try-catch to properly handle any exceptions or errors.

```php
use OpenAPI\Client\ApiException;

try {

    // Email or IP Insights usage...

} catch (ApiException $e) {
    throw new \Exception($e->getResponseBody());
}
```
Below are the `ApiException` functions available:

| Function | Type | Value Sample |
|------------|------|--------------|
| `$e->getMessage();` | string | `"[403] Client error: POST https://api.opportify.ai/insights/v1/... resulted in a 403 Forbidden"` |
| `$e->getResponseBody();` | string | `"{"errorMessage":"Your plan does not support AI features, please upgrade your plan or set enableAI as false.","errorCode":"INVALID_PLAN"}"` |
| `$e->getCode();` | integer | `403` |

## About this package

This PHP package is a customization of the base generated by:

- [OpenAPI Generator](https://openapi-generator.tech) project.
