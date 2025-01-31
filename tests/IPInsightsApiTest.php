<?php

use PHPUnit\Framework\TestCase;
use OpenAPI\Client\Api\IPInsightsApi;
use OpenAPI\Client\Model\AnalyzeIpRequest;
use OpenAPI\Client\Configuration;
use OpenAPI\Client\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Mockery;

class IPInsightsApiTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close(); // Ensures all expectations are verified
    }

    private function createMockConfig()
    {
        $mockConfig = Mockery::mock(Configuration::class);
        $mockConfig->shouldReceive('getApiKeyWithPrefix')->andReturn('fake_api_key');
        $mockConfig->shouldReceive('getHost')->andReturn('https://api.opportify.ai');
        $mockConfig->shouldReceive('getUserAgent')->andReturn('MockUserAgent');
        $mockConfig->shouldReceive('getDebug')->andReturn(false);
        $mockConfig->shouldReceive('getDebugFile')->andReturn('/dev/null');

        return $mockConfig;
    }

    public function testAnalyzeIpSuccess()
    {
        $mockResponseData = json_encode([
            "ipAddress" => "123.45.67.89",
            "ipAddressNumber" => 123456789,
            "ipType" => "IPv4",
            "ipCidr" => "123.0.0.0/10",
            "connectionType" => "wired",
            "hostReverse" => "example.host.provider.net",
            "geo" => [
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
            "whois" => [
                "rir" => "ARIN",
                "asn" => ["asName" => "EXAMPLE-AS"],
                "organization" => [
                    "orgId" => "ORG-EX1-US",
                    "orgName" => "Example Corp.",
                    "orgType" => "ISP",
                    "country" => "US"
                ],
                "abuseContact" => ["contactId" => "EX123-US", "name" => "Abuse Contact Example"],
                "adminContact" => ["contactId" => "AD123-US", "name" => "Admin Contact Example"],
                "techContact" => ["contactId" => "TE123-US", "name" => "Tech Contact Example"]
            ],
            "trustedProvider" => ["isKnownProvider" => true],
            "blocklisted" => ["isBlockListed" => false, "sources" => 0, "activeReports" => 0],
            "riskReport" => ["score" => 321, "level" => "low"]
        ]);

        // Create a valid Guzzle Response object
        $mockResponse = new Response(200, ['Content-Type' => 'application/json'], $mockResponseData);

        // Mock the Guzzle HTTP client to return the response
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->andReturn($mockResponse);

        $config = $this->createMockConfig();
        $apiInstance = new IPInsightsApi($mockClient, $config);

        $request = new AnalyzeIpRequest(['ip' => '123.45.67.89']);
        $response = $apiInstance->analyzeIp($request);

        // Decode response since it's returned as an object
        $decodedResponse = json_decode(json_encode($response));

        // Assertions
        $this->assertIsObject($decodedResponse);
        $this->assertEquals("123.45.67.89", $decodedResponse->ipAddress);
        $this->assertEquals(123456789, $decodedResponse->ipAddressNumber);
        $this->assertEquals("IPv4", $decodedResponse->ipType);
        $this->assertEquals("wired", $decodedResponse->connectionType);
        $this->assertEquals("example.host.provider.net", $decodedResponse->hostReverse);

        // Geo Assertions
        $this->assertIsObject($decodedResponse->geo);
        $this->assertEquals("US", $decodedResponse->geo->countryCode);
        $this->assertEquals("United States", $decodedResponse->geo->countryName);
        $this->assertEquals(37.7749, $decodedResponse->geo->latitude);
        $this->assertEquals(-122.4194, $decodedResponse->geo->longitude);
        $this->assertEquals("America/Los_Angeles", $decodedResponse->geo->timezone);

        // WHOIS Assertions
        $this->assertIsObject($decodedResponse->whois);
        $this->assertEquals("ARIN", $decodedResponse->whois->rir);
        $this->assertEquals("EXAMPLE-AS", $decodedResponse->whois->asn->asName);
        $this->assertEquals("Example Corp.", $decodedResponse->whois->organization->orgName);

        // Risk Report Assertions
        $this->assertIsObject($decodedResponse->riskReport);
        $this->assertEquals(321, $decodedResponse->riskReport->score);
        $this->assertEquals("low", $decodedResponse->riskReport->level);

        // Trusted Provider Assertions
        $this->assertIsObject($decodedResponse->trustedProvider);
        $this->assertTrue($decodedResponse->trustedProvider->isKnownProvider);

        // Blocklist Assertions
        $this->assertIsObject($decodedResponse->blocklisted);
        $this->assertFalse($decodedResponse->blocklisted->isBlockListed);
        $this->assertEquals(0, $decodedResponse->blocklisted->sources);
    }

    public function testAnalyzeIpThrows400BadRequest()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('[400]');

        $mockException = new RequestException(
            '[400] Bad Request',
            new Request('POST', 'https://api.opportify.ai/insights/v1/ip/analyze'),
            new Response(400, [], json_encode(['errorCode' => 'INVALID_INPUT', 'errorMessage' => 'Invalid IP address format']))
        );

        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->andThrow($mockException);

        $config = $this->createMockConfig();
        $apiInstance = new IPInsightsApi($mockClient, $config);

        $request = new AnalyzeIpRequest(['ip' => 'invalid_ip']);
        $apiInstance->analyzeIp($request);
    }

    public function testAnalyzeIpThrows403Forbidden()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('[403]');

        $mockException = new RequestException(
            '[403] Forbidden',
            new Request('POST', 'https://api.opportify.ai/insights/v1/ip/analyze'),
            new Response(403, [], json_encode(['errorCode' => 'INVALID_TOKEN', 'errorMessage' => 'Invalid API Key']))
        );

        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->andThrow($mockException);

        $config = $this->createMockConfig();
        $apiInstance = new IPInsightsApi($mockClient, $config);

        $request = new AnalyzeIpRequest(['ip' => '192.168.1.1']);
        $apiInstance->analyzeIp($request);
    }

    public function testAnalyzeIpThrows500ServerError()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('[500]');

        $mockException = new RequestException(
            '[500] Internal Server Error',
            new Request('POST', 'https://api.opportify.ai/insights/v1/ip/analyze'),
            new Response(500, [], json_encode(['errorCode' => 'SERVER_ERROR', 'errorMessage' => 'Internal Server Error']))
        );

        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->andThrow($mockException);

        $config = $this->createMockConfig();
        $apiInstance = new IPInsightsApi($mockClient, $config);

        $request = new AnalyzeIpRequest(['ip' => '192.168.1.1']);
        $apiInstance->analyzeIp($request);
    }
}
