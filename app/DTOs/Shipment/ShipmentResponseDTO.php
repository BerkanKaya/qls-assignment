<?php

namespace App\DTOs\Shipment;

final readonly class ShipmentResponseDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $labelPdfUrl,
        public readonly ?string $barcode,
        public readonly ?string $trackingUrl
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
