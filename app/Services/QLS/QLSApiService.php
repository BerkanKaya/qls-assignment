<?php

namespace App\Services\QLS;

use App\Exceptions\QLSApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class QLSApiService
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $username,
        private readonly string $password,
        private readonly int $timeout
    ) {}

    public static function fromConfig(): self
    {
        $config = config('qls');
        return new self($config['base_url'], $config['username'], $config['password'], $config['timeout']);
    }

    public function get(string $path): array
    {
        return $this->handle($this->request()->get($path));
    }

    public function post(string $path, array $payload): array
    {
        return $this->handle($this->request()->post($path, $payload));
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson()
            ->withBasicAuth($this->username, $this->password);
    }

    private function handle(Response $response): array
    {
        $json = $response->json();

        if ($response->failed()) {
            throw QLSApiException::fromResponse($response);
        }

        if (is_array($json) && !empty($json['errors'])) {
            throw QLSApiException::fromPayload($json);
        }

        if (!is_array($json)) {
            return [];
        }

        $data = $json['data'] ?? $json;

        return is_array($data) ? $data : [];
    }
}
