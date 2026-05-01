<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use OpenAPI\FraudIntel\Client\Api\FraudProtectionApi;
use OpenAPI\FraudIntel\Client\ApiException;
use OpenAPI\FraudIntel\Client\Configuration;
use OpenAPI\FraudIntel\Client\Model\AnalyzeFraudRequest;
use PHPUnit\Framework\TestCase;

class FraudProtectionApiTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    private function createMockConfig(): Configuration
    {
        $mockConfig = Mockery::mock(Configuration::class);
        $mockConfig->shouldReceive('getApiKeyWithPrefix')->andReturn('fake_api_key');
        $mockConfig->shouldReceive('getHost')->andReturn('https://api.opportify.ai/intel/v1');
        $mockConfig->shouldReceive('getUserAgent')->andReturn('MockUserAgent');
        $mockConfig->shouldReceive('getDebug')->andReturn(false);
        $mockConfig->shouldReceive('getDebugFile')->andReturn('/dev/null');
        $mockConfig->shouldReceive('getCertFile')->andReturn(null);
        $mockConfig->shouldReceive('getKeyFile')->andReturn(null);

        return $mockConfig;
    }

    public function test_analyze_fraud_success()
    {
        $mockResponseData = json_encode([
            'score' => 72,
            'level' => 'high',
            'factors' => ['suspicious_email', 'vpn_detected'],
            'sources' => [
                'email' => ['format' => (object)['isFormatValid' => true]],
                'ip'    => ['isVpn' => true, 'isTor' => false],
            ],
            'meta' => ['requestId' => 'req_abc123'],
        ]);

        $mockResponse = new Response(200, ['Content-Type' => 'application/json'], $mockResponseData);

        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->andReturn($mockResponse);

        $config = $this->createMockConfig();
        $apiInstance = new FraudProtectionApi($mockClient, $config);

        $request = new AnalyzeFraudRequest(['email' => 'test@example.com', 'enable_ai' => true]);
        $response = $apiInstance->analyzeFraud($request);

        $decoded = json_decode(json_encode($response));

        $this->assertIsObject($decoded);
        $this->assertEquals(72, $decoded->score);
        $this->assertEquals('high', $decoded->level);
        $this->assertIsArray($decoded->factors);
        $this->assertContains('vpn_detected', $decoded->factors);
        $this->assertIsObject($decoded->sources);
    }

    public function test_analyze_fraud_throws400_bad_request()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('[400]');

        $mockException = new RequestException(
            '[400] Bad Request',
            new Request('POST', 'https://api.opportify.ai/intel/v1/fraud/analyze'),
            new Response(400, [], json_encode(['errorCode' => 'INVALID_INPUT', 'errorMessage' => 'Missing required field']))
        );

        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->andThrow($mockException);

        $config = $this->createMockConfig();
        $apiInstance = new FraudProtectionApi($mockClient, $config);

        $request = new AnalyzeFraudRequest([]);
        $apiInstance->analyzeFraud($request);
    }

    public function test_analyze_fraud_throws403_forbidden()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('[403]');

        $mockException = new RequestException(
            '[403] Forbidden',
            new Request('POST', 'https://api.opportify.ai/intel/v1/fraud/analyze'),
            new Response(403, [], json_encode(['errorCode' => 'INVALID_TOKEN', 'errorMessage' => 'Invalid API Key']))
        );

        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->andThrow($mockException);

        $config = $this->createMockConfig();
        $apiInstance = new FraudProtectionApi($mockClient, $config);

        $request = new AnalyzeFraudRequest(['email' => 'test@example.com', 'enable_ai' => true]);
        $apiInstance->analyzeFraud($request);
    }

    public function test_analyze_fraud_throws500_server_error()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('[500]');

        $mockException = new RequestException(
            '[500] Internal Server Error',
            new Request('POST', 'https://api.opportify.ai/intel/v1/fraud/analyze'),
            new Response(500, [], json_encode(['errorCode' => 'SERVER_ERROR', 'errorMessage' => 'Internal Server Error']))
        );

        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->andThrow($mockException);

        $config = $this->createMockConfig();
        $apiInstance = new FraudProtectionApi($mockClient, $config);

        $request = new AnalyzeFraudRequest(['email' => 'test@example.com', 'enable_ai' => true]);
        $apiInstance->analyzeFraud($request);
    }
}
