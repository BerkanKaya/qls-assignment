<?php

namespace App\DTOs\Product;

final readonly class ProductDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $ean = null
    ) {
    }
}
