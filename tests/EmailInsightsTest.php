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

    public function test_analyze_various_scenarios()
    {
        $scenarios = [
            // Scenario 1
            [
                'emailAddress' => 'test5@example.com',
                'emailProvider' => 'unknown',
                'emailType' => 'private',
                'isDeliverable' => 'no',
                'isMailboxFull' => false,
                'isCatchAll' => false,
                'isFormatValid' => true,
                'emailCorrection' => '',
                'isReachable' => false,
                'riskReport' => [
                    'score' => 1000,
                    'level' => 'highest',
                    'baseAnalysis' => [
                        'instant-bounce',
                        'no-mx-or-invalid',
                        'provider-unknown',
                    ],
                ],
                'addressSignals' => [
                    'tagDetected' => false,
                    'tagValue' => '',
                    'normalizedAddress' => 'test5@example.com',
                    'isRoleAddress' => false,
                    'roleType' => '',
                    'isNoReply' => false,
                    'noReplyPattern' => '',
                ],
                'emailDNS' => [
                    'mx' => [],
                    'spfValid' => false,
                    'dkimConfigured' => false,
                    'dmarcValid' => false,
                    'mxRelay' => false,
                    'mxRelayCategory' => '',
                ],
                'domain' => [
                    'name' => 'example.com',
                    'enrichmentAvailable' => false,
                    'creationDate' => null,
                    'expirationDate' => null,
                    'updatedDate' => null,
                    'ageYears' => 0,
                    'registrar' => '',
                    'isBlockListed' => false,
                    'mtaStsStatus' => 'unknown',
                    'bimiStatus' => 'unknown',
                    'hasVMC' => false,
                    'aRecordValid' => false,
                    'aRecordReverseHost' => '',
                    'sslValid' => false,
                ],
            ],
            // Scenario 2
            [
                'emailAddress' => 'example@gmail.com',
                'emailProvider' => 'Google',
                'emailType' => 'free',
                'isDeliverable' => 'yes',
                'isMailboxFull' => false,
                'isCatchAll' => false,
                'isFormatValid' => true,
                'emailCorrection' => '',
                'isReachable' => true,
                'riskReport' => [
                    'score' => 410,
                    'level' => 'medium',
                    'baseAnalysis' => [
                        'free-provider',
                        'missing-auth-dkim',
                    ],
                ],
                'addressSignals' => [
                    'isNoReply' => false,
                    'tagValue' => '',
                    'normalizedAddress' => 'example@gmail.com',
                    'noReplyPattern' => '',
                    'roleType' => '',
                    'isRoleAddress' => false,
                    'tagDetected' => false,
                ],
                'emailDNS' => [
                    'mx' => [
                        '5 gmail-smtp-in.l.google.com',
                        '10 alt1.gmail-smtp-in.l.google.com',
                        '20 alt2.gmail-smtp-in.l.google.com',
                        '30 alt3.gmail-smtp-in.l.google.com',
                        '40 alt4.gmail-smtp-in.l.google.com',
                    ],
                    'spfValid' => true,
                    'dkimConfigured' => false,
                    'dmarcValid' => true,
                    'mxRelay' => false,
                    'mxRelayCategory' => '',
                ],
                'domain' => [
                    'registrar' => 'MarkMonitor Inc.',
                    'isBlockListed' => false,
                    'mtaStsStatus' => 'present',
                    'aRecordReverseHost' => 'ord37s32-in-f5.1e100.net',
                    'updatedDate' => '2025-07-11T10:10:56.000Z',
                    'creationDate' => '1995-08-13T04:00:00.000Z',
                    'enrichmentAvailable' => true,
                    'aRecordValid' => true,
                    'ageYears' => 30,
                    'hasVMC' => false,
                    'name' => 'gmail.com',
                    'sslValid' => true,
                    'bimiStatus' => 'absent',
                    'expirationDate' => '2026-08-12T04:00:00.000Z',
                ],
            ],
            // Scenario 3
            [
                'emailAddress' => 'example@icloud.com',
                'emailProvider' => 'iCloud',
                'emailType' => 'free',
                'isDeliverable' => 'yes',
                'isMailboxFull' => false,
                'isCatchAll' => false,
                'isFormatValid' => true,
                'emailCorrection' => '',
                'isReachable' => true,
                'riskReport' => [
                    'score' => 410,
                    'level' => 'medium',
                    'baseAnalysis' => [
                        'free-provider',
                        'missing-auth-dkim',
                    ],
                ],
                'addressSignals' => [
                    'tagDetected' => false,
                    'tagValue' => '',
                    'normalizedAddress' => 'example@icloud.com',
                    'isRoleAddress' => false,
                    'roleType' => '',
                    'isNoReply' => false,
                    'noReplyPattern' => '',
                ],
                'emailDNS' => [
                    'mx' => [
                        '10 mx02.mail.icloud.com',
                        '10 mx01.mail.icloud.com',
                    ],
                    'spfValid' => true,
                    'dkimConfigured' => false,
                    'dmarcValid' => true,
                    'mxRelay' => false,
                    'mxRelayCategory' => '',
                ],
                'domain' => [
                    'name' => 'icloud.com',
                    'enrichmentAvailable' => true,
                    'creationDate' => '1999-01-15T05:00:00.000Z',
                    'expirationDate' => '2026-01-15T05:00:00.000Z',
                    'updatedDate' => '2024-12-16T23:10:43.000Z',
                    'ageYears' => 26,
                    'registrar' => 'Nom-iq Ltd. dba COM LAUDE',
                    'isBlockListed' => false,
                    'mtaStsStatus' => 'absent',
                    'bimiStatus' => 'present-no-vmc',
                    'hasVMC' => false,
                    'aRecordValid' => true,
                    'aRecordReverseHost' => 'apple.fr',
                    'sslValid' => true,
                ],
            ],
            // Scenario 4
            [
                'emailAddress' => 'tatak1792@filipx.com',
                'emailProvider' => 'Temp-mail',
                'emailType' => 'disposable',
                'isDeliverable' => 'unknown',
                'isMailboxFull' => false,
                'isCatchAll' => true,
                'isFormatValid' => true,
                'emailCorrection' => '',
                'isReachable' => true,
                'riskReport' => [
                    'score' => 1000,
                    'level' => 'highest',
                    'baseAnalysis' => [
                        'blocklisted-domain',
                        'disposable-domain',
                        'spoofing-risk',
                    ],
                ],
                'addressSignals' => [
                    'tagDetected' => false,
                    'tagValue' => '',
                    'normalizedAddress' => 'tatak1792@filipx.com',
                    'isRoleAddress' => false,
                    'roleType' => '',
                    'isNoReply' => false,
                    'noReplyPattern' => '',
                ],
                'emailDNS' => [
                    'mx' => [
                        '10 mail.wabblywabble.com',
                        '10 mail.wallywatts.com',
                    ],
                    'spfValid' => true,
                    'dkimConfigured' => false,
                    'dmarcValid' => false,
                    'mxRelay' => false,
                    'mxRelayCategory' => '',
                ],
                'domain' => [
                    'name' => 'filipx.com',
                    'enrichmentAvailable' => true,
                    'creationDate' => '2015-08-23T18:02:54.000Z',
                    'expirationDate' => '2026-08-23T18:02:54.000Z',
                    'updatedDate' => '2025-09-23T15:34:43.000Z',
                    'ageYears' => 10,
                    'registrar' => 'NameSilo, LLC',
                    'isBlockListed' => true,
                    'mtaStsStatus' => 'absent',
                    'bimiStatus' => 'absent',
                    'hasVMC' => false,
                    'aRecordValid' => false,
                    'aRecordReverseHost' => '',
                    'sslValid' => false,
                ],
            ],
        ];

        foreach ($scenarios as $i => $mockResponseData) {
            $mockResponse = Mockery::mock();
            $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

            $mockApiInstance = Mockery::mock(EmailInsightsApi::class);
            $mockApiInstance->shouldReceive('analyzeEmail')
                ->once()
                ->andReturn($mockResponse);

            $emailInsights = new EmailInsights('fake_api_key', $mockApiInstance);
            $response = $emailInsights->analyze([
                'email' => $mockResponseData['emailAddress'],
                'enableAi' => true,
                'enableAutoCorrection' => false,
            ]);

            $this->assertIsObject($response, 'Scenario '.($i + 1).': Response should be object');
            $this->assertEquals($mockResponseData['emailAddress'], $response->emailAddress, 'Scenario '.($i + 1).': Email address matches');
            $this->assertEquals($mockResponseData['isDeliverable'], $response->isDeliverable, 'Scenario '.($i + 1).': isDeliverable matches');
            $this->assertEquals($mockResponseData['emailType'], $response->emailType, 'Scenario '.($i + 1).': emailType matches');
            $this->assertEquals($mockResponseData['isFormatValid'], $response->isFormatValid, 'Scenario '.($i + 1).': isFormatValid matches');
            $this->assertEquals($mockResponseData['isMailboxFull'], $response->isMailboxFull, 'Scenario '.($i + 1).': isMailboxFull matches');
            $this->assertEquals($mockResponseData['isCatchAll'], $response->isCatchAll, 'Scenario '.($i + 1).': isCatchAll matches');
            $this->assertEquals($mockResponseData['isReachable'], $response->isReachable, 'Scenario '.($i + 1).': isReachable matches');
            $this->assertEquals($mockResponseData['riskReport']['score'], $response->riskReport['score'], 'Scenario '.($i + 1).': riskReport.score matches');

            if ($i === 0) {
                $domain = $response->domain;
                if (is_array($domain)) {
                    $this->assertArrayHasKey('creationDate', $domain, 'Scenario 1: domain should include creationDate key');
                    $this->assertArrayHasKey('expirationDate', $domain, 'Scenario 1: domain should include expirationDate key');
                    $this->assertArrayHasKey('updatedDate', $domain, 'Scenario 1: domain should include updatedDate key');
                    $this->assertNull($domain['creationDate'], 'Scenario 1: creationDate should be null when enrichment is unavailable');
                    $this->assertNull($domain['expirationDate'], 'Scenario 1: expirationDate should be null when enrichment is unavailable');
                    $this->assertNull($domain['updatedDate'], 'Scenario 1: updatedDate should be null when enrichment is unavailable');
                } else {
                    $this->assertNull($domain->creationDate, 'Scenario 1: creationDate should be null when enrichment is unavailable');
                    $this->assertNull($domain->expirationDate, 'Scenario 1: expirationDate should be null when enrichment is unavailable');
                    $this->assertNull($domain->updatedDate, 'Scenario 1: updatedDate should be null when enrichment is unavailable');
                }
            }
        }
    }
}
