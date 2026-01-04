<?php

namespace App\DTOs\Shipment;

final readonly class ShipmentProductDTO
{
    public function __construct(
        public readonly ?int $productId,
        public readonly int $amount,
        public readonly ?string $ean = null,
        public readonly ?string $name = null,
        public readonly ?string $sku = null
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
