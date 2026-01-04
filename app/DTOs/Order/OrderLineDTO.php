<?php

namespace App\DTOs\Order;

final readonly class OrderLineDTO
{
    public function __construct(
        public readonly string $sku,
        public readonly string $name,
        public readonly int $amountOrdered,
        public readonly ?string $ean = null
    ) {
    }
}
