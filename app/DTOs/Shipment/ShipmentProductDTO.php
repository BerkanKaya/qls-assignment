<?php

namespace App\DTOs\Shipment;

final readonly class ShipmentProductDTO
{
    public function __construct(
        public ?int $productId,
        public int $amount,
        public ?string $ean = null,
        public ?string $name = null,
        public ?string $sku = null
    ) {
    }

    public function toPayload(): array
    {
        $payload = [
            'product_id' => $this->productId,
            'amount' => $this->amount,
            'ean' => $this->ean,
            'name' => $this->name,
            'sku' => $this->sku,
        ];

        return array_filter($payload, static fn ($value) => $value !== null);
    }
}
