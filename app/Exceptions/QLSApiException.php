<?php

namespace App\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;
use Throwable;

class QLSApiException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly array $payload = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function fromResponse(Response $response, string $fallbackMessage = 'QLS API request failed'): self
    {
        $json = $response->json();

        $payload = is_array($json)
            ? $json
            : ['raw' => $response->body()];

        $message = data_get($payload, 'message');

        return new self(
            is_string($message) && $message !== '' ? $message : $fallbackMessage,
            $payload,
            $response->status()
        );
    }

    public static function fromPayload(array $payload, string $message = 'QLS validation failed'): self
    {
        return new self($message, $payload, 422);
    }

    public function toFormErrors(): array
    {
        $fieldErrors = $this->extractFieldErrors($this->payload);

        if ($fieldErrors === []) {
            return ['api' => $this->fallbackMessage()];
        }

        $mapped = [];

        foreach ($fieldErrors as $field => $message) {
            if ($message === 'Verplicht') {
                $message = 'Vul de verplichte velden in.';
            }

            $mapped[$this->mapField($field)] = $message;
        }

        return $mapped;
    }

    private function fallbackMessage(): string
    {
        return match ($this->getCode()) {
            401, 403 => 'QLS authentication failed.',
            429 => 'QLS rate limit reached. Try again in a moment.',
            default => 'Shipment creation failed.',
        };
    }

    private function mapField(string $field): string
    {
        $field = str_replace('receiver_contact.', 'delivery.', $field);
        $field = str_replace('sender_contact.', 'billing.', $field);
        $field = str_replace('.locality', '.city', $field);

        return $field;
    }

    private function extractFieldErrors(array $payload): array
    {
        $errors = $payload['errors'] ?? null;

        if (!is_array($errors)) {
            return [];
        }

        return $this->flattenErrorTree($errors);
    }

    private function flattenErrorTree(array $tree, string $prefix = ''): array
    {
        $out = [];

        foreach ($tree as $key => $value) {
            if ($prefix === '' && str_starts_with((string) $key, '_') && is_string($value) && $value !== '') {
                $out['api'] = $this->appendMessage($out['api'] ?? null, $value);
                continue;
            }

            $path = $prefix === '' ? (string) $key : $prefix . '.' . (string) $key;

            if (is_string($value) && $value !== '') {
                $field = $this->stripErrorMarker($path);
                $out[$field] = $this->appendMessage($out[$field] ?? null, $value);
                continue;
            }

            if (is_array($value)) {
                foreach ($this->flattenErrorTree($value, $path) as $k => $v) {
                    $out[$k] = $this->appendMessage($out[$k] ?? null, $v);
                }
            }
        }

        return $out;
    }

    private function stripErrorMarker(string $path): string
    {
        $markers = ['._required', '._invalid', '._min', '._max', '._exists'];

        foreach ($markers as $marker) {
            if (str_ends_with($path, $marker)) {
                return substr($path, 0, -strlen($marker));
            }
        }

        return $path;
    }

    private function appendMessage(?string $existing, string $new): string
    {
        if ($existing === null || $existing === '') {
            return $new;
        }

        if (str_contains($existing, $new)) {
            return $existing;
        }

        return $existing . ' ' . $new;
    }
}
