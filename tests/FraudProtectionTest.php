<?php

use Mockery as m;
use OpenAPI\FraudIntel\Client\Api\FraudProtectionApi;
use OpenAPI\FraudIntel\Client\ApiException;
use Opportify\Sdk\FraudProtection;
use PHPUnit\Framework\TestCase;

class FraudProtectionTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_set_host()
    {
        $fp = new FraudProtection('fake_api_key');
        $fp->setHost('https://new-api.opportify.ai');

        $reflection = new \ReflectionClass($fp);
        $property = $reflection->getProperty('host');
        $property->setAccessible(true);

        $this->assertEquals('https://new-api.opportify.ai', $property->getValue($fp));
    }

    public function test_set_version()
    {
        $fp = new FraudProtection('fake_api_key');
        $fp->setVersion('v2');

        $reflection = new \ReflectionClass($fp);
        $property = $reflection->getProperty('version');
        $property->setAccessible(true);

        $this->assertEquals('v2', $property->getValue($fp));
    }

    public function test_set_prefix()
    {
        $fp = new FraudProtection('fake_api_key');
        $fp->setPrefix('new-prefix');

        $reflection = new \ReflectionClass($fp);
        $property = $reflection->getProperty('prefix');
        $property->setAccessible(true);

        $this->assertEquals('new-prefix', $property->getValue($fp));
    }

    public function test_set_debug_mode()
    {
        $fp = new FraudProtection('fake_api_key');
        $fp->setDebugMode(true);

        $reflection = new \ReflectionClass($fp);
        $property = $reflection->getProperty('debugMode');
        $property->setAccessible(true);

        $this->assertTrue($property->getValue($fp));
    }

    public function test_analyze_success()
    {
        $mockResponseData = (object) [
            'score'   => 72,
            'level'   => 'high',
            'factors' => ['suspicious_email', 'vpn_detected'],
            'sources' => (object) [
                'email' => (object) ['isDisposable' => true, 'isFormatValid' => true],
                'ip'    => (object) ['isVpn' => true, 'isTor' => false],
            ],
            'meta' => (object) ['requestId' => 'req_abc123'],
        ];

        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('jsonSerialize')->andReturn($mockResponseData);

        $mockApiInstance = Mockery::mock(FraudProtectionApi::class);
        $mockApiInstance->shouldReceive('analyzeFraud')
            ->once()
            ->andReturn($mockResponse);

        $fp = new FraudProtection('fake_api_key', $mockApiInstance);

        $response = $fp->analyze([
            'email'     => 'test@example.com',
            'user_ip'   => '1.2.3.4',
            'enable_ai' => true,
        ]);

        $this->assertIsObject($response);
        $this->assertEquals(72, $response->score);
        $this->assertEquals('high', $response->level);
        $this->assertIsArray($response->factors);
        $this->assertContains('vpn_detected', $response->factors);
        $this->assertIsObject($response->sources);
        $this->assertTrue($response->sources->email->isDisposable);
    }

    public function test_analyze_throws_exception_on_403()
    {
        $mockApiInstance = Mockery::mock(FraudProtectionApi::class);
        $mockApiInstance->shouldReceive('analyzeFraud')
            ->once()
            ->andThrow(new ApiException(
                '[403] Client error: `POST https://api.opportify.ai/intel/v1/fraud/analyze` resulted in a `403 Forbidden` response: {"errorCode": "INVALID_TOKEN", "errorMessage": "The token provided is either invalid, expired, or missing"}',
                403
            ));

        $fp = new FraudProtection('invalid_api_key', $mockApiInstance);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('[403]');
        $this->expectExceptionMessage('INVALID_TOKEN');

        $fp->analyze(['email' => 'test@example.com']);
    }

    public function test_analyze_throws_exception_on_500()
    {
        $mockApiInstance = Mockery::mock(FraudProtectionApi::class);
        $mockApiInstance->shouldReceive('analyzeFraud')
            ->once()
            ->andThrow(new ApiException('[500] Internal Server Error', 500));

        $fp = new FraudProtection('fake_api_key', $mockApiInstance);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('[500]');

        $fp->analyze(['email' => 'test@example.com']);
    }

    public function test_normalize_request_snake_case()
    {
        $fp = new FraudProtection('fake_api_key');

        $input = [
            'email'       => 'user@example.com',
            'user_ip'     => '10.0.0.1',
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'enable_ai'   => false,
        ];

        $expected = [
            'email'      => 'user@example.com',
            'user_ip'    => '10.0.0.1',
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'enable_ai'  => false,
        ];

        $reflection = new \ReflectionClass($fp);
        $method = $reflection->getMethod('normalizeRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($fp, [$input]);

        $this->assertEquals($expected, $normalized);
    }

    public function test_normalize_request_camel_case_aliases()
    {
        $fp = new FraudProtection('fake_api_key');

        $input = [
            'userIp'    => '10.0.0.1',
            'firstName' => 'Jane',
            'lastName'  => 'Doe',
            'enableAi'  => true,
        ];

        $reflection = new \ReflectionClass($fp);
        $method = $reflection->getMethod('normalizeRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($fp, [$input]);

        $this->assertEquals('10.0.0.1', $normalized['user_ip']);
        $this->assertEquals('Jane', $normalized['first_name']);
        $this->assertEquals('Doe', $normalized['last_name']);
        $this->assertTrue($normalized['enable_ai']);
    }

    public function test_normalize_request_form_data_array()
    {
        $fp = new FraudProtection('fake_api_key');

        $input = [
            'email'     => 'user@example.com',
            'form_data' => ['field1' => 'value1', 'field2' => 'value2'],
        ];

        $reflection = new \ReflectionClass($fp);
        $method = $reflection->getMethod('normalizeRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($fp, [$input]);

        $this->assertArrayHasKey('form_data', $normalized);
        $this->assertEquals(['field1' => 'value1', 'field2' => 'value2'], $normalized['form_data']);
    }

    public function test_normalize_request_form_data_non_array_throws()
    {
        $fp = new FraudProtection('fake_api_key');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('form_data must be provided as an array');

        $reflection = new \ReflectionClass($fp);
        $method = $reflection->getMethod('normalizeRequest');
        $method->setAccessible(true);
        $method->invokeArgs($fp, [['form_data' => 'not-an-array']]);
    }

    public function test_enable_ai_defaults_to_true()
    {
        $fp = new FraudProtection('fake_api_key');

        $reflection = new \ReflectionClass($fp);
        $method = $reflection->getMethod('normalizeRequest');
        $method->setAccessible(true);
        $normalized = $method->invokeArgs($fp, [['email' => 'user@example.com']]);

        $this->assertTrue($normalized['enable_ai']);
    }
}
