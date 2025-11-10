<?php

use GuzzleHttp\ClientInterface;
use Mockery as m;
use OpenAPI\Client\Api\EmailInsightsApi;
use OpenAPI\Client\ApiException;
use OpenAPI\Client\Configuration;
use OpenAPI\Client\HeaderSelector;
use OpenAPI\Client\Model\AnalyzeEmailRequest;
use PHPUnit\Framework\TestCase;

class EmailInsightsApiTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_analyze_email_success()
    {
        $mockClient = Mockery::mock(\GuzzleHttp\ClientInterface::class);

        // Define a properly formatted API JSON response
        $mockResponseData = json_encode([
            'emailAddress' => 'test@example.com',
            'emailProvider' => 'Google',
            'emailType' => 'free',
            'isFormatValid' => true,
            'emailCorrection' => '',
            'isReachable' => true,
            'isDeliverable' => 'true',
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
        ]);

        // Create a PSR-7 response with JSON body
        $mockResponse = new \GuzzleHttp\Psr7\Response(200, [], $mockResponseData);

        // Mock the send method to return the PSR-7 response
        $mockClient->shouldReceive('send')
            ->once()
            ->andReturn($mockResponse);

        // Mock Configuration and HeaderSelector
        $config = Mockery::mock(\OpenAPI\Client\Configuration::class);
        $config->shouldReceive('getApiKeyWithPrefix')->andReturn('fake_api_key');
        $config->shouldReceive('getHost')->andReturn('https://api.opportify.ai');
        $config->shouldReceive('getUserAgent')->andReturn('MockUserAgent');
        $config->shouldReceive('getDebug')->andReturn(false);
        $config->shouldReceive('getDebugFile')->andReturn('/dev/null');
        $config->shouldReceive('getCertFile')->andReturn(null);
        $config->shouldReceive('getKeyFile')->andReturn(null);

        $headerSelector = Mockery::mock(\OpenAPI\Client\HeaderSelector::class);
        $headerSelector->shouldReceive('selectHeaders')->andReturn([
            'Content-Type' => 'application/json',
        ]);

        // Create EmailInsightsApi instance with mock client
        $apiInstance = new EmailInsightsApi($mockClient, $config, $headerSelector);
        $request = new AnalyzeEmailRequest(['email' => 'test@example.com']);

        // Call analyzeEmail()
        $response = $apiInstance->analyzeEmail($request);

        // Assertions to ensure response is correct
        $this->assertInstanceOf(\OpenAPI\Client\Model\AnalyzeEmail200Response::class, $response);
        $this->assertEquals('test@example.com', $response->jsonSerialize()->emailAddress);
        $this->assertEquals('Google', $response->jsonSerialize()->emailProvider);
        $this->assertTrue($response->jsonSerialize()->isFormatValid);
    }

    public function test_analyze_email_invalid_request()
    {
        $mockClient = Mockery::mock(ClientInterface::class);

        // Simulate an API Exception for 400 Bad Request
        $mockClient->shouldReceive('send')
            ->once()
            ->andThrow(new ApiException(
                '[400] Invalid email format',
                400,
                [],
                json_encode(['error' => 'Invalid email format'])
            ));

        $config = Mockery::mock(Configuration::class);
        $config->shouldReceive('getApiKeyWithPrefix')->andReturn('fake_api_key');
        $config->shouldReceive('getHost')->andReturn('https://api.opportify.ai');
        $config->shouldReceive('getUserAgent')->andReturn('MockUserAgent');
        $config->shouldReceive('getDebug')->andReturn(false);
        $config->shouldReceive('getDebugFile')->andReturn('/dev/null');
        $config->shouldReceive('getCertFile')->andReturn(null);
        $config->shouldReceive('getKeyFile')->andReturn(null);

        $headerSelector = Mockery::mock(HeaderSelector::class);
        $headerSelector->shouldReceive('selectHeaders')->andReturn([
            'Content-Type' => 'application/json',
        ]);

        $apiInstance = new EmailInsightsApi($mockClient, $config, $headerSelector);
        $request = new AnalyzeEmailRequest(['email' => 'invalid-email']);

        $this->expectException(ApiException::class);
        $apiInstance->analyzeEmail($request);
    }

    public function test_analyze_email_forbidden()
    {
        $mockClient = Mockery::mock(ClientInterface::class);

        // Simulate an API Exception for 403 Forbidden
        $mockClient->shouldReceive('send')
            ->once()
            ->andThrow(new ApiException(
                '[403] Invalid API token',
                403,
                [],
                json_encode(['error' => 'Invalid API token'])
            ));

        $config = Mockery::mock(Configuration::class);
        $config->shouldReceive('getApiKeyWithPrefix')->andReturn('invalid_api_key');
        $config->shouldReceive('getHost')->andReturn('https://api.opportify.ai');
        $config->shouldReceive('getUserAgent')->andReturn('MockUserAgent');
        $config->shouldReceive('getDebug')->andReturn(false);
        $config->shouldReceive('getDebugFile')->andReturn('/dev/null');
        $config->shouldReceive('getCertFile')->andReturn(null);
        $config->shouldReceive('getKeyFile')->andReturn(null);

        $headerSelector = Mockery::mock(HeaderSelector::class);
        $headerSelector->shouldReceive('selectHeaders')->andReturn([
            'Content-Type' => 'application/json',
        ]);

        $apiInstance = new EmailInsightsApi($mockClient, $config, $headerSelector);
        $request = new AnalyzeEmailRequest(['email' => 'test@example.com']);

        $this->expectException(ApiException::class);
        $apiInstance->analyzeEmail($request);
    }

    public function test_analyze_email_server_error()
    {
        $mockClient = Mockery::mock(ClientInterface::class);

        // Simulate an API Exception for 500 Internal Server Error
        $mockClient->shouldReceive('send')
            ->once()
            ->andThrow(new ApiException(
                '[500] Internal Server Error',
                500,
                [],
                json_encode(['error' => 'Internal Server Error'])
            ));

        $config = Mockery::mock(Configuration::class);
        $config->shouldReceive('getApiKeyWithPrefix')->andReturn('fake_api_key');
        $config->shouldReceive('getHost')->andReturn('https://api.opportify.ai');
        $config->shouldReceive('getUserAgent')->andReturn('MockUserAgent');
        $config->shouldReceive('getDebug')->andReturn(false);
        $config->shouldReceive('getDebugFile')->andReturn('/dev/null');
        $config->shouldReceive('getCertFile')->andReturn(null);
        $config->shouldReceive('getKeyFile')->andReturn(null);

        $headerSelector = Mockery::mock(HeaderSelector::class);
        $headerSelector->shouldReceive('selectHeaders')->andReturn([
            'Content-Type' => 'application/json',
        ]);

        $apiInstance = new EmailInsightsApi($mockClient, $config, $headerSelector);
        $request = new AnalyzeEmailRequest(['email' => 'test@example.com']);

        $this->expectException(ApiException::class);
        $apiInstance->analyzeEmail($request);
    }
}
