<?php

namespace App\DTOs\Product;

final readonly class ProductCombinationDTO
{
    public function __construct(
        public int $id,
        public string $name
    ) {}
}
