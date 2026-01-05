<?php

namespace App\DTOs\Order;

final readonly class OrderLineDTO
{
    public function __construct(
        public string $sku,
        public string $name,
        public int $amountOrdered,
        public ?string $ean = null
    ) {
    }
}
