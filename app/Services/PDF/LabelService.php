<?php

namespace App\Services\PDF;

use App\Exceptions\PDFGenerationException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class LabelService
{
    public function __construct(
        private readonly string $username,
        private readonly string $password,
        private readonly int $timeout = 30
    ) {}

    public function fetch(string $labelUrl): string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withBasicAuth($this->username, $this->password)
                ->get($labelUrl)
                ->throw();
        } catch (RequestException $e) {
            $status = $e->response?->status() ?? 'unknown';
            throw new PDFGenerationException("Failed to download label (HTTP {$status})", 0, $e);
        }

        return $this->extractPdfFromJson($response->json());
    }

    private function extractPdfFromJson(array|null $json): string
    {
        if (!is_array($json) || !isset($json['data']) || !is_string($json['data'])) {
            throw new PDFGenerationException('Label response is missing valid "data" base64 field');
        }

        $pdf = base64_decode($json['data'], strict: true);

        if ($pdf === false || !str_starts_with($pdf, '%PDF-')) {
            throw new PDFGenerationException('Decoded label is not a valid PDF');
        }

        return $pdf;
    }
}
