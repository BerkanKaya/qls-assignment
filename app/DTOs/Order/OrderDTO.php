<?php

namespace App\DTOs\Order;

use App\DTOs\Common\ContactDetailsDTO;

final readonly class OrderDTO
{
    public function __construct(
        public string $number,
        public ContactDetailsDTO $billingAddress,
        public ContactDetailsDTO $deliveryAddress,
        public array $orderLines
    ) {}
}
