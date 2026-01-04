<?php

namespace Tests\Unit\Services\QLS;

use App\Exceptions\QLSApiException;
use App\Services\QLS\QLSApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QLSApiServiceTest extends TestCase
{
    public function test_get_returns_data_payload(): void
    {
        Http::fake([
            'https://example.test/*' => Http::response(['data' => ['id' => 'x']], 200),
        ]);

        $api = new QLSApiService('https://example.test', 'u', 'p', 10);

        $this->assertSame(['id' => 'x'], $api->get('/anything'));
    }

    public function test_failed_response_throws_qls_api_exception(): void
    {
        Http::fake([
            'https://example.test/*' => Http::response(['message' => 'Bad request'], 400),
        ]);

        $api = new QLSApiService('https://example.test', 'u', 'p', 10);

        $this->expectException(QLSApiException::class);
        $this->expectExceptionCode(400);

        $api->get('/anything');
    }

    public function test_successful_response_with_errors_key_throws_validation_exception(): void
    {
        Http::fake([
            'https://example.test/*' => Http::response(['errors' => ['api' => 'Nope']], 200),
        ]);

        $api = new QLSApiService('https://example.test', 'u', 'p', 10);

        $this->expectException(QLSApiException::class);
        $this->expectExceptionCode(422);

        $api->post('/anything', ['x' => 'y']);
    }
}
