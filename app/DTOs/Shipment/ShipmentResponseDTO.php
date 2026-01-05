<?php

namespace App\DTOs\Shipment;

final readonly class ShipmentResponseDTO
{
    public function __construct(
        public string $id,
        public string $labelPdfUrl,
        public ?string $barcode,
        public ?string $trackingUrl
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['label_pdf_url'] ?? $data['label_zpl_url'] ?? '',
            $data['barcode'] ?? null,
            $data['tracking_url'] ?? null
        );
    }
}
