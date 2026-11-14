<?php

use GuzzleHttp\Psr7\MultipartStream;
use Mockery as m;
use OpenAPI\Client\Api\EmailInsightsApi;
use OpenAPI\Client\Api\IpInsightsApi;
use Opportify\Sdk\EmailInsights;
use Opportify\Sdk\IpInsights;
use PHPUnit\Framework\TestCase;

/**
 * The batch API reads `enableAI` and `enableAutoCorrection` from multipart forms.
 * Any other spelling is ignored server-side, so the wire names must not change.
 */
class BatchMultipartFieldNamesTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_email_multipart_batch_sends_flags_under_the_api_field_names()
    {
        $captured = '';
        $api = m::mock(EmailInsightsApi::class);
        $api->shouldReceive('batchAnalyzeEmails')
            ->once()
            ->with(m::on(function ($stream) use (&$captured) {
                $captured = (string) $stream;

                return $stream instanceof MultipartStream;
            }), 'multipart/form-data')
            ->andReturn($this->jobResponse());

        $file = $this->tempFile('test1@example.com');

        // Accepted input spellings are unchanged: snake_case and camelCase both work.
        (new EmailInsights('fake_api_key', $api))->batchAnalyze([
            'file' => $file,
            'enable_ai' => false,
            'enableAutoCorrection' => true,
        ], 'multipart/form-data');

        unlink($file);

        $this->assertMatchesRegularExpression('/name="enableAI"\r\nContent-Length: \d+\r\n\r\nfalse/', $captured);
        $this->assertMatchesRegularExpression('/name="enableAutoCorrection"\r\nContent-Length: \d+\r\n\r\ntrue/', $captured);
        $this->assertStringNotContainsString('name="enable_ai"', $captured);
        $this->assertStringNotContainsString('name="enable_auto_correction"', $captured);
    }

    public function test_email_multipart_batch_omits_flags_that_were_not_provided()
    {
        $captured = '';
        $api = m::mock(EmailInsightsApi::class);
        $api->shouldReceive('batchAnalyzeEmails')
            ->once()
            ->with(m::on(function ($stream) use (&$captured) {
                $captured = (string) $stream;

                return true;
            }), 'multipart/form-data')
            ->andReturn($this->jobResponse());

        $file = $this->tempFile('test1@example.com');

        (new EmailInsights('fake_api_key', $api))->batchAnalyze(['file' => $file], 'multipart/form-data');

        unlink($file);

        $this->assertStringNotContainsString('name="enableAI"', $captured);
        $this->assertStringNotContainsString('name="enableAutoCorrection"', $captured);
    }

    public function test_ip_multipart_batch_sends_flag_under_the_api_field_name()
    {
        $captured = '';
        $api = m::mock(IpInsightsApi::class);
        $api->shouldReceive('batchAnalyzeIps')
            ->once()
            ->with(m::on(function ($stream) use (&$captured) {
                $captured = (string) $stream;

                return $stream instanceof MultipartStream;
            }), 'multipart/form-data')
            ->andReturn($this->jobResponse());

        $file = $this->tempFile('192.0.2.1');

        (new IpInsights('fake_api_key', $api))->batchAnalyze([
            'file' => $file,
            'enable_ai' => true,
        ], 'multipart/form-data');

        unlink($file);

        $this->assertMatchesRegularExpression('/name="enableAI"\r\nContent-Length: \d+\r\n\r\ntrue/', $captured);
        $this->assertStringNotContainsString('name="enable_ai"', $captured);
    }

    private function jobResponse()
    {
        $response = m::mock();
        $response->shouldReceive('jsonSerialize')->andReturn((object) ['jobId' => 'job-1', 'status' => 'QUEUED']);

        return $response;
    }

    private function tempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'batch_multipart_');
        file_put_contents($path, $contents);

        return $path;
    }
}
