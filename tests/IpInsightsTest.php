<?php

use Mockery as m;
use OpenAPI\Client\Api\IpInsightsApi;
use OpenAPI\Client\Model\ExportRequest;
use Opportify\Sdk\IpInsights;
use PHPUnit\Framework\TestCase;

class IpInsightsTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close(); // Ensures all expectations are verified
    }

    public function test_set_host()
    {
        $insights = new IpInsights('fake_api_key');
        $insights->setHost('https://new-opportify.com');

        $reflection = new \ReflectionClass($insights);
        $property = $reflection->getProperty('host');
        $property->setAccessible(true);

        $this->assertEquals('https://new-opportify.com', $property->getValue($insights));
    }

    public function test_set_version()
    {
        $insights = new IpInsights('fake_api_key');
        $insights->setVersion('v2');

        $reflection = new \ReflectionClass($insights);
        $property = $reflection->getProperty('version');
        $property->setAccessible(true);

        $this->assertEquals('v2', $property->getValue($insights));
    }

    public function test_set_prefix()
    {
        $insights = new IpInsights('fake_api_key');
        $insights->setPrefix('new-prefix');

        $reflection = new \ReflectionClass($insights);
        $property = $reflection->getProperty('prefix');
        $property->setAccessible(true);

        $this->assertEquals('new-prefix', $property->getValue($insights));
    }

    public function test_set_debug_mode()
    {
        $insights = new IpInsights('fake_api_key');
        $insights->setDebugMode(true);

        $reflection = new \ReflectionClass($insights);
        $property = $reflection->getProperty('debugMode');
        $property->setAccessible(true);

        $this->assertTrue($property->getValue($insights));
    }

    public function test_analyze_success()
    {
        $mockResponseData = (object) [
            'ipAddress' => '123.45.67.89',
            'ipAddressNumber' => 123456789,
            'ipType' => 'IPv4',
            'ipCidr' => '123.0.0.0/10',
            'connectionType' => 'wired',
            'hostReverse' => 'example.host.provider.net',
            'geo' => (object) [
                'continent' => 'NA',
                'countryCode' => 'US',
                'countryName' => 'United States',
                'countryShortName' => 'USA',
                'currencyCode' => 'USD',
                'domainExtension' => '.us',
                'languages' => 'en-US,es-US,haw,fr',
                'latitude' => 37.7749,
                'longitude' => -122.4194,
                'phoneIntCode' => '1',
                'timezone' => 'America/Los_Angeles',
            ],
            'whois' => (object) [
                'rir' => 'ARIN',
                'asn' => (object) ['asName' => 'EXAMPLE-AS'],
                'organization' => (object) [
                    'orgId' => 'ORG-EX1-US',
                    'orgName' => 'Example Corp.',
                    'orgType' => 'ISP',
                    'country' => 'US',
                ],
                'abuseContact' => (object) ['contactId' => 'EX123-US', 'name' => 'Abuse Contact Example'],
                'adminContact' => (object) ['contactId' => 'AD123-US', 'name' => 'Admin Contact Example'],
                'techContact' => (object) ['contactId' => 'TE123-US', 'name' => 'Tech Contact Example'],
            ],
            'trustedProvider' => (object) ['isKnownProvider' => true],
            'blocklisted' => (object) ['isBlockListed' => false, 'sources' => 0, 'activeReports' => 0],
            'riskReport' => (object) ['score' => 321, 'level' => 'low'],
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn($mockResponseData);

        // Mock the API instance
        $mockApiInstance = Mockery::mock(IpInsightsApi::class);
        $mockApiInstance->shouldReceive('analyzeIp')
            ->once()
            ->andReturn($mockResponse);

        // Inject the mock API instance via constructor
        $insights = new IpInsights('fake_api_key', $mockApiInstance);

        $response = $insights->analyze([
            'ip' => '123.45.67.89',
            'enableAi' => true,
        ]);

        // Assertions to ensure response is correct
        $this->assertIsObject($response);
        $this->assertEquals('123.45.67.89', $response->ipAddress);

        // FIXED: Correct access to `riskScore`
        $this->assertIsObject($response->riskReport);
        $this->assertEquals(321, $response->riskReport->score);

        $this->assertIsObject($response->geo); // Ensure geo is an object
        $this->assertEquals('US', $response->geo->countryCode);
        $this->assertEquals('United States', $response->geo->countryName);
        $this->assertEquals(37.7749, $response->geo->latitude);
        $this->assertEquals(-122.4194, $response->geo->longitude);
    }

    public function test_throws_exception_when_analyze_fails()
    {
        // Create a mock for IpInsightsApi
        $mockApiInstance = Mockery::mock(IpInsightsApi::class);
        $mockApiInstance->shouldReceive('analyzeIp')
            ->once()
            ->andThrow(new \OpenAPI\Client\ApiException(
                '[403] Client error: `POST https://api.opportify.com/insights/v1/ip/analyze` resulted in a `403 Forbidden` response: {"errorCode": "INVALID_TOKEN", "errorMessage": "The token provided is either invalid, expired, or missing"}',
                403
            ));

        // Inject the mock API instance via constructor
        $insights = new IpInsights('invalid_api_key', $mockApiInstance);

        $this->expectException(\OpenAPI\Client\ApiException::class);
        $this->expectExceptionMessage('[403]');
        $this->expectExceptionMessage('INVALID_TOKEN');

        $insights->analyze([
            'ip' => '192.168.1.1',
            'enableAi' => true,
        ]);
    }

    public function test_normalize_request()
    {
        $insights = new IpInsights('fake_api_key');

        $input = [
            'ip' => '192.168.1.1',
            'enableAi' => true,
        ];

        $expectedOutput = [
            'ip' => '192.168.1.1',
            'enable_ai' => true,
        ];

        $reflection = new \ReflectionClass($insights);
        $method = $reflection->getMethod('normalizeRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($insights, [$input]);

        $this->assertEquals($expectedOutput, $normalized);
    }

    public function test_normalize_batch_request()
    {
        $insights = new IpInsights('fake_api_key');

        $input = [
            'ips' => ['192.168.1.1', '10.0.0.1'],
            'enableAi' => true,
        ];

        $expectedOutput = [
            'ips' => ['192.168.1.1', '10.0.0.1'],
            'enable_ai' => true,
        ];

        $reflection = new \ReflectionClass($insights);
        $method = $reflection->getMethod('normalizeBatchRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($insights, [$input]);

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
        $mockApiInstance = Mockery::mock(IpInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeIps')
            ->once()
            ->andReturn($mockResponse);

        // Inject the mock API instance via constructor
        $insights = new IpInsights('fake_api_key', $mockApiInstance);

        $response = $insights->batchAnalyze([
            'ips' => ['192.168.1.1', '10.0.0.1'],
            'enableAi' => true,
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
        $mockApiInstance = Mockery::mock(IpInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeIps')
            ->once()
            ->with(Mockery::type('\GuzzleHttp\Psr7\MultipartStream'), 'multipart/form-data')
            ->andReturn($mockResponse);

        // Create a temporary file for testing
        $tempFilePath = sys_get_temp_dir().'/test_ips.csv';
        file_put_contents($tempFilePath, '192.168.1.1'.PHP_EOL.'10.0.0.1');

        // Inject the mock API instance via constructor
        $insights = new IpInsights('fake_api_key', $mockApiInstance);

        $response = $insights->batchAnalyze([
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
        $insights = new IpInsights('fake_api_key');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File parameter is required and must be a valid file path');

        $insights->batchAnalyze([
            'enableAi' => true,
        ], 'multipart/form-data');
    }

    public function test_batch_analyze_multipart_throws_exception_when_file_not_exists()
    {
        $insights = new IpInsights('fake_api_key');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File parameter is required and must be a valid file path');

        $insights->batchAnalyze([
            'file' => '/non/existent/file.csv',
            'enableAi' => true,
        ], 'multipart/form-data');
    }

    public function test_batch_analyze_multipart_handles_fopen_errors_gracefully()
    {
        // Note: This test demonstrates that our code properly checks fopen() return values
        // In practice, fopen() failures are rare and typically indicate system-level issues
        // The important thing is that we check for false and throw meaningful exceptions

        $insights = new IpInsights('fake_api_key');

        // Test with a path that's guaranteed to fail (null byte is invalid in filenames)
        $invalidPath = "invalid\0path.csv";

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File parameter is required and must be a valid file path');

        $insights->batchAnalyze([
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
        $mockApiInstance = Mockery::mock(IpInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeIps')
            ->once()
            ->with(Mockery::type('\GuzzleHttp\Psr7\MultipartStream'), 'multipart/form-data')
            ->andReturn($mockResponse);

        // Create a temporary file for testing
        $tempFilePath = sys_get_temp_dir().'/test_ips_full.csv';
        file_put_contents($tempFilePath, '192.168.1.1'.PHP_EOL.'10.0.0.1');

        // Inject the mock API instance via constructor
        $insights = new IpInsights('fake_api_key', $mockApiInstance);

        $response = $insights->batchAnalyze([
            'file' => $tempFilePath,
            'enable_ai' => true,
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
        $mockApiInstance = Mockery::mock(IpInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeIps')
            ->once()
            ->with(Mockery::type('string'), 'text/plain')
            ->andReturn($mockResponse);

        // Inject the mock API instance via constructor
        $insights = new IpInsights('fake_api_key', $mockApiInstance);

        $response = $insights->batchAnalyze([
            'text' => "192.168.1.1\n10.0.0.1",
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
        $mockApiInstance = Mockery::mock(IpInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeIps')
            ->once()
            ->andReturn($mockResponse);

        // Create a temporary file for testing
        $tempFilePath = sys_get_temp_dir().'/test_ips.csv';
        file_put_contents($tempFilePath, '192.168.1.1'.PHP_EOL.'10.0.0.1');

        // Inject the mock API instance via constructor
        $insights = new IpInsights('fake_api_key', $mockApiInstance);

        $response = $insights->batchAnalyzeFile($tempFilePath, ['enableAi' => true]);

        // Clean up
        unlink($tempFilePath);

        // Assertions to ensure response is correct
        $this->assertIsObject($response);
        $this->assertEquals('job-123456', $response->jobId);
        $this->assertEquals('QUEUED', $response->status);
    }

    public function test_batch_analyze_file_helper_method_with_txt_file()
    {
        $mockResponseData = [
            'jobId' => 'job-654321',
            'status' => 'QUEUED',
            'statusDescription' => '',
        ];

        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        $mockApiInstance = Mockery::mock(IpInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeIps')
            ->once()
            ->andReturn($mockResponse);

        $tempFilePath = sys_get_temp_dir().'/test_ips.txt';
        file_put_contents($tempFilePath, "192.168.1.1\n10.0.0.1");

        $insights = new IpInsights('fake_api_key', $mockApiInstance);
        $response = $insights->batchAnalyzeFile($tempFilePath, ['enableAi' => true]);

        unlink($tempFilePath);

        $this->assertIsObject($response);
        $this->assertEquals('job-654321', $response->jobId);
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
        $mockApiInstance = Mockery::mock(IpInsightsApi::class);
        $mockApiInstance->shouldReceive('getIpBatchStatus')
            ->once()
            ->with('job-123456')
            ->andReturn($mockResponse);

        // Inject the mock API instance via constructor
        $insights = new IpInsights('fake_api_key', $mockApiInstance);

        $response = $insights->getBatchStatus('job-123456');

        // Assertions to ensure response is correct
        $this->assertIsObject($response);
        $this->assertEquals('job-123456', $response->jobId);
        $this->assertEquals('COMPLETED', $response->status);
        $this->assertEquals(100, $response->progress);
    }

    public function test_create_batch_export_with_payload()
    {
        $mockResponseData = [
            'exportId' => 'ip-export-001',
            'status' => 'QUEUED',
        ];

        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        $mockApiInstance = Mockery::mock(IpInsightsApi::class);
        $mockApiInstance->shouldReceive('createIpBatchExport')
            ->once()
            ->with('job-ip-1', Mockery::on(function ($request) {
                \PHPUnit\Framework\Assert::assertInstanceOf(ExportRequest::class, $request);
                \PHPUnit\Framework\Assert::assertEquals('csv', $request->getExportType());
                \PHPUnit\Framework\Assert::assertEquals(['result.ipAddress', 'result.riskReport.score'], $request->getColumns());
                \PHPUnit\Framework\Assert::assertEquals(['result.riskReport.level' => 'low'], $request->getFilters());

                return true;
            }))
            ->andReturn($mockResponse);

        $ipInsights = new IpInsights('fake_api_key', $mockApiInstance);

        $response = $ipInsights->createBatchExport('job-ip-1', [
            'exportType' => 'CSV',
            'columns' => ['result.ipAddress', 'result.riskReport.score'],
            'filters' => ['result.riskReport.level' => 'low'],
        ]);

        $this->assertEquals('ip-export-001', $response->exportId);
        $this->assertEquals('QUEUED', $response->status);
    }

    public function test_create_batch_export_without_payload()
    {
        $mockResponseData = [
            'exportId' => 'ip-export-002',
            'status' => 'PROCESSING',
        ];

        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        $mockApiInstance = Mockery::mock(IpInsightsApi::class);
        $mockApiInstance->shouldReceive('createIpBatchExport')
            ->once()
            ->with('job-ip-2', null)
            ->andReturn($mockResponse);

        $ipInsights = new IpInsights('fake_api_key', $mockApiInstance);

        $response = $ipInsights->createBatchExport('job-ip-2');

        $this->assertEquals('ip-export-002', $response->exportId);
        $this->assertEquals('PROCESSING', $response->status);
    }

    public function test_create_batch_export_validates_job_id()
    {
        $ipInsights = new IpInsights('fake_api_key', Mockery::mock(IpInsightsApi::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Job ID cannot be empty when creating an export.');

        $ipInsights->createBatchExport('');
    }

    public function test_create_batch_export_validates_filters_type()
    {
        $ipInsights = new IpInsights('fake_api_key', Mockery::mock(IpInsightsApi::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Filters must be provided as an array.');

        $ipInsights->createBatchExport('job-ip-3', [
            'filters' => 'invalid',
        ]);
    }

    public function test_create_batch_export_validates_columns_type()
    {
        $ipInsights = new IpInsights('fake_api_key', Mockery::mock(IpInsightsApi::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Columns must be provided as an array.');

        $ipInsights->createBatchExport('job-ip-4', [
            'columns' => 'result.ipAddress',
        ]);
    }

    public function test_get_batch_export_status_success()
    {
        $mockResponseData = [
            'exportId' => 'ip-export-003',
            'status' => 'COMPLETED',
            'downloadUrl' => 'https://example.com/ip-export.json',
        ];

        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        $mockApiInstance = Mockery::mock(IpInsightsApi::class);
        $mockApiInstance->shouldReceive('getIpBatchExportStatus')
            ->once()
            ->with('job-ip-3', 'ip-export-003')
            ->andReturn($mockResponse);

        $ipInsights = new IpInsights('fake_api_key', $mockApiInstance);

        $response = $ipInsights->getBatchExportStatus('job-ip-3', 'ip-export-003');

        $this->assertEquals('ip-export-003', $response->exportId);
        $this->assertEquals('COMPLETED', $response->status);
        $this->assertEquals('https://example.com/ip-export.json', $response->downloadUrl);
    }

    public function test_get_batch_export_status_validates_identifiers()
    {
        $ipInsights = new IpInsights('fake_api_key', Mockery::mock(IpInsightsApi::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Job ID and export ID are required to fetch export status.');

        $ipInsights->getBatchExportStatus('   ', 'ip-export-004');
    }

    public function test_batch_analyze_with_name_parameter_json()
    {
        $mockResponseData = [
            'jobId' => 'job-123456',
            'name' => 'Network Security Scan',
            'status' => 'QUEUED',
            'statusDescription' => '',
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        // Mock the API instance
        $mockApiInstance = Mockery::mock(IPInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeIps')
            ->once()
            ->andReturn($mockResponse);

        // Inject the mock API instance via constructor
        $ipInsights = new IpInsights('fake_api_key', $mockApiInstance);

        $response = $ipInsights->batchAnalyze([
            'ips' => ['192.168.1.1', '10.0.0.1'],
            'name' => 'Network Security Scan',
            'enableAi' => true,
        ]);

        // Assertions to ensure response is correct
        $this->assertIsObject($response);
        $this->assertEquals('job-123456', $response->jobId);
        $this->assertEquals('Network Security Scan', $response->name);
        $this->assertEquals('QUEUED', $response->status);
    }

    public function test_batch_analyze_with_name_parameter_multipart()
    {
        $mockResponseData = [
            'jobId' => 'job-123456',
            'name' => 'IP List Analysis',
            'status' => 'QUEUED',
            'statusDescription' => '',
        ];

        // Create a mock for the API response that implements jsonSerialize()
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn((object) $mockResponseData);

        // Mock the API instance - expects MultipartStream with name parameter
        $mockApiInstance = Mockery::mock(IPInsightsApi::class);
        $mockApiInstance->shouldReceive('batchAnalyzeIps')
            ->once()
            ->with(Mockery::type('\GuzzleHttp\Psr7\MultipartStream'), 'multipart/form-data')
            ->andReturn($mockResponse);

        // Create a temporary file for testing
        $tempFilePath = sys_get_temp_dir().'/test_ips_with_name.csv';
        file_put_contents($tempFilePath, '192.168.1.1'.PHP_EOL.'10.0.0.1');

        try {
            // Inject the mock API instance via constructor
            $ipInsights = new IpInsights('fake_api_key', $mockApiInstance);

            $response = $ipInsights->batchAnalyze([
                'file' => $tempFilePath,
                'name' => 'IP List Analysis',
                'enableAi' => true,
            ], 'multipart/form-data');

            // Assertions to ensure response is correct
            $this->assertIsObject($response);
            $this->assertEquals('job-123456', $response->jobId);
            $this->assertEquals('IP List Analysis', $response->name);
            $this->assertEquals('QUEUED', $response->status);
        } finally {
            // Clean up the temporary file
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }

    public function test_normalize_batch_request_with_name()
    {
        $ipInsights = new IpInsights('fake_api_key');

        $input = [
            'ips' => ['192.168.1.1', '10.0.0.1'],
            'name' => 'Security Audit',
            'enableAi' => true,
        ];

        $expectedOutput = [
            'ips' => ['192.168.1.1', '10.0.0.1'],
            'name' => 'Security Audit',
            'enable_ai' => true,
        ];

        $reflection = new \ReflectionClass($ipInsights);
        $method = $reflection->getMethod('normalizeBatchRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($ipInsights, [$input]);

        $this->assertEquals($expectedOutput, $normalized);
    }

    public function test_normalize_batch_request_without_name()
    {
        $ipInsights = new IpInsights('fake_api_key');

        $input = [
            'ips' => ['192.168.1.1', '10.0.0.1'],
            'enableAi' => true,
        ];

        $expectedOutput = [
            'ips' => ['192.168.1.1', '10.0.0.1'],
            'enable_ai' => true,
        ];

        $reflection = new \ReflectionClass($ipInsights);
        $method = $reflection->getMethod('normalizeBatchRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($ipInsights, [$input]);

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
        $insights = new IpInsights('fake_api_key');

        $insights->setHost($host);
        $insights->setPrefix($prefix);
        $insights->setVersion($version);

        $reflection = new \ReflectionClass($insights);
        $method = $reflection->getMethod('refreshApiInstance');
        $method->setAccessible(true);
        $method->invokeArgs($insights, []);

        $property = $reflection->getProperty('finalUrl');
        $property->setAccessible(true);
        $this->assertEquals($expected, $property->getValue($insights));
    }
}
