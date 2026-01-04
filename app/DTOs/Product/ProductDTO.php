<?php

namespace App\DTOs\Product;

final readonly class ProductDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $ean = null
    ) {
    }
}
