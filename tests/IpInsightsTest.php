<?php

use PHPUnit\Framework\TestCase;
use Opportify\Sdk\IpInsights;
use OpenAPI\Client\Api\IpInsightsApi;
use OpenAPI\Client\Model\AnalyzeIpRequest;
use OpenAPI\Client\Configuration as ApiConfiguration;
use GuzzleHttp\Client;
use Mockery as m;

class IpInsightsTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close(); // Ensures all expectations are verified
    }

    public function testSetHost()
    {
        $insights = new IpInsights('fake_api_key');
        $insights->setHost('https://new-opportify.com');

        $reflection = new \ReflectionClass($insights);
        $property = $reflection->getProperty('host');
        $property->setAccessible(true);

        $this->assertEquals('https://new-opportify.com', $property->getValue($insights));
    }

    public function testSetVersion()
    {
        $insights = new IpInsights('fake_api_key');
        $insights->setVersion('v2');

        $reflection = new \ReflectionClass($insights);
        $property = $reflection->getProperty('version');
        $property->setAccessible(true);

        $this->assertEquals('v2', $property->getValue($insights));
    }

    public function testSetDebugMode()
    {
        $insights = new IpInsights('fake_api_key');
        $insights->setDebugMode(true);

        $reflection = new \ReflectionClass($insights);
        $property = $reflection->getProperty('debugMode');
        $property->setAccessible(true);

        $this->assertTrue($property->getValue($insights));
    }

    public function testAnalyzeSuccess()
    {
        $mockResponseData = (object) [
            "ipAddress" => "123.45.67.89",
            "ipAddressNumber" => 123456789,
            "ipType" => "IPv4",
            "ipCidr" => "123.0.0.0/10",
            "connectionType" => "wired",
            "hostReverse" => "example.host.provider.net",
            "geo" => (object) [
                "continent" => "NA",
                "countryCode" => "US",
                "countryName" => "United States",
                "countryShortName" => "USA",
                "currencyCode" => "USD",
                "domainExtension" => ".us",
                "languages" => "en-US,es-US,haw,fr",
                "latitude" => 37.7749,
                "longitude" => -122.4194,
                "phoneIntCode" => "1",
                "timezone" => "America/Los_Angeles"
            ],
            "whois" => (object) [
                "rir" => "ARIN",
                "asn" => (object) ["asName" => "EXAMPLE-AS"],
                "organization" => (object) [
                    "orgId" => "ORG-EX1-US",
                    "orgName" => "Example Corp.",
                    "orgType" => "ISP",
                    "country" => "US"
                ],
                "abuseContact" => (object) ["contactId" => "EX123-US", "name" => "Abuse Contact Example"],
                "adminContact" => (object) ["contactId" => "AD123-US", "name" => "Admin Contact Example"],
                "techContact" => (object) ["contactId" => "TE123-US", "name" => "Tech Contact Example"]
            ],
            "trustedProvider" => (object) ["isKnownProvider" => true],
            "blocklisted" => (object) ["isBlockListed" => false, "sources" => 0, "activeReports" => 0],
            "riskReport" => (object) ["score" => 321, "level" => "low"]
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
            'enableAi' => true
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

    public function testThrowsExceptionWhenAnalyzeFails()
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
            'enableAi' => true
        ]);
    }

    public function testNormalizeRequest()
    {
        $insights = new IpInsights('fake_api_key');

        $input = [
            'ip' => '192.168.1.1',
            'enableAi' => true
        ];

        $expectedOutput = [
            'ip' => '192.168.1.1',
            'enable_ai' => true
        ];

        $reflection = new \ReflectionClass($insights);
        $method = $reflection->getMethod('normalizeRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($insights, [$input]);

        $this->assertEquals($expectedOutput, $normalized);
    }
}
