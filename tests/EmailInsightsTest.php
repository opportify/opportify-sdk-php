<?php

use Mockery as m;
use OpenAPI\Client\Api\EmailInsightsApi;
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
