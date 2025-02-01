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
}
