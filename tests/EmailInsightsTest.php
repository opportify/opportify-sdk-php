<?php

use Mockery as m;
use OpenAPI\Client\Api\EmailInsightsApi;
use OpenAPI\Client\Model\ExportRequest;
use Opportify\Sdk\EmailInsights;
use PHPUnit\Framework\TestCase;

class EmailInsightsTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close(); // Ensures all expectations are verified
    }

    public function test_set_host()
    {
        $emailInsights = new EmailInsights('fake_api_key');
        $emailInsights->setHost('https://new-api.opportify.ai');

        $reflection = new \ReflectionClass($emailInsights);
        $property = $reflection->getProperty('host');
        $property->setAccessible(true);

        $this->assertEquals('https://new-api.opportify.ai', $property->getValue($emailInsights));
    }

    public function test_set_version()
    {
        $emailInsights = new EmailInsights('fake_api_key');
        $emailInsights->setVersion('v2');

        $reflection = new \ReflectionClass($emailInsights);
        $property = $reflection->getProperty('version');
        $property->setAccessible(true);

        $this->assertEquals('v2', $property->getValue($emailInsights));
    }

    public function test_set_prefix()
    {
        $emailInsights = new EmailInsights('fake_api_key');
        $emailInsights->setPrefix('new-prefix');

        $reflection = new \ReflectionClass($emailInsights);
        $property = $reflection->getProperty('prefix');
        $property->setAccessible(true);

        $this->assertEquals('new-prefix', $property->getValue($emailInsights));
    }

    public function test_set_debug_mode()
    {
        $emailInsights = new EmailInsights('fake_api_key');
        $emailInsights->setDebugMode(true);

        $reflection = new \ReflectionClass($emailInsights);
        $property = $reflection->getProperty('debugMode');
        $property->setAccessible(true);

        $this->assertTrue($property->getValue($emailInsights));
    }

    public function test_normalize_request()
    {
        $emailInsights = new EmailInsights('fake_api_key');

        $input = [
            'email' => 'test@example.com',
            'enableAi' => true,
            'enableAutoCorrection' => 'false',
        ];

        $expectedOutput = [
            'email' => 'test@example.com',
            'enable_ai' => true,
            'enable_auto_correction' => false,
            'enable_domain_enrichment' => true,
        ];

        $reflection = new \ReflectionClass($emailInsights);
        $method = $reflection->getMethod('normalizeRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($emailInsights, [$input]);

        $this->assertEquals($expectedOutput, $normalized);
    }

    public function test_normalize_request_with_domain_enrichment_toggle()
    {
        $emailInsights = new EmailInsights('fake_api_key');

        $input = [
            'email' => 'test@example.com',
            'enableAi' => false,
            'enableAutoCorrection' => true,
            'enableDomainEnrichment' => 'false',
        ];

        $expectedOutput = [
            'email' => 'test@example.com',
            'enable_ai' => false,
            'enable_auto_correction' => true,
            'enable_domain_enrichment' => false,
        ];

        $reflection = new \ReflectionClass($emailInsights);
        $method = $reflection->getMethod('normalizeRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($emailInsights, [$input]);

        $this->assertEquals($expectedOutput, $normalized);
    }

    public function test_analyze_success()
    {
        $mockResponseData = [
            'emailAddress' => 'test@example.com',
            'emailProvider' => 'Google',
            'emailType' => 'free',
            'isFormatValid' => true,
            'emailCorrection' => '',
            'isReachable' => true,
            'isDeliverable' => 'yes',
            'isMailboxFull' => false,
            'isCatchAll' => false,
            'emailDNS' => [
                'mx' => [
                    '10 alt1.gmail-smtp-in.l.google.com',
                    '40 alt4.gmail-smtp-in.l.google.com',
                    '5 gmail-smtp-in.l.google.com',
                    '30 alt3.gmail-smtp-in.l.google.com',
                    '20 alt2.gmail-smtp-in.l.google.com',
                ],
            ],
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        // Mock the API instance
        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('analyzeEmail')
            ->once()
            ->andReturn($mockResponse);

        // Inject the mock API instance via constructor
        $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

        $response = $emailInsights->analyze([
            'email' => 'test@example.com',
            'enableAi' => true,
            'enableAutoCorrection' => false,
        ]);

        // Assertions to ensure response is correct
        $this->assertIsObject($response);
        $this->assertEquals('test@example.com', $response->emailAddress);
        $this->assertEquals('Google', $response->emailProvider);
        $this->assertTrue($response->isFormatValid);
    }

    public function test_throws_exception_when_analyze_fails()
    {
        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('analyzeEmail')
            ->once()
            ->andThrow(new \OpenAPI\Client\ApiException(
                '[403] Client error: `POST https://api.opportify.ai/insights/v1/email/analyze` resulted in a `403 Forbidden` response: {"errorCode": "INVALID_TOKEN", "errorMessage": "The token provided is either invalid, expired, or missing"}',
                403
            ));

        $emailInsights = new EmailInsights('invalid_api_key');

        // Inject mock using Reflection
        $reflection = new \ReflectionClass($emailInsights);
        $property = $reflection->getProperty('apiInstance');
        $property->setAccessible(true);
        $property->setValue($emailInsights, $mockApiInstance);

        $this->expectException(\OpenAPI\Client\ApiException::class);
        $this->expectExceptionMessage('[403]');
        $this->expectExceptionMessage('INVALID_TOKEN');

        $emailInsights->analyze([
            'email' => 'test@example.com',
            'enableAi' => true,
            'enableAutoCorrection' => false,
        ]);
    }

    public function test_normalize_batch_request()
    {
        $emailInsights = new EmailInsights('fake_api_key');

        $input = [
            'emails' => ['test1@example.com', 'test2@example.com'],
            'enableAi' => true,
            'enableAutoCorrection' => 'false',
        ];

        $expectedOutput = [
            'emails' => ['test1@example.com', 'test2@example.com'],
            'enable_ai' => true,
            'enable_auto_correction' => false,
        ];

        $reflection = new \ReflectionClass($emailInsights);
        $method = $reflection->getMethod('normalizeBatchRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($emailInsights, [$input]);

        $this->assertEquals($expectedOutput, $normalized);
    }

    public function test_batch_analyze_success()
    {
        $mockResponseData = [
            'jobId' => 'job-123456',
            'status' => 'QUEUED',
            'statusDescription' => '',
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        // Mock the API instance
        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeEmails')
            ->once()
            ->andReturn($mockResponse);

        // Inject the mock API instance via constructor
        $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

        $response = $emailInsights->batchAnalyze([
            'emails' => ['test1@example.com', 'test2@example.com'],
            'enableAi' => true,
            'enableAutoCorrection' => false,
        ]);

        // Assertions to ensure response is correct
        $this->assertIsObject($response);
        $this->assertEquals('job-123456', $response->jobId);
        $this->assertEquals('QUEUED', $response->status);
    }

    public function test_batch_analyze_with_file_content_type()
    {
        $mockResponseData = [
            'jobId' => 'job-123456',
            'status' => 'QUEUED',
            'statusDescription' => '',
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        // Mock the API instance - now expects MultipartStream instead of array
        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeEmails')
            ->once()
            ->with(Mockery::type('\GuzzleHttp\Psr7\MultipartStream'), 'multipart/form-data')
            ->andReturn($mockResponse);

        // Create a temporary file for testing
        $tempFilePath = sys_get_temp_dir().'/test_emails.csv';
        file_put_contents($tempFilePath, 'test1@example.com'.PHP_EOL.'test2@example.com');

        // Inject the mock API instance via constructor
        $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

        $response = $emailInsights->batchAnalyze([
            'file' => $tempFilePath,
            'enableAi' => true,
        ], 'multipart/form-data');

        // Clean up
        unlink($tempFilePath);

        // Assertions to ensure response is correct
        $this->assertIsObject($response);
        $this->assertEquals('job-123456', $response->jobId);
        $this->assertEquals('QUEUED', $response->status);
    }

    public function test_batch_analyze_multipart_throws_exception_when_file_missing()
    {
        $emailInsights = new EmailInsights('fake_api_key');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File parameter is required and must be a valid file path');

        $emailInsights->batchAnalyze([
            'enableAi' => true,
        ], 'multipart/form-data');
    }

    public function test_batch_analyze_multipart_throws_exception_when_file_not_exists()
    {
        $emailInsights = new EmailInsights('fake_api_key');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File parameter is required and must be a valid file path');

        $emailInsights->batchAnalyze([
            'file' => '/non/existent/file.csv',
            'enableAi' => true,
        ], 'multipart/form-data');
    }

    public function test_batch_analyze_multipart_handles_fopen_errors_gracefully()
    {
        // Note: This test demonstrates that our code properly checks fopen() return values
        // In practice, fopen() failures are rare and typically indicate system-level issues
        // The important thing is that we check for false and throw meaningful exceptions

        $emailInsights = new EmailInsights('fake_api_key');

        // Test with a path that's guaranteed to fail (null byte is invalid in filenames)
        $invalidPath = "invalid\0path.csv";

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File parameter is required and must be a valid file path');

        $emailInsights->batchAnalyze([
            'file' => $invalidPath,
            'enableAi' => true,
        ], 'multipart/form-data');
    }

    public function test_batch_analyze_multipart_with_all_parameters()
    {
        $mockResponseData = [
            'jobId' => 'job-789012',
            'status' => 'QUEUED',
            'statusDescription' => '',
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        // Mock the API instance
        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeEmails')
            ->once()
            ->with(Mockery::type('\GuzzleHttp\Psr7\MultipartStream'), 'multipart/form-data')
            ->andReturn($mockResponse);

        // Create a temporary file for testing
        $tempFilePath = sys_get_temp_dir().'/test_emails_full.csv';
        file_put_contents($tempFilePath, 'test1@example.com'.PHP_EOL.'test2@example.com');

        // Inject the mock API instance via constructor
        $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

        $response = $emailInsights->batchAnalyze([
            'file' => $tempFilePath,
            'enable_ai' => true,
            'enable_auto_correction' => false,
        ], 'multipart/form-data');

        // Clean up
        unlink($tempFilePath);

        // Assertions to ensure response is correct
        $this->assertIsObject($response);
        $this->assertEquals('job-789012', $response->jobId);
        $this->assertEquals('QUEUED', $response->status);
    }

    public function test_batch_analyze_with_text_plain_content_type()
    {
        $mockResponseData = [
            'jobId' => 'job-123456',
            'status' => 'QUEUED',
            'statusDescription' => '',
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        // Mock the API instance
        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeEmails')
            ->once()
            ->with(Mockery::type('string'), 'text/plain')
            ->andReturn($mockResponse);

        // Inject the mock API instance via constructor
        $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

        $response = $emailInsights->batchAnalyze([
            'text' => "test1@example.com\ntest2@example.com",
        ], 'text/plain');

        // Assertions to ensure response is correct
        $this->assertIsObject($response);
        $this->assertEquals('job-123456', $response->jobId);
        $this->assertEquals('QUEUED', $response->status);
    }

    public function test_batch_analyze_file_helper_method()
    {
        $mockResponseData = [
            'jobId' => 'job-123456',
            'status' => 'QUEUED',
            'statusDescription' => '',
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        // Mock the API instance
        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeEmails')
            ->once()
            ->andReturn($mockResponse);

        // Create a temporary file for testing
        $tempFilePath = sys_get_temp_dir().'/test_emails.csv';
        file_put_contents($tempFilePath, 'test1@example.com'.PHP_EOL.'test2@example.com');

        // Inject the mock API instance via constructor
        $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

        $response = $emailInsights->batchAnalyzeFile($tempFilePath, ['enableAi' => true]);

        // Clean up
        unlink($tempFilePath);

        // Assertions to ensure response is correct
        $this->assertIsObject($response);
        $this->assertEquals('job-123456', $response->jobId);
        $this->assertEquals('QUEUED', $response->status);
    }

    public function test_get_batch_status_success()
    {
        $mockResponseData = [
            'jobId' => 'job-123456',
            'status' => 'COMPLETED',
            'statusDescription' => '',
            'progress' => 100,
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        // Mock the API instance
        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('getEmailBatchStatus')
            ->once()
            ->with('job-123456')
            ->andReturn($mockResponse);

        // Inject the mock API instance via constructor
        $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

        $response = $emailInsights->getBatchStatus('job-123456');

        // Assertions to ensure response is correct
        $this->assertIsObject($response);
        $this->assertEquals('job-123456', $response->jobId);
        $this->assertEquals('COMPLETED', $response->status);
        $this->assertEquals(100, $response->progress);
    }

    public function test_create_batch_export_with_payload()
    {
        $mockResponseData = [
            'exportId' => 'export-123',
            'status' => 'QUEUED',
        ];

        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('createEmailBatchExport')
            ->once()
            ->with('job-123', Mockery::on(function ($request) {
                \PHPUnit\Framework\Assert::assertInstanceOf(ExportRequest::class, $request);
                \PHPUnit\Framework\Assert::assertEquals('json', $request->getExportType());
                \PHPUnit\Framework\Assert::assertEquals(['emailAddress', 'riskReport.score'], $request->getColumns());
                \PHPUnit\Framework\Assert::assertEquals(['isDeliverable' => 'yes'], $request->getFilters());

                return true;
            }))
            ->andReturn($mockResponse);

        $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

        $response = $emailInsights->createBatchExport('job-123', [
            'exportType' => 'JSON',
            'columns' => ['emailAddress', 'riskReport.score'],
            'filters' => ['isDeliverable' => 'yes'],
        ]);

        $this->assertEquals('export-123', $response->exportId);
        $this->assertEquals('QUEUED', $response->status);
    }

    public function test_create_batch_export_without_payload()
    {
        $mockResponseData = [
            'exportId' => 'export-456',
            'status' => 'PROCESSING',
        ];

        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('createEmailBatchExport')
            ->once()
            ->with('job-456', null)
            ->andReturn($mockResponse);

        $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

        $response = $emailInsights->createBatchExport('job-456');

        $this->assertEquals('export-456', $response->exportId);
        $this->assertEquals('PROCESSING', $response->status);
    }

    public function test_create_batch_export_validates_job_id()
    {
        $emailInsights = new EmailInsights('fake_api_key', Mockery::mock(EmailInsightsApi::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Job ID cannot be empty when creating an export.');

        $emailInsights->createBatchExport('   ');
    }

    public function test_create_batch_export_validates_columns_type()
    {
        $emailInsights = new EmailInsights('fake_api_key', Mockery::mock(EmailInsightsApi::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Columns must be provided as an array.');

        $emailInsights->createBatchExport('job-101', [
            'columns' => 'emailAddress',
        ]);
    }

    public function test_create_batch_export_validates_filters_type()
    {
        $emailInsights = new EmailInsights('fake_api_key', Mockery::mock(EmailInsightsApi::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Filters must be provided as an array.');

        $emailInsights->createBatchExport('job-101', [
            'filters' => 'isDeliverable=yes',
        ]);
    }

    public function test_get_batch_export_status_success()
    {
        $mockResponseData = [
            'exportId' => 'export-789',
            'status' => 'COMPLETED',
            'downloadUrl' => 'https://example.com/download.csv',
        ];

        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('getEmailBatchExportStatus')
            ->once()
            ->with('job-789', 'export-789')
            ->andReturn($mockResponse);

        $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

        $response = $emailInsights->getBatchExportStatus('job-789', 'export-789');

        $this->assertEquals('export-789', $response->exportId);
        $this->assertEquals('COMPLETED', $response->status);
        $this->assertEquals('https://example.com/download.csv', $response->downloadUrl);
    }

    public function test_get_batch_export_status_validates_identifiers()
    {
        $emailInsights = new EmailInsights('fake_api_key', Mockery::mock(EmailInsightsApi::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Job ID and export ID are required to fetch export status.');

        $emailInsights->getBatchExportStatus('job-001', '   ');
    }

    public function test_batch_analyze_with_name_parameter_json()
    {
        $mockResponseData = [
            'jobId' => 'job-123456',
            'name' => 'My Email Batch Job',
            'status' => 'QUEUED',
            'statusDescription' => '',
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        // Mock the API instance
        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeEmails')
            ->once()
            ->andReturn($mockResponse);

        // Inject the mock API instance via constructor
        $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

        $response = $emailInsights->batchAnalyze([
            'emails' => ['test1@example.com', 'test2@example.com'],
            'name' => 'My Email Batch Job',
            'enableAi' => true,
            'enableAutoCorrection' => false,
        ]);

        // Assertions to ensure response is correct
        $this->assertIsObject($response);
        $this->assertEquals('job-123456', $response->jobId);
        $this->assertEquals('My Email Batch Job', $response->name);
        $this->assertEquals('QUEUED', $response->status);
    }

    public function test_batch_analyze_with_name_parameter_multipart()
    {
        $mockResponseData = [
            'jobId' => 'job-123456',
            'name' => 'CSV Import Job',
            'status' => 'QUEUED',
            'statusDescription' => '',
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        // Mock the API instance - expects MultipartStream with name parameter
        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeEmails')
            ->once()
            ->with(Mockery::type('\GuzzleHttp\Psr7\MultipartStream'), 'multipart/form-data')
            ->andReturn($mockResponse);

        // Create a temporary file for testing
        $tempFilePath = sys_get_temp_dir().'/test_emails_with_name.csv';
        file_put_contents($tempFilePath, 'test1@example.com'.PHP_EOL.'test2@example.com');

        try {
            // Inject the mock API instance via constructor
            $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

            $response = $emailInsights->batchAnalyze([
                'file' => $tempFilePath,
                'name' => 'CSV Import Job',
                'enableAi' => true,
                'enableAutoCorrection' => false,
            ], 'multipart/form-data');

            // Assertions to ensure response is correct
            $this->assertIsObject($response);
            $this->assertEquals('job-123456', $response->jobId);
            $this->assertEquals('CSV Import Job', $response->name);
            $this->assertEquals('QUEUED', $response->status);
        } finally {
            // Clean up the temporary file
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }

    public function test_batch_analyze_file_helper_method_with_txt_file()
    {
        $mockResponseData = [
            'jobId' => 'job-654321',
            'status' => 'QUEUED',
            'statusDescription' => '',
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        // Mock the API instance should still receive batchAnalyzeEmails call once
        $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeEmails')
            ->once()
            ->andReturn($mockResponse);

        // Create a temporary .txt file for testing
        $tempFilePath = sys_get_temp_dir().'/test_emails.txt';
        file_put_contents($tempFilePath, "test1@example.com\ntest2@example.com");

        // Inject the mock API instance via constructor
        $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);

        $response = $emailInsights->batchAnalyzeFile($tempFilePath, ['enableAi' => true]);

        // Clean up
        unlink($tempFilePath);

        $this->assertIsObject($response);
        $this->assertEquals('job-654321', $response->jobId);
        $this->assertEquals('QUEUED', $response->status);
    }

    public function test_normalize_batch_request_with_name()
    {
        $emailInsights = new EmailInsights('fake_api_key');

        $input = [
            'emails' => ['test1@example.com', 'test2@example.com'],
            'name' => 'Test Batch Job',
            'enableAi' => true,
            'enableAutoCorrection' => false,
        ];

        $expectedOutput = [
            'emails' => ['test1@example.com', 'test2@example.com'],
            'name' => 'Test Batch Job',
            'enable_ai' => true,
            'enable_auto_correction' => false,
        ];

        $reflection = new \ReflectionClass($emailInsights);
        $method = $reflection->getMethod('normalizeBatchRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($emailInsights, [$input]);

        $this->assertEquals($expectedOutput, $normalized);
    }

    public function test_normalize_batch_request_without_name()
    {
        $emailInsights = new EmailInsights('fake_api_key');

        $input = [
            'emails' => ['test1@example.com', 'test2@example.com'],
            'enableAi' => true,
            'enableAutoCorrection' => false,
        ];

        $expectedOutput = [
            'emails' => ['test1@example.com', 'test2@example.com'],
            'enable_ai' => true,
            'enable_auto_correction' => false,
        ];

        $reflection = new \ReflectionClass($emailInsights);
        $method = $reflection->getMethod('normalizeBatchRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($emailInsights, [$input]);

        $this->assertEquals($expectedOutput, $normalized);
        $this->assertArrayNotHasKey('name', $normalized);
    }

    public function urlScenariosProvider(): array
    {
        return [
            'all segments' => [
                'https://api.opportify.ai', 'insights', 'v1', 'https://api.opportify.ai/insights/v1',
            ],
            'empty prefix' => [
                'https://api.opportify.ai', '', 'v1', 'https://api.opportify.ai/v1',
            ],
            'empty version' => [
                'https://api.opportify.ai', 'insights', '', 'https://api.opportify.ai/insights',
            ],
            'empty both' => [
                'https://api.opportify.ai', '', '', 'https://api.opportify.ai',
            ],
            'host with trailing slash, others normal' => [
                'https://api.opportify.ai/', 'insights', 'v1', 'https://api.opportify.ai/insights/v1',
            ],
            'prefix with slashes' => [
                'https://api.opportify.ai', '/insights/', 'v1', 'https://api.opportify.ai/insights/v1',
            ],
            'version with slashes' => [
                'https://api.opportify.ai', 'insights', '/v2/', 'https://api.opportify.ai/insights/v2',
            ],
        ];
    }

    /**
     * @dataProvider urlScenariosProvider
     */
    public function test_final_url_building($host, $prefix, $version, $expected)
    {
        $emailInsights = new EmailInsights('fake_api_key');

        $emailInsights->setHost($host);
        $emailInsights->setPrefix($prefix);
        $emailInsights->setVersion($version);

        // Force refresh to rebuild apiInstance & finalUrl
        $reflection = new \ReflectionClass($emailInsights);
        $method = $reflection->getMethod('refreshApiInstance');
        $method->setAccessible(true);
        $method->invokeArgs($emailInsights, []);

        $property = $reflection->getProperty('finalUrl');
        $property->setAccessible(true);
        $this->assertEquals($expected, $property->getValue($emailInsights));
    }
}
