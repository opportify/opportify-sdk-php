[![Tests](https://img.shields.io/github/actions/workflow/status/opportify/opportify-sdk-php/phpunit.yml?label=tests&style=for-the-badge&labelColor=115e5c)](https://github.com/opportify/opportify-sdk-php/actions/workflows/phpunit.yml)
[![Packagist Downloads](https://img.shields.io/packagist/dt/opportify/opportify-sdk-php?style=for-the-badge&labelColor=115e5c)](https://packagist.org/packages/opportify/opportify-sdk-php)
[![Packagist Version](https://img.shields.io/packagist/v/opportify/opportify-sdk-php?style=for-the-badge&labelColor=115e5c)](https://packagist.org/packages/opportify/opportify-sdk-php)
[![License](https://img.shields.io/github/license/opportify/opportify-sdk-php?color=9cf&style=for-the-badge&labelColor=115e5c)](https://github.com/opportify/opportify-sdk-php/blob/main/LICENSE)

# Opportify-SDK-PHP

## Table of Contents

- [Overview](#overview)
- [Requirements](#requirements)
- [Getting Started](#getting-started)
    - [Calling Email Insights](#calling-email-insights)
    - [Calling IP Insights](#calling-ip-insights)
    - [Calling Fraud Protection](#calling-fraud-protection)
- [Batch Analysis (Email & IP)](#batch-analysis-email--ip)
    - [Batch Email Analysis (JSON)](#1-batch-email-analysis-json)
    - [Batch Email Analysis (Plain Text)](#2-batch-email-analysis-plain-text)
    - [Batch Email Analysis (File Upload)](#3-batch-email-analysis-file-upload)
    - [Batch IP Analysis (JSON)](#4-batch-ip-analysis-json)
    - [Batch IP Analysis (Plain Text)](#5-batch-ip-analysis-plain-text)
    - [Batch IP Analysis (File Upload)](#6-batch-ip-analysis-file-upload)
- [Batch Export Jobs](#batch-export-jobs)
    - [Email Batch Exports](#email-batch-exports)
    - [IP Batch Exports](#ip-batch-exports)
- [Enabling Debug Mode](#enabling-debug-mode)
- [Handling Errors](#handling-errors)
- [About this package](#about-this-package)

## Overview

The **Opportify SDK** gives your PHP application access to the full Opportify platform:

| Product | Purpose |
|---------|---------|
| **Email Insights** | Validate, enrich, and score email addresses |
| **IP Insights** | Geolocate, enrich, and assess risk for IP addresses |
| **Fraud Protection** | Analyze form submissions for fraud risk across email, IP, geo, session, and velocity signals |

All products share a common API key and the same SDK installation.

[Sign Up Free](https://www.opportify.ai)

### Base URLs

| Product | Base URL |
|---------|----------|
| Email & IP Insights | `https://api.opportify.ai/insights/v1/` |
| Fraud Protection | `https://api.opportify.ai/intel/v1/` |

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
    "enableAutoCorrection" => true,
    "enableDomainEnrichment" => true // Optional: include domain enrichment block
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

### Calling Fraud Protection

Analyze a form submission for fraud risk. The response provides an overall risk score with a breakdown by signal source (email, IP, geo, session, velocity).

```php
use Opportify\Sdk\FraudProtection;

$fraudProtection = new FraudProtection("<YOUR-KEY-HERE>");

$params = [
    // Identity
    "email"          => "user@example.com",
    "firstName"      => "Jane",
    "lastName"       => "Doe",
    "username"       => "jane_doe",
    "companyName"    => "Acme Corp",

    // Network
    "userIp"         => "3.1.122.82",

    // Contact details
    "phone1"         => "+1-800-555-0100",
    "website"        => "https://acme.example.com",

    // Submission context
    "subject"        => "Contact form submission",
    "message"        => "Hello, I am interested in your service.",
    "submissionType" => "contact",  // e.g. "contact", "signup", "checkout"
    "origin"         => "yoursite.com",  // hostname only — no protocol, path, or port

    // Address (all optional)
    "address1"       => "123 Main St",
    "city"           => "Springfield",
    "region"         => "IL",
    "country"        => "US",
    "postalCode"     => "62701",

    // Token & form tracking (optional)
    "opportifyToken"   => "opportify-generated-token",
    "opportifyFormUUID" => "uuid-of-the-form",

    // Raw form fields as key-value pairs (optional)
    "formData"       => ["custom_field" => "value"],
];

$result = $fraudProtection->analyze($params);
// $result->score    — integer 200–1000 (higher = riskier)
// $result->level    — "lowest" | "low" | "medium" | "high" | "highest"
// $result->factors  — string[] of detected risk signals
// $result->sources  — per-signal breakdown (email, IP, geo, session, velocity)
```

All parameter names accept both `snake_case` and `camelCase` (e.g. `user_ip` or `userIp`).


## Batch Analysis (Email & IP)

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

## Batch Export Jobs

Use batch exports to materialize filtered results from completed jobs. Exports run asynchronously and expose polling helpers similar to batch status checks.

### Email Batch Exports

```php
$emailInsights = new EmailInsights('<YOUR-KEY-HERE>');

// Trigger a new export for a completed batch job
$export = $emailInsights->createBatchExport('job-uuid-here', [
    'exportType' => 'csv',
    'columns' => [
        'emailAddress',
        'emailProvider',
        'riskReport.score',
        'isDeliverable'
    ],
    'filters' => [
        'isDeliverable' => 'true',
        'riskReport.score' => ['min' => 400]
    ]
]);

// Poll until the export is ready
$status = $emailInsights->getBatchExportStatus('job-uuid-here', $export->exportId);

if ($status->status === 'COMPLETED') {
    // Use $status->downloadUrl for the pre-signed file link
}
```

### IP Batch Exports

```php
$ipInsights = new IpInsights('<YOUR-KEY-HERE>');

$export = $ipInsights->createBatchExport('job-uuid-here', [
    'exportType' => 'json',
    'columns' => [
        'result.ipAddress',
        'result.connectionType',
        'result.riskReport.score'
    ],
    'filters' => [
        'result.riskReport.level' => ['low', 'medium']
    ]
]);

$status = $ipInsights->getBatchExportStatus('job-uuid-here', $export->exportId);

if ($status->status === 'COMPLETED') {
    // Use $status->downloadUrl to retrieve the generated export
} elseif ($status->status === 'FAILED') {
    // Review $status->errorCode and $status->errorMessage for remediation guidance
}
```


## Enabling Debug Mode

All wrappers support debug mode, which enables verbose HTTP logging via Guzzle:

```php
$emailInsights->setDebugMode(true);
$ipInsights->setDebugMode(true);
$fraudProtection->setDebugMode(true);
```

You can also override the host, API prefix, or version for testing against staging environments:

```php
$fraudProtection->setHost('https://staging.api.opportify.ai');
$fraudProtection->setVersion('v2');
$fraudProtection->setPrefix('intel');
```

## Handling Errors

We strongly recommend wrapping all SDK calls in a try-catch to handle API errors.

**Email Insights & IP Insights** use `OpenAPI\Client\ApiException`:

```php
use OpenAPI\Client\ApiException;

try {
    $result = $emailInsights->analyze($params);
    // or: $result = $ipInsights->analyze($params);
} catch (ApiException $e) {
    throw new \Exception($e->getResponseBody());
}
```

**Fraud Protection** uses its own namespace `OpenAPI\FraudIntel\Client\ApiException`:

```php
use OpenAPI\FraudIntel\Client\ApiException;

try {
    $result = $fraudProtection->analyze($params);
} catch (ApiException $e) {
    throw new \Exception($e->getResponseBody());
}
```

All `ApiException` instances expose the same interface:

| Method | Type | Example |
|--------|------|---------|
| `$e->getMessage()` | string | `"[403] Client error: POST https://api.opportify.ai/... resulted in a 403 Forbidden"` |
| `$e->getResponseBody()` | string | `'{"errorMessage":"Your plan does not support AI features","errorCode":"INVALID_PLAN"}'` |
| `$e->getCode()` | integer | `403` |

## About this package

This PHP package is a customization of the base generated by:

- [OpenAPI Generator](https://openapi-generator.tech) project.
